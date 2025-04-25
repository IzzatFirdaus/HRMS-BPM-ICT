<?php

namespace App\Http\Controllers\Admin; // Ensure the namespace is correct for your project

use App\Http\Controllers\Controller; // Extend the base Controller
use App\Models\User; // Import the User model
use App\Models\Department; // Assuming Department model exists
// Import the Position model, assuming it's now named Position.php
use App\Models\Position;
use App\Models\Grade; // Assuming Grade model exists
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash; // For hashing passwords
use Illuminate\Support\Facades\Mail; // Import the Mail facade
use App\Mail\WelcomeEmail; // Import the WelcomeEmail Mailable (ensure this Mailable exists)
use Illuminate\Validation\Rule; // For using validation rules like Rule::in, Rule::unique
use Illuminate\Support\Facades\Gate; // For manual authorization checks if not using policies only

class UserController extends Controller
{
  /**
   * Display a listing of the resource.
   */
  public function index()
  {
    // Optional: Add authorization check
    // $this->authorize('viewAny', User::class); // Assuming a UserPolicy exists

    // Fetch users with their related data for the index table
    // Relationships ('department', 'position', 'grade') must match the method names in the User model
    $users = User::with(['department', 'position', 'grade'])->get();

    // Return the view with the list of users
    // Assuming your admin user views are located in resources/views/admin/users
    return view('admin.users.index', compact('users'));
  }

  /**
   * Show the form for creating a new resource.
   */
  public function create()
  {
    // Optional: Add authorization check
    // $this->authorize('create', User::class); // Assuming a UserPolicy exists

    // Load data needed for the user creation form (e.g., departments, positions, grades, service statuses)
    $departments = Department::all(); // Assuming Department model exists
    $positions = Position::all(); // Load Positions (using the Position model)
    $grades = Grade::all(); // Assuming Grade model exists
    // You might define service statuses as a simple array or in a config file
    $serviceStatuses = ['permanent', 'contract', 'mystep', 'intern', 'other_agency']; // Match enum in migration
    $userStatuses = ['active', 'inactive', 'suspended']; // Match enum in migration


    // Return the view for creating a user
    return view('admin.users.create', compact('departments', 'positions', 'grades', 'serviceStatuses', 'userStatuses'));
  }

