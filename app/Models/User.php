<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes; // Assuming SoftDeletes is used as per migration
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable; // Assuming Jetstream/Fortify is used
use Laravel\Jetstream\HasProfilePhoto; // Assuming Jetstream is used
use Laravel\Sanctum\HasApiTokens; // Assuming Sanctum is used
use Spatie\Permission\Traits\HasRoles; // Assuming Spatie Permission is used
use Illuminate\Support\Carbon; // Import for PHPDoc

// Assuming you have a trait for created_by/updated_by/deleted_by
use App\Traits\CreatedUpdatedDeletedBy;

// Import specific Notification classes you might route emails for
use App\Notifications\EmailProvisioningComplete; // Example: Need to route this to personal email

/**
 * Add PHPDoc annotations to help static analysis tools (like Intelephense)
 * recognize the dynamic properties provided by Eloquent's magic methods.
 * This helps resolve "Undefined method 'id'" and similar linter warnings on User model properties.
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property int|null $created_by FK to users table (audit trail).
 * @property int|null $updated_by FK to users table (audit trail).
 * @property int|null $deleted_by FK to users table (audit trail).
 * @property string|null $two_factor_secret
 * @property array|null $two_factor_recovery_codes
 * @property string|null $profile_photo_path
 *
 * @property string|null $full_name Added from migration.
 * @property string|null $employee_id Added from migration.
 * @property string|null $nric Added from migration, unique.
 * @property string|null $mobile_number Added from migration.
 * @property string|null $personal_email Added from migration, unique.
 * @property string|null $motac_email Added from migration, unique.
 * @property string|null $user_id_assigned Added from migration, unique (External System ID).
 * @property int|null $department_id Added from migration.
 * @property int|null $position_id Added from migration.
 * @property int|null $grade_id Added from migration.
 * @property string|null $service_status Added from migration (e.g., 'permanent').
 * @property string|null $appointment_type Added from migration.
 * @property string|null $status Added from migration (e.g., 'active').
 * @property bool|null $is_admin Added from migration.
 * @property bool|null $is_bpm_staff Added from migration.
 * @property Carbon|null $mobile_verified_at Added from migration.
 *
 * // Relationships
 * @property-read Department|null $department
 * @property-read Position|null $position
 * @property-read Grade|null $grade
 * @property-read Employee|null $employee
 * @property-read \Illuminate\Database\Eloquent\Collection<int, EmailApplication> $emailApplications Applications submitted by this user.
 * @property-read \Illuminate\Database\Eloquent\Collection<int, EmailApplication> $supportedEmailApplications Applications where this user is the supporting officer.
 * @property-read \Illuminate\Database\Eloquent\Collection<int, LoanApplication> $loanApplications Loan applications submitted by this user.
 * @property-read \Illuminate\Database\Eloquent\Collection<int, LoanApplication> $responsibleLoanApplications Loan applications where this user is the responsible officer.
 * @property-read \Illuminate\Database\Eloquent\Collection<int, LoanTransaction> $issuedTransactions Loan transactions issued by this user.
 * @property-read \Illuminate\Database\Eloquent\Collection<int, LoanTransaction> $receivedTransactions Loan transactions received by this user.
 * @property-read \Illuminate\Database\Eloquent\Collection<int, LoanTransaction> $returningTransactions Loan transactions returned by this user.
 * @property-read \Illuminate\Database\Eloquent\Collection<int, LoanTransaction> $acceptedReturnTransactions Loan transactions accepted by this user.
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Approval> $approvals Approvals made by this user.
 *
 * // Accessors
 * @property-read string $profile_photo_url
 * @property-read string|null $service_status_translated Translated service status.
 *
 * @mixin \Illuminate\Database\Eloquent\Builder // Include mixin for model scopes and query builder methods
 */
class User extends Authenticatable // Or extends Authenticatable implements MustVerifyEmail if needed
{
  // --- Traits (As provided in your code) ---
  use CreatedUpdatedDeletedBy, // Assuming this trait exists and works with audit columns
    HasApiTokens,
    HasFactory,
    HasProfilePhoto,
    HasRoles, // Assuming Spatie Permission
    Notifiable, // For sending notifications
    SoftDeletes, // Assuming SoftDeletes from migration
    TwoFactorAuthenticatable; // Assuming Fortify

