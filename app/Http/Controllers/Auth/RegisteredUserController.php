<?php

namespace App\Http\Controllers\Auth; // Ensure the namespace matches your project structure

use App\Http\Controllers\Controller; // Extend the base Controller
// Import the User model (though less needed if creation logic is in the action)
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
// Import common validation rules, like the Password rule (less needed if using Form Request)
use Illuminate\Validation\Rules;
// Import the View class for type hinting the create method
use Illuminate\View\View;
use Illuminate\Support\Facades\Log; // Import Log facade for logging

// Import Fortify's action and request (assuming Fortify setup)
// The CreateNewUser action contains the core logic for creating a user record.
use App\Actions\Fortify\CreateNewUser;
// The NewUserRequest is a custom Form Request provided by Fortify/Breeze for validation.
// Note: The exact name might be just 'RegisterRequest' in some setups.
use App\Http\Requests\NewUserRequest; // <<< Verify this Form Request name is correct


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
   * @return \Illuminate\View\View The view containing the registration form.
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
   * @param  \App\Http\Requests\NewUserRequest  $request The validated incoming registration request.
   * (Note: Verify that 'NewUserRequest' is the correct Form Request name for your setup)
   * @return \Illuminate\Http\RedirectResponse A redirect response after successful registration and login.
   */
  public function store(NewUserRequest $request): RedirectResponse // Form Request handles validation
  {
    // The `NewUserRequest` Form Request (or whatever your Fortify setup uses)
    // handles the validation of the incoming request data automatically
    // before it reaches this method. The validated data is available via
    // `$request->validated()`.
    //
    // The logic for mapping registration form fields (e.g., full_name, nric, etc.)
    // to the User model fields and hashing the password should be contained
    // within the `App\Actions\Fortify\CreateNewUser` action's `create` method
    // and the validation rules in the `NewUserRequest` Form Request.

    // Use Fortify's `CreateNewUser` action to perform the core user creation logic.
    $creator = app(CreateNewUser::class); // Resolve the CreateNewUser action from the service container

    // Call the `create` method on the resolved action, passing the request data.
    // The action returns the newly created User model instance.
    // Passing $request->all() or $request->validated() is common here.
    $user = $creator->create($request->all()); // <<< Ensure CreateNewUser action correctly handles this data


    // Fire the `Registered` event.
    // Laravel has built-in listeners for this event, such as one that sends
    // the email verification notification if your User model implements
    // the `Illuminate\Contracts\Auth\MustVerifyEmail` interface.
    event(new Registered($user));

    // Log the newly created user into the application immediately after registration.
    // This uses the default authentication guard ('web').
    Auth::login($user);

    // Optional: Log successful registration and login
    Log::info('New user registered and logged in.', [
      'user_id' => $user->id, // Log the ID of the newly created user
      'email' => $user->email, // Log the user's email (or login credential field)
      'ip_address' => $request->ip(),
    ]);


    // Redirect the user to the home page (or their intended destination before registration)
    // after they have successfully registered and been logged in.
    // RouteServiceProvider::HOME is typically '/dashboard'.
    return redirect(RouteServiceProvider::HOME);

    // If you want to redirect with a success message, you can add ->with('status', 'registered') or similar.
    // return redirect(RouteServiceProvider::HOME)->with('status', 'registered');
  }
}
