<?php

namespace App\Models;

use App\Traits\CreatedUpdatedDeletedBy; // Assuming this trait exists and adds audit FKs/methods
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo; // Use BelongsTo trait
// Removed HasMany as it's not used directly without defining a method
use Illuminate\Database\Eloquent\SoftDeletes; // Use SoftDeletes trait

// Import models for relationships
use App\Models\LoanApplication;
use App\Models\Equipment; // LoanApplicationItem belongs to specific Equipment
use App\Models\User; // For audit columns (handled by trait)
use App\Models\LoanTransaction; // For deriving issued quantity


class LoanApplicationItem extends Model
{
  // Use the traits for factory, soft deletes, and audit columns
  use CreatedUpdatedDeletedBy, HasFactory, SoftDeletes;

  /**
   * The attributes that are mass assignable.
   * Includes fields from the migration, linking to a specific equipment.
   *
   * @var array<int, string>
   */
  protected $fillable = [
    // Removed 'id' from fillable - primary keys are typically not mass assignable

    'loan_application_id', // The loan application this item belongs to (Foreign key)
    'equipment_id', // ADDED: Foreign key to specific Equipment asset (replaces equipment_type)

    'quantity_requested', // Kuantiti requested (Integer)
    'quantity_approved', // Quantity approved by approver(s) (Integer)
    'notes', // Catatan for this specific item (Text)

    // Audit columns are typically handled by the trait, but listed here for clarity if mass assignment is needed
    // 'created_by',
    // 'updated_by',
    // 'deleted_by',
  ];

  /**
   * The attributes that should be cast.
   * Includes casts for FKs, integers, timestamps, and soft deletes.
   *
   * @var array<string, string>
   */
  protected $casts = [
    'loan_application_id' => 'integer', // Cast FKs to integer for clarity
    'equipment_id' => 'integer',

    'quantity_requested' => 'integer', // Cast integer quantities
    'quantity_approved' => 'integer',

    'created_at' => 'datetime', // Explicitly cast timestamps if trait doesn't handle or for clarity
    'updated_at' => 'datetime',
    'deleted_at' => 'datetime', // Cast soft delete timestamp
  ];


  // The CreatedUpdatedDeletedBy trait is assumed to add these audit relationships:
  // public function createdBy(): BelongsTo { ... }
  // public function updatedBy(): BelongsTo { ... }
  // public function deletedBy(): BelongsTo { ... }


  // 👉 Relationships

  /**
   * Get the loan application that the item belongs to.
   */
  public function loanApplication(): BelongsTo
  {
    // Assumes the 'loan_application_items' table has a 'loan_application_id' foreign key
    return $this->belongsTo(LoanApplication::class, 'loan_application_id'); // Explicitly define foreign key
  }

  /**
   * Get the specific equipment asset requested for this item.
   * Assumes the 'loan_application_items' table has an 'equipment_id' foreign key.
   */
  public function equipment(): BelongsTo // ADDED: BelongsTo relationship to Equipment
  {
    return $this->belongsTo(Equipment::class, 'equipment_id'); // Explicitly define foreign key
  }


  // 👉 Helper Methods or Accessors (Removed the original getIssuedQuantityAttribute logic)

  // The original getIssuedQuantityAttribute logic was based on equipment_type, which is no longer used.
  // Deriving issued quantity now needs to consider LoanTransaction records linked to the parent LoanApplication
  // and potentially related to this item's specific equipment_id (if the transaction links to the item, or if you query transactions for the application and filter by equipment).

  // A common approach is to count transactions linked to the parent application
  // that involved the specific equipment asset linked to this item.

  /**
   * Get the total quantity of this specific equipment item issued for the parent application.
   * Derived from related LoanTransaction models linked to the parent LoanApplication.
   * Assumes LoanTransaction has a 'status' column ('issued' state) and links to Equipment.
   *
   * @return int
   */
  public function getIssuedQuantityAttribute(): int
  {
    // Check if the item is linked to specific equipment and the parent application exists
    if ($this->equipment_id !== null && $this->loanApplication !== null) {
      // Count transactions for the parent loan application
      // that are marked as 'issued'
      // AND involve the specific equipment linked to this item.
      return $this->loanApplication->transactions()
        ->where('status', 'issued')
        ->where('equipment_id', $this->equipment_id) // Filter by the specific equipment ID on the item
        ->count(); // Assuming each transaction is for one unit
    }

    return 0; // Return 0 if item not linked to equipment or no parent application
  }


  // Add custom methods or accessors/mutators here as needed
}
