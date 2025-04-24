<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoanTransaction extends Model
{
  use HasFactory;

  // Define fillable or guarded properties
  protected $fillable = [
    'loan_application_id',
    'equipment_id',
    'issuing_officer_id',
    'receiving_officer_id',
    'accessories_checklist_on_issue',
    'issue_timestamp',
    'returning_officer_id',
    'return_accepting_officer_id',
    'accessories_checklist_on_return',
    'return_timestamp',
    'return_notes',
    'status',
  ];

  // Define cast properties
  protected $casts = [
    'accessories_checklist_on_issue' => 'json',
    'accessories_checklist_on_return' => 'json',
    'issue_timestamp' => 'datetime',
    'return_timestamp' => 'datetime',
  ];


  // Define relationships

  /**
   * Get the loan application associated with the transaction.
   */
  public function loanApplication()
  {
    return $this->belongsTo(LoanApplication::class);
  }

  /**
   * Get the specific equipment asset involved in the transaction.
   */
  public function equipment()
  {
    return $this->belongsTo(Equipment::class);
  }

  /**
   * Get the issuing officer.
   */
  public function issuingOfficer()
  {
    return $this->belongsTo(User::class, 'issuing_officer_id');
  }

  /**
   * Get the receiving officer.
   */
  public function receivingOfficer()
  {
    return $this->belongsTo(User::class, 'receiving_officer_id');
  }

  /**
   * Get the returning officer.
   */
  public function returningOfficer()
  {
    return $this->belongsTo(User::class, 'returning_officer_id');
  }

  /**
   * Get the return accepting officer.
   */
  public function returnAcceptingOfficer()
  {
    return $this->belongsTo(User::class, 'return_accepting_officer_id');
  }

  // Add custom methods or accessors/mutators here as needed
}
