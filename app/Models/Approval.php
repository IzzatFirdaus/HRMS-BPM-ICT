<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Approval extends Model
{
  use HasFactory;

  // Define fillable or guarded properties
  protected $fillable = [
    'approvable_id',
    'approvable_type',
    'officer_id',
    'status',
    'comments',
    'approval_timestamp',
  ];

  // Define cast properties
  protected $casts = [
    'approval_timestamp' => 'datetime',
  ];

  // Define relationships

  /**
   * Get the approvable model (EmailApplication or LoanApplication) that the approval belongs to.
   */
  public function approvable()
  {
    // Define the polymorphic relationship
    return $this->morphTo();
  }

  /**
   * Get the user (officer) who made the approval decision.
   */
  public function officer()
  {
    return $this->belongsTo(User::class, 'officer_id');
  }

  // Add custom methods or accessors/mutators here as needed
}
