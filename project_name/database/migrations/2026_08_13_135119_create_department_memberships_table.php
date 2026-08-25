<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('department_memberships', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('department_id')->constrained('departments')->cascadeOnDelete();
            $table->foreignUlid('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('joined_at')->useCurrent();
            $table->timestamp('left_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['department_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('department_memberships');
    }
};