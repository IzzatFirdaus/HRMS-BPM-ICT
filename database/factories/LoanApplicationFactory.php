<?php

namespace Database\Factories;

use App\Models\LoanApplication; // Import the LoanApplication model
use App\Models\User; // Import the User model
use App\Models\LoanApplicationItem; // Import the LoanApplicationItem model (Assuming this model/factory/migration exists)
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
    // Fetch the ID of the first user (often the admin) for audit columns.
    // This relies on at least one user being seeded *before* seeders using this factory.
    $adminUserForAudit = User::first();
    $auditUserId = $adminUserForAudit?->id ?? null; // Use null if no user exists yet

    // Fetch random existing user IDs for the applicant and responsible officer.
    // This relies on Users being seeded *before* LoanApplications.
    // Using inRandomOrder()->first()?->id links to existing users (nullable if no users exist).
    // If you always want a *new* user created, use User::factory() instead.
    $applicantUserId = User::inRandomOrder()->first()?->id;
    $responsibleOfficerId = User::inRandomOrder()->first()?->id;


    // Set loan start and end dates
    $startDate = $this->faker->dateTimeBetween('-1 week', '+2 weeks'); // Start date in the near future
    $endDate = $this->faker->dateTimeBetween($startDate, (clone $startDate)->modify('+1 month')); // End date after start, within a month

    return [
      // Link to the applicant user. Assign a random existing User ID or null.
      // Use ->for(User::factory()) or ->for(User::find(X)) in seeders/other factories to override.
      'user_id' => $applicantUserId,

      // Link to the responsible officer. Assign a random existing User ID or null.
      // 20% chance of being a different user, otherwise null (applicant is responsible)
      'responsible_officer_id' => $this->faker->boolean(20) ? $responsibleOfficerId : null,

      'purpose' => $this->faker->sentence(), // Purpose of the loan
      'location' => $this->faker->streetAddress(), // Location where equipment will be used
      'loan_start_date' => $startDate->format('Y-m-d'), // Formatted Start Date
      'loan_end_date' => $endDate->format('Y-m-d'), // Formatted End Date

      'status' => 'draft', // Default status as per migration enum

      'rejection_reason' => null, // Rejection reason, null by default
      'applicant_confirmation_timestamp' => null, // Applicant confirmation timestamp, null by default

      // 👇 ADDED: Audit fields for CreatedUpdatedDeletedBy trait
      'created_by' => $auditUserId,
      'updated_by' => $auditUserId,
      'deleted_by' => null, // Default to null for soft deletes
      // ☝️ END ADDED
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
      // Create related LoanApplicationItem records after the application is created
      // Create between 1 and 3 items for the application by default
      LoanApplicationItem::factory()->count($this->faker->numberBetween(1, 3))
        ->state(['loan_application_id' => $loanApplication->id]) // Link items to the created application
        ->create();
    });
  }

  /**
   * Indicate that the application is in a specific status.
   * This state method allows easily setting the status and cleaning up dependent fields.
   *
   * @param string $status The desired status.
   * @return static
   */
  public function status(string $status): static
  {
    // Ensure the status string matches a valid enum value in the migration.
    // Example: $this->faker->randomElement(['draft', 'pending_support', 'approved', 'rejected', 'partially_issued', 'issued', 'returned', 'overdue', 'cancelled']),

    return $this->state(fn(array $attributes) => [
      'status' => $status,
      // Clear/Set rejection reason based on status
      'rejection_reason' => $status === 'rejected' ? ($attributes['rejection_reason'] ?? $this->faker->sentence()) : null,
      // applicant_confirmation_timestamp is typically set via a 'certified' state or explicitly where needed.
      // Leave it null by default in the generic status state.
    ]);
  }

  /**
   * Indicate that the applicant has accepted certification.
   * This state sets applicant_confirmation_timestamp.
   *
   * @return static
   */
  public function certified(): static
  {
    return $this->state(fn(array $attributes) => [
      'applicant_confirmation_timestamp' => $attributes['applicant_confirmation_timestamp'] ?? now(),
    ]);
  }

  /**
   * Indicate that the application is in the 'pending_support' status.
   * Helper state for a common workflow step.
   * Requires applicant certification.
   *
   * @return static
   */
  public function pendingSupport(): static
  {
    return $this->status('pending_support')->certified();
  }

  /**
   * Indicate that the application has been approved (status 'approved').
   * Requires applicant certification and potentially prior approvals.
   *
   * @return static
   */
  public function approved(): static
  {
    return $this->status('approved')->certified();
  }

  /**
   * Indicate that the application has been rejected (status 'rejected').
   * Requires applicant certification.
   *
   * @return static
   */
  public function rejected(): static
  {
    return $this->status('rejected')
      ->certified()
      ->state(fn(array $attributes) => [
        // Ensure rejection reason is set when specifically using the rejected state
        'rejection_reason' => $attributes['rejection_reason'] ?? $this->faker->sentence(),
      ]);
  }


  /**
   * Indicate that the loan has been issued (status 'issued').
   * This state sets the status to 'issued'. Creating corresponding LoanTransactions
   * is typically handled separately (e.g., in a seeder using this state and then creating transactions).
   * Requires applicant certification and approval.
   *
   * @return static
   */
  public function issued(): static
  {
    return $this->status('issued')->certified()->approved(); // Assuming approval is a prerequisite
  }

  /**
   * Indicate that the loan has been returned (status 'returned').
   * This state sets the status to 'returned'. Creating corresponding LoanTransactions
   * is typically handled separately.
   * Requires applicant certification, approval, and issuing.
   *
   * @return static
   */
  public function returned(): static
  {
    return $this->status('returned')->certified()->approved()->issued(); // Assuming previous states are prerequisites
  }

  /**
   * Indicate that the application is cancelled (status 'cancelled').
   *
   * @return static
   */
  public function cancelled(): static
  {
    return $this->status('cancelled');
  }


  /**
   * Indicate that the application is deleted (soft deleted).
   * Requires SoftDeletes trait on the LoanApplication model.
   */
  public function deleted(): static
  {
    return $this->state(fn(array $attributes) => [
      'deleted_at' => now(),
      'deleted_by' => User::first()?->id ?? null, // Assign a deleter if possible
      // You might also set the status to a specific value for deleted items if needed
    ]);
  }
}
