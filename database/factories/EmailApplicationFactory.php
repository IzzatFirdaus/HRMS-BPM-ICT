<?php

namespace Database\Factories;

use App\Models\EmailApplication;
use App\Models\User; // Assuming User model exists
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\EmailApplication>
 */
class EmailApplicationFactory extends Factory
{
  protected $model = EmailApplication::class;

  /**
   * Define the model's default state.
   *
   * @return array<string, mixed>
   */
  public function definition(): array
  {
    $user = User::factory()->create(); // Creates a user for this application

    $purpose = $this->faker->sentence;
    $nameSlug = Str::slug($user->full_name); // Basic slug from user name

    return [
      'user_id' => $user->id,
      'purpose' => $purpose,
      'proposed_email' => $nameSlug . '@motac.gov.my', // Example proposed email
      'status' => $this->faker->randomElement(['draft', 'pending_support', 'pending_admin', 'approved', 'rejected', 'processing', 'completed']),
      'certification_accepted' => true,
      'certification_timestamp' => now(),
      'rejection_reason' => $this->faker->randomElement([null, $this->faker->sentence]),
      'final_assigned_email' => null, // Will be assigned later in processing
      'final_assigned_user_id' => null, // Will be assigned later in processing
    ];
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
   * Indicate that the application has been approved and processed.
   *
   * @return static
   */
  public function completed(): static
  {
    return $this->state(function (array $attributes) {
      $user = User::find($attributes['user_id']);
      $nameSlug = Str::slug($user->full_name);
      return [
        'status' => 'completed',
        'final_assigned_email' => $nameSlug . '@motac.gov.my',
        'final_assigned_user_id' => 'user' . $user->id, // Example User ID
      ];
    });
  }

  /**
   * Indicate that the application has been rejected.
   *
   * @return static
   */
  public function rejected(): static
  {
    return $this->state(fn(array $attributes) => [
      'status' => 'rejected',
      'rejection_reason' => $this->faker->sentence,
    ]);
  }
}
