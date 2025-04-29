<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\NewPasswordController;
// Note: Laravel 10+ uses App\Http\Controllers\Auth\PasswordController for updating password.
// Older versions might use different controllers or have the logic within NewPasswordController.
// We'll include the modern one, but your specific version might differ.
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Support\Facades\Route;

// Routes for guests (users who are not logged in)
Route::middleware('guest')->group(function () {
    // Registration Routes
    // Shows the registration form
    Route::get('register', [RegisteredUserController::class, 'create'])
        ->name('register');

    // Handles the registration form submission
    Route::post('register', [RegisteredUserController::class, 'store']);

    // Authentication (Login) Routes
    // Shows the login form
    Route::get('login', [AuthenticatedSessionController::class, 'create'])
        ->name('login');

    // Handles the login form submission
    Route::post('login', [AuthenticatedSessionController::class, 'store']);

    // Password Reset Link Routes
    // Shows the "Forgot Password" form
    Route::get('forgot-password', [PasswordResetLinkController::class, 'create']) // Corrected Controller name
        ->name('password.request');

    // Handles the submission of the "Forgot Password" form (sends reset link)
    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
        ->name('password.email');

    // Password Reset Form & Update Routes
    // Shows the "Reset Password" form (with token)
    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])
        ->name('password.reset');

    // Handles the submission of the "Reset Password" form (updates password)
    // In Laravel 10+, this typically maps to App\Http\Controllers\Auth\PasswordController@store
    // but keeping NewPasswordController@store for potential compatibility with older versions.
    Route::post('reset-password', [NewPasswordController::class, 'store'])
        ->name('password.store');
});

// Routes for authenticated users (users who are logged in)
Route::middleware('auth')->group(function () {
    // Email Verification Prompt
    // Shows the email verification notice if the user's email is not verified
    Route::get('verify-email', EmailVerificationPromptController::class)
        ->name('verification.notice');

    // Email Verification Handler
    // Handles the verification link clicked in the email
    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1']) // Requires signed URL and rate limiting
        ->name('verification.verify');

    // Resending Email Verification Link
    // Handles the request to resend the verification email
    Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:6,1') // Rate limiting
        ->name('verification.send');

    // Confirmation Password Routes
    // Shows the "Confirm Password" screen (for security-sensitive actions)
    Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])
        ->name('password.confirm');

    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);

    // Password Update Route (for authenticated users changing their password)
    // In Laravel 10+, this is handled by App\Http\Controllers\Auth\PasswordController@update
    Route::put('password', [PasswordController::class, 'update'])->name('password.update');

    // Logout Route
    // Handles the logout request
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');
});
