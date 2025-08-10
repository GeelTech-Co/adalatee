<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ExerciseController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WorkoutSessionController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/verify-email', [AuthController::class, 'verifyEmail']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);
Route::post('/resend-verification', [AuthController::class, 'resendVerification']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    // User Profile Management APIs
    Route::patch('/user/update', [UserController::class, 'update']);
    Route::patch('/user/change-password', [UserController::class, 'changePassword']);
    Route::post('/user/reset-data', [UserController::class, 'resetData']);
    Route::patch('/settings/language', [UserController::class, 'updateLanguage']);

    // Exercise Management APIs
    Route::get('/exercises', [ExerciseController::class, 'index']);
    Route::get('/exercises/{id}', [ExerciseController::class, 'show']);
    Route::middleware(['check.role:gym,admin', 'check.subscription'])->group(function () {
        Route::post('/exercises', [ExerciseController::class, 'store']);
        Route::patch('/exercises/{id}', [ExerciseController::class, 'update']);
        Route::delete('/exercises/{id}', [ExerciseController::class, 'destroy']);
    });

    // Workout Session Management APIs
    Route::get('/workout-sessions', [WorkoutSessionController::class, 'index']);
    Route::get('/workout-sessions/{id}', [WorkoutSessionController::class, 'show']);
    Route::post('/workout-sessions', [WorkoutSessionController::class, 'store']);
    Route::patch('/workout-sessions/{id}', [WorkoutSessionController::class, 'update']);
    Route::post('/workout-sessions/{id}/complete', [WorkoutSessionController::class, 'complete']);
    Route::post('/workout-sessions/{id}/skip', [WorkoutSessionController::class, 'skip']);
    Route::delete('/workout-sessions/{id}', [WorkoutSessionController::class, 'destroy']);
    Route::get('/workout-sessions/exercise/{exercise_id}/previous', [WorkoutSessionController::class, 'previousSessions']);
});