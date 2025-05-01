<?php

namespace App\Models;

use App\Traits\CreatedUpdatedDeletedBy;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class LoanApplication extends Model
{
  use CreatedUpdatedDeletedBy, HasFactory, SoftDeletes;

  // Workflow status constants
  public const STATUS_DRAFT = 'draft';
  public const STATUS_PENDING_SUPPORT = 'pending_support';
  public const STATUS_PENDING_BPM = 'pending_bpm';
  public const STATUS_APPROVED = 'approved';
  public const STATUS_REJECTED = 'rejected';
  public const STATUS_ISSUED = 'issued';
  public const STATUS_RETURNED = 'returned';
  public const STATUS_OVERDUE = 'overdue';
  public const STATUS_PARTIALLY_ISSUED = 'partially_issued';

  protected $fillable = [
    'user_id',
    'responsible_officer_id',
    'purpose',
    'location',
    'loan_start_date',
    'loan_end_date',
    'status',
    'rejection_reason',
    'applicant_confirmation_timestamp',
  ];

  protected $casts = [
    'user_id' => 'integer',
    'responsible_officer_id' => 'integer',
    'purpose' => 'string',
    'location' => 'string',
    'status' => 'string',
    'rejection_reason' => 'string',
    'loan_start_date' => 'date',
    'loan_end_date' => 'date',
    'applicant_confirmation_timestamp' => 'datetime',
    'created_at' => 'datetime',
    'updated_at' => 'datetime',
    'deleted_at' => 'datetime',
  ];

  // Relationships
  public function user(): BelongsTo
  {
    return $this->belongsTo(User::class);
  }

  public function responsibleOfficer(): BelongsTo
  {
    return $this->belongsTo(User::class, 'responsible_officer_id');
  }

  public function items(): HasMany
  {
    return $this->hasMany(LoanApplicationItem::class);
  }

  public function transactions(): HasMany
  {
    return $this->hasMany(LoanTransaction::class);
  }

  public function approvals(): MorphMany
  {
    return $this->morphMany(Approval::class, 'approvable');
  }

  // Status checks
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

  public function isPendingBpm(): bool
  {
    return $this->hasStatus(self::STATUS_PENDING_BPM);
  }

  public function isPendingApproval(): bool
  {
    return in_array($this->status, [self::STATUS_PENDING_SUPPORT, self::STATUS_PENDING_BPM]);
  }

  public function isApproved(): bool
  {
    return $this->hasStatus(self::STATUS_APPROVED);
  }

  public function isRejected(): bool
  {
    return $this->hasStatus(self::STATUS_REJECTED);
  }

  public function isPartiallyIssued(): bool
  {
    return $this->hasStatus(self::STATUS_PARTIALLY_ISSUED);
  }

  public function isIssued(): bool
  {
    return $this->hasStatus(self::STATUS_ISSUED);
  }

  public function isReturned(): bool
  {
    return $this->hasStatus(self::STATUS_RETURNED);
  }

  public function isOverdue(): bool
  {
    return $this->hasStatus(self::STATUS_OVERDUE);
  }

  public function isClosed(): bool
  {
    return in_array($this->status, [self::STATUS_RETURNED, self::STATUS_REJECTED]);
  }

  public function isSubmitted(): bool
  {
    return !$this->isDraft();
  }

  // Accessors
  public function getStatusTranslatedAttribute(): string
  {
    return match ($this->status) {
      self::STATUS_DRAFT => __('Draft'),
      self::STATUS_PENDING_SUPPORT => __('Pending Support Review'),
      self::STATUS_PENDING_BPM => __('Pending BPM Action'),
      self::STATUS_APPROVED => __('Approved'),
      self::STATUS_REJECTED => __('Rejected'),
      self::STATUS_PARTIALLY_ISSUED => __('Partially Issued'),
      self::STATUS_ISSUED => __('Issued'),
      self::STATUS_RETURNED => __('Returned'),
      self::STATUS_OVERDUE => __('Overdue'),
      default => __('Unknown Status'),
    };
  }
}
