<?php

namespace App\Models;

use App\Traits\CreatedUpdatedDeletedBy; // Assuming this trait exists and adds audit FKs/methods
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany; // Use BelongsToMany trait
use Illuminate\Database\Eloquent\Relations\HasMany as EloquentHasMany; // 👇 ALIAS: Import HasMany and alias it for clarity in type hints
use Illuminate\Database\Eloquent\SoftDeletes; // Use SoftDeletes trait

// Import model for BelongsToMany relationship
use App\Models\Employee; // Leave is linked to Employee via pivot
// Import model for HasMany relationship
use App\Models\EmployeeLeave; // Leave is linked to EmployeeLeave directly


class Leave extends Model
{
  // Use the traits for factory, soft deletes, and audit columns
  use CreatedUpdatedDeletedBy, HasFactory, SoftDeletes;

  /**
   * The attributes that are mass assignable.
   *
   * @var array<int, string>
   */
  protected $fillable = [
    'name',
    'is_instantly',
    'is_accumulative',
    'discount_rate',
    'days_limit',
    'minutes_limit',
    'notes',
    // Audit columns are typically handled by the trait
  ];

  /**
   * The attributes that should be cast.
   *
   * @var array<string, string>
   */
  protected $casts = [
    'is_instantly' => 'boolean',
    'is_accumulative' => 'boolean',
    'discount_rate' => 'integer',
    'days_limit' => 'integer',
    'minutes_limit' => 'integer',
    'created_at' => 'datetime',
    'updated_at' => 'datetime',
    'deleted_at' => 'datetime',
  ];

  // The CreatedUpdatedDeletedBy trait is assumed to add these audit relationships:
  // public function createdBy(): BelongsTo { ... }
  // public function updatedBy(): BelongsTo { ... }
  // public function deletedBy(): BelongsTo { ... }


  // 👉 Relationships

  /**
   * Get the employees who have taken this leave type through the pivot table.
   */
  public function employees(): BelongsToMany
  {
    // Assumes a 'employee_leave' pivot table exists linking leaves and employees
    // with columns 'leave_id' and 'employee_id'.
    return $this->belongsToMany(Employee::class, 'employee_leave', 'leave_id', 'employee_id')
      ->withPivot([
        'id', // Include the pivot table's own ID if it has one and you need it
        'from_date',
        'to_date',
        'start_at',
        'end_at',
        'note',
        'is_authorized',
        'is_checked',
        // Include pivot table audit columns if trait doesn't handle them automatically on the pivot model
        'created_by',
        'updated_by',
        'deleted_by',
        // Include pivot table timestamps if trait doesn't handle them automatically
        'created_at',
        'updated_at',
        'deleted_at'
      ])
      // Optional: Add casting for pivot attributes for correct types
      ->as('application') // Name the pivot attribute for easier access, e.g., $leave->employees[0]->application->from_date
      ->withCasts([
        'from_date' => 'date',
        'to_date' => 'date',
        'start_at' => 'datetime', // Or 'time' depending on storage and how you use it
        'end_at' => 'datetime', // Or 'time'
        'is_authorized' => 'boolean',
        'is_checked' => 'boolean',
        'created_by' => 'integer', // Cast pivot audit FKs to integer
        'updated_by' => 'integer',
        'deleted_by' => 'integer',
        'created_at' => 'datetime', // Cast pivot timestamps
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime', // Cast pivot soft delete timestamp
      ]);
  }

  /**
   * Get the individual employee leave applications for this leave type.
   */
  public function employeeLeaveApplications(): EloquentHasMany // 👇 CORRECTED: Use aliased type hint
  {
    // Assumes the 'employee_leave' table has a 'leave_id' foreign key
    return $this->hasMany(EmployeeLeave::class, 'leave_id');
  }


  // Add custom methods or accessors/mutators here as needed

  /**
   * Check if this leave type can be taken instantly without prior authorization.
   *
   * @return bool
   */
  public function isInstant(): bool
  {
    return (bool) $this->is_instantly;
  }

  /**
   * Check if remaining leave days/minutes accumulate to the next period.
   *
   * @return bool
   */
  public function isAccumulative(): bool
  {
    return (bool) $this->is_accumulative;
  }

  /**
   * Get the name formatted with the discount rate (e.g., "Annual Leave (100%)").
   *
   * @return string
   */
  public function getNameWithDiscountRateAttribute(): string
  {
    return "{$this->name} ({$this->discount_rate}%)";
  }
}
