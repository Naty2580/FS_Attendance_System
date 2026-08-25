<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

class AttendanceStatus extends Model
{
    use HasUlids;

    protected $fillable = [
        'code',
        'name',
        'description',
        'default_weight',
        'default_counts_as_attendance',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'default_weight' => 'integer',
        'default_counts_as_attendance' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];
}