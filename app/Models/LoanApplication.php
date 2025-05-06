<?php

namespace App\Models;

use App\Traits\CreatedUpdatedDeletedBy;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class LoanApplication extends Model
{
  use CreatedUpdatedDeletedBy, HasFactory, SoftDeletes;

  // Workflow status constants
  public const STATUS_DRAFT = 'draft';
  public const STATUS_PENDING_SUPPORT = 'pending_support';
  public const STATUS_PENDING_BPM = 'pending_bpm'; // Assuming BPM is another approval stage
  public const STATUS_APPROVED = 'approved';
  public const STATUS_REJECTED = 'rejected';
  public const STATUS_ISSUED = 'issued'; // Equipment has been issued
  public const STATUS_RETURNED = 'returned'; // All issued equipment has been returned
  public const STATUS_OVERDUE = 'overdue'; // Loan is overdue
  public const STATUS_PARTIALLY_ISSUED = 'partially_issued'; // Some, but not all, equipment has been issued
  public const STATUS_PARTIALLY_RETURNED = 'partially_returned'; // Some, but not all, issued equipment has been returned
  // *** FIX 1: Added the missing constant for processing status ***
  public const STATUS_PROCESSING = 'processing'; // Status while IT Admin is processing the request


  protected $fillable = [
    'user_id',
    'responsible_officer_id', // Assuming this refers to the head of department or similar
    'purpose',
    'location', // Location where equipment will be used
    'loan_start_date',
    'loan_end_date',
    'status',
    'rejection_reason',
    'applicant_confirmation_timestamp', // Timestamp when applicant confirms details (maybe on submission?)
    'submission_timestamp', // Timestamp when application is submitted for approval
    'final_assigned_it_admin_id', // IT Admin who finalized the processing (optional field)
    'it_completion_timestamp', // Timestamp when IT Admin marks as complete
    'admin_notes', // Notes added by IT Admin during processing
  ];

  protected $casts = [
    'user_id' => 'integer',
    'responsible_officer_id' => 'integer',
    'loan_start_date' => 'date', // Cast date fields
    'loan_end_date' => 'date', // Cast date fields
    'applicant_confirmation_timestamp' => 'datetime', // Cast timestamp fields
    'submission_timestamp' => 'datetime', // Cast timestamp fields
    'it_completion_timestamp' => 'datetime', // Cast timestamp fields
  ];

  // Default values for attributes if needed
  protected $attributes = [
    'status' => self::STATUS_DRAFT, // Default status is draft
  ];


  // Relationships

  /**
   * Get the user (applicant) who created the loan application.
   */
  public function user(): BelongsTo
  {
    return $this->belongsTo(User::class, 'user_id');
  }

  /**
   * Get the responsible officer for the application (e.g., Head of Department).
   */
  public function responsibleOfficer(): BelongsTo
  {
    return $this->belongsTo(User::class, 'responsible_officer_id');
  }

  /**
   * Get the IT Admin who finalized the processing.
   */
  public function finalAssignedItAdmin(): BelongsTo
  {
    return $this->belongsTo(User::class, 'final_assigned_it_admin_id');
  }


  /**
   * Get the equipment items requested in this application.
   */
  public function items(): HasMany
  {
    // Assumes LoanApplicationItem model exists and has 'loan_application_id' foreign key
    return $this->hasMany(LoanApplicationItem::class, 'loan_application_id');
  }

  /**
   * Get the loan transactions associated with this application.
   * This links the application to the actual issuance/return records.
   */
  public function transactions(): HasMany
  {
    // Assumes LoanTransaction model exists and has 'loan_application_id' foreign key
    return $this->hasMany(LoanTransaction::class, 'loan_application_id');
  }


  /**
   * Get the approval records for this application.
   * Uses a polymorphic relationship if using a generic 'approvals' table.
   */
  public function approvals(): MorphMany
  {
    // Assumes an Approval model and an 'approvals' table with 'approvable_id' and 'approvable_type'
    return $this->morphMany(Approval::class, 'approvable');
  }

  /**
   * Get the latest approval record for a specific stage.
   */
  public function latestApproval(?string $stage = null): ?Approval
  {
    $query = $this->approvals()->latest('approval_timestamp');

    if ($stage) {
      $query->where('stage', $stage);
    }

    return $query->first();
  }

  /**
   * Get the support officer assigned via the first approval stage.
   */
  public function supportingOfficer(): ?User
  {
    // Assuming the first approval record (if exists) is for support review
    // And assuming the 'officer' relationship exists on Approval model
    return $this->approvals()->oldest('approval_timestamp')->first()?->officer;
  }


  // Helper methods for status checks
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

  // Check if application is in any pending approval stage
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
    // A loan application is considered 'issued' if all approved items have been issued.
    // This might also encompass the STATUS_PARTIALLY_ISSUED or STATUS_PARTIALLY_RETURNED.
    // Check if its status is specifically STATUS_ISSUED OR if all items are issued.
    return $this->hasStatus(self::STATUS_ISSUED);
    // Alternative logic: return $this->items->every(fn($item) => $item->quantity_issued >= $item->quantity_approved) && $this->items->isNotEmpty();
  }

  public function isPartiallyReturned(): bool
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

  /**
   * Check if the application is currently being processed by IT Admin.
   * Assumes a STATUS_PROCESSING constant exists.
   */
  public function isProcessing(): bool // Add return type hint
  {
    // *** FIX 1: This method now correctly references the defined constant ***
    return $this->hasStatus(self::STATUS_PROCESSING);
  }


  public function isClosed(): bool
  {
    return in_array($this->status, [self::STATUS_RETURNED, self::STATUS_REJECTED, self::STATUS_OVERDUE]); // Also include overdue if applicable
  }

  public function isSubmitted(): bool
  {
    return !$this->isDraft();
  }


  // Accessors
  /**
   * Accessor to get the translated status string.
   *
   * @return string
   */
  public function getStatusTranslatedAttribute(): string
  {
    // TODO: Implement comprehensive translation logic based on all your defined constants
    return match ($this->status) {
      self::STATUS_DRAFT => __('Draf'),
      self::STATUS_PENDING_SUPPORT => __('Menunggu Sokongan'),
      self::STATUS_PENDING_BPM => __('Menunggu Tindakan BPM'),
      self::STATUS_APPROVED => __('Diluluskan'),
      self::STATUS_REJECTED => __('Ditolak'),
      self::STATUS_ISSUED => __('Dikeluarkan'),
      self::STATUS_RETURNED => __('Dipulangkan'),
      self::STATUS_OVERDUE => __('Lewat Dipulangkan'),
      self::STATUS_PARTIALLY_ISSUED => __('Dikeluarkan Sebahagian'),
      self::STATUS_PARTIALLY_RETURNED => __('Dipulangkan Sebahagian'),
      self::STATUS_PROCESSING => __('Sedang Diproses'), // *** FIX 1: Add translation for the new status ***
      // Add other statuses like STATUS_CANCELLED, STATUS_UNDER_MAINTENANCE etc. if defined
      default => $this->status, // Fallback to raw status
    };
  }

  /**
   * Accessor to get the service status translated string.
   * This accessor seems specific to a user model, but was found here.
   * Assuming it's intended for the User relationship.
   * If loan applications have a 'service_status' column, this accessor should be adapted.
   * Assuming this might be a leftover or intended for the related applicant user.
   * It's generally better to have this accessor on the User model.
   */
  // public function getServiceStatusTranslatedAttribute(): ?string
  // {
  //     // This logic belongs on the User model, not LoanApplication
  //     // If LoanApplication has a service_status column, implement translation here.
  //     // Otherwise, access it via the user relationship: $this->user->service_status_translated
  //     return null; // Placeholder
  // }


  // Scopes (if any)
  // Example: public function scopePending($query) { ... }

  // Mutators (if any)
  // Example: public function setPurposeAttribute($value) { ... }


}
