<?php

namespace Database\Seeders;

use App\Models\Approval;       // Import the Approval model
use App\Models\User;          // Import User model for officers and audit columns
use App\Models\EmailApplication; // Import EmailApplication as an example approvable model
use App\Models\LoanApplication; // Import LoanApplication as an example approvable model
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents; // Optional trait
use Faker\Factory as FakerFactory; // Import Faker Factory
use Illuminate\Support\Collection; // Import Collection class

class ApprovalsSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    // Optional: Delete existing records before seeding to avoid duplicates
    // Approval::truncate(); // Use with caution, especially with polymorphic relationships

    // Instantiate Faker manually if needed for logic within the seeder
    $faker = FakerFactory::create();

    // Get some potential officers who can approve things (e.g., users with 'is_approver_grade' or specific roles)
    // For simplicity, let's get a few random users
    $officers = User::inRandomOrder()->limit(15)->get();

    // Ensure we have officers to assign approvals to
    if ($officers->isEmpty()) {
      \Log::warning('No Users seeded to act as officers for Approvals. Skipping.');
      return; // Exit if dependencies are not met
    }

    // --- Create Approvals for Existing Email Applications ---
    // Get some existing Email Applications to create approvals for
    // You might select based on status, e.g., 'pending_support', 'pending_admin'
    $emailApplicationsToApprove = EmailApplication::whereIn('status', ['pending_support', 'pending_admin', 'approved', 'completed'])->inRandomOrder()->limit(30)->get();

    $emailAppApprovalCount = 0;
    foreach ($emailApplicationsToApprove as $emailApplication) {
      // Get a random officer for this approval
      $officer = $officers->random();

      // Create an Approval record linked to the Email Application
      // Use the forEmailApplication() state to set approvable_id and approvable_type
      Approval::factory()
        ->forEmailApplication($emailApplication) // Link to this specific Email Application
        ->stage($faker->randomElement(['support_review', 'admin_review'])) // Set a stage
        ->status($faker->randomElement(['pending', 'approved', 'rejected'])) // Set a status
        ->create([
          'officer_id' => $officer->id, // Assign the officer
          // comments and approval_timestamp are handled by status state
        ]);
      $emailAppApprovalCount++;
    }
    \Log::info("Created {$emailAppApprovalCount} approvals for Email Applications.");


    // --- Create Approvals for Existing Loan Applications ---
    // Get some existing Loan Applications to create approvals for
    // You might select based on status, e.g., 'pending_support', 'approved'
    $loanApplicationsToApprove = LoanApplication::whereIn('status', ['pending_support', 'approved', 'issued'])->inRandomOrder()->limit(20)->get();

    $loanAppApprovalCount = 0;
    foreach ($loanApplicationsToApprove as $loanApplication) {
      // Get a random officer for this approval
      $officer = $officers->random();

      // Create an Approval record linked to the Loan Application
      // Use the forLoanApplication() state to set approvable_id and approvable_type
      Approval::factory()
        ->forLoanApplication($loanApplication) // Link to this specific Loan Application
        ->stage($faker->randomElement(['officer_review', 'bpm_review'])) // Set a stage
        ->status($faker->randomElement(['pending', 'approved', 'rejected'])) // Set a status
        ->create([
          'officer_id' => $officer->id, // Assign the officer
          // comments and approval_timestamp are handled by status state
        ]);
      $loanAppApprovalCount++;
    }
    \Log::info("Created {$loanAppApprovalCount} approvals for Loan Applications.");


    // --- Create some Deleted Approvals ---
    $numDeletedApprovals = 3;
    // Get a mix of existing approvable models to link the deleted approvals to
    $approvablesForDeleted = collect()
      ->concat(EmailApplication::inRandomOrder()->limit(ceil($numDeletedApprovals / 2))->get()) // Get some email apps
      ->concat(LoanApplication::inRandomOrder()->limit(floor($numDeletedApprovals / 2))->get()) // Get some loan apps
      ->shuffle() // Mix them up
      ->take($numDeletedApprovals); // Take exactly the number needed

    // Ensure we have enough approvable models to link the deleted approvals
    if ($approvablesForDeleted->count() >= $numDeletedApprovals) {
      Approval::factory()
        ->count($numDeletedApprovals) // Create the specified number of deleted approvals
        ->deleted() // Use the deleted state
        // 👇 ADDED: Link each deleted approval to one of the fetched approvable models using sequence 👇
        ->sequence(fn($sequence) => [
          'approvable_id' => $approvablesForDeleted->get($sequence->index)->id,
          'approvable_type' => get_class($approvablesForDeleted->get($sequence->index)) // Use get_class() to get the model type string
        ])
        // ☝️ END ADDED ☝️
        ->create();
      \Log::info("Created {$numDeletedApprovals} deleted approvals linked to random approvables.");
    } else {
      \Log::warning('Not enough approvable models to link deleted Approvals. Skipping deleted approvals.');
    }


    // --- Create some specific Approved Approvals ---
    // Example: Create an approved approval by a specific officer for a specific application
    // $specificEmailApp = EmailApplication::find(1); // Find a specific application
    // $specificOfficer = User::find(5); // Find a specific officer
    // if ($specificEmailApp && $specificOfficer) {
    //      Approval::factory()
    //          ->forEmailApplication($specificEmailApp) // Link to the specific application
    //          ->stage('final_approval') // Set a specific stage
    //          ->approved() // Use the approved state (sets status, comments, timestamp)
    //          ->create([
    //              'officer_id' => $specificOfficer->id, // Assign the specific officer
    //               // comments and approval_timestamp are handled by the 'approved' state, but can be overridden here
    //              'comments' => 'Approved by Senior Officer.',
    //          ]);
    //      \Log::info("Created specific approved approval.");
    // }
  }
}
