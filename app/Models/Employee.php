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
use Illuminate\Support\Facades\Storage; // Import Storage facade (used in getEmployeePhoto)
use Illuminate\Support\Facades\Auth; // Auth is likely used by traits/policies, or for created_by/updated_by. Keep if used.


// Import models for relationships
use App\Models\User; // Employee likely linked to User model
use App\Models\Department; // Employee linked to Department
use App\Models\Position; // Employee linked to Position
use App\Models\Grade; // Employee linked to Grade
use App\Models\Center; // Employee linked to Center
use App\Models\Timeline; // Employee has many Timelines (historical positions)
use App\Models\Discount; // Employee has many Discounts (assuming this model exists)
use App\Models\EmployeeLeave; // Employee has many EmployeeLeave records


/**
 * App\Models\Employee
 *
 * Represents an employee record in the HRMS.
 * Linked to a User, Department, Position, Grade, Center, and has Timelines, Leaves, and Discounts.
 * Includes audit trails and soft deletion.
 *
 * @property int $id
 * @property int|null $user_id Foreign key to the users table. One-to-one relationship.
 * @property int|null $department_id Foreign key to the departments table. Many-to-one relationship.
 * @property int|null $position_id Foreign key to the positions table. Many-to-one relationship.
 * @property int|null $grade_id Foreign key to the grades table. Many-to-one relationship.
 * @property int|null $center_id Foreign key to the centers table. Many-to-one relationship.
 * @property string $first_name
 * @property string|null $last_name
 * @property string|null $email Company email address.
 * @property string|null $personal_email Personal email address.
 * @property string|null $phone_number Mobile phone number.
 * @property string|null $nric National registration identity card number.
 * @property string|null $staff_id Unique staff identifier.
 * @property string|null $service_status Employee service status (e.g., permanent, contract).
 * @property string|null $appointment_type Type of appointment.
 * @property string $status Employee status (e.g., active, inactive, on_leave).
 * @property Carbon|null $hire_date Date of hire.
 * @property Carbon|null $date_of_birth Date of birth.
 * @property string|null $address
 * @property string|null $city
 * @property string|null $state
 * @property string|null $postal_code
 * @property string|null $country
 * @property string|null $emergency_contact_name
 * @property string|null $emergency_contact_phone
 * @property string|null $emergency_contact_relationship
 * @property string|null $bank_name
 * @property string|null $bank_account_number
 * @property string|null $tax_identification_number
 * @property string|null $social_security_number
 * @property string|null $profile_photo_path Store photo path if not using Jetstream's HasProfilePhoto on User
 * @property string|null $gender
 * @property string|null $nationality
 * @property string|null $marital_status
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property int|null $deleted_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read User|null $user The associated User model.
 * @property-read Department|null $department The associated Department model.
 * @property-read Position|null $position The associated Position model.
 * @property-read Grade|null $grade The associated Grade model.
 * @property-read Center|null $center The associated Center model.
 * @property-read Collection<int, Timeline> $timelines The employee's historical position/assignment records.
 * @property-read Collection<int, Discount> $discounts The employee's discounts.
 * @property-read Collection<int, EmployeeLeave> $leaveRequests The employee's leave application records.
 * @property-read string $full_name The employee's full name (accessor).
 * @property-read string $profile_photo_url The URL to the employee's profile photo (accessor).
 * @property-read Timeline|null $currentTimeline The employee's most recent active timeline record.
 * @property-read Position|null $currentPosition The employee's current position via timeline (accessor).
 * @property-read Department|null $currentDepartment The employee's current department via timeline (accessor).
 * @property-read Center|null $currentCenter The employee's current center via timeline (accessor).
 */
class Employee extends Model
{
  use HasFactory, SoftDeletes, CreatedUpdatedDeletedBy;

  protected $fillable = [
    'user_id',
    'department_id',
    'position_id',
    'grade_id',
    'center_id',
    'first_name',
    'last_name',
    'email',
    'personal_email',
    'phone_number',
    'nric',
    'staff_id',
    'service_status',
    'appointment_type',
    'status',
    'hire_date',
    'date_of_birth',
    'address',
    'city',
    'state',
    'postal_code',
    'country',
    'emergency_contact_name',
    'emergency_contact_phone',
    'emergency_contact_relationship',
    'bank_name',
    'bank_account_number',
    'tax_identification_number',
    'social_security_number',
    'profile_photo_path',
    'gender',
    'nationality',
    'marital_status',
    // created_by, updated_by, deleted_by handled by trait
  ];

