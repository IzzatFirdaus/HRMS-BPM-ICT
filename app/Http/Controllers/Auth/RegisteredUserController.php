<?php

namespace App\Http\Controllers\Auth; // Ensure the namespace matches your project structure

use App\Http\Controllers\Controller; // Extend the base Controller
// Import the User model
use App\Models\User;
// Import the default redirection service provider
use App\Providers\RouteServiceProvider;
// Import the Registered event, fired after a user is registered
use Illuminate\Auth\Events\Registered;
// Import necessary classes for HTTP responses and requests
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request; // Import base Request if needed, though Form Request is preferred for store
// Import the Auth facade for logging in the new user
use Illuminate\Support\Facades\Auth;
// Import the Hash facade (less needed if using Fortify action for hashing)
use Illuminate\Support\Facades\Hash;
// Import common validation rules, like the Password rule
use Illuminate\Validation\Rules;
// Import the View class for type hinting the create method
use Illuminate\View\View;

// Import Fortify's action and request (assuming Fortify setup)
// The CreateNewUser action contains the core logic for creating a user record.
use App\Actions\Fortify\CreateNewUser;
// The NewUserRequest is a custom Form Request provided by Fortify/Breeze for validation.
// Note: The exact name might be just 'RegisterRequest' in some setups.
use App\Http\Requests\NewUserRequest;


// This controller handles the user registration process.
// It displays the registration form and handles the submission of that form
// to create a new user account in the database and log the user in.
// It is typically published as part of Laravel Fortify (used by Jetstream) or Laravel Breeze.
// It often delegates the core user creation logic and validation to Fortify's
// dedicated action and Form Request.

class RegisteredUserController extends Controller
{
  /**
   * Display the registration view.
   *
   * This method shows the form that new users fill out to register for an account.
   *
   * @return \Illuminate\View\View  The view containing the registration form.
   */
  public function create(): View
  {
    // Returns the view for the user registration form.
    // Ensure you have a view file at resources/views/auth/register.blade.php
    return view('auth.register');
  }

  /**
   * Handle an incoming registration request.
   *
   * This method handles the POST submission from the registration form.
   * It uses a custom Form Request (`NewUserRequest` or `RegisterRequest`)
   * provided by Fortify/Breeze to automatically validate the incoming data
   * based on the rules defined in the Form Request.
   * After successful validation, it calls Fortify's `CreateNewUser` action
   * to perform the actual user creation in the database and then logs the
   * newly created user in.
   *
   * @param  \App\Http\Requests\NewUserRequest  $request  The validated incoming registration request.
   * (Note: If your Fortify setup uses a different request name, update this type hint)
   * @return \Illuminate\Http\RedirectResponse  A redirect response after successful registration and login.
   */
  public function store(NewUserRequest $request): RedirectResponse
  {
    // The `NewUserRequest` Form Request (or whatever your Fortify setup uses)
    // handles the validation of the incoming request data automatically
    // before it reaches this method. The validated data is available via
    // `$request->validated()`.

    // Use Fortify's `CreateNewUser` action to perform the core user creation logic.
    // This action is responsible for:
    // - Creating the User model instance.
    // - Hashing the password.
    // - Saving the user record to the database.
    // - Potentially performing any other necessary steps after user creation.
    $creator = app(CreateNewUser::class); // Resolve the CreateNewUser action from the Laravel service container

    // Call the `create` method on the resolved action, passing all the validated request data.
    // The action returns the newly created User model instance.
    $user = $creator->create($request->all()); // Pass all request data or $request->validated()

    // Fire the `Registered` event.
    // Laravel has built-in listeners for this event, such as one that sends
    // the email verification notification if your User model implements
    // the `Illuminate\Contracts\Auth\MustVerifyEmail` interface.
    event(new Registered($user));

    // Log the newly created user into the application immediately after registration.
    // This uses the default authentication guard ('web').
    Auth::login($user);

    // Redirect the user to the home page (or their intended destination before registration)
    // after they have successfully registered and been logged in.
    // RouteServiceProvider::HOME is typically '/dashboard'.
    return redirect(RouteServiceProvider::HOME);
  }
}
