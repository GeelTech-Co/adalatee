<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomWorkoutPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'image_url',
        'days_count',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function days()
    {
        return $this->hasMany(CustomWorkoutPlanDay::class, 'plan_id');
    }

    public function workoutSessions()
    {
        return $this->hasMany(WorkoutSession::class, 'custom_plan_id');
    }
}