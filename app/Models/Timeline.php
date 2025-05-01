<?php

namespace App\Models; // Ensure the namespace is correct for your project

use App\Traits\CreatedUpdatedDeletedBy; // Assuming this trait exists and adds audit FKs/methods
use Illuminate\Database\Eloquent\Factories\HasFactory; // Import HasFactory trait
use Illuminate\Database\Eloquent\Model; // Import base Model class
use Illuminate\Database\Eloquent\Relations\BelongsTo; // Import BelongsTo relationship type
use Illuminate\Database\Eloquent\SoftDeletes; // Import SoftDeletes trait
use Illuminate\Support\Carbon; // Import Carbon for type hinting with date/datetime casts
// Removed unused aliased import: BelongsToRelation

// Import models for BelongsTo relationships (Eloquent needs to know about the related models)
use App\Models\Employee; // Timeline belongsTo Employee
use App\Models\Center; // Timeline belongsTo Center
use App\Models\Department; // Timeline belongsTo Department
use App\Models\Position; // Timeline belongsTo Position
use App\Models\User; // For audit columns (handled by trait CreatedUpdatedDeletedBy)


/**
 * App\Models\Timeline
 *
 * Represents a historical entry for an employee's assignment and location details over a specific time period.
 * Tracks the employee's associated center, department, and position, along with the start and end dates of the assignment.
 * Can indicate if the period is a sequential/continuous part of their employment history.
 * Includes audit trails and soft deletion.
 *
 * @property int $id
 * @property int $employee_id Foreign key to the employee associated with this timeline entry ('employees' table).
 * @property int|null $center_id Foreign key to the center associated with this timeline entry ('centers' table, nullable).
 * @property int|null $department_id Foreign key to the department associated with this timeline entry ('departments' table, nullable).
 * @property int|null $position_id Foreign key to the position associated with this timeline entry ('positions' table, nullable).
 * @property \Illuminate\Support\Carbon $start_date The start date of the assignment period.
 * @property \Illuminate\Support\Carbon|null $end_date The end date of the assignment period (null if ongoing/current).
 * @property bool $is_sequent Indicates if this is a sequential/continuous period of employment/assignment (boolean).
 * @property string|null $notes Additional notes about this timeline entry (nullable text field).
 * @property int|null $created_by Foreign key to the user who created the record.
 * @property int|null $updated_by Foreign key to the user who last updated the record.
 * @property int|null $deleted_by Foreign key to the user who soft deleted the record.
 * @property \Illuminate\Support\Carbon|null $created_at Automatically managed timestamp for creation.
 * @property \Illuminate\Support\Carbon|null $updated_at Automatically managed timestamp for last update.
 * @property \Illuminate\Support\Carbon|null $deleted_at The timestamp when the timeline entry was soft deleted.
 *
 * @property-read \App\Models\Center|null $center The center model associated with this timeline entry.
 * @property-read \App\Models\Department|null $department The department model associated with this timeline entry.
 * @property-read \App\Models\Employee $employee The employee model associated with this timeline entry.
 * @property-read \App\Models\Position|null $position The position model associated with this timeline entry.
 * @property-read \App\Models\User|null $createdBy The user who created the record (if trait adds this).
 * @property-read \App\Models\User|null $deletedBy The user who soft deleted the record (if trait adds this).
 * @property-read \App\Models\User|null $updatedBy The user who last updated the record (if trait adds this).
 *
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
  // HasFactory is for model factories.
  // SoftDeletes adds the 'deleted_at' column and scope.
  // CreatedUpdatedDeletedBy is assumed to add 'created_by', 'updated_by', 'deleted_by' columns and relationships.
  use CreatedUpdatedDeletedBy, HasFactory, SoftDeletes;

  /**
   * The table associated with the model.
   *
   * @var string
   */
  // protected $table = 'timelines'; // Explicitly define table name if it's not the plural of the model name ('timelines')


  /**
   * The attributes that are mass assignable.
   * Allows these attributes to be set using mass assignment (e.g., during create or update).
   * 'id' is excluded as it is the primary key.
   *
   * @var array<int, string>
   */
  protected $fillable = [
    'employee_id',   // Foreign key to employees table (required)
    'center_id',     // Foreign key to centers table (nullable)
    'department_id', // Foreign key to departments table (nullable)
    'position_id',   // Foreign key to positions table (nullable)

    'start_date',    // Date field (required Carbon instance)
    'end_date',      // Nullable date field (nullable Carbon instance)
    'is_sequent',    // Boolean flag (required boolean)
    'notes',         // Nullable text field (nullable string)

    // The CreatedUpdatedDeletedBy trait is assumed to handle these audit columns:
    // 'created_by',
    // 'updated_by',
    // 'deleted_by',
  ];

  /**
   * The attributes that should be cast.
   * Ensures data types are correct and dates are Carbon instances.
   * Includes casts for FKs, dates, booleans, strings, timestamps, and soft deletes.
   * Explicitly casting all attributes for clarity and type safety.
   *
   * @var array<string, string>
   */
  protected $casts = [
    'employee_id'   => 'integer', // Cast FKs to integer
    'center_id'     => 'integer',
    'department_id' => 'integer',
    'position_id'   => 'integer',

    'start_date'    => 'date',    // Cast date fields to Carbon instances (YYYY-MM-DD)
    'end_date'      => 'date',    // Cast nullable date field to Carbon instances

    'is_sequent'    => 'boolean', // Cast boolean flag

    'notes'         => 'string', // Explicitly cast notes as string

    // Standard Eloquent timestamps handled by base model and traits
    'created_at'    => 'datetime', // Explicitly cast creation timestamp to Carbon instance
    'updated_at'    => 'datetime', // Explicitly cast update timestamp to Carbon instance
    'deleted_at'    => 'datetime', // Cast soft delete timestamp to Carbon instance
    // Add casts for audit FKs if the trait doesn't add them: 'created_by' => 'integer', ...
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
  //     'created_at', // Consider hiding standard timestamps too if not needed
  //     'updated_at',
  //     'deleted_at', // Hide soft delete timestamp
  // ];


  // The CreatedUpdatedDeletedBy trait is assumed to add these audit relationships.
  // Their docblocks are included in the main class docblock above for clarity.
  // Example docblocks added by the trait:
  /*
        * Get the user who created the model.
        * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\User, \App\Models\Timeline>
        */
  // public function createdBy(): BelongsTo;

  /*
        * Get the user who last updated the model.
        * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\User, \App\Models\Timeline>
        */
  // public function updatedBy(): BelongsTo;

  /*
        * Get the user who soft deleted the model.
        * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\User, \App\Models\Timeline>
        */
  // public function deletedBy(): BelongsTo;


  // 👉 Relationships

  /**
   * Get the employee associated with this timeline entry.
   * Defines a many-to-one relationship where a Timeline entry belongs to one Employee.
   * This links the historical assignment record to the specific employee.
   * Assumes the 'timelines' table has an 'employee_id' foreign key that
   * references the 'employees' table's primary key ('id').
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Employee, \App\Models\Timeline>
   */
  public function employee(): BelongsTo // Added return type hint
  {
    // Defines a many-to-one relationship with the Employee model.
    // 'Employee::class' is the related model.
    // 'employee_id' is the foreign key on the 'timelines' table.
    // 'id' is the local key on the 'employees' table (default, can be omitted).
    return $this->belongsTo(Employee::class, 'employee_id'); // Explicitly define FK for clarity
    // If using the standard foreign key name, this is sufficient: return $this->belongsTo(Employee::class);
  }

  /**
   * Get the center associated with this timeline entry.
   * Defines a many-to-one relationship where a Timeline entry belongs to one Center.
   * This links the historical assignment record to the center where the employee was assigned.
   * Assumes the 'timelines' table has a 'center_id' foreign key that
   * references the 'centers' table's primary key ('id'). This relationship is nullable.
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Center, \App\Models\Timeline>
   */
  public function center(): BelongsTo // Added return type hint
  {
    // Defines a many-to-one relationship with the Center model.
    // 'Center::class' is the related model.
    // 'center_id' is the foreign key on the 'timelines' table.
    // 'id' is the local key on the 'centers' table (default, can be omitted).
    return $this->belongsTo(Center::class, 'center_id'); // Explicitly define FK for clarity
    // If using the standard foreign key name, this is sufficient: return $this->belongsTo(Center::class);
  }

  /**
   * Get the department associated with this timeline entry.
   * Defines a many-to-one relationship where a Timeline entry belongs to one Department.
   * This links the historical assignment record to the department where the employee was assigned.
   * Assumes the 'timelines' table has a 'department_id' foreign key that
   * references the 'departments' table's primary key ('id'). This relationship is nullable.
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Department, \App\Models\Timeline>
   */
  public function department(): BelongsTo // Added return type hint
  {
    // Defines a many-to-one relationship with the Department model.
    // 'Department::class' is the related model.
    // 'department_id' is the foreign key on the 'timelines' table.
    // 'id' is the local key on the 'departments' table (default, can be omitted).
    return $this->belongsTo(Department::class, 'department_id'); // Explicitly define FK for clarity
    // If using the standard foreign key name, this is sufficient: return $this->belongsTo(Department::class);
  }

  /**
   * Get the position associated with this timeline entry.
   * Defines a many-to-one relationship where a Timeline entry belongs to one Position.
   * This links the historical assignment record to the position held by the employee.
   * Assumes the 'timelines' table has a 'position_id' foreign key that
   * references the 'positions' table's primary key ('id'). This relationship is nullable.
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Position, \App\Models\Timeline>
   */
  public function position(): BelongsTo // Added return type hint
  {
    // Defines a many-to-one relationship with the Position model.
    // 'Position::class' is the related model.
    // 'position_id' is the foreign key on the 'timelines' table.
    // 'id' is the local key on the 'positions' table (default, can be omitted).
    return $this->belongsTo(Position::class, 'position_id'); // Explicitly define FK for clarity
    // If using the standard foreign key name, this is sufficient: return $this->belongsTo(Position::class);
  }


  // 👉 Helper Methods (Timeline Status)

  /**
   * Check if this timeline entry represents a current or ongoing assignment.
   * An assignment is considered current if the 'end_date' is null.
   *
   * @return bool True if the end date is null (ongoing), false otherwise.
   */
  public function isCurrent(): bool // Added helper method
  {
    // An assignment is current if the end_date is not set (null).
    return $this->end_date === null;
  }

  /**
   * Check if this timeline entry represents a past assignment.
   * An assignment is considered past if the 'end_date' is not null.
   *
   * @return bool True if the end date is set (past), false otherwise.
   */
  public function isPast(): bool // Added helper method
  {
    // An assignment is past if the end_date is set.
    return $this->end_date !== null;
  }


  // Add any other custom methods or accessors/mutators here as needed
}
