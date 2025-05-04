<?php

namespace App\Models;

use App\Traits\CreatedUpdatedDeletedBy;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne; // Import HasOne relationship type
use Illuminate\Database\Eloquent\SoftDeletes;

// Import models for relationships
use App\Models\Employee;
use App\Models\Department;
use App\Models\Center;
use App\Models\User;
use App\Models\LoanApplicationItem;
use App\Models\LoanTransaction;
use App\Models\Transition;
use App\Models\Position; // Import Position model


/**
 * App\Models\Equipment
 *
 * Represents an equipment item within the ICT equipment management system.
 * Stores details about the equipment, its status (availability and condition),
 * location, acquisition details, and relationships to employees, departments,
 * centers, and various loan/transition records. Includes audit trails and soft deletion.
 * Note: This model is designed to hold all relevant data, potentially merging
 * information that might have originated from separate 'equipment' and 'assets' tables.
 *
 * @property int $id
 * @property string|null $class Equipment classification (e.g., IT, Furniture, Vehicle).
 * @property string|null $asset_type More specific equipment type (e.g., Laptop, Projector, Chair, Sedan).
 * @property string|null $serial_number Equipment's unique serial number.
 * @property string|null $tag_id Equipment's unique asset tag ID assigned by the organization.
 * @property string|null $model Equipment model name or number.
 * @property string|null $manufacturer Equipment manufacturer's name.
 * @property float $value Equipment's current value or purchase price (stored as decimal).
 * @property \Illuminate\Support\Carbon|null $purchase_date Date the equipment was purchased.
 * @property \Illuminate\Support\Carbon|null $warranty_expiry_date Date the equipment's warranty expires.
 * @property string|null $notes Additional notes or remarks about the equipment.
 * @property \Illuminate\Support\Carbon|null $acquisition_date Date the equipment was acquired.
 * @property string|null $acquisition_type Type of acquisition (e.g., Purchase, Donation, Transfer).
 * @property string|null $funded_by Source of funding for the acquisition (e.g., Grant, Operating Budget).
 * @property string $availability_status Equipment's current availability status (e.g., available, on_loan, in_maintenance, retired).
 * @property string $condition_status Equipment's current condition status (e.g., good, fair, poor, damaged, in_repair).
 * @property string|null $location_details Detailed location information (e.g., "Room 101", "Storage Warehouse").
 * @property int|null $assigned_to_employee_id Foreign key to the employee the equipment is currently assigned to (nullable).
 * @property int|null $department_id Foreign key to the department the equipment belongs to or is permanently assigned to (nullable).
 * @property int|null $center_id Foreign key to the center the equipment belongs to or is permanently located at (nullable).
 * @property int|null $position_id Foreign key to the position associated with the equipment (nullable). // Added position_id to docblock
 * @property bool $is_active Indicates if the equipment record is currently active in the system (assuming this column exists).
 * @property int|null $created_by Foreign key to the user who created the record (handled by trait).
 * @property int|null $updated_by Foreign key to the user who last updated the record (handled by trait).
 * @property int|null $deleted_by Foreign key to the user who soft deleted the record (handled by trait).
 * @property \Illuminate\Support\Carbon|null $created_at Automatically managed timestamp for creation.
 * @property \Illuminate\Support\Carbon|null $updated_at Automatically managed timestamp for last update.
 * @property \Illuminate\Support\Carbon|null $deleted_at Timestamp for soft deletion.
 *
 * @property-read \App\Models\LoanTransaction|null $activeLoanTransaction The currently active loan transaction for the equipment (needed for eager loading in controller).
 * @property-read \App\Models\Employee|null $assignedToEmployee The employee the equipment is currently assigned to.
 * @property-read \App\Models\Center|null $center The center the equipment belongs to or is located in.
 * @property-read \App\Models\LoanTransaction|null $currentTransaction The current active loan transaction for the equipment (if on loan - helper method).
 * @property-read \App\Models\User|null $createdBy The user who created the record (if trait adds this).
 * @property-read \App\Models\User|null $deletedBy The user who soft deleted the record (if trait adds this).
 * @property-read \App\Models\Department|null $department The department the equipment belongs to or is located in.
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\LoanApplicationItem> $loanApplicationItems Loan application items associated with this equipment (many-to-one).
 * @property-read int|null $loan_application_items_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\LoanTransaction> $loanTransactions Loan transactions associated with this equipment (many-to-one).
 * @property-read int|null $loan_transactions_count
 * @property-read \App\Models\Position|null $position The position associated with the equipment. // Added to docblock
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Transition> $transitions Transitions associated with this equipment (many-to-one).
 * @property-read int|null $transitions_count
 * @property-read \App\Models\User|null $updatedBy The user who last updated the record (if trait adds this).
 *
 * @property-read string $nameAndTag The combined name and tag ID for the equipment.
 * @property-read string $availabilityStatusTranslated The human-readable, translated availability status.
 * @property-read string $conditionStatusTranslated The human-readable, translated condition status.
 *
 * @property-read string $name_and_tag
 * @property-read string $availability_status_translated
 * @property-read string $condition_status_translated
 * @property string|null $old_id
 * @property string|null $brand
 * @property string|null $description
 * @property int $in_service
 * @property int $is_gpr
 * @property int|null $real_price
 * @property int|null $expected_price
 * @property string|null $current_location
 * @method static \Database\Factories\EquipmentFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Equipment whereBrand($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Equipment whereCurrentLocation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Equipment whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Equipment whereExpectedPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Equipment whereInService($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Equipment whereIsGpr($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Equipment whereOldId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Equipment whereRealPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Equipment wherePositionId($value) // Added wherePositionId to docblock
 * @mixin \Eloquent
 */
