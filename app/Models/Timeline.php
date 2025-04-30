<?php

namespace App\Models;

use App\Traits\CreatedUpdatedDeletedBy; // Assuming this trait exists and adds audit FKs/methods
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo; // Use BelongsTo trait
use Illuminate\Database\Eloquent\SoftDeletes; // Use SoftDeletes trait
use Illuminate\Support\Carbon; // Import Carbon for type hinting with date/datetime casts
use Illuminate\Database\Eloquent\Relations\BelongsTo as BelongsToRelation; // Alias if needed


// Import models for BelongsTo relationships
use App\Models\Employee;
use App\Models\Center;
use App\Models\Department;
use App\Models\Position;
use App\Models\User; // For audit columns (handled by trait)


/**
 * App\Models\Timeline
 * 
 * Represents a historical entry for an employee's assignment, tracking their position,
 * department, and center over a specific time period.
 *
 * @property int $id
 * @property int $employee_id Foreign key to the employee associated with this timeline entry.
 * @property int|null $center_id Foreign key to the center associated with this timeline entry.
 * @property int|null $department_id Foreign key to the department associated with this timeline entry.
 * @property int|null $position_id Foreign key to the position associated with this timeline entry.
 * @property \Illuminate\Support\Carbon $start_date The start date of the assignment period.
 * @property \Illuminate\Support\Carbon|null $end_date The end date of the assignment period (null if ongoing).
 * @property bool $is_sequent Indicates if this is a sequential/continuous period of employment/assignment.
 * @property string|null $notes Additional notes about this timeline entry.
 * @property int|null $created_by Foreign key to the user who created the record.
 * @property int|null $updated_by Foreign key to the user who last updated the record.
 * @property int|null $deleted_by Foreign key to the user who soft deleted the record.
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at The timestamp when the timeline entry was soft deleted.
 * @property-read \App\Models\Center|null $center The center associated with this timeline entry.
 * @property-read \App\Models\Department|null $department The department associated with this timeline entry.
 * @property-read \App\Models\Employee $employee The employee associated with this timeline entry.
 * @property-read \App\Models\Position|null $position The position associated with this timeline entry.
 * @property-read \App\Models\User|null $createdBy The user who created the record.
 * @property-read \App\Models\User|null $deletedBy The user who soft deleted the record.
 * @property-read \App\Models\User|null $updatedBy The user who last updated the record.
 * @method static \Illuminate\Database\Eloquent\Builder|Timeline newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Timeline newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Timeline onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Timeline query()
 * @method static \Illuminate\Database\Eloquent\Builder|Timeline whereCenterId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Timeline whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Timeline whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Timeline whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Timeline whereDeletedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Timeline whereDepartmentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Timeline whereEmployeeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Timeline whereEndDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Timeline whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Timeline whereIsSequent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Timeline whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Timeline wherePositionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Timeline whereStartDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Timeline whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Timeline whereUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Timeline withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Timeline withoutTrashed()
 * @mixin \Eloquent
 */
class Timeline extends Model
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
    'employee_id', // Foreign key to employees table
    'center_id', // Foreign key to centers table
    'department_id', // Foreign key to departments table
    'position_id', // Foreign key to positions table

    'start_date', // Date field
    'end_date', // Nullable date field
    'is_sequent', // Boolean flag
    'notes', // Nullable text field

    // 'created_by', // Handled by trait
    // 'updated_by', // Handled by trait
    // 'deleted_by', // Handled by trait
  ];

  /**
   * The attributes that should be cast.
   * Ensures data types are correct and dates are Carbon instances.
   * Includes casts for FKs, dates, booleans, strings, timestamps, and soft deletes.
   * Explicitly casting all attributes for clarity.
   *
   * @var array<string, string>
   */
  protected $casts = [
    'employee_id' => 'integer', // Cast FKs to integer
    'center_id' => 'integer',
    'department_id' => 'integer',
    'position_id' => 'integer',

    'start_date' => 'date', // Cast date fields to Carbon instances (YYYY-MM-DD)
    'end_date' => 'date', // Cast nullable date field

    'is_sequent' => 'boolean', // Cast boolean flag

    'notes' => 'string', // Explicitly cast notes as string

    'created_at' => 'datetime', // Explicitly cast timestamps
    'updated_at' => 'datetime',
    'deleted_at' => 'datetime', // Cast soft delete timestamp
  ];


  // The CreatedUpdatedDeletedBy trait is assumed to add these audit relationships:

  /**
   * Get the user who created the model.
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\User, \App\Models\Timeline>
   */
  // public function createdBy(): BelongsToRelation;

  /**
   * Get the user who last updated the model.
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\User, \App\Models\Timeline>
   */
  // public function updatedBy(): BelongsToRelation;

  /**
   * Get the user who soft deleted the model.
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\User, \App\Models\Timeline>
   */
  // public function deletedBy(): BelongsToRelation;


  // 👉 Relationships

  /**
   * Get the employee associated with this timeline entry.
   * Defines a many-to-one relationship where a Timeline entry belongs to one Employee.
   * Assumes the 'timelines' table has an 'employee_id' foreign key.
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Employee, \App\Models\Timeline>
   */
  public function employee(): BelongsTo // Added return type hint
  {
    // Defines a many-to-one relationship with the Employee model
    return $this->belongsTo(Employee::class, 'employee_id'); // Explicitly define FK for clarity
  }

  /**
   * Get the center associated with this timeline entry.
   * Defines a many-to-one relationship where a Timeline entry belongs to one Center.
   * Assumes the 'timelines' table has a 'center_id' foreign key.
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Center, \App\Models\Timeline>
   */
  public function center(): BelongsTo // Added return type hint
  {
    // Defines a many-to-one relationship with the Center model
    return $this->belongsTo(Center::class, 'center_id'); // Explicitly define FK for clarity
  }

  /**
   * Get the department associated with this timeline entry.
   * Defines a many-to-one relationship where a Timeline entry belongs to one Department.
   * Assumes the 'timelines' table has a 'department_id' foreign key.
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Department, \App\Models\Timeline>
   */
  public function department(): BelongsTo // Added return type hint
  {
    // Defines a many-to-one relationship with the Department model
    return $this->belongsTo(Department::class, 'department_id'); // Explicitly define FK for clarity
  }

  /**
   * Get the position associated with this timeline entry.
   * Defines a many-to-one relationship where a Timeline entry belongs to one Position.
   * Assumes the 'timelines' table has a 'position_id' foreign key.
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Position, \App\Models\Timeline>
   */
  public function position(): BelongsTo // Added return type hint
  {
    // Defines a many-to-one relationship with the Position model
    return $this->belongsTo(Position::class, 'position_id'); // Explicitly define FK for clarity
  }


  // Add custom methods or accessors/mutators here as needed
}
