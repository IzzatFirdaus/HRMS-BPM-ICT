<?php

namespace App\Models;

use App\Traits\CreatedUpdatedDeletedBy; // Assuming this trait exists and adds audit FKs/methods
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; // Use SoftDeletes trait
use Illuminate\Support\Carbon; // Import Carbon for type hinting with datetime casts
use Illuminate\Database\Eloquent\Relations\BelongsTo; // Import BelongsTo for CreatedUpdatedDeletedBy trait


/**
 * App\Models\Import
 *
 * Represents a record of a file import process.
 * Tracks file details, import status, progress, and details (e.g., errors).
 *
 * @property int $id
 * @property string $file_name The original name of the imported file.
 * @property int $file_size The size of the imported file in bytes.
 * @property string $file_ext The file extension (e.g., 'xlsx', 'csv').
 * @property string $file_type The type of data being imported (e.g., 'employees', 'equipment').
 * @property string $status The current status of the import process (e.g., 'pending', 'processing', 'completed', 'failed').
 * @property array|null $details Details about the import process, often including errors (JSON or text, here cast to JSON).
 * @property int $current The number of rows/items processed so far.
 * @property int $total The total number of rows/items to process.
 * @property int|null $created_by Foreign key to the user who initiated the import.
 * @property int|null $updated_by Foreign key to the user who last updated the import record.
 * @property int|null $deleted_by Foreign key to the user who soft deleted the import record.
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at The timestamp when the import record was soft deleted.
 * @property-read \App\Models\User|null $createdBy The user who created the record.
 * @property-read \App\Models\User|null $deletedBy The user who soft deleted the record.
 * @property-read \App\Models\User|null $updatedBy The user who last updated the record.
 * @method static \Illuminate\Database\Eloquent\Builder|Import newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Import newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Import onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Import query()
 * @method static \Illuminate\Database\Eloquent\Builder|Import whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Import whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Import whereCurrent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Import whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Import whereDeletedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Import whereDetails($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Import whereFileExt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Import whereFileName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Import whereFileSize($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Import whereFileType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Import whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Import whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Import whereTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Import whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Import whereUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Import withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Import withoutTrashed()
 * @mixin \Eloquent
 */
class Import extends Model
{
  use CreatedUpdatedDeletedBy, HasFactory, SoftDeletes;

  // Define constants for import statuses for better code readability and maintainability
  public const STATUS_PENDING = 'pending'; // Import queued or waiting to start
  public const STATUS_PROCESSING = 'processing'; // Import is currently running
  public const STATUS_COMPLETED = 'completed'; // Import finished successfully
  public const STATUS_FAILED = 'failed'; // Import failed due to errors
  public const STATUS_CANCELLED = 'cancelled'; // Import was manually cancelled

  /**
   * The attributes that are mass assignable.
   * 'id' is excluded as it is the primary key.
   *
   * @var array<int, string>
   */
  protected $fillable = [
    'file_name', // The original name of the imported file
    'file_size', // The size of the imported file in bytes
    'file_ext', // The file extension
    'file_type', // The type of data being imported
    'status', // The current status of the import process
    'details', // Details about the import process (e.g., errors)
    'current', // The number of rows/items processed so far
    'total', // The total number of rows/items to process

    // 'created_by', // Handled by trait
    // 'updated_by', // Handled by trait
    // 'deleted_by', // Handled by trait
  ];

  /**
   * The attributes that should be cast.
   * Ensures data types are correct, especially for progress counters and JSON details.
   * Includes casts for strings, integers, JSON, timestamps, and soft deletes.
   * Explicitly casting all attributes for clarity.
   *
   * @var array<string, string>
   */
  protected $casts = [
    'file_name' => 'string', // Explicitly cast string attributes
    'file_ext' => 'string',
    'file_type' => 'string',
    'status' => 'string', // Import status

    'file_size' => 'integer', // Cast file size to integer
    'current' => 'integer', // Cast progress counters to integer
    'total' => 'integer',

    'details' => 'json', // Cast details column to a JSON array

    'created_at' => 'datetime', // Explicitly cast timestamps
    'updated_at' => 'datetime',
    'deleted_at' => 'datetime', // Cast soft delete timestamp
  ];

  // The CreatedUpdatedDeletedBy trait is assumed to add these audit relationships:

  /**
   * Get the user who created the model.
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\User, \App\Models\Import>
   */
  // public function createdBy(): BelongsToRelation;

  /**
   * Get the user who last updated the model.
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\User, \App\Models\Import>
   */
  // public function updatedBy(): BelongsToRelation;

  /**
   * Get the user who soft deleted the model.
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\User, \App\Models\Import>
   */
  // public function deletedBy(): BelongsToRelation;


  // No relationships defined on this model as it primarily tracks the import process itself.


  // Add custom methods or accessors/mutators here as needed

  /**
   * Check if the import process is currently pending.
   * Checks the status column against the STATUS_PENDING constant.
   *
   * @return bool True if the status is pending, false otherwise.
   */
  public function isPending(): bool
  {
    return $this->status === self::STATUS_PENDING; // Use constant
  }

  /**
   * Check if the import process is currently running.
   * Checks the status column against the STATUS_PROCESSING constant.
   *
   * @return bool True if the status is processing, false otherwise.
   */
  public function isProcessing(): bool
  {
    return $this->status === self::STATUS_PROCESSING; // Use constant
  }

  /**
   * Check if the import process has been completed successfully.
   * Checks the status column against the STATUS_COMPLETED constant.
   *
   * @return bool True if the status is completed, false otherwise.
   */
  public function isCompleted(): bool
  {
    return $this->status === self::STATUS_COMPLETED; // Use constant
  }

  /**
   * Check if the import process has failed.
   * Checks the status column against the STATUS_FAILED constant.
   *
   * @return bool True if the status is failed, false otherwise.
   */
  public function isFailed(): bool
  {
    return $this->status === self::STATUS_FAILED; // Use constant
  }

  /**
   * Check if the import process has been cancelled.
   * Checks the status column against the STATUS_CANCELLED constant.
   *
   * @return bool True if the status is cancelled, false otherwise.
   */
  public function isCancelled(): bool
  {
    return $this->status === self::STATUS_CANCELLED; // Use constant
  }

  /**
   * Get the completion percentage of the import process.
   * Calculates percentage based on 'current' and 'total'.
   * Returns 0 if total is 0 or less.
   *
   * @return float The completion percentage (0.0 to 100.0).
   */
  public function getCompletionPercentageAttribute(): float
  {
    if ($this->total <= 0) {
      return 0.0;
    }
    return round(($this->current / $this->total) * 100, 2); // Calculate and round to 2 decimal places
  }

  /**
   * Get the translated status string.
   *
   * @return string The translated status.
   */
  public function getStatusTranslatedAttribute(): string
  {
    return match ($this->status) {
      self::STATUS_PENDING => __('Pending'),
      self::STATUS_PROCESSING => __('Processing'),
      self::STATUS_COMPLETED => __('Completed'),
      self::STATUS_FAILED => __('Failed'),
      self::STATUS_CANCELLED => __('Cancelled'),
      default => $this->status, // Return raw status if unknown
    };
  }
}
