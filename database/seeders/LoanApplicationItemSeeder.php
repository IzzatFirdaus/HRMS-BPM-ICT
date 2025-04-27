<?php

namespace Database\Seeders;

use App\Models\LoanApplicationItem; // Import the LoanApplicationItem model
use App\Models\LoanApplication;   // Import LoanApplication model for linking
use App\Models\Equipment;         // Import Equipment model for linking
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents; // Optional trait
use Faker\Factory as FakerFactory; // 👇 ADDED: Import Faker Factory

class LoanApplicationItemSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    // Optional: Delete existing records before seeding to avoid duplicates
    // LoanApplicationItem::truncate(); // Use with caution

    // 👇 ADDED: Instantiate Faker manually for use in the seeder
    $faker = FakerFactory::create();
    // ☝️ END ADDED

    // --- Option 1: Simple seeding (if not relying on LoanApplicationFactory callback) ---
    // Create a number of fake item records using the factory, linking to random applications and equipment.
    // This might be redundant if LoanApplicationFactory's afterCreating callback already creates items.
    // LoanApplicationItem::factory()
    //     ->count(50) // Number of records to create
    //     ->create();


    // --- Option 2: Seeding items for EXISTING Loan Applications ---
    // Find some existing loan applications and add more items to them.
    $loanApplications = LoanApplication::inRandomOrder()->limit(10)->get(); // Get 10 random applications

    foreach ($loanApplications as $loanApplication) {
      // Create between 1 and 3 additional items for each selected application
      LoanApplicationItem::factory()
        // 👇 CORRECTED: Use the manually instantiated faker instance
        ->count($faker->numberBetween(1, 3))
        // ☝️ END CORRECTED
        ->for($loanApplication) // Link the item to the current loan application
        ->create();
    }

    // --- Option 3: Seeding specific items for specific applications/equipment ---
    // Example: Add a request for 2 specific laptops to a known application
    // $specificApplication = LoanApplication::find(1); // Find a specific application
    // $specificLaptop = Equipment::where('model', 'Laptop X1 Carbon')->first(); // Find specific equipment
    // if ($specificApplication && $specificLaptop) {
    //     LoanApplicationItem::factory()
    //          ->for($specificApplication) // Link to the specific application
    //          ->forEquipment($specificLaptop) // Link to the specific equipment using the factory state
    //          ->quantity(2) // Set specific quantity
    //          ->create([
    //              'notes' => 'Needed for urgent project.',
    //               // Audit columns handled by factory
    //          ]);
    // }
  }
}
