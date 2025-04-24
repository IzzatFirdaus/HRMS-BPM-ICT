<?php

namespace App\Services;

use App\Models\User;
use App\Models\EmailApplication;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class EmailApplicationService
{
  protected $emailProvisioningService;

  public function __construct(EmailProvisioningService $emailProvisioningService)
  {
    $this->emailProvisioningService = $emailProvisioningService;
  }

  /**
   * Create a new email application.
   *
   * @param User $applicant
   * @param array $data Application data from the form
   * @return EmailApplication
   */
  public function createApplication(User $applicant, array $data): EmailApplication
  {
    // Ensure policy check is done before calling this method

    // Basic data mapping
    $application = new EmailApplication($data);
    $application->user()->associate($applicant);
    $application->status = 'draft'; // Initial status

    // Generate and set a proposed email if not provided
    if (empty($application->proposed_email)) {
      $application->proposed_email = $this->suggestEmailAddress($applicant);
    }

    $application->save();

    return $application;
  }

  /**
   * Submit an email application from draft status.
   *
   * @param EmailApplication $application
   * @param array $data Data including certification status
   * @return EmailApplication
   */
  public function submitApplication(EmailApplication $application, array $data): EmailApplication
  {
    // Ensure policy check is done before calling this method
    // Update application with final data from submission (e.g., certification)
    $application->fill($data); // Fill certified data
    $application->status = 'pending_support'; // Move to the next status
    $application->certification_timestamp = now(); // Record submission time
    $application->save();

    // Trigger notification to the supporting officer
    // You would need logic here to find the correct approver(s) based on department/structure
    // Example (simplified):
    // $approver = User::where('grade_id', '>=', config('motac.approval.min_approver_grade_level'))
    //               ->where('department_id', $application->user->department_id) // Or other logic
    //               ->first();
    // if ($approver) {
    //     $approver->notify(new \App\Notifications\NewEmailApplicationPending($application)); // You'd create this Notification
    // }


    return $application;
  }


  /**
   * Suggest a potential email address based on the user's name.
   *
   * @param User $user
   * @return string
   */
  public function suggestEmailAddress(User $user): string
  {
    // Call the method from the Provisioning Service for consistency
    return $this->emailProvisioningService->generateEmail($user);
  }

  // Add other business logic methods here, e.g.,
  // public function processApprovedApplication(EmailApplication $application): EmailApplication;
  // public function assignFinalCredentials(EmailApplication $application, string $email = null, string $userId = null): EmailApplication;
}
