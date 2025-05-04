<?php

namespace App\Models; // Ensure the namespace is correct for your project

use App\Traits\CreatedUpdatedDeletedBy; // Assuming this trait exists and adds audit FKs/methods like created_by, updated_by, deleted_by
use Carbon\Carbon; // Import Carbon for type hinting with timestamps and date/datetime casts
use Illuminate\Database\Eloquent\Factories\HasFactory; // Import HasFactory trait
use Illuminate\Database\Eloquent\Model; // Import base Model class
use Illuminate\Database\Eloquent\Relations\BelongsTo; // Import BelongsTo relationship type
use Illuminate\Database\Eloquent\SoftDeletes; // Import SoftDeletes trait


// Import models for relationships (Eloquent needs to know about the related models for relationships)
use App\Models\LoanApplication; // LoanTransaction belongsTo LoanApplication
use App\Models\Equipment; // LoanTransaction belongsTo Equipment
use App\Models\User; // LoanTransaction belongsTo User (for the new 'user' relationship and audit/officer relationships)


/**
 * App\Models\LoanTransaction
 *
 * Represents a single transaction record within a loan application.
 * Tracks the issue or return of a specific equipment item, including
 * involved officers, checklists, timestamps, notes, and the transaction status.
 * Linked to a parent LoanApplication and a specific Equipment asset.
 * Includes audit trails and soft deletion.
 *
 * @property int $id
 * @property int $loan_application_id Foreign key to the parent loan application ('loan_applications' table).
 * @property int $equipment_id Foreign key to the specific equipment asset involved ('equipment' table).
 * @property int|null $issuing_officer_id Foreign key to the user who issued the equipment ('users' table, nullable).
 * @property int|null $receiving_officer_id Foreign key to the user who received the equipment at issue ('users' table, typically the applicant, nullable).
 * @property array|null $accessories_checklist_on_issue JSON checklist of accessories noted at the time of issue (nullable).
 * @property \Illuminate\Support\Carbon|null $issue_timestamp Timestamp when the equipment was issued (nullable).
 * @property int|null $returning_officer_id Foreign key to the user who returned the equipment ('users' table, typically the applicant, nullable).
 * @property int|null $return_accepting_officer_id Foreign key to the user who accepted the returned equipment ('users' table, typically BPM staff, nullable).
 * @property array|null $accessories_checklist_on_return JSON checklist of accessories noted at the time of return (nullable).
 * @property \Illuminate\Support\Carbon|null $return_timestamp Timestamp when the equipment was returned (nullable).
 * @property string|null $return_notes Notes recorded upon return of the equipment (Text or String, nullable).
 * @property string $status Workflow status of the transaction (e.g., issued, returned, overdue, lost, damaged).
 * @property int|null $created_by Foreign key to the user who created the record.
 * @property int|null $updated_by Foreign key to the user who last updated the record.
 * @property int|null $deleted_by Foreign key to the user who soft deleted the record.
 * @property \Illuminate\Support\Carbon|null $created_at Automatically managed timestamp for creation.
 * @property \Illuminate\Support\Carbon|null $updated_at Automatically managed timestamp for last update.
 * @property \Illuminate\Support\Carbon|null $deleted_at Timestamp for soft deletion.
 *
 * @property-read \App\Models\Equipment $equipment The specific equipment asset model involved in the transaction.
 * @property-read \App\Models\User|null $issuingOfficer The user model for the officer who issued the equipment.
 * @property-read \App\Models\LoanApplication $loanApplication The parent loan application model associated with the transaction.
 * @property-read \App\Models\User|null $receivingOfficer The user model for the officer who received the equipment at issue.
 * @property-read \App\Models\User|null $returnAcceptingOfficer The user model for the officer who accepted the returned equipment.
 * @property-read \App\Models\User|null $returningOfficer The user model for the officer who returned the equipment.
 * @property-read \App\Models\User|null $user The generic user associated with the transaction (e.g., the recipient). // Added for clarity
 * @property-read \App\Models\User|null $createdBy The user who created the record (if trait adds this).
 * @property-read \App\Models\User|null $deletedBy The user who soft deleted the record (if trait adds this).
 * @property-read \App\Models\User|null $updatedBy The user who last updated the record (if trait adds this).
 *
 * @property-read string $statusTranslated The human-readable, translated workflow status for the transaction.
 *
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
 * @property-read string $status_translated
 * @method static \Database\Factories\LoanTransactionFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LoanTransaction whereId($value)
 * @mixin \Eloquent
 */
class LoanTransaction extends Model
{
  // Use the traits for factory, soft deletes, and audit columns
  use CreatedUpdatedDeletedBy, HasFactory, SoftDeletes;

  /**
   * The table associated with the model.
   *
   * @var string
   */
  // protected $table = 'loan_transactions';


  // Define constants for transaction statuses
  public const STATUS_ISSUED = 'issued';
  public const STATUS_RETURNED = 'returned';
  public const STATUS_OVERDUE = 'overdue';
  public const STATUS_LOST = 'lost';
  public const STATUS_DAMAGED = 'damaged';


  /**
   * The attributes that are mass assignable.
   *
   * @var array<int, string>
   */
  protected $fillable = [
    'loan_application_id',
    'equipment_id',

    'issuing_officer_id',
    'receiving_officer_id', // This field might link to the user receiving the loan

    'accessories_checklist_on_issue',
    'issue_timestamp',

    'returning_officer_id',
    'return_accepting_officer_id',

    'accessories_checklist_on_return',
    'return_timestamp',
    'return_notes',

    'status',
  ];

