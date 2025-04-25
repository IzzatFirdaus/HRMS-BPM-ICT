<?php

namespace App\Services;

use App\Models\Approval; // Import the Approval model
use App\Models\User; // Import the User model
use Illuminate\Database\Eloquent\Model; // Import Model
use Illuminate\Support\Facades\DB; // Import DB facade for transactions
use Illuminate\Support\Facades\Log; // Import Log facade
use Illuminate\Support\Facades\Notification; // For sending notifications
use Exception; // Import base Exception class
use Illuminate\Database\Eloquent\ModelNotFoundException; // Import ModelNotFoundException if needed

// Import specific approvable models for type hinting or instanceof checks
use App\Models\EmailApplication;
use App\Models\LoanApplication;

// Import Notification classes (create these if they don't exist)
// use App\Notifications\EmailApplicationRejectedNotification; // Notify applicant of rejection
// use App\Notifications\LoanApplicationRejectedNotification; // Notify applicant of rejection
// use App\NewEmailApplicationPendingAdminNotification; // Notify IT Admins of pending review
// use App\NewLoanApplicationApprovedBPMNotification; // Notify BPM staff of approved loan
// use App\Notifications\LoanApplicationRejectedNotification; // Notify applicant of rejection


class ApprovalService
{
  // Inject any services needed for subsequent workflow steps (e.g., provisioning, notifying BPM)
  protected $emailProvisioningService; // Inject if final approval triggers provisioning
  // protected $loanApplicationService; // Inject if final approval triggers making equipment available for issuance or notifying BPM

  // Inject dependencies
  // Make injected services nullable as they might not be needed for all approval types/stages
  public function __construct(EmailProvisioningService $emailProvisioningService = null /*, LoanApplicationService $loanApplicationService = null */)
  {
    $this->emailProvisioningService = $emailProvisioningService;
    // $this->loanApplicationService = $loanApplicationService; // Uncomment and inject if needed
  }

  /**
   * Record an 'approved' decision for a specific approval record and update the approvable status.
   * This method is typically called by a controller or Livewire component (e.g., ApprovalDashboard).
   *
   * @param Approval $approval The specific approval record being processed (must be in 'pending' status).
   * @param User $approver The user making the approval decision (must be the assigned officer).
   * @param string|null $comments Optional comments from the approver.
   * @return Approval The updated approval record.
   * @throws \Exception If the approval is not pending or an error occurs.
   */
  public function recordApprovalDecision(Approval $approval, User $approver, ?string $comments = null): Approval
  {
    // Ensure policy check is done in the controller before calling this method (e.g., can('update', $approval))
    // Additional check here for safety/robustness: ensure the approval is still pending and assigned to the user
    if ($approval->status !== 'pending' || $approval->officer_id !== $approver->id) {
      Log::warning("Attempted to record approval on non-pending or unassigned Approval ID: " . $approval->id . " by user: " . $approver->id);
      throw new Exception("This approval task is not pending or not assigned to you.");
    }


    DB::beginTransaction(); // Start database transaction

    try {
      // 1. Update the status of the specific Approval record being processed
      $approval->status = 'approved'; // Mark this specific approval step as approved
      $approval->approval_timestamp = now(); // Set the timestamp
      $approval->comments = $comments; // Store comments on the approval record
      $approval->save();

      // 2. Update the status of the related approvable model based on the approval decision and workflow stage
      // This method contains the core workflow transition logic.
      $this->updateApprovableStatus($approval->approvable, 'approved', $approver);

      DB::commit(); // Commit the transaction

      // 3. Trigger notifications or next steps based on the *new* status of the approvable model
      // This includes finding the next approver, creating the next Approval record, or triggering final actions.
      $this->triggerWorkflowSteps($approval->approvable, $approval);


      Log::info("Approval decision recorded for Approval ID: " . $approval->id . ". Decision: Approved by officer: " . $approver->id . ". Approvable status updated to: " . $approval->approvable->status);

      return $approval; // Return the updated approval record (with relationships loaded if needed)
    } catch (Exception $e) {
      // Check if transaction is active before rolling back
      if (DB::transactionLevel() > 0) {
        DB::rollBack(); // Rollback the transaction on error
      }
      Log::error("Failed to record approval decision for Approval ID: " . $approval->id . ". Error: " . $e->getMessage());
      throw $e; // Re-throw the exception
    }
  }

