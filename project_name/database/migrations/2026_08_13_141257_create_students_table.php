<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('student_number')->unique();
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name');
            $table->string('gender', 20)->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('phone')->nullable();
            $table->string('guardian_name')->nullable();
            $table->string('guardian_phone')->nullable();
            $table->string('enrollment_status')->default('active'); // active, inactive
            $table->date('joined_at')->nullable();
            $table->date('left_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes(); // Never physically delete historically active students
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};