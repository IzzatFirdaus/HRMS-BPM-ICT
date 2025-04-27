<?php

namespace Database\Factories;

use App\Models\Transition; // Import the Transition model
use App\Models\Equipment; // Import Equipment model for linking (Standardized name)
use App\Models\Employee; // Import Employee model for linking
use App\Models\User;     // Import User model for audit columns
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon; // Import Carbon for date manipulation

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Transition>
 */
class TransitionFactory extends Factory
{
  /**
   * The name of the factory's corresponding model.
   *
   * @var string
   */
  protected $model = Transition::class;

  /**
   * Define the model's default state.
   *
   * @return array<string, mixed>
   */
  public function definition(): array
  {
    // Fetch random existing IDs for foreign keys.
    // This relies on seeders for Equipment, Employees, and Users
    // running *before* the Transition seeder.
    $equipmentId = Equipment::inRandomOrder()->first()?->id; // Standardized name
    $employeeId = Employee::inRandomOrder()->first()?->id;

    // Fetch the ID of the first user (often the admin) for audit columns.
    $adminUserForAudit = User::first();
    $auditUserId = $adminUserForAudit?->id ?? null; // Use null if no user exists yet

    // Generate handed date
    $handedDate = $this->faker->dateTimeBetween('-1 year', 'now');

    // Generate return date (optional, must be after handed date)
    $returnDate = $this->faker->optional(0.7)->dateTimeBetween($handedDate, 'now'); // 70% chance of having a return date

    return [
      'equipment_id' => $equipmentId, // Assign a random Equipment ID (or null if nullable FK)
      'employee_id' => $employeeId, // Assign a random Employee ID (or null if nullable FK)

      'handed_date' => $handedDate->format('Y-m-d'), // Formatted handed date
      'return_date' => $returnDate ? $returnDate->format('Y-m-d') : null, // Formatted return date (if exists)

      'center_document_number' => $this->faker->unique()->bothify('CD#####-####'), // Unique document number example
      'reason' => $this->faker->optional()->sentence(), // Optional reason
      'note' => $this->faker->optional()->text(200), // Optional note

      // Audit fields for CreatedUpdatedDeletedBy trait
      'created_by' => $auditUserId,
      'updated_by' => $auditUserId,
      'deleted_by' => null, // Default to null for soft deletes
    ];
  }

  /**
   * Indicate that the transition represents a returned item.
   */
  public function returned(): static
  {
    return $this->state(fn(array $attributes) => [
      'return_date' => $attributes['handed_date'] ? $this->faker->dateTimeBetween($attributes['handed_date'], 'now')->format('Y-m-d') : now()->format('Y-m-d'),
      'note' => $this->faker->text(200), // Add notes when returned
    ]);
  }


  /**
   * Indicate that the transition is deleted (soft deleted).
   * Requires SoftDeletes trait on the Transition model.
   */
  public function deleted(): static
  {
    return $this->state(fn(array $attributes) => [
      'deleted_at' => now(),
      'deleted_by' => User::first()?->id ?? null, // Assign a deleter if possible
    ]);
  }
}