class Equipment extends Model
{
  // Use the traits for factory, soft deletes, and audit columns
  use CreatedUpdatedDeletedBy, HasFactory, SoftDeletes;

  /**
   * The table associated with the model.
   *
   * @var string
   */
  // protected $table = 'equipment';


  // Define constants for availability statuses
  public const AVAILABILITY_AVAILABLE = 'available';
  public const AVAILABILITY_ON_LOAN = 'on_loan';
  public const AVAILABILITY_UNDER_MAINTENANCE = 'under_maintenance';
  public const AVAILABILITY_DISPOSED = 'disposed';
  public const AVAILABILITY_LOST = 'lost';
  public const AVAILABILITY_DAMAGED = 'damaged';


  // Define constants for condition statuses
  public const CONDITION_GOOD = 'Good';
  public const CONDITION_FINE = 'Fine';
  public const CONDITION_BAD = 'Bad';
  public const CONDITION_DAMAGED = 'Damaged';


  // Define static properties for consistent access to types and statuses
  public static array $equipmentTypes = ['laptop', 'projector', 'printer', 'monitor', 'keyboard', 'mouse', 'webcam', 'other'];

  public static array $availabilityStatuses = [
    self::AVAILABILITY_AVAILABLE,
    self::AVAILABILITY_ON_LOAN,
    self::AVAILABILITY_UNDER_MAINTENANCE,
    self::AVAILABILITY_DISPOSED,
    self::AVAILABILITY_LOST,
    self::AVAILABILITY_DAMAGED,
  ];

  public static array $conditionStatuses = [
    self::CONDITION_GOOD,
    self::CONDITION_FINE,
    self::CONDITION_BAD,
    self::CONDITION_DAMAGED,
  ];


  /**
   * The attributes that are mass assignable.
   *
   * @var array<int, string>
   */
  protected $fillable = [
    'class',
    'asset_type',
    'serial_number',
    'tag_id',
    'model',
    'manufacturer',
    'value',
    'purchase_date',
    'warranty_expiry_date',
    'notes',

    'old_id',
    'acquisition_date',
    'acquisition_type',
    'funded_by',

    'availability_status',
    'condition_status',
    'location_details',

    'assigned_to_employee_id',
    'department_id',
    'center_id',
    'position_id', // Added position_id to fillable
    'is_active',
    'in_service',
    'is_gpr',
    'real_price',
    'expected_price',
    'current_location',
    'brand',
    'description',
  ];