  /**
   * Store a newly created resource in storage.
   */
  public function store(Request $request)
  {
    // Optional: Add authorization check
    // $this->authorize('create', User::class); // Assuming a UserPolicy exists

    // 1. Validate the incoming request data
    // Adjust validation rules based on the fields you added to the 'users' table and your form
    $validatedData = $request->validate([
      'full_name' => 'required|string|max:255',
      'nric' => 'required|string|unique:users,identification_number|max:20', // Validate against 'identification_number' column
      'personal_email' => 'required|email|unique:users,personal_email|max:255', // Personal email should be unique
      'mobile_number' => 'nullable|string|max:20', // Assuming mobile number is optional
      'department_id' => 'nullable|exists:departments,id', // Validate existence in departments table
      'position_id' => 'nullable|exists:designations,id', // Validate existence in designations table (this refers to the table name)
      'grade_id' => 'nullable|exists:grades,id', // Validate existence in grades table
      'service_status' => ['required', Rule::in(['permanent', 'contract', 'mystep', 'intern', 'other_agency'])],
      'appointment_type' => 'nullable|string|max:255',
      // 'motac_email' => 'nullable|email|unique:users,motac_email|max:255', // MOTAC email likely assigned later
      // 'user_id_assigned' => 'nullable|string|unique:users,user_id_assigned|max:255', // System User ID likely assigned later
      'password' => 'required|string|min:8|confirmed', // Admin sets initial password, requires confirmation
      'status' => ['required', Rule::in(['active', 'inactive', 'suspended'])], // User account status
      // Add validation for any other user fields you are collecting in the form
      // Ensure 'email' field from HRMS is handled if still used for login
      // 'email' => 'required|email|unique:users,email|max:255', // If 'email' is for login
    ]);

    // 2. Create the new user in the database
    $user = User::create([
      'full_name' => $validatedData['full_name'],
      'identification_number' => $validatedData['nric'], // Map NRIC input to identification_number column
      'personal_email' => $validatedData['personal_email'],
      'mobile_number' => $validatedData['mobile_number'],
      'department_id' => $validatedData['department_id'],
      'position_id' => $validatedData['position_id'],
      'grade_id' => $validatedData['grade_id'],
      'service_status' => $validatedData['service_status'],
      'appointment_type' => $validatedData['appointment_type'],
      'password' => Hash::make($validatedData['password']), // Hash the password before saving!
      'status' => $validatedData['status'],
      // Assign default values or null for motac_email, user_id_assigned as they are part of provisioning workflow
      'motac_email' => null,
      'user_id_assigned' => null,
      // Add other fields as mapped from $validatedData, e.g., 'email' if separate login email
      // 'email' => $validatedData['email'], // If 'email' is for login
      'name' => $validatedData['full_name'], // Assuming HRMS 'name' is derived from full_name
    ]);

    // 3. Send the welcome email to the user's personal email
    // Ensure the user model instance is passed to the Mailable
    // Ensure the WelcomeEmail Mailable exists and is correctly configured
    try {
      // Check if personal email exists before attempting to send
      if ($user->personal_email) {
        Mail::to($user->personal_email) // Send to the user's personal email
          ->send(new WelcomeEmail($user));
        // Log successful email sending
        \Log::info("Welcome email sent to user ID: " . $user->id . " at " . $user->personal_email);
      } else {
        \Log::warning("Skipped sending welcome email for user ID: " . $user->id . " as personal email is missing.");
        // Optionally, flash a warning about the missing email
        session()->flash('warning_email', 'User created, but personal email is missing, welcome email was not sent.');
      }
    } catch (\Exception $e) {
      // Log any errors during email sending
      \Log::error("Failed to send welcome email to user ID: " . $user->id . ". Error: " . $e->getMessage());
      // Optionally, inform the admin that email sending failed
      session()->flash('warning_email', 'User created, but failed to send welcome email.');
    }


    // 4. Redirect to the user index page with a success message
    // Assuming you have a named route for the admin user index like 'admin.users.index'
    return redirect()->route('admin.users.index')->with('success', 'User created successfully!');
  }

  /**
   * Display the specified resource.
   */
  public function show(User $user)
  {
    // Optional: Add authorization check
    // $this->authorize('view', $user); // Assuming a UserPolicy exists

    // Eager load relationships if needed for the show view
    $user->load(['department', 'position', 'grade']);

    // Return the view to show user details
    return view('admin.users.show', compact('user'));
  }

  /**
   * Show the form for editing the specified resource.
   */
  public function edit(User $user)
  {
    // Optional: Add authorization check
    // $this->authorize('update', $user); // Assuming a UserPolicy exists

    // Load data needed for the edit form (e.g., departments, positions, grades, service statuses)
    $departments = Department::all();
    $positions = Position::all(); // Load Positions (using the Position model)
    $grades = Grade::all();
    $serviceStatuses = ['permanent', 'contract', 'mystep', 'intern', 'other_agency']; // Match enum in migration
    $userStatuses = ['active', 'inactive', 'suspended']; // Match enum in migration


    // Return the view for editing a user
    return view('admin.users.edit', compact('user', 'departments', 'positions', 'grades', 'serviceStatuses', 'userStatuses'));
  }