  /**
   * Record a 'rejected' decision for a specific approval record and update the approvable status.
   * This method is typically called by a controller or Livewire component (e.g., ApprovalDashboard).
   *
   * @param Approval $approval The specific approval record being processed (must be in 'pending' status).
   * @param User $rejector The user making the rejection decision (must be the assigned officer).
   * @param string|null $comments Optional comments from the rejector.
   * @return Approval The updated approval record.
   * @throws \Exception If the approval is not pending or unassigned, or an error occurs.
   */
  public function recordRejectionDecision(Approval $approval, User $rejector, ?string $comments = null): Approval
  {
    // Ensure policy check is done in the controller before calling this method (e.g., can('update', $approval))
    // Additional check here for safety/robustness: ensure the approval is still pending and assigned to the user
    if ($approval->status !== 'pending' || $approval->officer_id !== $rejector->id) {
      Log::warning("Attempted to record rejection on non-pending or unassigned Approval ID: " . $approval->id . " by user: " . $rejector->id);
      throw new Exception("This approval task is not pending or not assigned to you.");
    }


    DB::beginTransaction(); // Start database transaction

    try {
      // 1. Update the status of the specific Approval record being processed
      $approval->status = 'rejected'; // Mark this specific approval step as rejected
      $approval->approval_timestamp = now(); // Set the timestamp
      $approval->comments = $comments; // Store comments on the approval record
      $approval->save();

      // 2. Update the status of the related approvable model to 'rejected'
      // Rejection at any stage typically marks the entire application as rejected and stops the workflow.
      $this->updateApprovableStatus($approval->approvable, 'rejected', $rejector);

      DB::commit(); // Commit the transaction

      // 3. Trigger notifications or next steps based on the *new* status ('rejected')
      // This typically involves notifying the applicant.
      $this->triggerWorkflowSteps($approval->approvable, $approval);

      Log::info("Approval decision recorded for Approval ID: " . $approval->id . ". Decision: Rejected by officer: " . $rejector->id . ". Approvable status set to: " . $approval->approvable->status);

      return $approval; // Return the updated approval record
    } catch (Exception $e) {
      // Check if transaction is active before rolling back
      if (DB::transactionLevel() > 0) {
        DB::rollBack(); // Rollback the transaction on error
      }
      Log::error("Failed to record rejection decision for Approval ID: " . $approval->id . ". Error: " . $e->getMessage());
      throw $e; // Re-throw the exception
    }
  }


  /**
   * Update the status of the approvable model based on the approval decision and current status/stage.
   * This method contains the core workflow state transition logic for Email and Loan Applications.
   *
   * @param Model $approvable The model being approved/rejected (EmailApplication or LoanApplication).
   * @param string $decision 'approved' or 'rejected' status of the current approval step.
   * @param User $decidingOfficer The user who made the decision.
   */
  protected function updateApprovableStatus(Model $approvable, string $decision, User $decidingOfficer): void
  {
    // --- Implement your specific workflow state transitions here ---
    // This logic determines the NEXT status of the approvable based on its CURRENT status and the decision made.

    if ($decision === 'rejected') {
      // If *any* approval step is rejected, the entire application is rejected.
      $approvable->status = 'rejected';
      // Optionally, store the rejection reason from the current approval record onto the approvable model
      $approvable->rejection_reason = "Rejected at stage '" . ($approvable->approvals()->latest()->first()?->stage ?? 'unknown') . "' by " . $decidingOfficer->full_name . ($approvable->approvals()->latest()->first()?->comments ? ": " . $approvable->approvals()->latest()->first()?->comments : '');
      Log::info(get_class($approvable) . " ID: " . $approvable->id . " rejected. Status set to 'rejected'.");
    } elseif ($decision === 'approved') {
      // If approved, determine the next status based on the current status and approvable type.
      if ($approvable instanceof EmailApplication) {
        switch ($approvable->status) {
          case 'pending_support':
            // Approved at Support review stage -> Move to IT Admin review stage
            $approvable->status = 'pending_admin';
            Log::info("EmailApplication ID: " . $approvable->id . " approved at pending_support. Status set to 'pending_admin'.");
            break;
          case 'pending_admin':
            // Approved at IT Admin review stage -> Ready for provisioning
            $approvable->status = 'approved'; // Or 'ready_for_processing', 'pending_provisioning'
            Log::info("EmailApplication ID: " . $approvable->id . " approved at pending_admin. Status set to 'approved'.");
            // The actual provisioning is triggered after this status update (see triggerWorkflowSteps).
            break;
          // Add cases for other potential intermediate statuses if your workflow has them
          default:
            // Handle unexpected status or no further status change needed at this stage
            Log::warning("Approved EmailApplication ID: " . $approvable->id . " in unexpected status for transition: " . $approvable->status);
            break;
        }
      } elseif ($approvable instanceof LoanApplication) {
        switch ($approvable->status) {
          case 'pending_support':
            // Approved at Support review stage -> Ready for BPM issuance
            $approvable->status = 'approved'; // Status changes from pending_support to 'approved'
            Log::info("LoanApplication ID: " . $approvable->id . " approved at pending_support. Status set to 'approved'.");
            // BPM staff will see 'approved' applications and perform the issuance step separately.
            break;
          // Add cases for other potential intermediate statuses if your workflow has them
          default:
            // Handle unexpected status or no further status change needed at this stage
            Log::warning("Approved LoanApplication ID: " . $approvable->id . " in unexpected status for transition: " . $approvable->status);
            break;
        }
      }
      // Add elseif blocks for other approvable types if needed
    }

    // Save the approvable model with the updated status
    if ($approvable->isDirty('status')) {
      $approvable->save();
      Log::info(get_class($approvable) . " ID: " . $approvable->id . " status updated to: " . $approvable->status);
    } else {
      Log::info(get_class($approvable) . " ID: " . $approvable->id . " status did not change in updateApprovableStatus.");
    }

    // --- End of workflow status transition logic ---
  }

