<?php

namespace App\Models; // Ensure the namespace is correct for your project

use App\Traits\CreatedUpdatedDeletedBy; // Assuming this trait exists and adds audit FKs/methods
use Illuminate\Database\Eloquent\Casts\Attribute; // Import Attribute for modern accessors/mutators
use Illuminate\Database\Eloquent\Factories\HasFactory; // Import HasFactory trait
use Illuminate\Database\Eloquent\Model; // Import base Model class
use Illuminate\Database\Eloquent\Relations\BelongsTo; // Import BelongsTo relationship type
use Illuminate\Database\Eloquent\Relations\HasMany; // Import HasMany relationship type
use Illuminate\Database\Eloquent\SoftDeletes; // Import SoftDeletes trait
use Illuminate\Database\Eloquent\Collection; // Import Collection for return type hinting
// Removed unused aliased imports: BelongsToRelation, HasManyRelation
// Removed unused import: use Illuminate\Support\Carbon; // Carbon is used by date casts implicitly, but not directly called in this code. Keeping it is fine, but not strictly necessary.

// Import models for relationships (Eloquent needs to know about the related models)
use App\Models\Grade; // Position belongsTo Grade
use App\Models\Timeline; // Position hasMany Timelines
use App\Models\User; // Position hasMany Users
use App\Models\Employee; // Position hasMany Employees
use App\Models\User as UserModel; // Alias for User model in BelongsTo relationships from CreatedUpdatedDeletedBy trait (example)


