<?php

namespace Database\Seeders;

use App\Models\Position; // Import the Position model
// Remove these imports as they are called from DatabaseSeeder directly
// use App\Models\Grade;
// use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB; // Import DB facade


class PositionSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    // Temporarily disable foreign key checks
    DB::statement('SET FOREIGN_KEY_CHECKS = 0;');

    // Clear existing data using truncate for a clean slate
    // Ensure this line correctly references your Position model or table name ('designations')
    DB::table('designations')->truncate();
    // Or using Eloquent:
    // Position::query()->truncate(); // Make sure the Position model is imported and table name is correct

    // --- Add your Position (Designation) creation logic here ---
    // This logic relies on Grades and Users already existing (seeded before this seeder is called).
    // If you are using factories, ensure your PositionFactory can handle nullable grade_id/created_by/updated_by
    // or that your DatabaseSeeder calls the GradeSeeder and UserSeeder BEFORE PositionSeeder.

    // Find a default user (e.g., the admin created by AdminUserSeeder)
    // This assumes AdminUserSeeder created a user and that user exists
    $defaultUser = \App\Models\User::first(); // Assuming UserSeeder/AdminUserSeeder ran

    // Find example grades (rely on GradesSeeder having run)
    $jusaCGrade = \App\Models\Grade::where('name', 'JUSA C')->first();
    $grade41 = \App\Models\Grade::where('name', 'F41')->first() ?? \App\Models\Grade::where('name', 'N41')->first();


    // Example specific positions
    Position::create([
      'name' => 'Ketua Bahagian BPM',
      'vacancies_count' => 1,
      'grade_id' => $jusaCGrade?->id, // Link to existing grade
      'description' => 'Head of BPM Department',
      // <--- Explicitly set created_by and updated_by to user ID or null
      'created_by' => $defaultUser?->id, // Use user ID (integer) or null
      'updated_by' => $defaultUser?->id, // Use user ID (integer) or null
      // deleted_by remains null by default
    ]);

    Position::create([
      'name' => 'Pegawai Teknologi Maklumat',
      'vacancies_count' => 5,
      'grade_id' => $grade41?->id, // Link to existing grade
      'description' => 'IT Officer',
      // <--- Explicitly set created_by and updated_by to user ID or null
      'created_by' => $defaultUser?->id, // Use user ID (integer) or null
      'updated_by' => $defaultUser?->id, // Use user ID (integer) or null
      // deleted_by remains null by default
    ]);

    // Add other specific positions as needed...


    // If using a factory, you might need to adjust the factory definition
    // or use a state that sets created_by/updated_by correctly.
    // Example factory adjustment (in your PositionFactory.php)
    // 'created_by' => \App\Models\User::factory(), // Links to a new user (less common in seeding)
    // 'created_by' => \App\Models\User::inRandomOrder()->first()?->id, // Links to an existing random user
    // 'created_by' => \App\Models\User::first()?->id, // Links to the first existing user
    // 'created_by' => null, // Sets it to null

    // Ensure your factory also sets 'updated_by' similarly.


    // Re-enable foreign key checks after all operations in this seeder are done
    DB::statement('SET FOREIGN_KEY_CHECKS = 1;');
  }
}
