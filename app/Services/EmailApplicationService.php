<?php

namespace App\Services;

use App\Models\User;
use App\Models\EmailApplication;
use App\Models\Approval;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Exception;

// Notification classes—you’ll need to create these under app/Notifications
use App\Notifications\EmailApplicationSubmitted;
use App\Notifications\NewEmailApplicationForApproval;
// Assuming these exist based on previous discussion/code:
// use App\Notifications\EmailProvisioningComplete;
// use App\Notifications\EmailApplicationRejected;


class EmailApplicationService
{
  protected EmailProvisioningService $emailProvisioningService;

  /**
   * Inject the EmailProvisioningService dependency.
   */
  public function __construct(EmailProvisioningService $emailProvisioningService)
  {
    $this->emailProvisioningService = $emailProvisioningService;
  }

  /**
   * Create a new draft email application.
   *
   * @param  array  $validatedData
   * @param  User   $applicant
   * @return EmailApplication
   * @throws Exception
   */
  public function createApplication(array $validatedData, User $applicant): EmailApplication
  {
    Log::debug('Creating new email application draft', ['user_id' => $applicant->id]);

    DB::beginTransaction();
    try {
      $app = new EmailApplication();
      $app->user_id = $applicant->id;
      $app->fill($validatedData);
      $app->status = EmailApplication::STATUS_DRAFT;
      $app->save();

      Log::info('Draft email application created', ['application_id' => $app->id]);
      DB::commit();

      return $app;
    } catch (Exception $e) {
      DB::rollBack();
      Log::error('Failed to create draft', ['error' => $e->getMessage()]);
      throw new Exception('Gagal mencipta permohonan e-mel: ' . $e->getMessage());
    }
  }

  /**
   * Update an existing draft email application.
   *
   * @param  EmailApplication  $application
   * @param  array             $validatedData
   * @param  User              $user
   * @return EmailApplication
   * @throws Exception
   */
  public function updateApplication(EmailApplication $application, array $validatedData, User $user): EmailApplication
  {
    Log::debug('Updating email application draft', ['application_id' => $application->id]);

    if (!$application->isDraft()) {
      throw new Exception('Permohonan bukan dalam status draf.');
    }
    if ($application->user_id !== $user->id) {
      throw new Exception('Anda tidak dibenarkan mengemaskini permohonan ini.');
    }

    DB::beginTransaction();
    try {
      $application->fill($validatedData);
      $application->save();

      Log::info('Draft updated', ['application_id' => $application->id]);
      DB::commit();

      return $application->fresh();
    } catch (Exception $e) {
      DB::rollBack();
      Log::error('Failed to update draft', ['error' => $e->getMessage()]);
      throw new Exception('Gagal mengemaskini permohonan e-mel: ' . $e->getMessage());
    }
  }

  /**
   * Submit a draft application for approval.
   *
   * @param  EmailApplication  $application
   * @param  array             $validatedData
   * @param  User              $applicant
   * @return EmailApplication
   * @throws Exception
   */
  public function submitApplication(EmailApplication $application, array $validatedData, User $applicant): EmailApplication
  {
    Log::debug('Submitting email application', ['application_id' => $application->id]);

    if (!$application->isDraft() || $application->user_id !== $applicant->id) {
      throw new Exception('Permohonan tidak sah untuk dihantar.');
    }

    DB::beginTransaction();
    try {
      // Optionally update fields before submission
      // $application->fill($validatedData);

      $application->status               = EmailApplication::STATUS_PENDING_SUPPORT;
      $application->submission_timestamp = now();
      $application->save();

      // Find or assign supporting officer
      $officer = $this->assignSupportingOfficer($application);
      if (! $officer) {
        // Revert status if officer assignment fails and it's mandatory
        $application->status = EmailApplication::STATUS_REJECTED; // Example: mark as rejected due to config/setup error
        $application->rejection_reason = 'Ralat sistem: Pegawai penyokong tidak dapat ditentukan secara automatik.';
        $application->save();
        DB::commit(); // Commit the status change
        throw new Exception('Tiada pegawai penyokong dapat ditentukan secara automatik.');
      }

      // Create initial Approval record
      $approval = Approval::create([
        'approvable_id'   => $application->id,
        'approvable_type' => EmailApplication::class,
        'officer_id'      => $officer->id,
        'status'          => Approval::STATUS_PENDING,
        'stage'           => Approval::STAGE_SUPPORT_REVIEW,
      ]);

      // Notify applicant & supporting officer
      Notification::send($applicant, new EmailApplicationSubmitted($application));
      Notification::send($officer,   new NewEmailApplicationForApproval($application, $approval));

      Log::info('Application submitted and notifications sent', [
        'application_id' => $application->id,
        'approval_id'    => $approval->id,
        'officer_id'     => $officer->id,
      ]);

      DB::commit();
      return $application->fresh();
    } catch (Exception $e) {
      DB::rollBack();
      Log::error('Submission failed', ['error' => $e->getMessage()]);
      throw new Exception('Gagal menghantar permohonan e-mel: ' . $e->getMessage());
    }
  }

