<?php

namespace App\Models; // Ensure the namespace is correct for your project

use App\Traits\CreatedUpdatedDeletedBy; // Assuming this trait exists and adds audit FKs/methods
use Carbon\Carbon; // Import Carbon for date/time handling
use Illuminate\Database\Eloquent\Casts\Attribute; // Import Attribute for modern accessors/mutators
use Illuminate\Database\Eloquent\Factories\HasFactory; // Import HasFactory trait
use Illuminate\Database\Eloquent\Model; // Import base Model class
use Illuminate\Database\Eloquent\Relations\BelongsTo; // Import BelongsTo relationship type (for audit trait relationships)
use Illuminate\Database\Eloquent\Relations\BelongsToMany; // Import BelongsToMany relationship type
use Illuminate\Database\Eloquent\Relations\HasMany; // Import HasMany relationship type
use Illuminate\Database\Eloquent\SoftDeletes; // Import SoftDeletes trait
use Illuminate\Support\Collection; // Import Collection for type hinting


// Import models for relationships (Eloquent needs to know about the related models for relationships)
use App\Models\Timeline; // Center has many Timelines
use App\Models\Holiday; // Center belongs to many Holidays (via pivot)
use App\Models\Employee; // Used in activeEmployees function (Employee model needs to exist)
// Add other related models if necessary (e.g., User if users are directly assigned to centers)
// use App\Models\User;


/**
<<<<<<< HEAD
 * Class Center
 *
 * Represents an administrative or physical center within the organization.
 * Manages relationships with Timelines (employee assignments), Holidays,
 * and provides methods for checking work hours, weekends, and associated employees.
=======
 * 
 *
 * @property int $id
 * @property string $name
 * @property string|null $start_work_hour
 * @property string|null $end_work_hour
 * @property array<array-key, mixed> $weekends
 * @property bool $is_active
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property int|null $deleted_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Holiday> $holidays
 * @property-read int|null $holidays_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Timeline> $timelines
 * @property-read int|null $timelines_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Center newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Center newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Center onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Center query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Center whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Center whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Center whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Center whereDeletedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Center whereEndWorkHour($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Center whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Center whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Center whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Center whereStartWorkHour($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Center whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Center whereUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Center whereWeekends($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Center withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Center withoutTrashed()
 * @mixin \Eloquent
>>>>>>> cc6eb9f4f020325c04fee080d2466584ff27bb90
 */
class Center extends Model
{
  // Use the traits for factory, soft deletes, and audit columns
  // SoftDeletes adds the 'deleted_at' column and scope.
  // CreatedUpdatedDeletedBy is assumed to add 'created_by', 'updated_by', 'deleted_by' columns and relationships.
  use CreatedUpdatedDeletedBy, HasFactory, SoftDeletes;

  /**
   * The table associated with the model.
   *
   * @var string
   */
  // protected $table = 'centers'; // Explicitly define table name if it's not the plural of the model name


  /**
   * The attributes that are mass assignable.
   * Allows these attributes to be set using mass assignment (e.g., during create or update).
   *
   * @var array<int, string>
   */
  protected $fillable = [
    // 'id' is the primary key and typically not included in fillable
    'name',            // The name of the center (e.g., 'MOTAC Ibu Pejabat')
    'start_work_hour', // The standard start time for work (e.g., '08:00:00')
    'end_work_hour',   // The standard end time for work (e.g., '17:00:00')
    'weekends',        // An array of days considered weekends (e.g., ['saturday', 'sunday'])
    'is_active',       // Boolean indicating if the center is currently active
    // Add other fillable attributes here if necessary (e.g., 'address', 'contact_phone')
  ];

  /**
   * The attributes that should be cast.
   * Ensures date/datetime attributes are Carbon instances and JSON columns are arrays.
   *
   * @var array<string, string>
   */
  protected $casts = [
    'weekends' => 'array',    // Cast 'weekends' attribute to array for automatic JSON encoding/decoding
    'is_active' => 'boolean', // Cast 'is_active' to boolean
    'created_at' => 'datetime', // Explicitly cast creation timestamp
    'updated_at' => 'datetime', // Explicitly cast update timestamp
    'deleted_at' => 'datetime', // Cast soft delete timestamp
    // Add casts for other attributes if needed
  ];

  /**
   * The attributes that should be hidden for serialization.
   * (Optional) Prevents sensitive attributes from being returned in JSON responses.
   *
   * @var array<int, string>
   */
  // protected $hidden = [
  //     'created_by', // Example: hide audit columns from API responses
  //     'updated_by',
  //     'deleted_by',
  // ];


  // The CreatedUpdatedDeletedBy trait is assumed to add these audit relationships:
  // public function createdBy(): BelongsTo; // Relationship to the user who created the record
  // public function updatedBy(): BelongsTo; // Relationship to the user who last updated the record
  // public function deletedBy(): BelongsTo; // Relationship to the user who soft deleted the record


