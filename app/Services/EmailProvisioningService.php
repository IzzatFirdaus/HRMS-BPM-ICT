<?php

namespace App\Services;

use App\Models\User; // Import User model
use App\Models\EmailApplication; // Import EmailApplication model
use Illuminate\Support\Str; // Import Str facade for string manipulation (e.g., slug, random)
use Illuminate\Support\Facades\Log; // For logging
use Illuminate\Support\Facades\DB; // Import DB facade for transactions
use Exception; // Import base Exception class
use Illuminate\Support\Facades\Mail; // For sending emails
use App\Mail\WelcomeEmail; // Ensure this import exists and is correct for your WelcomeEmail Mailable class
use App\Mail\ProvisioningFailedNotification; // Assuming you have a Mailable for failure notifications


class EmailProvisioningService
{
    // Inject any dependencies needed (e.g., external API client, notification service)
    // protected ExternalEmailApiClient $externalEmailApiClient;
    // protected NotificationService $notificationService;

    // public function __construct(ExternalEmailApiClient $externalEmailApiClient, NotificationService $notificationService)
    // {
    //     $this->externalEmailApiClient = $externalEmailApiClient;
    //     $this->notificationService = $notificationService;
    // }

    /**
     * Generates a unique email address suggestion for a user based on naming convention.
     *
     * @param User $user The user for whom to generate the email. Must have 'first_name' and 'last_name'.
     * @return string The generated unique email address suggestion.
     * @throws Exception If email generation fails. // Simplified \Exception
     */
    public function generateEmail(User $user): string
    {
        Log::debug('Attempting to generate email suggestion for user: ' . $user->id);
        // Get the email domain from configuration
        // Ensure you have 'email_domain' set in your config/app.php or a dedicated config file
        $emailDomain = config('app.email_domain', 'example.com'); // Default to example.com if not set

        // Ensure user has necessary name attributes
        if (empty($user->first_name) || empty($user->last_name)) {
            Log::warning('User ' . $user->id . ' is missing first_name or last_name for email generation.');
            // Decide how to handle this - throw exception, return default, etc.
            // For a suggestion method, returning a generic one or throwing a specific exception might be appropriate
            // throw new \InvalidArgumentException("User must have first_name and last_name to generate email.");
            return ''; // Or a default placeholder like 'user'
        }

        try {
            // Basic email generation based on user's name fields
            // Using Str::slug for sanitization, replacing spaces and special chars with hyphens
            // It's generally safer to use hyphens (-) in the local part of an email address
            // instead of dots (.) or other special characters to avoid potential parsing issues
            $basePart = Str::slug($user->first_name . '.' . $user->last_name, '-');

            // Combine with domain
            $baseEmail = $basePart . '@' . $emailDomain;

            // Add a uniqueness check against existing user emails (assuming 'motac_email' column)
            $email = $baseEmail;
            $counter = 1;
            // Using a loop with a limit to prevent infinite loops in unexpected scenarios
            while (User::where('motac_email', $email)->exists() && $counter <= 100) {
                // If the base email exists, append a number and re-check
                $email = $basePart . $counter . '@' . $emailDomain;
                $counter++;
            }

            // If after 100 attempts, a unique email is not found, it indicates a problem
            // (e.g., a large number of users with the exact same name structure)
            if ($counter > 100) {
                Log::error("Could not generate unique email for user " . $user->id . " after 100 attempts with base: " . $basePart);
                throw new Exception("Failed to generate a unique email suggestion due to naming conflicts.");
            }


            Log::debug('Generated unique email suggestion: ' . $email . ' for user: ' . $user->id);
            return $email; // Return the unique suggestion

        } catch (Exception $e) {
            // Log the error with more context
            Log::error('Failed to generate email suggestion for user ' . ($user ? $user->id : 'null') . ': ' . $e->getMessage(), ['exception' => $e]);
            // Re-throw the exception for the calling code to handle (e.g., in a controller or Livewire component)
            throw $e;
        }
    }

    // --- The createApplication and updateApplication methods were removed from here
    // --- as they belong in the App\Services\EmailApplicationService.php file,
    // --- which handles the application lifecycle itself. This service focuses on provisioning.


