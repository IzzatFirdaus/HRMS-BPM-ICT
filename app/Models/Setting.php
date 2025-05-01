<?php

namespace App\Models; // Ensure the namespace is correct for your project

use App\Traits\CreatedUpdatedDeletedBy; // Assuming this trait exists and adds audit FKs/methods
use Illuminate\Database\Eloquent\Factories\HasFactory; // Import HasFactory trait
use Illuminate\Database\Eloquent\Model; // Import base Model class
use Illuminate\Database\Eloquent\SoftDeletes; // Import SoftDeletes trait
use Illuminate\Support\Carbon; // Import Carbon for type hinting with timestamps and date/datetime casts
use Illuminate\Database\Eloquent\Relations\BelongsTo; // Import BelongsTo relationship type (used by CreatedUpdatedDeletedBy trait)
// Removed unused aliased import: BelongsToRelation
// Removed unused import: use Illuminate\Support\Facades\Cache; // Not used directly in the original code example, but good to import if adding caching


/**
 * App\Models\Setting
<<<<<<< HEAD
 *
 * Represents application-wide configuration settings stored in a single database row.
 * Includes various system settings, such as SMS API credentials.
 * This model follows a singleton pattern for easy access to the global settings.
 * Includes audit trails and soft deletion (though soft deleting a singleton might be less common).
=======
 * 
 * Represents application-wide configuration settings, typically stored in a single database row.
 * Includes settings like SMS API credentials.
>>>>>>> cc6eb9f4f020325c04fee080d2466584ff27bb90
 *
 * @property int $id
 * @property string|null $sms_api_sender The sender ID or name for the SMS API (nullable).
 * @property string|null $sms_api_username The username for authentication with the SMS API (nullable).
 * @property string|null $sms_api_password The password for authentication with the SMS API (nullable).
 * // Add other setting properties here based on your database schema
 * @property int|null $created_by Foreign key to the user who created the record.
 * @property int|null $updated_by Foreign key to the user who last updated the record.
 * @property int|null $deleted_by Foreign key to the user who soft deleted the record.
 * @property \Illuminate\Support\Carbon|null $created_at Automatically managed timestamp for creation.
 * @property \Illuminate\Support\Carbon|null $updated_at Automatically managed timestamp for last update.
 * @property \Illuminate\Support\Carbon|null $deleted_at The timestamp when the setting was soft deleted.
 *
 * @property-read \App\Models\User|null $createdBy The user who created the record (if trait adds this).
 * @property-read \App\Models\User|null $deletedBy The user who soft deleted the record (if trait adds this).
 * @property-read \App\Models\User|null $updatedBy The user who last updated the record (if trait adds this).
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Setting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Setting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Setting onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Setting query()
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereDeletedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereSmsApiPassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereSmsApiSender($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereSmsApiUsername($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Setting withoutTrashed()
 * @mixin \Eloquent
 */
class Setting extends Model
{
  // Use the traits for factory, soft deletes, and audit columns
  // HasFactory is for model factories (useful for seeding the initial setting row).
  // SoftDeletes adds the 'deleted_at' column and scope (less common for a singleton model).
  // CreatedUpdatedDeletedBy is assumed to add 'created_by', 'updated_by', 'deleted_by' columns and relationships.
  use CreatedUpdatedDeletedBy, HasFactory, SoftDeletes;

  /**
   * The table associated with the model.
   *
   * @var string
   */
  // protected $table = 'settings'; // Explicitly define table name if it's not the plural of the model name ('settings')


  /**
   * The attributes that are mass assignable.
   * Allows these attributes to be set using mass assignment (e.g., during create or update).
   * 'id' is excluded as it is the primary key.
   *
   * @var array<int, string>
   */
  protected $fillable = [
    'sms_api_sender',   // The sender ID for the SMS API (nullable string)
    'sms_api_username', // The username for the SMS API (nullable string)
    'sms_api_password', // The password for the SMS API (nullable string)
    // Add other setting fields here

    // The CreatedUpdatedDeletedBy trait is assumed to handle these audit columns:
    // 'created_by',
    // 'updated_by',
    // 'deleted_by',
  ];

