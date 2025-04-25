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
    // If no users exist yet (e.g., first user being seeded), set to null
    if (!$randomUserId) {
      // Create a temporary basic user if none exist to avoid null created_by/updated_by
      $tempUser = User::firstOrCreate(
        ['email' => 'temp_seeder_user@example.com'],
        [
          'name' => 'Temporary Seeder User',
          'password' => Hash::make(Str::random(10)),
          'nric' => '000000000000',
          'phone_number' => '0000000000',
          'personal_email' => 'temp.personal@example.com',
          'is_admin' => false,
          'is_bpm_staff' => false,
          'department_id' => $departmentId, // Link to available department
          'position_id' => $positionId, // Link to available position
          'grade_id' => $gradeId, // Link to available grade
          'email_verified_at' => now(),
        ]
      );
      $randomUserId = $tempUser->id;
    }


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

      // Add definitions for your custom fields
      'nric' => $this->faker->numerify('##########'), // Generates 10 digits
      'phone_number' => $this->faker->phoneNumber(), // Generates a fake phone number
      'personal_email' => $this->faker->unique()->safeEmail(), // Generates a unique personal email
      'is_admin' => $this->faker->boolean(10), // 10% chance of being admin
      'is_bpm_staff' => $this->faker->boolean(20), // 20% chance of being BPM staff
      'department_id' => $departmentId, // Assign a random department ID or null
      'position_id' => $positionId, // Assign a random position ID or null
      'grade_id' => $gradeId, // Assign a random grade ID or null

      // Fields for CreatedUpdatedDeletedBy trait
      // Assign a random existing user ID for created_by and updated_by
      'created_by' => $randomUserId,
      'updated_by' => $randomUserId,
      'deleted_by' => null, // Default to null for soft deletes
    ];
  }

  /**
   * Indicate that the model's email address should be unverified.
   */
  public function unverified(): static
  {
    return $this->state(function (array $attributes) {
      return [
        'email_verified_at' => null,
      ];
    });
  }

  /**
   * Indicate that the user should have a personal team.
   * Keep this method if you are using Jetstream's team features.
   */
  public function withPersonalTeam(callable $callback = null): static
  {
    if (! Features::hasTeamFeatures()) {
      return $this->state([]);
    }

    return $this->has(
      Team::factory()
        ->state(fn(array $attributes, User $user) => [
          'name' => $user->name . '\'s Team',
          'user_id' => $user->id,
          'personal_team' => true,
        ])
        ->when(is_callable($callback), $callback),
      'ownedTeams'
    );
  }

  /**
   * Indicate that the model is deleted.
   * Add this method if you need to create soft-deleted users for testing.
   */
  public function deleted(): static
  {
    return $this->state(fn(array $attributes) => [
      'deleted_at' => now(),
      // Assign a random user ID for deleted_by if users exist
      'deleted_by' => User::inRandomOrder()->first()?->id,
    ]);
  }

  /**
   * Indicate that the user is an admin.
   */
  public function admin(): static
  {
    return $this->state(fn(array $attributes) => [
      'is_admin' => true,
    ]);
  }

  /**
   * Indicate that the user is BPM staff.
   */
  public function bpmStaff(): static
  {
    return $this->state(fn(array $attributes) => [
      'is_bpm_staff' => true,
    ]);
  }
}
