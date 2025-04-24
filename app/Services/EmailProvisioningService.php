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
      // Generate the email address.
      $email = Str::slug($user->first_name . '.' . $user->last_name) . '@motac.gov.my';

      // Consider adding a check for email uniqueness here.  If the generated
      // email already exists, you might want to add a number to it (e.g.,
      // john.doe1@example.com, john.doe2@example.com).  This is important
      // to prevent errors when creating user accounts.
      // Example (requires a database query):
      $originalEmail = $email;
      $counter = 1;
      while (User::where('email', $email)->exists()) {
        $email = Str::slug($user->first_name . '.' . $user->last_name) . $counter . '@motac.gov.my';
        $counter++;
      }

      return $email;
    } catch (Exception $e) {
      Log::error('Failed to generate email for user ' . ($user ? $user->id : 'null') . ': ' . $e->getMessage());
      // It's crucial to handle exceptions.  You might want to:
      // 1.  Throw the exception to be handled by a higher level (e.g., controller).
      // 2.  Return a default value (less preferred, but possible).
      // 3.  Return null or false, and handle that in the caller.
      throw $e; // Re-throw the exception so that it's handled by caller
    }
  }

  /**
   * Creates a new email application record.
   *
   * @param array $data The validated data from the store request.
   * @return \App\Models\EmailApplication The newly created EmailApplication model instance.
   */
  public function createApplication(array $data): EmailApplication
  {
    try {
      // Assuming the user is authenticated and their ID is available.
      $userId = auth()->id();

      // Create a new EmailApplication instance.
      $application = new EmailApplication();
      $application->user_id = $userId;
      $application->service_status = $data['service_status'];
      $application->purpose = $data['purpose'] ?? null; // Purpose is optional based on service status
      $application->proposed_email = $data['proposed_email'] ?? null; // Proposed email is optional
      $application->certification = $data['certification'];
      $application->status = 'pending'; // Set initial status
      $application->save();

      return $application;
    } catch (Exception $e) {
      Log::error('Failed to create email application for user ' . auth()->id() . ': ' . $e->getMessage());
      throw $e;
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
      //  Get the user.
      $user = $application->user;
      if (!$user) {
        Log::error('Cannot provision email.  EmailApplication (ID: ' . $application->id . ') does not have a user associated.');
        throw new Exception('EmailApplication does not have a user associated.');
      }
      $email = $this->generateEmail($user);

      //  Integration with Exchange/Google Workspace API.  This is where the
      //  core logic of your service goes.  The example below is a placeholder.
      //  You'll need to adapt it to the specific API you're using.
      //  For example, you might use a library like:
      //  -   For Microsoft Exchange:  php-ews (https://github.com/jamesiarmes/php-ews)
      //  -   For Google Workspace:  google/apiclient (https://github.com/googleapis/google-api-php-client)
      //
      //  The actual API calls will involve things like:
      //  -   Authenticating to the API.
      //  -   Creating a new mailbox (user) in the email system.
      //  -   Setting the initial password.
      //  -   Configuring any necessary settings.
      //
      //  Here's a VERY simplified placeholder example (replace with REAL API calls):
      //  ----------------------------------------------------------------------
      $apiResponse = $this->callEmailApi($email, 'password'); //  Make the API call

      if ($apiResponse['status'] === 'success') {
        // Update the EmailApplication model to store the provisioned email.
        $application->email = $email;
        $application->provisioned_at = now(); //  Set the timestamp.
        $application->status = 'provisioned'; //  Set a status.
        $application->save();

        // Optionally, send a welcome email to the user.
        $this->sendWelcomeEmail($user, $email, 'password'); //  Call sendWelcomeEmail
        return true;
      } else {
        Log::error('Email provisioning failed for application ID ' . $application->id .
          '. API response: ' . json_encode($apiResponse));
        $application->status = 'failed';
        $application->save();
        throw new Exception('Email provisioning failed: ' . $apiResponse['message']);
      }
      //  ----------------------------------------------------------------------
      //  End of placeholder.
      return true; //  Means Success
    } catch (Exception $e) {
      Log::error('Error provisioning email account: ' . $e->getMessage());
      //  Handle the exception appropriately (rethrow, return false, etc.).
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
    //  Replace this with your actual API call logic.  This is just a simulation.
    //  Important:  Handle authentication, error handling, and data formatting
    //  according to the API documentation of Exchange or Google Workspace.

    // Simulate a successful response
    if (strpos($email, 'success') !== false) { // Simulate
      return [
        'status' => 'success',
        'message' => 'Email account created successfully.',
        'email' => $email,
      ];
    }

    // Simulate a failed response
    return [
      'status' => 'error',
      'message' => 'Failed to create email account.  (Simulated error.)',
    ];
  }

  /**
   * Sends a welcome email to the user after their email account is provisioned.
   *
   * @param User $user The user.
   * @param string $email The provisioned email address.
   * @param string $password The initial password.
   * @return void
   */
  private function sendWelcomeEmail(User $user, string $email, string $password): void
  {
    try {
      // Use Laravel's Mail facade to send the email.
      Mail::to($user->email)->send(new WelcomeEmail($user, $email, $password)); //  Adjust Mail class
      Log::info('Welcome email sent to ' . $user->email . ' after email provisioning.');
    } catch (Exception $e) {
      Log::error('Failed to send welcome email to ' . $user->email . ': ' . $e->getMessage());
      //  Consider if you want to throw this error or just log it.  If the
      //  email sending fails, but the account was provisioned, it might not
      //  be a critical error.
    }
  }
}
