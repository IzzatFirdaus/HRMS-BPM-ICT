<?php

namespace App\Http\Controllers\Auth; // Ensure the namespace matches your project structure

use App\Http\Controllers\Controller; // Extend the base Controller
// Import the UpdateUserPassword action provided by Laravel Fortify (if using Fortify)
// This action contains the core logic for updating a user's password.
use App\Actions\Fortify\UpdateUserPassword; // Uncomment and ensure this path is correct

// Import necessary classes for HTTP responses and requests
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
// Import the Auth facade (useful for logging user ID)
use Illuminate\Support\Facades\Auth;
// Import the Hash facade (less needed if using Fortify action, but good for manual alternative)
use Illuminate\Support\Facades\Hash;
// Import the ValidationException for handling validation failures (less needed with validateWithBag, but good to keep)
use Illuminate\Validation\ValidationException; // <<< UNCOMMENTED THIS LINE
// Import the Rule class for validation rules like 'current_password'
use Illuminate\Validation\Rule; // Needed for Rule::current_password if not inlined
use Illuminate\Support\Facades\Log; // Import Log facade for logging


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
   * then updates the user's password in the database using the Fortify action.
   *
   * @param  \Illuminate\Http\Request  $request  The incoming request containing 'current_password', 'password', and 'password_confirmation'.
   * @param  \App\Actions\Fortify\UpdateUserPassword  $updater  The Fortify UpdateUserPassword action injected by the service container.
   * @return \Illuminate\Http\RedirectResponse  A redirect response after the update attempt.
   * @throws \Illuminate\Validation\ValidationException  If validation fails (handled by validateWithBag).
   */
  // Inject the Fortify action into the method signature
  public function update(Request $request, UpdateUserPassword $updater): RedirectResponse
  {
    // Log the attempt to update password
    Log::info('Attempting to update password for authenticated user.', [
      'user_id' => Auth::id(), // Log the ID of the authenticated user
      'ip_address' => $request->ip(),
    ]);

    // 1. Validate the incoming request data.
    // This validation ensures:
    // - 'current_password' is provided and matches the authenticated user's current password.
    //   The 'current_password' rule automatically checks this against the logged-in user.
    // - 'password' is provided, is a string, meets a minimum length (e.g., 8 characters),
    //   and matches the 'password_confirmation' field ('confirmed' rule).
    // We use `validateWithBag('updatePassword', ...)` which is often preferred
    // when integrating with front-end frameworks like Livewire or Inertia
    // to put validation errors into a specific named bag.
    try {
      $validated = $request->validateWithBag('updatePassword', [
        'current_password' => ['required', 'string', Rule::currentPassword()], // Use Rule::currentPassword() for clarity
        'password' => ['required', 'string', 'min:8', 'confirmed'],
      ]);

      // 2. Update the authenticated user's password using the Fortify action.
      // The core logic for updating the password (hashing, saving) is
      // contained within the `App\Actions\Fortify\UpdateUserPassword` action.
      $updater->update($request->user(), $validated);

      // Log successful password update
      Log::info('Password successfully updated for user.', [
        'user_id' => Auth::id(),
        'ip_address' => $request->ip(),
      ]);
    } catch (ValidationException $e) {
      // Log failed password update attempt due to validation (e.g., incorrect current password)
      Log::warning('Password update validation failed for user.', [
        'user_id' => Auth::id(),
        'ip_address' => $request->ip(),
        'errors' => $e->errors(), // Log the validation errors
      ]);
      // Re-throw the exception so validateWithBag can handle redirecting with errors
      throw $e;
    } catch (\Exception $e) {
      // Log any other unexpected errors during password update
      Log::error('An unexpected error occurred during password update for user.', [
        'user_id' => Auth::id(),
        'ip_address' => $request->ip(),
        'error' => $e->getMessage(),
      ]);
      // You might want to return an error response or redirect with a general error message here
      // For now, just re-throw or handle as appropriate for your application's error handling
      throw $e; // Re-throwing to indicate failure
    }


    // 3. Redirect the user back to the page they submitted the form from.
    // This is common for profile/settings update forms.
    // Include a session flash message with a status key 'password-updated'.
    // This status can be checked in the view to display a success notification (e.g., "Password updated successfully!").
    // The message associated with 'password-updated' should be translated in your lang files.
    return back()->with('status', 'password-updated');
  }

  // Alternative: Manually update the password if NOT using Fortify's action.
  // This assumes the 'current_password' validation rule was sufficient to verify the old password.
  // public function updateManually(Request $request): RedirectResponse
  // {
  //     $validated = $request->validateWithBag('updatePassword', [
  //         'current_password' => ['required', 'string', Rule::currentPassword()],
  //         'password' => ['required', 'string', 'min:8', 'confirmed'],
  //     ]);

  //     // Manually update the password
  //     $request->user()->forceFill([
  //         'password' => Hash::make($validated['password']), // Hash the new password
  //     ])->save(); // Save the changes

  //     Log::info('Password manually updated for user.', [
  //         'user_id' => Auth::id(),
  //         'ip_address' => $request->ip(),
  //     ]);

  //     return back()->with('status', 'password-updated');
  // }

}
