<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GymStat extends Model
{
    use HasFactory;

    protected $fillable = [
        'gym_id',
        'month',
        'total_subscriptions',
        'canceled_subscriptions',
        'net_subscriptions',
    ];

    protected $casts = [
        'month' => 'date',
    ];

    // Relationships
    public function gym()
    {
        return $this->belongsTo(User::class, 'gym_id');
    }
}