<?php

namespace App\Http\Controllers; // Ensure the namespace is correct for your project. Consider App\Http\Controllers\Admin if user management is an admin function.

use App\Http\Controllers\Controller; // Extend the base Controller
use App\Models\User; // Import the User model
use App\Models\Department; // Import Department for edit/create forms
use App\Models\Position; // Import Position for edit/create forms
use App\Models\Grade; // Import Grade for edit/create forms
use Illuminate\Http\Request; // Standard Request object (less needed with Form Requests)
use Illuminate\Support\Facades\Auth; // Import Auth facade
use Illuminate\Support\Facades\Gate; // Import Gate (less needed with Policies)
use Illuminate\Support\Facades\Log; // Import Log facade for logging
use Exception; // Import Exception for general errors
use Illuminate\Database\QueryException; // Import QueryException for database errors

// Assuming a UserService handles user creation, update, and deletion logic
use App\Services\UserService; // Import UserService
// Assuming Form Requests for validation
use App\Http\Requests\StoreUserRequest; // Import StoreUserRequest (create this if it doesn't exist)
use App\Http\Requests\UpdateUserRequest; // Import UpdateUserRequest (create this if it doesn't exist)


class UserController extends Controller
{
  protected $userService; // Use the UserService

  /**
   * Inject the UserService and apply authentication/authorization middleware.
   *
   * @param \App\Services\UserService $userService The user service instance.
   */
  public function __construct(UserService $userService) // Inject UserService
  {
    // Apply authentication middleware to all methods in this controller
    $this->middleware('auth');

    // Apply authorization policy checks automatically for resource methods (index, create, store, show, edit, update, destroy)
    // Assumes a UserPolicy exists and is registered.
    // Policy methods: viewAny, view, create, update, delete
    $this->authorizeResource(User::class, 'user'); // Use 'user' as parameter name

    $this->userService = $userService; // Assign the injected service
  }

  /**
   * Display a listing of the users.
   * Typically restricted to administrators or specific roles.
   *
   * @return \Illuminate\View\View
   */
  public function index(): \Illuminate\View\View // Add return type hint
  {
    // Authorization handled by authorizeResource in the constructor ('viewAny').
    // The policy's 'viewAny' method should check if the user has permission to list users.

    // Log viewing user index
    Log::info('User accessing user index page.', [
      'user_id' => Auth::id(),
      'ip_address' => request()->ip(),
    ]);

    // Fetch all users with relationships.
    $users = User::query()
      ->with('department', 'position', 'grade') // Eager load relationships
      ->get(); // Fetch all data (consider pagination for many users)

    // IMPORTANT: For a large number of users, replace get() with pagination:
    // $users = User::with('department', 'position', 'grade')->paginate(20); // Paginate for better performance

    // Return the view with the list of users
    // Ensure your view file name matches: resources/views/users/index.blade.php
    return view('users.index', compact('users'));
  }

  /**
   * Show the form for creating a new user.
   * Typically restricted to administrators.
   *
   * @return \Illuminate\View\View
   */
  public function create(): \Illuminate\View\View // Add return type hint
  {
    // Authorization handled by authorizeResource in the constructor ('create').
    // The policy's 'create' method should check if the user has permission to create users.

    // Log accessing user creation form
    Log::info('User accessing user creation form.', [
      'user_id' => Auth::id(),
      'ip_address' => request()->ip(),
    ]);

    // Load data needed for the form (e.g., lists for dropdowns)
    $departments = Department::all(); // Assuming Department model exists
    $positions = Position::all(); // Assuming Position model exists
    $grades = Grade::all(); // Assuming Grade model exists

    // Return the view for creating a user
    // Ensure your view file name matches: resources/views/users/create.blade.php
    return view('users.create', compact('departments', 'positions', 'grades'));
  }

