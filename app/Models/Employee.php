<?php

namespace App\Models; // Ensure the namespace is correct for your project

use App\Traits\CreatedUpdatedDeletedBy; // Assuming this trait exists and adds audit FKs/methods like created_by, updated_by, deleted_by
use Carbon\Carbon; // Import Carbon for date/time handling
use Illuminate\Database\Eloquent\Builder; // Import Builder for scope type hinting
use Illuminate\Database\Eloquent\Casts\Attribute; // Import Attribute for modern accessors/mutators
use Illuminate\Database\Eloquent\Factories\HasFactory; // Import HasFactory trait
use Illuminate\Database\Eloquent\Model; // Import base Model class
use Illuminate\Database\Eloquent\Relations\BelongsTo; // Import BelongsTo relationship type
use Illuminate\Database\Eloquent\Relations\BelongsToMany; // Import BelongsToMany relationship type
use Illuminate\Database\Eloquent\Relations\HasMany; // Import HasMany relationship type
use Illuminate\Database\Eloquent\Relations\HasOne; // Import HasOne relationship type
use Illuminate\Database\Eloquent\SoftDeletes; // Import SoftDeletes trait
use Illuminate\Database\Eloquent\Collection; // Import Collection for return type hinting
// Removed unused aliased imports: BelongsToRelation, BelongsToManyRelation, HasManyRelation, HasOneRelation
use Illuminate\Support\Facades\Storage; // Import Storage facade (used in getEmployeePhoto)
// Removed unused import: use Illuminate\Support\Facades\Auth; // Auth is likely used by traits/policies, not directly in this model


// Import models for relationships (Eloquent needs to know about the related models for relationships)
use App\Models\User; // Employee relates to User (hasOne) and audit trails (createdBy, etc.)
use App\Models\Contract; // Employee belongsTo Contract
use App\Models\Department; // Employee belongsTo Department
use App\Models\Position; // Employee belongsTo Position
use App\Models\Grade; // Employee belongsTo Grade
use App\Models\Fingerprint; // Employee hasMany Fingerprints
use App\Models\Discount; // Employee hasMany Discounts
use App\Models\Timeline; // Employee hasMany Timelines
use App\Models\Leave; // Employee belongsToMany Leaves
use App\Models\EmployeeLeave; // Employee hasMany EmployeeLeave (pivot model)
use App\Models\Message; // Employee hasMany Messages
use App\Models\Transition; // Employee hasMany Transitions
use App\Models\Equipment; // Employee hasMany AssignedEquipment (assuming FK assigned_to_employee_id)


