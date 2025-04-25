<?php

namespace App\Services;

use App\Models\LoanApplication; // Import LoanApplication model
use App\Models\LoanApplicationItem; // Import LoanApplicationItem model
use App\Models\LoanTransaction; // Import LoanTransaction model
use App\Models\Equipment; // Import Equipment model
use App\Models\User; // Import User model
use App\Models\Approval; // Import Approval model (needed for initiating workflow)
use App\Services\ApprovalService; // Import ApprovalService (if needed for complex creation, but here creating directly)
use Illuminate\Support\Facades\DB; // Import DB facade for transactions
use Illuminate\Support\Facades\Log; // For logging
use Illuminate\Support\Facades\Notification; // For sending notifications
use Exception; // Import Exception
use Illuminate\Database\Eloquent\ModelNotFoundException; // Import ModelNotFoundException if finding related models

// Import Notification classes (create these if they don't exist)
// use App\Notifications\NewLoanApplicationForApproval; // Notification for the first approver
// use App\Notifications\LoanApplicationDraftSaved; // Optional notification for applicant


class LoanApplicationService
{
  // Inject any services needed for subsequent workflow steps or related logic
  protected $approvalService; // Inject ApprovalService if needed for complex approval creation

  // Inject dependency (ApprovalService might be needed for other logic later)
  public function __construct(ApprovalService $approvalService)
  {
    $this->approvalService = $approvalService;
  }


  /**
   * Create a new loan application record.
   * Handles basic data mapping and sets initial status (e.g., 'draft' or 'pending_support').
   *
   * @param User $applicant The user submitting the application.
   * @param array $applicationData Main application data (validated).
   * @param array $itemsData Array of equipment item data (validated).
   * @return LoanApplication The newly created LoanApplication model instance.
   * @throws \Exception
   */
  public function createApplication(User $applicant, array $applicationData, array $itemsData): LoanApplication
  {
    // Ensure policy check (can('create', LoanApplication::class)) is done in the controller/component before calling this method.

    DB::beginTransaction(); // Start database transaction

    try {
      // Create the main application record and fill data
      $application = new LoanApplication();
      $application->fill($applicationData); // Fill application data (purpose, location, dates, responsible_officer_id, confirmation_timestamp, etc.)

      // Associate the application with the applicant user
      $application->user()->associate($applicant);

      // Status should be provided in $applicationData array by the component (e.g., 'draft')
      // If you want to default to draft here if not provided:
      $application->status = $applicationData['status'] ?? 'draft';

      // applicant_confirmation_timestamp should be provided in $applicationData by the component
      // if ($application->status === 'pending_support' && !isset($applicationData['applicant_confirmation_timestamp'])) {
      //     $application->applicant_confirmation_timestamp = now(); // Set timestamp if submitting immediately and not provided
      // }


      $application->save(); // Save main application first to get its ID


      // Attach requested equipment items
      foreach ($itemsData as $itemData) {
        // Ensure itemData has 'equipment_type', 'quantity_requested', 'notes'
        // Use create to automatically associate with the application
        $application->items()->create($itemData); // Assuming LoanApplication has 'items' hasMany relationship to LoanApplicationItem
      }


      DB::commit(); // Commit the transaction

      Log::info("New loan application created: " . $application->id . " by user: " . $applicant->id . " with status: " . $application->status);

      // Optional: Trigger notification if needed (e.g., Draft saved)
      // if ($application->status === 'draft') {
      //     $applicant->notify(new LoanApplicationDraftSaved($application)); // Create this notification
      // }


      return $application;
    } catch (Exception $e) {
      DB::rollBack(); // Rollback the transaction on error
      Log::error("Failed to create loan application for user: " . $applicant->id . ". Error: " . $e->getMessage());
      throw $e; // Re-throw the exception
    }
  }

