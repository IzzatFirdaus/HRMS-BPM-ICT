<?php

namespace App\Models;

// Assuming this trait exists and adds audit FKs/methods like created_by, updated_by, deleted_by
use App\Traits\CreatedUpdatedDeletedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
// Use the relationships directly without aliasing if you don't need aliases
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
// Use SoftDeletes trait for soft deletion functionality
use Illuminate\Database\Eloquent\SoftDeletes;
// Assuming Auth is used in the trait or policies (optional include if not used directly in the model)
use Illuminate\Support\Facades\Auth;

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
 *
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Approval> $approvals
 * @property-read int|null $approvals_count
 * @property-read \App\Models\User|null $finalAssignedUser The user who was finally assigned the email address.
 * @property-read \App\Models\User|null $supportingOfficer The supporting officer for the application.
 * @property-read \App\Models\User $user The applicant who submitted the application.
 * @property-read \App\Models\User|null $createdBy Relation to the user who created the record.
 * @property-read \App\Models\User|null $deletedBy Relation to the user who soft deleted the record.
 * @property-read \App\Models\User|null $updatedBy Relation to the user who last updated the record.
 *
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
 * @mixin \Eloquent
 */
class EmailApplication extends Model
{
  // Use the traits for factory, soft deletes, and audit columns
  use CreatedUpdatedDeletedBy, HasFactory, SoftDeletes;

  // Define constants for application statuses for better code readability and maintainability
  public const STATUS_DRAFT = 'draft';
  public const STATUS_PENDING_SUPPORT = 'pending_support'; // Pending review by supporting officer
  public const STATUS_PENDING_ADMIN = 'pending_admin';   // Pending review/action by IT Admin
  public const STATUS_APPROVED = 'approved';       // Approved by all necessary parties
  public const STATUS_REJECTED = 'rejected';       // Rejected at some stage
  public const STATUS_PROCESSING = 'processing';     // Being provisioned by system/IT
  public const STATUS_COMPLETED = 'completed';       // Provisioning successful and application closed

  // Define constants for service statuses (Taraf Perkhidmatan) based on PDF
  public const SERVICE_STATUS_PERMANENT = 'permanent';     // Kakitangan Tetap
  public const SERVICE_STATUS_CONTRACT = 'contract';       // Lantikan Kontrak
  public const SERVICE_STATUS_MYSTEP = 'mystep';         // Personel MySTEP
  public const SERVICE_STATUS_INTERN = 'intern';         // Pelajar Latihan Industri (ID Pengguna Sahaja)
  public const SERVICE_STATUS_OTHER_AGENCY = 'other_agency'; // Kakitangan agensi lain (E-mel Sandaran Sahaja)
  // Add other service statuses as needed


  /**
   * The attributes that are mass assignable.
   * Includes all workflow and data fields from the migration.
   * 'id' is excluded as it is the primary key.
   *
   * @var array<int, string>
   */
  protected $fillable = [
    'user_id',               // The applicant who submitted the application (Foreign key)
    'service_status',        // Taraf Perkhidmatan (String/Enum)
    'purpose',               // Tujuan/Catatan (Text)
    'proposed_email',        // Cadangan E-mel/ID (String)
    'group_email',           // Nama Group Email (String)
    'group_admin_name',      // Nama Admin/EO/CC (String)
    'group_admin_email',     // E-mel Admin/EO/CC (String)
    'supporting_officer_id', // Supporting Officer ID (Foreign key)
    'status',                // Workflow status (String/Enum)
    'certification_accepted', // Pengesahan Pemohon checkbox state (Boolean)
    'certification_timestamp', // Timestamp when applicant confirmed (Timestamp)
    'rejection_reason',      // Reason for rejection (Text)
    'final_assigned_email',  // The actual email address assigned after provisioning (String)
    'final_assigned_user_id', // The actual User ID assigned after approval/provisioning (Foreign key)
    'provisioned_at',        // Timestamp when provisioning was completed (Timestamp)

    // 'created_by', // Handled by trait
    // 'updated_by', // Handled by trait
    // 'deleted_by', // Handled by trait
  ];

