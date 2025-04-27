<?php

namespace Database\Seeders;

// Assuming a Category model exists and has 'name', audit columns, timestamps, soft deletes fillable/cast
use App\Models\Category; // Import the Category model (Adjust if your model name is different)
// We will use the DB facade instead of the User model directly for the early query
// REMOVE or comment out the User model import: use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents; // Optional trait
use Illuminate\Support\Facades\DB; // Import DB facade for truncation and user query
use Illuminate\Support\Facades\Log; // Import Log for logging


class CategoriesSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    \Log::info('Seeding categories...'); // Log start

    // 👇 ADDED: Temporarily disable foreign key checks for truncation
    // This is necessary because 'sub_categories' references 'categories'.
    // This provides a local guarantee if global checks in DatabaseSeeder are insufficient.
    DB::statement('SET FOREIGN_KEY_CHECKS = 0;');
    // ☝️ END ADDED

    // Clear existing records before seeding to avoid duplicates.
    // Use truncate for efficiency, requires FK checks to be off.
    // Assuming you are using DB::table for truncation as per the error message.
    DB::table('categories')->truncate(); // Assuming truncation method is DB::table
    \Log::info('Truncated categories table.');

    // 👇 ADDED: Re-enable foreign key checks after truncation
    DB::statement('SET FOREIGN_KEY_CHECKS = 1;');
    // ☝️ END ADDED


    // Get the ID of the first user (often the admin) for audit columns.
    // Use DB facade to bypass Eloquent model and SoftDeletes trait for this query.
    // This runs immediately after migrations, where Eloquent might not yet
    // fully recognize the deleted_at column despite it existing.
    // This relies on AdminUserSeeder having run successfully before this.
    // The users table and its deleted_at column should exist by this point due to migration order.
    $adminUserForAudit = DB::table('users')->first(); // UPDATED: Use DB::table to get the first user record
    $auditUserId = $adminUserForAudit?->id ?? null; // Use null if no user exists yet

    // Define standard categories
    // Ensure these keys match the columns defined in your categories table migration.
    $categories = [
      ['name' => 'Information Technology'],
      ['name' => 'Office Equipment'],
      ['name' => 'Furniture'],
      ['name' => 'Vehicles'],
      ['name' => 'Software Licenses'],
      // Add more categories as needed
    ];

    foreach ($categories as $categoryData) {
      // Create the category if it doesn't already exist based on name
      // Use firstOrCreate to prevent duplicates if the seeder is run multiple times
      // firstOrCreate will use the Category model, which has audit columns and timestamps.
      // This should be fine as the Category model does not have SoftDeletes.
      Category::firstOrCreate(
        ['name' => $categoryData['name']], // Find by name
        array_merge(
          $categoryData, // Add other attributes (just name)
          [
            // Assign audit columns using the fetched admin user ID
            // CreatedUpdatedDeletedBy trait is assumed to handle setting these
            // automatically based on Auth::id() in controllers/requests.
            // For seeders, manually setting is safer here.
            'created_by' => $auditUserId,
            'updated_by' => $auditUserId,
            // deleted_by and deleted_at are null by default in the database schema
          ]
        )
      );
    }

    \Log::info('Categories seeded successfully.'); // Log success


    // Optional: Create additional random categories using a factory if you have one
    // If you created a CategoryFactory, ensure it populates audit columns correctly.
    // \App\Models\Category::factory()->count(5)->create(); // Create 5 more random categories
  }
}
