<?php

namespace App\Models;

use App\Traits\CreatedUpdatedDeletedBy; // Assuming this trait exists and adds audit FKs/methods
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder; // Import Builder for scope type hinting
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo; // Use BelongsTo trait
use Illuminate\Database\Eloquent\Relations\BelongsToMany; // Use BelongsToMany trait
use Illuminate\Database\Eloquent\Relations\HasMany; // Use HasMany trait
use Illuminate\Database\Eloquent\Relations\HasOne; // Use HasOne trait
use Illuminate\Database\Eloquent\SoftDeletes; // Use SoftDeletes trait
use Illuminate\Database\Eloquent\Collection; // Import Collection for return type hinting
use Illuminate\Database\Eloquent\Relations\BelongsTo as BelongsToRelation; // Alias if needed
use Illuminate\Database\Eloquent\Relations\BelongsToMany as BelongsToManyRelation; // Alias if needed
use Illuminate\Database\Eloquent\Relations\HasMany as HasManyRelation; // Alias if needed
use Illuminate\Database\Eloquent\Relations\HasOne as HasOneRelation; // Alias if needed
use Illuminate\Support\Facades\Storage; // Import Storage facade


// Import models for relationships
use App\Models\User;
use App\Models\Contract;
use App\Models\Department; // Employee has department_id FK
use App\Models\Position; // Employee has position_id FK
use App\Models\Grade; // Employee has grade_id FK
use App\Models\Fingerprint;
use App\Models\Discount;
use App\Models\Timeline;
use App\Models\Leave; // For belongsToMany with leaves
use App\Models\EmployeeLeave; // For HasMany to pivot model
use App\Models\Message;
use App\Models\Transition;
use App\Models\Equipment; // If equipment is assigned via assigned_to_employee_id


/**
 * App\Models\Employee
 *
 * @property int $id
 * @property int|null $contract_id Foreign key to contracts table.
 * @property int|null $department_id Foreign key to departments table.
 * @property int|null $position_id Foreign key to positions table.
 * @property int|null $grade_id Foreign key to grades table.
 * @property string $first_name Employee's first name.
 * @property string|null $father_name Employee's father's name.
 * @property string|null $last_name Employee's last name.
 * @property string|null $mother_name Employee's mother's name.
 * @property string|null $birth_and_place Employee's birth date and place (as string).
 * @property string|null $national_number Employee's national identification number.
 * @property string|null $mobile_number Employee's mobile phone number.
 * @property string|null $degree Employee's degree or qualification.
 * @property string|null $gender Employee's gender.
 * @property string|null $address Employee's address.
 * @property string|null $notes Additional notes about the employee.
 * @property int $balance_leave_allowed Employee's remaining leave balance.
 * @property int $max_leave_allowed Employee's maximum allowed leave.
 * @property string|null $delay_counter Counter for delays (time format string).
 * @property string|null $hourly_counter Counter for hourly work (time format string).
 * @property bool $is_active Indicates if the employee is currently active.
 * @property string|null $profile_photo_path Path to the employee's profile photo.
 * @property int|null $created_by Foreign key to the user who created the record.
 * @property int|null $updated_by Foreign key to the user who last updated the record.
 * @property int|null $deleted_by Foreign key to the user who soft deleted the record.
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Equipment> $assignedEquipment Equipment currently assigned to the employee.
 * @property-read int|null $assigned_equipment_count
 * @property-read \App\Models\Contract|null $contract The employee's contract type.
 * @property-read \App\Models\User|null $createdBy The user who created the record.
 * @property-read \App\Models\Department|null $department The employee's department.
 * @property-read \App\Models\User|null $deletedBy The user who soft deleted the record.
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Discount> $discounts Discounts associated with the employee.
 * @property-read int|null $discounts_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\EmployeeLeave> $employeeLeaveApplications Individual employee leave applications.
 * @property-read int|null $employee_leave_applications_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Fingerprint> $fingerprints Fingerprint records for the employee.
 * @property-read int|null $fingerprints_count
 * @property-read \App\Models\Grade|null $grade The employee's grade.
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Leave> $leaves Leave types associated via pivot.
 * @property-read int|null $leaves_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Message> $messages Messages sent by the employee.
 * @property-read int|null $messages_count
 * @property-read \App\Models\Position|null $position The employee's position.
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Timeline> $timelines Timeline entries for assignments.
 * @property-read int|null $timelines_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Transition> $transitions Equipment transitions for the employee.
 * @property-read int|null $transitions_count
 * @property-read \App\Models\User|null $updatedBy The user who last updated the record.
 * @property-read \App\Models\User|null $user The user account associated with the employee.
 * @property-read string $fullName Employee's full name.
 * @property-read string $shortName Employee's short name.
 * @property-read int $workedYears Number of years worked based on timelines.
 * @property-read string $currentPosition Employee's current position name.
 * @property-read string $currentDepartment Employee's current department name.
 * @property-read string $currentCenter Employee's current center name.
 * @property-read string $joinAtShortForm Employee's join date in short form.
 * @property-read string $joinAt Employee's join date in formatted string.
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
 * @mixin \Eloquent
 */
