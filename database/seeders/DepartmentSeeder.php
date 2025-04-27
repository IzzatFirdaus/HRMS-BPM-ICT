<?php

namespace Database\Seeders;

use App\Models\Department; // Import the Department model
// REMOVE or comment out the User model import if you want to strictly use DB facade for User queries here
// use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents; // Optional trait
use Illuminate\Support\Facades\DB; // Import the DB facade
use Illuminate\Support\Facades\Log; // Import Log for logging


class DepartmentSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    \Log::info('Seeding departments...'); // Log start

    // Optional: Delete existing records before seeding if you want to start fresh every time
    // Ensure foreign key checks are off if using truncate and other tables reference departments.
    // DB::table('departments')->truncate();
    // \Log::info('Truncated departments table.');

    // Get the ID of the first user (often the admin) for audit columns.
    // Use DB facade to bypass Eloquent model and SoftDeletes trait for this query
    // as it runs immediately after migrations, where Eloquent might not yet
    // fully recognize the deleted_at column despite it existing.
    // This relies on AdminUserSeeder having run successfully before this.
    $adminUserForAudit = DB::table('users')->first(); // UPDATED: Use DB::table to get the first user record
    $auditUserId = $adminUserForAudit?->id ?? null; // Use null if no user exists yet

    // --- Seed Specific Departments using firstOrCreate (Recommended for standard lookups) ---
    $specificDepartments = [
      [
        'name' => 'Jabatan Teknologi Maklumat (ICT)', // Example: Information Technology Department
        'description' => 'Responsible for managing IT infrastructure and systems.',
      ],
      [
        'name' => 'Jabatan Sumber Manusia (HR)', // Example: Human Resources Department
        'description' => 'Handles employee relations, benefits, and recruitment.',
      ],
      [
        'name' => 'Jabatan Kewangan', // Example: Finance Department
        'description' => 'Manages budgets, payroll, and financial reporting.',
      ],
      [
        'name' => 'Bahagian Khidmat Pengurusan (BPM)', // Example: Management Services Division
        'description' => 'Provides administrative and support services.',
      ],
      // Add more specific departments as needed based on your organization's structure
    ];

    foreach ($specificDepartments as $departmentData) {
      // Use firstOrCreate to prevent duplicates if the seeder is run multiple times
      // firstOrCreate will use the Department model, which has audit columns and timestamps.
      // This should be fine as the Department model does not have SoftDeletes.
      Department::firstOrCreate(
        ['name' => $departmentData['name']], // Find department by name
        array_merge(
          $departmentData, // Add other department attributes
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
    \Log::info("Ensured specific departments exist.");


    // --- Optional: Seed Additional Random Departments using a Factory ---
    // Best practice is to create a DepartmentFactory if you need many random departments.
    // If you create a DepartmentFactory, ensure it populates 'name', 'description',
    // and audit columns correctly.

    // Example if you had a DepartmentFactory:
    // \App\Models\Department::factory()
    //     ->count(5) // Create 5 more random departments
    //     ->create(); // Create the records
    // \Log::info("Ensured random departments exist (if enabled).");

    \Log::info('Department seeding complete.'); // Log end

  }
}
