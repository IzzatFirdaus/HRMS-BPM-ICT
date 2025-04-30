<?php

namespace App\Models;

use App\Traits\CreatedUpdatedDeletedBy; // Assuming this trait exists and adds audit FKs/methods
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo; // Use BelongsTo trait
use Illuminate\Database\Eloquent\SoftDeletes; // Use SoftDeletes trait
use Illuminate\Database\Eloquent\Casts\Attribute; // Import Attribute for accessor/mutator type hinting
use Illuminate\Database\Eloquent\Relations\BelongsTo as BelongsToRelation; // Alias if needed


// Import models for relationships
use App\Models\LoanApplication;
use App\Models\Equipment; // LoanApplicationItem belongs to specific Equipment
use App\Models\User; // For audit columns (handled by trait)
use App\Models\LoanTransaction; // For deriving issued quantity


/**
 * App\Models\LoanApplicationItem
 * 
 * Represents a single item requested within a loan application.
 * Linked to a specific LoanApplication and a specific Equipment asset.
 *
 * @property int $id
 * @property int $loan_application_id The loan application this item belongs to (Foreign key).
 * @property int $equipment_id Foreign key to specific Equipment asset requested.
 * @property int $quantity_requested Kuantiti requested (Integer).
 * @property int|null $quantity_approved Quantity approved by approver(s) (Integer), can be null if not yet approved.
 * @property string|null $notes Catatan for this specific item (Text).
 * @property int|null $created_by Foreign key to the user who created the record.
 * @property int|null $updated_by Foreign key to the user who last updated the record.
 * @property int|null $deleted_by Foreign key to the user who soft deleted the record.
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\Equipment $equipment The specific equipment asset requested for this item.
 * @property-read \App\Models\LoanApplication $loanApplication The loan application that the item belongs to.
 * @property-read int $issuedQuantity The total quantity of this specific equipment item issued for the parent application.
 * @property-read \App\Models\User|null $createdBy The user who created the record.
 * @property-read \App\Models\User|null $deletedBy The user who soft deleted the record.
 * @property-read \App\Models\User|null $updatedBy The user who last updated the record.
 * @method static \Illuminate\Database\Eloquent\Builder|LoanApplicationItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LoanApplicationItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LoanApplicationItem onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|LoanApplicationItem query()
 * @method static \Illuminate\Database\Eloquent\Builder|LoanApplicationItem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LoanApplicationItem whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LoanApplicationItem whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LoanApplicationItem whereDeletedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LoanApplicationItem whereEquipmentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LoanApplicationItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LoanApplicationItem whereLoanApplicationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LoanApplicationItem whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LoanApplicationItem whereQuantityApproved($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LoanApplicationItem whereQuantityRequested($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LoanApplicationItem whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LoanApplicationItem whereUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LoanApplicationItem withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|LoanApplicationItem withoutTrashed()
 * @property string $equipment_type
 * @property-read int $issued_quantity
 * @method static \Database\Factories\LoanApplicationItemFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LoanApplicationItem whereEquipmentType($value)
 * @mixin \Eloquent
 */
class LoanApplicationItem extends Model
{
  // Use the traits for factory, soft deletes, and audit columns
  use CreatedUpdatedDeletedBy, HasFactory, SoftDeletes;

  /**
   * The attributes that are mass assignable.
   * Includes fields from the migration, linking to a specific equipment.
   * 'id' is excluded as it is the primary key.
   *
   * @var array<int, string>
   */
  protected $fillable = [
    'loan_application_id', // The loan application this item belongs to (Foreign key)
    'equipment_id', // Foreign key to specific Equipment asset

    'quantity_requested', // Kuantiti requested (Integer)
    'quantity_approved', // Quantity approved by approver(s) (Integer)
    'notes', // Catatan for this specific item (Text)

    // 'created_by', // Handled by trait
    // 'updated_by', // Handled by trait
    // 'deleted_by', // Handled by trait
  ];

  /**
   * The attributes that should be cast.
   * Ensures data types are correct and dates are Carbon instances.
   * Includes casts for FKs, integers, strings, timestamps, and soft deletes.
   * Explicitly casting all attributes for clarity.
   *
   * @var array<string, string>
   */
  protected $casts = [
    'loan_application_id' => 'integer', // Cast FKs to integer
    'equipment_id' => 'integer',

    'quantity_requested' => 'integer', // Cast integer quantities
    'quantity_approved' => 'integer',

    'notes' => 'string', // Explicitly cast notes as string

    'created_at' => 'datetime', // Explicitly cast timestamps
    'updated_at' => 'datetime',
    'deleted_at' => 'datetime', // Cast soft delete timestamp
  ];


  // The CreatedUpdatedDeletedBy trait is assumed to add these audit relationships:

  /**
   * Get the user who created the model.
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\User, \App\Models\LoanApplicationItem>
   */
  // public function createdBy(): BelongsToRelation;

  /**
   * Get the user who last updated the model.
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\User, \App\Models\LoanApplicationItem>
   */
  // public function updatedBy(): BelongsToRelation;

  /**
   * Get the user who soft deleted the model.
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\User, \App\Models\LoanApplicationItem>
   */
  // public function deletedBy(): BelongsToRelation;


  // 👉 Relationships

  /**
   * Get the loan application that the item belongs to.
   * Defines a many-to-one relationship where a LoanApplicationItem belongs to one LoanApplication.
   * Assumes the 'loan_application_items' table has a 'loan_application_id' foreign key.
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\LoanApplication, \App\Models\LoanApplicationItem>
   */
  public function loanApplication(): BelongsTo // Added return type hint
  {
    // Defines a many-to-one relationship with the LoanApplication model
    return $this->belongsTo(LoanApplication::class, 'loan_application_id'); // Explicitly define foreign key
  }

  /**
   * Get the specific equipment asset requested for this item.
   * Defines a many-to-one relationship where a LoanApplicationItem belongs to one Equipment asset.
   * Assumes the 'loan_application_items' table has an 'equipment_id' foreign key.
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Equipment, \App\Models\LoanApplicationItem>
   */
  public function equipment(): BelongsTo // Added return type hint
  {
    // Defines a many-to-one relationship with the Equipment model
    return $this->belongsTo(Equipment::class, 'equipment_id'); // Explicitly define foreign key
  }


  // 👉 Accessors

  /**
   * Get the total quantity of this specific equipment item issued for the parent application.
   * Derived by counting related 'issued' LoanTransaction records linked to the parent LoanApplication
   * that involve the specific equipment asset linked to this item.
   * Note: Assumes each 'issued' LoanTransaction record for this equipment represents one unit.
   *
   * @return int The total quantity issued.
   */
  public function getIssuedQuantityAttribute(): int // Added return type hint and refined docblock
  {
    // Check if the item is linked to specific equipment and the parent application exists
    if ($this->equipment_id !== null && $this->loanApplication !== null) {
      // Count transactions for the parent loan application
      // that are marked with the 'issued' status constant (or string literal)
      // AND involve the specific equipment linked to this item.
      return $this->loanApplication->transactions()
        ->where('status', LoanTransaction::STATUS_ISSUED ?? 'issued') // Use constant if defined, fallback to string literal
        ->where('equipment_id', $this->equipment_id) // Filter by the specific equipment ID on the item
        ->count(); // Assuming each transaction is for one unit
    }

    return 0; // Return 0 if item not linked to equipment or no parent application
  }


  // Add custom methods or accessors/mutators here as needed
}