class Employee extends Model
{
  // Use the traits for factory, soft deletes, and audit columns
  use CreatedUpdatedDeletedBy, HasFactory, SoftDeletes;

  /**
   * The attributes that are mass assignable.
   * Includes personal, work, and counter fields from the migration.
   * 'id' is excluded as it is the primary key.
   *
   * @var array<int, string>
   */
  protected $fillable = [
    'contract_id', // Foreign key to contracts table
    'department_id', // Foreign key to departments table
    'position_id', // Foreign key to positions table
    'grade_id', // Foreign key to grades table

    'first_name',
    'father_name',
    'last_name',
    'mother_name',
    'birth_and_place', // Might need casting depending on storage, cast as string
    'national_number',
    'mobile_number',
    'degree',
    'gender',
    'address',
    'notes',

    'balance_leave_allowed',
    'max_leave_allowed',
    'delay_counter', // Time format string
    'hourly_counter', // Time format string
    'is_active', // Boolean flag

    'profile_photo_path', // Path to employee photo

    // 'created_by', // Handled by trait
    // 'updated_by', // Handled by trait
    // 'deleted_by', // Handled by trait
  ];

  /**
   * The attributes that should be cast.
   * Includes casts for boolean, integer counters, time strings, timestamps, soft deletes.
   * Explicitly casting all attributes for clarity.
   *
   * @var array<string, string>
   */
  protected $casts = [
    'contract_id' => 'integer', // Cast FKs to integer
    'department_id' => 'integer',
    'position_id' => 'integer',
    'grade_id' => 'integer',

    'first_name' => 'string', // Explicitly cast string attributes
    'father_name' => 'string',
    'last_name' => 'string',
    'mother_name' => 'string',
    'birth_and_place' => 'string', // Casting as string based on attribute name
    'national_number' => 'string',
    'mobile_number' => 'string',
    'degree' => 'string',
    'gender' => 'string',
    'address' => 'string',
    'notes' => 'string',

    'balance_leave_allowed' => 'integer', // Cast integer counters
    'max_leave_allowed' => 'integer',
    'delay_counter' => 'string', // Casting as string as per original code
    'hourly_counter' => 'string', // Casting as string as per original code

    'is_active' => 'boolean', // Cast boolean flag
    'profile_photo_path' => 'string', // Explicitly cast profile photo path as string

    'created_at' => 'datetime', // Explicitly cast timestamps
    'updated_at' => 'datetime',
    'deleted_at' => 'datetime', // Cast soft delete timestamp
  ];

  // The CreatedUpdatedDeletedBy trait is assumed to add these audit relationships:
  // public function createdBy(): BelongsTo;
  // public function updatedBy(): BelongsTo;
  // public function deletedBy(): BelongsTo;


  // 👉 Relationships