  // 👉 Relationships

  /**
   * Get the timelines associated with the center.
   * Defines a one-to-many relationship where a Center has many Timelines.
   * This relationship is likely used to track employee assignments to this center over time.
   * Assumes the 'timelines' table has a 'center_id' foreign key that
   * references the 'centers' table's primary key ('id').
   *
   * @return \Illuminate\Database\Eloquent\Relations\HasMany
   */
  public function timelines(): HasMany // Added return type hint
  {
    // Defines a one-to-many relationship with the Timeline model.
    // 'Timeline::class' is the related model.
    // 'center_id' is the foreign key on the 'timelines' table.
    // 'id' is the local key on the 'centers' table (default, can be omitted).
    return $this->hasMany(Timeline::class, 'center_id');
  }

  /**
   * Get employees currently or previously assigned to this center via their timelines.
   * Note: The activeEmployees() function below provides a filtered list of *currently active* employees.
   *
   * @return \Illuminate\Database\Eloquent\Relations\HasManyThrough
   */
  // public function employees(): HasManyThrough
  // {
  // Defines a relationship where Center has Employees *through* Timelines
  // Assumes Timeline model belongsTo Employee and belongsTo Center
  //     return $this->hasManyThrough(
  //         Employee::class, // The final model we want to access
  //         Timeline::class, // The intermediate model
  //         'center_id',     // Foreign key on the Timeline model...
  //         'id',            // Local key on the Employee model...
  //         'id',            // Local key on the Center model...
  //         'employee_id'    // Foreign key on the Timeline model...
  //     );
  // }


  /**
   * Get the holidays associated with the center.
   * Defines a many-to-many relationship with the Holiday model via a pivot table.
   * This allows centers to have specific holidays associated with them, potentially
   * overriding or adding to system-wide holidays.
   * Assumes a 'center_holiday' pivot table exists linking centers and holidays
   * and that the pivot table has 'center_id' and 'holiday_id' columns.
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
   */
  public function holidays(): BelongsToMany // Added return type hint
  {
    // Defines a many-to-many relationship with the Holiday model.
    // 'Holiday::class' is the related model.
    // 'center_holiday' is the name of the intermediate table (pivot table).
    // 'center_id' is the foreign key on the pivot table referencing the Center model.
    // 'holiday_id' is the foreign key on the pivot table referencing the Holiday model.
    return $this->belongsToMany(Holiday::class, 'center_holiday', 'center_id', 'holiday_id');
    // If the pivot table had extra columns you needed to access (like 'date', 'notes'):
    // ->withPivot('date', 'notes'); // Include pivot table columns
    // If pivot table timestamps are used:
    // ->withTimestamps();
  }


  // 👉 Attributes (Accessors/Mutators using Attributes)

  // Example: Mutator for name to ensure consistent casing (optional)
  // Use Attributes for modern Laravel mutators/accessors.
  // Ensure you import: use Illuminate\Database\Eloquent\Casts\Attribute;
  // protected function name(): Attribute
  // {
  //     return Attribute::make(
  //         get: fn (string $value) => $value, // Optional: define custom retrieval logic
  //         set: fn (string $value) => $value, // Example: Store name as provided (no transformation)
  //         // If you needed to modify the name case consistently in the DB:
  //         // set: fn (string $value) => strtoupper($value), // Convert to uppercase
  //     );
  //     // Be cautious with using mutators like strtoupper() if you have a unique constraint
  //     // on the 'name' column and need case-sensitive uniqueness in the database.
  //     // Database-level constraints might interact differently with mutated values.
  // }

  /**
   * Accessor to format the center's start work hour.
   * Stored as time string (HH:MM:SS) in DB, returns as formatted string or null.
   *
   * @return \Illuminate\Database\Eloquent\Casts\Attribute
   */
  protected function startWorkHour(): Attribute
  {
    return Attribute::make(
      // Accessor: Format the stored time string for display
      get: fn(?string $value) => $value ? Carbon::parse($value)->format('H:i') : null, // Format as HH:MM, use ?string and null check
      // Mutator: Ensure the value is stored as provided time string (HH:MM:SS) or null
      set: fn(?string $value) => $value, // Use ?string for nullable columns
    );
  }

  /**
   * Accessor to format the center's end work hour.
   * Stored as time string (HH:MM:SS) in DB, returns as formatted string or null.
   *
   * @return \Illuminate\Database\Eloquent\Casts\Attribute
   */
  protected function endWorkHour(): Attribute
  {
    return Attribute::make(
      // Accessor: Format the stored time string for display
      get: fn(?string $value) => $value ? Carbon::parse($value)->format('H:i') : null, // Format as HH:MM, use ?string and null check
      // Mutator: Ensure the value is stored as provided time string (HH:MM:SS) or null
      set: fn(?string $value) => $value, // Use ?string for nullable columns
    );
  }

