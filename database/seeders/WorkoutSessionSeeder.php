<?php
namespace Database\Seeders;

use App\Models\WorkoutSession;
use App\Models\ExerciseLog;
use Illuminate\Database\Seeder;

class WorkoutSessionSeeder extends Seeder
{
    public function run()
    {
        $session = WorkoutSession::create([
            'user_id' => 1, // Admin user
            'plan_id' => null,
            'day_id' => null,
            'custom_plan_id' => null,
            'custom_day_id' => null,
            'start_time' => now(),
            'status' => 'in_progress',
        ]);

        ExerciseLog::create([
            'session_id' => $session->id,
            'exercise_id' => 1, // Cable Seated Chest Press
            'round_number' => 1,
            'reps' => 12,
            'weight' => 50,
        ]);

        ExerciseLog::create([
            'session_id' => $session->id,
            'exercise_id' => 2, // Leg Extension
            'round_number' => 1,
            'reps' => 10,
            'weight' => 40,
        ]);
    }
}