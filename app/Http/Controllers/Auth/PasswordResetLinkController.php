<?php

namespace App\Http\Controllers\Auth; // Ensure the namespace matches your project structure

use App\Http\Controllers\Controller; // Extend the base Controller
// Import necessary classes for HTTP responses and requests
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
// Import the Password facade for interacting with the password broker
use Illuminate\Support\Facades\Password;
// Import the ValidationException for handling validation errors
use Illuminate\Validation\ValidationException;
// Import the View class for type hinting
use Illuminate\View\View;


// This controller handles the initial part of the "Forgot Password" flow.
// It displays the form where the user enters their email address and handles the
// submission of that form to send the password reset link email.
// It works in conjunction with the NewPasswordController (which handles the actual password reset).
// It is typically published as part of Laravel Fortify (used by Jetstream) or Laravel Breeze.
// It interacts with Laravel's built-in password broker service.

class PasswordResetLinkController extends Controller
{
  /**
   * Display the password reset link request view.
   *
   * This method shows the form where the user is asked to enter their
   * email address to receive a password reset link.
   *
   * @return \Illuminate\View\View  The view containing the "Forgot Password" form.
   */
  public function create(): View
  {
    // Returns the view for the "Forgot Password" form.
    // Ensure you have a view file at resources/views/auth/forgot-password.blade.php
    return view('auth.forgot-password');
  }

  /**
   * Handle a send reset link request.
   *
   * This method handles the POST submission from the "Forgot Password" form.
   * It validates the submitted email address. If validation passes, it uses
   * Laravel's Password broker to find the user and dispatch the password
   * reset notification email containing the reset token link.
   *
   * @param  \Illuminate\Http\Request  $request  The incoming request containing the user's email address.
   * @return \Illuminate\Http\RedirectResponse  A redirect response after attempting to send the reset link.
   * @throws \Illuminate\Validation\ValidationException  If validation fails (e.g., email missing, invalid format, user not found).
   */
  public function store(Request $request): RedirectResponse
  {
    // Validate the incoming request data.
    // The 'email' field is required and must be a valid email format.
    // Note: The 'email' rule also often implicitly checks if a user with this email
    // exists within the configured user provider for the password broker.
    $request->validate([
      'email' => ['required', 'email'], // The user's email address is required and must be a valid email format
    ]);

    // Use Laravel's Password facade to interact with the password reset broker service.
    // We use the default password broker, typically configured for the 'users' provider
    // in config/auth.php under 'passwords.users.provider'.
    // The `sendResetLink()` method finds the user by email and dispatches the
    // Mailable responsible for sending the password reset email containing the token.
    // Fortify hooks into this method to handle the sending of the notification.
    $status = Password::broker(config('auth.passwords.users.provider')) // Use the default password broker for users
      ->sendResetLink(
        // Pass the request's email input to the sendResetLink method.
        // This is typically an array containing the 'email' key.
        $request->only('email')
      );

    // Check the status returned by the Password broker's `sendResetLink()` method.
    // The status is a string constant indicating the outcome.
    // `Password::RESET_LINK_SENT` is the constant for success.
    // Other status constants (e.g., `Password::INVALID_USER`) indicate failure reasons.
    if ($status == Password::RESET_LINK_SENT) {
      // If the password reset link was successfully sent:
      // Redirect the user back to the "Forgot Password" form page.
      // Include the submitted email address in the input so it can be repopulated in the form.
      // Include a session flash message with the success status key (translated).
      // The status message can be displayed to the user in the view (e.g., "We have emailed your password reset link!").
      return back()->withInput($request->only('email'))->with('status', __($status));
    }

    // If the password reset link could not be sent (e.g., no user found with that email):
    // Throw a ValidationException. This will automatically redirect the user
    // back to the "Forgot Password" form page with a validation error.
    // The error message will be associated with the 'email' field and will be
    // the translated status message returned by the password broker (e.g., "We could not find a user with that email address.").
    throw ValidationException::withMessages([
      'email' => trans($status), // Use the translated error message corresponding to the failure status
    ]);
  }
}