  /**
   * The attributes that should be cast.
   * Ensures data types are correct and dates are Carbon instances.
   *
   * @var array<string, string>
   */
  protected $casts = [
    'user_id'               => 'integer',   // Cast FKs to integer
    'supporting_officer_id' => 'integer',
    'final_assigned_user_id' => 'integer',

    'service_status'        => 'string',    // Cast service status as string (or to ServiceStatus::class if using PHP Enums)
    'purpose'               => 'string',    // Cast purpose as string
    'proposed_email'        => 'string',    // Cast proposed_email as string
    'group_email'           => 'string',    // Cast group_email as string
    'group_admin_name'      => 'string',    // Cast group_admin_name as string
    'group_admin_email'     => 'string',    // Cast group_admin_email as string
    'status'                => 'string',    // Cast status as string (or to ApplicationStatus::class if using PHP Enums)
    'rejection_reason'      => 'string',    // Cast rejection_reason as string
    'final_assigned_email'  => 'string',    // Cast final_assigned_email as string


    'certification_accepted' => 'boolean',   // Cast boolean flag
    'certification_timestamp' => 'datetime', // Cast timestamp to Carbon instance
    'provisioned_at'        => 'datetime',  // Cast provisioned_at timestamp

    'created_at'            => 'datetime',  // Explicitly cast timestamps
    'updated_at'            => 'datetime',
    'deleted_at'            => 'datetime',  // Cast soft delete timestamp
  ];


  // The CreatedUpdatedDeletedBy trait is assumed to add these audit relationships:
  // public function createdBy(): BelongsTo;
  // public function updatedBy(): BelongsTo;
  // public function deletedBy(): BelongsTo;


  // 👉 Relationships

  /**
   * Get the user who submitted the email application (the applicant).
   * Defines a many-to-one relationship where an EmailApplication belongs to one User.
   * Assumes the 'email_applications' table has a 'user_id' foreign key.
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
   */
  public function user(): BelongsTo // Added return type hint
  {
    // Defines a many-to-one relationship with the User model
    return $this->belongsTo(User::class, 'user_id'); // Explicitly define foreign key
  }

  /**
   * Get the supporting officer for the email application (the first approver).
   * Defines a many-to-one relationship where an EmailApplication belongs to one User.
   * Assumes the 'email_applications' table has a 'supporting_officer_id' foreign key.
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
   */
  public function supportingOfficer(): BelongsTo // Added return type hint
  {
    // Defines a many-to-one relationship with the User model
    return $this->belongsTo(User::class, 'supporting_officer_id'); // Belongs to User model with supporting_officer_id foreign key
  }

  /**
   * Get the user who was finally assigned the email address (if applicable).
   * This is likely the same as the applicant user, but the FK allows flexibility.
   * Defines a many-to-one relationship where an EmailApplication belongs to one User.
   * Assumes the 'email_applications' table has a 'final_assigned_user_id' foreign key.
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
   */
  public function finalAssignedUser(): BelongsTo // Added return type hint
  {
    // Defines a many-to-one relationship with the User model
    return $this->belongsTo(User::class, 'final_assigned_user_id'); // Belongs to User model with final_assigned_user_id foreign key
  }

  /**
   * Get the approvals for the email application (polymorphic relationship).
   * This links this application to its workflow approval records.
   * Defines a one-to-many polymorphic relationship where an EmailApplication has many Approvals.
   * Assumes the 'approvals' table has 'approvable_id' and 'approvable_type' columns.
   *
   * @return \Illuminate\Database\Eloquent\Relations\MorphMany
   */
  public function approvals(): MorphMany // Added return type hint
  {
    // MorphMany relationship to the Approval model where this model is the 'approvable'
    return $this->morphMany(Approval::class, 'approvable');
  }


