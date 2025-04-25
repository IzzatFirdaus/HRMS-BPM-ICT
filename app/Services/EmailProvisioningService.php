<?php

namespace App\Services;

use App\Models\User; // Import User model
use App\Models\EmailApplication; // Import EmailApplication model
use Illuminate\Support\Str; // Import Str facade for string manipulation (e.g., slug, random)
use Illuminate\Support\Facades\Log; // For logging
use Illuminate\Support\Facades\DB; // FIX: Import DB facade for transactions
use Exception; // Import base Exception class
use Illuminate\Support\Facades\Mail; // For sending emails
use App\Mail\WelcomeEmail; // Ensure this import exists and is correct for your WelcomeEmail Mailable class

class EmailProvisioningService
{
  // Inject any dependencies needed for API interaction or other tasks

  /**
   * Generates a unique email address suggestion for a user based on naming convention.
   * This method can be used by the form component or the provisioning process.
   *
   * @param User $user The user for whom to generate the email.
   * @return string The generated email address suggestion.
   */
  public function generateEmail(User $user): string
  {
    try {
      // Basic email generation based on user's name fields (adjust based on your User model)
      // Using Str::slug for sanitization
      $baseEmail = Str::slug($user->first_name . '.' . $user->last_name, '-') . '@motac.gov.my'; // Use hyphen slug for email compatibility

      // Add a uniqueness check against existing user emails (assuming 'motac_email' column)
      $email = $baseEmail;
      $counter = 1;
      while (User::where('motac_email', $email)->exists()) {
        // If the base email exists, append a number
        $email = Str::slug($user->first_name . '.' . $user->last_name, '-') . $counter . '@motac.gov.my';
        $counter++;
      }

      return $email; // Return the unique suggestion
    } catch (Exception $e) {
      // Log the error, but still allow form to proceed if used there
      Log::error('Failed to generate email suggestion for user ' . ($user ? $user->id : 'null') . ': ' . $e->getMessage());
      // Consider returning a default or empty string if generation fails, or re-throwing depending on context
      throw $e; // Re-throw if used in a critical path like provisioning
    }
  }

  // --- The createApplication and updateApplication methods were removed from here
  // --- as they belong in the App\Services\EmailApplicationService.php file.


  /**
   * Provisions an email account for the given application using an external API.
   * This is typically called after the application has been fully approved.
   *
   * @param EmailApplication $application The email application containing the necessary data (user, proposed email, etc.).
   * @return EmailApplication The updated EmailApplication model instance after provisioning attempt.
   * @throws \Exception If provisioning fails.
   */
  public function provisionAccount(EmailApplication $application): EmailApplication
  {
    // Ensure the application is in a state ready for provisioning (e.g., 'approved')
    // This check could be done here or in the calling logic (e.g., ApprovalService).
    // if ($application->status !== 'approved') {
    //     Log::warning('Attempted to provision email for application ID ' . $application->id . ' with status ' . $application->status);
    //     throw new Exception('Application not in approved status for provisioning.');
    // }

    // Use database transaction for atomicity if multiple database operations are involved
    DB::beginTransaction(); // FIX: Added DB facade import

    try {
      // Get the user associated with the application.
      // Ensure the user relationship is eager loaded on the application model before calling this method for efficiency.
      $application->loadMissing('user'); // Load user relationship if not already loaded
      $user = $application->user;
      if (!$user) {
        DB::rollBack(); // FIX: Added DB facade import
        Log::error('Cannot provision email. EmailApplication (ID: ' . $application->id . ') does not have a user associated.');
        throw new Exception('EmailApplication does not have a user associated.');
      }

      // Determine the email address to provision.
      // Use the final assigned email if already set, otherwise use proposed or generate.
      $emailToProvision = $application->final_assigned_email ?? $application->proposed_email ?? $this->generateEmail($user);

      // Generate a temporary password for provisioning.
      // You might have specific requirements for password complexity or generation.
      $tempPassword = Str::random(12); // Generate a secure random password


      // --- Integration with External Email System API (Exchange/Google Workspace, etc.) ---
      // This is the core provisioning logic. Replace this placeholder with your actual API call implementation.
      Log::info("Attempting email API call for: {$emailToProvision} for application ID: " . $application->id);
      $apiResponse = $this->callEmailApi($emailToProvision, $tempPassword, $user); // Pass user details if needed by API wrapper
      // --- End API Integration Placeholder ---


      // Process the API response
      if (isset($apiResponse['status']) && $apiResponse['status'] === 'success') {
        // Provisioning was successful

        // Update the EmailApplication model to store the provisioned email and user ID.
        // Ensure the 'provisioned_at' column exists and is cast to datetime in the EmailApplication model.
        $application->final_assigned_email = $apiResponse['email'] ?? $emailToProvision; // Use email from API response if provided
        $application->final_assigned_user_id = $apiResponse['user_id'] ?? null; // Assuming API returns a user ID/identifier
        $application->provisioned_at = now(); // Set the timestamp
        $application->status = 'completed'; // Set application status to 'completed' after successful provisioning
        $application->rejection_reason = null; // Clear rejection reason if any
        $application->save(); // Save application changes

        // Update the related User model with the provisioned details (e.g., the primary email they will use)
        // Assuming 'motac_email' and 'user_id_assigned' columns exist on the User model.
        $user->motac_email = $application->final_assigned_email;
        $user->user_id_assigned = $application->final_assigned_user_id; // Link the external system ID to the user
        $user->save(); // Save user changes


        // Optionally, send a welcome email to the user's personal email with their new credentials.
        // Ensure sendWelcomeEmail method is correctly implemented and Mailable class exists.
        $this->sendWelcomeEmail($user, $application->final_assigned_email, $tempPassword); // Send to personal email


        DB::commit(); // FIX: Added DB facade import

        Log::info('Email provisioning successful for application ID ' . $application->id . '. Email: ' . $application->final_assigned_email);

        return $application; // Return the updated application


      } else {
        // Provisioning failed
        DB::rollBack(); // FIX: Added DB facade import

        Log::error('Email provisioning failed for application ID ' . $application->id .
          '. API response: ' . json_encode($apiResponse ?? 'No response'));

        // Update the application status to indicate failure and store rejection reason/notes if possible
        $application->status = 'provision_failed'; // Set a specific failure status
        $application->rejection_reason = $apiResponse['message'] ?? 'Unknown API error during provisioning'; // Store API error message
        $application->save(); // Save application changes

        // Include API error message in the exception if available
        $errorMessage = $apiResponse['message'] ?? 'Unknown API error';
        throw new Exception('Email provisioning failed for application ' . $application->id . ': ' . $errorMessage);
      }
    } catch (Exception $e) {
      // Catch any exceptions during the process (including API call errors)
      // Check if transaction is active before rolling back
      if (DB::transactionLevel() > 0) { // FIX: Added DB facade import
        DB::rollBack(); // FIX: Added DB facade import
      }

      Log::error('Error provisioning email account for application ID ' . ($application->id ?? 'unknown') . ': ' . $e->getMessage());

      // Update the application status to indicate failure if not already done
      // This check prevents overwriting a specific 'provision_failed' status set earlier in the catch block
      if ($application && $application->status !== 'provision_failed') {
        $application->status = 'provision_failed';
        // Optionally add a generic error message if no specific API message was captured
        if (empty($application->rejection_reason)) {
          $application->rejection_reason = 'An unexpected error occurred during provisioning.';
        }
        $application->save();
      }

      throw $e; // Re-throw the exception for the calling logic to handle
    }
  }

