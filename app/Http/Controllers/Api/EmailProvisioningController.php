<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\EmailApplication;
use App\Services\EmailProvisioningService;
use Illuminate\Support\Facades\Log;

class EmailProvisioningController extends Controller
{
  protected $emailProvisioningService;

  public function __construct(EmailProvisioningService $emailProvisioningService)
  {
    $this->emailProvisioningService = $emailProvisioningService;
  }

  /**
   * Handle a request to provision an email account.
   * This could be triggered by a webhook or an internal API call.
   */
  public function provision(Request $request)
  {
    // Validate the incoming request data
    $request->validate([
      'application_id' => 'required|exists:email_applications,id',
      // Add any other validation rules for data needed for provisioning
    ]);

    $applicationId = $request->input('application_id');
    $application = EmailApplication::findOrFail($applicationId);

    // Check if the application is in the correct status for provisioning (e.g., approved)
    if ($application->status !== 'approved') {
      return response()->json(['message' => 'Application is not in approved status.'], 400);
    }

    // Assuming final_assigned_email and final_assigned_user_id are set after approval
    if (empty($application->final_assigned_email) && empty($application->final_assigned_user_id)) {
      return response()->json(['message' => 'Final email or user ID not assigned yet.'], 400);
    }

    // Use the service to perform the provisioning logic
    $success = $this->emailProvisioningService->provisionAccount($application);

    if ($success) {
      // Update application status to completed
      $application->status = 'completed';
      $application->save();

      // Optionally send a completion notification to the user
      // $application->user->notify(new \App\Notifications\EmailProvisionedNotification($application));

      return response()->json(['message' => 'Email account provisioning initiated successfully.', 'application_status' => $application->status]);
    } else {
      // Handle provisioning failure
      $application->status = 'provisioning_failed'; // Or a dedicated failed status
      $application->save();
      Log::error("API provisioning failed for application ID: " . $applicationId);
      return response()->json(['message' => 'Failed to provision email account.'], 500);
    }
  }

  // Add other API methods as needed (e.g., for updating status from external system)
  // public function updateStatus(Request $request, EmailApplication $application)
  // {
  //     // Logic to update application status based on external system callback
  // }
}
