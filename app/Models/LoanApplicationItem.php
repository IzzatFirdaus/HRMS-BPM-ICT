<?php

namespace App\Models; // Ensure the namespace is correct for your project

// Assuming this trait exists and adds audit FKs/methods like created_by, updated_by, deleted_by
use App\Traits\CreatedUpdatedDeletedBy;
// Assuming Carbon is used implicitly with date casts (including explicitly importing here is good practice)
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory; // Import HasFactory trait
use Illuminate\Database\Eloquent\Model; // Import base Model class
use Illuminate\Database\Eloquent\Relations\BelongsTo; // Import BelongsTo relationship type
use Illuminate\Database\Eloquent\SoftDeletes; // Import SoftDeletes trait
// Removed unused import: use Illuminate\Database\Eloquent\Casts\Attribute; // Not used for older style accessors
// Removed unused aliased import: BelongsToRelation


// Import models for relationships (Eloquent needs to know about the related models for relationships)
use App\Models\LoanApplication; // LoanApplicationItem belongsTo LoanApplication
use App\Models\Equipment; // LoanApplicationItem belongsTo Equipment
use App\Models\User; // For audit columns (handled by trait CreatedUpdatedDeletedBy)
use App\Models\LoanTransaction; // For deriving issued quantity in accessor


/**
 * App\Models\LoanApplicationItem
 *
 * Represents a single equipment item requested within a loan application.
 * Linked to a specific parent LoanApplication and a specific Equipment asset.
 * Stores the quantity requested, quantity approved, and any specific notes for this item.
 * Includes audit trails and soft deletion.
 *
 * @property int $id
 * @property int $loan_application_id The loan application this item belongs to (Foreign key to 'loan_applications' table).
 * @property int $equipment_id Foreign key to the specific Equipment asset requested ('equipment' table).
 * @property int $quantity_requested Kuantiti requested by the applicant (Integer).
 * @property int|null $quantity_approved Quantity approved by the approver(s) (Integer), can be null if not yet approved.
 * @property string|null $notes Catatan or additional notes for this specific item (Text or String, nullable).
 * @property int|null $created_by Foreign key to the user who created the record.
 * @property int|null $updated_by Foreign key to the user who last updated the record.
 * @property int|null $deleted_by Foreign key to the user who soft deleted the record.
 * @property \Illuminate\Support\Carbon|null $created_at Automatically managed timestamp for creation.
 * @property \Illuminate\Support\Carbon|null $updated_at Automatically managed timestamp for last update.
 * @property \Illuminate\Support\Carbon|null $deleted_at Timestamp for soft deletion.
 *
 * @property-read \App\Models\Equipment $equipment The specific equipment asset model requested for this item.
 * @property-read \App\Models\LoanApplication $loanApplication The parent loan application model that the item belongs to.
 * @property-read int $issuedQuantity The total quantity of this specific equipment item issued for the parent application.
 * @property-read \App\Models\User|null $createdBy The user who created the record (if trait adds this).
 * @property-read \App\Models\User|null $deletedBy The user who soft deleted the record (if trait adds this).
 * @property-read \App\Models\User|null $updatedBy The user who last updated the record (if trait adds this).
 *
 * @method static \Illuminate\Database\Eloquent\Builder|LoanApplicationItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LoanApplicationItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LoanApplicationItem onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|LoanApplicationItem query()
 * @method static \Illuminate\Database\Eloquent\Builder|LoanApplicationItem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LoanApplicationItem whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LoanApplicationItem whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LoanApplicationItem whereDeletedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LoanApplicationItem whereEquipmentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LoanApplicationItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LoanApplicationItem whereLoanApplicationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LoanApplicationItem whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LoanApplicationItem whereQuantityApproved($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LoanApplicationItem whereQuantityRequested($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LoanApplicationItem whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LoanApplicationItem whereUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LoanApplicationItem withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|LoanApplicationItem withoutTrashed()
 * @mixin \Eloquent
 */
