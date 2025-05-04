<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User; // Import the User model
use App\Models\Department; // Assuming Department model exists
use App\Models\Position; // Import the Position model
use App\Models\Grade; // Assuming Grade model exists
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Exception;
use Illuminate\Database\QueryException;
use Spatie\Permission\Models\Role; // Import Spatie Role model

// Ensure your User model uses the Illuminate\Database\Eloquent\SoftDeletes and Spatie\Permission\Traits\HasRoles traits.

class UserController extends Controller
{
  /**
   * Apply authentication middleware and authorizeResource for policy checks.
   * Assumes a UserPolicy is defined and registered to handle authorization logic
   * for index, create, store, show, edit, update, and destroy actions.
   */
  public function __construct()
  {
    $this->middleware('auth');
    $this->authorizeResource(User::class, 'user');
  }

  /**
   * Display a listing of the resource (Users).
   *
   * @return \Illuminate\View\View
   */
  public function index()
  {
    // Authorization is handled by authorizeResource in the constructor

    // Fetch users with their related data for the index table
    // Eager load relationships for performance (Department, Position, Grade, and Spatie Roles)
    // Relationships ('department', 'position', 'grade', 'roles') must match the method names in the User model
    // Using ->withTrashed() might be needed if you want to show soft-deleted users in the admin list
    $users = User::with(['department', 'position', 'grade', 'roles']) // Eager load 'roles' for Spatie
      ->latest() // Order by latest creation date
      ->paginate(15); // Paginate results

    // Return the view with the list of users
    return view('admin.users.index', compact('users'));
  }

  /**
   * Show the form for creating a new resource (User).
   *
   * @return \Illuminate\View\View
   */
  public function create()
  {
    // Authorization is handled by authorizeResource in the constructor

    // Load data needed for the user creation form
    $departments = Department::all();
    $positions = Position::all();
    $grades = Grade::all();
    // Fetch all available roles for assignment
    $roles = Role::all(); // Fetch all roles using Spatie model

    // Define enums as arrays, matching enum values in the migration
    $serviceStatuses = ['permanent', 'contract', 'mystep', 'intern', 'other_agency'];
    $userStatuses = ['active', 'inactive', 'suspended'];

    // Return the view for creating a user, passing all necessary data including roles
    return view('admin.users.create', compact('departments', 'positions', 'grades', 'serviceStatuses', 'userStatuses', 'roles'));
  }

