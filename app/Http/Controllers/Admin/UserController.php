<?php

namespace App\Http\Controllers\Admin; // Ensure the namespace is correct for your project

use App\Http\Controllers\Controller; // Extend the base Controller
use App\Models\User; // Import the User model
use App\Models\Department; // Assuming you need these models for forms/data display
use App\Models\Position; // Assuming these correspond to Designations in your repo
use App\Models\Grade; // Assuming you need this model
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash; // For hashing passwords
use Illuminate\Support\Facades\Mail; // Import the Mail facade
use App\Mail\WelcomeEmail; // Import the WelcomeEmail Mailable
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
    // Adjust relationships ('department', 'position', 'grade') based on your User model and DB schema
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
    $positions = Position::all(); // Assuming Position (Designation) model exists
    $grades = Grade::all(); // Assuming Grade model exists
    // You might define service statuses as a simple array or in a config file
    $serviceStatuses = ['permanent', 'contract', 'mystep', 'intern', 'other_agency'];
    $userStatuses = ['active', 'inactive', 'suspended'];


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
      'nric' => 'required|string|unique:users,nric|max:20', // NRIC should be unique
      'personal_email' => 'required|email|unique:users,personal_email|max:255', // Personal email should be unique
      'mobile_number' => 'nullable|string|max:20', // Assuming mobile number is optional based on DB schema
      'department_id' => 'nullable|exists:departments,id', // Assuming department_id is nullable and departments table exists
      'position_id' => 'nullable|exists:designations,id', // Assuming position_id is nullable and linked to designations table
      'grade_id' => 'nullable|exists:grades,id', // Assuming grade_id is nullable and grades table exists
      'service_status' => ['required', Rule::in(['permanent', 'contract', 'mystep', 'intern', 'other_agency'])],
      'appointment_type' => 'nullable|string|max:255',
      // 'motac_email' => 'nullable|email|unique:users,motac_email|max:255', // MOTAC email likely assigned later
      // 'user_id_assigned' => 'nullable|string|unique:users,user_id_assigned|max:255', // System User ID likely assigned later
      'password' => 'required|string|min:8|confirmed', // Admin sets initial password, requires confirmation
      'status' => ['required', Rule::in(['active', 'inactive', 'suspended'])], // User account status
      // Add validation for any other user fields you are collecting in the form
    ]);

    // 2. Create the new user in the database
    $user = User::create([
      'full_name' => $validatedData['full_name'],
      'nric' => $validatedData['nric'],
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
      // Add other fields as mapped from $validatedData
    ]);

    // 3. Send the welcome email to the user's personal email
    // Ensure the user model instance is passed to the Mailable
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
    $positions = Position::all();
    $grades = Grade::all();
    $serviceStatuses = ['permanent', 'contract', 'mystep', 'intern', 'other_agency'];
    $userStatuses = ['active', 'inactive', 'suspended'];


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
      'nric' => ['required', 'string', 'max:20', Rule::unique('users')->ignore($user->id)],
      'personal_email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
      'mobile_number' => 'nullable|string|max:20',
      'department_id' => 'nullable|exists:departments,id',
      'position_id' => 'nullable|exists:designations,id',
      'grade_id' => 'nullable|exists:grades,id',
      'service_status' => ['required', Rule::in(['permanent', 'contract', 'mystep', 'intern', 'other_agency'])],
      'appointment_type' => 'nullable|string|max:255',
      // motac_email and user_id_assigned might have specific update logic elsewhere
      'password' => 'nullable|string|min:8|confirmed', // Allow updating password, but not required
      'status' => ['required', Rule::in(['active', 'inactive', 'suspended'])],
      // Add validation for any other fields being updated
    ]);

    // 2. Update the user model
    // Handle password update separately if provided
    if (isset($validatedData['password'])) {
      $validatedData['password'] = Hash::make($validatedData['password']);
    } else {
      unset($validatedData['password']); // Don't update password if not provided
    }


    $user->update($validatedData);


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

    // 1. Delete the user
    $user->delete();

    // 2. Redirect with a success message
    return redirect()->route('admin.users.index')->with('success', 'User deleted successfully.');
  }

  // You can add other admin-specific methods here if needed,
  // e.g., for assigning roles, resetting passwords without current password, etc.
}
