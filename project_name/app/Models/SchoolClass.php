<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SchoolClass extends Model
{
    use HasUlids;

    // Explicitly define table to avoid 'school_classes' convention
    protected $table = 'classes'; 

    protected $fillable = [
        'class_level_id',
        'name',
        'code',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function classLevel(): BelongsTo
    {
        return $this->belongsTo(ClassLevel::class);
    }

    public function studentHistory(): HasMany
    {
        return $this->hasMany(StudentClassHistory::class, 'class_id');
    }

    public function attendanceAssignments(): HasMany
    {
        return $this->hasMany(AttendanceAssignment::class, 'class_id');
    }
    
    public function attendanceSessions(): HasMany
    {
        return $this->hasMany(AttendanceSession::class, 'class_id');
    }

    
    public function schedules(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(AttendanceSchedule::class, 'attendance_schedule_classes', 'class_id', 'attendance_schedule_id')
                    ->using(AttendanceScheduleClass::class)
                    ->withTimestamps();
    }
}