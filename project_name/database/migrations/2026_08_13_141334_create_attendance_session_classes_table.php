<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_session_classes', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('attendance_session_id')->constrained('attendance_sessions')->cascadeOnDelete();
            $table->foreignUlid('class_id')->constrained('classes')->restrictOnDelete();
            $table->string('status')->default('pending'); // pending, ongoing, completed
            $table->timestamps();

            // Crucial composite unique constraint: A class can only be assigned to a session once
            $table->unique(['attendance_session_id', 'class_id'], 'session_class_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_session_classes');
    }
};