<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailApplication extends Model
{
  use HasFactory;

  // Define fillable or guarded properties for mass assignment
  protected $fillable = [
    'user_id',
    'purpose',
    'proposed_email',
    'group_email',
    'group_admin_name',
    'group_admin_email',
    'status',
    'certification_accepted',
    'certification_timestamp',
    'rejection_reason',
    'final_assigned_email',
    'final_assigned_user_id',
  ];

  // Define cast properties for attribute casting
  protected $casts = [
    'certification_accepted' => 'boolean',
    'certification_timestamp' => 'datetime',
  ];

  // Define relationships

  /**
   * Get the user who submitted the email application.
   */
  public function user()
  {
    return $this->belongsTo(User::class);
  }

  /**
   * Get the approvals for the email application (polymorphic relationship).
   */
  public function approvals()
  {
    // MorphMany relationship to the Approval model
    return $this->morphMany(Approval::class, 'approvable');
  }

  // Add custom methods or accessors/mutators here as needed
  /**
   * Check if the application is in a pending status requiring support review.
   */
  public function isPendingSupport(): bool
  {
    return $this->status === 'pending_support';
  }

  /**
   * Check if the application is in a pending status requiring IT admin review.
   */
  public function isPendingAdmin(): bool
  {
    return $this->status === 'pending_admin';
  }

  /**
   * Check if the application is in draft status.
   */
  public function isDraft(): bool
  {
    return $this->status === 'draft';
  }

  /**
   * Check if the application has been completed (provisioned).
   */
  public function isCompleted(): bool
  {
    return $this->status === 'completed';
  }
}
