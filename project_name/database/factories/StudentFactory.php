<?php

namespace Database\Factories;

use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class StudentFactory extends Factory
{
    protected $model = Student::class;

    public function definition(): array
    {
        return [
            'id' => (string) Str::ulid(),
            'student_number' => 'STU-' . $this->faker->unique()->numerify('#####'),
            'first_name' => $this->faker->firstName(),
            'middle_name' => $this->faker->optional(0.7)->firstName(),
            'last_name' => $this->faker->lastName(),
            'gender' => $this->faker->randomElement(['Male', 'Female']),
            'date_of_birth' => $this->faker->dateTimeBetween('-18 years', '-5 years')->format('Y-m-d'),
            'phone' => $this->faker->optional(0.8)->phoneNumber(),
            'guardian_name' => $this->faker->name(),
            'guardian_phone' => $this->faker->phoneNumber(),
            'enrollment_status' => 'active',
            'joined_at' => $this->faker->dateTimeBetween('-2 years', '-1 month')->format('Y-m-d'),
        ];
    }
}