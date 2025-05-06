<?php

namespace App\Services;

use App\Models\User;
use App\Models\EmailApplication;
use Illuminate\Support\Str; // Assuming this might be used for password generation or string manipulation
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Exception; // Use base Exception for broad catches, but consider more specific exceptions where appropriate
use Illuminate\Support\Facades\Notification; // *** ADDED THIS LINE ***

use App\Mail\WelcomeEmail; // Ensure this Mailable class exists and is imported
use App\Mail\ProvisioningFailedNotification; // Ensure this Mailable class exists and is imported
// Optional: Import more specific exception types if you create them (e.g., \\App\\Exceptions\\\\ProvisioningApiException)


class EmailProvisioningService
{
  // Inject any dependencies needed (e.g., external API client)
  // protected ExternalEmailApiClient $externalEmailApiClient;\
  // public function __construct(ExternalEmailApiClient $externalEmailApiClient)\
  // {\
  //     $this->externalEmailApiClient = $externalEmailApiClient;\
  // }\

  public function __construct()
  {
    // You can initialize any dependencies here if needed that are not injected.
    // For example, if you need to create an instance of an external API client that doesn't use Laravel's service container.
  }


  /**
   * Generates a unique email address based on user details and preference.
   * This is a helper method and might contain your naming convention logic.
   *
   * @param  User   $user The user for whom the email is being generated.
   * @param  string|null $preferredEmail The user's preferred email address (optional).
   * @return string The generated unique email address.
   */
  public function generateUniqueEmailAddress(User $user, ?string $preferredEmail = null): string // Add return type hint
  {
    // Implement your logic here for generating the email address
    // This might involve sanitizing the preferred email, checking availability,
    // or using a standard naming convention based on user name/staff ID.

    if ($preferredEmail && $this->isEmailValidAndAvailable($preferredEmail)) { // Assumes isEmailValidAndAvailable method exists
      return $preferredEmail;
    }

    // Fallback to a standard naming convention if preferred email is not valid or available
    $nameParts = explode(' ', $user->name);
    $firstName = Str::slug($nameParts[0] ?? '', '.');
    $lastName = Str::slug(end($nameParts) ?? '', '.');

    // Simple example: first.last@motac.gov.my
    $generatedEmail = $firstName . '.' . $lastName . '@motac.gov.my'; // Replace with your domain

    // Add logic to handle duplicates (e.g., first.last2@motac.gov.my)
    // This would likely involve querying your User model and/or the external system.
    // while ($this->checkEmailExistsInExternalSystem($generatedEmail)) { // Assumes checkEmailExistsInExternalSystem method exists
    //     // Modify generatedEmail (e.g., add a number suffix)
    //     $generatedEmail = $this->addSuffixToEmail($generatedEmail); // Assumes addSuffixToEmail helper
    // }

    Log::debug("Generated email address for user ID " . ($user->id ?? 'N/A') . ": {$generatedEmail}");

    return strtolower($generatedEmail); // Return in lowercase
  }

  /**
   * Placeholder method to check if an email is valid and available.
   * Needs implementation based on your validation rules and external system check.
   *
   * @param string $email
   * @return bool
   */
  protected function isEmailValidAndAvailable(string $email): bool
  {
    // TODO: Implement email format validation
    // TODO: Implement check against existing users in your database
    // TODO: Implement check against external email system using checkEmailExistsInExternalSystem

    // Placeholder: Assume valid and available for now
    return true;
  }

  /**
   * Placeholder method to add suffix to email for duplicate handling.
   *
   * @param string $email
   * @return string
   */
  protected function addSuffixToEmail(string $email): string
  {
    // TODO: Implement logic to add a number or other suffix to handle duplicates
    // Example: user.name@domain.com -> user.name1@domain.com
    return $email; // Placeholder
  }


  /**
   * Sends a welcome email to the user after successful provisioning.
   *
   * @param  User  $user The user who received the email account.
   * @param  string $assignedEmail The newly assigned email address.
   * @param  string|null $initialPassword The initial password (handle securely!).
   * @return void
   */
  public function sendWelcomeEmail(User $user, string $assignedEmail, ?string $initialPassword = null): void
  {
    Log::debug("Attempting to send welcome email to user ID: " . ($user->id ?? 'N/A') . " (" . ($user->email ?? 'N/A') . ")");

    try {
      // Mail::to() will use the routeNotificationForMail method on the User model
      // if it's defined to send to 'personal_email'. Otherwise, it uses the 'email' field.
      Mail::to($user)->send(new WelcomeEmail($user, $assignedEmail, $initialPassword)); // Pass data to Mailable

      Log::info("Welcome email sent successfully to user ID: " . ($user->id ?? 'N/A'));
    } catch (Exception $e) {
      Log::error("Failed to send welcome email to user ID: " . ($user->id ?? 'N/A') . ": " . $e->getMessage(), ['exception' => $e]);
      // Depending on requirements, you might re-throw or log this failure.
      // throw new Exception('Failed to send welcome email.', 0, $e);
    }
  }

