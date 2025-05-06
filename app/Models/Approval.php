<?php

namespace App\Models;

use App\Traits\CreatedUpdatedDeletedBy; // Assuming this trait exists
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; // Assuming SoftDeletes trait is used
use Illuminate\Database\Eloquent\Relations\MorphTo; // For the approvable relationship
use Illuminate\Database\Eloquent\Relations\BelongsTo; // For the officer relationship
use Illuminate\Support\Carbon; // For date/datetime casts

/**
 * @property int $id
 * @property string $approvable_type
 * @property int $approvable_id
 * @property int $officer_id
 * @property string $status // e.g., 'pending', 'approved', 'rejected'
 * @property string $stage // e.g., 'support_review', 'admin_review', 'bpm_issue'
 * @property string|null $comments
 * @property Carbon|null $approval_timestamp
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property int|null $deleted_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read Model $approvable
 * @property-read User $officer
 * @property-read User|null $creator
 * @property-read User|null $editor
 * @property-read User|null $deleter
 */
class Approval extends Model
{
  use SoftDeletes, CreatedUpdatedDeletedBy; // Assuming these traits are used

  protected $fillable = [
    'approvable_type',
    'approvable_id',
    'officer_id',
    'status',
    'stage',
    'comments',
    'approval_timestamp',
    // created_by, updated_by, deleted_by handled by trait
  ];

  protected $casts = [
    'approval_timestamp' => 'datetime',
  ];

  // --- Status Constants ---
  public const STATUS_PENDING = 'pending';
  public const STATUS_APPROVED = 'approved';
  public const STATUS_REJECTED = 'rejected';
  // --- End Status Constants ---

  // --- Stage Constants ---
  public const STAGE_SUPPORT_REVIEW = 'support_review';
  public const STAGE_IT_ADMIN = 'it_admin';
  public const STAGE_BPM_ISSUE = 'bpm_issue'; // Added this missing constant
  // Add other stages like STAGE_DIRECTOR_APPROVAL etc. as needed based on workflow
  // --- End Stage Constants ---


  /**
   * Get the parent approvable model (EmailApplication or LoanApplication).
   */
  public function approvable(): MorphTo
  {
    return $this->morphTo();
  }

  /**
   * Get the officer assigned to this approval task.
   */
  public function officer(): BelongsTo
  {
    return $this->belongsTo(User::class, 'officer_id');
  }

  // Scope for pending approvals
  public function scopePending($query)
  {
    return $query->where('status', self::STATUS_PENDING);
  }

  // Scope for approved approvals
  public function scopeApproved($query)
  {
    return $query->where('status', self::STATUS_APPROVED);
  }

  // Scope for rejected approvals
  public function scopeRejected($query)
  {
    return $query->where('status', self::STATUS_REJECTED);
  }

  // Helper methods for status checks
  public function isPending(): bool
  {
    return $this->status === self::STATUS_PENDING;
  }

  public function isApproved(): bool
  {
    return $this->status === self::STATUS_APPROVED;
  }

  public function isRejected(): bool
  {
    return $this->status === self::STATUS_REJECTED;
  }

  // Add any other relationships, accessors, mutators, or methods below this line
}
