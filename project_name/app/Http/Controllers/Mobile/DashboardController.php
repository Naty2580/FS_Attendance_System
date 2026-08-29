<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\AttendanceSchedule;
use App\Models\AttendanceSession;
use App\Models\AttendanceSessionClass;
use App\Models\AttendanceAssignment;
use App\Models\SchoolClass;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\UniqueConstraintViolationException;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $now = Carbon::now();
        $todayDateString = $now->format('Y-m-d');
        $dayOfWeek = strtolower($now->englishDayOfWeek);
        $user = $request->user();

        // Check if user has God Mode / HR Leader override powers
        $isGlobalAdmin = $user->hasRole(['System Administrator', 'HR Leader']);

        if ($isGlobalAdmin) {
            // Admins get to see ALL active classes
            $assignedClassIds = SchoolClass::where('is_active', true)->pluck('id')->toArray();
        } else {
            // Regular teachers only see their assigned classes
            $assignedClassIds = AttendanceAssignment::where('user_id', $user->id)
                ->where('is_active', true)
                ->where('starts_at', '<=', $todayDateString)
                ->where(function ($query) use ($todayDateString) {
                    $query->whereNull('ends_at')->orWhere('ends_at', '>=', $todayDateString);
                })->pluck('class_id')->toArray();
        }

        if (empty($assignedClassIds)) {
            return Inertia::render('Mobile/Dashboard', ['todaySessions' => [], 'isAdmin' => $isGlobalAdmin]);
        }

        // Find active Schedule Blueprints for TODAY that include the targeted classes
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

        $dashboardItems = [];
        
        foreach ($schedules as $schedule) {
            foreach ($schedule->classes as $schoolClass) {
                
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
                    if ($schedule->expected_start_time) {
                        $expectedTime = Carbon::parse($todayDateString . ' ' . $schedule->expected_start_time);
                        $earliestStart = $expectedTime->copy()->subMinutes($schedule->start_window_before_minutes);
                        $latestStart = $expectedTime->copy()->addMinutes($schedule->start_window_after_minutes);

                        if ($now->between($earliestStart, $latestStart)) {
                            $isStartable = true;
                        } elseif ($now->greaterThan($latestStart)) {
                            $window = 'expired'; 
                        } else {
                            $window = 'too_early'; 
                        }
                    } else {
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
                    'status' => $status, 
                    'current_window' => $window,
                    'is_startable' => $isStartable,
                ];
            }
        }

        // Sort items so classes appear in a logical order (e.g., Class 1A, then 1B)
        usort($dashboardItems, function ($a, $b) {
            return strcmp($a['class_name'], $b['class_name']);
        });

        return Inertia::render('Mobile/Dashboard', [
            'todaySessions' => $dashboardItems,
            'isAdmin' => $isGlobalAdmin // Send flag to frontend to show a warning badge
        ]);
    }

    public function startOrJoinSession(Request $request)
    {
        $request->validate([
            'schedule_id' => 'required|string',
            'class_id' => 'required|string',
        ]);

        $now = Carbon::now();
        $todayDateString = $now->format('Y-m-d');
        
        $schedule = AttendanceSchedule::findOrFail($request->schedule_id);
        $user = $request->user();
        
        $isGlobalAdmin = $user->hasRole(['System Administrator', 'HR Leader']);

        // Security Validation
        if (!$isGlobalAdmin) {
            $isAssigned = AttendanceAssignment::where('user_id', $user->id)
                ->where('class_id', $request->class_id)
                ->where('is_active', true)
                ->where('starts_at', '<=', $todayDateString)
                ->where(function ($query) use ($todayDateString) {
                    $query->whereNull('ends_at')->orWhere('ends_at', '>=', $todayDateString);
                })->exists();

            if (!$isAssigned) {
                abort(403, 'You are not assigned to this class.');
            }
        }

        try {
            $session = DB::transaction(function () use ($schedule, $todayDateString, $now, $request) {
                
                $blueprintStart = Carbon::parse($schedule->expected_start_time ?? '00:00:00');
                
                // We fake the close time dynamically relative to NOW
                $totalDurationMinutes = $schedule->total_session_minutes;
                
                return AttendanceSession::firstOrCreate(
                    [
                        'attendance_schedule_id' => $schedule->id,
                        'class_id' => $request->class_id,
                        'session_date' => $todayDateString,
                    ],
                    [
                        'id' => (string) Str::ulid(),
                        'started_at' => $now, 
                        'status' => 'active',
                    ]
                );
            });
            
        } catch (UniqueConstraintViolationException $e) {
            $session = AttendanceSession::where('attendance_schedule_id', $schedule->id)
                ->where('class_id', $request->class_id)
                ->where('session_date', $todayDateString)
                ->firstOrFail();
        }

        return redirect()->route('attendance.roster', ['sessionId' => $session->id]);
    }
}