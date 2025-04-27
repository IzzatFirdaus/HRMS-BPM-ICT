<?php

namespace Database\Seeders;

use App\Models\Leave; // Import the Leave model
// We will use the DB facade instead of the User model directly for the early query
// REMOVE or comment out the User model import: use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents; // Optional trait
use Illuminate\Support\Facades\DB; // Import the DB facade
use Illuminate\Support\Facades\Log; // Import Log for logging


class LeavesSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    \Log::info('Seeding leaves...'); // Log start

    // Optional: Delete existing records before seeding to avoid duplicates
    // Ensure foreign key checks are off if using truncate and other tables reference leaves.
    // DB::table('leaves')->truncate();
    // \Log::info('Truncated leaves table.');
    // Or using Eloquent carefully if no FK issues: Leave::truncate();

    // Get the ID of the first user (often the admin) for audit columns.
    // Use DB facade to bypass Eloquent model and SoftDeletes trait for this query
    // as it runs immediately after migrations, where Eloquent might not yet
    // fully recognize the deleted_at column despite it existing.
    // This relies on AdminUserSeeder having run successfully before this.
    // The users table and its deleted_at column should exist by this point.
    $adminUserForAudit = DB::table('users')->first(); // UPDATED: Use DB::table to get the first user record
    $auditUserId = $adminUserForAudit?->id ?? null; // Use null if no user exists yet

    // Define standard leave types and their attributes
    $leaveTypes = [
      [
        'name' => 'Annual Leave',
        'is_instantly' => false, // Typically needs approval first
        'is_accumulative' => true, // Carried over year to year
        'discount_rate' => 100, // 100% paid leave
        'days_limit' => 25, // Example annual limit in days
        'minutes_limit' => 0, // Not limited by minutes
        'notes' => 'Standard annual leave entitlement.',
      ],
      [
        'name' => 'Sick Leave',
        'is_instantly' => true, // Can be taken instantly with medical cert
        'is_accumulative' => false, // Typically resets yearly
        'discount_rate' => 100, // 100% paid leave
        'days_limit' => 14, // Example annual limit in days
        'minutes_limit' => 0, // Not limited by minutes
        'notes' => 'Requires a valid medical certificate.',
      ],
      [
        'name' => 'Unpaid Leave',
        'is_instantly' => false,
        'is_accumulative' => false,
        'discount_rate' => 0, // 0% paid leave
        'days_limit' => 365, // Example max duration
        'minutes_limit' => 0,
        'notes' => 'Leave without pay.',
      ],
      [
        'name' => 'Emergency Leave',
        'is_instantly' => true,
        'is_accumulative' => false,
        'discount_rate' => 100,
        'days_limit' => 3, // Example limit
        'minutes_limit' => 0,
        'notes' => 'For unforeseen emergencies.',
      ],
      // Add more leave types as needed
    ];

    foreach ($leaveTypes as $leaveData) {
      // Create the leave type if it doesn't already exist based on name
      // firstOrCreate will use the Leave model, which does not have SoftDeletes.
      // It will correctly use the auditUserId fetched via DB::table.
      Leave::firstOrCreate(
        ['name' => $leaveData['name']], // Find by name
        array_merge(
          $leaveData, // Add other attributes
          [
            // Assign audit columns
            // CreatedUpdatedDeletedBy trait is assumed to handle setting these
            // automatically based on Auth::id() in controllers/requests.
            // For seeders, manually setting is safer here using the fetched admin user ID.
            'created_by' => $auditUserId, // Set audit columns
            'updated_by' => $auditUserId,
            // deleted_by and deleted_at are null by default in the database schema
          ]
        )
      );
      // Optional: Log each leave type created/found
      // \Log::info("Leave type ensured: {$leaveData['name']}");
    }

    \Log::info('Leaves seeding complete.'); // Log end

    // Optional: Create additional random leave types using the factory if you have one
    // If you created a LeavesFactory, ensure it populates audit columns correctly.
    // \App\Models\Leave::factory()->count(5)->create(); // Create 5 more random leaves
  }
}
