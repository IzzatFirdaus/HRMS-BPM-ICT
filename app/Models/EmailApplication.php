<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailApplication extends Model
{
  protected $casts = [
    'certification_accepted' => 'boolean',
    'certification_timestamp' => 'datetime',
  ];

  public function user()
  {
    return $this->belongsTo(User::class);
  }

  public function approvals()
  {
    return $this->morphMany(Approval::class, 'approvable');
  }
}
