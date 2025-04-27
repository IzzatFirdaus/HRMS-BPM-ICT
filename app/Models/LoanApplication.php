<?php

namespace App\Models;

use App\Traits\CreatedUpdatedDeletedBy; // Assuming this trait exists and adds audit FKs/methods
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo; // Use BelongsTo trait
use Illuminate\Database\Eloquent\Relations\HasMany; // Use HasMany trait
use Illuminate\Database\Eloquent\Relations\MorphMany; // Use MorphMany trait for polymorphic relationship
use Illuminate\Database\Eloquent\SoftDeletes; // Use SoftDeletes trait

// Import models for relationships
use App\Models\User; // LoanApplication belongs to users (applicant, responsible officer)
use App\Models\LoanApplicationItem; // LoanApplication has many Items
use App\Models\LoanTransaction; // LoanApplication has many Transactions
use App\Models\Approval; // LoanApplication has many Approvals (polymorphic)


class LoanApplication extends Model
{
  // Use the traits for factory, soft deletes, and audit columns
  use CreatedUpdatedDeletedBy, HasFactory, SoftDeletes;

  /**
   * The attributes that are mass assignable.
   * Includes all workflow and data fields from the migration.
   *
   * @var array<int, string>
   */
  protected $fillable = [
    'user_id', // The applicant who submitted the application (Foreign key)
    'responsible_officer_id', // Responsible Officer ID (Foreign key)
    'purpose', // Tujuan (Text)
    'location', // Lokasi (String)
    'loan_start_date', // Tarikh Pinjaman (Date)
    'loan_end_date', // Tarikh Pulangan (Date)
    'status', // Workflow status (String/Enum)
    'rejection_reason', // Reason for rejection (Text)
    'applicant_confirmation_timestamp', // Timestamp when applicant confirmed (Timestamp)

    // Audit columns are typically handled by the trait, but listed here for clarity if mass assignment is needed
    // 'created_by',
    // 'updated_by',
    // 'deleted_by',
  ];

  /**
   * The attributes that should be cast.
   * Includes casts for FKs, dates, timestamps, status, and standard audit/soft delete timestamps.
   *
   * @var array<string, string>
   */
  protected $casts = [
    'user_id' => 'integer', // Cast FKs to integer for clarity
    'responsible_officer_id' => 'integer',

    'loan_start_date' => 'date', // Cast date fields to Carbon instances
    'loan_end_date' => 'date',
    'applicant_confirmation_timestamp' => 'datetime', // Cast timestamp to Carbon instance

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
   * Get the user who submitted the loan application (the applicant).
   */
  public function user(): BelongsTo
  {
    // Assumes the 'loan_applications' table has a 'user_id' foreign key
    return $this->belongsTo(User::class, 'user_id'); // Explicitly define foreign key
  }

  /**
   * Get the responsible officer user for the loan application (if applicable).
   */
  public function responsibleOfficer(): BelongsTo
  {
    // Assumes the 'loan_applications' table has a 'responsible_officer_id' foreign key
    return $this->belongsTo(User::class, 'responsible_officer_id'); // Belongs to User model with responsible_officer_id foreign key
  }

  /**
   * Get the equipment items requested for the loan application.
   */
  public function items(): HasMany
  {
    // Assumes the 'loan_application_items' table has a 'loan_application_id' foreign key
    return $this->hasMany(LoanApplicationItem::class, 'loan_application_id'); // Explicitly define foreign key
  }

  /**
   * Get the transactions (issue/return records) for the loan application.
   */
  public function transactions(): HasMany
  {
    // Assumes the 'loan_transactions' table has a 'loan_application_id' foreign key
    return $this->hasMany(LoanTransaction::class, 'loan_application_id'); // Explicitly define foreign key
  }

  /**
   * Get the approvals for the loan application (polymorphic relationship).
   */
  public function approvals(): MorphMany
  {
    // MorphMany relationship to the Approval model where this model is the 'approvable'
    return $this->morphMany(Approval::class, 'approvable');
  }


  // 👉 Helper Methods (Workflow Status Checks)

  /**
   * Check if the application is in draft status.
   */
  public function isDraft(): bool
  {
    return $this->status === 'draft';
  }

  /**
   * Check if the application is in a pending status requiring support review.
   */
  public function isPendingSupport(): bool
  {
    return $this->status === 'pending_support';
  }

  /**
   * Check if the application is in a pending status requiring BPM review.
   */
  public function isPendingBpm(): bool
  {
    return $this->status === 'pending_bpm'; // Assuming 'pending_bpm' is a valid status
  }

  /**
   * Check if the application is currently pending any approval step.
   */
  public function isPendingApproval(): bool
  {
    // Include all pending statuses relevant to Loan Applications
    return in_array($this->status, ['pending_support', 'pending_bpm']); // Adjust statuses as needed
  }

  /**
   * Check if the application has been approved.
   */
  public function isApproved(): bool
  {
    return $this->status === 'approved';
  }

  /**
   * Check if the application has been rejected.
   */
  public function isRejected(): bool
  {
    return $this->status === 'rejected';
  }

  /**
   * Check if the equipment for the application has been issued.
   */
  public function isIssued(): bool
  {
    return $this->status === 'issued';
  }

  /**
   * Check if the equipment for the application has been returned.
   */
  public function isReturned(): bool
  {
    return $this->status === 'returned';
  }

  /**
   * Check if the application is overdue.
   */
  public function isOverdue(): bool
  {
    return $this->status === 'overdue';
  }


  // Add custom methods or accessors/mutators here as needed
}
