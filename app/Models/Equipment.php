<?php

namespace App\Models;

use App\Traits\CreatedUpdatedDeletedBy;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

// Import models for relationships - Ensure these are present and correct paths
use App\Models\EquipmentCategory; // *** THIS IMPORT IS NEEDED ***
use App\Models\Location; // *** THIS IMPORT IS NEEDED ***
use App\Models\LoanTransaction;
use App\Models\LoanApplicationItem; // Needed if used elsewhere, keep for now
use App\Models\User;
// Keep other imports if they are used elsewhere in the model based on your project setup
use App\Models\Employee;
use App\Models\Department;
use App\Models\Center;
use App\Models\Position;
use App\Models\Transition;

// Ensure Collection is imported if used in method type hints (e.g., for $transactions)
use Illuminate\Database\Eloquent\Collection;


/**
 * App\Models\Equipment
 *
 * Represents a specific physical piece of ICT equipment in the inventory.
 * Linked to a category, location, and involved in loan transactions.
 * Includes audit trails and soft deletion.
 * Follows schema details from System Design Document.
 *
 * @property int $id
 * @property string|null $class Equipment classification (e.g., IT, Furniture, Vehicle).
 * @property string $asset_tag Unique identifier for the physical asset (matches tag_id in design).
 * @property string $asset_type Equipment type (e.g., Laptop, Projector, Printer).
 * @property string|null $serial_number Serial number.
 * @property string|null $name Display name or model.
 * @property string|null $brand Brand.
 * @property string|null $model Model.
 * @property string|null $specification Technical specifications.
 * @property int $equipment_category_id Foreign key to the equipment category.
 * @property string $availability_status // e.g., 'Available', 'On Loan', 'Under Maintenance', 'Disposed', 'Lost', 'Damaged'
 * @property string $condition_status // e.g., 'Good', 'Fine', 'Bad', 'Damaged', 'Maintenance Needed'
 * @property int|null $location_id Foreign key to the current location.
 * @property Carbon|null $purchase_date Purchase date.
 * @property string|null $purchase_price Purchase price.
 * @property string|null $warranty_expiry_date Warranty expiry date.
 * @property string|null $notes Any additional notes.
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property int|null $deleted_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read EquipmentCategory $category The equipment category the equipment belongs to.
 * @property-read Location|null $location The current location of the equipment.
 * @property-read \Illuminate\Database\Eloquent\Collection<int, LoanTransaction> $transactions The loan transactions this equipment has been involved in.
 * @property-read LoanTransaction|null $currentTransaction The currently active loan transaction for this equipment.
 * @property-read User|null $creator
 * @property-read User|null $editor
 * @property-read User|null $deleter
 * @method static \Illuminate\Database\Eloquent\Builder|Equipment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Equipment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Equipment onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Equipment query()
 * @method static \Illuminate\Database\Eloquent\Builder|Equipment withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Equipment withoutTrashed()
 * @method static \Database\Factories\EquipmentFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder|Equipment whereAvailabilityStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Equipment whereConditionStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Equipment available()
 * @method static \Illuminate\Database\Eloquent\Builder|Equipment onLoan()
 * @method static \Illuminate\Database\Eloquent\Builder|Equipment underMaintenance()
 * @method static \Illuminate\Database\Eloquent\Builder|Equipment disposed()
 * @method static \Illuminate\Database\Eloquent\Builder|Equipment lost()
 * @method static \Illuminate\Database\Eloquent\Builder|Equipment damaged()
 * @method static \Illuminate\Database\Eloquent\Builder|Equipment withCurrentLoanDetails()
 * @mixin \Eloquent
 */
class Equipment extends Model
{
  use HasFactory, SoftDeletes, CreatedUpdatedDeletedBy;

