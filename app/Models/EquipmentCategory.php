<?php
// App/Models/EquipmentCategory.php

namespace App\Models;

use App\Traits\CreatedUpdatedDeletedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes; // Assuming SoftDeletes is used

// Import related models if needed for relationships from this model
// use App\Models\Equipment; // If EquipmentCategory hasMany Equipment
// use App\Models\User; // If audit trait uses User


/**
 * App\Models\EquipmentCategory
 *
 * Represents a category for ICT equipment (e.g., "Laptop", "Projector", "Printer").
 * Linked to Equipment. Includes audit trails and soft deletion.
 * Follows implied schema details from System Design Document.
 *
 * @property int $id
 * @property string $name Name of the category (e.g., "Laptop").
 * @property string|null $description Optional description.
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property int|null $deleted_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Equipment> $equipment The equipment in this category.
 * @property-read User|null $creator
 * @property-read User|null $editor
 * @property-read User|null $deleter
 * @method static \Illuminate\Database\Eloquent\Builder|EquipmentCategory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EquipmentCategory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EquipmentCategory onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|EquipmentCategory query()
 * @method static \Illuminate\Database\Eloquent\Builder|EquipmentCategory withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|EquipmentCategory withoutTrashed()
 * @method static \Database\Factories\EquipmentCategoryFactory factory($count = null, $state = [])
 * @mixin \Eloquent
 */
class EquipmentCategory extends Model
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
   * Get the equipment items belonging to this category.
   * Assumes Equipment model has 'equipment_category_id' foreign key.
   */
  public function equipment(): HasMany
  {
    return $this->hasMany(Equipment::class, 'equipment_category_id');
  }


  // Add other relationships, scopes, accessors, etc. as needed
}
