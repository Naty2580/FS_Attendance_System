<?php

namespace App\Observers;

use App\Models\AttendanceRecordChange;
use App\Models\AuditLog;
use Illuminate\Support\Str;

class AttendanceRecordChangeObserver
{
    public function created(AttendanceRecordChange $change): void
    {
        AuditLog::create([
            'id' => (string) Str::ulid(),
            'user_id' => $change->changed_by ?? auth()->id(),
            'action' => 'attendance_modified',
            'auditable_type' => AttendanceRecordChange::class,
            'auditable_id' => $change->id,
            'old_values' => ['status_id' => $change->old_status_id],
            'new_values' => ['status_id' => $change->new_status_id, 'reason' => $change->reason],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}