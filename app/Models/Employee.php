<?php

namespace App\Models;

use App\Traits\CreatedUpdatedDeletedBy;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log; // Used in defaultProfilePhotoUrl catch

// Import models for relationships
use App\Models\User;
use App\Models\Department;
use App\Models\Position;
use App\Models\Grade;
use App\Models\Contract;
use App\Models\EmployeeLeave;
use App\Models\Timeline;


/**
 * App\Models\Employee
 *
 * Represents an employee record within the organization.
 * Linked to User (identity), Department, Position, Grade, Contract, Timeline.
 * Manages personal details, service status, and relationships like Leave Requests.
 * Includes audit trails and soft deletion.
 *
 * @property int $id
 * @property int|null $user_id Foreign key to the related User record (if user is separate)
 * @property int|null $department_id Foreign key to the related Department
 * @property int|null $position_id Foreign key to the related Position
 * @property int|null $grade_id Foreign key to the related Grade
 * @property int|null $contract_id Foreign key to the related Contract
 * @property string|null $full_name Combined full name of the employee (Derived via Accessor)
 * @property string $first_name Employee's first name (Database Column)
 * @property string|null $father_name Employee's father's name (Database Column)
 * @property string $last_name Employee's last name (Database Column)
 * @property string|null $mother_name Employee's mother's name
 * @property string|null $birth_and_place Employee's date and place of birth
 * @property string $national_number National ID or Passport number
 * @property string $mobile_number Employee's mobile phone number
 * @property string|null $personal_email Employee's personal email address
 * @property string|null $motac_email Employee's official MOTAC email address
 * @property string|null $degree Employee's educational degree
 * @property string|null $gender Employee's gender (e.g., 'Male', 'Female', 'Other')
 * @property string|null $address Employee's physical address
 * @property string|null $service_status Employee's service status (e.g., 'permanent', 'contract')
 * @property string|null $appointment_type Employee's appointment type
 * @property string $status Employee's current status (e.g., 'active', 'inactive', 'on_leave')
 * @property string|null $notes Any additional notes about the employee
 * @property string|null $profile_photo_path Path to the employee's profile photo file
 * @property bool $is_active Flag indicating if the employee is currently active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $deleted_at For soft deletion
 * @property int|null $created_by Foreign key to the User who created the record
 * @property int|null $updated_by Foreign key to the User who last updated the record
 * @property int|null $deleted_by Foreign key to the User who soft-deleted the record
 *
 * @property-read User|null $user Related User model (if separate identity)
 * @property-read Department|null $department Related Department model
 * @property-read Position|null $position Related Position model
 * @property-read Grade|null $grade Related Grade model
 * @property-read Contract|null $contract Related Contract model
 * @property-read \Illuminate\Database\Eloquent\Collection<int, EmployeeLeave> $leaveRequests Related EmployeeLeave records
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Timeline> $timelines Related Timeline records (assuming employee_id FK)
 * @property-read string $profile_photo_url Accessor for the profile photo URL (Handles fallback)
 * @property-read string $service_status_translated Accessor for translated service status (example)
 *
 * @method static Builder|Employee newModelQuery()
 * @method static Builder|Employee newQuery()
 * @method static Builder|Employee onlyTrashed()
 * @method static Builder|Employee query()
 * @method static Builder|Employee withTrashed()
 * @method static Builder|Employee withoutTrashed()
 * @method static Builder|Employee onLeave(Builder $query) // Assuming this scope exists
 *
 * @mixin \Eloquent
 */
class Employee extends Model
{
  use HasFactory, SoftDeletes, CreatedUpdatedDeletedBy;

  // Attributes that are mass assignable
  protected $fillable = [
    'user_id',
    'department_id',
    'position_id',
    'grade_id',
    'contract_id',
    'first_name',
    'father_name',
    'last_name',
    'mother_name',
    'birth_and_place',
    'national_number',
    'mobile_number',
    'personal_email',
    'motac_email',
    'degree',
    'gender',
    'address',
    'service_status',
    'appointment_type',
    'status',
    'notes',
    'profile_photo_path',
    'is_active',
  ];

  // Attribute casting
  protected $casts = [
    'is_active' => 'boolean',
    'created_at' => 'datetime',
    'updated_at' => 'datetime',
    'deleted_at' => 'datetime',
  ];

