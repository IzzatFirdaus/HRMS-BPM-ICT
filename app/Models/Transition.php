<?php

namespace App\Models; // Ensure the namespace is correct for your project

use App\Traits\CreatedUpdatedDeletedBy; // Assuming this trait exists and adds audit FKs/methods like created_by, updated_by, deleted_by
use Illuminate\Database\Eloquent\Factories\HasFactory; // Import HasFactory trait
use Illuminate\Database\Eloquent\Model; // Import base Model class
use Illuminate\Database\Eloquent\Relations\BelongsTo; // Import BelongsTo relationship type
use Illuminate\Database\Eloquent\SoftDeletes; // Import SoftDeletes trait
use Illuminate\Support\Carbon; // Import Carbon for type hinting with date/datetime casts
// Removed unused aliased import: BelongsToRelation

// Import models for BelongsTo relationships (Eloquent needs to know about the related models)
use App\Models\Employee; // Transition belongsTo Employee
use App\Models\Equipment; // Transition belongsTo Equipment (replaces Asset)
use App\Models\User; // For audit columns (handled by trait CreatedUpdatedDeletedBy)


/**
 * App\Models\Transition
 *
 * Represents a record of equipment being handed out to or returned by an employee.
 * Tracks the equipment item involved, the employee associated with the transition,
 * the dates of handing out and return, document numbers, reason, and notes.
 * This model is designed to replace the functionality related to older asset transitions,
 * linking directly to the modern Equipment model.
 * Includes audit trails and soft deletion.
 *
 * @property int $id
 * @property int $equipment_id Foreign key to the equipment asset involved in the transition ('equipment' table).
 * @property int $employee_id Foreign key to the employee associated with the transition ('employees' table).
 * @property \Illuminate\Support\Carbon $handed_date Date when the equipment was handed out to the employee.
 * @property \Illuminate\Support\Carbon|null $return_date Date when the equipment was returned by the employee (null if not yet returned).
 * @property string|null $center_document_number Document number from the center/department related to the transition (nullable).
 * @property string|null $reason The reason for the transition (nullable string).
 * @property string|null $note Additional notes about the transition (nullable text field).
 * @property int|null $created_by Foreign key to the user who created the record.
 * @property int|null $updated_by Foreign key to the user who last updated the record.
 * @property int|null $deleted_by Foreign key to the user who soft deleted the record.
 * @property \Illuminate\Support\Carbon|null $created_at Automatically managed timestamp for creation.
 * @property \Illuminate\Support\Carbon|null $updated_at Automatically managed timestamp for last update.
 * @property \Illuminate\Support\Carbon|null $deleted_at The timestamp when the transition record was soft deleted.
 *
 * @property-read \App\Models\Employee $employee The employee model associated with this transition record.
 * @property-read \App\Models\Equipment $equipment The equipment asset model involved in the transition record.
 * @property-read \App\Models\User|null $createdBy The user who created the record (if trait adds this).
 * @property-read \App\Models\User|null $deletedBy The user who soft deleted the record (if trait adds this).
 * @property-read \App\Models\User|null $updatedBy The user who last updated the record (if trait adds this).
 *
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
 * @method static \Illuminate\Database\Eloquent\Builder|Transition whereUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transition withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Transition withoutTrashed()
 * @mixin \Eloquent
 */
class Transition extends Model
{
  // Use the traits for factory, soft deletes, and audit columns
  // HasFactory is for model factories.
  // SoftDeletes adds the 'deleted_at' column and scope.
  // CreatedUpdatedDeletedBy is assumed to add 'created_by', 'updated_by', 'deleted_by' columns and relationships.
  use CreatedUpdatedDeletedBy, HasFactory, SoftDeletes;

  /**
   * The table associated with the model.
   *
   * @var string
   */
  // protected $table = 'transitions'; // Explicitly define table name if it's not the plural of the model name


  /**
   * The attributes that are mass assignable.
   * Allows these attributes to be set using mass assignment (e.g., during create or update).
   * Includes fields from the migration, linking to a specific equipment and employee.
   * 'id' is excluded as it is the primary key.
   *
   * @var array<int, string>
   */
  protected $fillable = [
    'equipment_id',           // Foreign key to the equipment asset involved in the transition
    'employee_id',            // Foreign key to the employee

    'handed_date',            // Date field for when equipment was handed (required)
    'return_date',            // Nullable date field for when equipment was returned

    'center_document_number', // Nullable string for document number
    'reason',                 // Nullable string for reason
    'note',                   // Nullable text field for notes

    // 'created_by', // Handled by trait
    // 'updated_by', // Handled by trait
    // 'deleted_by', // Handled by trait
  ];