  /**
   * Store a newly created resource (User) in storage.
   *
   * @param  \Illuminate\Http\Request  $request  The incoming request.
   * @return \Illuminate\Http\RedirectResponse
   */
  public function store(Request $request)
  {
    // Authorization is handled by authorizeResource in the constructor

    // 1. Validate the incoming request data
    // Adjust validation rules based on the fields you added to the 'users' table and your form
    $validatedData = $request->validate([
      'full_name' => 'required|string|max:255',
      // CORRECTED: Changed validation rule name from identification_number to nric
      'nric' => 'required|string|unique:users,nric|max:20',
      // Personal email is nullable unique in DB, but marked required in form. Keep required for form.
      'personal_email' => 'required|email|unique:users,personal_email|max:255',
      'mobile_number' => 'nullable|string|max:20',
      'department_id' => 'nullable|exists:departments,id',
      'position_id' => 'nullable|exists:positions,id',
      'grade_id' => 'nullable|exists:grades,id',
      'service_status' => ['required', Rule::in(['permanent', 'contract', 'mystep', 'intern', 'other_agency'])],
      'appointment_type' => 'nullable|string|max:255',
      // MOTAC email and user_id_assigned are nullable unique in DB and likely assigned via workflow, make nullable in validation
      'motac_email' => 'nullable|email|unique:users,motac_email|max:255',
      'user_id_assigned' => 'nullable|string|unique:users,user_id_assigned|max:255',
      'password' => 'required|string|min:8|confirmed', // Admin sets initial password
      'status' => ['required', Rule::in(['active', 'inactive', 'suspended'])], // User account status
      // Assuming 'email' field is required for system login (standard Laravel)
      'email' => 'required|email|unique:users,email|max:255',
      // Validate the roles array submitted from the form
      'roles' => 'nullable|array', // roles can be null or an array
      'roles.*' => 'exists:roles,name', // each value in roles array must exist in roles table 'name' column
    ]);

    // 2. Create the new user in the database
    try {
      $user = User::create([
        'name' => $validatedData['full_name'], // Map full_name to standard 'name' for compatibility
        'email' => $validatedData['email'], // Use validated email for login
        'password' => Hash::make($validatedData['password']),
        'full_name' => $validatedData['full_name'],
        // Use corrected and validated field names from the request
        'nric' => $validatedData['nric'],
        'personal_email' => $validatedData['personal_email'],
        'mobile_number' => $validatedData['mobile_number'],
        'department_id' => $validatedData['department_id'],
        'position_id' => $validatedData['position_id'],
        'grade_id' => $validatedData['grade_id'],
        'service_status' => $validatedData['service_status'],
        'appointment_type' => $validatedData['appointment_type'],
        'status' => $validatedData['status'],
        // motac_email and user_id_assigned can be set if provided in the form, otherwise null
        'motac_email' => $validatedData['motac_email'] ?? null,
        'user_id_assigned' => $validatedData['user_id_assigned'] ?? null,
        // Other fields from migration added here with default/nullable if not in form
        'employee_id' => null, // Assuming employee_id linkage is handled elsewhere
        'is_admin' => in_array('Admin', $validatedData['roles'] ?? []), // Set flag based on assigned roles
        'is_bpm_staff' => in_array('BPM', $validatedData['roles'] ?? []), // Set flag based on assigned roles
        'profile_photo_path' => null, // Assuming profile photos are handled elsewhere
      ]);

      // 3. Assign roles to the newly created user using Spatie
      // Check if roles were submitted and sync them
      if (!empty($validatedData['roles'])) {
        // syncRoles expects an array of role names or IDs
        $user->syncRoles($validatedData['roles']);
      } else {
        // If no roles selected, ensure no roles are assigned
        $user->syncRoles([]);
      }


      // Optional: Log the creation
      Log::info('User created successfully', [
        'user_id' => $user->id,
        'full_name' => $user->full_name,
        'created_by' => Auth::id()
      ]);

      // 4. Redirect to the user index page with a success message (in Malay)
      return redirect()->route('admin.users.index')->with('success', 'Pengguna berjaya ditambah.');
    } catch (QueryException $e) {
      Log::error('Failed to create user due to database error: ' . $e->getMessage(), [
        'request_data' => $request->all(),
        'created_by' => Auth::id(),
        'exception' => $e,
      ]);
      // Changed error message to Malay
      return redirect()->back()->withInput()->with('error', 'Gagal menambah Pengguna disebabkan ralat pangkalan data. Sila semak log untuk butiran lanjut.');
    } catch (Exception $e) {
      Log::error('An unexpected error occurred while creating user: ' . $e->getMessage(), [
        'request_data' => $request->all(),
        'created_by' => Auth::id(),
        'exception' => $e,
      ]);
      // Changed error message to Malay
      return redirect()->back()->withInput()->with('error', 'Gagal menambah Pengguna disebabkan ralat tidak dijangka.');
    }
  }

  /**
   * Display the specified resource (User).
   *
   * @param  \App\Models\User  $user  The user instance resolved by route model binding.
   * @return \Illuminate\View\View
   */
  public function show(User $user)
  {
    // Authorization is handled by authorizeResource in the constructor

    // Eager load relationships needed for the show view
    $user->load(['department', 'position', 'grade', 'roles']); // Eager load 'roles'

    // Return the view to show user details
    return view('admin.users.show', compact('user'));
  }

