<?php

namespace App\Http\Controllers\Auth; // Ensure the namespace matches your project structure

use App\Http\Controllers\Controller; // Extend the base Controller
// Import the UpdateUserPassword action provided by Laravel Fortify (if using Fortify)
// This action contains the core logic for updating a user's password.
// use App\Actions\Fortify\UpdateUserPassword; // Uncomment if you intend to use Fortify's action

// Import necessary classes for HTTP responses and requests
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
// Import the Auth facade (though less needed if updating the authenticated user directly)
use Illuminate\Support\Facades\Auth;
// Import the Hash facade for hashing the new password
use Illuminate\Support\Facades\Hash;
// Import the ValidationException for handling validation failures
use Illuminate\Validation\ValidationException;
// Import the Rule class for validation rules like 'current_password'
use Illuminate\Validation\Rule;


// This controller is typically responsible for allowing an authenticated user
// to update their own password from their profile or settings page.
// It is distinct from the password reset flow (PasswordResetLinkController, NewPasswordController)
// used when a user has forgotten their password.
// In modern Laravel with Fortify, this controller often orchestrates validation
// and then defers the core logic of updating the password to Fortify's UpdateUserPassword action.

class PasswordController extends Controller
{
  /**
   * Update the authenticated user's password.
   *
   * This method handles the PUT request typically submitted from a form on the user's
   * profile or settings page. It validates the current password and the new password,
   * then updates the user's password in the database.
   *
   * @param  \Illuminate\Http\Request  $request  The incoming request containing 'current_password', 'password', and 'password_confirmation'.
   * @return \Illuminate\Http\RedirectResponse  A redirect response after the update attempt.
   * @throws \Illuminate\Validation\ValidationException  If validation fails (e.g., incorrect current password, new password validation rules not met).
   */
  public function update(Request $request): RedirectResponse
  {
    // 1. Validate the incoming request data.
    // This validation ensures:
    // - 'current_password' is provided and matches the authenticated user's current password.
    //   The 'current_password' rule automatically checks this against the logged-in user.
    // - 'password' is provided, is a string, meets a minimum length (e.g., 8 characters),
    //   and matches the 'password_confirmation' field ('confirmed' rule).
    // We use `validateWithBag('updatePassword', ...)` which is often preferred
    // when integrating with front-end frameworks like Livewire or Inertia
    // to put validation errors into a specific named bag.
    $validated = $request->validateWithBag('updatePassword', [
      'current_password' => ['required', 'string', 'current_password'], // 'current_password' rule verifies against the authenticated user's password
      'password' => ['required', 'string', 'min:8', 'confirmed'], // New password must be required, string, min 8, and match password_confirmation
    ]);

    // 2. Update the authenticated user's password.
    // In a standard Laravel Fortify setup, the core logic for updating the password
    // is contained within the `App\Actions\Fortify\UpdateUserPassword` action.
    // The recommended approach is to resolve and call this action:

    // $updater = app(UpdateUserPassword::class); // Resolve the Fortify action from the service container
    // $updater->update($request->user(), $validated); // Call the action, passing the authenticated user and validated data

    // Alternative: Manually update the password if not fully relying on Fortify's action here.
    // This assumes the 'current_password' validation rule was sufficient to verify the old password.
    $request->user()->forceFill([
      'password' => Hash::make($validated['password']), // Hash the new password using the default hasher
    ])->save(); // Save the changes to the user model


    // 3. Redirect the user back to the page they submitted the form from.
    // This is common for profile/settings update forms.
    // Include a session flash message with a status key 'password-updated'.
    // This status can be checked in the view to display a success notification (e.g., "Password updated successfully!").
    return back()->with('status', 'password-updated');
  }

  // Note: This controller typically only contains the 'update' method for authenticated users.
  // The methods related to initiating and completing a password reset for forgotten passwords
  // are handled by the PasswordResetLinkController and NewPasswordController.
}
