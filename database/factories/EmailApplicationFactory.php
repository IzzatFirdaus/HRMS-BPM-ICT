<?php

namespace Database\Factories;

use App\Models\EmailApplication; // Import the EmailApplication model
use App\Models\User; // Import the User model
use Illuminate\Database\Eloquent\Factories\Factory; // Import the base Factory class
use Illuminate\Support\Str; // Import Str facade
use Illuminate\Support\Carbon; // Import Carbon for date/time manipulation
use Illuminate\Support\Facades\DB; // Import DB facade if needed for unique checks

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
    // Fetch the ID of the first user (often the admin) for audit columns.
    // This relies on at least one user being seeded *before* seeders using this factory.
    // Use DB facade to bypass potential SoftDeletes issues if User model uses it
    $adminUserForAudit = DB::table('users')->first();
    $auditUserId = $adminUserForAudit?->id ?? null; // Use null if no user exists yet


    // Fetch a random existing user ID for the applicant (user_id).
    // This relies on Users being seeded *before* EmailApplications.
    // Using inRandomOrder()->first() links to an existing user randomly.
    $applicantUser = User::inRandomOrder()->first();
    $applicantUserId = $applicantUser?->id ?? null;


    // Fetch a random existing user ID for the supporting officer.
    // This also relies on Users being seeded *before* EmailApplications.
    $supportingOfficerUser = User::inRandomOrder()->first();
    $supportingOfficerId = $supportingOfficerUser?->id ?? null;

    // Generate a base email slug from potential user name fields for proposed email
    // Adjust based on your User model's actual name fields if needed
    // Use fallback faker data if no user found
    $nameSlug = Str::slug(($applicantUser?->first_name ?? $this->faker->firstName) . '.' . ($applicantUser?->last_name ?? $this->faker->lastName), '-');


    return [
      // Link to the applicant user.
      // Option 1: Link to an existing user (preferred if seeding many applications for existing users)
      'user_id' => $applicantUserId, // Assign a random existing user ID or null

      // Option 2: Create a user for this application by default (if every application MUST have a new user)
      // 'user_id' => User::factory(), // Uncomment this line and comment Option 1 if needed

      'service_status' => $this->faker->randomElement(['Kakitangan Tetap', 'Lantikan Kontrak', 'Personel MySTEP', 'Pelajar Latihan Industri', 'E-mel Sandaran MOTAC']), // Matches migration enum

      'purpose' => $this->faker->sentence(), // Purpose of the application
      // Use unique() for proposed email if needed, though duplicates here might be less critical
      'proposed_email' => $nameSlug . ($applicantUser?->id ?? $this->faker->randomNumber(3)) . '@motac.gov.my', // Example proposed email format


      // Group email details (optional fields)
      'group_email' => $this->faker->optional()->word() . '_group@motac.gov.my', // Using _group for clarity
      'group_admin_name' => $this->faker->optional()->name(),
      'group_admin_email' => $this->faker->optional()->safeEmail(),

      // Link to the Supporting Officer.
      // Option 1: Link to an existing user
      'supporting_officer_id' => $supportingOfficerId, // Assign a random existing user ID or null

      // Option 2: Create a supporting officer user by default
      // 'supporting_officer_id' => User::factory(), // Uncomment this line and comment Option 1 if needed

      'status' => 'draft', // Default status as per migration

      'certification_accepted' => $this->faker->boolean(90), // 90% chance is true for fake data
      'certification_timestamp' => $this->faker->optional(0.9)->dateTimeBetween('-1 year', 'now'), // 90% chance of having a timestamp if accepted

      'rejection_reason' => null, // Rejection reason, null by default
      'final_assigned_email' => null, // The actual email assigned after provisioning, null by default
      'final_assigned_user_id' => null, // The actual user ID assigned by external system, null by default
      'provisioned_at' => null, // Timestamp when provisioning was completed, null by default

      // ADDED: Audit fields for CreatedUpdatedDeletedBy trait
      'created_by' => $auditUserId,
      'updated_by' => $auditUserId,
      'deleted_by' => null, // Default to null for soft deletes
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
    return $this->status('pending_support')
      ->state(fn(array $attributes) => [
        // Ensure supporting_officer_id is set if not already assigned
        // Removed use ($supportingOfficerId) here as fn captures outer scope variables
        'supporting_officer_id' => $attributes['supporting_officer_id'] ?? User::inRandomOrder()->first()?->id,
        // Assuming certification is accepted before reaching this stage
        'certification_accepted' => true,
        'certification_timestamp' => $attributes['certification_timestamp'] ?? now(),
      ]);
  }

  /**
   * Indicate that the application is in the 'pending_admin' status.
   * Helper state for a common workflow step.
   *
   * @return static
   */
  public function pendingAdmin(): static
  {
    return $this->status('pending_admin')
      ->state(fn(array $attributes) => [
        // supporting_officer_id should ideally be set from previous stage (pending_support), don't override here unless specifically needed.
        // Assuming certification accepted
        'certification_accepted' => true,
        'certification_timestamp' => $attributes['certification_timestamp'] ?? now(),
      ]);
  }

  /**
   * Indicate that the application has been approved (e.g., by support).
   * You might need separate states or logic to handle multiple approval steps.
   * This assumes a status like 'approved' exists before 'processing'/'completed'.
   *
   * @return static
   */
  public function approved(): static
  {
    return $this->status('approved') // Assuming 'approved' is a status in your enum
      ->state(fn(array $attributes) => [
        // supporting_officer_id should ideally be set and approval recorded elsewhere.
        // Assuming certification accepted
        'certification_accepted' => true,
        'certification_timestamp' => $attributes['certification_timestamp'] ?? now(),
      ]);
  }

  /**
   * Indicate that the application is being processed (e.g., by IT Admin).
   *
   * @return static
   */
  public function processing(): static
  {
    return $this->status('processing') // Assuming 'processing' is a status in your enum
      ->state(fn(array $attributes) => [
        // Assuming previous steps (certification, support approval) are done
        'certification_accepted' => true,
        'certification_timestamp' => $attributes['certification_timestamp'] ?? now(),
      ]);
  }


  /**
   * Indicate that the application has been approved and processed (status 'completed').
   * This state sets the status to 'completed' and populates the final assigned email/ID and timestamp.
   *
   * @return static
   */
  public function completed(): static
  {
    return $this->status('completed')
      ->state(function (array $attributes) {
        // Attempt to get the user associated with this application.
        // This works correctly if user_id is an actual ID or a factory state that has been created.
        $user = null;
        if (isset($attributes['user_id'])) {
          // If user_id is a factory, create it; otherwise, find the user by ID.
          $user = $attributes['user_id'] instanceof Factory ? $attributes['user_id']->create() : User::find($attributes['user_id']);
        }

        // Generate name slug based on the actual user if found, or use faker defaults
        $nameSlug = Str::slug(($user?->first_name ?? $this->faker->firstName) . '.' . ($user?->last_name ?? $this->faker->lastName), '-'); // Adjust based on your User model name fields

        return [
          // Populate final assigned fields with fake data or use provided values
          // CORRECTED: Use faker->unique() for final_assigned_email to ensure uniqueness
          'final_assigned_email' => $attributes['final_assigned_email'] ?? $this->faker->unique()->safeEmail(),
          // CORRECTED: Use faker->unique() for final_assigned_user_id to ensure uniqueness
          'final_assigned_user_id' => $attributes['final_assigned_user_id'] ?? $this->faker->unique()->bothify('motac_uid_######'), // Example unique external User ID format

          'provisioned_at' => $attributes['provisioned_at'] ?? now(), // Set provisioned_at timestamp to now or use override
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
    return $this->status('rejected')
      ->state(fn(array $attributes) => [
        'rejection_reason' => $attributes['rejection_reason'] ?? $this->faker->sentence(), // Set a rejection reason or use override
        'final_assigned_email' => null, // Clear final assigned details
        'final_assigned_user_id' => null,
        'provisioned_at' => null,
      ]);
  }

  /**
   * Indicate that the application provision failed.
   *
   * @return static
   */
  public function provisionFailed(): static
  {
    return $this->status('provision_failed') // Assuming 'provision_failed' is a status in your enum
      ->state(fn(array $attributes) => [
        // Set relevant failure details if needed
        'rejection_reason' => $attributes['rejection_reason'] ?? 'Provisioning failed.', // Set a reason for failure
        'final_assigned_email' => null,
        'final_assigned_user_id' => null,
        'provisioned_at' => null,
      ]);
  }


  /**
   * Indicate that the application is deleted (soft deleted).
   * Requires SoftDeletes trait on the EmailApplication model.
   */
  public function deleted(): static
  {
    return $this->state(fn(array $attributes) => [
      'deleted_at' => now(),
      'deleted_by' => User::first()?->id ?? null, // Assign a deleter if possible
      // You might also set the status to a specific value for deleted items if needed
    ]);
  }

  /**
   * Indicate that the applicant has accepted certification.
   * This state sets certification_accepted to true and adds a timestamp.
   *
   * @return static
   */
  public function certified(): static
  {
    return $this->state(fn(array $attributes) => [
      'certification_accepted' => true,
      'certification_timestamp' => $attributes['certification_timestamp'] ?? now(),
    ]);
  }

  /**
   * Indicate that the application has group email details.
   *
   * @return static
   */
  public function withGroupEmailDetails(): static
  {
    return $this->state(fn(array $attributes) => [
      'group_email' => $this->faker->word() . '_group@motac.gov.my',
      'group_admin_name' => $this->faker->name(),
      'group_admin_email' => $this->faker->safeEmail(),
    ]);
  }
}
