<?php

namespace App\Models;

use App\Traits\CreatedUpdatedDeletedBy;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany; // Import MorphMany
use Illuminate\Database\Eloquent\SoftDeletes;

class LoanApplication extends Model
{
  use CreatedUpdatedDeletedBy, HasFactory, SoftDeletes;

  // Workflow status constants
  public const STATUS_DRAFT = 'draft';
  public const STATUS_PENDING_SUPPORT = 'pending_support';
  public const STATUS_PENDING_BPM = 'pending_bpm';
  public const STATUS_APPROVED = 'approved'; // Ready for issuance
  public const STATUS_REJECTED = 'rejected';
  public const STATUS_ISSUED = 'issued'; // All approved items issued
  public const STATUS_RETURNED = 'returned'; // All issued items returned
  public const STATUS_OVERDUE = 'overdue';
  public const STATUS_PARTIALLY_ISSUED = 'partially_issued'; // Some approved items issued
  public const STATUS_PARTIALLY_RETURNED = 'partially_returned'; // Some issued items returned

  protected $fillable = [
    'user_id', // Applicant User ID
    'responsible_officer_id', // Responsible Officer User ID (nullable)
    'purpose',
    'location',
    'loan_start_date',
    'loan_end_date',
    'status',
    'rejection_reason',
    'applicant_confirmation_timestamp',
    'submission_timestamp', // Added submission timestamp to fillable/casts
    // created_by, updated_by, deleted_by handled by trait
  ];

  protected $casts = [
    'loan_start_date' => 'date',
    'loan_end_date' => 'date',
    'applicant_confirmation_timestamp' => 'datetime',
    'submission_timestamp' => 'datetime', // Cast submission timestamp
    'created_at' => 'datetime',
    'updated_at' => 'datetime',
    'deleted_at' => 'datetime',
    'user_id' => 'integer',
    'responsible_officer_id' => 'integer',
    'rejection_reason' => 'string', // Explicitly cast nullable fields if needed
    'purpose' => 'string',
    'location' => 'string',
  ];

  // --- Relationships ---

  /**
   * Get the user who created the loan application (the applicant).
   * Defines a many-to-one relationship.
   * Assumes 'loan_applications' table has 'user_id' foreign key.
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\User, \App\Models\LoanApplication>
   */
  public function user(): BelongsTo // Added return type hint
  {
    return $this->belongsTo(User::class, 'user_id');
  }

  /**
   * Get the responsible officer for the loan application.
   * Defines a many-to-one relationship.
   * Assumes 'loan_applications' table has 'responsible_officer_id' foreign key.
   * This can be the same user as the applicant.
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\User, \App\Models\LoanApplication>
   */
  public function responsibleOfficer(): BelongsTo // Added return type hint
  {
    // Nullable relationship
    return $this->belongsTo(User::class, 'responsible_officer_id');
  }


  /**
   * Get the items requested in the loan application.
   * Defines a one-to-many relationship.
   * Assumes 'loan_application_items' table has 'loan_application_id' foreign key.
   *
   * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\LoanApplicationItem>
   */
  public function items(): HasMany // Added return type hint
  {
    return $this->hasMany(LoanApplicationItem::class, 'loan_application_id');
  }

  /**
   * Get the transactions related to this loan application (issuance, return).
   * Defines a one-to-many relationship.
   * Assumes 'loan_transactions' table has 'loan_application_id' foreign key.
   *
   * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\LoanTransaction>
   */
  public function transactions(): HasMany // Added return type hint
  {
    return $this->hasMany(LoanTransaction::class, 'loan_application_id');
  }

  /**
   * Get the approval records for this loan application.
   * Defines a polymorphic one-to-many relationship.
   * Assumes 'approvals' table has 'approvable_type' and 'approvable_id' columns.
   *
   * @return \Illuminate\Database\Eloquent\Relations\MorphMany<\App\Models\Approval>
   */
  public function approvals(): MorphMany // Added return type hint
  {
    return $this->morphMany(Approval::class, 'approvable');
  }


  // --- Status Check Helper Methods ---

  public function hasStatus(string $status): bool
  {
    return $this->status === $status;
  }

  public function isDraft(): bool
  {
    return $this->hasStatus(self::STATUS_DRAFT);
  }

  public function isPendingSupport(): bool
  {
    return $this->hasStatus(self::STATUS_PENDING_SUPPORT);
  }

  public function isPendingBpm(): bool
  {
    return $this->hasStatus(self::STATUS_PENDING_BPM);
  }

  public function isPendingApproval(): bool
  {
    return in_array($this->status, [self::STATUS_PENDING_SUPPORT, self::STATUS_PENDING_BPM]);
  }

  public function isApproved(): bool
  {
    return $this->hasStatus(self::STATUS_APPROVED);
  }

  public function isRejected(): bool
  {
    return $this->hasStatus(self::STATUS_REJECTED);
  }

  public function isPartiallyIssued(): bool
  {
    return $this->hasStatus(self::STATUS_PARTIALLY_ISSUED);
  }

  public function isIssued(): bool
  {
    return $this->hasStatus(self::STATUS_ISSUED);
  }

  public function isPartiallyReturned(): bool // Added this missing status check helper
  {
    return $this->hasStatus(self::STATUS_PARTIALLY_RETURNED);
  }


  public function isReturned(): bool
  {
    return $this->hasStatus(self::STATUS_RETURNED);
  }

  public function isOverdue(): bool
  {
    return $this->hasStatus(self::STATUS_OVERDUE);
  }

  public function isClosed(): bool
  {
    // An application is closed if it's fully returned or rejected
    return in_array($this->status, [self::STATUS_RETURNED, self::STATUS_REJECTED]);
  }

  public function isSubmitted(): bool
  {
    // An application is submitted if its status is not 'draft'
    return !$this->isDraft();
  }

  // --- Accessors ---

  /**
   * Get the translated status string for display.
   *
   * @return string
   */
  public function getStatusTranslatedAttribute(): string
  {
    return match ($this->status) {
      self::STATUS_DRAFT => __('Draft'),
      self::STATUS_PENDING_SUPPORT => __('Pending Support Review'),
      self::STATUS_PENDING_BPM => __('Pending BPM Action'),
      self::STATUS_APPROVED => __('Approved'),
      self::STATUS_REJECTED => __('Rejected'),
      self::STATUS_ISSUED => __('Issued'),
      self::STATUS_RETURNED => __('Returned'),
      self::STATUS_OVERDUE => __('Overdue'),
      self::STATUS_PARTIALLY_ISSUED => __('Partially Issued'),
      self::STATUS_PARTIALLY_RETURNED => __('Partially Returned'), // Added translation for new status
      default => $this->status, // Return raw status if unknown
    };
  }

  // Add other accessors, mutators, or scopes as needed...
}