  /**
   * Delete a draft email application.
   *
   * @param  EmailApplication  $application
   * @param  User              $user
   * @return bool
   * @throws Exception
   */
  public function deleteApplication(EmailApplication $application, User $user): bool
  {
    Log::debug('Deleting email application draft', ['application_id' => $application->id]);

    if (!$application->isDraft() || $application->user_id !== $user->id) {
      throw new Exception('Permohonan tidak sah untuk dipadam.');
    }

    DB::beginTransaction();
    try {
      $deleted = $application->delete();
      DB::commit();
      Log::info('Draft deleted', ['application_id' => $application->id]);
      return $deleted;
    } catch (Exception $e) {
      DB::rollBack();
      Log::error('Deletion failed', ['error' => $e->getMessage()]);
      throw new Exception('Gagal memadam permohonan e-mel: ' . $e->getMessage());
    }
  }

  /**
   * Assign a supporting officer based on business rules.
   *
   * @param  EmailApplication  $application
   * @return User|null
   */
  public function assignSupportingOfficer(EmailApplication $application): ?User // FIX: Changed visibility from protected to public
  {
    $minGrade = (int) config('motac.approval.min_support_grade_level', 41); // Assumes this config exists

    /** @var User|null $officer */
    $officer = User::whereHas('grade', fn($q) => $q->where('level', '>=', $minGrade))
      // Add more complex logic here to filter by department/location if needed
      // ->whereHas('department', fn($q) => $q->where('id', $application->user->department_id)) // Filter by applicant's department ID
      ->first(); // Get the first matching user

    if ($officer) {
      Log::debug('Supporting officer found', [
        'application_id' => $application->id,
        'officer_id'     => $officer->id,
      ]);
    } else {
      Log::warning('No supporting officer found', ['application_id' => $application->id]);
    }

    return $officer;
  }

