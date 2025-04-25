<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User; // Import the User model for relationships

class Grade extends Model
{
  use HasFactory;

  /**
   * The attributes that are mass assignable.
   *
   * @var array<int, string>
   */
  protected $fillable = [
    'name', // e.g., 'Grade 41', 'Grade 44', 'Grade 9'
    'level', // e.g., 41, 44, 9 (integer representation for sorting/comparison)
    // Add any other relevant fields from your grades table, e.g., 'description'
  ];

  /**
   * The attributes that should be cast.
   *
   * @var array<string, string>
   */
  protected $casts = [
    'level' => 'integer', // Ensure level is cast to an integer
  ];

  /**
   * Get the users that belong to this grade.
   */
  public function users()
  {
    // Assuming the 'users' table has a 'grade_id' foreign key
    return $this->hasMany(User::class);
  }

  // Add custom methods or accessors/mutators here as needed
}
