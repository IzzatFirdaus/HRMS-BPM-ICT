<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Grade; // Assuming you have a Grade model

class GradesSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    // Clear existing data
    DB::table('grades')->delete();

    // Define the grades
    $grades = [
      ['name' => 'N19', 'level' => 19, 'is_approver_grade' => false],
      ['name' => 'N29', 'level' => 29, 'is_approver_grade' => false],
      ['name' => 'N41', 'level' => 41, 'is_approver_grade' => true],
      ['name' => 'N44', 'level' => 44, 'is_approver_grade' => true],
      ['name' => 'N48', 'level' => 48, 'is_approver_grade' => true],
      ['name' => 'N52', 'level' => 52, 'is_approver_grade' => true],
      ['name' => 'JUSA C', 'level' => 56, 'is_approver_grade' => true], // Example JUSA grade
      // Add other grades as needed for MOTAC
    ];

    // Insert data into the grades table
    foreach ($grades as $gradeData) {
      Grade::create($gradeData);
    }

    // Optionally, set min_approval_grade_id if applicable
    // For example, if N44 requires approval from N48 or above
    // $gradeN44 = Grade::where('name', 'N44')->first();
    // $gradeN48 = Grade::where('name', 'N48')->first();
    // if ($gradeN44 && $gradeN48) {
    //     $gradeN44->min_approval_grade_id = $gradeN48->id;
    //     $gradeN44->save();
    // }
  }
}
