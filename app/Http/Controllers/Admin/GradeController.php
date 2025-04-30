<?php

namespace App\Http\Controllers\Admin; // Ensure the namespace is correct for your project (Admin)

use App\Http\Controllers\Controller; // Extend the base Controller
use App\Models\Grade; // Import the Grade model
use App\Models\Position; // Import Position model for deletion check
use App\Models\User; // Import User model for deletion check
use Illuminate\Http\Request; // Standard Request object
use Illuminate\Validation\Rule; // Import Rule for validation rules (e.g., unique, in)
use Illuminate\Support\Facades\Auth; // Import Auth facade for getting logged-in user
use Illuminate\Support\Facades\Log; // Import Log facade for logging
use Illuminate\Database\QueryException; // Import QueryException for database errors
use Exception; // Import Exception for general errors


class GradeController extends Controller
{
  /**
   * Apply authentication and admin middleware to all methods in this controller.
   * Use authorizeResource for policy checks.
   */
  public function __construct()
  {
    // Apply authentication middleware
    $this->middleware('auth');
    // Apply authorization policy checks automatically
    $this->authorizeResource(Grade::class, 'grade');
    // Note: When using authorizeResource, you don't need separate $this->authorize() calls
    // in each method (index, create, store, show, edit, update, destroy),
    // provided your GradePolicy is set up correctly.
  }

  /**
   * Display a listing of the resource (Grades).
   *
   * @return \Illuminate\View\View
   */
  public function index()
  {
    // Authorization is handled by authorizeResource in the constructor

    // Fetch all grades, eager-loading relationships and counts if needed
    // Eager load the minimum approval grade relationship if defined (self-referencing)
    // Use withCount() for positions and users to show related data counts
    $grades = Grade::with('minApprovalGrade') // Assuming 'minApprovalGrade' relationship (belongsTo self)
      ->withCount(['positions', 'users']) // Assuming 'positions' (hasMany) and 'users' (hasMany) relationships
      ->orderBy('level') // Order by level as it's a key attribute
      ->paginate(10); // Paginate for better performance

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
    // Authorization is handled by authorizeResource in the constructor

    // Fetch grades that can be selected as minimum approval grades (e.g., Grade 41 or higher)
    // You might filter this list based on business logic
    $approvalGrades = Grade::orderBy('level')->get(); // Or filter: Grade::where('level', '>=', 41)->orderBy('level')->get();

    // Return the view for creating a grade
    // Ensure your view file name matches: resources/views/admin/grades/create.blade.php
    return view('admin.grades.create', compact('approvalGrades'));
  }

  /**
   * Store a newly created resource (Grade) in storage.
   *
   * @param  \Illuminate\Http\Request  $request  The incoming request.
   * @return \Illuminate\Http\RedirectResponse
   */
  public function store(Request $request)
  {
    // Authorization is handled by authorizeResource in the constructor

    // 1. Validate the incoming request data
    // Adjust validation rules based on the fields in your 'grades' table and your form
    $validatedData = $request->validate([
      'name' => 'required|string|max:255|unique:grades,name', // Grade name should be unique
      'level' => 'required|integer|min:1|unique:grades,level', // Grade level should be unique and a positive integer
      'min_approval_grade_id' => 'nullable|exists:grades,id', // Foreign key to self (grades table)
      'is_approver_grade' => 'required|boolean', // Boolean field
      'description' => 'nullable|string', // Description is optional
    ]);

    // 2. Create the new grade record in the database
    $grade = Grade::create($validatedData);

    // Optional: Log the creation
    Log::info('Grade created', [
      'grade_id' => $grade->id,
      'name' => $grade->name,
      'level' => $grade->level,
      'created_by' => Auth::id()
    ]);

    // 3. Redirect to the grade index page or show page with a success message
    // Changed message to Malay
    return redirect()->route('admin.grades.index')->with('success', 'Gred berjaya ditambah.');
    // Or redirect to show: return redirect()->route('admin.grades.show', $grade)->with('success', 'Gred berjaya ditambah.');
  }

