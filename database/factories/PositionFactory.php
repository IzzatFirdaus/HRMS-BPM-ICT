<?php

namespace Database\Factories;

use App\Models\Position; // Use the correct model name
use App\Models\Grade; // Import Grade model if linking
use App\Models\User; // Import User model for audit columns
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

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
    return [
      'name' => $this->faker->unique()->jobTitle(), // Use faker for job titles
      'vacancies_count' => $this->faker->numberBetween(0, 10),
      'description' => $this->faker->sentence(),
      // Link to a random existing grade, or null
      'grade_id' => Grade::inRandomOrder()->first()?->id,

      // Populate audit columns with a random user ID if users exist
      'created_by' => User::inRandomOrder()->first()?->id,
      'updated_by' => User::inRandomOrder()->first()?->id,
      // deleted_by is typically null initially
    ];
  }

  /**
   * Indicate that the model is deleted.
   *
   * @return static
   */
  public function deleted()
  {
    return $this->state(fn(array $attributes) => [
      'deleted_at' => now(),
      'deleted_by' => User::inRandomOrder()->first()?->id, // Assign a deleter if needed
    ]);
  }
}
