<?php

namespace App\Http\Controllers;

use App\Models\Exercise;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class ExerciseController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = Exercise::query()->with('translations');

            if ($request->has('type')) {
                $query->where('type', $request->type);
            }
            if ($request->has('muscle_group')) {
                $query->where('muscle_group', $request->muscle_group);
            }

            $exercises = $query->get()->map(function ($exercise) {
                return [
                    'id' => $exercise->id,
                    'name' => $exercise->name,
                    'type' => $exercise->type,
                    'muscle_group' => $exercise->muscle_group,
                    'secondary_muscles' => $exercise->secondary_muscles,
                    'image_url' => $exercise->image_url,
                    'animation_url' => $exercise->animation_url,
                    'description' => $exercise->description,
                    'instructions' => $exercise->instructions,
                    'precautions' => $exercise->precautions,
                ];
            });

            return response()->json([
                'success' => true,
                'message' => __('messages.exercises_retrieved'),
                'data' => $exercises,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Failed to retrieve exercises: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => __('messages.failed_to_retrieve', ['error' => $e->getMessage()]),
            ], 500);
        }
    }

    public function show(Request $request, $id)
    {
        try {
            $exercise = Exercise::with('translations')->find($id);

            if (!$exercise) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.exercise_not_found'),
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => __('messages.exercise_retrieved'),
                'data' => [
                    'id' => $exercise->id,
                    'name' => $exercise->name,
                    'type' => $exercise->type,
                    'muscle_group' => $exercise->muscle_group,
                    'secondary_muscles' => $exercise->secondary_muscles,
                    'image_url' => $exercise->image_url,
                    'animation_url' => $exercise->animation_url,
                    'description' => $exercise->description,
                    'instructions' => $exercise->instructions,
                    'precautions' => $exercise->precautions,
                ],
            ], 200);
        } catch (\Exception $e) {
            Log::error('Failed to retrieve exercise: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => __('messages.failed_to_retrieve', ['error' => $e->getMessage()]),
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'type' => 'required|in:warmup,main,recovery',
                'muscle_group' => 'required|string|max:255',
                'secondary_muscles' => 'nullable|string',
                'description_en' => 'required|string',
                'instructions_en' => 'required|string',
                'precautions_en' => 'required|string',
                'description_ar' => 'required|string',
                'instructions_ar' => 'required|string',
                'precautions_ar' => 'required|string',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'animation' => 'nullable|mimes:mp4,webm|max:10240',
            ]);

            $user = $request->user();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.unauthenticated'),
                ], 401);
            }

            $data = [
                'name' => $validated['name'],
                'type' => $validated['type'],
                'muscle_group' => $validated['muscle_group'],
                'secondary_muscles' => $validated['secondary_muscles'] ?? null,
            ];

            if ($request->hasFile('image')) {
                $data['image_url'] = $request->file('image')->store('exercises/images', 'public');
            }
            if ($request->hasFile('animation')) {
                $data['animation_url'] = $request->file('animation')->store('exercises/animations', 'public');
            }

            $exercise = Exercise::create($data);

            // Add English translation
            $exercise->translations()->create([
                'locale' => 'en',
                'description' => $validated['description_en'],
                'instructions' => $validated['instructions_en'],
                'precautions' => $validated['precautions_en'],
            ]);

            // Add Arabic translation
            $exercise->translations()->create([
                'locale' => 'ar',
                'description' => $validated['description_ar'],
                'instructions' => $validated['instructions_ar'],
                'precautions' => $validated['precautions_ar'],
            ]);

            return response()->json([
                'success' => true,
                'message' => __('messages.exercise_created'),
                'data' => [
                    'id' => $exercise->id,
                    'name' => $exercise->name,
                    'type' => $exercise->type,
                    'muscle_group' => $exercise->muscle_group,
                    'secondary_muscles' => $exercise->secondary_muscles,
                    'image_url' => $exercise->image_url,
                    'animation_url' => $exercise->animation_url,
                    'description' => $exercise->description,
                    'instructions' => $exercise->instructions,
                    'precautions' => $exercise->precautions,
                ],
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => __('messages.validation_failed'),
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Failed to create exercise: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => __('messages.failed_to_create', ['error' => $e->getMessage()]),
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $exercise = Exercise::find($id);
            if (!$exercise) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.exercise_not_found'),
                ], 404);
            }

            $user = $request->user();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.unauthenticated'),
                ], 401);
            }

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'type' => 'required|in:warmup,main,recovery',
                'muscle_group' => 'required|string|max:255',
                'secondary_muscles' => 'nullable|string',
                'description_en' => 'required|string',
                'instructions_en' => 'required|string',
                'precautions_en' => 'required|string',
                'description_ar' => 'required|string',
                'instructions_ar' => 'required|string',
                'precautions_ar' => 'required|string',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'animation' => 'nullable|mimes:mp4,webm|max:10240',
            ]);

            $data = [
                'name' => $validated['name'],
                'type' => $validated['type'],
                'muscle_group' => $validated['muscle_group'],
                'secondary_muscles' => $validated['secondary_muscles'] ?? null,
            ];

            if ($request->hasFile('image')) {
                if ($exercise->image_url) {
                    Storage::disk('public')->delete($exercise->image_url);
                }
                $data['image_url'] = $request->file('image')->store('exercises/images', 'public');
            }
            if ($request->hasFile('animation')) {
                if ($exercise->animation_url) {
                    Storage::disk('public')->delete($exercise->animation_url);
                }
                $data['animation_url'] = $request->file('animation')->store('exercises/animations', 'public');
            }

            $exercise->update($data);

            // Update or create English translation
            $exercise->translations()->updateOrCreate(
                ['locale' => 'en'],
                [
                    'description' => $validated['description_en'],
                    'instructions' => $validated['instructions_en'],
                    'precautions' => $validated['precautions_en'],
                ]
            );

            // Update or create Arabic translation
            $exercise->translations()->updateOrCreate(
                ['locale' => 'ar'],
                [
                    'description' => $validated['description_ar'],
                    'instructions' => $validated['instructions_ar'],
                    'precautions' => $validated['precautions_ar'],
                ]
            );

            return response()->json([
                'success' => true,
                'message' => __('messages.exercise_updated'),
                'data' => [
                    'id' => $exercise->id,
                    'name' => $exercise->name,
                    'type' => $exercise->type,
                    'muscle_group' => $exercise->muscle_group,
                    'secondary_muscles' => $exercise->secondary_muscles,
                    'image_url' => $exercise->image_url,
                    'animation_url' => $exercise->animation_url,
                    'description' => $exercise->description,
                    'instructions' => $exercise->instructions,
                    'precautions' => $exercise->precautions,
                ],
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => __('messages.validation_failed'),
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Failed to update exercise: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => __('messages.failed_to_update', ['error' => $e->getMessage()]),
            ], 500);
        }
    }

    public function destroy(Request $request, $id)
    {
        try {
            $exercise = Exercise::find($id);
            if (!$exercise) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.exercise_not_found'),
                ], 404);
            }

            $user = $request->user();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.unauthenticated'),
                ], 401);
            }

            if ($exercise->image_url) {
                Storage::disk('public')->delete($exercise->image_url);
            }
            if ($exercise->animation_url) {
                Storage::disk('public')->delete($exercise->animation_url);
            }

            $exercise->delete(); // This will also delete translations due to onDelete('cascade')

            return response()->json([
                'success' => true,
                'message' => __('messages.exercise_deleted'),
            ], 200);
        } catch (\Exception $e) {
            Log::error('Failed to delete exercise: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => __('messages.failed_to_delete', ['error' => $e->getMessage()]),
            ], 500);
        }
    }
}