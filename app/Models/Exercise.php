<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Exercise extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'muscle_group',
        'secondary_muscles',
        'description',
        'instructions',
        'precautions',
        'image_url',
        'animation_url',
    ];

    protected $casts = [
        'type' => 'string',
    ];

    // Relationships
    public function predefinedWorkoutPlanExercises()
    {
        return $this->hasMany(PredefinedWorkoutPlanExercise::class);
    }

    public function customWorkoutPlanExercises()
    {
        return $this->hasMany(CustomWorkoutPlanExercise::class);
    }

    public function exerciseLogs()
    {
        return $this->hasMany(ExerciseLog::class);
    }
}