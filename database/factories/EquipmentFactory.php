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
      // <--- Change these values to lowercase to match the ENUM in the migration
      'asset_type' => $this->faker->randomElement(['laptop', 'projector', 'printer', 'monitor', 'keyboard', 'mouse', 'webcam', 'other']), // Removed 'Camera' as it wasn't in your migration's ENUM list
      'brand' => $this->faker->company(), // Fake company name for brand
      'model' => $this->faker->word() . ' ' . $this->faker->randomNumber(3), // Fake model name
      'serial_number' => $serialNumber, // Unique serial number
      'tag_id' => $tagId, // Unique tag ID
      'purchase_date' => $purchaseDate, // Purchase date
      'warranty_expiry_date' => $warrantyExpiryDate, // Warranty expiry date
      'status' => 'available', // Default status is 'available' as per migration
      'current_location' => $this->faker->city() . ', ' . $this->faker->streetAddress(), // Fake location
      'notes' => $this->faker->optional()->sentence(), // Optional notes

      // If you added audit columns to the equipment table, you might need to define them here as well.
      // e.g., 'created_by' => \App\Models\User::first()?->id, // Assuming users are seeded first
      //       'updated_by' => \App\Models\User::first()?->id,
    ];
  }

  // Keep your state methods ('available', 'onLoan', etc.) if you use them.
  // Ensure the 'status' values in these states also match the ENUM exactly.

  // Example:
  // public function onLoan(): static
  // {
  //    return $this->state(fn(array $attributes) => [
  //      'status' => 'on_loan', // <-- Ensure 'on_loan' matches ENUM value exactly
  //      // ...
  //    ]);
  // }

}
