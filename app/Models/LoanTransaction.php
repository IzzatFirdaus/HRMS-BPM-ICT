<?php

namespace App\Models; // Ensure the namespace is correct for your project

use App\Traits\CreatedUpdatedDeletedBy; // Assuming this trait exists and adds audit FKs/methods like created_by, updated_by, deleted_by
use Carbon\Carbon; // Import Carbon for type hinting with timestamps and date/datetime casts
use Illuminate\Database\Eloquent\Factories\HasFactory; // Import HasFactory trait
use Illuminate\Database\Eloquent\Model; // Import base Model class
use Illuminate\Database\Eloquent\Relations\BelongsTo; // Import BelongsTo relationship type
use Illuminate\Database\Eloquent\SoftDeletes; // Import SoftDeletes trait
// Removed unused import: use Illuminate\Database\Eloquent\Casts\Attribute;
// Removed unused aliased import: BelongsToRelation


// Import models for relationships (Eloquent needs to know about the related models for relationships)
use App\Models\LoanApplication; // LoanTransaction belongsTo LoanApplication
use App\Models\Equipment; // LoanTransaction belongsTo Equipment
use App\Models\User; // LoanTransaction belongsTo Issuing/Receiving/Returning/ReturnAccepting Officer and audit users


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
 * @property string $status Workflow status of the transaction (e.g., issued, returned, overdue).
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
 * @mixin \Eloquent
 */
class LoanTransaction extends Model
{
  // Use the traits for factory, soft deletes, and audit columns
  // HasFactory is for model factories.
  // SoftDeletes adds the 'deleted_at' column and scope.
  // CreatedUpdatedDeletedBy is assumed to add 'created_by', 'updated_by', 'deleted_by' columns and relationships.
  use CreatedUpdatedDeletedBy, HasFactory, SoftDeletes;

  /**
   * The table associated with the model.
   *
   * @var string
   */
  // protected $table = 'loan_transactions'; // Explicitly define table name if it's not the plural of the model name


  // Define constants for transaction statuses for better code readability and maintainability.
  // These should align with the values used in your workflow logic and database enum.
  public const STATUS_ISSUED = 'issued';             // Equipment has been physically issued to the user
  public const STATUS_RETURNED = 'returned';           // Equipment has been physically returned
  public const STATUS_OVERDUE = 'overdue';             // Equipment is past its expected return date
  // Add other statuses as needed (e.g., 'lost', 'damaged_on_loan', 'in_repair')


  /**
   * The attributes that are mass assignable.
   * Allows these attributes to be set using mass assignment (e.g., during create or update).
   * Includes all relevant fields from the migration.
   * 'id' is excluded as it is the primary key.
   *
   * @var array<int, string>
   */
  protected $fillable = [
    'loan_application_id',         // Foreign key to the parent loan application
    'equipment_id',                // Foreign key to the equipment asset involved

    'issuing_officer_id',          // Foreign key to the user who issued the equipment (nullable)
    'receiving_officer_id',        // Foreign key to the user who received the equipment at issue (typically the applicant, nullable)

    'accessories_checklist_on_issue', // JSON checklist of accessories on issue (nullable)
    'issue_timestamp',             // Timestamp when the equipment was issued (nullable)

    'returning_officer_id',        // Foreign key to the user who returned the equipment (typically the applicant, nullable)
    'return_accepting_officer_id', // Foreign key to the user who accepted the returned equipment (typically BPM staff, nullable)

    'accessories_checklist_on_return', // JSON checklist of accessories on return (nullable)
    'return_timestamp',            // Timestamp when the equipment was returned (nullable)
    'return_notes',                // Notes recorded upon return of the equipment (Text/String, nullable)

    'status',                      // Workflow status of the transaction (String/Enum)

    // The CreatedUpdatedDeletedBy trait is assumed to handle these audit columns:
    // 'created_by',
    // 'updated_by',
    // 'deleted_by',
  ];

