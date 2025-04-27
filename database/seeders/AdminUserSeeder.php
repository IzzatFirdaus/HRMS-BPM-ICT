<?php

namespace Database\Seeders;

// Removed App\Models\User import for strict DB facade usage in this specific seeder
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB; // Import the DB facade
use Illuminate\Support\Carbon; // Import Carbon for timestamps

class AdminUserSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    // Use DB facade for robustness during early seeding before full schema is ready.
    // This bypasses Eloquent models and their traits (like SoftDeletes).

    // Check if admin user already exists using DB facade
    // Using email as the primary check, assuming uniqueness
    // This query will NOT include SoftDeletes scope as we are using DB::table
    $adminExists = DB::table('users')
      ->where('email', 'admin@demo.com')
      ->first();

    if (!$adminExists) {
      // Create the default admin user using DB facade insert
      // ONLY include columns that exist in the initial create_users_table migration (2013_01_01 or 2014_10_12 basic)
      // DO NOT include MOTAC-specific fields (full_name, employee_id, nric, is_admin, etc.)
      // or deleted_at here, as those columns might not exist yet.
      DB::table('users')->insert([
        'name' => 'Admin', // Or a placeholder username
        'email' => 'admin@demo.com',
        'email_verified_at' => Carbon::now(), // Use Carbon::now() for timestamps
        'password' => Hash::make('password'), // Set a default password
        'remember_token' => null, // Default remember token to null
        'created_at' => Carbon::now(), // Manually set timestamps
        'updated_at' => Carbon::now(),
      ]);

      // Log the creation for verification
      \Log::info('Initial admin user created via DB::table insert.');
    } else {
      // Log that the admin user already exists
      \Log::info('Admin user already exists.');
    }

    // Subsequent seeders or logic (running AFTER add_motac_columns_to_users_table migration)
    // can safely use the User model to update this admin user with MOTAC fields
    // and create other users.
    // Example: In DatabaseSeeder after calling add_motac_columns_to_users_table migration:
    // User::where('email', 'admin@demo.com')->update([
    //     'full_name' => 'Administrator',
    //     'is_admin' => true,
    //     'status' => 'active',
    //     // ... other MOTAC fields ...
    //     // Audit columns should be handled by the CreatedUpdatedDeletedBy trait when using User::update
    // ]);
  }
}
