<?php

namespace App\Models; // Ensure the namespace is correct for your project

use App\Traits\CreatedUpdatedDeletedBy; // Assuming this trait exists and adds audit FKs/methods
use Illuminate\Database\Eloquent\Casts\Attribute; // Import Attribute for mutator example (commented out)
use Illuminate\Database\Eloquent\Factories\HasFactory; // Import HasFactory trait
use Illuminate\Database\Eloquent\Model; // Import base Model class
use Illuminate\Database\Eloquent\Relations\BelongsTo; // Import BelongsTo relationship type
use Illuminate\Database\Eloquent\SoftDeletes; // Import SoftDeletes trait
use Illuminate\Support\Carbon; // Import Carbon for type hinting with timestamps and date/datetime casts
// Removed unused import: use Illuminate\Database\Eloquent\Collection;
// Removed unused aliased imports: BelongsToRelation, HasManyRelation

// Import model for BelongsTo relationship (Eloquent needs to know about the related model)
use App\Models\Category; // SubCategory belongs to a Category
use App\Models\User; // For audit columns (handled by trait CreatedUpdatedDeletedBy)


/**
 * App\Models\SubCategory
 *
 * Represents a subcategory within a hierarchical category structure.
 * Stores the subcategory name and links to a parent Category.
 * May include a description if that column exists in the database table.
 * Includes audit trails and soft deletion.
 *
 * @property int $id
 * @property int $category_id Foreign key to the parent category ('categories' table).
 * @property string $name The name of the subcategory.
 * @property string|null $description A description of the subcategory (if column exists, nullable).
 * @property int|null $created_by Foreign key to the user who created the record.
 * @property int|null $updated_by Foreign key to the user who last updated the record.
 * @property int|null $deleted_by Foreign key to the user who soft deleted the record.
 * @property \Illuminate\Support\Carbon|null $created_at Automatically managed timestamp for creation.
 * @property \Illuminate\Support\Carbon|null $updated_at Automatically managed timestamp for last update.
 * @property \Illuminate\Support\Carbon|null $deleted_at The timestamp when the subcategory was soft deleted.
 *
 * @property-read \App\Models\Category $category The parent category model that the subcategory belongs to.
 * @property-read \App\Models\User|null $createdBy The user who created the record (if trait adds this).
 * @property-read \App\Models\User|null $deletedBy The user who soft deleted the record (if trait adds this).
 * @property-read \App\Models\User|null $updatedBy The user who last updated the record (if trait adds this).
 *
 * @method static \Illuminate\Database\Eloquent\Builder|SubCategory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SubCategory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SubCategory onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|SubCategory query()
 * @method static \Illuminate\Database\Eloquent\Builder|SubCategory whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SubCategory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SubCategory whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SubCategory whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SubCategory whereDeletedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SubCategory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SubCategory whereName($value)
 * // Added whereDescription if column exists and is queryable
 * @method static \Illuminate\Database\Eloquent\Builder|SubCategory whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SubCategory whereUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SubCategory withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|SubCategory withoutTrashed()
 * @mixin \Eloquent
 */
class SubCategory extends Model
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
  // protected $table = 'sub_categories'; // Explicitly define table name if it's not the plural of the model name ('sub_categories')


  /**
   * The attributes that are mass assignable.
   * Allows these attributes to be set using mass assignment (e.g., during create or update).
   * Includes fields linking to the parent category and the subcategory name.
   * 'id' is excluded as it is the primary key.
   *
   * @var array<int, string>
   */
  protected $fillable = [
    'category_id', // Foreign key to the parent categories table (required)
    'name',        // The name of the subcategory (required string)

    // If your 'sub_categories' table has a 'description' column, uncomment the line below to make it mass assignable:
    // 'description',

    // The CreatedUpdatedDeletedBy trait is assumed to handle these audit columns:
    // 'created_by',
    // 'updated_by',
    // 'deleted_by',
  ];

  /**
   * The attributes that should be cast.
   * Ensures data types are correct and dates are Carbon instances.
   * Includes casts for FK, strings, timestamps, and soft deletes.
   * Explicitly casting all attributes for clarity and type safety.
   *
   * @var array<string, string>
   */
  protected $casts = [
    'category_id' => 'integer', // Cast FK to integer (required)
    'name' => 'string',         // Explicitly cast name as string (required)

    // If your 'sub_categories' table has a 'description' column, uncomment the line below to cast it:
    // 'description' => 'string',

    // Standard Eloquent timestamps handled by base model and traits
    'created_at' => 'datetime', // Explicitly cast creation timestamp to Carbon instance
    'updated_at' => 'datetime', // Explicitly cast update timestamp to Carbon instance
    'deleted_at' => 'datetime', // Cast soft delete timestamp to Carbon instance
    // Add casts for audit FKs if the trait doesn't add them: 'created_by' => 'integer', ...
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
        * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\User, \App\Models\SubCategory>
        */
  // public function createdBy(): BelongsTo;

  /*
        * Get the user who last updated the model.
        * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\User, \App\Models\SubCategory>
        */
  // public function updatedBy(): BelongsTo;

  /*
        * Get the user who soft deleted the model.
        * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\User, \App\Models\SubCategory>
        */
  // public function deletedBy(): BelongsTo;


  // 👉 Relationships

  /**
   * Get the category that the subcategory belongs to.
   * Defines a many-to-one relationship where a SubCategory belongs to one Category.
   * This links the subcategory to its parent in the hierarchy.
   * Assumes the 'sub_categories' table has a 'category_id' foreign key that
   * references the 'categories' table's primary key ('id').
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Category, \App\Models\SubCategory>
   */
  public function category(): BelongsTo // Added return type hint
  {
    // Defines a many-to-one relationship with the Category model.
    // 'Category::class' is the related model.
    // 'category_id' is the foreign key on the 'sub_categories' table.
    // 'id' is the local key on the 'categories' table (default, can be omitted).
    return $this->belongsTo(Category::class, 'category_id'); // Explicitly define FK for clarity
    // If using the standard foreign key name, this is sufficient: return $this->belongsTo(Category::class);
  }

  // Add any other relationships or methods below this line

  // Example: Mutator for name to ensure consistent casing (optional)
  /**
   * Accessor/Mutator for the subcategory name.
   * The mutator automatically applies `ucwords` (capitalize first letter of each word) to the name before saving.
   * The accessor returns the stored value.
   * Note: Be cautious with this if you have a unique constraint on the 'name' column
   * and names like 'Laptop' and 'laptop' should be considered unique by the database.
   *
   * @return \Illuminate\Database\Eloquent\Casts\Attribute
   */
  // protected function name(): Attribute // Added return type hint and docblock
  // {
  //     return Attribute::make(
  //         get: fn (string $value) => $value, // Accessor to get the stored value
  //         set: fn (string $value) => ucwords($value), // Mutator to capitalize first letter of each word before saving
  //     );
  // }
}
