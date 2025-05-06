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
use Illuminate\Support\Facades\Auth; // Auth is likely used by traits/policies, or for created_by/updated_by. Keep if used.


// Import models for relationships
use App\Models\Department;
use App\Models\Position;
use App\Models\Grade;
use App\Models\User; // Assuming the User model is related
use App\Models\LoanApplication; // Assuming related Loan Applications
use App\Models\LoanApplicationItem; // Assuming related Loan Application Items
use App\Models\LoanTransaction; // Assuming related Loan Transactions
use App\Models\LeaveRequest; // Assuming related Leave Requests
use App\Models\Attendance; // Assuming related Attendance
use App\Models\Timeline; // Assuming related Timeline
use Spatie\Permission\Traits\HasRoles; // Assuming you use Spatie Roles & Permissions


/**
 * App\Models\Employee
 *
 * Represents an employee within the system.
 * Stores personal details, organizational assignments (department, position, grade),
 * and relationships to various system records like loan applications, leave requests, etc.
 * Includes traits for factory, soft deletes, audit stamps, and potentially roles/permissions.
 *
 * @property int $id
 * @property string $employee_id Unique employee identifier (e.g., NRIC, staff ID).
 * @property string $full_name Employee's full name.
 * @property string|null $nric National Registration Identity Card number (nullable).
 * @property string|null $mobile_number Employee's mobile contact number (nullable).
 * @property string|null $personal_email Employee's personal email address (nullable).
 * @property string|null $motac_email Employee's official MOTAC email address (nullable).
 * @property int|null $user_id The ID of the associated User account (if any).
 * @property int|null $department_id The ID of the employee's department.
 * @property int|null $position_id The ID of the employee's position.
 * @property int|null $grade_id The ID of the employee's grade level.
 * @property string|null $service_status e.g., 'permanent', 'contract', 'mystep', 'intern'.
 * @property string|null $appointment_type e.g., 'first', 'extension'.
 * @property string $status e.g., 'active', 'inactive', 'suspended'.
 * @property string|null $profile_photo_path Path to the employee's profile photo on disk (nullable).
 * @property int|null $created_by User ID who created the record.
 * @property int|null $updated_by User ID who last updated the record.
 * @property int|null $deleted_by User ID who soft deleted the record.
 * @property Carbon|null $created_at Timestamp when the record was created.
 * @property Carbon|null $updated_at Timestamp when the record was last updated.
 * @property Carbon|null $deleted_at Timestamp when the record was soft deleted.
 *
 * @property-read User|null $user The associated User account.
 * @property-read Department|null $department The employee's department.
 * @property-read Position|null $position The employee's position.
 * @property-read Grade|null $grade The employee's grade level.
 * @property-read \Illuminate\Database\Eloquent\Collection<int, LoanApplication> $loanApplications Loan applications submitted by this employee.
 * @property-read \Illuminate\Database\Eloquent\Collection<int, LoanApplicationItem> $loanApplicationItems Loan application items requested by this employee.
 * @property-read \Illuminate\Database\Eloquent\Collection<int, LoanTransaction> $loanTransactions Loan transactions involving this employee as the borrower.
 * @property-read \Illuminate\Database\Eloquent\Collection<int, LeaveRequest> $leaveRequests Leave requests submitted by this employee.
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Attendance> $attendances Attendance records for this employee.
 * @property-read Timeline|null $currentTimeline The employee's current timeline record (e.g., active position/assignment).
 * @property-read User|null $createdBy User who created the record.
 * @property-read User|null $updatedBy User who last updated the record.
 * @property-read User|null $deletedBy User who soft deleted the record.
 * @property-read string $full_name_with_id Accessor for full name and employee ID.
 * @property-read string $position_name Accessor for position name.
 * @property-read string $department_name Accessor for department name.
 * @property-read string $grade_name Accessor for grade name.
 * @property-read string $employee_photo_path Accessor for employee photo URL.
 * @property-read string|null $current_position Accessor for current position name via timeline.
 * @property-read bool $is_active Accessor for active status.
 */
class Employee extends Model
{
  use HasFactory, SoftDeletes, CreatedUpdatedDeletedBy; // Include traits
  // use HasRoles; // Uncomment if Employee model directly uses Spatie roles

  protected $fillable = [
    'employee_id',
    'full_name',
    'nric',
    'mobile_number',
    'personal_email',
    'motac_email',
    'user_id',
    'department_id',
    'position_id',
    'grade_id',
    'service_status',
    'appointment_type',
    'status',
    'profile_photo_path',
  ];

  protected $casts = [
    'created_at' => 'datetime',
    'updated_at' => 'datetime',
    'deleted_at' => 'datetime',
    'user_id' => 'integer',
    'department_id' => 'integer',
    'position_id' => 'integer',
    'grade_id' => 'integer',
  ];

  // Define relationships

  /**
   * Get the user account associated with the employee.
   */
  public function user(): BelongsTo
  {
    return $this->belongsTo(User::class);
  }

  /**
   * Get the department the employee belongs to.
   */
  public function department(): BelongsTo
  {
    return $this->belongsTo(Department::class);
  }

  /**
   * Get the position the employee holds.
   */
  public function position(): BelongsTo
  {
    return $this->belongsTo(Position::class);
  }

  /**
   * Get the grade level of the employee.
   */
  public function grade(): BelongsTo
  {
    return $this->belongsTo(Grade::class);
  }

  /**
   * Get the loan applications for the employee.
   */
  public function loanApplications(): HasMany
  {
    return $this->hasMany(LoanApplication::class);
  }