  // --- Constants for Enum Values (ADDED based on users table migration) ---
  // These match the enum values defined in 2013_11_01_132200_add_motac_columns_to_users_table.php
  public const SERVICE_STATUS_PERMANENT = 'permanent';
  public const SERVICE_STATUS_CONTRACT = 'contract';
  public const SERVICE_STATUS_MYSTEP = 'mystep';
  public const SERVICE_STATUS_INTERN = 'intern';
  public const SERVICE_STATUS_OTHER_AGENCY = 'other_agency';

  // These match the enum values defined in 2013_11_01_132200_add_motac_columns_to_users_table.php
  public const STATUS_ACTIVE = 'active';
  public const STATUS_INACTIVE = 'inactive';
  public const STATUS_SUSPENDED = 'suspended';
  // --- END ADDED CONSTANTS ---


  /**
   * The attributes that are mass assignable.
   * (As provided in your code, includes columns from migrations)
   *
   * @var array<int, string>
   */
  protected $fillable = [
    'name',
    'full_name',
    'employee_id',
    'nric',
    'mobile_number',
    'email',
    'password',
    'personal_email',
    'motac_email', // Added from migration
    'user_id_assigned', // Added from migration (External System ID)
    'department_id',
    'position_id',
    'grade_id',
    'service_status', // Added from migration
    'appointment_type',
    'is_admin', // Added from migration
    'is_bpm_staff', // Added from migration
    'status', // Added from migration
  ];

  /**
   * The attributes that should be hidden for serialization.
   * (As provided in your code, includes audit columns and soft deletes)
   *
   * @var array<int, string>
   */
  protected $hidden = [
    'password',
    'remember_token',
    'two_factor_recovery_codes',
    'two_factor_secret',
    'created_by', // Hidden as per trait/migration
    'updated_by', // Hidden as per trait/migration
    'deleted_by', // Hidden as per trait/migration
    'deleted_at', // Standard SoftDeletes
    'profile_photo_path', // Standard Jetstream
  ];

  /**
   * The attributes that should be cast.
   * (As provided in your code, includes casts for migration columns)
   *
   * @var array<string, string>
   */
  protected $casts = [
    'email_verified_at' => 'datetime',
    'mobile_verified_at' => 'datetime', // Added from migration
    'created_at' => 'datetime', // Redundant but harmless
    'updated_at' => 'datetime', // Redundant but harmless
    'deleted_at' => 'datetime', // Standard SoftDeletes
    'is_admin' => 'boolean', // Added from migration
    'is_bpm_staff' => 'boolean', // Added from migration
    'two_factor_recovery_codes' => 'array', // Standard Fortify
    // Add other casts as needed (e.g., 'password' => 'hashed' if not already done by default)
  ];

  /**
   * The accessors to append to the model's array form.
   * (As provided in your code)
   *
   * @var array<string>
   */
  protected $appends = [
    'profile_photo_url',
  ];

  // --- Relationships (As provided in your code, aligns with migrations) ---

  // Relationship to the Employee record (if users are linked to a separate employee table)
  // Assumes employee_id foreign key on users table
  public function employee(): BelongsTo
  {
    return $this->belongsTo(Employee::class);
  }

  // Relationship to the Department
  // Assumes department_id foreign key on users table
  public function department(): BelongsTo
  {
    return $this->belongsTo(Department::class);
  }

  // Relationship to the Position
  // Assumes position_id foreign key on users table
  public function position(): BelongsTo
  {
    return $this->belongsTo(Position::class);
  }

  // Relationship to the Grade
  // Assumes grade_id foreign key on users table
  public function grade(): BelongsTo
  {
    return $this->belongsTo(Grade::class);
  }

  // Relationship to Email Applications submitted by this user
  // Assumes user_id foreign key on email_applications table
  public function emailApplications(): HasMany
  {
    return $this->hasMany(EmailApplication::class);
  }

  // Relationship to Email Applications where this user is the assigned supporting officer
  // Assumes supporting_officer_id foreign key on email_applications table
  public function supportedEmailApplications(): HasMany
  {
    return $this->hasMany(EmailApplication::class, 'supporting_officer_id');
  }

  // Relationship to Loan Applications submitted by this user
  // Assumes user_id foreign key on loan_applications table
  public function loanApplications(): HasMany
  {
    return $this->hasMany(LoanApplication::class);
  }

  // Relationship to Loan Applications where this user is the assigned responsible officer
  // Assumes responsible_officer_id foreign key on loan_applications table
  public function responsibleLoanApplications(): HasMany
  {
    return $this->hasMany(LoanApplication::class, 'responsible_officer_id');
  }

