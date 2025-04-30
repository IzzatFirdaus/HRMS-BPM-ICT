<?php

namespace App\Http\Controllers\Auth; // Ensure the namespace matches your project structure

use App\Http\Controllers\Controller; // Extend the base Controller
// Import the default redirection service provider
use App\Providers\RouteServiceProvider;
// Import necessary classes for HTTP responses and requests
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log; // Import Log facade for logging
use Illuminate\Support\Facades\Auth; // Import Auth facade to get authenticated user ID

// This controller handles the action of sending a new email verification notification.
// It is typically published as part of Laravel Fortify (used by Jetstream) or Laravel Breeze.
// Its primary responsibility is to trigger the sending of the verification email
// when the user requests it (e.g., by clicking a "Resend Verification Email" link).

// Note: This controller is often implemented as a single-action controller
// using the __invoke() method. The code below uses a store() method, which
// aligns with the standard naming convention used in routes/auth.php for POST requests.

class EmailVerificationNotificationController extends Controller
{
  /**
   * Send a new email verification notification.
   *
   * This method handles the POST request to resend the verification email.
   * It checks if the authenticated user's email is already verified.
   * If not verified, it triggers the sending of the verification email
   * using the user model's built-in method.
   *
   * @param  \Illuminate\Http\Request  $request  The incoming request.
   * @return \Illuminate\Http\RedirectResponse  A redirect response.
   */
  public function store(Request $request): RedirectResponse
  {
    // Ensure the authenticated user model implements the Illuminate\Contracts\Auth\MustVerifyEmail interface.
    // This interface requires the hasVerifiedEmail() and sendEmailVerificationNotification() methods.

    // Check if the authenticated user's email address is already verified.
    if ($request->user()->hasVerifiedEmail()) {
      // Log if a user attempts to resend verification but is already verified
      Log::info('User attempted to resend verification email but was already verified.', [
        'user_id' => Auth::id(), // Log the ID of the authenticated user
        'ip_address' => $request->ip(),
      ]);

      // If the email is already verified, there's no need to resend the notification.
      // Redirect the user to their intended destination (the page they were trying
      // to access before being redirected by the verification middleware) or
      // fall back to the default home path defined in RouteServiceProvider::HOME.
      return redirect()->intended(RouteServiceProvider::HOME);
    }

    // If the email address is not verified, trigger the sending of a new
    // email verification notification to the user.
    $request->user()->sendEmailVerificationNotification();

    // Log that a verification email has been sent
    Log::info('New email verification notification sent.', [
      'user_id' => Auth::id(), // Log the ID of the authenticated user
      'ip_address' => $request->ip(),
    ]);


    // Redirect the user back to the page they were on (typically the email
    // verification notice page) and include a session flash message with a
    // status key 'verification-link-sent'. This status can be checked in
    // the view to display a success message to the user (e.g., "A new
    // verification link has been sent to your email address.").
    return back()->with('status', 'verification-link-sent');
  }

  // If you implement this as a single-action controller, the code would look like this:
  // public function __invoke(Request $request): RedirectResponse
  // {
  //      // Log if a user attempts to resend verification but is already verified
  //      if ($request->user()->hasVerifiedEmail()) {
  //          Log::info('User attempted to resend verification email but was already verified.', [
  //              'user_id' => Auth::id(),
  //              'ip_address' => $request->ip(),
  //          ]);
  //          return redirect()->intended(RouteServiceProvider::HOME);
  //      }
  //
  //      $request->user()->sendEmailVerificationNotification();
  //
  //      // Log that a verification email has been sent
  //      Log::info('New email verification notification sent.', [
  //          'user_id' => Auth::id(),
  //          'ip_address' => $request->ip(),
  //      ]);
  //
  //      return back()->with('status', 'verification-link-sent');
  // }
}
