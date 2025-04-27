<?php

namespace Database\Factories;

use App\Models\LoanTransaction;
use App\Models\LoanApplication;
use App\Models\Equipment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Faker\Generator as Faker; // Import Faker Generator
use Illuminate\Support\Carbon; // Import Carbon
use Illuminate\Support\Facades\DB; // Import DB facade for user query

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\LoanTransaction>
 */
class LoanTransactionFactory extends Factory
{
  /**
   * The name of the factory's corresponding model.
   *
   * @var string
   */
  protected $model = LoanTransaction::class;

  /**
   * Define the model's default state.
   * This defines the basic attributes for a default loan transaction (perhaps an "issued" one).
   *
   * @return array<string, mixed>
   */
  public function definition(): array
  {
    // Fetch random existing IDs for foreign keys.
    // These seeders (LoanApplication, Equipment, User) must run before this seeder.
    // Use find() with a check or inRandomOrder()->first() to get existing records.

    // Fetch the ID of the first user (admin) for audit columns
    // Use DB::table() here to avoid potential model issues during early seeding
    $adminUserForAudit = DB::table('users')->first();
    $auditUserId = $adminUserForAudit?->id ?? null;

    $loanApplication = LoanApplication::inRandomOrder()->first();
    $equipment = Equipment::inRandomOrder()->first();
    // Find potential officers - adjust filtering based on your User model roles/fields
    $issuingOfficer = User::where('is_bpm_staff', true)->inRandomOrder()->first(); // Assume BPM staff issue equipment
    $receivingOfficer = User::inRandomOrder()->first(); // The person receiving the equipment

    // Set a default issue timestamp
    $issueTimestamp = $this->faker->dateTimeBetween('-6 months', 'now');

    // Example checklist structure - just a PHP array
    $issueChecklist = [
      'cable' => $this->faker->randomElement(['ok', 'missing']),
      'adapter' => $this->faker->randomElement(['ok', 'missing', 'damaged']),
      'case' => $this->faker->randomElement(['ok', 'damaged']),
      'power_bank' => $this->faker->randomElement(['ok', 'missing', 'n/a']), // Example additional item
    ];


    return [
      // Link to LoanApplication and Equipment (nullable based on your migration)
      'loan_application_id' => $loanApplication?->id, // Assign a random existing ID or null
      'equipment_id' => $equipment?->id, // Assign a random existing ID or null (Nullable)

      // Officer Foreign Keys (nullable based on your migration)
      'issuing_officer_id' => $issuingOfficer?->id, // Assign a random BPM staff ID or null
      'receiving_officer_id' => $receivingOfficer?->id, // Assign a random user ID or null

      // JSON Checklists (nullable based on your migration)
      // Corrected: Assign a PHP array or null directly instead of using $this->faker->json()
      'accessories_checklist_on_issue' => $this->faker->boolean(80) ? $issueChecklist : null, // 80% chance of having a checklist

      // Timestamps (nullable based on your migration)
      'issue_timestamp' => $issueTimestamp,
      'return_timestamp' => null, // Null by default

      'returning_officer_id' => null,
      'return_accepting_officer_id' => null,
      'accessories_checklist_on_return' => null, // Null by default
      'return_notes' => null, // Nullable text field

      // Default status (can be overridden by states)
      'status' => 'issued', // Default status for a new transaction factory instance

      // Audit columns (nullable based on your migration)
      // These need to be explicitly set in the factory if not auto-filled by a trait during creation
      'created_by' => $auditUserId,
      'updated_by' => $auditUserId,
      'deleted_by' => null,

      // Soft delete timestamp (nullable based on your migration)
      'deleted_at' => null,
    ];
  }

  /**
   * Configure the model factory.
   * This method runs after a model has been created and allows creating related models or setting attributes.
   *
   * @return $this
   */
  public function configure()
  {
    return $this; // No default afterCreating callbacks needed here
  }

  /**
   * Indicate that the transaction status is 'issued'.
   * This is the default state.
   *
   * @return static
   */
  public function issued(): static
  {
    return $this->state(fn(array $attributes) => [
      'status' => 'issued',
      // Ensure required 'issued' fields are set if not overridden
      'issue_timestamp' => $attributes['issue_timestamp'] ?? now(),
      'issuing_officer_id' => $attributes['issuing_officer_id'] ?? User::where('is_bpm_staff', true)->inRandomOrder()->first()?->id, // Assign default issuing officer if not set
      'receiving_officer_id' => $attributes['receiving_officer_id'] ?? User::inRandomOrder()->first()?->id, // Assign default receiving officer if not set
      // Ensure accessories_checklist_on_issue is populated if status is issued
      'accessories_checklist_on_issue' => $attributes['accessories_checklist_on_issue'] ?? ($this->faker->boolean(80) ? ['cable' => 'ok', 'adapter' => 'ok', 'case' => 'ok'] : null),
    ]);
  }