  /**
   * The attributes that should be cast.
   *
   * @var array<string, string>
   */
  protected $casts = [
    'class' => 'string',
    'asset_type' => 'string',
    'serial_number' => 'string',
    'tag_id' => 'string',
    'model' => 'string',
    'manufacturer' => 'string',
    'acquisition_type' => 'string',
    'funded_by' => 'string',
    'notes' => 'string',
    'location_details' => 'string',
    'brand' => 'string',
    'description' => 'string',
    'old_id' => 'string',
    'current_location' => 'string',

    'value' => 'decimal:2',
    'real_price' => 'decimal:2',
    'expected_price' => 'decimal:2',

    'purchase_date' => 'date',
    'warranty_expiry_date' => 'date',
    'acquisition_date' => 'date',

    'availability_status' => 'string',
    'condition_status' => 'string',

    'assigned_to_employee_id' => 'integer',
    'department_id' => 'integer',
    'center_id' => 'integer',
    'position_id' => 'integer', // Added cast for position_id

    'is_active' => 'boolean',
    'in_service' => 'boolean',
    'is_gpr' => 'boolean',

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
   * Get the position associated with the equipment.
   * Defines a many-to-one relationship where Equipment belongs to one Position.
   * Assumes the 'equipment' table has a 'position_id' foreign key.
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Position>
   */
  public function position(): BelongsTo // Added the missing relationship method
  {
    // Link to the Position model using the 'position_id' foreign key (adjust if needed)
    return $this->belongsTo(Position::class, 'position_id');
    // If your foreign key column is not the default 'position_id', specify it.
    // return $this->belongsTo(Position::class, 'your_foreign_key_name');
  }


  /**
   * Get the employee the equipment is currently assigned to.
   * Defines a many-to-one relationship where Equipment belongs to one Employee.
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Employee>
   */
  public function assignedToEmployee(): BelongsTo
  {
    return $this->belongsTo(Employee::class, 'assigned_to_employee_id');
  }

  /**
   * Get the department the equipment belongs to or is located in.
   * Defines a many-to-one relationship where Equipment belongs to one Department.
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Department>
   */
  public function department(): BelongsTo
  {
    return $this->belongsTo(Department::class, 'department_id');
  }

  /**
   * Get the center the equipment belongs to or is located in.
   * Defines a many-to-one relationship where Equipment belongs to one Center.
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Center>
   */
  public function center(): BelongsTo
  {
    return $this->belongsTo(Center::class, 'center_id');
  }

  /**
   * Get the loan application items associated with this equipment.
   * Defines a one-to-many relationship where Equipment has many LoanApplicationItems.
   *
   * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\LoanApplicationItem>
   */
  public function loanApplicationItems(): HasMany
  {
    return $this->hasMany(LoanApplicationItem::class, 'equipment_id');
  }


  /**
   * Get the loan transactions associated with this equipment.
   * Defines a one-to-many relationship where Equipment has many LoanTransactions.
   *
   * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\LoanTransaction>
   */
  public function loanTransactions(): HasMany
  {
    return $this->hasMany(LoanTransaction::class, 'equipment_id');
  }

  /**
   * Get the transitions associated with this equipment.
   * Defines a one-to-many relationship where Equipment has many Transitions.
   *
   * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\Transition>
   */
  public function transitions(): HasMany
  {
    return $this->hasMany(Transition::class, 'equipment_id');
  }

  /**
   * Get the currently active loan transaction for the equipment.
   * Defines a one-to-one relationship (HasOne) to find the single related LoanTransaction record
   * that has been issued and not yet returned.
   * This relationship is required for eager loading in the controller.
   *
   * @return \Illuminate\Database\Eloquent\Relations\HasOne<\App\Models\LoanTransaction>
   */
  public function activeLoanTransaction(): HasOne
  {
    // Find the one LoanTransaction related to this equipment
    return $this->hasOne(LoanTransaction::class)
      // Add conditions to define what an "active" loan is
      ->whereNotNull('issue_timestamp') // The equipment has been issued
      ->whereNull('return_timestamp'); // The equipment has not been returned yet
    // You might want to add ->latest() here if multiple issued transactions could exist
    // without return timestamps (though that would indicate data issues).
  }


  // 👉 Helper Methods (Status Checks and Current Status)

  /**
   * Check if the equipment is currently available.
   * Checks the availability_status column against the AVAILABILITY_AVAILABLE constant.
   *
   * @return bool True if the equipment is available, false otherwise.
   */
  public function isAvailable(): bool
  {
    return $this->availability_status === self::AVAILABILITY_AVAILABLE;
  }

  /**
   * Check if the equipment is currently on loan.
   * Checks the availability_status column against the AVAILABILITY_ON_LOAN constant.
   *
   * @return bool True if the equipment is on loan, false otherwise.
   */
  public function isOnLoan(): bool
  {
    return $this->availability_status === self::AVAILABILITY_ON_LOAN;
  }

  /**
   * Check if the equipment is damaged.
   * Checks the condition_status column against the CONDITION_DAMAGED constant.
   *
   * @return bool True if the equipment is damaged, false otherwise.
   */
  public function isDamaged(): bool
  {
    return $this->condition_status === self::CONDITION_DAMAGED;
  }

  /**
   * Get the current active loan transaction for the equipment if it is on loan.
   * This helper method queries the loanTransactions relationship.
   *
   * @return \App\Models\LoanTransaction|null The active LoanTransaction model or null if the equipment is not on loan or no active transaction found.
   */
  public function currentTransaction(): ?LoanTransaction
  {
    if (!$this->isOnLoan()) {
      return null;
    }

    $issuedStatus = defined('App\\Models\\LoanTransaction::STATUS_ISSUED') ? LoanTransaction::STATUS_ISSUED : 'issued';

    return $this->loanTransactions()
      ->where('status', $issuedStatus)
      ->latest('issue_timestamp') // Order by issue_timestamp desc to get the latest issued
      ->first();
  }


  // 👉 Accessors (for display purposes)

  /**
   * Accessor to get the combined model name and tag ID for the equipment.
   * Useful for displaying a concise identifier for the equipment.
   *
   * @return string The combined name and tag string.
   */
  protected function getNameAndTagAttribute(): string
  {
    return trim(($this->model ?? '---') . ' (' . ($this->tag_id ?? '---') . ')');
  }

  /**
   * Accessor to get the translated availability status string.
   * Useful for displaying user-friendly availability status in views.
   * Uses a match statement for cleaner translation.
   *
   * @return string The human-readable, translated availability status.
   */
  protected function getAvailabilityStatusTranslatedAttribute(): string
  {
    return match ($this->availability_status) {
      self::AVAILABILITY_AVAILABLE => __('Available'),
      self::AVAILABILITY_ON_LOAN => __('On Loan'),
      self::AVAILABILITY_UNDER_MAINTENANCE => __('Under Maintenance'),
      self::AVAILABILITY_DISPOSED => __('Disposed'),
      self::AVAILABILITY_LOST => __('Lost'),
      self::AVAILABILITY_DAMAGED => __('Damaged'),
      default => $this->availability_status,
    };
  }

  /**
   * Accessor to get the translated condition status string.
   * Useful for displaying user-friendly condition status in views.
   * Uses a match statement for cleaner translation.
   *
   * @return string The human-readable, translated condition status.
   */
  protected function getConditionStatusTranslatedAttribute(): string // Completed the accessor method
  {
    return match ($this->condition_status) {
      self::CONDITION_GOOD => __('Good'),
      self::CONDITION_FINE => __('Fine'),
      self::CONDITION_BAD => __('Bad'),
      self::CONDITION_DAMAGED => __('Damaged'),
      default => $this->condition_status, // Return raw status if unknown
    };
  }
} // Closed the class definition
