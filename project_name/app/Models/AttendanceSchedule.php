<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AttendanceSchedule extends Model
{
    use HasUlids;

    protected $fillable = [
        'session_type_id',
        'name',
        'day_of_week',
        'expected_start_time',
        'start_window_before_minutes',
        'start_window_after_minutes',
        'present_grace_minutes',
        'late_grace_minutes',
        'total_session_minutes',
        'is_active',
        'effective_from',
        'effective_until',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'effective_from' => 'date',
        'effective_until' => 'date',
        'start_window_before_minutes' => 'integer',
        'start_window_after_minutes' => 'integer',
        'present_grace_minutes' => 'integer',
        'late_grace_minutes' => 'integer',
        'total_session_minutes' => 'integer',
    ];

    public function sessionType(): BelongsTo
    {
        return $this->belongsTo(AttendanceSessionType::class);
    }

    public function classes(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(SchoolClass::class, 'attendance_schedule_classes', 'attendance_schedule_id', 'class_id')
                    ->using(AttendanceScheduleClass::class)
                    ->withTimestamps();
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(AttendanceSession::class);
    }
}