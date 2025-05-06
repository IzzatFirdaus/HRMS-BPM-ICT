<?php

namespace App\Models;

use App\Traits\CreatedUpdatedDeletedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $loan_application_id
 * @property int|null $equipment_id // nullable if equipment was lost before transaction created? Or should always link to Equipment?
 * @property int|null $issuing_officer_id
 * @property int|null $receiving_officer_id // The person receiving on behalf of the applicant (if different)
 * @property array|null $accessories_checklist_on_issue // JSON column
 * @property Carbon|null $issue_timestamp
 * @property int|null $returning_officer_id // The person returning on behalf of the applicant (if different)
 * @property int|null $return_accepting_officer_id // BPM staff who accepted the return
 * @property array|null $accessories_checklist_on_return // JSON column
 * @property string|null $equipment_condition_on_return // e.g., 'Good', 'Damaged', 'Lost', 'Needs Maintenance'
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
 * Relationships:
 * @property-read \App\Models\LoanApplication $loanApplication
 * @property-read \App\Models\Equipment|null $equipment
 * @property-read \App\Models\User|null $issuingOfficer
 * @property-read \App\Models\User|null $receivingOfficer
 * @property-read \App\Models\User|null $returningOfficer
 * @property-read \App\Models\User|null $returnAcceptingOfficer
 */
class LoanTransaction extends Model
{
  use SoftDeletes, CreatedUpdatedDeletedBy; // Assuming these traits are used

  protected $table = 'loan_transactions'; // Ensure table name is correct

  // Fillable fields based on columns that can be mass-assigned
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

  // Cast JSON columns to arrays
  protected $casts = [
    'accessories_checklist_on_issue' => 'array',
    'accessories_checklist_on_return' => 'array',
    'issue_timestamp' => 'datetime',
    'return_timestamp' => 'datetime',
  ];

  // Default values for attributes if needed
  // protected $attributes = [
  //     'status' => self::STATUS_DRAFT, // Example if transactions start as draft
  // ];

  // --- Status Constants ---
  // Define possible statuses for a loan transaction lifecycle.
  const STATUS_ISSUED                         = 'issued'; // Equipment has been issued
  const STATUS_RETURNED                       = 'returned'; // Equipment has been successfully returned
  const STATUS_UNDER_MAINTENANCE_ON_LOAN      = 'under_maintenance_on_loan'; // Equipment needed maintenance while issued
  const STATUS_DAMAGED_ON_RETURN              = 'damaged_on_return'; // Equipment returned damaged
  const STATUS_LOST_ON_RETURN                 = 'lost_on_return'; // Equipment reported lost on return
  const STATUS_CANCELLED                      = 'cancelled'; // Transaction was cancelled (e.g., before issue)
  const STATUS_OVERDUE                        = 'overdue'; // Transaction is overdue for return
  const STATUS_UNDER_MAINTENANCE_ON_RETURN    = 'under_maintenance_on_return'; // Equipment needs maintenance after return

  // *** FIX 4: Add the missing STATUS_ON_LOAN constant ***
  // Note: While the service uses STATUS_ISSUED for the transaction state
  // when equipment is out, the policy diagnostic specifically requires this constant.
  const STATUS_ON_LOAN = 'on_loan'; // Adding constant as requested by diagnostic

  // --- End Status Constants ---


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
    // Ensure the foreign key is correct if it's not the default 'equipment_id'
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
   * Get the officer who returned the equipment on behalf of the applicant (if applicable).
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
    return $this->status === self::STATUS_ISSUED; // Use constant
  }

  public function isReturned(): bool
  {
    return $this->status === self::STATUS_RETURNED; // Use constant
  }

  public function isUnderMaintenanceOnLoan(): bool
  {
    return $this->status === self::STATUS_UNDER_MAINTENANCE_ON_LOAN; // Use constant
  }

  // Add other helper methods for other statuses as needed
  public function isDamagedOnReturn(): bool
  {
    return $this->status === self::STATUS_DAMAGED_ON_RETURN; // Use constant
  }

  public function isLostOnReturn(): bool
  {
    return $this->status === self::STATUS_LOST_ON_RETURN; // Use constant
  }

  public function isCancelled(): bool
  {
    return $this->status === self::STATUS_CANCELLED; // Use constant
  }

  public function isOverdue(): bool
  {
    return $this->status === self::STATUS_OVERDUE; // Use constant
  }

  public function isUnderMaintenanceOnReturn(): bool
  {
    return $this->status === self::STATUS_UNDER_MAINTENANCE_ON_RETURN; // Use constant
  }

  // Add any other model logic (scopes, accessors, mutators) here...
}
