<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Equipment; // Import the Equipment model

class EquipmentSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    // Temporarily disable foreign key checks to allow truncation/deletion
    DB::statement('SET FOREIGN_KEY_CHECKS = 0;');

    // Clear existing data from the equipment table for a clean seed
    // This is the line that was failing
    DB::table('equipment')->truncate();

    // Use the EquipmentFactory to create a larger number of fake equipment assets.
    // The factory contains the logic for generating realistic and unique data.
    // You can adjust the count() method to specify how many fake records you want.
    Equipment::factory()
      ->count(50) // Create 50 fake equipment records (adjust the number as needed)
      // If your factory depends on Categories or SubCategories,
      // ensure those seeders run BEFORE EquipmentSeeder in DatabaseSeeder.php.
      // You might need to pass category_id or sub_category_id explicitly or via states
      // if your factory doesn't handle linking to existing categories automatically.
      ->create(); // Create the records in the database


    // Re-enable foreign key checks after all operations in this seeder are done
    DB::statement('SET FOREIGN_KEY_CHECKS = 1;');
  }
}
