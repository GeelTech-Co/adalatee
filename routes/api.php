<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
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
});