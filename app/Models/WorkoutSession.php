<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkoutSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'plan_id',
        'day_id',
        'custom_plan_id',
        'custom_day_id',
        'start_time',
        'end_time',
        'status',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'status' => 'string',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function predefinedPlan()
    {
        return $this->belongsTo(PredefinedWorkoutPlan::class, 'plan_id');
    }

    public function predefinedDay()
    {
        return $this->belongsTo(PredefinedWorkoutPlanDay::class, 'day_id');
    }

    public function customPlan()
    {
        return $this->belongsTo(CustomWorkoutPlan::class, 'custom_plan_id');
    }

    public function customDay()
    {
        return $this->belongsTo(CustomWorkoutPlanDay::class, 'custom_day_id');
    }

    public function exerciseLogs()
    {
        return $this->hasMany(ExerciseLog::class, 'session_id');
    }

    public function hasIncompleteMainExercises()
    {
        // Get exercises from the plan or custom plan
        $expectedExercises = collect([]);
        if ($this->plan_id && $this->day_id) {
            $expectedExercises = $this->predefinedDay->exercises()
                ->where('type', 'main')
                ->pluck('id');
        } elseif ($this->custom_plan_id && $this->custom_day_id) {
            $expectedExercises = $this->customDay->exercises()
                ->where('type', 'main')
                ->pluck('id');
        }

        // Get completed main exercises from logs
        $completedExercises = $this->exerciseLogs()
            ->whereHas('exercise', function ($query) {
                $query->where('type', 'main');
            })
            ->pluck('exercise_id')
            ->unique();

        // Check if any expected main exercises are missing from logs
        return $expectedExercises->isNotEmpty() && $expectedExercises->diff($completedExercises)->isNotEmpty();
    }
}