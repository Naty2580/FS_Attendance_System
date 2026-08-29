<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\AttendanceSession;
use App\Models\Student;
use App\Models\AttendanceRecord;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class AttendanceRosterController extends Controller
{
    public function show(Request $request, string $sessionId)
    {
        // 1. Fetch the unified Session
        $session = AttendanceSession::with(['schedule.sessionType', 'schoolClass'])
            ->findOrFail($sessionId);

        // 2. Format session details for the frontend
        $sessionData = [
            'id' => $session->id,
            'class_id' => $session->class_id,
            'class_name' => $session->schoolClass->name,
            'type' => $session->schedule->sessionType->name,
            'date' => $session->session_date->format('Y-m-d'),
            // We show the teacher when they ACTUALLY started it
            'started_at' => Carbon::parse($session->started_at)->format('H:i'), 
            'current_window' => $session->getCurrentWindow(), 
        ];
        $sessionDate = $session->session_date->format('Y-m-d');

        // 3. Fetch all active students currently assigned to this class
        // 3. Fetch all active students who were assigned to this class ON the date of the session
        $sessionDate = $session->session_date->format('Y-m-d');

        $students = Student::where('enrollment_status', 'active')
            ->whereHas('classHistory', function ($query) use ($session, $sessionDate) {
                $query->where('class_id', $session->class_id)
                      // The assignment must have started before or on the session date
                      ->where('started_at', '<=', $sessionDate)
                      // And it must not have ended before the session date
                      ->where(function ($q) use ($sessionDate) {
                          $q->whereNull('ended_at')
                            ->orWhere('ended_at', '>=', $sessionDate);
                      });
            })
            ->orderBy('first_name')
            ->get()
            ->map(function ($student) {
                return [
                    'id' => $student->id,
                    'name' => $student->full_name,
                    'student_number' => $student->student_number,
                ];
            });

        // 4. Fetch existing attendance records (Now pointing directly to session_id)
        $existingRecords = AttendanceRecord::with('status')
            ->where('attendance_session_id', $sessionId)
            ->get()
            ->mapWithKeys(function ($record) {
                return [$record->student_id => $record->status->code];
            });

        return Inertia::render('Mobile/AttendanceRoster', [
            'sessionClass' => $sessionData, // Kept the variable name 'sessionClass' so we don't have to change React props
            'students' => $students,
            'existingRecords' => $existingRecords,
        ]);
    }

    public function endSession(Request $request, string $sessionId)
    {
        $session = AttendanceSession::findOrFail($sessionId);

        // Security Check: Make sure the user is actually assigned to this class today
       $user = $request->user();
        $isGlobalAdmin = $user->hasRole(['System Administrator', 'HR Leader']);

        if (!$isGlobalAdmin) {
            $isAssigned = \App\Models\AttendanceAssignment::where('user_id', $user->id)
                ->where('class_id', $session->class_id)
                ->where('is_active', true)
                ->where('starts_at', '<=', $session->session_date)
                ->where(function ($query) use ($session) {
                    $query->whereNull('ends_at')->orWhere('ends_at', '>=', $session->session_date);
                })->exists();

            if (!$isAssigned) {
                abort(403, 'Unauthorized');
            }
        }
        // We don't need to auto-mark anyone absent here, because the frontend 
        // will only reveal this button when 100% of students are already marked!
        $session->update(['status' => 'closed']);

        return redirect()->back()->with('success', 'Session ended successfully!');
    }

    public function bulkMarkPresent(Request $request, string $sessionId)
    {
        $session = AttendanceSession::findOrFail($sessionId);

        // Security Check: Is the user assigned to this class?
        $user = $request->user();
        $isGlobalAdmin = $user->hasRole(['System Administrator', 'HR Leader']);

        if (!$isGlobalAdmin) {
            $isAssigned = \App\Models\AttendanceAssignment::where('user_id', $user->id)
                ->where('class_id', $session->class_id)
                ->where('is_active', true)
                ->where('starts_at', '<=', $session->session_date)
                ->where(function ($query) use ($session) {
                    $query->whereNull('ends_at')->orWhere('ends_at', '>=', $session->session_date);
                })->exists();

            if (!$isAssigned) {
                abort(403, 'Unauthorized');
            }
        }

        // Security Check: Is the session actually open?
        if ($session->status === 'closed') {
            return redirect()->back()->withErrors(['error' => 'Cannot modify a closed session.']);
        }

        // We only allow this if the current window still allows "Present" marks
        if (!$session->canRecordStatus('present')) {
            return redirect()->back()->withErrors(['error' => 'The Present window has already closed.']);
        }

        $presentStatus = \App\Models\AttendanceStatus::where('code', 'present')->first();
        $userId = $request->user()->id;
        $now = Carbon::now();

        \Illuminate\Support\Facades\DB::transaction(function () use ($session, $presentStatus, $userId, $now) {
            // 1. Lock the session row to prevent the Auto-Close Cron from running at the same time
            $session->lockForUpdate();

            // 2. Get IDs of students ALREADY marked (whether present, absent, etc.)
            $markedStudentIds = $session->records()->pluck('student_id')->toArray();

            // 3. Get all currently active students assigned to this exact class
            $sessionDate = $session->session_date->format('Y-m-d');
            $enrolledStudents = Student::where('enrollment_status', 'active')
                ->whereHas('classHistory', function ($query) use ($session, $sessionDate) {
                    $query->where('class_id', $session->class_id)
                          ->where('started_at', '<=', $sessionDate)
                          ->where(function ($q) use ($sessionDate) {
                              $q->whereNull('ended_at')
                                ->orWhere('ended_at', '>=', $sessionDate);
                          });
                })->get();

            // 4. Bulk prepare the un-marked students
            $insertData = [];
            foreach ($enrolledStudents as $student) {
                if (!in_array($student->id, $markedStudentIds)) {
                    $insertData[] = [
                        'id' => (string) \Illuminate\Support\Str::ulid(),
                        'attendance_session_id' => $session->id,
                        'student_id' => $student->id,
                        'attendance_status_id' => $presentStatus->id,
                        'recorded_by' => $userId,
                        'recorded_at' => $now,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
            }

            // 5. Bulk Insert for massive performance
            if (!empty($insertData)) {
                \Illuminate\Support\Facades\DB::table('attendance_records')->insert($insertData);
            }
        }, 3); // 3 Retries for deadlock protection

        return redirect()->back()->with('success', 'All remaining students marked as Present!');    }
}