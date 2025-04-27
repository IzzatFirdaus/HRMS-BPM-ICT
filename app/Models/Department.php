<?php

namespace App\Models;

use App\Traits\CreatedUpdatedDeletedBy; // Assuming this trait exists and adds audit FKs/methods
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany; // Use HasMany trait
use Illuminate\Database\Eloquent\SoftDeletes; // Use SoftDeletes trait

// Import models for HasMany relationships
use App\Models\Timeline;
use App\Models\User; // User model has department_id FK
use App\Models\Employee; // Employee model has department_id FK


class Department extends Model
{
  // Use the traits for factory, soft deletes, and audit columns
  use CreatedUpdatedDeletedBy, HasFactory, SoftDeletes;

  /**
   * The attributes that are mass assignable.
   *
   * @var array<int, string>
   */
  protected $fillable = [
    'name',
    'description', // ADDED: 'description' column from the migration
  ];

  /**
   * The attributes that should be cast.
   *
   * @var array<string, string>
   */
  protected $casts = [
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
   * Get the timelines associated with the department.
   */
  public function timelines(): HasMany
  {
    // Assumes the 'timelines' table has a 'department_id' foreign key
    return $this->hasMany(Timeline::class, 'department_id'); // Explicitly define FK for clarity
  }

  /**
   * Get the users associated with the department.
   */
  public function users(): HasMany
  {
    // Assumes the 'users' table has a 'department_id' foreign key
    return $this->hasMany(User::class, 'department_id'); // Explicitly define FK for clarity
  }

  /**
   * Get the employees associated with the department.
   */
  public function employees(): HasMany
  {
    // Assumes the 'employees' table has a 'department_id' foreign key
    return $this->hasMany(Employee::class, 'department_id'); // Explicitly define FK for clarity
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
    // and names like 'it' and 'It' should be considered unique by the database.
  }

  // Add any other relationships or methods below this line
}
