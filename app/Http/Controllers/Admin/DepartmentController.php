<?php

namespace App\Http\Controllers\Admin; // Ensure the namespace is correct for your project

use App\Http\Controllers\Controller; // Extend the base Controller
use App\Models\Department; // Import the Department model
use Illuminate\Http\Request;
use Illuminate\Validation\Rule; // Import Rule for enum/unique validation
use Illuminate\Support\Facades\Log; // Import Log facade for logging
use Illuminate\Support\Facades\Auth; // Import Auth facade for getting logged-in user


class DepartmentController extends Controller
{
  /**
   * Display a listing of the resource.
   */
  public function index()
  {
    // Optional: Add authorization check using a Policy
    // $this->authorize('viewAny', Department::class); // Assumes a DepartmentPolicy exists

    // Fetch all departments and efficiently load the count of related users (members)
    // Use Eloquent's withCount() method with the relationship name 'users' as defined in your Department model
    $departments = Department::withCount('users')->get();

    // Return the view with the list of departments
    // Assuming your admin department views are located in resources/views/admin/departments
    return view('admin.departments.index', compact('departments'));
  }

  /**
   * Show the form for creating a new resource.
   */
  public function create()
  {
    // Optional: Add authorization check using a Policy
    // $this->authorize('create', Department::class); // Assumes a DepartmentPolicy exists

    // Define possible branch types for the form.
    // Ideally, this should be sourced from a central place like a config file,
    // an Enum class (PHP 8.1+), or the database if they are dynamic.
    $branchTypes = ['state', 'headquarters']; // Match the enum in your migration

    // Return the view for creating a department
    return view('admin.departments.create', compact('branchTypes'));
  }

  /**
   * Store a newly created resource in storage.
   */
  public function store(Request $request)
  {
    // Optional: Add authorization check using a Policy
    // $this->authorize('create', Department::class); // Assumes a DepartmentPolicy exists

    // 1. Validate the incoming request data
    $validatedData = $request->validate([
      'name' => 'required|string|max:255|unique:departments,name', // Department name should be unique
      'branch_type' => ['required', Rule::in(['state', 'headquarters'])], // Validate against defined enum values
      'code' => 'nullable|string|max:10|unique:departments,code', // Assuming 'code' is optional and unique
      'description' => 'nullable|string|max:500', // Added description field validation as per your example
    ]);

    // 2. Create the new department in the database
    $department = Department::create($validatedData);

    // Optional: Log the creation
    // Get the logged-in user's ID safely for logging using Auth:: facade
    $loggedInUserId = Auth::id(); // Auth::id() is a shorthand for Auth::user()->id

    Log::info('Department created', [
      'department_id' => $department->id,
      'name' => $department->name,
      'created_by' => $loggedInUserId
    ]);

    // 3. Redirect to the department index page with a success message
    // Assuming you have a named route for the admin department index like 'admin.departments.index'
    return redirect()->route('admin.departments.index')->with('success', 'Bahagian/Unit berjaya ditambah.'); // Malay success message
  }

  /**
   * Display the specified resource.
   */
  public function show(Department $department)
  {
    // Optional: Add authorization check using a Policy
    // $this->authorize('view', $department); // Assumes a DepartmentPolicy exists

    // Return the view to show department details
    return view('admin.departments.show', compact('department'));
  }

  /**
   * Show the form for editing the specified resource.
   */
  public function edit(Department $department)
  {
    // Optional: Add authorization check using a Policy
    // $this->authorize('update', $department); // Assumes a DepartmentPolicy exists

    // Define possible branch types for the form
    $branchTypes = ['state', 'headquarters']; // Match the enum in your migration

    // Return the view for editing a department
    return view('admin.departments.edit', compact('department', 'branchTypes'));
  }

  /**
   * Update the specified resource in storage.
   */
  public function update(Request $request, Department $department)
  {
    // Optional: Add authorization check using a Policy
    // $this->authorize('update', $department); // Assumes a DepartmentPolicy exists

    // 1. Validate the incoming request data for update
    // Use Rule::unique ignore to allow the department's current name/code
    $validatedData = $request->validate([
      'name' => ['required', 'string', 'max:255', Rule::unique('departments', 'name')->ignore($department->id)],
      'branch_type' => ['required', Rule::in(['state', 'headquarters'])],
      'code' => ['nullable', 'string', 'max:10', Rule::unique('departments', 'code')->ignore($department->id)],
      'description' => 'nullable|string|max:500', // Added description field validation as per your example
    ]);

    // 2. Update the department model
    $department->update($validatedData);

    // Optional: Log the update
    // Get the logged-in user's ID safely for logging using Auth:: facade
    $loggedInUserId = Auth::id(); // Auth::id() is a shorthand for Auth::user()->id

    Log::info('Department updated', [
      'department_id' => $department->id,
      'name' => $department->name,
      'updated_by' => $loggedInUserId
    ]);

    // 3. Redirect to the department details page or index page
    return redirect()->route('admin.departments.show', $department)->with('success', 'Bahagian/Unit berjaya dikemaskini.'); // Malay success message
    // Or redirect to index: return redirect()->route('admin.departments.index')->with('success', 'Department updated successfully.');
  }

  /**
   * Remove the specified resource from storage.
   */
  public function destroy(Department $department)
  {
    // Optional: Add authorization check using a Policy
    // $this->authorize('delete', $department); // Assumes a DepartmentPolicy exists

    // Add a check to prevent deleting departments with associated users
    if ($department->users()->count() > 0) {
      return redirect()->route('admin.departments.index')->with('error', 'Tidak dapat memadam Bahagian/Unit kerana terdapat pengguna yang berkaitan.'); // Malay error message
    }

    // 1. Delete the department
    $department->delete();

    // Optional: Log the deletion
    // Get the logged-in user's ID safely for logging using Auth:: facade
    $loggedInUserId = Auth::id(); // Auth::id() is a shorthand for Auth::user()->id

    Log::info('Department deleted', [
      'department_id' => $department->id,
      'name' => $department->name,
      'deleted_by' => $loggedInUserId
    ]);

    // 2. Redirect with a success message
    return redirect()->route('admin.departments.index')->with('success', 'Bahagian/Unit berjaya dibuang.'); // Malay success message
  }
}