  /**
   * Sends a notification to administrators if provisioning failed.
   *
   * @param  EmailApplication  $application The application that failed provisioning.
   * @param  string            $reason      The reason for the failure.
   * @param  User|null         $adminUser   The admin who triggered the process (optional).
   * @return void
   * @throws Exception If sending the notification fails.
   */
  public function sendProvisioningFailedNotification(EmailApplication $application, string $reason, ?User $adminUser = null): void // Add return type hint and throws tag
  {
    Log::debug("Attempting to send provisioning failed notification for application ID: " . ($application->id ?? 'N/A'));

    // TODO: Define who should receive this notification (e.g., users with a specific role or configured email addresses)
    $adminRecipients = User::role('IT Admin')->get(); // Example: Send to all users with 'IT Admin' role

    if ($adminRecipients->isEmpty()) {
      Log::warning("No IT Admin recipients found for provisioning failed notification for application ID: " . ($application->id ?? 'N/A'));
      return; // Exit if no recipients
    }

    try {
      // *** FIX: Ensure your App\Notifications\ProvisioningFailedNotification constructor accepts 3 arguments:
      // App\Models\EmailApplication, string $reason, App\Models\User|null $adminUser ***
      Notification::send($adminRecipients, new ProvisioningFailedNotification($application, $reason, $adminUser));

      Log::info("Provisioning failed notification sent for application ID: " . ($application->id ?? 'N/A'));
    } catch (Exception $e) {
      Log::error("Failed to send provisioning failed notification for application ID " . ($application->id ?? 'N/A') . ": " . $e->getMessage(), ['exception' => $e]);
      // Re-throw the exception if the caller needs to know about notification failure
      throw new Exception('Gagal menghantar notifikasi kegagalan peruntukan akaun e-mel kepada admin: ' . $e->getMessage(), 0, $e); // Malay message
    }
  }


  /**
   * Placeholder to check external system for existing address.
   * TODO: implement real external check using an API client.
   *
   * @param  string  $email The email address to check.
   * @return bool True if the email exists in the external system, false otherwise.
   * @throws Exception If the external check fails.
   */
  public function checkEmailExistsInExternalSystem(string $email): bool // Add return type hint
  {
    // Simulate an external system check
    Log::debug("Simulating check for existence of email '{$email}' in external system.");

    // TODO: Replace with actual API call to check email existence
    // Example:
    // try {
    //     $exists = $this->externalEmailApiClient->checkEmailExists($email);
    //     return $exists;
    // } catch (ExternalApiException $e) {
    //     Log::error("External email existence check failed for {$email}: {$e->getMessage()}");
    //     throw new Exception("Failed to check email existence in external system.", 0, $e);
    // }\

    // Placeholder: Always return false for now, simulating the email does not exist
    return false;
  }

