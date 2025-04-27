<?php

namespace App\Models;

use App\Traits\CreatedUpdatedDeletedBy; // Assuming this trait exists and adds audit FKs/methods
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany; // Use BelongsToMany trait
use Illuminate\Database\Eloquent\SoftDeletes; // Use SoftDeletes trait

// Import model for BelongsToMany relationship
use App\Models\Center; // Holiday is linked to Center via pivot


class Holiday extends Model
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
    'from_date', // From your fillable, assuming this column exists in migration
    'to_date',   // From your fillable, assuming this column exists in migration
    'note',      // From your fillable, assuming this column exists in migration
    // Audit columns are typically handled by the trait
  ];

  /**
   * The attributes that should be cast.
   *
   * @var array<string, string>
   */
  protected $casts = [
    'from_date' => 'date', // ADDED: Cast date fields
    'to_date' => 'date',   // ADDED: Cast date fields
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
   * Get the centers associated with this holiday through the pivot table.
   * Assumes a many-to-many relationship via a pivot table with audit columns and timestamps.
   */
  public function centers(): BelongsToMany
  {
    // Assuming a 'center_holiday' pivot table exists linking holidays and centers
    // with columns 'holiday_id' and 'center_id'.
    return $this->belongsToMany(Center::class, 'center_holiday', 'holiday_id', 'center_id')
      ->withPivot([
        // Include pivot table audit columns and timestamps if trait doesn't handle them automatically on the pivot model
        'created_by',
        'updated_by',
        'deleted_by',
        'created_at',
        'updated_at',
        'deleted_at'
      ])
      // Optional: Add casting for pivot attributes for correct types
      // You might need to define a model for the pivot table (CenterHoliday)
      // to properly use traits like CreatedUpdatedDeletedBy and SoftDeletes on the pivot itself.
      // If the pivot table has 'id' or other columns, add them to withPivot.
      ->withCasts([
        'created_by' => 'integer', // Cast pivot audit FKs to integer
        'updated_by' => 'integer',
        'deleted_by' => 'integer',
        'created_at' => 'datetime', // Cast pivot timestamps
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime', // Cast pivot soft delete timestamp
      ]);
  }

  // Note: If you commonly work with the pivot records directly, you might define a model for the pivot table (CenterHoliday)
  // and use a HasMany relationship to it here.


  // 👉 Attributes (Accessors/Mutators)

  // name() Attribute: Mutator to apply ucfirst (optional - be mindful of unique constraints)
  // Use this if you want to ensure the first letter is always capitalized in the database.
  protected function name(): Attribute
  {
    return Attribute::make(
      set: fn(string $value) => ucfirst($value), // Mutator to store as provided, applying ucfirst
    );
    // Be cautious with this if you have a unique constraint on the 'name' column
    // and names like 'New year' and 'new year' should be considered unique by the database.
  }


  // Add custom methods or accessors/mutators below this line

  /**
   * Check if this holiday spans multiple days.
   *
   * @return bool
   */
  public function isMultiDay(): bool
  {
    // Ensure dates are loaded or accessor is used
    if ($this->from_date instanceof \Carbon\Carbon && $this->to_date instanceof \Carbon\Carbon) {
      return $this->from_date->format('Y-m-d') !== $this->to_date->format('Y-m-d');
    }
    // Fallback if dates are not Carbon instances (e.g., not cast or not retrieved)
    return $this->from_date !== $this->to_date;
  }

  /**
   * Get the duration of the holiday in days.
   *
   * @return int The number of days the holiday spans (inclusive).
   */
  public function getDurationInDaysAttribute(): int
  {
    if ($this->from_date instanceof \Carbon\Carbon && $this->to_date instanceof \Carbon\Carbon) {
      // diffInDays is exclusive, add 1 to make it inclusive
      return $this->from_date->diffInDays($this->to_date) + 1;
    }
    return 0; // Return 0 if dates are not set or not valid Carbon instances
  }
}
