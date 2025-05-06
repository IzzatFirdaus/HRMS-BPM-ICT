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
use App\Models\Center; // Employee linked to Center
use App\Models\Position; // Employee linked to Position
use App\Models\Grade; // Assuming a Grade model exists and is linked to Employee
use App\Models\Unit; // Use statement for Unit
use App\Models\Timeline; // Assuming a Timeline model exists for employment history
use App\Models\EmployeeLeave; // Seems to be the Leave Request model
use App\Models\Attendance; // Use statement for Attendance
use App\Models\LoanApplication; // Assuming employee can have loan applications
use App\Models\EmailApplication; // Assuming employee can have email applications
use App\Models\Approval; // Assuming employee can be an approver


/**
 * App\Models\Employee
 *
 * Represents an employee within the HRMS.
 * Stores personal details, employment information, relationships to
 * organizational units, and potentially links to user accounts, leave, and attendance.
 * Includes audit trails and soft deletion.
 *
 * @property int $id
 * @property string $staff_id Unique staff identification number.
 * @property int|null $user_id Foreign key to the users table (optional - if every employee is a user).
 * @property string $name Full name of the employee.
 * @property string|null $display_name Name used for display (e.g., nickname, common name).
 * @property string $ic_number Identity Card (IC) number.
 * @property string $passport_number Passport number (if applicable).
 * @property string $phone_number Primary contact number.
 * @property string|null $emergency_contact_name
 * @property string|null $emergency_contact_number
 * @property string|null $date_of_birth
 * @property string|null $gender // e.g., 'Male', 'Female'
 * @property string|null $marital_status
 * @property int|null $department_id Foreign key to the departments table.
 * @property int|null $center_id Foreign key to the centers table.
 * @property int|null $unit_id Foreign key to the units table.
 * @property int|null $position_id Foreign key to the positions table.
 * @property int|null $grade_id Foreign key to the grades table.
 * @property string|null $employment_status // e.g., 'permanent', 'contract', 'intern'
 * @property string|null $office_phone_number
 * @property string|null $office_email
 * @property string|null $personal_email
 * @property string|null $address_line_1
 * @property string|null $address_line_2
 * @property string|null $postcode
 * @property string|null $city
 * @property string|null $state
 * @property string|null $country
 * @property string|null $photo_path Storage path to employee photo.
 * @property Carbon|null $joining_date Date of joining the organization.
 * @property Carbon|null $confirmation_date Date of confirmation (if permanent).
 * @property Carbon|null $contract_end_date Date of contract expiry (if contract).
 * @property Carbon|null $resignation_date Date of resignation.
 * @property string $service_status // e.g., 'active', 'inactive', 'suspended' - Assuming this is different from employment_status
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property int|null $deleted_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 *
 * // Relationships (for static analysis)
 * @property-read \App\Models\User|null $user
 * @property-read \App\Models\Department|null $department
 * @property-read \App\Models\Center|null $center
 * @property-read \App\Models\Unit|null $unit // Corrected PHPDoc type hint for BelongsTo
 * @property-read \App\Models\Position|null $position
 * @property-read \App\Models\Grade|null $grade
 * @property-read \App\Models\Timeline|null $currentTimeline
 * @property-read \Illuminate\Database\Eloquent\Collection<\App\Models\Timeline> $timeline
 * @property-read \Illuminate\Database\Eloquent\Collection<\App\Models\EmployeeLeave> $leaveRequests
 * @property-read \Illuminate\Database\Eloquent\Collection<\App\Models\Attendance> $attendances
 * @property-read \Illuminate\Database\Eloquent\Collection<\App\Models\LoanApplication> $loanApplications // Assuming employee can have loan applications
 * @property-read \Illuminate\Database\Eloquent\Collection<\App\Models\EmailApplication> $emailApplications // Assuming employee can have email applications
 * @property-read \Illuminate\Database\Eloquent\Collection<\App\Models\Approval> $approvals // Assuming employee can be an approver
 */
class Employee extends Model
{
  use CreatedUpdatedDeletedBy, HasFactory, SoftDeletes;

  protected $table = 'employees'; // Explicitly define table name if it deviates from convention

