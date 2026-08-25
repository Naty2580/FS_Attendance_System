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
        
        // Find sessions that are not closed
        $sessions = AttendanceSession::with(['schedule', 'schoolClass.studentHistory.student'])
            ->where('status', '!=', 'closed')
            ->get();

        $absentStatus = AttendanceStatus::where('code', 'absent')->first();
        $systemUser = User::role('System Administrator')->first();

        if (!$absentStatus || !$systemUser) {
            $this->error('Missing Absent status or System Admin user.');
            return;
        }

        $closedCount = 0;
        $autoAbsentCount = 0;

        foreach ($sessions as $session) {
            // Dynamic Expiry = When the teacher clicked start + the total duration set by HR
            $totalDuration = $session->schedule->total_session_minutes;
            $closingDateTime = $session->started_at->copy()->addMinutes($totalDuration);

            // If current time has passed the dynamic closing time
            if ($now->greaterThanOrEqualTo($closingDateTime)) {
                DB::transaction(function () use ($session, $absentStatus, $systemUser, &$autoAbsentCount) {
                    
                    // 1. Get IDs of students already marked
                    $markedStudentIds = $session->records()->pluck('student_id')->toArray();

                    // 2. Get all currently active students assigned to this exact class
                    $enrolledStudents = Student::where('enrollment_status', 'active')
                        ->whereHas('classHistory', function ($query) use ($session) {
                            $query->where('class_id', $session->class_id)
                                  ->where('is_current', true);
                        })->get();

                    // 3. Mark the missing ones as absent
                    $insertData = [];
                    foreach ($enrolledStudents as $student) {
                        if (!in_array($student->id, $markedStudentIds)) {
                            $insertData[] = [
                                'id' => (string) Str::ulid(),
                                'attendance_session_id' => $session->id, // Simplified!
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
                    
                    // Mark session as closed
                    $session->update(['status' => 'closed']);
                });

                $closedCount++;
            }
        }

        $this->info("Closed {$closedCount} sessions and auto-marked {$autoAbsentCount} students as absent.");
        Log::info("Attendance Auto-Close: Closed {$closedCount} sessions, auto-absent {$autoAbsentCount} students.");
    }
}