<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_schedule_classes', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('attendance_schedule_id')->constrained('attendance_schedules')->cascadeOnDelete();
            $table->foreignUlid('class_id')->constrained('classes')->cascadeOnDelete();
            $table->timestamps();
            
            $table->unique(['attendance_schedule_id', 'class_id'], 'schedule_class_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_schedule_classes');
    }
};