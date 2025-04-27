<?php

namespace Database\Seeders;

use App\Models\Grade; // Import the Grade model
// We will use the DB facade instead of the User model directly for the early query
// REMOVE or comment out the User model import: use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB; // Import the DB facade
use Illuminate\Support\Facades\Log; // Import Log for logging
use Illuminate\Database\Console\Seeds\WithoutModelEvents; // Optional trait


class GradesSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    \Log::info('Seeding grades...'); // Log start

    // Temporarily disable foreign key checks to allow truncation/deletion
    // This is necessary if other tables have foreign keys pointing to 'grades' (e.g., 'positions', 'users').
    // While handling this globally in DatabaseSeeder is often preferred,
    // keeping it here ensures this seeder can run independently if needed.
    DB::statement('SET FOREIGN_KEY_CHECKS = 0;');

    // Clear existing data using truncate for a clean slate
    // This is suitable for a lookup table like this.
    DB::table('grades')->truncate();
    \Log::info('Truncated grades table.');

    // Get the ID of the first user (often the admin) for audit columns.
    // Use DB facade to bypass Eloquent model and SoftDeletes trait for this query.
    // This relies on AdminUserSeeder having run successfully before this.
    // The users table and its deleted_at column should exist by this point.
    $adminUserForAudit = DB::table('users')->first(); // UPDATED: Use DB::table to get the first user record
    $auditUserId = $adminUserForAudit?->id ?? null; // Use null if no user exists yet

    // Define a representative set of grades with names, levels, and approver status flags.
    // Ensure each entry has a UNIQUE 'level' value if the database constraint exists.
    // Ensure each entry has a UNIQUE 'name' value if the database constraint exists.
    $grades = [
      // Non-Approver Grades (Below Gred 41 threshold) - Ensure unique levels and names
      ['name' => 'W19', 'level' => 19, 'is_approver_grade' => false], // Using W19 for level 19
      ['name' => 'C29', 'level' => 29, 'is_approver_grade' => false],
      ['name' => 'F32', 'level' => 32, 'is_approver_grade' => false],
      ['name' => 'N36', 'level' => 36, 'is_approver_grade' => false],
      ['name' => 'N40', 'level' => 40, 'is_approver_grade' => false],

      // Approver Grades (Gred 41 and above threshold) - Ensure unique levels and names
      ['name' => 'N41', 'level' => 41, 'is_approver_grade' => true], // Using N41 for level 41
      ['name' => 'N44', 'level' => 44, 'is_approver_grade' => true], // Using N44 for level 44
      ['name' => 'N48', 'level' => 48, 'is_approver_grade' => true], // Using N48 for level 48
      ['name' => 'N52', 'level' => 52, 'is_approver_grade' => true], // Using N52 for level 52
      ['name' => 'N54', 'level' => 54, 'is_approver_grade' => true], // Using N54 for level 54

      // Higher Management Grades (also approvers) - Ensure unique levels and names
      // Note: Levels for JUSA/Turus can vary, these are examples.
      ['name' => 'JUSA C', 'level' => 56, 'is_approver_grade' => true], // JUSA C is often linked to level 56
      ['name' => 'JUSA B', 'level' => 58, 'is_approver_grade' => true], // JUSA B is often linked to level 58
      ['name' => 'JUSA A', 'level' => 60, 'is_approver_grade' => true], // JUSA A is often linked to level 60
      ['name' => 'Turus III', 'level' => 61, 'is_approver_grade' => true], // Example levels for Turus
      ['name' => 'Turus II', 'level' => 63, 'is_approver_grade' => true],
      ['name' => 'Turus I', 'level' => 65, 'is_approver_grade' => true], // Corrected typo from 'name's'

      // Add other relevant grades specific to MOTAC if known - Ensure unique levels and names
      ['name' => 'Khas', 'level' => 0, 'is_approver_grade' => false], // Example for non-graded staff or special cases
      // Make sure any other added grades also have unique names and unique levels
    ];

    foreach ($grades as $gradeData) {
      // Use firstOrCreate to prevent duplicates if the seeder is run multiple times
      // firstOrCreate will use the Grade model, which has audit columns and timestamps.
      // This should be fine as the Grade model does not have SoftDeletes.
      Grade::firstOrCreate(
        // Find record by name (assuming name is unique)
        ['name' => $gradeData['name']],
        array_merge(
          $gradeData, // Add level and is_approver_grade
          [
            // Assign audit columns
            // CreatedUpdatedDeletedBy trait is assumed to handle setting these
            // automatically based on Auth::id() in controllers/requests.
            // For seeders, manually setting is safer here using the fetched admin user ID.
            'created_by' => $auditUserId,
            'updated_by' => $auditUserId,
            // deleted_by and deleted_at are null by default in the database schema
          ]
        )
      );
      // Optional: Log each grade created/found
      // \Log::info("Grade ensured: {$gradeData['name']} (Level {$gradeData['level']})");
    }

    \Log::info('Grades seeded successfully.'); // Log success

    // Re-enable foreign key checks after all operations in this seeder are done
    // This should ideally be handled globally in DatabaseSeeder.
    DB::statement('SET FOREIGN_KEY_CHECKS = 1;');
    // Also re-enable unique checks if you disabled them globally
    // DB::statement('SET UNIQUE_CHECKS = 1;');
  }
}
