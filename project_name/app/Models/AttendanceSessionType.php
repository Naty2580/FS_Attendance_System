<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AttendanceSessionType extends Model
{
    use HasUlids;

    protected $fillable = [
        'name',
        'code',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function schedules(): HasMany
    {
        return $this->hasMany(AttendanceSchedule::class, 'session_type_id');
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(AttendanceSession::class, 'session_type_id');
    }
}