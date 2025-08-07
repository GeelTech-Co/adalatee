<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('predefined_workout_plan_days', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plan_id')->constrained('predefined_workout_plans')->onDelete('cascade');
            $table->string('day_name');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('predefined_workout_plan_days');
    }
};