<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
// Import the custom LoginRequest Form Request provided by Laravel Fortify (or Breeze)
use App\Http\Requests\Auth\LoginRequest;
// Import the default redirection service provider
use App\Providers\RouteServiceProvider;
// Import necessary classes for HTTP responses and requests
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
// Import the Auth facade for authentication operations
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View; // Import the View class for type hinting
use Illuminate\Support\Facades\Log; // Import Log facade for logging

// This controller handles the authentication session lifecycle: login and logout.
// It is typically published as part of Laravel Fortify (which is used by Jetstream) or Laravel Breeze.
// It relies on Fortify's underlying actions and requests for the core authentication logic.

class AuthenticatedSessionController extends Controller
{
  /**
   * Display the login view.
   *
   * This method shows the form that users fill out to log in.
   * The view name ('auth.login') is configurable in Fortify's configuration.
   *
   * @return \Illuminate\View\View
   */
  public function create(): View
  {
    // Returns the view containing the login form.
    // Ensure you have a view file at resources/views/auth/login.blade.php
    return view('auth.login');
  }

  /**
   * Handle an incoming authentication request.
   *
   * This method receives the submitted login form data via a custom
   * Form Request (`LoginRequest`) which handles validation and the
   * authentication attempt using Fortify's actions.
   *
   * @param  \App\Http\Requests\Auth\LoginRequest  $request  The validated login request.
   * @return \Illuminate\Http\RedirectResponse  A redirect response after successful authentication.
   */
  public function store(LoginRequest $request): RedirectResponse
  {
    // The `authenticate()` method on the Fortify-provided LoginRequest
    // performs the validation and delegates the authentication attempt
    // to Fortify's `AttemptToAuthenticate` action. If authentication fails,
    // an exception is thrown, and Laravel's validation handling redirects back
    // with errors automatically.
    //
    // CUSTOMIZATION NOTE: If you need to enforce specific checks *after*
    // credentials are valid but *before* login (e.g., user must be 'active'),
    // you should typically implement this logic within the `LoginRequest`'s
    // `authenticate()` method itself or modify Fortify's authentication pipeline.
    // Do NOT add Auth::user()->status check *after* $request->authenticate()
    // because the user session is not fully established until after this method completes.
    $request->authenticate();

    // Regenerate the session ID to prevent session fixation attacks.
    $request->session()->regenerate();

    // Optional: Log successful login attempt
    if (Auth::check()) {
      Log::info('User successfully logged in.', [
        'user_id' => Auth::id(),
        'ip_address' => $request->ip(),
        'user_agent' => $request->header('User-Agent'),
      ]);
    }


    // Redirect the user to their intended destination (the page they tried to access before login)
    // or fall back to the default home path defined in RouteServiceProvider::HOME
    // (commonly '/dashboard').
    return redirect()->intended(RouteServiceProvider::HOME);
  }

  /**
   * Destroy an authenticated session (log the user out).
   *
   * This method logs the currently authenticated user out of the application.
   * It invalidates the session and regenerates the CSRF token for security.
   *
   * @param  \Illuminate\Http\Request  $request  The incoming request.
   * @return \Illuminate\Http\RedirectResponse  A redirect response after logout.
   */
  public function destroy(Request $request): RedirectResponse
  {
    // Optional: Log logout attempt/success
    if (Auth::check()) {
      Log::info('User logging out.', [
        'user_id' => Auth::id(),
        'ip_address' => $request->ip(),
        'user_agent' => $request->header('User-Agent'),
      ]);
    }

    // Log the user out from the default web guard.
    Auth::guard('web')->logout();

    // Invalidate the user's session. This clears all session data.
    $request->session()->invalidate();

    // Regenerate the CSRF token. This helps protect against CSRF attacks
    // on subsequent requests after logging out.
    $request->session()->regenerateToken();

    // Redirect the user after logging out.
    // Commonly redirects to the application's root URL ('/') or the login page ('/login').
    return redirect('/'); // Redirect to the homepage or landing page

    // Alternatively, to redirect specifically to the login page:
    // return redirect()->route('login');
  }
}
