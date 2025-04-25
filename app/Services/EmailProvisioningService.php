<?php

namespace App\Services;

use App\Models\User;
use App\Models\EmailApplication;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Exception;
use Illuminate\Support\Facades\Mail;
use App\Mail\WelcomeEmail; // Ensure this import exists and is correct

class EmailProvisioningService
{
  /**
   * Generates an email address for a user.
   *
   * @param User $user The user for whom to generate the email.
   * @return string The generated email address.
   */
  public function generateEmail(User $user): string
  {
    try {
      // Basic email generation based on first/last name
      // This might need adjustment based on your User model's name fields
      $email = Str::slug($user->first_name . '.' . $user->last_name) . '@motac.gov.my';

      // Consider adding a check for email uniqueness here. If the generated
      // email already exists, you might want to add a number to it.
      $originalEmail = $email;
      $counter = 1;
      // Assuming 'motac_email' is the column where generated emails are stored
      while (User::where('motac_email', $email)->exists()) {
        // Adjust based on your User model's name fields
        $email = Str::slug($user->first_name . '.' . $user->last_name) . $counter . '@motac.gov.my';
        $counter++;
      }

      return $email;
    } catch (Exception $e) {
      Log::error('Failed to generate email for user ' . ($user ? $user->id : 'null') . ': ' . $e->getMessage());
      throw $e; // Re-throw the exception
    }
  }

  /**
   * Creates a new email application record.
   * Modified to accept the User instance as the applicant.
   *
   * @param User $user The user submitting the application.
   * @param array $data The validated data from the store request.
   * @return \App\Models\EmailApplication The newly created EmailApplication model instance.
   */
  public function createApplication(User $user, array $data): EmailApplication
  {
    try {
      // Create a new EmailApplication instance.
      $application = new EmailApplication();
      $application->user_id = $user->id; // Use the passed User instance ID
      $application->service_status = $data['service_status'];
      $application->purpose = $data['purpose'] ?? null; // Purpose is optional based on service status
      $application->proposed_email = $data['proposed_email'] ?? null; // Proposed email is optional
      $application->certification = $data['certification'];
      $application->status = 'pending'; // Set initial status
      // Add other fields if necessary, e.g., submission timestamp is handled by Eloquent

      $application->save();

      // You might trigger notifications or initial workflow steps here

      return $application;
    } catch (Exception $e) {
      Log::error('Failed to create email application for user ' . $user->id . ': ' . $e->getMessage());
      throw $e; // Re-throw
    }
  }

  /**
   * Updates an existing email application record.
   * Added this method to address the 'updateApplication' error.
   *
   * @param EmailApplication $application The application instance to update.
   * @param array $data The validated data for the update.
   * @return bool True on success, false on failure.
   */
  public function updateApplication(EmailApplication $application, array $data): bool
  {
    try {
      // Update the application attributes with validated data.
      // Be careful which fields you allow to be updated after creation/submission.
      $updated = $application->update($data);

      // You might trigger notifications or other workflow changes here

      return $updated; // Returns true if updated, false otherwise
    } catch (Exception $e) {
      Log::error('Failed to update email application ID ' . $application->id . ': ' . $e->getMessage());
      throw $e; // Re-throw
    }
  }


