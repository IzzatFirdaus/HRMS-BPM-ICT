<?php

namespace App\Models; // Ensure the namespace is correct for your project

use App\Traits\CreatedUpdatedDeletedBy; // Assuming this trait exists and adds audit FKs/methods
use Illuminate\Database\Eloquent\Factories\HasFactory; // Import HasFactory trait
use Illuminate\Database\Eloquent\Model; // Import base Model class
use Illuminate\Database\Eloquent\Relations\BelongsTo; // Import BelongsTo relationship type
use Illuminate\Database\Eloquent\Relations\MorphTo; // Import MorphTo relationship type for polymorphic relationship
use Illuminate\Database\Eloquent\SoftDeletes; // Import SoftDeletes trait

// Import models for relationships (Eloquent needs to know about the related models for relationships)
use App\Models\User; // Approval belongs to an officer (User)
// The models participating in the morphTo relationship (EmailApplication, LoanApplication)
// do NOT need to be imported here in the Approval model itself, as Eloquent resolves
// them dynamically based on the 'approvable_type' column value.


/**
 * Class Approval
 *
 * Represents a single approval record for a polymorphic 'approvable' model
 * (e.g., EmailApplication, LoanApplication). Stores information about the
 * officer who made the decision, the status, the stage in the workflow,
 * comments, and the timestamp of the decision.
 */
class Approval extends Model
{
  // Use the traits for factory, soft deletes, and audit columns
  // SoftDeletes adds the 'deleted_at' column and scope.
  // CreatedUpdatedDeletedBy is assumed to add 'created_by', 'updated_by', 'deleted_by' columns and relationships.
  use CreatedUpdatedDeletedBy, HasFactory, SoftDeletes;

  // Define constants for possible approval statuses for better code readability and maintainability.
  // These should align with the values used in your workflow logic.
  public const STATUS_PENDING = 'pending';    // Waiting for a decision
  public const STATUS_APPROVED = 'approved';  // Decision was to approve
  public const STATUS_REJECTED = 'rejected';  // Decision was to reject

  // Define constants for approval stages based on your application workflow.
  // These indicate *where* in the workflow this approval record sits.
  public const STAGE_SUPPORT_REVIEW = 'support_review'; // Example stage: Initial review by IT Support/Supporting Officer
  public const STAGE_IT_ADMIN = 'it_admin';           // Example stage: Review/action by IT Admin
  public const STAGE_HOD = 'hod_approval';            // Example stage: Head of Department approval (if applicable)
  public const STAGE_BPM = 'bpm_processing';        // Example stage: BPM Staff processing (e.g., issuing equipment)
  // Add other stages as needed based on your specific approval matrix/workflow steps.


  /**
   * The table associated with the model.
   *
   * @var string
   */
  // protected $table = 'approvals'; // Explicitly define table name if it's not the plural of the model name


  /**
   * The attributes that are mass assignable.
   * Includes polymorphic fields, officer, status, stage, comments, and timestamp.
   * Audit columns are typically handled by the trait and should generally not be mass assignable.
   *
   * @var array<int, string>
   */
  protected $fillable = [
    'approvable_id',   // ID of the model being approved (e.g., EmailApplication ID, LoanApplication ID)
    'approvable_type', // Class name of the model being approved (e.g., App\Models\EmailApplication)
    'officer_id',      // The user (officer) who made the approval decision (Foreign key to 'users' table)
    'status',          // Approval status (e.g., approved, rejected, pending)
    'stage',           // Approval stage (e.g., support_review, it_admin)
    'comments',        // Officer's comments or justification for the decision
    'approval_timestamp', // Timestamp when the approval decision was made

    // The CreatedUpdatedDeletedBy trait is assumed to handle these:
    // 'created_by', // User who created the record (initial 'pending' approval usually)
    // 'updated_by', // User who updated the record (when status changes from pending)
    // 'deleted_by', // User who soft deleted the record (if applicable)
  ];

