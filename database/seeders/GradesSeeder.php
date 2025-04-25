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
    // Clear existing data using truncate for a clean slate
    DB::table('grades')->truncate();

    // Define a representative set of grades with names, levels, and approver status flags.
    // Levels are based on common civil service grades in Malaysia.
    $grades = [
      // Non-Approver Grades (Below Gred 41 threshold)
      ['name' => 'W19', 'level' => 19, 'is_approver_grade' => false],
      ['name' => 'N19', 'level' => 19, 'is_approver_grade' => false],
      ['name' => 'C29', 'level' => 29, 'is_approver_grade' => false],
      ['name' => 'N29', 'level' => 29, 'is_approver_grade' => false],
      ['name' => 'F32', 'level' => 32, 'is_approver_grade' => false],
      ['name' => 'N36', 'level' => 36, 'is_approver_grade' => false],
      ['name' => 'N40', 'level' => 40, 'is_approver_grade' => false],

      // Approver Grades (Gred 41 and above threshold)
      ['name' => 'F41', 'level' => 41, 'is_approver_grade' => true],
      ['name' => 'N41', 'level' => 41, 'is_approver_grade' => true], // Gred 41 is the minimum for approvers in some workflows
      ['name' => 'F44', 'level' => 44, 'is_approver_grade' => true],
      ['name' => 'N44', 'level' => 44, 'is_approver_grade' => true],
      ['name' => 'F48', 'level' => 48, 'is_approver_grade' => true],
      ['name' => 'N48', 'level' => 48, 'is_approver_grade' => true],
      ['name' => 'F52', 'level' => 52, 'is_approver_grade' => true],
      ['name' => 'N52', 'level' => 52, 'is_approver_grade' => true],
      ['name' => 'F54', 'level' => 54, 'is_approver_grade' => true],
      ['name' => 'N54', 'level' => 54, 'is_approver_grade' => true],

      // Higher Management Grades (also approvers)
      ['name' => 'JUSA C', 'level' => 56, 'is_approver_grade' => true],
      ['name' => 'JUSA B', 'level' => 58, 'is_approver_grade' => true],
      ['name' => 'JUSA A', 'level' => 60, 'is_approver_grade' => true],
      ['name' => 'Turus III', 'level' => 61, 'is_approver_grade' => true], // Example, levels may vary slightly
      ['name' => 'Turus II', 'level' => 63, 'is_approver_grade' => true],
      ['name' => 'Turus I', 'level' => 65, 'is_approver_grade' => true],

      // Add other relevant grades specific to MOTAC if known
      ['name' => 'Khas', 'level' => 0, 'is_approver_grade' => false], // Example for non-graded staff like interns before they get a proper grade
    ];

    // Insert data into the grades table using the Grade model
    foreach ($grades as $gradeData) {
      Grade::create($gradeData);
    }

    // Note: The commented-out logic for setting min_approval_grade_id from the first snippet
    // is not included here, as that column was removed from the migration based on
    // the current workflow logic which relies on the 'level' attribute and config value.
  }
}
