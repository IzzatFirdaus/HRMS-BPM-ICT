<?php

namespace App\Models;

use App\Traits\CreatedUpdatedDeletedBy; // Assuming this trait exists and adds audit FKs/methods
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo; // Use BelongsTo trait
use Illuminate\Database\Eloquent\SoftDeletes; // Use SoftDeletes trait
use Illuminate\Support\Carbon; // Import Carbon for type hinting with timestamps
use Illuminate\Database\Eloquent\Relations\BelongsTo as BelongsToRelation; // Alias if needed


// Import models for relationships
use App\Models\LoanApplication;
use App\Models\Equipment;
use App\Models\User; // For officer relationships
// Removed Approval import as it's not directly related to LoanTransaction model via FK


/**
 * App\Models\LoanTransaction
 *
 * Represents a single transaction record within a loan application,
 * tracking the issue or return of a specific equipment item.
 *
 * @property int $id
 * @property int $loan_application_id Foreign key to the loan application.
 * @property int $equipment_id Foreign key to the equipment asset involved.
 * @property int|null $issuing_officer_id Foreign key to the user who issued the equipment.
 * @property int|null $receiving_officer_id Foreign key to the user who received the equipment (at issue).
 * @property array|null $accessories_checklist_on_issue JSON checklist of accessories on issue.
 * @property \Illuminate\Support\Carbon|null $issue_timestamp Timestamp when the equipment was issued.
 * @property int|null $returning_officer_id Foreign key to the user who returned the equipment.
 * @property int|null $return_accepting_officer_id Foreign key to the user who accepted the returned equipment.
 * @property array|null $accessories_checklist_on_return JSON checklist of accessories on return.
 * @property \Illuminate\Support\Carbon|null $return_timestamp Timestamp when the equipment was returned.
 * @property string|null $return_notes Notes recorded upon return of the equipment.
 * @property string $status Workflow status of the transaction (e.g., issued, returned, overdue).
 * @property int|null $created_by Foreign key to the user who created the record.
 * @property int|null $updated_by Foreign key to the user who last updated the record.
 * @property int|null $deleted_by Foreign key to the user who soft deleted the record.
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\Equipment $equipment The specific equipment asset involved in the transaction.
 * @property-read \App\Models\User|null $issuingOfficer The officer who issued the equipment.
 * @property-read \App\Models\LoanApplication $loanApplication The loan application associated with the transaction.
 * @property-read \App\Models\User|null $receivingOfficer The officer who received the equipment (at issue).
 * @property-read \App\Models\User|null $returnAcceptingOfficer The officer who accepted the returned equipment.
 * @property-read \App\Models\User|null $returningOfficer The officer who returned the equipment.
 * @property-read \App\Models\User|null $createdBy The user who created the record.
 * @property-read \App\Models\User|null $deletedBy The user who soft deleted the record.
 * @property-read \App\Models\User|null $updatedBy The user who last updated the record.
 * @method static \Illuminate\Database\Eloquent\Builder|LoanTransaction newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LoanTransaction newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LoanTransaction onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|LoanTransaction query()
 * @method static \Illuminate\Database\Eloquent\Builder|LoanTransaction whereAccessoriesChecklistOnIssue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LoanTransaction whereAccessoriesChecklistOnReturn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LoanTransaction whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LoanTransaction whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LoanTransaction whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LoanTransaction whereDeletedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LoanTransaction whereEquipmentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LoanTransaction whereIssueTimestamp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LoanTransaction whereIssuingOfficerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LoanTransaction whereLoanApplicationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LoanTransaction whereReceivingOfficerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LoanTransaction whereReturnAcceptingOfficerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LoanTransaction whereReturnNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LoanTransaction whereReturnTimestamp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LoanTransaction whereReturningOfficerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LoanTransaction whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LoanTransaction whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LoanTransaction whereUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LoanTransaction withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|LoanTransaction withoutTrashed()
 * @mixin \Eloquent
 */
class LoanTransaction extends Model
{
  // Use the traits for factory, soft deletes, and audit columns
  use CreatedUpdatedDeletedBy, HasFactory, SoftDeletes;

  // Define constants for transaction statuses for better code readability and maintainability
  public const STATUS_ISSUED = 'issued'; // Equipment has been given to the user
  public const STATUS_RETURNED = 'returned'; // Equipment has been returned
  public const STATUS_OVERDUE = 'overdue'; // Equipment is past its return date
  // Add other statuses as needed (e.g., 'lost', 'damaged_on_loan')


  /**
   * The attributes that are mass assignable.
   * Includes all relevant fields from the migration.
   * 'id' is excluded as it is the primary key.
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

    // 'created_by', // Handled by trait
    // 'updated_by', // Handled by trait
    // 'deleted_by', // Handled by trait
  ];

  /**
   * The attributes that should be cast.
   * Ensures data types are correct and dates are Carbon instances.
   * Includes casts for FKs, JSON, timestamps, status, and standard audit/soft delete timestamps.
   * Explicitly casting all attributes for clarity.
   *
   * @var array<string, string>
   */
  protected $casts = [
    'loan_application_id' => 'integer', // Cast FKs to integer
    'equipment_id' => 'integer',
    'issuing_officer_id' => 'integer',
    'receiving_officer_id' => 'integer',
    'returning_officer_id' => 'integer',
    'return_accepting_officer_id' => 'integer',

    'accessories_checklist_on_issue' => 'json', // Cast JSON fields to arrays/objects
    'accessories_checklist_on_return' => 'json',

    'issue_timestamp' => 'datetime', // Cast timestamps to Carbon instances
    'return_timestamp' => 'datetime',

    'return_notes' => 'string', // Explicitly cast notes as string

    'status' => 'string', // Cast status as string (or to TransactionStatus::class if using PHP Enums)

    'created_at' => 'datetime', // Explicitly cast timestamps
    'updated_at' => 'datetime',
    'deleted_at' => 'datetime', // Cast soft delete timestamp
  ];


