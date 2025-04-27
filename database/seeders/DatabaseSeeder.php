<?php

namespace Database\Seeders;

use App\Models\User; // Import the User model
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role; // Keep if using Spatie roles
use Illuminate\Support\Facades\DB; // Make sure DB facade is imported
use Illuminate\Support\Facades\Log; // Keep if using Log facade


class DatabaseSeeder extends Seeder
{
  /**
   * Seed the application's database.
   */
  public function run(): void
  {
    \Log::info('Starting database seeding...'); // Log start

    // 👇 UNCOMMENTED: Temporarily disable foreign key checks globally for all seeders.
    // Use this with caution and only if you encounter stubborn FK issues.
    // Remember to re-enable it afterwards.
    DB::statement('SET FOREIGN_KEY_CHECKS = 0;');
    // Optional: Also disable unique checks if you encounter unique constraint errors during seeding
    // DB::statement('SET UNIQUE_CHECKS = 0;');


    // 👇 Seeding Order to resolve dependencies (This order looks correct)
    $this->call([
      // 1. Seed a basic Admin User first (for immediate audit FK needs on other tables)
      AdminUserSeeder::class,

      // 2. Seed Core Lookup tables (including those needed by Position and Employee later)
      ContractsSeeder::class,
      CenterSeeder::class, // Will now truncate without error
      DepartmentSeeder::class, // Needs User (audit)
      GradesSeeder::class,     // Needs User (audit)
      PositionSeeder::class,   // Needs Department, Grades, User (audit) - Order matters here!
      LeavesSeeder::class,     // Needs User (audit)
      HolidaysSeeder::class,   // Needs User (audit)
      SettingsSeeder::class,   // Needs User (audit)
      CategoriesSeeder::class, // Needs User (audit)
      SubCategoriesSeeder::class, // Needs Categories, User (audit) - Order matters here!
      EquipmentSeeder::class,  // Needs User (audit)

      // 3. Seed Employees (depends on Contracts, Department, Position, Grades, and Users for audit)
      // Needs the tables above to be seeded first.
      EmployeesSeeder::class, // Needs Contracts, Department, Position, Grades, User (audit)

      // 4. Seed Bulk Users (links to Employee, Department, Position, Grade, and uses Admin for audit)
      // Needs AdminUser, Employees, Department, Position, Grades to be seeded first.
      UserSeeder::class, // Needs AdminUser, Employee, Department, Position, Grades (relies on factory fetching)

      // 5. Seed Data dependent on Employees, Equipment, and ALL Users/Applications/Transactions
      // Ensure dependencies are seeded before these.
      TimelineSeeder::class, // Needs Employee, Center, Department, Position, User (audit)
      EmployeeLeaveSeeder::class, // Needs Employee, Leaves, User (audit)
      EmailApplicationSeeder::class, // Needs User (applicant, supporting officer, audit)
      LoanApplicationSeeder::class, // Needs User (applicant, responsible officer, audit), Equipment
      LoanApplicationItemSeeder::class, // Needs LoanApplication - Order matters!
      LoanTransactionsSeeder::class, // Needs LoanApplication, Equipment, User (officers, audit) - Order matters!
      TransitionSeeder::class, // Needs Equipment, Employee, User (audit)
      ApprovalsSeeder::class, // Needs User (officer, audit), EmailApplication, LoanApplication - Order matters!

      // 6. Permissions & Roles (Depends on Users)
      PermissionRoleTableSeeder::class, // Needs Users - Call after all users exist

      // CenterHolidayTableSeeder::class, // ADDED/UNCOMMENTED: Ensure this seeder exists and is called if you have a CenterHolidayTableSeeder
    ]);


    // 👇 UNCOMMENTED: Re-enable foreign key checks (if disabled above)
    DB::statement('SET FOREIGN_KEY_CHECKS = 1;');
    // Optional: Also re-enable unique checks if you disabled them globally
    // DB::statement('SET UNIQUE_CHECKS = 1;');


    // --- Role Creation and Assignment ---
    // This logic should remain AFTER all users and permissions/roles are seeded (Step 1, 4 & 6 above).
    // Ensure AdminUserSeeder creates a user with email 'admin@demo.com'.

    Log::info('Assigning roles...'); // Log start

    // Find or create the 'Admin' role
    $adminRole = Role::firstOrCreate(['name' => 'Admin']);
    Log::info('Admin role ensured.'); // Log role check

    // Find the admin user by email
    $admin = User::where('email', 'admin@demo.com')->first();

    // Check if the admin user was found before assigning the role
    if ($admin) {
      // Check if the role is not already assigned before assigning to avoid errors/duplicates
      if (!$admin->hasRole($adminRole)) {
        $admin->assignRole($adminRole);
        Log::info('Admin role assigned to user ID: ' . $admin->id);
      } else {
        Log::info('Admin user ID: ' . $admin->id . ' already has the Admin role.');
      }
    } else {
      Log::warning('Admin user (admin@demo.com) not found for role assignment.');
    }

    // Assign roles to other users if needed
    // Example: Assign 'BPM Staff' role to users marked as is_bpm_staff
    // $bpmUsers = User::where('is_bpm_staff', true)->get();
    // if ($bpmUsers->count() > 0) {
    //     $bpmRole = Role::firstOrCreate(['name' => 'BPM Staff']);
    //     foreach ($bpmUsers as $bpmUser) {
    //          if (!$bpmUser->hasRole($bpmRole)) { // Check if role not already assigned
    //              $bpmUser->assignRole($bpmRole);
    //          }
    //     }
    //     Log::info('BPM Staff role ensured for ' . $bpmUsers->count() . ' users.');
    // } else {
    //     Log::info('No BPM staff users found to assign role.');
    // }

    Log::info('Role assignment complete.');
    Log::info('Database seeding complete.');
  }
}
