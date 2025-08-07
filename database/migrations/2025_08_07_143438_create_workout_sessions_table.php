<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workout_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('plan_id')->nullable()->constrained('predefined_workout_plans')->onDelete('set null');
            $table->foreignId('day_id')->nullable()->constrained('predefined_workout_plan_days')->onDelete('set null');
            $table->foreignId('custom_plan_id')->nullable()->constrained('custom_workout_plans')->onDelete('set null');
            $table->foreignId('custom_day_id')->nullable()->constrained('custom_workout_plan_days')->onDelete('set null');
            $table->dateTime('start_time');
            $table->dateTime('end_time')->nullable();
            $table->enum('status', ['completed', 'in_progress', 'skipped'])->default('in_progress');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workout_sessions');
    }
};