  /**
   * Update an existing loan application record (e.g., a draft).
   * Updates main application data and synchronizes items.
   *
   * @param LoanApplication $application The application instance to update.
   * @param array $applicationData Updated main application data (validated).
   * @param array $itemsData Updated array of equipment item data (validated).
   * @return LoanApplication The updated application record.
   * @throws \Exception
   */
  public function updateApplication(LoanApplication $application, array $applicationData, array $itemsData): LoanApplication
  {
    // Ensure policy check (can('update', $application)) is done in the controller/component before calling this method.
    // Ensure the application status is 'draft' before allowing update if that's your workflow.

    DB::beginTransaction(); // Start database transaction

    try {
      // Update the main application data
      // Ensure fillable properties are set in the LoanApplication model.
      $application->fill($applicationData);

      // Save the updated application
      $application->save(); // Save main application first

      // Sync items - delete existing and create new ones.
      // This is a simple sync method. If items can be updated by ID, a more complex sync might be needed.
      $application->items()->delete(); // Delete all existing items
      foreach ($itemsData as $itemData) {
        // Ensure itemData has 'equipment_type', 'quantity_requested', 'notes'
        $application->items()->create($itemData); // Recreate items
      }


      DB::commit(); // Commit the transaction

      Log::info("Loan application updated: " . $application->id);

      // Optional: Trigger notifications if needed (e.g., Draft updated)
      // $application->user->notify(new LoanApplicationDraftSaved($application)); // Create this notification if different from creation notification


      return $application; // Return the updated application record
    } catch (Exception $e) {
      DB::rollBack(); // Rollback the transaction on error
      Log::error("Failed to update loan application: " . $application->id . ". Error: " . $e->getMessage());
      throw $e; // Re-throw the exception
    }
  }

  /**
   * Initiate the approval workflow for a draft loan application.
   * This method transitions the application status from 'draft' to 'pending_support'
   * and creates the first Approval record for the designated approver (Gred 41+).
   * This method resolves the "Undefined method 'initiateApprovalWorkflow'" error in the Livewire component.
   *
   * @param LoanApplication $application The draft application instance to submit.
   * @return LoanApplication The application after workflow initiation.
   * @throws \Exception
   */
  public function initiateApprovalWorkflow(LoanApplication $application): LoanApplication
  {
    // Ensure policy check (can('update', $application)) is done in the controller/component.
    // Ensure the application status is 'draft' before calling this method.

    DB::beginTransaction(); // Start database transaction

    try {
      // 1. Check if the application is in the correct status for submission (must be 'draft')
      if ($application->status !== 'draft') {
        throw new Exception("Loan Application ID " . $application->id . " cannot initiate workflow. Status is not 'draft'.");
      }

      // 2. Update application status to 'pending_support'
      $application->status = 'pending_support';
      // The applicant_confirmation_timestamp is set by the component before calling this service method
      // $application->applicant_confirmation_timestamp = now(); // Ensure this is set if not already done

      $application->save(); // Save status change

      // 3. Find the first approver (Gred 41+ Officer)
      // Based on PDF, endorsement by Bahagian/Unit/Seksyen officer Gred 41+ is required.
      // Logic to find the correct approver(s) based on applicant's department, location, or other criteria.
      // Simplified example: Find the first user with Grade level 41 or higher.
      // Use explicit integer cast as a potential workaround for static analysis tool confusion.
      $minApproverGradeLevel = (int) config('motac.approval.min_loan_approver_grade_level', 41); // Get min grade from config - FIX: Added explicit cast


      $firstApproverUser = User::whereHas('grade', fn($query) => $query->where('level', '>=', $minApproverGradeLevel))
        // Add more complex logic here to filter by department/location if needed
        // ->whereHas('department', fn($query) => $query->where('id', $application->user->department_id)) // Filter by applicant's department ID
        // Add filtering by position (e.g., Head of Department role/title) if applicable
        // ->whereHas('position', fn($query) => $query->where('name', 'like', '%Head%'))
        ->first(); // Get the first matching user

      // Check if an approver was found
      if (!$firstApproverUser) {
        // Log a critical error as the workflow cannot proceed
        Log::critical("No approver (Gred " . $minApproverGradeLevel . "+) found to initiate workflow for Loan Application ID: " . $application->id);
        // You might want to revert status, notify admin, or throw a more specific exception
        throw new Exception("No eligible approver found for this application.");
      }


      // 4. Create the first Approval record assigned to the designated approver
      // Use the polymorphic relationship (Assuming LoanApplication has 'approvals' relationship to Approval)
      $approval = $application->approvals()->create([
        'officer_id' => $firstApproverUser->id, // Assign to the Gred 41+ officer
        'status' => 'pending', // Set status of this approval step to pending
        'stage' => 'support_review', // Identify the stage (e.g., 'division_endorsement')
        // Add other relevant data like due date if applicable
      ]);

      DB::commit(); // Commit the transaction

      Log::info("Workflow initiated for Loan Application ID: " . $application->id . ". Status set to 'pending_support'. First approval created (Approval ID: " . $approval->id . ") for officer ID: " . $firstApproverUser->id);

      // 5. Trigger notification to the first approver
      // Ensure NewLoanApplicationForApproval Notification class exists
      // $firstApproverUser->notify(new NewLoanApplicationForApproval($application, $approval)); // Create this notification


      return $application; // Return the application after initiating workflow
    } catch (Exception $e) {
      DB::rollBack(); // Rollback the transaction on error
      Log::error("Failed to initiate workflow for Loan Application ID: " . $application->id . ". Error: " . $e->getMessage());
      // Re-throw the exception for the Livewire component to handle
      throw $e;
    }
  }


