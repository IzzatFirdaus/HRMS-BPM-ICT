<?php

namespace App\Models;

use App\Traits\CreatedUpdatedDeletedBy; // Assuming this trait exists and adds audit FKs/methods
// Removed Carbon as it's not used directly in the model definition
// Removed Builder, Attribute, HasMany, BelongsToMany, HasOne as they are not directly used without defining methods
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo; // Use BelongsTo trait
use Illuminate\Database\Eloquent\SoftDeletes; // Use SoftDeletes trait

// Import models for BelongsTo relationships
use App\Models\Employee;
use App\Models\Center;
use App\Models\Department;
use App\Models\Position;
use App\Models\User; // For audit columns (handled by trait)


class Timeline extends Model
{
  // Use the traits for factory, soft deletes, and audit columns
  use CreatedUpdatedDeletedBy, HasFactory, SoftDeletes;

  /**
   * The attributes that are mass assignable.
   *
   * @var array<int, string>
   */
  protected $fillable = [
    'employee_id', // Foreign key to employees table
    'center_id', // Foreign key to centers table
    'department_id', // Foreign key to departments table
    'position_id', // Foreign key to positions table

    'start_date', // Date field
    'end_date', // Nullable date field
    'is_sequent', // Boolean flag
    'notes', // Nullable text field

    // Audit columns are typically handled by the trait, but listed here for clarity if mass assignment is needed
    // 'created_by',
    // 'updated_by',
    // 'deleted_by',
  ];

  /**
   * The attributes that should be cast.
   *
   * @var array<string, string>
   */
  protected $casts = [
    'employee_id' => 'integer', // Cast FKs to integer for clarity
    'center_id' => 'integer',
    'department_id' => 'integer',
    'position_id' => 'integer',

    'start_date' => 'date', // ADDED: Cast date fields to Carbon instances
    'end_date' => 'date', // ADDED: Cast nullable date field

    'is_sequent' => 'boolean', // ADDED: Cast boolean flag

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
   * Get the employee associated with this timeline entry.
   */
  public function employee(): BelongsTo
  {
    // Assumes the 'timelines' table has an 'employee_id' foreign key
    return $this->belongsTo(Employee::class, 'employee_id'); // Explicitly define FK for clarity
  }

  /**
   * Get the center associated with this timeline entry.
   */
  public function center(): BelongsTo
  {
    // Assumes the 'timelines' table has a 'center_id' foreign key
    return $this->belongsTo(Center::class, 'center_id'); // Explicitly define FK for clarity
  }

  /**
   * Get the department associated with this timeline entry.
   */
  public function department(): BelongsTo
  {
    // Assumes the 'timelines' table has a 'department_id' foreign key
    return $this->belongsTo(Department::class, 'department_id'); // Explicitly define FK for clarity
  }

  /**
   * Get the position associated with this timeline entry.
   */
  public function position(): BelongsTo
  {
    // Assumes the 'timelines' table has a 'position_id' foreign key
    return $this->belongsTo(Position::class, 'position_id'); // Explicitly define FK for clarity
  }


  // Add custom methods or accessors/mutators here as needed
}
