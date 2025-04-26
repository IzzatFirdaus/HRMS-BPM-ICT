<?php

namespace Database\Seeders;

use Faker\Factory;
use App\Models\Center;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Position;
use App\Models\Timeline;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr; // Import Arr facade for safe random selection
use Illuminate\Support\Facades\Log; // Import Log for debugging

class TimelineSeeder extends Seeder
{
  public function run(): void
  {
    $faker = Factory::create();

    // Get ALL available IDs from the required tables
    $employeeIds = Employee::pluck('id')->toArray();
    $centerIds = Center::pluck('id')->toArray();
    $departmentIds = Department::pluck('id')->toArray();
    $positionIds = Position::pluck('id')->toArray();

    // Check if we have enough data to create timelines
    // You need at least one of each dependency to create a timeline
    if (empty($employeeIds) || empty($centerIds) || empty($departmentIds) || empty($positionIds)) {
      Log::warning("TimelineSeeder skipped: Not enough data in required tables (Employees, Centers, Departments, Positions).");
      // Optionally, you could call the relevant seeders here if they were missed,
      // but it's better to fix the DatabaseSeeder call order.
      // $this->call(...)
      return; // Exit seeder if dependencies are not met
    }

    // Create timelines (e.g., 10 records, or fewer if data pools are very small)
    // You need to create at most the minimum number of available items across all collections
    $maxTimelinesToCreate = min(count($employeeIds), count($centerIds), count($departmentIds), count($positionIds), 10); // Limit to 10 or min available

    Log::info("TimelineSeeder: Creating up to {$maxTimelinesToCreate} timelines.");


    for ($i = 0; $i < $maxTimelinesToCreate; $i++) {
      // Pick a SINGLE random ID from each array for this timeline record
      $randomEmployeeId = Arr::random($employeeIds);
      $randomCenterId = Arr::random($centerIds);
      $randomDepartmentId = Arr::random($departmentIds);
      $randomPositionId = Arr::random($positionIds);

      // Optional: Ensure unique combinations if needed, but for seeding, randomness is usually fine.

      Timeline::create([
        'center_id' => $randomCenterId,
        'department_id' => $randomDepartmentId,
        'position_id' => $randomPositionId,
        'employee_id' => $randomEmployeeId,
        'start_date' => $faker->date(),
        'end_date' => $faker->optional(0.8)->date(), // 80% chance of having an end date
        // Audit columns - assume users are seeded and trait is used
        // If trait handles null, you don't need these here.
        // If trait doesn't handle null, find a user ID:
        // 'created_by' => \App\Models\User::inRandomOrder()->first()?->id,
        // 'updated_by' => \App\Models\User::inRandomOrder()->first()?->id,
        // 'deleted_by' => null,
        // created_at and updated_at are handled by timestamps()
      ]);
    }
    Log::info("TimelineSeeder: Created " . Timeline::count() . " timelines.");
  }
}
