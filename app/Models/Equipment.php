<?php

namespace App\Models; // Ensure the namespace is correct for your project

use App\Traits\CreatedUpdatedDeletedBy; // Assuming this trait exists and adds audit FKs/methods like created_by, updated_by, deleted_by
use Carbon\Carbon; // Assuming Carbon is used implicitly with date/datetime casts and potentially in methods/accessors
use Illuminate\Database\Eloquent\Factories\HasFactory; // Import HasFactory trait
use Illuminate\Database\Eloquent\Model; // Import base Model class
use Illuminate\Database\Eloquent\Relations\BelongsTo; // Import BelongsTo relationship type
use Illuminate\Database\Eloquent\Relations\HasMany; // Import HasMany relationship type
use Illuminate\Database\Eloquent\SoftDeletes; // Import SoftDeletes trait
// Removed unused aliased imports: BelongsToRelation, HasManyRelation
// Removed unused import: use Illuminate\Database\Eloquent\Builder;
// Removed unused import: use Illuminate\Database\Eloquent\Casts\Attribute;


// Import models for relationships (Eloquent needs to know about the related models for relationships)
use App\Models\Employee; // Equipment belongsTo AssignedToEmployee
use App\Models\Department; // Equipment belongsTo Department
use App\Models\Center; // Equipment belongsTo Center
use App\Models\User; // For audit columns (handled by trait CreatedUpdatedDeletedBy)
use App\Models\LoanApplicationItem; // Equipment hasMany LoanApplicationItems
use App\Models\LoanTransaction; // Equipment hasMany LoanTransactions
use App\Models\Transition; // Equipment hasMany Transitions


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
 * @property bool $is_active Indicates if the equipment record is currently active in the system (assuming this column exists).
 * @property int|null $created_by Foreign key to the user who created the record (handled by trait).
 * @property int|null $updated_by Foreign key to the user who last updated the record (handled by trait).
 * @property int|null $deleted_by Foreign key to the user who soft deleted the record (handled by trait).
 * @property \Illuminate\Support\Carbon|null $created_at Automatically managed timestamp for creation.
 * @property \Illuminate\Support\Carbon|null $updated_at Automatically managed timestamp for last update.
 * @property \Illuminate\Support\Carbon|null $deleted_at Timestamp for soft deletion.
 *
 * @property-read \App\Models\Employee|null $assignedToEmployee The employee the equipment is currently assigned to.
 * @property-read \App\Models\Center|null $center The center the equipment belongs to or is located in.
 * @property-read \App\Models\LoanTransaction|null $currentTransaction The current active loan transaction for the equipment (if on loan).
 * @property-read \App\Models\User|null $createdBy The user who created the record (if trait adds this).
 * @property-read \App\Models\User|null $deletedBy The user who soft deleted the record (if trait adds this).
 * @property-read \App\Models\Department|null $department The department the equipment belongs to or is located in.
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\LoanApplicationItem> $loanApplicationItems Loan application items associated with this equipment (many-to-one).
 * @property-read int|null $loan_application_items_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\LoanTransaction> $loanTransactions Loan transactions associated with this equipment (many-to-one).
 * @property-read int|null $loan_transactions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Transition> $transitions Transitions associated with this equipment (many-to-one).
 * @property-read int|null $transitions_count
 * @property-read \App\Models\User|null $updatedBy The user who last updated the record (if trait adds this).
 *
 * @property-read string $nameAndTag The combined name and tag ID for the equipment.
 * @property-read string $availabilityStatusTranslated The human-readable, translated availability status.
 * @property-read string $conditionStatusTranslated The human-readable, translated condition status.
 *
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
  // HasFactory is for model factories.
  // SoftDeletes adds the 'deleted_at' column and scope.
  // CreatedUpdatedDeletedBy is assumed to add 'created_by', 'updated_by', 'deleted_by' columns and relationships.
  use CreatedUpdatedDeletedBy, HasFactory, SoftDeletes;

  /**
   * The table associated with the model.
   *
   * @var string
   */
  // protected $table = 'equipment'; // Explicitly define table name if it's not the plural of the model name


  // Define constants for availability statuses for better code readability and maintainability.
  // These should align with the values used in your workflow logic and database enum.
  public const AVAILABILITY_AVAILABLE = 'available';       // Equipment is ready for use/loan
  public const AVAILABILITY_ON_LOAN = 'on_loan';           // Equipment is currently out on loan
  public const AVAILABILITY_IN_MAINTENANCE = 'in_maintenance'; // Equipment is undergoing maintenance/repair
  public const AVAILABILITY_RETIRED = 'retired';           // Equipment has been retired or disposed of
  // Add other availability statuses as needed based on your system requirements.

  // Define constants for condition statuses for better code readability and maintainability.
  // These should align with the values used in your workflow logic and database enum.
  public const CONDITION_GOOD = 'good';                 // Equipment is in good working order
  public const CONDITION_FAIR = 'fair';                 // Equipment has minor wear but is functional
  public const CONDITION_POOR = 'poor';                 // Equipment is functional but in poor condition
  public const CONDITION_DAMAGED = 'damaged';             // Equipment is damaged and likely non-functional or unsafe
  public const CONDITION_IN_REPAIR = 'in_repair';           // Equipment is currently being repaired
  // Add other condition statuses as needed based on your system requirements.


  /**
   * The attributes that are mass assignable.
   * Allows these attributes to be set using mass assignment (e.g., during create or update).
   * Includes fields from both original 'equipment' and 'assets' tables.
   * 'id' is excluded as it is the primary key.
   *
   * @var array<int, string>
   */
  protected $fillable = [
    // Core equipment details
    'class',             // equipment classification (e.g., IT, Furniture)
    'asset_type',        // more specific type (e.g., Laptop, Chair)
    'serial_number',     // Equipment's serial number (nullable or required)
    'tag_id',            // Equipment's asset tag ID (nullable or required, should be unique)
    'model',             // Equipment model (nullable)
    'manufacturer',      // Equipment manufacturer (nullable)
    'value',             // Equipment's value or price (decimal)
    'purchase_date',     // Date of purchase (date, nullable)
    'warranty_expiry_date', // Warranty expiry date (date, nullable)
    'notes',             // Additional notes about the equipment (text/string, nullable)

    // Acquisition details (potentially merged from 'assets' data)
    // 'old_id', // If you need to keep track of old asset IDs - exclude from fillable unless necessary
    'acquisition_date', // Date of acquisition (date, nullable)
    'acquisition_type', // Type of acquisition (string, nullable)
    'funded_by',         // Source of funding for acquisition (string, nullable)
    // Note: Assuming 'note' from assets is redundant with 'notes' from equipment, using 'notes'.

    // Status and location fields (updated names as per PHPDoc)
    'availability_status', // Equipment's availability status (string/enum)
    'condition_status', // Equipment's condition status (string/enum)
    'location_details', // Detailed location information (string, nullable)

    // Foreign keys for current assignment/location
    'assigned_to_employee_id', // Link to assigned employee (integer, nullable)
    'department_id', // Link to department (integer, nullable)
    'center_id', // Link to center (integer, nullable)

    'is_active', // Boolean flag - include in fillable if it exists and is mass assignable (boolean)

    // The CreatedUpdatedDeletedBy trait is assumed to handle these audit columns:
    // 'created_by',
    // 'updated_by',
    // 'deleted_by',
  ];

  /**
   * The attributes that should be cast.
   * Ensures data types are correct and dates are Carbon instances.
   * Includes casts for dates, decimals, enums (as string), booleans, timestamps, and soft deletes.
   * Explicitly casting all attributes for clarity and type safety.
   *
   * @var array<string, string>
   */
  protected $casts = [
    // Explicitly cast string attributes
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

    'value' => 'decimal:2', // Cast decimal value with 2 precision (adjust precision as needed)

    // Cast date fields to Carbon instances (YYYY-MM-DD)
    'purchase_date' => 'date',
    'warranty_expiry_date' => 'date',
    'acquisition_date' => 'date',

    // Cast status as string (or to PHP Enum class if defined: \App\Enums\AvailabilityStatus::class)
    'availability_status' => 'string',
    // Cast status as string (or to PHP Enum class if defined: \App\Enums\ConditionStatus::class)
    'condition_status' => 'string',

    // Cast FKs to integer (nullable foreign keys will be null if not set)
    'assigned_to_employee_id' => 'integer',
    'department_id' => 'integer',
    'center_id' => 'integer',

    'is_active' => 'boolean', // Cast boolean flag (assuming this column exists)

    // Standard Eloquent timestamps
    'created_at' => 'datetime', // Explicitly cast creation timestamp to Carbon instance
    'updated_at' => 'datetime', // Explicitly cast update timestamp to Carbon instance
    'deleted_at' => 'datetime', // Cast soft delete timestamp to Carbon instance
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
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\User, \App\Models\Equipment>
   */
  // public function createdBy(): BelongsTo;

  /**
   * Get the user who last updated the model.
   * Assumes the 'updated_by' foreign key exists and links to the 'users' table.
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\User, \App\Models\Equipment>
   */
  // public function updatedBy(): BelongsTo;

  /**
   * Get the user who soft deleted the model.
   * Assumes the 'deleted_by' foreign key exists and links to the 'users' table.
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\User, \App\Models\Equipment>
   */
  // public function deletedBy(): BelongsTo;


  // 👉 Relationships

  /**
   * Get the employee the equipment is currently assigned to.
   * Defines a many-to-one relationship where Equipment belongs to one Employee.
   * This relationship is useful for quickly finding the employee who has the equipment.
   * Assumes the 'equipment' table has an 'assigned_to_employee_id' foreign key that
   * references the 'employees' table's primary key ('id'). This relationship is nullable.
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Employee, \App\Models\Equipment>
   */
  public function assignedToEmployee(): BelongsTo // Added return type hint
  {
    // Defines a many-to-one relationship with the Employee model.
    // 'Employee::class' is the related model.
    // 'assigned_to_employee_id' is the foreign key on the 'equipment' table.
    // 'id' is the local key on the 'employees' table (default, can be omitted).
    return $this->belongsTo(Employee::class, 'assigned_to_employee_id');
    // If using the standard foreign key name, this is sufficient: return $this->belongsTo(Employee::class);
  }

  /**
   * Get the department the equipment belongs to or is located in.
   * Defines a many-to-one relationship where Equipment belongs to one Department.
   * This relationship indicates the managing or primary department for the equipment.
   * Assumes the 'equipment' table has a 'department_id' foreign key that
   * references the 'departments' table's primary key ('id'). This relationship is nullable.
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Department, \App\Models\Equipment>
   */
  public function department(): BelongsTo // Added return type hint
  {
    // Defines a many-to-one relationship with the Department model.
    // 'Department::class' is the related model.
    // 'department_id' is the foreign key on the 'equipment' table.
    // 'id' is the local key on the 'departments' table (default, can be omitted).
    return $this->belongsTo(Department::class, 'department_id');
    // If using the standard foreign key name, this is sufficient: return $this->belongsTo(Department::class);
  }

  /**
   * Get the center the equipment belongs to or is located in.
   * Defines a many-to-one relationship where Equipment belongs to one Center.
   * This relationship indicates the managing or primary center for the equipment.
   * Assumes the 'equipment' table has a 'center_id' foreign key that
   * references the 'centers' table's primary key ('id'). This relationship is nullable.
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Center, \App\Models\Equipment>
   */
  public function center(): BelongsTo // Added return type hint
  {
    // Defines a many-to-one relationship with the Center model.
    // 'Center::class' is the related model.
    // 'center_id' is the foreign key on the 'equipment' table.
    // 'id' is the local key on the 'centers' table (default, can be omitted).
    return $this->belongsTo(Center::class, 'center_id');
    // If using the standard foreign key name, this is sufficient: return $this->belongsTo(Center::class);
  }

  /**
   * Get the loan application items associated with this equipment.
   * Defines a one-to-many relationship where Equipment has many LoanApplicationItems.
   * This relationship is useful for tracking which application requests included this specific equipment item.
   * Assumes the 'loan_application_items' table has an 'equipment_id' foreign key that
   * references the 'equipment' table's primary key ('id').
   *
   * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\LoanApplicationItem>
   */
  public function loanApplicationItems(): HasMany // Added return type hint
  {
    // Defines a one-to-many relationship with the LoanApplicationItem model.
    // 'LoanApplicationItem::class' is the related model.
    // 'equipment_id' is the foreign key on the 'loan_application_items' table.
    // 'id' is the local key on the 'equipment' table (default, can be omitted).
    return $this->hasMany(LoanApplicationItem::class, 'equipment_id');
    // If using the standard foreign key name, this is sufficient: return $this->hasMany(LoanApplicationItem::class);
  }


  /**
   * Get the loan transactions associated with this equipment.
   * Defines a one-to-many relationship where Equipment has many LoanTransactions.
   * This relationship is essential for tracking the history of when this equipment
   * was issued and returned in loan processes.
   * Assumes the 'loan_transactions' table has an 'equipment_id' foreign key that
   * references the 'equipment' table's primary key ('id').
   *
   * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\LoanTransaction>
   */
  public function loanTransactions(): HasMany // Added return type hint
  {
    // Defines a one-to-many relationship with the LoanTransaction model.
    // 'LoanTransaction::class' is the related model.
    // 'equipment_id' is the foreign key on the 'loan_transactions' table.
    // 'id' is the local key on the 'equipment' table (default, can be omitted).
    return $this->hasMany(LoanTransaction::class, 'equipment_id');
    // If using the standard foreign key name, this is sufficient: return $this->hasMany(LoanTransaction::class);
  }

  /**
   * Get the transitions associated with this equipment (potentially from merging 'assets' data).
   * Defines a one-to-many relationship where Equipment has many Transitions.
   * This relationship might track movements, changes in status, or assignments over time.
   * Assumes the 'transitions' table has an 'equipment_id' foreign key (which might have
   * been 'asset_id' in an older schema) that references the 'equipment' table's primary key ('id').
   *
   * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\Transition>
   */
  public function transitions(): HasMany // Added return type hint
  {
    // Defines a one-to-many relationship with the Transition model.
    // 'Transition::class' is the related model.
    // 'equipment_id' is the foreign key on the 'transitions' table.
    // 'id' is the local key on the 'equipment' table (default, can be omitted).
    return $this->hasMany(Transition::class, 'equipment_id'); // Explicitly define FK based on likely merge
    // If using the standard foreign key name, this is sufficient: return $this->hasMany(Transition::class);
  }


  // 👉 Helper Methods (Status Checks and Current Status)

  /**
   * Check if the equipment is currently available.
   * Checks the availability_status column against the AVAILABILITY_AVAILABLE constant.
   *
   * @return bool True if the equipment is available, false otherwise.
   */
  public function isAvailable(): bool // Added return type hint
  {
    // Checks the availability_status column against the defined constant
    return $this->availability_status === self::AVAILABILITY_AVAILABLE; // Use constant
  }

  /**
   * Check if the equipment is currently on loan.
   * Checks the availability_status column against the AVAILABILITY_ON_LOAN constant.
   *
   * @return bool True if the equipment is on loan, false otherwise.
   */
  public function isOnLoan(): bool // Added return type hint
  {
    // Checks the availability_status column against the defined constant
    return $this->availability_status === self::AVAILABILITY_ON_LOAN; // Use constant
  }

  /**
   * Check if the equipment is damaged.
   * Checks the condition_status column against the CONDITION_DAMAGED constant.
   *
   * @return bool True if the equipment is damaged, false otherwise.
   */
  public function isDamaged(): bool // Added return type hint
  {
    // Checks the condition_status column against the defined constant
    return $this->condition_status === self::CONDITION_DAMAGED; // Use constant
  }

  /**
   * Get the current active loan transaction for the equipment if it is on loan.
   * An active transaction is typically the latest transaction with a status indicating it's issued.
   * Assumes a 'status' column exists on the LoanTransaction model/table and
   * LoanTransaction::STATUS_ISSUED (or the string literal 'issued') is the status for active loans.
   *
   * @return \App\Models\LoanTransaction|null The active LoanTransaction model or null if the equipment is not on loan or no active transaction found.
   */
  public function currentTransaction(): ?LoanTransaction // Added return type hint
  {
    // Check if the equipment is on loan based on its availability status
    if (!$this->isOnLoan()) {
      return null; // No active transaction if not on loan
    }

    // Find the latest related loan transaction that has the status 'issued'.
    // This assumes the latest 'issued' transaction represents the current loan.
    // Assuming LoanTransaction model has a STATUS_ISSUED constant, otherwise use the string literal 'issued'.
    $issuedStatus = defined('App\\Models\\LoanTransaction::STATUS_ISSUED') ? LoanTransaction::STATUS_ISSUED : 'issued';

    return $this->loanTransactions()
      ->where('status', $issuedStatus)
      ->latest() // Order by created_at or issue_timestamp desc to get the latest
      ->first();
  }


  // 👉 Accessors (for display purposes)

  /**
   * Accessor to get the combined model name and tag ID for the equipment.
   * Useful for displaying a concise identifier for the equipment.
   *
   * @return string The combined name and tag string.
   */
  protected function getNameAndTagAttribute(): string // Corrected method visibility to protected for accessors, Added return type hint
  {
    // Use null coalescing operator (??) for safety if model or tag_id are null
    return trim(($this->model ?? '---') . ' (' . ($this->tag_id ?? '---') . ')');
  }

  /**
   * Accessor to get the translated availability status string.
   * Useful for displaying user-friendly availability status in views.
   * Uses a match statement for cleaner translation based on availability status constants.
   *
   * @return string The human-readable, translated availability status.
   */
  protected function getAvailabilityStatusTranslatedAttribute(): string // Corrected method visibility to protected for accessors, Added return type hint
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
   * Accessor to get the translated condition status string.
   * Useful for displaying user-friendly condition status in views.
   * Uses a match statement for cleaner translation based on condition status constants.
   *
   * @return string The human-readable, translated condition status.
   */
  protected function getConditionStatusTranslatedAttribute(): string // Corrected method visibility to protected for accessors, Added return type hint
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

  // Add any other custom methods or accessors/mutators below this line

  // Example: Accessor to get the value formatted as currency (optional)
  // use Illuminate\Database\Eloquent\Casts\Attribute; // Import Attribute if needed
  // protected function valueFormatted(): Attribute
  // {
  //     return Attribute::make(
  //         get: fn (float $value) => 'RM ' . number_format($value, 2), // Format as RM currency
  //     );
  // }
}
