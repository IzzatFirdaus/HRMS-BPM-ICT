<?php

namespace App\Models;

use App\Traits\CreatedUpdatedDeletedBy; // Assuming this trait exists and adds audit FKs/methods
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany; // Use HasMany trait
use Illuminate\Database\Eloquent\SoftDeletes; // Use SoftDeletes trait

// Import models for HasMany relationships
use App\Models\User; // User model has grade_id FK
use App\Models\Employee; // Employee model has grade_id FK


class Grade extends Model
{
  // Use the traits for factory, soft deletes, and audit columns
  use CreatedUpdatedDeletedBy, HasFactory, SoftDeletes;

  /**
   * The attributes that are mass assignable.
   *
   * @var array<int, string>
   */
  protected $fillable = [
    'name', // e.g., 'Grade 41', 'Grade 44', 'Grade 9'
    'level', // e.g., 41, 44, 9 (integer representation for sorting/comparison)
    'is_approver_grade', // ADDED: is_approver_grade column from migration
    // Add any other relevant fields from your grades table, e.g., 'description' if it exists
  ];

  /**
   * The attributes that should be cast.
   *
   * @var array<string, string>
   */
  protected $casts = [
    'level' => 'integer', // Ensure level is cast to an integer
    'is_approver_grade' => 'boolean', // ADDED: Cast is_approver_grade to boolean as per migration
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
   * Get the users that belong to this grade.
   */
  public function users(): HasMany
  {
    // Assumes the 'users' table has a 'grade_id' foreign key
    return $this->hasMany(User::class, 'grade_id'); // Explicitly define FK for clarity
  }

  /**
   * Get the employees that belong to this grade.
   */
  public function employees(): HasMany
  {
    // Assumes the 'employees' table has a 'grade_id' foreign key
    return $this->hasMany(Employee::class, 'grade_id'); // Explicitly define FK for clarity
  }

  // Add custom methods or accessors/mutators here as needed

  /**
   * Check if this grade is designated as an approver grade.
   *
   * @return bool
   */
  public function isApprover(): bool
  {
    return (bool) $this->is_approver_grade;
  }
}