  // Relationships to Loan Transactions where this user is involved in various roles
  // Assumes loan_transactions table has foreign keys: issuing_officer_id, receiving_officer_id, returning_officer_id, return_accepting_officer_id
  public function issuedTransactions(): HasMany
  {
    return $this->hasMany(LoanTransaction::class, 'issuing_officer_id');
  }

  public function receivedTransactions(): HasMany
  {
    return $this->hasMany(LoanTransaction::class, 'receiving_officer_id');
  }

  public function returningTransactions(): HasMany
  {
    return $this->hasMany(LoanTransaction::class, 'returning_officer_id');
  }

  public function acceptedReturnTransactions(): HasMany
  {
    return $this->hasMany(LoanTransaction::class, 'return_accepting_officer_id');
  }

  // Relationship to Approvals where this user is the officer making the decision
  // Assumes officer_id foreign key on approvals table
  public function approvals(): HasMany
  {
    return $this->hasMany(Approval::class, 'officer_id');
  }
  // --- END RELATIONSHIPS ---


  // --- Helper Methods (As provided in your code) ---

  // Check if the user has an approval grade level >= the minimum required
  public function hasApprovalGrade(): bool
  {
    // Assumes Grade model exists and has a 'level' attribute
    // Assumes 'motac.approval.min_approver_grade_level' config value is set (e.g., in config/motac.php)
    return $this->grade && $this->grade->level >= config('motac.approval.min_approver_grade_level', 0);
  }

  // Check if the user is an admin
  public function isAdmin(): bool
  {
    // Assumes 'is_admin' boolean column exists on users table
    return $this->is_admin;
    // Or using Spatie roles: return $this->hasRole('admin');
  }

  // Check if the user is BPM staff
  public function isBpmStaff(): bool
  {
    // Assumes 'is_bpm_staff' boolean column exists on users table
    return $this->is_bpm_staff;
    // Or using Spatie roles: return $this->hasRole('bpm_staff');
  }

  // Check if the user has a specific status
  public function hasStatus(string $status): bool
  {
    // Uses the constants defined above for clarity
    return $this->status === $status;
  }

  // Check if the user is active
  public function isActive(): bool
  {
    return $this->hasStatus(self::STATUS_ACTIVE); // Use constant
  }

  // Check if the user is inactive
  public function isInactive(): bool
  {
    return $this->hasStatus(self::STATUS_INACTIVE); // Use constant
  }
  // --- END HELPER METHODS ---


  /**
   * Specify the mail recipient for notifications.
   * This method overrides the default behavior of the Notifiable trait,
   * allowing you to send certain notifications to the personal email.
   * (ADDED based on previous review suggestion)
   *
   * @param \Illuminate\Notifications\Notification $notification
   * @return array|string|null The email address(es) to send the notification to.
   */
  public function routeNotificationForMail($notification): array|string|null
  {
    // Example: For the EmailProvisioningComplete notification, send it to the personal_email
    // provided it exists and is a valid email format.
    if ($notification instanceof EmailProvisioningComplete && $this->personal_email && filter_var($this->personal_email, FILTER_VALIDATE_EMAIL)) {
      return $this->personal_email;
    }

    // For all other notifications, or if personal_email is missing/invalid for the above,
    // fallback to the primary 'email' field, which is the Notifiable trait's default.
    return $this->email; // Or simply return null to let Notifiable trait handle it
  }

  // You can add similar methods for other notification channels if needed
  // public function routeNotificationForVonage($notification): string
  // {
  //     // Return the user's mobile number if available and valid for SMS notifications
  //     return $this->mobile_number;
  // }

  /**
   * Accessor for translated service status (assuming service_status column is enum).
   * You would define the translation mapping here or in a config file.
   * Example mapping: ['permanent' => 'Kakitangan Tetap', ...]
   *
   * @return string|null
   */
  public function getServiceStatusTranslatedAttribute(): ?string
  {
    $statuses = [
      self::SERVICE_STATUS_PERMANENT => 'Kakitangan Tetap',
      self::SERVICE_STATUS_CONTRACT => 'Lantikan Kontrak',
      self::SERVICE_STATUS_MYSTEP => 'Personel MySTEP',
      self::SERVICE_STATUS_INTERN => 'Pelajar Latihan Industri',
      self::SERVICE_STATUS_OTHER_AGENCY => 'E-mel Sandaran MOTAC', // Based on migration enum values
      // Add other statuses if your enum includes them
    ];

    return $statuses[$this->service_status] ?? $this->service_status; // Return translated or raw value
  }
}
