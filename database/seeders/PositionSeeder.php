<?php

namespace Database\Seeders;

use App\Models\Position; // Import the Position model
use App\Models\Grade; // Import Grade model (needed to ensure grades exist)
use App\Models\User; // Import User model (needed to ensure users exist)
use Illuminate\Database\Seeder;
// No need to import Faker\Factory directly if using the model factory

class PositionSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    // Ensure necessary related data exists before creating positions.
    // This is important because Position has foreign keys to 'grades' and 'users'.
    // You might need to call other seeders here if they haven't run yet.

    // Example: Ensure Grades exist (if not seeded elsewhere)
    if (Grade::count() === 0) {
      $this->call(GradeSeeder::class); // Assuming you have a GradeSeeder
    }

    // Example: Ensure Users exist (if not seeded elsewhere, especially for created_by/updated_by)
    if (User::count() === 0) {
      $this->call(UserSeeder::class); // Assuming you have a UserSeeder
    }


    // Use the Position factory to create records.
    // The factory handles generating realistic data and linking to grades/users.

    // Create 10 positions using the factory
    Position::factory()->count(10)->create();

    // You can also create specific positions if needed
    // Position::create([
    //     'name' => 'Ketua Bahagian',
    //     'vacancies_count' => 1,
    //     'grade_id' => Grade::where('name', 'JUSA C')->first()?->id,
    //     'description' => 'Head of Department',
    //     'created_by' => User::first()?->id, // Assign to the first user, or a specific user
    // ]);

    // Position::create([
    //     'name' => 'Pegawai Teknologi Maklumat',
    //     'vacancies_count' => 5,
    //     'grade_id' => Grade::where('name', 'FT29')->first()?->id,
    //     'description' => 'IT Officer',
    //     'created_by' => User::first()?->id,
    // ]);

    // You can add more specific positions as needed for your application
  }
}