  /**
   * Accessor to get the formatted weekend days string.
   * Converts the 'weekends' array into a human-readable string (e.g., "Saturday, Sunday").
   *
   * @return \Illuminate\Database\Eloquent\Casts\Attribute
   */
  protected function weekendsFormatted(): Attribute
  {
    return Attribute::make(
      get: fn() => $this->weekends ? implode(', ', array_map('ucfirst', $this->weekends)) : __('Tiada'), // Join weekend days, capitalize first letter
    );
  }


  // 👉 Functions / Custom Query Scopes

  /**
   * Get the active employees currently assigned to this center via timeline.
   * This function queries the Employee model and filters based on active timelines
   * linked to this specific center.
   * Requires the Employee model to have a 'timelines' relationship (HasMany Timeline)
   * and assume the Timeline model belongsTo Employee and has 'center_id' and 'end_date' columns.
   * Assumes Employee model has an 'is_active' boolean column.
   *
   * @return \Illuminate\Database\Eloquent\Collection<\App\Models\Employee>
   */
  public function activeEmployees(): Collection // Added return type hint
  {
    // Find Employee models that have at least one timeline entry linked to this center
    // with an end_date that is NULL (indicating current assignment).
    // Also filter to include only Employees where 'is_active' is true.
    return Employee::whereHas('timelines', function ($query) {
      $query->where('center_id', $this->id) // Filter timelines for this center
        ->whereNull('end_date');       // Ensure the timeline assignment is current (no end date)
    })
      ->where('is_active', true) // Filter employees who are generally active
      ->orderBy('first_name', 'asc') // Example ordering
      ->get(); // Get the resulting collection of Employees
  }

  /**
   * Find a holiday associated with this center for a specific date.
   * Checks both the center's directly linked holidays (via BelongsToMany)
   * and potentially system-wide holidays if that logic is needed.
   * Assumes the Holiday table has a 'date' column for the holiday date.
   *
   * @param string|Carbon $date The date to check for a holiday.
   * @return \App\Models\Holiday|null The Holiday model or null if not found.
   */
  public function getHoliday(string|Carbon $date): ?Holiday // Added parameter and return type hints
  {
    // Ensure the input date is a Carbon instance for easy comparison
    $date = $date instanceof Carbon ? $date->startOfDay() : Carbon::parse($date)->startOfDay();

    // 1. Check for holidays specifically linked to this center via the many-to-many relationship
    $centerHoliday = $this->holidays()
      ->whereDate('date', $date) // Use whereDate for date comparison
      ->first();

    if ($centerHoliday) {
      return $centerHoliday; // Return the center-specific holiday if found
    }

    // 2. Optional: Check for system-wide holidays if center-specific holidays don't exist for the date.
    // This requires a way to query system-wide holidays (e.g., a global scope, a separate relationship on a configuration model, or a dedicated HolidayService).
    // Example assuming a static method on the Holiday model for system-wide holidays:
    // $systemWideHoliday = Holiday::findSystemWideByDate($date); // Assumes findSystemWideByDate method exists

    // if ($systemWideHoliday) {
    //      return $systemWideHoliday; // Return the system-wide holiday if found
    // }


    // If no holiday is found (center-specific or system-wide), return null
    return null;
  }


  /**
   * Check if a given date falls on a weekend for this center.
   * Uses the 'weekends' array attribute.
   *
   * @param string|Carbon $date The date to check.
   * @return bool True if the date is a weekend day for this center, false otherwise.
   */
  public function isWeekend(string|Carbon $date): bool
  {
    // Ensure the input date is a Carbon instance
    $date = $date instanceof Carbon ? $date : Carbon::parse($date);

    // Get the lowercase name of the day (e.g., 'saturday', 'sunday')
    $dayName = strtolower($date->format('l')); // 'l' format gives full day name (Monday, Sunday)

    // Check if the day name is in the 'weekends' array attribute
    return in_array($dayName, $this->weekends ?? []); // Use nullish coalescing in case 'weekends' is null
  }

  /**
   * Check if a given date is a work day for this center (i.e., not a weekend and not a holiday).
   *
   * @param string|Carbon $date The date to check.
   * @return bool True if the date is a work day, false otherwise.
   */
  public function isWorkDay(string|Carbon $date): bool
  {
    // Ensure the input date is a Carbon instance
    $date = $date instanceof Carbon ? $date : Carbon::parse($date);

    // A date is a work day if it's NOT a weekend AND NOT a holiday for this center
    return !$this->isWeekend($date) && !$this->getHoliday($date);
  }


  // Add any other existing methods below this line
}