  /**
   * The attributes that should be cast.
   * Ensures data types are correct and dates are Carbon instances.
   * Includes casts for strings, timestamps, and soft deletes.
   * Explicitly casting all attributes for clarity and type safety.
   *
   * @var array<string, string>
   */
  protected $casts = [
    'sms_api_sender'   => 'string', // Explicitly cast string attributes
    'sms_api_username' => 'string',
    'sms_api_password' => 'string',
    // Add casts for other setting fields here (e.g., 'is_feature_enabled' => 'boolean')

    // Standard Eloquent timestamps handled by base model and traits
    'created_at'       => 'datetime', // Explicitly cast creation timestamp to Carbon instance
    'updated_at'       => 'datetime', // Explicitly cast update timestamp to Carbon instance
    'deleted_at'       => 'datetime', // Cast soft delete timestamp to Carbon instance
    // Add casts for audit FKs if the trait doesn't add them: 'created_by' => 'integer', ...
  ];

  /**
   * The attributes that should be hidden for serialization.
   * (Optional) Prevents sensitive attributes from being returned in JSON responses.
   * Hiding API credentials is a good practice.
   *
   * @var array<int, string>
   */
  protected $hidden = [
    'sms_api_password', // Hide sensitive API password
    // Hide audit columns unless explicitly needed in API responses
    'created_by',
    'updated_by',
    'deleted_by',
    'created_at', // Consider hiding standard timestamps too if not needed
    'updated_at',
    'deleted_at', // Hide soft delete timestamp
  ];


  // The CreatedUpdatedDeletedBy trait is assumed to add these audit relationships.
  // Their docblocks are included in the main class docblock above for clarity.
  // Example docblocks added by the trait:
  /*
        * Get the user who created the model.
        * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\User, \App\Models\Setting>
        */
  // public function createdBy(): BelongsTo;

  /*
        * Get the user who last updated the model.
        * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\User, \App\Models\Setting>
        */
  // public function updatedBy(): BelongsTo;

  /*
        * Get the user who soft deleted the model.
        * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\User, \App\Models\Setting>
        */
  // public function deletedBy(): BelongsTo;


  // No relationships defined on this model itself as it likely stores global settings.
  // It can, however, be related *to* other models (e.g., createdBy, updatedBy relationships from the trait).


  // 👉 Static Helper Methods for Singleton Access

  /**
   * Get the single instance of application settings.
   * This method retrieves the first (and likely only) record from the settings table.
   * It's highly recommended to cache this instance as settings are frequently accessed.
   *
   * @return static|null The Setting model instance, or null if no record exists.
   */
  public static function instance(): ?static // Added return type hint and refined docblock
  {
    // Use Laravel's Cache facade to store the settings instance indefinitely.
    // This minimizes database queries after the first access.
    // Remember to clear the cache (e.g., `php artisan cache:clear`) when settings are updated.
    return \Cache::rememberForever('app_settings', function () {
      return static::first();
    });

    // Without caching:
    // return static::first();
  }

  /**
   * Get a specific setting value by key (using the column name).
   * Retrieves the setting value from the single cached instance.
   * Useful for accessing settings dynamically by their column name.
   *
   * @param string $key The name of the column (setting) to retrieve.
   * @param mixed|null $default Default value to return if the setting or column does not exist.
   * @return mixed The setting value, or the default value if the settings instance is null or the key does not exist.
   */
  public static function get(string $key, mixed $default = null): mixed // Added type hints and refined docblock
  {
    // Retrieve the singleton settings instance (will be cached).
    $settings = static::instance();

    // Use data_get for safe access to properties, handles null settings instance.
    // data_get also supports dot notation for accessing nested data within JSON columns if needed for other settings.
    return data_get($settings, $key, $default);
  }

  // Add specific static methods for commonly accessed settings for type safety and clarity if preferred over the generic get().
  // These leverage the generic get() method internally.

  /**
   * Get the SMS API username setting.
   * Provides a specific, type-hinted getter for a common setting.
   *
   * @return string|null The SMS API username, or null if not set.
   */
  public static function getSmsApiUsername(): ?string // Added return type hint and refined docblock
  {
    // Use the generic get method for consistent access logic
    return static::get('sms_api_username');
  }

  /**
   * Get the SMS API password setting.
   * Note: Be cautious when retrieving sensitive information like passwords.
   * Avoid logging or exposing this value unnecessarily.
   *
   * @return string|null The SMS API password, or null if not set.
   */
  public static function getSmsApiPassword(): ?string // Added specific getter and docblock
  {
    return static::get('sms_api_password');
  }

  /**
   * Get the SMS API sender ID setting.
   *
   * @return string|null The SMS API sender ID, or null if not set.
   */
  public static function getSmsApiSender(): ?string // Added specific getter and docblock
  {
    return static::get('sms_api_sender');
  }


  // Add any other custom static methods here for other specific settings (e.g., getEmailConfig, isFeatureEnabled)


  // Add any other relationships or methods below this line
}
