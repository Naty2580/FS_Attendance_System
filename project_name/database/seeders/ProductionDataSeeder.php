<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Role;
use App\Models\ClassLevel;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\AttendanceSessionType;
use App\Models\AttendanceSchedule;
use App\Models\AttendanceAssignment;

class ProductionDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Starting Production Data Seeder...');

        // 1. Get Base Configs
        $courseType = AttendanceSessionType::where('code', 'course')->first();
        $attendanceRole = Role::where('name', 'Attendance Member')->first();

        // 2. Create the Specific Users
        $users = [
            'test' => User::firstOrCreate(['email' => 'test@gmail.com'], [
                'id' => (string) Str::ulid(), 'name' => 'Test User', 'password' => Hash::make('test@fs'), 'status' => 'active'
            ]),
            'henok' => User::firstOrCreate(['email' => 'henok@gmail.com'], [
                'id' => (string) Str::ulid(), 'name' => 'Henok', 'password' => Hash::make('henok@fs'), 'status' => 'active'
            ]),
            'meski' => User::firstOrCreate(['email' => 'meski@gmail.com'], [
                'id' => (string) Str::ulid(), 'name' => 'Meski', 'password' => Hash::make('meski@fs'), 'status' => 'active'
            ]),
            'gech' => User::firstOrCreate(['email' => 'gech@gmail.com'], [
                'id' => (string) Str::ulid(), 'name' => 'Gech', 'password' => Hash::make('gech@fs'), 'status' => 'active'
            ]),
            
            // 3 Extra Random Teachers
            'teacher1' => User::firstOrCreate(['email' => 'teacher1@gmail.com'], [
                'id' => (string) Str::ulid(), 'name' => 'Abebe Teacher', 'password' => Hash::make('password'), 'status' => 'active'
            ]),
            'teacher2' => User::firstOrCreate(['email' => 'teacher2@gmail.com'], [
                'id' => (string) Str::ulid(), 'name' => 'Selam Teacher', 'password' => Hash::make('password'), 'status' => 'active'
            ]),
            'teacher3' => User::firstOrCreate(['email' => 'teacher3@gmail.com'], [
                'id' => (string) Str::ulid(), 'name' => 'Dawit Teacher', 'password' => Hash::make('password'), 'status' => 'active'
            ]),
        ];

        // Assign Roles
        foreach ($users as $user) {
            if (!$user->hasRole('Attendance Member')) {
                $user->assignRole($attendanceRole);
            }
        }

        $this->command->info('Users Created. Building Classes and Students (this might take a few seconds)...');

        $classesByLevel = [];
        
        // 3. Create Levels, Classes, Students, and Teacher Assignments
        for ($i = 1; $i <= 10; $i++) {
            $level = ClassLevel::firstOrCreate(['code' => "L{$i}"], [
                'id' => (string) Str::ulid(),
                'name' => "Level {$i}",
                'sort_order' => $i,
            ]);

            // Create Sections A and B for each level
            $sections = ['A', 'B'];
            foreach ($sections as $sec) {
                $schoolClass = SchoolClass::firstOrCreate(['code' => "{$i}{$sec}"], [
                    'id' => (string) Str::ulid(),
                    'class_level_id' => $level->id,
                    'name' => "Class {$i}{$sec}",
                ]);

                $classesByLevel[$i][] = $schoolClass->id;

                // Create 10 students for this section and assign them
                $students = Student::factory()->count(10)->create();
                foreach ($students as $student) {
                    $student->assignToClass($schoolClass->id, Carbon::now()->subMonths(3));
                }

                // Assign the correct Teacher based on your rules
                $teacher = null;
                if ($i == 1) $teacher = $users['test'];
                if ($i >= 2 && $i <= 5) $teacher = $users['henok'];
                if ($i >= 6 && $i <= 7) $teacher = $users['meski'];
                if ($i >= 8 && $i <= 10) $teacher = $users['gech'];

                AttendanceAssignment::create([
                    'id' => (string) Str::ulid(),
                    'user_id' => $teacher->id,
                    'class_id' => $schoolClass->id,
                    'starts_at' => Carbon::now()->subMonths(1),
                    'is_active' => true,
                ]);
            }
        }

        $this->command->info('Classes and Students seeded. Building Schedules...');

        // Helper to grab class IDs
        $getClasses = function ($startLevel, $endLevel) use ($classesByLevel) {
            $ids = [];
            for ($i = $startLevel; $i <= $endLevel; $i++) {
                $ids = array_merge($ids, $classesByLevel[$i]);
            }
            return $ids;
        };

        // 4. Create the Schedule Blueprints based on your rules
        $schedules = [
            [
                'name' => 'Level 1 Saturday Morning', 'day' => 'monday', 'time' => '05:56:00',
                'classes' => $getClasses(1, 1)
            ],
            // Levels 1-2: Sunday 09:00 AM
            [
                'name' => 'Levels 2 Sunday Morning', 'day' => 'Sunday', 'time' => '09:00:00',
                'classes' => $getClasses(2, 2)
            ],
            // Levels 3-5: Sunday 11:00 AM
            [
                'name' => 'Levels 3-5 Sunday Midday', 'day' => 'Sunday', 'time' => '11:00:00',
                'classes' => $getClasses(3, 5)
            ],
            // Levels 6-7: Saturday 4:00 PM & Sunday 2:00 PM
            [
                'name' => 'Levels 6-7 Saturday Afternoon', 'day' => 'Saturday', 'time' => '16:00:00',
                'classes' => $getClasses(6, 7)
            ],
            [
                'name' => 'Levels 6-7 Sunday Afternoon', 'day' => 'Sunday', 'time' => '14:00:00',
                'classes' => $getClasses(6, 7)
            ],
            // Levels 8-10: Saturday 4:00 PM & Sunday 10:00 AM
            [
                'name' => 'Levels 8-10 Saturday Afternoon', 'day' => 'Saturday', 'time' => '16:00:00',
                'classes' => $getClasses(8, 10)
            ],
            [
                'name' => 'Levels 8-10 Sunday Morning', 'day' => 'Sunday', 'time' => '10:00:00',
                'classes' => $getClasses(8, 10)
            ],
        ];

        foreach ($schedules as $schedData) {
            $schedule = AttendanceSchedule::create([
                'id' => (string) Str::ulid(),
                'session_type_id' => $courseType->id,
                'name' => $schedData['name'],
                'day_of_week' => $schedData['day'],
                'expected_start_time' => $schedData['time'],
                'start_window_before_minutes' => 60,  // Can start up to 1 hr early
                'start_window_after_minutes' => 120,  // Can start up to 2 hrs late
                'present_grace_minutes' => 15,        // 15 mins to mark Present
                'late_grace_minutes' => 15,           // 15 mins to mark Late
                'total_session_minutes' => 120,       // Session auto-closes after 2 hours
                'effective_from' => Carbon::now()->subMonths(1),
            ]);

            // Attach the classes via the pivot table
            $schedule->classes()->attach($schedData['classes']);
        }

        $this->command->info('Production Database Successfully Seeded!');
    }
}

$user = App\Models\User::create([  
     'name' => 'System Admin',    
     'email' => 'admin@church.com', 
      'password' => Hash::make('password123'), 
       'status' => 'active' 
        ]); 
        $user->assignRole('System Administrator');   
