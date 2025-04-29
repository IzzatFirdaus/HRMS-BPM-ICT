<?php

namespace App\Notifications;

use App\Models\User; // Import User model
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
// ShouldQueue interface is not implemented as this notification only uses the 'database' channel
// use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * Class DefaultNotification
 *
 * A default notification intended for storage in the database, typically for in-app display.
 * Provides basic information about a user and a message.
 */
class DefaultNotification extends Notification // Should not implement ShouldQueue if only using 'database' channel
{
  use Queueable; // Keep Queueable trait even if not implementing ShouldQueue, it can still be useful

  /**
   * The user model instance associated with this notification.
   *
   * @var \App\Models\User
   */
  private User $user; // Property to hold the User model

  /**
   * The message content for the notification.
   *
   * @var string
   */
  private string $message; // Property to hold the message string


  /**
   * Create a new notification instance.
   *
   * @param \App\Models\User $user The user associated with the notification.
   * @param string $message The message content.
   * @return void
   */
  public function __construct(User $user, string $message) // Constructor accepts User model and message
  {
    $this->user = $user; // Assign the User model instance
    $this->message = $message; // Assign the message
  }

  /**
   * Get the notification's delivery channels.
   * This notification is configured to be stored in the database only.
   *
   * @param object $notifiable The notifiable entity (typically the recipient User model instance).
   * @return array<int, string> An array containing the channel name 'database'.
   */
  public function via(object $notifiable): array // Added type hint for notifiable, refined docblock
  {
    return ['database']; // Specify that this notification should be stored in the database
  }

  /**
   * Get the array representation of the notification.
   * This data will be stored in the 'data' column of the 'notifications' table.
   *
   * @param object $notifiable The notifiable entity (typically the recipient User model instance).
   * @return array<string, mixed> An array of data to be stored.
   */
  public function toArray(object $notifiable): array // Added type hint for notifiable, specified return type, refined docblock
  {
    // Access user data directly from the stored User model property.
    // Use optional chaining (?->) for safe access to nested properties/accessors.
    $employeeFullName = $this->user?->EmployeeFullName; // Assuming EmployeeFullName accessor exists on User
    $employeeId = $this->user?->employee_id; // Assuming employee_id attribute exists on User
    $profilePhotoUrl = $this->user?->profile_photo_url; // Assuming profile_photo_url accessor exists on User (from HasProfilePhoto)

    return [
      'user_name' => $employeeFullName, // Store the full name
      'employee_id' => $employeeId, // Store the employee ID
      'image_url' => $profilePhotoUrl, // Store the profile photo URL
      'message' => $this->message, // Store the message content
      // Add any other data relevant to this default notification type
    ];
  }

  // Implement toDatabase method if you need custom logic for database storage beyond toArray.
  // public function toDatabase(object $notifiable): array { ... }
}
