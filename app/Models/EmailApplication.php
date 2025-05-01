<?php

namespace App\Models;

use App\Traits\CreatedUpdatedDeletedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

<<<<<<< HEAD
=======
// Import models for relationships if they are in a different namespace (User and Approval are typically in App\Models)
use App\Models\User;     // EmailApplication belongs to users (applicant, supporting officer, final assigned)
use App\Models\Approval; // EmailApplication has many Approvals (polymorphic)


/**
 * App\Models\EmailApplication
 *
 * @property int $id
 * @property int $user_id The applicant who submitted the application (Foreign key).
 * @property string $service_status Taraf Perkhidmatan (e.g., Permanent, Contract, MySTEP).
 * @property string|null $purpose Tujuan/Catatan (Text).
 * @property string|null $proposed_email Cadangan E-mel/ID (String).
 * @property string|null $group_email Nama Group Email (String).
 * @property string|null $group_admin_name Nama Admin/EO/CC (String).
 * @property string|null $group_admin_email E-mel Admin/EO/CC (String).
 * @property int|null $supporting_officer_id Supporting Officer ID (Foreign key).
 * @property string $status Workflow status (e.g., draft, pending_support, approved).
 * @property bool $certification_accepted Pengesahan Pemohon checkbox state (Boolean).
 * @property \Illuminate\Support\Carbon|null $certification_timestamp Timestamp when applicant confirmed (Timestamp).
 * @property string|null $rejection_reason Reason for rejection (Text).
 * @property string|null $final_assigned_email The actual email address assigned after provisioning (String).
 * @property int|null $final_assigned_user_id The actual User ID assigned after approval/provisioning (Foreign key).
 * @property \Illuminate\Support\Carbon|null $provisioned_at Timestamp when provisioning was completed (Timestamp).
 * @property int|null $created_by Foreign key to the user who created the record (handled by trait).
 * @property int|null $updated_by Foreign key to the user who last updated the record (handled by trait).
 * @property int|null $deleted_by Foreign key to the user who soft deleted the record (handled by trait).
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Approval> $approvals
 * @property-read int|null $approvals_count
 * @property-read \App\Models\User|null $finalAssignedUser The user who was finally assigned the email address.
 * @property-read \App\Models\User|null $supportingOfficer The supporting officer for the application.
 * @property-read \App\Models\User $user The applicant who submitted the application.
 * @property-read \App\Models\User|null $createdBy Relation to the user who created the record.
 * @property-read \App\Models\User|null $deletedBy Relation to the user who soft deleted the record.
 * @property-read \App\Models\User|null $updatedBy Relation to the user who last updated the record.
 * @method static \Illuminate\Database\Eloquent\Builder|EmailApplication newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EmailApplication newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EmailApplication onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|EmailApplication query()
 * @method static \Illuminate\Database\Eloquent\Builder|EmailApplication whereCertificationAccepted($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmailApplication whereCertificationTimestamp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmailApplication whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmailApplication whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmailApplication whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmailApplication whereDeletedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmailApplication whereFinalAssignedEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmailApplication whereFinalAssignedUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmailApplication whereGroupAdminEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmailApplication whereGroupAdminName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmailApplication whereGroupEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmailApplication whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmailApplication whereProposedEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmailApplication whereProvisionedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmailApplication wherePurpose($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmailApplication whereRejectionReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmailApplication whereServiceStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmailApplication whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmailApplication whereSupportingOfficerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmailApplication whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmailApplication whereUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmailApplication withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|EmailApplication withoutTrashed()
 * @property-read string $service_status_translated
 * @property-read string $status_translated
 * @method static \Database\Factories\EmailApplicationFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailApplication whereUserId($value)
 * @mixin \Eloquent
 */
>>>>>>> cc6eb9f4f020325c04fee080d2466584ff27bb90
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