  /**
   * Get the user account associated with the employee.
   * Defines a one-to-one relationship where an Employee has one User account.
   * Assumes the 'users' table has an 'employee_id' foreign key.
   *
   * @return \Illuminate\Database\Eloquent\Relations\HasOne
   */
  public function user(): HasOne // Added return type hint
  {
    // Defines a one-to-one relationship with the User model
    return $this->hasOne(User::class, 'employee_id'); // Explicitly define FK
  }

  /**
   * Get the contract type associated with the employee.
   * Defines a many-to-one relationship where an Employee belongs to one Contract.
   * Assumes the 'employees' table has a 'contract_id' foreign key.
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
   */
  public function contract(): BelongsTo // Added return type hint
  {
    // Defines a many-to-one relationship with the Contract model
    return $this->belongsTo(Contract::class, 'contract_id'); // Explicitly define FK
  }

  /**
   * Get the department associated with the employee.
   * Defines a many-to-one relationship where an Employee belongs to one Department.
   * Assumes the 'employees' table has a 'department_id' foreign key.
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
   */
  public function department(): BelongsTo // Added return type hint
  {
    // Defines a many-to-one relationship with the Department model
    return $this->belongsTo(Department::class, 'department_id'); // Explicitly define FK
  }

  /**
   * Get the position associated with the employee.
   * Defines a many-to-one relationship where an Employee belongs to one Position.
   * Assumes the 'employees' table has a 'position_id' foreign key.
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
   */
  public function position(): BelongsTo // Added return type hint
  {
    // Defines a many-to-one relationship with the Position model
    return $this->belongsTo(Position::class, 'position_id'); // Explicitly define FK
  }

  /**
   * Get the grade associated with the employee.
   * Defines a many-to-one relationship where an Employee belongs to one Grade.
   * Assumes the 'employees' table has a 'grade_id' foreign key.
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
   */
  public function grade(): BelongsTo // Added return type hint
  {
    // Defines a many-to-one relationship with the Grade model
    return $this->belongsTo(Grade::class, 'grade_id'); // Explicitly define FK
  }

  /**
   * Get the fingerprint records for the employee.
   * Defines a one-to-many relationship where an Employee has many Fingerprints.
   * Assumes the 'fingerprints' table has an 'employee_id' foreign key.
   *
   * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\Fingerprint>
   */
  public function fingerprints(): HasMany // Added return type hint
  {
    // Defines a one-to-many relationship with the Fingerprint model
    return $this->hasMany(Fingerprint::class, 'employee_id'); // Explicitly define FK
  }

  /**
   * Get the discount records for the employee.
   * Defines a one-to-many relationship where an Employee has many Discounts.
   * Assumes the 'discounts' table has an 'employee_id' foreign key.
   *
   * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\Discount>
   */
  public function discounts(): HasMany // Added return type hint
  {
    // Defines a one-to-many relationship with the Discount model
    return $this->hasMany(Discount::class, 'employee_id'); // Explicitly define FK
  }

  /**
   * Get the timeline entries for the employee's assignments.
   * Defines a one-to-many relationship where an Employee has many Timelines.
   * Assumes the 'timelines' table has an 'employee_id' foreign key.
   *
   * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\Timeline>
   */
  public function timelines(): HasMany // Added return type hint
  {
    // Defines a one-to-many relationship with the Timeline model
    return $this->hasMany(Timeline::class, 'employee_id'); // Explicitly define FK
  }

  /**
   * Get the individual employee leave applications for the employee.
   * This is a HasMany relationship to the pivot table model (EmployeeLeave).
   * Assumes the 'employee_leave' table has an 'employee_id' foreign key.
   *
   * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\EmployeeLeave>
   */
  public function employeeLeaveApplications(): HasMany // Added return type hint
  {
    // Defines a one-to-many relationship with the EmployeeLeave pivot model
    return $this->hasMany(EmployeeLeave::class, 'employee_id');
  }

