<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class Student extends Model
{
    use HasFactory, HasUlids, SoftDeletes;

    protected $fillable = [
        'student_number',
        'first_name',
        'middle_name',
        'last_name',
        'gender',
        'date_of_birth',
        'phone',
        'guardian_name',
        'guardian_phone',
        'enrollment_status',
        'joined_at',
        'left_at',
        'notes',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'joined_at' => 'date',
        'left_at' => 'date',
    ];

    // Relationships

    public function classHistory(): HasMany
    {
        return $this->hasMany(StudentClassHistory::class);
    }

    /**
     * Get the student's currently active class assignment.
     */
    public function currentClass(): HasOne
    {
        return $this->hasOne(StudentClassHistory::class)
                    ->where('is_current', true);
    }

    public function attendanceRecords(): HasMany
    {
        return $this->hasMany(AttendanceRecord::class);
    }

    // Business Logic Actions

    /**
     * Safely assigns a student to a new class, preserving historical data
     * by closing the previous assignment rather than modifying it.
     */
    public function assignToClass(string $newClassId, Carbon $startDate): void
    {
        DB::transaction(function () use ($newClassId, $startDate) {
            // 1. Close current assignment if it exists
            $current = $this->currentClass()->first();
            
            if ($current) {
                // If they are already in this class, do nothing
                if ($current->class_id === $newClassId) {
                    return;
                }

                $current->update([
                    'is_current' => false,
                    'ended_at' => $startDate->copy()->subDay(), // Ends the day before the new one starts
                ]);
            }

            // 2. Create the new historical assignment
            $this->classHistory()->create([
                'class_id' => $newClassId,
                'started_at' => $startDate,
                'is_current' => true,
                'ended_at' => null,
            ]);
        });
    }

    /**
     * Full name accessor
     */
    public function getFullNameAttribute(): string
    {
        return collect([$this->first_name, $this->middle_name, $this->last_name])
            ->filter()
            ->join(' ');
    }
}