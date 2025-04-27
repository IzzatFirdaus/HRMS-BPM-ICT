<?php

namespace Database\Factories;

use App\Models\Employee; // Import the Employee model
use App\Models\Contract; // Import the Contract model for linking
use App\Models\User;     // Import the User model for audit columns
use Illuminate\Database\Eloquent\Factories\Factory; // Import the base Factory class
use Illuminate\Support\Str; // Import Str facade if needed
use Illuminate\Support\Carbon; // Import Carbon for date/time manipulation

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Employee>
 */
class EmployeeFactory extends Factory
{
  /**
   * The name of the factory's corresponding model.
   *
   * @var string
   */
  protected $model = Employee::class;

  /**
   * Define the model's default state.
   *
   * @return array<string, mixed>
   */
  public function definition(): array
  {
    // Fetch a random existing Contract ID.
    // This relies on your seeders for Contracts running *before*
    // the Employee seeder that uses this factory.
    $contractId = Contract::inRandomOrder()->first()?->id;

    // Fetch the ID of the first user (often the admin) to use for audit columns.
    // This relies on at least one user being seeded *before* seeders using this factory.
    $adminUserForAudit = User::first();
    $auditUserId = $adminUserForAudit?->id ?? null; // Use null if no user exists yet

    // Generate a birth date sometime in the past (e.g., between 20 and 60 years ago)
    $birthDate = $this->faker->dateTimeBetween('-60 years', '-20 years');
    $birthPlace = $this->faker->city(); // Place of birth

    return [
      'contract_id' => $contractId, // Assign a random Contract ID or null

      'first_name' => $this->faker->firstName(),
      'father_name' => $this->faker->lastName(), // Using lastName for father's name (adjust as needed)
      'last_name' => $this->faker->lastName(),
      'mother_name' => $this->faker->lastName(), // Using lastName for mother's name (adjust as needed)
      'birth_and_place' => $birthDate->format('Y-m-d') . ', ' . $birthPlace, // Combine date and place
      'national_number' => $this->faker->unique()->numerify('############'), // Unique 12-digit number for NRIC
      'mobile_number' => $this->faker->unique()->phoneNumber(), // Unique phone number
      'degree' => $this->faker->randomElement(['SPM', 'Diploma', 'Degree', 'Masters', 'PhD']), // Example degrees
      'gender' => $this->faker->randomElement(['Male', 'Female', 'Other']), // Matches migration enum
      'address' => $this->faker->address(),
      'notes' => $this->faker->optional()->text(200), // Optional notes

      'balance_leave_allowed' => $this->faker->numberBetween(0, 25), // Example leave balance
      'max_leave_allowed' => $this->faker->numberBetween(20, 30), // Example max leave
      // Generate random times for counters (HH:MM:SS)
      'delay_counter' => Carbon::createFromTime($this->faker->numberBetween(0, 5), $this->faker->numberBetween(0, 59), 0)->format('H:i:s'),
      'hourly_counter' => Carbon::createFromTime($this->faker->numberBetween(0, 10), $this->faker->numberBetween(0, 59), 0)->format('H:i:s'),

      'is_active' => $this->faker->boolean(90), // 90% chance of being active
      'profile_photo_path' => null, // Assuming profile photos are handled differently or optional

      // Audit fields for CreatedUpdatedDeletedBy trait
      'created_by' => $auditUserId,
      'updated_by' => $auditUserId,
      'deleted_by' => null, // Default to null for soft deletes
    ];
  }

  /**
   * Indicate that the employee is inactive.
   */
  public function inactive(): static
  {
    return $this->state(fn(array $attributes) => [
      'is_active' => false,
    ]);
  }

  /**
   * Indicate that the employee is deleted (soft deleted).
   * Requires SoftDeletes trait on the Employee model.
   */
  public function deleted(): static
  {
    return $this->state(fn(array $attributes) => [
      'deleted_at' => now(),
      'deleted_by' => User::first()?->id ?? null, // Assign a deleter if possible
      'is_active' => false, // Mark as inactive when deleted
    ]);
  }
}
