<?php

namespace App\Models; // Ensure the namespace is correct for your project

use App\Traits\CreatedUpdatedDeletedBy; // Assuming this trait exists and adds audit FKs/methods
use Illuminate\Database\Eloquent\Factories\HasFactory; // Import HasFactory trait
use Illuminate\Database\Eloquent\Model; // Import base Model class
use Illuminate\Database\Eloquent\Relations\HasMany; // Import HasMany relationship type
use Illuminate\Database\Eloquent\SoftDeletes; // Import SoftDeletes trait
use Illuminate\Database\Eloquent\Relations\BelongsTo; // Import BelongsTo relationship type (for relationships potentially added by the trait)

// Import model for HasMany relationship
use App\Models\Employee; // Contract has many Employees


/**
 * App\Models\Contract
 *
 * Represents a type of employment contract or service appointment (e.g., 'Permanent', 'Contract', 'MySTEP').
 * Includes details like a work rate and notes.
 * Has a one-to-many relationship with Employee models.
 *
 * @property int $id
 * @property string $name The name of the contract type (e.g., 'Permanent', 'Contract', 'MySTEP').
 * @property float $work_rate The work rate associated with this contract type (e.g., an hourly or daily rate).
 * @property string|null $notes Any additional notes or description for the contract type.
 * @property int|null $created_by Foreign key to the user who created the record (handled by trait if applied here).
 * @property int|null $updated_by Foreign key to the user who last updated the record (handled by trait if applied here).
 * @property int|null $deleted_by Foreign key to the user who soft deleted the record (handled by trait if applied here).
 * @property \Illuminate\Support\Carbon|null $created_at Automatically managed timestamp for creation.
 * @property \Illuminate\Support\Carbon|null $updated_at Automatically managed timestamp for last update.
 * @property \Illuminate\Support\Carbon|null $deleted_at Timestamp for soft deletion.
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Employee> $employees The employees with this contract type.
 * @property-read int|null $employees_count Count of employees with this contract type.
 * @property-read \App\Models\User|null $createdBy Relation to the user who created the record (if trait adds this).
 * @property-read \App\Models\User|null $deletedBy Relation to the user who soft deleted the record (if trait adds this).
 * @property-read \App\Models\User|null $updatedBy Relation to the user who last updated the record (if trait adds this).
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Contract newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Contract newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Contract onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Contract query()
 * @method static \Illuminate\Database\Eloquent\Builder|Contract whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Contract whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Contract whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Contract whereDeletedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Contract whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Contract whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Contract whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Contract whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Contract whereUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Contract whereWorkRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Contract withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Contract withoutTrashed()
 * @mixin \Eloquent
 */
class Contract extends Model
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
  // protected $table = 'contracts'; // Explicitly define table name if it's not the plural of the model name


  /**
   * The attributes that are mass assignable.
   * Allows these attributes to be set using mass assignment (e.g., during create or update).
   * 'id' is excluded as it is the primary key.
   *
   * @var array<int, string>
   */
  protected $fillable = [
    'name',      // Name of the contract type
    'work_rate', // Work rate
    'notes',     // Additional notes (ensure this column exists in your migration and is nullable if applicable)

    // The CreatedUpdatedDeletedBy trait is assumed to handle these audit columns:
    // 'created_by',
    // 'updated_by',
    // 'deleted_by',
  ];

  /**
   * The attributes that should be cast.
   * Ensures data types are correct and dates are Carbon instances.
   * Use 'decimal' for precise numeric values like currency or rates.
   *
   * @var array<string, string>
   */
  protected $casts = [
    'name' => 'string',          // Explicitly cast name as string
    'work_rate' => 'decimal:2', // Cast work_rate as decimal with 2 places (adjust precision as needed) - Recommended for rates/currency
    'notes' => 'string',         // Explicitly cast notes as string
    'created_at' => 'datetime',  // Explicitly cast creation timestamp
    'updated_at' => 'datetime',  // Explicitly cast update timestamp
    'deleted_at' => 'datetime',  // Cast soft delete timestamp
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
  // ];


  // The CreatedUpdatedDeletedBy trait is assumed to add these audit relationships:
  // public function createdBy(): BelongsTo; // Relationship to the user who created the record
  // public function updatedBy(): BelongsTo; // Relationship to the user who last updated the record
  // public function deletedBy(): BelongsTo; // Relationship to the user who soft deleted the record


  // 👉 Relationships

  /**
   * Get the employees associated with the contract.
   * Defines a one-to-many relationship where a Contract has many Employees.
   * Assumes the 'employees' table has a 'contract_id' foreign key that
   * references the 'contracts' table's primary key ('id').
   *
   * @return \Illuminate\Database\Eloquent\Relations\HasMany
   */
  public function employees(): HasMany // Added return type hint
  {
    // Defines a one-to-many relationship with the Employee model.
    // 'Employee::class' is the related model.
    // 'contract_id' is the foreign key on the 'employees' table.
    // 'id' is the local key on the 'contracts' table (default, can be omitted).
    return $this->hasMany(Employee::class, 'contract_id');
    // If using the standard foreign key name, this is sufficient: return $this->hasMany(Employee::class);
  }

  // Add any other relationships, accessors, mutators, or methods below this line

  // Example: Accessor to format the work rate for display (optional)
  // use Illuminate\Database\Eloquent\Casts\Attribute; // Import Attribute if needed
  // protected function workRate(): Attribute
  // {
  //     return Attribute::make(
  //         get: fn (float $value) => number_format($value, 2), // Format to 2 decimal places
  //     );
  // }
}