/**
 * App\Models\Employee
 *
 * Represents an employee within the HRMS system. Stores personal, work, and status details,
 * and manages relationships with various other system entities like contracts, departments,
 * positions, grades, timelines, leaves, fingerprints, discounts, messages, transitions,
 * assigned equipment, and a related user account. Includes audit trails, soft deletion,
 * accessors for derived attributes, and a local scope for checking leave applications.
 *
 * @property int $id
 * @property int|null $contract_id Foreign key to the contracts table.
 * @property int|null $department_id Foreign key to the departments table.
 * @property int|null $position_id Foreign key to the positions table.
 * @property int|null $grade_id Foreign key to the grades table.
 * @property string $first_name Employee's first name.
 * @property string|null $father_name Employee's father's name.
 * @property string|null $last_name Employee's last name.
 * @property string|null $mother_name Employee's mother's name.
 * @property string|null $birth_and_place Employee's birth date and place (stored as string).
 * @property string|null $national_number Employee's national identification number (NRIC).
 * @property string|null $mobile_number Employee's mobile phone number.
 * @property string|null $degree Employee's highest degree or qualification.
 * @property string|null $gender Employee's gender.
 * @property string|null $address Employee's address.
 * @property string|null $notes Additional notes or remarks about the employee.
 * @property int $balance_leave_allowed Employee's remaining annual leave balance.
 * @property int $max_leave_allowed Employee's maximum allowed annual leave.
 * @property string|null $delay_counter Counter for tracking delays (stored as time format string e.g., 'HH:MM:SS').
 * @property string|null $hourly_counter Counter for tracking hourly work (stored as time format string e.g., 'HH:MM:SS').
 * @property bool $is_active Indicates if the employee is currently active.
 * @property string|null $profile_photo_path Path to the employee's profile photo file.
 * @property int|null $created_by Foreign key to the user who created the record (handled by trait).
 * @property int|null $updated_by Foreign key to the user who last updated the record (handled by trait).
 * @property int|null $deleted_by Foreign key to the user who soft deleted the record (handled by trait).
 * @property \Illuminate\Support\Carbon|null $created_at Automatically managed timestamp for creation.
 * @property \Illuminate\Support\Carbon|null $updated_at Automatically managed timestamp for last update.
 * @property \Illuminate\Support\Carbon|null $deleted_at Timestamp for soft deletion.
 *
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Equipment> $assignedEquipment Equipment currently assigned to the employee.
 * @property-read int|null $assigned_equipment_count
 * @property-read \App\Models\Contract|null $contract The employee's contract type.
 * @property-read \App\Models\User|null $createdBy The user who created the record (if trait adds this).
 * @property-read \App\Models\Department|null $department The employee's department.
 * @property-read \App\Models\User|null $deletedBy The user who soft deleted the record (if trait adds this).
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Discount> $discounts Discounts associated with the employee.
 * @property-read int|null $discounts_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\EmployeeLeave> $employeeLeaveApplications Individual employee leave application records.
 * @property-read int|null $employee_leave_applications_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Fingerprint> $fingerprints Fingerprint records for the employee.
 * @property-read int|null $fingerprints_count
 * @property-read \App\Models\Grade|null $grade The employee's grade.
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Leave> $leaves Leave types associated via pivot table (employee_leave).
 * @property-read int|null $leaves_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Message> $messages Messages sent by the employee.
 * @property-read int|null $messages_count
 * @property-read \App\Models\Position|null $position The employee's position.
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Timeline> $timelines Timeline entries for assignments and status changes.
 * @property-read int|null $timelines_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Transition> $transitions Equipment transitions involving the employee.
 * @property-read int|null $transitions_count
 * @property-read \App\Models\User|null $updatedBy The user who last updated the record (if trait adds this).
 * @property-read \App\Models\User|null $user The user account associated with the employee (if a one-to-one user account exists).
 *
 * @property-read string $fullName Employee's concatenated full name.
 * @property-read string $shortName Employee's concatenated short name (first + last).
 * @property-read int $workedYears Number of years worked based on timelines.
 * @property-read string $currentPosition Employee's current position name based on active timeline.
 * @property-read string $currentDepartment Employee's current department name based on active timeline.
 * @property-read string $currentCenter Employee's current center name based on active timeline.
 * @property-read string $joinAtShortForm Employee's join date in short human-readable format.
 * @property-read string $joinAt Employee's join date in formatted string.
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Employee checkLeave(int $leave_id, string $from_date, string $to_date, ?string $start_at = null, ?string $end_at = null) Scope to find employees with a matching leave application.
 * @method static \Illuminate\Database\Eloquent\Builder|Employee newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Employee newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Employee onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Employee query()
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereBalanceLeaveAllowed($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereBirthAndPlace($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereContractId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereDelayCounter($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereDeletedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereDepartmentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereDegree($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereFatherName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereFirstName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereGender($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereGradeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereHourlyCounter($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereLastName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereMaxLeaveAllowed($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereMobileNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereMotherName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereNationalNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee wherePositionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereProfilePhotoPath($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Employee withoutTrashed()
<<<<<<< HEAD
=======
 * @method static \Illuminate\Database\Eloquent\Builder|Employee checkLeave($leave_id, $from_date, $to_date, $start_at, $end_at)
 * @property-read string $current_center
 * @property-read string $current_department
 * @property-read string $current_position
 * @property-read string $full_name
 * @property-read string $join_at
 * @property-read string $join_at_short_form
 * @property-read string $short_name
 * @property-read int $worked_years
 * @method static \Database\Factories\EmployeeFactory factory($count = null, $state = [])
 * @method static Builder<static>|Employee whereUpdatedAt($value)
 * @method static Builder<static>|Employee whereUpdatedBy($value)
>>>>>>> cc6eb9f4f020325c04fee080d2466584ff27bb90
 * @mixin \Eloquent
 */
