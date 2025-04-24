<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Equipment; // Assuming you have an Equipment model
use Illuminate\Support\Str;

class EquipmentSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    // Clear existing data
    // DB::table('equipment')->delete(); // Use carefully, especially in production

    // Define sample equipment
    $equipment = [
      [
        'asset_type' => 'laptop',
        'brand' => 'Dell',
        'model' => 'Latitude 7400',
        'serial_number' => 'SN' . Str::random(8),
        'tag_id' => 'MOTAC-LT-' . Str::random(4),
        'purchase_date' => '2022-01-15',
        'warranty_expiry_date' => '2025-01-14',
        'status' => 'available',
        'current_location' => 'Stor BPM',
        'notes' => 'Standard issue laptop',
      ],
      [
        'asset_type' => 'projector',
        'brand' => 'Epson',
        'model' => 'EB-W51',
        'serial_number' => 'SN' . Str::random(8),
        'tag_id' => 'MOTAC-PJ-' . Str::random(4),
        'purchase_date' => '2023-03-10',
        'warranty_expiry_date' => '2026-03-09',
        'status' => 'available',
        'current_location' => 'Stor BPM',
        'notes' => 'Meeting room projector',
      ],
      // Add more sample equipment
    ];

    // Insert data
    foreach ($equipment as $itemData) {
      Equipment::create($itemData);
    }

    // Or use the factory for more flexibility (if you created the factory in Step 4)
    // \App\Models\Equipment::factory()->count(20)->create();
  }
}
