<?php

namespace Database\Seeders;

use App\Models\EmployeeLeave; // Import the EmployeeLeave model
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents; // Optional trait

class EmployeeLeaveSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    // Optionally, delete existing records before seeding to avoid duplicates
    // EmployeeLeave::truncate(); // Use with caution

    // Create a number of fake employee leave records using the factory
    // Example: Create 50 fake leave records
    EmployeeLeave::factory()
      ->count(50) // Number of records to create
      // Add states here if you want a mix (e.g., ->state([...]) or ->authorized() )
      ->create(); // Create the records

    // Example: Create some authorized leave records
    // EmployeeLeave::factory()
    //    ->count(20)
    //    ->authorized() // Use the authorized state
    //    ->create();

    // Example: Create some checked leave records
    // EmployeeLeave::factory()
    //    ->count(15)
    //    ->checked() // Use the checked state
    //    ->create();

    // Example: Create a specific leave record
    // $employee = \App\Models\Employee::find(1); // Find specific employee
    // $leaveType = \App\Models\Leave::where('name', 'Annual Leave')->first(); // Find specific leave type
    // if ($employee && $leaveType) {
    //      EmployeeLeave::factory()->create([
    //          'employee_id' => $employee->id,
    //          'leave_id' => $leaveType->id,
    //          'from_date' => '2025-05-01',
    //          'to_date' => '2025-05-02',
    //          'note' => 'Family trip',
    //          'is_authorized' => true,
    //          'created_by' => \App\Models\User::first()?->id,
    //          'updated_by' => \App\Models\User::first()?->id,
    //      ]);
    // }
  }
}
