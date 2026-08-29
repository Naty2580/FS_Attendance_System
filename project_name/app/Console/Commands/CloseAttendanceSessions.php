<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\AttendanceSession;
use App\Models\AttendanceStatus;
use App\Models\Student;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CloseAttendanceSessions extends Command
{
    protected $signature = 'attendance:close-sessions';
    protected $description = 'Closes expired attendance sessions and marks unmarked students as absent.';

   public function handle()
    {
        $now = Carbon::now();
        
        // 1. Fetch IDs of sessions that MIGHT be expired
        // (We don't lock them yet, we just get the IDs to avoid locking the whole table)
        $sessionIds = AttendanceSession::where('status', '!=', 'closed')->pluck('id');

        $absentStatus = AttendanceStatus::where('code', 'absent')->first();
        $systemUser = User::role('System Administrator')->first();

        if (!$absentStatus || !$systemUser) {
            $this->error('Missing Absent status or System Admin user.');
            return;
        }

        $closedCount = 0;
        $autoAbsentCount = 0;

        foreach ($sessionIds as $sessionId) {
            
            // 2. Wrap each session evaluation in its own dedicated transaction
            DB::transaction(function () use ($sessionId, $now, $absentStatus, $systemUser, &$closedCount, &$autoAbsentCount) {
                
                // 3. 🛑 PESSIMISTIC LOCK: Lock this specific session row!
                // If a teacher clicks "End Session" right now, they must wait until this transaction finishes.
                $session = AttendanceSession::with('schedule')->where('id', $sessionId)->lockForUpdate()->first();

                // Double check it wasn't closed by the teacher while we were waiting to get the lock
                if (!$session || $session->status === 'closed') {
                    return; // Skip it safely
                }

                $totalDuration = $session->schedule->total_session_minutes;
                $closingDateTime = $session->started_at->copy()->addMinutes($totalDuration);

                // If current time has passed the dynamic closing time
                if ($now->greaterThanOrEqualTo($closingDateTime)) {
                    
                    $markedStudentIds = $session->records()->pluck('student_id')->toArray();

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

                    $insertData = [];
                    foreach ($enrolledStudents as $student) {
                        if (!in_array($student->id, $markedStudentIds)) {
                            $insertData[] = [
                                'id' => (string) \Illuminate\Support\Str::ulid(),
                                'attendance_session_id' => $session->id,
                                'student_id' => $student->id,
                                'attendance_status_id' => $absentStatus->id,
                                'recorded_by' => $systemUser->id,
                                'recorded_at' => Carbon::now(),
                                'created_at' => Carbon::now(),
                                'updated_at' => Carbon::now(),
                            ];
                            $autoAbsentCount++;
                        }
                    }

                    if (!empty($insertData)) {
                        DB::table('attendance_records')->insert($insertData);
                    }
                    
                    $session->update(['status' => 'closed']);
                    $closedCount++;
                }
            });
        }

        $this->info("Closed {$closedCount} sessions and auto-marked {$autoAbsentCount} students as absent.");
        Log::info("Attendance Auto-Close: Closed {$closedCount} sessions, auto-absent {$autoAbsentCount} students.");
    }
}