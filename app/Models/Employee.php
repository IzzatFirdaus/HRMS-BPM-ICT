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


class Employee extends Model
{
  // Use the traits for factory, soft deletes, and audit columns
  use CreatedUpdatedDeletedBy, HasFactory, SoftDeletes;

  /**
   * The attributes that are mass assignable.
   * Includes personal, work, and counter fields from the migration.
   *
   * @var array<int, string>
   */
  protected $fillable = [
    // Removed 'id' from fillable - primary keys are typically not mass assignable

    'contract_id', // Foreign key to contracts table
    'department_id', // ADDED: Foreign key to departments table
    'position_id', // ADDED: Foreign key to positions table
    'grade_id', // ADDED: Foreign key to grades table

    'first_name',
    'father_name',
    'last_name',
    'mother_name',
    'birth_and_place', // Might need casting depending on storage
    'national_number',
    'mobile_number',
    'degree',
    'gender',
    'address',
    'notes',

    'balance_leave_allowed',
    'max_leave_allowed',
    'delay_counter', // Time format
    'hourly_counter', // Time format
    'is_active', // Boolean flag

    'profile_photo_path', // Path to employee photo

    // Audit columns are typically handled by the trait, but listed here for clarity if mass assignment is needed
    // 'created_by',
    // 'updated_by',
    // 'deleted_by',
  ];

  /**
   * The attributes that should be cast.
   * Includes casts for boolean, integer counters, time, timestamps, soft deletes.
   *
   * @var array<string, string>
   */
  protected $casts = [
    'contract_id' => 'integer', // Cast FKs to integer for clarity
    'department_id' => 'integer',
    'position_id' => 'integer',
    'grade_id' => 'integer',

    'balance_leave_allowed' => 'integer', // Cast integer counters
    'max_leave_allowed' => 'integer',
    'delay_counter' => 'string', // Store/cast as string if using H:i:s format
    'hourly_counter' => 'string', // Store/cast as string if using H:i:s format

    'is_active' => 'boolean', // Cast boolean flag

    'created_at' => 'datetime', // Explicitly cast timestamps if trait doesn't handle or for clarity
    'updated_at' => 'datetime',
    'deleted_at' => 'datetime', // Cast soft delete timestamp

    // 'birth_and_place' => 'date', // Uncomment and change if this column stores a date
  ];

  // The CreatedUpdatedDeletedBy trait is assumed to add these audit relationships:
  // public function createdBy(): BelongsTo { ... }
  // public function updatedBy(): BelongsTo { ... }
  // public function deletedBy(): BelongsTo { ... }


  // 👉 Relationships

  /**
   * Get the user account associated with the employee.
   * Assumes the 'users' table has an 'employee_id' foreign key.
   */
  public function user(): HasOne
  {
    return $this->hasOne(User::class, 'employee_id'); // Explicitly define FK
  }

  /**
   * Get the contract type associated with the employee.
   */
  public function contract(): BelongsTo
  {
    // Assumes the 'employees' table has a 'contract_id' foreign key
    return $this->belongsTo(Contract::class, 'contract_id'); // Explicitly define FK
  }

  /**
   * Get the department associated with the employee.
   */
  public function department(): BelongsTo // ADDED: BelongsTo relationship to Department
  {
    // Assumes the 'employees' table has a 'department_id' foreign key
    return $this->belongsTo(Department::class, 'department_id'); // Explicitly define FK
  }

  /**
   * Get the position associated with the employee.
   */
  public function position(): BelongsTo // ADDED: BelongsTo relationship to Position
  {
    // Assumes the 'employees' table has a 'position_id' foreign key
    return $this->belongsTo(Position::class, 'position_id'); // Explicitly define FK
  }

  /**
   * Get the grade associated with the employee.
   */
  public function grade(): BelongsTo // ADDED: BelongsTo relationship to Grade
  {
    // Assumes the 'employees' table has a 'grade_id' foreign key
    return $this->belongsTo(Grade::class, 'grade_id'); // Explicitly define FK
  }

  /**
   * Get the fingerprint records for the employee.
   */
  public function fingerprints(): HasMany
  {
    // Assumes the 'fingerprints' table has an 'employee_id' foreign key
    return $this->hasMany(Fingerprint::class, 'employee_id'); // Explicitly define FK
  }

