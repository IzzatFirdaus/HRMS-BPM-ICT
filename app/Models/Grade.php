<?php

namespace App\Models;

use App\Traits\CreatedUpdatedDeletedBy; // Assuming this trait exists and adds audit FKs/methods
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany; // Use HasMany trait
use Illuminate\Database\Eloquent\SoftDeletes; // Use SoftDeletes trait
use Illuminate\Database\Eloquent\Relations\BelongsTo; // Import BelongsTo for trait relationships
use Illuminate\Database\Eloquent\Relations\HasMany as HasManyRelation; // Alias if needed


// Import models for HasMany relationships
use App\Models\User; // User model has grade_id FK
use App\Models\Employee; // Employee model has grade_id FK


/**
 * App\Models\Grade
 * 
 * Represents an employee grade (e.g., Grade 41, Grade 44) and tracks if it's an approver grade.
 * Linked to User and Employee models.
 *
 * @property int $id
 * @property string $name The name of the grade (e.g., 'Grade 41').
 * @property int $level The numerical level of the grade (e.g., 41).
 * @property bool $is_approver_grade Indicates if this grade is designated as an approver grade.
 * @property int|null $created_by Foreign key to the user who created the record.
 * @property int|null $updated_by Foreign key to the user who last updated the record.
 * @property int|null $deleted_by Foreign key to the user who soft deleted the record.
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Employee> $employees Employees belonging to this grade.
 * @property-read int|null $employees_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $users Users belonging to this grade.
 * @property-read int|null $users_count
 * @property-read \App\Models\User|null $createdBy The user who created the record.
 * @property-read \App\Models\User|null $deletedBy The user who soft deleted the record.
 * @property-read \App\Models\User|null $updatedBy The user who last updated the record.
 * @method static \Illuminate\Database\Eloquent\Builder|Grade newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Grade newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Grade onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Grade query()
 * @method static \Illuminate\Database\Eloquent\Builder|Grade whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Grade whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Grade whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Grade whereDeletedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Grade whereIsApproverGrade($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Grade whereLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Grade whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Grade whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Grade whereUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Grade withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Grade withoutTrashed()
 * @method static \Database\Factories\GradeFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Grade whereId($value)
 * @mixin \Eloquent
 */
class Grade extends Model
{
  // Use the traits for factory, soft deletes, and audit columns
  use CreatedUpdatedDeletedBy, HasFactory, SoftDeletes;

  /**
   * The attributes that are mass assignable.
   * 'id' is excluded as it is the primary key.
   *
   * @var array<int, string>
   */
  protected $fillable = [
    'name', // e.g., 'Grade 41', 'Grade 44', 'Grade 9'
    'level', // e.g., 41, 44, 9 (integer representation for sorting/comparison)
    'is_approver_grade', // Indicates if this grade can approve applications/workflows
    // Add any other relevant fields from your grades table, e.g., 'description' if it exists
    // 'created_by', // Handled by trait
    // 'updated_by', // Handled by trait
    // 'deleted_by', // Handled by trait
  ];

  /**
   * The attributes that should be cast.
   * Ensures data types are correct and dates are Carbon instances.
   *
   * @var array<string, string>
   */
  protected $casts = [
    'name' => 'string', // Explicitly cast name as string
    'level' => 'integer', // Ensure level is cast to an integer
    'is_approver_grade' => 'boolean', // Cast is_approver_grade to boolean

    'created_at' => 'datetime', // Explicitly cast timestamps
    'updated_at' => 'datetime',
    'deleted_at' => 'datetime', // Cast soft delete timestamp
  ];

  // The CreatedUpdatedDeletedBy trait is assumed to add these audit relationships:

  /**
   * Get the user who created the model.
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\User, \App\Models\Grade>
   */
  // public function createdBy(): BelongsToRelation;

  /**
   * Get the user who last updated the model.
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\User, \App\Models\Grade>
   */
  // public function updatedBy(): BelongsToRelation;

  /**
   * Get the user who soft deleted the model.
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\User, \App\Models\Grade>
   */
  // public function deletedBy(): BelongsToRelation;


  // 👉 Relationships

  /**
   * Get the users that belong to this grade.
   * Defines a one-to-many relationship where a Grade has many Users.
   * Assumes the 'users' table has a 'grade_id' foreign key.
   *
   * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\User>
   */
  public function users(): HasMany // Added return type hint
  {
    // Defines a one-to-many relationship with the User model
    return $this->hasMany(User::class, 'grade_id'); // Explicitly define FK for clarity
  }

  /**
   * Get the employees that belong to this grade.
   * Defines a one-to-many relationship where a Grade has many Employees.
   * Assumes the 'employees' table has a 'grade_id' foreign key.
   *
   * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\Employee>
   */
  public function employees(): HasMany // Added return type hint
  {
    // Defines a one-to-many relationship with the Employee model
    return $this->hasMany(Employee::class, 'grade_id'); // Explicitly define FK for clarity
  }

  // Add custom methods or accessors/mutators here as needed

  /**
   * Check if this grade is designated as an approver grade.
   * Directly returns the boolean value of the 'is_approver_grade' attribute after casting.
   *
   * @return bool
   */
  public function isApprover(): bool // Added return type hint
  {
    // The attribute is already cast to boolean via $casts
    return $this->is_approver_grade;
  }
}
