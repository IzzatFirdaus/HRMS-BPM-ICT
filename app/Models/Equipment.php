<?php

namespace App\Models;

use App\Traits\CreatedUpdatedDeletedBy; // Assuming this trait exists and adds audit FKs/methods
use Carbon\Carbon; // Assuming Carbon is used implicitly with date casts
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo; // Use BelongsTo trait
use Illuminate\Database\Eloquent\Relations\HasMany; // Use HasMany trait
use Illuminate\Database\Eloquent\SoftDeletes; // Use SoftDeletes trait
use Illuminate\Database\Eloquent\Relations\BelongsTo as BelongsToRelation; // Alias if needed
use Illuminate\Database\Eloquent\Relations\HasMany as HasManyRelation; // Alias if needed

// Import models for relationships
use App\Models\Employee; // For assigned_to_employee_id
use App\Models\Department; // For department_id
use App\Models\Center; // For center_id
use App\Models\User; // For audit columns (handled by trait)
use App\Models\LoanApplicationItem; // Equipment in item requests
use App\Models\LoanTransaction; // Equipment in loan transactions
use App\Models\Transition; // Equipment in transitions


/**
 * App\Models\Equipment
 *
 * Represents an equipment item, merging data that might have come from separate 'equipment' and 'assets' tables.
 * Includes details about the equipment itself, its status, location, and relationships to loan/transition records.
 *
 * @property int $id
 * @property string|null $class equipment classification (e.g., IT, Furniture).
 * @property string|null $asset_type more specific type (e.g., Laptop, Chair).
 * @property string|null $serial_number Equipment's serial number.
 * @property string|null $tag_id Equipment's asset tag ID.
 * @property string|null $model Equipment model.
 * @property string|null $manufacturer Equipment manufacturer.
 * @property float $value Equipment's value or price.
 * @property \Illuminate\Support\Carbon|null $purchase_date Date of purchase.
 * @property \Illuminate\Support\Carbon|null $warranty_expiry_date Warranty expiry date.
 * @property string|null $notes Additional notes about the equipment.
 * @property \Illuminate\Support\Carbon|null $acquisition_date Date of acquisition.
 * @property string|null $acquisition_type Type of acquisition.
 * @property string|null $funded_by Source of funding for acquisition.
 * @property string $availability_status Equipment's availability status (e.g., available, on_loan, maintenance, retired).
 * @property string $condition_status Equipment's condition status (e.g., good, fair, poor, damaged, in_repair).
 * @property string|null $location_details Detailed location information.
 * @property int|null $assigned_to_employee_id Foreign key to the employee the equipment is currently assigned to.
 * @property int|null $department_id Foreign key to the department the equipment belongs to or is located in.
 * @property int|null $center_id Foreign key to the center the equipment belongs to or is located in.
 * @property bool $is_active Indicates if the equipment record is active (assuming this column exists).
 * @property int|null $created_by Foreign key to the user who created the record.
 * @property int|null $updated_by Foreign key to the user who last updated the record.
 * @property int|null $deleted_by Foreign key to the user who soft deleted the record.
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\Employee|null $assignedToEmployee The employee the equipment is currently assigned to.
 * @property-read \App\Models\Center|null $center The center the equipment belongs to or is located in.
 * @property-read \App\Models\LoanTransaction|null $currentTransaction The current active loan transaction for the equipment.
 * @property-read \App\Models\User|null $createdBy The user who created the record.
 * @property-read \App\Models\User|null $deletedBy The user who soft deleted the record.
 * @property-read \App\Models\Department|null $department The department the equipment belongs to or is located in.
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\LoanApplicationItem> $loanApplicationItems Loan application items associated with this equipment.
 * @property-read int|null $loan_application_items_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\LoanTransaction> $loanTransactions Loan transactions associated with this equipment.
 * @property-read int|null $loan_transactions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Transition> $transitions Transitions associated with this equipment.
 * @property-read int|null $transitions_count
 * @property-read \App\Models\User|null $updatedBy The user who last updated the record.
 * @method static \Illuminate\Database\Eloquent\Builder|Equipment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Equipment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Equipment onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Equipment query()
 * @method static \Illuminate\Database\Eloquent\Builder|Equipment whereAcquisitionDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Equipment whereAcquisitionType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Equipment whereAssignedToEmployeeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Equipment whereAvailabilityStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Equipment whereCenterId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Equipment whereClass($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Equipment whereConditionStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Equipment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Equipment whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Equipment whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Equipment whereDeletedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Equipment whereDepartmentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Equipment whereFundedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Equipment whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Equipment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Equipment whereLocationDetails($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Equipment whereManufacturer($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Equipment whereModel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Equipment whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Equipment wherePurchaseDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Equipment whereSerialNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Equipment whereTagId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Equipment whereAssetType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Equipment whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Equipment whereUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Equipment whereValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Equipment whereWarrantyExpiryDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Equipment withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Equipment withoutTrashed()
 * @mixin \Eloquent
 */
