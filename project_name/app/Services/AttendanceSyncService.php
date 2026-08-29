<?php

namespace App\Services;

use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\AttendanceStatus;
use App\Models\AttendanceAssignment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AttendanceSyncService
{
    public function processQueue(array $records, string $userId): array
    {
        $results = [
            'synced' => [],
            'failed' => [],
        ];

        $statuses = AttendanceStatus::all()->keyBy('code');
        
        // Group by the new simplified session_id
        $groupedRecords = collect($records)->groupBy('attendance_session_id');

        foreach ($groupedRecords as $sessionId => $group) {
            $session = AttendanceSession::find($sessionId);
            
            if (!$session) {
                foreach ($group as $record) {
                    $results['failed'][] = ['sync_id' => $record['sync_id'], 'reason' => 'Session not found.'];
                }
                continue;
            }

            // Security: Is this user actually assigned to this class today?
           $isGlobalAdmin = auth()->user()->hasRole(['System Administrator', 'HR Leader']);

            if (!$isGlobalAdmin) {
                $isAssigned = AttendanceAssignment::where('user_id', $userId)
                    ->where('class_id', $session->class_id)
                    ->where('is_active', true)
                    ->where('starts_at', '<=', $session->session_date)
                    ->where(function ($query) use ($session) {
                        $query->whereNull('ends_at')
                              ->orWhere('ends_at', '>=', $session->session_date);
                    })->exists();

                if (!$isAssigned && !auth()->user()->can('override closed attendance')) {
                    foreach ($group as $record) {
                        $results['failed'][] = ['sync_id' => $record['sync_id'], 'reason' => 'Unauthorized for this class.'];
                    }
                    continue;
                }
            }
            $window = $session->getCurrentWindow();
            $canOverride = auth()->user()->can('override closed attendance');

            foreach ($group as $data) {
                // We use the new simplified validation method!
                if (!$canOverride && !$session->canRecordStatus($data['status_code'])) {
                    $results['failed'][] = [
                        'sync_id' => $data['sync_id'],
                        'reason' => "Status '{$data['status_code']}' not allowed during '{$window}' window.",
                    ];
                    continue;
                }

                try {
                    // 🛑 DEADLOCK PROTECTION: Notice the "3" at the end of this transaction. 
                    // This tells Laravel to retry this exact transaction up to 3 times if Postgres reports a deadlock!
                    DB::transaction(function () use ($data, $sessionId, $userId, $statuses) {
                        $newStatusId = $statuses[$data['status_code']]->id;
                        $recordedAt = Carbon::parse($data['recorded_at']);

                        // Pessimistic lock on the specific student's record to prevent double-overwrites
                        $existingRecord = AttendanceRecord::where('attendance_session_id', $sessionId)
                            ->where('student_id', $data['student_id'])
                            ->lockForUpdate()
                            ->first();

                        if ($existingRecord) {
                            if ($recordedAt->greaterThan($existingRecord->recorded_at) && $existingRecord->attendance_status_id !== $newStatusId) {
                                
                                $existingRecord->changes()->create([
                                    'old_status_id' => $existingRecord->attendance_status_id,
                                    'new_status_id' => $newStatusId,
                                    'changed_by' => $userId,
                                    'reason' => 'Offline Sync Update',
                                ]);

                                $existingRecord->update([
                                    'attendance_status_id' => $newStatusId,
                                    'updated_by' => $userId,
                                    'recorded_at' => $recordedAt, 
                                ]);
                            }
                        } else {
                            AttendanceRecord::create([
                                'attendance_session_id' => $sessionId,
                                'student_id' => $data['student_id'],
                                'attendance_status_id' => $newStatusId,
                                'recorded_by' => $userId,
                                'recorded_at' => $recordedAt,
                            ]);
                        }
                    }, 3); // <-- The Magic Number (3 Retries)

                    $results['synced'][] = $data['sync_id'];

                } catch (\Exception $e) {
                    // If it fails even after 3 retries, log the exact reason and fail safely
                    Log::error("CRITICAL: Attendance Sync Failed for Student {$data['student_id']} in Session {$sessionId}. Reason: " . $e->getMessage());
                    
                    $results['failed'][] = [
                        'sync_id' => $data['sync_id'], 
                        'reason' => 'Database transaction failed.'
                    ];
                }
            }
        }

        return $results;
    }
}