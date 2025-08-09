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
                'message' => 'Registration successful. Please verify your email.',
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
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Registration failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to register user: ' . $e->getMessage(),
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
                    'message' => 'Email not found',
                ], 404);
            }

            if (!Hash::check($validated['password'], $user->password)) {
                Log::info('Login failed: Invalid password for ' . $user->email);
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid password',
                ], 401);
            }

            if (!$user->hasVerifiedEmail()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email not verified. Please check your email for the verification link.',
                ], 403);
            }

            $token = $user->createToken('auth_token', ['*'], now()->addHours(12))->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Login successful',
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
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Login failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to login: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function logout(Request $request)
    {
        try {
            $request->user()->currentAccessToken()->delete();
            return response()->json([
                'success' => true,
                'message' => 'Logged out successfully',
            ], 200);
        } catch (\Exception $e) {
            Log::error('Logout failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to logout: ' . $e->getMessage(),
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
                    'message' => 'Email not found',
                ], 404);
            }

            $status = Password::sendResetLink($request->only('email'));
            Log::info('Password reset status for ' . $request->email . ': ' . $status);

            if ($status === Password::RESET_LINK_SENT) {
                return response()->json([
                    'success' => true,
                    'message' => 'Password reset link sent to your email',
                ], 200);
            }

            if ($status === Password::RESET_THROTTLED) {
                return response()->json([
                    'success' => false,
                    'message' => 'Too many reset attempts. Please try again later.',
                ], 429);
            }

            return response()->json([
                'success' => false,
                'message' => 'Unable to send reset link',
            ], 500);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Forgot password failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to process reset request: ' . $e->getMessage(),
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
                    'message' => 'Password reset successful',
                ], 200);
            }

            if ($status === Password::INVALID_TOKEN) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid or expired token',
                ], 400);
            }

            if ($status === Password::INVALID_USER) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email not found',
                ], 404);
            }

            return response()->json([
                'success' => false,
                'message' => 'Unable to reset password',
            ], 500);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Reset password failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to reset password: ' . $e->getMessage(),
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
                    'message' => 'User not found',
                ], 404);
            }

            if (!hash_equals((string) $request->hash, sha1($user->email))) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid verification link',
                ], 400);
            }

            if (!$request->hasValidSignature()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid or expired signature',
                ], 400);
            }

            if ($user->hasVerifiedEmail()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email already verified',
                ], 400);
            }

            $user->markEmailAsVerified();
            Log::info('Email verified for: ' . $user->email);

            return response()->json([
                'success' => true,
                'message' => 'Email verified successfully',
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Email verification failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to verify email: ' . $e->getMessage(),
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
                    'message' => 'Email is already verified',
                ], 400);
            }

            event(new Registered($user));
            Log::info('Verification email resent for: ' . $user->email);

            return response()->json([
                'success' => true,
                'message' => 'Verification email resent successfully. Please check your inbox.',
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Resend verification failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to resend verification email: ' . $e->getMessage(),
            ], 500);
        }
    }
}