<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\ClassLevel;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\AttendanceSessionType;
use App\Models\AttendanceSchedule;
use App\Models\AttendanceSession;
use App\Models\AttendanceSessionClass;
use App\Models\AttendanceAssignment;
use Carbon\Carbon;
use Illuminate\Support\Str;

class TestEnvironmentSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Grab your existing Admin User and Session Types
        $admin = User::where('email', 'admin@church.com')->first();
        $courseType = AttendanceSessionType::where('code', 'course')->first();
        
        if (!$admin || !$courseType) {
            $this->command->error('Please run the Roles/Permissions & Config seeders and create your Admin user first.');
            return;
        }

        // 2. Create Class Levels
        $level1 = ClassLevel::create(['id' => (string) Str::ulid(), 'name' => 'Level 1', 'code' => 'L1', 'sort_order' => 1]);
        $level2 = ClassLevel::create(['id' => (string) Str::ulid(), 'name' => 'Level 2', 'code' => 'L2', 'sort_order' => 2]);

        // 3. Create Classes
        $class1A = SchoolClass::create(['id' => (string) Str::ulid(), 'class_level_id' => $level1->id, 'name' => 'Class 1A', 'code' => '1A']);
        $class1B = SchoolClass::create(['id' => (string) Str::ulid(), 'class_level_id' => $level1->id, 'name' => 'Class 1B', 'code' => '1B']);
        $class2A = SchoolClass::create(['id' => (string) Str::ulid(), 'class_level_id' => $level2->id, 'name' => 'Class 2A', 'code' => '2A']);

        // 4. Create Students and Safely Assign Them (Testing our Business Logic!)
        $this->command->info('Creating and assigning students...');
        $students1A = Student::factory()->count(10)->create();
        foreach ($students1A as $student) {
            $student->assignToClass($class1A->id, Carbon::now()->subMonths(6));
        }

        $students1B = Student::factory()->count(8)->create();
        foreach ($students1B as $student) {
            $student->assignToClass($class1B->id, Carbon::now()->subMonths(6));
        }

        // 5. Assign the Admin User to be the "Attendance Member" for Class 1A for this entire year
        AttendanceAssignment::create([
            'id' => (string) Str::ulid(),
            'user_id' => $admin->id,
            'class_id' => $class1A->id,
            'starts_at' => Carbon::now()->subDays(10),
            'ends_at' => Carbon::now()->addMonths(11),
            'is_active' => true,
        ]);

        // 6. Create a Schedule rule
        $schedule = AttendanceSchedule::create([
            'id' => (string) Str::ulid(),
            'session_type_id' => $courseType->id,
            'name' => 'Saturday Morning Course',
            'day_of_week' => 'Saturday',
            'start_time' => '08:00:00',
            'present_until' => '08:15:00',
            'close_at' => '08:30:00',
            'effective_from' => Carbon::now()->subMonths(1),
        ]);

        // 7. Create an actual Session FOR TODAY (So we can test the Mobile App instantly)
        $today = Carbon::today();
        
        $session = AttendanceSession::create([
            'id' => (string) Str::ulid(),
            'session_type_id' => $courseType->id,
            'attendance_schedule_id' => $schedule->id,
            'session_date' => $today,
            
            // To test the "Present" window right now, we will manipulate the times to wrap around the current exact time.
            'starts_at' => Carbon::now()->subMinutes(10)->format('H:i:s'), // Started 10 mins ago
            'present_until' => Carbon::now()->addMinutes(30)->format('H:i:s'), // Present for 30 more mins
            'closes_at' => Carbon::now()->addHours(1)->format('H:i:s'), // Closes in 1 hour
            'status' => 'open',
        ]);

        // 8. Attach Class 1A to Today's Session
        AttendanceSessionClass::create([
            'id' => (string) Str::ulid(),
            'attendance_session_id' => $session->id,
            'class_id' => $class1A->id,
            'status' => 'pending',
        ]);

        $this->command->info('Test Environment Successfully Seeded!');
    }
}