  /**
   * Placeholder for the actual API call to the external email system.
   * This method needs to be implemented based on your specific email service API (e.g., Microsoft Exchange, Google Workspace).
   *
   * @param string $email The email address to provision.
   * @param string $password The initial password for the account.
   * @param User $user The user model (may contain details needed for the API call).
   * @return array An array representing the API response structure (status, message, provisioned email, user ID from external system).
   */
  private function callEmailApi(string $email, string $password, User $user): array
  {
    // --- IMPORTANT: Replace this placeholder with your actual API integration logic ---
    // This is a simulation for demonstration purposes.

    Log::info("Simulating external email API call for: {$email} for user: {$user->id}");

    // Simulate API authentication, request building, error handling, etc.

    // Example Simulation:
    // Simulate success for emails containing 'test-success' or 'provision-ok'
    if (Str::contains($email, 'test-success') || Str::contains($email, 'provision-ok')) {
      return [
        'status' => 'success',
        'message' => 'Email account created successfully (simulated).',
        'email' => $email, // Return the email address that was provisioned
        'user_id' => 'external_id_' . Str::before($email, '@'), // Simulate assigning an external system user ID
      ];
    }

    // Simulate failure for emails containing 'test-fail' or 'provision-error'
    if (Str::contains($email, 'test-fail') || Str::contains($email, 'provision-error')) {
      return [
        'status' => 'error',
        'message' => 'Simulated API error: Account creation failed.',
        'error_code' => 'SIM_ERR_001', // Optional error code
      ];
    }


    // Default simulation: success
    return [
      'status' => 'success',
      'message' => 'Email account created successfully (simulated - default).',
      'email' => $email,
      'user_id' => 'external_id_' . Str::random(8),
    ];

    // --- End Simulation Placeholder ---
  }

  /**
   * Sends a welcome email to the user's personal email with their new MOTAC email credentials.
   *
   * @param User $user The user model (should have personal_email attribute).
   * @param string $motacEmail The provisioned MOTAC email address.
   * @param string $password The initial password for the account.
   * @return void
   */
  private function sendWelcomeEmail(User $user, string $motacEmail, string $password): void
  {
    try {
      // Use Laravel's Mail facade to send the email to the user's PERSONAL email.
      // Ensure you have a Mailable class named 'WelcomeEmail' that is imported correctly.
      // This Mailable should accept the User instance, the new MOTAC email, and the initial password.
      if ($user->personal_email && filter_var($user->personal_email, FILTER_VALIDATE_EMAIL)) {
        Mail::to($user->personal_email)->send(new WelcomeEmail($user, $motacEmail, $password));
        Log::info('Welcome email sent to ' . $user->personal_email . ' after email provisioning for user ID: ' . $user->id);
      } else {
        Log::warning('Skipped sending welcome email for user ' . $user->id . '. Personal email is missing or invalid.');
        // Optionally, notify an admin or applicant via another channel if the welcome email cannot be sent
      }
    } catch (Exception $e) {
      Log::error('Failed to send welcome email to user ' . ($user->id ?? 'unknown') . ': ' . $e->getMessage());
      // Decide if you want to throw this exception (halting the process) or just log it.
      // Throwing might be appropriate if sending credentials is a critical step.
      throw $e; // Re-throw the exception
    }
  }
}
