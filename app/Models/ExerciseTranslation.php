<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExerciseTranslation extends Model
{
    use HasFactory;

    protected $fillable = [
        'exercise_id',
        'locale',
        'description',
        'instructions',
        'precautions',
    ];

    public function exercise()
    {
        return $this->belongsTo(Exercise::class);
    }
}