  protected $casts = [
    'hire_date' => 'date',
    'date_of_birth' => 'date',
    'created_at' => 'datetime',
    'updated_at' => 'datetime',
    'deleted_at' => 'datetime',
    'user_id' => 'integer',
    'department_id' => 'integer',
    'position_id' => 'integer',
    'grade_id' => 'integer',
    'center_id' => 'integer',
  ];

  // --- Relationships ---

  /**
   * Get the user account associated with the employee.
   * Defines a one-to-one relationship.
   * Assumes 'users' table has an 'employee_id' foreign key, or 'employees' has a 'user_id' foreign key.
   * Based on PHPDoc and fillable, 'employees' table has 'user_id'.
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\User, \App\Models\Employee>
   */
  public function user(): BelongsTo // Added return type hint
  {
    // Assumes 'employees' table has 'user_id' foreign key
    return $this->belongsTo(User::class, 'user_id');
  }


  /**
   * Get the department the employee belongs to.
   * Defines a many-to-one relationship.
   * Assumes 'employees' table has 'department_id' foreign key.
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Department, \App\Models\Employee>
   */
  public function department(): BelongsTo // Added return type hint
  {
    return $this->belongsTo(Department::class, 'department_id');
  }

  /**
   * Get the position the employee holds.
   * Defines a many-to-one relationship.
   * Assumes 'employees' table has 'position_id' foreign key.
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Position, \App\Models\Employee>
   */
  public function position(): BelongsTo // Added return type hint
  {
    return $this->belongsTo(Position::class, 'position_id');
  }

  /**
   * Get the grade the employee belongs to.
   * Defines a many-to-one relationship.
   * Assumes 'employees' table has 'grade_id' foreign key.
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Grade, \App\Models\Employee>
   */
  public function grade(): BelongsTo // Added return type hint
  {
    return $this->belongsTo(Grade::class, 'grade_id');
  }

  /**
   * Get the center the employee is assigned to.
   * Defines a many-to-one relationship.
   * Assumes 'employees' table has 'center_id' foreign key.
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Center, \App\Models\Employee>
   */
  public function center(): BelongsTo // Added return type hint
  {
    return $this->belongsTo(Center::class, 'center_id');
  }


  /**
   * Get the historical timeline records for the employee.
   * Defines a one-to-many relationship.
   * Assumes 'timelines' table has 'employee_id' foreign key.
   *
   * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\Timeline>
   */
  public function timelines(): HasMany // Added return type hint
  {
    return $this->hasMany(Timeline::class, 'employee_id');
  }

  /**
   * Get the discounts associated with the Employee.
   * Defines a one-to-many relationship.
   * Assumes a 'discounts' table with an 'employee_id' foreign key.
   *
   * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\Discount>
   */
  public function discounts(): HasMany // Added return type hint
  {
    // Adjust 'App\\Models\\Discount' if your Discount model is in a different namespace
    // Adjust 'employee_id' if the foreign key column name on the discounts table is different
    return $this->hasMany(Discount::class, 'employee_id'); // Assuming Discount model and foreign key
  }

  /**
   * Get the leave application records for the employee.
   * Defines a one-to-many relationship to the pivot model EmployeeLeave.
   * Assumes 'employee_leave' table has 'employee_id' foreign key.
   *
   * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\EmployeeLeave>
   */
  public function leaveRequests(): HasMany // Added return type hint
  {
    // Assuming 'employee_leave' table is the pivot and has 'employee_id' FK
    // and EmployeeLeave model represents records in that table.
    return $this->hasMany(EmployeeLeave::class, 'employee_id');
  }


  // --- Accessors and Mutators ---

  /**
   * Get the employee's full name.
   * Combines first and last names.
   *
   * @return \Illuminate\Database\Eloquent\Casts\Attribute
   */
  protected function fullName(): Attribute // Added return type hint
  {
    return Attribute::make(
      get: fn(mixed $value, array $attributes) => trim($attributes['first_name'] . ' ' . $attributes['last_name']),
    );
  }

