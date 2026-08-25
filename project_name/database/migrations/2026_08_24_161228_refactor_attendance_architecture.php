<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Drop the old tables in strict order (Children first to prevent foreign key errors)
        Schema::dropIfExists('attendance_record_changes');
        Schema::dropIfExists('attendance_records');
        Schema::dropIfExists('attendance_session_classes'); // The pivot we are deleting permanently
        Schema::dropIfExists('attendance_sessions');

        // 2. Refactor the Schedule Blueprint (Replacing static times with dynamic windows)
        Schema::table('attendance_schedules', function (Blueprint $table) {
            $table->dropColumn(['start_time', 'present_until', 'close_at']);
            
            $table->time('expected_start_time')->after('day_of_week')->nullable();
            $table->integer('start_window_before_minutes')->default(30)->after('expected_start_time')->comment('How early they can click Start');
            $table->integer('start_window_after_minutes')->default(60)->after('start_window_before_minutes')->comment('How late they can click Start');
            $table->integer('present_grace_minutes')->default(15)->after('start_window_after_minutes')->comment('Duration of Present window');
            $table->integer('late_grace_minutes')->default(15)->after('present_grace_minutes')->comment('Duration of Late window');
            $table->integer('total_session_minutes')->default(60)->after('late_grace_minutes')->comment('When the session automatically closes');
        });

        // 3. Create the New, Simplified Session Table
        Schema::create('attendance_sessions', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('attendance_schedule_id')->constrained('attendance_schedules')->cascadeOnDelete();
            $table->foreignUlid('class_id')->constrained('classes')->cascadeOnDelete();
            $table->date('session_date');
            $table->timestamp('started_at'); // The EXACT time the teacher clicked the button
            $table->string('status')->default('active'); // active, closed
            $table->timestamps();
            
            // A class can only have one session per schedule per day
            $table->unique(['attendance_schedule_id', 'class_id', 'session_date'], 'session_schedule_class_date_unique');
        });

        // 4. Recreate the Records Table (Now pointing directly to the session!)
        Schema::create('attendance_records', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('attendance_session_id')->constrained('attendance_sessions')->cascadeOnDelete();
            $table->foreignUlid('student_id')->constrained('students')->restrictOnDelete();
            $table->foreignUlid('attendance_status_id')->constrained('attendance_statuses')->restrictOnDelete();
            $table->foreignUlid('recorded_by')->constrained('users')->restrictOnDelete();
            $table->timestamp('recorded_at');
            $table->foreignUlid('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('note')->nullable();
            $table->timestamps();

            // A student can only have one record per session
            $table->unique(['attendance_session_id', 'student_id'], 'record_session_student_unique');
        });

        // 5. Recreate the Audit Trail Table
        Schema::create('attendance_record_changes', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('attendance_record_id')->constrained('attendance_records')->cascadeOnDelete();
            $table->foreignUlid('old_status_id')->nullable()->constrained('attendance_statuses')->nullOnDelete();
            $table->foreignUlid('new_status_id')->constrained('attendance_statuses')->restrictOnDelete();
            $table->foreignUlid('changed_by')->constrained('users')->restrictOnDelete();
            $table->text('reason')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        // For a safe rollback during dev
        Schema::dropIfExists('attendance_record_changes');
        Schema::dropIfExists('attendance_records');
        Schema::dropIfExists('attendance_sessions');
    }
};