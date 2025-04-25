<?php

namespace App\Models;

use App\Traits\CreatedUpdatedDeletedBy;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo; // Import BelongsTo
use Illuminate\Database\Eloquent\SoftDeletes;

// Import the Grade model
use App\Models\Grade;

class Position extends Model // Note: This likely maps to the 'designations' table in your HRMS
{
  use CreatedUpdatedDeletedBy, HasFactory, SoftDeletes;

  // Specify the table name if it's not the plural of the model name (e.g., if it's 'designations')
  // protected $table = 'designations';

  protected $fillable = [
    'name',
    'vacancies_count', // Keep existing HRMS field
    'grade_id', // Add grade_id based on MOTAC positions table design
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
}
