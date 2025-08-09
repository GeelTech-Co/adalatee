<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\WorkoutSession;
use App\Models\CustomWorkoutPlan;
use App\Models\ExerciseLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function update(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
            ]);

            $user = $request->user();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.user_not_found'),
                ], 404);
            }

            $user->update([
                'name' => $validated['name'],
            ]);

            return response()->json([
                'success' => true,
                'message' => __('messages.name_updated'),
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'role' => $user->role,
                    ],
                ],
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => __('messages.validation_failed'),
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Failed to update user name: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => __('messages.failed_to_update', ['error' => $e->getMessage()]),
            ], 500);
        }
    }

    public function changePassword(Request $request)
    {
        try {
            $validated = $request->validate([
                'old_password' => 'required|string',
                'password' => 'required|confirmed|regex:/^(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}$/',
            ]);

            $user = $request->user();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.user_not_found'),
                ], 404);
            }

            if (!Hash::check($validated['old_password'], $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.invalid_old_password'),
                ], 401);
            }

            $user->update([
                'password' => Hash::make($validated['password']),
            ]);

            return response()->json([
                'success' => true,
                'message' => __('messages.password_changed'),
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => __('messages.validation_failed'),
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Failed to change password: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => __('messages.failed_to_update', ['error' => $e->getMessage()]),
            ], 500);
        }
    }

    public function resetData(Request $request)
    {
        try {
            $user = $request->user();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.user_not_found'),
                ], 404);
            }

            DB::transaction(function () use ($user) {
                // Delete related workout sessions
                WorkoutSession::where('user_id', $user->id)->delete();
                // Delete related custom workout plans
                CustomWorkoutPlan::where('user_id', $user->id)->delete();
                // Delete related exercise logs
                ExerciseLog::whereIn('session_id', function ($query) use ($user) {
                    $query->select('id')
                          ->from('workout_sessions')
                          ->where('user_id', $user->id);
                })->delete();
            });

            return response()->json([
                'success' => true,
                'message' => __('messages.data_reset'),
            ], 200);
        } catch (\Exception $e) {
            Log::error('Failed to reset user data: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => __('messages.failed_to_update', ['error' => $e->getMessage()]),
            ], 500);
        }
    }

    public function updateLanguage(Request $request)
    {
        try {
            $validated = $request->validate([
                'language' => 'required|string|in:en,ar',
            ]);

            $user = $request->user();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.user_not_found'),
                ], 404);
            }

            $user->settings()->updateOrCreate(
                ['user_id' => $user->id],
                ['language' => $validated['language']]
            );

            return response()->json([
                'success' => true,
                'message' => __('messages.language_updated'),
                'data' => [
                    'language' => $validated['language'],
                ],
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => __('messages.validation_failed'),
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Failed to update language: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => __('messages.failed_to_update', ['error' => $e->getMessage()]),
            ], 500);
        }
    }
}