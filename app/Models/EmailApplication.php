<?php

namespace App\Models;

use App\Traits\CreatedUpdatedDeletedBy; // Trait for audit columns (created_by, updated_by, deleted_by)
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes; // Trait for soft deletes (deleted_at)
use Illuminate\Database\Eloquent\Builder; // Import for scope example
use Illuminate\Support\Carbon; // Import for PHPDoc
use Illuminate\Support\Facades\Lang; // Assuming you use Laravel's localization for translations


/**
 * Add PHPDoc annotations to help static analysis tools (like Intelephense)
 * recognize the dynamic properties provided by Eloquent's magic methods.
 * This helps resolve "Undefined method 'id'" and similar linter warnings.
 *
 * @property int $id
 * @property int $user_id The applicant who submitted the application.
 * @property string|null $service_status Matches enum in email_applications migration (e.g., 'Kakitangan Tetap').
 * @property string|null $purpose Tujuan Permohonan.
 * @property string|null $proposed_email Cadangan E-mel/ID.
 * @property string|null $group_email Nama Group Email.
 * @property string|null $group_admin_name Nama Admin/EO/CC.
 * @property string|null $group_admin_email E-mel Admin/EO/CC.
 * @property int|null $supporting_officer_id FK to users table (for the support reviewer).
 * @property string $status Matches enum in email_applications migration (e.g., 'draft').
 * @property bool $certification_accepted Whether the applicant accepted the certification terms.
 * @property Carbon|null $certification_timestamp Timestamp when the certification was accepted.
 * @property string|null $rejection_reason Reason for rejection if status is rejected.
 * @property string|null $final_assigned_email The email/ID finally assigned by IT Admin.
 * @property string|null $admin_notes Notes added by the IT Admin (requires column via migration).
 *
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property int|null $created_by FK to users table (audit trail).
 * @property int|null $updated_by FK to users table (audit trail).
 * @property int|null $deleted_by FK to users table (audit trail).
 *
 * // Relationships
 * @property-read User $user The applicant.
 * @property-read User|null $supportingOfficer The assigned supporting officer.
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Approval> $approvals The approvals related to this application.
 * @property-read User|null $creator The user who created this record (via CreatedUpdatedDeletedBy trait).
 * @property-read User|null $updator The user who last updated this record (via CreatedUpdatedDeletedBy trait).
 * @property-read User|null $deletor The user who deleted this record (via CreatedUpdatedDeletedBy trait).
 *
 * // Accessors
 * @property-read string $service_status_translated Translated service status.
 * @property-read string $status_translated Translated workflow status.
 *
 * @mixin \Illuminate\Database\Eloquent\Builder // Include mixin for model scopes and query builder methods
 */
class EmailApplication extends Model
{
  // --- Traits ---
  use CreatedUpdatedDeletedBy, // Assumes this trait exists and works with audit columns
    HasFactory,
    SoftDeletes; // Assumes SoftDeletes from migration

  // --- Constants ---
  // Workflow status constants - Ensure these match the ENUM in the email_applications migration!
  public const STATUS_DRAFT = 'draft';
  public const STATUS_PENDING_SUPPORT = 'pending_support';
  public const STATUS_PENDING_ADMIN = 'pending_admin';
  public const STATUS_APPROVED = 'approved';
  public const STATUS_REJECTED = 'rejected';
  public const STATUS_PROCESSING = 'processing';
  public const STATUS_COMPLETED = 'completed';

  // Service status constants - Match the ENUM in the email_applications migration's 'service_status' column.
  public const SERVICE_STATUS_KAKITANGAN_TETAP = 'Kakitangan Tetap';
  public const SERVICE_STATUS_LANTIKAN_KONTRAK = 'Lantikan Kontrak';
  public const SERVICE_STATUS_PERSONEL_MYSTEP = 'Personel MySTEP';
  public const SERVICE_STATUS_PELAJAR_INDUSTRI = 'Pelajar Latihan Industri';
  public const SERVICE_STATUS_EMEL_SANDARAN = 'E-mel Sandaran MOTAC';

  /**
   * The attributes that are mass assignable.
   *
   * @var array<int, string>
   */
  protected $fillable = [
    'user_id', // Applicant (FK to users table)
    'service_status',
    'purpose',
    'proposed_email',
    'group_email',
    'group_admin_name',
    'group_admin_email',
    'supporting_officer_id', // FK to users table (for the support reviewer)
    'status',
    'certification_accepted',
    'certification_timestamp',
    'rejection_reason',
    'final_assigned_email',
    'admin_notes', // Included as noted in the service code
    // Audit columns handled by trait, no need to include here
  ];

