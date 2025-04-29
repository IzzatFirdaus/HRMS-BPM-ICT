<?php

namespace App\Models;

use App\Traits\CreatedUpdatedDeletedBy; // Assuming this trait exists and adds audit FKs/methods
use Carbon\Carbon; // Assuming Carbon is used implicitly with date casts
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo; // Use BelongsTo trait
use Illuminate\Database\Eloquent\SoftDeletes; // Use SoftDeletes trait
use Illuminate\Database\Eloquent\Relations\BelongsTo as BelongsToRelation; // Alias if needed


// Import models for BelongsTo relationships
use App\Models\Employee;
use App\Models\Leave;
use App\Models\User; // For audit columns (handled by trait)


/**
 * App\Models\EmployeeLeave
 *
 * Represents a specific instance of an employee taking a type of leave.
 * This model typically maps to a pivot table ('employee_leave') with additional columns.
 *
 * @property int $id
 * @property int $employee_id Foreign key to employees table.
 * @property int $leave_id Foreign key to leaves table.
 * @property \Illuminate\Support\Carbon $from_date The start date of the leave.
 * @property \Illuminate\Support\Carbon $to_date The end date of the leave.
 * @property string|null $start_at The start time of the leave (if applicable).
 * @property string|null $end_at The end time of the leave (if applicable).
 * @property string|null $note Additional notes for the leave application.
 * @property bool $is_authorized Indicates if the leave application has been authorized.
 * @property bool $is_checked Indicates if the leave application has been checked/processed.
 * @property int|null $created_by Foreign key to the user who created the record.
 * @property int|null $updated_by Foreign key to the user who last updated the record.
 * @property int|null $deleted_by Foreign key to the user who soft deleted the record.
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\Employee $employee The employee associated with this record.
 * @property-read \App\Models\Leave $leave The leave type associated with this record.
 * @property-read \App\Models\User|null $createdBy The user who created the record.
 * @property-read \App\Models\User|null $deletedBy The user who soft deleted the record.
 * @property-read \App\Models\User|null $updatedBy The user who last updated the record.
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeLeave newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeLeave newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeLeave onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeLeave query()
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeLeave whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeLeave whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeLeave whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeLeave whereDeletedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeLeave whereEmployeeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeLeave whereEndAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeLeave whereFromDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeLeave whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeLeave whereIsAuthorized($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeLeave whereIsChecked($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeLeave whereLeaveId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeLeave whereNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeLeave whereStartAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeLeave whereToDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeLeave whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeLeave whereUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeLeave withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeLeave withoutTrashed()
 * @mixin \Eloquent
 */
class EmployeeLeave extends Model // Maps to the 'employee_leave' pivot table
{
  // Use the traits for factory, soft deletes, and audit columns
  use CreatedUpdatedDeletedBy, HasFactory, SoftDeletes;

  /**
   * The table associated with the model.
   * Explicitly define the table name if it doesn't follow Laravel's naming conventions.
   *
   * @var string
   */
  protected $table = 'employee_leave'; // Explicitly define the table name

  /**
   * The attributes that are mass assignable.
   * Includes all columns except the primary key (id) and standard timestamps/soft deletes.
   *
   * @var array<int, string>
   */
  protected $fillable = [
    // 'id' is typically not mass assignable as it's the primary key

    'employee_id', // Foreign key to employees table
    'leave_id', // Foreign key to leaves table

    'from_date', // Date field
    'to_date', // Date field
    'start_at', // Time field
    'end_at', // Time field
    'note', // Nullable text field

    'is_authorized', // Boolean field
    'is_checked', // Boolean field

    // 'created_by', // Handled by trait
    // 'updated_by', // Handled by trait
    // 'deleted_by', // Handled by trait
  ];