  /**
   * BPM Staff issues equipment for an approved loan application.
   *
   * @param LoanApplication $application The approved loan application.
   * @param Equipment $equipment The specific equipment asset being issued.
   * @param array $transactionData Transaction details (e.g., accessories, notes).
   * @param User $issuingOfficer The BPM staff issuing.
   * @return LoanTransaction The newly created LoanTransaction record.
   * @throws \Exception
   */
  public function issueEquipment(LoanApplication $application, Equipment $equipment, array $transactionData, User $issuingOfficer): LoanTransaction
  {
    // Ensure policy check is done before calling this method (e.g., can('issue', $application))

    DB::beginTransaction();
    try {
      // Create the loan transaction record
      // Assuming LoanApplication has a 'transactions' hasMany relationship to LoanTransaction
      $transaction = $application->transactions()->create([
        'equipment_id' => $equipment->id,
        'issuing_officer_id' => $issuingOfficer->id,
        'receiving_officer_id' => $transactionData['receiving_officer_id'] ?? $application->user_id, // Default to applicant if receiving officer not specified
        'accessories_checklist_on_issue' => $transactionData['accessories'] ?? null, // Ensure these fields exist in LoanTransaction model
        'issue_timestamp' => now(),
        'status' => 'issued', // Status of this specific transaction
        'notes' => $transactionData['notes'] ?? null, // Or dedicated notes field
      ]);

      // Update the specific equipment asset status
      $equipment->status = 'on_loan';
      $equipment->save();

      // Update the loan application status based on items issued vs requested
      // You would need logic to check if all requested items have been issued
      // For simplification, set status to 'partially_issued' or 'issued' based on counting transactions vs items
      $totalRequested = $application->items->sum('quantity_requested');
      $totalIssuedTransactions = $application->transactions()->whereIn('status', ['issued', 'returned', 'damaged', 'lost'])->count(); // Count transactions related to items

      $application->status = ($totalIssuedTransactions >= $totalRequested) ? 'issued' : 'partially_issued';
      $application->save();


      DB::commit();

      Log::info("Equipment issued for loan application " . $application->id . ": Transaction " . $transaction->id . ". Application status set to: " . $application->status);

      // Notify the applicant that equipment has been issued
      // $application->user->notify(new \App\Notifications\EquipmentIssuedNotification($transaction)); // Create this notification


      return $transaction; // Return the newly created transaction
    } catch (Exception $e) {
      DB::rollBack();
      Log::error("Failed to issue equipment for loan application " . $application->id . ". Error: " . $e->getMessage());
      throw $e;
    }
  }

