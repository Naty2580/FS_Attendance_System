<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_schedules', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('session_type_id')->constrained('attendance_session_types')->restrictOnDelete();
            $table->string('name');
            $table->string('day_of_week', 20); // e.g., 'Saturday', 'Sunday'
            $table->time('start_time');
            $table->time('present_until');
            $table->time('close_at');
            $table->boolean('is_active')->default(true);
            $table->date('effective_from');
            $table->date('effective_until')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_schedules');
    }
};