  /**
   * The attributes that should be cast.
   *
   * @var array<string, string>
   */
  protected $casts = [
    'certification_accepted' => 'boolean',
    'certification_timestamp' => 'datetime',
    'created_at' => 'datetime',
    'updated_at' => 'datetime',
    'deleted_at' => 'datetime',
    // Optional: Casting status enums using PHP 8.1+ Enums (requires creating App\Enums\EmailApplicationStatus and App\Enums\EmailServiceStatus)
    // 'status' => \App\Enums\EmailApplicationStatus::class,
    // 'service_status' => \App\Enums\EmailServiceStatus::class,
  ];

  // --- Relationships ---

  /**
   * Get the applicant user for the email application.
   */
  public function user(): BelongsTo
  {
    // Explicitly specify user_id for clarity, though Laravel often infers it
    return $this->belongsTo(User::class, 'user_id');
  }

  /**
   * Get the supporting officer assigned to the application.
   */
  public function supportingOfficer(): BelongsTo
  {
    return $this->belongsTo(User::class, 'supporting_officer_id');
  }

  /**
   * Get the approvals associated with the email application.
   * Assumes 'approvals' table has 'approvable_id' and 'approvable_type' columns
   */
  public function approvals(): MorphMany
  {
    return $this->morphMany(Approval::class, 'approvable');
  }

  /**
   * Get the user who created this record (via CreatedUpdatedDeletedBy trait).
   */
  public function creator(): BelongsTo
  {
    return $this->belongsTo(User::class, 'created_by');
  }

  /**
   * Get the user who last updated this record (via CreatedUpdatedDeletedBy trait).
   */
  public function updator(): BelongsTo
  {
    return $this->belongsTo(User::class, 'updated_by');
  }

  /**
   * Get the user who deleted this record (via SoftDeletes and CreatedUpdatedDeletedBy trait).
   */
  public function deletor(): BelongsTo
  {
    return $this->belongsTo(User::class, 'deleted_by');
  }

  // --- Status Check Methods ---
  // These methods provide a clear way to check the current status using constants.

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
    // Checks if status is either pending support or pending admin
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
    // Any status other than draft means it's been submitted at least once
    return !$this->isDraft();
  }

  // --- Local Scope ---
  // Allows querying for applications by status easily.
  // Example: EmailApplication::status(EmailApplication::STATUS_PENDING_ADMIN)->get();
  public function scopeStatus(Builder $query, string $status): void
  {
    $query->where('status', $status);
  }

  // --- Accessors ---
  // Provide translated versions of status fields.

  /**
   * Get the translated service status.
   *
   * @return string
   */
  public function getServiceStatusTranslatedAttribute(): string
  {
    // Use a match expression for clean mapping and __() helper for localization
    return match ($this->service_status) {
      self::SERVICE_STATUS_KAKITANGAN_TETAP => __('application.service_status.permanent_staff'), // Example translation key
      self::SERVICE_STATUS_LANTIKAN_KONTRAK => __('application.service_status.contract_staff'),
      self::SERVICE_STATUS_PERSONEL_MYSTEP => __('application.service_status.mystep_personnel'),
      self::SERVICE_STATUS_PELAJAR_INDUSTRI => __('application.service_status.intern'),
      self::SERVICE_STATUS_EMEL_SANDARAN => __('application.service_status.other_agency_staff'),
      default => __('application.service_status.unknown'), // Handles unexpected values
    };
  }

  /**
   * Get the translated workflow status.
   *
   * @return string
   */
  public function getStatusTranslatedAttribute(): string
  {
    // Use a match expression for clean mapping and __() helper for localization
    return match ($this->status) {
      self::STATUS_DRAFT => __('application.status.draft'), // Example translation key
      self::STATUS_PENDING_SUPPORT => __('application.status.pending_support_review'),
      self::STATUS_PENDING_ADMIN => __('application.status.pending_it_admin_review'),
      self::STATUS_APPROVED => __('application.status.approved'),
      self::STATUS_REJECTED => __('application.status.rejected'),
      self::STATUS_PROCESSING => __('application.status.processing'),
      self::STATUS_COMPLETED => __('application.status.completed'),
      default => __('application.status.unknown'), // Handles unexpected values
    };
  }
}
