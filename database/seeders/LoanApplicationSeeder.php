<?php

namespace Database\Seeders;

use App\Models\LoanApplication; // Import the LoanApplication model
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents; // Optional trait

class LoanApplicationSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    // Optional: Delete existing records before seeding to avoid duplicates
    // LoanApplication::truncate(); // Use with caution, especially with foreign keys

    // Create a mix of loan applications in different statuses using the factory and its states

    // Create some applications in the default 'draft' status
    LoanApplication::factory()
      ->count(15) // Create 15 draft applications
      ->create();
    // Note: The LoanApplicationFactory's afterCreating callback will automatically
    // create related LoanApplicationItem records for each application.

    // Create some applications that have been certified by the applicant
    LoanApplication::factory()
      ->count(10) // Create 10 certified applications
      ->certified() // Use the certified state
      ->create();

    // Create some applications pending support review
    LoanApplication::factory()
      ->count(8) // Create 8 pending support applications
      ->pendingSupport() // Use the pendingSupport state
      ->create();

    // Create some applications that have been approved (e.g., by support/responsible officer)
    LoanApplication::factory()
      ->count(12) // Create 12 approved applications
      ->approved() // Use the approved state
      ->create();

    // Create some rejected applications
    LoanApplication::factory()
      ->count(5) // Create 5 rejected applications
      ->rejected() // Use the rejected state
      ->create();

    // Create some cancelled applications
    LoanApplication::factory()
      ->count(3) // Create 3 cancelled applications
      ->cancelled() // Use the cancelled state
      ->create();


    // Applications with status 'issued' or 'returned' typically require
    // corresponding records in the 'loan_transactions' table.
    // It's generally better to create LoanTransactions and let them
    // implicitly set the LoanApplication status, or use a dedicated seeder
    // that creates both the application and the transaction together.
    // We won't create 'issued'/'returned' states here directly via factory.

    // Create some deleted applications
    LoanApplication::factory()
      ->count(2) // Create 2 deleted applications
      ->deleted() // Use the deleted state
      ->create();


    // Example: Create a specific loan application for a known user
    // $user = \App\Models\User::where('email', 'another.user@example.com')->first(); // Find the user
    // if ($user) {
    //     LoanApplication::factory()
    //         ->for($user) // Link to the specific user as the applicant
    //         ->certified() // Mark as certified
    //         ->create([
    //             'purpose' => 'Borrow laptop for presentation',
    //             'loan_start_date' => '2025-06-01',
    //             'loan_end_date' => '2025-06-03',
    //              // Add other specific attributes
    //              // Audit columns are handled by the factory
    //         ]);
    // }
  }
}
