<?php

namespace App\Services;

use App\Models\User; // Import User model
use App\Models\EmailApplication; // Import EmailApplication model
use App\Models\Approval; // Import Approval model (needed for initiating workflow)
use Illuminate\Support\Str; // Import Str facade
use Illuminate\Support\Facades\DB; // Import DB facade for transactions
use Illuminate\Support\Facades\Log; // Import Log facade
use Exception; // Import Exception
use Illuminate\Support\Facades\Notification; // For sending notifications

// Import Notification classes (create these if they don't exist)
// use App\Notifications\NewEmailApplicationForApproval; // Notification for the first approver
// use App\Notifications\EmailApplicationDraftSaved; // Optional notification for applicant


class EmailApplicationService
{
  // Inject any services needed for subsequent workflow steps or related logic
  protected $emailProvisioningService; // Inject if needed for suggestions
  // protected $approvalService; // Inject if Approval creation logic is complex and handled by another service

  // Inject dependencies
  public function __construct(EmailProvisioningService $emailProvisioningService) // Inject ApprovalService if needed for complex creation
  {
    $this->emailProvisioningService = $emailProvisioningService;
    // $this->approvalService = $approvalService;
  }

  /**
   * Create a new email application record.
   * Handles basic data mapping and sets initial status to 'draft'.
   *
   * @param User $applicant The user submitting the application.
   * @param array $data Application data from the form (validated).
   * @return EmailApplication The newly created EmailApplication model instance.
   * @throws \Exception
   */
  public function createApplication(User $applicant, array $data): EmailApplication
  {
    // Ensure policy check (can('create', EmailApplication::class)) is done in the controller/component before calling this method.

    DB::beginTransaction(); // Start database transaction

    try {
      // Create a new EmailApplication instance and fill data
      $application = new EmailApplication();
      $application->fill($data); // Fill application data (purpose, proposed_email, group_email, etc.)

      // Associate the application with the applicant user
      $application->user()->associate($applicant);

      // Set initial status to 'draft'
      $application->status = 'draft';

      // Certification status and timestamp are set on submission, not draft creation
      $application->certification_accepted = $data['certification_accepted'] ?? false; // Save certification state if provided even for draft
      // $application->certification_timestamp = null; // Timestamp is set on submission

      // Save the application to the database
      $application->save();

      DB::commit(); // Commit the transaction

      Log::info("Email application draft created for user ID: " . $applicant->id . ". Application ID: " . $application->id);

      // Optional: Notify the applicant that their draft was saved
      // $applicant->notify(new EmailApplicationDraftSaved($application)); // Create this notification

      return $application;
    } catch (Exception $e) {
      DB::rollBack(); // Rollback the transaction on error
      Log::error("Failed to create email application draft for user ID: " . $applicant->id . ". Error: " . $e->getMessage());
      throw $e; // Re-throw the exception
    }
  }

  /**
   * Update an existing email application record (e.g., a draft).
   *
   * @param EmailApplication $application The application instance to update.
   * @param array $data The validated data for the update.
   * @return EmailApplication The updated application record.
   * @throws \Exception
   */
  public function updateApplication(EmailApplication $application, array $data): EmailApplication
  {
    // Ensure policy check (can('update', $application)) is done in the controller/component before calling this method.
    // Ensure the application status is 'draft' before allowing update if that's your workflow.

    DB::beginTransaction(); // Start database transaction

    try {
      // Fill the application attributes with validated data.
      // Ensure fillable properties are set in the EmailApplication model.
      $application->fill($data);

      // Save the updated application
      $application->save();

      DB::commit(); // Commit the transaction

      Log::info("Email application ID: " . $application->id . " updated.");

      return $application; // Return the updated application record
    } catch (Exception $e) {
      DB::rollBack(); // Rollback the transaction on error
      Log::error("Failed to update email application ID: " . $application->id . ". Error: " . $e->getMessage());
      throw $e; // Re-throw the exception
    }
  }

  /**
   * Initiate the approval workflow for a draft email application.
   * This method transitions the application status to 'pending_support'
   * and creates the first Approval record for the supporting officer.
   * It replaces the logic previously in submitApplication in the Livewire component.
   *
   * @param EmailApplication $application The draft application instance to submit.
   * @return EmailApplication The application after workflow initiation.
   * @throws \Exception
   */
  public function initiateApprovalWorkflow(EmailApplication $application): EmailApplication
  {
    // Ensure policy check (can('update', $application)) is done in the controller/component.
    // Ensure the application status is 'draft' before calling this method.

    DB::beginTransaction(); // Start database transaction

    try {
      // 1. Check if the application is in the correct status for submission (must be 'draft')
      if ($application->status !== 'draft') {
        throw new Exception("Application ID " . $application->id . " cannot initiate workflow. Status is not 'draft'.");
      }

      // 2. Update application status to 'pending_support'
      $application->status = 'pending_support';
      $application->certification_timestamp = now(); // Set submission timestamp
      $application->save();

      // 3. Find the first approver (the supporting officer)
      // Ensure supporting_officer_id is set on the application
      if (!$application->supporting_officer_id) {
        throw new Exception("Application ID " . $application->id . " cannot initiate workflow. Supporting officer is not assigned.");
      }
      $firstApproverUser = User::find($application->supporting_officer_id);
      if (!$firstApproverUser) {
        throw new Exception("Application ID " . $application->id . " cannot initiate workflow. Supporting officer user (ID: " . $application->supporting_officer_id . ") not found.");
      }


      // 4. Create the first Approval record assigned to the supporting officer
      // Use the polymorphic relationship
      $approval = $application->approvals()->create([
        'officer_id' => $firstApproverUser->id, // Assign to the supporting officer
        'status' => 'pending', // Set status of this approval step to pending
        'stage' => 'support_review', // Identify the stage
        // Add other relevant data like due date if applicable
      ]);

      DB::commit(); // Commit the transaction

      Log::info("Workflow initiated for Email Application ID: " . $application->id . ". Status set to 'pending_support'. First approval created for officer ID: " . $firstApproverUser->id);

      // 5. Trigger notification to the first approver (supporting officer)
      // Ensure NewEmailApplicationForApproval Notification class exists
      // $firstApproverUser->notify(new NewEmailApplicationForApproval($application, $approval)); // Create this notification


      return $application; // Return the application after initiating workflow
    } catch (Exception $e) {
      DB::rollBack(); // Rollback the transaction on error
      Log::error("Failed to initiate workflow for Email Application ID: " . $application->id . ". Error: " . $e->getMessage());
      // Revert status back to draft if possible? Or handle error state?
      // Depends on how you want to manage errors mid-transaction. Re-throwing is standard.
      throw $e; // Re-throw
    }
  }


  /**
   * Suggest a potential email address based on the user's name.
   * Delegates to the EmailProvisioningService.
   *
   * @param User $user The user for whom to suggest an email.
   * @return string A suggested email address.
   */
  public function suggestEmailAddress(User $user): string
  {
    // Call the method from the Provisioning Service for consistency
    return $this->emailProvisioningService->generateEmail($user);
  }

  // Add other business logic methods here, e.g.,
  // public function processApprovedApplication(EmailApplication $application): EmailApplication; // Method for IT Admin processing
  // public function assignFinalCredentials(EmailApplication $application, string $email = null, string $userId = null): EmailApplication;
}