  /**
   * Get the discount records for the employee.
   */
  public function discounts(): HasMany
  {
    // Assumes the 'discounts' table has an 'employee_id' foreign key
    return $this->hasMany(Discount::class, 'employee_id'); // Explicitly define FK
  }

  /**
   * Get the timeline entries for the employee's assignments.
   */
  public function timelines(): HasMany
  {
    // Assumes the 'timelines' table has an 'employee_id' foreign key
    return $this->hasMany(Timeline::class, 'employee_id'); // Explicitly define FK
  }

  /**
   * Get the individual employee leave applications for the employee.
   * This is a HasMany relationship to the pivot table model.
   */
  public function employeeLeaveApplications(): HasMany
  {
    // Assumes the 'employee_leave' table has an 'employee_id' foreign key
    return $this->hasMany(EmployeeLeave::class, 'employee_id');
  }

  /**
   * Get the leave types associated with the employee through the pivot table.
   * This is a many-to-many relationship.
   */
  public function leaves(): BelongsToMany
  {
    // Assumes a 'employee_leave' pivot table linking employees and leaves
    // with columns 'employee_id' and 'leave_id'.
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
   * Assumes the 'messages' table has an 'employee_id' foreign key.
   */
  public function messages(): HasMany
  {
    return $this->hasMany(Message::class, 'employee_id'); // Explicitly define FK
  }

  /**
   * Get the equipment transitions for the employee.
   * Assumes the 'transitions' table has an 'employee_id' foreign key.
   */
  public function transitions(): HasMany
  {
    return $this->hasMany(Transition::class, 'employee_id'); // Explicitly define FK
  }

  /**
   * Get the equipment currently assigned to the employee.
   * Assumes the 'equipment' table has an 'assigned_to_employee_id' foreign key.
   */
  public function assignedEquipment(): HasMany // ADDED: Relationship to assigned equipment
  {
    return $this->hasMany(Equipment::class, 'assigned_to_employee_id');
  }


  // 👉 Attributes (Accessors/Mutators)

  // hourly_counter Attribute: Accessor to format time (optional)
  protected function hourlyCounter(): Attribute
  {
    // Adjust format based on your needs (e.g., 'H:i')
    return Attribute::make(
      // Accessor: format time string or Carbon instance
      get: fn(?string $value) => $value ? Carbon::parse($value)->format('H:i') : null, // Use ?string for nullable column
      // Mutator: store as HH:MM:SS string
      set: fn(?string $value) => $value, // Use ?string for nullable column
    );
  }

  // delay_counter Attribute: Accessor to format time (optional)
  protected function delayCounter(): Attribute
  {
    // Adjust format based on your needs (e.g., 'H:i')
    return Attribute::make(
      // Accessor: format time string or Carbon instance
      get: fn(?string $value) => $value ? Carbon::parse($value)->format('H:i') : null, // Use ?string for nullable column
      // Mutator: store as HH:MM:SS string
      set: fn(?string $value) => $value, // Use ?string for nullable column
    );
  }

  /**
   * Get the employee's full name (concatenation of name parts).
   */
  public function getFullNameAttribute(): string
  {
    // Use null coalescing operator (??) for safety if middle/father names are null
    return trim("{$this->first_name} {$this->father_name} {$this->last_name}");
  }

  /**
   * Get the employee's short name (first and last name).
   */
  public function getShortNameAttribute(): string
  {
    return trim("{$this->first_name} {$this->last_name}");
  }


  // 👉 Scopes

  /**
   * Scope to check for a specific employee leave application entry.
   * Refactored to correctly use wherePivot inside whereHas.
   *
   * @param \Illuminate\Database\Eloquent\Builder $query
   * @param mixed $leave_id
   * @param mixed $from_date
   * @param mixed $to_date
   * @param mixed $start_at
   * @param mixed $end_at
   * @return void
   */
  public function scopeCheckLeave(
    Builder $query,
    $leave_id,
    $from_date,
    $to_date,
    $start_at,
    $end_at
  ): void {
    $query->whereHas('employeeLeaveApplications', function ($query) use (
      $leave_id,
      $from_date,
      $to_date,
      $start_at,
      $end_at
    ) {
      // Query the employeeLeaveApplications relationship (HasMany to the pivot model)
      $query
        ->where('leave_id', $leave_id)
        ->where('from_date', $from_date)
        ->where('to_date', $to_date)
        ->where('start_at', $start_at)
        ->where('end_at', $end_at);
      // Do NOT add where('employee_id', ...) here, it's already scoped by the HasMany
    });
    // Note: If you prefer using the BelongsToMany relationship 'leaves' with wherePivot,
    // the scope would look different:
    // $query->whereHas('leaves', function ($query) use (...) {
    //      $query->wherePivot('leave_id', $leave_id)
    //            ->wherePivot('from_date', $from_date)
    //            // ... etc for other pivot columns
    // });
    // Using HasMany to the pivot model (EmployeeLeave) is often clearer for complex pivot queries.
  }


  // 👉 Functions (Accessors and other methods)

  /**
   * Get the number of years worked based on timeline entries.
   *
   * @return int
   */
  public function getWorkedYearsAttribute(): int
  {
    // Assuming 'is_sequent' logic determines employment start date
    // Refactored to be more direct and handle potential nulls.
    $firstSequentTimeline = $this->timelines()
      ->where('is_sequent', 1) // Look for the start of a sequence
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
      return $years == 0 ? 1 : $years;
    }

    return 0; // Return 0 if no valid start date found
  }


