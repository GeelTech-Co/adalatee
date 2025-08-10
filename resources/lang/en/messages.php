<?php

return [
    // UserController messages
    'name_updated' => 'Name updated successfully',
    'password_changed' => 'Password changed successfully',
    'data_reset' => 'User data reset successfully',
    'language_updated' => 'Language updated successfully',
    'validation_failed' => 'Validation failed',
    'invalid_old_password' => 'Invalid old password',
    'user_not_found' => 'User not found',
    'unauthenticated' => 'Unauthenticated',
    'failed_to_update' => 'Failed to update: :error',

    // AuthController messages
    'registration_successful' => 'Registration successful. Please verify your email.',
    'login_successful' => 'Login successful',
    'email_not_found' => 'Email not found',
    'invalid_password' => 'Invalid password',
    'email_not_verified' => 'Email not verified. Please check your email for the verification link.',
    'logout_successful' => 'Logged out successfully',
    'password_reset_link_sent' => 'Password reset link sent to your email',
    'too_many_reset_attempts' => 'Too many reset attempts. Please try again later.',
    'unable_to_send_reset_link' => 'Unable to send reset link',
    'password_reset_successful' => 'Password reset successful',
    'invalid_token' => 'Invalid or expired token',
    'email_verification_successful' => 'Email verified successfully',
    'email_already_verified' => 'Email is already verified',
    'invalid_verification_link' => 'Invalid verification link',
    'invalid_signature' => 'Invalid or expired signature',
    'verification_email_resent' => 'Verification email resent successfully. Please check your inbox.',
    'failed_to_resend_verification' => 'Failed to resend verification email: :error',

    // ExerciseController messages
    'exercises_retrieved' => 'Exercises retrieved successfully',
    'exercise_retrieved' => 'Exercise retrieved successfully',
    'exercise_created' => 'Exercise created successfully',
    'exercise_updated' => 'Exercise updated successfully',
    'exercise_deleted' => 'Exercise deleted successfully',
    'exercise_not_found' => 'Exercise not found',
    'unauthorized' => 'Unauthorized action',
    'failed_to_create' => 'Failed to create: :error',
    'failed_to_retrieve' => 'Failed to retrieve: :error',
    'failed_to_delete' => 'Failed to delete: :error',

    // WorkoutSessionController messages
    'workout_sessions_retrieved' => 'Workout sessions retrieved successfully',
    'workout_session_retrieved' => 'Workout session retrieved successfully',
    'workout_session_created' => 'Workout session created successfully',
    'workout_session_updated' => 'Workout session updated successfully',
    'workout_session_completed' => 'Workout session completed successfully',
    'workout_session_skipped' => 'Workout session skipped successfully',
    'workout_session_deleted' => 'Workout session deleted successfully',
    'workout_session_not_found' => 'Workout session not found',
    'incomplete_main_exercises_warning' => 'Some main exercises are not completed. Are you sure you want to end the session?',
    'no_previous_sessions' => 'No previous sessions available',
    'validation_failed_plan' => 'Cannot select both predefined and custom plan.',

    // PredefinedWorkoutPlanController messages
    'predefined_plans_retrieved' => 'Predefined workout plans retrieved successfully',
    'predefined_plan_retrieved' => 'Predefined workout plan retrieved successfully',
    'predefined_plan_created' => 'Predefined workout plan created successfully',
    'predefined_plan_updated' => 'Predefined workout plan updated successfully',
    'predefined_plan_deleted' => 'Predefined workout plan deleted successfully',
    'predefined_plan_not_found' => 'Predefined workout plan not found',
    'unauthorized' => 'Unauthorized access',
    
];