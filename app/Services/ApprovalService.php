<?php

namespace App\Services;

use App\Models\Approval;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification; // For sending notifications

class ApprovalService
{
  protected $emailProvisioningService; // Inject if approval triggers provisioning
  // protected $emailApplicationService; // Inject if approval triggers email app logic

  public function __construct(EmailProvisioningService $emailProvisioningService /*, EmailApplicationService $emailApplicationService */) // Inject dependencies
  {
    $this->emailProvisioningService = $emailProvisioningService;
    // $this->emailApplicationService = $emailApplicationService;
  }

  /**
   * Record an approval decision for an approvable model and update its status.
   *
   * @param Model $approvable The model being approved (e.g., EmailApplication, LoanApplication)
   * @param User $approver The user making the approval decision
   * @param string $status 'approved' or 'rejected'
   * @param string|null $comments Optional comments
   * @return Approval The created approval record
   * @throws \Exception
   */
  public function recordApproval(Model $approvable, User $approver, string $status, ?string $comments = null): Approval
  {
    // Ensure policy check is done before calling this method (e.g., can('approve', $approvable) or can('reject', $approvable))

    DB::beginTransaction(); // Use database transactions

    try {
      // Create the approval record
      $approval = new Approval([
        'officer_id' => $approver->id,
        'status' => $status, // Status of this specific approval step
        'comments' => $comments,
        'approval_timestamp' => now(),
      ]);

      // Associate the approval with the approvable model using polymorphic relationship
      $approvable->approvals()->save($approval);


      // Update the status of the approvable model based on the approval decision and workflow stage
      $this->updateApprovableStatus($approvable, $status, $approver);


      DB::commit(); // Commit the transaction


      // Trigger notifications or next steps based on the new status of the approvable model
      if ($approvable instanceof \App\Models\EmailApplication) {
        if ($approvable->status === 'pending_admin') {
          // Notify IT Admin
          // $itAdmins = User::whereHas('roles', fn($q) => $q->where('name', 'it_admin'))->get(); // Assuming roles
          // Notification::send($itAdmins, new \App\Notifications\EmailApplicationPendingAdminReview($approvable)); // Create this notification
        } elseif ($approvable->status === 'rejected') {
          // Notify Applicant
          $approvable->user->notify(new \App\Notifications\EmailApplicationRejectedNotification($approvable)); // Create this notification
        }
      } elseif ($approvable instanceof \App\Models\LoanApplication) {
        if ($approvable->status === 'approved') {
          // Notify BPM staff
          // Notification::route('mail', config('motac.equipment_loan.bpm_notification_recipient'))
          //    ->notify(new \App\Notifications\LoanApplicationApprovedForBPM($approvable)); // Create this notification
        } elseif ($approvable->status === 'rejected') {
          // Notify Applicant
          $approvable->user->notify(new \App\Notifications\LoanApplicationRejectedNotification($approvable)); // Create this notification
        }
      }


      Log::info("Approval recorded for " . get_class($approvable) . " ID: " . $approvable->id . ". Decision: " . $status . " by officer: " . $approver->id);

      return $approval;
    } catch (\Exception $e) {
      DB::rollBack(); // Rollback the transaction on error
      Log::error("Failed to record approval for " . get_class($approvable) . " ID: " . $approvable->id . ". Error: " . $e->getMessage());
      throw $e; // Re-throw the exception
    }
  }

  /**
   * Update the status of the approvable model based on the approval decision and current status.
   * This logic needs concrete implementation based on your specific workflow steps from the system design.
   *
   * @param Model $approvable
   * @param string $approvalStatus 'approved' or 'rejected'
   * @param User $approver
   */
  protected function updateApprovableStatus(Model $approvable, string $approvalStatus, User $approver): void
  {
    // --- Implement your specific workflow state transitions here ---
    // This is the core logic for multi-stage approvals or simple workflows

    if ($approvable instanceof \App\Models\EmailApplication) {
      // Example Workflow for Email Application: draft -> pending_support -> pending_admin -> approved/rejected -> processing -> completed
      if ($approvable->status === 'pending_support') {
        if ($approvalStatus === 'approved') {
          // Move to the next approval stage (IT Admin review)
          $approvable->status = 'pending_admin';
        } elseif ($approvalStatus === 'rejected') {
          // Rejected at the support stage
          $approvable->status = 'rejected';
          $approvable->rejection_reason = $approvable->rejection_reason ? $approvable->rejection_reason . "\n--\nRejected by " . $approver->full_name . ($approvable->approvals()->latest()->first()->comments ? ": " . $approvable->approvals()->latest()->first()->comments : '') : "Rejected by " . $approver->full_name . ($approvable->approvals()->latest()->first()->comments ? ": " . $approvable->approvals()->latest()->first()->comments : '');
        }
      }
      // If it's already pending_admin, this approval might be an IT admin "approval" which triggers provisioning?
      // Or is IT admin's action a separate 'process' step, not an 'approve' step in the Approval model?
      // Based on system design section 5.1, IT Processing is a separate step after support approval.
      // So, 'approve'/'reject' methods in policies and the Approval model might only apply to the 'pending_support' stage.
      // The IT admin would have a different action/method (e.g., 'process', 'provision')

    } elseif ($approvable instanceof \App\Models\LoanApplication) {
      // Example Workflow for Loan Application: draft -> pending_support -> approved/rejected -> partially_issued/issued -> returned/overdue
      if ($approvable->status === 'pending_support') {
        if ($approvalStatus === 'approved') {
          // Approved at the support stage, ready for BPM issuance
          $approvable->status = 'approved'; // Status changes from pending_support to 'approved'
        } elseif ($approvalStatus === 'rejected') {
          // Rejected at the support stage
          $approvable->status = 'rejected';
          $approvable->rejection_reason = $approvable->rejection_reason ? $approvable->rejection_reason . "\n--\nRejected by " . $approver->full_name . ($approvable->approvals()->latest()->first()->comments ? ": " . $approvable->approvals()->latest()->first()->comments : '') : "Rejected by " . $approver->full_name . ($approvable->approvals()->latest()->first()->comments ? ": " . $approvable->approvals()->latest()->first()->comments : '');
        }
      }
      // BPM staff actions (issue, processReturn) are separate from this ApprovalService

    }

    // Save the approvable model with the updated status
    if ($approvable->isDirty('status')) {
      $approvable->save();
      Log::info(get_class($approvable) . " ID: " . $approvable->id . " status updated to: " . $approvable->status);
    } else {
      Log::info(get_class($approvable) . " ID: " . $approvable->id . " status did not change.");
    }


    // --- End of workflow logic ---
  }

  // You can add other methods related to approvals here, e.g.,
  // public function getPendingApprovalsForUser(User $user): Collection; // Method Livewire component might use
  // public function getApprovalHistoryForUser(User $user): Collection;
}
