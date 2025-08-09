<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $locale = 'en'; // Default language

        // Check if user is authenticated and has language setting
        if ($user && $user->settings && in_array($user->settings->language, ['en', 'ar'])) {
            $locale = $user->settings->language;
        } else {
            // Fallback to Accept-Language header
            $acceptLanguage = $request->header('Accept-Language', 'en');
            $locale = str_starts_with($acceptLanguage, 'ar') ? 'ar' : 'en';
        }

        app()->setLocale($locale);
        return $next($request);
    }
}