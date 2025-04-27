<?php

namespace App\Models;

use App\Traits\CreatedUpdatedDeletedBy; // Assuming this trait exists and adds audit FKs/methods
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo; // Use BelongsTo trait
use Illuminate\Database\Eloquent\Relations\HasMany; // Use HasMany trait
use Illuminate\Database\Eloquent\SoftDeletes; // Use SoftDeletes trait

// Import models for relationships
use App\Models\Employee; // For assigned_to_employee_id
use App\Models\Department; // For department_id
use App\Models\Center; // For center_id
use App\Models\User; // For audit columns (handled by trait)
use App\Models\LoanApplicationItem; // Equipment in item requests
use App\Models\LoanTransaction; // Equipment in loan transactions
use App\Models\Transition; // Equipment in transitions


class Equipment extends Model // This model now represents the merged 'equipment' table
{
  // Use the traits for factory, soft deletes, and audit columns
  use CreatedUpdatedDeletedBy, HasFactory, SoftDeletes;

  /**
   * The attributes that are mass assignable.
   * Includes fields from both original 'equipment' and 'assets' tables.
   *
   * @var array<int, string>
   */
  protected $fillable = [
    // Fields from original 'equipment' migration
    'class', // ADDED: equipment classification (e.g., IT, Furniture)
    'asset_type', // ADDED: more specific type (e.g., Laptop, Chair)
    'serial_number',
    'tag_id',
    'model',
    'manufacturer', // ADDED: manufacturer
    'value', // ADDED: value/price
    'purchase_date', // From original equipment

    'warranty_expiry_date', // From original equipment
    'notes', // From original equipment

    // Fields merged from 'assets' migration
    // 'old_id', // If you need to keep track of old asset IDs
    'acquisition_date', // From assets migration
    'acquisition_type', // From assets migration
    'funded_by', // From assets migration
    // Note: Assuming 'note' from assets is redundant with 'notes' from equipment, using 'notes'.

    // Status and location fields (updated names)
    'availability_status', // ADDED: replaces original 'status'
    'condition_status', // ADDED: new condition status
    'location_details', // ADDED: replaces original 'current_location' or similar

    // Foreign keys
    'assigned_to_employee_id', // Link to assigned employee
    'department_id', // Link to department
    'center_id', // Link to center

    // Audit columns are typically handled by the trait, but listed here for clarity if mass assignment is needed
    // 'created_by',
    // 'updated_by',
    // 'deleted_by',
  ];

  /**
   * The attributes that should be cast.
   * Includes casts for dates, enums, decimals, timestamps, and soft deletes.
   *
   * @var array<string, string>
   */
  protected $casts = [
    'purchase_date' => 'date',
    'warranty_expiry_date' => 'date',
    'acquisition_date' => 'date', // ADDED: cast for acquisition date
    'value' => 'decimal:2', // ADDED: cast decimal value with 2 precision
    'is_active' => 'boolean', // Assuming is_active exists from assets? Check migration
    'availability_status' => 'string', // Or 'enum' if using custom enum casting
    'condition_status' => 'string', // Or 'enum'
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
   * Get the employee the equipment is currently assigned to.
   */
  public function assignedToEmployee(): BelongsTo
  {
    return $this->belongsTo(Employee::class, 'assigned_to_employee_id'); // Explicitly define FK
  }

  /**
   * Get the department the equipment belongs to or is located in.
   */
  public function department(): BelongsTo
  {
    return $this->belongsTo(Department::class, 'department_id'); // Explicitly define FK
  }

  /**
   * Get the center the equipment belongs to or is located in.
   */
  public function center(): BelongsTo
  {
    return $this->belongsTo(Center::class, 'center_id'); // Explicitly define FK
  }

  /**
   * Get the loan application items associated with this equipment.
   */
  public function loanApplicationItems(): HasMany
  {
    // Assumes loan_application_items table has equipment_id FK
    return $this->hasMany(LoanApplicationItem::class, 'equipment_id'); // Explicitly define FK
  }


  /**
   * Get the loan transactions associated with this equipment.
   */
  public function loanTransactions(): HasMany
  {
    // Assumes loan_transactions table has equipment_id FK
    return $this->hasMany(LoanTransaction::class, 'equipment_id'); // Explicitly define FK
  }

  /**
   * Get the transitions associated with this equipment (from assets table).
   */
  public function transitions(): HasMany
  {
    // Assumes transitions table has equipment_id FK (was asset_id)
    return $this->hasMany(Transition::class, 'equipment_id'); // Explicitly define FK
  }


  // 👉 Helper Methods

  /**
   * Check if the equipment is currently available.
   */
  public function isAvailable(): bool
  {
    // Checks the new availability_status column
    return $this->availability_status === 'available';
  }

  /**
   * Check if the equipment is currently on loan.
   */
  public function isOnLoan(): bool
  {
    // Checks the new availability_status column
    return $this->availability_status === 'on_loan';
  }

  /**
   * Check if the equipment is damaged.
   */
  public function isDamaged(): bool
  {
    // Checks the new condition_status column
    return $this->condition_status === 'damaged';
  }

  /**
   * Get the current loan transaction if the equipment is on loan.
   * Assumes a 'status' column exists on the LoanTransaction model/table
   * and 'issued' is the status for active loans.
   */
  public function currentTransaction(): ?LoanTransaction // Use nullable return type hint
  {
    // Find the latest related loan transaction that has the status 'issued'
    return $this->loanTransactions()->where('status', 'issued')->latest()->first();
  }


  // Add custom methods or accessors/mutators here as needed
}
