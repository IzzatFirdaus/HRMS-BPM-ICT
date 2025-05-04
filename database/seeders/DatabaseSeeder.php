<?php

namespace Database\Seeders;

// --- Standard Namespace/Class Use Statements (must be before the class) ---
use App\Models\User; // Import the User model (Used for finding and assigning roles)
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role; // Import Role model (Used for finding or creating roles)
use Illuminate\Support\Facades\DB; // Import DB facade (Used for raw queries like SET FOREIGN_KEY_CHECKS)
use Illuminate\Support\Facades\Log; // Import Log facade (For logging seeding progress and issues)
use Illuminate\Support\Facades\Hash; // Import Hash facade (For hashing example user passwords)
use Illuminate\Support\Carbon; // Import Carbon (For setting timestamps on created users)

// --- Import other seeders that this seeder calls ---
// Ensure ALL seeders listed in the $this->call() array below are imported here.
// Add or remove imports based on the seeders you actually have and call.
use Database\Seeders\AdminUserSeeder;
use Database\Seeders\ContractsSeeder;
use Database\Seeders\CenterSeeder;
use Database\Seeders\DepartmentSeeder;
use Database\Seeders\GradesSeeder;
use Database\Seeders\PositionSeeder;
use Database\Seeders\LeavesSeeder;
use Database\Seeders\HolidaysSeeder;
use Database\Seeders\SettingsSeeder;
use Database\Seeders\CategoriesSeeder;
use Database\Seeders\SubCategoriesSeeder;
use Database\Seeders\EquipmentSeeder;
use Database\Seeders\EmployeesSeeder;
use Database\Seeders\UserSeeder; // Assuming this seeds additional users (e.g., using factories)
use Database\Seeders\TimelineSeeder;
use Database\Seeders\EmployeeLeaveSeeder;
use Database\Seeders\EmailApplicationSeeder;
use Database\Seeders\LoanApplicationSeeder;
use Database\Seeders\LoanApplicationItemSeeder;
use Database\Seeders\LoanTransactionsSeeder;
use Database\Seeders\TransitionSeeder;
use Database\Seeders\ApprovalsSeeder;
use Database\Seeders\PermissionRoleTableSeeder; // CRUCIAL: This seeder *creates* the roles and permissions
// use Database\Seeders\CenterHolidayTableSeeder; // Uncomment if you have this seeder