  /**
   * Display the specified resource (Grade).
   *
   * @param  \App\Models\Grade  $grade  The grade instance resolved by route model binding.
   * @return \Illuminate\View\View
   */
  public function show(Grade $grade) // Use route model binding
  {
    // Authorization is handled by authorizeResource in the constructor

    // Eager load relationships if needed for the show view
    $grade->load(['minApprovalGrade', 'positions', 'users']); // Load related approval grade, positions, and users

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
    // Authorization is handled by authorizeResource in the constructor

    // Fetch grades that can be selected as minimum approval grades
    $approvalGrades = Grade::orderBy('level')->get(); // Or filter: Grade::where('level', '>=', 41)->orderBy('level')->get();


    // Return the view for editing a grade
    // Ensure your view file name matches: resources/views/admin/grades/edit.blade.php
    return view('admin.grades.edit', compact('grade', 'approvalGrades'));
  }

  /**
   * Update the specified resource (Grade) in storage.
   *
   * @param  \Illuminate\Http\Request  $request  The incoming request.
   * @param  \App\Models\Grade  $grade  The grade instance resolved by route model binding.
   * @return \Illuminate\Http\RedirectResponse
   */
  public function update(Request $request, Grade $grade)
  {
    // Authorization is handled by authorizeResource in the constructor

    // 1. Validate the incoming request data for update
    // Use Rule::unique ignore to allow the grade's current name/level
    $validatedData = $request->validate([
      'name' => ['required', 'string', 'max:255', Rule::unique('grades', 'name')->ignore($grade->id)],
      'level' => ['required', 'integer', 'min:1', Rule::unique('grades', 'level')->ignore($grade->id)],
      'min_approval_grade_id' => 'nullable|exists:grades,id', // Foreign key to self (grades table)
      'is_approver_grade' => 'required|boolean', // Boolean field
      'description' => 'nullable|string',
    ]);

    // 2. Update the grade model
    $grade->update($validatedData);

    // Optional: Log the update
    Log::info('Grade updated', [
      'grade_id' => $grade->id,
      'name' => $grade->name,
      'level' => $grade->level,
      'updated_by' => Auth::id()
    ]);


    // 3. Redirect to the grade show page or index page with a success message
    // Changed message to Malay
    return redirect()->route('admin.grades.show', $grade)->with('success', 'Gred berjaya dikemaskini.');
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
    // Authorization is handled by authorizeResource in the constructor

    // Prevent deletion if grade has associated positions or users
    if ($grade->positions()->exists() || $grade->users()->exists()) {
      Log::warning('Attempted to delete Grade ID ' . $grade->id . ' with associated positions or users', [
        'grade_id' => $grade->id,
        'deleted_by' => Auth::id()
      ]);
      // Changed error message to Malay and redirect to index
      return redirect()->route('admin.grades.index')->with('error', 'Tidak dapat memadam Gred kerana terdapat Jawatan atau pengguna yang berkaitan.');
    }

    // 1. Delete the grade
    try {
      $gradeId = $grade->id; // Store ID before deletion
      $gradeName = $grade->name; // Store name before deletion

      $grade->delete();

      // Optional: Log the deletion
      Log::info('Grade deleted', [
        'grade_id' => $gradeId,
        'name' => $gradeName,
        'deleted_by' => Auth::id()
      ]);

      // 2. Redirect to the index page with a success message
      // Changed message to Malay
      return redirect()->route('admin.grades.index')->with('success', 'Gred berjaya dibuang.');
    } catch (QueryException $e) {
      Log::error('Failed to delete Grade ID ' . ($grade->id ?? 'unknown') . ' due to database constraint: ' . $e->getMessage(), [
        'grade_id' => $grade->id ?? 'unknown',
        'error' => $e->getMessage(),
        'deleted_by' => Auth::id()
      ]);
      // Changed error message to Malay
      return redirect()->route('admin.grades.index')->with('error', 'Gagal membuang Gred disebabkan ralat pangkalan data.');
    } catch (Exception $e) {
      Log::error('An unexpected error occurred while deleting Grade ID ' . ($grade->id ?? 'unknown') . ': ' . $e->getMessage(), [
        'grade_id' => $grade->id ?? 'unknown',
        'error' => $e->getMessage(),
        'deleted_by' => Auth::id()
      ]);
      // Changed error message to Malay
      return redirect()->route('admin.grades.index')->with('error', 'Gagal membuang Gred disebabkan ralat tidak dijangka.');
    }
  }

  // You can add other methods here if needed
}
