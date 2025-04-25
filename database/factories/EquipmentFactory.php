<?php

namespace Database\Factories;

use App\Models\Equipment; // Import the Equipment model
use Illuminate\Database\Eloquent\Factories\Factory; // Import the base Factory class
use Illuminate\Support\Str; // Import Str facade for string manipulation
use Illuminate\Support\Carbon; // Import Carbon for date manipulation

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Equipment>
 */
class EquipmentFactory extends Factory
{
  /**
   * The name of the factory's corresponding model.
   *
   * @var string
   */
  protected $model = Equipment::class;

  /**
   * Define the model's default state.
   * This defines the basic attributes for a default equipment asset, usually 'available'.
   *
   * @return array<string, mixed>
   */
  public function definition(): array
  {
    // Generate a unique serial number and tag ID
    $serialNumber = $this->faker->unique()->uuid(); // Using UUID for uniqueness
    $tagId = $this->faker->unique()->ean13(); // Using EAN13 as a unique identifier

    // Set purchase date and warranty expiry date
    $purchaseDate = $this->faker->dateTimeBetween('-5 years', 'now');
    $warrantyExpiryDate = $this->faker->dateTimeBetween($purchaseDate, (clone $purchaseDate)->modify('+3 years'));

    return [
      'asset_type' => $this->faker->randomElement(['Laptop', 'Projector', 'Printer', 'Monitor', 'Keyboard', 'Mouse', 'Webcam', 'Camera', 'Other']), // Randomly select an asset type
      'brand' => $this->faker->company(), // Fake company name for brand
      'model' => $this->faker->word() . ' ' . $this->faker->randomNumber(3), // Fake model name
      'serial_number' => $serialNumber, // Unique serial number
      'tag_id' => $tagId, // Unique tag ID
      'purchase_date' => $purchaseDate, // Purchase date
      'warranty_expiry_date' => $warrantyExpiryDate, // Warranty expiry date
      'status' => 'available', // Default status is 'available' as per migration
      'current_location' => $this->faker->city() . ', ' . $this->faker->streetAddress(), // Fake location
      'notes' => $this->faker->optional()->sentence(), // Optional notes
    ];
  }

  /**
   * Indicate that the equipment is currently available.
   * This is the default state, but explicit state method can be useful.
   *
   * @return static
   */
  public function available(): static
  {
    return $this->state(fn(array $attributes) => [
      'status' => 'available',
      'current_location' => $attributes['current_location'] ?? $this->faker->city() . ', Stor BPM', // Example: default to BPM store if available
    ]);
  }

  /**
   * Indicate that the equipment is currently on loan.
   *
   * @return static
   */
  public function onLoan(): static
  {
    return $this->state(fn(array $attributes) => [
      'status' => 'on_loan',
      'current_location' => $attributes['current_location'] ?? $this->faker->city() . ', ' . $this->faker->streetAddress(), // Location where it's on loan
    ]);
  }

  /**
   * Indicate that the equipment is under maintenance.
   *
   * @return static
   */
  public function underMaintenance(): static
  {
    return $this->state(fn(array $attributes) => [
      'status' => 'under_maintenance',
      'current_location' => $attributes['current_location'] ?? 'Bengkel IT', // Example: default to IT Workshop
      'notes' => ($attributes['notes'] ?? '') . "\n" . $this->faker->sentence(5) . " (Under Maintenance)", // Add maintenance note
    ]);
  }

  /**
   * Indicate that the equipment has been disposed of.
   *
   * @return static
   */
  public function disposed(): static
  {
    return $this->state(fn(array $attributes) => [
      'status' => 'disposed',
      'current_location' => 'Disposed', // Example: set location to Disposed
      'notes' => ($attributes['notes'] ?? '') . "\n" . $this->faker->sentence(5) . " (Disposed)", // Add disposal note
    ]);
  }

  /**
   * Indicate that the equipment is lost.
   *
   * @return static
   */
  public function lost(): static
  {
    return $this->state(fn(array $attributes) => [
      'status' => 'lost',
      'current_location' => 'Unknown', // Example: set location to Unknown
      'notes' => ($attributes['notes'] ?? '') . "\n" . $this->faker->sentence(5) . " (Lost)", // Add lost note
    ]);
  }

  /**
   * Indicate that the equipment is damaged.
   *
   * @return static
   */
  public function damaged(): static
  {
    return $this->state(fn(array $attributes) => [
      'status' => 'damaged',
      'notes' => ($attributes['notes'] ?? '') . "\n" . $this->faker->sentence(5) . " (Damaged)", // Add damaged note
    ]);
  }

  // Add other states or methods as needed for specific scenarios
}