class DatabaseSeeder extends Seeder
{
  /**
   * Seed the application's database.
   *
   * This method orchestrates the calling of other seeders in a specific order
   * to satisfy database foreign key dependencies. It also handles the creation
   * and assignment of roles after the necessary tables and roles exist.
   */
  public function run(): void
  {
    \Log::info('Starting database seeding...'); // Log start

    // --- Dependency Management: Temporarily disable foreign key checks ---
    // This is often necessary when seeders might insert data that temporarily violates
    // foreign key constraints due to the insertion order across multiple tables.
    // Use this with caution and ensure checks are re-enabled afterwards.
    DB::statement('SET FOREIGN_KEY_CHECKS = 0;');
    // Optional: Also disable unique checks if you encounter unique constraint errors during seeding
    // DB::statement('SET UNIQUE_CHECKS = 0;');


    // --- Call other Seeders in Order of Dependency ---
    // Ensure the order here respects foreign key constraints and logical data flow.
    // For example, tables referenced by foreign keys should be seeded *before*
    // tables that reference them.

    // IMPORTANT: PermissionRoleTableSeeder MUST run AFTER AdminUserSeeder and UserSeeder
    // (or whichever seeder creates your users) and AFTER Spatie's migrations have run.
    $this->call([
      // 1. Seed a basic Admin User first using DB::table.
      // This creates a minimal user record early to satisfy audit trail foreign keys
      // in tables created by migrations that run before 'add_motac_columns_to_users_table'
      // and Spatie's migrations.
      AdminUserSeeder::class,

      // 2. Seed Core Lookup tables and base entities.
      ContractsSeeder::class,
      CenterSeeder::class,
      DepartmentSeeder::class, // Depends on Users (for audit columns) - requires AdminUserSeeder first
      GradesSeeder::class,     // Depends on Users (for audit columns) - requires AdminUserSeeder first
      PositionSeeder::class,   // Depends on Department, Grades, Users (audit) - Order matters!
      LeavesSeeder::class,     // Depends on Users (audit)
      HolidaysSeeder::class,   // Depends on Users (audit)
      SettingsSeeder::class,   // Depends on Users (audit)
      CategoriesSeeder::class, // Depends on Users (audit)
      SubCategoriesSeeder::class, // Depends on Categories, Users (audit) - Order matters!
      EquipmentSeeder::class,  // Depends on Users (audit)

      // 3. Seed Employees.
      // Depends on entities like Contracts, Department, Position, Grades, and Users (for audit).
      EmployeesSeeder::class,

      // 4. Seed Bulk Users.
      // This seeder should create additional users, potentially linking them to Employees, etc.
      // It relies on AdminUser existing for created_by/updated_by if using audit traits/logic.
      UserSeeder::class, // Ensure this seeder handles MOTAC columns and audit fields.

      // 5. Seed Data dependent on Employees, Equipment, and ALL Users/Applications/Transactions.
      // These seeders populate transactional or linking tables.
      TimelineSeeder::class, // Depends on Employee, Center, Department, Position, User (audit)
      EmployeeLeaveSeeder::class, // Depends on Employee, Leaves, User (audit)
      EmailApplicationSeeder::class, // Depends on User (applicant, supporting officer, audit)
      LoanApplicationSeeder::class, // Depends on User (applicant, responsible officer, audit), Equipment (indirectly via items)
      LoanApplicationItemSeeder::class, // Depends on LoanApplication
      LoanTransactionsSeeder::class, // Depends on LoanApplication, Equipment, User (officers, audit)
      TransitionSeeder::class, // Depends on Equipment, Employee, User (audit)
      ApprovalsSeeder::class, // Depends on User (officer, audit), EmailApplication, LoanApplication (via morphs)

      // 6. Permissions & Roles Setup.
      // This seeder MUST run *after* the 'users' table and Spatie's permission tables
      // are created by migrations, and typically after initial users are seeded by AdminUserSeeder/UserSeeder.
      // It CREATES the specific roles ('Admin', 'Approver', 'User', 'BPM', etc.) and assigns permissions.
      PermissionRoleTableSeeder::class,

      // Optional seeders
      // CenterHolidayTableSeeder::class, // Link Centers and Holidays if applicable

    ]);


    // --- Dependency Management: Re-enable foreign key checks ---
    // Re-enable the checks that were disabled at the start.
    DB::statement('SET FOREIGN_KEY_CHECKS = 1;');
    // Optional: Also re-enable unique checks if you disabled them globally
    // DB::statement('SET UNIQUE_CHECKS = 1;');


    // --- Role Assignment to Specific Users ---
    // This section finds users (created by AdminUserSeeder or UserSeeder)
    // and assigns roles (created by PermissionRoleTableSeeder) to them.
    // This must happen *after* all dependent seeders have run.

    Log::info('Assigning roles to specific users...'); // Log start

    // Find the roles by name. They should exist because PermissionRoleTableSeeder ran.
    // Use first() here; firstOrCreate() is for the seeder that *defines* the roles.
    $adminRole = Role::where('name', 'Admin')->first();
    $approverRole = Role::where('name', 'Approver')->first();
    $userRole = Role::where('name', 'User')->first();
    $bpmRole = Role::where('name', 'BPM')->first();
    // Find other custom roles if applicable (e.g., AM, CC, CR, HR roles)
    $amRole = Role::where('name', 'AM')->first();
    $ccRole = Role::where('name', 'CC')->first();
    $crRole = Role::where('name', 'CR')->first();
    $hrRole = Role::where('name', 'HR')->first();


    // Check if all essential roles were found before attempting assignment
    // Adjust the roles listed here based on which roles are absolutely critical for your system.
    $essentialRolesFound = $adminRole && $approverRole && $userRole && $bpmRole && $amRole && $ccRole && $crRole && $hrRole;

    if (!$essentialRolesFound) {
      // Log an error if any crucial role is missing.
      // Check which specific roles are null and log details.
      Log::error('One or more essential roles not found for assignment.');
      if (!$adminRole) Log::error(' - Admin role missing.');
      if (!$approverRole) Log::error(' - Approver role missing.');
      if (!$userRole) Log::error(' - User role missing.');
      if (!$bpmRole) Log::error(' - BPM role missing.');
      if (!$amRole) Log::error(' - AM role missing.');
      if (!$ccRole) Log::error(' - CC role missing.');
      if (!$crRole) Log::error(' - CR role missing.');
      if (!$hrRole) Log::error(' - HR role missing.');
      Log::error('Please ensure PermissionRoleTableSeeder is configured correctly to create these roles.');

      // You might consider stopping the seeder here if roles are critical.
      // Example: throw new \Exception('Essential roles not found. Seeding stopped.');
    } else {
      Log::info('Required roles found. Proceeding with user assignment.');

      // --- Assign 'Admin' role to the default admin user (created by AdminUserSeeder) ---
      // Find the admin user by the email used in AdminUserSeeder
      $admin = User::where('email', 'admin@demo.com')->first();

      // Check if the admin user was found before attempting to assign the role
      if ($admin) {
        // Use hasRole check to prevent assigning the role multiple times on re-seeding
        if (!$admin->hasRole($adminRole)) {
          $admin->assignRole($adminRole);
          Log::info('Admin role assigned to user ID: ' . $admin->id . ' (admin@demo.com)');
        } else {
          Log::info('Admin user ID: ' . $admin->id . ' (admin@demo.com) already has the Admin role.');
        }
        // Optional: Assign Admin other roles if needed for testing specific combined permissions
        // Example: $admin->assignRole([$approverRole, $userRole, $bpmRole]);
        // Admin role typically has all permissions via Spatie config, so additional roles may not be strictly necessary for Admin.
      } else {
        Log::warning('Admin user (admin@demo.com) not found for Admin role assignment.');
      }

      // --- Create and Assign roles to additional example users for testing different access levels ---
      // Use distinct emails that are unlikely to be created by your main UserSeeder.
      // Ensure you include all necessary MOTAC-specific columns when creating users here,
      // as the 'add_motac_columns_to_users_table' migration will have run by this point.

      // Example 'Approver' user
      $approverEmail = 'approver@demo.com';
      $approverUser = User::where('email', $approverEmail)->first();
      if (!$approverUser) {
        try {
          $approverUser = User::create([
            'name' => 'Approver Example', // Basic name
            'full_name' => 'Approver User Example', // MOTAC full name
            'email' => $approverEmail,
            'password' => Hash::make('password'), // Set a default password for testing
            'email_verified_at' => Carbon::now(),
            'status' => 'active', // Set a default status
            // Add other nullable MOTAC fields with defaults or null if needed
            // Make sure these fields exist and are fillable in your User model
            // 'employee_id' => null, 'nric' => null, 'motac_email' => null,
            // 'department_id' => null, 'position_id' => null, 'grade_id' => null,
            // 'is_admin' => false, 'is_bpm_staff' => false,
            // Audit columns will likely be filled by the CreatedUpdatedDeletedBy trait if you use it
          ]);
          Log::info("Example Approver user created: {$approverEmail}");
        } catch (\Throwable $e) {
          Log::error("Failed to create Approver user ({$approverEmail}): " . $e->getMessage());
          $approverUser = null; // Ensure $approverUser is null if creation fails
        }
      }
      // Assign the 'Approver' role if the user exists and doesn't already have it
      if ($approverUser && $approverRole && !$approverUser->hasRole($approverRole)) {
        $approverUser->assignRole($approverRole);
        Log::info("Approver role assigned to {$approverEmail}");
      }


      // Example 'User' (Regular User)
      $regularUserEmail = 'user@demo.com';
      $regularUser = User::where('email', $regularUserEmail)->first();
      if (!$regularUser) {
        try {
          $regularUser = User::create([
            'name' => 'Regular Example', // Basic name
            'full_name' => 'Regular User Example', // MOTAC full name
            'email' => $regularUserEmail,
            'password' => Hash::make('password'), // Set a default password for testing
            'email_verified_at' => Carbon::now(),
            'status' => 'active', // Set a default status
            // Add other nullable MOTAC fields with defaults or null
            // Make sure these fields exist and are fillable in your User model
          ]);
          Log::info("Example Regular user created: {$regularUserEmail}");
        } catch (\Throwable $e) {
          Log::error("Failed to create Regular user ({$regularUserEmail}): " . $e->getMessage());
          $regularUser = null;
        }
      }
      // Assign the 'User' role if the user exists and doesn't already have it
      if ($regularUser && $userRole && !$regularUser->hasRole($userRole)) {
        $regularUser->assignRole($userRole);
        Log::info("User role assigned to {$regularUserEmail}");
      }

      // Example 'BPM' user
      $bpmUserEmail = 'bpm@demo.com';
      $bpmUser = User::where('email', $bpmUserEmail)->first();
      if (!$bpmUser) {
        try {
          $bpmUser = User::create([
            'name' => 'BPM Example', // Basic name
            'full_name' => 'BPM User Example', // MOTAC full name
            'email' => $bpmUserEmail,
            'password' => Hash::make('password'), // Set a default password for testing
            'email_verified_at' => Carbon::now(),
            'status' => 'active', // Set a default status
            // Consider setting is_bpm_staff = true if that flag is used separately from roles
            // 'is_bpm_staff' => true,
            // Add other nullable MOTAC fields with defaults or null
            // Make sure these fields exist and are fillable in your User model
          ]);
          Log::info("Example BPM user created: {$bpmUserEmail}");
        } catch (\Throwable $e) {
          Log::error("Failed to create BPM user ({$bpmUserEmail}): " . $e->getMessage());
          $bpmUser = null;
        }
      }
      // Assign the 'BPM' role if the user exists and doesn't already have it
      if ($bpmUser && $bpmRole && !$bpmUser->hasRole($bpmRole)) {
        $bpmUser->assignRole($bpmRole);
        Log::info("BPM role assigned to {$bpmUserEmail}");
      }

      // --- Add logic for other specific role assignments here if needed ---
      // Example: Assign 'AM' role to a user
      // $amUserEmail = 'am@demo.com';
      // $amUser = User::where('email', $amUserEmail)->first();
      // if (!$amUser) { /* creation logic */ }
      // if ($amUser && $amRole && !$amUser->hasRole($amRole)) {
      //     $amUser->assignRole($amRole);
      //     Log::info("AM role assigned to {$amUserEmail}");
      // }


    } // End check if essential roles were found


    Log::info('Specific user role assignment complete.');
    Log::info('Database seeding complete.');
  }
}