class Employee extends Model
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
  // protected $table = 'employees'; // Explicitly define table name if it's not the plural of the model name


  /**
   * The attributes that are mass assignable.
   * Allows these attributes to be set using mass assignment (e.g., during create or update).
   * 'id' is excluded as it is the primary key.
   *
   * @var array<int, string>
   */
  protected $fillable = [
    'contract_id',       // Foreign key to contracts table (nullable)
    'department_id',     // Foreign key to departments table (nullable)
    'position_id',       // Foreign key to positions table (nullable)
    'grade_id',          // Foreign key to grades table (nullable)

    'first_name',        // Employee's first name (required)
    'father_name',       // Employee's father's name (nullable)
    'last_name',         // Employee's last name (nullable)
    'mother_name',       // Employee's mother's name (nullable)
    'birth_and_place',   // Employee's birth date and place (stored as string, nullable)
    'national_number',   // Employee's national identification number (NRIC) (nullable or required)
    'mobile_number',     // Employee's mobile phone number (nullable or required)
    'degree',            // Employee's highest degree or qualification (nullable)
    'gender',            // Employee's gender (nullable)
    'address',           // Employee's address (nullable)
    'notes',             // Additional notes about the employee (nullable)

    'balance_leave_allowed', // Employee's remaining annual leave balance (integer)
    'max_leave_allowed', // Employee's maximum allowed annual leave (integer)
    'delay_counter',     // Counter for tracking delays (stored as time format string, nullable)
    'hourly_counter',    // Counter for tracking hourly work (stored as time format string, nullable)
    'is_active',         // Indicates if the employee is currently active (boolean)

    'profile_photo_path', // Path to the employee's profile photo file (string, nullable)

    // The CreatedUpdatedDeletedBy trait is assumed to handle these audit columns:
    // 'created_by',
    // 'updated_by',
    // 'deleted_by',
  ];

  /**
   * The attributes that should be cast.
   * Ensures data types are correct and dates are Carbon instances.
   * Explicitly casting all attributes for clarity and type safety.
   *
   * @var array<string, string>
   */
  protected $casts = [
    'contract_id' => 'integer',       // Cast FKs to integer
    'department_id' => 'integer',
    'position_id' => 'integer',
    'grade_id' => 'integer',

    // Explicitly cast string attributes
    'first_name' => 'string',
    'father_name' => 'string',
    'last_name' => 'string',
    'mother_name' => 'string',
    'birth_and_place' => 'string',    // Casting as string based on attribute name
    'national_number' => 'string',
    'mobile_number' => 'string',
    'degree' => 'string',
    'gender' => 'string',
    'address' => 'string',
    'notes' => 'string',
    'profile_photo_path' => 'string', // Explicitly cast profile photo path as string

    'balance_leave_allowed' => 'integer', // Cast integer counters
    'max_leave_allowed' => 'integer',

    // Casting as string as per original code structure, although Carbon casting might be useful if time arithmetic is needed.
    // Consider casting to 'datetime' if storing full timestamps, or using custom casts if only time is stored but needs Carbon functionality.
    'delay_counter' => 'string',
    'hourly_counter' => 'string',

    'is_active' => 'boolean', // Cast boolean flag

    // Standard Eloquent timestamps
    'created_at' => 'datetime',       // Explicitly cast creation timestamp
    'updated_at' => 'datetime',       // Explicitly cast update timestamp
    'deleted_at' => 'datetime',       // Cast soft delete timestamp
    // Add casts for other attributes if needed
  ];

  /**
   * The attributes that should be hidden for serialization.
   * (Optional) Prevents sensitive attributes from being returned in JSON responses.
   *
   * @var array<int, string>
   */
  // protected $hidden = [
  //     'password', // Example: hide user password hash if Employee relates to User with password
  //     'remember_token', // Example: hide remember token
  //     'created_by', // Example: hide audit columns from API responses
  //     'updated_by',
  //     'deleted_by',
  // ];


  // The CreatedUpdatedDeletedBy trait is assumed to add these audit relationships:
  // public function createdBy(): BelongsTo<\App\Models\User, \App\Models\Employee>; // Relationship to the user who created the record
  // public function updatedBy(): BelongsTo<\App\Models\User, \App\Models\Employee>; // Relationship to the user who last updated the record
  // public function deletedBy(): BelongsTo<\App\Models\User, \App\Models\Employee>; // Relationship to the user who soft deleted the record


  // 👉 Relationships

  /**
   * Get the user account associated with the employee.
   * Defines a one-to-one relationship where an Employee has one User account.
   * Assumes the 'users' table has an 'employee_id' foreign key that
   * references this Employee model's primary key ('id'). This relationship is nullable.
   *
   * @return \Illuminate\Database\Eloquent\Relations\HasOne<\App\Models\User>
   */
  public function user(): HasOne // Added return type hint
  {
    // Defines a one-to-one relationship with the User model.
    // 'User::class' is the related model.
    // 'employee_id' is the foreign key on the 'users' table.
    // 'id' is the local key on the 'employees' table (default, can be omitted).
    return $this->hasOne(User::class, 'employee_id');
    // If using the standard foreign key name, this is sufficient: return $this->hasOne(User::class);
  }

  /**
   * Get the contract type associated with the employee.
   * Defines a many-to-one relationship where an Employee belongs to one Contract.
   * Assumes the 'employees' table has a 'contract_id' foreign key that
   * references the 'contracts' table's primary key ('id'). This relationship is nullable.
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Contract, \App\Models\Employee>
   */
  public function contract(): BelongsTo // Added return type hint
  {
    // Defines a many-to-one relationship with the Contract model.
    // 'Contract::class' is the related model.
    // 'contract_id' is the foreign key on the 'employees' table.
    // 'id' is the local key on the 'contracts' table (default, can be omitted).
    return $this->belongsTo(Contract::class, 'contract_id');
    // If using the standard foreign key name, this is sufficient: return $this->belongsTo(Contract::class);
  }

  /**
   * Get the department associated with the employee.
   * Defines a many-to-one relationship where an Employee belongs to one Department.
   * Assumes the 'employees' table has a 'department_id' foreign key that
   * references the 'departments' table's primary key ('id'). This relationship is nullable.
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Department, \App\Models\Employee>
   */
  public function department(): BelongsTo // Added return type hint
  {
    // Defines a many-to-one relationship with the Department model.
    // 'Department::class' is the related model.
    // 'department_id' is the foreign key on the 'employees' table.
    // 'id' is the local key on the 'departments' table (default, can be omitted).
    return $this->belongsTo(Department::class, 'department_id');
    // If using the standard foreign key name, this is sufficient: return $this->belongsTo(Department::class);
  }

  /**
   * Get the position associated with the employee.
   * Defines a many-to-one relationship where an Employee belongs to one Position.
   * Assumes the 'employees' table has a 'position_id' foreign key that
   * references the 'positions' table's primary key ('id'). This relationship is nullable.
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Position, \App\Models\Employee>
   */
  public function position(): BelongsTo // Added return type hint
  {
    // Defines a many-to-one relationship with the Position model.
    // 'Position::class' is the related model.
    // 'position_id' is the foreign key on the 'employees' table.
    // 'id' is the local key on the 'positions' table (default, can be omitted).
    return $this->belongsTo(Position::class, 'position_id');
    // If using the standard foreign key name, this is sufficient: return $this->belongsTo(Position::class);
  }

  /**
   * Get the grade associated with the employee.
   * Defines a many-to-one relationship where an Employee belongs to one Grade.
   * Assumes the 'employees' table has a 'grade_id' foreign key that
   * references the 'grades' table's primary key ('id'). This relationship is nullable.
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Grade, \App\Models\Employee>
   */
  public function grade(): BelongsTo // Added return type hint
  {
    // Defines a many-to-one relationship with the Grade model.
    // 'Grade::class' is the related model.
    // 'grade_id' is the foreign key on the 'employees' table.
    // 'id' is the local key on the 'grades' table (default, can be omitted).
    return $this->belongsTo(Grade::class, 'grade_id');
    // If using the standard foreign key name, this is sufficient: return $this->belongsTo(Grade::class);
  }

  /**
   * Get the fingerprint records for the employee.
   * Defines a one-to-many relationship where an Employee has many Fingerprints.
   * Assumes the 'fingerprints' table has an 'employee_id' foreign key that
   * references the 'employees' table's primary key ('id').
   *
   * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\Fingerprint>
   */
  public function fingerprints(): HasMany // Added return type hint
  {
    // Defines a one-to-many relationship with the Fingerprint model.
    // 'Fingerprint::class' is the related model.
    // 'employee_id' is the foreign key on the 'fingerprints' table.
    // 'id' is the local key on the 'employees' table (default, can be omitted).
    return $this->hasMany(Fingerprint::class, 'employee_id');
    // If using the standard foreign key name, this is sufficient: return $this->hasMany(Fingerprint::class);
  }

  /**
   * Get the discount records for the employee.
   * Defines a one-to-many relationship where an Employee has many Discounts.
   * Assumes the 'discounts' table has an 'employee_id' foreign key that
   * references the 'employees' table's primary key ('id').
   *
   * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\Discount>
   */
  public function discounts(): HasMany // Added return type hint
  {
    // Defines a one-to-many relationship with the Discount model.
    // 'Discount::class' is the related model.
    // 'employee_id' is the foreign key on the 'discounts' table.
    // 'id' is the local key on the 'employees' table (default, can be omitted).
    return $this->hasMany(Discount::class, 'employee_id');
    // If using the standard foreign key name, this is sufficient: return $this->hasMany(Discount::class);
  }

  /**
   * Get the timeline entries for the employee's assignments and status changes.
   * Defines a one-to-many relationship where an Employee has many Timelines.
   * Assumes the 'timelines' table has an 'employee_id' foreign key that
   * references the 'employees' table's primary key ('id').
   *
   * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\Timeline>
   */
  public function timelines(): HasMany // Added return type hint
  {
    // Defines a one-to-many relationship with the Timeline model.
    // 'Timeline::class' is the related model.
    // 'employee_id' is the foreign key on the 'timelines' table.
    // 'id' is the local key on the 'employees' table (default, can be omitted).
    return $this->hasMany(Timeline::class, 'employee_id');
    // If using the standard foreign key name, this is sufficient: return $this->hasMany(Timeline::class);
  }

  /**
   * Get the individual employee leave application records for the employee.
   * This is a HasMany relationship to the pivot table model (EmployeeLeave).
   * This relationship is useful for accessing the pivot table records directly
   * when the pivot table has its own primary key or additional complex data beyond the foreign keys.
   * Assumes the 'employee_leave' table has an 'employee_id' foreign key that
   * references the 'employees' table's primary key ('id').
   *
   * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\EmployeeLeave>
   */
  public function employeeLeaveApplications(): HasMany // Added return type hint
  {
    // Defines a one-to-many relationship with the EmployeeLeave pivot model.
    // 'EmployeeLeave::class' is the related model.
    // 'employee_id' is the foreign key on the 'employee_leave' table.
    // 'id' is the local key on the 'employees' table (default, can be omitted).
    return $this->hasMany(EmployeeLeave::class, 'employee_id');
    // If using the standard foreign key name, this is sufficient: return $this->hasMany(EmployeeLeave::class);
  }

  /**
   * Get the leave types associated with the employee through the pivot table.
   * This is a many-to-many relationship with the Leave model, using the 'employee_leave'
   * pivot table. This relationship is useful for accessing the related Leave models directly.
   * Assumes a 'employee_leave' pivot table exists linking employees and leaves
   * with columns 'employee_id' and 'leave_id'. The `withPivot` and `withCasts`
   * methods are used to retrieve and cast columns from the pivot table.
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany<\App\Models\Leave>
   */
  public function leaves(): BelongsToMany // Added return type hint
  {
    // Defines a many-to-many relationship with the Leave model via the 'employee_leave' pivot table.
    // 'Leave::class' is the related model.
    // 'employee_leave' is the name of the intermediate table (pivot table).
    // 'employee_id' is the foreign key on the pivot table referencing the Employee model.
    // 'leave_id' is the foreign key on the pivot table referencing the Leave model.
    return $this->belongsToMany(Leave::class, 'employee_leave', 'employee_id', 'leave_id')
      // Include specific columns from the pivot table that you need to access.
      ->withPivot([
        'id',             // Include the pivot table's own ID if it has one
        'from_date',      // Start date of the leave application on the pivot
        'to_date',        // End date of the leave application on the pivot
        'start_at',       // Start time of leave on the pivot
        'end_at',         // End time of leave on the pivot
        'note',           // Note for the leave application on the pivot
        'is_authorized',  // Authorization status on the pivot
        'is_checked',     // Checked status on the pivot
        // Include pivot table audit columns and timestamps if they exist and trait doesn't handle them automatically on the pivot model
        'created_by',
        'updated_by',
        'deleted_by',
        'created_at',
        'updated_at',
        'deleted_at'
      ])
      // Alias the pivot attribute for easier access, e.g., $employee->leaves[0]->application->from_date
      ->as('application')
      // Cast pivot attributes for correct PHP types
      ->withCasts([
        'id' => 'integer',          // Cast pivot ID to integer
        'from_date' => 'date',      // Cast pivot dates to Carbon instances
        'to_date' => 'date',
        'start_at' => 'datetime',   // Or 'time' depending on database column type and how you use it
        'end_at' => 'datetime',     // Or 'time'
        'is_authorized' => 'boolean', // Cast pivot boolean flags
        'is_checked' => 'boolean',
        'created_by' => 'integer',  // Cast pivot audit FKs to integer
        'updated_by' => 'integer',
        'deleted_by' => 'integer',
        'created_at' => 'datetime', // Cast pivot timestamps to Carbon instances
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime', // Cast pivot soft delete timestamp
      ]);
  }


  /**
   * Get the messages sent by the employee.
   * Defines a one-to-many relationship where an Employee has many Messages.
   * Assumes the 'messages' table has an 'employee_id' foreign key that
   * references the 'employees' table's primary key ('id').
   *
   * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\Message>
   */
  public function messages(): HasMany // Added return type hint
  {
    // Defines a one-to-many relationship with the Message model.
    // 'Message::class' is the related model.
    // 'employee_id' is the foreign key on the 'messages' table.
    // 'id' is the local key on the 'employees' table (default, can be omitted).
    return $this->hasMany(Message::class, 'employee_id');
    // If using the standard foreign key name, this is sufficient: return $this->hasMany(Message::class);
  }

  /**
   * Get the equipment transitions for the employee.
   * Defines a one-to-many relationship where an Employee has many Transitions.
   * This relationship is likely used to track the issuance/return history of equipment
   * to and from this employee.
   * Assumes the 'transitions' table has an 'employee_id' foreign key that
   * references the 'employees' table's primary key ('id').
   *
   * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\Transition>
   */
  public function transitions(): HasMany // Added return type hint
  {
    // Defines a one-to-many relationship with the Transition model.
    // 'Transition::class' is the related model.
    // 'employee_id' is the foreign key on the 'transitions' table.
    // 'id' is the local key on the 'employees' table (default, can be omitted).
    return $this->hasMany(Transition::class, 'employee_id');
    // If using the standard foreign key name, this is sufficient: return $this->hasMany(Transition::class);
  }

  /**
   * Get the equipment currently assigned to the employee.
   * Defines a one-to-many relationship where an Employee has many Equipment items.
   * This relationship is likely used to quickly find all equipment currently marked
   * as assigned to this employee.
   * Assumes the 'equipment' table has an 'assigned_to_employee_id' foreign key that
   * references this Employee model's primary key ('id').
   *
   * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\Equipment>
   */
  public function assignedEquipment(): HasMany // Added return type hint
  {
    // Defines a one-to-many relationship with the Equipment model.
    // 'Equipment::class' is the related model.
    // 'assigned_to_employee_id' is the foreign key on the 'equipment' table.
    // 'id' is the local key on the 'employees' table (default, can be omitted).
    return $this->hasMany(Equipment::class, 'assigned_to_employee_id');
    // If using the standard foreign key name, this is sufficient: return $this->hasMany(Equipment::class);
  }


  // The CreatedUpdatedDeletedBy trait is assumed to add these audit relationships:

  /**
   * Get the user who created the model.
   * Assumes the 'created_by' foreign key exists and links to the 'users' table.
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\User, \App\Models\Employee>
   */
  // public function createdBy(): BelongsTo;

  /**
   * Get the user who last updated the model.
   * Assumes the 'updated_by' foreign key exists and links to the 'users' table.
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\User, \App\Models\Employee>
   */
  // public function updatedBy(): BelongsTo;

  /**
   * Get the user who soft deleted the model.
   * Assumes the 'deleted_by' foreign key exists and links to the 'users' table.
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\User, \App\Models\Employee>
   */
  // public function deletedBy(): BelongsTo;


  // 👉 Attributes (Accessors/Mutators using Attributes)

  /**
   * Accessor to format the employee's hourly counter.
   * Stored as time string (HH:MM:SS) in DB, returns as formatted string (HH:MM) or null.
   * Uses modern Attribute casting.
   *
   * @return \Illuminate\Database\Eloquent\Casts\Attribute
   */
  protected function hourlyCounter(): Attribute // Added return type hint
  {
    return Attribute::make(
      // Accessor: format time string or Carbon instance
      get: fn(?string $value) => $value ? Carbon::parse($value)->format('H:i') : null, // Use ?string for nullable column
      // Mutator: store as HH:MM:SS string or null
      set: fn(?string $value) => $value, // Use ?string for nullable column
    );
    // Consider casting to 'datetime' in $casts if time arithmetic is needed more frequently.
  }

  /**
   * Accessor to format the employee's delay counter.
   * Stored as time string (HH:MM:SS) in DB, returns as formatted string (HH:MM) or null.
   * Uses modern Attribute casting.
   *
   * @return \Illuminate\Database\Eloquent\Casts\Attribute
   */
  protected function delayCounter(): Attribute // Added return type hint
  {
    return Attribute::make(
      // Accessor: format time string or Carbon instance
      get: fn(?string $value) => $value ? Carbon::parse($value)->format('H:i') : null, // Use ?string for nullable column
      // Mutator: store as HH:MM:SS string or null
      set: fn(?string $value) => $value, // Use ?string for nullable column
    );
    // Consider casting to 'datetime' in $casts if time arithmetic is needed more frequently.
  }

  /**
   * Accessor to get the employee's full name by concatenating name parts.
   * Handles nullable name parts safely.
   *
   * @return string The full name.
   */
  protected function getFullNameAttribute(): string // Corrected method visibility to protected for accessors, Added return type hint
  {
    // Use null coalescing operator (??) for safety if name parts are null
    return trim($this->first_name . ' ' . ($this->father_name ?? '') . ' ' . ($this->last_name ?? ''));
  }

  /**
   * Accessor to get the employee's short name (first and last name).
   * Handles nullable last name safely.
   *
   * @return string The short name.
   */
  protected function getShortNameAttribute(): string // Corrected method visibility to protected for accessors, Added return type hint
  {
    // Use null coalescing operator (??) for safety if last name is null
    return trim($this->first_name . ' ' . ($this->last_name ?? '')); // Use ?? '' for clarity
  }


  /**
   * Accessor to get the number of years worked based on timeline entries.
   * Finds the earliest relevant timeline entry (either 'is_sequent' or just the absolute earliest).
   * Calculates the difference in years from that date to today.
   * Note: This method queries the 'timelines' relationship. Consider eager loading 'timelines'
   * when querying Employee models if this is frequently accessed to avoid N+1 issues.
   *
   * @return int The number of worked years.
   */
  protected function getWorkedYearsAttribute(): int // Corrected method visibility to protected for accessors, Added return type hint
  {
    // Assuming 'is_sequent' logic determines employment start date
    // Find the earliest timeline entry where is_sequent is true
    $firstSequentTimeline = $this->timelines()
      ->where('is_sequent', true) // Look for the start of a sequence
      ->orderBy('start_date', 'asc')
      ->first();

    // Fallback to the absolute earliest start date if no sequent found
    // Ensure start_date on Timeline model is cast to date/datetime for comparison
    if (!$firstSequentTimeline) {
      $firstSequentTimeline = $this->timelines()
        ->orderBy('start_date', 'asc')
        ->first();
    }

    // Check if a valid timeline and start date (as Carbon instance) were found
    if ($firstSequentTimeline && $firstSequentTimeline->start_date instanceof Carbon) {
      // Calculate the difference in years from the start date to today
      $years = $firstSequentTimeline->start_date->diffInYears(Carbon::now());
      // Return at least 1 year if the calculated difference is 0 (meaning within the first year)
      // This logic might reflect a specific business rule for rounding up the first year.
      return $years == 0 ? 1 : $years;
    }

    return 0; // Return 0 if no valid start date found in timelines
  }


  /**
   * Accessor to get the employee's current position name based on their latest active timeline.
   * An active timeline is one with a null `end_date`.
   * Note: This method queries the 'timelines' and 'position' relationships. Consider eager loading
   * 'timelines.position' when querying Employee models if this is frequently accessed to avoid N+1 issues.
   *
   * @return string The current position name or '---' if not found.
   */
  protected function getCurrentPositionAttribute(): string // Corrected method visibility to protected for accessors, Added return type hint
  {
    // Find the currently active timeline entry (where end_date is null) and eager load the related position
    // Assumes end_date on Timeline model is cast appropriately
    $data = $this->timelines()
      ->with('position') // Eager load position for efficiency if accessing name
      ->whereNull('end_date') // Find the currently active timeline entry
      ->first();

    // Use optional chaining (?->) and null coalescing (??) for safe access to the position name
    return $data?->position?->name ?? '---';
  }

  /**
   * Accessor to get the employee's current department name based on their latest active timeline.
   * An active timeline is one with a null `end_date`.
   * Note: This method queries the 'timelines' and 'department' relationships. Consider eager loading
   * 'timelines.department' when querying Employee models if this is frequently accessed to avoid N+1 issues.
   *
   * @return string The current department name or '---' if not found.
   */
  protected function getCurrentDepartmentAttribute(): string // Corrected method visibility to protected for accessors, Added return type hint
  {
    // Find the currently active timeline entry (where end_date is null) and eager load the related department
    // Assumes end_date on Timeline model is cast appropriately
    $data = $this->timelines()
      ->with('department') // Eager load department
      ->whereNull('end_date') // Find the currently active timeline entry
      ->first();

    // Use optional chaining (?->) and null coalescing (??) for safe access to the department name
    return $data?->department?->name ?? '---';
  }

  /**
   * Accessor to get the employee's current center name based on their latest active timeline.
   * An active timeline is one with a null `end_date`.
   * Note: This method queries the 'timelines' and 'center' relationships. Consider eager loading
   * 'timelines.center' when querying Employee models if this is frequently accessed to avoid N+1 issues.
   *
   * @return string The current center name or '---' if not found.
   */
  protected function getCurrentCenterAttribute(): string // Corrected method visibility to protected for accessors, Added return type hint
  {
    // Find the currently active timeline entry (where end_date is null) and eager load the related center
    // Assumes end_date on Timeline model is cast appropriately
    $data = $this->timelines()
      ->with('center') // Eager load center
      ->whereNull('end_date') // Find the currently active timeline entry
      ->first();

    // Use optional chaining (?->) and null coalescing (??) for safe access to the center name
    return $data?->center?->name ?? '---';
  }

  /**
   * Accessor to get the employee's join date in a short, human-readable format.
   * This is based on the earliest timeline entry's start date.
   * Note: This method queries the 'timelines' relationship. Consider eager loading 'timelines'
   * when querying Employee models if this is frequently accessed to avoid N+1 issues.
   *
   * @return string The short formatted join date or '---' if no timeline data found.
   */
  protected function getJoinAtShortFormAttribute(): string // Corrected method visibility to protected for accessors, Added return type hint
  {
    // Find the earliest timeline entry to determine join date.
    // Assumes start_date on Timeline model is cast to date/datetime.
    $data = $this->timelines()->orderBy('start_date')->first();

    // Check if a valid timeline entry with a start date (as Carbon instance) was found
    if ($data && $data->start_date instanceof Carbon) {
      // Use diffForHumans on the Carbon instance for a human-readable format (e.g., "3 months ago", "1 year ago")
      return __('Joined') . ' ' . $data->start_date->diffForHumans();
    }

    return '---'; // Return placeholder if no timeline data found
  }

  /**
   * Accessor to get the employee's join date in a formatted string.
   * This is based on the earliest timeline entry's start date.
   * Note: This method queries the 'timelines' relationship. Consider eager loading 'timelines'
   * when querying Employee models if this is frequently accessed to avoid N+1 issues.
   *
   * @return string The formatted join date string or '---' if no timeline data found.
   */
  protected function getJoinAtAttribute(): string // Corrected method visibility to protected for accessors, Added return type hint
  {
    // Find the earliest timeline entry to determine join date.
    // Assumes start_date on Timeline model is cast to date/datetime.
    $data = $this->timelines()->orderBy('start_date')->first();

    // Check if a valid timeline entry with a start date (as Carbon instance) was found
    if ($data && $data->start_date instanceof Carbon) {
      // Use translatedFormat on the Carbon instance for a formatted date string (e.g., "15 Januari 2024")
      return $data->start_date->translatedFormat('j F Y');
    }

    return '---'; // Return placeholder if no timeline data found
  }


  /**
   * Helper function to get the URL to the employee's profile photo.
   * Checks if a related User model exists and has a profile photo path.
   * Assumes profile photos are stored in storage/app/public or public directory
   * and are accessible via Storage::url() or asset().
   * Could be refactored into an Attribute accessor for consistency.
   *
   * @return string The URL to the profile photo or a default photo URL.
   */
  public function getEmployeePhoto(): string // Added return type hint and refined docblock
  {
    // Default photo path relative to storage/app/public or public
    $defaultPhotoPath = 'profile-photos/default-photo.jpg';

    // Find the related user account via the hasOne relationship.
    // Eager load the user relationship if calling this frequently in lists.
    $user = $this->user; // Access the hasOne relationship

    // Check if user exists and has a profile photo path stored
    if ($user && $user->profile_photo_path) {
      // Use Storage::url() for files stored in storage/app/public and linked via storage:link
      return Storage::url($user->profile_photo_path);
      // If photos are stored directly in the public directory:
      // return asset($user->profile_photo_path);
    }

    // Return the URL to the default photo
    // Assuming default photo is also accessible via storage:link
    return Storage::url($defaultPhotoPath);
    // If default photo is in the public directory:
    // return asset($defaultPhotoPath);
  }

  // 👉 Scopes

  /**
   * Scope a query to check for a specific employee leave application entry.
   * Filters Employees who have a related EmployeeLeave record matching the given criteria.
   * Assumes the 'employee_leave' table has 'leave_id', 'from_date', 'to_date', 'start_at', and 'end_at' columns.
   *
   * @param \Illuminate\Database\Eloquent\Builder<\App\Models\Employee> $query The Eloquent query builder instance.
   * @param int $leave_id The ID of the leave type to check for.
   * @param string $from_date The start date of the leave period (YYYY-MM-DD).
   * @param string $to_date The end date of the leave period (YYYY-MM-DD).
   * @param string|null $start_at The start time of leave (HH:MM:SS), if applicable.
   * @param string|null $end_at The end time of leave (HH:MM:SS), if applicable.
   * @return \Illuminate\Database\Eloquent\Builder<\App\Models\Employee> The modified query builder instance.
   */
  public function scopeCheckLeave(
    Builder $query, // Added type hint for Builder
    int $leave_id, // Added type hint
    string $from_date, // Added type hint (assuming string Y-m-d input)
    string $to_date, // Added type hint (assuming string Y-m-d input)
    ?string $start_at = null, // Added type hint and default null
    ?string $end_at = null // Added type hint and default null
  ): Builder { // Added return type hint
    // Use whereHas to filter Employees based on conditions on their related employeeLeaveApplications
    return $query->whereHas('employeeLeaveApplications', function (Builder $query) use ( // Added Builder type hint
      $leave_id,
      $from_date,
      $to_date,
      $start_at,
      $end_at
    ) {
      $query
        ->where('leave_id', $leave_id)
        ->whereDate('from_date', $from_date) // Use whereDate for date comparison (compares only the date part)
        ->whereDate('to_date', $to_date);   // Use whereDate for date comparison

      // Add time checks only if start_at and end_at are provided and not null
      // Assumes start_at and end_at on the employee_leave table are stored as time strings or full timestamps.
      if (!is_null($start_at)) {
        // Use whereTime if the column is specifically a TIME type or only the time part matters
        // Use where for string comparison if stored as HH:MM:SS string
        $query->where('start_at', $start_at);
        // Or if stored as a datetime column and you need to compare the time part:
        // $query->whereTime('start_at', Carbon::parse($start_at)->format('H:i:s'));
      }

      if (!is_null($end_at)) {
        // Use whereTime if the column is specifically a TIME type or only the time part matters
        // Use where for string comparison if stored as HH:MM:SS string
        $query->where('end_at', $end_at);
        // Or if stored as a datetime column and you need to compare the time part:
        // $query->whereTime('end_at', Carbon::parse($end_at)->format('H:i:s'));
      }
    });
  }

  // Add any other existing methods, scopes, or relationships below this line
}
