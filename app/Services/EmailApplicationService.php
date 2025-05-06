<?php

namespace App\Services;

use App\Models\User;
use App\Models\EmailApplication;
use App\Models\Approval; // Import the Approval model
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification; // Used if sending notifications via facade
use Exception;

// Import Notification classes with proper use statements
use App\Notifications\EmailApplicationSubmitted; // Notification for applicant upon submission
use App\Notifications\NewEmailApplicationForApproval; // Notification for supporting officer
use App\Notifications\EmailProvisioningComplete; // Assuming you'll create this for applicant when processing is done
use App\Notifications\EmailApplicationRejected; // Assuming you'll create this for rejected applications
// Add other necessary Notification classes here as you create them

/**
 * Service class for managing EmailApplication lifecycle and workflow.
 */
class EmailApplicationService
{
  protected $emailProvisioningService;

  /**
   * Inject the EmailProvisioningService dependency.
   *
   * @param \App\Services\EmailProvisioningService $emailProvisioningService
   */
  public function __construct(EmailProvisioningService $emailProvisioningService)
  {
    $this->emailProvisioningService = $emailProvisioningService;
  }

  /**
   * Creates a new email application in draft status.
   * Handles database transaction for creation.
   *
   * @param \App\Models\User $applicant The user submitting the application.
   * @param array $data Validated data for the application.
   * @return \App\Models\EmailApplication The newly created draft application.
   * @throws \Exception If creation fails.
   */
  public function createApplication(User $applicant, array $data): EmailApplication
  {
    /** @var \App\Models\User $applicant */ // Add type hint for static analysis
    DB::beginTransaction();

    try {
      $application = new EmailApplication();
      // Fill only the mass assignable attributes from the validated data
      $application->fill($data);
      // Associate the application with the applicant user
      $application->user()->associate($applicant);
      // Set initial status to draft using model constant
      $application->status = EmailApplication::STATUS_DRAFT;
      // certification_timestamp is set when initiating approval workflow (submission), not draft creation

      $application->save();

      DB::commit();

      // Refactored logging line for static analysis
      $applicantId = $applicant->id ?? 'N/A';
      $applicationId = $application->id ?? 'N/A';
      Log::info("EmailApplication draft created", [
        'user_id' => $applicantId,
        'application_id' => $applicationId
      ]);

      // TODO: Optional: Notify applicant that draft is saved (requires creating EmailApplicationDraftSaved notification)
      // if ($applicant->personal_email || $applicant->email) {
      //     // Simplified Notification reference
      //     $applicant->notify(new EmailApplicationDraftSaved($application)); // Requires EmailApplicationDraftSaved Mailable
      // } else {
      //     Log::warning("Applicant user not found for application ID: " . ($application->id ?? 'N/A') . ". Cannot send draft saved notification.");
      // }


      return $application;
    } catch (Exception $e) {
      // Rollback transaction on error
      if (DB::transactionLevel() > 0) { // Check if a transaction is active
        DB::rollBack();
      }
      // Log the error with relevant context
      $applicantId = $applicant->id ?? 'N/A'; // Refactored for static analysis
      Log::error("Failed to create email application draft", [
        'error' => $e->getMessage(),
        'user_id' => $applicantId,
        'data' => $data // Include data for debugging
      ]);
      // Re-throw the exception for upstream handling
      throw $e;
    }
  }

  /**
   * Updates an existing email application that is in draft status.
   * Handles database transaction for update.
   *
   * @param \App\Models\EmailApplication $application The application instance to update (must be in draft status).
   * @param array $data Validated data for updating the application.
   * @return \App\Models\EmailApplication The updated draft application.
   * @throws \Exception If update fails or application is not in draft status.
   */
  public function updateApplication(EmailApplication $application, array $data): EmailApplication
  {
    /** @var \App\Models\EmailApplication $application */ // Add type hint for static analysis
    DB::beginTransaction();

    try {
      // Ensure only draft applications can be updated via this method using model helper
      if (!$application->isDraft()) {
        throw new Exception("Cannot update application. It is not in draft status.");
      }

      // Fill only the mass assignable attributes
      $application->fill($data);
      $application->save();

      DB::commit();

      // Refactored logging line for static analysis
      $applicationId = $application->id ?? 'N/A';
      Log::info("EmailApplication draft updated", [
        'application_id' => $applicationId
      ]);

      return $application;
    } catch (Exception $e) {
      // Rollback transaction on error
      if (DB::transactionLevel() > 0) { // Check if a transaction is active
        DB::rollBack();
      }
      // Log the error with relevant context
      $applicationId = $application->id ?? 'N/A'; // Refactored for static analysis
      Log::error("Failed to update email application draft", [
        'application_id' => $applicationId,
        'error' => $e->getMessage(),
        'data' => $data // Include data for debugging
      ]);
      // Re-throw the exception
      throw $e;
    }
  }

