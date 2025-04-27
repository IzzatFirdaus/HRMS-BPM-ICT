<?php

namespace App\Models;

use App\Traits\CreatedUpdatedDeletedBy; // Assuming this trait exists and adds audit FKs/methods
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo; // Use BelongsTo trait
use Illuminate\Database\Eloquent\SoftDeletes; // Use SoftDeletes trait

// Import models for BelongsTo relationships
use App\Models\Employee;
use App\Models\Equipment; // Equipment model (replaces Asset)
use App\Models\User; // For audit columns (handled by trait)
// Removed Category and SubCategory imports as they are not directly related via FKs on Transitions


class Transition extends Model
{
  // Use the traits for factory, soft deletes, and audit columns
  use CreatedUpdatedDeletedBy, HasFactory, SoftDeletes;

  /**
   * The attributes that are mass assignable.
   * Includes fields from the migration, using 'equipment_id' instead of 'asset_id'.
   *
   * @var array<int, string>
   */
  protected $fillable = [
    'equipment_id', // ADDED: Foreign key to the equipment asset (replaces asset_id)
    'employee_id', // Foreign key to the employee

    'handed_date', // Date field for when equipment was handed
    'return_date', // Nullable date field for when equipment was returned
    'center_document_number', // Nullable string for document number
    'reason', // Nullable string for reason
    'note', // Nullable text field for notes

    // Audit columns are typically handled by the trait, but listed here for clarity if mass assignment is needed
    // 'created_by',
    // 'updated_by',
    // 'deleted_by',
  ];

  /**
   * The attributes that should be cast.
   * Includes casts for FKs, dates, timestamps, and soft deletes.
   *
   * @var array<string, string>
   */
  protected $casts = [
    'equipment_id' => 'integer', // Cast FKs to integer for clarity
    'employee_id' => 'integer',

    'handed_date' => 'date', // ADDED: Cast date fields to Carbon instances
    'return_date' => 'date', // ADDED: Cast nullable date field

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
   * Get the employee associated with this transition record.
   */
  public function employee(): BelongsTo
  {
    // Assumes the 'transitions' table has an 'employee_id' foreign key
    return $this->belongsTo(Employee::class, 'employee_id'); // Explicitly define FK for clarity
  }

  /**
   * Get the equipment asset involved in the transition record.
   * This relationship replaces the old 'asset' relationship.
   */
  public function equipment(): BelongsTo // ADDED: Relationship to Equipment (replaces asset)
  {
    // Assumes the 'transitions' table has an 'equipment_id' foreign key
    return $this->belongsTo(Equipment::class, 'equipment_id'); // Explicitly define FK for clarity
  }


  // 👉 Functions (Removed outdated functions)

  // The getCategory and getSubCategory functions were based on string manipulation of an old asset ID format
  // and directly queried the database. This logic is outdated and should be removed.
  // If you need Category or SubCategory information, access it via the related Equipment model:
  // $transition->equipment->category (assuming Equipment model has a 'category' relationship)
  // $transition->equipment->subCategory (assuming Equipment model has a 'subCategory' relationship)


  // Add custom methods or accessors/mutators here as needed
}
