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
}