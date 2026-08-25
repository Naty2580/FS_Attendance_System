<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\AttendanceSchedule;
use App\Models\AttendanceSession;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GenerateWeeklySessions extends Command
{
    // Keeping the same command signature so we don't have to rename files, 
    // but the logic inside is now a "Rolling Horizon".
    protected $signature = 'attendance:generate-weekly';
    protected $description = 'Maintains a rolling 14-day horizon of future attendance sessions based on active schedules.';

    public function handle()
    {
        // Define the rolling horizon (e.g., always keep the next 14 days generated)
        $horizonDays = 14;
        $startDate = Carbon::today();
        $endDate = Carbon::today()->addDays($horizonDays);
        
        $this->info("Checking rolling horizon from {$startDate->toDateString()} to {$endDate->toDateString()}...");
        $generatedCount = 0;

        // Loop through every single date in our 14-day window
        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            
            $dayOfWeek = strtolower($date->englishDayOfWeek); // Convert 'Saturday' to 'saturday'

            // We use DB::raw to make the database comparison case-insensitive
            $schedules = AttendanceSchedule::with('classes')
                ->where('is_active', true)
                ->whereRaw('LOWER(day_of_week) = ?', [$dayOfWeek])
                ->where('effective_from', '<=', $date) 
                ->where(function ($query) use ($date) {
                    $query->whereNull('effective_until')
                          ->orWhere('effective_until', '>=', $date); 
                })->get();

            foreach ($schedules as $schedule) {
                // Check if this exact session has already been generated for this date
                $exists = AttendanceSession::where('attendance_schedule_id', $schedule->id)
                    ->where('session_date', $date->toDateString())
                    ->exists();

                if (!$exists) {
                    DB::transaction(function () use ($schedule, $date, &$generatedCount) {
                        
                        // 1. Create the Session
                        $session = AttendanceSession::create([
                            'id' => (string) Str::ulid(),
                            'session_type_id' => $schedule->session_type_id,
                            'attendance_schedule_id' => $schedule->id,
                            'session_date' => $date->toDateString(),
                            'starts_at' => $schedule->start_time,
                            'present_until' => $schedule->present_until,
                            'closes_at' => $schedule->close_at,
                            'status' => 'scheduled', 
                        ]);

                        // 2. Attach the classes dynamically
                        $insertData = [];
                        foreach ($schedule->classes as $schoolClass) {
                            $insertData[] = [
                                'id' => (string) Str::ulid(),
                                'attendance_session_id' => $session->id,
                                'class_id' => $schoolClass->id,
                                'status' => 'pending',
                                'created_at' => Carbon::now(),
                                'updated_at' => Carbon::now(),
                            ];
                        }

                        if (!empty($insertData)) {
                            DB::table('attendance_session_classes')->insert($insertData);
                        }
                        
                        $generatedCount++;
                    });
                }
            }
        }

        $this->info("Successfully generated {$generatedCount} new sessions to maintain the 14-day horizon!");
        Log::info("Rolling Scheduler generated {$generatedCount} missing sessions.");
    }
}