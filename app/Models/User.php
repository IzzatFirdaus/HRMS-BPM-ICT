<?php
// User.php - Moving constants

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

// --- ADD THESE TWO USE STATEMENTS for Email Verification ---
use Illuminate\Contracts\Auth\MustVerifyEmail; // Import the interface
use Illuminate\Auth\MustVerifyEmail as MustVerifyEmailTrait; // Import the trait, use alias to avoid conflict if another MustVerifyEmail exists
// --- END ADDITIONS ---


/**
 * Add PHPDoc annotations to help static analysis tools (like Intelephense)
 * recognize the dynamic properties provided by Eloquent's magic methods.
 * This helps resolve "Undefined method ..."
 * @property int $id
 * @property string $name
 * @property string $email
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property mixed $password
 * @property string|null $two_factor_secret
 * @property string|null $two_factor_recovery_codes
 * @property string|null $two_factor_confirmed_at
 * @property string|null $remember_token
 * @property int|null $current_team_id
 * @property string|null $profile_photo_path
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at // Added for SoftDeletes trait
 * @property int|null $employee_id // Added based on previous discussions/schema
 * @property int|null $department_id // Added based on previous discussions/schema
 * @property int|null $position_id // Added based on previous discussions/schema
 * @property int|null $grade_id // Added based on previous discussions/schema
 * @property string|null $full_name // Added based on previous discussions/schema
 * @property string|null $personal_email // Added based on previous discussions/schema
 * @property string|null $motac_email // Added based on previous discussions/schema
 * @property string|null $nric // Added based on previous discussions/schema
 * @property string|null $mobile_number // Added based on previous discussions/schema
 * @property int|null $user_id_assigned // Purpose unclear from context - related to assignment?
 * @property string|null $service_status // e.g., permanent, contract, mystep, intern, other_agency
 * @property string|null $appointment_type // e.g., 'P', 'C', 'MySTEP', 'Intern'
 * @property string|null $status // e.g., 'active', 'inactive', 'suspended'
 * @property bool $is_admin // Custom flag, consider using roles instead
 * @property bool $is_bpm_staff // Custom flag, consider using roles instead
 * @property-read string $profile_photo_url
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Laravel\Sanctum\PersonalAccessToken> $tokens
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Permission\Models\Permission> $permissions // Added for Spatie
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Permission\Models\Role> $roles // Added for Spatie
 * @property-read Employee|null $employee // Relationship to Employee model
 * @property-read Department|null $department // Relationship to Department model
 * @property-read Position|null $position // Relationship to Position model
 * @property-read Grade|null $grade // Relationship to Grade model
 * @property-read \Illuminate\Database\Eloquent\Collection<int, LoanApplication> $loanApplications // Assuming HasMany relationship
 * @property-read \Illuminate\Database\Eloquent\Collection<int, EmailApplication> $emailApplications // Assuming HasMany relationship
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Approval> $approvals // Assuming HasMany or other relationship
 * @property-read \Illuminate\Database\Eloquent\Collection<int, LoanTransaction> $issuedTransactions // Assuming HasMany relationships
 * @property-read \Illuminate\Database\Eloquent\Collection<int, LoanTransaction> $receivedTransactions // Assuming HasMany relationships
 * @property-read \Illuminate\Database\Eloquent\Collection<int, LoanTransaction> $returnedTransactions // Assuming HasMany relationships
 * @property-read \Illuminate\Database\Eloquent\Collection<int, LoanTransaction> $returnAcceptedTransactions // Assuming HasMany relationships
 * @method static \Illuminate\Database\Eloquent\Builder|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|User onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|User query()
 * @method static \Illuminate\Database\Eloquent\Builder|User withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|User withoutTrashed()
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder|User permission($permissions) // Added for Spatie
 * @method static \Illuminate\Database\Eloquent\Builder|User role($roles, $guard = null) // Added for Spatie
 * @method static \Illuminate\Database\Eloquent\Builder|User withRoleAndPermission() // Added for Spatie Scope
 * @method static \Illuminate\Database\Eloquent\Builder|User withRoles(...$roles) // Added for Spatie Scope
 * @method static \Illuminate\Database\Eloquent\Builder|User withPermissions(...$permissions) // Added for Spatie Scope
 * @method static \Illuminate\Database\Eloquent\Builder|User whereDoesntHaveRole() // Added for Spatie Scope
 * @method static \Illuminate\Database\Eloquent\Builder|User whereHasRole() // Added for Spatie Scope
 * @method static \Illuminate\Database\Eloquent\Builder|User whereDoesntHavePermission() // Added for Spatie Scope
 * @method static \Illuminate\Database\Eloquent\Builder|User whereHasPermission() // Added for Spatie Scope
 * @mixin \Eloquent
 */
class User extends Authenticatable implements MustVerifyEmail // Implement MustVerifyEmail interface
{
  use HasApiTokens, HasFactory, HasProfilePhoto, Notifiable, TwoFactorAuthenticatable, SoftDeletes, HasRoles; // Use SoftDeletes and HasRoles traits
  use CreatedUpdatedDeletedBy; // Assuming this trait is used
  use MustVerifyEmailTrait; // Use the MustVerifyEmail trait


