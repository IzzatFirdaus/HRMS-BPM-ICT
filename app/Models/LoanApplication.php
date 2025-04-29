<?php

namespace App\Models;

use App\Traits\CreatedUpdatedDeletedBy; // Assuming this trait exists and adds audit FKs/methods
use Carbon\Carbon; // Assuming Carbon is used implicitly with date casts
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo; // Use BelongsTo trait
use Illuminate\Database\Eloquent\Relations\HasMany; // Use HasMany trait
use Illuminate\Database\Eloquent\Relations\MorphMany; // Use MorphMany trait for polymorphic relationship
use Illuminate\Database\Eloquent\SoftDeletes; // Use SoftDeletes trait
use Illuminate\Database\Eloquent\Relations\BelongsTo as BelongsToRelation; // Alias if needed
use Illuminate\Database\Eloquent\Relations\HasMany as HasManyRelation; // Alias if needed
use Illuminate\Database\Eloquent\Relations\MorphMany as MorphManyRelation; // Alias if needed
use Illuminate\Support\Collection; // Import Collection for return type hinting


// Import models for relationships
use App\Models\User; // LoanApplication belongs to users (applicant, responsible officer)
use App\Models\LoanApplicationItem; // LoanApplication has many Items
use App\Models\LoanTransaction; // LoanApplication has many Transactions
use App\Models\Approval; // LoanApplication has many Approvals (polymorphic)


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
 * @property int|null $created_by Foreign key to the user who created the record.
 * @property int|null $updated_by Foreign key to the user who last updated the record.
 * @property int|null $deleted_by Foreign key to the user who soft deleted the record.
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
 * @property-read \App\Models\User|null $createdBy The user who created the record.
 * @property-read \App\Models\User|null $deletedBy The user who soft deleted the record.
 * @property-read \App\Models\User|null $updatedBy The user who last updated the record.
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
 * @mixin \Eloquent
 */
class LoanApplication extends Model
{
  // Use the traits for factory, soft deletes, and audit columns
  use CreatedUpdatedDeletedBy, HasFactory, SoftDeletes;

  // Define constants for application statuses for better code readability and maintainability
  public const STATUS_DRAFT = 'draft'; // Application is being created, not yet submitted
  public const STATUS_PENDING_SUPPORT = 'pending_support'; // Submitted, pending review by IT Support/BPM
  public const STATUS_PENDING_BPM = 'pending_bpm'; // Submitted, pending review/action by BPM (if different from Support)
  public const STATUS_APPROVED = 'approved'; // Approved by all necessary parties
  public const STATUS_REJECTED = 'rejected'; // Rejected at some stage
  public const STATUS_ISSUED = 'issued'; // Equipment has been issued to the applicant
  public const STATUS_RETURNED = 'returned'; // Equipment has been returned
  public const STATUS_OVERDUE = 'overdue'; // Equipment is past its return date
  // Add other statuses as needed (e.g., 'cancelled')


  /**
   * The attributes that are mass assignable.
   * Includes all workflow and data fields from the migration.
   * 'id' is excluded as it is the primary key.
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

    // 'created_by', // Handled by trait
    // 'updated_by', // Handled by trait
    // 'deleted_by', // Handled by trait
  ];

  /**
   * The attributes that should be cast.
   * Ensures data types are correct and dates are Carbon instances.
   * Includes casts for FKs, dates, timestamps, status, and standard audit/soft delete timestamps.
   * Explicitly casting all attributes for clarity.
   *
   * @var array<string, string>
   */
  protected $casts = [
    'user_id' => 'integer', // Cast FKs to integer
    'responsible_officer_id' => 'integer',

    'purpose' => 'string', // Explicitly cast string attributes
    'location' => 'string',
    'status' => 'string', // Cast status as string (or to ApplicationStatus::class if using PHP Enums)
    'rejection_reason' => 'string',

    'loan_start_date' => 'date', // Cast date fields to Carbon instances (YYYY-MM-DD)
    'loan_end_date' => 'date',
    'applicant_confirmation_timestamp' => 'datetime', // Cast timestamp to Carbon instance

    'created_at' => 'datetime', // Explicitly cast timestamps
    'updated_at' => 'datetime',
    'deleted_at' => 'datetime', // Cast soft delete timestamp
  ];


  // The CreatedUpdatedDeletedBy trait is assumed to add these audit relationships:

  /**
   * Get the user who created the model.
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\User, \App\Models\LoanApplication>
   */
  // public function createdBy(): BelongsToRelation;

  /**
   * Get the user who last updated the model.
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\User, \App\Models\LoanApplication>
   */
  // public function updatedBy(): BelongsToRelation;

  /**
   * Get the user who soft deleted the model.
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\User, \App\Models\LoanApplication>
   */
  // public function deletedBy(): BelongsToRelation;


  // 👉 Relationships

  /**
   * Get the user who submitted the loan application (the applicant).
   * Defines a many-to-one relationship where a LoanApplication belongs to one User.
   * Assumes the 'loan_applications' table has a 'user_id' foreign key.
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\User, \App\Models\LoanApplication>
   */
  public function user(): BelongsTo // Added return type hint
  {
    // Defines a many-to-one relationship with the User model
    return $this->belongsTo(User::class, 'user_id'); // Explicitly define foreign key
  }

  /**
   * Get the responsible officer user for the loan application (if applicable).
   * Defines a many-to-one relationship where a LoanApplication belongs to one User (the responsible officer).
   * Assumes the 'loan_applications' table has a 'responsible_officer_id' foreign key.
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\User, \App\Models\LoanApplication>
   */
  public function responsibleOfficer(): BelongsTo // Added return type hint
  {
    // Defines a many-to-one relationship with the User model
    return $this->belongsTo(User::class, 'responsible_officer_id'); // Belongs to User model with responsible_officer_id foreign key
  }

