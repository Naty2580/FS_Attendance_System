<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
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
        Schema::dropIfExists('attendance_record_changes');
    }
};