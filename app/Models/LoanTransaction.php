<?php

namespace App\Models;

use App\Traits\CreatedUpdatedDeletedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon; // Import for PHPDoc

/**
 * @property int $id
 * @property int $loan_application_id
 * @property int|null $equipment_id // nullable if equipment was lost
 * @property int|null $issuing_officer_id
 * @property int|null $receiving_officer_id // The person receiving on behalf of the applicant (if different)
 * @property array|null $accessories_checklist_on_issue // JSON column
 * @property Carbon|null $issue_timestamp
 * @property int|null $returning_officer_id // The person returning on behalf of the applicant (if different)
 * @property int|null $return_accepting_officer_id // BPM staff who accepted the return
 * @property array|null $accessories_checklist_on_return // JSON column
 * @property string|null $equipment_condition_on_return // e.g., 'Good', 'Damaged', 'Lost'
 * @property string|null $return_notes
 * @property Carbon|null $return_timestamp
 * @property string $status // e.g., 'issued', 'returned', 'under_maintenance_on_loan', 'damaged_on_return', 'lost_on_return', 'cancelled', 'overdue', 'under_maintenance_on_return'
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property int|null $deleted_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 *
 * // Relationships (for static analysis)
 * @property-read \App\Models\LoanApplication $loanApplication
 * @property-read \App\Models\Equipment|null $equipment
 * @property-read \App\Models\User|null $issuingOfficer
 * @property-read \App\Models\User|null $receivingOfficer
 * @property-read \App\Models\User|null $returningOfficer
 * @property-read \App\Models\User|null $returnAcceptingOfficer
 */
class LoanTransaction extends Model
{
  use CreatedUpdatedDeletedBy, SoftDeletes;

  protected $table = 'loan_transactions'; // Explicitly define table name if it deviates from convention

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
    // created_by, updated_by, deleted_by are handled by the trait
  ];

  protected $casts = [
    'accessories_checklist_on_issue' => 'array', // Cast JSON columns
    'accessories_checklist_on_return' => 'array', // Cast JSON columns
    'issue_timestamp' => 'datetime', // Cast timestamp columns
    'return_timestamp' => 'datetime', // Cast timestamp columns
  ];


  // --- Status Constants ---
  public const STATUS_ISSUED = 'issued'; // Equipment has been issued
  public const STATUS_RETURNED = 'returned'; // All issued equipment has been returned
  public const STATUS_ON_LOAN = 'on_loan'; // Equipment is currently on loan (distinct status)
  // *** FIX 1: Added the missing constant for LoanApplicationService ***
  public const STATUS_UNDER_MAINTENANCE_ON_LOAN = 'under_maintenance_on_loan'; // Equipment is on loan but reported under maintenance
  public const STATUS_UNDER_MAINTENANCE_ON_RETURN = 'under_maintenance_on_return'; // *** FIX 2: Added constant *** Equipment returned needs maintenance
  public const STATUS_DAMAGED_ON_RETURN = 'damaged_on_return'; // Equipment returned damaged
  public const STATUS_LOST_ON_RETURN = 'lost_on_return'; // Equipment returned as lost
  public const STATUS_CANCELLED = 'cancelled'; // Transaction was cancelled
  public const STATUS_OVERDUE = 'overdue'; // Transaction is overdue for return
  // --- End Status Constants ---\


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
  public function equipment(): BelongsTo
  {
    return $this->belongsTo(Equipment::class, 'equipment_id');
  }

  /**
   * Get the officer who issued the equipment.
   */
  public function issuingOfficer(): BelongsTo
  {
    return $this->belongsTo(User::class, 'issuing_officer_id');
  }

  /**
   * Get the officer who received the equipment on behalf of the applicant (if applicable).
   */
  public function receivingOfficer(): BelongsTo
  {
    return $this->belongsTo(User::class, 'receiving_officer_id');
  }

  /**
   * Get the officer who returned the equipment on behalf of the applicant (if applicable).\
   */
  public function returningOfficer(): BelongsTo
  {
    return $this->belongsTo(User::class, 'returning_officer_id');
  }

  /**
   * Get the officer who accepted the return of the equipment.
   */
  public function returnAcceptingOfficer(): BelongsTo
  {
    return $this->belongsTo(User::class, 'return_accepting_officer_id');
  }

  // Add helper methods for status checks
  public function isIssued(): bool
  {
    return $this->status === self::STATUS_ISSUED;
  }

  public function isReturned(): bool
  {
    return $this->status === self::STATUS_RETURNED;
  }

  public function isOnLoan(): bool
  {
    return $this->status === self::STATUS_ON_LOAN;
  }

  public function isUnderMaintenanceOnLoan(): bool
  {
    return $this->status === self::STATUS_UNDER_MAINTENANCE_ON_LOAN;
  }

  public function isDamagedOnReturn(): bool
  {
    return $this->status === self::STATUS_DAMAGED_ON_RETURN;
  }

  public function isLostOnReturn(): bool
  {
    return $this->status === self::STATUS_LOST_ON_RETURN;
  }

  public function isCancelled(): bool
  {
    return $this->status === self::STATUS_CANCELLED;
  }

  public function isOverdue(): bool
  {
    return $this->status === self::STATUS_OVERDUE;
  }

  // *** FIX 3: Added helper method for the new constant ***
  public function isUnderMaintenanceOnReturn(): bool
  {
    return $this->status === self::STATUS_UNDER_MAINTENANCE_ON_RETURN;
  }


  // Define which statuses indicate the equipment is currently out on loan
  public function isCurrentlyOnLoan(): bool
  {
    // An item is on loan if its status is ISSUED, ON_LOAN, or UNDER_MAINTENANCE_ON_LOAN
    return in_array($this->status, [
      self::STATUS_ISSUED,
      self::STATUS_ON_LOAN,
      self::STATUS_UNDER_MAINTENANCE_ON_LOAN,
    ]);
  }


  /**
   * Determine if the transaction can be returned.
   *
   * @return bool
   */
  public function canBeReturned(): bool
  {
    // Equipment can be returned if it's currently issued or under maintenance on loan
    return $this->isCurrentlyOnLoan();
  }
}