  /**
   * Initiates the approval workflow for a draft email application upon submission.
   * Sets status to 'pending_support', finds/validates the supporting officer,
   * creates the initial approval record, and sends notifications.
   * Expects supporting_officer_id to be pre-assigned to the $application model before calling this method.
   *
   * @param \App\Models\EmailApplication $application The draft application instance to submit.
   * @return \App\Models\EmailApplication The application after initiating workflow.
   * @throws \Exception If workflow initiation fails (e.g., not draft, supporting officer not found/assigned).
   */
  public function initiateEmailApplicationApprovalWorkflow(EmailApplication $application): EmailApplication
  {
    /** @var \App\Models\EmailApplication $application */ // Add type hint for static analysis
    DB::beginTransaction();

    try {
      // Use model helper method for status check.
      if (!$application->isDraft()) {
        $applicationId = $application->id ?? 'N/A'; // Refactored for static analysis
        Log::warning("Cannot initiate workflow. Application ID {$applicationId} is not in draft status.");
        throw new Exception("Cannot initiate workflow. Application ID {$applicationId} is not in draft status.");
      }

      // Check if supporting_officer_id is set and the relationship loads the officer
      // Eager load the supportingOfficer relationship before calling this method if possible
      // or check for the relationship object after accessing it.
      if (!$application->supportingOfficer) { // Check for the relationship object
        $applicationId = $application->id ?? 'N/A'; // Refactored for static analysis
        $supportingOfficerId = $application->supporting_officer_id ?? 'N/A'; // Refactored for static analysis
        Log::error("Supporting officer relationship not loaded or not found for application ID {$applicationId} with ID: {$supportingOfficerId} during workflow initiation.");
        // Throw exception as officer must be assigned for the workflow to start
        throw new Exception("Supporting officer could not be assigned. Please contact support.");
      }

      // The supporting officer user model is available via the relationship
      /** @var \App\Models\User $approver */ // Add type hint for static analysis
      $approver = $application->supportingOfficer;

      // Set application status to pending support review using model constant
      $application->status = EmailApplication::STATUS_PENDING_SUPPORT;
      // Set timestamp upon submission (workflow initiation)
      // Assuming submission_timestamp field exists based on previous discussion/potential migration.
      $application->submission_timestamp = now();
      $application->save();

      // Create the initial approval record for the supporting officer
      // Assumes Approval model has STATUS_PENDING and STAGE_SUPPORT_REVIEW constants
      /** @var \App\Models\Approval $approval */ // Add type hint for static analysis
      $approval = $application->approvals()->create([
        'officer_id' => $approver->id, // Use found approver ID
        'status' => Approval::STATUS_PENDING,
        'stage' => Approval::STAGE_SUPPORT_REVIEW,
        'created_by' => auth()->id(), // Assumes user is logged in and CreatedBy trait works
      ]);

      DB::commit();

      // TODO: Trigger notifications AFTER commit
      // Ensure Notification classes exist and constructors match.
      // Notify applicant that application is submitted. Requires Notifiable trait on User.
      if ($application->user) { // Ensure user relationship is loaded
        // Simplified Notification reference
        // $application->user->notify(new EmailApplicationSubmitted($application));
      } else {
        $applicationId = $application->id ?? 'N/A'; // Refactored for static analysis
        Log::warning("Applicant user not found for application ID: {$applicationId}. Cannot send submission notification.");
      }

      // Notify the supporting officer about the new application. Requires Notifiable trait on User.
      if ($approver) { // Check if approver user was found
        // Simplified Notification reference
        // $approver->notify(new NewEmailApplicationForApproval($application, $approval));
      } else {
        $applicationId = $application->id ?? 'N/A'; // Refactored for static analysis
        Log::warning("Supporting officer user notifiable user not found for application ID: {$applicationId}. Cannot send approval notification.");
      }


      // Refactored logging line for static analysis
      $applicationId = $application->id ?? 'N/A';
      $approverId = $approver->id ?? 'N/A';
      Log::info("EmailApplication workflow initiated", [
        'application_id' => $applicationId,
        'new_status' => $application->status ?? 'N/A',
        'first_approver_id' => $approverId
      ]);

      return $application;
    } catch (Exception $e) {
      // Rollback transaction on error
      if (DB::transactionLevel() > 0) { // Check if a transaction is active
        DB::rollBack();
      }
      // Refactored logging line for static analysis
      $applicationId = $application->id ?? 'N/A';
      Log::error("EmailApplication workflow initiation failed for application ID {$applicationId}: " . $e->getMessage(), ['exception' => $e]);
      // Re-throw the exception
      throw $e;
    }
  }

