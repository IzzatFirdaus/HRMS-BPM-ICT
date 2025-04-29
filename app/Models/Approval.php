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

  // Define constants for approval statuses for better code readability and maintainability
  public const STATUS_PENDING = 'pending';
  public const STATUS_APPROVED = 'approved';
  public const STATUS_REJECTED = 'rejected';

  // Define constants for approval stages based on the application workflow (e.g., from other components/services)
  public const STAGE_SUPPORT_REVIEW = 'support_review'; // Example stage: Initial review by IT Support
  public const STAGE_IT_ADMIN = 'IT_admin'; // Example stage: Final review/action by IT Admin
  // Add other stages as needed based on your workflow


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
    'stage', // Approval stage (e.g., support, IT_admin)
    'comments', // Officer's comments (Text)
    'approval_timestamp', // Timestamp of the decision

    // Audit columns are typically handled by the trait and should generally not be mass assignable
    // 'created_by', // Handled by CreatedUpdatedDeletedBy trait
    // 'updated_by', // Handled by CreatedUpdatedDeletedBy trait
    // 'deleted_by', // Handled by CreatedUpdatedDeletedBy trait
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

    // Status and stage are typically stored as strings, or cast to PHP Enums if defined
    'status' => 'string', // Cast status as string (or to ApprovalStatus::class if using PHP Enums)
    'stage' => 'string', // Cast stage as string (or to ApprovalStage::class if using PHP Enums)

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
   * Get the approvable model (EmailApplication or LoanApplication) that the approval belongs to.
   * Defines the polymorphic relationship.
   *
   * @return \Illuminate\Database\Eloquent\Relations\MorphTo
   */
  public function approvable(): MorphTo // Added return type hint
  {
    // Eloquent determines the related model based on approvable_type and approvable_id
    return $this->morphTo();
  }

  /**
   * Get the user (officer) who made the approval decision.
   * Assumes the 'approvals' table has an 'officer_id' foreign key.
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
   */
  public function officer(): BelongsTo // Added return type hint
  {
    // Defines a many-to-one relationship with the User model
    return $this->belongsTo(User::class, 'officer_id'); // Explicitly define foreign key
  }


  // 👉 Helper Methods (Approval Status Checks)

  /**
   * Check if the approval status is 'approved'.
   *
   * @return bool
   */
  public function isApproved(): bool
  {
    return $this->status === self::STATUS_APPROVED; // Use constant
  }

  /**
   * Check if the approval status is 'rejected'.
   *
   * @return bool
   */
  public function isRejected(): bool
  {
    return $this->status === self::STATUS_REJECTED; // Use constant
  }

  /**
   * Check if the approval status is 'pending'.
   *
   * @return bool
   */
  public function isPending(): bool
  {
    return $this->status === self::STATUS_PENDING; // Use constant
  }

  /**
   * Check if the approval was made at a specific stage.
   *
   * @param string $stage The stage to check against (e.g., 'support_review', 'IT_admin').
   * @return bool
   */
  public function atStage(string $stage): bool
  {
    return $this->stage === $stage;
  }


  // Add custom methods or accessors/mutators here as needed

  /**
   * Get the translated status string.
   *
   * @return string
   */
  public function getStatusTranslatedAttribute(): string
  {
    return match ($this->status) {
      self::STATUS_PENDING => __('Pending'),
      self::STATUS_APPROVED => __('Approved'),
      self::STATUS_REJECTED => __('Rejected'),
      default => $this->status, // Return raw status if unknown
    };
  }

  /**
   * Get the translated stage string.
   *
   * @return string
   */
  public function getStageTranslatedAttribute(): string
  {
    return match ($this->stage) {
      self::STAGE_SUPPORT_REVIEW => __('Support Review'),
      self::STAGE_IT_ADMIN => __('IT Admin'),
      default => $this->stage, // Return raw stage if unknown
    };
  }
}
