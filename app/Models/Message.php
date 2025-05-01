<?php

namespace App\Models;

use App\Traits\CreatedUpdatedDeletedBy; // Assuming this trait exists and adds audit FKs/methods
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo; // Use BelongsTo trait
use Illuminate\Database\Eloquent\SoftDeletes; // Use SoftDeletes trait
use Illuminate\Support\Carbon; // Import Carbon for type hinting with datetime casts
use Illuminate\Database\Eloquent\Relations\BelongsTo as BelongsToRelation; // Alias if needed

// Import model for BelongsTo relationship
use App\Models\Employee;
use App\Models\User; // For linking sender photo via updated_by and for audit columns (handled by trait)


/**
 * App\Models\Message
 * 
 * Represents a message record, potentially for notifications sent to employees (e.g., via SMS).
 * Linked to an Employee and tracks message details, recipient, status, and sender photo based on the updater.
 *
 * @property int $id
 * @property int $employee_id Foreign key to the employee who is the recipient or subject of the message.
 * @property string $text The content of the message.
 * @property string|null $recipient The recipient identifier (e.g., phone number, email address, user ID).
 * @property bool $is_sent Flag indicating if the message was successfully sent.
 * @property string|null $error Any error message if sending failed (nullable).
 * @property int|null $created_by Foreign key to the user who created the record.
 * @property int|null $updated_by Foreign key to the user who last updated the record (often the sender in simple setups).
 * @property int|null $deleted_by Foreign key to the user who soft deleted the record.
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at The timestamp when the message record was soft deleted.
 * @property-read \App\Models\Employee $employee The employee associated with this message record.
 * @property-read string $messageSenderPhoto The URL or path to the profile photo of the message sender (based on updated_by).
 * @property-read \App\Models\User|null $createdBy The user who created the record.
 * @property-read \App\Models\User|null $deletedBy The user who soft deleted the record.
 * @property-read \App\Models\User|null $updatedBy The user who last updated the record.
 * @method static \Illuminate\Database\Eloquent\Builder|Message newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Message newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Message onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Message query()
 * @method static \Illuminate\Database\Eloquent\Builder|Message whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Message whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Message whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Message whereDeletedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Message whereEmployeeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Message whereError($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Message whereIsSent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Message whereRecipient($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Message whereText($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Message whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Message whereUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Message withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Message withoutTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Message whereId($value)
 * @mixin \Eloquent
 */
class Message extends Model
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
    'text', // The content of the message
    'recipient', // The recipient identifier (e.g., phone number, email address)
    'is_sent', // Flag indicating if the message was successfully sent
    'error', // Any error message if sending failed

    // 'created_by', // Handled by trait
    // 'updated_by', // Handled by trait
    // 'deleted_by', // Handled by trait
  ];

  /**
   * The attributes that should be cast.
   * Ensures data types are correct.
   * Includes casts for FK, strings, booleans, timestamps, and soft deletes.
   * Explicitly casting all attributes for clarity.
   *
   * @var array<string, string>
   */
  protected $casts = [
    'employee_id' => 'integer', // Cast FK to integer

    'text' => 'string', // Explicitly cast string attributes
    'recipient' => 'string', // Recipient identifier (e.g., phone number)
    'error' => 'string', // Error message

    'is_sent' => 'boolean', // Cast boolean flag

    'created_at' => 'datetime', // Explicitly cast timestamps
    'updated_at' => 'datetime',
    'deleted_at' => 'datetime', // Cast soft delete timestamp
  ];


  // The CreatedUpdatedDeletedBy trait is assumed to add these audit relationships:

  /**
   * Get the user who created the model.
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\User, \App\Models\Message>
   */
  // public function createdBy(): BelongsToRelation;

  /**
   * Get the user who last updated the model.
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\User, \App\Models\Message>
   */
  // public function updatedBy(): BelongsToRelation;

  /**
   * Get the user who soft deleted the model.
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\User, \App\Models\Message>
   */
  // public function deletedBy(): BelongsToRelation;


  // 👉 Relationships

  /**
   * Get the employee associated with this message record.
   * Defines a many-to-one relationship where a Message belongs to one Employee.
   * Assumes the 'messages' table has an 'employee_id' foreign key.
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Employee, \App\Models\Message>
   */
  public function employee(): BelongsTo // Added return type hint
  {
    // Defines a many-to-one relationship with the Employee model
    return $this->belongsTo(Employee::class, 'employee_id'); // Explicitly define FK for clarity
  }


  // 👉 Accessors or Helper Methods

  /**
   * Get the profile photo URL/path of the user who last updated this message record.
   * This method assumes the user who updated the record is the 'sender' in this context.
   * Queries the User model based on the 'updated_by' foreign key.
   *
   * @return string The URL or path to the sender's profile photo, or a default administrator photo path if not found.
   */
  public function getMessageSenderPhoto(): string // Added return type hint and refined docblock
  {
    // Find the user who last updated this message record using the 'updated_by' foreign key.
    // Use optional chaining (?->) to safely access the profile_photo_path if the sender or path is null.
    $senderPhotoPath = $this->updatedBy?->profile_photo_path; // Assuming 'updatedBy' relationship is defined by trait and User has 'profile_photo_path'

    // Check if a sender photo path was found.
    if ($senderPhotoPath) {
      // Prepend 'storage/' if necessary to form a public URL (depending on storage configuration)
      // This might need adjustment based on your specific file storage setup (e.g., using Storage facade URL).
      return 'storage/' . $senderPhotoPath; // Assuming 'storage/' prefix is needed for public access
    }

    // Return a default administrator photo path if no sender photo is found.
    return 'storage/profile-photos/.administrator.jpg'; // Ensure this path is correct and accessible
  }

  // Add any other custom methods or accessors/mutators below this line
}