  /**
   * The attributes that should be cast.
   * Ensures data types are correct and dates are Carbon instances.
   * Includes casts for FKs, dates, times, booleans, timestamps, soft deletes.
   *
   * @var array<string, string>
   */
  protected $casts = [
    'employee_id' => 'integer', // Cast FKs to integer
    'leave_id' => 'integer',

    'from_date' => 'date', // Cast date fields to Carbon instances (YYYY-MM-DD)
    'to_date' => 'date', // Cast date fields to Carbon instances (YYYY-MM-DD)

    // Cast time fields. Use 'string' if storing HH:MM:SS strings.
    // Use 'datetime' if storing timestamps or if database column is compatible (e.g., DATETIME, TIMESTAMP).
    // If database column is TIME, 'string' might be the most reliable cast.
    'start_at' => 'string', // Casting as string (assuming HH:MM:SS format in DB TIME column)
    'end_at' => 'string', // Casting as string (assuming HH:MM:SS format in DB TIME column)
    // Alternative: If using DATETIME/TIMESTAMP columns in DB and storing full date/time:
    // 'start_at' => 'datetime', // Cast as datetime
    // 'end_at' => 'datetime', // Cast as datetime
    // Then use ->format('H:i:s') in accessors if needed for time-only display.


    'note' => 'string', // Explicitly cast note as string

    'is_authorized' => 'boolean', // Cast boolean fields
    'is_checked' => 'boolean', // Cast boolean fields

    'created_at' => 'datetime', // Explicitly cast timestamps
    'updated_at' => 'datetime',
    'deleted_at' => 'datetime', // Cast soft delete timestamp
  ];

  /**
   * The attributes that should be hidden for serialization.
   * Prevents sensitive or unnecessary fields from being included in JSON responses.
   *
   * @var array<int, string>
   */
  protected $hidden = [
    'is_authorized', // Hidden as requested
    'is_checked', // Hidden as requested
    'deleted_by', // Hidden as requested
    'deleted_at', // Hidden as requested
    // Add other fields you want to hide from JSON responses
  ];


  // The CreatedUpdatedDeletedBy trait is assumed to add these audit relationships:

  /**
   * Get the user who created the model.
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\User, \App\Models\EmployeeLeave>
   */
  // public function createdBy(): BelongsToRelation;

  /**
   * Get the user who last updated the model.
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\User, \App\Models\EmployeeLeave>
   */
  // public function updatedBy(): BelongsToRelation;

  /**
   * Get the user who soft deleted the model.
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\User, \App\Models\EmployeeLeave>
   */
  // public function deletedBy(): BelongsToRelation;


  // 👉 Relationships

  /**
   * Get the employee associated with this leave application record.
   * Defines a many-to-one relationship where an EmployeeLeave record belongs to one Employee.
   * Assumes the 'employee_leave' table has an 'employee_id' foreign key.
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Employee, \App\Models\EmployeeLeave>
   */
  public function employee(): BelongsTo // Added return type hint
  {
    // Defines a many-to-one relationship with the Employee model
    return $this->belongsTo(Employee::class, 'employee_id'); // Explicitly define FK for clarity
  }

  /**
   * Get the leave type associated with this leave application record.
   * Defines a many-to-one relationship where an EmployeeLeave record belongs to one Leave.
   * Assumes the 'employee_leave' table has a 'leave_id' foreign key.
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Leave, \App\Models\EmployeeLeave>
   */
  public function leave(): BelongsTo // Added return type hint
  {
    // Defines a many-to-one relationship with the Leave model
    return $this->belongsTo(Leave::class, 'leave_id'); // Explicitly define FK for clarity
  }


  // 👉 Helper Methods (Status Checks)

  /**
   * Check if the leave application has been authorized.
   * Directly returns the boolean value of the 'is_authorized' attribute after casting.
   *
   * @return bool
   */
  public function isAuthorized(): bool // Added return type hint
  {
    // The attribute is already cast to boolean via $casts
    return $this->is_authorized;
  }

  /**
   * Check if the leave application has been checked/processed.
   * Directly returns the boolean value of the 'is_checked' attribute after casting.
   *
   * @return bool
   */
  public function isChecked(): bool // Added return type hint
  {
    // The attribute is already cast to boolean via $casts
    return $this->is_checked;
  }

  // Add custom methods or accessors/mutators here as needed
}
