<?php

namespace App\Models;

use App\Traits\CreatedUpdatedDeletedBy; // Assuming this trait exists and adds audit FKs/methods
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory; // Optional, if you need factories for this model
use Illuminate\Database\Eloquent\Relations\BelongsTo; // Use BelongsTo trait
use Illuminate\Database\Eloquent\SoftDeletes; // Use SoftDeletes trait
use Illuminate\Support\Carbon; // Import Carbon for type hinting with timestamps
use Illuminate\Database\Eloquent\Relations\BelongsTo as BelongsToRelation; // Alias if needed
use Illuminate\Database\Eloquent\Relations\MorphTo; // Import MorphTo for polymorphic relationship (if used)

// Import model for relationships
use App\Models\User; // Import User model


/**
 * App\Models\Notification
 *
 * Represents a custom notification record for a user.
 * Stores notification data and read status. Can potentially support polymorphic notifications.
 *
 * @property string $id The primary key for the notification (often a UUID in Laravel's default).
 * @property string $type The notification class name.
 * @property array $data The notification data as a JSON encoded array.
 * @property \Illuminate\Support\Carbon|null $read_at The timestamp when the notification was marked as read.
 * @property int $user_id The ID of the user who should receive the notification (if not using polymorphic).
 * @property string|null $notifiable_type If polymorphic, the model type of the notifiable entity.
 * @property int|string|null $notifiable_id If polymorphic, the ID of the notifiable entity.
 * @property int|null $created_by Foreign key to the user who created the record (if applicable).
 * @property int|null $updated_by Foreign key to the user who last updated the record (if applicable).
 * @property int|null $deleted_by Foreign key to the user who soft deleted the record (if applicable).
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at The timestamp when the notification was soft deleted.
 * @property-read \App\Models\User $user The user that the notification belongs to (if not using polymorphic 'notifiable').
 * @property-read Model|\Eloquent $notifiable The notifiable entity that the notification belongs to (if using polymorphic).
 * @property-read \App\Models\User|null $createdBy The user who created the record.
 * @property-read \App\Models\User|null $deletedBy The user who soft deleted the record.
 * @property-read \App\Models\User|null $updatedBy The user who last updated the record.
 * @method static \Illuminate\Database\Eloquent\Builder|Notification newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Notification newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Notification onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Notification query()
 * @method static \Illuminate\Database\Eloquent\Builder|Notification whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notification whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notification whereData($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notification whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notification whereDeletedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notification whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notification whereNotifiableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notification whereNotifiableType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notification whereReadAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notification whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notification whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notification whereUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notification whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notification withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Notification withoutTrashed()
 * @mixin \Eloquent
 */
class Notification extends Model
{
  // use HasFactory; // Uncomment if you need factories for custom notifications
  // Use SoftDeletes trait for soft deletion
  use SoftDeletes;
  // Assuming CreatedUpdatedDeletedBy trait exists and adds audit FKs/methods
  use CreatedUpdatedDeletedBy;


  /**
   * The attributes that are mass assignable.
   * Includes fields from the migration, focusing on linking to a user and storing data.
   * 'id' is typically not mass assignable as it's the primary key.
   *
   * @var array<int, string>
   */
  protected $fillable = [
    'user_id', // The ID of the user who should receive the notification
    'type', // The notification class name
    'data', // The notification data as a JSON encoded array
    'read_at', // The timestamp when the notification was marked as read
    // 'notifiable_type', // If polymorphic, link to different model types - include if mass assignable
    // 'notifiable_id', // If polymorphic - include if mass assignable
    // 'created_by', // Handled by trait
    // 'updated_by', // Handled by trait
    // 'deleted_by', // Handled by trait
  ];

  /**
   * The attributes that should be cast.
   * Ensures data types are correct, especially for JSON and dates.
   *
   * @var array<string, string>
   */
  protected $casts = [
    'user_id' => 'integer', // Cast user_id to integer
    'type' => 'string', // Explicitly cast type as string
    'data' => 'json', // Cast the data column to a JSON array
    'read_at' => 'datetime', // Cast the read_at column to a Carbon instance
    'created_at' => 'datetime', // Explicitly cast timestamps
    'updated_at' => 'datetime',
    'deleted_at' => 'datetime', // Cast soft delete timestamp
    // If using polymorphic IDs that might be UUIDs or integers:
    // 'notifiable_id' => 'string', // Or 'integer' depending on the notifiable model's primary key type
    // 'notifiable_type' => 'string',
  ];

  /**
   * The table associated with the model.
   * Uncomment and set if your custom notifications table is not named 'notifications'.
   *
   * @var string
   */
  // protected $table = 'my_custom_notifications_table';


  // The CreatedUpdatedDeletedBy trait is assumed to add these audit relationships:

  /**
   * Get the user who created the model.
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\User, \App\Models\Notification>
   */
  // public function createdBy(): BelongsToRelation;

  /**
   * Get the user who last updated the model.
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\User, \App\Models\Notification>
   */
  // public function updatedBy(): BelongsToRelation;

  /**
   * Get the user who soft deleted the model.
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\User, \App\Models\Notification>
   */
  // public function deletedBy(): BelongsToRelation;


  // 👉 Relationships

  /**
   * Get the user that the notification belongs to.
   * This assumes notifications are linked directly to users via 'user_id'.
   * If using polymorphic 'notifiable', you would use the 'notifiable' relationship below.
   * Defines a many-to-one relationship where a Notification belongs to one User.
   * Assumes the notifications table has a 'user_id' foreign key.
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\User, \App\Models\Notification>
   */
  public function user(): BelongsTo // Added return type hint
  {
    // Defines a many-to-one relationship with the User model
    return $this->belongsTo(User::class, 'user_id'); // Explicitly define foreign key
  }

  /**
   * Get the notifiable entity that the notification belongs to.
   * Uncomment and implement if your notifications table uses polymorphic columns (notifiable_type, notifiable_id).
   * Defines a polymorphic relationship where a Notification can belong to different types of notifiable models.
   * Assumes the notifications table has 'notifiable_id' and 'notifiable_type' columns.
   *
   * @return \Illuminate\Database\Eloquent\Relations\MorphTo
   */
  // public function notifiable(): MorphTo // Added return type hint
  // {
  //     return $this->morphTo();
  // }


  // 👉 Helper Methods (Read Status)

  /**
   * Mark the notification as read by setting the 'read_at' timestamp to now.
   *
   * @return bool True on success, false on failure.
   */
  public function markAsRead(): bool // Added return type hint and refined docblock
  {
    return $this->update(['read_at' => now()]);
  }

  /**
   * Mark the notification as unread by setting the 'read_at' timestamp to null.
   *
   * @return bool True on success, false on failure.
   */
  public function markAsUnread(): bool // Added return type hint and refined docblock
  {
    return $this->update(['read_at' => null]);
  }

  /**
   * Determine if the notification has been read.
   * Checks if the 'read_at' timestamp is not null.
   *
   * @return bool True if read, false if unread.
   */
  public function read(): bool // Added return type hint and refined docblock
  {
    return $this->read_at !== null;
  }

  /**
   * Determine if the notification has not been read.
   * Checks if the 'read_at' timestamp is null.
   *
   * @return bool True if unread, false if read.
   */
  public function unread(): bool // Added return type hint and refined docblock
  {
    return $this->read_at === null;
  }

  // Add custom methods or accessors/mutators here as needed
}
