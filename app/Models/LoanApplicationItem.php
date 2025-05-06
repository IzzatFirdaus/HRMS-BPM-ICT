<?php

namespace App\Models; // Ensure the namespace is correct for your project

// Assuming this trait exists and adds audit FKs/methods like created_by, updated_by, deleted_by
use App\Traits\CreatedUpdatedDeletedBy;
// Assuming Carbon is used implicitly with date casts (including explicitly importing here is good practice)
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory; // Import HasFactory trait
use Illuminate\Database\Eloquent\Model; // Import base Model class
use Illuminate\Database\Eloquent\Relations\BelongsTo; // Import BelongsTo relationship type
use Illuminate\Database\Eloquent\SoftDeletes; // Import SoftDeletes trait
use Illuminate\Database\Eloquent\Casts\Attribute; // Import Attribute for modern accessors/mutators if needed

// Import models for relationships (Eloquent needs to know about the related models for relationships)
use App\Models\LoanApplication; // LoanApplicationItem belongsTo LoanApplication
use App\Models\Equipment; // LoanApplicationItem belongsTo Equipment (if an item requests a *specific* piece of equipment)
use App\Models\User; // For audit columns (handled by trait CreatedUpdatedDeletedBy)
use App\Models\LoanTransaction; // For deriving issued quantity in accessor


/**
 * App\Models\LoanApplicationItem
 *
 * Represents a single equipment item requested within a loan application.
 * Initially just stores the *type* and *quantity* requested.
 * Can optionally be linked to a specific Equipment asset later (e.g., upon issuance).
 * Stores quantities approved, issued, and returned. Includes audit trails and soft deletion.
 *
 * @property int $id
 * @property int $loan_application_id Foreign key to the parent loan application.
 * @property int|null $equipment_id Foreign key to a specific equipment item (if the item is for one specific asset).
 * @property string $equipment_type The general type of equipment requested (e.g., 'Laptop', 'Projector').
 * @property int $quantity_requested The quantity of this item type requested by the applicant.
 * @property int $quantity_approved The quantity of this item type approved by the approver.
 * @property int $quantity_issued The quantity of this item type actually issued.
 * @property int $quantity_returned The quantity of this item type returned.
 * @property string|null $notes Additional notes for this specific item.
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property int|null $deleted_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read LoanApplication $loanApplication The parent loan application.
 * @property-read Equipment|null $equipment The specific equipment asset if linked.
 */
class LoanApplicationItem extends Model
{
  use HasFactory, SoftDeletes; // Assuming SoftDeletes trait is used

  protected $fillable = [
    'loan_application_id',
    'equipment_id', // Nullable
    'equipment_type',
    'quantity_requested',
    'quantity_approved',
    'quantity_issued',
    'quantity_returned',
    'notes',
    // created_by, updated_by, deleted_by handled by trait CreatedUpdatedDeletedBy
  ];

  protected $casts = [
    'quantity_requested' => 'integer',
    'quantity_approved' => 'integer',
    'quantity_issued' => 'integer',
    'quantity_returned' => 'integer',
    'loan_application_id' => 'integer',
    'equipment_id' => 'integer',
    'equipment_type' => 'string',
    'notes' => 'string',
    'created_at' => 'datetime',
    'updated_at' => 'datetime',
    'deleted_at' => 'datetime',
  ];

  // If CreatedUpdatedDeletedBy trait is used, ensure these columns are fillable or handled by the trait
  // protected $guarded = []; // Or use guarded instead of fillable


  // --- Relationships ---

  /**
   * Get the loan application that the item belongs to.
   * Defines a many-to-one relationship.
   * Assumes 'loan_application_items' table has 'loan_application_id' foreign key.
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\LoanApplication, \App\Models\LoanApplicationItem>
   */
  public function loanApplication(): BelongsTo // Added return type hint
  {
    return $this->belongsTo(LoanApplication::class, 'loan_application_id');
  }

  /**
   * Get the specific equipment asset linked to this item (if applicable).
   * Defines a many-to-one relationship.
   * Assumes 'loan_application_items' table has 'equipment_id' foreign key.
   * This relationship is nullable if the item just represents a type/quantity, not a specific asset.
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Equipment, \App\Models\LoanApplicationItem>
   */
  public function equipment(): BelongsTo // Added return type hint
  {
    return $this->belongsTo(Equipment::class, 'equipment_id');
  }


  // --- Accessors (Examples - these might be complex depending on logic) ---

  // /**
  //  * Calculate the total quantity issued for this specific item type across all transactions
  //  * related to the parent application. This logic might be better in the service or
  //  * a dedicated accessor that queries transactions.
  //  * Note: The previous implementation of this accessor in the file snippet was incorrect
  //  * as it assumed the item had an equipment_id which is not always the case for requested items.
  //  * Instead, it should sum up quantity_issued on the item itself, or query transactions
  //  * related to the parent application and filter by equipment type if transaction links to type.
  //  * A simpler accessor just returns the 'quantity_issued' column value.
  //  *
  //  * @return int The quantity issued for this item type based on the quantity_issued column.
  //  */
  // protected function getIssuedQuantityAttribute(): int // Corrected method visibility to protected for accessors, Added return type hint
  // {
  //     // This simply returns the value of the 'quantity_issued' column.
  //     // If you need a computed sum from transactions, that requires different logic.
  //     return $this->quantity_issued ?? 0; // Use nullish coalescing just in case
  // }


  // Add any other custom methods or accessors/mutators below this line
}
