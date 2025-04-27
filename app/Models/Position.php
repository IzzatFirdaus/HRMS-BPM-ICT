<?php

namespace App\Models;

use App\Traits\CreatedUpdatedDeletedBy; // Assuming this trait exists and adds audit FKs/methods
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany; // Use HasMany trait
use Illuminate\Database\Eloquent\Relations\BelongsTo; // Use BelongsTo trait
use Illuminate\Database\Eloquent\SoftDeletes; // Use SoftDeletes trait

// Import the Grade model for BelongsTo relationship
use App\Models\Grade;
// Import models for HasMany relationships (users and employees link to positions)
use App\Models\Timeline;
use App\Models\User; // User model has position_id FK
use App\Models\Employee; // Employee model has position_id FK


class Position extends Model // This model maps to the 'positions' table
{
  // Use the traits for factory, soft deletes, and audit columns
  use CreatedUpdatedDeletedBy, HasFactory, SoftDeletes;

  // 👇 REMOVED or CORRECTED: Specify the table name if it's not the plural of the model name
  // If your table is indeed named 'positions', you can remove this line:
  // protected $table = 'positions'; // Or if still 'designations': protected $table = 'designations';
  // Assuming standard naming, we remove it if the table is 'positions'.


  /**
   * The attributes that are mass assignable.
   *
   * @var array<int, string>
   */
  protected $fillable = [
    'name',
    'vacancies_count', // From positions migration
    'description', // From positions migration
    'grade_id', // Foreign key from positions migration
    // Audit columns are typically handled by the trait
  ];

  /**
   * The attributes that should be cast.
   *
   * @var array<string, string>
   */
  protected $casts = [
    'vacancies_count' => 'integer', // Cast nullable integer
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
   * Get the timelines associated with the position.
   */
  public function timelines(): HasMany
  {
    // Assumes the 'timelines' table has a 'position_id' foreign key
    return $this->hasMany(Timeline::class, 'position_id'); // Explicitly define FK for clarity
  }

  /**
   * Get the users that belong to this position.
   */
  public function users(): HasMany
  {
    // Assumes the 'users' table has a 'position_id' foreign key
    return $this->hasMany(User::class, 'position_id'); // Explicitly define FK for clarity
  }

  /**
   * Get the employees that belong to this position.
   */
  public function employees(): HasMany
  {
    // Assumes the 'employees' table has a 'position_id' foreign key
    return $this->hasMany(Employee::class, 'position_id'); // Explicitly define FK for clarity
  }


  /**
   * Get the grade associated with the position.
   */
  public function grade(): BelongsTo
  {
    // Assumes the 'positions' table has a 'grade_id' foreign key
    return $this->belongsTo(Grade::class, 'grade_id'); // Explicitly define FK for clarity
  }


  // 👉 Attributes (Accessors/Mutators)

  // name() Attribute: Mutator to apply ucfirst (optional - be mindful of unique constraints)
  // Use this if you want to ensure the first letter is always capitalized in the database.
  protected function name(): Attribute
  {
    return Attribute::make(
      set: fn(string $value) => ucfirst($value), // Mutator to store as provided, applying ucfirst
    );
    // Be cautious with this if you have a unique constraint on the 'name' column
    // and names like 'Manager' and 'manager' should be considered unique by the database.
  }

  // Add any other relationships or methods below this line
}