  protected $fillable = [
    'staff_id',
    'user_id',
    'name',
    'display_name',
    'ic_number',
    'passport_number',
    'phone_number',
    'emergency_contact_name',
    'emergency_contact_number',
    'date_of_birth',
    'gender',
    'marital_status',
    'department_id',
    'center_id',
    'unit_id',
    'position_id',
    'grade_id',
    'employment_status',
    'office_phone_number',
    'office_email',
    'personal_email',
    'address_line_1',
    'address_line_2',
    'postcode',
    'city',
    'state',
    'country',
    'photo_path',
    'joining_date',
    'confirmation_date',
    'contract_end_date',
    'resignation_date',
    'service_status',
    // created_by, updated_by, deleted_by are handled by the trait
  ];

  protected $casts = [
    'date_of_birth' => 'date',
    'joining_date' => 'date',
    'confirmation_date' => 'date',
    'contract_end_date' => 'date',
    'resignation_date' => 'date',
    // Add casts for other relevant fields like boolean statuses if you add them
  ];


  // --- Status Constants (Examples - define based on your data/workflow) ---
  public const SERVICE_STATUS_ACTIVE = 'active';
  public const SERVICE_STATUS_INACTIVE = 'inactive';
  public const SERVICE_STATUS_SUSPENDED = 'suspended';
  // Add other employment statuses if needed, e.g., contract, permanent
  public const EMPLOYMENT_STATUS_PERMANENT = 'permanent';
  public const EMPLOYMENT_STATUS_CONTRACT = 'contract';
  public const EMPLOYMENT_STATUS_MYSTEP = 'mystep';
  public const EMPLOYMENT_STATUS_INTERN = 'intern';
  public const EMPLOYMENT_STATUS_OTHER_AGENCY = 'other_agency';
  // Add gender constants if needed
  public const GENDER_MALE = 'Male';
  public const GENDER_FEMALE = 'Female';
  // Add marital status constants if needed
  // --- End Status Constants ---


  // Relationships

  /**
   * Get the user account associated with the employee.
   */
  public function user(): BelongsTo
  {
    return $this->belongsTo(User::class, 'user_id');
  }

  /**
   * Get the department the employee belongs to.
   */
  public function department(): BelongsTo
  {
    return $this->belongsTo(Department::class, 'department_id');
  }

  /**
   * Get the center the employee belongs to.
   */
  public function center(): BelongsTo
  {
    return $this->belongsTo(Center::class, 'center_id');
  }

  /**
   * Get the unit the employee belongs to.
   */
  // Removed generic type hint <Unit>
  public function unit(): BelongsTo
  {
    return $this->belongsTo(Unit::class, 'unit_id');
  }

  /**
   * Get the position the employee holds.
   */
  public function position(): BelongsTo
  {
    return $this->belongsTo(Position::class, 'position_id');
  }

  /**
   * Get the grade the employee has.
   */
  public function grade(): BelongsTo
  {
    return $this->belongsTo(Grade::class, 'grade_id');
  }


  /**
   * Get the employee's timeline records (employment history).
   */
  public function timeline(): HasMany
  {
    // Assumes Timeline model exists and has 'employee_id' foreign key
    return $this->hasMany(Timeline::class, 'employee_id');
  }

  /**
   * Get the employee's current active timeline position.
   * Assumes there's a way to determine the "current" timeline record.
   * Example: Find the latest timeline record with no end date or a future end date.
   */
  public function currentTimeline(): HasOne
  {
    // Assumes Timeline model exists and has 'employee_id' foreign key
    // Assumes 'end_date' column on Timeline model
    // Assumes a scope like 'current' or specific where clauses
    return $this->hasOne(Timeline::class, 'employee_id')
      ->latest('start_date') // Order by start date desc
      ->whereNull('end_date') // Where end date is null (ongoing)
      ->orWhere('end_date', '>=', Carbon::today()); // Or where end date is in the future (or today)

    // Note: Complex "current" logic might need a dedicated method or accessor if pure relationship doesn't work.
  }


  /**
   * Get the leave requests (leave instances) for the employee.
   * Defines a one-to-many relationship to the EmployeeLeave model.
   */
  public function leaveRequests(): HasMany
  {
    // Relationship should point to EmployeeLeave::class
    // Assumes EmployeeLeave model exists and has 'employee_id' foreign key
    return $this->hasMany(EmployeeLeave::class, 'employee_id');
  }

  /**
   * Get the attendance records for the employee.
   * Defines a one-to-many relationship to the Attendance model.
   */
  // Removed generic type hint <Attendance>
  public function attendances(): HasMany
  {
    // Relationship should point to Attendance::class
    // Assumes Attendance model exists and has 'employee_id' foreign key
    return $this->hasMany(Attendance::class, 'employee_id');
  }