  /**
   * BPM Staff processes the return of equipment.
   *
   * @param LoanTransaction $transaction The transaction record for the issued equipment being returned.
   * @param array $returnDetails Return details (e.g., accessories checklist on return, notes, equipment status on return).
   * @param User $acceptingOfficer The BPM staff accepting the return.
   * @return LoanTransaction The updated LoanTransaction record.
   * @throws \Exception
   */
  public function processReturn(LoanTransaction $transaction, array $returnDetails, User $acceptingOfficer): LoanTransaction
  {
    // Ensure policy check is done before calling this method (e.g., can('processReturn', $transaction->loanApplication))
    // Ensure the transaction status is 'issued' before allowing return processing

    DB::beginTransaction();
    try {
      // Check if the transaction is in 'issued' status
      if ($transaction->status !== 'issued') {
        throw new Exception("Transaction ID " . $transaction->id . " cannot process return. Status is not 'issued'.");
      }

      // Update the loan transaction record with return details
      $transaction->fill([
        'returning_officer_id' => $returnDetails['returning_officer_id'] ?? $transaction->loanApplication->user_id, // Default to applicant if returning officer not specified
        'return_accepting_officer_id' => $acceptingOfficer->id, // Current user is accepting officer
        'return_timestamp' => now(),
        'accessories_checklist_on_return' => $returnDetails['accessories'] ?? null, // Ensure this field exists in LoanTransaction model
        'return_notes' => $returnDetails['notes'] ?? null, // Ensure this field exists in LoanTransaction model
        'status' => $returnDetails['status'] ?? 'returned', // Allow specifying transaction status like 'returned', 'damaged', 'lost'
      ]);
      $transaction->save();

      // Update the specific equipment asset status based on return condition
      $equipment = $transaction->equipment;
      if ($equipment) {
        // Use the status provided in returnDetails (e.g., 'available', 'under_maintenance', 'disposed')
        $equipment->status = $returnDetails['equipment_status_on_return'] ?? 'available';
        $equipment->save();
      } else {
        Log::warning("Equipment not found for transaction ID: " . $transaction->id . " during return processing.");
      }


      // Update the loan application status (e.g., 'returned', 'partially_returned', 'overdue')
      // Check if all items related to the application are returned/accounted for
      $loanApplication = $transaction->loanApplication;
      // Example logic: Count issued items vs. returned/damaged/lost transactions
      $totalIssuedItemsCount = $loanApplication->items->sum('quantity_requested'); // Total quantity requested and approved
      $totalReturnedOrAccountedForCount = $loanApplication->transactions()
        ->whereIn('status', ['returned', 'damaged', 'lost']) // Transactions representing items accounted for
        ->count();

      // Check if all *issued* transactions are marked as returned/damaged/lost
      $allIssuedTransactionsAccountedFor = $loanApplication->transactions()
        ->where('status', 'issued') // Find any remaining 'issued' transactions
        ->count() === 0;


      if ($allIssuedTransactionsAccountedFor) {
        // If all issued transactions are accounted for, mark application as returned
        $loanApplication->status = 'returned'; // Set application status to 'returned'
        $loanApplication->save();
        Log::info("Loan Application ID: " . $loanApplication->id . " status set to 'returned' as all issued items are accounted for.");
        // Notify applicant that the loan is completed?
        // $loanApplication->user->notify(new \App\Notifications\LoanCompletedNotification($loanApplication)); // Create this notification

      } else {
        // If not all items are returned, status might remain 'issued' or change to 'partially_returned' (if you have that status)
        // Or check for overdue status based on original end date
        Log::info("Loan Application ID: " . $loanApplication->id . " remains 'issued' or 'partially_returned' as not all items are accounted for.");
        // Implement logic for 'partially_returned' or overdue here if applicable
        // if ($loanApplication->status === 'issued') { // Only change from 'issued' to 'partially_returned'
        //      $loanApplication->status = 'partially_returned';
        //      $loanApplication->save();
        // }
      }


      DB::commit();

      Log::info("Equipment return processed for transaction " . $transaction->id . ". Equipment status set to: " . $equipment->status . ". Loan Application status set to: " . $loanApplication->status);

      // Notify the applicant about the return of the specific item? Or only when the whole loan is returned?
      // $transaction->loanApplication->user->notify(new \App\Notifications\EquipmentReturnedNotification($transaction)); // Create this notification


      return $transaction; // Return the updated transaction record
    } catch (Exception $e) {
      DB::rollBack();
      Log::error("Failed to process equipment return for transaction " . $transaction->id . ". Error: " . $e->getMessage());
      throw $e;
    }
  }

  // Add methods for checking equipment availability, generating reports, etc.

  /**
   * Find the appropriate first approver (Gred 41+) for a loan application.
   * This logic needs refinement based on your organizational structure.
   *
   * @param LoanApplication $application The loan application.
   * @return User|null The first approver user, or null if none found.
   */
  protected function findFirstApprover(LoanApplication $application): ?User
  {
    // Based on PDF, endorsement by Bahagian/Unit/Seksyen officer Gred 41+ is required.
    // This officer might be the Head of Department or a designated approver for the applicant's unit.
    // Simplified logic: Find the first user who is Gred 41 or higher.
    // Use explicit integer cast as a potential workaround for static analysis tool confusion.
    $minApproverGradeLevel = (int) config('motac.approval.min_loan_approver_grade_level', 41); // Get min grade from config


    $firstApproverUser = User::whereHas('grade', fn($query) => $query->where('level', '>=', $minApproverGradeLevel))
      // Add more complex logic here to filter by department/location if needed
      // ->whereHas('department', fn($query) => $query->where('id', $application->user->department_id)) // Filter by applicant's department ID
      // Add filtering by position (e.g., Head of Department role/title) if applicable
      // ->whereHas('position', fn($query) => $query->where('name', 'like', '%Head%'))
      ->first(); // Get the first matching user


    return $firstApproverUser; // Return the found approver user or null
  }
}
