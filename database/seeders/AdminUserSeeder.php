<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Department;
use App\Models\Position;
use App\Models\Grade;
use App\Models\Employee;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
// Remove DB facade if not used after removing delete/truncate
// use Illuminate\Support\Facades\DB;


class AdminUserSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    // Remove optional delete logic - migrate:fresh handles clearing tables.
    // Be very cautious with deleting specific records if FKs exist!
    // DB::statement('SET FOREIGN_KEY_CHECKS = 0;'); // Only if you uncomment delete
    // User::where('email', 'admin@demo.com')->delete();
    // User::where('id', 1)->delete();
    // DB::statement('SET FOREIGN_KEY_CHECKS = 1;'); // Only if you uncomment delete


    // Find existing Department, Position, and Grade records to link the admin user to.
    // Ensure DepartmentSeeder, PositionSeeder, and GradeSeeder run BEFORE this seeder.
    $department = Department::first();
    $position = Position::first();
    $grade = Grade::first();

    // Find an employee ID to link to.
    // This assumes EmployeesSeeder has run BEFORE this seeder.
    $employee = Employee::first(); // Get the first employee created by EmployeesSeeder
    $employeeId = $employee?->id; // Get the employee's ID, or null if no employee found

    // Create the default Admin User.
    // Check if an admin user with this email already exists (useful if not using migrate:fresh)
    // If using migrate:fresh, this check might be redundant, but doesn't hurt.
    $adminExists = User::where('email', 'admin@demo.com')->exists();

    if (!$adminExists) {
      $adminUser = User::create([
        'name' => 'administrator', // Common lowercase username or system name
        'full_name' => 'Administrator MOTAC', // This column is added in the migration
        'employee_id' => $employeeId, // Link to found employee ID or null
        'email' => 'admin@demo.com',
        'password' => Hash::make('password'),
        'profile_photo_path' => 'profile-photos/.default-photo.jpg',
        'remember_token' => Str::random(10),

        // MOTAC-specific fields - Ensure these match migration column names EXACTLY
        'nric' => '000000000000',
        'mobile_number' => '0123456789',
        'personal_email' => 'admin.personal@demo.com',
        'service_status' => 'permanent', // Must match enum in migration
        'appointment_type' => 'Tetap',
        'is_admin' => true,
        'is_bpm_staff' => true,
        'user_id_assigned' => null,
        'status' => 'active', // Must match enum in migration
        'motac_email' => 'admin@motac.gov.my',

        // Link to related models (using IDs) - Ensure these exist from earlier seeders
        'department_id' => $department?->id,
        'position_id' => $position?->id,
        'grade_id' => $grade?->id,

        // Audit fields handled by trait or set to null/user ID
        'created_by' => null,
        'updated_by' => null,
      ]);

      \Log::info('Admin user seeded successfully (ID: ' . $adminUser->id . ').');
    } else {
      \Log::info('Admin user (admin@demo.com) already exists.');
    }
  }
}
