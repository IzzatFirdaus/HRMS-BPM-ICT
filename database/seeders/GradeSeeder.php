<?php

namespace Database\Seeders;

use App\Models\Grade; // Import the Grade model
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB; // Optional: Use DB facade for raw inserts if preferred, but Eloquent is fine

class GradeSeeder extends Seeder
{
  /**
   * Run the database seeds.
   *
   * @return void
   */
  public function run(): void
  {
    // Define the grades to be seeded.
    // These are examples based on typical Malaysian public sector grades.
    $grades = [
      ['name' => 'N19'],
      ['name' => 'N29'],
      ['name' => 'N36'],
      ['name' => 'N41'],
      ['name' => 'N44'],
      ['name' => 'N48'],
      ['name' => 'N52'],
      ['name' => 'N54'],
      ['name' => 'JUSA C'], // Jawatan Utama Sektor Awam C
      ['name' => 'JUSA B'], // Jawatan Utama Sektor Awam B
      ['name' => 'JUSA A'], // Jawatan Utama Sektor Awam A
      ['name' => 'Turus III'], // Gred Khas (Special Grade)
      ['name' => 'Turus II'],
      ['name' => 'Turus I'],
      ['name' => 'FT19'], // F for Technology/IT Scheme
      ['name' => 'FT29'],
      ['name' => 'FT38'],
      ['name' => 'FT41'],
      ['name' => 'FT44'],
      ['name' => 'FT48'],
      ['name' => 'FT52'],
      ['name' => 'FT54'],
      // Add other relevant grades as needed for your organization
      // e.g., W (Social), C (Technical), E (Education), etc.
    ];

    // Clear existing grades to prevent duplicates if running multiple times.
    // Use truncate() if the table is empty or you don't have foreign key constraints
    // that would be violated. If you have related records, consider deleting carefully
    // or only run this seeder once.
    Grade::truncate(); // Use truncate for a clean slate

    // Insert the grades into the database using the Grade model.
    foreach ($grades as $grade) {
      Grade::create($grade);
    }

    // Alternatively, you could use DB::table() for slightly faster bulk inserts:
    // DB::table('grades')->insert($grades);

    // Log a message (optional)
    // \Log::info('Grades seeded successfully.');
  }
}
