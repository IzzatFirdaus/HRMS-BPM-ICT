<?php

namespace App\Models;

use App\Traits\CreatedUpdatedDeletedBy;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

// Import models for relationships
use App\Models\Timeline;
use App\Models\User;
use App\Models\Employee;


/**
 * App\Models\Department
 *
 * Represents an organizational department or unit within MOTAC.
 * Linked to Timelines, Users, and Employees via one-to-many relationships.
 * Includes audit trails and soft deletion.
 *
 * @property int $id
 * @property string $name
 * @property string|null $branch_type Example: 'HQ', 'Branch'
 * @property string|null $code Short code for the department
 * @property string|null $description
 * @property bool $is_active
 * @property int|null $head_of_department_id Foreign key to the User who is head of this department
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $deleted_at For soft deletion
 * @property int|null $created_by Foreign key to the User who created the record
 * @property int|null $updated_by Foreign key to the User who last updated the record
 * @property int|null $deleted_by Foreign key to the User who soft-deleted the record
 *
 * @property-read Collection<int, Timeline> $timelines
 * @property-read int|null $timelines_count
 * @property-read Collection<int, User> $users
 * @property-read int|null $users_count
 * @property-read Collection<int, Employee> $employees
 * @property-read int|null $employees_count
 * @property-read User|null $headOfDepartment
 *
 * @method static Builder|Department newModelQuery()
 * @method static Builder|Department newQuery()
 * @method static Builder|Department onlyTrashed()
 * @method static Builder|Department query()
 * @method static Builder|Department whereBranchType($value)
 * @method static Builder|Department whereCode($value)
 * @method static Builder|Department whereContactEmail($value) // Assuming these exist in the migration
 * @method static Builder|Department whereContactPerson($value) // Assuming these exist in the migration
 * @method static Builder|Department whereContactPhone($value) // Assuming these exist in the migration
 * @method static Builder|Department whereCreatedAt($value)
 * @method static Builder|Department whereCreatedBy($value)
 * @method static Builder|Department whereDeletedAt($value)
 * @method static Builder|Department whereDeletedBy($value)
 * @method static Builder|Department whereDescription($value)
 * @method static Builder|Department whereHeadOfDepartmentId($value)
 * @method static Builder|Department whereId($value)
 * @method static Builder|Department whereIsActive($value)
 * @method static Builder|Department whereLocation($value) // Assuming this exists in the migration
 * @method static Builder|Department whereName($value)
 * @method static Builder|Department whereStartWorkHour($value) // Assuming these exist in the migration
 * @method static Builder|Department whereEndWorkHour($value) // Assuming these exist in the migration
 * @method static Builder|Department whereUpdatedAt($value)
 * @method static Builder|Department whereUpdatedBy($value)
 * @method static Builder|Department whereWeekends($value) // Assuming this exists in the migration
 * @method static Builder|Department withTrashed()
 * @method static Builder|Department withoutTrashed()
 * @method static Builder|Department active() // Scope defined below
 *
 * @mixin \Eloquent
 */
class Department extends Model
{
  use HasFactory, SoftDeletes, CreatedUpdatedDeletedBy;

  protected $fillable = [
    'name',
    'branch_type',
    'code',
    'description',
    'is_active',
    'head_of_department_id',
    // 'location', 'start_work_hour', 'end_work_hour', 'weekends' - add if these are in fillable and migration
  ];

  protected $casts = [
    'is_active' => 'boolean',
    'weekends' => 'json',
    'start_work_hour' => 'datetime',
    'end_work_hour' => 'datetime',
  ];

  /**
   * Get the timelines for the department.
   */
  public function timelines(): HasMany
  {
    return $this->hasMany(Timeline::class, 'department_id');
  }

  /**
   * Get the users for the department.
   */
  public function users(): HasMany
  {
    return $this->hasMany(User::class, 'department_id');
  }

  /**
   * Get the employees for the department.
   */
  public function employees(): HasMany
  {
    return $this->hasMany(Employee::class, 'department_id');
  }

  /**
   * Get the user who is the head of the department.
   */
  public function headOfDepartment(): BelongsTo
  {
    return $this->belongsTo(User::class, 'head_of_department_id');
  }

  /**
   * Get or set the department's name.
   * Applies ucfirst mutation on setting.
   */
  protected function name(): Attribute
  {
    return Attribute::make(
      set: fn(string $value) => ucfirst($value),
    );
  }

  /**
   * Scope to include active departments.
   */
  public function scopeActive(Builder $query): Builder
  {
    return $query->where('is_active', true);
  }
}
