<?php

namespace Database\Seeders;

use App\Models\Position; // Import the Position model
use App\Models\Grade; // Import the Grade model for finding grade IDs
// Removed App\Models\User import for strict DB facade usage in the user query
// use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB; // Import DB facade
use Illuminate\Support\Facades\Log; // Import Log for logging
use Illuminate\Database\Console\Seeds\WithoutModelEvents; // Optional trait


class PositionSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    \Log::info('Seeding positions...'); // Log start

    // Temporarily disable foreign key checks (if not handled globally in DatabaseSeeder)
    // This is needed if other tables link to 'positions'.
    DB::statement('SET FOREIGN_KEY_CHECKS = 0;');

    // Clear existing data using truncate for a clean slate
    // This should truncate the 'positions' table.
    DB::table('positions')->truncate(); // Corrected: Truncate the 'positions' table
    \Log::info('Truncated positions table.');


    // Get the ID of the first user (often the admin) for audit columns.
    // Use DB facade to bypass Eloquent model and SoftDeletes trait for this query.
    // This relies on AdminUserSeeder having run successfully before this.
    // The users table and its deleted_at column should exist by this point.
    $adminUserForAudit = DB::table('users')->first(); // Use DB::table to get the first user record
    $auditUserId = $adminUserForAudit?->id ?? null; // Use null if no user exists yet

    // Find example grades (rely on GradesSeeder having run successfully before this)
    // Use the Grade model to find grade IDs for linking positions.
    $jusaCGrade = Grade::where('name', 'JUSA C')->first();
    // Ensure the grade name 'F41' or 'N41' exists in your GradesSeeder data
    $grade41 = Grade::where('name', 'F41')->first() ?? Grade::where('name', 'N41')->first();
    // Find other grades as needed for linking positions...


    // Example specific positions using the Position model
    // Ensure 'grade_id', 'created_by', 'updated_by' are included in the create data.
    Position::create([
      'name' => 'Ketua Bahagian BPM',
      'vacancies_count' => 1,
      'grade_id' => $jusaCGrade?->id, // Link to existing grade ID or null
      'description' => 'Head of BPM Department', // Include description
      // Explicitly set created_by and updated_by to the audit user ID or null
      'created_by' => $auditUserId, // Use user ID (integer) or null
      'updated_by' => $auditUserId, // Use user ID (integer) or null
      // deleted_by remains null by default
    ]);

    Position::create([
      'name' => 'Pegawai Teknologi Maklumat',
      'vacancies_count' => 5,
      'grade_id' => $grade41?->id, // Link to existing grade ID or null
      'description' => 'IT Officer', // Include description
      // Explicitly set created_by and updated_by to the audit user ID or null
      'created_by' => $auditUserId, // Use user ID (integer) or null
      'updated_by' => $auditUserId, // Use user ID (integer) or null
      // deleted_by remains null by default
    ]);

    // Add other specific positions as needed...
    // Remember to find the correct grade ID for each position if linking.


    // If using a factory (PositionFactory), ensure its definition includes:
    // 'name' => $this->faker->unique()->jobTitle(), // Example unique position name
    // 'vacancies_count' => $this->faker->numberBetween(1, 10),
    // 'grade_id' => \App\Models\Grade::inRandomOrder()->first()?->id, // Links to an existing random grade
    // 'description' => $this->faker->sentence(), // Example description
    // 'created_by' => \App\Models\User::first()?->id, // Links to the first existing user (admin)
    // 'updated_by' => \App\Models\User::first()?->id, // Links to the first existing user (admin)
    // 'deleted_by' => null, // Explicitly null if using soft deletes


    \Log::info('Positions seeded successfully.'); // Log success

    // Re-enable foreign key checks (if disabled above)
    DB::statement('SET FOREIGN_KEY_CHECKS = 1;');
  }
}
