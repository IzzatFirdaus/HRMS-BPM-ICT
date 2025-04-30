<?php

namespace App\Models;

use App\Traits\CreatedUpdatedDeletedBy; // Assuming this trait exists and adds audit FKs/methods
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo; // Use BelongsTo trait
use Illuminate\Database\Eloquent\SoftDeletes; // Use SoftDeletes trait
use Illuminate\Support\Carbon; // Import Carbon for type hinting with date/datetime casts
use Illuminate\Database\Eloquent\Relations\BelongsTo as BelongsToRelation; // Alias if needed

// Import models for BelongsTo relationships
use App\Models\Employee;
use App\Models\Equipment; // Equipment model (replaces Asset)
use App\Models\User; // For audit columns (handled by trait)


/**
 * App\Models\Transition
 * 
 * Represents a record of equipment being handed out to or returned by an employee.
 * Tracks the equipment, the employee, dates, document numbers, and notes.
 * This model replaces the functionality related to asset transitions.
 *
 * @property int $id
 * @property int $equipment_id Foreign key to the equipment asset involved in the transition.
 * @property int $employee_id Foreign key to the employee associated with the transition.
 * @property \Illuminate\Support\Carbon $handed_date Date when the equipment was handed out.
 * @property \Illuminate\Support\Carbon|null $return_date Date when the equipment was returned (null if not yet returned).
 * @property string|null $center_document_number Document number from the center/department (nullable).
 * @property string|null $reason The reason for the transition (nullable string).
 * @property string|null $note Additional notes about the transition (nullable text field).
 * @property int|null $created_by Foreign key to the user who created the record.
 * @property int|null $updated_by Foreign key to the user who last updated the record.
 * @property int|null $deleted_by Foreign key to the user who soft deleted the record.
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at The timestamp when the transition record was soft deleted.
 * @property-read \App\Models\Employee $employee The employee associated with this transition record.
 * @property-read \App\Models\Equipment $equipment The equipment asset involved in the transition record.
 * @property-read \App\Models\User|null $createdBy The user who created the record.
 * @property-read \App\Models\User|null $deletedBy The user who soft deleted the record.
 * @property-read \App\Models\User|null $updatedBy The user who last updated the record.
 * @method static \Illuminate\Database\Eloquent\Builder|Transition newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Transition newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Transition onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Transition query()
 * @method static \Illuminate\Database\Eloquent\Builder|Transition whereCenterDocumentNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transition whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transition whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transition whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transition whereDeletedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transition whereEmployeeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transition whereEquipmentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transition whereHandedDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transition whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transition whereNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transition whereReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transition whereReturnDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transition whereUpdatedAt($value)
 * @method static \Illuminate\Eloquent\Builder|Transition whereUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transition withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Transition withoutTrashed()
 * @method static \Database\Factories\TransitionFactory factory($count = null, $state = [])
 * @mixin \Eloquent
 */
class Transition extends Model
{
  // Use the traits for factory, soft deletes, and audit columns
  use CreatedUpdatedDeletedBy, HasFactory, SoftDeletes;

  /**
   * The attributes that are mass assignable.
   * Includes fields from the migration, using 'equipment_id' instead of 'asset_id'.
   * 'id' is excluded as it is the primary key.
   *
   * @var array<int, string>
   */
  protected $fillable = [
    'equipment_id', // Foreign key to the equipment asset involved in the transition
    'employee_id', // Foreign key to the employee

    'handed_date', // Date field for when equipment was handed
    'return_date', // Nullable date field for when equipment was returned
    'center_document_number', // Nullable string for document number
    'reason', // Nullable string for reason
    'note', // Nullable text field for notes

    // 'created_by', // Handled by trait
    // 'updated_by', // Handled by trait
    // 'deleted_by', // Handled by trait
  ];

  /**
   * The attributes that should be cast.
   * Ensures data types are correct and dates are Carbon instances.
   * Includes casts for FKs, dates, strings, timestamps, and soft deletes.
   * Explicitly casting all attributes for clarity.
   *
   * @var array<string, string>
   */
  protected $casts = [
    'equipment_id' => 'integer', // Cast FKs to integer
    'employee_id' => 'integer',

    'handed_date' => 'date', // Cast date fields to Carbon instances (YYYY-MM-DD)
    'return_date' => 'date', // Cast nullable date field

    'center_document_number' => 'string', // Explicitly cast string attributes
    'reason' => 'string',
    'note' => 'string',

    'created_at' => 'datetime', // Explicitly cast timestamps
    'updated_at' => 'datetime',
    'deleted_at' => 'datetime', // Cast soft delete timestamp
  ];


  // The CreatedUpdatedDeletedBy trait is assumed to add these audit relationships:

  /**
   * Get the user who created the model.
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\User, \App\Models\Transition>
   */
  // public function createdBy(): BelongsToRelation;

  /**
   * Get the user who last updated the model.
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\User, \App\Models\Transition>
   */
  // public function updatedBy(): BelongsToRelation;

  /**
   * Get the user who soft deleted the model.
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\User, \App\Models\Transition>
   */
  // public function deletedBy(): BelongsToRelation;


  // 👉 Relationships

  /**
   * Get the employee associated with this transition record.
   * Defines a many-to-one relationship where a Transition record belongs to one Employee.
   * Assumes the 'transitions' table has an 'employee_id' foreign key.
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Employee, \App\Models\Transition>
   */
  public function employee(): BelongsTo // Added return type hint
  {
    // Defines a many-to-one relationship with the Employee model
    return $this->belongsTo(Employee::class, 'employee_id'); // Explicitly define FK for clarity
  }

  /**
   * Get the equipment asset involved in the transition record.
   * Defines a many-to-one relationship where a Transition record belongs to one Equipment asset.
   * Assumes the 'transitions' table has an 'equipment_id' foreign key (replacing asset_id).
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Equipment, \App\Models\Transition>
   */
  public function equipment(): BelongsTo // Added return type hint
  {
    // Defines a many-to-one relationship with the Equipment model
    return $this->belongsTo(Equipment::class, 'equipment_id'); // Explicitly define FK for clarity
  }


  // 👉 Functions (Removed outdated functions)

  // The getCategory and getSubCategory functions were based on string manipulation of an old asset ID format
  // and directly queried the database. This logic is outdated and should be removed.
  // If you need Category or SubCategory information, access it via the related Equipment model:
  // $transition->equipment->category (assuming Equipment model has a 'category' relationship)
  // $transition->equipment->subCategory (assuming Equipment model has a 'subCategory' relationship)


  // Add custom methods or accessors/mutators here as needed
}
