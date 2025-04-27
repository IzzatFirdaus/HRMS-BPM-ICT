<?php

namespace Database\Seeders;

use App\Models\User; // Import the User model
// Removed commented-out imports for Department, Position, Grade, Hash, Str
// Removed commented-out dependency checks and nested calls

use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents; // Optional trait

class UserSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    \Log::info('Seeding regular users...'); // Log start

    // Remove truncate() - migrate:fresh handles clearing tables.
    // Ensure AdminUserSeeder runs BEFORE this seeder to avoid deleting admin.

    // --- Create a batch of regular users using the User Factory ---
    // The factory will handle generating fake data, linking to random
    // Departments, Positions, Grades, and potentially Employees (if linking is in factory),
    // and populating audit columns, relying on DatabaseSeeder order.

    // Create 50 regular users.
    User::factory()->count(50)->create();
    \Log::info('Created 50 regular users.');

    // Example: Create users with specific states
    // Create some BPM staff users
    // User::factory()->count(10)->bpmStaff()->create();
    // \Log::info('Created 10 BPM staff users.');

    // Create some admin users (redundant if AdminUserSeeder runs first, use with caution)
    // User::factory()->count(2)->admin()->create();
    // \Log::info('Created 2 additional admin users (use with caution).');

    \Log::info('Regular user seeding complete.'); // Log end
  }
}
