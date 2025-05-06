<?php
// App/Models/Location.php
namespace App\Models;

use App\Traits\CreatedUpdatedDeletedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes; // Assuming SoftDeletes is used

// Import related models if needed for relationships from this model
// use App\Models\Equipment; // If Location hasMany Equipment
// use App\Models\User; // If audit trait uses User

/**
 * App\Models\Location
 *
 * Represents a physical location where equipment can be stored or assigned.
 * Linked to Equipment. Includes audit trails and soft deletion.
 * Follows implied schema details from System Design Document.
 *
 * @property int $id
 * @property string $name Name of the location (e.g., "Bilik Server", "Pejabat BPM", "Store").
 * @property string|null $description Optional description.
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property int|null $deleted_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Equipment> $equipment The equipment located here.
 * @property-read User|null $creator
 * @property-read User|null $editor
 * @property-read User|null $deleter
 * @method static \Illuminate\Database\Eloquent\Builder|Location newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Location newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Location onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Location query()
 * @method static \Illuminate\Database\Eloquent\Builder|Location withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Location withoutTrashed()
 * @method static \Database\Factories\LocationFactory factory($count = null, $state = [])
 * @mixin \Eloquent
 */
class Location extends Model
{
  use HasFactory, SoftDeletes, CreatedUpdatedDeletedBy;

  protected $fillable = [
    'name',
    'description',
  ];

  protected $casts = [
    'created_at' => 'datetime',
    'updated_at' => 'datetime',
    'deleted_at' => 'datetime',
  ];

  // --- Relationships ---

  /**
   * Get the equipment items located at this location.
   * Assumes Equipment model has 'location_id' foreign key.
   */
  public function equipment(): HasMany
  {
    return $this->hasMany(Equipment::class, 'location_id');
  }

  // Add other relationships, scopes, accessors, etc. as needed
}