  /**
   * Handles the decision (approve/reject) made by a Supporting Officer for a specific approval step.
   * Updates the approval record and the application status.
   *
   * @param \App\Models\EmailApplication $application The application being reviewed.
   * @param \App\Models\Approval $approval The specific approval step for this officer.
   * @param string $decision The decision ('approved' or 'rejected').
   * @param string|null $comments Optional comments from the officer.
   * @param \App\Models\User $officer The officer making the decision.
   * @return \App\Models\EmailApplication The updated application.
   * @throws \Exception If the decision is invalid or the officer is unauthorized/approval not pending.
   */
  public function supportOfficerDecision(EmailApplication $application, Approval $approval, string $decision, ?string $comments, User $officer): EmailApplication
  {
    /** @var \App\Models\EmailApplication $application */ // Add type hint for static analysis
    /** @var \App\Models\Approval $approval */ // Add type hint for static analysis
    /** @var \App\Models\User $officer */ // Add type hint for static analysis
    DB::beginTransaction();
    try {
      // Validate that the officer making the decision is the one assigned to this approval step
      if (($approval->officer_id ?? null) !== ($officer->id ?? null)) {
        $applicationId = $application->id ?? 'N/A'; // Refactored for static analysis
        $approvalId = $approval->id ?? 'N/A'; // Refactored for static analysis
        $officerId = $officer->id ?? 'N/A'; // Refactored for static analysis
        Log::warning("Unauthorized officer attempted to make decision for application ID {$applicationId}, approval ID {$approvalId}, officer ID {$officerId}.");
        throw new Exception("Officer is not authorized to make this decision for this application.");
      }
      // Check if the approval step is still pending using Approval model constant
      if ($approval->status !== Approval::STATUS_PENDING) {
        $applicationId = $application->id ?? 'N/A'; // Refactored for static analysis
        $approvalId = $approval->id ?? 'N/A'; // Refactored for static analysis
        $officerId = $officer->id ?? 'N/A'; // Refactored for static analysis
        Log::warning("Decision already recorded for approval step for application ID {$applicationId}, approval ID {$approvalId}, officer ID {$officerId}. Current status: " . ($approval->status ?? 'N/A'));
        throw new Exception("Decision already recorded for this approval step.");
      }
      // Validate the decision value using Approval model constants
      if (!in_array($decision, [Approval::STATUS_APPROVED, Approval::STATUS_REJECTED])) {
        $applicationId = $application->id ?? 'N/A'; // Refactored for static analysis
        $approvalId = $approval->id ?? 'N/A'; // Refactored for static analysis
        $officerId = $officer->id ?? 'N/A'; // Refactored for static analysis
        Log::warning("Invalid decision status provided for application ID {$applicationId}, approval ID {$approvalId}, officer ID {$officerId}. Decision: {$decision}");
        throw new Exception("Invalid decision status provided.");
      }

      // Update the specific approval step record with the decision
      $approval->status = $decision;
      $approval->comments = $comments;
      $approval->approval_timestamp = now();
      $approval->updated_by = $officer->id; // Assumes UpdatedBy trait and updated_by column on Approval
      $approval->save();

      // Update the application status based on the decision
      if ($decision === Approval::STATUS_APPROVED) {
        // If approved by support, move to IT Admin pending status using Application constant
        $application->status = EmailApplication::STATUS_PENDING_ADMIN;
        // TODO: Implement logic for multiple approval stages if needed
        // (e.g., check if ALL required approvals for the current stage are 'approved' before moving to the NEXT stage)
      } elseif ($decision === Approval::STATUS_REJECTED) {
        // If rejected by support, set application status to rejected using Application constant
        $application->status = EmailApplication::STATUS_REJECTED;
        $application->rejection_reason = $comments ?? 'Rejected by supporting officer.'; // Save reason
      }
      // Assuming Application model also has updated_by via trait
      $application->updated_by = $officer->id;
      $application->save();

      DB::commit();

      // Refactored logging line for static analysis
      $applicationId = $application->id ?? 'N/A';
      $approvalId = $approval->id ?? 'N/A';
      $officerId = $officer->id ?? 'N/A';
      Log::info("Supporting officer decision recorded", [
        'application_id' => $applicationId,
        'approval_id' => $approvalId,
        'decision' => $decision,
        'officer_id' => $officerId,
        'new_application_status' => $application->status ?? 'N/A',
      ]);


      // TODO: Trigger notifications based on the final status AFTER commit
      // This requires creating additional Notification classes.
      // Example: Notify IT Admins if status becomes PENDING_ADMIN, Notify Applicant if REJECTED.
      // if ($application->isPendingAdmin()) { // Using helper method
      //     // Find IT Admins (e.g., by role or flag) - Requires logic to fetch IT Admins
      //      $itAdmins = User::where('is_admin', true)->get(); // Example fetching users with 'is_admin' flag
      //      if ($itAdmins->isNotEmpty()) {
      //          // Simplified Notification reference
      //          Notification::send($itAdmins, new \App\Notifications\EmailApplicationApprovedBySupport($application)); // Requires EmailApplicationApprovedBySupport Mailable
      //      } else {
      //           $applicationId = $application->id ?? 'N/A'; // Refactored for static analysis
      //           Log::warning("No IT Admins found to notify for approved application ID: {$applicationId}");
      //      }

      // } elseif ($application->isRejected()) { // Using helper method
      //      if ($application->user) {
      //          // Simplified Notification reference
      //          $application->user->notify(new EmailApplicationRejected($application, $comments));
      //      } else {
      //           $applicationId = $application->id ?? 'N/A'; // Refactored for static analysis
      //           Log::warning("Applicant user not found for application ID: {$applicationId}. Cannot send rejection notification.");
      //      }
      // }

      return $application;
    } catch (Exception $e) {
      // Rollback transaction on error
      if (DB::transactionLevel() > 0) { // Check if a transaction is active
        DB::rollBack();
      }
      // Refactored logging line for static analysis
      $applicationId = $application->id ?? 'N/A';
      $approvalId = $approval->id ?? 'N/A';
      $officerId = $officer->id ?? 'N/A';
      $decision = $decision ?? 'N/A'; // Handle case where decision might not be set before exception
      Log::error("Failed to record supporting officer decision for application ID {$applicationId}, approval ID {$approvalId}, officer ID {$officerId}, decision: {$decision}: " . $e->getMessage(), ['exception' => $e]);
      // Re-throw the exception
      throw $e;
    }
  }

