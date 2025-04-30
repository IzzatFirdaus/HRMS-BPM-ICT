<?php

namespace App\Models;

// Assuming this trait exists and adds audit FKs/methods like created_by, updated_by, deleted_by
use App\Traits\CreatedUpdatedDeletedBy;
// Assuming Carbon is used implicitly with date/datetime casts (including explicitly importing here is good practice)
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
// Use the relationships directly without aliasing unless you need aliases for specific reasons
use Illuminate\Database\Eloquent\Relations\BelongsTo; // Used by CreatedUpdatedDeletedBy trait for audit FKs
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany as EloquentHasMany; // ALIAS: Import HasMany and alias it for clarity in type hints
// use Illuminate\Database\Eloquent\Relations\MorphMany; // Not directly used in this model's relationships
use Illuminate\Database\Eloquent\SoftDeletes; // Use SoftDeletes trait
use Illuminate\Database\Eloquent\Casts\Attribute; // Import Attribute for accessor/mutator type hinting
use Illuminate\Database\Eloquent\Collection; // Import Collection for relationship return type hinting
// Removed unused alias: use Illuminate\Database\Eloquent\Relations\BelongsTo as BelongsToRelation;


// Import models for relationships if they are in a different namespace
use App\Models\Employee;      // Leave is linked to Employee via the employee_leave pivot table
use App\Models\EmployeeLeave; // Leave is linked to EmployeeLeave directly (one-to-many)
use App\Models\User;         // For audit columns (handled by trait)


/**
 * App\Models\Leave
 *
 * Represents a type of leave (e.g., Annual Leave, Sick Leave).
 * Linked to individual employee leave applications through the `EmployeeLeave` model,
 * and indirectly to employees via `EmployeeLeave` which links to the `Employee` model.
 *
 * @property int $id
 * @property string $name The name of the leave type (e.g., 'Annual Leave').
 * @property bool $is_instantly Indicates if this leave can be taken instantly without prior authorization.
 * @property bool $is_accumulative Indicates if remaining leave days/minutes accumulate to the next period.
 * @property int $discount_rate The rate at which this leave is discounted (e.g., 100 for full pay).
 * @property int $days_limit The maximum number of days allowed for this leave type.
 * @property int $minutes_limit The maximum number of minutes allowed for this leave type (if applicable).
 * @property string|null $notes Additional notes about the leave type.
 * @property int|null $created_by Foreign key to the user who created the record (handled by trait).
 * @property int|null $updated_by Foreign key to the user who last updated the record (handled by trait).
 * @property int|null $deleted_by Foreign key to the user who soft deleted the record (handled by trait).
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 *
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Employee> $employees The employees who have taken this leave type (via pivot).
 * @property-read int|null $employees_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\EmployeeLeave> $employeeLeaveApplications Individual employee leave applications for this leave type (one-to-many).
 * @property-read int|null $employee_leave_applications_count
 * @property-read \App\Models\User|null $createdBy Relation to the user who created the record.
 * @property-read \App\Models\User|null $deletedBy Relation to the user who soft deleted the record.
 * @property-read \App\Models\User|null $updatedBy Relation to the user who last updated the record.
 *
 * @property-read string $name_with_discount_rate The name formatted with the discount rate (accessor).
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Leave newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Leave newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Leave onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Leave query()
 * @method static \Illuminate\Database\Eloquent\Builder|Leave whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Leave whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Leave whereDaysLimit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Leave whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Leave whereDeletedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Leave whereDiscountRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Leave whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Leave whereIsAccumulative($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Leave whereIsInstantly($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Leave whereMinutesLimit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Leave whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Leave whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Leave whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Leave whereUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Leave withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Leave withoutTrashed()
 * @mixin \Eloquent
 */