  /**
   * Get the URL to the employee's profile photo.
   * Provides a default avatar if no photo is set.
   *
   * @return \Illuminate\Database\Eloquent\Casts\Attribute
   */
  protected function profilePhotoUrl(): Attribute // Added return type hint
  {
    return Attribute::make(
      get: fn(mixed $value, array $attributes) => $attributes['profile_photo_path']
        ? Storage::url($attributes['profile_photo_path'])
        : 'https://ui-avatars.com/api/?name=' . urlencode($this->full_name) . '&color=7F9CF5&background=EBF4FF',
    );
  }

  /**
   * Get the employee's current timeline record (most recent with end_date null).
   * Defines a one-to-one relationship (or can be a computed property).
   * Let's define it as a relationship for easier eager loading.
   * Assumes 'timelines' table has 'employee_id' FK and 'end_date'.
   *
   * @return \Illuminate\Database\Eloquent\Relations\HasOne<\App\Models\Timeline>
   */
  public function currentTimeline(): HasOne // Added return type hint
  {
    return $this->hasOne(Timeline::class, 'employee_id')->latest('start_date')->whereNull('end_date');
  }

  /**
   * Accessor for the employee's current position via their current timeline.
   *
   * @return \Illuminate\Database\Eloquent\Casts\Attribute
   */
  protected function currentPosition(): Attribute // Added return type hint
  {
    return Attribute::make(
      get: fn() => $this->currentTimeline?->position // Use null-safe operator
    );
  }

  /**
   * Accessor for the employee's current department via their current timeline.
   *
   * @return \Illuminate\Database\Eloquent\Casts\Attribute
   */
  protected function currentDepartment(): Attribute // Added return type hint
  {
    return Attribute::make(
      get: fn() => $this->currentTimeline?->department // Use null-safe operator
    );
  }

  /**
   * Accessor for the employee's current center via their current timeline.
   *
   * @return \Illuminate\Database\Eloquent\Casts\Attribute
   */
  protected function currentCenter(): Attribute // Added return type hint
  {
    return Attribute::make(
      get: fn() => $this->currentTimeline?->center // Use null-safe operator
    );
  }


  // --- Scopes ---

  /**
   * Apply a scope to only include active employees.
   * Assumes 'status' column exists.
   *
   * @param  \Illuminate\Database\Eloquent\Builder  $query
   * @return void
   */
  public function scopeActive(Builder $query): void // Added return type hint
  {
    $query->where('status', 'active'); // Assuming 'active' is a status value
  }

  /**
   * Apply a scope to search employees by first name, last name, or staff ID.
   *
   * @param  \Illuminate\Database\Eloquent\Builder  $query
   * @param  string  $search The search term.
   * @return void
   */
  public function scopeSearch(Builder $query, string $search): void // Added return type hint
  {
    $query->where('first_name', 'like', '%' . $search . '%')
      ->orWhere('last_name', 'like', '%' . $search . '%')
      ->orWhere('staff_id', 'like', '%' . $search . '%');
  }


  /**
   * Scope to include employees on leave.
   * Assumes Employee has a 'leaveRequests' relationship to EmployeeLeave,
   * and EmployeeLeave model has 'from_date', 'to_date', and 'status' columns.
   *
   * @param  \Illuminate\Database\Eloquent\Builder  $query
   * @return void
   */
  public function scopeOnLeave(Builder $query): void // Added return type hint
  {
    $today = Carbon::today();
    // Use the correct relationship name 'leaveRequests' and the correct model 'EmployeeLeave'
    $query->whereHas('leaveRequests', function (Builder $query) use ($today) {
      $query->where('status', 'approved') // Assuming a status field on EmployeeLeave
        ->whereDate('from_date', '<=', $today) // Assuming 'from_date' column on EmployeeLeave
        ->whereDate('to_date', '>=', $today); // Assuming 'to_date' column on EmployeeLeave
    });
  }


  // Scopes based on the traceback from ReportsController
  // These methods would be in the ReportController, not the Employee model itself,
  // but listing them here for context of what queries the model supports.
  // Example: public function scopeWithCounts(Builder $query): void // Example for userActivity report
  // {
  //      // Assuming these relationships exist on Employee or the related User model
  //      $query->withCount(['loanApplications', 'emailApplications', 'approvals']); // Assumes relationships exist on Employee
  //      // If these relationships are on the User model: $query->with('user')->withCount(['user.loanApplications', 'user.emailApplications', 'user.approvals']);
  // }


}
