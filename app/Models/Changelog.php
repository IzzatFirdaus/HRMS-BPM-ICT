<?php

namespace App\Models; // Ensure the namespace is correct for your project

use App\Traits\CreatedUpdatedDeletedBy; // Assuming this trait exists and adds audit FKs/methods
use Carbon\Carbon; // Import Carbon if needed for custom date handling (used implicitly by 'datetime' cast)
// use Illuminate\Database\Eloquent\Casts\Attribute; // Import Attribute if needed for custom accessors/mutators (not currently used) - REMOVED THIS IMPORT
use Illuminate\Database\Eloquent\Factories\HasFactory; // Import HasFactory trait
use Illuminate\Database\Eloquent\Model; // Import base Model class
use Illuminate\Database\Eloquent\Relations\BelongsTo; // Import BelongsTo relationship type (for relationships potentially added by the trait)
use Illuminate\Database\Eloquent\SoftDeletes; // Import SoftDeletes trait


/**
 * App\Models\Changelog
 *
 * Represents a single entry in the application's changelog, detailing changes for a specific version.
 * Includes version, title, description, and audit trails.
 *
 * @property int $id
 * @property string $version The application version this changelog entry belongs to (e.g., '1.0.0').
 * @property string $title The title of the changelog entry (e.g., 'New Feature: Loan Requests').
 * @property string $description The detailed description of the changes (can be markdown/HTML).
 * @property int|null $created_by Foreign key to the user who created the record (handled by trait if applied here).
 * @property int|null $updated_by Foreign key to the user who last updated the record (handled by trait if applied here).
 * @property int|null $deleted_by Foreign key to the user who soft deleted the record (handled by trait if applied here).
<<<<<<< HEAD
 * @property \Illuminate\Support\Carbon|null $created_at Automatically managed timestamp for creation.
 * @property \Illuminate\Support\Carbon|null $updated_at Automatically managed timestamp for last update.
 * @property \Illuminate\Support\Carbon|null $deleted_at Timestamp for soft deletion.
 *
=======
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
>>>>>>> cc6eb9f4f020325c04fee080d2466584ff27bb90
 * @property-read \App\Models\User|null $createdBy Relation to the user who created the record (if trait adds this).
 * @property-read \App\Models\User|null $deletedBy Relation to the user who soft deleted the record (if trait adds this).
 * @property-read \App\Models\User|null $updatedBy Relation to the user who last updated the record (if trait adds this).
 * @method static \Illuminate\Database\Eloquent\Builder|Changelog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Changelog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Changelog onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Changelog query()
 * @method static \Illuminate\Database\Eloquent\Builder|Changelog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Changelog whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Changelog whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Changelog whereDeletedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Changelog whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Changelog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Changelog whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Changelog whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Changelog whereUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Changelog whereVersion($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Changelog withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Changelog withoutTrashed()
 * @mixin \Eloquent
 */
class Changelog extends Model
{
  // Use the traits for factory, soft deletes, and audit columns
  // HasFactory is for model factories.
  // SoftDeletes adds the 'deleted_at' column and scope.
  // CreatedUpdatedDeletedBy is assumed to add 'created_by', 'updated_by', 'deleted_by' columns and relationships.
  use CreatedUpdatedDeletedBy, HasFactory, SoftDeletes;

  /**
   * The table associated with the model.
   *
   * @var string
   */
  // protected $table = 'changelogs'; // Explicitly define table name if it's not the plural of the model name


  /**
   * The attributes that are mass assignable.
   * Allows these attributes to be set using mass assignment (e.g., during create or update).
   *
   * @var array<int, string>
   */
  protected $fillable = [
    // 'id' is the primary key and typically not included in fillable
    'version',     // Application version string
    'title',       // Title of the changelog entry
    'description', // Detailed description of the changes

    // The CreatedUpdatedDeletedBy trait is assumed to handle these audit columns:
    // 'created_by',
    // 'updated_by',
    // 'deleted_by',
  ];

  /**
   * The attributes that should be cast.
   * Ensures date/datetime attributes are Carbon instances. Explicit casting for strings is optional but harmless.
   *
   * @var array<string, string>
   */
  protected $casts = [
    'version'     => 'string',   // Explicitly cast version as string
    'title'       => 'string',   // Explicitly cast title as string
    'description' => 'string',   // Explicitly cast description as string
    'created_at'  => 'datetime', // Explicitly cast creation timestamp
    'updated_at'  => 'datetime', // Explicitly cast update timestamp
    'deleted_at'  => 'datetime', // Cast soft delete timestamp
    // Add casts for other attributes if needed
  ];

  /**
   * The attributes that should be hidden for serialization.
   * (Optional) Prevents sensitive attributes from being returned in JSON responses.
   *
   * @var array<int, string>
   */
  // protected $hidden = [
  //     'created_by', // Example: hide audit columns from API responses
  //     'updated_by',
  //     'deleted_by',
  // ];


  // The CreatedUpdatedDeletedBy trait is assumed to add these audit relationships:
  // public function createdBy(): BelongsTo; // Relationship to the user who created the record
  // public function updatedBy(): BelongsTo; // Relationship to the user who last updated the record
  // public function deletedBy(): BelongsTo; // Relationship to the user who soft deleted the record


  // 👉 Relationships
  // No other specific relationships are typically needed for a simple changelog model itself.

  // 👉 Attributes (Accessors/Mutators)
  // Add custom accessors or mutators here if needed (e.g., formatting description, managing version string format).
  // Ensure you import Illuminate\Database\Eloquent\Casts\Attribute if using modern accessors/mutators.

  // Example: Accessor to auto-capitalize the version string (optional)
  // protected function version(): Attribute
  // {
  //     return Attribute::make(
  //         get: fn (string $value) => strtoupper($value),
  //     );
  // }
}
