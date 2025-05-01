<?php

namespace App\Models;

use App\Traits\CreatedUpdatedDeletedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmailApplication extends Model
{
  use CreatedUpdatedDeletedBy, HasFactory, SoftDeletes;

  // Workflow status constants
  public const STATUS_DRAFT = 'draft';
  public const STATUS_PENDING_SUPPORT = 'pending_support';
  public const STATUS_PENDING_ADMIN = 'pending_admin';
  public const STATUS_APPROVED = 'approved';
  public const STATUS_REJECTED = 'rejected';
  public const STATUS_PROCESSING = 'processing';
  public const STATUS_COMPLETED = 'completed';

  // Service status constants
  public const SERVICE_STATUS_PERMANENT = 'permanent';
  public const SERVICE_STATUS_CONTRACT = 'contract';
  public const SERVICE_STATUS_MYSTEP = 'mystep';
  public const SERVICE_STATUS_INTERN = 'intern';
  public const SERVICE_STATUS_OTHER_AGENCY = 'other_agency';

  protected $fillable = [
    'user_id',
    'service_status',
    'purpose',
    'proposed_email',
    'group_email',
    'group_admin_name',
    'group_admin_email',
    'supporting_officer_id',
    'status',
    'certification_accepted',
    'certification_timestamp',
    'rejection_reason',
    'final_assigned_email',
    'final_assigned_user_id',
    'provisioned_at',
  ];

  protected $casts = [
    'user_id' => 'integer',
    'supporting_officer_id' => 'integer',
    'final_assigned_user_id' => 'integer',
    'service_status' => 'string',
    'purpose' => 'string',
    'proposed_email' => 'string',
    'group_email' => 'string',
    'group_admin_name' => 'string',
    'group_admin_email' => 'string',
    'status' => 'string',
    'rejection_reason' => 'string',
    'final_assigned_email' => 'string',
    'certification_accepted' => 'boolean',
    'certification_timestamp' => 'datetime',
    'provisioned_at' => 'datetime',
    'created_at' => 'datetime',
    'updated_at' => 'datetime',
    'deleted_at' => 'datetime',
  ];

  // Relationships
  public function user(): BelongsTo
  {
    return $this->belongsTo(User::class);
  }

  public function supportingOfficer(): BelongsTo
  {
    return $this->belongsTo(User::class, 'supporting_officer_id');
  }

  public function finalAssignedUser(): BelongsTo
  {
    return $this->belongsTo(User::class, 'final_assigned_user_id');
  }

  public function approvals(): MorphMany
  {
    return $this->morphMany(Approval::class, 'approvable');
  }

  // Status check methods
  public function hasStatus(string $status): bool
  {
    return $this->status === $status;
  }

  public function isDraft(): bool
  {
    return $this->hasStatus(self::STATUS_DRAFT);
  }

  public function isPendingSupport(): bool
  {
    return $this->hasStatus(self::STATUS_PENDING_SUPPORT);
  }

  public function isPendingAdmin(): bool
  {
    return $this->hasStatus(self::STATUS_PENDING_ADMIN);
  }

  public function isPendingApproval(): bool
  {
    return in_array($this->status, [self::STATUS_PENDING_SUPPORT, self::STATUS_PENDING_ADMIN]);
  }

  public function isApproved(): bool
  {
    return $this->hasStatus(self::STATUS_APPROVED);
  }

  public function isRejected(): bool
  {
    return $this->hasStatus(self::STATUS_REJECTED);
  }

  public function isProcessing(): bool
  {
    return $this->hasStatus(self::STATUS_PROCESSING);
  }

  public function isCompleted(): bool
  {
    return $this->hasStatus(self::STATUS_COMPLETED);
  }

  public function isSubmitted(): bool
  {
    return !$this->isDraft();
  }

  // Accessors
  public function getServiceStatusTranslatedAttribute(): string
  {
    return match ($this->service_status) {
      self::SERVICE_STATUS_PERMANENT => __('Permanent Staff'),
      self::SERVICE_STATUS_CONTRACT => __('Contract Staff'),
      self::SERVICE_STATUS_MYSTEP => __('MySTEP Personnel'),
      self::SERVICE_STATUS_INTERN => __('Intern'),
      self::SERVICE_STATUS_OTHER_AGENCY => __('Other Agency Staff'),
      default => __('Unknown Service Status'),
    };
  }

  public function getStatusTranslatedAttribute(): string
  {
    return match ($this->status) {
      self::STATUS_DRAFT => __('Draft'),
      self::STATUS_PENDING_SUPPORT => __('Pending Support Review'),
      self::STATUS_PENDING_ADMIN => __('Pending IT Admin Review'),
      self::STATUS_APPROVED => __('Approved'),
      self::STATUS_REJECTED => __('Rejected'),
      self::STATUS_PROCESSING => __('Processing'),
      self::STATUS_COMPLETED => __('Completed'),
      default => __('Unknown Status'),
    };
  }
}