  /**
   * Trigger subsequent workflow steps and notifications based on the new status.
   * This includes creating the next approval task, notifying relevant parties, or triggering final actions like provisioning.
   *
   * @param Model $approvable The model that was updated (EmailApplication or LoanApplication).
   * @param Approval $processedApproval The specific approval record that was processed.
   */
  protected function triggerWorkflowSteps(Model $approvable, Approval $processedApproval): void
  {
    // --- Implement actions based on the approvable's NEW status ---

    // Action on Rejection: Notify the applicant
    if ($approvable->status === 'rejected') {
      // Ensure the approvable has a 'user' relationship to the applicant
      if ($approvable->user) {
        // $approvable->user->notify(new EmailApplicationRejectedNotification($approvable, $processedApproval)); // Use specific notification class
        Log::info("Notifying user " . $approvable->user_id . " about rejected " . get_class($approvable) . " ID: " . $approvable->id);
        // TODO: Implement specific EmailApplicationRejectedNotification and LoanApplicationRejectedNotification
      } else {
        Log::warning("Cannot notify user for rejected " . get_class($approvable) . " ID: " . $approvable->id . ". User relationship missing.");
      }
    }

    // Action on Approval leading to next pending stage (Email Application only)
    if ($approvable instanceof EmailApplication) {
      if ($approvable->status === 'pending_admin') {
        // Application moved to IT Admin review. Find IT Admins and create the next Approval record.
        $nextStage = 'admin_review';
        $nextApproverRole = 'it_admin'; // Role for the next approver(s)

        // Find users who should approve the next stage
        $nextApprovers = User::whereHas('roles', fn($q) => $q->where('name', $nextApproverRole))->get(); // Example using Spatie roles
        // Or find based on grade, department, etc.

        if ($nextApprovers->isEmpty()) {
          Log::critical("No users found with role '" . $nextApproverRole . "' to assign next approval for EmailApplication ID: " . $approvable->id);
          // Consider notifying an admin that the workflow is stuck
        } else {
          // Create the next Approval record(s) assigned to the next approver(s)
          foreach ($nextApprovers as $nextApprover) {
            $this->createNextApproval($approvable, $nextApprover, $nextStage); // Create one approval record per officer
          }
          // Notify the next approver(s) that they have a pending approval task
          // Notification::send($nextApprovers, new NewEmailApplicationPendingAdminNotification($approvable, $processedApproval)); // Create this notification
          Log::info("Created next approval task(s) for EmailApplication ID: " . $approvable->id . " and notified IT Admins.");
        }
      } elseif ($approvable->status === 'approved') {
        // Email Application is fully approved and ready for provisioning.
        // Trigger the provisioning process using the EmailProvisioningService.
        // Ensure the service is injected and not null.
        if ($this->emailProvisioningService) {
          try {
            // Call the provisionAccount method on the injected service
            $this->emailProvisioningService->provisionAccount($approvable);
            Log::info("Triggered email provisioning for EmailApplication ID: " . $approvable->id);
            // The provisionAccount method handles updating the status to 'completed' or 'provision_failed'
            // and notifying the user about their new email.
          } catch (Exception $e) {
            // Catch exceptions from the provisioning service
            Log::error("Failed to trigger or execute provisioning for EmailApplication ID: " . $approvable->id . ": " . $e->getMessage());
            // The provisioning service should ideally handle updating the application status to indicate failure.
            // You might notify an admin here about the provisioning failure.
          }
        } else {
          Log::critical("EmailProvisioningService is not injected! Cannot trigger provisioning for EmailApplication ID: " . $approvable->id);
          // Consider updating the application status to 'provisioning_service_missing' or similar and notifying an admin
        }
      }
    } // Add elseif block for LoanApplication specific triggers

    // Action on Approval leading to final stage (Email or Loan)
    if ($approvable->status === 'approved') {
      if ($approvable instanceof LoanApplication) {
        // Loan Application is fully approved and ready for BPM issuance.
        // Notify BPM staff that a new loan application is approved and ready for issuance.
        $bpmStaffRole = 'bpm_staff'; // Role for BPM staff
        $bpmStaff = User::whereHas('roles', fn($q) => $q->where('name', $bpmStaffRole))->get(); // Example using Spatie roles

        if ($bpmStaff->isEmpty()) {
          Log::warning("No users found with role '" . $bpmStaffRole . "' to notify about approved LoanApplication ID: " . $approvable->id);
          // Consider notifying an admin
        } else {
          // Notification::send($bpmStaff, new NewLoanApplicationApprovedBPMNotification($approvable, $processedApproval)); // Create this notification
          Log::info("Notified BPM staff about approved LoanApplication ID: " . $approvable->id . ".");
        }
        // The actual issuance is triggered separately by BPM staff (EquipmentChecklist component).
      }
      // Email Application 'approved' status triggers provisioning as handled above.
    }

    // Add other status-based triggers here if needed (e.g., notify applicant when status becomes 'issued' for loan)
    // These notifications could be sent when LoanTransaction status is updated in LoanApplicationService.

    // Optional: Send a generic notification about the decision being recorded to the officer who made the decision
    // $processedApproval->officer->notify(new ApprovalDecisionRecorded($processedApproval)); // Create this notification
  }