  /**
   * The attributes that should be cast.
   * Ensures data types are correct and dates are Carbon instances.
   * Includes casts for FKs, JSON, timestamps, status, and standard audit/soft delete timestamps.
   * Explicitly casting all attributes for clarity and type safety.
   *
   * @var array<string, string>
   */
  protected $casts = [
    'loan_application_id'         => 'integer',   // Cast FKs to integer
    'equipment_id'                => 'integer',
    'issuing_officer_id'          => 'integer',
    'receiving_officer_id'        => 'integer',
    'returning_officer_id'        => 'integer',
    'return_accepting_officer_id' => 'integer',

    'accessories_checklist_on_issue' => 'json', // Cast JSON fields to arrays/objects
    'accessories_checklist_on_return' => 'json',

    'issue_timestamp'             => 'datetime',  // Cast timestamps to Carbon instances (nullable timestamps will be null)
    'return_timestamp'            => 'datetime',

    'return_notes'                => 'string',    // Explicitly cast notes as string

    'status'                      => 'string',    // Cast status as string (or to TransactionStatus::class if using PHP Enums)

    // Standard Eloquent timestamps
    'created_at'                  => 'datetime',  // Explicitly cast creation timestamp to Carbon instance
    'updated_at'                  => 'datetime',  // Explicitly cast update timestamp to Carbon instance
    'deleted_at'                  => 'datetime',  // Cast soft delete timestamp to Carbon instance
    // Add casts for other attributes if needed
  ];

  /**
   * The attributes that should be hidden for serialization.
   * (Optional) Prevents sensitive attributes from being returned in JSON responses.
   *
   * @var array<int, string>
   */
  // protected $hidden = [
  //     'created_by', // Example: hide audit columns from API responses
  //     'updated_by',
  //     'deleted_by',
  // ];


  // The CreatedUpdatedDeletedBy trait is assumed to add these audit relationships:

  /**
   * Get the user who created the model.
   * Assumes the 'created_by' foreign key exists and links to the 'users' table.
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\User, \App\Models\LoanTransaction>
   */
  // public function createdBy(): BelongsTo;

  /**
   * Get the user who last updated the model.
   * Assumes the 'updated_by' foreign key exists and links to the 'users' table.
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\User, \App\Models\LoanTransaction>
   */
  // public function updatedBy(): BelongsTo;

  /**
   * Get the user who soft deleted the model.
   * Assumes the 'deleted_by' foreign key exists and links to the 'users' table.
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\User, \App\Models\LoanTransaction>
   */
  // public function deletedBy(): BelongsTo;


  // 👉 Relationships

  /**
   * Get the loan application associated with the transaction.
   * Defines a many-to-one relationship where a LoanTransaction belongs to one LoanApplication.
   * This links the individual transaction record back to the overall loan request.
   * Assumes the 'loan_transactions' table has a 'loan_application_id' foreign key that
   * references the 'loan_applications' table's primary key ('id').
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\LoanApplication, \App\Models\LoanTransaction>
   */
  public function loanApplication(): BelongsTo // Added return type hint
  {
    // Defines a many-to-one relationship with the LoanApplication model.
    // 'LoanApplication::class' is the related model.
    // 'loan_application_id' is the foreign key on the 'loan_transactions' table.
    // 'id' is the local key on the 'loan_applications' table (default, can be omitted).
    return $this->belongsTo(LoanApplication::class, 'loan_application_id');
    // If using the standard foreign key name, this is sufficient: return $this->belongsTo(LoanApplication::class);
  }

  /**
   * Get the specific equipment asset involved in the transaction.
   * Defines a many-to-one relationship where a LoanTransaction belongs to one Equipment asset.
   * This links the transaction record to the specific equipment item being issued or returned.
   * Assumes the 'loan_transactions' table has an 'equipment_id' foreign key that
   * references the 'equipment' table's primary key ('id').
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Equipment, \App\Models\LoanTransaction>
   */
  public function equipment(): BelongsTo // Added return type hint
  {
    // Defines a many-to-one relationship with the Equipment model.
    // 'Equipment::class' is the related model.
    // 'equipment_id' is the foreign key on the 'loan_transactions' table.
    // 'id' is the local key on the 'equipment' table (default, can be omitted).
    return $this->belongsTo(Equipment::class, 'equipment_id');
    // If using the standard foreign key name, this is sufficient: return $this->belongsTo(Equipment::class);
  }

  /**
   * Get the issuing officer.
   * Defines a many-to-one relationship where a LoanTransaction belongs to one User (the issuing officer).
   * This officer is responsible for handing over the equipment at issue.
   * Assumes the 'loan_transactions' table has an 'issuing_officer_id' foreign key that
   * references the 'users' table's primary key ('id'). This relationship is nullable.
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\User, \App\Models\LoanTransaction>
   */
  public function issuingOfficer(): BelongsTo // Added return type hint
  {
    // Defines a many-to-one relationship with the User model.
    // 'User::class' is the related model.
    // 'issuing_officer_id' is the foreign key on the 'loan_transactions' table.
    // 'id' is the local key on the 'users' table (default, can be omitted).
    return $this->belongsTo(User::class, 'issuing_officer_id');
  }

