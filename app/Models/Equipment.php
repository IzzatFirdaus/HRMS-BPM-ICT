<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Equipment extends Model
{
  use HasFactory;

  // Define fillable or guarded properties
  protected $fillable = [
    'asset_type',
    'brand',
    'model',
    'serial_number',
    'tag_id',
    'purchase_date',
    'warranty_expiry_date',
    'status',
    'current_location',
    'notes',
  ];

  // Define cast properties
  protected $casts = [
    'purchase_date' => 'date',
    'warranty_expiry_date' => 'date',
  ];

  // Define relationships

  /**
   * Get the loan transactions associated with this specific equipment asset.
   */
  public function loanTransactions()
  {
    return $this->hasMany(LoanTransaction::class);
  }

  // Add custom methods or accessors/mutators here as needed

  /**
   * Check if the equipment is currently available.
   */
  public function isAvailable(): bool
  {
    return $this->status === 'available';
  }

  /**
   * Get the current loan transaction if the equipment is on loan.
   */
  public function currentTransaction()
  {
    // Assuming there's a way to identify the active loan transaction
    // This might require adding a flag or checking the latest transaction status
    return $this->loanTransactions()->where('status', 'issued')->latest()->first();
  }
}
