<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gym_stats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gym_id')->constrained('users')->onDelete('cascade');
            $table->date('month');
            $table->integer('total_subscriptions');
            $table->integer('canceled_subscriptions');
            $table->integer('net_subscriptions');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gym_stats');
    }
};