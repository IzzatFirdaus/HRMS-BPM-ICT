<?php

namespace App\Models;

use App\Traits\CreatedUpdatedDeletedBy; // Assuming this trait exists
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; // Assuming SoftDeletes trait is used
use Illuminate\Database\Eloquent\Relations\MorphTo; // For the approvable relationship
use Illuminate\Database\Eloquent\Relations\BelongsTo; // For the officer relationship
use Illuminate\Support\Carbon; // For date/datetime casts
use Illuminate\Database\Eloquent\Builder; // Import Builder for scope type hinting


/**
 * @property int $id
 * @property string $approvable_type The model type of the entity being approved (e.g., 'App\Models\LoanApplication').
 * @property int $approvable_id The ID of the entity being approved.
 * @property int $officer_id The ID of the User who is the approver for this stage/record.
 * @property string $status The status of this specific approval step ('pending', 'approved', 'rejected').
 * @property string $stage The stage of the approval workflow this record represents (e.g., 'support_review', 'it_admin').
 * @property string|null $comments Comments provided by the officer.
 * @property Carbon|null $approval_timestamp Timestamp when the decision was made.
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property int|null $deleted_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read Model $approvable The parent approvable model instance.
 * @property-read User $officer The User model representing the approving officer.
 * @property-read User|null $creator
 * @property-read User|null $editor
 * @property-read User|null $deleter
 */
class Approval extends Model
{
  use SoftDeletes, CreatedUpdatedDeletedBy; // Assuming these traits are used

  // Approval status constants
  public const STATUS_PENDING = 'pending';
  public const STATUS_APPROVED = 'approved';
  public const STATUS_REJECTED = 'rejected';

  // Approval stage constants (based on workflow steps)
  public const STAGE_SUPPORT_REVIEW = 'support_review';
  public const STAGE_IT_ADMIN = 'it_admin';
  public const STAGE_BPM_ISSUE = 'bpm_issue'; // Added this missing constant if it represents an approval stage

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
    'created_at' => 'datetime',
    'updated_at' => 'datetime',
    'deleted_at' => 'datetime',
    'approvable_id' => 'integer',
    'officer_id' => 'integer',
    'status' => 'string',
    'stage' => 'string',
    'comments' => 'string',
  ];

  // If CreatedUpdatedDeletedBy trait is used, ensure these columns are fillable or handled by the trait
  // protected $guarded = []; // Or use guarded instead of fillable


  // --- Relationships ---

  /**
   * Get the parent approvable model (EmailApplication or LoanApplication).
   * Defines a polymorphic many-to-one relationship.
   * Assumes 'approvals' table has 'approvable_type' and 'approvable_id' columns.
   *
   * @return \Illuminate\Database\Eloquent\Relations\MorphTo
   */
  public function approvable(): MorphTo // Added return type hint
  {
    return $this->morphTo();
  }

  /**
   * Get the officer assigned to this approval task.
   * Defines a many-to-one relationship.
   * Assumes 'approvals' table has 'officer_id' foreign key.
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\User, \App\Models\Approval>
   */
  public function officer(): BelongsTo // Added return type hint
  {
    return $this->belongsTo(User::class, 'officer_id');
  }

  // --- Scopes ---

  /**
   * Scope to include pending approvals.
   *
   * @param  \Illuminate\Database\Eloquent\Builder  $query
   * @return \Illuminate\Database\Eloquent\Builder
   */
  public function scopePending(Builder $query): Builder // Added return type hint
  {
    return $query->where('status', self::STATUS_PENDING);
  }

  /**
   * Scope to include approved approvals.
   *
   * @param  \Illuminate\Database\Eloquent\Builder  $query
   * @return \Illuminate\Database\Eloquent\Builder
   */
  public function scopeApproved(Builder $query): Builder // Added return type hint
  {
    return $query->where('status', self::STATUS_APPROVED);
  }

  /**
   * Scope to include rejected approvals.
   *
   * @param  \Illuminate\Database\Eloquent\Builder  $query
   * @return \Illuminate\Database\Eloquent\Builder
   */
  public function scopeRejected(Builder $query): Builder // Added return type hint
  {
    return $query->where('status', self::STATUS_REJECTED);
  }

  // --- Helper methods for status checks (already present) ---
  // ...

  // Add any other custom methods, scopes, accessors/mutators...
}
