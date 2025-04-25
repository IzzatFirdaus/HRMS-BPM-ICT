<?php

namespace App\Http\Controllers\Admin; // Ensure the namespace is correct for your project (Admin)

use App\Http\Controllers\Controller; // Extend the base Controller
use App\Models\Grade; // Import the Grade model
use Illuminate\Http\Request; // Standard Request object
use Illuminate\Validation\Rule; // Import Rule for validation rules (e.g., unique)
use Illuminate\Support\Facades\Auth; // Import Auth facade if needed
use Illuminate\Support\Facades\Gate; // Import Gate if needed (Policies are preferred with $this->authorize)

// Import Form Requests if you create them for validation
// use App\Http\Requests\Admin\StoreGradeRequest;
// use App\Http\Requests\Admin\UpdateGradeRequest;


class GradeController extends Controller
{
  /**
   * Apply authentication and admin middleware to all methods in this controller.
   * You might also apply specific permissions via middleware or policies.
   */
  public function __construct()
  {
    // Apply authentication middleware
    $this->middleware('auth');
    // Apply admin middleware if this controller is only for admin access
    // $this->middleware('admin'); // Assuming you have an 'admin' middleware alias
    // Or apply a permission check middleware
    // $this->middleware('can:manage-grades'); // Assuming a Spatie permission or Gate
  }

  /**
   * Display a listing of the resource (Grades).
   *
   * @return \Illuminate\View\View
   */
  public function index()
  {
    // Authorize if the user can view any grades (using a Policy)
    $this->authorize('viewAny', Grade::class); // Assuming a GradePolicy exists

    // Fetch all grades, ordered by level
    $grades = Grade::orderBy('level')->paginate(10); // Order by level as it's a key attribute

    // Return the view with the list of grades
    // Ensure your view file name matches: resources/views/admin/grades/index.blade.php
    return view('admin.grades.index', compact('grades'));
  }

  /**
   * Show the form for creating a new resource (Grade).
   *
   * @return \Illuminate\View\View
   */
  public function create()
  {
    // Authorize if the user can create grades
    $this->authorize('create', Grade::class); // Assuming a GradePolicy exists

    // Return the view for creating a grade
    // Ensure your view file name matches: resources/views/admin/grades/create.blade.php
    return view('admin.grades.create');
  }

  /**
   * Store a newly created resource (Grade) in storage.
   *
   * @param  \Illuminate\Http\Request  $request  The incoming request.
   * @return \Illuminate\Http\RedirectResponse
   */
  public function store(Request $request) // Use StoreGradeRequest if created
  {
    // Authorize if the user can create grades
    $this->authorize('create', Grade::class); // Assuming a GradePolicy exists

    // 1. Validate the incoming request data
    // Adjust validation rules based on the fields in your 'grades' table and your form
    // If using a Form Request (StoreGradeRequest), validation is handled there.
    $validatedData = $request->validate([
      'name' => 'required|string|max:255|unique:grades,name', // Grade name should be unique
      'level' => 'required|integer|min:1|unique:grades,level', // Grade level should be unique and a positive integer
      'description' => 'nullable|string', // Description is optional
      // Add validation for any other grade fields
    ]);

    // 2. Create the new grade record in the database
    $grade = Grade::create($validatedData);

    // 3. Redirect to the grade index page or show page with a success message
    return redirect()->route('admin.grades.index')->with('success', 'Grade added successfully!');
    // Or redirect to show: return redirect()->route('admin.grades.show', $grade)->with('success', 'Grade added successfully!');
  }

  /**
   * Display the specified resource (Grade).
   *
   * @param  \App\Models\Grade  $grade  The grade instance resolved by route model binding.
   * @return \Illuminate\View\View
   */
  public function show(Grade $grade) // Use route model binding
  {
    // Authorize if the user can view this specific grade
    $this->authorize('view', $grade); // Assuming a GradePolicy exists

    // Eager load relationships if needed for the show view (e.g., users assigned to this grade)
    // $grade->load('users'); // Assuming a 'users' relationship on the Grade model

    // Return the view to show grade details
    // Ensure your view file name matches: resources/views/admin/grades/show.blade.php
    return view('admin.grades.show', compact('grade'));
  }

  /**
   * Show the form for editing the specified resource (Grade).
   *
   * @param  \App\Models\Grade  $grade  The grade instance resolved by route model binding.
   * @return \Illuminate\View\View
   */
  public function edit(Grade $grade) // Use route model binding
  {
    // Authorize if the user can update this grade
    $this->authorize('update', $grade); // Assuming a GradePolicy exists

    // Return the view for editing a grade
    // Ensure your view file name matches: resources/views/admin/grades/edit.blade.php
    return view('admin.grades.edit', compact('grade'));
  }

  /**
   * Update the specified resource (Grade) in storage.
   *
   * @param  \Illuminate\Http\Request  $request  The incoming request.
   * @param  \App\Models\Grade  $grade  The grade instance resolved by route model binding.
   * @return \Illuminate\Http\RedirectResponse
   */
  public function update(Request $request, Grade $grade) // Use UpdateGradeRequest if created
  {
    // Authorize if the user can update this grade
    $this->authorize('update', $grade); // Assuming a GradePolicy exists

    // 1. Validate the incoming request data for update
    // Use Rule::unique ignore to allow the grade's current name/level
    // If using a Form Request (UpdateGradeRequest), validation is handled there.
    $validatedData = $request->validate([
      'name' => ['required', 'string', 'max:255', Rule::unique('grades', 'name')->ignore($grade->id)],
      'level' => ['required', 'integer', 'min:1', Rule::unique('grades', 'level')->ignore($grade->id)],
      'description' => 'nullable|string',
      // Add validation for any other grade fields
    ]);

    // 2. Update the grade model
    $grade->update($validatedData);

    // 3. Redirect to the grade show page or index page with a success message
    return redirect()->route('admin.grades.show', $grade)->with('success', 'Grade updated successfully.');
    // Or redirect to index: return redirect()->route('admin.grades.index')->with('success', 'Grade updated successfully.');
  }

  /**
   * Remove the specified resource (Grade) from storage.
   *
   * @param  \App\Models\Grade  $grade  The grade instance resolved by route model binding.
   * @return \Illuminate\Http\RedirectResponse
   */
  public function destroy(Grade $grade) // Use route model binding
  {
    // Authorize if the user can delete this grade
    $this->authorize('delete', $grade); // Assuming a GradePolicy exists

    // Consider if grades with associated users should be deleted.
    // You might prevent deletion if it has related users.
    if ($grade->users()->exists()) { // Assuming a 'users' relationship on the Grade model
      return redirect()->back()->with('error', 'Cannot delete grade with associated users.');
    }

    // 1. Delete the grade
    $grade->delete();

    // 2. Redirect to the index page with a success message
    return redirect()->route('admin.grades.index')->with('success', 'Grade deleted successfully.');
  }

  // You can add other methods here if needed, e.g., for bulk actions
}
