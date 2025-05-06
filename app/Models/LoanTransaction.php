<?php

namespace App\Models;

use App\Traits\CreatedUpdatedDeletedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $loan_application_id Foreign key to the parent loan application.
 * @property int|null $equipment_id Foreign key to the specific equipment item (nullable if equipment was lost or not specific).
 * @property int $issuing_officer_id Foreign key to the User who issued the equipment (BPM Staff/Admin).
 * @property int|null $receiving_officer_id Foreign key to the User who received on behalf of the applicant (if different from applicant).
 * @property array|null $accessories_checklist_on_issue JSON column storing checklist items/status upon issue.
 * @property Carbon $issue_timestamp Timestamp when the equipment was issued.
 * @property int|null $returning_officer_id Foreign key to the User who returned on behalf of the applicant (if different from applicant).
 * @property int|null $return_accepting_officer_id Foreign key to the User (BPM Staff/Admin) who accepted the return.
 * @property array|null $accessories_checklist_on_return JSON column storing checklist items/status upon return.
 * @property string|null $equipment_condition_on_return Condition noted upon return (e.g., 'Good', 'Damaged', 'Lost').
 * @property string|null $return_notes Additional notes recorded upon return.
 * @property Carbon|null $return_timestamp Timestamp when the equipment was returned.
 * @property string $status // e.g., 'issued', 'returned', 'under_maintenance_on_loan', 'damaged_on_return', 'lost_on_return', 'cancelled', 'overdue'
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property int|null $deleted_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read LoanApplication $loanApplication
 * @property-read Equipment|null $equipment
 * @property-read User $issuingOfficer
 * @property-read User|null $receivingOfficer
 * @property-read User|null $returningOfficer
 * @property-read User|null $returnAcceptingOfficer
 * @method static \Illuminate\Database\Eloquent\Builder|LoanTransaction newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LoanTransaction newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LoanTransaction onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|LoanTransaction query()
 * @method static \Illuminate\Database\Eloquent\Builder|LoanTransaction withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|LoanTransaction withoutTrashed()
 * @method static \Database\Factories\LoanTransactionFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder|LoanTransaction isIssued()
 * @method static \Illuminate\Database\Eloquent\Builder|LoanTransaction isReturned()
 * @method static \Illuminate\Database\Eloquent\Builder|LoanTransaction isUnderMaintenanceOnLoan()
 * @method static \Illuminate\Database\Eloquent\Builder|LoanTransaction isDamagedOnReturn()
 * @method static \Illuminate\Database\Eloquent\Builder|LoanTransaction isLostOnReturn()
 * @method static \Illuminate\Database\Eloquent\Builder|LoanTransaction isCancelled()
 * @method static \Illuminate\Database\Eloquent\Builder|LoanTransaction isOverdue()
 * @method static \Illuminate\Database\Eloquent\Builder|LoanTransaction isCurrentlyOnLoan()
 * @mixin \Eloquent
 */
class LoanTransaction extends Model
{
  use SoftDeletes, CreatedUpdatedDeletedBy; // Assuming these traits are used

  // --- Status Constants ---
  public const STATUS_ISSUED = 'issued';
  public const STATUS_RETURNED = 'returned';
  public const STATUS_UNDER_MAINTENANCE_ON_LOAN = 'under_maintenance_on_loan'; // Added this constant previously
  public const STATUS_DAMAGED_ON_RETURN = 'damaged_on_return'; // Added this constant previously
  public const STATUS_LOST_ON_RETURN = 'lost_on_return'; // Added this constant previously
  public const STATUS_CANCELLED = 'cancelled'; // Added this constant previously
  public const STATUS_OVERDUE = 'overdue'; // Added this constant previously
  // ADDED THE MISSING CONSTANT
  public const STATUS_UNDER_MAINTENANCE_ON_RETURN = 'under_maintenance_on_return';


  protected $fillable = [
    'loan_application_id',
    'equipment_id',
    'issuing_officer_id',
    'receiving_officer_id',
    'accessories_checklist_on_issue',
    'issue_timestamp',
    'returning_officer_id',
    'return_accepting_officer_id',
    'accessories_checklist_on_return',
    'equipment_condition_on_return',
    'return_notes',
    'return_timestamp',
    'status',
  ];

  protected $casts = [
    'equipment_id' => 'integer',
    'issuing_officer_id' => 'integer',
    'receiving_officer_id' => 'integer',
    'returning_officer_id' => 'integer',
    'return_accepting_officer_id' => 'integer',
    'accessories_checklist_on_issue' => 'array', // Cast JSON columns
    'accessories_checklist_on_return' => 'array', // Cast JSON columns
    'issue_timestamp' => 'datetime',
    'return_timestamp' => 'datetime',
    'created_at' => 'datetime',
    'updated_at' => 'datetime',
    'deleted_at' => 'datetime',
  ];

  // --- Relationships ---

  /**
   * Get the loan application that this transaction belongs to.
   */
  public function loanApplication(): BelongsTo
  {
    return $this->belongsTo(LoanApplication::class);
  }

  /**
   * Get the equipment item involved in this transaction.
   */
  public function equipment(): BelongsTo // Added return type hint
  {
    return $this->belongsTo(Equipment::class, 'equipment_id');
  }

  /**
   * Get the officer who issued the equipment.
   */
  public function issuingOfficer(): BelongsTo // Added return type hint
  {
    return $this->belongsTo(User::class, 'issuing_officer_id');
  }

  /**
   * Get the officer who received the equipment on behalf of the applicant (if applicable).
   */
  public function receivingOfficer(): BelongsTo // Added return type hint
  {
    return $this->belongsTo(User::class, 'receiving_officer_id');
  }

  /**
   * Get the officer who returned the equipment on behalf of the applicant (if applicable).
   */
  public function returningOfficer(): BelongsTo // Added return type hint
  {
    return $this->belongsTo(User::class, 'returning_officer_id');
  }

  /**
   * Get the officer who accepted the return of the equipment.
   */
  public function returnAcceptingOfficer(): BelongsTo // Added return type hint
  {
    return $this->belongsTo(User::class, 'return_accepting_officer_id');
  }


  // --- Helper Methods for Status Checks ---

  public function hasStatus(string $status): bool
  {
    return $this->status === $status;
  }

  public function isIssued(): bool
  {
    return $this->hasStatus(self::STATUS_ISSUED);
  }

  public function isReturned(): bool
  {
    return $this->hasStatus(self::STATUS_RETURNED);
  }

  public function isUnderMaintenanceOnLoan(): bool
  {
    return $this->hasStatus(self::STATUS_UNDER_MAINTENANCE_ON_LOAN);
  }

  public function isDamagedOnReturn(): bool
  {
    return $this->hasStatus(self::STATUS_DAMAGED_ON_RETURN);
  }

  public function isLostOnReturn(): bool
  {
    return $this->hasStatus(self::STATUS_LOST_ON_RETURN);
  }

  public function isCancelled(): bool
  {
    return $this->hasStatus(self::STATUS_CANCELLED);
  }

  public function isOverdue(): bool
  {
    return $this->hasStatus(self::STATUS_OVERDUE);
  }

  /**
   * Check if the transaction status indicates the item is currently on loan (not returned, lost, or cancelled).
   *
   * @return bool
   */
  public function isCurrentlyOnLoan(): bool // Helper method added in previous turn
  {
    return in_array($this->status, [
      self::STATUS_ISSUED,
      self::STATUS_UNDER_MAINTENANCE_ON_LOAN,
      // Add any other 'on loan' statuses here
    ]);
  }

  // Add other helper methods as needed (e.g., isReturnable, isIssueable)

}
