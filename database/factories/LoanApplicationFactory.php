<?php

namespace Database\Factories;

use App\Models\LoanApplication;
use App\Models\User; // Assuming User model exists
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\LoanApplication>
 */
class LoanApplicationFactory extends Factory
{
  protected $model = LoanApplication::class;

  /**
   * Define the model's default state.
   *
   * @return array<string, mixed>
   */
  public function definition(): array
  {
    $user = User::factory()->create(); // Creates a user for this application

    $startDate = $this->faker->dateTimeBetween('-1 month', '+1 week');
    $endDate = $this->faker->dateTimeBetween($startDate, (clone $startDate)->modify('+2 weeks'));

    return [
      'user_id' => $user->id,
      'responsible_officer_id' => $this->faker->boolean(20) ? User::factory()->create()->id : null, // 20% chance of having a different responsible officer
      'purpose' => $this->faker->sentence,
      'location' => $this->faker->streetAddress,
      'loan_start_date' => $startDate,
      'loan_end_date' => $endDate,
      'status' => $this->faker->randomElement(['draft', 'pending_support', 'approved', 'rejected', 'partially_issued', 'issued', 'returned', 'overdue', 'cancelled']),
      'rejection_reason' => $this->faker->randomElement([null, $this->faker->sentence]),
      'applicant_confirmation_timestamp' => now(), // Assuming confirmation happens immediately on creation for factory
    ];
  }

  /**
   * Configure the model factory.
   *
   * @return $this
   */
  public function configure()
  {
    return $this->afterCreating(function (LoanApplication $loanApplication) {
      // Create related loan items after the application is created
      // You would typically create LoanApplicationItem records here
      // For simplicity, let's just log for now
      // \App\Models\LoanApplicationItem::factory()->count($this->faker->numberBetween(1, 3))->create(['loan_application_id' => $loanApplication->id]);
    });
  }

  /**
   * Indicate that the application is in a specific status.
   *
   * @param string $status
   * @return static
   */
  public function status(string $status): static
  {
    return $this->state(fn(array $attributes) => [
      'status' => $status,
    ]);
  }

  /**
   * Indicate that the loan has been issued.
   *
   * @return static
   */
  public function issued(): static
  {
    return $this->state(fn(array $attributes) => [
      'status' => 'issued',
    ]);
  }

  /**
   * Indicate that the loan has been returned.
   *
   * @return static
   */
  public function returned(): static
  {
    return $this->state(fn(array $attributes) => [
      'status' => 'returned',
    ]);
  }
}
