<?php

namespace App\Http\Controllers\Api; // Ensure the namespace is correct for your project (Api)

use App\Http\Controllers\Controller; // Extend the base Controller
use Illuminate\Http\Request; // Standard Request object
use App\Models\EmailApplication; // Import the EmailApplication model
use App\Services\EmailProvisioningService; // Import the EmailProvisioningService
use Illuminate\Support\Facades\Log; // Import Log facade
use Exception; // Import Exception for general errors
use Illuminate\Database\Eloquent\ModelNotFoundException; // Import ModelNotFoundException
use Illuminate\Support\Facades\Auth; // Import Auth facade for getting logged-in user - Correct import

class EmailProvisioningController extends Controller
{
  protected $emailProvisioningService;

  /**
   * Constructor to inject dependencies and apply security middleware.
   * This API endpoint MUST be protected with appropriate security measures.
   *
   * @param \App\Services\EmailProvisioningService $emailProvisioningService The provisioning service.
   */
  public function __construct(EmailProvisioningService $emailProvisioningService)
  {
    $this->emailProvisioningService = $emailProvisioningService;

    // !!! IMPORTANT SECURITY MEASURES !!!
    // Implement STRONG security for this API endpoint. It should ONLY be accessible
    // by trusted internal systems or authorized external services.
    // Choose one or more of the following methods and configure them properly:
    // 1. API Token Authentication: Laravel Sanctum (for SPAs/Mobile) or Passport (for traditional API tokens)
    //    Example: $this->middleware('auth:sanctum');
    //    Example: $this->middleware('auth:api');
    // 2. Custom Signature Verification: Verify the request payload using a shared secret and hashing.
    //    Example: $this->middleware('verify-request-signature'); // You create this middleware
    // 3. IP Whitelisting: Restrict access to known IP addresses of the triggering system.
    //    This is typically done at the web server level (Nginx/Apache) or via a middleware.
    // 4. Basic Authentication over HTTPS: Less secure than tokens, but possible if endpoints are internal.
    //    Example: $this->middleware('auth.basic');

    // Example middleware placeholder (uncomment and configure your chosen security method):
    // $this->middleware('your-chosen-api-security-middleware');
    // E.g., $this->middleware('auth:api');
  }

