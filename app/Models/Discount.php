<?php

namespace App\Models;

use App\Traits\CreatedUpdatedDeletedBy;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

// Import model for BelongsTo relationship
use App\Models\Employee;

/**
 * App\Models\Discount
 *
 * @property int $id
 * @property int $employee_id Foreign key to the employee the discount applies to.
 * @property float $rate The discount rate or amount.
 * @property \Illuminate\Support\Carbon $date The date the discount applies to.
 * @property string|null $reason The reason for the discount.
 * @property bool $is_auto Indicates if the discount was applied automatically.
 * @property bool $is_sent Indicates if the discount notification/record has been sent/processed.
 * @property string|null $batch A batch identifier for processing discounts.
 * @property int|null $created_by Foreign key to the user who created the record.
 * @property int|null $updated_by Foreign key to the user who last updated the record.
 * @property int|null $deleted_by Foreign key to the user who soft deleted the record.
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\Employee $employee The employee the discount belongs to.
 * @property-read \App\Models\User|null $createdBy
 * @property-read \App\Models\User|null $deletedBy
 * @property-read \App\Models\User|null $updatedBy
 * @method static \Illuminate\Database\Eloquent\Builder|Discount newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Discount newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Discount onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Discount query()
 * @method static \Illuminate\Database\Eloquent\Builder|Discount whereBatch($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Discount whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Discount whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Discount whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Discount whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Discount whereDeletedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Discount whereEmployeeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Discount whereIsAuto($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Discount whereIsSent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Discount whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Discount whereRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Discount whereReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Discount whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Discount whereUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Discount withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Discount withoutTrashed()
 * @mixin \Eloquent
 */
class Discount extends Model
{
  use CreatedUpdatedDeletedBy, HasFactory, SoftDeletes;

  /**
   * The attributes that are mass assignable.
   * 'id' is excluded as it is the primary key.
   *
   * @var array<int, string>
   */
  protected $fillable = [
    'employee_id',
    'rate',
    'date',
    'reason',
    'is_auto',
    'is_sent',
    'batch',
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
    'employee_id' => 'integer', // Cast foreign key to integer
    'rate' => 'float', // Cast rate as float (adjust to 'decimal:2' or similar if needed)
    'date' => 'date', // Cast date to Carbon instance
    'reason' => 'string', // Explicitly cast reason as string
    'is_auto' => 'boolean', // Cast is_auto to boolean
    'is_sent' => 'boolean', // Cast is_sent to boolean
    'batch' => 'string', // Cast batch as string (adjust to 'integer' if applicable)
    'created_at' => 'datetime', // Explicitly cast timestamps
    'updated_at' => 'datetime',
    'deleted_at' => 'datetime', // Cast soft delete timestamp
  ];

  // The CreatedUpdatedDeletedBy trait is assumed to add these audit relationships:
  // public function createdBy(): BelongsTo;
  // public function updatedBy(): BelongsTo;
  // public function deletedBy(): BelongsTo;


  // 👉 Relationships

  /**
   * Get the employee that the discount belongs to.
   * Defines a many-to-one relationship where a Discount belongs to one Employee.
   * Assumes the 'discounts' table has an 'employee_id' foreign key.
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
   */
  public function employee(): BelongsTo // Added return type hint
  {
    // Defines a many-to-one relationship with the Employee model
    return $this->belongsTo(Employee::class, 'employee_id'); // Explicitly define foreign key for clarity
  }

  // The date attribute method is redundant because the 'date' cast
  // automatically provides a Carbon instance. You can format the date
  // in your views or using a separate accessor if needed.

  // Add any other relationships or methods below this line
}