  /**
   * Get the equipment items requested for the loan application.
   * Defines a one-to-many relationship where a LoanApplication has many LoanApplicationItems.
   * Assumes the 'loan_application_items' table has a 'loan_application_id' foreign key.
   *
   * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\LoanApplicationItem>
   */
  public function items(): HasMany // Added return type hint
  {
    // Defines a one-to-many relationship with the LoanApplicationItem model
    return $this->hasMany(LoanApplicationItem::class, 'loan_application_id'); // Explicitly define foreign key
  }

  /**
   * Get the transactions (issue/return records) for the loan application.
   * Defines a one-to-many relationship where a LoanApplication has many LoanTransactions.
   * Assumes the 'loan_transactions' table has a 'loan_application_id' foreign key.
   *
   * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\LoanTransaction>
   */
  public function transactions(): HasMany // Added return type hint
  {
    // Defines a one-to-many relationship with the LoanTransaction model
    return $this->hasMany(LoanTransaction::class, 'loan_application_id'); // Explicitly define foreign key
  }

  /**
   * Get the approvals for the loan application (polymorphic relationship).
   * Defines a one-to-many polymorphic relationship where a LoanApplication has many Approvals.
   * Assumes the 'approvals' table has 'approvable_id' and 'approvable_type' columns.
   *
   * @return \Illuminate\Database\Eloquent\Relations\MorphMany<\App\Models\Approval>
   */
  public function approvals(): MorphMany // Added return type hint
  {
    // MorphMany relationship to the Approval model where this model is the 'approvable'
    return $this->morphMany(Approval::class, 'approvable');
  }


  // 👉 Helper Methods (Workflow Status Checks)

  /**
   * Check if the application is in draft status.
   * Checks the status column against the STATUS_DRAFT constant.
   *
   * @return bool
   */
  public function isDraft(): bool // Added return type hint
  {
    return $this->status === self::STATUS_DRAFT; // Use constant
  }

  /**
   * Check if the application is in a pending status requiring support review.
   * Checks the status column against the STATUS_PENDING_SUPPORT constant.
   *
   * @return bool
   */
  public function isPendingSupport(): bool // Added return type hint
  {
    return $this->status === self::STATUS_PENDING_SUPPORT; // Use constant
  }

  /**
   * Check if the application is in a pending status requiring BPM review.
   * Checks the status column against the STATUS_PENDING_BPM constant.
   *
   * @return bool
   */
  public function isPendingBpm(): bool // Added return type hint
  {
    return $this->status === self::STATUS_PENDING_BPM; // Use constant
  }

  /**
   * Check if the application is currently pending any approval step.
   * Checks the status column against the relevant pending constants.
   *
   * @return bool
   */
  public function isPendingApproval(): bool // Added return type hint
  {
    // Include all pending statuses relevant to Loan Applications
    return in_array($this->status, [self::STATUS_PENDING_SUPPORT, self::STATUS_PENDING_BPM]); // Use constants
  }

  /**
   * Check if the application has been approved.
   * Checks the status column against the STATUS_APPROVED constant.
   *
   * @return bool
   */
  public function isApproved(): bool // Added return type hint
  {
    return $this->status === self::STATUS_APPROVED; // Use constant
  }

  /**
   * Check if the application has been rejected.
   * Checks the status column against the STATUS_REJECTED constant.
   *
   * @return bool
   */
  public function isRejected(): bool // Added return type hint
  {
    return $this->status === self::STATUS_REJECTED; // Use constant
  }

  /**
   * Check if the application status indicates equipment has been issued.
   * Checks the status column against the STATUS_ISSUED constant.
   *
   * @return bool
   */
  public function isIssued(): bool // Added return type hint
  {
    return $this->status === self::STATUS_ISSUED; // Use constant
  }

  /**
   * Check if the application status indicates equipment has been returned.
   * Checks the status column against the STATUS_RETURNED constant.
   *
   * @return bool
   */
  public function isReturned(): bool // Added return type hint
  {
    return $this->status === self::STATUS_RETURNED; // Use constant
  }

  /**
   * Check if the application status is marked as overdue.
   * Checks the status column against the STATUS_OVERDUE constant.
   *
   * @return bool
   */
  public function isOverdue(): bool // Added return type hint
  {
    return $this->status === self::STATUS_OVERDUE; // Use constant
  }

  /**
   * Check if the application has been submitted (is not in draft status).
   * Checks the status column against the STATUS_DRAFT constant.
   *
   * @return bool
   */
  public function isSubmitted(): bool // Added helper method for submission check
  {
    return $this->status !== self::STATUS_DRAFT; // Use constant
  }


  // Add custom methods or accessors/mutators here as needed

  /**
   * Get the translated status string.
   *
   * @return string
   */
  public function getStatusTranslatedAttribute(): string // Added accessor for translated status
  {
    return match ($this->status) {
      self::STATUS_DRAFT => __('Draft'),
      self::STATUS_PENDING_SUPPORT => __('Pending Support Review'),
      self::STATUS_PENDING_BPM => __('Pending BPM Review'),
      self::STATUS_APPROVED => __('Approved'),
      self::STATUS_REJECTED => __('Rejected'),
      self::STATUS_ISSUED => __('Issued'),
      self::STATUS_RETURNED => __('Returned'),
      self::STATUS_OVERDUE => __('Overdue'),
      default => $this->status, // Return raw status if unknown
    };
  }
}
