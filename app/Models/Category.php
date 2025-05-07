<?php

namespace App\Models; // Ensure the namespace is correct for your project

use App\Traits\CreatedUpdatedDeletedBy; // Assuming this trait exists and adds audit FKs/methods
use Illuminate\Database\Eloquent\Factories\HasFactory; // Import HasFactory trait
use Illuminate\Database\Eloquent\Model; // Import base Model class
use Illuminate\Database\Eloquent\Relations\HasMany; // Import HasMany relationship type
use Illuminate\Database\Eloquent\SoftDeletes; // Import SoftDeletes trait

// Import model for HasMany relationship
use App\Models\SubCategory; // Category has many SubCategories
// Import other models if Category directly relates to them (e.g., Equipment)
// use App\Models\Equipment;


/**
 *
 *
 * @property int $id
 * @property string $name
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property int|null $deleted_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, SubCategory> $subCategories
 * @property-read int|null $sub_categories_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereDeletedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category withoutTrashed()
 * @mixin \Eloquent
 */
class Category extends Model
{
  // Use the traits for factory, soft deletes, and audit columns
  // SoftDeletes adds the 'deleted_at' column and scope.
  // CreatedUpdatedDeletedBy is assumed to add 'created_by', 'updated_by', 'deleted_by' columns and relationships.
  use CreatedUpdatedDeletedBy, HasFactory, SoftDeletes;

  /**
   * The table associated with the model.
   *
   * @var string
   */
  // protected $table = 'categories'; // Explicitly define table name if it's not the plural of the model name


  /**
   * The attributes that are mass assignable.
   * Allows these attributes to be set using mass assignment (e.g., during create or update).
   *
   * @var array<int, string>
   */
  protected $fillable = [
    // 'id' is the primary key and typically not included in fillable
    'name', // The name of the category (e.g., 'Komputer Riba', 'Pencetak')
    // Add other fillable attributes here if necessary (e.g., 'description', 'is_active')

    // The CreatedUpdatedDeletedBy trait is assumed to handle these audit columns:
    // 'created_by',
    // 'updated_by',
    // 'deleted_by',
  ];

  /**
   * The attributes that should be cast.
   * Ensures date/datetime attributes are Carbon instances.
   *
   * @var array<string, string>
   */
  protected $casts = [
    'created_at' => 'datetime', // Explicitly cast creation timestamp
    'updated_at' => 'datetime', // Explicitly cast update timestamp
    'deleted_at' => 'datetime', // Cast soft delete timestamp
    // Add casts for other attributes if needed (e.g., 'is_active' => 'boolean')
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
   * Get the subcategories associated with the category.
   * This defines a one-to-many relationship where a Category has many SubCategories.
   * Assumes the 'sub_categories' table has a 'category_id' foreign key that
   * references the 'categories' table's primary key ('id').
   *
   * @return \Illuminate\Database\Eloquent\Relations\HasMany
   */
  public function subCategories(): HasMany // Added return type hint
  {
    // Defines a one-to-many relationship with the SubCategory model.
    // 'SubCategory::class' is the related model.
    // 'category_id' is the foreign key on the 'sub_categories' table.
    // 'id' is the local key on the 'categories' table (default, can be omitted).
    return $this->hasMany(SubCategory::class, 'category_id');
  }

  // Optional: If Equipment can also directly belong to a Category (less likely if SubCategory is always used)
  // public function equipment(): HasMany
  // {
  //     // Assumes the 'equipment' table has a 'category_id' foreign key
  //     return $this->hasMany(Equipment::class, 'category_id');
  // }


  // 👉 Accessors and Mutators (using Attributes since PHP 8.1 / Laravel 9+)

  // Example: Mutator for name to ensure consistent casing (optional)
  // Use Attributes for modern Laravel mutators/accessors.
  // Ensure you import: use Illuminate\Database\Eloquent\Casts\Attribute;
  // protected function name(): Attribute
  // {
  //     return Attribute::make(
  //         get: fn (string $value) => $value, // Optional: define custom retrieval logic
  //         set: fn (string $value) => ucwords($value), // Example: Capitalize first letter of each word when saving
  //     );
  //     // Be cautious with using mutators like ucwords() if you have a unique constraint
  //     // on the 'name' column and need case-sensitive uniqueness in the database.
  //     // Database-level constraints might interact differently with mutated values.
  // }


  // Add other custom methods or accessors/mutators below this line
}