  /**
   * Show the form for editing the specified resource (User).
   *
   * @param  \App\Models\User  $user  The user instance resolved by route model binding.
   * @return \Illuminate\View\View
   */
  public function edit(User $user)
  {
    // Authorization is handled by authorizeResource in the constructor

    // Load data needed for the edit form
    $departments = Department::all();
    $positions = Position::all();
    $grades = Grade::all();
    // Fetch all available roles for assignment
    $roles = Role::all(); // Fetch all roles using Spatie model
    // Fetch the roles currently assigned to the user
    $userRoles = $user->roles->pluck('name')->toArray(); // Get an array of current role names

    // Define enums as arrays, matching enum values in the migration
    $serviceStatuses = ['permanent', 'contract', 'mystep', 'intern', 'other_agency'];
    $userStatuses = ['active', 'inactive', 'suspended'];

    // Return the view for editing a user, passing all necessary data including roles
    return view('admin.users.edit', compact('user', 'departments', 'positions', 'grades', 'serviceStatuses', 'userStatuses', 'roles', 'userRoles'));
  }

  /**
   * Update the specified resource (User) in storage.
   *
   * @param  \Illuminate\Http\Request  $request  The incoming request.
   * @param  \App\Models\User  $user  The user instance resolved by route model binding.
   * @return \Illuminate\Http\RedirectResponse
   */
  public function update(Request $request, User $user)
  {
    // Authorization is handled by authorizeResource in the constructor

    // 1. Validate the incoming request data for update
    $validatedData = $request->validate([
      'full_name' => 'required|string|max:255',
      // CORRECTED: Changed validation rule name to nric, ignore current user
      'nric' => ['required', 'string', 'max:20', Rule::unique('users', 'nric')->ignore($user->id)],
      // Personal email unique, ignore current user
      'personal_email' => ['required', 'email', 'max:255', Rule::unique('users', 'personal_email')->ignore($user->id)],
      'mobile_number' => 'nullable|string|max:20',
      'department_id' => 'nullable|exists:departments,id',
      'position_id' => 'nullable|exists:positions,id',
      'grade_id' => 'nullable|exists:grades,id',
      'service_status' => ['required', Rule::in(['permanent', 'contract', 'mystep', 'intern', 'other_agency'])],
      'appointment_type' => 'nullable|string|max:255',
      // motac_email and user_id_assigned are nullable unique in DB, ignore current user
      'motac_email' => ['nullable', 'email', 'max:255', Rule::unique('users', 'motac_email')->ignore($user->id)],
      'user_id_assigned' => ['nullable', 'string', 'max:255', Rule::unique('users', 'user_id_assigned')->ignore($user->id)],
      // Password update is optional, requires confirmation if present
      'password' => 'nullable|string|min:8|confirmed',
      'status' => ['required', Rule::in(['active', 'inactive', 'suspended'])], // User account status
      // Assuming 'email' field is required for system login (standard Laravel)
      'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
      // Validate the roles array submitted from the form
      'roles' => 'nullable|array', // roles can be null or an array
      'roles.*' => 'exists:roles,name', // each value in roles array must exist in roles table 'name' column
    ]);

    // 2. Update the user model
    try {
      $userData = [
        'name' => $validatedData['full_name'], // Map full_name to standard 'name'
        'email' => $validatedData['email'], // Use validated email
        'full_name' => $validatedData['full_name'],
        // Use corrected and validated field names
        'nric' => $validatedData['nric'],
        'personal_email' => $validatedData['personal_email'],
        'mobile_number' => $validatedData['mobile_number'],
        'department_id' => $validatedData['department_id'],
        'position_id' => $validatedData['position_id'],
        'grade_id' => $validatedData['grade_id'],
        'service_status' => $validatedData['service_status'],
        'appointment_type' => $validatedData['appointment_type'],
        'status' => $validatedData['status'],
        'motac_email' => $validatedData['motac_email'] ?? null,
        'user_id_assigned' => $validatedData['user_id_assigned'] ?? null,
        // Update flags based on assigned roles
        'is_admin' => in_array('Admin', $validatedData['roles'] ?? []),
        'is_bpm_staff' => in_array('BPM', $validatedData['roles'] ?? []),
        // employee_id and profile_photo_path likely not updated via this form
      ];

      // Only update password if it was provided and validated
      if (!empty($validatedData['password'])) {
        $userData['password'] = Hash::make($validatedData['password']);
      }

      $user->update($userData);

      // 3. Update roles for the user using Spatie
      // syncRoles expects an array of role names or IDs
      $user->syncRoles($validatedData['roles'] ?? []); // Pass the array or an empty array if null


      // Optional: Log the update
      Log::info('User updated successfully', [
        'user_id' => $user->id,
        'full_name' => $user->full_name,
        'updated_by' => Auth::id()
      ]);

      // 4. Redirect to the user details page or index page with a success message (in Malay)
      return redirect()->route('admin.users.show', $user)->with('success', 'Pengguna berjaya dikemaskini.');
    } catch (QueryException $e) {
      Log::error('Failed to update User ID ' . ($user->id ?? 'unknown') . ' due to database error: ' . $e->getMessage(), [
        'user_id' => $user->id ?? 'unknown',
        'request_data' => $request->all(),
        'updated_by' => Auth::id(),
        'exception' => $e,
      ]);
      // Changed error message to Malay
      return redirect()->back()->withInput()->with('error', 'Gagal mengemaskini Pengguna disebabkan ralat pangkalan data. Sila semak log untuk butiran lanjut.');
    } catch (Exception $e) {
      Log::error('An unexpected error occurred while updating User ID ' . ($user->id ?? 'unknown') . ': ' . $e->getMessage(), [
        'user_id' => $user->id ?? 'unknown',
        'request_data' => $request->all(),
        'updated_by' => Auth::id(),
        'exception' => $e,
      ]);
      // Changed error message to Malay
      return redirect()->back()->withInput()->with('error', 'Gagal mengemaskini Pengguna disebabkan ralat tidak dijangka.');
    }
  }

