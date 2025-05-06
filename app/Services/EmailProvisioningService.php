<?php

namespace App\Services;

use App\Models\User;
use App\Models\EmailApplication;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Exception; // Use base Exception for broad catches, but consider more specific exceptions where appropriate
use Illuminate\Support\Facades\Mail;
use App\Mail\WelcomeEmail; // Ensure this Mailable class exists and is imported
use App\Mail\ProvisioningFailedNotification; // Ensure this Mailable class exists and is imported
// Optional: Import more specific exception types if you create them (e.g., \App\Exceptions\ProvisioningApiException)

class EmailProvisioningService
{
  // Inject any dependencies needed (e.g., external API client)
  // protected ExternalEmailApiClient $externalEmailApiClient;

  // public function __construct(ExternalEmailApiClient $externalEmailApiClient)
  // {
  //     $this->externalEmailApiClient = $externalEmailApiClient;
  // }

  /**
   * Generates a unique email address suggestion for a user based on naming convention.
   * Adapt to use 'full_name' from the provided user migration schema.
   *
   * @param User $user The user for whom to generate the email. Must have 'full_name'.
   * @return string The generated unique email address suggestion.
   * @throws Exception If email generation fails.
   */
  public function generateEmail(User $user): string
  {
    Log::debug('Attempting to generate email suggestion for user ID: ' . $user->id);

    // --- SUGGESTED EDIT ---
    // Use 'full_name' as per the provided users table migration, not first_name/last_name.
    // Implement logic to generate a base part from the full name.
    $fullName = $user->full_name;
    if (empty($fullName)) {
      Log::warning('User ' . $user->id . ' is missing full_name for email generation.');
      // Return a generic placeholder or throw an exception if full_name is mandatory.
      // For a *suggestion*, returning a placeholder might be more user-friendly than throwing.
      return 'user' . $user->id . '@' . config('app.email_domain', 'example.com'); // Fallback generic email
      // throw new \InvalidArgumentException("User must have full_name to generate email."); // Alternative: throw exception
    }

    try {
      // Example generation from full_name: replace spaces/non-alphanumeric with dot, convert to lowercase.
      // Adjust this logic based on your desired naming convention (e.g., first.last, first_last, initiallastname).
      // Ensure it handles names with multiple parts, special characters, etc.
      $basePart = str_replace([' ', '.', '-'], '.', strtolower($fullName)); // Example: "John Doe" -> "john.doe"
      $basePart = preg_replace('/[^a-z0-9.]/', '', $basePart); // Remove anything not a-z, 0-9, or dot

      // Remove consecutive dots or leading/trailing dots if necessary
      $basePart = trim($basePart, '.');
      $basePart = preg_replace('/\.\.+/', '.', $basePart);

      // Ensure basePart is not empty after sanitization
      if (empty($basePart)) {
        $basePart = 'user' . $user->id; // Fallback if name sanitization results in empty string
      }

      // Get the email domain from configuration
      // Ensure you have 'email_domain' set in your config/app.php or a dedicated config file
      $emailDomain = config('app.email_domain', 'example.com');

      // Combine with domain
      $baseEmail = $basePart . '@' . $emailDomain;

      // Add a uniqueness check against existing user emails (assuming 'motac_email' column on User model)
      $email = $baseEmail;
      $counter = 1;
      // Using a loop with a limit to prevent infinite loops
      // Check against User model's 'motac_email' field as per migration
      while (User::where('motac_email', $email)->exists() && $counter <= 100) {
        $email = $basePart . $counter . '@' . $emailDomain;
        $counter++;
      }

      if ($counter > 100) {
        Log::error("Could not generate unique email for user " . $user->id . " after 100 attempts with base: " . $basePart);
        throw new Exception("Failed to generate a unique email suggestion due to naming conflicts.");
      }

      Log::debug('Generated unique email suggestion: ' . $email . ' for user ID: ' . $user->id);
      return $email;
    } catch (Exception $e) {
      Log::error('Failed to generate email suggestion for user ID ' . ($user ? $user->id : 'null') . ': ' . $e->getMessage(), ['exception' => $e]);
      // Re-throw the exception
      throw $e;
    }
  }


