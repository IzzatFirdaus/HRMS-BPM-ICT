<?php

namespace App\Services;

use App\Models\User;
use App\Models\EmailApplication;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Exception;

// Optional: Uncomment and create these as needed
// use App\Notifications\NewEmailApplicationForApproval;
// use App\Notifications\EmailApplicationDraftSaved;

class EmailApplicationService
{
  protected $emailProvisioningService;

  public function __construct(EmailProvisioningService $emailProvisioningService)
  {
    $this->emailProvisioningService = $emailProvisioningService;
  }

  /**
   * Create a new draft email application.
   */
  public function createApplication(User $applicant, array $data): EmailApplication
  {
    DB::beginTransaction();

    try {
      $application = new EmailApplication();
      $application->fill($data);
      $application->user()->associate($applicant);
      $application->status = 'draft';
      $application->certification_accepted = $data['certification_accepted'] ?? false;
      $application->save();

      DB::commit();

      Log::info("EmailApplication draft created", ['user_id' => $applicant->id, 'id' => $application->id]);

      // Optional: Notify applicant
      // $applicant->notify(new EmailApplicationDraftSaved($application));

      return $application;
    } catch (Exception $e) {
      DB::rollBack();
      Log::error("Failed to create email application", ['error' => $e->getMessage()]);
      throw $e;
    }
  }

  /**
   * Update a draft email application.
   */
  public function updateApplication(EmailApplication $application, array $data): EmailApplication
  {
    DB::beginTransaction();

    try {
      $application->fill($data);
      $application->save();

      DB::commit();

      Log::info("EmailApplication updated", ['id' => $application->id]);

      return $application;
    } catch (Exception $e) {
      DB::rollBack();
      Log::error("Failed to update email application", ['id' => $application->id, 'error' => $e->getMessage()]);
      throw $e;
    }
  }

  /**
   * Start approval workflow for a draft application.
   */
  public function initiateApprovalWorkflow(EmailApplication $application): EmailApplication
  {
    DB::beginTransaction();

    try {
      if ($application->status !== 'draft') {
        throw new Exception("Cannot initiate workflow. Application is not in draft.");
      }

      if (!$application->supporting_officer_id) {
        throw new Exception("Cannot initiate workflow. Supporting officer not assigned.");
      }

      $approver = User::find($application->supporting_officer_id);

      if (!$approver) {
        throw new Exception("Supporting officer not found for ID: " . $application->supporting_officer_id);
      }

      $application->status = 'pending_support';
      $application->certification_timestamp = now();
      $application->save();

      $approval = $application->approvals()->create([
        'officer_id' => $approver->id,
        'status' => 'pending',
        'stage' => 'support_review',
      ]);

      DB::commit();

      Log::info("Workflow initiated", [
        'application_id' => $application->id,
        'officer_id' => $approver->id
      ]);

      // Optional: Notify approver
      // $approver->notify(new NewEmailApplicationForApproval($application, $approval));

      return $application;
    } catch (Exception $e) {
      DB::rollBack();
      Log::error("Workflow initiation failed", ['application_id' => $application->id, 'error' => $e->getMessage()]);
      throw $e;
    }
  }

  /**
   * Suggest email address based on user's name.
   */
  public function suggestEmailAddress(User $user): string
  {
    return $this->emailProvisioningService->generateEmail($user);
  }
}
