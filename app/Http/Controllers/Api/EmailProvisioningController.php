<?php

namespace App\Http\Controllers\Api; // Ensure the namespace is correct for your project (Api)

use App\Http\Controllers\Controller; // Extend the base Controller
use Illuminate\Http\Request; // Standard Request object
use App\Models\EmailApplication; // Import the EmailApplication model
use App\Services\EmailProvisioningService; // Import the EmailProvisioningService
use Illuminate\Support\Facades\Log; // Import Log facade
use Exception; // Import Exception for general errors
use Illuminate\Database\Eloquent\ModelNotFoundException; // Import ModelNotFoundException
use Illuminate\Support\Facades\Auth; // Import Auth facade for getting logged-in user
use Illuminate\Validation\Rule; // Import Rule for validation (needed for updateStatus example)
use Illuminate\Support\Facades\Validator; // Import Validator facade for manual validation if not using Form Requests
use App\Models\User; // Import User model


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
    // Choose one or more of the following:
    // - API Tokens (e.g., Laravel Sanctum)
    // - Signed Requests
    // - IP Whitelisting
    // - HTTP Basic Authentication over HTTPS
    // - Dedicated API Gateway Authentication
    // DO NOT rely solely on the 'auth:api' middleware unless your API authentication
    // is robust and appropriate for system-to-system communication.
    // Example using Sanctum (requires 'Laravel\Sanctum\HasApiTokens' trait on User model):
    // $this->middleware('auth:sanctum');

    // For this example, adding a simple bearer token check or similar might be a starting point,
    // but a dedicated, verifiable mechanism is strongly recommended for production.

    // Example basic check (replace with your actual secure method):
    // if (request()->header('X-Internal-Api-Key') !== config('services.internal_api.key')) {
    //     abort(401, 'Unauthorized - Invalid API Key');
    // }
  }

  /**
   * Handles incoming requests to provision a new email account for an approved application.
   * This endpoint would typically be called by an internal process or potentially an external trigger
   * after an application has been approved by the IT Admin.
   *
   * Expected Request Body (JSON):
   * {
   * "application_id": 123, // The ID of the approved EmailApplication
   * "final_assigned_email": "john.doe@motac.gov.my", // The actual email address to create
   * "user_id_assigned": "johndoe123", // Optional: User ID in the external system
   * "provisioned_at": "YYYY-MM-DD HH:MM:SS" // Optional: Timestamp of provisioning if done externally
   * // Add other relevant data needed for provisioning
   * }
   *
   * @param  \Illuminate\Http\Request  $request The incoming API request.
   * @return \Illuminate\Http\JsonResponse
   */
  public function process(Request $request): \Illuminate\Http\JsonResponse // Add return type hint
  {
    // Log the incoming request (mask sensitive data if necessary)
    Log::info('Received API request to process email provisioning.', [
      'ip_address' => $request->ip(),
      // Avoid logging sensitive data like passwords or full request body in production logs
      // 'request_data' => $request->all(),
    ]);

    // 1. Validate Incoming Data
    // Use Laravel's Validator facade for API validation
    $validator = Validator::make($request->all(), [
      'application_id'       => ['required', 'integer', Rule::exists('email_applications', 'id')],
      'final_assigned_email' => 'required|email|max:255',
      'user_id_assigned'     => 'nullable|string|max:255',
      'provisioned_at'       => 'nullable|date',
      // Add validation rules for any other data needed for provisioning
    ]);

    if ($validator->fails()) {
      Log::warning('Email provisioning API request validation failed.', [
        'ip_address' => $request->ip(),
        'errors'     => $validator->errors()->toArray(),
      ]);
      return response()->json([
        'message' => 'Invalid data provided.',
        'errors'  => $validator->errors(),
        'code'    => 'validation_failed',
      ], 422); // 422 Unprocessable Entity
    }

    $validatedData = $validator->validated();

    try {
      // 2. Find the Email Application
      // Use findOrFail to automatically throw ModelNotFoundException if not found
      $application = EmailApplication::findOrFail($validatedData['application_id']);

      // *** FIX 2: Added debug log to check type before service call ***
      \Log::debug('Type of $application before processProvisioning: ' . get_class($application));


      // 3. Ensure Application is in the correct status for API provisioning (e.g., STATUS_APPROVED)
      // This check is critical. Only process applications that have been fully approved internally.
      // Assumes EmailApplication model has isApproved() helper method or STATUS_APPROVED constant
      if (!$application->isApproved()) { // Use isApproved() or check status constant
        Log::warning('API provisioning requested for application not in APPROVED status.', [
          'application_id' => $application->id,
          'current_status' => $application->status,
          'ip_address'     => $request->ip(),
        ]);
        return response()->json([
          'message' => 'Email application is not in an approved status for provisioning.',
          'application_status' => $application->status,
          'code'    => 'invalid_status',
        ], 409); // 409 Conflict
      }

      // 4. Delegate the actual provisioning to the service
      // Pass the application model and validated data.
      // The service should handle:
      // - Interacting with the external system (creating the email account).
      // - Updating the application status based on the provisioning outcome (completed/failed).
      // - Updating the related User model with assigned email/ID.
      // - Handling errors from the external system.
      // - Ensuring atomicity (using DB transactions if needed within service).
      // *** Call the appropriate method in the service (processProvisioning) ***
      // Note: Auth::user() might not be available or meaningful in an API context unless you are
      // authenticating the API caller as a specific user. The service method expects a User object
      // as the third argument, which represents the *admin user* performing the action *internally*.
      // If this API is called by an external system, you might need a different way to represent
      // who is triggering this action, or adjust the service signature if the admin user is not needed.
      // For now, let's assume a mechanism provides a User or pass null if the service allows.
      // If Auth::user() is null in this API context, the service method might need adjustment.
      // If the service requires the *internal IT Admin* who approved it, you might need to
      // fetch that from the application's approval history or the application record itself.
      // Assuming the service method is flexible or Auth::user() *is* the API caller user:
      $provisioningOutcome = $this->emailProvisioningService->processProvisioning( // Corrected method name
        $application,
        $validatedData,
        // Pass a User object representing the action triggerer.
        // If API is system-to-system, you might create a 'System' user or pass null.
        // If API is user-authenticated, pass Auth::user().
        // Let's pass null for now, assuming the service method can handle it or this API doesn't need it.
        // Or, ideally, fetch the approving admin user from the application's history if the service needs THAT specific user.
        // Example fetching approving user: $application->latestApproval?->officer // Requires relationships
        null // Passing null - adjust service signature if it requires a User and cannot be null
        // Or pass a placeholder system user: User::find(config('app.system_user_id'))
      );


      // 5. Return Response based on Service Outcome
      // The service should ideally return a structured result indicating success or failure.
      // Assuming $provisioningOutcome contains a 'status' key ('completed' or 'failed')
      if ($provisioningOutcome['status'] === 'completed') {
        Log::info('Email provisioning API call successful.', ['application_id' => $application->id]);
        return response()->json([
          'message' => 'Email account provisioned successfully.',
          'application_status' => $application->fresh()->status, // Return fresh status
          'code'    => 'provisioning_successful',
          // Include any relevant data from provisioningOutcome
          'assigned_email' => $application->fresh()->final_assigned_email,
        ], 200); // 200 OK
      } else { // Status is 'failed' or similar
        Log::warning('Email provisioning API call reported failure.', [
          'application_id' => $application->id,
          'failure_reason' => $provisioningOutcome['message'] ?? 'Unknown failure.',
          'code'    => 'provisioning_failed',
        ]);
        return response()->json([
          'message' => $provisioningOutcome['message'] ?? 'Email account provisioning failed.',
          'application_status' => $application->fresh()->status, // Return fresh status
          'code'    => 'provisioning_failed',
        ], 500); // 500 Internal Server Error or a more specific 4xx status like 424 Failed Dependency
      }
    } catch (ModelNotFoundException $e) {
      Log::warning('API provisioning requested for non-existent EmailApplication ID: ' . ($validatedData['application_id'] ?? 'N/A') . '.', [
        'ip_address' => $request->ip(),
      ]);
      return response()->json(['message' => 'Email application not found.', 'code' => 'application_not_found'], 404); // 404 Not Found
    } catch (Exception $e) {
      // Catch any exceptions thrown by the service layer (e.g., external API errors).
      Log::error('Exception caught in EmailProvisioningController@process for application ID ' . ($validatedData['application_id'] ?? 'N/A') . ': ' . $e->getMessage(), ['ip_address' => $request->ip(), 'exception' => $e]);
      // Return a generic error message in production, log details.
      $errorMessage = config('app.debug') ? $e->getMessage() : 'An internal error occurred during provisioning.';
      return response()->json(['message' => $errorMessage, 'code' => 'internal_error'], 500); // 500 Internal Server Error
    }
  }

  /**
   * Example endpoint for receiving status updates from an external system.
   * This is separate from the initial provisioning trigger.
   *
   * Expected Request Body (JSON):
   * {
   * "external_application_id": "xyz789", // ID from external system (if stored)
   * "application_id": 123, // Or the internal application ID
   * "new_status": "active", // Status from external system (e.g., 'active', 'suspended', 'deleted')
   * "details": "Account is now active.", // Optional: details from external system
   * "timestamp": "YYYY-MM-DD HH:MM:SS" // Optional: Timestamp of status change in external system
   * }
   *
   * @param Request $request
   * @return \Illuminate\Http\JsonResponse
   */
  // public function updateStatus(Request $request): \Illuminate\Http\JsonResponse
  // {
  //      // Implement robust security checks for this endpoint as well.

  //      $validator = Validator::make($request->all(), [
  //           // Validate identifier: Either application_id or external_application_id must be present
  //           'application_id' => ['sometimes', 'required_without:external_application_id', 'integer', Rule::exists('email_applications', 'id')],
  //           'external_application_id' => ['sometimes', 'required_without:application_id', 'string', 'max:255'], // Requires a field for this in DB
  //           'new_status' => ['required', 'string', 'max:50'], // Validate against expected external statuses
  //           'details' => 'nullable|string',
  //           'timestamp' => 'nullable|date',
  //      ]);

  //      if ($validator->fails()) {
  //           return response()->json(['message' => 'Invalid data for status update.', 'errors' => $validator->errors()], 422);
  //      }

  //      $validatedData = $validator->validated();

  //      try {
  //           // Find the application using either internal ID or external ID
  //           if (isset($validatedData['application_id'])) {
  //                $application = EmailApplication::findOrFail($validatedData['application_id']);
  //           } elseif (isset($validatedData['external_application_id'])) {
  //                // This requires storing external_application_id on the EmailApplication model
  //                $application = EmailApplication::where('external_application_id', $validatedData['external_application_id'])->firstOrFail();
  //           } else {
  //                // Should not happen due to validation, but as a safeguard
  //                return response()->json(['message' => 'Application identifier missing.'], 400); // Bad Request
  //           }

  //           // Update the application status based on the external system's status
  //           // You'll need logic here to map external statuses to your internal statuses
  //           // Example mapping: 'active' -> 'completed', 'suspended' -> 'suspended', 'deleted' -> 'cancelled'
  //           $newStatus = match($validatedData['new_status']) {
  //                'active' => EmailApplication::STATUS_COMPLETED,
  //                'suspended' => EmailApplication::STATUS_SUSPENDED, // Assumes SUSPENDED constant exists
  //                'deleted' => EmailApplication::STATUS_CANCELLED, // Assuming cancelled is the equivalent
  //                // Add other mappings
  //                default => $application->status, // Keep current status if external status is unknown
  //           };


  //           // Update application model (and potentially related User model)
  //           // Only update status if it's a valid transition or different from current
  //           // Add checks here to ensure status transitions are allowed before saving
  //           // if ($application->status !== $newStatus && $application->canTransitionTo($newStatus)) { // Assumes canTransitionTo method exists
  //           //      $application->status = $newStatus;
  //           //      $application->save();
  //           // }

  //           // Assuming your EmailApplication model has these fields to store external references
  //           // $application->external_reference_id = $validatedData['external_application_id'] ?? $application->external_reference_id; // Requires migration
  //           // $application->external_status_details = $validatedData['details'] ?? $application->external_status_details; // Requires migration
  //           // $application->last_synced_at = $validatedData['timestamp'] ?? now(); // Requires migration


  //           // Log the successful update
  //           Log::info("EmailApplication ID: " . $application->id . " status successfully updated to: " . $newStatus . " by external system.");

  //           // Return a success response
  //           return response()->json(['message' => 'Application status updated successfully.', 'application_status' => $application->status], 200);


  //      } catch (ModelNotFoundException $e) {
  //           // Log and return 404 if application is not found
  //           $applicationId = $validatedData['application_id'] ?? 'N/A';
  //           Log::warning("Status update received for non-existent EmailApplication ID: " . $applicationId . ".");
  //           return response()->json(['message' => 'Email application not found.', 'code' => 'application_not_found'], 404); // 404 Not Found

  //      } catch (Exception $e) {
  //           // Log and return 500 for other errors
  //           $applicationId = $validatedData['application_id'] ?? 'N/A';
  //           Log::error("An error occurred processing status update for EmailApplication ID: " . $applicationId . ": " . $e->getMessage());
  //           // Return a generic error message in production, log details.
  //           $errorMessage = config('app.debug') ? $e->getMessage() : 'An error occurred while updating application status.';
  //           return response()->json(['message' => $errorMessage, 'code' => 'update_error', 'error' => config('app.debug') ? $e->getMessage() : 'Server Error'], 500); // Conditionally expose error
  //      }
  // }
} // Ensure this closing brace is present.
