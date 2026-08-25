<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_sessions', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('session_type_id')->constrained('attendance_session_types')->restrictOnDelete();
            $table->foreignUlid('attendance_schedule_id')->nullable()->constrained('attendance_schedules')->nullOnDelete();
            $table->date('session_date');
            $table->time('starts_at');
            $table->time('present_until');
            $table->time('closes_at');
            $table->string('status')->default('scheduled'); // scheduled, open, closed
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_sessions');
    }
};