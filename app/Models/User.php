<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail; // Keep if your user needs email verification
// Assuming this trait exists and is used for audit columns on *other* models
// If this trait also adds audit FKs to the User model itself, keep it.
use App\Traits\CreatedUpdatedDeletedBy;
// Import Carbon for type hinting with date/datetime casts
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// Use the relationships directly without aliasing unless you need aliases for specific reasons
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany; // Keep if used for roles/permissions via HasRoles trait
use Illuminate\Database\Eloquent\Relations\HasMany;
// use Illuminate\Database\Eloquent\Relations\MorphMany; // Keep if User model has any MorphMany relationships
use Illuminate\Database\Eloquent\SoftDeletes; // Keep if using soft deletes for users (migration supports this)
use Illuminate\Database\Eloquent\Casts\Attribute; // Keep if using Attribute casts/accessors
// Import Collection for return type hinting on HasMany/BelongsToMany relationships
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Auth\User as Authenticatable; // Extend Laravel's base user class
use Illuminate\Notifications\Notifiable; // Keep if using notifications
use Laravel\Fortify\TwoFactorAuthenticatable; // Keep if using Fortify 2FA
use Laravel\Jetstream\HasProfilePhoto; // Keep if using Jetstream profile photos
use Laravel\Sanctum\HasApiTokens; // Keep if using Sanctum API tokens
use Spatie\Permission\Traits\HasRoles; // Keep if using Spatie Permissions - Adds 'roles' and 'permissions' relationships


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
 *
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
 *
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
 * @mixin \Eloquent
 */
class User extends Authenticatable
{
  // Keep existing traits
  use CreatedUpdatedDeletedBy, // Keep if this trait adds audit FKs to the User model itself (less common for User model, but possible)
    HasApiTokens,           // Keep if using Sanctum API tokens
    HasFactory,             // Keep if using factories
    HasProfilePhoto,        // Keep if using Jetstream profile photos
    HasRoles,               // Keep if using Spatie Permissions
    Notifiable,             // Keep if using notifications
    SoftDeletes,            // Keep if using soft deletes for users
    TwoFactorAuthenticatable; // Keep if using Fortify 2FA

  /**
   * The attributes that are mass assignable.
   * Includes standard user fields, new fields from MOTAC design, and audit columns.
   * 'id' is typically not mass assignable.
   *
   * @var array<int, string>
   */
  protected $fillable = [
    'name',                 // Existing HRMS field (e.g., username or short name)
    'full_name',            // Add full_name based on MOTAC design for official name
    'employee_id',          // Existing HRMS field - foreign key to employee model
    'nric',                 // NRIC based on MOTAC design
    'mobile_number',        // Mobile number based on MOTAC design

    'email',                // Existing HRMS field (could be primary contact email)
    'email_verified_at',    // Existing HRMS field
    'password',             // Existing HRMS field
    // 'profile_photo_path', // Handled by HasProfilePhoto trait usually, remove from fillable unless explicitly needed
    // 'remember_token',     // Managed by Laravel's built-in authentication

    'personal_email',       // Personal email based on MOTAC design
    'motac_email',          // MOTAC email based on MOTAC design (assigned after provisioning)
    'user_id_assigned',     // External user ID based on MOTAC design (assigned after provisioning)

    'department_id',        // Foreign key to departments table
    'position_id',          // Foreign key to positions table
    'grade_id',             // Foreign key to grades table

    'service_status',       // Service status based on MOTAC design (e.g., permanent, contract)
    'appointment_type',     // Appointment type based on MOTAC design

    'is_admin',             // Flag indicating administrator role
    'is_bpm_staff',         // Flag indicating BPM staff role
    'status',               // User account status based on MOTAC design (e.g., active, inactive, suspended)

    // Audit columns are handled by CreatedUpdatedDeletedBy trait if applied to User model.
    // Otherwise, include here manually if you need to mass assign them.
    // Based on migration, User table *does* have audit columns. Assuming trait handles on this model.
    // If trait does NOT handle on User model itself, uncomment these:
    // 'created_by',
    // 'updated_by',
    // 'deleted_by',
  ];

  /**
   * The attributes that should be hidden for serialization.
   * Prevents sensitive or unnecessary fields from being included in JSON responses.
   * Includes password, security tokens, and typically audit columns.
   *
   * @var array<int, string>
   */
  protected $hidden = [
    'password',
    'remember_token',
    'two_factor_recovery_codes', // Keep if using Fortify 2FA
    'two_factor_secret',         // Keep if using Fortify 2FA
    // Audit columns are often hidden unless explicitly needed
    'created_by',
    'updated_by',
    'deleted_by',
    'deleted_at', // Hide soft delete timestamp
    // Consider hiding profile_photo_path if profile_photo_url accessor is used
    'profile_photo_path',
  ];

