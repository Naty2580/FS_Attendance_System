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
        $students = Student::where('enrollment_status', 'active')
            ->whereHas('classHistory', function ($query) use ($session) {
                $query->where('class_id', $session->class_id)
                      ->where('is_current', true);
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
        $isAssigned = \App\Models\AttendanceAssignment::where('user_id', $request->user()->id)
            ->where('class_id', $session->class_id)
            ->where('is_active', true)
            ->where('starts_at', '<=', $session->session_date)
            ->where(function ($query) use ($session) {
                $query->whereNull('ends_at')->orWhere('ends_at', '>=', $session->session_date);
            })->exists();

        if (!$isAssigned) {
            abort(403, 'Unauthorized');
        }

        // We don't need to auto-mark anyone absent here, because the frontend 
        // will only reveal this button when 100% of students are already marked!
        $session->update(['status' => 'closed']);

        return redirect()->back(); // Refreshes the Inertia page
    }
}