  /**
   * Handles the state transition and updates related to IT Admin processing.
   * Updates application and user models based on admin input, calls provisioning service,
   * and manages the transaction.
   *
   * @param \App\Models\EmailApplication $application The application pending IT admin processing.
   * @param array $validatedData Validated data from IT Admin form (includes final_assigned_email, status, admin_notes, user_id_assigned).
   * @param \App\Models\User $admin The IT Admin user performing the action.
   * @return \App\Models\EmailApplication Updated application instance.
   * @throws \Exception If the application is not pending admin processing, associated user not found, or provisioning fails.
   */
  public function handleAdminProcessingUpdate(EmailApplication $application, array $validatedData, User $admin): EmailApplication
  {
    /** @var \App\Models\EmailApplication $application */ // Add type hint for static analysis
    /** @var \App\Models\User $admin */ // Add type hint for static analysis
    DB::beginTransaction();
    try {
      // Ensure valid state transition (e.g., from pending_admin) using model constant
      if (!$application->isPendingAdmin()) { // Using helper method
        $applicationId = $application->id ?? 'N/A'; // Refactored for static analysis
        Log::warning("Application ID {$applicationId} is not pending IT admin processing. Current status: " . ($application->status ?? 'N/A'));
        throw new Exception("Application ID {$applicationId} is not pending IT admin processing.");
      }

      // Update application fields based on admin input
      $application->final_assigned_email = $validatedData['final_assigned_email'] ?? null; // Use nullish coalescing
      // Set status based on IT admin choice (processing or completed, possibly rejected if you add that option)
      $application->status = $validatedData['status'];
      $application->admin_notes = $validatedData['admin_notes'] ?? null; // Assuming admin_notes column exists


      // --- IMPORTANT ---
      // Update the RELATED USER model with the assigned email and user ID.
      // These fields ('motac_email', 'user_id_assigned') are on the 'users' table per migrations.
      // Ensure the user relationship is loaded or load it here if necessary.
      // Loading the user relationship if not already loaded
      $application->loadMissing('user');
      if (!$application->user) {
        $applicationId = $application->id ?? 'N/A'; // Refactored for static analysis
        Log::error("Related user not found for email application ID: {$applicationId} during admin processing update.");
        throw new Exception("Associated user not found for application ID {$applicationId}.");
      }
      /** @var \App\Models\User $user */ // Add type hint for static analysis for the related user
      $user = $application->user;
      $user->motac_email = $validatedData['final_assigned_email'] ?? null; // Update user's MOTAC email
      $user->user_id_assigned = $validatedData['user_id_assigned'] ?? null; // Update user's external ID
      $user->save(); // Save changes to the related user model
      // --- END IMPORTANT ---

      // TODO: If provisioned_at is needed, add it via migration and set it here:
      if ($application->isCompleted()) { // Using helper method
        // Assuming provisioned_at field exists based on previous discussion/migration.
        $application->provisioned_at = now(); // Set timestamp when status is completed
      }

      // Set who updated the application (assuming UpdatedBy trait)
      $application->updated_by = $admin->id;
      $application->save(); // Save application updates

      // --- Call the Email Provisioning Service ONLY if status is moving to 'processing' or 'completed' ---
      // This prevents calling the external API if the admin just wants to save notes without processing.
      // The provisioning service will handle sending the welcome email on success.
      if ($application->isProcessing() || $application->isCompleted()) { // Using helper methods

        // Call the provisioning service with the updated application and user models.
        // This method interacts with the external API and returns success/error result.
        // It should NOT manage transactions or update status/user based on its *own* outcome.
        $provisioningResult = $this->emailProvisioningService->provisionAccount($application, $application->user); // Pass application and user

        // Process the result returned by the EmailProvisioningService
        if (($provisioningResult['status'] ?? 'error') === 'error') {
          // If provisioning API call returned an error status:
          // Update the application status to indicate failure and store rejection reason/notes.
          // Since we are still in the transaction managed by this method, these updates will be rolled back if commit fails.
          // Consider adding a STATUS_PROVISIONING_FAILED constant to EmailApplication model if this is a distinct state.
          $application->status = EmailApplication::STATUS_REJECTED; // Fallback to REJECTED or use specific failed status
          $application->rejection_reason = $provisioningResult['message'] ?? 'Provisioning API returned an error.';
          // Optionally add failure note to application notes
          $application->admin_notes = ($application->admin_notes ? $application->admin_notes . "\n" : '') . "Provisioning Failed: " . $application->rejection_reason;

          // Re-save application status update (within the transaction)
          $application->save();

          // The EmailProvisioningService might handle notifying the admin of failure on API error internally.
          // Or you can trigger notification here based on the failed status if preferred.
          // Find IT Admins (e.g., by role or flag) - Requires logic to fetch IT Admins
          // $itAdmins = User::where('is_admin', true)->get(); // Example fetching users with 'is_admin' flag
          // if ($itAdmins->isNotEmpty()) {
          //     // Simplified Notification reference
          //     Notification::send($itAdmins, new ProvisioningFailedNotification($application, $application->rejection_reason));
          // } else {
          //     $applicationId = $application->id ?? 'N/A'; // Refactored for static analysis
          //     Log::warning("Admin user not found/notifiable for provisioning failure notification for application ID: {$applicationId}");
          // }
        }
        // If provisioningResult['status'] is 'success', the provisioning service handled sending the welcome email.
        // The application status and User model were already updated above based on admin input.
      }
      // If the admin set a status other than processing/completed (e.g., rejected),
      // no provisioning call is made, and the rejection status is already set.

      DB::commit(); // Commit the transaction if all steps were successful

      // Refactored logging line for static analysis
      $applicationId = $application->id ?? 'N/A';
      $adminId = $admin->id ?? 'N/A';
      Log::info("Email application admin processing update handled.", [
        'application_id' => $applicationId,
        'final_status_after_update' => $application->status ?? 'N/A',
        'admin_id' => $adminId,
      ]);


      // TODO: Trigger notifications based on the FINAL status AFTER commit
      // Example: Notify applicant if status becomes COMPLETED or REJECTED.
      if ($application->isCompleted()) { // Using helper method
        if ($application->user) {
          // Simplified Notification reference
          // $application->user->notify(new EmailProvisioningComplete($application));
        } else {
          $applicationId = $application->id ?? 'N/A'; // Refactored for static analysis
          Log::warning("Applicant user not found for application ID: {$applicationId}. Cannot send completion notification.");
        }
      } elseif ($application->isRejected()) { // Using helper method
        if ($application->user) {
          // Simplified Notification reference
          $application->user->notify(new EmailApplicationRejected($application, $application->rejection_reason));
        } else {
          $applicationId = $application->id ?? 'N/A'; // Refactored for static analysis
          Log::warning("Applicant user not found for application ID: {$applicationId}. Cannot send rejection notification.");
        }
      }


      return $application; // Return the updated application instance

    } catch (Exception $e) {
      // Catch any exceptions thrown during this method (including exceptions from EmailProvisioningService)
      if (DB::transactionLevel() > 0) { // Check if a transaction is active before rolling back
        DB::rollBack(); // Roll back the transaction on error
      }
      // Refactored logging line for static analysis
      $applicationId = $application->id ?? 'N/A';
      $adminId = $admin->id ?? 'N/A';
      Log::error("Failed to handle admin processing update for email application ID {$applicationId}: " . $e->getMessage(), ['admin_id' => $adminId, 'exception' => $e]);

      // Re-throw the exception for upstream handling (e.g., in a controller/Livewire component)
      throw $e;
    }
  }

