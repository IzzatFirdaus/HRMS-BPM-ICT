<?php

namespace Database\Factories;

use App\Models\Approval; // Import the Approval model
use App\Models\User;     // Import the User model for officer and audit columns
use App\Models\EmailApplication; // Import EmailApplication as an example approvable model
use App\Models\LoanApplication; // Import LoanApplication as an example approvable model
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model; // Import base Model class for type hinting
use Illuminate\Support\Carbon; // Import Carbon for date/time manipulation

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Approval>
 */
class ApprovalFactory extends Factory
{
  /**
   * The name of the factory's corresponding model.
   *
   * @var string
   */
  protected $model = Approval::class;

  /**
   * Define the model's default state.
   * This defines the basic attributes for a default, usually 'pending', approval record.
   *
   * @return array<string, mixed>
   */
  public function definition(): array
  {
    // Fetch random existing user IDs for the officer and audit columns.
    // This relies on Users being seeded *before* the Approval seeder.
    $officerId = User::inRandomOrder()->first()?->id;

    // Fetch the ID of the first user (often the admin) for audit columns.
    $adminUserForAudit = User::first();
    $auditUserId = $adminUserForAudit?->id ?? null; // Use null if no user exists yet

    return [
      // Note: approvable_id and approvable_type are NOT defined here.
      // They are set using the ->for() method or dedicated state methods
      // that link this approval to a specific approvable model instance/factory.

      // Link to the officer responsible for this approval step.
      'officer_id' => $officerId, // Assign a random existing User ID or null

      'stage' => null, // Stage is nullable, can be set by states or explicitly
      'status' => 'pending', // Default status as per migration
      'comments' => null, // Comments are null by default
      'approval_timestamp' => null, // Timestamp is null by default (set when status is approved/rejected)

      // Audit fields for CreatedUpdatedDeletedBy trait
      'created_by' => $auditUserId,
      'updated_by' => $auditUserId,
      'deleted_by' => null, // Default to null for soft deletes
    ];
  }

  /**
   * Configure the model factory.
   *
   * @return $this
   */
  public function configure()
  {
    // This is where you might define callbacks, but for polymorphic relationships,
    // using the ->for() method or dedicated states is usually clearer than afterMaking/afterCreating.
    return $this; // No default callbacks needed here
  }


  /**
   * Indicate that the approval is for a specific approvable model instance.
   * This sets the approvable_id and approvable_type.
   *
   * @param Model|Factory $approvable The approvable model instance or factory.
   * @return static
   */
  public function forApprovable(Model|Factory $approvable): static
  {
    return $this->for($approvable, 'approvable'); // Use the ->for() method with the relationship name 'approvable'
  }

  /**
   * Indicate that the approval is for an EmailApplication.
   * Helper state for a common approvable type.
   *
   * @param EmailApplication|Factory|int|null $emailApplication The EmailApplication instance, factory, ID, or null to create a new one.
   * @return static
   */
  public function forEmailApplication(EmailApplication|Factory|int|null $emailApplication = null): static
  {
    // If a model instance or factory is provided, use it. If null, create a new EmailApplication.
    $approvable = $emailApplication ?? EmailApplication::factory();
    return $this->forApprovable($approvable);
  }

  /**
   * Indicate that the approval is for a LoanApplication.
   * Helper state for a common approvable type.
   *
   * @param LoanApplication|Factory|int|null $loanApplication The LoanApplication instance, factory, ID, or null to create a new one.
   * @return static
   */
  public function forLoanApplication(LoanApplication|Factory|int|null $loanApplication = null): static
  {
    // If a model instance or factory is provided, use it. If null, create a new LoanApplication.
    $approvable = $loanApplication ?? LoanApplication::factory();
    return $this->forApprovable($approvable);
  }


  /**
   * Indicate that the approval is in a specific status.
   * This state method allows easily setting the status and related fields.
   *
   * @param string $status The desired status ('pending', 'approved', 'rejected').
   * @return static
   */
  public function status(string $status): static
  {
    // Ensure the status string matches a valid value in the migration enum/definition.
    $validStatuses = ['pending', 'approved', 'rejected']; // Based on your migration
    if (!in_array($status, $validStatuses)) {
      throw new \InvalidArgumentException("Invalid approval status: {$status}. Valid statuses are: " . implode(', ', $validStatuses));
    }

    return $this->state(fn(array $attributes) => [
      'status' => $status,
      // Set/Clear related fields based on status
      'comments' => ($status === 'approved' || $status === 'rejected') ? ($attributes['comments'] ?? $this->faker->sentence()) : null,
      'approval_timestamp' => ($status === 'approved' || $status === 'rejected') ? ($attributes['approval_timestamp'] ?? now()) : null,
      'deleted_by' => $status === 'rejected' ? ($attributes['deleted_by'] ?? User::first()?->id ?? null) : null, // Example: deleted_by might be set on rejection if it represents the approver rejecting. Adjust based on your logic.
    ]);
  }

  /**
   * Indicate that the approval has been approved.
   * Sets status to 'approved' and adds comments/timestamp.
   *
   * @return static
   */
  public function approved(): static
  {
    return $this->status('approved');
  }

  /**
   * Indicate that the approval has been rejected.
   * Sets status to 'rejected' and adds comments/timestamp.
   *
   * @return static
   */
  public function rejected(): static
  {
    return $this->status('rejected');
  }

  /**
   * Indicate the stage of the approval.
   *
   * @param string $stage The approval stage (e.g., 'support_review', 'admin_review').
   * @return static
   */
  public function stage(string $stage): static
  {
    return $this->state(fn(array $attributes) => [
      'stage' => $stage,
    ]);
  }


  /**
   * Indicate that the approval is deleted (soft deleted).
   * Requires SoftDeletes trait on the Approval model.
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
