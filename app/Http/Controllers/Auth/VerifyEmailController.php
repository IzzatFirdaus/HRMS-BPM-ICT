<?php

namespace App\Http\Controllers\Auth; // Ensure the namespace matches your project structure

use App\Http\Controllers\Controller; // Extend the base Controller
// Import the default redirection service provider
use App\Providers\RouteServiceProvider;
// Import the Verified event, fired after an email address is successfully verified
use Illuminate\Auth\Events\Verified;
// Import the custom EmailVerificationRequest Form Request provided by Laravel
// This request class handles the validation of the signed verification URL.
use Illuminate\Foundation\Auth\EmailVerificationRequest;
// Import necessary classes for HTTP responses
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log; // Import Log facade for logging
use Illuminate\Support\Facades\Auth; // Import Auth facade to get authenticated user ID

// This controller is a single-action controller that handles the email verification link.
// When a user clicks the signed verification link in the email, this controller's
// __invoke method is executed. It verifies the request and marks the user's email as verified.
// It is typically published as part of Laravel Fortify (used by Jetstream) or Laravel Breeze.

class VerifyEmailController extends Controller
{
  /**
   * Mark the authenticated user's email address as verified.
   *
   * This single-action method is the entry point for the email verification link.
   * It automatically receives a validated `EmailVerificationRequest` instance,
   * which confirms that the request is a valid signed URL for the authenticated user.
   * It then checks if the user's email is already verified and, if not, marks it as verified.
   *
   * @param  \Illuminate\Foundation\Auth\EmailVerificationRequest  $request  The incoming signed and validated email verification request.
   * @return \Illuminate\Http\RedirectResponse  A redirect response after the verification attempt.
   */
  public function __invoke(EmailVerificationRequest $request): RedirectResponse // Declares it's a single-action invokable controller and adds return type hint
  {
    // The `EmailVerificationRequest` automatically handles:
    // 1. Ensuring the user is authenticated.
    // 2. Validating that the request URL is a valid signed URL.
    // 3. Validating that the 'id' and 'hash' parameters in the URL match the authenticated user.
    // If any of these checks fail, it will automatically return an appropriate response (e.g., 403 Forbidden or redirect).

    // Check if the authenticated user's email address is already verified.
    // The `hasVerifiedEmail()` method is provided by the `Illuminate\Contracts\Auth\MustVerifyEmail` interface,
    // which your User model should implement.
    if ($request->user()->hasVerifiedEmail()) {
      // Log if a user attempts to verify but is already verified
      Log::info('User attempted to verify email but was already verified.', [
        'user_id' => Auth::id(), // Log the ID of the authenticated user
        'ip_address' => $request->ip(),
      ]);

      // If the email is already verified, redirect the user to their intended
      // destination (the page they tried to access before being redirected
      // by the 'verified' middleware) or fall back to the default home path.
      // We append a query parameter '?verified=1' or similar to the URL
      // so the destination page can show a confirmation message (e.g., "Your email was already verified!").
      return redirect()->intended(RouteServiceProvider::HOME . '?verified=1');
    }

    // If the email address is NOT already verified, proceed to mark it as verified.
    // The `markEmailAsVerified()` method is provided by the `MustVerifyEmail` interface.
    // This method updates the `email_verified_at` column in the database for the user.
    // It returns `true` if the email was successfully marked as verified (i.e., it wasn't already verified),
    // and `false` if it was already verified (though the check above handles this case).
    if ($request->user()->markEmailAsVerified()) {
      // Log that the email was successfully marked as verified
      Log::info('User email successfully marked as verified.', [
        'user_id' => Auth::id(), // Log the ID of the authenticated user
        'ip_address' => $request->ip(),
      ]);

      // If the email was successfully marked as verified (i.e., it just happened),
      // fire the `Illuminate\Auth\Events\Verified` event.
      // You can listen to this event in your application's EventServiceProvider
      // to perform actions immediately after a user's email is verified (e.g.,
      // assign a specific role, send a welcome email, update user status).
      event(new Verified($request->user()));
    }

    // After successful verification (whether it just happened or it was already verified),
    // redirect the user to their intended destination (or the default home path).
    // We again append a query parameter '?verified=1' or similar to the URL
    // so the destination page can show a confirmation message (e.g., "Your email has been verified!").
    return redirect()->intended(RouteServiceProvider::HOME . '?verified=1');
  }
}