  /**
   * Provisions an email account for the given application using an external API.
   * This is typically called after the application has been fully approved and processed internally
   * (e.g., status is 'pending_admin' and IT Admin has submitted processing form).
   * This method handles the external interaction and updates the User model based on the outcome.
   * It does NOT update the EmailApplication status or final assigned fields; that's done by the caller.
   *
   * @param EmailApplication $application The email application (should be updated with IT Admin input like final_assigned_email).
   * @param User $user The user associated with the application (passed explicitly for clarity).
   * @return array An array indicating the outcome: ['status' => 'success'|'error', 'message' => string, 'external_user_id' => string|null, 'provisioned_email' => string|null]
   * @throws Exception If a critical error occurs during the provisioning process.
   */
  public function provisionAccount(EmailApplication $application, User $user): array
  {
    // This method should be called when the application status is already set by the controller/service
    // (e.g., to 'processing'). We don't need to check for 'provisionable_statuses' here.
    // The caller is responsible for ensuring the application is in the correct state.

    // We don't need a transaction here if the caller (EmailApplicationService/Controller)
    // manages the transaction for updating the EmailApplication and User models.
    // If THIS service is solely responsible for ALL database updates related to provisioning,
    // including the EmailApplication status transition to 'completed'/'failed',
    // then a transaction here IS appropriate.
    // Let's assume for now the caller manages the transaction for application/user updates,
    // and this service just focuses on the API call and its immediate outcome processing.

    // --- Decision based on design flow ---
    // The handleAdminProcessingUpdate method in EmailApplicationService or the controller
    // updates the EmailApplication model and the User model *before* calling this.
    // Therefore, this method should NOT manage its own transaction or update application/user status.
    // Its job is purely to interact with the external API and return the result.
    // The caller will then commit or rollback the main transaction and update application status based on this result.

    // Get the email address to provision. Use the final_assigned_email from the application model
    // which should have been set by the IT Admin input in the calling code.
    $emailToProvision = $application->final_assigned_email;

    // Critical check: Ensure final_assigned_email is set and valid BEFORE calling API.
    if (empty($emailToProvision) || !filter_var($emailToProvision, FILTER_VALIDATE_EMAIL)) {
      Log::error("Cannot provision email for application ID: " . $application->id . ". Final assigned email is missing or invalid: " . $emailToProvision);
      // Throw a specific exception type for clearer error handling by the caller
      throw new \InvalidArgumentException("Final assigned email is missing or invalid for provisioning.");
    }

    // Get the user associated with the application. It's better to pass the User model explicitly
    // from the caller to avoid re-fetching and ensure it's loaded.
    // If not passed, load it, but passing is preferred.
    // $application->loadMissing('user'); // Only if not passed explicitly
    // $user = $application->user;
    // Critical check: Ensure the user exists and is linked.
    if (!$user) { // This check is still valuable even if passed explicitly, just in case.
      Log::error('Cannot provision email. EmailApplication (ID: ' . $application->id . ') does not have a user associated (passed).');
      throw new \RuntimeException('EmailApplication does not have a user associated.');
    }


    // Generate a temporary password for the account.
    $tempPassword = Str::random(config('email_provisioning.temp_password_length', 12));
    // Ensure config key 'email_provisioning.temp_password_length' exists.


    // --- Integration with External Email System API (Exchange/Google Workspace, etc.) ---
    Log::info("EmailProvisioningService: Preparing external email API call for: {$emailToProvision} for user: {$user->id}");

    try {
      // Call the method that interacts with the external API
      $apiResponse = $this->callEmailApi($emailToProvision, $tempPassword, $user);

      // Process the API response to determine the outcome
      if (isset($apiResponse['status']) && $apiResponse['status'] === 'success') {
        // Provisioning was successful based on the external API's response

        Log::info('External email provisioning API call successful for application ID ' . $application->id . '. Email: ' . $emailToProvision);

        // --- SUGGESTED EDIT ---
        // Do NOT update EmailApplication model fields (final_assigned_email, provisioned_at, final_assigned_user_id) here.
        // These updates, and the status transition to 'completed'/'provisioned', and the User model update
        // should be handled by the calling code (handleAdminProcessingUpdate) based on the result returned by this method.
        // This keeps this method focused on the external API interaction and its raw outcome.
        // --- END SUGGESTED EDIT ---


        // Optionally, send a welcome email to the user's personal email with their new credentials.
        // Handle exceptions from sending email gracefully *if provisioning itself was successful*.
        try {
          $this->sendWelcomeEmail($user, $emailToProvision, $tempPassword); // Use $emailToProvision or $apiResponse['email'] if API returns it
          Log::info('Welcome email sending triggered for user ID: ' . $user->id);
        } catch (Exception $e) {
          // Log email sending failure but do NOT indicate provisioning failed.
          Log::error('Failed to send welcome email after provisioning for user ID ' . $user->id . ' (email: ' . ($user->personal_email ?? 'N/A') . '): ' . $e->getMessage(), ['exception' => $e]);
          // You might notify an admin internally about *this specific email sending failure*.
        }

        // Return structured success response
        return [
          'status' => 'success',
          'message' => $apiResponse['message'] ?? 'Provisioning successful.',
          'external_user_id' => $apiResponse['external_user_id'] ?? null,
          'provisioned_email' => $apiResponse['email'] ?? $emailToProvision, // Return the email used/assigned
        ];
      } else {
        // Provisioning failed based on API response status ('error')

        $errorMessage = $apiResponse['message'] ?? 'Unknown API error during provisioning';
        Log::error('External email provisioning API call failed for application ID ' . $application->id .
          '. Error: ' . $errorMessage . '. API response: ' . json_encode($apiResponse));

        // --- SUGGESTED EDIT ---
        // Do NOT update EmailApplication model status or rejection_reason here.
        // The caller will handle setting the status to 'provisioning_failed' and storing the rejection reason
        // based on the array returned by this method.
        // --- END SUGGESTED EDIT ---


        // Optionally, notify an admin about the provisioning failure
        try {
          $this->notifyAdminOfFailure($application, $errorMessage);
        } catch (Exception $e) {
          Log::error('Failed to send admin notification of provisioning failure for application ID ' . $application->id . ': ' + $e->getMessage(), ['exception' => $e]);
        }

        // Return structured error response
        return [
          'status' => 'error',
          'message' => $errorMessage,
          // Include other relevant data from API response if helpful for debugging or display
          'api_error_code' => $apiResponse['error_code'] ?? null,
        ];
      }
    } catch (Exception $e) { // Catch any exceptions during the API call itself (e.g., network error)
      // Log the critical error
      Log::error('Critical error during external email API call for application ID ' . $application->id . ': ' . $e->getMessage(), ['exception' => $e]);

      // Optionally, notify an admin about the critical failure (different notification than API-reported error)
      try {
        $this->notifyAdminOfCriticalFailure($application, $e); // Needs new method/notification
      } catch (Exception $ne) { /* Log notification error */
      }

      // Re-throw the exception for the calling logic to handle (e.g., rollback transaction, update application status to failed)
      throw new Exception('Critical error during email provisioning API interaction: ' . $e->getMessage(), 0, $e); // Wrap and re-throw
    }
  }

