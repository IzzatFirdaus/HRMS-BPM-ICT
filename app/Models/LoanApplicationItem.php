<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoanApplicationItem extends Model
{
  use HasFactory;

  // Define fillable or guarded properties
  protected $fillable = [
    'loan_application_id',
    'equipment_type',
    'quantity_requested',
    'notes',
    'quantity_approved',
    'quantity_issued',
  ];

  // Define relationships

  /**
   * Get the loan application that the item belongs to.
   */
  public function loanApplication()
  {
    return $this->belongsTo(LoanApplication::class);
  }

  // Add custom methods or accessors/mutators here as needed
}