  /**
   * Helper method to create the next approval record in a multi-stage workflow.
   *
   * @param Model $approvable The approvable model (EmailApplication or LoanApplication).
   * @param User $nextOfficer The user who will be assigned the next approval task.
   * @param string $stage The identifier for the next approval stage (e.g., 'admin_review', 'bpm_confirmation').
   * @return Approval The newly created approval record.
   * @throws \Exception If the approval record fails to save.
   */
  protected function createNextApproval(Model $approvable, User $nextOfficer, string $stage): Approval
  {
    // Create the new pending approval record assigned to the next officer
    $nextApproval = new Approval([
      'officer_id' => $nextOfficer->id, // Assign to the next officer
      'status' => 'pending', // Set status to pending
      'stage' => $stage, // Identify the stage (e.g., 'admin_review')
      // Add other relevant data like due date if applicable
    ]);

    // Associate the approval polymorphically with the approvable model
    // Use the relationship to ensure it's linked correctly
    $approvable->approvals()->save($nextApproval);


    Log::info("Created next approval record (Stage: {$stage}) for " . get_class($approvable) . " ID: " . $approvable->id . " assigned to officer: " . $nextOfficer->id);

    // TODO: Implement notification to the next officer that they have a pending approval task
    // $nextOfficer->notify(new YouHaveAPendingApprovalNotification($nextApproval)); // Create this notification

    return $nextApproval;
  }


  // You can add other methods related to approvals here, e.g.,
  // public function getPendingApprovalsForUser(User $user): Collection; // This logic is handled by ApprovalDashboard Livewire component directly querying Approval model
  // public function getApprovalHistoryForApprovable(Model $approvable): Collection; // Method to retrieve all approval records for an application
}