  /**
   * Handle a request to trigger email account provisioning for a specific application.
   * This API endpoint is typically triggered by an internal process, workflow engine,
   * or an authorized administrative action AFTER an application has been approved
   * and is ready for technical processing.
   *
   * @param  \Illuminate\Http\Request  $request  The incoming request, expecting 'application_id'.
   * @return \Illuminate\Http\JsonResponse
   */
  public function provision(Request $request)
  {
    // Validate the incoming request data
    // Ensure 'application_id' is present and corresponds to an existing email application
    $request->validate([
      'application_id' => 'required|integer|exists:email_applications,id',
      // Add any other validation rules if the API receives additional data
      // needed for provisioning (e.g., assigned email/user ID if not pre-filled)
      // 'assigned_email' => 'nullable|email|max:255',
      // 'assigned_user_id' => 'nullable|string|max:255',
    ]);

    $applicationId = $request->input('application_id');
    // FIX: Correctly use the imported Auth facade
    $loggedInUserId = Auth::check() ? Auth::id() : null; // Get user ID if triggered by an authenticated user/system

    Log::info("Attempting email provisioning for EmailApplication ID: " . $applicationId, [
      'triggered_by_user_id' => $loggedInUserId,
      'source_ip' => $request->ip()
    ]);

    try {
      // Retrieve the EmailApplication instance. Use findOrFail for route model binding style error handling.
      $application = EmailApplication::findOrFail($applicationId);
      // Alternatively, your original code's where()->firstOrFail() is also fine:
      // $application = EmailApplication::where('id', $applicationId)->firstOrFail();

    } catch (ModelNotFoundException $e) {
      Log::warning("EmailApplication ID: " . $applicationId . " not found for provisioning trigger.");
      // Return a 404 response if the application does not exist
      return response()->json(['message' => 'Email application not found.', 'code' => 'application_not_found'], 404);
    } catch (Exception $e) {
      // Catch other potential errors during retrieval
      Log::error("Error retrieving EmailApplication ID: " . $applicationId . " for provisioning: " . $e->getMessage());
      return response()->json(['message' => 'Error retrieving application details.', 'code' => 'retrieval_error'], 500);
    }


    // --- Workflow Status Checks ---
    // Prevent processing if the application is not in the correct state for provisioning.
    // Align these statuses with your EmailApplication status ENUM in the database and your workflow.
    // The system design mentions 'pending_admin' and 'processing' as relevant states before 'completed'.
    $allowedStatusesForProvisioning = ['pending_admin']; // Application is approved by supporting officer and ready for IT Admin action/API trigger
    $inProgressStatus = 'processing'; // Status when provisioning is ongoing
    $alreadyProcessedStatuses = ['completed', 'provisioning_failed']; // Prevent reprocessing these final states

    if (in_array($application->status, $alreadyProcessedStatuses)) {
      Log::warning("Attempted to provision EmailApplication ID: " . $applicationId . " but status is already '" . $application->status . "'. Provisioning skipped (already in final state).");
      // Return OK status as it's already in a final state, indicating the trigger was received but processing isn't needed again.
      // Provide the current status in the response.
      return response()->json([
        'message' => 'Email account provisioning has already reached a final state for this application.',
        'application_status' => $application->status,
        'code' => 'already_in_final_state'
      ], 200);
    }

    // If the application is already 'processing', maybe return 200 or 409 depending on desired API idempotency behavior
    if ($application->status === $inProgressStatus) {
      Log::warning("Attempted to provision EmailApplication ID: " . $applicationId . " but status is already '" . $application->status . "'. Provisioning skipped (already in progress).");
      return response()->json([
        'message' => 'Email account provisioning is already in progress for this application.',
        'application_status' => $application->status,
        'code' => 'already_in_progress'
      ], 200); // Or 409 Conflict if you want to indicate the request conflicts with current state
    }

    if (!in_array($application->status, $allowedStatusesForProvisioning)) {
      Log::warning("Attempted to provision EmailApplication ID: " . $applicationId . " but status is '" . $application->status . "'. Provisioning skipped (not in allowed state).");
      // Return a specific status code indicating the application is not in a provisionable state
      return response()->json([
        'message' => 'Application is not in a state ready for provisioning.',
        'current_status' => $application->status,
        'allowed_statuses' => $allowedStatusesForProvisioning,
        'code' => 'not_in_provisionable_state'
      ], 409); // 409 Conflict
    }
    // --- End Workflow Status Checks ---


    // Ensure final assigned email/user ID is set before attempting provisioning.
    // This assumes these are determined during the approval or a previous step.
    // Based on the workflow, the IT Admin *should* fill this before triggering the API.
    $hasAssignedEmail = !empty($application->final_assigned_email);
    $hasAssignedUserId = !empty($application->final_assigned_user_id);

    if (!$hasAssignedEmail && !$hasAssignedUserId) {
      Log::error("Attempted to provision EmailApplication ID: " . $applicationId . " but final email/user ID is not assigned.");
      // Update status to indicate a blocking issue if needed. Using a specific status like 'assignment_missing' is good.
      $application->status = 'assignment_missing'; // Example status, ensure this exists in your enum
      $application->save();
      return response()->json([
        'message' => 'Final email or user ID was not assigned before provisioning attempt.',
        'code' => 'assignment_missing'
      ], 400); // 400 Bad Request
    }

    // Update status to indicate provisioning is now in progress BEFORE calling the potentially long-running service
    // This prevents duplicate processing if the API is triggered multiple times concurrently.
    if ($application->status !== $inProgressStatus) {
      $application->status = $inProgressStatus;
      $application->save();
      Log::info("EmailApplication ID: " . $applicationId . " status updated to 'processing'.");
    }


    try {
      // Use the service to perform the actual provisioning logic (integration with external system).
      // Pass the single EmailApplication model instance.
      // The service should handle communication with external email systems and update the application status upon completion/failure.
      $provisioningResult = $this->emailProvisioningService->provisionAccount($application); // Assume service returns true/false or a result object

      // Re-fetch the application to get its latest status and assigned details after the service call completes
      $application->refresh();

      if ($provisioningResult === true || ($provisioningResult && is_object($provisioningResult) && property_exists($provisioningResult, 'success') && $provisioningResult->success === true)) {
        // Provisioning was successful according to the service
        Log::info("Email account provisioning service reported success for application ID: " . $applicationId . ". Final Status: " . $application->status);
        return response()->json([
          'message' => 'Email account provisioning process reported success.',
          'application_status' => $application->status, // Report final status from the service
          'assigned_email' => $application->final_assigned_email,
          'assigned_user_id' => $application->final_assigned_user_id,
          'code' => 'provisioning_success'
        ], 200);
      } else {
        // Provisioning failed according to the service
        Log::error("Email account provisioning service reported failure for application ID: " . $applicationId . ". Final Status: " . $application->status);
        return response()->json([
          'message' => 'Email account provisioning service reported failure.',
          'application_status' => $application->status, // Report final status from the service (should be failed)
          'code' => 'provisioning_service_failed'
        ], 500); // 500 Internal Server Error
      }
    } catch (Exception $e) {
      // Catch exceptions thrown by the service or during the process
      Log::error("An unexpected exception occurred during email provisioning for application ID: " . $applicationId . ". Error: " . $e->getMessage());

      // Attempt to update application status to show error if not already handled by service
      $application->refresh(); // Get latest status
      $finalStatuses = ['completed', 'provisioned']; // Don't overwrite these if service already set them
      if (!in_array($application->status, $finalStatuses)) {
        $application->status = 'provisioning_failed'; // Example status, ensure this exists in your enum
        $application->save();
        Log::info("EmailApplication ID: " . $applicationId . " status updated to 'provisioning_failed' due to exception.");
      }


      return response()->json([
        'message' => 'An unexpected error occurred during email provisioning.',
        'error_details' => $e->getMessage(), // Be cautious about exposing raw error messages in production APIs
        'application_status' => $application->status, // Report the status after attempting update
        'code' => 'unexpected_error'
      ], 500); // 500 Internal Server Error
    }
  }

