<?php
namespace App\Http\Controllers;

use App\Models\PredefinedWorkoutPlan;
use App\Models\PredefinedWorkoutPlanDay;
use App\Models\PredefinedWorkoutPlanExercise;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class PredefinedWorkoutPlanController extends Controller
{
    public function index(Request $request)
    {
        try {
            $plans = PredefinedWorkoutPlan::with(['days.exercises.exercise.translations'])->get();

            $data = $plans->map(function ($plan) {
                return [
                    'id' => $plan->id,
                    'name' => $plan->name,
                    'image_url' => $plan->image_url,
                    'days_count' => $plan->days_count,
                    'days' => $plan->days->map(function ($day) {
                        return [
                            'id' => $day->id,
                            'plan_id' => $day->plan_id,
                            'day_name' => $day->day_name,
                            'exercises' => $day->exercises->map(function ($planExercise) {
                                return [
                                    'exercise_id' => $planExercise->exercise_id,
                                    'name' => $planExercise->exercise->name,
                                    'type' => $planExercise->exercise->type,
                                    'order' => $planExercise->order,
                                    'description' => $planExercise->exercise->description,
                                    'instructions' => $planExercise->exercise->instructions,
                                    'precautions' => $planExercise->exercise->precautions,
                                ];
                            })->sortBy('order')->values(),
                        ];
                    }),
                ];
            });

            return response()->json([
                'success' => true,
                'message' => __('messages.predefined_plans_retrieved'),
                'data' => $data,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Failed to retrieve predefined workout plans: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => __('messages.failed_to_retrieve', ['error' => $e->getMessage()]),
            ], 500);
        }
    }

    public function show(Request $request, $id)
    {
        try {
            $plan = PredefinedWorkoutPlan::with(['days.exercises.exercise.translations'])->find($id);

            if (!$plan) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.predefined_plan_not_found'),
                ], 404);
            }

            $data = [
                'id' => $plan->id,
                'name' => $plan->name,
                'image_url' => $plan->image_url,
                'days_count' => $plan->days_count,
                'days' => $plan->days->map(function ($day) {
                    return [
                        'id' => $day->id,
                        'plan_id' => $day->plan_id,
                        'day_name' => $day->day_name,
                        'exercises' => $day->exercises->map(function ($planExercise) {
                            return [
                                'exercise_id' => $planExercise->exercise_id,
                                'name' => $planExercise->exercise->name,
                                'type' => $planExercise->exercise->type,
                                'order' => $planExercise->order,
                                'description' => $planExercise->exercise->description,
                                'instructions' => $planExercise->exercise->instructions,
                                'precautions' => $planExercise->exercise->precautions,
                            ];
                        })->sortBy('order')->values(),
                    ];
                }),
            ];

            return response()->json([
                'success' => true,
                'message' => __('messages.predefined_plan_retrieved'),
                'data' => $data,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Failed to retrieve predefined workout plan: ' . $e->getMessage());
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
            if (!$user || $user->role !== 'admin') {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.unauthorized'),
                ], 403);
            }

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'image_url' => 'nullable|url',
                'days_count' => 'required|integer|min:1',
                'days' => 'required|array|min:1',
                'days.*.day_name' => 'required|string|max:255',
                'days.*.exercises' => 'required|array|min:1',
                'days.*.exercises.*.exercise_id' => 'required|exists:exercises,id',
                'days.*.exercises.*.order' => 'required|integer|min:1',
            ]);

            $plan = PredefinedWorkoutPlan::create([
                'name' => $validated['name'],
                'image_url' => $validated['image_url'] ?? null,
                'days_count' => $validated['days_count'],
            ]);

            foreach ($validated['days'] as $dayData) {
                $day = $plan->days()->create([
                    'day_name' => $dayData['day_name'],
                ]);

                foreach ($dayData['exercises'] as $exerciseData) {
                    $day->exercises()->create([
                        'exercise_id' => $exerciseData['exercise_id'],
                        'order' => $exerciseData['order'],
                    ]);
                }
            }

            $plan->load('days.exercises.exercise.translations');

            $data = [
                'id' => $plan->id,
                'name' => $plan->name,
                'image_url' => $plan->image_url,
                'days_count' => $plan->days_count,
                'days' => $plan->days->map(function ($day) {
                    return [
                        'id' => $day->id,
                        'plan_id' => $day->plan_id,
                        'day_name' => $day->day_name,
                        'exercises' => $day->exercises->map(function ($planExercise) {
                            return [
                                'exercise_id' => $planExercise->exercise_id,
                                'name' => $planExercise->exercise->name,
                                'type' => $planExercise->exercise->type,
                                'order' => $planExercise->order,
                                'description' => $planExercise->exercise->description,
                                'instructions' => $planExercise->exercise->instructions,
                                'precautions' => $planExercise->exercise->precautions,
                            ];
                        })->sortBy('order')->values(),
                    ];
                }),
            ];

            return response()->json([
                'success' => true,
                'message' => __('messages.predefined_plan_created'),
                'data' => $data,
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => __('messages.validation_failed'),
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Failed to create predefined workout plan: ' . $e->getMessage());
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
            if (!$user || $user->role !== 'admin') {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.unauthorized'),
                ], 403);
            }

            $plan = PredefinedWorkoutPlan::find($id);
            if (!$plan) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.predefined_plan_not_found'),
                ], 404);
            }

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'image_url' => 'nullable|url',
                'days_count' => 'required|integer|min:1',
                'days' => 'required|array|min:1',
                'days.*.day_name' => 'required|string|max:255',
                'days.*.exercises' => 'required|array|min:1',
                'days.*.exercises.*.exercise_id' => 'required|exists:exercises,id',
                'days.*.exercises.*.order' => 'required|integer|min:1',
            ]);

            $plan->update([
                'name' => $validated['name'],
                'image_url' => $validated['image_url'] ?? null,
                'days_count' => $validated['days_count'],
            ]);

            $plan->days()->delete();
            foreach ($validated['days'] as $dayData) {
                $day = $plan->days()->create([
                    'day_name' => $dayData['day_name'],
                ]);

                foreach ($dayData['exercises'] as $exerciseData) {
                    $day->exercises()->create([
                        'exercise_id' => $exerciseData['exercise_id'],
                        'order' => $exerciseData['order'],
                    ]);
                }
            }

            $plan->load('days.exercises.exercise.translations');

            $data = [
                'id' => $plan->id,
                'name' => $plan->name,
                'image_url' => $plan->image_url,
                'days_count' => $plan->days_count,
                'days' => $plan->days->map(function ($day) {
                    return [
                        'id' => $day->id,
                        'plan_id' => $day->plan_id,
                        'day_name' => $day->day_name,
                        'exercises' => $day->exercises->map(function ($planExercise) {
                            return [
                                'exercise_id' => $planExercise->exercise_id,
                                'name' => $planExercise->exercise->name,
                                'type' => $planExercise->exercise->type,
                                'order' => $planExercise->order,
                                'description' => $planExercise->exercise->description,
                                'instructions' => $planExercise->exercise->instructions,
                                'precautions' => $planExercise->exercise->precautions,
                            ];
                        })->sortBy('order')->values(),
                    ];
                }),
            ];

            return response()->json([
                'success' => true,
                'message' => __('messages.predefined_plan_updated'),
                'data' => $data,
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => __('messages.validation_failed'),
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Failed to update predefined workout plan: ' . $e->getMessage());
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
            if (!$user || $user->role !== 'admin') {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.unauthorized'),
                ], 403);
            }

            $plan = PredefinedWorkoutPlan::find($id);
            if (!$plan) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.predefined_plan_not_found'),
                ], 404);
            }

            $plan->delete(); // This will cascade to days and exercises

            return response()->json([
                'success' => true,
                'message' => __('messages.predefined_plan_deleted'),
            ], 200);
        } catch (\Exception $e) {
            Log::error('Failed to delete predefined workout plan: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => __('messages.failed_to_delete', ['error' => $e->getMessage()]),
            ], 500);
        }
    }
}