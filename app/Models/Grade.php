<?php

namespace App\Models;

use App\Traits\CreatedUpdatedDeletedBy;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes; // Correct namespace for SoftDeletes trait

// Import models for relationships
use App\Models\User;
use App\Models\Employee;
use App\Models\Position; // Import Position model


/**
 * App\Models\Grade
 *
 * Represents an employee grade level (e.g., '41', '44', 'N19').
 * Stores grade name, numerical level, and indicates if the grade is designated as an approver grade.
 * Linked to User, Employee, and Position models via one-to-many relationships.
 * Includes a self-referencing relationship to track minimum approval grades.
 * Includes audit trails and soft deletion.
 *
 * @property int $id
 * @property string $name The name of the grade (e.g., 'Grade 41', 'PTD 48').
 * @property int $level The numerical level of the grade (e.g., 41, 48, 19) for sorting or comparison.
 * @property bool $is_approver_grade Indicates if this grade is designated as an approver grade (e.g., Grade 41+).
 * @property int|null $min_approval_grade_id Foreign key to the Grade model representing the minimum grade required to approve items for this grade level (self-referencing, nullable).
 * @property int|null $created_by Foreign key to the user who created the record.
 * @property int|null $updated_by Foreign key to the user who last updated the record.
 * @property int|null $deleted_by Foreign key to the user who soft deleted the record.
 * @property \Illuminate\Support\Carbon|null $created_at Automatically managed timestamp for creation.
 * @property \Illuminate\Support\Carbon|null $updated_at Automatically managed timestamp for last update.
 * @property \Illuminate\Support\Carbon|null $deleted_at Timestamp for soft deletion.
 *
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Employee> $employees Employees belonging to this grade.
 * @property-read int|null $employees_count Count of employees in this grade.
 * @property-read \App\Models\Grade|null $minApprovalGrade The minimum approval grade required for this grade level (self-referencing).
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Position> $positions Positions belonging to this grade.
 * @property-read int|null $positions_count Count of positions in this grade.
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $users Users belonging to this grade.
 * @property-read int|null $users_count Count of users in this grade.
 * @property-read \App\Models\User|null $createdBy The user who created the record (if trait adds this).
 * @property-read \App\Models\User|null $deletedBy The user who soft deleted the record (if trait adds this).
 * @property-read \App\Models\User|null $updatedBy The user who last updated the record (if trait adds this).
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Grade newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Grade newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Grade onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Grade query()
 * @method static \Illuminate\Database\Eloquent\Builder|Grade whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Grade whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Grade whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Grade whereDeletedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Grade whereIsApproverGrade($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Grade whereLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Grade whereMinApprovalGradeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Grade whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Grade whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Grade whereUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Grade withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Grade withoutTrashed()
 * @method static \Database\Factories\GradeFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Grade whereId($value)
 * @mixin \Eloquent
 */
class Grade extends Model
{
  // Use the traits for factory, soft deletes, and audit columns
  use CreatedUpdatedDeletedBy, HasFactory, SoftDeletes;

  /**
   * The table associated with the model.
   *
   * @var string
   */
  // protected $table = 'grades';


  /**
   * The attributes that are mass assignable.
   *
   * @var array<int, string>
   */
  protected $fillable = [
    'name',
    'level',
    'is_approver_grade',
    'min_approval_grade_id',
    'description',
  ];

  /**
   * The attributes that should be cast.
   *
   * @var array<string, string>
   */
  protected $casts = [
    'name' => 'string',
    'level' => 'integer',
    'is_approver_grade' => 'boolean',
    'min_approval_grade_id' => 'integer',
    'description' => 'string',
    'created_at' => 'datetime',
    'updated_at' => 'datetime',
    'deleted_at' => 'datetime',
  ];

  /**
   * The attributes that should be hidden for serialization.
   *
   * @var array<int, string>
   */
  // protected $hidden = [];


  // The CreatedUpdatedDeletedBy trait is assumed to add these audit relationships:

  // public function createdBy(): BelongsTo;
  // public function updatedBy(): BelongsTo;
  // public function deletedBy(): BelongsTo;


  // 👉 Relationships

  /**
   * Get the positions that belong to this grade.
   * Defines a one-to-many relationship where a Grade has many Positions.
   * Assumes the 'positions' table has a 'grade_id' foreign key.
   *
   * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\Position>
   */
  public function positions(): HasMany
  {
    return $this->hasMany(Position::class, 'grade_id');
    // If using the standard foreign key name, this is sufficient: return $this->hasMany(Position::class);
  }


  /**
   * Get the users that belong to this grade.
   * Defines a one-to-many relationship where a Grade has many Users.
   * Assumes the 'users' table has a 'grade_id' foreign key.
   *
   * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\User>
   */
  public function users(): HasMany
  {
    return $this->hasMany(User::class, 'grade_id');
    // If using the standard foreign key name, this is sufficient: return $this->hasMany(User::class);
  }

  /**
   * Get the employees that belong to this grade.
   * Defines a one-to-many relationship where a Grade has many Employees.
   * Assumes the 'employees' table has a 'grade_id' foreign key.
   *
   * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\Employee>
   */
  public function employees(): HasMany
  {
    return $this->hasMany(Employee::class, 'grade_id');
    // If using the standard foreign key name, this is sufficient: return $this->hasMany(Employee::class);
  }


  /**
   * Get the minimum approval grade required for this grade level.
   * Defines a many-to-one self-referencing relationship.
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Grade, \App\Models\Grade>
   */
  public function minApprovalGrade(): BelongsTo
  {
    return $this->belongsTo(Grade::class, 'min_approval_grade_id');
  }


  // 👉 Helper Methods

  /**
   * Check if this grade is designated as an approver grade.
   *
   * @return bool True if this grade can approve, false otherwise.
   */
  public function isApprover(): bool
  {
    return $this->is_approver_grade;
  }

  // Add custom methods or accessors/mutators here as needed
}
