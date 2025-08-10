<?php
namespace App\Http\Controllers;

use App\Models\WorkoutSession;
use App\Models\ExerciseLog;
use App\Models\PredefinedWorkoutPlanDay;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class WorkoutSessionController extends Controller
{
    public function index(Request $request)
    {
        try {
            $user = $request->user();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.unauthenticated'),
                ], 401);
            }

            $query = WorkoutSession::query()
                ->with(['exerciseLogs.exercise.translations', 'predefinedPlan', 'predefinedDay', 'customPlan', 'customDay'])
                ->where('user_id', $user->id);

            if ($request->has('date')) {
                $query->whereDate('start_time', $request->date);
            }
            if ($request->has('plan_id')) {
                $query->where('plan_id', $request->plan_id);
            }
            if ($request->has('custom_plan_id')) {
                $query->where('custom_plan_id', $request->custom_plan_id);
            }

            $sessions = $query->get()->map(function ($session) {
                $mainExerciseLogs = $session->exerciseLogs->filter(function ($log) {
                    return $log->exercise->type === 'main';
                });
                $averageWeight = $mainExerciseLogs->isNotEmpty()
                    ? $mainExerciseLogs->sum('weight') / $mainExerciseLogs->sum('reps')
                    : null;

                return [
                    'id' => $session->id,
                    'user_id' => $session->user_id,
                    'plan_id' => $session->plan_id,
                    'day_id' => $session->day_id,
                    'custom_plan_id' => $session->custom_plan_id,
                    'custom_day_id' => $session->custom_day_id,
                    'start_time' => $session->start_time,
                    'end_time' => $session->end_time,
                    'status' => $session->status,
                    'exercise_logs' => $session->exerciseLogs->map(function ($log) {
                        return [
                            'exercise_id' => $log->exercise_id,
                            'name' => $log->exercise->name,
                            'type' => $log->exercise->type,
                            'round_number' => $log->round_number,
                            'reps' => $log->reps,
                            'weight' => $log->weight,
                            'description' => $log->exercise->description,
                            'instructions' => $log->exercise->instructions,
                            'precautions' => $log->exercise->precautions,
                        ];
                    }),
                    'average_weight' => $averageWeight,
                ];
            });

            return response()->json([
                'success' => true,
                'message' => __('messages.workout_sessions_retrieved'),
                'data' => $sessions,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Failed to retrieve workout sessions: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => __('messages.failed_to_retrieve', ['error' => $e->getMessage()]),
            ], 500);
        }
    }

    public function show(Request $request, $id)
    {
        try {
            $user = $request->user();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.unauthenticated'),
                ], 401);
            }

            $session = WorkoutSession::with(['exerciseLogs.exercise.translations', 'predefinedPlan', 'predefinedDay', 'customPlan', 'customDay'])
                ->where('user_id', $user->id)
                ->find($id);

            if (!$session) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.workout_session_not_found'),
                ], 404);
            }

            $mainExerciseLogs = $session->exerciseLogs->filter(function ($log) {
                return $log->exercise->type === 'main';
            });
            $averageWeight = $mainExerciseLogs->isNotEmpty()
                ? $mainExerciseLogs->sum('weight') / $mainExerciseLogs->sum('reps')
                : null;

            $data = [
                'id' => $session->id,
                'user_id' => $session->user_id,
                'plan_id' => $session->plan_id,
                'day_id' => $session->day_id,
                'custom_plan_id' => $session->custom_plan_id,
                'custom_day_id' => $session->custom_day_id,
                'start_time' => $session->start_time,
                'end_time' => $session->end_time,
                'status' => $session->status,
                'exercise_logs' => $session->exerciseLogs->map(function ($log) {
                    return [
                        'exercise_id' => $log->exercise_id,
                        'name' => $log->exercise->name,
                        'type' => $log->exercise->type,
                        'round_number' => $log->round_number,
                        'reps' => $log->reps,
                        'weight' => $log->weight,
                        'description' => $log->exercise->description,
                        'instructions' => $log->exercise->instructions,
                        'precautions' => $log->exercise->precautions,
                    ];
                }),
                'average_weight' => $averageWeight,
            ];

            return response()->json([
                'success' => true,
                'message' => __('messages.workout_session_retrieved'),
                'data' => $data,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Failed to retrieve workout session: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => __('messages.failed_to_retrieve', ['error' => $e->getMessage()]),
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $user = $request->user();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.unauthenticated'),
                ], 401);
            }

            $validated = $request->validate([
                'plan_id' => 'nullable|exists:predefined_workout_plans,id',
                'day_id' => 'nullable|exists:predefined_workout_plan_days,id',
                'custom_plan_id' => 'nullable|exists:custom_workout_plans,id',
                'custom_day_id' => 'nullable|exists:custom_workout_plan_days,id',
                'start_time' => 'required|date',
                'exercise_logs' => 'required|array|min:1',
                'exercise_logs.*.exercise_id' => 'required|exists:exercises,id',
                'exercise_logs.*.round_number' => 'required|integer|min:1',
                'exercise_logs.*.reps' => 'required|integer|min:1',
                'exercise_logs.*.weight' => 'nullable|numeric|min:0',
            ]);

            // Ensure either predefined or custom plan is provided, but not both
            if ($validated['plan_id'] && $validated['custom_plan_id']) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.validation_failed'),
                    'errors' => ['plan' => __('messages.validation_failed_plan')],
                ], 422);
            }

            // Validate that day_id belongs to plan_id
            if ($validated['plan_id'] && $validated['day_id']) {
                $day = PredefinedWorkoutPlanDay::where('id', $validated['day_id'])
                    ->where('plan_id', $validated['plan_id'])
                    ->first();
                if (!$day) {
                    return response()->json([
                        'success' => false,
                        'message' => __('messages.validation_failed'),
                        'errors' => ['day_id' => 'The selected day does not belong to the specified plan.'],
                    ], 422);
                }
            }

            $session = WorkoutSession::create([
                'user_id' => $user->id,
                'plan_id' => $validated['plan_id'] ?? null,
                'day_id' => $validated['day_id'] ?? null,
                'custom_plan_id' => $validated['custom_plan_id'] ?? null,
                'custom_day_id' => $validated['custom_day_id'] ?? null,
                'start_time' => $validated['start_time'],
                'status' => 'in_progress',
            ]);

            foreach ($validated['exercise_logs'] as $logData) {
                $session->exerciseLogs()->create([
                    'exercise_id' => $logData['exercise_id'],
                    'round_number' => $logData['round_number'],
                    'reps' => $logData['reps'],
                    'weight' => $logData['weight'] ?? null,
                ]);
            }

            $session->load('exerciseLogs.exercise.translations');

            $mainExerciseLogs = $session->exerciseLogs->filter(function ($log) {
                return $log->exercise->type === 'main';
            });
            $averageWeight = $mainExerciseLogs->isNotEmpty()
                ? $mainExerciseLogs->sum('weight') / $mainExerciseLogs->sum('reps')
                : null;

            $data = [
                'id' => $session->id,
                'user_id' => $session->user_id,
                'plan_id' => $session->plan_id,
                'day_id' => $session->day_id,
                'custom_plan_id' => $session->custom_plan_id,
                'custom_day_id' => $session->custom_day_id,
                'start_time' => $session->start_time,
                'end_time' => $session->end_time,
                'status' => $session->status,
                'exercise_logs' => $session->exerciseLogs->map(function ($log) {
                    return [
                        'exercise_id' => $log->exercise_id,
                        'name' => $log->exercise->name,
                        'type' => $log->exercise->type,
                        'round_number' => $log->round_number,
                        'reps' => $log->reps,
                        'weight' => $log->weight,
                        'description' => $log->exercise->description,
                        'instructions' => $log->exercise->instructions,
                        'precautions' => $log->exercise->precautions,
                    ];
                }),
                'average_weight' => $averageWeight,
            ];

            return response()->json([
                'success' => true,
                'message' => __('messages.workout_session_created'),
                'data' => $data,
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => __('messages.validation_failed'),
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Failed to create workout session: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => __('messages.failed_to_create', ['error' => $e->getMessage()]),
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $user = $request->user();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.unauthenticated'),
                ], 401);
            }

            $session = WorkoutSession::where('user_id', $user->id)->find($id);
            if (!$session) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.workout_session_not_found'),
                ], 404);
            }

            $validated = $request->validate([
                'plan_id' => 'nullable|exists:predefined_workout_plans,id',
                'day_id' => 'nullable|exists:predefined_workout_plan_days,id',
                'custom_plan_id' => 'nullable|exists:custom_workout_plans,id',
                'custom_day_id' => 'nullable|exists:custom_workout_plan_days,id',
                'start_time' => 'required|date',
                'end_time' => 'nullable|date|after:start_time',
                'status' => 'required|in:completed,in_progress,skipped',
                'exercise_logs' => 'required|array|min:1',
                'exercise_logs.*.exercise_id' => 'required|exists:exercises,id',
                'exercise_logs.*.round_number' => 'required|integer|min:1',
                'exercise_logs.*.reps' => 'required|integer|min:1',
                'exercise_logs.*.weight' => 'nullable|numeric|min:0',
            ]);

            // Ensure either predefined or custom plan is provided, but not both
            if ($validated['plan_id'] && $validated['custom_plan_id']) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.validation_failed'),
                    'errors' => ['plan' => __('messages.validation_failed_plan')],
                ], 422);
            }

            // Validate that day_id belongs to plan_id
            if ($validated['plan_id'] && $validated['day_id']) {
                $day = PredefinedWorkoutPlanDay::where('id', $validated['day_id'])
                    ->where('plan_id', $validated['plan_id'])
                    ->first();
                if (!$day) {
                    return response()->json([
                        'success' => false,
                        'message' => __('messages.validation_failed'),
                        'errors' => ['day_id' => __('messages.invalid_day_for_plan')],
                    ], 422);
                }
            }

            // Check for incomplete main exercises if completing the session
            if ($validated['status'] === 'completed' && $session->hasIncompleteMainExercises()) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.incomplete_main_exercises_warning'),
                    'data' => ['session_id' => $session->id],
                ], 422);
            }

            $session->update([
                'plan_id' => $validated['plan_id'] ?? null,
                'day_id' => $validated['day_id'] ?? null,
                'custom_plan_id' => $validated['custom_plan_id'] ?? null,
                'custom_day_id' => $validated['custom_day_id'] ?? null,
                'start_time' => $validated['start_time'],
                'end_time' => $validated['end_time'] ?? null,
                'status' => $validated['status'],
            ]);

            $session->exerciseLogs()->delete();
            foreach ($validated['exercise_logs'] as $logData) {
                $session->exerciseLogs()->create([
                    'exercise_id' => $logData['exercise_id'],
                    'round_number' => $logData['round_number'],
                    'reps' => $logData['reps'],
                    'weight' => $logData['weight'] ?? null,
                ]);
            }

            $session->load('exerciseLogs.exercise.translations');

            $mainExerciseLogs = $session->exerciseLogs->filter(function ($log) {
                return $log->exercise->type === 'main';
            });
            $averageWeight = $mainExerciseLogs->isNotEmpty()
                ? $mainExerciseLogs->sum('weight') / $mainExerciseLogs->sum('reps')
                : null;

            $data = [
                'id' => $session->id,
                'user_id' => $session->user_id,
                'plan_id' => $session->plan_id,
                'day_id' => $session->day_id,
                'custom_plan_id' => $session->custom_plan_id,
                'custom_day_id' => $session->custom_day_id,
                'start_time' => $session->start_time,
                'end_time' => $session->end_time,
                'status' => $session->status,
                'exercise_logs' => $session->exerciseLogs->map(function ($log) {
                    return [
                        'exercise_id' => $log->exercise_id,
                        'name' => $log->exercise->name,
                        'type' => $log->exercise->type,
                        'round_number' => $log->round_number,
                        'reps' => $log->reps,
                        'weight' => $log->weight,
                        'description' => $log->exercise->description,
                        'instructions' => $log->exercise->instructions,
                        'precautions' => $log->exercise->precautions,
                    ];
                }),
                'average_weight' => $averageWeight,
            ];

            return response()->json([
                'success' => true,
                'message' => __('messages.workout_session_updated'),
                'data' => $data,
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => __('messages.validation_failed'),
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Failed to update workout session: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => __('messages.failed_to_update', ['error' => $e->getMessage()]),
            ], 500);
        }
    }

    public function complete(Request $request, $id)
    {
        try {
            $user = $request->user();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.unauthenticated'),
                ], 401);
            }

            $session = WorkoutSession::where('user_id', $user->id)->find($id);
            if (!$session) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.workout_session_not_found'),
                ], 404);
            }

            // Check for incomplete main exercises
            if ($session->hasIncompleteMainExercises()) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.incomplete_main_exercises_warning'),
                    'data' => ['session_id' => $session->id],
                ], 422);
            }

            $session->update([
                'status' => 'completed',
                'end_time' => now(),
            ]);

            $session->load('exerciseLogs.exercise.translations');

            $mainExerciseLogs = $session->exerciseLogs->filter(function ($log) {
                return $log->exercise->type === 'main';
            });
            $averageWeight = $mainExerciseLogs->isNotEmpty()
                ? $mainExerciseLogs->sum('weight') / $mainExerciseLogs->sum('reps')
                : null;

            $data = [
                'id' => $session->id,
                'user_id' => $session->user_id,
                'plan_id' => $session->plan_id,
                'day_id' => $session->day_id,
                'custom_plan_id' => $session->custom_plan_id,
                'custom_day_id' => $session->custom_day_id,
                'start_time' => $session->start_time,
                'end_time' => $session->end_time,
                'status' => $session->status,
                'exercise_logs' => $session->exerciseLogs->map(function ($log) {
                    return [
                        'exercise_id' => $log->exercise_id,
                        'name' => $log->exercise->name,
                        'type' => $log->exercise->type,
                        'round_number' => $log->round_number,
                        'reps' => $log->reps,
                        'weight' => $log->weight,
                        'description' => $log->exercise->description,
                        'instructions' => $log->exercise->instructions,
                        'precautions' => $log->exercise->precautions,
                    ];
                }),
                'average_weight' => $averageWeight,
            ];

            return response()->json([
                'success' => true,
                'message' => __('messages.workout_session_completed'),
                'data' => $data,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Failed to complete workout session: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => __('messages.failed_to_update', ['error' => $e->getMessage()]),
            ], 500);
        }
    }

    public function skip(Request $request, $id)
    {
        try {
            $user = $request->user();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.unauthenticated'),
                ], 401);
            }

            $session = WorkoutSession::where('user_id', $user->id)->find($id);
            if (!$session) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.workout_session_not_found'),
                ], 404);
            }

            $session->update([
                'status' => 'skipped',
                'end_time' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => __('messages.workout_session_skipped'),
            ], 200);
        } catch (\Exception $e) {
            Log::error('Failed to skip workout session: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => __('messages.failed_to_update', ['error' => $e->getMessage()]),
            ], 500);
        }
    }

    public function destroy(Request $request, $id)
    {
        try {
            $user = $request->user();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.unauthenticated'),
                ], 401);
            }

            $session = WorkoutSession::where('user_id', $user->id)->find($id);
            if (!$session) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.workout_session_not_found'),
                ], 404);
            }

            $session->delete(); // This will also delete exercise logs due to onDelete('cascade')

            return response()->json([
                'success' => true,
                'message' => __('messages.workout_session_deleted'),
            ], 200);
        } catch (\Exception $e) {
            Log::error('Failed to delete workout session: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => __('messages.failed_to_delete', ['error' => $e->getMessage()]),
            ], 500);
        }
    }

    public function previousSessions(Request $request, $exercise_id)
    {
        try {
            $user = $request->user();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.unauthenticated'),
                ], 401);
            }

            $previousSessions = WorkoutSession::query()
                ->where('user_id', $user->id)
                ->whereHas('exerciseLogs', function ($query) use ($exercise_id) {
                    $query->where('exercise_id', $exercise_id);
                })
                ->with(['exerciseLogs' => function ($query) use ($exercise_id) {
                    $query->where('exercise_id', $exercise_id);
                }])
                ->get()
                ->map(function ($session) {
                    $log = $session->exerciseLogs->first();
                    return [
                        'session_id' => $session->id,
                        'start_time' => $session->start_time,
                        'end_time' => $session->end_time,
                        'status' => $session->status,
                        'round_number' => $log->round_number,
                        'reps' => $log->reps,
                        'weight' => $log->weight,
                        'average_weight' => $log->weight ? $log->weight / $log->reps : null,
                    ];
                });

            if ($previousSessions->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'message' => __('messages.no_previous_sessions'),
                    'data' => [],
                ], 200);
            }

            return response()->json([
                'success' => true,
                'message' => __('messages.workout_sessions_retrieved'),
                'data' => $previousSessions,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Failed to retrieve previous sessions: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => __('messages.failed_to_retrieve', ['error' => $e->getMessage()]),
            ], 500);
        }
    }
}