<?php

namespace Database\Factories;

use App\Models\User; // Import the User model
// Removed: use App\Models\Team; // No longer needed if not using Jetstream teams feature
use App\Models\Department; // Import Department model for linking
use App\Models\Position; // Import Position model for linking (Standardized name)
use App\Models\Grade; // Import Grade model for linking
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash; // Import Hash facade for password
use Illuminate\Support\Facades\DB; // IMPORT DB FACADE FOR USER QUERY
use Illuminate\Support\Facades\Log; // Import Log for logging (optional in factory, but good for debugging)
// Removed: use Laravel\Jetstream\Features; // No longer needed if removing Jetstream feature checks


/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
  /**
   * The name of the factory's corresponding model.
   *
   * @var string
   */
  protected $model = User::class; // Explicitly define the model

  /**
   * Define the model's default state.
   *
   * @return array<string, mixed>
   */
  public function definition(): array
  {
    // Fetch a random existing Department, Position, and Grade ID.
    // This relies on your seeders for Departments, Positions, and Grades
    // running *before* the User seeder that uses this factory.
    // Assuming these models do NOT have SoftDeletes or they don't cause the early query issue.
    $departmentId = Department::inRandomOrder()->first()?->id;
    $positionId = Position::inRandomOrder()->first()?->id; // Use Position as the standardized table name
    $gradeId = Grade::inRandomOrder()->first()?->id;

    // Get the ID of the first user (often the admin) to use for audit columns.
    // This relies on at least one user being seeded *before* others that use this factory.
    // Use DB facade to bypass Eloquent model and SoftDeletes trait for this query.
    // This runs immediately after migrations, where Eloquent might not yet
    // fully recognize the deleted_at column despite it existing.
    $adminUserForAudit = DB::table('users')->first(); // UPDATED: Use DB::table
    $auditUserId = $adminUserForAudit?->id ?? null; // Use null if no user exists yet


    return [
      'name' => $this->faker->userName(), // Often a username or unique identifier
      'email' => $this->faker->unique()->safeEmail(), // Primary login email
      'email_verified_at' => now(),
      'password' => Hash::make('password'), // Default password for seeded users
      'two_factor_secret' => null, // Keep if using Jetstream 2FA
      'two_factor_recovery_codes' => null, // Keep if using Jetstream 2FA
      'remember_token' => Str::random(10),
      // Keep if using Jetstream profile photos (feature IS enabled in your config)
      'profile_photo_path' => null,
      // Removed: 'current_team_id' => null, // Only needed if using the full Jetstream teams feature
      'employee_id' => null, // Typically linked separately if needed, set to null by default in factory

      // Add definitions for your custom fields (ensure column names match migration)
      'full_name' => $this->faker->name(), // Full name of the user
      'nric' => $this->faker->unique()->numerify('############'), // Malaysian NRIC format (12 digits)
      // Note: Your users table has both 'mobile' and 'mobile_number'.
      // This factory populates 'mobile_number'. The 'mobile' column was not in your migration.
      // REMOVED: 'mobile' => null, // Removed this line as the column does not exist in migrations
      'mobile_number' => $this->faker->unique()->phoneNumber(), // Use this column as per migration
      'personal_email' => $this->faker->unique()->safeEmail(), // Personal email
      'service_status' => $this->faker->randomElement(['permanent', 'contract', 'mystep', 'intern', 'other_agency']), // Matches migration enum
      'appointment_type' => $this->faker->word(), // e.g., "Pegawai", "Pembantu Tadbir"
      'is_admin' => $this->faker->boolean(10), // 10% chance of being an admin
      'is_bpm_staff' => $this->faker->boolean(20), // 20% chance of being BPM staff
      'user_id_assigned' => $this->faker->unique()->bothify('??######'), // Example: "AB12345" - adjust format as needed
      'status' => $this->faker->randomElement(['active', 'inactive', 'suspended']), // Matches migration enum
      'motac_email' => $this->faker->unique()->email(), // MOTAC assigned email


      // Link to related models (using IDs) - Ensure these exist from earlier seeders
      'department_id' => $departmentId, // Assign a random department ID or null
      'position_id' => $positionId, // Assign a random position ID or null (Standardized FK name)
      'grade_id' => $gradeId, // Assign a random grade ID or null


      // Audit fields for CreatedUpdatedDeletedBy trait
      // Assign the ID of the first user (Admin) or null if none exists
      'created_by' => $auditUserId,
      'updated_by' => $auditUserId,
      'deleted_by' => null, // Default to null for soft deletes
    ];
  }

  /**
   * Indicate that the user's email address is unverified.
   */
  public function unverified(): static
  {
    return $this->state(fn(array $attributes) => [
      'email_verified_at' => null,
    ]);
  }

  // Removed: withPersonalTeam() method as the feature is not enabled in your Jetstream config.

  /**
   * Indicate that the user is deleted.
   * Requires SoftDeletes trait on the User model.
   */
  public function deleted(): static
  {
    return $this->state(fn(array $attributes) => [
      'deleted_at' => now(),
      // Get the ID of the first user for deleted_by.
      // Use DB facade to bypass Eloquent model and SoftDeletes trait for this query.
      'deleted_by' => DB::table('users')->first()?->id ?? null, // UPDATED: Use DB::table for consistency
      'status' => 'inactive', // Update status consistent with deletion
    ]);
  }

  /**
   * Indicate that the user is an admin.
   */
  public function admin(): static
  {
    return $this->state(fn(array $attributes) => [
      'is_admin' => true,
      // Optionally set default department/position/grade for admin if needed
    ]);
  }

  /**
   * Indicate that the user is BPM staff.
   */
  public function bpmStaff(): static
  {
    return $this->state(fn(array $attributes) => [
      'is_bpm_staff' => true,
      // Optionally set default department/position/grade for BPM staff if needed
    ]);
  }

  /**
   * Indicate the user's status.
   */
  public function status(string $userStatus): static
  {
    return $this->state(fn(array $attributes) => [
      'status' => $userStatus, // Ensure the string matches a valid enum value
    ]);
  }
}
