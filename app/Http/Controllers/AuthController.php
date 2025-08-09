<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|confirmed|regex:/^(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}$/',
            ]);

            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => 'user',
            ]);

            event(new Registered($user));
            Log::info('Email verification sent for: ' . $user->email);

            return response()->json([
                'success' => true,
                'message' => __('messages.registration_successful'),
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'role' => $user->role,
                    ],
                ],
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => __('messages.validation_failed'),
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Registration failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => __('messages.failed_to_update', ['error' => $e->getMessage()]),
            ], 500);
        }
    }

    public function login(Request $request)
    {
        try {
            $validated = $request->validate([
                'email' => 'required|email',
                'password' => 'required',
            ]);

            $user = User::where('email', $validated['email'])->first();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.email_not_found'),
                ], 404);
            }

            if (!Hash::check($validated['password'], $user->password)) {
                Log::info('Login failed: Invalid password for ' . $user->email);
                return response()->json([
                    'success' => false,
                    'message' => __('messages.invalid_password'),
                ], 401);
            }

            if (!$user->hasVerifiedEmail()) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.email_not_verified'),
                ], 403);
            }

            $token = $user->createToken('auth_token', ['*'], now()->addHours(12))->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => __('messages.login_successful'),
                'data' => [
                    'token' => $token,
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
            Log::error('Login failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => __('messages.failed_to_update', ['error' => $e->getMessage()]),
            ], 500);
        }
    }

    public function logout(Request $request)
    {
        try {
            $request->user()->currentAccessToken()->delete();
            return response()->json([
                'success' => true,
                'message' => __('messages.logout_successful'),
            ], 200);
        } catch (\Exception $e) {
            Log::error('Logout failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => __('messages.failed_to_update', ['error' => $e->getMessage()]),
            ], 500);
        }
    }

    public function forgotPassword(Request $request)
    {
        try {
            $request->validate(['email' => 'required|email']);

            $user = User::where('email', $request->email)->first();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.email_not_found'),
                ], 404);
            }

            $status = Password::sendResetLink($request->only('email'));
            Log::info('Password reset status for ' . $request->email . ': ' . $status);

            if ($status === Password::RESET_LINK_SENT) {
                return response()->json([
                    'success' => true,
                    'message' => __('messages.password_reset_link_sent'),
                ], 200);
            }

            if ($status === Password::RESET_THROTTLED) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.too_many_reset_attempts'),
                ], 429);
            }

            return response()->json([
                'success' => false,
                'message' => __('messages.unable_to_send_reset_link'),
            ], 500);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => __('messages.validation_failed'),
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Forgot password failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => __('messages.failed_to_update', ['error' => $e->getMessage()]),
            ], 500);
        }
    }

    public function resetPassword(Request $request)
    {
        try {
            $request->validate([
                'token' => 'required|string',
                'email' => 'required|email',
                'password' => 'required|confirmed|regex:/^(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}$/',
            ]);

            $status = Password::reset(
                $request->only('email', 'token', 'password', 'password_confirmation'),
                function ($user, $password) {
                    $user->password = Hash::make($password);
                    $user->setRememberToken(Str::random(60));
                    $user->save();
                }
            );

            if ($status === Password::PASSWORD_RESET) {
                return response()->json([
                    'success' => true,
                    'message' => __('messages.password_reset_successful'),
                ], 200);
            }

            if ($status === Password::INVALID_TOKEN) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.invalid_token'),
                ], 400);
            }

            if ($status === Password::INVALID_USER) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.email_not_found'),
                ], 404);
            }

            return response()->json([
                'success' => false,
                'message' => __('messages.unable_to_send_reset_link'),
            ], 500);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => __('messages.validation_failed'),
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Reset password failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => __('messages.failed_to_update', ['error' => $e->getMessage()]),
            ], 500);
        }
    }

    public function verifyEmail(Request $request)
    {
        try {
            $request->validate([
                'id' => 'required|exists:users,id',
                'hash' => 'required|string',
            ]);

            $user = User::find($request->id);
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.user_not_found'),
                ], 404);
            }

            if (!hash_equals((string) $request->hash, sha1($user->email))) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.invalid_verification_link'),
                ], 400);
            }

            if (!$request->hasValidSignature()) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.invalid_signature'),
                ], 400);
            }

            if ($user->hasVerifiedEmail()) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.email_already_verified'),
                ], 400);
            }

            $user->markEmailAsVerified();
            Log::info('Email verified for: ' . $user->email);

            return response()->json([
                'success' => true,
                'message' => __('messages.email_verification_successful'),
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => __('messages.validation_failed'),
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Email verification failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => __('messages.failed_to_update', ['error' => $e->getMessage()]),
            ], 500);
        }
    }

    public function resendVerification(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email|exists:users,email',
            ]);

            $user = User::where('email', $request->email)->first();

            if ($user->hasVerifiedEmail()) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.email_already_verified'),
                ], 400);
            }

            event(new Registered($user));
            Log::info('Verification email resent for: ' . $user->email);

            return response()->json([
                'success' => true,
                'message' => __('messages.verification_email_resent'),
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => __('messages.validation_failed'),
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Resend verification failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => __('messages.failed_to_resend_verification', ['error' => $e->getMessage()]),
            ], 500);
        }
    }
}