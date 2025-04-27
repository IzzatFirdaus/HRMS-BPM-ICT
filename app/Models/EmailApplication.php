<?php

namespace App\Models;

use App\Traits\CreatedUpdatedDeletedBy; // Assuming this trait exists and adds audit FKs/methods
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo; // Use BelongsTo trait
use Illuminate\Database\Eloquent\Relations\MorphMany; // Use MorphMany trait for polymorphic relationship
use Illuminate\Database\Eloquent\SoftDeletes; // Use SoftDeletes trait

// Import models for relationships
use App\Models\User; // EmailApplication belongs to users (applicant, supporting officer, final assigned)
use App\Models\Approval; // EmailApplication has many Approvals (polymorphic)


class EmailApplication extends Model
{
  // Use the traits for factory, soft deletes, and audit columns
  use CreatedUpdatedDeletedBy, HasFactory, SoftDeletes;

  /**
   * The attributes that are mass assignable.
   * Includes all workflow and data fields from the migration.
   *
   * @var array<int, string>
   */
  protected $fillable = [
    'user_id', // The applicant who submitted the application (Foreign key)
    'service_status', // Taraf Perkhidmatan (String/Enum)
    'purpose', // Tujuan/Catatan (Text)
    'proposed_email', // Cadangan E-mel/ID (String)
    'group_email', // Nama Group Email (String)
    'group_admin_name', // Nama Admin/EO/CC (String)
    'group_admin_email', // E-mel Admin/EO/CC (String)
    'supporting_officer_id', // Supporting Officer ID (Foreign key)
    'status', // Workflow status (String/Enum)
    'certification_accepted', // Pengesahan Pemohon checkbox state (Boolean)
    'certification_timestamp', // Timestamp when applicant confirmed (Timestamp)
    'rejection_reason', // Reason for rejection (Text)
    'final_assigned_email', // The actual email address assigned after provisioning (String)
    'final_assigned_user_id', // The actual User ID assigned after approval/provisioning (Foreign key)
    'provisioned_at', // Timestamp when provisioning was completed (Timestamp) // ADDED: Uncommented

    // Audit columns are typically handled by the trait, but listed here for clarity if mass assignment is needed
    // 'created_by',
    // 'updated_by',
    // 'deleted_by',
  ];

  /**
   * The attributes that should be cast.
   * Includes casts for booleans, timestamps, and standard audit/soft delete timestamps.
   *
   * @var array<string, string>
   */
  protected $casts = [
    'user_id' => 'integer', // Cast FKs to integer for clarity
    'supporting_officer_id' => 'integer',
    'final_assigned_user_id' => 'integer',

    'certification_accepted' => 'boolean', // Cast boolean flag
    'certification_timestamp' => 'datetime', // Cast timestamp to Carbon instance
    'provisioned_at' => 'datetime', // ADDED: Cast provisioned_at timestamp

    // Status fields might need casting if they are database enums, otherwise string is fine.
    'service_status' => 'string', // Cast status as string/enum
    'status' => 'string', // Cast status as string/enum

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
   * Get the user who submitted the email application (the applicant).
   */
  public function user(): BelongsTo
  {
    // Assumes the 'email_applications' table has a 'user_id' foreign key
    return $this->belongsTo(User::class, 'user_id'); // Explicitly define foreign key
  }

  /**
   * Get the supporting officer for the email application (the first approver).
   */
  public function supportingOfficer(): BelongsTo
  {
    // Assumes the 'email_applications' table has a 'supporting_officer_id' foreign key
    return $this->belongsTo(User::class, 'supporting_officer_id'); // Belongs to User model with supporting_officer_id foreign key
  }

  /**
   * Get the user who was finally assigned the email address (if applicable).
   * This is likely the same as the applicant user, but the FK allows flexibility.
   */
  public function finalAssignedUser(): BelongsTo // ADDED: Relationship to final assigned user
  {
    // Assumes the 'email_applications' table has a 'final_assigned_user_id' foreign key
    return $this->belongsTo(User::class, 'final_assigned_user_id'); // Belongs to User model with final_assigned_user_id foreign key
  }

  /**
   * Get the approvals for the email application (polymorphic relationship).
   * This links this application to its workflow approval records.
   */
  public function approvals(): MorphMany // Corrected relationship type
  {
    // MorphMany relationship to the Approval model where this model is the 'approvable'
    return $this->morphMany(Approval::class, 'approvable');
  }


  // 👉 Helper Methods (Workflow Status Checks)

  /**
   * Check if the application is in draft status.
   */
  public function isDraft(): bool
  {
    return $this->status === 'draft';
  }

  /**
   * Check if the application is in a pending status requiring support review.
   */
  public function isPendingSupport(): bool
  {
    return $this->status === 'pending_support';
  }

  /**
   * Check if the application is in a pending status requiring IT admin review.
   */
  public function isPendingAdmin(): bool
  {
    return $this->status === 'pending_admin';
  }

  /**
   * Check if the application is currently pending any approval step.
   * This could be support review or IT admin review.
   */
  public function isPendingApproval(): bool
  {
    return in_array($this->status, ['pending_support', 'pending_admin']);
  }

  /**
   * Check if the application has been approved.
   */
  public function isApproved(): bool
  {
    return $this->status === 'approved';
  }

  /**
   * Check if the application has been rejected.
   */
  public function isRejected(): bool
  {
    return $this->status === 'rejected';
  }

  /**
   * Check if the application is in the process of being provisioned.
   */
  public function isProcessing(): bool
  {
    return $this->status === 'processing';
  }

  /**
   * Check if the application has been completed (provisioned and closed).
   */
  public function isCompleted(): bool
  {
    return $this->status === 'completed';
  }


  // Add custom methods or accessors/mutators here as needed
}