  /**
   * Remove the specified resource (User) from storage.
   * This method performs a SOFT DELETE.
   * Requires your User model to use the Illuminate\Database\Eloquent\SoftDeletes trait.
   *
   * @param  \App\Models\User  $user  The user instance resolved by route model binding.
   * @return \Illuminate\Http\RedirectResponse
   */
  public function destroy(User $user)
  {
    // Authorization is handled by authorizeResource in the constructor

    // IMPORTANT: Ensure your User model uses the `SoftDeletes` trait.
    // This method will perform a soft delete by default if the trait is used.

    try {
      $userId = $user->id; // Store ID before deletion
      $fullName = $user->full_name; // Store name before deletion

      // Perform soft delete if SoftDeletes trait is used
      $user->delete();

      // Optional: Log the deletion (this logs soft deletion as well)
      Log::info('User deleted (soft deleted) successfully', [
        'user_id' => $userId,
        'full_name' => $fullName,
        'deleted_by' => Auth::id()
      ]);

      // 2. Redirect with a success message (in Malay)
      return redirect()->route('admin.users.index')->with('success', 'Pengguna berjaya dibuang (soft delete).');
    } catch (QueryException $e) {
      // Catch specific database errors, e.g., if soft deletes are not configured but FK constraints exist
      Log::error('Failed to soft delete User ID ' . ($user->id ?? 'unknown') . ' due to database constraint: ' . $e->getMessage(), [
        'user_id' => $user->id ?? 'unknown',
        'error' => $e->getMessage(),
        'deleted_by' => Auth::id(),
      ]);
      // Changed error message to Malay
      return redirect()->route('admin.users.index')->with('error', 'Gagal membuang Pengguna disebabkan ralat pangkalan data. Pastikan Soft Delete diaktifkan pada model.');
    } catch (Exception $e) {
      // Catch any other unexpected errors
      Log::error('An unexpected error occurred while soft deleting User ID ' . ($user->id ?? 'unknown') . ': ' . $e->getMessage(), [
        'user_id' => $user->id ?? 'unknown',
        'error' => $e->getMessage(),
        'deleted_by' => Auth::id(),
      ]);
      // Changed error message to Malay
      return redirect()->route('admin.users.index')->with('error', 'Gagal membuang Pengguna disebabkan ralat tidak dijangka.');
    }
  }

