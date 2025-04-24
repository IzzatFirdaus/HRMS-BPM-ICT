<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoanApplication extends Model
{
  use HasFactory;

  // Define fillable or guarded properties
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

  // Define cast properties
  protected $casts = [
    'loan_start_date' => 'date',
    'loan_end_date' => 'date',
    'applicant_confirmation_timestamp' => 'datetime',
  ];

  // Define relationships

  /**
   * Get the applicant user for the loan application.
   */
  public function user()
  {
    return $this->belongsTo(User::class, 'user_id');
  }

  /**
   * Get the responsible officer user for the loan application (if applicable).
   */
  public function responsibleOfficer()
  {
    return $this->belongsTo(User::class, 'responsible_officer_id');
  }

  /**
   * Get the equipment items requested for the loan application.
   */
  public function items()
  {
    return $this->hasMany(LoanApplicationItem::class);
  }

  /**
   * Get the transactions (issue/return records) for the loan application.
   */
  public function transactions()
  {
    return $this->hasMany(LoanTransaction::class);
  }

  /**
   * Get the approvals for the loan application (polymorphic).
   */
  public function approvals()
  {
    return $this->morphMany(Approval::class, 'approvable');
  }

  // Add custom methods or accessors/mutators here as needed
}
