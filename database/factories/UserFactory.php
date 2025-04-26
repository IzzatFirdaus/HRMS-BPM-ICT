<?php

namespace Database\Factories;

use App\Models\User; // Import the User model
use App\Models\Team; // Keep if using Jetstream teams
use App\Models\Department; // Import Department model for linking
use App\Models\Position; // Import Position model for linking
use App\Models\Grade; // Import Grade model for linking
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash; // Import Hash facade for password
use Laravel\Jetstream\Features; // Keep if using Jetstream features

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
    // Ensure related models exist before attempting to link
    // This prevents errors if seeders are run out of order
    $departmentId = Department::inRandomOrder()->first()?->id;
    $positionId = Position::inRandomOrder()->first()?->id;
    $gradeId = Grade::inRandomOrder()->first()?->id;

    // Get a random user ID for created_by/updated_by, ensuring at least one user exists
    // This might require a basic user to be created before seeding other users
    $randomUserId = User::inRandomOrder()->first()?->id;
    // If no users exist yet (e.g., first user being seeded by this factory), set to null initially
    $createdUpdatedById = $randomUserId ?? null;


    // If no users exist yet (e.g., the *very first* user being seeded by the factory)
    // the CreatedUpdatedDeletedBy trait might try to set these to the current user (null)
    // or a default string like 'System', causing issues.
    // The temporary user logic is an alternative, but it might create duplicates if not careful.
    // Let's simplify the audit column assignment in the factory to null or first user if available.
    // The temporary user logic adds complexity and might not be needed if the trait handles null correctly.
    // We'll remove the temporary user creation block to simplify and rely on the trait/DatabaseSeeder order.
    // The AdminUserSeeder runs *before* this factory, so at least one user (the admin) *should* exist.
    $adminUserForAudit = \App\Models\User::first();
    $auditUserId = $adminUserForAudit?->id ?? null;


    return [
      'name' => $this->faker->name(),
      'email' => $this->faker->unique()->safeEmail(),
      'email_verified_at' => now(),
      'password' => Hash::make('password'), // Use Hash facade for password
      'two_factor_secret' => null, // Keep if using Jetstream 2FA
      'two_factor_recovery_codes' => null, // Keep if using Jetstream 2FA
      'remember_token' => Str::random(10),
      'profile_photo_path' => null, // Keep if using Jetstream profile photos
      'current_team_id' => null, // Keep if using Jetstream teams
      'employee_id' => null, // Factory might not link to employees automatically, set to null or link specifically


      // Add definitions for your custom fields - ENSURE THESE MATCH MIGRATION COLUMNS
      'full_name' => $this->faker->name(), // Matches migration
      'nric' => $this->faker->unique()->numerify('##########'), // Matches migration
      'mobile_number' => $this->faker->phoneNumber(), // Matches migration (Changed from phone_number)
      'personal_email' => $this->faker->unique()->safeEmail(), // Matches migration
      'service_status' => $this->faker->randomElement(['permanent', 'contract', 'mystep', 'intern', 'other_agency']), // Matches migration enum
      'appointment_type' => $this->faker->word(), // Matches migration
      'is_admin' => $this->faker->boolean(10), // Matches migration
      'is_bpm_staff' => $this->faker->boolean(20), // Matches migration
      'user_id_assigned' => $this->faker->unique()->randomNumber(5), // Matches migration (unique string/int?)
      'status' => $this->faker->randomElement(['active', 'inactive', 'suspended']), // Matches migration enum

      // Link to related models (using IDs) - Ensure these exist from earlier seeders
      'department_id' => $departmentId, // Assign a random department ID or null
      'position_id' => $positionId, // Assign a random position ID or null
      'grade_id' => $gradeId, // Assign a random grade ID or null


      // Audit fields for CreatedUpdatedDeletedBy trait
      // Assign the ID of the first user (Admin) or null if none exists
      'created_by' => $auditUserId,
      'updated_by' => $auditUserId,
      'deleted_by' => null, // Default to null for soft deletes
    ];
  }

  // Keep your state methods ('unverified', 'withPersonalTeam', 'deleted', 'admin', 'bpmStaff')
  // Ensure status/role values in states match ENUMs/booleans exactly.

  // Remove the temporary user creation block
  /*
    // If no users exist yet (e.g., first user being seeded), set to null
    if (!$randomUserId) {
      // Create a temporary basic user if none exist to avoid null created_by/updated_by
      $tempUser = User::firstOrCreate(
        ['email' => 'temp_seeder_user@example.com'],
        [
          'name' => 'Temporary Seeder User',
          'password' => Hash::make(Str::random(10)),
          'nric' => '000000000000',
          'mobile_number' => '0000000000', // Corrected column name
          'personal_email' => 'temp.personal@example.com',
          'is_admin' => false,
          'is_bpm_staff' => false,
          'department_id' => $departmentId, // Link to available department
          'position_id' => $positionId, // Link to available position
          'grade_id' => $gradeId, // Link to available grade
          'email_verified_at' => now(),
        ]
      );
      $randomUserId = $tempUser->id; // Use this ID for the current factory item
    }
    */
}