  // Uncomment and implement restore/forceDelete methods if needed, adding authorization checks.
  // /**
  //  * Restore a soft-deleted user.
  //  * Requires the User model to use the SoftDeletes trait.
  //  *
  //  * @param int $id The ID of the soft-deleted user.
  //  * @return \Illuminate\Http\RedirectResponse
  //  */
  // public function restore($id)
  // {
  //     // Authorize restoration if using a Policy (e.g., $this->authorize('restore', User::class); or on the specific user)
  //     // $this->authorize('restore', User::class);

  //     $user = User::onlyTrashed()->findOrFail($id);

  //     // Authorize specific user restoration if needed
  //     // $this->authorize('restore', $user);

  //     try {
  //         $user->restore();

  //         Log::info('User restored successfully', [
  //             'user_id' => $user->id,
  //             'full_name' => $user->full_name,
  //             'restored_by' => Auth::id()
  //         ]);

  //         return redirect()->route('admin.users.index')->with('success', 'Pengguna berjaya dipulihkan.');

  //     } catch (Exception $e) {
  //          Log::error('An unexpected error occurred while restoring User ID ' . ($id ?? 'unknown') . ': ' . $e->getMessage(), [
  //              'user_id' => $id ?? 'unknown',
  //              'error' => $e->getMessage(),
  //              'restored_by' => Auth::id()
  //          ]);
  //         return redirect()->back()->with('error', 'Gagal memulihkan Pengguna disebabkan ralat tidak dijangka.');
  //     }
  // }

  // /**
  //  * Permanently delete a user.
  //  * Requires the User model to use the SoftDeletes trait.
  //  *
  //  * @param int $id The ID of the user to force delete.
  //  * @return \Illuminate\Http\RedirectResponse
  //  */
  // public function forceDelete($id)
  // {
  //      // Authorize force deletion if using a Policy (e.g., $this->authorize('forceDelete', User::class); or on the specific user)
  //      // $this->authorize('forceDelete', User::class);

  //     $user = User::onlyTrashed()->findOrFail($id);

  //      // Authorize specific user force deletion if needed
  //      // $this->authorize('forceDelete', $user);

  //     try {
  //         $userId = $user->id;
  //         $fullName = $user->full_name;

  //         $user->forceDelete(); // Permanently delete

  //         Log::info('User permanently deleted successfully', [
  //             'user_id' => $userId,
  //             'full_name' => $fullName,
  //             'force_deleted_by' => Auth::id()
  //         ]);

  //         return redirect()->route('admin.users.index')->with('success', 'Pengguna berjaya dibuang secara kekal.');

  //     } catch (QueryException $e) {
  //          Log::error('Failed to force delete User ID ' . ($id ?? 'unknown') . ' due to database constraint: ' . $e->getMessage(), [
  //              'user_id' => $id ?? 'unknown',
  //              'error' => $e->getMessage(),
  //              'force_deleted_by' => Auth::id()
  //          ]);
  //         return redirect()->back()->with('error', 'Gagal membuang Pengguna secara kekal disebabkan ralat pangkalan data.');
  //     } catch (Exception $e) {
  //          Log::error('An unexpected error occurred while force deleting User ID ' . ($id ?? 'unknown') . ': ' . $e->getMessage(), [
  //              'user_id' => $id ?? 'unknown',
  //              'error' => $e->getMessage(),
  //              'force_deleted_by' => Auth::id()
  //          ]);
  //         return redirect()->back()->with('error', 'Gagal membuang Pengguna secara kekal disebabkan ralat tidak dijangka.');
  //     }
  // }
}
