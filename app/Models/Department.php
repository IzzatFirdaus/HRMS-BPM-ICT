<?php

namespace App\Models;

use App\Traits\CreatedUpdatedDeletedBy; // Assuming this trait exists and adds audit FKs/methods
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes; // Use SoftDeletes trait
use Illuminate\Database\Eloquent\Relations\BelongsTo; // Import BelongsTo for trait relationships

// Import models for HasMany relationships
use App\Models\Timeline;
use App\Models\User; // User model has department_id FK
use App\Models\Employee; // Employee model has department_id FK


/**
 * App\Models\Department
 *
 * @property int $id
 * @property string $name The name of the department.
 * @property string|null $description A description of the department.
 * @property int|null $created_by Foreign key to the user who created the record.
 * @property int|null $updated_by Foreign key to the user who last updated the record.
 * @property int|null $deleted_by Foreign key to the user who soft deleted the record.
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Employee> $employees
 * @property-read int|null $employees_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Timeline> $timelines
 * @property-read int|null $timelines_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $users
 * @property-read int|null $users_count
 * @property-read \App\Models\User|null $createdBy
 * @property-read \App\Models\User|null $deletedBy
 * @property-read \App\Models\User|null $updatedBy
 * @method static \Illuminate\Database\Eloquent\Builder|Department newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Department newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Department onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Department query()
 * @method static \Illuminate\Database\Eloquent\Builder|Department whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Department whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Department whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Department whereDeletedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Department whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Department whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Department whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Department whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Department whereUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Department withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Department withoutTrashed()
 * @mixin \Eloquent
 */
class Department extends Model
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
    'name',
    'description', // ADDED: 'description' column from the migration
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
    'description' => 'string', // Explicitly cast description as string (or 'text' if database column is TEXT)
    'created_at' => 'datetime', // Explicitly cast timestamps
    'updated_at' => 'datetime',
    'deleted_at' => 'datetime', // Cast soft delete timestamp
  ];


  // The CreatedUpdatedDeletedBy trait is assumed to add these audit relationships:
  // public function createdBy(): BelongsTo;
  // public function updatedBy(): BelongsTo;
  // public function deletedBy(): BelongsTo;


  // 👉 Relationships

  /**
   * Get the timelines associated with the department.
   * Defines a one-to-many relationship where a Department has many Timelines.
   * Assumes the 'timelines' table has a 'department_id' foreign key.
   *
   * @return \Illuminate\Database\Eloquent\Relations\HasMany
   */
  public function timelines(): HasMany // Added return type hint
  {
    // Defines a one-to-many relationship with the Timeline model
    return $this->hasMany(Timeline::class, 'department_id'); // Explicitly define FK for clarity
  }

  /**
   * Get the users associated with the department.
   * Defines a one-to-many relationship where a Department has many Users.
   * Assumes the 'users' table has a 'department_id' foreign key.
   *
   * @return \Illuminate\Database\Eloquent\Relations\HasMany
   */
  public function users(): HasMany // Added return type hint
  {
    // Defines a one-to-many relationship with the User model
    return $this->hasMany(User::class, 'department_id'); // Explicitly define FK for clarity
  }

  /**
   * Get the employees associated with the department.
   * Defines a one-to-many relationship where a Department has many Employees.
   * Assumes the 'employees' table has a 'department_id' foreign key.
   *
   * @return \Illuminate\Database\Eloquent\Relations\HasMany
   */
  public function employees(): HasMany // Added return type hint
  {
    // Defines a one-to-many relationship with the Employee model
    return $this->hasMany(Employee::class, 'department_id'); // Explicitly define FK for clarity
  }


  // 👉 Attributes (Accessors/Mutators)

  /**
   * Get or set the department's name.
   * Applies ucfirst mutation on setting.
   *
   * @return \Illuminate\Database\Eloquent\Casts\Attribute
   */
  protected function name(): Attribute // Added return type hint
  {
    return Attribute::make(
      // get: fn (string $value) => $value, // Accessor not needed if just returning the value
      set: fn(string $value) => ucfirst($value), // Mutator to store as provided, applying ucfirst
    );
    // Be cautious with this if you have a unique constraint on the 'name' column
    // and names like 'it' and 'It' should be considered unique by the database.
  }

  // Add any other relationships or methods below this line
}
