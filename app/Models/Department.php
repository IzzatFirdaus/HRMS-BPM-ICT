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
 * @property string $name The name of the department (e.g., 'Bahagian Pengurusan Maklumat').
 * @property string|null $description A description of the department or its functions.
 * @property int|null $created_by Foreign key to the user who created the record.
 * @property int|null $updated_by Foreign key to the user who last updated the record.
 * @property int|null $deleted_by Foreign key to the user who soft deleted the record.
 * @property \Illuminate\Support\Carbon|null $created_at Automatically managed timestamp for creation.
 * @property \Illuminate\Support\Carbon|null $updated_at Automatically managed timestamp for last update.
 * @property \Illuminate\Support\Carbon|null $deleted_at Timestamp for soft deletion.
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Employee> $employees The employees belonging to this department.
 * @property-read int|null $employees_count Count of employees in this department.
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Timeline> $timelines The timelines associated with this department.
 * @property-read int|null $timelines_count Count of timelines for this department.
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $users The users belonging to this department.
 * @property-read int|null $users_count Count of users in this department.
 * @property-read \App\Models\User|null $createdBy Relation to the user who created the record (if trait adds this).
 * @property-read \App\Models\User|null $deletedBy Relation to the user who soft deleted the record (if trait adds this).
 * @property-read \App\Models\User|null $updatedBy Relation to the user who last updated the record (if trait adds this).
 *
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
  // HasFactory is for model factories.
  // SoftDeletes adds the 'deleted_at' column and scope.
  // CreatedUpdatedDeletedBy is assumed to add 'created_by', 'updated_by', 'deleted_by' columns and relationships.
  use CreatedUpdatedDeletedBy, HasFactory, SoftDeletes;

  /**
   * The table associated with the model.
   *
   * @var string
   */
  // protected $table = 'departments'; // Explicitly define table name if it's not the plural of the model name


  /**
   * The attributes that are mass assignable.
   * Allows these attributes to be set using mass assignment (e.g., during create or update).
   * 'id' is excluded as it is the primary key.
   *
   * @var array<int, string>
   */
  protected $fillable = [
    'name',        // Name of the department
    'description', // Description of the department (ensure this column exists in your migration and is nullable if applicable)
    // Add other fillable attributes here if necessary (e.g., 'code', 'head_of_department_id')

    // The CreatedUpdatedDeletedBy trait is assumed to handle these audit columns:
    // 'created_by',
    // 'updated_by',
    // 'deleted_by',
  ];

  /**
   * The attributes that should be cast.
   * Ensures data types are correct and dates are Carbon instances.
   *
   * @var array<string, string>
   */
  protected $casts = [
    'name' => 'string',        // Explicitly cast name as string
    'description' => 'string', // Explicitly cast description as string (or 'text' if database column is TEXT, 'string' still works but 'text' is more explicit)
    'created_at' => 'datetime', // Explicitly cast creation timestamp
    'updated_at' => 'datetime', // Explicitly cast update timestamp
    'deleted_at' => 'datetime', // Cast soft delete timestamp
    // Add casts for other attributes if needed
  ];

  /**
   * The attributes that should be hidden for serialization.
   * (Optional) Prevents sensitive attributes from being returned in JSON responses.
   *
   * @var array<int, string>
   */
  // protected $hidden = [
  //     'created_by', // Example: hide audit columns from API responses
  //     'updated_by',
  //     'deleted_by',
  // ];


  // The CreatedUpdatedDeletedBy trait is assumed to add these audit relationships:
  // public function createdBy(): BelongsTo; // Relationship to the user who created the record
  // public function updatedBy(): BelongsTo; // Relationship to the user who last updated the record
  // public function deletedBy(): BelongsTo; // Relationship to the user who soft deleted the record


  // 👉 Relationships

  /**
   * Get the timelines associated with the department.
   * Defines a one-to-many relationship where a Department has many Timelines.
   * This relationship is likely used if timeline entries are tied to the
   * department in which an action occurred or an employee was assigned at that time.
   * Assumes the 'timelines' table has a 'department_id' foreign key that
   * references the 'departments' table's primary key ('id').
   *
   * @return \Illuminate\Database\Eloquent\Relations\HasMany
   */
  public function timelines(): HasMany // Added return type hint
  {
    // Defines a one-to-many relationship with the Timeline model.
    // 'Timeline::class' is the related model.
    // 'department_id' is the foreign key on the 'timelines' table.
    // 'id' is the local key on the 'departments' table (default, can be omitted).
    return $this->hasMany(Timeline::class, 'department_id');
    // If using the standard foreign key name, this is sufficient: return $this->hasMany(Timeline::class);
  }

  /**
   * Get the users associated with the department.
   * Defines a one-to-many relationship where a Department has many Users.
   * This is the standard way to link users to their primary department.
   * Assumes the 'users' table has a 'department_id' foreign key that
   * references the 'departments' table's primary key ('id').
   *
   * @return \Illuminate\Database\Eloquent\Relations\HasMany
   */
  public function users(): HasMany // Added return type hint
  {
    // Defines a one-to-many relationship with the User model.
    // 'User::class' is the related model.
    // 'department_id' is the foreign key on the 'users' table.
    // 'id' is the local key on the 'departments' table (default, can be omitted).
    return $this->hasMany(User::class, 'department_id');
    // If using the standard foreign key name, this is sufficient: return $this->hasMany(User::class);
  }

  /**
   * Get the employees associated with the department.
   * Defines a one-to-many relationship where a Department has many Employees.
   * This is the standard way to link employees to their primary department.
   * Note: This might overlap with the 'timelines' relationship if employee assignment is *only* tracked via timelines.
   * Assumes the 'employees' table has a 'department_id' foreign key that
   * references the 'departments' table's primary key ('id').
   *
   * @return \Illuminate\Database\Eloquent\Relations\HasMany
   */
  public function employees(): HasMany // Added return type hint
  {
    // Defines a one-to-many relationship with the Employee model.
    // 'Employee::class' is the related model.
    // 'department_id' is the foreign key on the 'employees' table.
    // 'id' is the local key on the 'departments' table (default, can be omitted).
    return $this->hasMany(Employee::class, 'department_id');
    // If using the standard foreign key name, this is sufficient: return $this->hasMany(Employee::class);
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
      // Accessor: Optional - define custom retrieval logic if needed
      // get: fn (string $value) => $value, // Returning the raw value is default

      // Mutator: Apply ucfirst when the name attribute is set before saving to the database
      set: fn(string $value) => ucfirst($value), // Capitalize the first letter
    );
    // Be cautious with using mutators like ucfirst() if you have a unique constraint
    // on the 'name' column and names like 'IT' and 'it' should be considered unique by the database.
    // Database-level constraints might interact differently with mutated values.
  }

  // Add any other relationships, accessors, mutators, or methods below this line
}
