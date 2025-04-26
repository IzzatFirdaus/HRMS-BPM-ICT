<?php

namespace App\Models;

use App\Traits\CreatedUpdatedDeletedBy;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

// Import the Grade model
use App\Models\Grade;

class Position extends Model // Note: This likely maps to the 'designations' table in your HRMS
{
  use CreatedUpdatedDeletedBy, HasFactory, SoftDeletes;

  // Specify the table name if it's not the plural of the model name
  // <--- Add this line to tell the model to use the 'designations' table
  protected $table = 'designations';

  protected $fillable = [
    'name',
    'vacancies_count', // Keep existing HRMS field
    'description', // Add description field based on designations table
    'grade_id', // Add grade_id based on MOTAC positions table design
    // Add created_by, updated_by, deleted_by to fillable if not handled by trait
    // 'created_by',
    // 'updated_by',
    // 'deleted_by',
  ];

  // 👉 Existing HRMS Links
  public function timelines(): HasMany
  {
    return $this->hasMany(Timeline::class); // Keep existing HRMS relationship
  }

  // 👉 New MOTAC Resource Management Relationship

  /**
   * Get the grade associated with the position (designation).
   */
  public function grade(): BelongsTo
  {
    return $this->belongsTo(Grade::class); // Define relationship to the Grade model
  }


  // 👉 Attributes (Existing)
  protected function name(): Attribute
  {
    return Attribute::make(set: fn(string $value) => ucfirst($value)); // Keep existing mutator
  }

  // Add any other existing methods or accessors/mutators below this line

  // It seems your migrations handle created_by/updated_by/deleted_by columns directly,
  // but your trait might handle populating them. If the trait populates them,
  // you don't need them in $fillable unless you manually set them sometimes.
  // If the trait relies on the columns existing, the migration 'create_designations_table'
  // correctly adds them.
}
