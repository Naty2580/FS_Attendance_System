<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\AttendanceSchedule;
use App\Models\AttendanceSession;
use App\Models\AttendanceAssignment;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $now = Carbon::now();
        $todayDateString = $now->format('Y-m-d');
        $dayOfWeek = strtolower($now->englishDayOfWeek); // e.g., 'sunday'
        $user = $request->user();

        // 1. Get classes the user is assigned to today
        $assignedClassIds = AttendanceAssignment::where('user_id', $user->id)
            ->where('is_active', true)
            ->where('starts_at', '<=', $todayDateString)
            ->where(function ($query) use ($todayDateString) {
                $query->whereNull('ends_at')->orWhere('ends_at', '>=', $todayDateString);
            })->pluck('class_id')->toArray();

        if (empty($assignedClassIds)) {
            return Inertia::render('Mobile/Dashboard', ['todaySessions' => []]);
        }

        // 2. Find active Schedule Blueprints for TODAY that include these classes
        $schedules = AttendanceSchedule::with(['sessionType', 'classes' => function ($q) use ($assignedClassIds) {
                $q->whereIn('classes.id', $assignedClassIds);
            }])
            ->where('is_active', true)
            ->whereRaw('LOWER(day_of_week) = ?', [$dayOfWeek])
            ->where('effective_from', '<=', $todayDateString)
            ->where(function ($query) use ($todayDateString) {
                $query->whereNull('effective_until')->orWhere('effective_until', '>=', $todayDateString);
            })
            ->whereHas('classes', function ($q) use ($assignedClassIds) {
                $q->whereIn('classes.id', $assignedClassIds);
            })
            ->get();

        // 3. Format for the UI
        $dashboardItems = [];
        
        foreach ($schedules as $schedule) {
            foreach ($schedule->classes as $schoolClass) {
                
                // Check if a session already exists for this exact class and schedule today
                $existingSession = AttendanceSession::where('attendance_schedule_id', $schedule->id)
                    ->where('class_id', $schoolClass->id)
                    ->where('session_date', $todayDateString)
                    ->first();

                $sessionId = null;
                $status = 'not_started';
                $window = 'not_started';
                $isStartable = false;
                $expectedStartString = $schedule->expected_start_time ? Carbon::parse($schedule->expected_start_time)->format('H:i') : null;

                if ($existingSession) {
                    $sessionId = $existingSession->id;
                    $status = 'created';
                    $window = $existingSession->getCurrentWindow();
                } else {
                    // Session hasn't started. Calculate if the teacher is ALLOWED to start it right now.
                    if ($schedule->expected_start_time) {
                        $expectedTime = Carbon::parse($todayDateString . ' ' . $schedule->expected_start_time);
                        $earliestStart = $expectedTime->copy()->subMinutes($schedule->start_window_before_minutes);
                        $latestStart = $expectedTime->copy()->addMinutes($schedule->start_window_after_minutes);

                        if ($now->between($earliestStart, $latestStart)) {
                            $isStartable = true;
                        } elseif ($now->greaterThan($latestStart)) {
                            $window = 'expired'; // They missed the window to start it entirely
                        } else {
                            $window = 'too_early'; // It's not time yet
                        }
                    } else {
                        // If admin didn't set an expected time, it can be started anytime today
                        $isStartable = true;
                    }
                }

                $dashboardItems[] = [
                    'schedule_id' => $schedule->id,
                    'class_id' => $schoolClass->id,
                    'session_id' => $sessionId,
                    'class_name' => $schoolClass->name,
                    'type' => $schedule->sessionType->name,
                    'expected_start' => $expectedStartString,
                    'status' => $status, // 'not_started' or 'created'
                    'current_window' => $window,
                    'is_startable' => $isStartable,
                ];
            }
        }

        return Inertia::render('Mobile/Dashboard', [
            'todaySessions' => $dashboardItems,
        ]);
    }

    /**
     * Triggers when the teacher taps "Start Session"
     */
    public function startOrJoinSession(Request $request)
    {
        $request->validate([
            'schedule_id' => 'required|string',
            'class_id' => 'required|string',
        ]);

        $now = Carbon::now();
        $todayDateString = $now->format('Y-m-d');
        
        $schedule = AttendanceSchedule::findOrFail($request->schedule_id);

        // Security Validation: Ensure the user is actually assigned to this class today
        $isAssigned = AttendanceAssignment::where('user_id', $request->user()->id)
            ->where('class_id', $request->class_id)
            ->where('is_active', true)
            ->where('starts_at', '<=', $todayDateString)
            ->where(function ($query) use ($todayDateString) {
                $query->whereNull('ends_at')->orWhere('ends_at', '>=', $todayDateString);
            })->exists();

        if (!$isAssigned) {
            abort(403, 'You are not assigned to this class.');
        }

        $session = DB::transaction(function () use ($schedule, $todayDateString, $now, $request) {
            
            // Create or Find the Session
            return AttendanceSession::firstOrCreate(
                [
                    'attendance_schedule_id' => $schedule->id,
                    'class_id' => $request->class_id,
                    'session_date' => $todayDateString,
                ],
                [
                    'id' => (string) Str::ulid(),
                    'started_at' => $now, // The exact click time becomes the unalterable anchor!
                    'status' => 'active',
                ]
            );
        });

        // Redirect directly to the Roster using the new unified Session ID
        return redirect()->route('attendance.roster', ['sessionId' => $session->id]);
    }
}