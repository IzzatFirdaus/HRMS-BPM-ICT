<?php

namespace App\Http\Controllers\Auth; // Ensure the namespace matches your project structure

use App\Http\Controllers\Controller; // Extend the base Controller
// Import the default redirection service provider
use App\Providers\RouteServiceProvider;
// Import necessary classes for HTTP responses and requests
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
// Import the Auth facade for accessing the authenticated user and guard validation
use Illuminate\Support\Facades\Auth;
// Import the ValidationException for handling password confirmation failures
use Illuminate\Validation\ValidationException;
// Import the View class for type hinting
use Illuminate\View\View;

// This controller handles the "Confirm Password" feature.
// It displays the form for users to re-enter their password before accessing sensitive areas
// and verifies the submitted password using Fortify's underlying actions.
// It is typically published as part of Laravel Fortify (used by Jetstream) or Laravel Breeze.

class ConfirmablePasswordController extends Controller
{
  /**
   * Show the confirm password view.
   *
   * This method displays the form where the user is prompted to
   * re-enter their current password to confirm their identity
   * before proceeding to a protected action.
   *
   * @return \Illuminate\View\View  The view containing the password confirmation form.
   */
  public function show(): View
  {
    // Returns the view for the password confirmation form.
    // Ensure you have a view file at resources/views/auth/confirm-password.blade.php
    return view('auth.confirm-password');
  }

  /**
   * Confirm the user's password.
   *
   * This method handles the submission from the password confirmation form.
   * It validates the provided password against the authenticated user's password
   * using the configured authentication guard.
   *
   * @param  \Illuminate\Http\Request  $request  The incoming request containing the password.
   * @return \Illuminate\Http\RedirectResponse  A redirect response after successful confirmation.
   * @throws \Illuminate\Validation\ValidationException  If the provided password is incorrect.
   */
  public function store(Request $request): RedirectResponse
  {
    // Validate the incoming request data.
    // Ensure the 'password' field is present and is a string.
    $request->validate([
      'password' => ['required', 'string'],
    ]);

    // Attempt to validate the provided password against the authenticated user's password
    // using the default authentication guard ('web').
    // Auth::guard('web')->validate() does not log the user in, it only checks credentials.
    // It uses the authenticated user's credentials (email or configured username) and the submitted password.
    // Fortify hooks into this method to perform the actual password verification using its `ConfirmUserPassword` action.
    if (! Auth::guard('web')->validate([
      'email' => $request->user()->email, // Use the authenticated user's email (or configured login field)
      'password' => $request->password,   // Use the password from the request input
    ])) {
      // If the password validation fails (password does not match):
      // Throw a ValidationException. This will automatically redirect the user
      // back to the form with a validation error message associated with the 'password' field.
      throw ValidationException::withMessages([
        'password' => __('auth.password'), // Use the standard translation key for password validation failure
      ]);
    }

    // If the password is confirmed successfully:
    // Store a timestamp in the user's session to mark that their password
    // has been recently confirmed. This timestamp is checked by the
    // 'password.confirm' middleware.
    $request->session()->put('auth.password_confirmed_at', time());

    // Redirect the user to their intended destination (the URL they were trying
    // to access before being redirected to the confirmation page) or fall back
    // to the default home path defined in RouteServiceProvider::HOME.
    return redirect()->intended(RouteServiceProvider::HOME);
  }
}