  /**
   * Provisions an email account using an external API (e.g., Exchange, Google Workspace).
   *
   * @param EmailApplication $application The email application containing the necessary data.
   * @return bool True on success, false on failure.  Consider returning more detailed information.
   */
  public function provisionAccount(EmailApplication $application)
  {
    try {
      // Get the user.
      // Eager load the user relationship on the application model before calling this method.
      $user = $application->user;
      if (!$user) {
        Log::error('Cannot provision email. EmailApplication (ID: ' . $application->id . ') does not have a user associated.');
        throw new Exception('EmailApplication does not have a user associated.');
      }

      // Use the email from the application if proposed, otherwise generate
      $email = $application->proposed_email ?? $this->generateEmail($user);

      // Generate a temporary password for provisioning
      $tempPassword = Str::random(12); // Generate a secure random password

      //  Integration with Exchange/Google Workspace API. This is a placeholder.
      //  ----------------------------------------------------------------------
      $apiResponse = $this->callEmailApi($email, $tempPassword); //  Make the API call

      if (isset($apiResponse['status']) && $apiResponse['status'] === 'success') {
        // Update the EmailApplication model to store the provisioned email and user ID.
        // Also update the User model with the assigned MOTAC email and User ID.
        $application->final_assigned_email = $apiResponse['email'] ?? $email; // Use email from API response if provided
        $application->final_assigned_user_id = $apiResponse['user_id'] ?? null; // Assuming API returns user ID
        $application->provisioned_at = now(); // Set the timestamp.
        $application->status = 'provisioned'; // Set a status.
        $application->save();

        // Update the related User model with the provisioned details
        $user->motac_email = $application->final_assigned_email;
        $user->user_id_assigned = $application->final_assigned_user_id;
        $user->save();


        // Optionally, send a welcome email to the user's personal email with credentials.
        $this->sendWelcomeEmail($user, $application->final_assigned_email, $tempPassword); // Send to personal email

        return true;
      } else {
        Log::error('Email provisioning failed for application ID ' . $application->id .
          '. API response: ' . json_encode($apiResponse ?? 'No response'));
        $application->status = 'provision_failed'; // Set a specific failure status
        $application->save();
        // Include API error message in the exception if available
        $errorMessage = $apiResponse['message'] ?? 'Unknown API error';
        throw new Exception('Email provisioning failed: ' . $errorMessage);
      }
      //  ----------------------------------------------------------------------
    } catch (Exception $e) {
      Log::error('Error provisioning email account: ' . $e->getMessage());
      // Consider updating the application status to indicate failure if not already set
      if (!isset($application->status) || $application->status !== 'provision_failed') {
        $application->status = 'provision_failed';
        $application->save();
      }
      throw $e; // Re-throw
    }
  }

  /**
   * Placeholder for the actual API call to the email system.
   *
   * @param string $email The email address to provision.
   * @param string $password The initial password for the account.
   * @return array An array representing the API response (replace with actual data).
   */
  private function callEmailApi(string $email, string $password): array
  {
    //  Replace this with your actual API call logic. This is a simulation.
    //  Important: Handle authentication, error handling, and data formatting
    //  according to the API documentation of Exchange or Google Workspace.

    Log::info("Simulating email API call for: {$email}");

    // Simulate a successful response
    if (Str::contains($email, 'success')) { // Simulate success based on email string
      return [
        'status' => 'success',
        'message' => 'Email account created successfully (simulated).',
        'email' => $email,
        'user_id' => Str::before($email, '@') // Simulate assigning user ID
      ];
    }

    // Simulate a failed response
    return [
      'status' => 'error',
      'message' => 'Failed to create email account. (Simulated error.)',
    ];
  }

  /**
   * Sends a welcome email to the user after their email account is provisioned.
   * Updated to send to personal email and include credentials.
   *
   * @param User $user The user.
   * @param string $motacEmail The provisioned MOTAC email address.
   * @param string $password The initial password.
   * @return void
   */
  private function sendWelcomeEmail(User $user, string $motacEmail, string $password): void
  {
    try {
      // Use Laravel's Mail facade to send the email to the user's PERSONAL email.
      // Ensure the WelcomeEmail Mailable accepts user, email, and password.
      if ($user->personal_email) {
        Mail::to($user->personal_email)->send(new WelcomeEmail($user, $motacEmail, $password));
        Log::info('Welcome email sent to ' . $user->personal_email . ' after email provisioning.');
      } else {
        Log::warning('Skipped sending welcome email for user ' . $user->id . '. Personal email is missing.');
        // Optionally, notify an admin that the welcome email could not be sent
      }
    } catch (Exception $e) {
      Log::error('Failed to send welcome email to ' . ($user->personal_email ?? 'user ID ' . $user->id) . ': ' . $e->getMessage());
      // Consider if you want to throw this error or just log it.
    }
  }
}