  /**
   * Get the employee's current position name based on their latest active timeline.
   *
   * @return string
   */
  public function getCurrentPositionAttribute(): string
  {
    // Eager loading 'position' on the timeline when querying the employee is more efficient
    // e.g., Employee::with('timelines.position')->find($employeeId);
    $data = $this->timelines()
      ->with('position') // Eager load position for efficiency if accessing name
      ->whereNull('end_date') // Find the currently active timeline entry
      ->first();

    // Use optional chaining and null coalescing for safety
    return $data?->position?->name ?? '---';
  }

  /**
   * Get the employee's current department name based on their latest active timeline.
   *
   * @return string
   */
  public function getCurrentDepartmentAttribute(): string
  {
    // Eager loading 'department' on the timeline when querying the employee is more efficient
    $data = $this->timelines()
      ->with('department') // Eager load department
      ->whereNull('end_date') // Find the currently active timeline entry
      ->first();

    // Use optional chaining and null coalescing for safety
    return $data?->department?->name ?? '---';
  }

  /**
   * Get the employee's current center name based on their latest active timeline.
   *
   * @return string
   */
  public function getCurrentCenterAttribute(): string
  {
    // Eager loading 'center' on the timeline when querying the employee is more efficient
    $data = $this->timelines()
      ->with('center') // Eager load center
      ->whereNull('end_date') // Find the currently active timeline entry
      ->first();

    // Use optional chaining and null coalescing for safety
    return $data?->center?->name ?? '---';
  }

  /**
   * Get the employee's join date in a short, human-readable format.
   *
   * @return string
   */
  public function getJoinAtShortFormAttribute(): string
  {
    // Find the earliest timeline entry to determine join date
    $data = $this->timelines()->orderBy('start_date')->first();

    if ($data && $data->start_date instanceof Carbon) {
      // Use diffForHumans on the Carbon instance
      return __('Joined') . ' ' . $data->start_date->diffForHumans();
    }

    return '---'; // Return placeholder if no timeline data
  }

  /**
   * Get the employee's join date in a formatted string.
   *
   * @return string
   */
  public function getJoinAtAttribute(): string
  {
    // Find the earliest timeline entry to determine join date
    $data = $this->timelines()->orderBy('start_date')->first();

    if ($data && $data->start_date instanceof Carbon) {
      // Use translatedFormat on the Carbon instance
      return $data->start_date->translatedFormat('j F Y');
    }

    return '---'; // Return placeholder if no timeline data
  }


  /**
   * Get the URL to the employee's profile photo.
   * Checks if a related User model exists and has a profile photo.
   *
   * @return string
   */
  public function getEmployeePhoto(): string
  {
    $defaultPhotoPath = 'profile-photos/default-photo.jpg'; // Use path relative to storage/app/public or public

    // Find the related user account
    $user = $this->user; // Access the hasOne relationship

    // Check if user exists and has a profile photo path
    if ($user && $user->profile_photo_path) {
      // Use storage::url() or asset() depending on how you serve photos
      // Assuming photos are in storage/app/public/profile-photos
      return \Storage::url($user->profile_photo_path);
      // If stored in public directory directly:
      // return asset('profile-photos/' . basename($user->profile_photo_path));
    }

    // Return the default photo path
    return \Storage::url($defaultPhotoPath);
    // If default is in public:
    // return asset($defaultPhotoPath);
  }

  // Add any other methods below this line
}
