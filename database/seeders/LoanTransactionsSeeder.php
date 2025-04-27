<?php

namespace Database\Seeders;

use App\Models\LoanTransaction; // Import the LoanTransaction model
use App\Models\LoanApplication; // Import LoanApplication model for linking
use App\Models\Equipment;       // Import Equipment model for linking
use App\Models\User;           // Import User model for linking officers and audit columns
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents; // Optional trait
use Faker\Factory as FakerFactory; // Import Faker Factory

class LoanTransactionsSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    // Optional: Delete existing records before seeding to avoid duplicates
    // LoanTransaction::truncate(); // Use with caution, especially with foreign keys

    // Instantiate Faker manually for use in the seeder
    $faker = FakerFactory::create();

    // Get some existing applications and equipment to link transactions to
    // You might want to get applications with status 'approved' or similar
    $approvedApplications = LoanApplication::where('status', 'approved')->inRandomOrder()->limit(20)->get();
    $availableEquipment = Equipment::where('availability_status', 'available')->inRandomOrder()->limit(30)->get(); // Get some available equipment

    // Get some potential issuing officers (e.g., BPM staff) and other users
    $issuingOfficers = User::where('is_bpm_staff', true)->inRandomOrder()->limit(10)->get();
    $allUsers = User::inRandomOrder()->limit(20)->get(); // Other users for receiving/returning officers

    // Get the ID of the first user (admin) for audit columns during updates
    $adminUserForAudit = User::first();
    $auditUserId = $adminUserForAudit?->id ?? null; // Use null if no user exists yet


    // --- Create Issued Transactions (requires linking application and equipment) ---
    $issuedCount = 0;
    foreach ($approvedApplications as $application) {
      // Skip if no available equipment left to link
      if ($availableEquipment->isEmpty()) {
        break;
      }

      // Get a random available equipment from the list
      $equipment = $availableEquipment->pop(); // Use pop to get and remove from the list

      // Get a random issuing officer
      $issuingOfficer = $issuingOfficers->isEmpty() ? null : $issuingOfficers->random();
      $receivingOfficer = $allUsers->isEmpty() ? null : $allUsers->random();

      // Create an 'issued' transaction linked to the application and equipment
      LoanTransaction::factory()
        ->for($application) // Link to the specific application
        ->forEquipment($equipment) // Link to the specific equipment using the factory state
        ->create([
          'issuing_officer_id' => $issuingOfficer?->id,
          'receiving_officer_id' => $receivingOfficer?->id,
          // issue_timestamp and status('issued') are default or handled by factory state
        ]);
      $issuedCount++;

      // Optionally update the equipment status to 'on_loan'
      if ($equipment) { // Check if equipment is not null
        // 👇 CORRECTED: Explicitly set updated_by to the audit user ID during update
        $equipment->update([
          'availability_status' => 'on_loan',
          'updated_by' => $auditUserId
        ]);
      }

      // Optionally update the application status to 'issued'
      if ($application) { // Check if application is not null
        // 👇 CORRECTED: Explicitly set updated_by to the audit user ID during update
        $application->update([
          'status' => 'issued',
          'updated_by' => $auditUserId
        ]);
      }
    }
    \Log::info("Created {$issuedCount} issued loan transactions.");


    // --- Create Returned Transactions (from some of the issued ones) ---
    // Get some of the transactions that were just issued
    $issuedTransactions = LoanTransaction::where('status', 'issued')->inRandomOrder()->limit($issuedCount > 15 ? 15 : $issuedCount)->get();

    $returnedCount = 0;
    foreach ($issuedTransactions as $transaction) {
      $returningOfficer = $allUsers->isEmpty() ? null : $allUsers->random();
      $returnAcceptingOfficer = $issuingOfficers->isEmpty() ? null : $issuingOfficers->random();

      // Use the 'returned' state to set return details
      $transaction->factory()->returned()->create([
        'returning_officer_id' => $returningOfficer?->id,
        'return_accepting_officer_id' => $returnAcceptingOfficer?->id,
        // return_timestamp and status('returned') are handled by the state
        'return_notes' => $faker->optional()->sentence(), // Add optional notes
      ]);
      $returnedCount++;

      // Optionally update the equipment status back to 'available'
      if ($transaction->equipment) {
        // 👇 CORRECTED: Explicitly set updated_by to the audit user ID during update
        $transaction->equipment->update([
          'availability_status' => 'available',
          'updated_by' => $auditUserId
        ]);
      }
      // Optionally update the application status to 'returned'
      if ($transaction->loanApplication) {
        // 👇 CORRECTED: Explicitly set updated_by to the audit user ID during update
        $transaction->loanApplication->update([
          'status' => 'returned',
          'updated_by' => $auditUserId
        ]);
      }
    }
    \Log::info("Marked {$returnedCount} loan transactions as returned.");


    // --- Create some Overdue Transactions (from remaining issued ones) ---
    // Get any remaining issued transactions that weren't returned
    $overdueTransactions = LoanTransaction::where('status', 'issued')->inRandomOrder()->limit(5)->get();

    $overdueCount = 0;
    foreach ($overdueTransactions as $transaction) {
      // Use the 'overdue' state
      $transaction->factory()->overdue()->create(); // No update calls on equipment/application in this loop

      $overdueCount++;
      // Optionally update the application status to 'overdue'
      if ($transaction->loanApplication) {
        // 👇 CORRECTED: Explicitly set updated_by to the audit user ID during update
        $transaction->loanApplication->update([
          'status' => 'overdue',
          'updated_by' => $auditUserId
        ]);
      }
    }
    \Log::info("Marked {$overdueCount} loan transactions as overdue.");


    // --- Create some Deleted Transactions ---
    LoanTransaction::factory()
      ->count(3) // Create 3 deleted transactions
      ->deleted() // Use the deleted state
      ->create(); // No update calls here. deleted state sets deleted_by, which should be handled by the trait.
    \Log::info("Created 3 deleted loan transactions.");
  }
}
