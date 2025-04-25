<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\LoanApplication; // Import the LoanApplication model for relationship

class LoanApplicationItem extends Model
{
  use HasFactory;

  // Define fillable or guarded properties
  protected $fillable = [
    'loan_application_id', // The loan application this item belongs to
    'equipment_type', // Jenis Peralatan requested
    'quantity_requested', // Kuantiti requested
    'notes', // Catatan for this specific item
    'quantity_approved', // Quantity approved by approver(s)
    // 'quantity_issued', // Removed: Issued quantity is tracked via LoanTransaction models
  ];

  // No casts needed for the current properties

  // Define relationships

  /**
   * Get the loan application that the item belongs to.
   */
  public function loanApplication()
  {
    return $this->belongsTo(LoanApplication::class, 'loan_application_id'); // Explicitly define foreign key
  }

  // Add custom methods or accessors/mutators here as needed

  /**
   * Accessor to get the total quantity issued for this item type on the parent application.
   * This is derived from related LoanTransaction models.
   * Uncomment and implement if needed.
   */
  // public function getIssuedQuantityAttribute(): int
  // {
  //     // You would need to query the loan application's transactions
  //     // that are 'issued' and related to equipment assets of this item's type.
  //     // This requires relationships from LoanApplication to LoanTransaction to Equipment.
  //     // Example (simplified - requires relationships setup):
  //     // return $this->loanApplication->transactions()
  //     //             ->where('status', 'issued')
  //     //             ->whereHas('equipment', fn($q) => $q->where('equipment_type', $this->equipment_type))
  //     //             ->count(); // Assuming each transaction is for one unit
  //     return 0; // Default implementation
  // }
}