  // --- Status Constants for availability_status ---
  public const AVAILABILITY_AVAILABLE = 'Available';
  public const AVAILABILITY_ON_LOAN = 'On Loan';
  public const AVAILABILITY_UNDER_MAINTENANCE = 'Under Maintenance';
  public const AVAILABILITY_DISPOSED = 'Disposed';
  public const AVAILABILITY_LOST = 'Lost';
  public const AVAILABILITY_DAMAGED = 'Damaged';
  // --- End Availability Status Constants ---

  // --- Status Constants for condition_status ---
  public const CONDITION_GOOD = 'Good';
  public const CONDITION_FINE = 'Fine';
  public const CONDITION_BAD = 'Bad';
  public const CONDITION_DAMAGED = 'Damaged';
  public const CONDITION_MAINTENANCE_NEEDED = 'Maintenance Needed';
  // --- End Condition Status Constants ---


  protected $fillable = [
    'asset_tag',
    'equipment_category_id',
    'name',
    'serial_number',
    'description',
    'availability_status',
    'condition_status',
    'location_id',
    'purchase_date',
    'purchase_price',
    'warranty_expiry_date',
    'notes',
    'class',
    'asset_type',
    'brand',
    'model',
    'specification',
  ];

  protected $casts = [
    'equipment_category_id' => 'integer',
    'location_id' => 'integer',
    'purchase_date' => 'date',
    'warranty_expiry_date' => 'date',
    'created_at' => 'datetime',
    'updated_at' => 'datetime',
    'deleted_at' => 'datetime',
  ];

  // --- Relationships ---

  /**
   * Get the category that the equipment belongs to.
   * Matches System Design Document. Requires EquipmentCategory model and import.
   */
  public function category(): BelongsTo
  {
    return $this->belongsTo(EquipmentCategory::class, 'equipment_category_id');
  }

  /**
   * Get the location of the equipment.
   * Matches System Design Document. Requires Location model and import.
   */
  public function location(): BelongsTo
  {
    // The design uses 'current_location', assuming 'location_id' is the FK.
    return $this->belongsTo(Location::class, 'location_id');
  }

  /**
   * Get the loan transactions associated with this equipment.
   * Matches System Design Document. Requires LoanTransaction model and import.
   */
  public function transactions(): HasMany
  {
    // Assumes LoanTransaction model has 'equipment_id' foreign key.
    return $this->hasMany(LoanTransaction::class, 'equipment_id');
  }

  /**
   * Get the currently active loan transaction for this equipment.
   * This relationship is crucial for finding the current borrower and loan details
   * as per the system design's transaction-based workflow. Requires LoanTransaction model and import.
   */
  public function currentTransaction(): HasOne
  {
    // Defines a one-to-one relationship with LoanTransaction,
    // filtering for transactions where this equipment is currently on loan.
    // Uses statuses defined in LoanTransaction model.
    return $this->hasOne(LoanTransaction::class, 'equipment_id')
      ->whereIn('status', [
        LoanTransaction::STATUS_ISSUED,
        LoanTransaction::STATUS_UNDER_MAINTENANCE_ON_LOAN,
        // Include other 'on loan' statuses from LoanTransaction if applicable
      ])
      // Order by issue date descending to get the *most recent* transaction if multiple somehow match (unlikely with correct status management)
      ->latest('issue_timestamp');
  }

  /**
   * NOTE: The `currentLoanItem` relationship was previously attempted based on a ReportController error.
   * Based on the System Design Document, the standard path to get current loan *details*
   * (like the borrower or the item details from the original request) for a specific
   * Equipment instance is via its `currentTransaction` -> `loanApplication` -> `items` / `user`.
   * The `LoanApplicationItem` table seems to represent the requested *type* and *quantity*
   * within an application, not necessarily tracking the *specific* issued Equipment instance
   * directly in a way that facilitates a `HasOne` relationship for the "current" item on the Equipment model itself
   * for the purpose of finding the borrower.
   *
   * To avoid confusion and align with the system design's implicit structure,
   * the `currentLoanItem` relationship method is REMOVED. Access the loan application item details
   * via `$equipment->currentTransaction->loanApplication->items` and filter the collection in the view or an accessor
   * if you need the specific item details corresponding to this equipment's current loan.
   */
  // public function currentLoanItem(): HasOne {} // <-- REMOVED based on system design review


