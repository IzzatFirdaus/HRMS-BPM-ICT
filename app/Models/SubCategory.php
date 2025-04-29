<?php

namespace App\Models;

use App\Traits\CreatedUpdatedDeletedBy; // Assuming this trait exists and adds audit FKs/methods
use Illuminate\Database\Eloquent\Casts\Attribute; // Import Attribute for mutator example
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo; // Use BelongsTo trait
use Illuminate\Database\Eloquent\SoftDeletes; // Use SoftDeletes trait
use Illuminate\Support\Carbon; // Import Carbon for type hinting with timestamps
use Illuminate\Database\Eloquent\Relations\BelongsTo as BelongsToRelation; // Alias if needed


// Import model for BelongsTo relationship
use App\Models\Category; // SubCategory belongs to a Category
use App\Models\User; // For audit columns (handled by trait)


/**
 * App\Models\SubCategory
 *
 * Represents a subcategory within a hierarchical category structure.
 * Linked to a parent Category.
 *
 * @property int $id
 * @property int $category_id Foreign key to the parent category.
 * @property string $name The name of the subcategory.
 * @property string|null $description A description of the subcategory (if column exists).
 * @property int|null $created_by Foreign key to the user who created the record.
 * @property int|null $updated_by Foreign key to the user who last updated the record.
 * @property int|null $deleted_by Foreign key to the user who soft deleted the record.
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at The timestamp when the subcategory was soft deleted.
 * @property-read \App\Models\Category $category The parent category that the subcategory belongs to.
 * @property-read \App\Models\User|null $createdBy The user who created the record.
 * @property-read \App\Models\User|null $deletedBy The user who soft deleted the record.
 * @property-read \App\Models\User|null $updatedBy The user who last updated the record.
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
 * @method static \Illuminate\Database\Eloquent\Builder|SubCategory whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SubCategory whereUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SubCategory withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|SubCategory withoutTrashed()
 * @mixin \Eloquent
 */
class SubCategory extends Model
{
  // Use the traits for factory, soft deletes, and audit columns
  use CreatedUpdatedDeletedBy, HasFactory, SoftDeletes;

  /**
   * The attributes that are mass assignable.
   * 'id' is excluded as it is the primary key.
   *
   * @var array<int, string>
   */
  protected $fillable = [
    'category_id', // Foreign key to the categories table
    'name', // The name of the subcategory
    // 'description', // Add 'description' if your sub_categories table has one and include in $casts below
    // 'created_by', // Handled by trait
    // 'updated_by', // Handled by trait
    // 'deleted_by', // Handled by trait
  ];

  /**
   * The attributes that should be cast.
   * Ensures data types are correct and dates are Carbon instances.
   * Includes casts for FK, strings, timestamps, and soft deletes.
   * Explicitly casting all attributes for clarity.
   *
   * @var array<string, string>
   */
  protected $casts = [
    'category_id' => 'integer', // Cast FK to integer
    'name' => 'string', // Explicitly cast name as string
    // 'description' => 'string', // Uncomment and cast description as string if column exists
    'created_at' => 'datetime', // Explicitly cast timestamps
    'updated_at' => 'datetime',
    'deleted_at' => 'datetime', // Cast soft delete timestamp
  ];


  // The CreatedUpdatedDeletedBy trait is assumed to add these audit relationships:

  /**
   * Get the user who created the model.
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\User, \App\Models\SubCategory>
   */
  // public function createdBy(): BelongsToRelation;

  /**
   * Get the user who last updated the model.
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\User, \App\Models\SubCategory>
   */
  // public function updatedBy(): BelongsToRelation;

  /**
   * Get the user who soft deleted the model.
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\User, \App\Models\SubCategory>
   */
  // public function deletedBy(): BelongsToRelation;


  // 👉 Relationships

  /**
   * Get the category that the subcategory belongs to.
   * Defines a many-to-one relationship where a SubCategory belongs to one Category.
   * Assumes the 'sub_categories' table has a 'category_id' foreign key.
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Category, \App\Models\SubCategory>
   */
  public function category(): BelongsTo // Added return type hint
  {
    // Defines a many-to-one relationship with the Category model
    return $this->belongsTo(Category::class, 'category_id'); // Explicitly define FK for clarity
  }

  // Add any other relationships or methods below this line

  // Example: Mutator for name to ensure consistent casing (optional)
  /**
   * Get or set the subcategory name.
   * Applies ucwords mutation on setting.
   * Note: Be cautious with this if you have a unique constraint on the 'name' column
   * and names like 'Laptop' and 'laptop' should be considered unique by the database.
   *
   * @return \Illuminate\Database\Eloquent\Casts\Attribute
   */
  // protected function name(): Attribute // Added return type hint and docblock
  // {
  //      return Attribute::make(
  //          // get: fn (string $value) => $value, // Accessor not needed if just returning the value
  //          set: fn (string $value) => ucwords($value), // Capitalize first letter of each word
  //      );
  // }
}