  /**
   * Store a newly created user in storage.
   * Uses the StoreUserRequest Form Request for validation.
   * Delegates the creation logic to the UserService.
   * Typically restricted to administrators.
   *
   * @param  \App\Http\Requests\StoreUserRequest  $request  The validated incoming request.
   * @return \Illuminate\Http\RedirectResponse
   */
  public function store(StoreUserRequest $request): \Illuminate\Http\RedirectResponse // Use Form Request, add return type hint
  {
    // Authorization handled by authorizeResource in the constructor ('create').
    // Validation handled automatically by StoreUserRequest.

    // Log creation attempt
    Log::info('Attempting to create new user.', [
      'user_id' => Auth::id(), // Log the admin user creating the new user
      'ip_address' => $request->ip(),
      'validated_data_keys' => array_keys($request->validated()), // Log keys, not values for sensitive data
    ]);

    try {
      // Delegate the creation logic to the UserService.
      // The service should handle:
      // - Creating the User model instance with validated data.
      // - Hashing the password.
      // - Assigning roles/permissions (if applicable).
      // - Saving the user record to the database.
      $user = $this->userService->createUser($request->validated()); // Assumes createUser method exists and accepts validated data

      // Log successful creation
      Log::info('User created successfully.', [
        'new_user_id' => $user->id,
        'new_user_email' => $user->email, // Log the new user's email or identifier
        'created_by_user_id' => Auth::id(),
      ]);

      // Redirect to the 'show' route for the newly created user or the index page
      // Changed message to Malay
      return redirect()->route('users.show', $user)
        ->with('success', 'Pengguna baru berjaya didaftarkan.'); // Malay success message

    } catch (Exception $e) {
      // Log any exceptions during creation
      Log::error('Error creating user.', [
        'created_by_user_id' => Auth::id(),
        'error' => $e->getMessage(),
        'ip_address' => $request->ip(),
        'validated_data' => $request->validated(), // Log validated data on error for debugging
      ]);
      // Changed message to Malay
      return redirect()->back()->withInput()->with('error', 'Gagal mendaftar pengguna baru disebabkan ralat: ' . $e->getMessage()); // Malay error message
    }
  }


  /**
   * Display the specified user.
   * Typically restricted to administrators, or a user viewing their own profile.
   *
   * @param  \App\Models\User  $user  The user instance resolved by route model binding.
   * @return \Illuminate\View\View
   */
  public function show(User $user): \Illuminate\View\View // Add return type hint
  {
    // Authorization handled by authorizeResource in the constructor ('view' on the specific $user).
    // The policy's 'view' method should verify if the authenticated user can view this user's profile
    // (e.g., is the user themselves, is an admin).

    // Log viewing user profile
    Log::info('User viewing user profile.', [
      'viewed_user_id' => $user->id,
      'viewed_by_user_id' => Auth::id(),
      'ip_address' => request()->ip(),
    ]);

    // Eager load relationships needed for the show view
    $user->load('department', 'position', 'grade');

    // Return the view to show user details
    // Ensure your view file name matches: resources/views/users/show.blade.php
    return view('users.show', compact('user'));
  }


  /**
   * Show the form for editing the specified user.
   * Typically restricted to administrators, or a user editing their own profile.
   *
   * @param  \App\Models\User  $user  The user instance resolved by route model binding.
   * @return \Illuminate\View\View
   */
  public function edit(User $user): \Illuminate\View\View // Add return type hint
  {
    // Authorization handled by authorizeResource in the constructor ('update' on the specific $user).
    // The policy's 'update' method should verify if the authenticated user can edit this user's profile
    // (e.g., is the user themselves, is an admin).

    // Log accessing user edit form
    Log::info('User accessing user edit form.', [
      'edited_user_id' => $user->id,
      'accessed_by_user_id' => Auth::id(),
      'ip_address' => request()->ip(),
    ]);

    // Load data needed for the form (e.g., lists for dropdowns if editing related fields)
    $departments = Department::all();
    $positions = Position::all();
    $grades = Grade::all();
    // You might also need to load roles if role assignment is done here
    // $roles = \Spatie\Permission\Models\Role::all(); // Assuming Spatie/Laravel-Permission

    // Return the view for editing, passing the user data and supporting lists
    // Ensure your view file name matches: resources/views/users/edit.blade.php
    return view('users.edit', compact('user', 'departments', 'positions', 'grades'/*, 'roles'*/));
  }


