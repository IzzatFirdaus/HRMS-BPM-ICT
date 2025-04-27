<?php

namespace App\Models;

use App\Traits\CreatedUpdatedDeletedBy; // Assuming this trait exists and adds audit FKs/methods
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo; // Use BelongsTo trait
use Illuminate\Database\Eloquent\SoftDeletes; // Use SoftDeletes trait
use Illuminate\Support\Carbon; // Import Carbon if needed for time formatting (though casts handle timestamps)


// Import models for relationships
use App\Models\LoanApplication;
use App\Models\Equipment;
use App\Models\User; // For officer relationships
// Removed Approval import as it's not directly related to LoanTransaction model via FK


class LoanTransaction extends Model
{
  // Use the traits for factory, soft deletes, and audit columns
  use CreatedUpdatedDeletedBy, HasFactory, SoftDeletes;

  /**
   * The attributes that are mass assignable.
   * Includes all relevant fields from the migration.
   *
   * @var array<int, string>
   */
  protected $fillable = [
    'loan_application_id', // Foreign key to the loan application
    'equipment_id', // Foreign key to the equipment asset

    'issuing_officer_id', // Foreign key to the issuing user
    'receiving_officer_id', // Foreign key to the receiving user (at issue)

    'accessories_checklist_on_issue', // JSON checklist
    'issue_timestamp', // Timestamp of issue

    'returning_officer_id', // Foreign key to the returning user
    'return_accepting_officer_id', // Foreign key to the return accepting user

    'accessories_checklist_on_return', // JSON checklist
    'return_timestamp', // Timestamp of return
    'return_notes', // Notes on return

    'status', // Workflow status (e.g., issued, returned, overdue)

    // Audit columns are typically handled by the trait, but listed here for clarity if mass assignment is needed
    // 'created_by',
    // 'updated_by',
    // 'deleted_by',
  ];

  /**
   * The attributes that should be cast.
   * Includes casts for FKs, JSON, timestamps, status, and standard audit/soft delete timestamps.
   *
   * @var array<string, string>
   */
  protected $casts = [
    'loan_application_id' => 'integer', // Cast FKs to integer for clarity
    'equipment_id' => 'integer',
    'issuing_officer_id' => 'integer',
    'receiving_officer_id' => 'integer',
    'returning_officer_id' => 'integer',
    'return_accepting_officer_id' => 'integer',

    'accessories_checklist_on_issue' => 'json', // Cast JSON fields to arrays/objects
    'accessories_checklist_on_return' => 'json',

    'issue_timestamp' => 'datetime', // Cast timestamps to Carbon instances
    'return_timestamp' => 'datetime',

    // Status field might need casting if it is a database enum, otherwise string is fine.
    'status' => 'string', // Cast status as string/enum

    'created_at' => 'datetime', // Explicitly cast timestamps if trait doesn't handle or for clarity
    'updated_at' => 'datetime',
    'deleted_at' => 'datetime', // Cast soft delete timestamp
  ];


  // The CreatedUpdatedDeletedBy trait is assumed to add these audit relationships:
  // public function createdBy(): BelongsTo { ... }
  // public function updatedBy(): BelongsTo { ... }
  // public function deletedBy(): BelongsTo { ... }


  // 👉 Relationships

  /**
   * Get the loan application associated with the transaction.
   */
  public function loanApplication(): BelongsTo
  {
    // Assumes the 'loan_transactions' table has a 'loan_application_id' foreign key
    return $this->belongsTo(LoanApplication::class, 'loan_application_id'); // Explicitly define foreign key
  }

  /**
   * Get the specific equipment asset involved in the transaction.
   */
  public function equipment(): BelongsTo
  {
    // Assumes the 'loan_transactions' table has an 'equipment_id' foreign key
    return $this->belongsTo(Equipment::class, 'equipment_id'); // Explicitly define foreign key
  }

  /**
   * Get the issuing officer.
   */
  public function issuingOfficer(): BelongsTo
  {
    // Assumes the 'loan_transactions' table has an 'issuing_officer_id' foreign key
    return $this->belongsTo(User::class, 'issuing_officer_id'); // Explicitly define foreign key
  }

  /**
   * Get the receiving officer (at issue).
   */
  public function receivingOfficer(): BelongsTo
  {
    // Assumes the 'loan_transactions' table has a 'receiving_officer_id' foreign key
    return $this->belongsTo(User::class, 'receiving_officer_id'); // Explicitly define foreign key
  }

  /**
   * Get the returning officer.
   */
  public function returningOfficer(): BelongsTo
  {
    // Assumes the 'loan_transactions' table has a 'returning_officer_id' foreign key
    return $this->belongsTo(User::class, 'returning_officer_id'); // Explicitly define foreign key
  }

  /**
   * Get the return accepting officer.
   */
  public function returnAcceptingOfficer(): BelongsTo
  {
    // Assumes the 'loan_transactions' table has a 'return_accepting_officer_id' foreign key
    return $this->belongsTo(User::class, 'return_accepting_officer_id'); // Explicitly define foreign key
  }


  // 👉 Helper Methods (Transaction Status Checks)

  /**
   * Check if the transaction represents an item that has been issued.
   */
  public function isIssued(): bool
  {
    return $this->status === 'issued';
  }

  /**
   * Check if the transaction represents an item that has been returned.
   */
  public function isReturned(): bool
  {
    return $this->status === 'returned';
  }

  /**
   * Check if the transaction is marked as overdue.
   */
  public function isOverdue(): bool
  {
    return $this->status === 'overdue';
  }

  // Add custom methods or accessors/mutators here as needed
}
