<?php

namespace Database\Seeders;

use App\Models\Employee; // Import the Employee model
use App\Models\Contract; // Import the Contract model for linking
// We will use the DB facade instead of the User model directly for the early query
// REMOVE or comment out the User model import: use App\Models\User;
use Faker\Factory;        // Import Faker Factory
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log; // Import Log facade
use Illuminate\Support\Facades\DB; // Import DB facade for user query and potential truncate


class EmployeesSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    \Log::info('Seeding employees...'); // Log start

    $faker = Factory::create();

    // Optional: Delete existing records before seeding to avoid duplicates
    // Ensure foreign key checks are off if using truncate and other tables reference employees.
    // DB::statement('SET FOREIGN_KEY_CHECKS = 0;'); // Add local FK check disable if needed
    // DB::table('employees')->truncate(); // Use with caution if you need a clean slate
    // \Log::info('Truncated employees table.');
    // DB::statement('SET FOREIGN_KEY_CHECKS = 1;'); // Add local FK check enable if needed

    // Ensure Contracts and Users exist before trying to link employees
    // You should ensure ContractsSeeder and AdminUserSeeder run BEFORE this seeder

    // Get Contract IDs - Assuming Contract model does NOT have SoftDeletes
    $contractIds = Contract::pluck('id')->toArray();

    // 👇 UPDATED: Get user IDs using DB facade to bypass Eloquent SoftDeletes 👇
    // This runs immediately after migrations, where Eloquent might not yet
    // fully recognize the deleted_at column despite it existing.
    // This relies on AdminUserSeeder having run successfully before this.
    // The users table and its deleted_at column should exist by this point due to migration order.
    $userIds = DB::table('users')->pluck('id')->toArray(); // Use DB::table
    // ☝️ END UPDATED ☝️


    // Check if there are any contracts and users to link to
    if (empty($contractIds)) {
      Log::warning('No contracts found. Skipping Employee seeding as contract_id is required.');
      // Optionally call the ContractsSeeder here if it's safe to do so
      // $this->call(ContractsSeeder::class);
      // $contractIds = Contract::pluck('id')->toArray(); // Re-fetch IDs
      // if (empty($contractIds)) {
      //      return; // Exit if still no contracts after seeding attempt
      // }
      return; // Exit if no contracts
    }

    if (empty($userIds)) {
      Log::warning('No users found. Skipping Employee seeding as created_by/updated_by require user IDs.');
      // Optionally call the UserSeeder here if it's safe to do so (though AdminUserSeeder should create at least one user)
      // $this->call(UserSeeder::class); // Assuming UserSeeder creates non-admin users
      // $userIds = User::pluck('id')->toArray(); // Re-fetch IDs
      // if (empty($userIds)) {
      //      return; // Exit if still no users after seeding attempt
      // }
      return; // Exit if no users
    }

    // You might also need to fetch Department, Position, and Grade IDs here
    // if you are linking employees to specific Dept/Pos/Grade during initial seeding.
    // Ensure DepartmentSeeder, PositionSeeder, GradesSeeder run BEFORE this seeder.
    // Assuming these models do NOT have SoftDeletes or run later
    // $departmentIds = \App\Models\Department::pluck('id')->toArray();
    // $positionIds = \App\Models\Position::pluck('id')->toArray();
    // $gradeIds = \App\Models\Grade::pluck('id')->toArray();


    // Create employee records
    // You might want to use a factory for bulk creation instead of a loop with create()
    // Ensure your EmployeeFactory uses DB::table for user/contract/dept/pos/grade lookups if needed.
    // Employee::factory()->count(50)->create(); // Example using factory

    // Or continue with the loop if preferred
    for ($i = 0; $i < 10; $i++) { // Create 10 employee records
      // Get a random Contract ID
      $randomContractId = $faker->randomElement($contractIds);

      // Get random User IDs for audit columns from the fetched array
      $randomCreatedBy = $faker->randomElement($userIds);
      $randomUpdatedBy = $faker->randomElement($userIds);
      // deleted_by can be null initially

      // Get random Dept/Pos/Grade IDs if linking employees to them now from fetched arrays
      // Ensure these lookups also use DB::table if their models have SoftDeletes
      // $randomDepartmentId = $faker->randomElement($departmentIds);
      // $randomPositionId = $faker->randomElement($positionIds);
      // $randomGradeId = $faker->randomElement($gradeIds);


      Employee::create([
        'contract_id' => $randomContractId, // Use a random existing contract ID
        'first_name' => $faker->firstName(),
        'father_name' => $faker->lastName(),
        'last_name' => $faker->lastName(),
        'mother_name' => $faker->lastName(),
        'birth_and_place' => $faker->city() . ', ' . $faker->country(), // More realistic location
        'national_number' => $faker->unique()->numerify('############'), // Adjust format if needed (e.g., 12 digits for NRIC)
        'mobile_number' => $faker->unique()->phoneNumber(), // Use faker's phoneNumber
        'degree' => $faker->randomElement(['Bachelor', 'Master', 'PhD', 'Diploma', 'Certificate']), // More specific degrees
        // Use 'Male'/'Female' strings as per ENUM in migration
        'gender' => $faker->randomElement(['Male', 'Female']),
        'address' => $faker->address(),
        'notes' => $faker->optional(0.5)->text(100), // Optional notes (50% chance)
        'balance_leave_allowed' => $faker->numberBetween(0, 20), // More realistic initial leave balance
        'max_leave_allowed' => $faker->numberBetween(15, 30), // More realistic maximum leave days
        'delay_counter' => '00:00:00', // Keep as static string if intended
        'hourly_counter' => '00:00:00', // Keep as static string if intended
        'is_active' => $faker->boolean(90), // 90% chance of being active
        'profile_photo_path' => $faker->optional(0.3)->imageUrl(640, 480, 'people'), // Optional profile photo URL
        // Assign existing user IDs to audit columns
        'created_by' => $randomCreatedBy,
        'updated_by' => $randomUpdatedBy,
        'deleted_by' => null, // Default to null

        // Assign Department, Position, Grade IDs if linking them now
        // 'department_id' => $randomDepartmentId,
        // 'position_id' => $randomPositionId,
        // 'grade_id' => $randomGradeId,

        // Timestamps are handled automatically by Eloquent and traits
        // 'created_at' => now(),
        // 'updated_at' => now(),
        // 'deleted_at' => null,
      ]);
    }

    Log::info('Employees seeded successfully (' . Employee::count() . ' records).'); // Log count
  }
}
