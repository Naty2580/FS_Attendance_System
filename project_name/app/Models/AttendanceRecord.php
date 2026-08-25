<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AttendanceRecord extends Model
{
    use HasUlids;

    protected $fillable = [
        'attendance_session_id', // Changed from attendance_session_class_id
        'student_id',
        'attendance_status_id',
        'recorded_by',
        'recorded_at',
        'updated_by',
        'note',
    ];

    protected $casts = [
        'recorded_at' => 'datetime',
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(AttendanceSession::class, 'attendance_session_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(AttendanceStatus::class, 'attendance_status_id');
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function changes(): HasMany
    {
        return $this->hasMany(AttendanceRecordChange::class);
    }
}