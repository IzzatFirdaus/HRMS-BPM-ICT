<?php

namespace App\Models;

use Illuminate\Support\Carbon;
use App\Traits\CreatedUpdatedDeletedBy;
use Illuminate\Database\Eloquent\Model;
use App\Models\User; // Import User model
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Equipment; // Import Equipment model
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\LoanApplication; // Import LoanApplication model
use Illuminate\Database\Eloquent\Relations\BelongsTo; // Import BelongsTo

/**
 * App\Models\LoanTransaction
 *
 * Represents a specific transaction record for an equipment loan,
 * tracking issuance, return, and associated details.
 *
 * @property int $id
 * @property int $loan_application_id // The application this transaction is part of
 * @property int|null $equipment_id // The specific equipment item involved
 * @property int|null $user_id // The user who is the borrower (if linked directly to transaction) - Added based on ReportController needs
 * @property int|null $issuing_officer_id // BPM staff who issued
 * @property int|null $receiving_officer_id // The person receiving the equipment
 * @property array|null $accessories_checklist_on_issue // JSON column
 * @property Carbon|null $issue_timestamp
 * @property int|null $returning_officer_id // The person returning the equipment
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
 * @property-read LoanApplication $loanApplication
 * @property-read Equipment $equipment
 * @property-read User|null $user // Relationship to the borrower - Added based on ReportController needs
 * @property-read User|null $issuingOfficer // Relationship to the issuing BPM officer
 * @property-read User|null $receivingOfficer // Relationship to the receiving person
 * @property-read User|null $returningOfficer // Relationship to the returning person
 * @property-read User|null $returnAcceptingOfficer // Relationship to the return accepting BPM officer
 */
class LoanTransaction extends Model
{
  use HasFactory, SoftDeletes, CreatedUpdatedDeletedBy; // Added CreatedUpdatedDeletedBy trait

  protected $fillable = [
    'loan_application_id',
    'equipment_id',
    'user_id', // Added user_id to fillable
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
    'accessories_checklist_on_issue' => 'array',
    'accessories_checklist_on_return' => 'array',
    'issue_timestamp' => 'datetime',
    'return_timestamp' => 'datetime',
    'created_at' => 'datetime',
    'updated_at' => 'datetime',
    'deleted_at' => 'datetime',
  ];

  // Define status constants
  const STATUS_PENDING_ISSUE = 'pending_issue'; // Transaction created but not issued yet
  const STATUS_ISSUED = 'issued';
  const STATUS_RETURNED = 'returned';
  const STATUS_UNDER_MAINTENANCE_ON_LOAN = 'under_maintenance_on_loan';
  const STATUS_DAMAGED_ON_RETURN = 'damaged_on_return';
  const STATUS_LOST_ON_RETURN = 'lost_on_return';
  const STATUS_CANCELLED = 'cancelled'; // Transaction cancelled before issuance
  const STATUS_OVERDUE = 'overdue';
  const STATUS_UNDER_MAINTENANCE_ON_RETURN = 'under_maintenance_on_return';

  // Removed the STATUS_ON_LOAN constant as it belongs to Equipment availability status

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
    return $this->belongsTo(Equipment::class);
  }

  /**
   * Get the user (borrower) associated with this loan transaction.
   * Assumes 'user_id' foreign key in loan_transactions table.
   * This relationship is needed for the ReportController eager loading.
   */
  public function user(): BelongsTo
  {
    // If your foreign key is something other than 'user_id',
    // specify it as the second argument: ->belongsTo(User::class, 'your_foreign_key_name')
    return $this->belongsTo(User::class);
  }

  /**
   * Get the officer who issued the equipment.
   */
  public function issuingOfficer(): BelongsTo
  {
    return $this->belongsTo(User::class, 'issuing_officer_id');
  }

  /**
   * Get the person who received the equipment.
   */
  public function receivingOfficer(): BelongsTo
  {
    return $this->belongsTo(User::class, 'receiving_officer_id');
  }

  /**
   * Get the officer who returned the equipment.
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
}
