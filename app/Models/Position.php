<?php

namespace App\Models;

use App\Traits\CreatedUpdatedDeletedBy;
use Carbon\Carbon; // FIX: Import Carbon
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

// Import models for relationships
use App\Models\Grade;
use App\Models\Timeline;
use App\Models\User;
use App\Models\Employee;


/**
 * App\Models\Position
 *
 * Represents a job position or title within the organization.
 * Linked to Grades, Timelines, Users, and Employees.
 * Includes audit trails and soft deletion.
 *
 * @property int $id
 * @property string $name
 * @property int $vacancies_count Number of available slots for this position
 * @property int|null $grade_id Foreign key to the associated Grade
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $deleted_at For soft deletion
 * @property int|null $created_by Foreign key to the User who created the record
 * @property int|null $updated_by Foreign key to the User who last updated the record
 * @property int|null $deleted_by Foreign key to the User who soft-deleted the record
 *
 * @property-read Grade|null $grade
 * @property-read Collection<int, Timeline> $timelines
 * @property-read int|null $timelines_count
 * @property-read Collection<int, User> $users // Users assigned to this position
 * @property-read int|null $users_count
 * @property-read Collection<int, Employee> $employees // Employees holding this position (if Employee is separate)
 * @property-read int|null $employees_count
 *
 * @method static Builder|Position newModelQuery()
 * @method static Builder|Position newQuery()
 * @method static Builder|Position onlyTrashed()
 * @method static Builder|Position query()
 * @method static Builder|Position whereCreatedAt($value)
 * @method static Builder|Position whereCreatedBy($value)
 * @method static Builder|Position whereDeletedAt($value)
 * @method static Builder|Position whereDeletedBy($value)
 * @method static Builder|Position whereGradeId($value)
 * @method static Builder|Position whereId($value)
 * @method static Builder|Position whereName($value)
 * @method static Builder|Position whereUpdatedAt($value)
 * @method static Builder|Position whereUpdatedBy($value)
 * @method static Builder|Position whereVacanciesCount($value)
 * @method static Builder|Position withTrashed()
 * @method static Builder|Position withoutTrashed()
 * @method static Builder|Position active() // Scope defined below
 *
 * @mixin \Eloquent
 */
class Position extends Model
{
  use HasFactory, SoftDeletes, CreatedUpdatedDeletedBy;

  protected $fillable = [
    'name',
    'vacancies_count',
    'grade_id',
  ];

  protected $casts = [
    'vacancies_count' => 'integer',
    // Add casts for date/time columns if they exist and are not handled by timestamps/softDeletes
    // 'start_time' => 'datetime',
    // 'end_time' => 'datetime',
  ];

  // Relationships

  /**
   * Get the grade that the position belongs to.
   */
  public function grade(): BelongsTo
  {
    return $this->belongsTo(Grade::class, 'grade_id');
  }

  /**
   * Get the timelines associated with the position.
   */
  public function timelines(): HasMany
  {
    return $this->hasMany(Timeline::class, 'position_id');
  }

  /**
   * Get the users who hold this position.
   * Assumes a 'position_id' foreign key on the 'users' table.
   */
  public function users(): HasMany
  {
    return $this->hasMany(User::class, 'position_id');
  }

  /**
   * Get the employees who hold this position.
   * Assumes a 'position_id' foreign key on the 'employees' table if Employee is separate from User.
   */
  public function employees(): HasMany
  {
    return $this->hasMany(Employee::class, 'position_id');
  }


  // Attributes (Accessors/Mutators)

  /**
   * Get or set the position's name.
   * Applies ucfirst mutation on setting.
   */
  protected function name(): Attribute
  {
    return Attribute::make(
      set: fn(string $value) => ucfirst($value),
    );
  }

  // Scopes

  /**
   * Scope to include active positions.
   * Note: Position model doesn't seem to have an 'is_active' column in the fillable/docblock,
   * but the scope is defined here. Ensure the column exists in the migration.
   */
  public function scopeActive(Builder $query): Builder
  {
    // Assuming an 'is_active' column exists in the 'positions' table
    return $query->where('is_active', true);
  }

  // Add any other custom methods or accessors/mutators here.
}