class Leave extends Model
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
    'is_instantly',
    'is_accumulative',
    'discount_rate',
    'days_limit',
    'minutes_limit',
    'notes',
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
    'name'            => 'string',  // Explicitly cast name as string
    'notes'           => 'string',  // Explicitly cast notes as string
    'is_instantly'    => 'boolean', // Cast boolean flags
    'is_accumulative' => 'boolean',
    'discount_rate'   => 'integer', // Cast integer fields
    'days_limit'      => 'integer',
    'minutes_limit'   => 'integer',

    'created_at'      => 'datetime', // Explicitly cast timestamps
    'updated_at'      => 'datetime',
    'deleted_at'      => 'datetime', // Cast soft delete timestamp
  ];

  // The CreatedUpdatedDeletedBy trait is assumed to add these audit relationships.
  // Their docblocks are included in the main class docblock above for clarity.
  // Example docblocks added by the trait:
  /*
     * Get the user who created the model.
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\User, \App\Models\Leave>
     */
  // public function createdBy(): BelongsTo;

  /*
     * Get the user who last updated the model.
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\User, \App\Models\Leave>
     */
  // public function updatedBy(): BelongsTo;

  /*
     * Get the user who soft deleted the model.
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\User, \App\Models\Leave>
     */
  // public function deletedBy(): BelongsTo;


  // 👉 Relationships

  /**
   * Get the employees who have taken this leave type through the pivot table.
   * Defines a many-to-many relationship with the Employee model via the 'employee_leave' pivot table.
   * Assumes a 'employee_leave' pivot table linking leaves and employees with columns 'leave_id' and 'employee_id'.
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany<\App\Models\Employee>
   */
  public function employees(): BelongsToMany // Added return type hint
  {
    // Defines a many-to-many relationship with the Employee model via the 'employee_leave' pivot table
    return $this->belongsToMany(Employee::class, 'employee_leave', 'leave_id', 'employee_id')
      ->withPivot([
        'id', // Include the pivot table's own ID if it has one and you need it
        'from_date',
        'to_date',
        'start_at',
        'end_at',
        'note',
        'is_authorized',
        'is_checked',
        // Include pivot table audit columns if trait doesn't handle them automatically on the pivot model
        'created_by',
        'updated_by',
        'deleted_by',
        // Include pivot table timestamps if trait doesn't handle them automatically
        'created_at',
        'updated_at',
        'deleted_at'
      ])
      // Optional: Add casting for pivot attributes for correct types
      ->as('application') // Name the pivot attribute for easier access, e.g., $leave->employees[0]->application->from_date
      ->withCasts([
        'id'            => 'integer', // Cast pivot ID
        'from_date'     => 'date',    // Cast date attributes on the pivot
        'to_date'       => 'date',
        'start_at'      => 'datetime', // Or 'time' depending on storage and how you use it
        'end_at'        => 'datetime', // Or 'time'
        'is_authorized' => 'boolean', // Cast boolean flags on the pivot
        'is_checked'    => 'boolean',
        'created_by'    => 'integer', // Cast pivot audit FKs to integer
        'updated_by'    => 'integer',
        'deleted_by'    => 'integer',
        'created_at'    => 'datetime', // Cast pivot timestamps
        'updated_at'    => 'datetime',
        'deleted_at'    => 'datetime', // Cast pivot soft delete timestamp
      ]);
  }

  /**
   * Get the individual employee leave applications for this leave type.
   * Defines a one-to-many relationship where a Leave type has many EmployeeLeave records.
   * Assumes the 'employee_leave' table has a 'leave_id' foreign key.
   *
   * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\EmployeeLeave>
   */
  public function employeeLeaveApplications(): EloquentHasMany // Corrected return type hint alias
  {
    // Defines a one-to-many relationship with the EmployeeLeave pivot model
    return $this->hasMany(EmployeeLeave::class, 'leave_id'); // Explicitly define FK for clarity
  }


  // 👉 Helper Methods (Status Checks)

  /**
   * Check if this leave type can be taken instantly without prior authorization.
   * Directly returns the boolean value of the 'is_instantly' attribute after casting.
   *
   * @return bool
   */
  public function isInstant(): bool // Added return type hint
  {
    // The attribute is already cast to boolean via $casts
    return $this->is_instantly;
  }

  /**
   * Check if remaining leave days/minutes accumulate to the next period.
   * Directly returns the boolean value of the 'is_accumulative' attribute after casting.
   *
   * @return bool
   */
  public function isAccumulative(): bool // Added return type hint
  {
    // The attribute is already cast to boolean via $casts
    return $this->is_accumulative;
  }

  // Add custom methods or accessors/mutators here as needed

  /**
   * Get the name formatted with the discount rate (e.g., "Annual Leave (100%)").
   * This is an accessor. Use $leave->name_with_discount_rate.
   *
   * @return \Illuminate\Database\Eloquent\Casts\Attribute
   */
  // Corrected accessor return type hint and method name format
  protected function nameWithDiscountRate(): Attribute
  {
    return Attribute::make(
      get: fn(mixed $value, array $attributes) => "{$attributes['name']} ({$attributes['discount_rate']}%)",
    );
  }
}
