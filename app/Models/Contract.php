<?php

namespace App\Models;

use App\Traits\CreatedUpdatedDeletedBy; // Assuming this trait exists and adds audit FKs/methods
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes; // Use SoftDeletes trait

class Contract extends Model
{
  // Use the traits for factory, soft deletes, and audit columns
  use CreatedUpdatedDeletedBy, HasFactory, SoftDeletes;

  /**
   * The attributes that are mass assignable.
   *
   * @var array<int, string>
   */
  protected $fillable = ['name', 'work_rate', 'notes']; // Ensure 'notes' column exists in migration

  // The CreatedUpdatedDeletedBy trait is assumed to add these relationships:
  // public function createdBy(): BelongsTo { ... }
  // public function updatedBy(): BelongsTo { ... }
  // public function deletedBy(): BelongsTo { ... }


  // 👉 Relationships

  /**
   * Get the employees associated with the contract.
   */
  public function employees(): HasMany
  {
    // Assumes the 'employees' table has a 'contract_id' foreign key
    return $this->hasMany(Employee::class);
  }

  // Add any other relationships or methods below this line
}
