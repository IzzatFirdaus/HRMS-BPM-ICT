<?php

namespace App\Http\Controllers\Auth; // Ensure the namespace matches your project structure

use App\Http\Controllers\Controller; // Extend the base Controller
// Import the default redirection service provider
use App\Providers\RouteServiceProvider;
// Import necessary classes for HTTP responses and requests
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
// Import the Hash facade for password hashing
use Illuminate\Support\Facades\Hash;
// Import the Password facade for interacting with the password broker
use Illuminate\Support\Facades\Password;
// Import the Str facade for string manipulation (like generating remember tokens)
use Illuminate\Support\Str;
// Import the ValidationException for handling validation errors
use Illuminate\Validation\ValidationException;
// Import the View class for type hinting
use Illuminate\View\View;
// Import the Auth facade for potentially logging in the user after reset
use Illuminate\Support\Facades\Auth;


// This controller handles the process of resetting a user's password.
// It displays the form where the user enters their new password and handles the submission
// to update the password in the database.
// It is typically published as part of Laravel Fortify (used by Jetstream) or Laravel Breeze.
// It works in conjunction with the PasswordResetLinkController (which sends the reset email).

class NewPasswordController extends Controller
{
  /**
   * Display the password reset view.
   *
   * This method is accessed via the link sent in the password reset email.
   * It displays the form where the user enters their new password.
   * The form requires the email address and the password reset token,
   * which are typically passed as query parameters in the URL.
   *
   * @param  \Illuminate\Http\Request  $request  The incoming request, containing the reset token and email in query parameters.
   * @return \Illuminate\View\View  The view containing the password reset form.
   */
  public function create(Request $request): View
  {
    // Returns the view for the password reset form.
    // We pass the $request object to the view so that the email address and
    // token can be automatically populated in the form fields.
    // Ensure you have a view file at resources/views/auth/reset-password.blade.php
    return view('auth.reset-password', [
      'request' => $request, // Pass the request object to the view
    ]);
  }

  /**
   * Handle an incoming new password request.
   *
   * This method handles the POST submission from the password reset form.
   * It validates the submitted data (token, email, password, password_confirmation).
   * It then uses Laravel's Password broker to validate the token and update the user's password.
   * This process typically involves integrating with Fortify's `ResetUserPassword` action.
   *
   * @param  \Illuminate\Http\Request  $request  The incoming new password request containing token, email, password, password_confirmation.
   * @return \Illuminate\Http\RedirectResponse  A redirect response after the password reset attempt.
   * @throws \Illuminate\Validation\ValidationException  If the password reset attempt fails (e.g., invalid token, validation errors).
   */
  public function store(Request $request): RedirectResponse
  {
    // Validate the incoming request data.
    // 'token' is the hidden field containing the reset token.
    // 'email' is the user's email address (should match the one in the token record).
    // 'password' is the new password, which must be required, confirmed (match 'password_confirmation'), and meet minimum length requirements (e.g., 8 characters).
    $request->validate([
      'token' => 'required', // The password reset token is required
      'email' => ['required', 'email'], // The user's email is required and must be a valid email format
      'password' => ['required', 'confirmed', 'min:8'], // The new password is required, must be confirmed, and be at least 8 characters long
    ]);

    // Use Laravel's Password facade (password broker) to attempt to reset the user's password.
    // We use the default password broker ('users') which corresponds to the 'users' guard.
    // The `reset()` method handles validating the token and calls a closure if the token is valid.
    // Fortify hooks into this `reset()` method and provides the logic within the closure.
    $status = Password::broker(config('auth.passwords.users.provider')) // Use the default password broker for users (defined in config/auth.php)
      ->reset(
        // Pass the necessary data (email, password, password_confirmation, token) to the reset method.
        // The password broker will use this data to validate the token and update the password.
        $request->only('email', 'password', 'password_confirmation', 'token'),
        // This closure is executed by the password broker IF the token is valid and matches the email.
        // The $user model instance whose password needs to be reset is passed into this closure.
        function ($user) use ($request) {
          // --- This block's logic is often managed by Fortify's `ResetUserPassword` action ---
          // This closure contains the core logic for updating the user's password in the database.

          // 1. Update the user's password. Hash the new password before saving it.
          //    `forceFill` is used to bypass mass assignment protection if needed, but usually not necessary for 'password'.
          $user->forceFill([
            'password' => Hash::make($request->password), // Hash the new password using the default hasher
            'remember_token' => Str::random(60), // Generate a new remember token for security
          ])->save(); // Save the updated user model

          // 2. Log the user in immediately after successfully resetting their password (optional but a common user experience).
          //    This uses the default authentication guard ('web').
          Auth::login($user);
          // --- End Fortify-managed logic ---
        }
      );

    // Check the status returned by the Password broker's `reset()` method.
    // The status is a string representing the outcome (success or failure reason).
    // Password::PASSWORD_RESET is a constant representing successful password reset.
    if ($status == Password::PASSWORD_RESET) {
      // If the password was successfully reset:
      // Redirect the user to their intended destination (the page they tried
      // to access before starting the password reset flow) or fall back
      // to the default home path defined in RouteServiceProvider::HOME.
      // Include a session flash message with the success status (translated).
      return redirect()->intended(RouteServiceProvider::HOME)->with('status', __($status));
    }

    // If the password reset failed (e.g., invalid token, expired token, email doesn't match token):
    // Throw a ValidationException. This will automatically redirect the user
    // back to the password reset form.
    // The error message will be associated with the 'email' field and will be
    // the translated status message returned by the password broker (e.g., "This password reset token is invalid.").
    throw ValidationException::withMessages([
      'email' => trans($status), // Use the translated error message corresponding to the failure status
    ]);
  }
}