  /**
   * Performs the actual email account provisioning in the external system.
   * This method is called by the EmailApplicationService after approval
   * and potentially by the EmailProvisioningController API endpoint.
   *
   * @param  EmailApplication $application The application being provisioned.
   * @param  array            $provisioningData Data needed for provisioning (e.g., final email, user details).
   * @param  User|null        $triggeringUser  The user who triggered provisioning (IT Admin via internal process or system user via API).
   * @return array           A result array, e.g., ['status' => 'completed'|'failed', 'message' => '...'].
   * @throws Exception       If a critical error prevents provisioning (e.g., API down).
   */
  public function processProvisioning(EmailApplication $application, array $provisioningData, ?User $triggeringUser = null): array
  {
    Log::info('Starting processProvisioning for application ID: ' . ($application->id ?? 'N/A'), [
      'triggering_user_id' => $triggeringUser->id ?? 'N/A',
      'provisioning_data_keys' => array_keys($provisioningData),
    ]);

    // TODO: Implement the actual integration with your external email system's API here.
    // This is the core logic for creating the email account.

    try {
      // Example steps:
      // 1. Extract necessary data from $application and $provisioningData
      $userToProvision = $application->user; // The user the account is for
      $finalEmail = $provisioningData['final_assigned_email']; // Final email determined by IT Admin
      $externalUserId = $provisioningData['user_id_assigned'] ?? null; // Optional external ID

      // Critical check: Ensure user relationship exists before proceeding
      if (!$userToProvision) {
        Log::critical('User relationship missing for application ID: ' . ($application->id ?? 'N/A') . ' during processProvisioning.');
        // The calling service/controller handles the transaction rollback and status update
        throw new Exception('Ralat sistem: Pengguna tidak ditemui untuk permohonan ini.'); // Malay message
      }


      // 2. Call external system API
      // Example using a hypothetical API client:
      // $apiResponse = $this->externalEmailApiClient->createAccount([
      //     'email' => $finalEmail,
      //     'username' => $userToProvision->staff_id, // Or other unique identifier
      //     'first_name' => $userToProvision->first_name, // Assuming first_name/last_name attributes exist
      //     'last_name' => $userToProvision->last_name,
      //     'password' => Str::random(16), // Generate temporary password (handle securely!)
      //     'department' => $userToProvision->department->name ?? null,
      //     // Pass any other data required by your external system API
      // ]);

      // 3. Process API response and update application/user accordingly
      // Example: If API call was successful:
      // if ($apiResponse['success']) {
      //     // Update User model with assigned details
      //     $userToProvision->motac_email = $finalEmail; // Assuming motac_email field exists on User model
      //     $userToProvision->user_id_assigned = $apiResponse['external_user_id'] ?? $externalUserId; // Store external system's user ID
      //     $userToProvision->save();

      //     // The calling service/controller updates the EmailApplication status to COMPLETED
      //     Log::info('External email provisioning API call succeeded.', ['application_id' => $application->id ?? 'N/A']);
      //     return ['status' => 'completed', 'message' => 'Account created successfully in external system.'];

      // } else {
      //     // Handle API errors or failures
      //     $errorMessage = $apiResponse['error_message'] ?? 'External API call failed.';
      //     Log::error('External email provisioning API call failed.', [
      //         'application_id' => $application->id ?? 'N/A',
      //         'api_error' => $apiResponse['error_details'] ?? 'N/A',
      //     ]);

      //     // The calling service/controller updates the EmailApplication status to PROVISIONING_FAILED
      //     return ['status' => 'failed', 'message' => 'External provisioning failed: ' . $errorMessage];
      // }

      // --- Placeholder Implementation ---
      // For now, simulate success after a delay or based on some simple condition
      Log::info('Simulating successful external email provisioning for application ID: ' . ($application->id ?? 'N/A'));
      // In a real scenario, this would be the result of an API call.
      // Assuming success for simulation:
      // $userToProvision = $application->user; // Already fetched above

      // Simulate updating the user model
      // Need to wrap this in a transaction if not already done by the caller Service/Controller
      // DB::beginTransaction();
      // try {
      if ($userToProvision) {
        // These fields must exist on your User model/database table
        $userToProvision->motac_email = $finalEmail; // Requires motac_email column
        $userToProvision->user_id_assigned = $externalUserId; // Requires user_id_assigned column
        $userToProvision->save(); // Save changes to the user model
        Log::info('Simulated updating user model with provisioned email/ID.', ['user_id' => $userToProvision->id ?? 'N/A']);
      } else {
        Log::warning('Simulated provisioning success, but user model relationship missing for application ID: ' . ($application->id ?? 'N/A') . '. User model update skipped.');
      }
      //      DB::commit();
      // } catch (\Exception $e) {
      //      DB::rollBack();
      //      Log::error('Simulated user model update failed during processProvisioning: ' . $e->getMessage(), ['exception' => $e]);
      // Decide how to handle this failure within the provisioning process
      // Maybe return status as failed, or throw?
      //      throw new Exception('Simulated user model update failed: ' . $e->getMessage(), 0, $e);
      // }


      // Simulate sending a welcome email (if your external system doesn't send it)
      // $this->sendWelcomeEmail($userToProvision, $finalEmail, 'SimulatedTemporaryPassword123!'); // Needs password handling

      return ['status' => 'completed', 'message' => 'Simulated account creation success.'];
      // --- End Placeholder Implementation ---


    } catch (Exception $e) {
      // Catch exceptions during the API call or internal processing within this method
      Log::error('Exception occurred during processProvisioning for application ID: ' . ($application->id ?? 'N/A') . ': ' . $e->getMessage(), ['exception' => $e]);
      // Re-throw or return a failed status array
      // The calling service/controller handles the transaction rollback and status update to PROVISIONING_FAILED
      throw new Exception('Error during external provisioning process: ' . $e->getMessage(), 0, $e);
      // Or return: return ['status' => 'failed', 'message' => 'Error during provisioning: ' . $e->getMessage()]; // If caller handles exceptions differently
    }
  }


  // Add other service methods related to Email Applications as needed
  // (e.g., deactivateAccount, updateAccount, syncAccountStatus, etc.)
}