  /**
   * Get the loan applications submitted by the employee.
   * Defines a one-to-many relationship to the LoanApplication model.
   */
  public function loanApplications(): HasMany
  {
    // Assumes LoanApplication model exists and has 'user_id' foreign key linking to Employee's user_id
    // Or directly linking to employee_id if that's the foreign key. Adjust foreign key if needed.
    return $this->hasMany(LoanApplication::class, 'user_id'); // Assuming user_id on LoanApplication links to employee's user_id
    // If LoanApplication directly links to employee_id: return $this->hasMany(LoanApplication::class, 'employee_id');
  }

  /**
   * Get the email applications submitted by the employee.
   * Defines a one-to-many relationship to the EmailApplication model.
   */
  public function emailApplications(): HasMany
  {
    // Assumes EmailApplication model exists and has 'user_id' foreign key linking to Employee's user_id
    // Or directly linking to employee_id if that's the foreign key. Adjust foreign key if needed.
    return $this->hasMany(EmailApplication::class, 'user_id'); // Assuming user_id on EmailApplication links to employee's user_id
    // If EmailApplication directly links to employee_id: return $this->hasMany(EmailApplication::class, 'employee_id');
  }

  /**
   * Get the approvals made by the employee (as an officer/approver).
   * Defines a one-to-many relationship to the Approval model.
   */
  public function approvals(): HasMany
  {
    // Assumes Approval model exists and has 'officer_id' foreign key linking to the user or employee ID
    return $this->hasMany(Approval::class, 'officer_id'); // Assuming officer_id on Approval links to employee ID
  }


  // Accessors (Examples)

  /**
   * Accessor to get the full address of the employee.
   * Combine address lines, postcode, city, state, and country.
   */
  public function getFullAddressAttribute(): string
  {
    $addressParts = array_filter([
      $this->address_line_1,
      $this->address_line_2,
      $this->postcode,
      $this->city,
      $this->state,
      $this->country,
    ]);

    return implode(', ', $addressParts);
  }

  /**
   * Accessor to get the URL for the employee's photo.
   * Assumes photos are stored using Laravel's Storage facade.
   */
  protected function getPhotoUrlAttribute(): ?string // Use attribute accessor method name format
  {
    if ($this->photo_path) {
      // Assumes 'public' disk or configured disk for photos
      return Storage::url($this->photo_path);
    }
    // Return a default photo URL if no photo is set
    return null; // Or url('/images/default-employee.png')
  }

  // Example using Attribute class for a more modern accessor
  protected function nameWithStaffId(): Attribute
  {
    return Attribute::make(
      get: fn(mixed $value, array $attributes) => "{$attributes['name']} ({$attributes['staff_id']})",
    );
  }


  // Scopes (Examples)

  /**
   * Scope a query to include employees whose name or staff ID matches a search term.
   *
   * @param  \Illuminate\Database\Eloquent\Builder  $query
   * @param  string  $search
   * @return void
   */
  public function scopeSearch(Builder $query, string $search): void // Added return type hint
  {
    $query->where('name', 'like', '%' . $search . '%')
      ->orWhere('staff_id', 'like', '%' . $search . '%');
  }


  /**
   * Scope to include employees with active timeline positions.
   * This might be needed if you're querying employees based on their current assignment.
   * Note: The 'currentTimeline' relationship logic needs to be robust.
   *
   * @param  \Illuminate\Database\Eloquent\Builder  $query
   * @return void
   */
  public function scopeWithCurrentTimelinePosition(Builder $query): void // Added return type hint
  {
    $query->whereHas('currentTimeline', function (Builder $query) {
      $query->whereNotNull('position_id'); // Check if the timeline record has a position
      // Add any other conditions to identify the *current* record if the relationship logic isn't enough
    });
    // Or simply load the relationship for access without filtering:
    // $query->with('currentTimeline.position');
  }


  // Example of a scope for employees on leave (if leave requests have start/end dates and status)
  /**
   * Scope a query to include employees who are currently on leave.
   * Assumes EmployeeLeave model has 'start_date', 'end_date', and 'status' columns.
   *
   * @param  \Illuminate\Database\Eloquent\Builder  $query
   * @return void
   */
  // Updated scope to use EmployeeLeave::class and relationship name
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
