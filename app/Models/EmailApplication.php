<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User; // Import the User model for relationships
use App\Models\Approval; // Import the Approval model for relationships

class EmailApplication extends Model
{
  use HasFactory;

  // Define fillable or guarded properties for mass assignment
  protected $fillable = [
    'user_id', // The applicant who submitted the application
    'service_status', // FIX: Added Service Status field (Taraf Perkhidmatan)
    'purpose', // Tujuan/Catatan
    'proposed_email', // Cadangan E-mel/ID
    'group_email', // Nama Group Email
    'group_admin_name', // Nama Admin/EO/CC
    'group_admin_email', // E-mel Admin/EO/CC
    'supporting_officer_id', // FIX: Added Supporting Officer ID field
    'status', // Workflow status (draft, pending_support, pending_admin, approved, rejected, processing, completed)
    'certification_accepted', // Pengesahan Pemohon checkbox state
    'certification_timestamp', // Timestamp when applicant confirmed
    'rejection_reason', // Reason for rejection
    'final_assigned_email', // The actual email address assigned after approval/provisioning
    'final_assigned_user_id', // The actual User ID assigned after approval/provisioning
    // 'provisioned_at', // Optional: Timestamp when provisioning was completed
  ];

  // Define cast properties for attribute casting
  protected $casts = [
    'certification_accepted' => 'boolean',
    'certification_timestamp' => 'datetime',
    // 'provisioned_at' => 'datetime', // Optional cast
  ];

  // Define relationships

  /**
   * Get the user who submitted the email application (the applicant).
   */
  public function user()
  {
    return $this->belongsTo(User::class, 'user_id'); // Explicitly define foreign key
  }

  /**
   * Get the supporting officer for the email application (the first approver).
   * FIX: Added supportingOfficer relationship.
   */
  public function supportingOfficer()
  {
    return $this->belongsTo(User::class, 'supporting_officer_id'); // Belongs to User model with supporting_officer_id foreign key
  }

  /**
   * Get the approvals for the email application (polymorphic relationship).
   * This links this application to its workflow approval records.
   */
  public function approvals()
  {
    // MorphMany relationship to the Approval model where this model is the 'approvable'
    return $this->morphMany(Approval::class, 'approvable');
  }


  // Add custom methods or accessors/mutators here as needed

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
   * Check if the application is in draft status.
   */
  public function isDraft(): bool
  {
    return $this->status === 'draft';
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
}
