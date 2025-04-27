<?php

namespace Database\Seeders;

use Faker\Factory as FakerFactory; // Import Faker Factory
use App\Models\Center;      // Import Center model for linking
use App\Models\Department;  // Import Department model for linking
use App\Models\Employee;    // Import Employee model for linking
use App\Models\Position;     // Import Position model for linking (Standardized name)
use App\Models\Timeline;    // Import Timeline model
use App\Models\User;       // Import User model for audit columns
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents; // Optional trait
use Illuminate\Support\Arr; // Import Arr facade for safe random selection
use Illuminate\Support\Facades\Log; // Import Log for debugging

class TimelineSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    \Log::info('Seeding timelines...'); // Log start

    // Instantiate Faker manually for use in the seeder
    $faker = FakerFactory::create();

    // Get ALL available IDs from the required tables for linking
    // This relies on seeders for Employees, Centers, Departments, Positions, and Users
    // running *before* this seeder.
    $employeeIds = Employee::pluck('id')->toArray();
    $centerIds = Center::pluck('id')->toArray();
    $departmentIds = Department::pluck('id')->toArray();
    $positionIds = Position::pluck('id')->toArray();

    // Get the ID of the first user (often the admin) for audit columns.
    $adminUserForAudit = User::first();
    $auditUserId = $adminUserForAudit?->id ?? null; // Use null if no user exists yet


    // Check if we have enough data in required tables to create timelines
    // You need at least one of each dependency and at least one user for audit.
    if (empty($employeeIds) || empty($centerIds) || empty($departmentIds) || empty($positionIds) || $auditUserId === null) {
      Log::warning("TimelineSeeder skipped: Not enough data in required tables (Employees, Centers, Departments, Positions, Users for audit).");
      // Optionally, you could call the relevant seeders here if they were missed,
      // but it's better to fix the DatabaseSeeder call order.
      return; // Exit seeder if dependencies are not met
    }

    // Decide how many timelines to create.
    // You need to create at most the minimum number of available items across all collections,
    // unless you intend to reuse IDs (which is fine for timelines as employees can have multiple).
    $maxTimelinesToCreate = min(count($employeeIds), count($centerIds), count($departmentIds), count($positionIds)) * 2; // Example: Create up to twice the minimum count, but set a reasonable cap.
    $maxTimelinesToCreate = min($maxTimelinesToCreate, 50); // Cap at 50 total timelines for this seeder run

    Log::info("TimelineSeeder: Attempting to create up to {$maxTimelinesToCreate} timelines.");

    // Optional: Clear existing data if you want a clean slate for timelines
    // Timeline::truncate(); // Use with caution if other tables link to timelines


    for ($i = 0; $i < $maxTimelinesToCreate; $i++) {
      // Pick a SINGLE random ID from each array for this timeline record
      // Using Arr::random is safe even if there's only one element
      $randomEmployeeId = Arr::random($employeeIds);
      $randomCenterId = Arr::random($centerIds);
      $randomDepartmentId = Arr::random($departmentIds);
      $randomPositionId = Arr::random($positionIds);

      // Generate start date and optional end date
      $startDate = $faker->dateTimeBetween('-5 years', 'now')->format('Y-m-d');

      // 80% chance of having an end date, ensuring end_date is after start_date if present
      // Get the DateTime object (which might be null) first
      $endDateObject = $faker->optional(0.8)->dateTimeBetween($startDate, 'now');

      // Check if the DateTime object is not null before formatting it
      $endDate = $endDateObject ? $endDateObject->format('Y-m-d') : null;


      // Create the Timeline record
      // You could also use Timeline::factory()->create([...]) if you had a factory
      Timeline::create([
        'employee_id' => $randomEmployeeId, // Link to random Employee
        'center_id' => $randomCenterId,      // Link to random Center
        'department_id' => $randomDepartmentId, // Link to random Department
        'position_id' => $randomPositionId, // Link to random Position

        'start_date' => $startDate,
        'end_date' => $endDate, // Nullable end date

        // ADDED: Assign audit columns
        'created_by' => $auditUserId,
        'updated_by' => $auditUserId,
        'deleted_by' => null, // Default to null for soft deletes

        // created_at, updated_at, deleted_at are handled by timestamps() and softDeletes()
      ]);
    }

    Log::info("TimelineSeeder: Created " . Timeline::count() . " timelines in total."); // Log total count after seeding
  }
}
