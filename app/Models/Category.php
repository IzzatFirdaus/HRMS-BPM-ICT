<?php

namespace App\Models;

use App\Traits\CreatedUpdatedDeletedBy; // Assuming this trait exists and adds audit FKs/methods
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany; // Use HasMany trait
// Removed BelongsToMany as it's not the correct relationship type here
use Illuminate\Database\Eloquent\SoftDeletes; // Use SoftDeletes trait

// Import model for HasMany relationship
use App\Models\SubCategory; // Category has many SubCategories


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
  use CreatedUpdatedDeletedBy, HasFactory, SoftDeletes;

  /**
   * The attributes that are mass assignable.
   *
   * @var array<int, string>
   */
  protected $fillable = [
    // 'id' is typically not mass assignable as it's the primary key
    'name',
    // Audit columns like created_by, updated_by are handled by the CreatedUpdatedDeletedBy trait
  ];

  /**
   * The attributes that should be cast.
   * Ensures dates are Carbon instances.
   *
   * @var array<string, string>
   */
  protected $casts = [
    'created_at' => 'datetime', // Explicitly cast timestamps
    'updated_at' => 'datetime',
    'deleted_at' => 'datetime', // Cast soft delete timestamp
  ];


  // The CreatedUpdatedDeletedBy trait is assumed to add these audit relationships:
  // public function createdBy(): BelongsTo;
  // public function updatedBy(): BelongsTo;
  // public function deletedBy(): BelongsTo;


  // 👉 Relationships

  /**
   * Get the subcategories associated with the category.
   * This defines a one-to-many relationship where a Category has many SubCategories.
   * Assumes the 'sub_categories' table has a 'category_id' foreign key.
   *
   * @return \Illuminate\Database\Eloquent\Relations\HasMany
   */
  public function subCategories(): HasMany // Added return type hint
  {
    // Defines a one-to-many relationship with the SubCategory model
    return $this->hasMany(SubCategory::class, 'category_id'); // Explicitly define FK for clarity
  }


  // Add custom methods or accessors/mutators below this line

  // Example: Mutator for name to ensure consistent casing (optional)
  // protected function name(): Attribute
  // {
  //     return Attribute::make(
  //         set: fn (string $value) => ucwords($value), // Capitalize first letter of each word
  //     );
  //     // Be cautious with this if you have a unique constraint on the 'name' column
  //     // and names like 'IT' and 'it' should be considered unique by the database.
  // }
}