  /**
   * The attributes that should be cast.
   * Ensures data types are correct, especially for dates, booleans, and potential JSON.
   *
   * @var array<string, string>
   */
  protected $casts = [
    'name'                    => 'string',    // Explicitly cast string attributes
    'full_name'               => 'string',
    'nric'                    => 'string',
    'mobile_number'           => 'string',
    'email'                   => 'string',
    'password'                => 'string',    // Passwords are strings (hashed)
    'profile_photo_path'      => 'string',    // Path is a string
    'remember_token'          => 'string',    // Token is a string
    'personal_email'          => 'string',
    'motac_email'             => 'string',
    'user_id_assigned'        => 'string',    // Assuming external ID is string
    'service_status'          => 'string',
    'appointment_type'        => 'string',
    'status'                  => 'string',    // User account status
    'two_factor_secret'       => 'string',    // 2FA secret is string
    'two_factor_recovery_codes' => 'json',    // Recovery codes are often stored as JSON

    'employee_id'             => 'integer',   // Cast FKs to integer
    'department_id'           => 'integer',
    'position_id'             => 'integer',
    'grade_id'                => 'integer',
    'created_by'              => 'integer',   // Audit FKs (if cast here)
    'updated_by'              => 'integer',
    'deleted_by'              => 'integer',

    'email_verified_at'       => 'datetime',  // Cast timestamps to Carbon instances
    'mobile_verified_at'      => 'datetime',  // Keep existing cast if this column exists
    'created_at'              => 'datetime',
    'updated_at'              => 'datetime',
    'deleted_at'              => 'datetime',

    'is_admin'                => 'boolean',   // Cast boolean flags
    'is_bpm_staff'            => 'boolean',
  ];

  /**
   * The accessors to append to the model's array form.
   * Includes virtual attributes derived from traits or accessors.
   *
   * @var array<int, string>
   */
  protected $appends = [
    'profile_photo_url', // Provided by HasProfilePhoto trait
    // 'employee_full_name', // Uncomment if still deriving from HRMS Employee
    // Add other virtual attributes you want appended by default
  ];

  // The CreatedUpdatedDeletedBy trait is assumed to add these audit relationships.
  // Their docblocks are included in the main class docblock above for clarity.
  // Example docblocks added by the trait (if trait is applied to User model itself):
  /*
     * Get the user who created this model record.
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\User, \App\Models\User>
     */
  // public function createdBy(): BelongsTo; // If User model has created_by FK

  /*
     * Get the user who last updated this model record.
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\User, \App\Models\User>
     */
  // public function updatedBy(): BelongsTo; // If User model has updated_by FK

  /*
     * Get the user who soft deleted this model record.
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\User, \App\Models\User>
     */
  // public function deletedBy(): BelongsTo; // If User model has deleted_by FK


  // 👉 Existing HRMS Links
  /**
   * Get the related Employee record from the HRMS system.
   * Defines a many-to-one relationship where a User belongs to one HRMS Employee record.
   * Assumes the 'users' table has an 'employee_id' foreign key linking to the 'employees' table.
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Employee, \App\Models\User>
   */
  public function employee(): BelongsTo // Added return type hint and refined docblock
  {
    // Link to HRMS Employee model (if it exists), linked by employee_id
    return $this->belongsTo(Employee::class, 'employee_id'); // Explicitly define FK
  }

  // 👉 New MOTAC Resource Management Relationships

  /**
   * Get the department associated with the user.
   * Defines a many-to-one relationship where a User belongs to one Department.
   * Assumes the 'users' table has a 'department_id' foreign key.
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Department, \App\Models\User>
   */
  public function department(): BelongsTo // Added return type hint
  {
    // Defines a many-to-one relationship with the Department model
    return $this->belongsTo(Department::class, 'department_id'); // Explicitly define FK
  }

  /**
   * Get the position associated with the user.
   * Defines a many-to-one relationship where a User belongs to one Position.
   * Assumes the 'users' table has a 'position_id' foreign key linking to the 'positions' table.
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Position, \App\Models\User>
   */
  public function position(): BelongsTo // Added return type hint
  {
    // Link to the Position model (which maps to the 'positions' table), linked by position_id
    return $this->belongsTo(Position::class, 'position_id'); // Explicitly define FK
  }

