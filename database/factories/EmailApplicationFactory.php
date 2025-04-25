<?php

namespace Database\Factories;

use App\Models\EmailApplication; // Import the EmailApplication model
use App\Models\User; // Import the User model
use Illuminate\Database\Eloquent\Factories\Factory; // Import the base Factory class
use Illuminate\Support\Str; // Import Str facade

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\EmailApplication>
 */
class EmailApplicationFactory extends Factory
{
  /**
   * The name of the factory's corresponding model.
   *
   * @var string
   */
  protected $model = EmailApplication::class;

  /**
   * Define the model's default state.
   * This defines the basic attributes for a default, usually 'draft', email application.
   *
   * @return array<string, mixed>
   */
  public function definition(): array
  {
    // Create a user for this application by default.
    // Using User::factory() here defines the relationship using a factory state.
    // When the EmailApplication is created, this user will also be created if they don't exist,
    // or you can override this using ->for(User::find(1)) or ->for(User::factory())
    $user = User::factory();

    // Generate a base email slug from potential user name fields for proposed email
    // Assuming User model has first_name and last_name; adjust if needed based on your User model structure.
    $nameSlug = Str::slug($this->faker->firstName . '.' . $this->faker->lastName, '-'); // Use hyphen slug for email compatibility

    return [
      'user_id' => $user, // Link to the user factory state (will create user when application is created)
      'service_status' => $this->faker->randomElement(['Kakitangan Tetap', 'Lantikan Kontrak', 'Personel MySTEP', 'Pelajar Latihan Industri', 'E-mel Sandaran MOTAC']), // FIX: Added service_status based on PDF/form [cite: 5]
      'purpose' => $this->faker->sentence(), // Purpose (Tujuan/Catatan) [cite: 5]
      'proposed_email' => $nameSlug . '@motac.gov.my', // Example proposed email format (Cadangan E-mel/ID) [cite: 5]

      // Group email details (optional fields in PDF) [cite: 5]
      'group_email' => null,
      'group_admin_name' => null,
      'group_admin_email' => null,

      // Add supporting_officer_id - link to another user (the first approver assigned)
      // For simplicity, link to a random existing user or create one using factory state.
      'supporting_officer_id' => User::factory(), // FIX: Added supporting_officer_id

      'status' => 'draft', // FIX: Set default status to 'draft' as per migration and initial state
      'certification_accepted' => $this->faker->boolean(90), // Pengesahan Pemohon checkbox [cite: 1] (90% chance is true for fake data)
      'certification_timestamp' => $this->faker->optional()->dateTimeBetween('-1 year', 'now'), // Timestamp when certification accepted [cite: 1]
      'rejection_reason' => null, // Rejection reason, null by default [cite: 5] (implicit in workflow)
      'final_assigned_email' => null, // The actual email assigned after provisioning, null by default
      'final_assigned_user_id' => null, // The actual user ID assigned by external system, null by default
      'provisioned_at' => null, // FIX: Added provisioned_at timestamp, null by default
    ];
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
    return $this->state(fn(array $attributes) => [
      'status' => $status,
      // Clear rejection reason if status is not 'rejected'
      'rejection_reason' => $status !== 'rejected' ? null : ($attributes['rejection_reason'] ?? $this->faker->sentence()),
      // Clear final assigned details and provisioned_at if status is not 'completed'
      'final_assigned_email' => $status !== 'completed' ? null : ($attributes['final_assigned_email'] ?? null),
      'final_assigned_user_id' => $status !== 'completed' ? null : ($attributes['final_assigned_user_id'] ?? null),
      'provisioned_at' => $status !== 'completed' ? null : ($attributes['provisioned_at'] ?? null),
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
   * Indicate that the application is in the 'pending_admin' status.
   * Helper state for a common workflow step.
   *
   * @return static
   */
  public function pendingAdmin(): static
  {
    return $this->status('pending_admin');
  }

  /**
   * Indicate that the application has been approved and processed (status 'completed').
   * This state sets the status to 'completed' and populates the final assigned email/ID and timestamp.
   *
   * @return static
   */
  public function completed(): static
  {
    return $this->state(function (array $attributes) {
      // Ensure user is available (either created by default or explicitly set via ->for())
      // Accessing user directly from attributes might require the user to be created beforehand if not using factory state in definition
      $user = $attributes['user_id'] instanceof Factory ? $attributes['user_id']->create() : User::find($attributes['user_id']);
      // Re-generate name slug based on the actual user created/found
      $nameSlug = Str::slug($user->first_name . '.' . $user->last_name, '-'); // Adjust based on your User model name fields

      return [
        'status' => 'completed',
        // Populate final assigned fields with fake data or use provided values
        'final_assigned_email' => $attributes['final_assigned_email'] ?? $nameSlug . $user->id . '@motac.gov.my', // Use generated or override
        'final_assigned_user_id' => $attributes['final_assigned_user_id'] ?? 'motac_uid_' . $user->id, // Example external User ID format
        'provisioned_at' => $attributes['provisioned_at'] ?? now(), // FIX: Set provisioned_at timestamp to now
        'rejection_reason' => null, // Clear rejection reason
      ];
    });
  }

  /**
   * Indicate that the application has been rejected.
   * This state sets the status to 'rejected' and adds a rejection reason.
   *
   * @return static
   */
  public function rejected(): static
  {
    return $this->state(fn(array $attributes) => [
      'status' => 'rejected',
      'rejection_reason' => $attributes['rejection_reason'] ?? $this->faker->sentence(), // Set a rejection reason
      'final_assigned_email' => null, // Clear final assigned details
      'final_assigned_user_id' => null,
      'provisioned_at' => null,
    ]);
  }

  // Add states for other specific statuses like 'approved', 'processing', 'provision_failed' if needed

  /**
   * Indicate that the application requires supporting officer approval.
   * This implies status is pending_support and supporting_officer_id is set.
   * Assigns a specific officer or creates one.
   *
   * @param User|Factory|int|null $officer The officer to assign (User model, factory state, ID, or null to create a new one).
   * @return static
   */
  public function needsSupportApproval(User|Factory|int|null $officer = null): static
  {
    return $this->state(function (array $attributes) use ($officer) {
      return [
        'status' => 'pending_support',
        // Assign the provided officer, or create a new one if none provided
        'supporting_officer_id' => $officer ?? User::factory(),
        'certification_accepted' => true, // Assumes certification is accepted before reaching this stage
        'certification_timestamp' => $attributes['certification_timestamp'] ?? now(),
        'rejection_reason' => null, // Clear rejection reason
        'final_assigned_email' => null, // Clear final assigned details
        'final_assigned_user_id' => null,
        'provisioned_at' => null,
      ];
    });
  }

  /**
   * Indicate that the application requires IT Admin approval/processing.
   * This implies status is pending_admin.
   *
   * @return static
   */
  public function needsAdminApproval(): static
  {
    return $this->state(fn(array $attributes) => [
      'status' => 'pending_admin',
      // supporting_officer_id should ideally be set from previous stage (needsSupportApproval), don't override here.
      'certification_accepted' => true, // Assumes certification accepted
      'certification_timestamp' => $attributes['certification_timestamp'] ?? now(),
      'rejection_reason' => null, // Clear rejection reason
      'final_assigned_email' => null, // Clear final assigned details
      'final_assigned_user_id' => null,
      'provisioned_at' => null,
    ]);
  }
}
