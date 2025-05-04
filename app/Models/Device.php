<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; // If you plan to soft delete devices

class Device extends Model
{
  use HasFactory, SoftDeletes; // Add SoftDeletes if applicable

  // Add fillable or guarded properties if needed
  // protected $fillable = ['name', 'serial_number', ...];

  // Define relationships, e.g., if a Device has many Fingerprints
  // public function fingerprints()
  // {
  //     return $this->hasMany(Fingerprint::class);
  // }
}
