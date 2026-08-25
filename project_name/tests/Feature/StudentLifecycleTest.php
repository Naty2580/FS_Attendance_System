<?php

use App\Models\Student;
use App\Models\ClassLevel;
use App\Models\SchoolClass;
use Carbon\Carbon;
use Illuminate\Support\Str;

test('a student can be assigned to a class and historical records are preserved', function () {
    // 1. Setup Data
    $level = ClassLevel::create([
        'id' => (string) Str::ulid(),
        'name' => 'Level 1',
        'code' => 'L1',
    ]);

    $classA = SchoolClass::create([
        'id' => (string) Str::ulid(),
        'class_level_id' => $level->id,
        'name' => 'Class 1A',
        'code' => '1A',
    ]);

    $classB = SchoolClass::create([
        'id' => (string) Str::ulid(),
        'class_level_id' => $level->id,
        'name' => 'Class 1B',
        'code' => '1B',
    ]);

    // Manually create a student for the test
    $student = Student::create([
        'id' => (string) Str::ulid(),
        'student_number' => 'TEST-001',
        'first_name' => 'John',
        'last_name' => 'Doe',
        'enrollment_status' => 'active',
    ]);

    // 2. Action: Assign to Class A on January 1st
    $januaryFirst = Carbon::parse('2026-01-01');
    $student->assignToClass($classA->id, $januaryFirst);

    // Assert: Check the assignment is current
    expect($student->currentClass->class_id)->toBe($classA->id)
        ->and($student->classHistory()->count())->toBe(1);

    // 3. Action: Promote to Class B on June 1st
    $juneFirst = Carbon::parse('2026-06-01');
    $student->assignToClass($classB->id, $juneFirst);

    // 4. Assertions (The Business Logic Check)
    
    // Refresh student from DB
    $student->refresh();

    // Check they only have ONE current class
    expect($student->currentClass->class_id)->toBe($classB->id);

    // Check that history count is now 2
    expect($student->classHistory()->count())->toBe(2);

    // Check the historical record for Class A
    $historyA = $student->classHistory()->where('class_id', $classA->id)->first();
    expect($historyA->is_current)->toBeFalse()
        // It should end the day BEFORE the new class started
        ->and($historyA->ended_at->format('Y-m-d'))->toBe('2026-05-31');

    // Check the active record for Class B
    $historyB = $student->classHistory()->where('class_id', $classB->id)->first();
    expect($historyB->is_current)->toBeTrue()
        ->and($historyB->ended_at)->toBeNull();
});