  /**
   * Handles the IT Admin processing step for an approved email application.
   * This involves calling the external provisioning service and updating the final status.
   * This method is typically called by the ApprovalService or EmailAccountController after IT Admin approval.
   *
   * @param EmailApplication $application The email application being processed (should be in STATUS_APPROVED or similar).
   * @param array $validatedData Validated data from the IT Admin form (e.g., final_assigned_email, admin_notes).
   * @param User $adminUser The IT Admin performing the processing.
   * @return EmailApplication The updated email application model.
   * @throws Exception If processing fails (e.g., provisioning error).
   */
  public function process(EmailApplication $application, array $validatedData, User $adminUser): EmailApplication
  {
    Log::debug('Starting IT Admin processing for EmailApplication ID: ' . $application->id, ['admin_user_id' => $adminUser->id]);

    // Ensure the application is in a state ready for processing (e.g., internally approved)
    if ($application->status !== EmailApplication::STATUS_APPROVED) { // Use the 'approved' status constant after admin approval
      Log::warning('Attempted to process EmailApplication not in APPROVED status.', ['application_id' => $application->id, 'status' => $application->status]);
      throw new Exception('Permohonan tidak dalam status yang sesuai untuk diproses oleh IT Admin.');
    }

    DB::beginTransaction();
    try {
      // 1. Update application fields from IT Admin input
      // It's crucial that final_assigned_email is provided and valid here.
      $application->final_assigned_email = $validatedData['final_assigned_email'] ?? $application->final_assigned_email; // Allow updating if needed
      $application->admin_notes = $validatedData['admin_notes'] ?? $application->admin_notes;
      $application->provisioned_at = $validatedData['provisioned_at'] ?? now(); // Use provided date or now
      // Do NOT change status yet - the provisioning service determines the final status.
      $application->save();

      Log::info('EmailApplication record updated with IT Admin input.', ['application_id' => $application->id, 'final_email' => $application->final_assigned_email]);

      // 2. Call the EmailProvisioningService to interact with the external system
      // This service handles the actual provisioning and reports the outcome.
      // It should return a structured response or throw specific exceptions.
      if (!$this->emailProvisioningService) {
        Log::critical('EmailProvisioningService is not injected! Cannot proceed with processing.');
        throw new Exception('Internal system error: Email provisioning service not available.');
      }

      try {
        // Call the provisioning service method (which should handle external API call)
        // Pass the necessary data, including the email to provision and the user.
        // The provisioning service is responsible for setting the final status (completed/failed)
        // and potentially returning external identifiers or error details.
        $provisioningResult = $this->emailProvisioningService->processProvisioning(
          $application, // Pass the application model
          $validatedData, // Pass IT Admin data (may contain final email, etc.)
          $adminUser // Pass the admin user performing the action
        );

        // Process the outcome reported by the provisioning service
        if ($provisioningResult['status'] === 'success') {
          // Provisioning reported success by the external system
          $application->status = EmailApplication::STATUS_COMPLETED; // Use constant
          // Optionally store external user ID if returned by the service
          // $application->external_user_id = $provisioningResult['external_user_id'] ?? null;
          Log::info('Email provisioning service reported success.', ['application_id' => $application->id]);
        } else {
          // Provisioning reported failure by the external system
          $application->status = EmailApplication::STATUS_PROVISIONING_FAILED; // Use constant
          $application->rejection_reason = $provisioningResult['message'] ?? 'External provisioning failed.';
          // Optionally store API error code or details
          // $application->api_error_code = $provisioningResult['api_error_code'] ?? null;
          Log::warning('Email provisioning service reported failure.', ['application_id' => $application->id, 'reason' => $application->rejection_reason]);
        }

        // Save the application with the final status determined by provisioning
        $application->save();
        DB::commit(); // Commit the transaction

        // 3. Trigger post-processing notifications (optional)
        if ($application->status === EmailApplication::STATUS_COMPLETED) {
          // Notify the applicant their email is ready
          // This notification might include credentials and the new email address.
          // The EmailProvisioningService might also send this welcome email directly.
          // If ProvisioningService sends welcome email, avoid duplicate notification here.
          // Example if this service sends it: $this->notifyApplicantOfProvisioningComplete($application); // Needs implementation
        } else { // Provisioning failed
          // Notify relevant parties (e.g., admin team) about the failure
          // The EmailProvisioningService might also handle admin notifications on failure.
          // Example if this service sends it: $this->notifyAdminOfProvisioningFailure($application); // Needs implementation
        }

        Log::info('IT Admin processing completed for EmailApplication ID: ' . $application->id . '. Final Status: ' . $application->status);
      } catch (Exception $e) {
        // Catch exceptions thrown by the EmailProvisioningService call itself (e.g., network error)
        // In this case, the provisioning attempt failed due to a system/connectivity issue.
        Log::error('Exception during call to EmailProvisioningService::processProvisioning for EmailApplication ID: ' . $application->id . ': ' . $e->getMessage(), ['exception' => $e]);

        // Rollback the transaction if it's still active (only application updates were committed before service call)
        if (DB::transactionLevel() > 0) {
          DB::rollBack();
        }

        // Update the application status to provisioning failed due to system error
        // Refresh the model in case it was partially updated before the exception
        $application->refresh();
        $application->status = EmailApplication::STATUS_PROVISIONING_FAILED; // Use constant
        $application->rejection_reason = 'Ralat sistem semasa peruntukan e-mel: ' . $e->getMessage(); // Store system error as reason
        $application->save(); // Save the failed status outside the original transaction if it was rolled back

        // Notify admins about this critical system error (if not already done by provisioning service)
        // Example: $this->notifyAdminOfCriticalProvisioningFailure($application, $e); // Needs implementation

        // Re-throw for the calling controller/service to handle
        throw new Exception('Gagal memproses peruntukan e-mel disebabkan ralat sistem: ' . $e->getMessage(), 0, $e);
      }


      return $application->fresh(); // Return the updated application model

    } catch (Exception $e) {
      // Catch any other exceptions during the initial update/checks
      if (DB::transactionLevel() > 0) {
        DB::rollBack();
      }
      Log::error('Exception during IT Admin processing setup for EmailApplication ID: ' . ($application->id ?? 'N/A') . ': ' . $e->getMessage(), ['exception' => $e]);
      throw new Exception('Gagal memproses permohonan e-mel: ' . $e->getMessage());
    }
  }

  // TODO: Add methods for other potential admin actions if not covered by process()
  // e.g., manually mark completed, manually mark failed, etc.

  // Note: Approval/rejection logic handled in ApprovalService.
}