class Equipment extends Model // This model now represents the merged 'equipment' table
{
  // Use the traits for factory, soft deletes, and audit columns
  use CreatedUpdatedDeletedBy, HasFactory, SoftDeletes;

  // Define constants for availability statuses for better code readability and maintainability
  public const AVAILABILITY_AVAILABLE = 'available';
  public const AVAILABILITY_ON_LOAN = 'on_loan';
  public const AVAILABILITY_IN_MAINTENANCE = 'in_maintenance'; // Example additional status
  public const AVAILABILITY_RETIRED = 'retired'; // Example additional status
  // Add other availability statuses as needed

  // Define constants for condition statuses
  public const CONDITION_GOOD = 'good'; // Example status
  public const CONDITION_FAIR = 'fair'; // Example status
  public const CONDITION_POOR = 'poor'; // Example status
  public const CONDITION_DAMAGED = 'damaged'; // Explicitly from original code
  public const CONDITION_IN_REPAIR = 'in_repair'; // Example additional status
  // Add other condition statuses as needed


  /**
   * The attributes that are mass assignable.
   * Includes fields from both original 'equipment' and 'assets' tables.
   * 'id' is excluded as it is the primary key.
   *
   * @var array<int, string>
   */
  protected $fillable = [
    // Fields from original 'equipment' migration
    'class', // equipment classification (e.g., IT, Furniture)
    'asset_type', // more specific type (e.g., Laptop, Chair)
    'serial_number',
    'tag_id',
    'model',
    'manufacturer', // manufacturer
    'value', // value/price
    'purchase_date', // From original equipment

    'warranty_expiry_date', // From original equipment
    'notes', // From original equipment

    // Fields merged from 'assets' migration
    // 'old_id', // If you need to keep track of old asset IDs - exclude from fillable unless necessary
    'acquisition_date', // From assets migration
    'acquisition_type', // From assets migration
    'funded_by', // From assets migration
    // Note: Assuming 'note' from assets is redundant with 'notes' from equipment, using 'notes'.

    // Status and location fields (updated names)
    'availability_status', // replaces original 'status'
    'condition_status', // new condition status
    'location_details', // replaces original 'current_location' or similar

    // Foreign keys
    'assigned_to_employee_id', // Link to assigned employee
    'department_id', // Link to department
    'center_id', // Link to center

    'is_active', // Boolean flag - include in fillable if it exists and is mass assignable

    // 'created_by', // Handled by trait
    // 'updated_by', // Handled by trait
    // 'deleted_by', // Handled by trait
  ];

