<?php

namespace Database\Seeders;

use App\Models\EmailApplication; // Import the EmailApplication model
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents; // Optional trait

class EmailApplicationSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    // Optional: Delete existing records before seeding to avoid duplicates
    // EmailApplication::truncate(); // Use with caution, especially with foreign keys

    // Create a mix of email applications in different statuses using the factory and its states

    // Create some applications in the default 'draft' status
    EmailApplication::factory()
      ->count(10) // Create 10 draft applications
      ->create();

    // Create some applications that have been certified by the applicant
    EmailApplication::factory()
      ->count(15) // Create 15 certified applications
      ->certified() // Use the certified state
      ->create();

    // Create some applications pending support review
    EmailApplication::factory()
      ->count(10) // Create 10 pending support applications
      ->pendingSupport() // Use the pendingSupport state
      ->create();

    // Create some applications pending IT Admin review
    EmailApplication::factory()
      ->count(8) // Create 8 pending admin applications
      ->pendingAdmin() // Use the pendingAdmin state
      ->create();

    // Create some applications that have been approved
    EmailApplication::factory()
      ->count(7) // Create 7 approved applications
      ->approved() // Use the approved state (assuming 'approved' is in your status enum)
      ->create();

    // Create some applications that are being processed
    EmailApplication::factory()
      ->count(5) // Create 5 processing applications
      ->processing() // Use the processing state (assuming 'processing' is in your status enum)
      ->create();


    // Create some completed applications
    EmailApplication::factory()
      ->count(20) // Create 20 completed applications
      ->completed() // Use the completed state
      ->create();

    // Create some rejected applications
    EmailApplication::factory()
      ->count(5) // Create 5 rejected applications
      ->rejected() // Use the rejected state
      ->create();

    // Create some applications where provisioning failed
    EmailApplication::factory()
      ->count(2) // Create 2 failed applications
      ->provisionFailed() // Use the provisionFailed state (assuming 'provision_failed' is in your status enum)
      ->create();

    // Create some deleted applications
    EmailApplication::factory()
      ->count(3) // Create 3 deleted applications
      ->deleted() // Use the deleted state
      ->create();


    // Optional: Create some applications with group email details
    EmailApplication::factory()
      ->count(5) // Create 5 applications with group details
      ->withGroupEmailDetails() // Use the withGroupEmailDetails state
      ->create();


    // Example: Create a specific email application for a known user
    // This block is commented out and does NOT contain the ParseError
    // $user = \App\Models\User::where('email', 'specific.user@example.com')->first(); // Find the user
    // if ($user) {
    //     EmailApplication::factory()
    //         ->for($user) // Link to the specific user using the ->for() method
    //         ->status('completed') // Set a specific status
    //         ->create([
    //             'proposed_email' => 'john.doe.assigned@motac.gov.my',
    //             'final_assigned_email' => 'john.doe.assigned@motac.gov.my',
    //             'final_assigned_user_id' => 'motac_uid_12345',
    //             'provisioned_at' => now(),
    //              // Set audit columns if needed, though factory default should handle it
    //              'created_by' => \App\Models\User::first()?->id,
    //              'updated_by' => \App\Models\User::first()?->id,
    //         ]);
    // }
  }
}