  /**
   * Indicate that the transaction status is 'returned'.
   * Populates return-related fields.
   *
   * @return static
   */
  public function returned(): static
  {
    return $this->state(function (array $attributes) {
      // Ensure issue details are present before setting return details
      $issueTimestamp = $attributes['issue_timestamp'] ?? $this->faker->dateTimeBetween('-1 year', 'now');

      // Get officers if not overridden
      $returningOfficer = $attributes['returning_officer_id'] ?? User::inRandomOrder()->first()?->id; // Assign default returning officer if not set
      $returnAcceptingOfficer = $attributes['return_accepting_officer_id'] ?? User::where('is_bpm_staff', true)->inRandomOrder()->first()?->id; // Assign default return accepting officer if not set

      // Example return checklist structure - just a PHP array
      $returnChecklist = [
        'cable' => $this->faker->randomElement(['ok', 'missing', 'damaged']),
        'adapter' => $this->faker->randomElement(['ok', 'missing', 'damaged']),
        'case' => $this->faker->randomElement(['ok', 'damaged']),
        'power_bank' => $this->faker->randomElement(['ok', 'missing', 'n/a']),
        'notes' => $this->faker->optional()->sentence(), // Optional notes within checklist
      ];

      return [
        'status' => 'returned',
        'issue_timestamp' => $issueTimestamp, // Ensure issue timestamp is set
        'return_timestamp' => $attributes['return_timestamp'] ?? $this->faker->dateTimeBetween($issueTimestamp, 'now'), // Set return timestamp after issue
        'returning_officer_id' => $returningOfficer,
        'return_accepting_officer_id' => $returnAcceptingOfficer,
        // Corrected: Assign a PHP array or null directly
        'accessories_checklist_on_issue' => $attributes['accessories_checklist_on_issue'] ?? ($this->faker->boolean(80) ? ['cable' => 'ok', 'adapter' => 'ok'] : null), // Ensure issue checklist is also present if needed
        'accessories_checklist_on_return' => $attributes['accessories_checklist_on_return'] ?? ($this->faker->boolean(80) ? $returnChecklist : null), // 80% chance of having a return checklist

        'return_notes' => $attributes['return_notes'] ?? $this->faker->optional()->sentence(), // Add optional return notes
      ];
    });
  }

  /**
   * Indicate that the transaction status is 'overdue'.
   * Requires issue details.
   *
   * @return static
   */
  public function overdue(): static
  {
    return $this->state(function (array $attributes) {
      // Ensure issue timestamp is set
      $issueTimestamp = $attributes['issue_timestamp'] ?? $this->faker->dateTimeBetween('-1 year', '-2 months'); // Issued a while ago

      return [
        'status' => 'overdue',
        'issue_timestamp' => $issueTimestamp,
        'return_timestamp' => null, // Overdue implies not returned
        'return_notes' => $attributes['return_notes'] ?? 'Overdue.', // Add overdue note
      ];
    });
  }

  /**
   * Indicate that the transaction status is 'lost'.
   * Requires issue details.
   *
   * @return static
   */
  public function lost(): static
  {
    return $this->state(function (array $attributes) {
      // Ensure issue timestamp is set
      $issueTimestamp = $attributes['issue_timestamp'] ?? $this->faker->dateTimeBetween('-1 year', '-3 months'); // Issued a while ago

      return [
        'status' => 'lost',
        'issue_timestamp' => $issueTimestamp,
        'return_timestamp' => null, // Lost implies not returned
        'return_notes' => $attributes['return_notes'] ?? 'Reported as lost.', // Add lost note
      ];
    });
  }

  /**
   * Indicate that the transaction status is 'damaged'.
   * Can be from an issued or returned transaction.
   *
   * @return static
   */
  public function damaged(): static
  {
    return $this->state(function (array $attributes) {
      // This state might be applied after issued or returned.
      // Ensure the relevant timestamps are set.
      $issueTimestamp = $attributes['issue_timestamp'] ?? $this->faker->dateTimeBetween('-1 year', 'now');
      // Only set return timestamp if it wasn't explicitly provided and the status isn't just 'issued'
      $returnTimestamp = $attributes['return_timestamp'] ?? ($attributes['status'] !== 'issued' ? $this->faker->optional()->dateTimeBetween($issueTimestamp, 'now') : null);

      return [
        'status' => 'damaged',
        'issue_timestamp' => $issueTimestamp,
        'return_timestamp' => $returnTimestamp,
        'return_notes' => $attributes['return_notes'] ?? 'Equipment reported damaged.', // Add notes
      ];
    });
  }


  /**
   * Indicate that the transaction is deleted (soft deleted).
   * Requires SoftDeletes trait on the LoanTransaction model.
   */
  public function deleted(): static
  {
    return $this->state(fn(array $attributes) => [
      'deleted_at' => now(),
      'deleted_by' => User::first()?->id ?? null, // Assign a deleter if possible
    ]);
  }

  // Helper state to link to a specific Loan Application
  public function forLoanApplication(LoanApplication $loanApplication): static
  {
    return $this->state(fn(array $attributes) => [
      'loan_application_id' => $loanApplication->id,
    ]);
  }

  // Helper state to link to specific Equipment
  public function forEquipment(Equipment $equipment): static
  {
    return $this->state(fn(array $attributes) => [
      'equipment_id' => $equipment->id,
    ]);
  }
}
