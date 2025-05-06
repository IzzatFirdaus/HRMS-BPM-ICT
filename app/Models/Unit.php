<?php

namespace App\Models;

use App\Traits\CreatedUpdatedDeletedBy; // Assuming this trait is used
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; // Assuming SoftDeletes is used
use Illuminate\Database\Eloquent\Relations\HasMany; // For relationship to Employees
use Illuminate\Database\Eloquent\Relations\BelongsTo; // For relationship to Department and Center

/**
 * App\Models\Unit
 *
 * Represents a functional unit within a Department or Center.
 *
 * @property int $id
 * @property string $name Name of the unit.
 * @property int|null $department_id Foreign key to the departments table (optional).
 * @property int|null $center_id Foreign key to the centers table (optional).
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property int|null $deleted_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 *
 * // Relationships
 * @property-read \Illuminate\Database\Eloquent\Collection<\App\Models\Employee> $employees
 * @property-read \App\Models\Department|null $department
 * @property-read \App\Models\Center|null $center
 */
class Unit extends Model
{
  use CreatedUpdatedDeletedBy, SoftDeletes;

  protected $table = 'units'; // Explicitly define table name if it deviates from convention

  protected $fillable = [
    'name',
    'department_id',
    'center_id',
  ];

  protected $casts = [
    // Add casts for other relevant fields
  ];

  /**
   * Get the employees belonging to the unit.
   */
  public function employees(): HasMany
  {
    return $this->hasMany(Employee::class, 'unit_id');
  }

  /**
   * Get the department the unit belongs to.
   */
  public function department(): BelongsTo
  {
    return $this->belongsTo(Department::class, 'department_id');
  }

  /**
   * Get the center the unit belongs to.
   */
  public function center(): BelongsTo
  {
    return $this->belongsTo(Center::class, 'center_id');
  }

  // Add any other relationships, accessors, mutators, or scopes here
}
