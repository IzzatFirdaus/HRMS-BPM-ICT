<?php

namespace App\Models;

use App\Traits\CreatedUpdatedDeletedBy; // Assuming this trait exists and adds audit FKs/methods
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo; // Use BelongsTo trait
use Illuminate\Database\Eloquent\SoftDeletes; // Use SoftDeletes trait

// Import models for BelongsTo relationships
use App\Models\Employee;
use App\Models\Leave;
use App\Models\User; // For audit columns (handled by trait)


class EmployeeLeave extends Model // Maps to the 'employee_leave' pivot table
{
  // Use the traits for factory, soft deletes, and audit columns
  use CreatedUpdatedDeletedBy, HasFactory, SoftDeletes;

  /**
   * The table associated with the model.
   *
   * @var string
   */
  protected $table = 'employee_leave'; // Explicitly define the table name

  /**
   * The attributes that are mass assignable.
   * Includes all columns except the primary key (id) and standard timestamps/soft deletes.
   *
   * @var array<int, string>
   */
  protected $fillable = [
    // Removed 'id' from fillable - primary keys are typically not mass assignable

    'employee_id', // Foreign key to employees table
    'leave_id', // Foreign key to leaves table

    'from_date', // Date field
    'to_date', // Date field
    'start_at', // Time field
    'end_at', // Time field
    'note', // Nullable text field

    'is_authorized', // Boolean field
    'is_checked', // Boolean field

    // Audit columns are typically handled by the trait, but listed here for clarity if mass assignment is needed
    // 'created_by',
    // 'updated_by',
    // 'deleted_by',
  ];

  /**
   * The attributes that should be cast.
   * Includes casts for FKs, dates, times, booleans, timestamps, soft deletes.
   *
   * @var array<string, string>
   */
  protected $casts = [
    'employee_id' => 'integer', // Cast FKs to integer for clarity
    'leave_id' => 'integer',

    'from_date' => 'date', // ADDED: Cast date fields to Carbon instances
    'to_date' => 'date',   // ADDED: Cast date fields

    // Cast time fields. Use 'datetime' if you store HH:MM:SS or 'timestamp' if storing unix timestamp.
    // Using 'string' might be simplest if just storing HH:MM:SS strings and handling formatting manually.
    // Let's assume time strings are stored, casting as 'string' is safe.
    'start_at' => 'string', // ADDED: Cast time field as string
    'end_at' => 'string',   // ADDED: Cast time field as string
    // If storing as Carbon instances and you need time only:
    // 'start_at' => 'datetime', // Cast as datetime
    // 'end_at' => 'datetime',   // Cast as datetime
    // Then in accessor: Carbon::parse($this->start_at)->format('H:i')

    'is_authorized' => 'boolean', // ADDED: Cast boolean fields
    'is_checked' => 'boolean',   // ADDED: Cast boolean fields

    'created_at' => 'datetime', // Explicitly cast timestamps if trait doesn't handle or for clarity
    'updated_at' => 'datetime',
    'deleted_at' => 'datetime', // Cast soft delete timestamp
  ];

  /**
   * The attributes that should be hidden for serialization.
   * Includes fields from your provided code snippet.
   *
   * @var array<int, string>
   */
  protected $hidden = [
    'is_authorized', // Hidden as requested
    'is_checked',   // Hidden as requested
    'deleted_by',   // Hidden as requested
    'deleted_at',   // Hidden as requested
  ];


  // The CreatedUpdatedDeletedBy trait is assumed to add these audit relationships:
  // public function createdBy(): BelongsTo { ... }
  // public function updatedBy(): BelongsTo { ... }
  // public function deletedBy(): BelongsTo { ... }


  // 👉 Relationships

  /**
   * Get the employee associated with this leave application record.
   */
  public function employee(): BelongsTo
  {
    // Assumes the 'employee_leave' table has an 'employee_id' foreign key
    return $this->belongsTo(Employee::class, 'employee_id'); // Explicitly define FK for clarity
  }

  /**
   * Get the leave type associated with this leave application record.
   */
  public function leave(): BelongsTo
  {
    // Assumes the 'employee_leave' table has a 'leave_id' foreign key
    return $this->belongsTo(Leave::class, 'leave_id'); // Explicitly define FK for clarity
  }


  // Add custom methods or accessors/mutators here as needed

  /**
   * Check if the leave application has been authorized.
   *
   * @return bool
   */
  public function isAuthorized(): bool
  {
    return (bool) $this->is_authorized;
  }

  /**
   * Check if the leave application has been checked/processed.
   *
   * @return bool
   */
  public function isChecked(): bool
  {
    return (bool) $this->is_checked;
  }
}
