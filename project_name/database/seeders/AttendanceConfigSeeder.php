<?php

namespace Database\Seeders;

use App\Models\AttendanceStatus;
use App\Models\AttendanceSessionType;
use Illuminate\Database\Seeder;

class AttendanceConfigSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Initial Statuses
        $statuses = [
            ['code' => 'present', 'name' => 'Present', 'default_weight' => 1, 'default_counts_as_attendance' => true, 'sort_order' => 1],
            ['code' => 'late', 'name' => 'Late', 'default_weight' => 1, 'default_counts_as_attendance' => true, 'sort_order' => 2],
            ['code' => 'permission', 'name' => 'Permission', 'default_weight' => 0, 'default_counts_as_attendance' => false, 'sort_order' => 3],
            ['code' => 'absent', 'name' => 'Absent', 'default_weight' => 0, 'default_counts_as_attendance' => false, 'sort_order' => 4],
        ];

        foreach ($statuses as $status) {
            AttendanceStatus::firstOrCreate(['code' => $status['code']], $status);
        }

        // 2. Initial Session Types
        $types = [
            ['code' => 'course', 'name' => 'Course'],
            ['code' => 'hymn', 'name' => 'Hymn'],
        ];

        foreach ($types as $type) {
            AttendanceSessionType::firstOrCreate(['code' => $type['code']], $type);
        }
    }
}