<?php

namespace App\Http\Controllers\Auth; // Ensure the namespace matches your project structure

use App\Http\Controllers\Controller; // Extend the base Controller
// Import the default redirection service provider
use App\Providers\RouteServiceProvider;
// Import necessary classes for HTTP responses and requests
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
// Import the View class for type hinting
use Illuminate\View\View;
use Illuminate\Support\Facades\Log; // Import Log facade for logging
use Illuminate\Support\Facades\Auth; // Import Auth facade to get authenticated user ID


// This controller's sole purpose is to display the email verification notice view.
// It is typically used in conjunction with the 'verified' middleware.
// When an authenticated user tries to access a route protected by 'verified'
// and their email address is not yet verified, they are automatically
// redirected to the route handled by this controller.
// It is commonly implemented as a single-action (invokable) controller.
// It is typically published as part of Laravel Fortify (used by Jetstream) or Laravel Breeze.

class EmailVerificationPromptController extends Controller
{
  /**
   * Display the email verification prompt.
   *
   * This single-action method is executed when the route it handles is accessed.
   * It checks if the authenticated user's email has already been verified.
   * If verified, it redirects the user to the intended destination.
   * If not verified, it displays the email verification notice view.
   *
   * @param  \Illuminate\Http\Request  $request  The incoming request, containing the authenticated user.
   * @return \Illuminate\Http\RedirectResponse|\Illuminate\View\View  A redirect response or a view response.
   */
  public function __invoke(Request $request): RedirectResponse|View // Declares it's a single-action invokable controller and adds return type hint
  {
    // Ensure the authenticated user model implements the Illuminate\Contracts\Auth\MustVerifyEmail interface.
    // This interface provides the hasVerifiedEmail() method.

    // Check if the authenticated user's email address is already verified.
    if ($request->user()->hasVerifiedEmail()) {
      // Optional: Log if a verified user somehow reaches this prompt (shouldn't happen with correct middleware)
      Log::warning('Verified user redirected from verification prompt.', [
        'user_id' => Auth::id(), // Log the ID of the authenticated user
        'ip_address' => $request->ip(),
      ]);

      // If the email is already verified, redirect the user to their intended
      // destination (the page they were trying to access before being
      // redirected by the 'verified' middleware) or fall back to the
      // default home path defined in RouteServiceProvider::HOME.
      // There is no need to show the verification prompt if they are already verified.
      return redirect()->intended(RouteServiceProvider::HOME);
    }

    // Log that an unverified user is being shown the verification prompt
    Log::info('Unverified user shown email verification prompt.', [
      'user_id' => Auth::id(), // Log the ID of the authenticated user
      'ip_address' => $request->ip(),
    ]);


    // If the email address is NOT verified, display the email verification notice view.
    // This view typically informs the user that their email is not verified and
    // provides instructions on how to verify it, including a link to resend
    // the verification email (handled by EmailVerificationNotificationController).
    // We pass any session status (like the 'verification-link-sent' status
    // from the notification controller) to the view so it can be displayed to the user.
    // Ensure you have a view file at resources/views/auth/verify-email.blade.php
    return view('auth.verify-email', ['status' => session('status')]);
  }
}
