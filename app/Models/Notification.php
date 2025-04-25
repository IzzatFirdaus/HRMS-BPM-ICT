<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory; // Optional, if you need factories for this model
use App\Models\User; // Import User model

class Notification extends Model
{
  // use HasFactory; // Uncomment if you need factories for custom notifications

  /**
   * The attributes that are mass assignable.
   *
   * @var array<int, string>
   */
  protected $fillable = [
    'user_id', // The ID of the user who should receive the notification
    'type',    // The notification class name
    'data',    // The notification data as a JSON encoded array
    'read_at', // The timestamp when the notification was marked as read
    // 'id', // Laravel's default notifications use UUIDs, but this model assumes integer IDs unless specified otherwise
    // 'notifiable_type', // If polymorphic, link to different model types
    // 'notifiable_id', // If polymorphic
  ];

  /**
   * The attributes that should be cast.
   *
   * @var array<string, string>
   */
  protected $casts = [
    'data' => 'json', // Cast the data column to a JSON array
    'read_at' => 'datetime', // Cast the read_at column to a Carbon instance
  ];

  /**
   * The table associated with the model.
   *
   * @var string
   */
  // protected $table = 'my_custom_notifications_table'; // Uncomment and set if your table is not named 'notifications'


  /**
   * Get the user that the notification belongs to.
   * This assumes notifications are linked directly to users via 'user_id'.
   * If using polymorphic 'notifiable', you would use the 'notifiable' relationship below.
   */
  public function user()
  {
    return $this->belongsTo(User::class, 'user_id'); // Explicitly define foreign key
  }

  /**
   * Get the notifiable entity that the notification belongs to.
   * Uncomment and implement if your notifications table uses polymorphic columns (notifiable_type, notifiable_id).
   */
  // public function notifiable()
  // {
  //     return $this->morphTo();
  // }

  // Add custom methods or accessors/mutators here as needed

  /**
   * Mark the notification as read.
   *
   * @return bool
   */
  public function markAsRead()
  {
    return $this->update(['read_at' => now()]);
  }

  /**
   * Mark the notification as unread.
   *
   * @return bool
   */
  public function markAsUnread()
  {
    return $this->update(['read_at' => null]);
  }

  /**
   * Determine if the notification has been read.
   *
   * @return bool
   */
  public function read()
  {
    return $this->read_at !== null;
  }

  /**
   * Determine if the notification has not been read.
   *
   * @return bool
   */
  public function unread()
  {
    return $this->read_at === null;
  }
}