class LoanApplicationItem extends Model
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
  // protected $table = 'loan_application_items'; // Explicitly define table name if it's not the plural of the model name


  /**
   * The attributes that are mass assignable.
   * Allows these attributes to be set using mass assignment (e.g., during create or update).
   * Includes fields linking to a specific equipment and details about the request.
   * 'id' is excluded as it is the primary key.
   *
   * @var array<int, string>
   */
  protected $fillable = [
    'loan_application_id', // The loan application this item belongs to (Foreign key)
    'equipment_id',        // Foreign key to specific Equipment asset

    'quantity_requested',  // Kuantiti requested (Integer)
    'quantity_approved',   // Quantity approved by approver(s) (Integer, nullable)
    'notes',               // Catatan for this specific item (Text/String, nullable)

    // 'created_by', // Handled by trait
    // 'updated_by', // Handled by trait
    // 'deleted_by', // Handled by trait
  ];

  /**
   * The attributes that should be cast.
   * Ensures data types are correct and dates are Carbon instances.
   * Includes casts for FKs, integers, strings, timestamps, and soft deletes.
   * Explicitly casting all attributes for clarity and type safety.
   *
   * @var array<string, string>
   */
  protected $casts = [
    'loan_application_id' => 'integer', // Cast FKs to integer
    'equipment_id'        => 'integer',

    'quantity_requested'  => 'integer', // Cast integer quantities
    'quantity_approved'   => 'integer',

    'notes'               => 'string', // Explicitly cast notes as string

    // Standard Eloquent timestamps
    'created_at'          => 'datetime', // Explicitly cast creation timestamp to Carbon instance
    'updated_at'          => 'datetime', // Explicitly cast update timestamp to Carbon instance
    'deleted_at'          => 'datetime', // Cast soft delete timestamp to Carbon instance
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

  /**
   * Get the user who created the model.
   * Assumes the 'created_by' foreign key exists and links to the 'users' table.
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\User, \App\Models\LoanApplicationItem>
   */
  // public function createdBy(): BelongsTo;

  /**
   * Get the user who last updated the model.
   * Assumes the 'updated_by' foreign key exists and links to the 'users' table.
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\User, \App\Models\LoanApplicationItem>
   */
  // public function updatedBy(): BelongsTo;

  /**
   * Get the user who soft deleted the model.
   * Assumes the 'deleted_by' foreign key exists and links to the 'users' table.
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\User, \App\Models\LoanApplicationItem>
   */
  // public function deletedBy(): BelongsTo;


  // 👉 Relationships

  /**
   * Get the loan application that the item belongs to.
   * Defines a many-to-one relationship where a LoanApplicationItem belongs to one LoanApplication.
   * Assumes the 'loan_application_items' table has a 'loan_application_id' foreign key that
   * references the 'loan_applications' table's primary key ('id').
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\LoanApplication, \App\Models\LoanApplicationItem>
   */
  public function loanApplication(): BelongsTo // Added return type hint
  {
    // Defines a many-to-one relationship with the LoanApplication model.
    // 'LoanApplication::class' is the related model.
    // 'loan_application_id' is the foreign key on the 'loan_application_items' table.
    // 'id' is the local key on the 'loan_applications' table (default, can be omitted).
    return $this->belongsTo(LoanApplication::class, 'loan_application_id');
    // If using the standard foreign key name, this is sufficient: return $this->belongsTo(LoanApplication::class);
  }

  /**
   * Get the specific equipment asset requested for this item.
   * Defines a many-to-one relationship where a LoanApplicationItem belongs to one Equipment asset.
   * This links the requested item line to the actual equipment record.
   * Assumes the 'loan_application_items' table has an 'equipment_id' foreign key that
   * references the 'equipment' table's primary key ('id').
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Equipment, \App\Models\LoanApplicationItem>
   */
  public function equipment(): BelongsTo // Added return type hint
  {
    // Defines a many-to-one relationship with the Equipment model.
    // 'Equipment::class' is the related model.
    // 'equipment_id' is the foreign key on the 'loan_application_items' table.
    // 'id' is the local key on the 'equipment' table (default, can be omitted).
    return $this->belongsTo(Equipment::class, 'equipment_id');
    // If using the standard foreign key name, this is sufficient: return $this->belongsTo(Equipment::class);
  }


  // 👉 Accessors

  /**
   * Accessor to get the total quantity of this specific equipment item issued for the parent application.
   * Derived by counting related 'issued' LoanTransaction records linked to the parent LoanApplication
   * that involve the specific equipment asset linked to this item.
   * Note: Assumes each 'issued' LoanTransaction record for this equipment represents one unit.
   * Requires the parent LoanApplication and its transactions to be loaded.
   *
   * @return int The total quantity issued for this item in the parent application.
   */
  protected function getIssuedQuantityAttribute(): int // Corrected method visibility to protected for accessors, Added return type hint
  {
    // Check if the item is linked to specific equipment and the parent application exists.
    // Accessing $this->loanApplication will load the relationship if not already loaded.
    // Accessing $this->loanApplication->transactions() will load the relationship if not already loaded.
    if ($this->equipment_id !== null && $this->loanApplication !== null) {
      // Count transactions for the parent loan application
      // that are marked with the 'issued' status constant (or string literal)
      // AND involve the specific equipment linked to this item's equipment_id.
      // Assuming LoanTransaction model has a STATUS_ISSUED constant, otherwise use the string literal 'issued'.
      $issuedStatus = defined('App\\Models\\LoanTransaction::STATUS_ISSUED') ? LoanTransaction::STATUS_ISSUED : 'issued';

      return $this->loanApplication->transactions()
        ->where('status', $issuedStatus) // Filter transactions by 'issued' status
        ->where('equipment_id', $this->equipment_id) // Filter by the specific equipment ID on this item
        ->count(); // Count the matching transactions
    }

    return 0; // Return 0 if item not linked to equipment or no parent application
  }


  // Add any other custom methods or accessors/mutators below this line
}
