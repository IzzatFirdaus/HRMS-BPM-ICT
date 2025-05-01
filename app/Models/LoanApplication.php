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

<<<<<<< HEAD
=======

// Import models for relationships if they are in a different namespace
use App\Models\User;                // LoanApplication belongs to users (applicant, responsible officer)
use App\Models\LoanApplicationItem; // LoanApplication has many Items
use App\Models\LoanTransaction;     // LoanApplication has many Transactions
use App\Models\Approval;            // LoanApplication has many Approvals (polymorphic)


/**
 * App\Models\LoanApplication
 * 
 * Represents a loan application for ICT equipment.
 * Tracks the applicant, responsible officer, requested items, transactions, and workflow status.
 *
 * @property int $id
 * @property int $user_id The applicant who submitted the application (Foreign key).
 * @property int|null $responsible_officer_id Responsible Officer ID (Foreign key).
 * @property string|null $purpose Tujuan (Text).
 * @property string|null $location Lokasi (String).
 * @property \Illuminate\Support\Carbon|null $loan_start_date Tarikh Pinjaman (Date).
 * @property \Illuminate\Support\Carbon|null $loan_end_date Tarikh Pulangan (Date).
 * @property string $status Workflow status (e.g., draft, pending_support, approved, issued, returned, overdue).
 * @property string|null $rejection_reason Reason for rejection (Text).
 * @property \Illuminate\Support\Carbon|null $applicant_confirmation_timestamp Timestamp when applicant confirmed (Timestamp).
 * @property int|null $created_by Foreign key to the user who created the record (handled by trait).
 * @property int|null $updated_by Foreign key to the user who last updated the record (handled by trait).
 * @property int|null $deleted_by Foreign key to the user who soft deleted the record (handled by trait).
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Approval> $approvals Approvals for the loan application.
 * @property-read int|null $approvals_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\LoanApplicationItem> $items Equipment items requested for the loan application.
 * @property-read int|null $items_count
 * @property-read \App\Models\User|null $responsibleOfficer Responsible Officer user for the application.
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\LoanTransaction> $transactions Transactions (issue/return records) for the application.
 * @property-read int|null $transactions_count
 * @property-read \App\Models\User $user The applicant who submitted the application.
 * @property-read \App\Models\User|null $createdBy Relation to the user who created the record.
 * @property-read \App\Models\User|null $deletedBy Relation to the user who soft deleted the record.
 * @property-read \App\Models\User|null $updatedBy Relation to the user who last updated the record.
 * @method static \Illuminate\Database\Eloquent\Builder|LoanApplication newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LoanApplication newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LoanApplication onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|LoanApplication query()
 * @method static \Illuminate\Database\Eloquent\Builder|LoanApplication whereApplicantConfirmationTimestamp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LoanApplication whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LoanApplication whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LoanApplication whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LoanApplication whereDeletedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LoanApplication whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LoanApplication whereLoanEndDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LoanApplication whereLoanStartDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LoanApplication whereLocation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LoanApplication wherePurpose($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LoanApplication whereRejectionReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LoanApplication whereResponsibleOfficerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LoanApplication whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LoanApplication whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LoanApplication whereUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LoanApplication whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LoanApplication withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|LoanApplication withoutTrashed()
 * @property-read string $status_translated
 * @method static \Database\Factories\LoanApplicationFactory factory($count = null, $state = [])
 * @mixin \Eloquent
 */
>>>>>>> cc6eb9f4f020325c04fee080d2466584ff27bb90
class LoanApplication extends Model
{
  use CreatedUpdatedDeletedBy, HasFactory, SoftDeletes;

  // Workflow status constants
  public const STATUS_DRAFT = 'draft';
  public const STATUS_PENDING_SUPPORT = 'pending_support';
  public const STATUS_PENDING_BPM = 'pending_bpm';
  public const STATUS_APPROVED = 'approved';
  public const STATUS_REJECTED = 'rejected';
  public const STATUS_ISSUED = 'issued';
  public const STATUS_RETURNED = 'returned';
  public const STATUS_OVERDUE = 'overdue';
  public const STATUS_PARTIALLY_ISSUED = 'partially_issued';

  protected $fillable = [
    'user_id',
    'responsible_officer_id',
    'purpose',
    'location',
    'loan_start_date',
    'loan_end_date',
    'status',
    'rejection_reason',
    'applicant_confirmation_timestamp',
  ];

  protected $casts = [
    'user_id' => 'integer',
    'responsible_officer_id' => 'integer',
    'purpose' => 'string',
    'location' => 'string',
    'status' => 'string',
    'rejection_reason' => 'string',
    'loan_start_date' => 'date',
    'loan_end_date' => 'date',
    'applicant_confirmation_timestamp' => 'datetime',
    'created_at' => 'datetime',
    'updated_at' => 'datetime',
    'deleted_at' => 'datetime',
  ];

  // Relationships
  public function user(): BelongsTo
  {
    return $this->belongsTo(User::class);
  }

  public function responsibleOfficer(): BelongsTo
  {
    return $this->belongsTo(User::class, 'responsible_officer_id');
  }

  public function items(): HasMany
  {
    return $this->hasMany(LoanApplicationItem::class);
  }

  public function transactions(): HasMany
  {
    return $this->hasMany(LoanTransaction::class);
  }

  public function approvals(): MorphMany
  {
    return $this->morphMany(Approval::class, 'approvable');
  }

  // Status checks
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
    return in_array($this->status, [self::STATUS_RETURNED, self::STATUS_REJECTED]);
  }

  public function isSubmitted(): bool
  {
    return !$this->isDraft();
  }

  // Accessors
  public function getStatusTranslatedAttribute(): string
  {
    return match ($this->status) {
      self::STATUS_DRAFT => __('Draft'),
      self::STATUS_PENDING_SUPPORT => __('Pending Support Review'),
      self::STATUS_PENDING_BPM => __('Pending BPM Action'),
      self::STATUS_APPROVED => __('Approved'),
      self::STATUS_REJECTED => __('Rejected'),
      self::STATUS_PARTIALLY_ISSUED => __('Partially Issued'),
      self::STATUS_ISSUED => __('Issued'),
      self::STATUS_RETURNED => __('Returned'),
      self::STATUS_OVERDUE => __('Overdue'),
      default => __('Unknown Status'),
    };
  }
}
