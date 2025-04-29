<?php

namespace App\Models;

use App\Traits\CreatedUpdatedDeletedBy; // Assuming this trait exists and adds audit FKs/methods
use Illuminate\Database\Eloquent\Casts\Attribute; // Import Attribute for accessor/mutator examples
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany; // Use BelongsToMany trait
use Illuminate\Database\Eloquent\SoftDeletes; // Use SoftDeletes trait
use Illuminate\Support\Carbon; // Import Carbon for type hinting with date/datetime casts
use Illuminate\Database\Eloquent\Collection; // Import Collection for relationship return type hinting
use Illuminate\Database\Eloquent\Relations\BelongsTo as BelongsToRelation; // Alias if needed


// Import model for BelongsToMany relationship
use App\Models\Center; // Holiday is linked to Center via pivot
use App\Models\User; // For audit columns (handled by trait)


/**
 * App\Models\Holiday
 *
 * Represents a holiday that can be associated with multiple centers.
 * Tracks holiday name, dates, notes, and links to centers via a pivot table.
 *
 * @property int $id
 * @property string $name The name of the holiday.
 * @property \Illuminate\Support\Carbon $from_date The start date of the holiday.
 * @property \Illuminate\Support\Carbon $to_date The end date of the holiday.
 * @property string|null $note Additional notes about the holiday (nullable).
 * @property int|null $created_by Foreign key to the user who created the record.
 * @property int|null $updated_by Foreign key to the user who last updated the record.
 * @property int|null $deleted_by Foreign key to the user who soft deleted the record.
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at The timestamp when the holiday was soft deleted.
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Center> $centers The centers associated with this holiday.
 * @property-read int|null $centers_count
 * @property-read int $durationInDays The duration of the holiday in days.
 * @property-read bool $isMultiDay Check if this holiday spans multiple days.
 * @property-read \App\Models\User|null $createdBy The user who created the record.
 * @property-read \App\Models\User|null $deletedBy The user who soft deleted the record.
 * @property-read \App\Models\User|null $updatedBy The user who last updated the record.
 * @method static \Illuminate\Database\Eloquent\Builder|Holiday newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Holiday newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Holiday onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Holiday query()
 * @method static \Illuminate\Database\Eloquent\Builder|Holiday whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Holiday whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Holiday whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Holiday whereDeletedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Holiday whereFromDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Holiday whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Holiday whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Holiday whereNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Holiday whereToDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Holiday whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Holiday whereUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Holiday withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Holiday withoutTrashed()
 * @mixin \Eloquent
 */
class Holiday extends Model
{
  // Use the traits for factory, soft deletes, and audit columns
  use CreatedUpdatedDeletedBy, HasFactory, SoftDeletes;

  /**
   * The attributes that are mass assignable.
   * 'id' is excluded as it is the primary key.
   *
   * @var array<int, string>
   */
  protected $fillable = [
    'name', // The name of the holiday
    'from_date', // The start date of the holiday
    'to_date', // The end date of the holiday
    'note', // Additional notes about the holiday
    // 'created_by', // Handled by trait
    // 'updated_by', // Handled by trait
    // 'deleted_by', // Handled by trait
  ];

  /**
   * The attributes that should be cast.
   * Ensures data types are correct and dates are Carbon instances.
   * Includes casts for strings, dates, timestamps, and soft deletes.
   * Explicitly casting all attributes for clarity.
   *
   * @var array<string, string>
   */
  protected $casts = [
    'name' => 'string', // Explicitly cast name as string
    'note' => 'string', // Explicitly cast note as string

    'from_date' => 'date', // Cast date fields to Carbon instances (YYYY-MM-DD)
    'to_date' => 'date', // Cast date fields

    'created_at' => 'datetime', // Explicitly cast timestamps
    'updated_at' => 'datetime',
    'deleted_at' => 'datetime', // Cast soft delete timestamp
  ];


  // The CreatedUpdatedDeletedBy trait is assumed to add these audit relationships:

  /**
   * Get the user who created the model.
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\User, \App\Models\Holiday>
   */
  // public function createdBy(): BelongsToRelation;

  /**
   * Get the user who last updated the model.
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\User, \App\Models\Holiday>
   */
  // public function updatedBy(): BelongsToRelation;

  /**
   * Get the user who soft deleted the model.
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\User, \App\Models\Holiday>
   */
  // public function deletedBy(): BelongsToRelation;


  // 👉 Relationships

  /**
   * Get the centers associated with this holiday through the pivot table.
   * Defines a many-to-many relationship with the Center model via the 'center_holiday' pivot table.
   * Assumes a 'center_holiday' pivot table linking holidays and centers with columns 'holiday_id' and 'center_id'.
   * Includes pivot table audit columns and timestamps with casting.
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany<\App\Models\Center>
   */
  public function centers(): BelongsToMany // Added return type hint and refined docblock
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
      // If the pivot table has 'id' or other columns, add them to withPivot and cast them.
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

  // Example: Mutator for name to ensure consistent casing (optional)
  /**
   * Get or set the holiday name.
   * Applies ucfirst mutation on setting.
   * Note: Be cautious with this if you have a unique constraint on the 'name' column
   * and names like 'New year' and 'new year' should be considered unique by the database.
   *
   * @return \Illuminate\Database\Eloquent\Casts\Attribute
   */
  // protected function name(): Attribute // Added return type hint and docblock
  // {
  //      return Attribute::make(
  //          // get: fn (string $value) => $value, // Accessor not needed if just returning the value
  //          set: fn (string $value) => ucfirst($value), // Capitalize first letter of each word
  //      );
  // }


  // Add custom methods or accessors/mutators below this line

  /**
   * Check if this holiday spans multiple days.
   * Compares the 'from_date' and 'to_date' attributes after they have been cast to Carbon instances.
   *
   * @return bool True if the 'from_date' and 'to_date' are different (indicating a multi-day holiday), false otherwise.
   */
  public function isMultiDay(): bool // Added return type hint and refined docblock
  {
    // Since from_date and to_date are cast to Carbon, we can directly compare them or their formatted strings.
    // Comparing Carbon instances directly is generally preferred.
    // Ensure both dates are not null before comparing if the columns are nullable.
    // Based on the migration, from_date and to_date appear non-nullable, but checking for robustness.
    if ($this->from_date instanceof Carbon && $this->to_date instanceof Carbon) {
      return !$this->from_date->isSameDay($this->to_date);
    }

    // Fallback logic if dates are somehow not Carbon instances or are null (shouldn't happen with non-nullable casts)
    return $this->from_date != $this->to_date; // Compare raw values if Carbon instances aren't available
  }

  /**
   * Get the duration of the holiday in days.
   * Calculates the inclusive difference in days between 'from_date' and 'to_date' using Carbon.
   *
   * @return int The number of days the holiday spans (inclusive). Returns 1 for single-day holidays, 0 if dates are invalid/missing.
   */
  public function getDurationInDaysAttribute(): int // Added return type hint and refined docblock
  {
    if ($this->from_date instanceof Carbon && $this->to_date instanceof Carbon) {
      // diffInDays is exclusive, add 1 to make it inclusive
      return $this->from_date->diffInDays($this->to_date) + 1;
    }
    return 0; // Return 0 if dates are not set or not valid Carbon instances
  }
}
