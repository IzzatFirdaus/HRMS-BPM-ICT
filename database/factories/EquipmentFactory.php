<?php

namespace Database\Factories;

use App\Models\Equipment; // Import the Equipment model
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

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
    // Get the ID of the first user (often the admin) to use for audit columns.
    // This relies on at least one user being seeded *before* seeders using this factory.
    // Use DB facade to bypass Eloquent model and SoftDeletes trait for this query.
    // This runs immediately after migrations, where Eloquent might not yet
    // fully recognize the deleted_at column despite it existing.
    $adminUserForAudit = DB::table('users')->first();
    $auditUserId = $adminUserForAudit?->id ?? null; // Use null if no user exists yet

    // Generate a unique serial number and tag ID
    // Using UUID for serial_number for high uniqueness probability
    $serialNumber = $this->faker->unique()->uuid();
    // Using EAN13 or similar for tag_id, ensure it fits your expected format
    $tagId = $this->faker->unique()->ean13(); // Example: EAN13 is a 13-digit number


    // Set acquisition and purchase dates
    $acquisitionDate = $this->faker->dateTimeBetween('-10 years', '-1 year'); // Asset acquired sometime in the past
    $purchaseDate = (clone $acquisitionDate)->modify('+1 week'); // Purchase date slightly after acquisition


    // Set warranty expiry date relative to purchase date
    $warrantyExpiryDate = $this->faker->dateTimeBetween($purchaseDate, (clone $purchaseDate)->modify('+5 years')); // Warranty expires within 5 years of purchase


    return [
      // 👇 Merged and updated fields based on standardized 'equipment' schema 👇

      // Fields originally in 'assets'
      'old_id' => $this->faker->optional(0.5)->uuid(), // Optional previous ID
      'class' => $this->faker->randomElement(['Electronic', 'Furniture', 'Gear', 'Other']), // Matches migration enum
      'condition_status' => $this->faker->randomElement(['Good', 'Fine', 'Bad', 'Damaged']), // Matches migration enum (Physical condition)
      'description' => $this->faker->sentence(), // Description of the asset
      'in_service' => $this->faker->boolean(90), // 90% chance of being in service
      'is_gpr' => $this->faker->boolean(70), // 70% chance of being GPR
      'real_price' => $this->faker->numberBetween(100, 50000), // Realistic price range
      'expected_price' => $this->faker->numberBetween(50, 40000), // Realistic price range
      'acquisition_date' => $acquisitionDate->format('Y-m-d'), // Formatted date
      'acquisition_type' => $this->faker->randomElement(['Directed', 'Founded', 'Transferred', 'Purchased']), // Matches migration enum
      'funded_by' => $this->faker->optional(0.7)->word(), // Optional funding source
      // ** Corrected: Changed 'note' to 'notes' **
      'notes' => $this->faker->optional()->text(200), // Optional notes (using text)

      // Fields originally in 'equipment'
      'asset_type' => $this->faker->randomElement(['laptop', 'projector', 'printer', 'monitor', 'keyboard', 'mouse', 'webcam', 'other']), // Matches migration enum (Specific type)
      'brand' => $this->faker->company(), // Fake company name for brand
      'model' => $this->faker->word() . ' ' . $this->faker->randomNumber(3), // Fake model name
      'serial_number' => $serialNumber, // Unique serial number
      'tag_id' => $tagId, // Unique tag ID
      'purchase_date' => $purchaseDate->format('Y-m-d'), // Purchase date
      'warranty_expiry_date' => $warrantyExpiryDate->format('Y-m-d'), // Warranty expiry date
      'availability_status' => $this->faker->randomElement(['available', 'on_loan', 'under_maintenance', 'disposed', 'lost', 'damaged']), // Matches migration enum (Availability status)
      'current_location' => $this->faker->city() . ', ' . $this->faker->streetAddress(), // Fake location


      // Audit fields for CreatedUpdatedDeletedBy trait
      // Assign the ID of the first user (Admin) or null if none exists
      'created_by' => $auditUserId,
      'updated_by' => $auditUserId,
      'deleted_by' => null, // Default to null for soft deletes
      // ☝️ END Merged and updated fields ☝️

    ];
  }

  // Add your state methods here (e.g., for specific statuses)
  // Ensure the status values in these states match the ENUMs exactly ('condition_status', 'availability_status').

  /**
   * Indicate the equipment is available.
   */
  public function available(): static
  {
    return $this->state(fn(array $attributes) => [
      'availability_status' => 'available',
    ]);
  }

  /**
   * Indicate the equipment is on loan.
   */
  public function onLoan(): static
  {
    return $this->state(fn(array $attributes) => [
      'availability_status' => 'on_loan',
    ]);
  }

  /**
   * Indicate the equipment is damaged.
   */
  public function damaged(): static
  {
    return $this->state(fn(array $attributes) => [
      'condition_status' => 'Damaged', // Use condition status enum
      'availability_status' => 'damaged', // Use availability status enum
    ]);
  }

  /**
   * Indicate the equipment is disposed.
   */
  public function disposed(): static
  {
    return $this->state(fn(array $attributes) => [
      'availability_status' => 'disposed', // Use availability status enum
      'in_service' => false, // Mark as not in service
    ]);
  }

  /**
   * Indicate the equipment is deleted (soft deleted).
   * Requires SoftDeletes trait on the Equipment model.
   */
  public function deleted(): static
  {
    return $this->state(fn(array $attributes) => [
      'deleted_at' => now(),
      // Get the ID of the first user for deleted_by.
      // Use DB facade to bypass Eloquent model and SoftDeletes trait for this query.
      'deleted_by' => DB::table('users')->first()?->id ?? null,
      'availability_status' => 'disposed', // Or another appropriate status for deleted items
      'in_service' => false,
    ]);
  }

  /**
   * Indicate the equipment is under maintenance.
   * This is a generic state method using the 'status' parameter.
   */
  public function status(string $status): static
  {
    return $this->state(fn(array $attributes) => [
      'availability_status' => $status,
    ]);
  }
}
