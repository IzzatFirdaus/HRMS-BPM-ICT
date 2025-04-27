<?php

namespace App\Models;

use App\Traits\CreatedUpdatedDeletedBy; // Assuming this trait exists and adds audit FKs/methods
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo; // Use BelongsTo trait
use Illuminate\Database\Eloquent\Relations\MorphTo; // Use MorphTo trait for polymorphic relationship
use Illuminate\Database\Eloquent\SoftDeletes; // Use SoftDeletes trait


// Import models for relationships
use App\Models\User; // Approval belongs to an officer (User)
// No need to import Approvable models here, they are handled by the morphTo relationship


class Approval extends Model
{
  // Use the traits for factory, soft deletes, and audit columns
  use CreatedUpdatedDeletedBy, HasFactory, SoftDeletes;

  /**
   * The attributes that are mass assignable.
   * Includes polymorphic fields, officer, status, stage, comments, and timestamp.
   *
   * @var array<int, string>
   */
  protected $fillable = [
    'approvable_id', // ID of the model being approved (e.g., EmailApplication ID)
    'approvable_type', // Class name of the model being approved (e.g., App\Models\EmailApplication)
    'officer_id', // The user who made the approval decision (Foreign key)
    'status', // Approval status (e.g., approved, rejected, pending)
    'stage', // ADDED: Approval stage (e.g., support, IT_admin)
    'comments', // Officer's comments (Text)
    'approval_timestamp', // Timestamp of the decision

    // Audit columns are typically handled by the trait, but listed here for clarity if mass assignment is needed
    // 'created_by',
    // 'updated_by',
    // 'deleted_by',
  ];

  /**
   * The attributes that should be cast.
   * Includes casts for FKs, timestamps, status/stage, and standard audit/soft delete timestamps.
   *
   * @var array<string, string>
   */
  protected $casts = [
    'approvable_id' => 'integer', // Cast polymorphic ID
    'officer_id' => 'integer', // Cast officer FK

    'approval_timestamp' => 'datetime', // Cast timestamp to Carbon instance

    // Status and stage fields might need casting if they are database enums, otherwise string is fine.
    'status' => 'string', // Cast status as string/enum
    'stage' => 'string', // ADDED: Cast stage as string/enum

    'created_at' => 'datetime', // Explicitly cast timestamps if trait doesn't handle or for clarity
    'updated_at' => 'datetime',
    'deleted_at' => 'datetime', // Cast soft delete timestamp
  ];


  // The CreatedUpdatedDeletedBy trait is assumed to add these audit relationships:
  // public function createdBy(): BelongsTo { ... }
  // public function updatedBy(): BelongsTo { ... }
  // public function deletedBy(): BelongsTo { ... }


  // 👉 Relationships

  /**
   * Get the approvable model (EmailApplication or LoanApplication) that the approval belongs to.
   */
  public function approvable(): MorphTo // Corrected relationship type hint
  {
    // Define the polymorphic relationship - Eloquent determines the related model based on approvable_type
    return $this->morphTo();
  }

  /**
   * Get the user (officer) who made the approval decision.
   */
  public function officer(): BelongsTo
  {
    // Assumes the 'approvals' table has an 'officer_id' foreign key
    return $this->belongsTo(User::class, 'officer_id'); // Explicitly define foreign key
  }


  // 👉 Helper Methods (Approval Status Checks)

  /**
   * Check if the approval status is approved.
   */
  public function isApproved(): bool
  {
    return $this->status === 'approved';
  }

  /**
   * Check if the approval status is rejected.
   */
  public function isRejected(): bool
  {
    return $this->status === 'rejected';
  }

  /**
   * Check if the approval status is pending.
   */
  public function isPending(): bool
  {
    return $this->status === 'pending';
  }

  /**
   * Check if the approval was made at a specific stage.
   *
   * @param string $stage The stage to check against (e.g., 'support', 'IT_admin').
   * @return bool
   */
  public function atStage(string $stage): bool
  {
    return $this->stage === $stage;
  }


  // Add custom methods or accessors/mutators here as needed
}
