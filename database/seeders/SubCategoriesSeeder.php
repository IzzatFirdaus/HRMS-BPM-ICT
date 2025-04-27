<?php

namespace Database\Seeders;

// Assuming a SubCategory model exists and has 'name', 'category_id', audit columns, timestamps, soft deletes fillable/cast
use App\Models\SubCategory; // Import the SubCategory model (Adjust if model name is different)
// Assuming a Category model exists for linking
use App\Models\Category;    // Import the Category model for linking
// We will use the DB facade instead of the User model directly for the early query
// REMOVE or comment out the User model import: use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents; // Optional trait
use Illuminate\Support\Facades\DB; // Import DB facade for user query
use Illuminate\Support\Facades\Log; // Import Log for logging


class SubCategoriesSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    \Log::info('Seeding subcategories...'); // Log start

    // Optional: Delete existing records before seeding to avoid duplicates
    // Ensure foreign key checks are off if using truncate and other tables reference sub_categories.
    // DB::table('sub_categories')->truncate(); // Use with caution if you need a clean slate
    // \Log::info('Truncated sub_categories table.');


    // Get the ID of the first user (often the admin) for audit columns.
    // Use DB facade to bypass Eloquent model and SoftDeletes trait for this query.
    // This runs immediately after migrations, where Eloquent might not yet
    // fully recognize the deleted_at column despite it existing.
    // This relies on AdminUserSeeder having run successfully before this.
    // The users table and its deleted_at column should exist by this point due to migration order.
    $adminUserForAudit = DB::table('users')->first(); // UPDATED: Use DB::table to get the first user record
    $auditUserId = $adminUserForAudit?->id ?? null; // Use null if no user exists yet

    // Define subcategories and link them to categories by name
    // This relies on CategoriesSeeder having run BEFORE this seeder.
    // Ensure 'category_name' matches the 'name' in your CategoriesSeeder.
    $subCategories = [
      ['name' => 'Laptops', 'category_name' => 'Information Technology'],
      ['name' => 'Desktops', 'category_name' => 'Information Technology'],
      ['name' => 'Printers', 'category_name' => 'Information Technology'],
      ['name' => 'Monitors', 'category_name' => 'Information Technology'],
      ['name' => 'Projectors', 'category_name' => 'Office Equipment'],
      ['name' => 'Chairs', 'category_name' => 'Furniture'],
      ['name' => 'Tables', 'category_name' => 'Furniture'],
      // Add more subcategories as needed and map them to category names
    ];

    foreach ($subCategories as $subCategoryData) {
      // Find the corresponding category by name using the Category model
      // Assuming Category model does NOT have SoftDeletes (or it runs later)
      $category = Category::where('name', $subCategoryData['category_name'])->first();

      // Only create the subcategory if the category was found
      if ($category) {
        // Create the subcategory if it doesn't already exist based on name and category_id
        // Use firstOrCreate to prevent duplicates if the seeder is run multiple times
        // firstOrCreate will use the SubCategory model, which has audit columns and timestamps.
        // Assuming SubCategory model does NOT have SoftDeletes.
        SubCategory::firstOrCreate(
          ['name' => $subCategoryData['name'], 'category_id' => $category->id], // Find by name and category_id
          // Using array_merge to add the category_id from the found category and audit columns
          array_merge(
            ['name' => $subCategoryData['name']], // Ensure only name is taken from original data
            [
              'category_id' => $category->id, // Ensure category_id is set using the found category's ID
              // Assuming sub_categories table has audit columns
              'created_by' => $auditUserId, // Set audit columns using the fetched admin user ID
              'updated_by' => $auditUserId,
              // deleted_by and deleted_at are null by default in the database schema
            ]
          )
        );
      } else {
        // Log a warning if a category was not found, indicating a potential issue with seeding order or category name mismatch
        \Log::warning("Category not found for subcategory '{$subCategoryData['name']}': {$subCategoryData['category_name']}. Skipping subcategory creation.");
      }
    }

    \Log::info('Subcategories seeded successfully.'); // Log success

    // Optional: Create additional random subcategories using a factory if you have one
    // If you created a SubCategoryFactory and it can link to random Categories:
    // \App\Models\SubCategory::factory()->count(10)->create(); // Create 10 more random subcategories
  }
}
