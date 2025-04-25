<?php

namespace Database\Factories;

use App\Models\Grade; // Import the Grade model
use Illuminate\Database\Eloquent\Factories\Factory; // Import the base Factory class

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Grade>
 */
class GradeFactory extends Factory
{
  /**
   * The name of the factory's corresponding model.
   *
   * @var string
   */
  protected $model = Grade::class;

  /**
   * Define the model's default state.
   * This defines the basic attributes for a default grade.
   *
   * @return array<string, mixed>
   */
  public function definition(): array
  {
    // Generate a unique grade name and level.
    // Assuming grades follow a pattern like 'Gred XX'.
    $level = $this->faker->unique()->numberBetween(1, 54); // Generate a unique level between 1 and 54 (common max civil service grade)
    $name = 'Gred ' . $level; // Basic name based on level

    // Adjust for higher grades if necessary (e.g., JUSA, Turus)
    if ($level >= 52) {
      $name = 'JUSA ' . $this->faker->randomElement(['C', 'B', 'A']);
    }
    if ($level >= 54) {
      $name = 'Turus ' . $this->faker->randomElement(['III', 'II', 'I']);
    }


    return [
      'name' => $name, // Unique grade name
      'level' => $level, // Unique numeric level
      'is_approver_grade' => false, // Default is not an approver grade
    ];
  }

  /**
   * Indicate that the grade is an approver grade (e.g., Gred 41 and above).
   * This state sets 'is_approver_grade' to true and ensures the level is high enough.
   *
   * @param int $minApproverLevel The minimum level for this to be considered an approver grade.
   * @return static
   */
  public function approverGrade(int $minApproverLevel = 41): static
  {
    return $this->state(function (array $attributes) use ($minApproverLevel) {
      // Ensure the level is at least the minimum required for an approver grade
      $level = $attributes['level'] ?? $this->faker->unique()->numberBetween($minApproverLevel, 54); // Generate a level >= min, ensuring uniqueness

      // If the generated level is below the minimum, get a new unique one within the approver range
      if ($level < $minApproverLevel) {
        $level = $this->faker->unique()->numberBetween($minApproverLevel, 54);
      }

      // Update name based on the (potentially new) level
      $name = 'Gred ' . $level;
      if ($level >= 52) {
        $name = 'JUSA ' . $this->faker->randomElement(['C', 'B', 'A']);
      }
      if ($level >= 54) {
        $name = 'Turus ' . $this->faker->randomElement(['III', 'II', 'I']);
      }


      return [
        'name' => $name, // Update name to match the level
        'level' => $level, // Ensure the level is set
        'is_approver_grade' => true, // Mark as an approver grade
      ];
    });
  }

  /**
   * Indicate a standard non-approver grade (e.g., Gred 40 or below).
   * This state sets 'is_approver_grade' to false and ensures the level is below a common threshold.
   *
   * @param int $maxNonApproverLevel The maximum level for a non-approver grade.
   * @return static
   */
  public function nonApproverGrade(int $maxNonApproverLevel = 40): static
  {
    return $this->state(function (array $attributes) use ($maxNonApproverLevel) {
      // Ensure the level is at most the maximum for a non-approver grade
      $level = $attributes['level'] ?? $this->faker->unique()->numberBetween(1, $maxNonApproverLevel); // Generate level <= max, ensuring uniqueness

      // If the generated level is above the maximum, get a new unique one within the non-approver range
      if ($level > $maxNonApproverLevel) {
        $level = $this->faker->unique()->numberBetween(1, $maxNonApproverLevel);
      }

      // Update name based on the (potentially new) level
      $name = 'Gred ' . $level;

      return [
        'name' => $name, // Update name to match the level
        'level' => $level, // Ensure the level is set
        'is_approver_grade' => false, // Mark as a non-approver grade
      ];
    });
  }

  // Add other states or methods as needed
}