  /**
   * The attributes that should be cast.
   * Ensures data types are correct and dates are Carbon instances.
   * Includes casts for dates, decimals, enums (as string), booleans, timestamps, and soft deletes.
   * Explicitly casting all attributes for clarity.
   *
   * @var array<string, string>
   */
  protected $casts = [
    'class' => 'string', // Explicitly cast string attributes
    'asset_type' => 'string',
    'serial_number' => 'string',
    'tag_id' => 'string',
    'model' => 'string',
    'manufacturer' => 'string',
    'acquisition_type' => 'string',
    'funded_by' => 'string',
    'notes' => 'string',
    'location_details' => 'string',

    'value' => 'decimal:2', // Cast decimal value with 2 precision

    'purchase_date' => 'date', // Cast date fields to Carbon instances (YYYY-MM-DD)
    'warranty_expiry_date' => 'date',
    'acquisition_date' => 'date',

    'availability_status' => 'string', // Cast status as string (or to AvailabilityStatus::class if using PHP Enums)
    'condition_status' => 'string', // Cast status as string (or to ConditionStatus::class if using PHP Enums)

    'assigned_to_employee_id' => 'integer', // Cast FKs to integer
    'department_id' => 'integer',
    'center_id' => 'integer',

    'is_active' => 'boolean', // Cast boolean flag (assuming this column exists)

    'created_at' => 'datetime', // Explicitly cast timestamps
    'updated_at' => 'datetime',
    'deleted_at' => 'datetime', // Cast soft delete timestamp
  ];


  // The CreatedUpdatedDeletedBy trait is assumed to add these audit relationships:

  /**
   * Get the user who created the model.
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\User, \App\Models\Equipment>
   */
  // public function createdBy(): BelongsToRelation;

  /**
   * Get the user who last updated the model.
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\User, \App\Models\Equipment>
   */
  // public function updatedBy(): BelongsToRelation;

  /**
   * Get the user who soft deleted the model.
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\User, \App\Models\Equipment>
   */
  // public function deletedBy(): BelongsToRelation;


  // 👉 Relationships

  /**
   * Get the employee the equipment is currently assigned to.
   * Defines a many-to-one relationship where Equipment belongs to one Employee.
   * Assumes the 'equipment' table has an 'assigned_to_employee_id' foreign key.
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Employee, \App\Models\Equipment>
   */
  public function assignedToEmployee(): BelongsTo // Added return type hint
  {
    // Defines a many-to-one relationship with the Employee model
    return $this->belongsTo(Employee::class, 'assigned_to_employee_id'); // Explicitly define FK
  }

  /**
   * Get the department the equipment belongs to or is located in.
   * Defines a many-to-one relationship where Equipment belongs to one Department.
   * Assumes the 'equipment' table has a 'department_id' foreign key.
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Department, \App\Models\Equipment>
   */
  public function department(): BelongsTo // Added return type hint
  {
    // Defines a many-to-one relationship with the Department model
    return $this->belongsTo(Department::class, 'department_id'); // Explicitly define FK
  }

  /**
   * Get the center the equipment belongs to or is located in.
   * Defines a many-to-one relationship where Equipment belongs to one Center.
   * Assumes the 'equipment' table has a 'center_id' foreign key.
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Center, \App\Models\Equipment>
   */
  public function center(): BelongsTo // Added return type hint
  {
    // Defines a many-to-one relationship with the Center model
    return $this->belongsTo(Center::class, 'center_id'); // Explicitly define FK
  }

  /**
   * Get the loan application items associated with this equipment.
   * Defines a one-to-many relationship where Equipment has many LoanApplicationItems.
   * Assumes loan_application_items table has equipment_id FK.
   *
   * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\LoanApplicationItem>
   */
  public function loanApplicationItems(): HasMany // Added return type hint
  {
    // Defines a one-to-many relationship with the LoanApplicationItem model
    return $this->hasMany(LoanApplicationItem::class, 'equipment_id'); // Explicitly define FK
  }


  /**
   * Get the loan transactions associated with this equipment.
   * Defines a one-to-many relationship where Equipment has many LoanTransactions.
   * Assumes loan_transactions table has equipment_id FK.
   *
   * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\LoanTransaction>
   */
  public function loanTransactions(): HasMany // Added return type hint
  {
    // Defines a one-to-many relationship with the LoanTransaction model
    return $this->hasMany(LoanTransaction::class, 'equipment_id'); // Explicitly define FK
  }

