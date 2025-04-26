<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Grade; // Assuming you have a Grade model
// use Illuminate\Database\Console\Seeds\WithoutModelEvents; // Uncomment if you want to use the trait

class GradesSeeder extends Seeder
{
  // use WithoutModelEvents; // Uncomment if you want to use the trait

  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    // Temporarily disable foreign key checks to allow truncation/deletion
    // This was added in the previous step and should remain
    DB::statement('SET FOREIGN_KEY_CHECKS = 0;');

    // Clear existing data using truncate for a clean slate
    DB::table('grades')->truncate();

    // Define a representative set of grades with names, levels, and approver status flags.
    // Ensure each entry has a UNIQUE 'level' value to satisfy the database constraint.
    $grades = [
      // Non-Approver Grades (Below Gred 41 threshold) - Ensure unique levels
      ['name' => 'W19', 'level' => 19, 'is_approver_grade' => false], // Using W19 for level 19
      ['name' => 'C29', 'level' => 29, 'is_approver_grade' => false],
      ['name' => 'F32', 'level' => 32, 'is_approver_grade' => false],
      ['name' => 'N36', 'level' => 36, 'is_approver_grade' => false],
      ['name' => 'N40', 'level' => 40, 'is_approver_grade' => false],

      // Approver Grades (Gred 41 and above threshold) - Ensure unique levels
      ['name' => 'N41', 'level' => 41, 'is_approver_grade' => true], // Using N41 for level 41
      ['name' => 'N44', 'level' => 44, 'is_approver_grade' => true], // Using N44 for level 44
      ['name' => 'N48', 'level' => 48, 'is_approver_grade' => true], // Using N48 for level 48
      ['name' => 'N52', 'level' => 52, 'is_approver_grade' => true], // Using N52 for level 52
      ['name' => 'N54', 'level' => 54, 'is_approver_grade' => true], // Using N54 for level 54

      // Higher Management Grades (also approvers) - Ensure unique levels
      ['name' => 'JUSA C', 'level' => 56, 'is_approver_grade' => true], // JUSA C is level 56, B is 58, A is 60
      ['name' => 'JUSA B', 'level' => 58, 'is_approver_grade' => true],
      ['name' => 'JUSA A', 'level' => 60, 'is_approver_grade' => true],
      ['name' => 'Turus III', 'level' => 61, 'is_approver_grade' => true], // Example levels
      ['name' => 'Turus II', 'level' => 63, 'is_approver_grade' => true],
      ['name' => 'Turus I', 'level' => 65, 'is_approver_grade' => true],

      // Add other relevant grades specific to MOTAC if known - Ensure unique levels
      ['name' => 'Khas', 'level' => 0, 'is_approver_grade' => false], // Example for non-graded staff
      // Make sure any other added grades also have unique levels
    ];

    // Insert data into the grades table using the Grade model
    foreach ($grades as $gradeData) {
      Grade::create($gradeData);
    }

    // Re-enable foreign key checks after all operations in this seeder are done
    DB::statement('SET FOREIGN_KEY_CHECKS = 1;');
  }
}