  /**
   * Deletes an email application draft (soft deletes).
   *
   * @param \App\Models\EmailApplication $application The application instance to delete.
   * @return bool True if deleted, false otherwise.
   * @throws \Exception If application is not in draft status or deletion fails.
   */
  public function deleteApplication(EmailApplication $application): bool
  {
    /** @var \App\Models\EmailApplication $application */ // Add type hint for static analysis
    DB::beginTransaction();
    try {
      // Use model helper method for status check. Only allow deleting drafts.
      if (!$application->isDraft()) {
        $applicationId = $application->id ?? 'N/A'; // Refactored for static analysis
        $userId = auth()->id() ?? 'N/A'; // Refactored for static analysis
        Log::warning("Attempted to delete non-draft email application. Application ID {$applicationId}, current status: " . ($application->status ?? 'N/A') . ", user ID: {$userId}");
        throw new Exception("Cannot delete application. Only drafts can be deleted.");
      }

      // Soft delete the application
      // The SoftDeletes trait handles setting deleted_at.
      // The CreatedUpdatedDeletedBy trait should handle setting deleted_by if applied and configured correctly.
      $deleted = $application->delete();

      if ($deleted) {
        DB::commit();
        $applicationId = $application->id ?? 'N/A'; // Refactored for static analysis
        $userId = auth()->id() ?? 'N/A'; // Refactored for static analysis
        Log::info("EmailApplication draft soft deleted successfully. Application ID {$applicationId}, user ID: {$userId}");
      } else {
        // This case is less common with soft deletes unless there's a DB issue
        DB::rollBack();
        $applicationId = $application->id ?? 'N/A'; // Refactored for static analysis
        $userId = auth()->id() ?? 'N/A'; // Refactored for static analysis
        Log::warning("EmailApplication soft deletion returned false. Application ID {$applicationId}, user ID: {$userId}");
      }

      return $deleted;
    } catch (Exception $e) {
      // Rollback transaction on error
      if (DB::transactionLevel() > 0) { // Check if a transaction is active
        DB::rollBack();
      }
      // Refactored logging line for static analysis
      $applicationId = $application->id ?? 'N/A'; // Refactored for static analysis
      $userId = auth()->id() ?? 'N/A'; // Refactored for static analysis
      Log::error("Failed to delete email application draft for application ID {$applicationId}: " . $e->getMessage(), ['user_id' => $userId, 'exception' => $e]);
      // Re-throw the exception
      throw $e;
    }
  }


