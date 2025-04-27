<?php

namespace Database\Seeders;

use App\Models\Transition; // Import the Transition model
use App\Models\Equipment;  // Import Equipment model for linking (Standardized name)
use App\Models\Employee;   // Import Employee model for linking
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents; // Optional trait
use Faker\Factory as FakerFactory; // Import Faker Factory

class TransitionSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    // Optional: Delete existing records before seeding to avoid duplicates
    // Transition::truncate(); // Use with caution, especially with foreign keys

    // Instantiate Faker manually if needed for logic within the seeder
    // $faker = FakerFactory::create();

    // Get some existing employees and equipment to link transitions to
    $employees = Employee::inRandomOrder()->limit(30)->get(); // Get some random employees
    $equipment = Equipment::inRandomOrder()->limit(40)->get(); // Get some random equipment

    // Ensure we have enough employees and equipment to create transitions
    if ($employees->isEmpty() || $equipment->isEmpty()) {
      \Log::warning('Not enough Employees or Equipment seeded to create Transitions. Skipping.');
      return; // Exit if dependencies are not met
    }

    // --- Create Issued/Outstanding Transitions ---
    // Create transitions where equipment is currently assigned to an employee
    $outstandingCount = 0;
    $numOutstanding = min($employees->count(), $equipment->count(), 25); // Create up to 25 outstanding transitions
    $assignedEquipment = collect(); // Keep track of equipment assigned in this loop

    for ($i = 0; $i < $numOutstanding; $i++) {
      $employee = $employees->pop(); // Get and remove employee from list
      $item = $equipment->pop(); // Get and remove equipment from list

      if ($employee && $item) {
        Transition::factory()->create([
          'employee_id' => $employee->id,
          'equipment_id' => $item->id,
          // handed_date is default in factory, return_date is null by default
        ]);
        $outstandingCount++;
        $assignedEquipment->push($item); // Add to assigned list

        // Optionally update equipment status to 'on_loan' if needed
        $item->update(['availability_status' => 'on_loan']);
      }
    }
    \Log::info("Created {$outstandingCount} outstanding transitions.");

    // --- Create Returned Transitions (from some of the assigned equipment) ---
    // Select some of the equipment that was just assigned
    $numReturned = min($assignedEquipment->count(), 15); // Return up to 15 of the assigned items
    $equipmentToReturn = $assignedEquipment->random($numReturned); // Get random items to return

    $returnedCount = 0;
    foreach ($equipmentToReturn as $item) {
      // Find the most recent outstanding transition for this equipment
      $latestTransition = Transition::where('equipment_id', $item->id)
        ->whereNull('return_date') // Look for outstanding ones
        ->latest('handed_date')
        ->first();

      if ($latestTransition) {
        // Use the 'returned' state to update the existing transition
        $latestTransition->factory()->returned()->create();
        $returnedCount++;

        // Optionally update equipment status back to 'available'
        $item->update(['availability_status' => 'available']);
      }
    }
    \Log::info("Marked {$returnedCount} transitions as returned.");


    // --- Create some Deleted Transitions ---
    Transition::factory()
      ->count(3) // Create 3 deleted transitions
      ->deleted() // Use the deleted state
      ->create();
    \Log::info("Created 3 deleted transitions.");
  }
}