  /**
   * The attributes that should be cast.
   *
   * @var array<string, string>
   */
  protected $casts = [
    'loan_application_id' => 'integer',
    'equipment_id' => 'integer',
    'issuing_officer_id' => 'integer',
    'receiving_officer_id' => 'integer',
    'returning_officer_id' => 'integer',
    'return_accepting_officer_id' => 'integer',

    'accessories_checklist_on_issue' => 'json',
    'accessories_checklist_on_return' => 'json',

    'issue_timestamp' => 'datetime',
    'return_timestamp' => 'datetime',

    'return_notes' => 'string',

    'status' => 'string',

    'created_at' => 'datetime',
    'updated_at' => 'datetime',
    'deleted_at' => 'datetime',
  ];

  /**
   * The attributes that should be hidden for serialization.
   *
   * @var array<int, string>
   */
  // protected $hidden = [];


  // The CreatedUpdatedDeletedBy trait is assumed to add these audit relationships:
  // public function createdBy(): BelongsTo;
  // public function updatedBy(): BelongsTo;
  // public function deletedBy(): BelongsTo;


  // 👉 Relationships

  /**
   * Get the user associated with the loan transaction.
   * This relationship might link to the user who is the recipient of the loan.
   * Assuming it links to the receiving_officer_id for simplicity based on existing fields.
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\User>
   */
  public function user(): BelongsTo // Added the missing 'user' relationship
  {
    // Link to the User model using the receiving_officer_id foreign key
    return $this->belongsTo(User::class, 'receiving_officer_id');
  }


  /**
   * Get the loan application associated with the transaction.
   * Defines a many-to-one relationship where a LoanTransaction belongs to one LoanApplication.
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\LoanApplication>
   */
  public function loanApplication(): BelongsTo
  {
    return $this->belongsTo(LoanApplication::class, 'loan_application_id');
  }

  /**
   * Get the specific equipment asset involved in the transaction.
   * Defines a many-to-one relationship where a LoanTransaction belongs to one Equipment asset.
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Equipment>
   */
  public function equipment(): BelongsTo
  {
    return $this->belongsTo(Equipment::class, 'equipment_id');
  }

  /**
   * Get the issuing officer.
   * Defines a many-to-one relationship where a LoanTransaction belongs to one User (the issuing officer).
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\User>
   */
  public function issuingOfficer(): BelongsTo
  {
    return $this->belongsTo(User::class, 'issuing_officer_id');
  }

  /**
   * Get the receiving officer (at issue).
   * Defines a many-to-one relationship where a LoanTransaction belongs to one User (the receiving officer at issue).
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\User>
   */
  public function receivingOfficer(): BelongsTo
  {
    return $this->belongsTo(User::class, 'receiving_officer_id');
  }

  /**
   * Get the returning officer.
   * Defines a many-to-one relationship where a LoanTransaction belongs to one User (the officer who returned).
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\User>
   */
  public function returningOfficer(): BelongsTo
  {
    return $this->belongsTo(User::class, 'returning_officer_id');
  }

  /**
   * Get the return accepting officer.
   * Defines a many-to-one relationship where a LoanTransaction belongs to one User (the officer who accepted the return).
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\User>
   */
  public function returnAcceptingOfficer(): BelongsTo
  {
    return $this->belongsTo(User::class, 'return_accepting_officer_id');
  }


  // 👉 Helper Methods (Transaction Status Checks)

  /**
   * Check if the transaction has a specific workflow status.
   *
   * @param string $status The status value to check against (use LoanTransaction::STATUS_ constants).
   * @return bool True if the transaction has the specified status, false otherwise.
   */
  public function hasStatus(string $status): bool
  {
    return $this->status === $status;
  }

  /**
   * Check if the transaction represents an item that has been issued.
   *
   * @return bool True if the transaction is issued, false otherwise.
   */
  public function isIssued(): bool
  {
    return $this->hasStatus(self::STATUS_ISSUED);
  }

  /**
   * Check if the transaction represents an item that has been returned.
   *
   * @return bool True if the transaction is returned, false otherwise.
   */
  public function isReturned(): bool
  {
    return $this->hasStatus(self::STATUS_RETURNED);
  }

  /**
   * Check if the transaction is marked as overdue.
   *
   * @return bool True if the transaction is overdue, false otherwise.
   */
  public function isOverdue(): bool
  {
    return $this->hasStatus(self::STATUS_OVERDUE);
  }

  /**
   * Check if the transaction is marked as lost.
   *
   * @return bool True if the transaction is lost, false otherwise.
   */
  public function isLost(): bool
  {
    return $this->hasStatus(self::STATUS_LOST);
  }

  /**
   * Check if the transaction is marked as damaged.
   *
   * @return bool True if the transaction is damaged, false otherwise.
   */
  public function isDamaged(): bool
  {
    return $this->hasStatus(self::STATUS_DAMAGED);
  }


  /**
   * Check if the transaction has both issue and return information recorded.
   *
   * @return bool True if both issue and return timestamps are present, false otherwise.
   */
  public function isCompletedTransaction(): bool
  {
    return $this->issue_timestamp !== null && $this->return_timestamp !== null;
  }

  /**
   * Accessor to get the translated status string.
   *
   * @return string The human-readable, translated workflow status for the transaction.
   */
  protected function getStatusTranslatedAttribute(): string
  {
    return match ($this->status) {
      self::STATUS_ISSUED => __('Issued'),
      self::STATUS_RETURNED => __('Returned'),
      self::STATUS_OVERDUE => __('Overdue'),
      self::STATUS_LOST => __('Lost'),
      self::STATUS_DAMAGED => __('Damaged'),
      default => $this->status,
    };
  }
}
