<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\Concerns\HasUlids;

class AttendanceScheduleClass extends Pivot
{
    use HasUlids;

    // Explicitly define the table name
    protected $table = 'attendance_schedule_classes';

    // Tell Laravel that the primary key is a string (ULID) and auto-increment is false
    public $incrementing = false;
    protected $keyType = 'string';
}