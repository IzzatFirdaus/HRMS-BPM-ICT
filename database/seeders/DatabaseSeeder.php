<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;


class DatabaseSeeder extends Seeder
{
  /**
   * Seed the application's database.
   */
  public function run(): void
  {
    // Optional: Temporarily disable foreign key checks globally for all seeders.
    // Use this only if individual seeder FK check disabling isn't sufficient
    // or causes issues due to complex factory relationships creating data out of order.
    // DB::statement('SET FOREIGN_KEY_CHECKS = 0;');


    $this->call([
      // 1. Core Lookups & Basic Structure (mostly independent)
      ContractsSeeder::class,
      CenterSeeder::class,
      DepartmentSeeder::class,
      GradesSeeder::class,
      //LeavesSeeder::class,
      //HolidaysSeeder::class,
      SettingsSeeder::class,
      //CategoriesSeeder::class,
      //SubCategoriesSeeder::class,
      EquipmentSeeder::class,

      // 2. Seed Positions (Designations) - Depends on Grades, Department
      PositionSeeder::class,

      // 3. Seed Employees - Depends on Contracts, Department, Position/Designation, Users (for created_by/updated_by, if trait doesn't handle null)
      // IMPORTANT: Employees MUST be seeded BEFORE Users IF Users reference Employees via employee_id FK.
      EmployeesSeeder::class, // <-- Seed Employees FIRST if Users link to them

      // 4. Seed Users - Depends on Employees (if using employee_id FK), Department, Position, Grade
      // IMPORTANT: User seeders MUST run AFTER Employee seeder if employee_id FK is used.
      AdminUserSeeder::class, // <-- Seed specific admin user(s)
      UserSeeder::class,      // <-- Seed bulk regular users (Ensure UserSeeder does NOT truncate users!)

      // 5. Seed Data Dependent on Users, Employees, and Structure
      TimelineSeeder::class,  // Depends on Employees, Centers
      //EmployeeLeaveSeeder::class, // Depends on Employees, Leaves
      //EmailApplicationSeeder::class, // Depends on Users
      //LoanApplicationSeeder::class, // Depends on Users, Equipment?

      // 6. Seed Data Referencing Users (LIKE APPROVALS)
      // APPROVALS MUST BE SEEDED *AFTER* ALL USERS EXIST.
      //ApprovalsSeeder::class, // <-- Add this (if you have it) and ensure it's here
      // Add other seeders that reference users here (e.g., comments, posts, etc.)

      // 7. Permissions & Roles (Depends on Users)
      // PermissionRoleTableSeeder::class, // Uncomment if you have this seeder and it should run here

      // Add any other seeders here based on their dependencies...
      // Make sure seeders like CenterHolidayTableSeeder run after Center and Holiday seeders.
      // Make sure seeders for LoanApplicationItems and LoanTransactions run after LoanApplications.
    ]);

    // Optional: Re-enable foreign key checks (if disabled above)
    // DB::statement('SET FOREIGN_KEY_CHECKS = 1;');


    // --- Role Creation and Assignment ---
    // This should typically happen after all users are seeded.
    // Ensure AdminUserSeeder creates a user with email 'admin@demo.com'.

    // Find or create the 'Admin' role
    $adminRole = Role::firstOrCreate(['name' => 'Admin']);

    // Find the admin user by email or ID
    $admin = User::where('email', 'admin@demo.com')->first(); // Find admin by email
    // Or if AdminUserSeeder guarantees ID 1: $admin = User::find(1);

    // Check if the admin user was found before assigning the role
    if ($admin) {
      $admin->assignRole($adminRole);
      \Log::info('Admin role assigned to user ID: ' . $admin->id);
    } else {
      \Log::warning('Admin user (admin@demo.com) not found for role assignment.');
    }

    // Assign roles to other users if needed
    // $bpmUsers = User::where('is_bpm_staff', true)->get();
    // if ($bpmUsers->count() > 0) {
    //     $bpmRole = Role::firstOrCreate(['name' => 'BPM Staff']);
    //     foreach ($bpmUsers as $bpmUser) {
    //          $bpmUser->assignRole($bpmRole);
    //     }
    //     \Log::info('BPM Staff role assigned to ' . $bpmUsers->count() . ' users.');
    // }
  }
}
