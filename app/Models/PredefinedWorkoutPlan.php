<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PredefinedWorkoutPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'image_url',
        'days_count',
    ];

    // Relationships
    public function days()
    {
        return $this->hasMany(PredefinedWorkoutPlanDay::class, 'plan_id');
    }

    public function workoutSessions()
    {
        return $this->hasMany(WorkoutSession::class, 'plan_id');
    }
}