  /**
   * Get the grade associated with the user.
   * Defines a many-to-one relationship where a User belongs to one Grade.
   * Assumes the 'users' table has a 'grade_id' foreign key.
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Grade, \App\Models\User>
   */
  public function grade(): BelongsTo // Added return type hint
  {
    // Defines a many-to-one relationship with the Grade model
    return $this->belongsTo(Grade::class, 'grade_id'); // Explicitly define FK
  }

  /**
   * Get the email applications submitted by the user.
   * Defines a one-to-many relationship where a User has many EmailApplications as the applicant.
   * Assumes the 'email_applications' table has a 'user_id' foreign key.
   *
   * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\EmailApplication>
   */
  public function emailApplications(): HasMany // Added return type hint
  {
    // Defines a one-to-many relationship with the EmailApplication model
    return $this->hasMany(EmailApplication::class, 'user_id'); // User as applicant
  }

  /**
   * Get the email applications reviewed/supported by the user.
   * Defines a one-to-many relationship where a User has many EmailApplications as the supporting officer.
   * Assumes the 'email_applications' table has a 'supporting_officer_id' foreign key.
   *
   * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\EmailApplication>
   */
  public function supportedEmailApplications(): HasMany // Added return type hint
  {
    // Defines a one-to-many relationship with the EmailApplication model
    return $this->hasMany(EmailApplication::class, 'supporting_officer_id'); // User as supporting officer
  }

  /**
   * Get the loan applications submitted by the user.
   * Defines a one-to-many relationship where a User has many LoanApplications as the applicant.
   * Assumes the 'loan_applications' table has a 'user_id' foreign key.
   *
   * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\LoanApplication>
   */
  public function loanApplications(): HasMany // Added return type hint
  {
    // Defines a one-to-many relationship with the LoanApplication model
    return $this->hasMany(LoanApplication::class, 'user_id'); // User as applicant
  }

  /**
   * Get the loan applications where the user was the responsible officer.
   * Defines a one-to-many relationship where a User is the responsible officer for many LoanApplications.
   * Assumes the 'loan_applications' table has a 'responsible_officer_id' foreign key.
   *
   * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\LoanApplication>
   */
  public function responsibleLoanApplications(): HasMany // Added return type hint
  {
    // Defines a one-to-many relationship with the LoanApplication model
    return $this->hasMany(LoanApplication::class, 'responsible_officer_id'); // User as responsible officer
  }


  /**
   * Get the loan transactions where the user was the issuing officer.
   * Defines a one-to-many relationship where a User was the issuing officer for many LoanTransactions.
   * Assumes the 'loan_transactions' table has an 'issuing_officer_id' foreign key.
   *
   * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\LoanTransaction>
   */
  public function issuedTransactions(): HasMany // Added return type hint
  {
    // Defines a one-to-many relationship with the LoanTransaction model
    return $this->hasMany(LoanTransaction::class, 'issuing_officer_id'); // Explicitly define FK
  }

  /**
   * Get the loan transactions where the user was the receiving officer (on issue).
   * Defines a one-to-many relationship where a User was the receiving officer for many LoanTransactions.
   * Assumes the 'loan_transactions' table has a 'receiving_officer_id' foreign key.
   *
   * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\LoanTransaction>
   */
  public function receivedTransactions(): HasMany // Added return type hint
  {
    // Defines a one-to-many relationship with the LoanTransaction model
    return $this->hasMany(LoanTransaction::class, 'receiving_officer_id'); // Explicitly define FK
  }

  /**
   * Get the loan transactions where the user was the returning officer.
   * Defines a one-to-many relationship where a User was the returning officer for many LoanTransactions.
   * Assumes the 'loan_transactions' table has a 'returning_officer_id' foreign key.
   *
   * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\LoanTransaction>
   */
  public function returningTransactions(): HasMany // Added return type hint
  {
    // Defines a one-to-many relationship with the LoanTransaction model
    return $this->hasMany(LoanTransaction::class, 'returning_officer_id'); // Explicitly define FK
  }

  /**
   * Get the loan transactions where the user was the return accepting officer.
   * Defines a one-to-many relationship where a User was the return accepting officer for many LoanTransactions.
   * Assumes the 'loan_transactions' table has a 'return_accepting_officer_id' foreign key.
   *
   * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\LoanTransaction>
   */
  public function acceptedReturnTransactions(): HasMany // Added return type hint
  {
    // Defines a one-to-many relationship with the LoanTransaction model
    return $this->hasMany(LoanTransaction::class, 'return_accepting_officer_id'); // Explicitly define FK
  }

