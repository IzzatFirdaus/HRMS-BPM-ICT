<?php

namespace App\Models;

use App\Traits\CreatedUpdatedDeletedBy; // Assuming this trait exists and adds audit FKs/methods
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; // Use SoftDeletes trait
use Illuminate\Support\Carbon; // Import Carbon for type hinting with timestamps
use Illuminate\Database\Eloquent\Relations\BelongsTo; // Import BelongsTo for CreatedUpdatedDeletedBy trait


/**
 * App\Models\Setting
 *
 * Represents application-wide configuration settings, typically stored in a single database row.
 * Includes settings like SMS API credentials.
 *
 * @property int $id
 * @property string|null $sms_api_sender The sender ID for the SMS API.
 * @property string|null $sms_api_username The username for the SMS API.
 * @property string|null $sms_api_password The password for the SMS API.
 * @property int|null $created_by Foreign key to the user who created the record.
 * @property int|null $updated_by Foreign key to the user who last updated the record.
 * @property int|null $deleted_by Foreign key to the user who soft deleted the record.
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at The timestamp when the setting was soft deleted.
 * @property-read \App\Models\User|null $createdBy The user who created the record.
 * @property-read \App\Models\User|null $deletedBy The user who soft deleted the record.
 * @property-read \App\Models\User|null $updatedBy The user who last updated the record.
 * @method static \Illuminate\Database\Eloquent\Builder|Setting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Setting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Setting onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Setting query()
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereDeletedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereId($value)
 * @method static \Illuminate\Eloquent\Builder|Setting whereSmsApiPassword($value)
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
  use CreatedUpdatedDeletedBy, HasFactory, SoftDeletes;

  /**
   * The attributes that are mass assignable.
   * 'id' is excluded as it is the primary key.
   *
   * @var array<int, string>
   */
  protected $fillable = [
    'sms_api_sender', // The sender ID for the SMS API
    'sms_api_username', // The username for the SMS API
    'sms_api_password', // The password for the SMS API
    // 'created_by', // Handled by trait
    // 'updated_by', // Handled by trait
    // 'deleted_by', // Handled by trait
  ];

  /**
   * The attributes that should be cast.
   * Ensures data types are correct and dates are Carbon instances.
   *
   * @var array<string, string>
   */
  protected $casts = [
    'sms_api_sender' => 'string', // Explicitly cast string attributes
    'sms_api_username' => 'string',
    'sms_api_password' => 'string',

    'created_at' => 'datetime', // Explicitly cast timestamps
    'updated_at' => 'datetime',
    'deleted_at' => 'datetime', // Cast soft delete timestamp
  ];

  // The CreatedUpdatedDeletedBy trait is assumed to add these audit relationships:

  /**
   * Get the user who created the model.
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\User, \App\Models\Setting>
   */
  // public function createdBy(): BelongsToRelation;

  /**
   * Get the user who last updated the model.
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\User, \App\Models\Setting>
   */
  // public function updatedBy(): BelongsToRelation;

  /**
   * Get the user who soft deleted the model.
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\User, \App\Models\Setting>
   */
  // public function deletedBy(): BelongsToRelation;


  // No relationships defined on this model as it likely stores global settings.


  // 👉 Helper Methods for Singleton Access
  // Since this table likely holds a single row of system settings,
  // adding methods to easily retrieve that row is common.

  /**
   * Get the single instance of application settings.
   * This method retrieves the first (and likely only) record from the settings table.
   * Consider adding caching here (e.g., using `Cache::rememberForever`) if the settings
   * are accessed frequently across requests to minimize database queries.
   *
   * @return static|null The Setting model instance, or null if no record exists.
   */
  public static function instance(): ?static // Added return type hint and refined docblock
  {
    // Retrieve the first (and likely only) record from the settings table.
    // Example with basic caching (requires Laravel Cache facade):
    // return \Cache::rememberForever('app_settings', function () {
    //     return static::first();
    // });
    return static::first();
  }

  /**
   * Get a specific setting value by key (using the column name).
   * Retrieves the setting from the single instance.
   *
   * @param string $key The name of the column to retrieve.
   * @param mixed|null $default Default value to return if the setting or column does not exist.
   * @return mixed|null The setting value, or the default value if not found.
   */
  public static function get(string $key, mixed $default = null): mixed // Added type hints and refined docblock
  {
    $settings = static::instance();

    // Use data_get for safe access to properties, handles null settings instance.
    // data_get also supports dot notation for arrays/JSON if needed for other settings.
    return data_get($settings, $key, $default);
  }

  // Add specific static methods for commonly accessed settings if preferred
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


  // Add any other custom static methods here for other settings


  // Add any other relationships or methods below this line
}
