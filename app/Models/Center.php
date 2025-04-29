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
use Illuminate\Support\Collection; // Import Collection for type hinting

// Import models for relationships
use App\Models\Timeline; // Center has many Timelines
use App\Models\Holiday; // Center belongs to many Holidays (via pivot)
use App\Models\Employee; // Used in activeEmployees function


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
   * Get the timelines associated with the center.
   * Defines a one-to-many relationship where a Center has many Timelines.
   * Assumes the 'timelines' table has a 'center_id' foreign key.
   *
   * @return \Illuminate\Database\Eloquent\Relations\HasMany
   */
  public function timelines(): HasMany // Added return type hint
  {
    // Defines a one-to-many relationship with the Timeline model
    return $this->hasMany(Timeline::class, 'center_id'); // Explicitly define FK for clarity
  }

  /**
   * Get the holidays associated with the center.
   * Defines a many-to-many relationship with the Holiday model via a pivot table.
   * Assumes a 'center_holiday' pivot table exists linking centers and holidays
   * and that the pivot table has 'center_id' and 'holiday_id' columns.
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
   */
  public function holidays(): BelongsToMany // Added return type hint
  {
    // Defines a many-to-many relationship with the Holiday model
    return $this->belongsToMany(Holiday::class, 'center_holiday', 'center_id', 'holiday_id'); // Explicitly define pivot table and FK names for clarity
    // If the pivot table had extra columns (like 'date'), you'd add ->withPivot('date')
  }


  // 👉 Attributes (Accessors/Mutators)

  // Removed custom mutator for 'weekends' as it's handled automatically by the 'array' cast

  /**
   * Get or set the center's name.
   *
   * @return \Illuminate\Database\Eloquent\Casts\Attribute
   */
  protected function name(): Attribute
  {
    return Attribute::make(
      // get: fn (string $value) => $value, // Accessor not needed if just returning the value
      set: fn(string $value) => $value, // Mutator to store as provided
    );
    // If you needed to modify the name case in the DB:
    // set: fn (string $value) => strtoupper($value),
  }

  /**
   * Get or set the center's start work hour.
   * Formats the time for display.
   *
   * @return \Illuminate\Database\Eloquent\Casts\Attribute
   */
  protected function startWorkHour(): Attribute
  {
    return Attribute::make(
      // Stored as time string (HH:MM:SS) in DB, return as formatted string or Carbon instance
      get: fn(?string $value) => $value ? Carbon::parse($value)->format('H:i:s') : null, // Use ?string and null check for nullable columns
      // Store as provided time string (HH:MM:SS)
      set: fn(?string $value) => $value, // Use ?string for nullable columns
    );
  }

  /**
   * Get or set the center's end work hour.
   * Formats the time for display.
   *
   * @return \Illuminate\Database\Eloquent\Casts\Attribute
   */
  protected function endWorkHour(): Attribute
  {
    return Attribute::make(
      // Stored as time string (HH:MM:SS) in DB, return as formatted string or Carbon instance
      get: fn(?string $value) => $value ? Carbon::parse($value)->format('H:i:s') : null, // Use ?string and null check for nullable columns
      // Store as provided time string (HH:MM:SS)
      set: fn(?string $value) => $value, // Use ?string for nullable columns
    );
  }


  // 👉 Functions

  /**
   * Get the active employees currently assigned to this center via timeline.
   * Requires the Employee model to have a 'timelines' relationship and an 'is_active' boolean column.
   *
   * @return \Illuminate\Database\Eloquent\Collection<\App\Models\Employee>
   */
  public function activeEmployees(): Collection // Added return type hint
  {
    // Find Employee models that have a timeline entry linked to this center with no end date
    // AND are marked as active themselves.
    return Employee::whereHas('timelines', function ($query) {
      $query->where('center_id', $this->id)
        ->whereNull('end_date'); // Employee is currently assigned to THIS center
    })
      ->where('is_active', true) // Employee is generally active
      ->orderBy('first_name', 'asc') // Assuming 'first_name' exists on Employee
      ->get();
  }

  /**
   * Find a holiday associated with this center for a specific date.
   * Assumes the holidays table has a 'date' column for the holiday date.
   * Assumes the relationship with Holiday model is correctly defined via a pivot table.
   *
   * @param string|Carbon $date The date to check for a holiday.
   * @return \App\Models\Holiday|null The Holiday model or null if not found.
   */
  public function getHoliday(string|Carbon $date): ?Holiday // Added parameter and return type hints
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
