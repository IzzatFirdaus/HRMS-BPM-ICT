<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
// Import the custom LoginRequest Form Request provided by Laravel Fortify
use App\Http\Requests\Auth\LoginRequest;
// Import the default redirection service provider
use App\Providers\RouteServiceProvider;
// Import necessary classes for HTTP responses and requests
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
// Import the Auth facade for authentication operations
use Illuminate\Support\Facades\Auth;
// Import the View class for type hinting
use Illuminate\View\View;

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
    $request->authenticate();

    // Regenerate the session ID to prevent session fixation attacks.
    $request->session()->regenerate();

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
    // Log the user out from the default web guard.
    // Fortify's logout action is typically handled by this core Laravel call.
    Auth::guard('web')->logout();

    // Invalidate the user's session. This clears all session data.
    $request->session()->invalidate();

    // Regenerate the CSRF token. This helps protect against CSRF attacks
    // on subsequent requests after logging out.
    $request->session()->regenerateToken();

    // Redirect the user after logging out.
    // Commonly redirects to the application's root URL ('/') or the login page ('/login').
    return redirect('/'); // Redirect to the homepage
    // Alternatively, to redirect to the login page:
    // return redirect()->route('login');
  }
}
