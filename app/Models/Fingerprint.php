<?php

namespace App\Models;

use App\Traits\CreatedUpdatedDeletedBy; // Assuming this trait exists and adds audit FKs/methods
use Carbon\Carbon; // Used within attribute accessors
use Illuminate\Database\Eloquent\Builder; // Import Builder for scope type hinting
use Illuminate\Database\Eloquent\Casts\Attribute; // Import Attribute for accessor/mutator type hinting
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo; // Use BelongsTo trait
use Illuminate\Database\Eloquent\SoftDeletes; // Use SoftDeletes trait
use Illuminate\Database\Eloquent\Relations\BelongsTo as BelongsToRelation; // Alias if needed

// Import model for BelongsTo relationship
use App\Models\Employee;
use App\Models\User; // For audit columns (handled by trait)


/**
 * App\Models\Fingerprint
 *
 * Represents a daily attendance record for an employee, typically captured via a fingerprint or time clock system.
 * Includes check-in/out times, logs, and related information.
 *
 * @property int $id
 * @property int $employee_id Foreign key to the employee associated with this record.
 * @property \Illuminate\Support\Carbon $date The date of the attendance record.
 * @property string|null $log Raw log data from the fingerprint device (nullable).
 * @property \Illuminate\Support\Carbon|null $check_in The check-in time for the day (nullable timestamp).
 * @property \Illuminate\Support\Carbon|null $check_out The check-out time for the day (nullable timestamp).
 * @property bool $is_checked Flag indicating if the record has been reviewed or processed.
 * @property string|null $excuse Notes or reasons for absence/anomalies (nullable text field).
 * @property int|null $created_by Foreign key to the user who created the record.
 * @property int|null $updated_by Foreign key to the user who last updated the record.
 * @property int|null $deleted_by Foreign key to the user who soft deleted the record.
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at The timestamp when the record was soft deleted.
 * @property-read string $checkIn The formatted check-in time (H:i string).
 * @property-read string $checkOut The formatted check-out time (H:i string).
 * @property-read \App\Models\Employee $employee The employee associated with this record.
 * @property-read \App\Models\User|null $createdBy The user who created the record.
 * @property-read \App\Models\User|null $deletedBy The user who soft deleted the record.
 * @property-read \App\Models\User|null $updatedBy The user who last updated the record.
 * @method static \Illuminate\Database\Eloquent\Builder|Fingerprint newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Fingerprint newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Fingerprint onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Fingerprint query()
 * @method static \Illuminate\Database\Eloquent\Builder|Fingerprint whereCheckIn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Fingerprint whereCheckOut($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Fingerprint whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Fingerprint whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Fingerprint whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Fingerprint whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Fingerprint whereDeletedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Fingerprint whereEmployeeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Fingerprint whereExcuse($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Fingerprint whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Fingerprint whereIsChecked($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Fingerprint whereLog($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Fingerprint whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Fingerprint whereUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Fingerprint withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Fingerprint withoutTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Fingerprint filteredFingerprints(Builder $query, $selectedEmployeeId, $fromDate, $toDate, $isAbsence, $isOneFingerprint)
 * @mixin \Eloquent
 */
class Fingerprint extends Model
{
  use CreatedUpdatedDeletedBy, HasFactory, SoftDeletes;

  /**
   * The attributes that are mass assignable.
   * 'id' is excluded as it is the primary key.
   *
   * @var array<int, string>
   */
  protected $fillable = [
    'employee_id', // Foreign key to employees table
    'date', // Date of the attendance record
    'log', // Raw log data
    'check_in', // Check-in time
    'check_out', // Check-out time
    'is_checked', // Review/processed flag
    'excuse', // Notes/reasons

    // 'created_by', // Handled by trait
    // 'updated_by', // Handled by trait
    // 'deleted_by', // Handled by trait
  ];

  /**
   * The attributes that should be cast.
   * Ensures data types are correct and dates/times are Carbon instances.
   * Includes casts for FK, dates, strings, datetimes, booleans, timestamps, and soft deletes.
   * Explicitly casting all attributes for clarity.
   *
   * @var array<string, string>
   */
  protected $casts = [
    'employee_id' => 'integer', // Cast FK to integer
    'date' => 'date', // Cast date to Carbon instance (YYYY-MM-DD)
    'log' => 'string', // Explicitly cast log as string
    // Cast check_in and check_out as datetime so they are Carbon instances in accessors
    'check_in' => 'datetime',
    'check_out' => 'datetime',
    'is_checked' => 'boolean', // Cast boolean flag
    'excuse' => 'string', // Explicitly cast excuse as string

    'created_at' => 'datetime', // Explicitly cast timestamps
    'updated_at' => 'datetime',
    'deleted_at' => 'datetime', // Cast soft delete timestamp
  ];