  /**
   * Get the loan application items for the employee.
   */
  public function loanApplicationItems(): HasMany
  {
    return $this->hasMany(LoanApplicationItem::class);
  }

  /**
   * Get the loan transactions for the employee (as borrower).
   */
  public function loanTransactions(): HasMany
  {
    return $this->hasMany(LoanTransaction::class, 'user_id'); // Assuming user_id on LoanTransaction is the borrower
  }


  /**
   * Get the leave requests for the employee.
   */
  public function leaveRequests(): HasMany
  {
    return $this->hasMany(LeaveRequest::class); // Assuming LeaveRequest model exists
  }

  /**
   * Get the attendance records for the employee.
   */
  public function attendances(): HasMany
  {
    return $this->hasMany(Attendance::class); // Assuming Attendance model exists
  }

  /**
   * Get the employee's current timeline record.
   * Assumes a Timeline model exists and is linked via employee_id,
   * and that 'current' is determined by end_date being null.
   */
  public function currentTimeline(): HasOne
  {
    return $this->hasOne(Timeline::class)->latestOfMany()->whereNull('end_date');
  }


  // Define Accessors

  /**
   * Get the employee's full name and ID.
   */
  protected function getFullNameWithIdAttribute(): string
  {
    return "{$this->full_name} ({$this->employee_id})";
  }

  /**
   * Get the employee's position name.
   * Uses optional() for safety if position relationship is null.
   */
  protected function getPositionNameAttribute(): ?string
  {
    return optional($this->position)->name;
  }

  /**
   * Get the employee's department name.
   * Uses optional() for safety if department relationship is null.
   */
  protected function getDepartmentNameAttribute(): ?string
  {
    return optional($this->department)->name;
  }

  /**
   * Get the employee's grade name.
   * Uses optional() for safety if grade relationship is null.
   */
  protected function getGradeNameAttribute(): ?string
  {
    return optional($this->grade)->name;
  }


  /**
   * Get the URL for the employee's profile photo.
   * Handles cases where profile_photo_path is null or the file doesn't exist.
   */
  protected function getEmployeePhotoPathAttribute(): string
  {
    // Check if the path exists and the file exists at that path
    if ($this->profile_photo_path && Storage::disk('public')->exists($this->profile_photo_path)) {
      return Storage::disk('public')->url($this->profile_photo_path);
    }

    // Return default photo URL if no photo or file not found
    // Assumes 'profile-photos/.default-photo.jpg' exists in the public storage disk
    return Storage::disk('public')->url('profile-photos/.default-photo.jpg');
  }

  /**
   * Get the employee's current position name from the timeline.
   * Uses nullsafe operator (?->) for safety.
   */
  protected function getCurrentPositionAttribute(): ?string
  {
    // Accesses currentTimeline, then its position relationship, then the name property.
    // ?-> safely handles cases where currentTimeline or position might be null.
    return $this->currentTimeline?->position?->name;
  }


  /**
   * Check if the employee's status is 'active'.
   */
  public function isActive(): bool
  {
    return $this->status === 'active';
  }

  /**
   * Check if the employee has any of the required roles.
   * Assumes the Employee model uses the HasRoles trait from Spatie.
   */
  public function hasRequiredRole(array $requiredRoles): bool
  {
    // Uncomment the 'use HasRoles;' trait at the top to use this method
    // return $this->hasAnyRole($requiredRoles);

    // Placeholder if HasRoles trait is not on this model (e.g., roles are only on the User model)
    // You would need to adjust this logic based on where roles are managed.
    // For example, if roles are only on the User model associated via user_id:
    if ($this->user) {
      return $this->user->hasAnyRole($requiredRoles);
    }
    return false; // Employee has no associated user or user has no required roles
  }


  // Add scopes or other methods as needed

  /**
   * Scope a query to only include active employees.
   */
  public function scopeActive(Builder $query): void
  {
    $query->where('status', 'active');
  }

  /**
   * Scope a query to search employees by full name or employee ID.
   */
  public function scopeSearch(Builder $query, ?string $search): void
  {
    if ($search) {
      $query->where('full_name', 'like', '%' . $search . '%')
        ->orWhere('employee_id', 'like', '%' . $search . '%');
    }
  }


  /**
   * Scope to include employees with active timeline positions.
   * This might be needed if you're querying employees based on their current assignment.
   */
  public function scopeWithCurrentTimelinePosition(Builder $query): void
  {
    $query->whereHas('currentTimeline', function (Builder $query) {
      $query->whereNotNull('position_id'); // Check if the timeline record has a position
    });
    // Or simply load the relationship for access:
    // $query->with('currentTimeline.position');
  }


  // Example of a scope for employees on leave (if leave requests have start/end dates and status)
  // public function scopeOnLeave(Builder $query): void
  // {
  //     $today = Carbon::today();
  //     $query->whereHas('leaveRequests', function (Builder $query) use ($today) {
  //         $query->where('status', 'approved') // Assuming a status field
  //               ->whereDate('start_date', '<=', $today)
  //               ->whereDate('end_date', '>=', $today);
  //     });
  // }


  // Scopes based on the traceback from ReportsController
  // These methods would be in the ReportController, not the Employee model itself,
  // but listing them here for context of what queries the model supports.
  // public function scopeWithCounts(Builder $query): void // Example for userActivity report
  // {
  //      $query->withCount(['emailApplications', 'loanApplications', 'approvals']); // Assumes relationships exist on User or Employee
  // }

}
