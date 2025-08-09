<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExerciseLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'exercise_id',
        'round_number',
        'reps',
        'weight',
    ];

    protected $casts = [
        'weight' => 'decimal:2',
    ];

    // Relationships
    public function session()
    {
        return $this->belongsTo(WorkoutSession::class, 'session_id');
    }

    public function exercise()
    {
        return $this->belongsTo(Exercise::class);
    }
}