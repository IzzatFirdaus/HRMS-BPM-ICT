<?php

namespace App\Models;

use App\Traits\CreatedUpdatedDeletedBy; // Assuming this trait exists and adds audit FKs/methods
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany; // Use HasMany trait
use Illuminate\Database\Eloquent\Relations\BelongsTo; // Use BelongsTo trait
use Illuminate\Database\Eloquent\SoftDeletes; // Use SoftDeletes trait
use Illuminate\Database\Eloquent\Collection; // Import Collection for return type hinting
use Illuminate\Database\Eloquent\Relations\BelongsTo as BelongsToRelation; // Alias if needed
use Illuminate\Database\Eloquent\Relations\HasMany as HasManyRelation; // Alias if needed

// Import the Grade model for BelongsTo relationship
use App\Models\Grade;
// Import models for HasMany relationships (users and employees link to positions)
use App\Models\Timeline;
use App\Models\User; // User model has position_id FK
use App\Models\Employee; // Employee model has position_id FK


/**
 * App\Models\Position
 * 
 * Represents an employee position or job title.
 * Linked to Grade, Timelines, Users, and Employees.
 *
 * @property int $id
 * @property string $name The name of the position (e.g., 'Manager', 'Officer').
 * @property int|null $vacancies_count The number of vacant positions (nullable integer).
 * @property string|null $description A description of the position.
 * @property int|null $grade_id Foreign key to the associated grade.
 * @property int|null $created_by Foreign key to the user who created the record.
 * @property int|null $updated_by Foreign key to the user who last updated the record.
 * @property int|null $deleted_by Foreign key to the user who soft deleted the record.
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Employee> $employees Employees holding this position.
 * @property-read int|null $employees_count
 * @property-read \App\Models\Grade|null $grade The grade associated with this position.
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Timeline> $timelines Timelines associated with this position.
 * @property-read int|null $timelines_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $users Users holding this position.
 * @property-read int|null $users_count
 * @property-read \App\Models\User|null $createdBy The user who created the record.
 * @property-read \App\Models\User|null $deletedBy The user who soft deleted the record.
 * @property-read \App\Models\User|null $updatedBy The user who last updated the record.
 * @method static \Illuminate\Database\Eloquent\Builder|Position newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Position newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Position onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Position query()
 * @method static \Illuminate\Database\Eloquent\Builder|Position whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Position whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Position whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Position whereDeletedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Position whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Position whereGradeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Position whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Position whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Position whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Position whereUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Position whereVacanciesCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Position withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Position withoutTrashed()
 * @method static \Database\Factories\PositionFactory factory($count = null, $state = [])
 * @mixin \Eloquent
 */
class Position extends Model // This model maps to the 'positions' table
{
  // Use the traits for factory, soft deletes, and audit columns
  use CreatedUpdatedDeletedBy, HasFactory, SoftDeletes;

  // 👇 REMOVED or CORRECTED: Specify the table name if it's not the plural of the model name
  // If your table is indeed named 'positions', you can remove this line.
  // If it's named something else, like 'designations', uncomment and set it:
  // protected $table = 'designations';


  /**
   * The attributes that are mass assignable.
   * 'id' is excluded as it is the primary key.
   *
   * @var array<int, string>
   */
  protected $fillable = [
    'name', // The name of the position
    'vacancies_count', // The number of vacant positions
    'description', // A description of the position
    'grade_id', // Foreign key to the associated grade
    // 'created_by', // Handled by trait
    // 'updated_by', // Handled by trait
    // 'deleted_by', // Handled by trait
  ];

  /**
   * The attributes that should be cast.
   * Ensures data types are correct and dates are Carbon instances.
   * Includes casts for strings, integers, timestamps, and soft deletes.
   * Explicitly casting all attributes for clarity.
   *
   * @var array<string, string>
   */
  protected $casts = [
    'name' => 'string', // Explicitly cast name as string
    'description' => 'string', // Explicitly cast description as string
    'vacancies_count' => 'integer', // Cast nullable integer
    'grade_id' => 'integer', // Cast nullable integer foreign key

    'created_at' => 'datetime', // Explicitly cast timestamps
    'updated_at' => 'datetime',
    'deleted_at' => 'datetime', // Cast soft delete timestamp
  ];


  // The CreatedUpdatedDeletedBy trait is assumed to add these audit relationships:

  /**
   * Get the user who created the model.
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\User, \App\Models\Position>
   */
  // public function createdBy(): BelongsToRelation;

  /**
   * Get the user who last updated the model.
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\User, \App\Models\Position>
   */
  // public function updatedBy(): BelongsToRelation;

  /**
   * Get the user who soft deleted the model.
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\User, \App\Models\Position>
   */
  // public function deletedBy(): BelongsToRelation;


  // 👉 Relationships

  /**
   * Get the timelines associated with the position.
   * Defines a one-to-many relationship where a Position has many Timelines.
   * Assumes the 'timelines' table has a 'position_id' foreign key.
   *
   * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\Timeline>
   */
  public function timelines(): HasMany // Added return type hint
  {
    // Defines a one-to-many relationship with the Timeline model
    return $this->hasMany(Timeline::class, 'position_id'); // Explicitly define FK for clarity
  }

  /**
   * Get the users that belong to this position.
   * Defines a one-to-many relationship where a Position has many Users.
   * Assumes the 'users' table has a 'position_id' foreign key.
   *
   * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\User>
   */
  public function users(): HasMany // Added return type hint
  {
    // Defines a one-to-many relationship with the User model
    return $this->hasMany(User::class, 'position_id'); // Explicitly define FK for clarity
  }

  /**
   * Get the employees that belong to this position.
   * Defines a one-to-many relationship where a Position has many Employees.
   * Assumes the 'employees' table has a 'position_id' foreign key.
   *
   * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\Employee>
   */
  public function employees(): HasMany // Added return type hint
  {
    // Defines a one-to-many relationship with the Employee model
    return $this->hasMany(Employee::class, 'position_id'); // Explicitly define FK for clarity
  }


  /**
   * Get the grade associated with the position.
   * Defines a many-to-one relationship where a Position belongs to one Grade.
   * Assumes the 'positions' table has a 'grade_id' foreign key.
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Grade, \App\Models\Position>
   */
  public function grade(): BelongsTo // Added return type hint
  {
    // Defines a many-to-one relationship with the Grade model
    return $this->belongsTo(Grade::class, 'grade_id'); // Explicitly define FK for clarity
  }


  // 👉 Attributes (Accessors/Mutators)

  /**
   * Get or set the position name.
   * Applies ucfirst mutation on setting.
   * Note: Be cautious with this if you have a unique constraint on the 'name' column
   * and names like 'Manager' and 'manager' should be considered unique by the database.
   *
   * @return \Illuminate\Database\Eloquent\Casts\Attribute
   */
  protected function name(): Attribute // Added return type hint and refined docblock
  {
    return Attribute::make(
      // get: fn (string $value) => $value, // Accessor not needed if just returning the value
      set: fn(string $value) => ucfirst($value), // Mutator to store as provided, applying ucfirst
    );
  }

  // Add any other relationships or methods below this line
}