  // --- Status Constants for service_status attribute ---
  // MOVED these constants here from inside the accessor method where they caused a syntax error
  public const SERVICE_STATUS_PERMANENT = 'permanent';
  public const SERVICE_STATUS_CONTRACT = 'contract';
  public const SERVICE_STATUS_MYSTEP = 'mystep';
  public const SERVICE_STATUS_INTERN = 'intern';
  public const SERVICE_STATUS_OTHER_AGENCY = 'other_agency';
  // --- End Status Constants ---


  /**
   * The attributes that are mass assignable.
   *
   * @var array<int, string>
   */
  protected $fillable = [
    'name',
    'email',
    'password',
    'employee_id', // Added based on schema
    'department_id', // Added based on schema
    'position_id', // Added based on schema
    'grade_id', // Added based on schema
    'full_name', // Added based on schema
    'personal_email', // Added based on schema
    'motac_email', // Added based on schema
    'nric', // Added based on schema
    'mobile_number', // Added based on schema
    'user_id_assigned', // Added based on schema
    'service_status', // Added based on schema
    'appointment_type', // Added based on schema
    'status', // Added based on schema (e.g., active, inactive)
    'is_admin', // Added based on schema (consider using roles)
    'is_bpm_staff', // Added based on schema (consider using roles)
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
   * The accessors to append to the model's array form.
   *
   * @var array<int, string>
   */
  protected $appends = [
    'profile_photo_url',
    'service_status_translated', // Append the translated status
  ];

  /**
   * Get the attributes that should be cast.
   *
   * @return array<string, string>
   */
  protected function casts(): array
  {
    return [
      'email_verified_at' => 'datetime',
      'password' => 'hashed',
      'two_factor_confirmed_at' => 'datetime',
      'is_admin' => 'boolean', // Cast boolean fields
      'is_bpm_staff' => 'boolean', // Cast boolean fields
      'created_at' => 'datetime', // Added for SoftDeletes consistency
      'updated_at' => 'datetime', // Added for SoftDeletes consistency
      'deleted_at' => 'datetime', // Added for SoftDeletes consistency
      'department_id' => 'integer',
      'position_id' => 'integer',
      'grade_id' => 'integer',
      'employee_id' => 'integer',
    ];
  }


  // --- Relationships ---

  /**
   * Get the employee record associated with the user.
   * Assumes a one-to-one relationship where User has one Employee.
   * Assumes the 'employees' table has a 'user_id' foreign key.
   */
  public function employee(): BelongsTo // Changed to BelongsTo, assuming employee table has user_id
  {
    // Assuming employee table has user_id foreign key
    return $this->belongsTo(Employee::class, 'employee_id'); // Adjust FK if needed
  }

  /**
   * Get the department the user belongs to.
   */
  public function department(): BelongsTo
  {
    return $this->belongsTo(Department::class);
  }

  /**
   * Get the position the user holds.
   */
  public function position(): BelongsTo
  {
    return $this->belongsTo(Position::class);
  }

  /**
   * Get the grade the user has.
   */
  public function grade(): BelongsTo
  {
    return $this->belongsTo(Grade::class);
  }


  // Relationships for applications and transactions where this user is the applicant
  public function loanApplications(): HasMany
  {
    return $this->hasMany(LoanApplication::class, 'user_id');
  }

  public function emailApplications(): HasMany
  {
    return $this->hasMany(EmailApplication::class, 'user_id');
  }

  // Relationships for applications/transactions where this user is involved as an officer
  // Add relationships like issuedTransactions, receivedTransactions, etc. if needed
  public function issuedTransactions(): HasMany
  {
    return $this->hasMany(LoanTransaction::class, 'issuing_officer_id');
  }

  public function receivedTransactions(): HasMany
  {
    return $this->hasMany(LoanTransaction::class, 'receiving_officer_id');
  }

  public function returnedTransactions(): HasMany
  {
    return $this->hasMany(LoanTransaction::class, 'returning_officer_id');
  }

  public function returnAcceptedTransactions(): HasMany
  {
    return $this->hasMany(LoanTransaction::class, 'return_accepting_officer_id');
  }

  // Relationship to Approvals where this user is the officer assigned to approve
  public function approvals(): HasMany
  {
    return $this->hasMany(Approval::class, 'officer_id');
  }


  // --- Notification Routing ---

  /**
   * Route notifications for the mail channel to the user's personal email if available.
   * Falls back to the primary email if personal email is not set.
   */
  public function routeNotificationForMail(): string
  {
    // Check if personal_email is set and is a valid email format
    if ($this->personal_email && filter_var($this->personal_email, FILTER_VALIDATE_EMAIL)) {
      return $this->personal_email;
    }

    // For all other notifications, or if personal_email is missing/invalid for the above,
    // fallback to the primary 'email' field, which is the Notifiable trait's default.
    return $this->email; // Or simply return null to let Notifiable trait handle it
  }

  // You can add similar methods for other notification channels if needed
  // public function routeNotificationForVonage($notification): string
  // {
  //      // Return the user's mobile number if available and valid for SMS notifications
  //      return $this->mobile_number;
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
    // Using the constants defined at the top of the class
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

  // Add other model methods, scopes, or relationships...

  /**
   * Determine if the user has the given role(s).
   * Overrides the default spatie method if custom logic is needed,
   * otherwise can be removed and use the trait's method directly.
   *
   * @param  string|array  $roles
   * @param  string|null  $guard
   * @return bool
   */
  // public function hasRole($roles, string $guard = null): bool
  // {
  //     // Custom logic if needed, otherwise just use the trait's method:
  //     return parent::hasRole($roles, $guard);
  // }

}
