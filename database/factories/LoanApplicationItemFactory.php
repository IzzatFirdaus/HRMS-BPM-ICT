<?php

namespace Database\Factories;

use App\Models\LoanApplicationItem; // Import the LoanApplicationItem model
use App\Models\LoanApplication;   // Import LoanApplication model for linking
use App\Models\Equipment;         // Import Equipment model for linking (Standardized name)
use App\Models\User;              // Import User model for audit columns
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\LoanApplicationItem>
 */
class LoanApplicationItemFactory extends Factory // Note: Corrected to singular "Item"
{
  /**
   * The name of the factory's corresponding model.
   *
   * @var string
   */
  protected $model = LoanApplicationItem::class;

  /**
   * Define the model's default state.
   * This defines the basic attributes for a single item requested in a loan application.
   *
   * @return array<string, mixed>
   */
  public function definition(): array
  {
    // Fetch random existing IDs for foreign keys.
    // This relies on seeders for LoanApplications, Equipment, and Users
    // running *before* seeders using this factory.
    $loanApplicationId = LoanApplication::inRandomOrder()->first()?->id;
    // We are no longer linking to a specific Equipment ID in the item,
    // but rather a type (string), based on the migration.
    // The Equipment model can still be used to get common types.
    $equipmentType = Equipment::inRandomOrder()->first()?->asset_type ?? $this->faker->word(); // Get a random equipment type from existing equipment or a generic word

    // Fetch the ID of the first user (often the admin) for audit columns.
    // Assuming User model exists and is seeded.
    $adminUserForAudit = User::first();
    $auditUserId = $adminUserForAudit?->id ?? null; // Use null if no user exists yet

    return [
      // Link to the parent loan application.
      // This should ideally be explicitly set when using the factory
      // via ->for(LoanApplication::class). Setting a default is a fallback.
      'loan_application_id' => $loanApplicationId, // Assign a random existing Loan Application ID or null

      // Set the equipment type based on the migration's column name
      'equipment_type' => $equipmentType, // <-- Corrected column name

      // Set the requested quantity based on the migration's column name
      'quantity_requested' => $this->faker->numberBetween(1, 5), // <-- Corrected column name

      'notes' => $this->faker->optional()->sentence(), // Optional notes about the item request

      // quantity_approved is nullable in the migration, leave as null by default
      'quantity_approved' => null,

      // Audit fields for CreatedUpdatedDeletedBy trait (if not automatically filled by model/trait)
      // Based on your previous factory code structure, these are explicitly set here.
      // Assuming your loan_application_items table has these columns (as per the migration you showed later)
      'created_by' => $auditUserId,
      'updated_by' => $auditUserId,
      'deleted_by' => null, // Default to null for soft deletes

      // Soft delete timestamp, null by default (if not automatically filled by model/trait)
      // Assuming your loan_application_items table uses soft deletes (as per the migration you showed later)
      'deleted_at' => null,
    ];
  }

  /**
   * Configure the model factory.
   *
   * @return $this
   */
  public function configure()
  {
    return $this; // No default callbacks needed here
  }

  /**
   * Indicate the requested quantity.
   * Use quantity_requested based on the migration.
   *
   * @param int $quantity The desired quantity.
   * @return static
   */
  public function quantityRequested(int $quantity): static // Renamed state method for clarity
  {
    return $this->state(fn(array $attributes) => [
      'quantity_requested' => $quantity, // Use quantity_requested
    ]);
  }

  /**
   * Indicate the specific equipment type being requested.
   * Use equipment_type based on the migration.
   *
   * @param string $type The desired equipment type (e.g., 'Laptop').
   * @return static
   */
  public function type(string $type): static // Added/Corrected state method for equipment_type
  {
    return $this->state(fn(array $attributes) => [
      'equipment_type' => $type, // Use equipment_type
    ]);
  }


  /**
   * Indicate that the item is deleted (soft deleted).
   * Requires SoftDeletes trait on the LoanApplicationItem model.
   */
  public function deleted(): static
  {
    return $this->state(fn(array $attributes) => [
      'deleted_at' => now(),
      'deleted_by' => User::first()?->id ?? null, // Assign a deleter if possible
    ]);
  }
}
