<?php

namespace App\Models;

use App\Traits\CreatedUpdatedDeletedBy; // Assuming this trait exists and adds audit FKs/methods
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; // Use SoftDeletes trait

class Setting extends Model
{
  // Use the traits for factory, soft deletes, and audit columns
  use CreatedUpdatedDeletedBy, HasFactory, SoftDeletes;

  /**
   * The attributes that are mass assignable.
   *
   * @var array<int, string>
   */
  protected $fillable = [
    'sms_api_sender',
    'sms_api_username',
    'sms_api_password',
    // Audit columns are typically handled by the trait
  ];

  /**
   * The attributes that should be cast.
   *
   * @var array<string, string>
   */
  protected $casts = [
    'created_at' => 'datetime', // Explicitly cast timestamps if trait doesn't handle or for clarity
    'updated_at' => 'datetime',
    'deleted_at' => 'datetime', // Cast soft delete timestamp
  ];


  // The CreatedUpdatedDeletedBy trait is assumed to add these audit relationships:
  // public function createdBy(): BelongsTo { ... }
  // public function updatedBy(): BelongsTo { ... }
  // public function deletedBy(): BelongsTo { ... }


  // 👉 Helper Methods for Singleton Access
  // Since this table likely holds a single row of system settings,
  // adding methods to easily retrieve that row is common.

  /**
   * Get the single instance of application settings.
   *
   * @return static|null
   */
  public static function instance(): ?static
  {
    // Retrieve the first (and likely only) record from the settings table.
    // Cache this result if accessed frequently across requests.
    return static::first();
  }

  /**
   * Get a specific setting value by key (using the column name).
   *
   * @param string $key The name of the column to retrieve.
   * @param mixed|null $default Default value if the setting or column does not exist.
   * @return mixed|null
   */
  public static function get(string $key, mixed $default = null): mixed
  {
    $settings = static::instance();

    // Use data_get for safe access to nested/dot notation if needed,
    // or simply direct property access for top-level columns.
    return data_get($settings, $key, $default);
  }

  // Add specific static methods for commonly accessed settings if preferred
  /**
   * Get the SMS API username.
   *
   * @return string|null
   */
  public static function getSmsApiUsername(): ?string
  {
    return static::get('sms_api_username');
  }

  // Add methods for other SMS API credentials, etc.


  // Add any other relationships or methods below this line
}