  /**
   * Get the leave types associated with the employee through the pivot table.
   * This is a many-to-many relationship.
   * Assumes a 'employee_leave' pivot table linking employees and leaves
   * with columns 'employee_id' and 'leave_id'.
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany<\App\Models\Leave>
   */
  public function leaves(): BelongsToMany // Added return type hint
  {
    // Defines a many-to-many relationship with the Leave model via the 'employee_leave' pivot table
    return $this->belongsToMany(Leave::class, 'employee_leave', 'employee_id', 'leave_id')
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
      ->as('application') // Name the pivot attribute for easier access, e.g., $employee->leaves[0]->application->from_date
      ->withCasts([
        'id' => 'integer', // Cast pivot ID
        'from_date' => 'date',
        'to_date' => 'date',
        'start_at' => 'datetime', // Or 'time' depending on storage and how you use it
        'end_at' => 'datetime', // Or 'time'
        'is_authorized' => 'boolean',
        'is_checked' => 'boolean',
        'created_by' => 'integer', // Cast pivot audit FKs to integer
        'updated_by' => 'integer',
        'deleted_by' => 'integer',
        'created_at' => 'datetime', // Cast pivot timestamps
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime', // Cast pivot soft delete timestamp
      ]);
  }


  /**
   * Get the messages sent by the employee.
   * Defines a one-to-many relationship where an Employee has many Messages.
   * Assumes the 'messages' table has an 'employee_id' foreign key.
   *
   * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\Message>
   */
  public function messages(): HasMany // Added return type hint
  {
    // Defines a one-to-many relationship with the Message model
    return $this->hasMany(Message::class, 'employee_id'); // Explicitly define FK
  }

  /**
   * Get the equipment transitions for the employee.
   * Defines a one-to-many relationship where an Employee has many Transitions.
   * Assumes the 'transitions' table has an 'employee_id' foreign key.
   *
   * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\Transition>
   */
  public function transitions(): HasMany // Added return type hint
  {
    // Defines a one-to-many relationship with the Transition model
    return $this->hasMany(Transition::class, 'employee_id'); // Explicitly define FK
  }

  /**
   * Get the equipment currently assigned to the employee.
   * Defines a one-to-many relationship where an Employee has many Equipment items.
   * Assumes the 'equipment' table has an 'assigned_to_employee_id' foreign key.
   *
   * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\Equipment>
   */
  public function assignedEquipment(): HasMany // Added return type hint
  {
    // Defines a one-to-many relationship with the Equipment model
    return $this->hasMany(Equipment::class, 'assigned_to_employee_id');
  }


  // The CreatedUpdatedDeletedBy trait is assumed to add these audit relationships:

  /**
   * Get the user who created the model.
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\User, \App\Models\Employee>
   */
  // public function createdBy(): BelongsToRelation;

  /**
   * Get the user who last updated the model.
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\User, \App\Models\Employee>
   */
  // public function updatedBy(): BelongsToRelation;

  /**
   * Get the user who soft deleted the model.
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\User, \App\Models\Employee>
   */
  // public function deletedBy(): BelongsToRelation;


  // 👉 Attributes (Accessors/Mutators)

  /**
   * Get or set the employee's hourly counter.
   * Formats the time for display.
   *
   * @return \Illuminate\Database\Eloquent\Casts\Attribute
   */
  protected function hourlyCounter(): Attribute // Added return type hint
  {
    return Attribute::make(
      // Accessor: format time string or Carbon instance
      get: fn(?string $value) => $value ? Carbon::parse($value)->format('H:i') : null, // Use ?string for nullable column
      // Mutator: store as HH:MM:SS string
      set: fn(?string $value) => $value, // Use ?string for nullable column
    );
  }

  /**
   * Get or set the employee's delay counter.
   * Formats the time for display.
   *
   * @return \Illuminate\Database\Eloquent\Casts\Attribute
   */
  protected function delayCounter(): Attribute // Added return type hint
  {
    return Attribute::make(
      // Accessor: format time string or Carbon instance
      get: fn(?string $value) => $value ? Carbon::parse($value)->format('H:i') : null, // Use ?string for nullable column
      // Mutator: store as HH:MM:SS string
      set: fn(?string $value) => $value, // Use ?string for nullable column
    );
  }

