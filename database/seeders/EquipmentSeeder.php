<?php

namespace Database\Seeders;

use App\Models\Equipment; // Import the Equipment model
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents; // Optional trait
use Illuminate\Support\Facades\DB; // Import DB facade for foreign key checks

class EquipmentSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    \Log::info('Seeding equipment...'); // Log start

    // Temporarily disable foreign key checks to allow truncation/deletion
    // This is necessary if other tables have foreign keys pointing to 'equipment'.
    DB::statement('SET FOREIGN_KEY_CHECKS = 0;');

    // Clear existing data from the equipment table for a clean seed
    // Use truncate for efficiency if possible
    DB::table('equipment')->truncate();
    \Log::info('Truncated equipment table.');

    // Use the EquipmentFactory to create a variety of fake equipment records.
    // The factory contains the logic for generating realistic and unique data,
    // including merging fields from the old 'assets' table, audit columns, and soft deletes.

    // Create a base set of default equipment (availability_status = 'available')
    Equipment::factory()
      ->count(30) // Create 30 available equipment records
      ->create();
    \Log::info('Created 30 default (available) equipment records.');

    // Create some equipment that is currently on loan
    Equipment::factory()
      ->count(10) // Create 10 on-loan equipment records
      ->onLoan() // Use the 'onLoan' state
      ->create();
    \Log::info('Created 10 on-loan equipment records.');

    // Create some equipment that is damaged or under maintenance
    Equipment::factory()
      ->count(5) // Create 5 records
      ->damaged() // Use the 'damaged' state (sets condition_status and availability_status)
      ->create();
    \Log::info('Created 5 damaged equipment records.');

    Equipment::factory()
      ->count(3) // Create 3 records
      ->status('under_maintenance') // Use the generic status state
      ->create();
    \Log::info('Created 3 under maintenance equipment records.');


    // Create some equipment that has been disposed of (soft deleted)
    Equipment::factory()
      ->count(2) // Create 2 deleted records
      ->deleted() // Use the 'deleted' state
      ->create();
    \Log::info('Created 2 deleted equipment records.');


    // Total records created: 30 + 10 + 5 + 3 + 2 = 50 (matching your original count)
    \Log::info('Equipment seeding complete.'); // Log end


    // Re-enable foreign key checks after all operations in this seeder are done
    DB::statement('SET FOREIGN_KEY_CHECKS = 1;');
    // Also re-enable unique checks if you disabled them globally
    // DB::statement('SET UNIQUE_CHECKS = 1;');
  }
}