  /**
   * Placeholder for the actual API call to the external email system.
   * Needs to be implemented based on your specific email service API.
   *
   * @param string $email The email address to provision.
   * @param string $password The initial password.
   * @param User $user The user model.
   * @return array Expected structure: ['status' => 'success'|'error', 'message' => string, ...]
   * @throws Exception If the API call itself fails (e.g., network, authentication).
   */
  private function callEmailApi(string $email, string $password, User $user): array
  {
    // --- IMPORTANT: Replace this placeholder with your actual API integration logic ---
    // This is a simulation.

    Log::info("EmailProvisioningService: Simulating external email API call for: {$email} for user: {$user->id}");

    // Example Simulation Logic (Keep this structured return):
    if (Str::contains($email, 'test-success') || Str::contains($email, 'provision-ok')) {
      return ['status' => 'success', 'message' => 'Account created.', 'email' => $email, 'external_user_id' => 'ext_' . Str::random(5)];
    }
    if (Str::contains($email, 'test-fail') || Str::contains($email, 'provision-error')) {
      return ['status' => 'error', 'message' => 'Simulated failure: User exists.', 'error_code' => 'API_ERR_001'];
    }
    // Default success
    return ['status' => 'success', 'message' => 'Default success.', 'email' => $email, 'external_user_id' => 'ext_' . Str::random(5)];

    // --- END SIMULATION ---

    // Remember to throw specific exceptions (e.g., from Guzzle) on network/API authentication failures.
  }

