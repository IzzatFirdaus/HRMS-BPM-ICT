<?php

namespace App\Models;

use App\Traits\CreatedUpdatedDeletedBy; // Assuming this trait exists and adds audit FKs/methods
use Illuminate\Database\Eloquent\Casts\Attribute; // Import Attribute for mutator example
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo; // Use BelongsTo trait
use Illuminate\Database\Eloquent\SoftDeletes; // Use SoftDeletes trait

// Import model for BelongsTo relationship
use App\Models\Category; // SubCategory belongs to a Category


class SubCategory extends Model
{
  // Use the traits for factory, soft deletes, and audit columns
  use CreatedUpdatedDeletedBy, HasFactory, SoftDeletes;

  /**
   * The attributes that are mass assignable.
   *
   * @var array<int, string>
   */
  protected $fillable = [
    // Removed 'id' from fillable - it's typically not mass assignable
    'category_id', // Foreign key to the categories table
    'name',
    // Add 'description' if your sub_categories table has one
    // 'description',
    // Audit columns are typically handled by the trait
  ];

  /**
   * The attributes that should be cast.
   *
   * @var array<string, string>
   */
  protected $casts = [
    // 'category_id' => 'integer', // Casting FKs is usually not needed, relationship handles it
    'created_at' => 'datetime', // Explicitly cast timestamps if trait doesn't handle or for clarity
    'updated_at' => 'datetime',
    'deleted_at' => 'datetime', // Cast soft delete timestamp
  ];


  // The CreatedUpdatedDeletedBy trait is assumed to add these audit relationships:
  // public function createdBy(): BelongsTo { ... }
  // public function updatedBy(): BelongsTo { ... }
  // public function deletedBy(): BelongsTo { ... }


  // 👉 Relationships

  /**
   * Get the category that the subcategory belongs to.
   * This is a many-to-one relationship (many subcategories belong to one category).
   */
  public function category(): BelongsTo
  {
    // Assumes the 'sub_categories' table has a 'category_id' foreign key
    return $this->belongsTo(Category::class, 'category_id'); // Explicitly define FK for clarity
  }

  // Add any other relationships or methods below this line

  // Example: Mutator for name to ensure consistent casing (optional)
  // protected function name(): Attribute
  // {
  //     return Attribute::make(
  //         set: fn (string $value) => ucwords($value), // Capitalize first letter of each word
  //     );
  //      // Be cautious with this if you have a unique constraint on the 'name' column
  //      // and names like 'Laptop' and 'laptop' should be considered unique by the database.
  // }
}