  /**
   * The attributes that should be cast.
   * Includes casts for FKs, timestamps, status/stage, and standard audit/soft delete timestamps.
   * Casting ensures that these attributes are returned as specific PHP types when accessed.
   *
   * @var array<string, string>
   */
  protected $casts = [
    'approvable_id' => 'integer',     // Cast polymorphic ID to integer
    'officer_id' => 'integer',        // Cast officer FK to integer

    'approval_timestamp' => 'datetime', // Cast approval timestamp to a Carbon instance

    // Status and stage are typically stored as strings. Could be cast to PHP Enums if defined.
    'status' => 'string',             // Cast status as string (or to App\Enums\ApprovalStatus::class if using PHP Enums)
    'stage' => 'string',              // Cast stage as string (or to App\Enums\ApprovalStage::class if using PHP Enums)

    // Standard Eloquent timestamps
    'created_at' => 'datetime',       // Explicitly cast creation timestamp
    'updated_at' => 'datetime',       // Explicitly cast update timestamp
    'deleted_at' => 'datetime',       // Cast soft delete timestamp
  ];


  // The CreatedUpdatedDeletedBy trait is assumed to add these audit relationships:
  // public function createdBy(): BelongsTo; // Relationship to the user who created the record
  // public function updatedBy(): BelongsTo; // Relationship to the user who last updated the record
  // public function deletedBy(): BelongsTo; // Relationship to the user who soft deleted the record


  // 👉 Relationships

  /**
   * Get the approvable model (EmailApplication or LoanApplication) that the approval belongs to.
   * Defines the polymorphic relationship where this Approval model is the 'morphMany' side
   * and EmailApplication/LoanApplication models are the 'morphOne' or 'morphMany' related side.
   *
   * @return \Illuminate\Database\Eloquent\Relations\MorphTo
   */
  public function approvable(): MorphTo // Added return type hint
  {
    // Eloquent determines the related model based on approvable_type and approvable_id columns.
    // The default column names ('approvable_type', 'approvable_id') match the method name 'approvable'.
    return $this->morphTo();
  }

  /**
   * Get the user (officer) who made the approval decision.
   * Assumes the 'approvals' table has an 'officer_id' foreign key referencing the 'users' table.
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
   */
  public function officer(): BelongsTo // Added return type hint
  {
    // Defines a many-to-one relationship with the User model.
    // 'User::class' is the related model.
    // 'officer_id' is the foreign key on the 'approvals' table.
    // 'id' is the local key on the 'users' table (default, can be omitted).
    return $this->belongsTo(User::class, 'officer_id');
  }


  // 👉 Helper Methods (Approval Status and Stage Checks)

  /**
   * Check if the approval record has a specific status.
   *
   * @param string $status The status value to check against (use Approval::STATUS_ constants).
   * @return bool
   */
  public function hasStatus(string $status): bool
  {
    return $this->status === $status;
  }

  /**
   * Check if the approval status is 'approved'.
   *
   * @return bool
   */
  public function isApproved(): bool
  {
    return $this->hasStatus(self::STATUS_APPROVED); // Use constant via hasStatus helper
  }

  /**
   * Check if the approval status is 'rejected'.
   *
   * @return bool
   */
  public function isRejected(): bool
  {
    return $this->hasStatus(self::STATUS_REJECTED); // Use constant via hasStatus helper
  }

  /**
   * Check if the approval status is 'pending'.
   *
   * @return bool
   */
  public function isPending(): bool
  {
    return $this->hasStatus(self::STATUS_PENDING); // Use constant via hasStatus helper
  }

  /**
   * Check if the approval was made at a specific stage.
   *
   * @param string $stage The stage value to check against (use Approval::STAGE_ constants).
   * @return bool
   */
  public function atStage(string $stage): bool
  {
    return $this->stage === $stage;
  }


  // 👉 Accessors (for display purposes)

  /**
   * Get the translated status string.
   * Useful for displaying user-friendly status in views.
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
   * Useful for displaying user-friendly stage names in views.
   *
   * @return string
   */
  public function getStageTranslatedAttribute(): string
  {
    return match ($this->stage) {
      self::STAGE_SUPPORT_REVIEW => __('Support Review'),
      self::STAGE_IT_ADMIN => __('IT Admin'),
      self::STAGE_HOD => __('HOD Approval'), // Using added HOD stage constant
      self::STAGE_BPM => __('BPM Processing'), // Using added BPM stage constant
      default => $this->stage, // Return raw stage if unknown
    };
  }

  // Add custom methods or accessors/mutators here as needed for business logic or display.

  /**
   * Get the full name of the officer who made this approval decision.
   *
   * @return string|null
   */
  public function getOfficerNameAttribute(): ?string
  {
    // Access the officer relationship and get the name (safely)
    return $this->officer->full_name ?? $this->officer->name ?? null;
  }
}