  /**
   * Sends a welcome email to the user's personal email with their new MOTAC email credentials.
   *
   * @param User $user The user model (should have personal_email attribute).
   * @param string $motacEmail The provisioned MOTAC email address.
   * @param string $password The initial password for the account.
   * @return void
   * @throws Exception If sending the email fails.
   */
  private function sendWelcomeEmail(User $user, string $motacEmail, string $password): void
  {
    Log::info('Attempting to send welcome email for user ID: ' . $user->id);
    try {
      // Ensure WelcomeEmail Mailable exists and is imported.
      if ($user->personal_email && filter_var($user->personal_email, FILTER_VALIDATE_EMAIL)) {
        Mail::to($user->personal_email)->send(new WelcomeEmail($user, $motacEmail, $password));
        Log::info('Welcome email sending triggered successfully for user ID: ' . $user->id);
      } else {
        Log::warning('Skipped sending welcome email for user ID ' . $user->id . '. Personal email is missing or invalid.');
        // TODO: Consider an alternative notification if welcome email is critical and personal email is missing.
      }
    } catch (Exception $e) {
      Log::error('Failed to send welcome email to user ID ' . ($user->id ?? 'unknown') . ' (email: ' . ($user->personal_email ?? 'N/A') . '): ' . $e->getMessage(), ['exception' => $e]);
      // IMPORTANT: Decide if welcome email failure should halt the *entire* provisioning process.
      // Usually, it should not, as the account is already created externally. Just log the failure.
      // However, if the calling code expects an exception on *any* failure, re-throw.
      // For robustness, perhaps just log here and let the main provisioning method succeed if API call was OK.
      // If you need to re-throw, uncomment the line below:
      // throw $e;
    }
  }

  /**
   * Sends a notification to admins if provisioning fails due to an API-reported error.
   *
   * @param EmailApplication $application The application that failed to provision.
   * @param string $errorMessage The error message details from the API.
   * @return void
   */
  private function notifyAdminOfFailure(EmailApplication $application, string $errorMessage): void
  {
    Log::info('Attempting to send admin notification of provisioning failure for application ID: ' . $application->id);
    try {
      // Find admin users to notify (adjust query based on your admin role/permission logic)
      $adminUsers = User::where('is_admin', true)->get(); // Example: Assuming an 'is_admin' flag on User model

      if ($adminUsers->isEmpty()) {
        Log::warning('No admin users found to notify about provisioning failure.');
        return;
      }

      // Use a Mailable for the failure notification
      // Ensure ProvisioningFailedNotification Mailable exists and is imported
      foreach ($adminUsers as $admin) {
        if ($admin->email && filter_var($admin->email, FILTER_VALIDATE_EMAIL)) {
          Mail::to($admin->email)->send(new ProvisioningFailedNotification($application, $errorMessage));
        } else {
          Log::warning('Skipping admin failure notification for user ID ' . $admin->id . ' - Invalid or missing email.');
        }
      }

      Log::info('Admin notification sent for provisioning failure of application ID: ' . $application->id);
    } catch (Exception $e) {
      // Log this failure but do NOT re-throw.
      Log::error('Failed to send admin notification for provisioning failure of application ID ' + $application->id + ': ' + $e->getMessage(), ['exception' => $e]);
    }
  }

  /**
   * Sends a notification to admins if provisioning fails due to a critical system error (e.g., API connectivity).
   * This is separate from an API-reported *business* error.
   *
   * @param EmailApplication $application The application involved in the critical failure.
   * @param Exception $exception The exception that occurred.
   * @return void
   */
  private function notifyAdminOfCriticalFailure(EmailApplication $application, Exception $exception): void
  {
    Log::info('Attempting to send admin notification of critical provisioning failure for application ID: ' . $application->id);
    try {
      $adminUsers = User::where('is_admin', true)->get(); // Example: Assuming an 'is_admin' flag

      if ($adminUsers->isEmpty()) {
        Log::warning('No admin users found to notify about critical provisioning failure.');
        return;
      }

      // Use a different Mailable or notification type for critical errors if needed
      // Example uses the same, but content might differ
      $errorMessage = "Critical System Error: " . $exception->getMessage() . "\nApplication ID: " . $application->id;
      // Optionally include more details from the exception or application

      foreach ($adminUsers as $admin) {
        if ($admin->email && filter_var($admin->email, FILTER_VALIDATE_EMAIL)) {
          // You might need a different Mailable like CriticalProvisioningFailureNotification
          Mail::to($admin->email)->send(new ProvisioningFailedNotification($application, $errorMessage));
        } else {
          Log::warning('Skipping admin critical failure notification for user ID ' . $admin->id . ' - Invalid or missing email.');
        }
      }

      Log::info('Admin notification sent for critical provisioning failure of application ID: ' . $application->id);
    } catch (Exception $e) {
      Log::error('Failed to send admin notification for CRITICAL provisioning failure of application ID ' + $application->id + ': ' + $e->getMessage(), ['exception' => $e]);
    }
  }


  // Add other service methods related to Email Applications as needed
  // (e.g., deactivateAccount, updateAccount, syncAccountStatus, etc.)
}
