<?php

namespace Database\Seeders;

use App\Models\User; // Import User model if assigning roles to users here
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB; // Import DB facade for disabling checks if needed
use Illuminate\Support\Facades\Log; // Import Log facade

class PermissionRoleTableSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    // Reset cached roles and permissions
    // This is important to ensure all permissions and roles are re-registered
    app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    Log::info('Cached permissions reset.');

    // Optional: Clear existing permissions and roles
    // This can be helpful during development to start fresh, but use with caution in production.
    // DB::statement('SET FOREIGN_KEY_CHECKS = 0;');
    // Role::truncate();
    // Permission::truncate();
    // DB::table('model_has_roles')->truncate();
    // DB::table('model_has_permissions')->truncate();
    // DB::table('role_has_permissions')->truncate();
    // DB::statement('SET FOREIGN_KEY_CHECKS = 1;');
    // Log::info('Existing roles and permissions truncated (optional).');


    // --- Define Permissions ---
    // Create permissions for different actions/resources in your application.
    // Use a consistent naming convention (e.g., resource-action)

    // User Management Permissions
    $userPermissions = [
      'view-users',
      'create-users',
      'edit-users',
      'delete-users',
      'assign-roles', // Permission to assign roles to users
      'view-user-logs',
      'reset-user-password',
    ];

    // Employee Management Permissions
    $employeePermissions = [
      'view-employees',
      'create-employees',
      'edit-employees',
      'delete-employees',
      'view-employee-leave', // View employee leave records
      'manage-employee-leave', // Create/edit/delete employee leave records
      'view-employee-contracts',
      'manage-employee-contracts',
      'view-employee-fingerprints', // View fingerprint records
      'manage-employee-fingerprints', // Manage fingerprint records
      'view-employee-discounts', // View discount records
      'manage-employee-discounts', // Manage discount records
      'view-employee-messages', // View messages
      'send-employee-messages', // Send messages
      'view-employee-transitions', // View asset transitions
      'manage-employee-transitions', // Manage asset transitions
      'view-employee-timelines', // View employee timelines
      'manage-employee-timelines', // Manage employee timelines
    ];

    // Application Management Permissions (Email, Loan, etc.)
    $applicationPermissions = [
      'view-email-applications',
      'create-email-applications', // Applicant permission
      'edit-email-applications', // Applicant permission (draft)
      'delete-email-applications', // Applicant permission (draft)
      'review-email-applications', // Support/Admin permission to review/approve/reject
      'provision-email-applications', // IT Admin permission to provision
      'view-loan-applications',
      'create-loan-applications', // Applicant permission
      'edit-loan-applications', // Applicant permission (draft)
      'delete-loan-applications', // Applicant permission (draft)
      'review-loan-applications', // Support/Officer permission to review/approve/reject
      'issue-loan-equipment', // BPM permission to issue equipment
      'receive-loan-equipment', // BPM permission to receive equipment return
    ];

    // Equipment/Asset Management Permissions (using the standardized 'equipment' name)
    $equipmentPermissions = [
      'view-equipment',
      'create-equipment',
      'edit-equipment',
      'delete-equipment',
      // Inventory management related permissions might go here
      'manage-equipment-inventory',
    ];

    // System/Lookup Management Permissions (e.g., Grades, Departments, Positions, Contracts, Centers, Leaves, Holidays, Categories, SubCategories, Settings, Audit Logs, System Logs)
    $systemPermissions = [
      'view-grades',
      'manage-grades', // Create/edit/delete grades
      'view-departments',
      'manage-departments',
      'view-positions', // Using the standardized 'positions' name
      'manage-positions',
      'view-contracts',
      'manage-contracts',
      'view-centers',
      'manage-centers',
      'view-leaves',
      'manage-leaves',
      'view-holidays',
      'manage-holidays',
      'view-categories',
      'manage-categories',
      'view-subcategories',
      'manage-subcategories',
      'view-settings',
      'manage-settings',
      'view-audit-logs', // Permission to view audit logs (CreatedUpdatedDeletedBy)
      'view-system-logs', // <--- ADDED: This permission was missing from the creation list
    ];

    // Combine all permissions
    $allPermissions = array_merge(
      $userPermissions,
      $employeePermissions,
      $applicationPermissions,
      $equipmentPermissions,
      $systemPermissions
    );

    // Create all permissions if they don't exist
    foreach ($allPermissions as $permissionName) {
      Permission::firstOrCreate(['name' => $permissionName]);
    }
    Log::info("Ensured all necessary permissions exist.");

    // --- Define Roles ---
    // Create roles or find existing ones.
    // 'Admin' role is often created first in DatabaseSeeder or AdminUserSeeder.
    $adminRole = Role::firstOrCreate(['name' => 'Admin']);
    $bpmStaffRole = Role::firstOrCreate(['name' => 'BPM Staff']);
    $employeeRole = Role::firstOrCreate(['name' => 'Employee']); // Basic role for most users
    // Add other roles as needed (e.g., 'Department Head', 'IT Admin', 'HR Staff')
    $departmentHeadRole = Role::firstOrCreate(['name' => 'Department Head']);
    $itAdminRole = Role::firstOrCreate(['name' => 'IT Admin']);
    $hrStaffRole = Role::firstOrCreate(['name' => 'HR Staff']);
    Log::info("Ensured all necessary roles exist.");


    // --- Assign Permissions to Roles ---

    // Admin: Has all permissions
    $adminRole->syncPermissions(Permission::all()); // Give Admin all permissions
    Log::info("'Admin' role assigned all permissions.");

    // BPM Staff: Can manage equipment, review loan applications, issue/receive equipment
    $bpmStaffPermissions = array_merge(
      $equipmentPermissions, // Can manage equipment inventory
      ['view-loan-applications', 'review-loan-applications', 'issue-loan-equipment', 'receive-loan-equipment'], // Loan application/transaction permissions
      ['view-employees', 'view-employee-transitions', 'view-employee-timelines'] // Can view some employee/transition info
    );
    $bpmStaffRole->syncPermissions($bpmStaffPermissions);
    Log::info("'BPM Staff' role assigned permissions.");

    // Employee: Basic permissions to view their own info and create applications
    $employeePermissionsBasic = [
      'view-users', // Can view basic user profiles
      'view-employees', // Can view basic employee profiles
      'view-employee-leave', // Can view their own leave
      'create-email-applications', // Can create email apps
      'edit-email-applications', // Can edit their own draft email apps
      'delete-email-applications', // Can delete their own draft email apps
      'view-email-applications', // Can view their own email apps
      'create-loan-applications', // Can create loan apps
      'edit-loan-applications', // Can edit their own draft loan apps
      'delete-loan-applications', // Can delete their own draft loan apps
      'view-loan-applications', // Can view their own loan apps
      'view-equipment', // Can view available equipment
      'view-employee-transitions', // Can view their own assigned equipment
      'view-employee-timelines', // Can view their own timeline
    ];
    $employeeRole->syncPermissions($employeePermissionsBasic);
    Log::info("'Employee' role assigned permissions.");


    // Department Head: Can review applications from their department, view employee info in their department
    $departmentHeadPermissions = [
      'view-users',
      'view-employees', // Can view employees (add department scope later if needed)
      'view-employee-leave',
      'view-email-applications',
      'review-email-applications', // Can review email applications (add department scope later if needed)
      'view-loan-applications',
      'review-loan-applications', // Can review loan applications (add department scope later if needed)
      'view-equipment',
      'view-employee-transitions',
      'view-employee-timelines',
    ];
    $departmentHeadRole->syncPermissions($departmentHeadPermissions);
    Log::info("'Department Head' role assigned permissions.");


    // IT Admin: Can manage users, equipment, software licenses, provision emails, reset passwords, view logs
    $itAdminPermissions = array_merge(
      $userPermissions, // Can manage users fully
      $equipmentPermissions, // Can manage equipment fully
      ['view-email-applications', 'review-email-applications', 'provision-email-applications'], // Email app review and provisioning
      ['view-loan-applications', 'review-loan-applications', 'issue-loan-equipment', 'receive-loan-equipment'], // Loan app review and transactions
      ['view-system-logs', 'view-audit-logs'] // Including the needed logs permissions
    );
    // Prevent IT Admin from assigning roles to avoid permission escalation unless intended
    $itAdminPermissions = array_diff($itAdminPermissions, ['assign-roles']);
    $itAdminRole->syncPermissions($itAdminPermissions);
    Log::info("'IT Admin' role assigned permissions.");


    // HR Staff: Can manage employee records, leave, contracts, fingerprints, discounts, messages, etc.
    $hrStaffPermissions = array_merge(
      $employeePermissions, // Can manage employee data fully
      ['view-users', 'edit-users'], // Can view/edit basic user info related to employee data
      ['view-leaves', 'manage-leaves'], // Can manage leave types
      ['view-holidays', 'manage-holidays'], // Can manage holidays
      ['view-contracts', 'manage-contracts'] // Can manage contracts
    );
    // Prevent HR Staff from assigning roles unless intended
    $hrStaffPermissions = array_diff($hrStaffPermissions, ['assign-roles']);
    $hrStaffRole->syncPermissions($hrStaffPermissions);
    Log::info("'HR Staff' role assigned permissions.");


    // --- Assign Roles to Users ---
    // You can assign roles to users here if needed.
    // The Admin user role is already handled in DatabaseSeeder.php.

    // Example: Assign 'BPM Staff' role to users who have 'is_bpm_staff' set to true
    // $bpmUsers = User::where('is_bpm_staff', true)->get();
    // foreach ($bpmUsers as $user) {
    //     $user->assignRole('BPM Staff');
    // }
    // Log::info("Assigned 'BPM Staff' role to users with is_bpm_staff=true.");

    // Example: Assign 'Employee' role to all users who are not Admin or BPM Staff (or other specific roles)
    // This needs to be done carefully to avoid overwriting roles.
    // $usersToAssignEmployeeRole = User::doesntHave('roles')->get(); // Get users with no roles yet
    // foreach ($usersToAssignEmployeeRole as $user) {
    //      $user->assignRole('Employee');
    // }
    // Log::info("Assigned 'Employee' role to users who had no roles.");

    // You might assign roles based on department, position, grade, etc., after those seeders run.
  }
}
