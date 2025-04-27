<?php

namespace App\Models;

use App\Traits\CreatedUpdatedDeletedBy; // Assuming this trait exists and adds audit FKs/methods
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes; // Use SoftDeletes trait
// Removed unnecessary imports like Auth, Log if not used in the core model definition

class Center extends Model
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
    'start_work_hour',
    'end_work_hour',
    'weekends', // 'weekends' should be fillable to allow mass assignment of the array/JSON
    'is_active' // 'is_active' should be fillable
  ];

  /**
   * The attributes that should be cast.
   *
   * @var array<string, string>
   */
  protected $casts = [
    'weekends' => 'array', // Cast 'weekends' attribute to array for automatic JSON encoding/decoding
    'is_active' => 'boolean', // Cast 'is_active' to boolean as per migration
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
   * Get the timelines associated with the center.
   */
  public function timelines(): HasMany
  {
    // Assumes the 'timelines' table has a 'center_id' foreign key
    return $this->hasMany(Timeline::class, 'center_id'); // Explicitly define FK for clarity
  }

  /**
   * Get the holidays associated with the center.
   * Assumes a many-to-many relationship via a pivot table.
   */
  public function holidays(): BelongsToMany
  {
    // Assuming a 'center_holiday' pivot table exists linking centers and holidays
    // And that the pivot table has 'center_id' and 'holiday_id' columns
    return $this->belongsToMany(Holiday::class, 'center_holiday', 'center_id', 'holiday_id'); // Explicitly define pivot table and FK names for clarity
    // If the pivot table had extra columns (like 'date'), you'd add ->withPivot('date')
  }


  // 👉 Attributes (Accessors/Mutators)

  // Removed custom mutator for 'weekends' as it's handled automatically by the 'array' cast

  // name() Attribute: Keep if you need specific handling, otherwise it's not strictly necessary
  protected function name(): Attribute
  {
    return Attribute::make(
      // get: fn (string $value) => $value, // Accessor not needed if just returning the value
      set: fn(string $value) => $value, // Mutator to store as provided
    );
    // If you needed to modify the name case in the DB:
    // set: fn (string $value) => strtoupper($value),
  }

  // start_work_hour Attribute: Accessor to format time (optional)
  protected function startWorkHour(): Attribute
  {
    // Adjust format based on your needs (e.g., 'H:i', 'g:i A')
    return Attribute::make(
      // Stored as time, return as formatted string or Carbon instance
      get: fn(?string $value) => $value ? Carbon::parse($value)->format('H:i:s') : null, // Use ?string and null check for nullable columns
      // Store as provided time string (HH:MM:SS)
      set: fn(?string $value) => $value, // Use ?string for nullable columns
    );
  }

  // end_work_hour Attribute: Accessor to format time (optional)
  protected function endWorkHour(): Attribute
  {
    // Adjust format based on your needs
    return Attribute::make(
      // Stored as time, return as formatted string or Carbon instance
      get: fn(?string $value) => $value ? Carbon::parse($value)->format('H:i:s') : null, // Use ?string and null check for nullable columns
      // Store as provided time string (HH:MM:SS)
      set: fn(?string $value) => $value, // Use ?string for nullable columns
    );
  }


  // 👉 Functions

  /**
   * Get the active employees currently assigned to this center via timeline.
   * Requires the Employee model to have an 'is_active' boolean column.
   *
   * @return \Illuminate\Database\Eloquent\Collection<\App\Models\Employee>
   */
  public function activeEmployees()
  {
    // Refactored query for clarity and efficiency
    // Find Employee models that have a timeline entry linked to this center with no end date
    // AND are marked as active themselves.
    return Employee::whereHas('timelines', function ($query) {
      $query->where('center_id', $this->id)
        ->whereNull('end_date'); // Employee is currently assigned to THIS center
    })
      ->where('is_active', true) // Employee is generally active
      ->orderBy('first_name', 'asc') // Assuming 'first_name' exists on Employee
      ->get();

    // The commented-out logic below involving Center 100 and user roles is application-specific
    // and should be implemented in a service layer, controller, or dedicated query scope/class
    // rather than directly within the model's base method.
  }

  /**
   * Find a holiday associated with this center for a specific date.
   * Assumes the holidays table has a 'date' column for the holiday date.
   * Assumes the relationship with Holiday model is correctly defined.
   *
   * @param string|Carbon $date The date to check for a holiday.
   * @return \App\Models\Holiday|null The Holiday model or null if not found.
   */
  public function getHoliday($date): ?Holiday
  {
    // Ensure $date is a Carbon instance or date string and formatted for comparison
    $date = Carbon::parse($date)->format('Y-m-d');

    // Use the holidays relationship and filter by the 'date' column on the holidays table
    return $this->holidays()
      ->where('date', $date) // Filter on the 'date' column of the Holiday model's table
      ->first();

    // If the holiday date was stored on the pivot table instead:
    // return $this->holidays()->wherePivot('date', $date)->first(); // Only use if pivot table HAS a 'date' column
  }

  // Add any other existing methods below this line
}