  // Accessors to append to array/JSON output
  protected $appends = [
    'full_name',
    'profile_photo_url',
    'service_status_translated',
  ];


  // Relationships

  /**
   * Get the user associated with the employee.
   */
  public function user(): BelongsTo
  {
    return $this->belongsTo(User::class);
  }

  /**
   * Get the department that the employee belongs to.
   */
  public function department(): BelongsTo
  {
    return $this->belongsTo(Department::class);
  }

  /**
   * Get the position that the employee holds.
   */
  public function position(): BelongsTo
  {
    return $this->belongsTo(Position::class);
  }

  /**
   * Get the grade that the employee has.
   */
  public function grade(): BelongsTo
  {
    return $this->belongsTo(Grade::class);
  }

  /**
   * Get the contract that the employee has.
   */
  public function contract(): BelongsTo
  {
    return $this->belongsTo(Contract::class);
  }

  /**
   * Get the leave requests for the employee.
   * Assumes a pivot table or intermediate model `EmployeeLeave` with 'employee_id' FK.
   */
  public function leaveRequests(): HasMany
  {
    return $this->hasMany(EmployeeLeave::class, 'employee_id');
  }

  /**
   * Get the timelines for the employee.
   * Assumes 'employee_id' foreign key on the 'timelines' table.
   */
  public function timelines(): HasMany
  {
    return $this->hasMany(Timeline::class, 'employee_id');
  }


  // Attributes (Accessors/Mutators)

  /**
   * Get the employee's full name.
   * Combines first, father (if exists), and last names.
   * Uses null-coalescing for robustness against missing attributes in the data array.
   */
  protected function fullName(): Attribute
  {
    return Attribute::make(
      get: fn($value, $attributes) => trim(
        ($attributes['first_name'] ?? '') . ' ' .
          (($attributes['father_name'] ?? '') ? ($attributes['father_name'] ?? '') . ' ' : '') .
          ($attributes['last_name'] ?? '')
      ),
    );
  }

  /**
   * Get the URL to the employee's profile photo.
   * Provides a fallback URL if no photo path is set or the file doesn't exist.
   */
  protected function profilePhotoUrl(): Attribute
  {
    return Attribute::make(
      get: fn() => $this->profile_photo_path
        ? Storage::disk($this->profilePhotoDisk())->url($this->profile_photo_path)
        : $this->defaultProfilePhotoUrl(),
    );
  }

  /**
   * Get the disk that profile photos should be stored on.
   */
  protected function profilePhotoDisk(): string
  {
    return isset($_SERVER['VAPOR_ARTIFACTS_DIR']) ? 's3' : 'public';
  }

  /**
   * Get the default profile photo URL if no profile photo has been uploaded.
   * Generates a UI Avatar based on the full name.
   */
  protected function defaultProfilePhotoUrl(): string
  {
    try {
      $name = trim(collect(explode(' ', $this->full_name))->map(function ($segment) {
        return mb_substr($segment, 0, 1);
      })->implode(' '));

      if (empty($name)) {
        $name = 'U';
      }
    } catch (\Throwable $e) {
      Log::error('Error generating default profile photo URL for employee ID ' . ($this->id ?? 'N/A') . ': ' . $e->getMessage());
      $name = 'U';
    }

    return 'https://ui-avatars.com/api/?name=' . urlencode($name) . '&color=7F9CF5&background=EBF4FF';
  }

  /**
   * Get the translated service status.
   * Assumes translation keys like 'service_status.permanent', 'service_status.contract', etc.
   */
  protected function serviceStatusTranslated(): Attribute
  {
    return Attribute::make(
      get: fn() => $this->service_status ? __("service_status." . $this->service_status) : '',
    );
  }


  // Scopes

  /**
   * Scope to include employees currently on leave.
   * Requires 'leaveRequests' relationship to EmployeeLeave,
   * and EmployeeLeave model has 'from_date', 'to_date', and 'status' columns.
   */
  public function scopeOnLeave(Builder $query): Builder
  {
    $today = Carbon::today();
    return $query->whereHas('leaveRequests', function (Builder $query) use ($today) {
      $query->where('status', 'approved')
        ->whereDate('from_date', '<=', $today)
        ->whereDate('to_date', '>=', $today);
    });
  }
}
