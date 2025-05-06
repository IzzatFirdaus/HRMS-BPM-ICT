<?php

namespace App\Models; // Ensure the namespace is correct for your project

use App\Traits\CreatedUpdatedDeletedBy; // Assuming this trait exists and adds audit FKs/methods
use Carbon\Carbon; // Import Carbon if needed for date/time handling (used implicitly by 'datetime' cast)
use Illuminate\Database\Eloquent\Casts\Attribute; // Import Attribute for modern accessors/mutators
use Illuminate\Database\Eloquent\Factories\HasFactory; // Import HasFactory trait
use Illuminate\Database\Eloquent\Model; // Import base Model class
use Illuminate\Database\Eloquent\Relations\HasMany; // Import HasMany relationship type
use Illuminate\Database\Eloquent\SoftDeletes; // Import SoftDeletes trait
use Illuminate\Database\Eloquent\Relations\BelongsTo; // Import BelongsTo relationship type (for relationships potentially added by the trait)
use Illuminate\Database\Eloquent\Builder; // Import Builder for scope type hinting


// Import models for relationships (Eloquent needs to know about the related models for relationships)
use App\Models\Timeline; // Department has many Timelines (assuming FK department_id on timelines table)
use App\Models\User; // Department has many Users (assuming FK department_id on users table)
use App\Models\Employee; // Department has many Employees (assuming FK department_id on employees table)


/**
 * App\Models\Department
 *
 * Represents an organizational department or unit within MOTAC.
 * Linked to Timelines, Users, and Employees via one-to-many relationships.
 * Includes audit trails and soft deletion.
 *
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property int|null $head_of_department_id Foreign key to User or Employee (optional).
 * @property bool $is_active
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property int|null $deleted_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Timeline> $timelines The timelines associated with this department.
 * @property-read \Illuminate\Database\Eloquent\Collection<int, User> $users The users associated with this department.
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Employee> $employees The employees associated with this department.
 * @property-read User|null $creator
 * @property-read User|null $editor
 * @property-read User|null $deleter
 */
class Department extends Model
{
  use HasFactory, SoftDeletes, CreatedUpdatedDeletedBy;

  protected $fillable = [
    'name',
    'description',
    'head_of_department_id', // Assuming this column exists
    'is_active',
    // created_by, updated_by, deleted_by handled by trait
  ];

  protected $casts = [
    'is_active' => 'boolean',
    'head_of_department_id' => 'integer',
    'created_at' => 'datetime',
    'updated_at' => 'datetime',
    'deleted_at' => 'datetime',
    'name' => 'string',
    'description' => 'string',
  ];

  // If CreatedUpdatedDeletedBy trait is used, ensure these columns are fillable or handled by the trait
  // protected $guarded = []; // Or use guarded instead of fillable


  // --- Relationships ---

  /**
   * Get the timelines associated with this department.
   * Defines a one-to-many relationship.
   * Assumes 'timelines' table has 'department_id' foreign key.
   *
   * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\Timeline>
   */
  public function timelines(): HasMany // Added return type hint
  {
    return $this->hasMany(Timeline::class, 'department_id');
  }

  /**
   * Get the users associated with this department.
   * Defines a one-to-many relationship.
   * Assumes 'users' table has 'department_id' foreign key.
   * Note: User might also be linked via Employee. Review schema for primary linkage.
   *
   * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\User>
   */
  public function users(): HasMany // Added return type hint
  {
    // Assuming 'users' table has 'department_id' foreign key
    return $this->hasMany(User::class, 'department_id');
  }

  /**
   * Get the employees associated with this department.
   * Defines a one-to-many relationship.
   * Assumes 'employees' table has 'department_id' foreign key.
   *
   * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\Employee>
   */
  public function employees(): HasMany // Added return type hint
  {
    return $this->hasMany(Employee::class, 'department_id');
  }

  /**
   * Get the head of department user.
   * Defines a many-to-one relationship (nullable).
   * Assumes 'departments' table has 'head_of_department_id' foreign key.
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\User, \App\Models\Department>
   */
  public function headOfDepartment(): BelongsTo // Added return type hint
  {
    return $this->belongsTo(User::class, 'head_of_department_id');
  }


  // 👉 Attributes (Accessors/Mutators)

  /**
   * Get or set the department's name.
   * Applies ucfirst mutation on setting for consistent capitalization.
   *
   * @return \Illuminate\Database\Eloquent\Casts\Attribute
   */
  protected function name(): Attribute // Added return type hint
  {
    return Attribute::make(
      // Mutator: Apply ucfirst when the name attribute is set before saving to the database
      set: fn(string $value) => ucfirst($value), // Capitalize the first letter
    );
    // Be cautious with using mutators like ucfirst() if you have a unique constraint
    // on the 'name' column and names like 'IT' and 'it' should be considered unique by the database.
  }

  // --- Scopes ---

  /**
   * Scope to include active departments.
   *
   * @param  \Illuminate\Database\Eloquent\Builder  $query
   * @return \Illuminate\Database\Eloquent\Builder
   */
  public function scopeActive(Builder $query): Builder // Added return type hint
  {
    return $query->where('is_active', true);
  }


  // Add any other relationships, accessors, mutators, or methods below this line
}
