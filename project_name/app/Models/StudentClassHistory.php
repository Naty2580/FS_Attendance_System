<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentClassHistory extends Model
{
    use HasUlids;

    protected $table = 'student_class_history';

    protected $fillable = [
        'student_id',
        'class_id',
        'started_at',
        'ended_at',
        'is_current',
    ];

    protected $casts = [
        'started_at' => 'date',
        'ended_at' => 'date',
        'is_current' => 'boolean',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }
}