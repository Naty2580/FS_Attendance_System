<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceRecordChange extends Model
{
    use HasUlids;

    // We only need created_at for history
    public const UPDATED_AT = null;

    protected $fillable = [
        'attendance_record_id',
        'old_status_id',
        'new_status_id',
        'changed_by',
        'reason',
    ];

    public function record(): BelongsTo
    {
        return $this->belongsTo(AttendanceRecord::class, 'attendance_record_id');
    }

    public function oldStatus(): BelongsTo
    {
        return $this->belongsTo(AttendanceStatus::class, 'old_status_id');
    }

    public function newStatus(): BelongsTo
    {
        return $this->belongsTo(AttendanceStatus::class, 'new_status_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}