  /**
   * Get the receiving officer (at issue).
   * Defines a many-to-one relationship where a LoanTransaction belongs to one User (the receiving officer at issue).
   * This is typically the applicant, responsible for receiving the equipment.
   * Assumes the 'loan_transactions' table has a 'receiving_officer_id' foreign key that
   * references the 'users' table's primary key ('id'). This relationship is nullable.
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\User, \App\Models\LoanTransaction>
   */
  public function receivingOfficer(): BelongsTo // Added return type hint
  {
    // Defines a many-to-one relationship with the User model.
    // 'User::class' is the related model.
    // 'receiving_officer_id' is the foreign key on the 'loan_transactions' table.
    // 'id' is the local key on the 'users' table (default, can be omitted).
    return $this->belongsTo(User::class, 'receiving_officer_id');
  }

  /**
   * Get the returning officer.
   * Defines a many-to-one relationship where a LoanTransaction belongs to one User (the officer who returned).
   * This is typically the applicant or the designated responsible officer.
   * Assumes the 'loan_transactions' table has a 'returning_officer_id' foreign key that
   * references the 'users' table's primary key ('id'). This relationship is nullable.
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\User, \App\Models\LoanTransaction>
   */
  public function returningOfficer(): BelongsTo // Added return type hint
  {
    // Defines a many-to-one relationship with the User model.
    // 'User::class' is the related model.
    // 'returning_officer_id' is the foreign key on the 'loan_transactions' table.
    // 'id' is the local key on the 'users' table (default, can be omitted).
    return $this->belongsTo(User::class, 'returning_officer_id');
  }

  /**
   * Get the return accepting officer.
   * Defines a many-to-one relationship where a LoanTransaction belongs to one User (the officer who accepted the return).
   * This officer is responsible for receiving the equipment back into custody, typically BPM staff.
   * Assumes the 'loan_transactions' table has a 'return_accepting_officer_id' foreign key that
   * references the 'users' table's primary key ('id'). This relationship is nullable.
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\User, \App\Models\LoanTransaction>
   */
  public function returnAcceptingOfficer(): BelongsTo // Added return type hint
  {
    // Defines a many-to-one relationship with the User model.
    // 'User::class' is the related model.
    // 'return_accepting_officer_id' is the foreign key on the 'loan_transactions' table.
    // 'id' is the local key on the 'users' table (default, can be omitted).
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
   * Checks the status column against the STATUS_ISSUED constant.
   *
   * @return bool True if the transaction is issued, false otherwise.
   */
  public function isIssued(): bool // Added return type hint
  {
    return $this->hasStatus(self::STATUS_ISSUED); // Use constant via hasStatus helper
  }

  /**
   * Check if the transaction represents an item that has been returned.
   * Checks the status column against the STATUS_RETURNED constant.
   *
   * @return bool True if the transaction is returned, false otherwise.
   */
  public function isReturned(): bool // Added return type hint
  {
    return $this->hasStatus(self::STATUS_RETURNED); // Use constant via hasStatus helper
  }

  /**
   * Check if the transaction is marked as overdue.
   * Checks the status column against the STATUS_OVERDUE constant.
   *
   * @return bool True if the transaction is overdue, false otherwise.
   */
  public function isOverdue(): bool // Added return type hint
  {
    return $this->hasStatus(self::STATUS_OVERDUE); // Use constant via hasStatus helper
  }

  /**
   * Check if the transaction has both issue and return information recorded.
   * This indicates the physical handover process has been completed in both directions.
   *
   * @return bool True if both issue and return timestamps are present, false otherwise.
   */
  public function isCompletedTransaction(): bool // Added helper method
  {
    return $this->issue_timestamp !== null && $this->return_timestamp !== null;
  }

  // Add custom methods or accessors/mutators here as needed

  /**
   * Accessor to get the translated status string.
   * Useful for displaying user-friendly workflow status in views.
   * Uses a match statement for cleaner translation based on workflow status constants.
   *
   * @return string The human-readable, translated workflow status for the transaction.
   */
  protected function getStatusTranslatedAttribute(): string // Corrected method visibility to protected for accessors, Added return type hint
  {
    return match ($this->status) {
      self::STATUS_ISSUED => __('Issued'),
      self::STATUS_RETURNED => __('Returned'),
      self::STATUS_OVERDUE => __('Overdue'),
      default => $this->status, // Return raw status if unknown
    };
  }
}