  // The CreatedUpdatedDeletedBy trait is assumed to add these audit relationships:

  /**
   * Get the user who created the model.
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\User, \App\Models\LoanTransaction>
   */
  // public function createdBy(): BelongsToRelation;

  /**
   * Get the user who last updated the model.
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\User, \App\Models\LoanTransaction>
   */
  // public function updatedBy(): BelongsToRelation;

  /**
   * Get the user who soft deleted the model.
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\User, \App\Models\LoanTransaction>
   */
  // public function deletedBy(): BelongsToRelation;


  // 👉 Relationships

  /**
   * Get the loan application associated with the transaction.
   * Defines a many-to-one relationship where a LoanTransaction belongs to one LoanApplication.
   * Assumes the 'loan_transactions' table has a 'loan_application_id' foreign key.
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\LoanApplication, \App\Models\LoanTransaction>
   */
  public function loanApplication(): BelongsTo // Added return type hint
  {
    // Defines a many-to-one relationship with the LoanApplication model
    return $this->belongsTo(LoanApplication::class, 'loan_application_id'); // Explicitly define foreign key
  }

  /**
   * Get the specific equipment asset involved in the transaction.
   * Defines a many-to-one relationship where a LoanTransaction belongs to one Equipment asset.
   * Assumes the 'loan_transactions' table has an 'equipment_id' foreign key.
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Equipment, \App\Models\LoanTransaction>
   */
  public function equipment(): BelongsTo // Added return type hint
  {
    // Defines a many-to-one relationship with the Equipment model
    return $this->belongsTo(Equipment::class, 'equipment_id'); // Explicitly define foreign key
  }

  /**
   * Get the issuing officer.
   * Defines a many-to-one relationship where a LoanTransaction belongs to one User (the issuing officer).
   * Assumes the 'loan_transactions' table has an 'issuing_officer_id' foreign key.
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\User, \App\Models\LoanTransaction>
   */
  public function issuingOfficer(): BelongsTo // Added return type hint
  {
    // Defines a many-to-one relationship with the User model
    return $this->belongsTo(User::class, 'issuing_officer_id'); // Explicitly define foreign key
  }

  /**
   * Get the receiving officer (at issue).
   * Defines a many-to-one relationship where a LoanTransaction belongs to one User (the receiving officer at issue).
   * Assumes the 'loan_transactions' table has a 'receiving_officer_id' foreign key.
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\User, \App\Models\LoanTransaction>
   */
  public function receivingOfficer(): BelongsTo // Added return type hint
  {
    // Defines a many-to-one relationship with the User model
    return $this->belongsTo(User::class, 'receiving_officer_id'); // Explicitly define foreign key
  }

  /**
   * Get the returning officer.
   * Defines a many-to-one relationship where a LoanTransaction belongs to one User (the officer who returned).
   * Assumes the 'loan_transactions' table has a 'returning_officer_id' foreign key.
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\User, \App\Models\LoanTransaction>
   */
  public function returningOfficer(): BelongsTo // Added return type hint
  {
    // Defines a many-to-one relationship with the User model
    return $this->belongsTo(User::class, 'returning_officer_id'); // Explicitly define foreign key
  }

  /**
   * Get the return accepting officer.
   * Defines a many-to-one relationship where a LoanTransaction belongs to one User (the officer who accepted the return).
   * Assumes the 'loan_transactions' table has a 'return_accepting_officer_id' foreign key.
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\User, \App\Models\LoanTransaction>
   */
  public function returnAcceptingOfficer(): BelongsTo // Added return type hint
  {
    // Defines a many-to-one relationship with the User model
    return $this->belongsTo(User::class, 'return_accepting_officer_id'); // Explicitly define foreign key
  }


  // 👉 Helper Methods (Transaction Status Checks)

  /**
   * Check if the transaction represents an item that has been issued.
   * Checks the status column against the STATUS_ISSUED constant.
   *
   * @return bool
   */
  public function isIssued(): bool // Added return type hint
  {
    return $this->status === self::STATUS_ISSUED; // Use constant
  }

  /**
   * Check if the transaction represents an item that has been returned.
   * Checks the status column against the STATUS_RETURNED constant.
   *
   * @return bool
   */
  public function isReturned(): bool // Added return type hint
  {
    return $this->status === self::STATUS_RETURNED; // Use constant
  }

  /**
   * Check if the transaction is marked as overdue.
   * Checks the status column against the STATUS_OVERDUE constant.
   *
   * @return bool
   */
  public function isOverdue(): bool // Added return type hint
  {
    return $this->status === self::STATUS_OVERDUE; // Use constant
  }

  // Add custom methods or accessors/mutators here as needed

  /**
   * Check if the transaction has both issue and return information recorded.
   *
   * @return bool
   */
  public function isCompletedTransaction(): bool // Added helper method
  {
    return $this->issue_timestamp !== null && $this->return_timestamp !== null;
  }

  /**
   * Get the translated status string.
   *
   * @return string
   */
  public function getStatusTranslatedAttribute(): string // Added accessor for translated status
  {
    return match ($this->status) {
      self::STATUS_ISSUED => __('Issued'),
      self::STATUS_RETURNED => __('Returned'),
      self::STATUS_OVERDUE => __('Overdue'),
      default => $this->status, // Return raw status if unknown
    };
  }
}
