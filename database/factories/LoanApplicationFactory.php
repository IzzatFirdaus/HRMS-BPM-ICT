<?php

namespace Database\Factories;

use App\Models\LoanApplication; // Import the LoanApplication model
use App\Models\User; // Import the User model
use App\Models\LoanApplicationItem; // FIX: Import the LoanApplicationItem model
use Illuminate\Database\Eloquent\Factories\Factory; // Import the base Factory class
use Illuminate\Support\Carbon; // Import Carbon for date manipulation

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\LoanApplication>
 */
class LoanApplicationFactory extends Factory
{
  /**
   * The name of the factory's corresponding model.
   *
   * @var string
   */
  protected $model = LoanApplication::class;

  /**
   * Define the model's default state.
   * This defines the basic attributes for a default, usually 'draft', loan application.
   *
   * @return array<string, mixed>
   */
  public function definition(): array
  {
    // Create a user (applicant) for this application by default using factory state
    $user = User::factory();

    // Set loan start and end dates
    $startDate = $this->faker->dateTimeBetween('-1 week', '+2 weeks'); // Start date in the near future
    $endDate = $this->faker->dateTimeBetween($startDate, (clone $startDate)->modify('+1 month')); // End date after start, within a month

    return [
      'user_id' => $user, // Link to the applicant user factory state
      // Responsible officer: 20% chance of being a different user, otherwise null (applicant is responsible)
      'responsible_officer_id' => $this->faker->boolean(20) ? User::factory() : null, // Link to responsible officer user factory state or null
      'purpose' => $this->faker->sentence(), // Purpose of the loan (Tujuan Permohonan)
      'location' => $this->faker->streetAddress(), // Location where equipment will be used (Lokasi)
      'loan_start_date' => $startDate, // Loan Start Date (Tarikh Pinjaman)
      'loan_end_date' => $endDate, // Loan End Date (Tarikh Dijangka Pulang)
      'status' => 'draft', // FIX: Set default status to 'draft' as per migration and initial state
      'rejection_reason' => null, // Rejection reason, null by default
      'applicant_confirmation_timestamp' => null, // FIX: Applicant confirmation timestamp, null by default (set on submission)
    ];
  }

  /**
   * Configure the model factory.
   * This method runs after a model has been created and allows creating related models (like LoanApplicationItems).
   *
   * @return $this
   */
  public function configure()
  {
    return $this->afterCreating(function (LoanApplication $loanApplication) {
      // FIX: Create related LoanApplicationItem records after the application is created
      // Create between 1 and 3 items for the application
      LoanApplicationItem::factory()->count($this->faker->numberBetween(1, 3))
        ->state(['loan_application_id' => $loanApplication->id]) // Link items to the created application
        ->create();
    });
  }

  /**
   * Indicate that the application is in a specific status.
   * This state method allows easily setting the status and clearing dependent fields.
   *
   * @param string $status The desired status.
   * @return static
   */
  public function status(string $status): static
  {
    return $this->state(fn(array $attributes) => [
      'status' => $status,
      // Clear rejection reason if status is not 'rejected'
      'rejection_reason' => $status !== 'rejected' ? null : ($attributes['rejection_reason'] ?? $this->faker->sentence()),
      // Clear applicant confirmation timestamp if status is 'draft' (assuming confirmation only happens on submission)
      'applicant_confirmation_timestamp' => $status === 'draft' ? null : ($attributes['applicant_confirmation_timestamp'] ?? now()),
      // Statuses like 'issued', 'returned', 'overdue' etc. would typically involve LoanTransaction creation,
      // which is not handled in this generic status state. Use dedicated states or seeders for those.
    ]);
  }

  /**
   * Indicate that the application is in the 'pending_support' status.
   * Helper state for a common workflow step.
   *
   * @return static
   */
  public function pendingSupport(): static
  {
    return $this->status('pending_support');
  }

  /**
   * Indicate that the application has been approved (status 'approved').
   * This state sets the status to 'approved'.
   *
   * @return static
   */
  public function approved(): static
  {
    return $this->status('approved');
  }

  /**
   * Indicate that the application has been rejected (status 'rejected').
   * This state sets the status to 'rejected' and adds a rejection reason.
   *
   * @return static
   */
  public function rejected(): static
  {
    return $this->status('rejected')->state(fn(array $attributes) => [
      // Ensure rejection reason is set when specifically using the rejected state
      'rejection_reason' => $attributes['rejection_reason'] ?? $this->faker->sentence(),
    ]);
  }


  /**
   * Indicate that the loan has been issued (status 'issued').
   * This state sets the status to 'issued'. Creating corresponding LoanTransactions
   * is typically handled separately (e.g., in a seeder using this state).
   *
   * @return static
   */
  public function issued(): static
  {
    return $this->status('issued');
  }

  /**
   * Indicate that the loan has been returned (status 'returned').
   * This state sets the status to 'returned'. Creating corresponding LoanTransactions
   * is typically handled separately.
   *
   * @return static
   */
  public function returned(): static
  {
    return $this->status('returned');
  }

  // Add states for other specific statuses like 'partially_issued', 'overdue', 'cancelled' if needed
}
