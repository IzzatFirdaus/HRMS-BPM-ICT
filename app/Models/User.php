<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail; // Keep if your user needs email verification
use App\Traits\CreatedUpdatedDeletedBy; // Assuming this trait exists and is used for audit columns on *other* models
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes; // Keep if using soft deletes for users (migration supports this)
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable; // Keep if using Fortify 2FA
use Laravel\Jetstream\HasProfilePhoto; // Keep if using Jetstream profile photos
use Laravel\Sanctum\HasApiTokens; // Keep if using Sanctum API tokens
use Spatie\Permission\Traits\HasRoles; // Keep if using Spatie Permissions

// Import the models needed for relationships
use App\Models\Department;
use App\Models\Position;
use App\Models\Grade;
use App\Models\Employee; // For employee_id relationship
use App\Models\EmailApplication;
use App\Models\LoanApplication;
use App\Models\LoanTransaction;
use App\Models\Approval;
// Add other models if user has relationships to them (e.g., assets/equipment they are assigned)


class User extends Authenticatable
{
  // Keep existing traits
  use CreatedUpdatedDeletedBy, // Keep if this trait adds audit FKs to the User model itself (less common for User model, but possible)
    HasApiTokens,
    HasFactory,
    HasProfilePhoto,
    HasRoles, // Keep if using Spatie Permissions
    Notifiable,
    SoftDeletes, // Keep if using soft deletes for users
    TwoFactorAuthenticatable; // Keep if using Fortify 2FA

  /**
   * The attributes that are mass assignable.
   *
   * @var array<int, string>
   */
  protected $fillable = [
    'name', // Existing HRMS field (e.g., username or short name)
    'full_name', // Add full_name based on MOTAC design for official name
    'employee_id', // Existing HRMS field - foreign key to employee model
    'nric', // ADDED: NRIC based on MOTAC design
    'mobile_number', // ADDED: Mobile number based on MOTAC design
    // Removed 'identification_number', 'mobile', 'phone_number' - assumed 'nric' and 'mobile_number' are the standard fields

    'email', // Existing HRMS field (could be primary contact email)
    'email_verified_at', // Existing HRMS field
    'password', // Existing HRMS field
    'profile_photo_path', // Existing HRMS field
    'remember_token', // Existing HRMS field

    'personal_email', // Add personal email based on MOTAC design
    'motac_email', // Add motac_email based on MOTAC design (assigned after provisioning)
    'user_id_assigned', // Add user_id_assigned based on MOTAC design (assigned after provisioning, e.g., external system ID)

    'department_id', // Add department_id based on MOTAC design (foreign key to departments table)
    'position_id', // Add position_id based on MOTAC design (foreign key to positions table)
    'grade_id', // Add grade_id based on MOTAC design (foreign key to grades table)

    'service_status', // Add service_status based on MOTAC design (e.g., permanent, contract)
    'appointment_type', // Add appointment_type based on MOTAC design

    'is_admin', // Add is_admin flag
    'is_bpm_staff', // Add is_bpm_staff flag
    'status', // Add user account status based on MOTAC design (e.g., active, inactive, suspended)

    // Audit columns (created_by, updated_by, deleted_by) are typically handled by the CreatedUpdatedDeletedBy trait on *other* models.
    // If the User model itself HAS audit columns referencing *other* users (less common), add them here.
    // Based on 2025_04_22_083508_add_motac_columns_to_users_table, User table *does* have audit columns.
    // The trait needs to handle applying these to the User model itself if used here, OR you list them manually.
    // Assuming the trait on User model means User records *have* audit columns referencing *other* users.
    'created_by',
    'updated_by',
    'deleted_by',
  ];

  /**
   * The attributes that should be hidden for serialization.
   *
   * @var array<int, string>
   */
  protected $hidden = [
    'password',
    'remember_token',
    'two_factor_recovery_codes', // Keep if using Fortify 2FA
    'two_factor_secret', // Keep if using Fortify 2FA
    // Audit columns can be hidden if you don't want them in JSON/array output by default
    'created_by',
    'updated_by',
    'deleted_by',
    'deleted_at', // Hide soft delete timestamp
  ];

  /**
   * The attributes that should be cast.
   *
   * @var array<string, string>
   */
  protected $casts = [
    'email_verified_at' => 'datetime',
    'mobile_verified_at' => 'datetime', // Keep existing cast if this column exists
    'is_admin' => 'boolean', // ADDED: Cast boolean flags
    'is_bpm_staff' => 'boolean', // ADDED: Cast boolean flags
    'status' => 'string', // Cast status as string if it's a string/enum in DB
    'created_at' => 'datetime', // Explicitly cast timestamps if trait doesn't handle or for clarity
    'updated_at' => 'datetime',
    'deleted_at' => 'datetime', // Cast soft delete timestamp
  ];

  // If 'full_name' is a direct database column, remove this accessor unless needed for formatting
  // protected function getFullNameAttribute($value): string
  // {
  //     return $value ?? $this->attributes['name'] ?? '';
  // }

  // Appends are typically virtual attributes. Since 'full_name' is a DB column,
  // you don't strictly need to append it, it's included by default when accessing the attribute.
  // Keep profile_photo_url if using Jetstream's trait accessor.
  protected $appends = [
    'profile_photo_url', // Keep existing from HasProfilePhoto trait
    // 'employee_full_name', // Keep if still deriving from HRMS Employee
  ];

  // Removed getEmployeeFullNameAttribute if 'full_name' column is now the primary source.
  // If still needed to get name from HRMS Employee, keep this accessor.