  /**
   * Get the approvals made by the user (as an officer).
   * Defines a one-to-many relationship where a User has made many Approvals.
   * Assumes the 'approvals' table has an 'officer_id' foreign key.
   *
   * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\Approval>
   */
  public function approvals(): HasMany // Added return type hint
  {
    // Defines a one-to-many relationship with the Approval model
    return $this->hasMany(Approval::class, 'officer_id'); // Explicitly define FK
  }

  // 👉 Inverse Audit Relationships (Records created/updated/deleted BY this user)
  // Assuming CreatedUpdatedDeletedBy trait adds audit FKs to OTHER models.
  // Add these relationships to the User model to see what THIS user audited.
  // A generic relationship to 'all models' created/updated/deleted is complex and not standard Eloquent.
  // Define specific relationships for models you want to track audit actions BY this user on.

  /**
   * Get all Department records created by this user.
   *
   * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\Department>
   */
  // public function createdDepartments(): HasMany // Example specific inverse audit relationship
  // {
  //      return $this->hasMany(Department::class, 'created_by');
  // }

  /**
   * Get all Department records updated by this user.
   *
   * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\Department>
   */
  // public function updatedDepartments(): HasMany // Example specific inverse audit relationship
  // {
  //      return $this->hasMany(Department::class, 'updated_by');
  // }

  /**
   * Get all Department records deleted by this user (soft deletes).
   *
   * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\Department>
   */
  // public function deletedDepartments(): HasMany // Example specific inverse audit relationship
  // {
  //      return $this->hasMany(Department::class, 'deleted_by');
  // }

  // Add more specific inverse audit relationships as needed for other models.


  // 👉 New MOTAC Resource Management Helper Methods

  /**
   * Check if the user has a grade level required for approval.
   * Checks if the user's grade level is greater than or equal to the minimum approver grade level defined in config.
   * Note: Requires the 'grade' relationship to be loaded before calling this method to avoid N+1 issues.
   *
   * @return bool True if the user's grade meets or exceeds the minimum approver grade level, false otherwise.
   */
  public function hasApprovalGrade(): bool // Added return type hint and refined docblock
  {
    // Ensure the grade relationship is loaded and not null before accessing level
    // Use optional chaining (?->) for safety and null coalescing (??) for grade level default
    // Check if the grade relationship exists and has a 'level' property.
    return $this->relationLoaded('grade')
      && $this->grade !== null
      && ($this->grade->level ?? 0) >= config('motac.approval.min_approver_grade_level', 0); // Default config value to 0 for safety
  }


  /**
   * Check if the user is an administrator.
   * Directly returns the boolean value of the 'is_admin' attribute after casting.
   *
   * @return bool True if the user is an administrator, false otherwise.
   */
  public function isAdmin(): bool // Added return type hint and refined docblock
  {
    // Assuming a boolean column 'is_admin' exists and is cast to boolean via $casts
    return $this->is_admin;
    // Or combine with role check if needed:
    // return $this->is_admin || $this->hasRole('Admin');
  }

  /**
   * Check if the user is BPM staff.
   * Directly returns the boolean value of the 'is_bpm_staff' attribute after casting.
   *
   * @return bool True if the user is BPM staff, false otherwise.
   */
  public function isBpmStaff(): bool // Added return type hint and refined docblock
  {
    // Assuming a boolean column 'is_bpm_staff' exists and is cast to boolean via $casts
    return $this->is_bpm_staff;
    // Or combine with role check if needed:
    // return $this->is_bpm_staff || $this->hasRole('BPM Staff');
  }

  /**
   * Check if the user account has a specific status.
   * Checks the status column against the provided status string.
   *
   * @param string $status The status string to check against (e.g., 'active', 'inactive').
   * @return bool True if the user's status matches the provided status, false otherwise.
   */
  public function hasStatus(string $status): bool // Added type hint and return type hint, refined docblock
  {
    // Assuming a string column 'status' exists and is cast to string via $casts
    return $this->status === $status;
  }

  /**
   * Check if the user account is active.
   * Uses the hasStatus helper method.
   *
   * @return bool True if the user status is 'active', false otherwise.
   */
  public function isActive(): bool // Added specific status checker
  {
    return $this->hasStatus('active'); // Assuming 'active' is the status string for active users
  }

  /**
   * Check if the user account is inactive.
   * Uses the hasStatus helper method.
   *
   * @return bool True if the user status is 'inactive', false otherwise.
   */
  public function isInactive(): bool // Added specific status checker
  {
    return $this->hasStatus('inactive'); // Assuming 'inactive' is the status string for inactive users
  }


  // Add any other existing methods or accessors/mutators below this line

  // The getProfilePhotoUrlAttribute accessor is typically provided by the HasProfilePhoto trait.
  // You do not need to define it here unless you want to override the trait's behavior.
}
