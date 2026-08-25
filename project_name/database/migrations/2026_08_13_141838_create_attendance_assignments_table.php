<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_assignments', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUlid('class_id')->constrained('classes')->cascadeOnDelete();
            $table->date('starts_at');
            $table->date('ends_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Requested indexes for querying active assignments quickly
            $table->index(['user_id', 'is_active']);
            $table->index(['class_id', 'is_active']);
            $table->index(['user_id', 'class_id', 'starts_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_assignments');
    }
};