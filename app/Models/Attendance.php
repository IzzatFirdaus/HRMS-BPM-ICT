<?php

namespace App\Models;

use App\Traits\CreatedUpdatedDeletedBy; // Assuming this trait is used
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; // Assuming SoftDeletes is used
use Illuminate\Database\Eloquent\Relations\BelongsTo; // For relationship to Employee
use Illuminate\Support\Carbon; // Import for date/datetime casts

/**
 * App\Models\Attendance
 *
 * Represents an employee's attendance record.
 *
 * @property int $id
 * @property int $employee_id Foreign key to employees table.
 * @property \Illuminate\Support\Carbon $record_date The date of the attendance record.
 * @property \Illuminate\Support\Carbon|null $check_in_time The time of check-in.
 * @property \Illuminate\Support\Carbon|null $check_out_time The time of check-out.
 * @property string|null $status // e.g., 'Present', 'Absent', 'Late', 'On Leave'
 * @property string|null $notes Additional notes for the attendance record.
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property int|null $deleted_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 *
 * // Relationships
 * @property-read \App\Models\Employee $employee
 */
class Attendance extends Model
{
  use CreatedUpdatedDeletedBy, SoftDeletes;

  protected $table = 'attendances'; // Explicitly define table name if it deviates from convention

  protected $fillable = [
    'employee_id',
    'record_date',
    'check_in_time',
    'check_out_time',
    'status',
    'notes',
  ];

  protected $casts = [
    'record_date' => 'date',
    'check_in_time' => 'datetime',
    'check_out_time' => 'datetime',
  ];

  /**
   * Get the employee associated with the attendance record.
   */
  public function employee(): BelongsTo
  {
    return $this->belongsTo(Employee::class, 'employee_id');
  }

  // Add any other relationships, accessors, mutators, or scopes here
}
