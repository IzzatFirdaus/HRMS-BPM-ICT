<?php

namespace Database\Factories;

use App\Models\Grade; // Import the Grade model
use App\Models\User; // Import the User model for audit columns
use Illuminate\Database\Eloquent\Factories\Factory; // Import the base Factory class
use Illuminate\Support\Carbon; // Import Carbon for date/time manipulation

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
    // Fetch the ID of the first user (often the admin) for audit columns.
    // This relies on at least one user being seeded *before* seeders using this factory.
    $adminUserForAudit = User::first();
    $auditUserId = $adminUserForAudit?->id ?? null; // Use null if no user exists yet

    // Generate a unique grade level and a basic name based on the level.
    // Generate a unique level between 1 and a reasonable max (e.g., 54).
    $level = $this->faker->unique()->numberBetween(1, 54);
    $name = 'Gred ' . $level; // Basic name based on level

    // Adjust name for higher grades if necessary (e.g., JUSA, Turus) based on your system logic
    if ($level >= 52 && $level <= 54) { // Levels 52, 53, 54 might be JUSA
      $jusaLevel = $level - 51; // 52->1, 53->2, 54->3
      $jusaSuffix = match ($jusaLevel) {
        1 => 'C',
        2 => 'B',
        3 => 'A',
        default => 'Unknown' // Fallback
      };
      $name = 'JUSA ' . $jusaSuffix;
    } elseif ($level > 54) { // Levels above 54 might be Turus (adjust levels as per actual system)
      $turusLevel = $level - 54; // 55->1, 56->2, etc.
      $turusSuffix = match ($turusLevel) {
        1 => 'III',
        2 => 'II',
        3 => 'I', // Adjust number of Turus levels as needed
        default => 'Unknown'
      };
      $name = 'Turus ' . $turusSuffix;
    }
    // Add other specific grade names if needed for certain levels

    return [
      'name' => $name, // Unique grade name
      'level' => $level, // Unique numeric level
      'is_approver_grade' => false, // Default is not an approver grade

      // 👇 ADDED: Audit fields for CreatedUpdatedDeletedBy trait
      'created_by' => $auditUserId,
      'updated_by' => $auditUserId,
      'deleted_by' => null, // Default to null for soft deletes
      // ☝️ END ADDED

      // 👇 ADDED: Soft delete timestamp, null by default
      'deleted_at' => null,
      // ☝️ END ADDED
    ];
  }

  /**
   * Indicate that the grade is an approver grade.
   * This state sets 'is_approver_grade' to true and ensures the level is high enough.
   * It tries to generate a unique level within the approver range.
   *
   * @param int $minApproverLevel The minimum level for this to be considered an approver grade (e.g., 41).
   * @return static
   */
  public function approverGrade(int $minApproverLevel = 41): static
  {
    return $this->state(function (array $attributes) use ($minApproverLevel) {
      // Ensure the level is at least the minimum required for an approver grade
      // Generate a unique level >= minApproverLevel. If a level was already set in definition/previous state, use it, but ensure it meets the min.
      $level = $attributes['level'] ?? null;
      if ($level === null || $level < $minApproverLevel) {
        $level = $this->faker->unique()->numberBetween($minApproverLevel, 60); // Generate a unique level within a higher range
      }

      // Recalculate name based on the (potentially new) level
      $name = 'Gred ' . $level;
      if ($level >= 52 && $level <= 54) { // Levels 52, 53, 54 might be JUSA
        $jusaLevel = $level - 51;
        $jusaSuffix = match ($jusaLevel) {
          1 => 'C',
          2 => 'B',
          3 => 'A',
          default => 'Unknown'
        };
        $name = 'JUSA ' . $jusaSuffix;
      } elseif ($level > 54) { // Levels above 54 might be Turus (adjust levels)
        $turusLevel = $level - 54;
        $turusSuffix = match ($turusLevel) {
          1 => 'III',
          2 => 'II',
          3 => 'I',
          default => 'Unknown'
        };
        $name = 'Turus ' . $turusSuffix;
      } else {
        // If level is >= minApproverLevel but below JUSA/Turus range
        $name = 'Gred ' . $level;
      }


      return [
        'name' => $attributes['name'] ?? $name, // Use provided name if any, otherwise generate based on level
        'level' => $level, // Ensure the level is set correctly
        'is_approver_grade' => true, // Mark as an approver grade
      ];
    });
  }

  /**
   * Indicate a standard non-approver grade.
   * This state sets 'is_approver_grade' to false and ensures the level is below a common threshold.
   * It tries to generate a unique level within the non-approver range.
   *
   * @param int $maxNonApproverLevel The maximum level for a non-approver grade (e.g., 40).
   * @return static
   */
  public function nonApproverGrade(int $maxNonApproverLevel = 40): static
  {
    return $this->state(function (array $attributes) use ($maxNonApproverLevel) {
      // Ensure the level is at most the maximum for a non-approver grade
      // Generate a unique level <= maxNonApproverLevel. If a level was already set in definition/previous state, use it, but ensure it meets the max.
      $level = $attributes['level'] ?? null;
      if ($level === null || $level > $maxNonApproverLevel) {
        $level = $this->faker->unique()->numberBetween(1, $maxNonApproverLevel); // Generate a unique level within a lower range
      }

      // Recalculate basic name based on the (potentially new) level (JUSA/Turus logic not needed here)
      $name = 'Gred ' . $level;

      return [
        'name' => $attributes['name'] ?? $name, // Use provided name if any, otherwise generate
        'level' => $level, // Ensure the level is set correctly
        'is_approver_grade' => false, // Mark as a non-approver grade
      ];
    });
  }

  /**
   * Indicate that the grade is deleted (soft deleted).
   * Requires SoftDeletes trait on the Grade model.
   */
  public function deleted(): static
  {
    return $this->state(fn(array $attributes) => [
      'deleted_at' => now(),
      'deleted_by' => User::first()?->id ?? null, // Assign a deleter if possible
    ]);
  }

  // Add other states or methods as needed
}