/**
 * App\Models\Position
<<<<<<< HEAD
 *
 * Represents an employee position or job title within the organizational structure.
 * Stores the position name, vacancy count, description, and links to an associated grade.
 * Also tracks which Timelines, Users, and Employees are linked to this position.
 * Includes audit trails and soft deletion.
=======
 * 
 * Represents an employee position or job title.
 * Linked to Grade, Timelines, Users, and Employees.
>>>>>>> cc6eb9f4f020325c04fee080d2466584ff27bb90
 *
 * @property int $id
 * @property string $name The name of the position (e.g., 'Manager', 'Assistant Director', 'Clerk').
 * @property int|null $vacancies_count The number of vacant positions currently open (nullable integer).
 * @property string|null $description A detailed description of the position's roles and responsibilities (nullable).
 * @property int|null $grade_id Foreign key to the associated grade level ('grades' table, nullable).
 * @property int|null $created_by Foreign key to the user who created the record.
 * @property int|null $updated_by Foreign key to the user who last updated the record.
 * @property int|null $deleted_by Foreign key to the user who soft deleted the record.
 * @property \Illuminate\Support\Carbon|null $created_at Automatically managed timestamp for creation.
 * @property \Illuminate\Support\Carbon|null $updated_at Automatically managed timestamp for last update.
 * @property \Illuminate\Support\Carbon|null $deleted_at Timestamp for soft deletion.
 *
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Employee> $employees Employees currently holding this position.
 * @property-read int|null $employees_count Count of employees in this position.
 * @property-read \App\Models\Grade|null $grade The grade level associated with this position.
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Timeline> $timelines Timeline entries associated with this position (historical assignments).
 * @property-read int|null $timelines_count Count of associated timeline entries.
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $users Users currently holding this position (user accounts linked to this position).
 * @property-read int|null $users_count Count of users in this position.
 * @property-read \App\Models\User|null $createdBy The user who created the record (if trait adds this).
 * @property-read \App\Models\User|null $deletedBy The user who soft deleted the record (if trait adds this).
 * @property-read \App\Models\User|null $updatedBy The user who last updated the record (if trait adds this).
 *
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
  // HasFactory is for model factories.
  // SoftDeletes adds the 'deleted_at' column and scope.
  // CreatedUpdatedDeletedBy is assumed to add 'created_by', 'updated_by', 'deleted_by' columns and relationships.
  use CreatedUpdatedDeletedBy, HasFactory, SoftDeletes;

  /**
   * The table associated with the model.
   *
   * @var string
   */
  // protected $table = 'positions'; // Explicitly define table name if it's not the plural of the model name ('positions')


  /**
   * The attributes that are mass assignable.
   * Allows these attributes to be set using mass assignment (e.g., during create or update).
   * 'id' is excluded as it is the primary key.
   *
   * @var array<int, string>
   */
  protected $fillable = [
    'name',            // The name of the position (required)
    'vacancies_count', // The number of vacant positions (nullable integer)
    'description',     // A description of the position (nullable text field)
    'grade_id',        // Foreign key to the associated grade (nullable integer)

    // The CreatedUpdatedDeletedBy trait is assumed to handle these audit columns:
    // 'created_by',
    // 'updated_by',
    // 'deleted_by',
  ];

  /**
   * The attributes that should be cast.
   * Ensures data types are correct and dates are Carbon instances.
   * Includes casts for strings, integers, timestamps, and soft deletes.
   * Explicitly casting all attributes for clarity and type safety.
   *
   * @var array<string, string>
   */
  protected $casts = [
    'name' => 'string',            // Explicitly cast name as string
    'description' => 'string',     // Explicitly cast description as string
    'vacancies_count' => 'integer', // Cast nullable integer
    'grade_id' => 'integer',       // Cast nullable integer foreign key

    // Standard Eloquent timestamps handled by base model and traits
    'created_at' => 'datetime',    // Explicitly cast creation timestamp to Carbon instance
    'updated_at' => 'datetime',    // Explicitly cast update timestamp to Carbon instance
    'deleted_at' => 'datetime',    // Cast soft delete timestamp to Carbon instance
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
  //     'deleted_at', // Hide soft delete timestamp
  // ];


  // The CreatedUpdatedDeletedBy trait is assumed to add these audit relationships.
  // Their docblocks are included in the main class docblock above for clarity.
  // Example docblocks added by the trait:
  /*
        * Get the user who created the model.
        * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\User, \App\Models\Position>
        */
  // public function createdBy(): BelongsTo;

  /*
        * Get the user who last updated the model.
        * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\User, \App\Models\Position>
        */
  // public function updatedBy(): BelongsTo;

  /*
        * Get the user who soft deleted the model.
        * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\User, \App\Models\Position>
        */
  // public function deletedBy(): BelongsTo;


  // 👉 Relationships

  /**
   * Get the timelines associated with the position.
   * Defines a one-to-many relationship where a Position has many Timelines.
   * This tracks the historical assignments of employees to this position over time.
   * Assumes the 'timelines' table has a 'position_id' foreign key that
   * references the 'positions' table's primary key ('id').
   *
   * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\Timeline>
   */
  public function timelines(): HasMany // Added return type hint
  {
    // Defines a one-to-many relationship with the Timeline model.
    // 'Timeline::class' is the related model.
    // 'position_id' is the foreign key on the 'timelines' table.
    // 'id' is the local key on the 'positions' table (default, can be omitted).
    return $this->hasMany(Timeline::class, 'position_id'); // Explicitly define FK for clarity
    // If using the standard foreign key name, this is sufficient: return $this->hasMany(Timeline::class);
  }

  /**
   * Get the users that belong to this position.
   * Defines a one-to-many relationship where a Position has many Users.
   * This links user accounts to their current position.
   * Assumes the 'users' table has a 'position_id' foreign key that
   * references the 'positions' table's primary key ('id').
   *
   * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\User>
   */
  public function users(): HasMany // Added return type hint
  {
    // Defines a one-to-many relationship with the User model.
    // 'User::class' is the related model.
    // 'position_id' is the foreign key on the 'users' table.
    // 'id' is the local key on the 'positions' table (default, can be omitted).
    return $this->hasMany(User::class, 'position_id'); // Explicitly define FK for clarity
    // If using the standard foreign key name, this is sufficient: return $this->hasMany(User::class);
  }

  /**
   * Get the employees that belong to this position.
   * Defines a one-to-many relationship where a Position has many Employees.
   * This links employee records to their current position.
   * Assumes the 'employees' table has a 'position_id' foreign key that
   * references the 'positions' table's primary key ('id').
   *
   * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\Employee>
   */
  public function employees(): HasMany // Added return type hint
  {
    // Defines a one-to-many relationship with the Employee model.
    // 'Employee::class' is the related model.
    // 'position_id' is the foreign key on the 'employees' table.
    // 'id' is the local key on the 'positions' table (default, can be omitted).
    return $this->hasMany(Employee::class, 'position_id'); // Explicitly define FK for clarity
    // If using the standard foreign key name, this is sufficient: return $this->hasMany(Employee::class);
  }


  /**
   * Get the grade associated with the position.
   * Defines a many-to-one relationship where a Position belongs to one Grade.
   * Assumes the 'positions' table has a 'grade_id' foreign key that
   * references the 'grades' table's primary key ('id'). This relationship is nullable.
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Grade, \App\Models\Position>
   */
  public function grade(): BelongsTo // Added return type hint
  {
    // Defines a many-to-one relationship with the Grade model.
    // 'Grade::class' is the related model.
    // 'grade_id' is the foreign key on the 'positions' table.
    // 'id' is the local key on the 'grades' table (default, can be omitted).
    return $this->belongsTo(Grade::class, 'grade_id'); // Explicitly define FK for clarity
    // If using the standard foreign key name, this is sufficient: return $this->belongsTo(Grade::class);
  }


  // 👉 Attributes (Accessors/Mutators using Attributes)

  /**
   * Accessor/Mutator for the position name.
   * The mutator automatically applies `ucfirst` to the name before saving.
   * Note: Be cautious with this if you have a unique constraint on the 'name' column
   * and names like 'Manager' and 'manager' should be considered unique by the database.
   *
   * @return \Illuminate\Database\Eloquent\Casts\Attribute
   */
  protected function name(): Attribute // Added return type hint and refined docblock
  {
    return Attribute::make(
      // Accessor: Simply return the stored value (ucfirst applied by mutator on save)
      get: fn(string $value) => $value,
      // Mutator: Apply ucfirst to the value before storing
      set: fn(string $value) => ucfirst($value),
    );
  }

  // Add any other relationships, methods, scopes, or accessors/mutators below this line
}