  // Add other API methods as needed (e.g., for external systems to update status or report issues)
  // The commented-out updateStatus method is a good example of how an external system
  // could communicate back to update the application status asynchronously.
  // Ensure strong security is applied to any such endpoints.

  /**
   * Example endpoint for an external system to update the application status.
   * This would require authentication and careful validation.
   *
   * @param \Illuminate\Http\Request $request Expects 'application_id', 'status', etc.
   * @return \Illuminate\Http\JsonResponse
   */
  // public function updateStatus(Request $request)
  // {
  //      // !!! IMPORTANT !!! Secure this endpoint with the same or higher security than the provision endpoint.
  //      // Example: $this->middleware('your-chosen-api-security-middleware');

  //      $request->validate([
  //           'application_id' => 'required|integer|exists:email_applications,id',
  //           'status' => ['required', Rule::in(['provisioned', 'failed', 'completed', 'error_details', 'etc.'])], // Validate allowed statuses from external system
  //           'external_reference_id' => 'nullable|string|max:255', // Optional: ID from the external system
  //           'details' => 'nullable|string', // Optional: Status message or error details from external system
  //      ]);

  //      $applicationId = $request->input('application_id');
  //      $newStatus = $request->input('status');
  //      $externalReferenceId = $request->input('external_reference_id');
  //      $details = $request->input('details');

  //      Log::info("Received status update for EmailApplication ID: " . $applicationId . " from external system. New status: " . $newStatus);

  //      try {
  //           $application = EmailApplication::findOrFail($applicationId);

  //           // Add logic to prevent status being downgraded or set incorrectly if needed
  //           // E.g., Only allow status transitions that make sense (processing -> provisioned/failed/completed)

  //           $application->status = $newStatus;
  //           // Assuming your EmailApplication model has these fields
  //           // $application->external_reference_id = $externalReferenceId;
  //           // $application->external_status_details = $details; // Store details if applicable

  //           $application->save();

  //           Log::info("EmailApplication ID: " . $application->id . " status successfully updated to: " . $application->status . " by external system.");

  //           return response()->json(['message' => 'Application status updated successfully.', 'application_status' => $application->status], 200);

  //      } catch (ModelNotFoundException $e) {
  //           Log::warning("Status update received for non-existent EmailApplication ID: " . $applicationId . ".");
  //           return response()->json(['message' => 'Email application not found.', 'code' => 'application_not_found'], 404);
  //      } catch (Exception $e) {
  //           Log::error("An error occurred processing status update for EmailApplication ID: " . $applicationId . ": " . $e->getMessage());
  //           return response()->json(['message' => 'An error occurred while updating application status.', 'code' => 'update_error', 'error' => $e->getMessage()], 500);
  //      }
  // }
}