  /**
   * The attributes that should be cast.
   * Ensures data types are correct and dates are Carbon instances.
   * Includes casts for FKs, dates, strings, timestamps, and soft deletes.
   * Explicitly casting all attributes for clarity and type safety.
   *
   * @var array<string, string>
   */
  protected $casts = [
    'equipment_id'           => 'integer', // Cast FKs to integer
    'employee_id'            => 'integer',

    'handed_date'            => 'date',    // Cast date fields to Carbon instances (YYYY-MM-DD)
    'return_date'            => 'date',    // Cast nullable date field to Carbon instances

    'center_document_number' => 'string', // Explicitly cast string attributes
    'reason'                 => 'string',
    'note'                   => 'string',

    // Standard Eloquent timestamps handled by base model and traits
    'created_at'             => 'datetime', // Explicitly cast creation timestamp to Carbon instance
    'updated_at'             => 'datetime', // Explicitly cast update timestamp to Carbon instance
    'deleted_at'             => 'datetime', // Cast soft delete timestamp to Carbon instance
    // Add casts for other attributes if needed
  ];

  /**
   * The attributes that should be hidden for serialization.
   * (Optional) Prevents sensitive attributes from being returned in JSON responses.
   *
   * @var array<int, string>
   */
  // protected $hidden = [
  //     'created_by', // Example: hide audit columns from API responses
  //     'updated_by',
  //     'deleted_by',
  //     'deleted_at', // Hide soft delete timestamp
  // ];


  // The CreatedUpdatedDeletedBy trait is assumed to add these audit relationships.
  // Their docblocks are included in the main class docblock above for clarity.
  // Example docblocks added by the trait:
  /*
        * Get the user who created the model.
        * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\User, \App\Models\Transition>
        */
  // public function createdBy(): BelongsTo;

  /*
        * Get the user who last updated the model.
        * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\User, \App\Models\Transition>
        */
  // public function updatedBy(): BelongsTo;

  /*
        * Get the user who soft deleted the model.
        * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\User, \App\Models\Transition>
        */
  // public function deletedBy(): BelongsTo;


  // 👉 Relationships

  /**
   * Get the employee associated with this transition record.
   * Defines a many-to-one relationship where a Transition record belongs to one Employee.
   * This links the transition event to the employee involved.
   * Assumes the 'transitions' table has an 'employee_id' foreign key that
   * references the 'employees' table's primary key ('id').
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Employee, \App\Models\Transition>
   */
  public function employee(): BelongsTo // Added return type hint
  {
    // Defines a many-to-one relationship with the Employee model.
    // 'Employee::class' is the related model.
    // 'employee_id' is the foreign key on the 'transitions' table.
    // 'id' is the local key on the 'employees' table (default, can be omitted).
    return $this->belongsTo(Employee::class, 'employee_id'); // Explicitly define FK for clarity
    // If using the standard foreign key name, this is sufficient: return $this->belongsTo(Employee::class);
  }

  /**
   * Get the equipment asset involved in the transition record.
   * Defines a many-to-one relationship where a Transition record belongs to one Equipment asset.
   * This links the transition event to the specific equipment item.
   * Assumes the 'transitions' table has an 'equipment_id' foreign key (which replaces an older 'asset_id') that
   * references the 'equipment' table's primary key ('id').
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Equipment, \App\Models\Transition>
   */
  public function equipment(): BelongsTo // Added return type hint
  {
    // Defines a many-to-one relationship with the Equipment model.
    // 'Equipment::class' is the related model.
    // 'equipment_id' is the foreign key on the 'transitions' table.
    // 'id' is the local key on the 'equipment' table (default, can be omitted).
    return $this->belongsTo(Equipment::class, 'equipment_id'); // Explicitly define FK for clarity (replacing asset_id)
    // If using the standard foreign key name, this is sufficient: return $this->belongsTo(Equipment::class);
  }


  // 👉 Helper Methods (Transition Status)

  /**
   * Check if the equipment has been returned in this transition record.
   * Checks if the 'return_date' is not null.
   *
   * @return bool True if the equipment has been returned, false otherwise.
   */
  public function isReturned(): bool // Added helper method
  {
    // Equipment is returned if the return_date is set.
    return $this->return_date !== null;
  }

  /**
   * Check if the equipment is currently outstanding in this transition record.
   * Checks if the 'return_date' is null.
   *
   * @return bool True if the equipment is still outstanding, false otherwise.
   */
  public function isOutstanding(): bool // Added helper method
  {
    // Equipment is outstanding if the return_date is null.
    return $this->return_date === null;
  }

  // The getCategory and getSubCategory functions were based on string manipulation of an old asset ID format
  // and directly queried the database. This logic is outdated and should be removed.
  // If you need Category or SubCategory information, access it via the related Equipment model:
  // $transition->equipment->category (assuming Equipment model has a 'category' relationship or accessor)
  // $transition->equipment->subCategory (assuming Equipment model has a 'subCategory' relationship or accessor)


  // Add custom methods or accessors/mutators here as needed
}