    /**
     * Provisions an email account for the given application using an external API.
     * This is typically called after the application has been fully approved and processed internally.
     * This method handles the external interaction and updates the application status based on the outcome.
     *
     * @param EmailApplication $application The email application containing the necessary data (user, proposed/final email, etc.).
     * @return EmailApplication The updated EmailApplication model instance after the provisioning attempt.
     * @throws Exception If a critical error occurs during the provisioning process that prevents retries. // Simplified \Exception
     */
    public function provisionAccount(EmailApplication $application): EmailApplication
    {
        // Ensure the application is in a state ready for provisioning (e.g., 'ready_for_processing')
        // This check could be done here for robustness, or it could be handled by the calling logic
        // (e.g., a Workflow service, Approval service, or the API controller that triggers this).
        // Getting allowed statuses from configuration is a good practice.
        $provisionableStatuses = config('email_provisioning.provisionable_statuses', ['ready_for_processing']); // Get from config

        if (!in_array($application->status, $provisionableStatuses)) {
            Log::warning('Attempted to provision email for application ID ' . $application->id . ' with status ' . $application->status . '. Not in a provisionable state.');
            // Decide how to handle this:
            // - Return the application as is (less disruptive)
            // - Throw a specific exception type (more explicit error handling needed by caller)
            // Returning the application is less disruptive if the calling code can handle it.
            // throw new \InvalidArgumentException("Application ID {$application->id} is not in a provisionable status.");
            return $application; // Return application as is without attempting provisioning
        }


        // Use database transaction for atomicity if multiple database operations are involved
        // All database operations within this method should be inside the transaction to ensure
        // that either all succeed or none are applied if an error occurs.
        DB::beginTransaction();

        try {
            // Get the user associated with the application.
            // Ensure the user relationship is eager loaded on the application model before calling this method for efficiency.
            // loadMissing only loads if not already loaded.
            $application->loadMissing('user');
            $user = $application->user;

            // Critical check: Ensure the user exists and is linked to the application.
            if (!$user) {
                DB::rollBack(); // Roll back transaction as we cannot proceed
                Log::error('Cannot provision email. EmailApplication (ID: ' . $application->id . ') does not have a user associated.');
                // Throw a specific exception type for clearer error handling by the caller
                throw new \RuntimeException('EmailApplication does not have a user associated.');
            }

            // Determine the email address to provision.
            // Prioritize 'final_assigned_email' (assigned by admin), then 'proposed_email' (applicant's suggestion).
            // If both are missing, fall back to generating one.
            $emailToProvision = $application->final_assigned_email ?? $application->proposed_email;

            // If final or proposed email is missing, try generating one as a last resort fallback
            if (empty($emailToProvision)) {
                Log::warning("Final/Proposed email missing for application ID: " . $application->id . ". Attempting to generate fallback.");
                try {
                    $emailToProvision = $this->generateEmail($user);
                    Log::info("Generated fallback email '{$emailToProvision}' for application ID: " . $application->id);
                } catch (Exception $e) { // Simplified \Exception
                    // If fallback generation also fails, log and update status before re-throwing
                    DB::rollBack();
                    Log::error("Failed to generate fallback email for application ID: " . $application->id . ". Error: " . $e->getMessage());
                    $application->status = 'provisioning_failed'; // Update status on generation failure
                    $application->rejection_reason = 'Failed to determine or generate email address: ' . $e->getMessage();
                    $application->save(); // Save status update before throwing
                    throw new \RuntimeException("Failed to determine or generate email address for provisioning.", 0, $e);
                }
            }

            // Validate the determined email format before attempting API call (optional but good practice)
            if (!filter_var($emailToProvision, FILTER_VALIDATE_EMAIL)) {
                DB::rollBack();
                Log::error("Determined email '{$emailToProvision}' for application ID: " . $application->id . " is not a valid email format.");
                $application->status = 'provisioning_failed'; // Update status
                $application->rejection_reason = 'Invalid email format determined: ' . $emailToProvision;
                $application->save(); // Save status update
                throw new \InvalidArgumentException("Invalid email format determined for provisioning: {$emailToProvision}");
            }


            // Generate a temporary password for the account.
            // Get password length from configuration.
            $tempPassword = Str::random(config('email_provisioning.temp_password_length', 12));
            // Consider password complexity requirements if the external system supports them.


            // --- Integration with External Email System API (Exchange/Google Workspace, etc.) ---
            // This is the core provisioning logic. Replace this placeholder with your actual API call implementation.
            Log::info("EmailProvisioningService: Preparing external email API call for: {$emailToProvision} for application ID: " . $application->id);

            // Update application status to indicate provisioning is in progress *before* the API call
            // This provides visibility in the UI/logs while the API call is happening.
            if ($application->status !== 'processing') { // Avoid setting 'processing' if it's already there
                $application->status = 'processing';
                $application->save(); // Save status update
                Log::info("EmailApplication ID: " . $application->id . " status updated to 'processing' before API call.");
            }


            // Call the method that interacts with the external API
            // Pass necessary data: email, temp password, and potentially user details (full name, employee ID, department, etc.)
            // The callEmailApi method should ideally return a consistent structure or throw specific exceptions.
            $apiResponse = $this->callEmailApi($emailToProvision, $tempPassword, $user);

            // Process the API response to determine the outcome
            if (isset($apiResponse['status']) && $apiResponse['status'] === 'success') {
                // Provisioning was successful based on the external API's response

                // Update the EmailApplication model to store the provisioned details.
                // Ensure the 'provisioned_at' column exists and is cast to datetime in the EmailApplication model.
                $application->final_assigned_email = $apiResponse['email'] ?? $emailToProvision; // Use email from API response if provided, fallback to determined email
                $application->final_assigned_user_id = $apiResponse['external_user_id'] ?? null; // Use a standard key name for the external system's user ID/identifier
                $application->provisioned_at = now(); // Set the timestamp of successful provisioning
                $application->status = 'provisioned'; // Set application status to 'provisioned' upon successful API creation
                $application->rejection_reason = null; // Clear rejection reason if any from previous steps
                // $application->notes = ($application->notes ? $application->notes . "\n" : '') . "Provisioning successful. External ID: " . ($application->final_assigned_user_id ?? 'N/A'); // Add success note
                $application->save(); // Save application changes to the database

                // Update the related User model with the provisioned details
                // Assuming 'motac_email' and 'user_id_assigned' columns exist on the User model.
                // This links the provisioned email and external ID to the user record.
                $user->motac_email = $application->final_assigned_email;
                $user->user_id_assigned = $application->final_assigned_user_id;
                $user->save(); // Save user changes to the database


                // Optionally, send a welcome email to the user's personal email with their new credentials.
                // Ensure sendWelcomeEmail method is correctly implemented and WelcomeEmail Mailable class exists.
                // Handle exceptions from sending email gracefully *if provisioning itself was successful*.
                try {
                    $this->sendWelcomeEmail($user, $application->final_assigned_email, $tempPassword);
                    Log::info('Welcome email sending triggered for user ID: ' . $user->id);
                } catch (Exception $e) { // Simplified \Exception
                    // Log email sending failure but do NOT roll back the transaction or mark provisioning as failed
                    // if the account was successfully created in the external system.
                    Log::error('Failed to send welcome email after provisioning for user ID ' . $user->id . ' (email: ' . ($user->personal_email ?? 'N/A') . '): ' . $e->getMessage(), ['exception' => $e]);
                    // You might send an internal alert here if welcome email failure is critical.
                    // $this->notificationService->notifyAdminAboutEmailFailure($user, $application, 'Welcome Email Failed'); // Example
                }


                DB::commit(); // Commit the database transaction since all steps were successful

                Log::info('Email provisioning successful for application ID ' . $application->id . '. Email: ' . $application->final_assigned_email . '. User ID: ' . $user->id);

                // After successful provisioning, the application status might transition to 'completed'
                // depending on if there are further steps after provisioning (like assigning equipment, etc.).
                // This transition could be handled by a workflow service or within this method if provisioning is the final step.
                // If provisioning IS the final step, you could set status to 'completed' here instead of 'provisioned'.
                // $application->status = 'completed'; $application->save(); // If 'completed' is the final state after provisioning


                return $application; // Return the updated application model instance


            } else {
                // Provisioning failed based on API response status ('error')
                DB::rollBack(); // Roll back the database transaction as external provisioning failed

                $errorMessage = $apiResponse['message'] ?? 'Unknown API error during provisioning';
                Log::error('Email provisioning failed for application ID ' . $application->id .
                    '. API response: ' . json_encode($apiResponse));

                // Update the application status to indicate failure and store rejection reason/notes
                $application->status = 'provisioning_failed'; // Set a specific failure status
                $application->rejection_reason = $errorMessage; // Store API error message provided by external system
                // $application->notes = ($application->notes ? $application->notes . "\n" : '') . "Provisioning Failed: " . $errorMessage; // Add failure note
                $application->save(); // Save application changes to the database (within the rolled-back transaction, but save() outside)


                // Optionally, notify an admin or the user about the provisioning failure
                try {
                    $this->notifyAdminOfFailure($application, $errorMessage);
                } catch (Exception $e) { /* Log notification error but don't re-throw */
                } // Simplified \Exception


                // Throw an exception to indicate the failure to the calling code (e.g., API controller)
                // This exception message will be returned in the API response or logged further up the chain.
                throw new Exception('Email provisioning failed for application ' . $application->id . ': ' . $errorMessage);
            }
        } catch (Exception $e) { // Simplified \Exception
            // Catch any exceptions during the process (e.g., database error, API call network error, user not found, exception from sendWelcomeEmail if re-thrown)
            // Check if a transaction is active before attempting to roll back
            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }

            Log::error('Critical error during email provisioning for application ID ' . ($application->id ?? 'unknown') . ': ' . $e->getMessage(), ['exception' => $e]);

            // Update the application status to indicate critical failure if it wasn't already marked as failed or completed
            // Refresh the model to get the latest status in case it was partially updated before the error occurred
            $application->refresh();
            if (!in_array($application->status, ['provisioned', 'completed', 'provisioning_failed'])) {
                $application->status = 'provisioning_failed';
                // If no specific reason was captured from API, add a generic error message
                if (empty($application->rejection_reason)) {
                    $application->rejection_reason = 'A critical error occurred during provisioning.';
                }
                // Append the exception message to notes for debugging
                $application->notes = ($application->notes ? $application->notes . "\n" : '') . "Critical Error: " . $e->getMessage();
                $application->save(); // Save status update outside the transaction
                Log::info("EmailApplication ID: " . $application->id . " status updated to 'provisioning_failed' due to critical error.");
            } else {
                Log::info("EmailApplication ID: " + $application->id + " status was already in a final state (" + $application->status + ") after critical error.");
            }

            // Re-throw the exception for the calling logic (e.g., API controller) to handle
            throw $e;
        }
    }

    /**
     * Placeholder for the actual API call to the external email system.
     * This method needs to be implemented based on your specific email service API (e.g., Microsoft Exchange, Google Workspace).
     *
     * @param string $email The email address to provision.
     * @param string $password The initial password for the account.
     * @param User $user The user model (may contain details needed for the API call like full name, employee ID, department, etc.).
     * @return array An array representing the API response structure with 'status' ('success' or 'error'), 'message', and potentially 'email', 'external_user_id'.
     * @throws Exception If the API call itself fails at a lower level (e.g., network error, authentication failed with the API). // Simplified \Exception
     */
    private function callEmailApi(string $email, string $password, User $user): array
    {
        // --- IMPORTANT: Replace this placeholder with your actual API integration logic ---
        // This is a simulation for demonstration purposes.

        Log::info("EmailProvisioningService: Simulating external email API call for: {$email} for user: {$user->id}");

        // Implement actual API authentication (e.g., using Guzzle HTTP client or a dedicated SDK)
        // Build the request payload using user details ($user model provides access to first_name, last_name, employee_id, department, etc.), the determined $email, and the temporary $password.
        // Make the HTTP request to the external email system's API endpoint.
        // Handle the HTTP response: check status code, parse response body (JSON, XML).
        // Extract success/error status, messages, and any provisioned details (like the final email if different, or an external system user ID).
        // Throw a low-level exception (e.g., GuzzleHttp\Exception\RequestException) if the API call fails at the network or transport level.

        // Example Simulation:
        // Simulate success for emails containing 'test-success' or 'provision-ok'
        if (Str::contains($email, 'test-success') || Str::contains($email, 'provision-ok')) {
            Log::debug("Simulating successful API response for {$email}");
            return [
                'status' => 'success',
                'message' => 'Email account created successfully (simulated).',
                'email' => $email, // Return the email address that was provisioned (can be different from input)
                'external_user_id' => 'external_id_' . Str::before($email, '@'), // Simulate assigning an external system user ID
            ];
        }

        // Simulate failure for emails containing 'test-fail' or 'provision-error'
        if (Str::contains($email, 'test-fail') || Str::contains($email, 'provision-error')) {
            Log::debug("Simulating failed API response for {$email}");
            return [
                'status' => 'error',
                'message' => 'Simulated API error: Account creation failed. User already exists or invalid input.',
                'error_code' => 'SIM_ERR_001', // Optional error code from the external system
            ];
        }


        // Default simulation: success for any other email if no specific trigger is found
        Log::debug("Simulating successful API response by default for {$email}");
        return [
            'status' => 'success',
            'message' => 'Email account created successfully (simulated - default).',
            'email' => $email,
            'external_user_id' => 'external_id_' . Str::random(8), // Simulate assigning a random external ID
        ];

        // Example of throwing an exception for a critical API call error (e.g., network issue)
        // throw new \GuzzleHttp\Exception\ConnectException('Network error during API call', new \GuzzleHttp\Psr7\Request('POST', 'your_api_url'));
        // The calling provisionAccount method should catch such exceptions.
    }

    /**
     * Sends a welcome email to the user's personal email with their new MOTAC email credentials.
     *
     * @param User $user The user model (should have personal_email attribute).
     * @param string $motacEmail The provisioned MOTAC email address.
     * @param string $password The initial password for the account.
     * @return void
     * @throws Exception If sending the email fails. // Simplified \Exception
     */
    private function sendWelcomeEmail(User $user, string $motacEmail, string $password): void
    {
        Log::info('Attempting to send welcome email for user ID: ' . $user->id);
        try {
            // Use Laravel's Mail facade to send the email to the user's PERSONAL email.
            // Ensure you have a Mailable class named 'WelcomeEmail' that is imported correctly.
            // This Mailable should accept the User instance, the new MOTAC email, and the initial password.
            if ($user->personal_email && filter_var($user->personal_email, FILTER_VALIDATE_EMAIL)) {
                Mail::to($user->personal_email)->send(new WelcomeEmail($user, $motacEmail, $password));
                Log::info('Welcome email sent successfully to ' . $user->personal_email . ' after email provisioning for user ID: ' . $user->id);
            } else {
                Log::warning('Skipped sending welcome email for user ' . $user->id . '. Personal email is missing or invalid.');
                // Optionally, notify an admin or applicant via another channel if the welcome email cannot be sent
                // $this->notificationService->notifyAdminAboutEmailFailure($user, $application, 'Welcome Email Failed - Personal Email Invalid'); // Example
            }
        } catch (Exception $e) { // Simplified \Exception
            // Log the error, including the recipient email if available
            Log::error('Failed to send welcome email to user ' . ($user->id ?? 'unknown') . ' (email: ' . ($user->personal_email ?? 'N/A') . '): ' + $e->getMessage(), ['exception' => $e]); // Simplified \Exception
            // Decide if failure to send email should be a critical error halting the process.
            // Usually, provisioning success is primary, email sending is secondary.
            // You might just log this and not re-throw, depending on requirements.
            throw $e; // Re-throwing as in the original code's error handling flow
        }
    }

    /**
     * Optionally sends a notification to admins if provisioning fails.
     *
     * @param EmailApplication $application The application that failed to provision.
     * @param string $errorMessage The error message details.
     * @return void
     */
    private function notifyAdminOfFailure(EmailApplication $application, string $errorMessage): void
    {
        Log::info('Attempting to send admin notification of provisioning failure for application ID: ' . $application->id);
        try {
            // Find admin users to notify (adjust query based on how you identify admins)
            $adminUsers = User::where('is_admin', true)->get(); // Example: Assuming an 'is_admin' flag

            if ($adminUsers->isEmpty()) {
                Log::warning('No admin users found to notify about provisioning failure.');
                return;
            }

            // Use a Mailable for the failure notification
            // Ensure ProvisioningFailedNotification Mailable exists and is imported
            // This Mailable should contain details about the application and the error message.
            foreach ($adminUsers as $admin) {
                // Ensure the admin user has an email address
                if ($admin->email && filter_var($admin->email, FILTER_VALIDATE_EMAIL)) {
                    Mail::to($admin->email)->send(new ProvisioningFailedNotification($application, $errorMessage));
                } else {
                    Log::warning('Skipping admin notification for user ID ' . $admin->id . ' - Invalid or missing email.');
                }
            }

            Log::info('Admin notification sent for provisioning failure of application ID: ' . $application->id);
        } catch (Exception $e) { // Simplified \Exception
            // Log this failure but do NOT re-throw - the main process should not halt if admin notification fails
            Log::error('Failed to send admin notification for provisioning failure of application ID ' + $application->id + ': ' + $e->getMessage(), ['exception' => $e]); // Simplified \Exception
        }
    }


    // Add other service methods related to Email Applications as needed (e.g.,
    // createInitialApprovals, notifyApprovers, handleProvisioningCallback, etc.)
}
