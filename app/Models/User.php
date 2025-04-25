<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Traits\CreatedUpdatedDeletedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany; // Import HasMany
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

// Import the new models needed for relationships
use App\Models\Department; // Assuming Department model exists in HRMS or will be aligned
use App\Models\Position; // HRMS uses Designation, MOTAC uses Position, link them here
use App\Models\Grade; // Assuming Grade model exists or will be created
use App\Models\EmailApplication; // New MOTAC model
use App\Models\LoanApplication; // New MOTAC model
use App\Models\LoanTransaction; // New MOTAC model
use App\Models\Approval; // New MOTAC model

class User extends Authenticatable
{
  use CreatedUpdatedDeletedBy,
    HasApiTokens,
    HasFactory,
    HasProfilePhoto,
    HasRoles, // Keep if using Spatie Permissions
    Notifiable,
    SoftDeletes,
    TwoFactorAuthenticatable;

  /**
   * The attributes that are mass assignable.
   *
   * @var array<int, string>
   */
  protected $fillable = [
    'name', // Existing HRMS field - consider if 'full_name' is preferred based on MOTAC design
    'full_name', // Add full_name based on MOTAC design if different from 'name'
    'employee_id', // Existing HRMS field - linkage to HRMS employee model
    'identification_number', // Add NRIC based on MOTAC design
    'mobile', // Existing HRMS field
    'mobile_number', // Add mobile_number based on MOTAC design if different from 'mobile'
    'mobile_verified_at', // Existing HRMS field
    'email', // Existing HRMS field (could be personal_email or motac_email)
    'personal_email', // Add personal email based on MOTAC design
    'motac_email', // Add motac_email based on MOTAC design (assigned after provisioning)
    'user_id_assigned', // Add user_id_assigned based on MOTAC design (assigned after provisioning)
    'email_verified_at', // Existing HRMS field
    'password', // Existing HRMS field
    'profile_photo_path', // Existing HRMS field
    'department_id', // Add department_id based on MOTAC design
    'position_id', // Add position_id based on MOTAC design (linking to Designation)
    'grade_id', // Add grade_id based on MOTAC design
    'service_status', // Add service_status based on MOTAC design
    'appointment_type', // Add appointment_type based on MOTAC design
    'status', // Add user account status based on MOTAC design (active/inactive/suspended)
  ];

  /**
   * The attributes that should be hidden for serialization.
   *
   * @var array<int, string>
   */
  protected $hidden = [
    'password',
    'remember_token',
    'two_factor_recovery_codes',
    'two_factor_secret',
  ];

  /**
   * The attributes that should be cast.
   *
   * @var array<string, string>
   */
  protected $casts = [
    'email_verified_at' => 'datetime',
    'mobile_verified_at' => 'datetime', // Keep existing cast
    // Add casts for new date/json columns if any were added (e.g., in grades or other related models)
  ];

  protected $appends = ['profile_photo_url', 'employee_full_name']; // Keep existing appends


  // 👉 Existing HRMS Links
  public function employee(): BelongsTo
  {
    // Keep existing relationship to HRMS Employee model
    return $this->belongsTo(Employee::class);
  }

  // 👉 New MOTAC Resource Management Relationships

  /**
   * Get the department associated with the user.
   */
  public function department(): BelongsTo
  {
    return $this->belongsTo(Department::class);
  }

  /**
   * Get the position (designation) associated with the user.
   */
  public function position(): BelongsTo
  {
    // Link to the HRMS Designation model, aliased as Position for clarity with MOTAC design
    return $this->belongsTo(Position::class, 'position_id');
  }

  /**
   * Get the grade associated with the user.
   */
  public function grade(): BelongsTo
  {
    return $this->belongsTo(Grade::class);
  }

  /**
   * Get the email applications submitted by the user.
   */
  public function emailApplications(): HasMany
  {
    return $this->hasMany(EmailApplication::class);
  }

  /**
   * Get the loan applications submitted by the user.
   */
  public function loanApplications(): HasMany
  {
    return $this->hasMany(LoanApplication::class);
  }

  /**
   * Get the loan transactions where the user was the issuing officer.
   */
  public function issuedTransactions(): HasMany
  {
    return $this->hasMany(LoanTransaction::class, 'issuing_officer_id');
  }

  /**
   * Get the loan transactions where the user was the receiving officer.
   */
  public function receivedTransactions(): HasMany
  {
    return $this->hasMany(LoanTransaction::class, 'receiving_officer_id');
  }

  /**
   * Get the loan transactions where the user was the returning officer.
   */
  public function returningTransactions(): HasMany
  {
    return $this->hasMany(LoanTransaction::class, 'returning_officer_id');
  }

  /**
   * Get the loan transactions where the user was the return accepting officer.
   */
  public function acceptedReturnTransactions(): HasMany
  {
    return $this->hasMany(LoanTransaction::class, 'return_accepting_officer_id');
  }


  /**
   * Get the approvals made by the user.
   */
  public function approvals(): HasMany
  {
    return $this->hasMany(Approval::class, 'officer_id');
  }


  // 👉 Existing HRMS Attributes
  public function getEmployeeFullNameAttribute(): string
  {
    // Keep existing logic for getting full name from the HRMS Employee relationship
    if ($this->employee) {
      return $this->employee->first_name . ' ' . $this->employee->last_name;
    }

    // Potentially return 'name' or 'full_name' if employee relationship is null
    return $this->full_name ?? $this->name ?? ''; // Prioritize new full_name field
  }

  // 👉 New MOTAC Resource Management Helper Methods

  /**
   * Check if the user has a grade level required for approval.
   * Assumes grade relationship is loaded and Grade model has 'level' attribute.
   * Assumes min_approver_grade_level is set in config('motac.approval').
   */
  public function hasApprovalGrade(): bool
  {
    // Ensure the grade relationship is loaded before accessing its properties
    if ($this->relationLoaded('grade')) {
      return $this->grade !== null
        && isset($this->grade->level)
        && $this->grade->level >= config('motac.approval.min_approver_grade_level');
    }

    // If grade relationship is not loaded, you might choose to load it or return false
    // For simplicity here, we return false if not loaded. Consider eager loading 'grade' where needed.
    return false;
  }


  /**
   * Check if the user is an administrator (based on roles).
   * Assumes Spatie roles are used and 'Admin' is the administrator role name.
   */
  public function isAdmin(): bool
  {
    return $this->hasRole('Admin'); // Adjust role name if your admin role is named differently
  }


  // Add any other existing methods or accessors/mutators below this line
}