  // --- Scopes ---

  /**
   * Scope a query to include only available equipment. Requires Builder import.
   */
  public function scopeAvailable(Builder $query): void
  {
    $query->where('availability_status', self::AVAILABILITY_AVAILABLE);
  }

  /**
   * Scope a query to include only equipment currently on loan. Requires Builder import.
   */
  public function scopeOnLoan(Builder $query): void
  {
    $query->where('availability_status', self::AVAILABILITY_ON_LOAN);
  }

  /**
   * Scope a query to include only equipment under maintenance. Requires Builder import.
   */
  public function scopeUnderMaintenance(Builder $query): void
  {
    $query->where('availability_status', self::AVAILABILITY_UNDER_MAINTENANCE);
  }

  /**
   * Scope a query to include only disposed equipment. Requires Builder import.
   */
  public function scopeDisposed(Builder $query): void
  {
    $query->where('availability_status', self::AVAILABILITY_DISPOSED);
  }

  /**
   * Scope a query to include only lost equipment. Requires Builder import.
   */
  public function scopeLost(Builder $query): void
  {
    $query->where('availability_status', self::AVAILABILITY_LOST);
  }

  /**
   * Scope a query to include only damaged equipment. Requires Builder import.
   */
  public function scopeDamaged(Builder $query): void
  {
    $query->where('availability_status', self::AVAILABILITY_DAMAGED);
  }


  /**
   * Scope to eager load relationships needed for displaying current loan details in reports or listings.
   * Uses the `currentTransaction` relationship chain, aligning with the system design. Requires Builder import.
   */
  public function scopeWithCurrentLoanDetails(Builder $query): void
  {
    // This scope loads the relationships needed for the Equipment Report.
    // Uses the correct path: Equipment -> currentTransaction -> LoanApplication -> User
    // Requires LoanApplication and User models/imports via LoanTransaction relationship.
    $query->with([
      'currentTransaction.loanApplication.user', // Correct path to current borrower
      // Optional: If you need the items from the current loan application
      // 'currentTransaction.loanApplication.items',
      'location', // Eager load location - Requires Location model and import
      'category', // Eager load category - Requires EquipmentCategory model and import
    ]);
  }


  // --- Accessors (Examples) ---

  /**
   * Accessor for purchase_price to format it as currency. Requires Attribute import.
   */
  protected function purchasePriceFormatted(): Attribute
  {
    return Attribute::make(
      get: fn($value) => $value ? 'RM ' . number_format($value, 2) : null,
    );
  }

  /**
   * Accessor to check if the equipment is currently on loan. Requires Attribute import.
   * Uses the `currentTransaction` relationship.
   */
  protected function isOnLoan(): Attribute
  {
    return Attribute::make(
      get: fn() => $this->currentTransaction !== null,
    );
  }


  /**
   * Accessor to get the translated availability status string.
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
   */
  protected function getConditionStatusTranslatedAttribute(): string
  {
    return match ($this->condition_status) {
      self::CONDITION_GOOD => __('Good'),
      self::CONDITION_FINE => __('Fine'),
      self::CONDITION_BAD => __('Bad'),
      self::CONDITION_DAMAGED => __('Damaged'),
      self::CONDITION_MAINTENANCE_NEEDED => __('Maintenance Needed'),
      default => $this->condition_status,
    };
  }

  /**
   * Accessor to get the asset tag formatted with a prefix (if needed). Requires Attribute import.
   */
  protected function formattedAssetTag(): Attribute
  {
    return Attribute::make(
      get: fn($value, $attributes) => 'MOTAC-ICT-' . $attributes['asset_tag'], // Example prefix
    );
  }


  // Add other model methods, scopes, or relationships...
}
