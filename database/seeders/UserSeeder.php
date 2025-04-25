<?php

namespace Database\Seeders;

use App\Models\User; // Import the User model
use App\Models\Department; // Import Department model for linking
use App\Models\Position; // Import Position model for linking
use App\Models\Grade; // Import Grade model for linking
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash; // For hashing passwords
use Illuminate\Support\Str; // For generating random strings

class UserSeeder extends Seeder
{
  /**
   * Run the database seeds.
   *
   * @return void
   */
  public function run(): void
  {
    // Clear existing users to prevent duplicates if running multiple times.
    // Be cautious with truncate() if you have foreign key constraints from other tables
    // that are not being truncated or reset. If so, consider deleting records instead.
    User::truncate();

    // --- Create a default Admin User ---
    // This provides a known user for logging in and testing admin functionalities.
    User::create([
      'name' => 'Admin User',
      'email' => 'admin@example.com',
      'email_verified_at' => now(),
      'password' => Hash::make('password'), // Use a strong default password
      'remember_token' => Str::random(10),
      'nric' => '000000000000', // Example NRIC
      'phone_number' => '0123456789', // Example phone number
      'personal_email' => 'admin.personal@example.com', // Example personal email
      'is_admin' => true, // Mark as admin
      'is_bpm_staff' => true, // Mark as BPM staff if applicable
      // Assign default department, position, and grade if they exist.
      // Ensure DepartmentSeeder, PositionSeeder, and GradeSeeder run before this one.
      'department_id' => Department::first()?->id,
      'position_id' => Position::first()?->id,
      'grade_id' => Grade::first()?->id,
    ]);

    // --- Create some regular users using the User Factory ---
    // This is more efficient for creating multiple users with varied data.
    // Ensure you have a UserFactory defined in database/factories/UserFactory.php
    // The factory should handle assigning random departments, positions, and grades.

    // First, ensure there are enough departments, positions, and grades to link users to.
    // If not, the factory might fail. You might need to call their seeders here
    // if they are not called in DatabaseSeeder before this one.
    if (Department::count() === 0) {
      $this->call(DepartmentSeeder::class); // Assuming you have a DepartmentSeeder
    }
    if (Position::count() === 0) {
      $this->call(PositionSeeder::class); // Assuming you have a PositionSeeder
    }
    if (Grade::count() === 0) {
      $this->call(GradeSeeder::class); // Assuming you have a GradeSeeder
    }


    // Create 50 regular users using the factory
    User::factory()->count(50)->create();

    // Log a message (optional)
    // \Log::info('Users seeded successfully.');
  }
}
