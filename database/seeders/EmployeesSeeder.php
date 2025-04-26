<?php

namespace Database\Seeders;

use App\Models\Employee; // Import the Employee model
use App\Models\Contract; // Import the Contract model for linking
use App\Models\User;     // Import the User model for audit columns
use Faker\Factory;        // Import Faker Factory
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log; // Import Log facade

class EmployeesSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    $faker = Factory::create();

    // Ensure Contracts and Users exist before trying to link employees
    // You should ensure ContractsSeeder and UserSeeder run BEFORE this seeder
    $contractIds = Contract::pluck('id')->toArray();
    $userIds = User::pluck('id')->toArray();

    // Check if there are any contracts and users to link to
    if (empty($contractIds)) {
      Log::warning('No contracts found. Skipping Employee seeding as contract_id is required.');
      // Optionally call the ContractsSeeder here if it's safe to do so
      // $this->call(ContractsSeeder::class);
      // $contractIds = Contract::pluck('id')->toArray(); // Re-fetch IDs
      // if (empty($contractIds)) {
      //     return; // Exit if still no contracts after seeding attempt
      // }
      return; // Exit if no contracts
    }

    if (empty($userIds)) {
      Log::warning('No users found. Skipping Employee seeding as created_by/updated_by require user IDs.');
      // Optionally call the UserSeeder here if it's safe to do so
      // $this->call(UserSeeder::class);
      // $userIds = User::pluck('id')->toArray(); // Re-fetch IDs
      // if (empty($userIds)) {
      //     return; // Exit if still no users after seeding attempt
      // }
      return; // Exit if no users
    }


    // Create 10 employee records (increased count for more data)
    for ($i = 0; $i < 10; $i++) {
      // Get a random Contract ID
      $randomContractId = $faker->randomElement($contractIds);

      // Get random User IDs for audit columns
      $randomCreatedBy = $faker->randomElement($userIds);
      $randomUpdatedBy = $faker->randomElement($userIds);
      // deleted_by can be null initially

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
        'gender' => $faker->randomElement(['Lelaki', 'Perempuan']), // Use string values for gender if applicable, or 0/1 if integer
        'address' => $faker->address(),
        'notes' => $faker->optional(0.5)->text(100), // Optional notes (50% chance)
        'max_leave_allowed' => $faker->numberBetween(15, 30), // More realistic leave days
        'delay_counter' => '00:00:00', // Keep as static string if intended
        'hourly_counter' => '00:00:00', // Keep as static string if intended
        'is_active' => $faker->boolean(90), // 90% chance of being active
        'profile_photo_path' => $faker->optional(0.3)->imageUrl(640, 480, 'people'), // Optional profile photo URL
        // Assign existing user IDs to audit columns
        'created_by' => $randomCreatedBy,
        'updated_by' => $randomUpdatedBy,
        'deleted_by' => null, // Default to null

        // Timestamps are handled automatically by Eloquent and traits
        // 'created_at' => now(),
        // 'updated_at' => now(),
        // 'deleted_at' => null,
      ]);
    }

    Log::info('Employees seeded successfully (' . Employee::count() . ' records).'); // Log count
  }
}
