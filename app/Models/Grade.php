<?php

namespace App\Models; // Ensure the namespace is correct for your project

use App\Traits\CreatedUpdatedDeletedBy; // Assuming this trait exists and adds audit FKs/methods like created_by, updated_by, deleted_by
use Carbon\Carbon; // Assuming Carbon is used implicitly with date casts
use Illuminate\Database\Eloquent\Factories\HasFactory; // Import HasFactory trait
use Illuminate\Database\Eloquent\Model; // Import base Model class
use Illuminate\Database\Eloquent\Relations\BelongsTo; // Import BelongsTo relationship type
use Illuminate\Database\Eloquent\Relations\HasMany; // Import HasMany relationship type
use Illuminate\Database\Eloquent\SoftDeletes; // Import SoftDeletes trait
// Removed unused aliased import: HasManyRelation
// Removed unused aliased import: BelongsToRelation // Although BelongsTo is used directly


// Import models for relationships (Eloquent needs to know about the related models for relationships)
use App\Models\User; // Grade hasMany Users
use App\Models\Employee; // Grade hasMany Employees
// Self-referencing relationship: Grade relates to Grade via min_approval_grade_id


/**
 * App\Models\Grade
<<<<<<< HEAD
 *
 * Represents an employee grade level (e.g., '41', '44', 'N19').
 * Stores grade name, numerical level, and indicates if the grade is designated as an approver grade.
 * Linked to User and Employee models via one-to-many relationships.
 * Includes a self-referencing relationship to track minimum approval grades.
 * Includes audit trails and soft deletion.
=======
 * 
 * Represents an employee grade (e.g., Grade 41, Grade 44) and tracks if it's an approver grade.
 * Linked to User and Employee models.
>>>>>>> cc6eb9f4f020325c04fee080d2466584ff27bb90
 *
 * @property int $id
 * @property string $name The name of the grade (e.g., 'Grade 41', 'PTD 48').
 * @property int $level The numerical level of the grade (e.g., 41, 48, 19) for sorting or comparison.
 * @property bool $is_approver_grade Indicates if this grade is designated as an approver grade (e.g., Grade 41+).
 * @property int|null $min_approval_grade_id Foreign key to the Grade model representing the minimum grade required to approve items for this grade level (self-referencing, nullable).
 * @property int|null $created_by Foreign key to the user who created the record.
 * @property int|null $updated_by Foreign key to the user who last updated the record.
 * @property int|null $deleted_by Foreign key to the user who soft deleted the record.
 * @property \Illuminate\Support\Carbon|null $created_at Automatically managed timestamp for creation.
 * @property \Illuminate\Support\Carbon|null $updated_at Automatically managed timestamp for last update.
 * @property \Illuminate\Support\Carbon|null $deleted_at Timestamp for soft deletion.
 *
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Employee> $employees Employees belonging to this grade.
 * @property-read int|null $employees_count Count of employees in this grade.
 * @property-read \App\Models\Grade|null $minApprovalGrade The minimum approval grade required for this grade level (self-referencing).
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $users Users belonging to this grade.
 * @property-read int|null $users_count Count of users in this grade.
 * @property-read \App\Models\User|null $createdBy The user who created the record (if trait adds this).
 * @property-read \App\Models\User|null $deletedBy The user who soft deleted the record (if trait adds this).
 * @property-read \App\Models\User|null $updatedBy The user who last updated the record (if trait adds this).
 *
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
 * @method static \Illuminate\Database\Eloquent\Builder|Grade whereMinApprovalGradeId($value)
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
  // HasFactory is for model factories.
  // SoftDeletes adds the 'deleted_at' column and scope.
  // CreatedUpdatedDeletedBy is assumed to add 'created_by', 'updated_by', 'deleted_by' columns and relationships.
  use CreatedUpdatedDeletedBy, HasFactory, SoftDeletes;

  /**
   * The table associated with the model.
   *
   * @var string
   */
  // protected $table = 'grades'; // Explicitly define table name if it's not the plural of the model name


  /**
   * The attributes that are mass assignable.
   * Allows these attributes to be set using mass assignment (e.g., during create or update).
   * 'id' is excluded as it is the primary key.
   *
   * @var array<int, string>
   */
  protected $fillable = [
    'name',            // Name of the grade (e.g., 'Grade 41')
    'level',           // Numerical level of the grade (integer)
    'is_approver_grade', // Boolean indicating if this grade can approve
    'min_approval_grade_id', // Foreign key for self-referencing min approval grade (nullable)
    // Add any other relevant fields from your grades table, e.g., 'description' if it exists

    // The CreatedUpdatedDeletedBy trait is assumed to handle these audit columns:
    // 'created_by',
    // 'updated_by',
    // 'deleted_by',
  ];

  /**
   * The attributes that should be cast.
   * Ensures data types are correct and dates are Carbon instances.
   * Explicitly casting all attributes for clarity.
   *
   * @var array<string, string>
   */
  protected $casts = [
    'name' => 'string',          // Explicitly cast name as string
    'level' => 'integer',        // Ensure level is cast to an integer
    'is_approver_grade' => 'boolean', // Cast is_approver_grade to boolean
    'min_approval_grade_id' => 'integer', // Cast foreign key to integer (nullable foreign keys will be null if not set)


    // Standard Eloquent timestamps
    'created_at' => 'datetime',  // Explicitly cast creation timestamp to Carbon instance
    'updated_at' => 'datetime',  // Explicitly cast update timestamp to Carbon instance
    'deleted_at' => 'datetime',  // Cast soft delete timestamp to Carbon instance
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

  /**
   * Get the user who created the model.
   * Assumes the 'created_by' foreign key exists and links to the 'users' table.
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\User, \App\Models\Grade>
   */
  // public function createdBy(): BelongsTo;

  /**
   * Get the user who last updated the model.
   * Assumes the 'updated_by' foreign key exists and links to the 'users' table.
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\User, \App\Models\Grade>
   */
  // public function updatedBy(): BelongsTo;

  /**
   * Get the user who soft deleted the model.
   * Assumes the 'deleted_by' foreign key exists and links to the 'users' table.
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\User, \App\Models\Grade>
   */
  // public function deletedBy(): BelongsTo;


  // 👉 Relationships

  /**
   * Get the users that belong to this grade.
   * Defines a one-to-many relationship where a Grade has many Users.
   * Assumes the 'users' table has a 'grade_id' foreign key that
   * references the 'grades' table's primary key ('id').
   *
   * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\User>
   */
  public function users(): HasMany // Added return type hint
  {
    // Defines a one-to-many relationship with the User model.
    // 'User::class' is the related model.
    // 'grade_id' is the foreign key on the 'users' table.
    // 'id' is the local key on the 'grades' table (default, can be omitted).
    return $this->hasMany(User::class, 'grade_id');
    // If using the standard foreign key name, this is sufficient: return $this->hasMany(User::class);
  }

  /**
   * Get the employees that belong to this grade.
   * Defines a one-to-many relationship where a Grade has many Employees.
   * Assumes the 'employees' table has a 'grade_id' foreign key that
   * references the 'grades' table's primary key ('id').
   *
   * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\Employee>
   */
  public function employees(): HasMany // Added return type hint
  {
    // Defines a one-to-many relationship with the Employee model.
    // 'Employee::class' is the related model.
    // 'grade_id' is the foreign key on the 'employees' table.
    // 'id' is the local key on the 'grades' table (default, can be omitted).
    return $this->hasMany(Employee::class, 'grade_id');
    // If using the standard foreign key name, this is sufficient: return $this->hasMany(Employee::class);
  }

  /**
   * Get the minimum approval grade required for this grade level.
   * Defines a many-to-one self-referencing relationship where a Grade
   * belongs to a minimum approval Grade via the 'min_approval_grade_id' foreign key.
   * This relationship is nullable.
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Grade, \App\Models\Grade>
   */
  public function minApprovalGrade(): BelongsTo // Added self-referencing relationship
  {
    // Defines a many-to-one relationship with the Grade model itself.
    // 'Grade::class' is the related model (itself).
    // 'min_approval_grade_id' is the foreign key on the 'grades' table.
    // 'id' is the local key on the 'grades' table (default, can be omitted).
    return $this->belongsTo(Grade::class, 'min_approval_grade_id');
  }


  // 👉 Helper Methods

  /**
   * Check if this grade is designated as an approver grade.
   * Directly returns the boolean value of the 'is_approver_grade' attribute after casting.
   *
   * @return bool True if this grade can approve, false otherwise.
   */
  public function isApprover(): bool // Added return type hint
  {
    // The attribute is already cast to boolean via $casts
    return $this->is_approver_grade;
  }

  // Add custom methods or accessors/mutators here as needed

  // Example: Accessor to get the formatted grade name (optional)
  // use Illuminate\Database\Eloquent\Casts\Attribute; // Import Attribute if needed
  // protected function nameFormatted(): Attribute
  // {
  //     return Attribute::make(
  //         get: fn (string $value) => 'Gred ' . $value, // Add "Gred " prefix
  //     );
  // }
}
