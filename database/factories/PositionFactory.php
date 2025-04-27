<?php

namespace Database\Factories;

use App\Models\Position; // Use the correct model name
use App\Models\Grade; // Import Grade model if linking
use App\Models\User; // Import User model for audit columns
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model; // 👇 ADDED: Import the base Model class
use Illuminate\Support\Str;
use Illuminate\Support\Carbon; // Import Carbon for date/time manipulation

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Position> // Update docblock
 */
class PositionFactory extends Factory // Update class name if it was DesignationFactory
{
  /**
   * The name of the factory's corresponding model.
   *
   * @var string
   */
  protected $model = Position::class; // Reference the correct model

  /**
   * Define the model's default state.
   *
   * @return array<string, mixed>
   */
  public function definition(): array
  {
    // Fetch a random existing Grade ID.
    // This relies on your seeders for Grades running *before*
    // the Position seeder that uses this factory.
    $gradeId = Grade::inRandomOrder()->first()?->id;

    // Fetch the ID of the first user (often the admin) for audit columns.
    // This relies on at least one user being seeded *before* seeders using this factory.
    $adminUserForAudit = User::first();
    $auditUserId = $adminUserForAudit?->id ?? null; // Use null if no user exists yet

    return [
      'name' => $this->faker->unique()->jobTitle(), // Use faker for unique job titles
      'vacancies_count' => $this->faker->numberBetween(0, 10), // Number of vacancies
      'description' => $this->faker->optional()->sentence(), // Optional description

      // Link to a random existing grade, or null
      'grade_id' => $gradeId,

      // Audit fields for CreatedUpdatedDeletedBy trait
      // Assign the ID of the first user (Admin) or null if none exists
      'created_by' => $auditUserId,
      'updated_by' => $auditUserId,
      // deleted_by is typically null initially
      'deleted_by' => null,

      // Soft delete timestamp, null by default
      'deleted_at' => null,
    ];
  }

  /**
   * Indicate that the model is deleted (soft deleted).
   * Requires SoftDeletes trait on the Position model.
   *
   * @return static
   */
  public function deleted(): static
  {
    return $this->state(fn(array $attributes) => [
      'deleted_at' => now(),
      // Assign the ID of the first user (Admin) or null if none exists as the deleter
      'deleted_by' => User::first()?->id ?? null,
    ]);
  }

  // Add other states or methods as needed (e.g., specific name/level combinations)

  /**
   * Indicate a specific name and optional grade for the position.
   *
   * @param string $name The position name.
   * @param Grade|Factory|int|null $grade The grade instance, factory, ID, or null.
   * @return static
   */
  public function withDetails(string $name, Grade|Factory|int|null $grade = null): static
  {
    // Resolve the grade ID if an object or factory is provided
    $gradeId = $grade;
    if ($grade instanceof Model) { // Check if it's a Model instance
      $gradeId = $grade->id;
    } elseif ($grade instanceof Factory) { // Check if it's a Factory instance
      $gradeId = $grade->create()->id; // Create the model from the factory and get its ID
    }
    // If $grade was null or an int, $gradeId remains as is.
    // If $grade was a Model that wasn't found or a Factory that failed, $gradeId might be null.

    return $this->state(fn(array $attributes) => [
      'name' => $name, // Set the specific name
      'grade_id' => $gradeId, // Set the specific grade ID
    ]);
  }
}
