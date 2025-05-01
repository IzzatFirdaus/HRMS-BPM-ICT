<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\CreatedUpdatedDeletedBy;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

<<<<<<< HEAD
=======

// Import the models needed for relationships if they are in a different namespace
use App\Models\Department;
use App\Models\Position;
use App\Models\Grade;
// Assuming App\Models\Employee exists and users are linked to it
use App\Models\Employee;
// Assuming App\Models\EmailApplication exists
use App\Models\EmailApplication;
// Assuming App\Models\LoanApplication exists
use App\Models\LoanApplication;
// Assuming App\Models\LoanTransaction exists
use App\Models\LoanTransaction;
// Assuming App\Models\Approval exists
use App\Models\Approval;
// Add other models if user has relationships to them (e.g., assets/equipment they are assigned)


/**
 * App\Models\User
 * 
 * Represents a user account in the system, extending Laravel's built-in Authenticatable user.
 * Includes fields for user details, links to HRMS employee data, organizational structure (department, position, grade),
 * and relationships to various application/workflow records (email, loan applications, transactions, approvals).
 * Uses traits for API tokens, factory, profile photos, roles, notifications, soft deletes, 2FA, and audit columns.
 *
 * @property int $id
 * @property string $name Existing HRMS field (e.g., username or short name).
 * @property string|null $full_name Full official name based on MOTAC design.
 * @property int|null $employee_id Foreign key to the HRMS Employee model.
 * @property string|null $nric NRIC/Identification Number based on MOTAC design.
 * @property string|null $mobile_number Mobile phone number based on MOTAC design.
 * @property string $email Existing HRMS field (could be primary contact email).
 * @property \Illuminate\Support\Carbon|null $email_verified_at Timestamp when email was verified.
 * @property mixed $password The user's password (hashed string). Use mixed or string.
 * @property string|null $profile_photo_path Path to the user's profile photo.
 * @property string|null $remember_token "Remember me" token.
 * @property string|null $personal_email Personal email based on MOTAC design.
 * @property string|null $motac_email MOTAC assigned email based on MOTAC design.
 * @property string|null $user_id_assigned External system assigned user ID based on MOTAC design.
 * @property int|null $department_id Foreign key to the associated department.
 * @property int|null $position_id Foreign key to the associated position.
 * @property int|null $grade_id Foreign key to the associated grade.
 * @property string|null $service_status Service status based on MOTAC design (e.g., permanent, contract).
 * @property string|null $appointment_type Appointment type based on MOTAC design.
 * @property bool $is_admin Flag indicating if the user is an administrator.
 * @property bool $is_bpm_staff Flag indicating if the user is BPM staff.
 * @property string|null $status User account status based on MOTAC design (e.g., active, inactive, suspended).
 * @property string|null $two_factor_secret For Two-Factor Authentication (Fortify).
 * @property string|null $two_factor_recovery_codes For Two-Factor Authentication (Fortify - typically string after casting to json).
 * @property \Illuminate\Support\Carbon|null $mobile_verified_at Timestamp when mobile number was verified (if column exists and cast).
 * @property int|null $created_by Foreign key to the user who created this record (handled by trait if applied here).
 * @property int|null $updated_by Foreign key to the user who last updated this record (handled by trait if applied here).
 * @property int|null $deleted_by Foreign key to the user who soft deleted this record (handled by trait if applied here).
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Approval> $approvals Approvals made by the user (as an officer).
 * @property-read int|null $approvals_count
 * @property-read \App\Models\User|null $createdBy Relation to the user who created this record (if trait adds this).
 * @property-read \App\Models\User|null $deletedBy Relation to the user who soft deleted this record (if trait adds this).
 * @property-read \App\Models\Department|null $department The department associated with the user.
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\EmailApplication> $emailApplications Email applications submitted by the user.
 * @property-read int|null $email_applications_count
 * @property-read \App\Models\Employee|null $employee The related Employee record from the HRMS system.
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $employees Inverse relationship (if User is linked from Employee via employee_id - confirm migration/relationship definition).
 * @property-read int|null $employees_count
 * @property-read \App\Models\Grade|null $grade The grade associated with the user.
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\LoanTransaction> $issuedTransactions Loan transactions where the user was the issuing officer.
 * @property-read int|null $issued_transactions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\LoanApplication> $loanApplications Loan applications submitted by the user.
 * @property-read int|null $loan_applications_count
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications Notifications received by the user.
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Permission\Models\Permission> $permissions The permissions belonging to the model (from HasRoles trait).
 * @property-read int|null $permissions_count
 * @property-read \App\Models\Position|null $position The position associated with the user.
 * @property-read string|null $profile_photo_url The URL to the user's profile photo (from HasProfilePhoto trait).
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\LoanTransaction> $receivedTransactions Loan transactions where the user was the receiving officer (on issue).
 * @property-read int|null $received_transactions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\LoanApplication> $responsibleLoanApplications Loan applications where the user was the responsible officer.
 * @property-read int|null $responsible_loan_applications_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Permission\Models\Role> $roles The roles belonging to the model (from HasRoles trait).
 * @property-read int|null $roles_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\LoanTransaction> $returningTransactions Loan transactions where the user was the returning officer.
 * @property-read int|null $returning_transactions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\LoanTransaction> $acceptedReturnTransactions Loan transactions where the user was the return accepting officer.
 * @property-read int|null $accepted_return_transactions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\EmailApplication> $supportedEmailApplications Email applications reviewed/supported by the user.
 * @property-read int|null $supported_email_applications_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Laravel\Sanctum\PersonalAccessToken> $tokens API tokens belonging to the user (from HasApiTokens trait).
 * @property-read int|null $tokens_count
 * @property-read \App\Models\User|null $updatedBy The user who last updated this record (if trait adds this).
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|User onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|User permission($permissions, $guardName = null) // From HasRoles trait
 * @method static \Illuminate\Database\Eloquent\Builder|User query()
 * @method static \Illuminate\Database\Eloquent\Builder|User role($roles, $guardName = null) // From HasRoles trait
 * @method static \Illuminate\Database\Eloquent\Builder|User whereAppointmentType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereDeletedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereDepartmentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereEmployeeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereFullName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereGradeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereIsAdmin($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereIsBpmStaff($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereMobileNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereMobileVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereNric($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePersonalEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePositionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereProfilePhotoPath($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereServiceStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereTwoFactorRecoveryCodes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereTwoFactorSecret($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereUserIdAssigned($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|User withoutTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereMotacEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User withoutPermission($permissions)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User withoutRole($roles, $guard = null)
 * @mixin \Eloquent
 */
