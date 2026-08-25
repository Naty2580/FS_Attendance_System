<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_class_history', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignUlid('class_id')->constrained('classes')->restrictOnDelete();
            $table->date('started_at');
            $table->date('ended_at')->nullable();
            $table->boolean('is_current')->default(true);
            $table->timestamps();

            // Explicitly requested indexes for performance/query paths
            $table->index(['student_id', 'started_at']);
            $table->index(['student_id', 'is_current']);
            $table->index(['class_id', 'started_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_class_history');
    }
};