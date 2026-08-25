<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class AttendanceSession extends Model
{
    use HasUlids;

    protected $fillable = [
        'attendance_schedule_id',
        'class_id',
        'session_date',
        'started_at',
        'status', // active, closed
    ];

    protected $casts = [
        'session_date' => 'date',
        'started_at' => 'datetime',
    ];

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(AttendanceSchedule::class, 'attendance_schedule_id');
    }

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function records(): HasMany
    {
        return $this->hasMany(AttendanceRecord::class, 'attendance_session_id');
    }

    // --- The Simplified State Machine ---

    public function getCurrentWindow(?Carbon $now = null): string
    {
        if ($this->status === 'closed') {
            return 'closed';
        }

        $now = $now ?? Carbon::now();
        $startedAt = $this->started_at;
        $schedule = $this->schedule;

        // Calculate dynamic windows based on exactly when the teacher clicked "Start"
        $presentUntil = $startedAt->copy()->addMinutes($schedule->present_grace_minutes);
        $lateUntil = $presentUntil->copy()->addMinutes($schedule->late_grace_minutes);

        if ($now->lessThanOrEqualTo($presentUntil)) {
            return 'present';
        }

        if ($now->lessThanOrEqualTo($lateUntil)) {
            return 'late';
        }

        // If it's past the Late Window, the frontend buttons lock down.
        return 'closed';
    }

    public function canRecordStatus(string $statusCode, ?Carbon $now = null): bool
    {
        $window = $this->getCurrentWindow($now);

        if ($window === 'closed') {
            return false;
        }

        if ($window === 'present') {
            return in_array($statusCode, ['present', 'permission', 'absent']);
        }

        if ($window === 'late') {
            return in_array($statusCode, ['late', 'permission', 'absent']);
        }

        return false;
    }
}