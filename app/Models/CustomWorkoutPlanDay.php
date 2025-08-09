<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomWorkoutPlanDay extends Model
{
    use HasFactory;

    protected $fillable = [
        'plan_id',
        'day_name',
    ];

    // Relationships
    public function plan()
    {
        return $this->belongsTo(CustomWorkoutPlan::class, 'plan_id');
    }

    public function exercises()
    {
        return $this->hasMany(CustomWorkoutPlanExercise::class, 'plan_day_id');
    }

    public function workoutSessions()
    {
        return $this->hasMany(WorkoutSession::class, 'custom_day_id');
    }
}