  // 👉 Helper Methods (Workflow Status Checks)

  /**
   * Check if the application is in draft status.
   *
   * @return bool
   */
  public function isDraft(): bool // Added return type hint
  {
    return $this->status === self::STATUS_DRAFT; // Use constant
  }

  /**
   * Check if the application is in a pending status requiring support review.
   *
   * @return bool
   */
  public function isPendingSupport(): bool // Added return type hint
  {
    return $this->status === self::STATUS_PENDING_SUPPORT; // Use constant
  }

  /**
   * Check if the application is in a pending status requiring IT admin review.
   *
   * @return bool
   */
  public function isPendingAdmin(): bool // Added return type hint
  {
    return $this->status === self::STATUS_PENDING_ADMIN; // Use constant
  }

  /**
   * Check if the application is currently pending any approval step.
   * This could be support review or IT admin review.
   *
   * @return bool
   */
  public function isPendingApproval(): bool // Added return type hint
  {
    return in_array($this->status, [self::STATUS_PENDING_SUPPORT, self::STATUS_PENDING_ADMIN]); // Use constants
  }

  /**
   * Check if the application has been approved.
   *
   * @return bool
   */
  public function isApproved(): bool // Added return type hint
  {
    return $this->status === self::STATUS_APPROVED; // Use constant
  }

  /**
   * Check if the application has been rejected.
   *
   * @return bool
   */
  public function isRejected(): bool // Added return type hint
  {
    return $this->status === self::STATUS_REJECTED; // Use constant
  }

  /**
   * Check if the application is in the process of being provisioned.
   *
   * @return bool
   */
  public function isProcessing(): bool // Added return type hint
  {
    return $this->status === self::STATUS_PROCESSING; // Use constant
  }

  /**
   * Check if the application has been completed (provisioning successful and application closed).
   *
   * @return bool
   */
  public function isCompleted(): bool // Added return type hint
  {
    return $this->status === self::STATUS_COMPLETED; // Use constant
  }

  /**
   * Check if the application has been submitted (is not in draft status).
   *
   * @return bool
   */
  public function isSubmitted(): bool // Added helper method
  {
    return $this->status !== self::STATUS_DRAFT; // Use constant
  }


  // Add custom methods or accessors/mutators here as needed

  /**
   * Get the translated service status string.
   *
   * @return string
   */
  public function getServiceStatusTranslatedAttribute(): string // Added accessor for translated service status
  {
    // Use a match statement for cleaner status translation
    return match ($this->service_status) {
      self::SERVICE_STATUS_PERMANENT => __('Permanent Staff'),
      self::SERVICE_STATUS_CONTRACT => __('Contract Staff'),
      self::SERVICE_STATUS_MYSTEP    => __('MySTEP Personnel'),
      self::SERVICE_STATUS_INTERN    => __('Intern'),
      self::SERVICE_STATUS_OTHER_AGENCY => __('Other Agency Staff'),
      default                       => $this->service_status, // Return raw status if unknown
    };
  }

  /**
   * Get the translated workflow status string.
   *
   * @return string
   */
  public function getStatusTranslatedAttribute(): string // Added accessor for translated workflow status
  {
    // Use a match statement for cleaner status translation
    return match ($this->status) {
      self::STATUS_DRAFT          => __('Draft'),
      self::STATUS_PENDING_SUPPORT => __('Pending Support Review'),
      self::STATUS_PENDING_ADMIN  => __('Pending IT Admin Review'),
      self::STATUS_APPROVED       => __('Approved'),
      self::STATUS_REJECTED       => __('Rejected'),
      self::STATUS_PROCESSING     => __('Processing'),
      self::STATUS_COMPLETED      => __('Completed'),
      default                     => $this->status, // Return raw status if unknown
    };
  }
}