  // 👉 Existing HRMS Links
  /**
   * Get the related Employee record from the HRMS system.
   */
  public function employee(): BelongsTo
  {
    // Link to HRMS Employee model (if it exists), linked by employee_id
    return $this->belongsTo(Employee::class, 'employee_id');
  }

  // 👉 New MOTAC Resource Management Relationships

  /**
   * Get the department associated with the user.
   */
  public function department(): BelongsTo
  {
    return $this->belongsTo(Department::class, 'department_id');
  }

  /**
   * Get the position associated with the user.
   */
  public function position(): BelongsTo
  {
    // Link to the Position model (which maps to the 'positions' table), linked by position_id
    return $this->belongsTo(Position::class, 'position_id');
  }

  /**
   * Get the grade associated with the user.
   */
  public function grade(): BelongsTo
  {
    return $this->belongsTo(Grade::class, 'grade_id');
  }

  /**
   * Get the email applications submitted by the user.
   */
  public function emailApplications(): HasMany
  {
    return $this->hasMany(EmailApplication::class, 'user_id'); // User as applicant
  }

  /**
   * Get the email applications reviewed/supported by the user.
   */
  public function supportedEmailApplications(): HasMany
  {
    return $this->hasMany(EmailApplication::class, 'supporting_officer_id'); // User as supporting officer
  }

  /**
   * Get the loan applications submitted by the user.
   */
  public function loanApplications(): HasMany
  {
    return $this->hasMany(LoanApplication::class, 'user_id'); // User as applicant
  }

  /**
   * Get the loan applications where the user was the responsible officer.
   */
  public function responsibleLoanApplications(): HasMany
  {
    return $this->hasMany(LoanApplication::class, 'responsible_officer_id'); // User as responsible officer
  }


  /**
   * Get the loan transactions where the user was the issuing officer.
   */
  public function issuedTransactions(): HasMany
  {
    return $this->hasMany(LoanTransaction::class, 'issuing_officer_id');
  }

  /**
   * Get the loan transactions where the user was the receiving officer (on issue).
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
   * Get the approvals made by the user (as an officer).
   */
  public function approvals(): HasMany
  {
    return $this->hasMany(Approval::class, 'officer_id');
  }

  // 👉 Inverse Audit Relationships (Records created/updated/deleted BY this user)
  // Assuming CreatedUpdatedDeletedBy trait adds audit FKs to OTHER models.
  // Add these relationships to the User model to see what THIS user audited.

  /**
   * Get all records created by this user.
   */
  public function createdRecords(): HasMany
  {
    // You would need a specific implementation if you want ALL models.
    // Often defined per model, e.g., $user->createdDepartments().
    // Or a generic relation if the trait supports it and you define it.
    // Example for a specific model:
    // public function createdDepartments(): HasMany { return $this->hasMany(Department::class, 'created_by'); }

    // A more general approach might require a package or more complex query/relation setup.
    // For simplicity, list specific ones or rely on the trait's documentation if it provides a generic way.
    // Let's define specific ones for demonstration.
    return $this->hasMany(Department::class, 'created_by'); // Example: Records created in the departments table
    // Add more as needed:
    // return $this->hasMany(Position::class, 'created_by');
    // return $this->hasMany(Equipment::class, 'created_by');
    // etc.
  }

  /**
   * Get all records updated by this user.
   */
  public function updatedRecords(): HasMany
  {
    return $this->hasMany(Department::class, 'updated_by'); // Example
    // Add more as needed
  }

  /**
   * Get all records deleted by this user (soft deletes).
   */
  public function deletedRecords(): HasMany
  {
    return $this->hasMany(Department::class, 'deleted_by'); // Example
    // Add more as needed
  }


  // 👉 New MOTAC Resource Management Helper Methods

  /**
   * Check if the user has a grade level required for approval.
   * Assumes grade relationship is loaded and Grade model has 'level' attribute.
   * Assumes min_approver_grade_level is set in config('motac.approval').
   *
   * @return bool
   */
  public function hasApprovalGrade(): bool
  {
    // Ensure the grade relationship is loaded and not null before accessing level
    // Use optional chaining (?->) for safety
    return $this->relationLoaded('grade')
      && $this->grade !== null
      && ($this->grade->level ?? 0) >= config('motac.approval.min_approver_grade_level', 0); // Default config value to 0 for safety
  }


  /**
   * Check if the user is an administrator (based on is_admin flag or roles).
   * Using the is_admin column as per your fillable. Can combine with role check.
   *
   * @return bool
   */
  public function isAdmin(): bool
  {
    // Assuming a boolean column 'is_admin' exists
    return (bool) $this->is_admin;
    // Or combine with role check if needed:
    // return (bool) $this->is_admin || $this->hasRole('Admin');
  }

  /**
   * Check if the user is BPM staff (based on is_bpm_staff flag or role).
   * Using the is_bpm_staff column as per your fillable. Can combine with role check.
   *
   * @return bool
   */
  public function isBpmStaff(): bool
  {
    // Assuming a boolean column 'is_bpm_staff' exists
    return (bool) $this->is_bpm_staff;
    // Or combine with role check if needed:
    // return (bool) $this->is_bpm_staff || $this->hasRole('BPM Staff');
  }

  /**
   * Check if the user has a specific status.
   *
   * @param string $status The status to check against.
   * @return bool
   */
  public function hasStatus(string $status): bool
  {
    // Assuming a string column 'status' exists
    return $this->status === $status;
  }


  // Add any other existing methods or accessors/mutators below this line

  // The getProfilePhotoUrlAttribute is provided by the HasProfilePhoto trait
  // No need to define it here unless you want to override the trait's behavior.
}