  /**
   * Get the employee's full name (concatenation of name parts).
   *
   * @return string
   */
  public function getFullNameAttribute(): string // Added return type hint
  {
    // Use null coalescing operator (??) for safety if name parts are null
    return trim($this->first_name . ' ' . ($this->father_name ?? '') . ' ' . ($this->last_name ?? ''));
  }

  /**
   * Get the employee's short name (first and last name).
   *
   * @return string
   */
  public function getShortNameAttribute(): string // Added return type hint
  {
    // Use null coalescing operator (??) for safety if last name is null
    return trim($this->first_name . ' ' . ($this->last_name ? $this->last_name : ''));
  }


  /**
   * Get the number of years worked based on timeline entries.
   * Note: This method queries the 'timelines' relationship. Consider eager loading 'timelines'
   * when querying Employee models if this is frequently accessed to avoid N+1 issues.
   *
   * @return int
   */
  function getWorkedYearsAttribute(): int // Removed 'public' as it's not allowed for accessors
  {
    // Assuming 'is_sequent' logic determines employment start date
    // Find the earliest timeline entry where is_sequent is true
    $firstSequentTimeline = $this->timelines()
      ->where('is_sequent', true) // Look for the start of a sequence
      ->orderBy('start_date', 'asc')
      ->first();

    // Fallback to the absolute earliest start date if no sequent found
    if (!$firstSequentTimeline) {
      $firstSequentTimeline = $this->timelines()
        ->orderBy('start_date', 'asc')
        ->first();
    }

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
   * Get the employee's current position name based on their latest active timeline.
   * Note: This method queries the 'timelines' and 'position' relationships. Consider eager loading
   * 'timelines.position' when querying Employee models if this is frequently accessed to avoid N+1 issues.
   *
   * @return string
   */
  public function getCurrentPositionAttribute(): string // Added return type hint
  {
    // Find the currently active timeline entry and eager load the related position
    $data = $this->timelines()
      ->with('position') // Eager load position for efficiency if accessing name
      ->whereNull('end_date') // Find the currently active timeline entry
      ->first();

    // Use optional chaining and null coalescing for safety
    return $data?->position?->name ?? '---';
  }

  /**
   * Get the employee's current department name based on their latest active timeline.
   * Note: This method queries the 'timelines' and 'department' relationships. Consider eager loading
   * 'timelines.department' when querying Employee models if this is frequently accessed to avoid N+1 issues.
   *
   * @return string
   */
  public function getCurrentDepartmentAttribute(): string // Added return type hint
  {
    // Find the currently active timeline entry and eager load the related department
    $data = $this->timelines()
      ->with('department') // Eager load department
      ->whereNull('end_date') // Find the currently active timeline entry
      ->first();

    // Use optional chaining and null coalescing for safety
    return $data?->department?->name ?? '---';
  }

  /**
   * Get the employee's current center name based on their latest active timeline.
   * Note: This method queries the 'timelines' and 'center' relationships. Consider eager loading
   * 'timelines.center' when querying Employee models if this is frequently accessed to avoid N+1 issues.
   *
   * @return string
   */
  public function getCurrentCenterAttribute(): string // Added return type hint
  {
    // Find the currently active timeline entry and eager load the related center
    $data = $this->timelines()
      ->with('center') // Eager load center
      ->whereNull('end_date') // Find the currently active timeline entry
      ->first();

    // Use optional chaining and null coalescing for safety
    return $data?->center?->name ?? '---';
  }

  /**
   * Get the employee's join date in a short, human-readable format.
   * This is based on the earliest timeline entry's start date.
   * Note: This method queries the 'timelines' relationship. Consider eager loading 'timelines'
   * when querying Employee models if this is frequently accessed to avoid N+1 issues.
   *
   * @return string
   */
  public function getJoinAtShortFormAttribute(): string // Added return type hint
  {
    // Find the earliest timeline entry to determine join date
    // Assumes start_date on Timeline model is cast to date/datetime
    $data = $this->timelines()->orderBy('start_date')->first();

    if ($data && $data->start_date instanceof Carbon) {
      // Use diffForHumans on the Carbon instance for a readable format
      return __('Joined') . ' ' . $data->start_date->diffForHumans();
    }

    return '---'; // Return placeholder if no timeline data found
  }

  /**
   * Get the employee's join date in a formatted string.
   * This is based on the earliest timeline entry's start date.
   * Note: This method queries the 'timelines' relationship. Consider eager loading 'timelines'
   * when querying Employee models if this is frequently accessed to avoid N+1 issues.
   *
   * @return string
   */
  public function getJoinAtAttribute(): string // Added return type hint
  {
    // Find the earliest timeline entry to determine join date
    // Assumes start_date on Timeline model is cast to date/datetime
    $data = $this->timelines()->orderBy('start_date')->first();

    if ($data && $data->start_date instanceof Carbon) {
      // Use translatedFormat on the Carbon instance for a formatted date string
      return $data->start_date->translatedFormat('j F Y');
    }

    return '---'; // Return placeholder if no timeline data found
  }


  /**
   * Get the URL to the employee's profile photo.
   * Checks if a related User model exists and has a profile photo path.
   * Assumes profile photos are stored in storage/app/public or public directory.
   *
   * @return string The URL to the profile photo or a default photo URL.
   */
  public function getEmployeePhoto(): string // Added return type hint and refined docblock
  {
    // Default photo path relative to storage/app/public or public
    $defaultPhotoPath = 'profile-photos/default-photo.jpg';

    // Find the related user account via the hasOne relationship
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
   * Refactored to correctly use where on the related EmployeeLeave model via whereHas.
   *
   * @param \Illuminate\Database\Eloquent\Builder<\App\Models\Employee> $query The Eloquent query builder instance.
   * @param int $leave_id The ID of the leave type.
   * @param string $from_date The start date of the leave period (YYYY-MM-DD).
   * @param string $to_date The end date of the leave period (YYYY-MM-DD).
   * @param string|null $start_at The start time of leave (HH:MM:SS), if applicable.
   * @param string|null $end_at The end time of leave (HH:MM:SS), if applicable.
   * @return \Illuminate\Database\Eloquent\Builder<\App\Models\Employee> // Return type hint
   */
  public function scopeCheckLeave(
    Builder $query, // Added type hint for Builder
    int $leave_id, // Added type hint
    string $from_date, // Added type hint (assuming string Y-m-d input)
    string $to_date, // Added type hint (assuming string Y-m-d input)
    ?string $start_at = null, // Added type hint and default null
    ?string $end_at = null // Added type hint and default null
  ): Builder { // Added return type hint
    // Query the employeeLeaveApplications relationship (HasMany to the pivot model)
    return $query->whereHas('employeeLeaveApplications', function (Builder $query) use ( // Added Builder type hint
      $leave_id,
      $from_date,
      $to_date,
      $start_at,
      $end_at
    ) {
      $query
        ->where('leave_id', $leave_id)
        ->whereDate('from_date', $from_date) // Use whereDate for date comparison
        ->whereDate('to_date', $to_date); // Use whereDate for date comparison

      // Add time checks only if start_at and end_at are provided
      if (!is_null($start_at)) {
        $query->where('start_at', $start_at);
      }
      if (!is_null($end_at)) {
        $query->where('end_at', $end_at);
      }
      // Do NOT add where('employee_id', ...) here, it's already scoped by the HasMany relation via whereHas
    });
    // Note: If you prefer using the BelongsToMany relationship 'leaves' with wherePivot,
    // the scope would look different and query the pivot table directly.
  }


  // 👉 Functions (Other methods not accessors/mutators)


  // Add any other methods below this line
}
