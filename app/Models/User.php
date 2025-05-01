<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\CreatedUpdatedDeletedBy;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Authenticatable
{
  use CreatedUpdatedDeletedBy,
    HasApiTokens,
    HasFactory,
    HasProfilePhoto,
    HasRoles,
    Notifiable,
    SoftDeletes,
    TwoFactorAuthenticatable;

  protected $fillable = [
    'name',
    'full_name',
    'employee_id',
    'nric',
    'mobile_number',
    'email',
    'password',
    'personal_email',
    'motac_email',
    'user_id_assigned',
    'department_id',
    'position_id',
    'grade_id',
    'service_status',
    'appointment_type',
    'is_admin',
    'is_bpm_staff',
    'status',
  ];

  protected $hidden = [
    'password',
    'remember_token',
    'two_factor_recovery_codes',
    'two_factor_secret',
    'created_by',
    'updated_by',
    'deleted_by',
    'deleted_at',
    'profile_photo_path',
  ];

  protected $casts = [
    'email_verified_at' => 'datetime',
    'mobile_verified_at' => 'datetime',
    'created_at' => 'datetime',
    'updated_at' => 'datetime',
    'deleted_at' => 'datetime',
    'is_admin' => 'boolean',
    'is_bpm_staff' => 'boolean',
    'two_factor_recovery_codes' => 'array',
  ];

  protected $appends = [
    'profile_photo_url',
  ];

  // Relationships
  public function employee(): BelongsTo
  {
    return $this->belongsTo(Employee::class);
  }

  public function department(): BelongsTo
  {
    return $this->belongsTo(Department::class);
  }

  public function position(): BelongsTo
  {
    return $this->belongsTo(Position::class);
  }

  public function grade(): BelongsTo
  {
    return $this->belongsTo(Grade::class);
  }

  public function emailApplications(): HasMany
  {
    return $this->hasMany(EmailApplication::class);
  }

  public function supportedEmailApplications(): HasMany
  {
    return $this->hasMany(EmailApplication::class, 'supporting_officer_id');
  }

  public function loanApplications(): HasMany
  {
    return $this->hasMany(LoanApplication::class);
  }

  public function responsibleLoanApplications(): HasMany
  {
    return $this->hasMany(LoanApplication::class, 'responsible_officer_id');
  }

  public function issuedTransactions(): HasMany
  {
    return $this->hasMany(LoanTransaction::class, 'issuing_officer_id');
  }

  public function receivedTransactions(): HasMany
  {
    return $this->hasMany(LoanTransaction::class, 'receiving_officer_id');
  }

  public function returningTransactions(): HasMany
  {
    return $this->hasMany(LoanTransaction::class, 'returning_officer_id');
  }

  public function acceptedReturnTransactions(): HasMany
  {
    return $this->hasMany(LoanTransaction::class, 'return_accepting_officer_id');
  }

  public function approvals(): HasMany
  {
    return $this->hasMany(Approval::class, 'officer_id');
  }

  // Helper Methods
  public function hasApprovalGrade(): bool
  {
    return $this->grade && $this->grade->level >= config('motac.approval.min_approver_grade_level', 0);
  }

  public function isAdmin(): bool
  {
    return $this->is_admin;
  }

  public function isBpmStaff(): bool
  {
    return $this->is_bpm_staff;
  }

  public function hasStatus(string $status): bool
  {
    return $this->status === $status;
  }

  public function isActive(): bool
  {
    return $this->hasStatus('active');
  }

  public function isInactive(): bool
  {
    return $this->hasStatus('inactive');
  }
}
