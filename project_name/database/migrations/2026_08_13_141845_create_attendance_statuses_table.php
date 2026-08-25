<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_statuses', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('code')->unique(); // 'present', 'late', 'permission', 'absent'
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('default_weight')->default(0); // 1 or 0
            $table->boolean('default_counts_as_attendance')->default(false);
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_statuses');
    }
};