  /**
   * Update the specified resource in storage.
   */
  public function update(Request $request, User $user)
  {
    // Optional: Add authorization check
    // $this->authorize('update', $user); // Assuming a UserPolicy exists

    // 1. Validate the incoming request data for update
    // Use Rule::unique ignore to allow the user's current NRIC/email
    $validatedData = $request->validate([
      'full_name' => 'required|string|max:255',
      'nric' => ['required', 'string', 'max:20', Rule::unique('users', 'identification_number')->ignore($user->id)], // Validate against 'identification_number' column
      'personal_email' => ['required', 'email', 'max:255', Rule::unique('users', 'personal_email')->ignore($user->id)], // Personal email unique, ignore current user
      'mobile_number' => 'nullable|string|max:20',
      'department_id' => 'nullable|exists:departments,id',
      'position_id' => 'nullable|exists:designations,id', // Validate existence in designations table (this refers to the table name)
      'grade_id' => 'nullable|exists:grades,id', // Validate existence in grades table
      'service_status' => ['required', Rule::in(['permanent', 'contract', 'mystep', 'intern', 'other_agency'])],
      'appointment_type' => 'nullable|string|max:255',
      // motac_email and user_id_assigned might have specific update logic elsewhere and may need unique ignore rules too
      'password' => 'nullable|string|min:8|confirmed', // Allow updating password, but not required
      'status' => ['required', Rule::in(['active', 'inactive', 'suspended'])],
      // Add validation for any other fields being updated
      // Ensure 'email' field from HRMS is handled if still used for login
      // 'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)], // If 'email' is for login
    ]);

    // 2. Update the user model
    // Handle password update separately if provided
    if (isset($validatedData['password'])) {
      $validatedData['password'] = Hash::make($validatedData['password']);
    } else {
      unset($validatedData['password']); // Don't update password if not provided
    }

    // Map validated data to User model columns
    $userData = [
      'full_name' => $validatedData['full_name'],
      'identification_number' => $validatedData['nric'],
      'personal_email' => $validatedData['personal_email'],
      'mobile_number' => $validatedData['mobile_number'],
      'department_id' => $validatedData['department_id'],
      'position_id' => $validatedData['position_id'],
      'grade_id' => $validatedData['grade_id'],
      'service_status' => $validatedData['service_status'],
      'appointment_type' => $validatedData['appointment_type'],
      'status' => $validatedData['status'],
      // Include password if it was set
      'password' => $validatedData['password'] ?? $user->password, // Use new hashed password or existing one
      // Add other fields as mapped
      'name' => $validatedData['full_name'], // Assuming HRMS 'name' is derived from full_name
      // Handle 'email' field if used for login separately
      // 'email' => $validatedData['email'] ?? $user->email,
    ];


    $user->update($userData);


    // 3. Redirect to the user details page or index page
    return redirect()->route('admin.users.show', $user)->with('success', 'User updated successfully.'); // Redirect to show view
    // Or redirect to index: return redirect()->route('admin.users.index')->with('success', 'User updated successfully.');
  }

  /**
   * Remove the specified resource from storage.
   */
  public function destroy(User $user)
  {
    // Optional: Add authorization check
    // $this->authorize('delete', $user); // Assuming a UserPolicy exists

    // 1. Delete the user (consider soft deletes if applicable)
    $user->delete();

    // 2. Redirect with a success message
    return redirect()->route('admin.users.index')->with('success', 'User deleted successfully.');
  }

  // You can add other admin-specific methods here if needed,
  // e.g., for assigning roles/permissions, resetting passwords without current password, etc.
  /**
   * Show the form for editing user roles and permissions.
   * You might need a separate view for this.
   */
  // public function editRolesPermissions(User $user)
  // {
  //     // $this->authorize('assignRolesPermissions', $user); // Assuming a policy check
  //     $roles = Role::all(); // Assuming Spatie Role model
  //     $permissions = Permission::all(); // Assuming Spatie Permission model
  //     return view('admin.users.roles_permissions', compact('user', 'roles', 'permissions'));
  // }

  /**
   * Update user roles and permissions.
   * You might need a specific Request class for validation.
   */
  // public function updateRolesPermissions(Request $request, User $user)
  // {
  //     // $this->authorize('assignRolesPermissions', $user); // Assuming a policy check
  //     $validatedData = $request->validate([
  //         'roles' => 'nullable|array',
  //         'roles.*' => 'exists:roles,name',
  //         'permissions' => 'nullable|array',
  //         'permissions.*' => 'exists:permissions,name',
  //     ]);

  //     $user->syncRoles($validatedData['roles'] ?? []);
  //     $user->syncPermissions($validatedData['permissions'] ?? []);

  //     return redirect()->route('admin.users.show', $user)->with('success', 'Roles and permissions updated successfully.');
  // }
}