  /**
   * Delegates email address suggestion logic to the EmailProvisioningService.
   *
   * @param \App\Models\User $user The user for whom to suggest an email.
   * @return string Suggested email address.
   * @throws \Exception If suggestion fails.
   */
  public function suggestEmailAddress(User $user): string
  {
    /** @var \App\Models\User $user */ // Add type hint for static analysis
    $userId = $user->id ?? 'N/A'; // Refactored for static analysis
    Log::debug("EmailApplicationService: Requesting email suggestion from EmailProvisioningService for user ID: {$userId}");
    try {
      // Delegates to the provisioning service.
      // Ensure EmailProvisioningService::generateEmail exists and is implemented correctly.
      return $this->emailProvisioningService->generateEmail($user);
    } catch (Exception $e) {
      // Log and re-throw exception from the provisioning service
      $userId = $user->id ?? 'N/A'; // Refactored for static analysis
      Log::error("EmailApplicationService: Failed to get email suggestion from EmailProvisioningService for user ID {$userId}: " . $e->getMessage(), ['exception' => $e]);
      throw $e; // Re-throw the original exception
    }
  }

  // --- Additional Service Methods ---

  /**
   * Finds a suitable supporting officer for the applicant and assigns it to the application.
   * This is a placeholder; logic needs to be implemented based on
   * organizational structure, department, grade, etc.
   *
   * @param \App\Models\EmailApplication $application The application to assign an officer to.
   * @return \App\Models\User|null The supporting officer, or null if none found/assigned.
   * @throws \Exception If an officer cannot be found or assigned.
   */
  public function assignSupportingOfficer(EmailApplication $application): ?User
  {
    /** @var \App\Models\EmailApplication $application */ // Add type hint for static analysis
    // TODO: Implement actual logic to find a supporting officer.
    // This logic should find the user and then assign their ID to $application->supporting_officer_id.
    // Example placeholder:
    // $officer = User::where('department_id', $application->user->department_id)
    //              ->where('is_supporting_officer', true) // Example criteria
    //              ->first();

    // if ($officer) {
    //     $application->supporting_officer_id = $officer->id;
    //     // Optionally set name if that column exists and is needed
    //     // $application->supporting_officer_name = $officer->name;
    //     $application->save(); // Save the assignment
    //     Log::info("Supporting officer assigned", ['application_id' => $application->id ?? 'N/A', 'officer_id' => $officer->id]);
    //     return $officer;
    // } else {
    //     Log::warning("No supporting officer found for application", ['application_id' => $application->id ?? 'N/A', 'applicant_id' => $application->user->id ?? 'N/A']);
    //     // Depending on requirements, you might throw an exception here if assignment is mandatory for submission
    //     // throw new Exception("Could not automatically assign a supporting officer.");
    //     return null;
    // }
    $applicationId = $application->id ?? 'N/A'; // Refactored for static analysis
    $applicantId = $application->user->id ?? 'N/A'; // Refactored for static analysis
    Log::warning("assignSupportingOfficer logic not implemented for application ID {$applicationId}, applicant ID: {$applicantId}.");

    // As it's a placeholder, let's assume it should throw if mandatory for submission flow
    throw new Exception("Supporting officer assignment logic is not implemented.");
  }

  // TODO: Add methods for IT Admin approval, rejection, and completion steps.
  // Example stubs:
  // public function adminApprove(EmailApplication $application, User $admin, ?string $adminNotes): EmailApplication { ... }
  // public function adminReject(EmailApplication $application, User $admin, string $rejectionReason): EmailApplication { ... }
  // public function adminMarkCompleted(EmailApplication $application, User $admin): EmailApplication { ... }
}
