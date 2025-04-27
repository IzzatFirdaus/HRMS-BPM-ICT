<?php

namespace Database\Factories;

use App\Models\EmployeeLeave; // Import the EmployeeLeave model
use App\Models\Employee;    // Import Employee model for linking
use App\Models\Leave;       // Import Leave model for linking
use App\Models\User;        // Import User model for audit columns
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon; // Import Carbon for date/time manipulation

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\EmployeeLeave>
 */
class EmployeeLeaveFactory extends Factory
{
  /**
   * The name of the factory's corresponding model.
   *
   * @var string
   */
  protected $model = EmployeeLeave::class;

  /**
   * Define the model's default state.
   * This defines the basic attributes for a default employee leave record.
   *
   * @return array<string, mixed>
   */
  public function definition(): array
  {
    // Fetch random existing IDs for foreign keys.
    // This relies on seeders for Employees, Leaves, and Users
    // running *before* seeders using this factory.
    $employeeId = Employee::inRandomOrder()->first()?->id;
    $leaveId = Leave::inRandomOrder()->first()?->id;

    // Fetch the ID of the first user (often the admin) for audit columns.
    $adminUserForAudit = User::first();
    $auditUserId = $adminUserForAudit?->id ?? null; // Use null if no user exists yet

    // Generate leave dates
    $fromDate = $this->faker->dateTimeBetween('-6 months', 'now');
    // Ensure toDate is after or on fromDate, and within a reasonable range
    $toDate = $this->faker->dateTimeBetween($fromDate, (clone $fromDate)->modify('+2 weeks')); // Leave lasts up to 2 weeks

    // Generate optional start/end times for partial day leaves
    $hasTimes = $this->faker->boolean(30); // 30% chance of having specific times
    $startAt = $hasTimes ? Carbon::parse($fromDate)->setTime($this->faker->numberBetween(8, 11), $this->faker->numberBetween(0, 59), 0)->format('H:i:s') : null;
    $endAt = $hasTimes ? Carbon::parse($toDate)->setTime($this->faker->numberBetween(13, 17), $this->faker->numberBetween(0, 59), 0)->format('H:i:s') : null;


    return [
      // Link to the employee and leave type. Assign random existing IDs or null.
      'employee_id' => $employeeId,
      'leave_id' => $leaveId,

      'from_date' => $fromDate->format('Y-m-d'), // Formatted start date of leave
      'to_date' => $toDate->format('Y-m-d'), // Formatted end date of leave
      'start_at' => $startAt, // Optional start time
      'end_at' => $endAt, // Optional end time

      'note' => $this->faker->optional()->sentence(), // Optional note

      'is_authorized' => false, // Default is not authorized
      'is_checked' => false, // Default is not checked

      // Audit fields for CreatedUpdatedDeletedBy trait
      'created_by' => $auditUserId,
      'updated_by' => $auditUserId,
      'deleted_by' => null, // Default to null for soft deletes

      // Soft delete timestamp, null by default
      'deleted_at' => null,
    ];
  }

  /**
   * Indicate that the leave record is authorized.
   */
  public function authorized(): static
  {
    return $this->state(fn(array $attributes) => [
      'is_authorized' => true,
    ]);
  }

  /**
   * Indicate that the leave record has been checked.
   */
  public function checked(): static
  {
    return $this->state(fn(array $attributes) => [
      'is_checked' => true,
    ]);
  }

  /**
   * Indicate that the leave record is deleted (soft deleted).
   * Requires SoftDeletes trait on the EmployeeLeave model.
   */
  public function deleted(): static
  {
    return $this->state(fn(array $attributes) => [
      'deleted_at' => now(),
      'deleted_by' => User::first()?->id ?? null, // Assign a deleter if possible
    ]);
  }
}
