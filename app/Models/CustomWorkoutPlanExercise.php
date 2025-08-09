<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomWorkoutPlanExercise extends Model
{
    use HasFactory;

    protected $fillable = [
        'plan_day_id',
        'exercise_id',
        'order',
    ];

    // Relationships
    public function planDay()
    {
        return $this->belongsTo(CustomWorkoutPlanDay::class, 'plan_day_id');
    }

    public function exercise()
    {
        return $this->belongsTo(Exercise::class);
    }
}