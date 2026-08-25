<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_records', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('attendance_session_class_id')->constrained('attendance_session_classes')->restrictOnDelete();
            $table->foreignUlid('student_id')->constrained('students')->restrictOnDelete();
            $table->foreignUlid('attendance_status_id')->constrained('attendance_statuses')->restrictOnDelete();
            $table->foreignUlid('recorded_by')->constrained('users')->restrictOnDelete();
            $table->timestamp('recorded_at');
            $table->foreignUlid('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('note')->nullable();
            $table->timestamps();

            // Critical unique constraint: exactly one record per student per class-session
            $table->unique(['attendance_session_class_id', 'student_id'], 'record_session_class_student_unique');

            // Critical Indexes as requested
            $table->index(['student_id', 'attendance_session_class_id'], 'idx_student_session_class');
            $table->index(['attendance_session_class_id', 'attendance_status_id'], 'idx_session_class_status');
            $table->index(['recorded_by', 'recorded_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_records');
    }
};