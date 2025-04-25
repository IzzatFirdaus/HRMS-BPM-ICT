<?php

namespace App\Http\Controllers\Admin; // Ensure the namespace is correct for your project

use App\Http\Controllers\Controller; // Extend the base Controller
use App\Models\Department; // Import the Department model
use Illuminate\Http\Request;
use Illuminate\Validation\Rule; // Import Rule for enum validation
use Illuminate\Support\Facades\Log; // Import Log facade for logging (optional but good practice)
use Illuminate\Support\Facades\Gate; // For manual authorization checks if not using policies only
use Illuminate\Support\Facades\Auth; // Import Auth facade - **This is already imported**

class DepartmentController extends Controller
{
  /**
   * Display a listing of the resource.
   */
  public function index()
  {
    // Optional: Add authorization check using a Policy
    // $this->authorize('viewAny', Department::class); // Assuming a DepartmentPolicy exists with a viewAny method

    // Fetch all departments. Consider pagination if you expect many departments.
    $departments = Department::all(); // Or Department::paginate(15);

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
    // $this->authorize('create', Department::class); // Assuming a DepartmentPolicy exists with a create method

    // Define possible branch types for the form.
    // It's often better to define these in a config file or an Enum class if using PHP 8.1+
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
    // $this->authorize('create', Department::class); // Assuming a DepartmentPolicy exists with a create method

    // 1. Validate the incoming request data
    $validatedData = $request->validate([
      'name' => 'required|string|max:255|unique:departments,name', // Department name should be unique
      'branch_type' => ['required', Rule::in(['state', 'headquarters'])], // Validate against defined enum values
      'code' => 'nullable|string|max:10|unique:departments,code', // Assuming 'code' is optional and unique
      'description' => 'nullable|string|max:500', // Added description field validation
    ]);

    // 2. Create the new department in the database
    $department = Department::create($validatedData);

    // Optional: Log the creation
    // Get the logged-in user's ID safely for logging using Auth:: facade
    $loggedInUserId = Auth::check() ? Auth::user()->id : null; // Changed auth()-> to Auth::
    Log::info('Department created', [
      'department_id' => $department->id,
      'name' => $department->name,
      'created_by' => $loggedInUserId // Use the variable
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
    // $this->authorize('view', $department); // Assuming a DepartmentPolicy exists with a view method

    // Return the view to show department details
    return view('admin.departments.show', compact('department'));
  }

  /**
   * Show the form for editing the specified resource.
   */
  public function edit(Department $department)
  {
    // Optional: Add authorization check using a Policy
    // $this->authorize('update', $department); // Assuming a DepartmentPolicy exists with an update method

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
    // $this->authorize('update', $department); // Assuming a DepartmentPolicy exists with an update method

    // 1. Validate the incoming request data for update
    // Use Rule::unique ignore to allow the department's current name/code
    $validatedData = $request->validate([
      'name' => ['required', 'string', 'max:255', Rule::unique('departments', 'name')->ignore($department->id)],
      'branch_type' => ['required', Rule::in(['state', 'headquarters'])],
      'code' => ['nullable', 'string', 'max:10', Rule::unique('departments', 'code')->ignore($department->id)],
      'description' => 'nullable|string|max:500', // Added description field validation
    ]);

    // 2. Update the department model
    $department->update($validatedData);

    // Optional: Log the update
    // Get the logged-in user's ID safely for logging using Auth:: facade
    $loggedInUserId = Auth::check() ? Auth::user()->id : null; // Changed auth()-> to Auth::
    Log::info('Department updated', [
      'department_id' => $department->id,
      'name' => $department->name,
      'updated_by' => $loggedInUserId // Use the variable
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
    // $this->authorize('delete', $department); // Assuming a DepartmentPolicy exists with a delete method

    // 1. Delete the department (consider checking for related records first if necessary)
    $department->delete();

    // Optional: Log the deletion
    // Get the logged-in user's ID safely for logging using Auth:: facade
    $loggedInUserId = Auth::check() ? Auth::user()->id : null; // Changed auth()-> to Auth::
    Log::info('Department deleted', [
      'department_id' => $department->id,
      'name' => $department->name,
      'deleted_by' => $loggedInUserId // Use the variable
    ]);


    // 2. Redirect with a success message
    return redirect()->route('admin.departments.index')->with('success', 'Bahagian/Unit berjaya dibuang.'); // Malay success message
  }
}