  /**
   * Update the specified user in storage.
   * Uses the UpdateUserRequest Form Request for validation.
   * Delegates the update logic to the UserService.
   * Typically restricted to administrators, or a user updating their own profile.
   *
   * @param  \App\Http\Requests\UpdateUserRequest  $request  The validated incoming request.
   * @param  \App\Models\User  $user  The user instance resolved by route model binding.
   * @return \Illuminate\Http\RedirectResponse
   */
  public function update(UpdateUserRequest $request, User $user): \Illuminate\Http\RedirectResponse // Use Form Request, add return type hint
  {
    // Authorization handled by authorizeResource in the constructor ('update' on the specific $user).
    // Validation handled automatically by UpdateUserRequest.

    // Log update attempt
    Log::info('Attempting to update user profile.', [
      'updated_user_id' => $user->id,
      'updated_by_user_id' => Auth::id(),
      'ip_address' => $request->ip(),
      'validated_data_keys' => array_keys($request->validated()), // Log keys, not values for sensitive data
    ]);

    try {
      // Delegate the update logic to the UserService.
      // The service should handle:
      // - Updating the user record with the validated data.
      // - Handling specific fields like password (if allowed via this form).
      // - Handling role/permission updates (if allowed via this form).
      // - Saving changes.
      $updated = $this->userService->updateUser($user, $request->validated()); // Assumes updateUser method exists

      if ($updated) {
        // Log successful update
        Log::info('User profile updated successfully.', [
          'updated_user_id' => $user->id,
          'updated_by_user_id' => Auth::id(),
        ]);
        // Changed message to Malay
        return redirect()->route('users.show', $user)
          ->with('success', 'Maklumat pengguna berjaya dikemaskini.'); // Malay success message
      } else {
        // Log failure (might indicate a service-level rule prevented update or no changes)
        Log::warning('User profile update failed via service (no changes or service rule).', [
          'updated_user_id' => $user->id,
          'updated_by_user_id' => Auth::id(),
        ]);
        // Changed message to Malay
        return redirect()->back()->withInput()->with('error', 'Gagal mengemaskini maklumat pengguna.'); // Malay error message
      }
    } catch (Exception $e) {
      // Log any exceptions during update
      Log::error('Error updating user profile.', [
        'updated_user_id' => $user->id,
        'updated_by_user_id' => Auth::id(),
        'error' => $e->getMessage(),
        'ip_address' => $request->ip(),
        'validated_data' => $request->validated(), // Log validated data on error for debugging
      ]);
      // Changed message to Malay
      return redirect()->back()->withInput()->with('error', 'Gagal mengemaskini maklumat pengguna disebabkan ralat: ' . $e->getMessage()); // Malay error message
    }
  }

  /**
   * Remove the specified user from storage.
   * Typically restricted to administrators.
   * Delegates deletion logic to the UserService.
   *
   * @param  \App\Models\User  $user  The user instance resolved by route model binding.
   * @return \Illuminate\Http\RedirectResponse
   */
  public function destroy(User $user): \Illuminate\Http\RedirectResponse // Add return type hint
  {
    // Authorization handled by authorizeResource in the constructor ('delete' on the specific $user).
    // The policy's 'delete' method should check if the authenticated user has permission to delete users
    // and potentially prevent self-deletion or deletion of super-admins.

    // Log deletion attempt
    Log::info('Attempting to delete user.', [
      'deleted_user_id' => $user->id,
      'deleted_by_user_id' => Auth::id(),
      'ip_address' => request()->ip(),
    ]);

    // IMPORTANT: Implement checks here or in the UserService before deleting a user.
    // Considerations:
    // 1. Prevent deleting the currently authenticated user ($user->id === Auth::id()).
    // 2. Prevent deleting users with certain roles (e.g., administrators).
    // 3. What happens to records owned by this user (applications, transactions)?
    //    - Option A: Prevent deletion if related records exist (requires manual deletion or transfer first).
    //    - Option B: Soft delete the user and associated records.
    //    - Option C: Delete related records (CAUTION!).
    //    - Option D: Reassign related records to a generic user.

    // Example check (prevent self-deletion - this might also be in the Policy):
    // if ($user->id === Auth::id()) {
    //      Log::warning('Attempted self-deletion.', ['user_id' => Auth::id()]);
    //      return redirect()->back()->with('error', 'Anda tidak boleh memadam akaun anda sendiri.'); // Malay error
    // }


    try {
      // Delegate deletion logic to the UserService.
      // The service should handle soft deletion, related record management,
      // and potentially throwing exceptions if business rules prevent deletion.
      $deleted = $this->userService->deleteUser($user); // Assumes deleteUser method exists

      if ($deleted) {
        // Log successful deletion
        Log::info('User deleted successfully via service.', [
          'deleted_user_id' => $user->id, // Note: If soft deleted, ID is still valid
          'deleted_by_user_id' => Auth::id(),
        ]);
        // Changed message to Malay
        return redirect()->route('users.index')
          ->with('success', 'Pengguna berjaya dibuang.'); // Malay success message
      } else {
        // Log failure (might indicate service-level rule prevented deletion)
        Log::warning('User deletion failed via service.', [
          'deleted_user_id' => $user->id,
          'deleted_by_user_id' => Auth::id(),
        ]);
        // Changed message to Malay
        return redirect()->back()->with('error', 'Gagal membuang pengguna.'); // Malay error message
      }
    } catch (Exception $e) {
      // Log any exceptions during deletion (e.g., from UserService business rule checks)
      Log::error('Error deleting user.', [
        'deleted_user_id' => $user->id,
        'deleted_by_user_id' => Auth::id(),
        'error' => $e->getMessage(),
        'ip_address' => request()->ip(),
      ]);
      // Changed message to Malay
      return redirect()->back()->with('error', 'Gagal membuang pengguna disebabkan ralat: ' . $e->getMessage()); // Malay error message
    }
  }
}