  // The CreatedUpdatedDeletedBy trait is assumed to add these audit relationships:

  /**
   * Get the user who created the model.
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\User, \App\Models\Fingerprint>
   */
  // public function createdBy(): BelongsToRelation;

  /**
   * Get the user who last updated the model.
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\User, \App\Models\Fingerprint>
   */
  // public function updatedBy(): BelongsToRelation;

  /**
   * Get the user who soft deleted the model.
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\User, \App\Models\Fingerprint>
   */
  // public function deletedBy(): BelongsToRelation;


  // 👉 Relationships

  /**
   * Get the employee associated with this timeline entry.
   * Defines a many-to-one relationship where a Fingerprint record belongs to one Employee.
   * Assumes the 'fingerprints' table has an 'employee_id' foreign key.
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Employee, \App\Models\Fingerprint>
   */
  public function employee(): BelongsTo // Added return type hint
  {
    // Defines a many-to-one relationship with the Employee model
    return $this->belongsTo(Employee::class, 'employee_id'); // Explicitly define FK for clarity
  }


  // 👉 Attributes (Accessors)

  /**
   * Get the formatted check-in time (H:i string).
   * Accesses the 'check_in' attribute, which is cast to a Carbon instance.
   *
   * @return string The formatted time string, or an empty string if check-in is null.
   */
  protected function checkIn(): Attribute // Added return type hint and refined docblock
  {
    return Attribute::make(
      // $value is a Carbon instance or null due to the 'datetime' cast
      get: fn(?Carbon $value) => $value !== null ? $value->format('H:i') : '',
    );
  }

  /**
   * Get the formatted check-out time (H:i string).
   * Accesses the 'check_out' attribute, which is cast to a Carbon instance.
   *
   * @return string The formatted time string, or an empty string if check-out is null.
   */
  protected function checkOut(): Attribute // Added return type hint and refined docblock
  {
    return Attribute::make(
      // $value is a Carbon instance or null due to the 'datetime' cast
      get: fn(?Carbon $value) => $value !== null ? $value->format('H:i') : '',
    );
  }


  // 👉 Scopes

  /**
   * Scope a query to filter fingerprint records.
   * Filters by employee, date range, absence (log is null), and records with only check-in (one fingerprint).
   *
   * @param \Illuminate\Database\Eloquent\Builder $query The Eloquent query builder instance.
   * @param int $selectedEmployeeId The ID of the employee to filter by.
   * @param string $fromDate The start date of the date range (YYYY-MM-DD string or Carbon instance).
   * @param string $toDate The end date of the date range (YYYY-MM-DD string or Carbon instance).
   * @param bool $isAbsence Whether to filter for records where log is null (absence).
   * @param bool $isOneFingerprint Whether to filter for records with only check-in (check_in not null, check_out null).
   * @return \Illuminate\Database\Eloquent\Builder The filtered query builder instance.
   */
  public function scopeFilteredFingerprints(
    Builder $query,
    int $selectedEmployeeId, // Added type hint
    string $fromDate, // Added type hint (assuming string input, cast to date handles parsing)
    string $toDate, // Added type hint
    bool $isAbsence, // Added type hint
    bool $isOneFingerprint // Added type hint
  ): Builder { // Changed return type hint to Builder
    return $query
      ->where('employee_id', $selectedEmployeeId)
      // Casting 'date' to date in $casts ensures whereBetween works correctly with date strings
      ->whereBetween('date', [$fromDate, $toDate])
      ->when($isAbsence, function (Builder $query) { // Added type hint for nested query builder
        return $query->whereNull('log');
      })
      ->when($isOneFingerprint, function (Builder $query) { // Added type hint for nested query builder
        return $query->whereNotNull('check_in')->whereNull('check_out');
      })
      ->orderBy('date'); // Order by date to get records chronologically
  }

  // Add any other custom methods or accessors/mutators below this line
}
