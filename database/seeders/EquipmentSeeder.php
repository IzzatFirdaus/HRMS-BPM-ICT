<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Equipment; // Import the Equipment model (already present)
// use Database\Factories\EquipmentFactory; // No need to import factory explicitly if in default location

class EquipmentSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    // Clear existing data from the equipment table for a clean seed
    DB::table('equipment')->truncate();

    // Use the EquipmentFactory to create a larger number of fake equipment assets.
    // The factory contains the logic for generating realistic and unique data.
    // You can adjust the count() method to specify how many fake records you want.
    Equipment::factory()
      ->count(50) // FIX: Create 50 fake equipment records (adjust the number as needed)
      // You can use states here if you want some equipment to be in specific statuses, e.g.:
      // ->state([
      //     'status' => 'available', // Ensure all are available
      //     'current_location' => 'Stor BPM',
      // ])
      // Or mix states:
      // ->sequence(
      //     ['status' => 'available'],
      //     ['status' => 'on_loan'],
      //     ['status' => 'under_maintenance'],
      // )
      ->create(); // Create the records in the database

    // Removed the hardcoded sample equipment array and loop, as the factory handles data generation.
  }
}
