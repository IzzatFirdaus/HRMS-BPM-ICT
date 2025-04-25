<?php

namespace App\Http\Controllers\Api; // Ensure the namespace is correct for your project (Api)

use App\Http\Controllers\Controller; // Extend the base Controller
use Illuminate\Http\Request; // Standard Request object
use App\Models\EmailApplication; // Import the EmailApplication model
use App\Services\EmailProvisioningService; // Import the EmailProvisioningService
use Illuminate\Support\Facades\Log; // Import Log facade
use Exception; // Import Exception
use Illuminate\Database\Eloquent\ModelNotFoundException; // Import ModelNotFoundException

class EmailProvisioningController extends Controller
{
  protected $emailProvisioningService;

  /**
   * Constructor to inject dependencies and apply middleware.
   * This endpoint should be protected with appropriate security measures.
   */
  public function __construct(EmailProvisioningService $emailProvisioningService)
  {
    $this->emailProvisioningService = $emailProvisioningService;

    // !!! IMPORTANT !!!
    // Implement strong security for this API endpoint.
    // This could include:
    // - API Token authentication middleware (e.g., $this->middleware('auth:api');)
    // - Checking for a valid signature on the request payload
    // - IP whitelisting if triggered from a known source
    // - Custom middleware to verify the request origin or credentials

    // Example middleware placeholder (uncomment and configure):
    // $this->middleware('api-token-check'); // Assuming you create this middleware
  }

  /**
   * Handle a request to provision an email account for a specific application.
   * This endpoint is typically triggered by an internal process, workflow engine,
   * or an authorized administrative action after an application has been approved.
   *
   * @param  \Illuminate\Http\Request  $request  The incoming request.
   * @return \Illuminate\Http\JsonResponse
   */
  public function provision(Request $request)
  {
    // Validate the incoming request data
    // Ensure 'application_id' is present and corresponds to an existing email application
    $request->validate([
      'application_id' => 'required|integer|exists:email_applications,id',
      // Add any other validation rules for data needed for provisioning,
      // e.g., if the final assigned email/user ID is sent in the request.
    ]);

    $applicationId = $request->input('application_id');

    try {
      // Retrieve the EmailApplication instance.
      // Using where()->firstOrFail() explicitly gets a single model or throws an exception.
      $application = EmailApplication::where('id', $applicationId)->firstOrFail();
    } catch (ModelNotFoundException $e) {
      Log::warning("EmailApplication ID: " . $applicationId . " not found for provisioning.");
      // Return a 404 response if the application does not exist
      return response()->json(['message' => 'Email application not found.'], 404);
    }


    // --- Workflow Status Checks ---
    // Prevent reprocessing if the application is not in the correct state for provisioning.
    // Assuming 'approved' is the status that triggers provisioning.
    // Also check if it's already being processed or completed to ensure idempotency.
    $allowedStatusesForProvisioning = ['approved', 'ready_for_processing']; // Adjust based on your workflow statuses
    $alreadyProcessedStatuses = ['processing', 'provisioned', 'completed', 'provisioning_failed', 'assignment_missing']; // Prevent reprocessing these

    if (!in_array($application->status, $allowedStatusesForProvisioning)) {
      Log::warning("Attempted to provision EmailApplication ID: " . $applicationId . " but status is '" . $application->status . "'. Provisioning skipped.");
      // Return a specific status code indicating the application is not in a provisionable state
      return response()->json(['message' => 'Application is not in a state ready for provisioning. Current status: ' . $application->status], 409); // 409 Conflict

    }

    if (in_array($application->status, $alreadyProcessedStatuses)) {
      Log::warning("Attempted to provision EmailApplication ID: " . $applicationId . " but status is already '" . $application->status . "'. Provisioning skipped (already processed).");
      // Return OK status as it's already in a final/processing state, indicating the trigger was received but processing isn't needed again.
      return response()->json(['message' => 'Email account provisioning has already been initiated or completed for this application.', 'application_status' => $application->status], 200);
    }
    // --- End Workflow Status Checks ---


    // Ensure final assigned email/user ID is set before attempting provisioning.
    // This assumes these are determined during the approval or a previous step.
    if (empty($application->final_assigned_email) && empty($application->final_assigned_user_id)) {
      Log::error("Attempted to provision EmailApplication ID: " . $applicationId . " but final email/user ID is not assigned.");
      // Update status to indicate a blocking issue if needed
      $application->status = 'assignment_missing'; // Example status
      $application->save();
      return response()->json(['message' => 'Final email or user ID was not assigned before provisioning attempt.'], 400);
    }

    // Optional: Update status to indicate provisioning is now in progress
    if ($application->status !== 'processing') {
      $application->status = 'processing';
      $application->save();
    }


    try {
      // Use the service to perform the provisioning logic.
      // Pass the single EmailApplication model instance.
      $success = $this->emailProvisioningService->provisionAccount($application);

      if ($success) {
        // The service should ideally update the status to 'provisioned' or 'completed'.
        // Re-fetch or check the application status after the service call
        $application->refresh(); // Get the latest status from DB

        // If the service handles the final status update:
        return response()->json(['message' => 'Email account provisioning process finished.', 'application_status' => $application->status], 200);
      } else {
        // The service should ideally handle setting the 'provisioning_failed' status.
        // Re-fetch or check the application status after the service call
        $application->refresh(); // Get the latest status from DB

        Log::error("API provisioning failed for application ID: " . $applicationId . ". Final Status: " . $application->status);
        return response()->json(['message' => 'Email account provisioning failed.', 'application_status' => $application->status], 500);
      }
    } catch (Exception $e) {
      // Catch exceptions thrown by the service or during the process
      Log::error("An exception occurred during email provisioning for application ID: " . $applicationId . ". Error: " . $e->getMessage());

      // Optionally update application status to show error if not already handled by service
      $application->refresh(); // Get latest status
      if ($application->status !== 'provisioning_failed' && $application->status !== 'completed' && $application->status !== 'provisioned') {
        $application->status = 'provisioning_failed'; // Set status to failed if not already a final status
        $application->save();
      }

      return response()->json(['message' => 'An error occurred during email provisioning.', 'error' => $e->getMessage()], 500);
    }
  }

  // Add other API methods as needed (e.g., for external systems to update status or report issues)
  /**
   * Example endpoint for an external system to update the application status.
   * This would require authentication and careful validation.
   */
  // public function updateStatus(Request $request)
  // {
  //     // !!! IMPORTANT !!! Secure this endpoint
  //     // $this->middleware('api-signature-check'); // Example middleware

  //     $request->validate([
  //         'application_id' => 'required|integer|exists:email_applications,id',
  //         'status' => ['required', Rule::in(['provisioned', 'failed', 'completed', 'etc.'])], // Validate allowed statuses
  //         'external_id' => 'nullable|string', // Optional: ID from the external system
  //         'message' => 'nullable|string', // Optional: Status message from external system
  //     ]);

  //     try {
  //          $application = EmailApplication::where('id', $request->input('application_id'))->firstOrFail();
  //     } catch (ModelNotFoundException $e) {
  //         return response()->json(['message' => 'Email application not found.'], 404);
  //     }


  //     // Update application status based on external system's report
  //     // Consider a more robust update logic via a service method
  //     $application->status = $request->input('status');
  //     $application->external_id = $request->input('external_id'); // Save external ID if applicable
  //     // You might store the message in a log or a dedicated field
  //     $application->save();

  //     Log::info("EmailApplication ID: " . $application->id . " status updated by external system to: " . $application->status);

  //     return response()->json(['message' => 'Application status updated successfully.', 'application_status' => $application->status], 200);
  // }
}