>>>>>>> cc6eb9f4f020325c04fee080d2466584ff27bb90
class User extends Authenticatable
{
  use CreatedUpdatedDeletedBy,
    HasApiTokens,
    HasFactory,
    HasProfilePhoto,
    HasRoles,
    Notifiable,
    SoftDeletes,
    TwoFactorAuthenticatable;

  protected $fillable = [
    'name',
    'full_name',
    'employee_id',
    'nric',
    'mobile_number',
    'email',
    'password',
    'personal_email',
    'motac_email',
    'user_id_assigned',
    'department_id',
    'position_id',
    'grade_id',
    'service_status',
    'appointment_type',
    'is_admin',
    'is_bpm_staff',
    'status',
  ];

  protected $hidden = [
    'password',
    'remember_token',
    'two_factor_recovery_codes',
    'two_factor_secret',
    'created_by',
    'updated_by',
    'deleted_by',
    'deleted_at',
    'profile_photo_path',
  ];

  protected $casts = [
    'email_verified_at' => 'datetime',
    'mobile_verified_at' => 'datetime',
    'created_at' => 'datetime',
    'updated_at' => 'datetime',
    'deleted_at' => 'datetime',
    'is_admin' => 'boolean',
    'is_bpm_staff' => 'boolean',
    'two_factor_recovery_codes' => 'array',
  ];

  protected $appends = [
    'profile_photo_url',
  ];

  // Relationships
  public function employee(): BelongsTo
  {
    return $this->belongsTo(Employee::class);
  }

  public function department(): BelongsTo
  {
    return $this->belongsTo(Department::class);
  }

  public function position(): BelongsTo
  {
    return $this->belongsTo(Position::class);
  }

  public function grade(): BelongsTo
  {
    return $this->belongsTo(Grade::class);
  }

  public function emailApplications(): HasMany
  {
    return $this->hasMany(EmailApplication::class);
  }

  public function supportedEmailApplications(): HasMany
  {
    return $this->hasMany(EmailApplication::class, 'supporting_officer_id');
  }

  public function loanApplications(): HasMany
  {
    return $this->hasMany(LoanApplication::class);
  }

  public function responsibleLoanApplications(): HasMany
  {
    return $this->hasMany(LoanApplication::class, 'responsible_officer_id');
  }

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

  public function approvals(): HasMany
  {
    return $this->hasMany(Approval::class, 'officer_id');
  }

  // Helper Methods
  public function hasApprovalGrade(): bool
  {
    return $this->grade && $this->grade->level >= config('motac.approval.min_approver_grade_level', 0);
  }

  public function isAdmin(): bool
  {
    return $this->is_admin;
  }

  public function isBpmStaff(): bool
  {
    return $this->is_bpm_staff;
  }

  public function hasStatus(string $status): bool
  {
    return $this->status === $status;
  }

  public function isActive(): bool
  {
    return $this->hasStatus('active');
  }

  public function isInactive(): bool
  {
    return $this->hasStatus('inactive');
  }
}
