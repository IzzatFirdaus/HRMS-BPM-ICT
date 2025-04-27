<?php

namespace Database\Seeders;

use App\Models\Contract; // Import the Contract model
// REMOVE or comment out the User model import if you want to strictly use DB facade for User queries here
// use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents; // Optional trait
use Illuminate\Support\Facades\DB; // Import the DB facade


class ContractsSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    \Log::info('Seeding contracts...'); // Log start

    // Optional: Delete existing records before seeding if you want to start fresh every time
    // Ensure foreign key checks are off if using truncate and other tables reference contracts.
    // DB::table('contracts')->truncate();
    // \Log::info('Truncated contracts table.');

    // Get the ID of the first user (often the admin) for audit columns.
    // Use DB facade to bypass Eloquent model and SoftDeletes trait for this query
    // as it runs immediately after migrations, where Eloquent might not yet
    // fully recognize the deleted_at column despite it existing.
    $adminUserForAudit = DB::table('users')->first(); // UPDATED: Use DB::table to get the first user record
    $auditUserId = $adminUserForAudit?->id ?? null; // Use null if no user exists yet

    // Define the standard contract types
    $contractTypes = [
      [
        'name' => 'Full Time (100%)', // Example: Full Time contract
        'work_rate' => 100, // Percentage work rate
        'notes' => 'Standard full-time contract with 100% work rate.', // Added notes based on model fillable
      ],
      [
        'name' => 'Part Time (60%)', // Example: Part Time contract
        'work_rate' => 60, // Percentage work rate
        'notes' => 'Standard part-time contract with 60% work rate.', // Added notes
      ],
      [
        'name' => 'Contract of Service', // Example: Contract for specific duration
        'work_rate' => 100, // Can vary based on terms
        'notes' => 'Contract for a specific service or duration.', // Added notes
      ],
      // Add other contract types as needed based on your system
    ];

    foreach ($contractTypes as $contractData) {
      // Use firstOrCreate to prevent duplicates if the seeder is run multiple times
      // firstOrCreate will use the Contract model, which has audit columns and timestamps.
      // This should be fine as the Contract model does not have SoftDeletes.
      Contract::firstOrCreate(
        ['name' => $contractData['name']], // Find contract by name
        array_merge(
          $contractData, // Add other contract attributes (includes notes)
          [
            // Assign audit columns
            // CreatedUpdatedDeletedBy trait is assumed to handle setting these
            // automatically based on Auth::id() in controllers/requests.
            // For seeders, manually setting is safer here using the fetched admin user ID.
            'created_by' => $auditUserId,
            'updated_by' => $auditUserId,
            // deleted_by and deleted_at are null by default in the database schema
          ]
        )
      );
    }

    \Log::info('Contracts seeding complete.'); // Log end

    // Optional: Create additional random contracts using a factory if you have one
    // If you created a ContractFactory, ensure it populates audit columns correctly.
    // \App\Models\Contract::factory()->count(5)->create(); // Create 5 more random contracts
  }
}
