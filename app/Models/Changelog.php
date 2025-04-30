<?php

namespace App\Models;

use App\Traits\CreatedUpdatedDeletedBy; // Assuming this trait exists and adds audit FKs/methods
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo; // Import BelongsTo for trait relationships


/**
 * App\Models\Changelog
 *
 * @property int $id
 * @property string $version The application version this changelog entry belongs to.
 * @property string $title The title of the changelog entry.
 * @property string $description The detailed description of the changes.
 * @property int|null $created_by Foreign key to the user who created the record (handled by trait if applied here).
 * @property int|null $updated_by Foreign key to the user who last updated the record (handled by trait if applied here).
 * @property int|null $deleted_by Foreign key to the user who soft deleted the record (handled by trait if applied here).
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 *
 * @property-read \App\Models\User|null $createdBy Relation to the user who created the record (if trait adds this).
 * @property-read \App\Models\User|null $deletedBy Relation to the user who soft deleted the record (if trait adds this).
 * @property-read \App\Models\User|null $updatedBy Relation to the user who last updated the record (if trait adds this).
 *
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
  use CreatedUpdatedDeletedBy, HasFactory, SoftDeletes;

  /**
   * The attributes that are mass assignable.
   * 'id' is excluded as it is the primary key.
   *
   * @var array<int, string>
   */
  protected $fillable = [
    'version',
    'title',
    'description',
    // 'created_by', // Handled by trait if applied here
    // 'updated_by', // Handled by trait if applied here
    // 'deleted_by', // Handled by trait if applied here
  ];

  /**
   * The attributes that should be cast.
   * Ensures dates are Carbon instances.
   *
   * @var array<string, string>
   */
  protected $casts = [
    'version'     => 'string', // Explicitly cast version as string
    'title'       => 'string', // Explicitly cast title as string
    'description' => 'string', // Explicitly cast description as string
    'created_at'  => 'datetime', // Explicitly cast timestamps
    'updated_at'  => 'datetime',
    'deleted_at'  => 'datetime', // Cast soft delete timestamp
  ];

  // The CreatedUpdatedDeletedBy trait is assumed to add these audit relationships:
  // public function createdBy(): BelongsTo; // If trait adds this
  // public function updatedBy(): BelongsTo; // If trait adds this
  // public function deletedBy(): BelongsTo; // If trait adds this

  // No other specific relationships are typically needed for a simple changelog model.
}