  /**
   * Get the transitions associated with this equipment (from assets table).
   * Defines a one-to-many relationship where Equipment has many Transitions.
   * Assumes transitions table has equipment_id FK (was asset_id).
   *
   * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\Transition>
   */
  public function transitions(): HasMany // Added return type hint
  {
    // Defines a one-to-many relationship with the Transition model
    return $this->hasMany(Transition::class, 'equipment_id'); // Explicitly define FK
  }


  // 👉 Helper Methods (Status Checks)

  /**
   * Check if the equipment is currently available.
   * Checks the availability_status column against the AVAILABILITY_AVAILABLE constant.
   *
   * @return bool
   */
  public function isAvailable(): bool // Added return type hint
  {
    // Checks the new availability_status column
    return $this->availability_status === self::AVAILABILITY_AVAILABLE; // Use constant
  }

  /**
   * Check if the equipment is currently on loan.
   * Checks the availability_status column against the AVAILABILITY_ON_LOAN constant.
   *
   * @return bool
   */
  public function isOnLoan(): bool // Added return type hint
  {
    // Checks the new availability_status column
    return $this->availability_status === self::AVAILABILITY_ON_LOAN; // Use constant
  }

  /**
   * Check if the equipment is damaged.
   * Checks the condition_status column against the CONDITION_DAMAGED constant.
   *
   * @return bool
   */
  public function isDamaged(): bool // Added return type hint
  {
    // Checks the new condition_status column
    return $this->condition_status === self::CONDITION_DAMAGED; // Use constant
  }

  /**
   * Get the current loan transaction if the equipment is on loan.
   * Assumes a 'status' column exists on the LoanTransaction model/table
   * and LoanTransaction::STATUS_ISSUED (or 'issued') is the status for active loans.
   *
   * @return \App\Models\LoanTransaction|null The active LoanTransaction model or null if not on loan or no active transaction found.
   */
  public function currentTransaction(): ?LoanTransaction // Added return type hint
  {
    // Find the latest related loan transaction that has the status 'issued'
    // Assuming LoanTransaction model has a STATUS_ISSUED constant, otherwise use the string literal 'issued'.
    return $this->loanTransactions()->where('status', LoanTransaction::STATUS_ISSUED ?? 'issued')->latest()->first();
  }


  // Add custom methods or accessors/mutators here as needed

  /**
   * Get the combined name and tag ID for the equipment.
   *
   * @return string
   */
  public function getNameAndTagAttribute(): string // Added accessor
  {
    return trim(($this->model ?? '---') . ' (' . ($this->tag_id ?? '---') . ')');
  }

  /**
   * Get the translated availability status string.
   *
   * @return string
   */
  public function getAvailabilityStatusTranslatedAttribute(): string // Added accessor for translated status
  {
    return match ($this->availability_status) {
      self::AVAILABILITY_AVAILABLE => __('Available'),
      self::AVAILABILITY_ON_LOAN => __('On Loan'),
      self::AVAILABILITY_IN_MAINTENANCE => __('In Maintenance'),
      self::AVAILABILITY_RETIRED => __('Retired'),
      default => $this->availability_status, // Return raw status if unknown
    };
  }

  /**
   * Get the translated condition status string.
   *
   * @return string
   */
  public function getConditionStatusTranslatedAttribute(): string // Added accessor for translated status
  {
    return match ($this->condition_status) {
      self::CONDITION_GOOD => __('Good'),
      self::CONDITION_FAIR => __('Fair'),
      self::CONDITION_POOR => __('Poor'),
      self::CONDITION_DAMAGED => __('Damaged'),
      self::CONDITION_IN_REPAIR => __('In Repair'),
      default => $this->condition_status, // Return raw status if unknown
    };
  }
}
