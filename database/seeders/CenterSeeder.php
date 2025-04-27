<?php

namespace Database\Seeders;

use App\Models\Center; // Import the Center model
use App\Models\User;  // Keep import for model definition reference in comments
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents; // Optional trait
use Faker\Factory as FakerFactory; // Import Faker Factory if needed
use Illuminate\Support\Carbon; // Import Carbon for time manipulation
use Illuminate\Support\Facades\DB; // Import DB for truncation and User query
use Illuminate\Support\Facades\Log; // Import Log facade


class CenterSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    \Log::info('Seeding centers...'); // Log start

    // Optional: Temporarily disable foreign key checks if other tables link to centers (e.g., timelines, employees)
    // If using DB::statement in DatabaseSeeder, you don't need these here.
    // DB::statement('SET FOREIGN_KEY_CHECKS = 0;');

    // Clear existing centers.
    // Use truncate for efficiency.
    // Ensure foreign key checks are off globally in DatabaseSeeder for this to work.
    DB::table('centers')->truncate();
    \Log::info('Truncated centers table.');

    // Instantiate Faker manually if needed for logic within the seeder
    // $faker = FakerFactory::create(); // Keep if you add random seeding later

    // Get the ID of the first user (often the admin) for audit columns.
    // This relies on AdminUserSeeder having run before this seeder.
    // Use DB facade to bypass Eloquent model and SoftDeletes trait for this query
    // as it runs immediately after migrations, where Eloquent might not yet
    // fully recognize the deleted_at column despite it existing.
    $adminUserForAudit = DB::table('users')->first(); // UPDATED: Use DB::table to get the first user record
    $auditUserId = $adminUserForAudit?->id ?? null; // Use null if no user exists yet

    // --- Seed Specific Centers using firstOrCreate (Recommended for standard lookups) ---
    $specificCenters = [
      [
        'name' => 'Headquarters', // Example: Main office
        'start_work_hour' => '08:00:00',
        'end_work_hour' => '17:00:00',
        'weekends' => ['Saturday', 'Sunday'], // Pass as PHP array
        'is_active' => true,
      ],
      [
        'name' => 'Branch A', // Example: A branch office
        'start_work_hour' => '08:30:00',
        'end_work_hour' => '17:30:00',
        'weekends' => ['Saturday', 'Sunday'], // Pass as PHP array
        'is_active' => true,
      ],
      [
        'name' => 'Operations Hub', // Example: A different type of center
        'start_work_hour' => '07:00:00',
        'end_work_hour' => '16:00:00',
        'weekends' => ['Sunday'], // Pass as PHP array
        'is_active' => true,
      ],
      // Add more specific centers as needed
    ];

    foreach ($specificCenters as $centerData) {
      // Use firstOrCreate to prevent duplicates if the seeder is run multiple times
      // firstOrCreate will use the Center model, which has audit columns and timestamps,
      // and (if using the trait) should handle audit columns during creation.
      Center::firstOrCreate(
        ['name' => $centerData['name']], // Find center by name
        array_merge(
          $centerData, // Add other center attributes (weekends is an array here, model cast handles JSON)
          [
            // Assign audit columns manually. The CreatedUpdatedDeletedBy trait
            // is primarily for setting these via Auth::id() in controllers/requests.
            // Manual setting in seeders using the fetched user ID is standard.
            'created_by' => $auditUserId,
            'updated_by' => $auditUserId,
            // deleted_by and deleted_at are null by default in the database schema
          ]
        )
      );
    }
    \Log::info("Ensured specific centers exist.");


    // --- Optional: Seed Additional Random Centers using a Factory ---
    // Best practice is to create a CenterFactory if you need many random centers.
    // If you create a CenterFactory, ensure its definition handles JSON encoding for 'weekends'
    // and populates audit columns correctly (e.g., by passing the user ID to the factory).

    // Example if you had a CenterFactory (and it has the array cast defined):
    // \App\Models\Center::factory()
    //     ->count(5) // Create 5 more random centers
    //     ->create(['created_by' => $auditUserId, 'updated_by' => $auditUserId]); // Pass audit user ID
    // \Log::info("Ensured random centers exist (if enabled).");

    \Log::info('Center seeding complete.'); // Log end

    // Optional: Re-enable foreign key checks if you disabled them
    // If using DB::statement in DatabaseSeeder, you don't need these here.
    // DB::statement('SET FOREIGN_KEY_CHECKS = 1;');
    // Also re-enable unique checks if you disabled them globally
    // DB::statement('SET UNIQUE_CHECKS = 1;');
  }
}
