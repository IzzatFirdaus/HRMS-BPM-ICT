<?php

namespace App\Models;

use App\Traits\CreatedUpdatedDeletedBy;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection; // Import Collection for type hinting

// Import models for relationships
use App\Models\Timeline;
use App\Models\Holiday;
use App\Models\Employee;
use App\Models\User; // Uncommented this import, assuming audit trait uses it


/**
 * Class Center
 *
 * Represents an administrative or physical center within the organization.
 * Manages relationships with Timelines (employee assignments), Holidays,
 * and provides methods for checking work hours, weekends, and associated employees.
 *
 * @property int $id
 * @property string $name
 * @property string|null $start_work_hour
 * @property string|null $end_work_hour
 * @property array<array-key, mixed> $weekends // Cast to array
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
 * @property-read string $weekends_formatted // Custom accessor
 * @property-read \Illuminate\Database\Eloquent\Collection<\App\Models\Employee> $activeEmployees // Custom method
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
 */
class Center extends Model
{
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
    'weekends', // Should be an array of day numbers (0-6) or strings ('saturday') depending on how you want to store
    'is_active',
    // Add other fillable attributes here
  ];

  /**
   * The attributes that should be cast.
   *
   * @var array<string, string>
   */
  protected $casts = [
    'weekends' => 'array', // Cast 'weekends' attribute to array
    'is_active' => 'boolean',
    'created_at' => 'datetime',
    'updated_at' => 'datetime',
    'deleted_at' => 'datetime',
  ];

  /**
   * The attributes that should be hidden for serialization.
   *
   * @var array<int, string>
   */
  // protected $hidden = [];


  // The CreatedUpdatedDeletedBy trait is assumed to add these audit relationships:
  // public function createdBy(): BelongsTo;
  // public function updatedBy(): BelongsTo;
  // public function deletedBy(): BelongsTo;


  // 👉 Relationships

  /**
   * Get the timelines associated with the center.
   *
   * @return \Illuminate\Database\Eloquent\Relations\HasMany
   */
  public function timelines(): HasMany
  {
    return $this->hasMany(Timeline::class, 'center_id');
  }

  /**
   * Get employees currently or previously assigned to this center via their timelines.
   *
   * @return \Illuminate\Database\Eloquent\Relations\HasManyThrough
   */
  // public function employees(): HasManyThrough { ... }


  /**
   * Get the holidays associated with the center.
   * Defines a many-to-many relationship with the Holiday model via a pivot table.
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
   */
  public function holidays(): BelongsToMany
  {
    return $this->belongsToMany(Holiday::class, 'center_holiday', 'center_id', 'holiday_id');
    // ->withPivot('date', 'notes') // Example: Include pivot table columns
    // ->withTimestamps(); // Example: If pivot table timestamps are used
  }


  // 👉 Attributes (Accessors/Mutators using Attributes)

  /**
   * Accessor to format the center's start work hour.
   *
   * @return \Illuminate\Database\Eloquent\Casts\Attribute
   */
  protected function startWorkHour(): Attribute
  {
    return Attribute::make(
      get: fn(?string $value) => $value ? Carbon::parse($value)->format('H:i') : null,
      set: fn(?string $value) => $value, // Store as provided time string (e.g., '08:00')
    );
  }

  /**
   * Accessor to format the center's end work hour.
   *
   * @return \Illuminate\Database\Eloquent\Casts\Attribute
   */
  protected function endWorkHour(): Attribute
  {
    return Attribute::make(
      get: fn(?string $value) => $value ? Carbon::parse($value)->format('H:i') : null,
      set: fn(?string $value) => $value, // Store as provided time string (e.g., '17:00')
    );
  }

  /**
   * Accessor to get the formatted weekend days string.
   * Assumes the 'weekends' attribute is an array of integer day numbers (0 for Sunday, 6 for Saturday).
   *
   * @return \Illuminate\Database\Eloquent\Casts\Attribute
   */
  protected function weekendsFormatted(): Attribute
  {
    return Attribute::make(
      get: function (?array $weekends) { // Accept nullable array
        if (empty($weekends)) {
          return __('Tiada'); // Return 'None' or similar if no weekends
        }

        $dayNames = [];
        // Map day numbers (0-6) to abbreviated day names (Sun, Mon, etc.)
        foreach ($weekends as $dayNumber) {
          // Ensure $dayNumber is a valid integer before using
          if (is_numeric($dayNumber) && $dayNumber >= 0 && $dayNumber <= 6) {
            // Carbon::parse("Sunday") creates a Carbon instance for the start of the week (Sunday is day 0)
            $dayNames[] = Carbon::parse("Sunday")->addDays((int) $dayNumber)->format('D'); // 'D' for abbreviated name
            // Use format('l') for full name (e.g., 'Sunday') if preferred
          }
        }

        return implode(', ', $dayNames); // Join day names with comma and space
      },
      // No mutator needed if storing as array and relying on $casts
    );
  }


  // 👉 Functions / Custom Query Scopes

  /**
   * Get the active employees currently assigned to this center via timeline.
   *
   * @return \Illuminate\Database\Eloquent\Collection<\App\Models\Employee>
   */
  public function activeEmployees(): Collection
  {
    return Employee::whereHas('timelines', function ($query) {
      $query->where('center_id', $this->id)
        ->whereNull('end_date');
    })
      ->where('is_active', true)
      ->orderBy('first_name', 'asc')
      ->get();
  }

  /**
   * Find a holiday associated with this center for a specific date.
   *
   * @param string|Carbon $date The date to check for a holiday.
   * @return \App\Models\Holiday|null The Holiday model or null if not found.
   */
  public function getHoliday(string|Carbon $date): ?Holiday
  {
    $date = $date instanceof Carbon ? $date->startOfDay() : Carbon::parse($date)->startOfDay();

    $centerHoliday = $this->holidays()
      ->whereDate('date', $date)
      ->first();

    if ($centerHoliday) {
      return $centerHoliday;
    }

    // Optional: Check for system-wide holidays here if needed
    // $systemWideHoliday = Holiday::findSystemWideByDate($date);
    // if ($systemWideHoliday) { return $systemWideHoliday; }

    return null;
  }


  /**
   * Check if a given date falls on a weekend for this center.
   * Uses the 'weekends' array attribute (assumed to contain day numbers 0-6).
   *
   * @param string|Carbon $date The date to check.
   * @return bool True if the date is a weekend day for this center, false otherwise.
   */
  public function isWeekend(string|Carbon $date): bool
  {
    $date = $date instanceof Carbon ? $date : Carbon::parse($date);

    // Get the day of the week as an integer (0 for Sunday, 6 for Saturday)
    $dayOfWeek = $date->dayOfWeek; // Returns 0 (Sun) to 6 (Sat)

    // Check if the day number is in the 'weekends' array attribute (which is cast to array)
    // The attribute is an array of numbers (e.g., [0, 6] for Sun and Sat)
    return in_array($dayOfWeek, $this->weekends ?? []); // Use nullish coalescing in case 'weekends' is null/empty after cast
  }

  /**
   * Check if a given date is a work day for this center (i.e., not a weekend and not a holiday).
   *
   * @param string|Carbon $date The date to check.
   * @return bool True if the date is a work day, false otherwise.
   */
  public function isWorkDay(string|Carbon $date): bool
  {
    $date = $date instanceof Carbon ? $date : Carbon::parse($date);

    return !$this->isWeekend($date) && !$this->getHoliday($date);
  }


  // Add any other existing methods below this line
}
