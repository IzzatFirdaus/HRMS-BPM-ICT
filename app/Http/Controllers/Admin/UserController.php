<?php

namespace App\Http\Controllers\Admin; // Ensure the namespace is correct for your project

use App\Http\Controllers\Controller; // Extend the base Controller
use App\Models\User; // Import the User model
use App\Models\Department; // Assuming Department model exists
use App\Models\Position; // Import the Position model
use App\Models\Grade; // Assuming Grade model exists
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash; // For hashing passwords
use Illuminate\Validation\Rule; // For using validation rules like Rule::in, Rule::unique
use Illuminate\Support\Facades\Log; // Import the Log facade for logging
use Illuminate\Support\Facades\Auth; // Import Auth facade for getting logged-in user
use Exception; // Import Exception for general errors
use Illuminate\Database\QueryException; // Import QueryException for database errors

class UserController extends Controller
{
  /**
   * Apply authentication middleware and authorizeResource for policy checks.
   */
  public function __construct()
  {
    // Apply authentication middleware
    $this->middleware('auth');
    // Apply authorization policy checks automatically
    $this->authorizeResource(User::class, 'user');
    // Note: When using authorizeResource, you don't need separate $this->authorize() calls
    // in each method (index, create, store, show, edit, update, destroy),
    // provided your UserPolicy is set up correctly.
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
    // Relationships ('department', 'position', 'grade') must match the method names in the User model
    // Use pagination to avoid loading too many users at once
    // If using soft deletes, you might want to include trashed users for admin view: ->withTrashed()
    $users = User::with(['department', 'position', 'grade'])
      ->latest() // Order by latest creation date
      ->paginate(15); // Paginate results

    // Return the view with the list of users
    // Assuming your admin user views are located in resources/views/admin/users
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

    // Load data needed for the user creation form (e.g., departments, positions, grades, service statuses)
    $departments = Department::all(); // Assuming Department model exists
    $positions = Position::all(); // Load Positions
    $grades = Grade::all(); // Assuming Grade model exists

    // Define enums as arrays. Match enum in migration.
    $serviceStatuses = ['permanent', 'contract', 'mystep', 'intern', 'other_agency'];
    $userStatuses = ['active', 'inactive', 'suspended']; // Match enum in migration

    // Return the view for creating a user
    return view('admin.users.create', compact('departments', 'positions', 'grades', 'serviceStatuses', 'userStatuses'));
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
      'identification_number' => 'required|string|unique:users,identification_number|max:20', // Corrected field name to match DB schema
      'personal_email' => 'required|email|unique:users,personal_email|max:255', // Personal email should be unique
      'mobile_number' => 'nullable|string|max:20', // Assuming mobile number is optional
      'department_id' => 'nullable|exists:departments,id', // Validate existence in departments table
      'position_id' => 'nullable|exists:positions,id', // Validate existence in positions table
      'grade_id' => 'nullable|exists:grades,id', // Validate existence in grades table
      'service_status' => ['required', Rule::in(['permanent', 'contract', 'mystep', 'intern', 'other_agency'])],
      'appointment_type' => 'nullable|string|max:255',
      // motac_email and user_id_assigned are likely assigned later via email provisioning workflow
      // 'motac_email' => 'nullable|email|unique:users,motac_email|max:255',
      // 'user_id_assigned' => 'nullable|string|unique:users,user_id_assigned|max:255',
      'password' => 'required|string|min:8|confirmed', // Admin sets initial password, requires confirmation
      'status' => ['required', Rule::in(['active', 'inactive', 'suspended'])], // User account status
      // If 'email' field from HRMS is used for system login, validate it here
      // 'email' => 'required|email|unique:users,email|max:255',
    ]);

    // 2. Create the new user in the database
    $user = User::create([
      'full_name' => $validatedData['full_name'],
      'identification_number' => $validatedData['identification_number'], // Use validated field name
      'personal_email' => $validatedData['personal_email'],
      'mobile_number' => $validatedData['mobile_number'],
      'department_id' => $validatedData['department_id'],
      'position_id' => $validatedData['position_id'],
      'grade_id' => $validatedData['grade_id'],
      'service_status' => $validatedData['service_status'],
      'appointment_type' => $validatedData['appointment_type'],
      'password' => Hash::make($validatedData['password']), // Hash the password!
      'status' => $validatedData['status'],
      // Assign default values or null for fields managed by provisioning workflow
      'motac_email' => null,
      'user_id_assigned' => null,
      // If 'email' field from HRMS is used for system login
      // 'email' => $validatedData['email'] ?? null, // Use validated field name
      // Map 'name' for HRMS template compatibility if needed, deriving from full_name
      'name' => $validatedData['full_name'],
    ]);

    // Optional: Log the creation
    Log::info('User created', [
      'user_id' => $user->id,
      'full_name' => $user->full_name,
      'created_by' => Auth::id() // Log the ID of the user who created this record
    ]);

    // 3. Redirect to the user index page with a success message
    // Changed message to Malay
    return redirect()->route('admin.users.index')->with('success', 'Pengguna berjaya ditambah.');
    // Or redirect to show: return redirect()->route('admin.users.show', $user)->with('success', 'Pengguna berjaya ditambah.');
  }

  /**
   * Display the specified resource (User).
   *
   * @param  \App\Models\User  $user  The user instance resolved by route model binding.
   * @return \Illuminate\View\View
   */
  public function show(User $user) // Use route model binding
  {
    // Authorization is handled by authorizeResource in the constructor

    // Eager load relationships needed for the show view
    $user->load(['department', 'position', 'grade']); // Load related data

    // Return the view to show user details
    return view('admin.users.show', compact('user'));
  }

  /**
   * Show the form for editing the specified resource (User).
   *
   * @param  \App\Models\User  $user  The user instance resolved by route model binding.
   * @return \Illuminate\View\View
   */
  public function edit(User $user) // Use route model binding
  {
    // Authorization is handled by authorizeResource in the constructor

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
   * Update the specified resource (User) in storage.
   *
   * @param  \Illuminate\Http\Request  $request  The incoming request.
   * @param  \App\Models\User  $user  The user instance resolved by route model binding.
   * @return \Illuminate\Http\RedirectResponse
   */
  public function update(Request $request, User $user) // Use route model binding
  {
    // Authorization is handled by authorizeResource in the constructor

    // 1. Validate the incoming request data for update
    // Use Rule::unique ignore to allow the user's current NRIC/email
    $validatedData = $request->validate([
      'full_name' => 'required|string|max:255',
      'identification_number' => ['required', 'string', 'max:20', Rule::unique('users', 'identification_number')->ignore($user->id)], // Corrected field name, ignore current user
      'personal_email' => ['required', 'email', 'max:255', Rule::unique('users', 'personal_email')->ignore($user->id)], // Personal email unique, ignore current user
      'mobile_number' => 'nullable|string|max:20',
      'department_id' => 'nullable|exists:departments,id',
      'position_id' => 'nullable|exists:positions,id',
      'grade_id' => 'nullable|exists:grades,id',
      'service_status' => ['required', Rule::in(['permanent', 'contract', 'mystep', 'intern', 'other_agency'])],
      'appointment_type' => 'nullable|string|max:255',
      // motac_email and user_id_assigned might be updatable via this form by admin, add unique ignore rule if so
      // 'motac_email' => ['nullable', 'email', 'max:255', Rule::unique('users', 'motac_email')->ignore($user->id)],
      // 'user_id_assigned' => ['nullable', 'string', 'max:255', Rule::unique('users', 'user_id_assigned')->ignore($user->id)],
      'password' => 'nullable|string|min:8|confirmed', // Allow updating password, but not required, requires confirmation if present
      'status' => ['required', Rule::in(['active', 'inactive', 'suspended'])], // User account status
      // If 'email' field from HRMS is used for system login
      // 'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
    ]);

    // 2. Update the user model
    // Prepare data for update, handling password separately
    $userData = [
      'full_name' => $validatedData['full_name'],
      'identification_number' => $validatedData['identification_number'], // Use validated field name
      'personal_email' => $validatedData['personal_email'],
      'mobile_number' => $validatedData['mobile_number'],
      'department_id' => $validatedData['department_id'],
      'position_id' => $validatedData['position_id'],
      'grade_id' => $validatedData['grade_id'],
      'service_status' => $validatedData['service_status'],
      'appointment_type' => $validatedData['appointment_type'],
      'status' => $validatedData['status'],
      // If 'email' field from HRMS is used for system login
      // 'email' => $validatedData['email'] ?? $user->email, // Use validated field name or existing
      // Map 'name' for HRMS template compatibility if needed
      'name' => $validatedData['full_name'],
    ];

    // Only update password if it was provided and validated
    if (!empty($validatedData['password'])) {
      $userData['password'] = Hash::make($validatedData['password']);
    }

    $user->update($userData);

    // Optional: Log the update
    Log::info('User updated', [
      'user_id' => $user->id,
      'full_name' => $user->full_name,
      'updated_by' => Auth::id() // Log the ID of the user who updated this record
    ]);

    // 3. Redirect to the user details page or index page
    // Changed message to Malay
    return redirect()->route('admin.users.show', $user)->with('success', 'Pengguna berjaya dikemaskini.');
    // Or redirect to index: return redirect()->route('admin.users.index')->with('success', 'Pengguna berjaya dikemaskini.');
  }

  /**
   * Remove the specified resource (User) from storage.
   * This method should ideally perform a SOFT DELETE to maintain data integrity
   * for related records like email applications, loan transactions, approvals, etc.
   * Ensure your User model uses the Illuminate\Database\Eloquent\SoftDeletes trait.
   *
   * @param  \App\Models\User  $user  The user instance resolved by route model binding.
   * @return \Illuminate\Http\RedirectResponse
   */
  public function destroy(User $user) // Use route model binding
  {
    // Authorization is handled by authorizeResource in the constructor

    // IMPORTANT: For data integrity, soft deleting users is highly recommended
    // rather than permanent deletion, especially if users have associated
    // records (applications, loans, approvals, etc.).
    // Ensure your User model uses the `SoftDeletes` trait.

    try {
      $userId = $user->id; // Store ID before deletion
      $fullName = $user->full_name; // Store name before deletion

      // Perform soft delete if SoftDeletes trait is used, otherwise permanent delete
      $user->delete();

      // Optional: Log the deletion (this logs soft deletion as well)
      Log::info('User deleted (soft deleted)', [
        'user_id' => $userId,
        'full_name' => $fullName,
        'deleted_by' => Auth::id() // Log the ID of the user who initiated deletion
      ]);

      // 2. Redirect with a success message
      // Changed message to Malay
      return redirect()->route('admin.users.index')->with('success', 'Pengguna berjaya dibuang (soft delete).');
    } catch (QueryException $e) {
      Log::error('Failed to delete User ID ' . ($user->id ?? 'unknown') . ' due to database constraint: ' . $e->getMessage(), [
        'user_id' => $user->id ?? 'unknown',
        'error' => $e->getMessage(),
        'deleted_by' => Auth::id()
      ]);
      // Changed error message to Malay
      return redirect()->route('admin.users.index')->with('error', 'Gagal membuang Pengguna disebabkan ralat pangkalan data. Pastikan Soft Delete diaktifkan pada model.');
    } catch (Exception $e) {
      Log::error('An unexpected error occurred while deleting User ID ' . ($user->id ?? 'unknown') . ': ' . $e->getMessage(), [
        'user_id' => $user->id ?? 'unknown',
        'error' => $e->getMessage(),
        'deleted_by' => Auth::id()
      ]);
      // Changed error message to Malay
      return redirect()->route('admin.users.index')->with('error', 'Gagal membuang Pengguna disebabkan ralat tidak dijangka.');
    }
  }

  // You can add other admin-specific methods here if needed,
  // e.g., for assigning roles/permissions, restoring soft-deleted users, force deleting users, etc.

  /**
   * Restore a soft-deleted user.
   * Requires the User model to use the SoftDeletes trait.
   *
   * @param int $id The ID of the soft-deleted user.
   * @return \Illuminate\Http\RedirectResponse
   */
  // public function restore($id)
  // {
  //     // Authorize restoration if using a Policy
  //     // $this->authorize('restore', User::class); // Or specific policy logic

  //     $user = User::onlyTrashed()->findOrFail($id);

  //     // Authorize specific user restoration
  //     // $this->authorize('restore', $user);

  //     $user->restore();

  //     Log::info('User restored', [
  //         'user_id' => $user->id,
  //         'full_name' => $user->full_name,
  //         'restored_by' => Auth::id()
  //     ]);

  //     return redirect()->route('admin.users.index')->with('success', 'Pengguna berjaya dipulihkan.');
  // }

  /**
   * Permanently delete a user.
   * Requires the User model to use the SoftDeletes trait.
   *
   * @param int $id The ID of the user to force delete.
   * @return \Illuminate\Http\RedirectResponse
   */
  // public function forceDelete($id)
  // {
  //      // Authorize force deletion if using a Policy
  //      // $this->authorize('forceDelete', User::class); // Or specific policy logic

  //     $user = User::onlyTrashed()->findOrFail($id);

  //      // Authorize specific user force deletion
  //      // $this->authorize('forceDelete', $user);

  //     try {
  //         $userId = $user->id;
  //         $fullName = $user->full_name;

  //         $user->forceDelete(); // Permanently delete

  //         Log::info('User permanently deleted', [
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
  //         return redirect()->route('admin.users.index')->with('error', 'Gagal membuang Pengguna secara kekal disebabkan ralat pangkalan data.');
  //     } catch (Exception $e) {
  //          Log::error('An unexpected error occurred while force deleting User ID ' . ($id ?? 'unknown') . ': ' . $e->getMessage(), [
  //              'user_id' => $id ?? 'unknown',
  //              'error' => $e->getMessage(),
  //              'force_deleted_by' => Auth::id()
  //          ]);
  //         return redirect()->route('admin.users.index')->with('error', 'Gagal membuang Pengguna secara kekal disebabkan ralat tidak dijangka.');
  //     }
  // }
}
