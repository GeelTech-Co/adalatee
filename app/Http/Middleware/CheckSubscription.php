<?php

namespace App\Http\Middleware;

use App\Models\Subscription;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSubscription
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Check if user is authenticated
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        // Admins and Gym accounts don't need a subscription
        if (in_array($user->role, ['admin', 'gym'])) {
            return $next($request);
        }

        // Check for active subscription
        $subscription = Subscription::where('user_id', $user->id)
            ->where('status', 'active')
            ->where('end_date', '>=', now())
            ->first();

        if (!$subscription) {
            return response()->json(['message' => 'Active subscription required'], 403);
        }

        return $next($request);
    }
}