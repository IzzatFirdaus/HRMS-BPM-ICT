<?php

namespace App\Http\Controllers\Admin; // Ensure the namespace is correct for your project

use App\Http\Controllers\Controller; // Extend the base Controller
use App\Models\Position; // Import the Position model (which maps to the 'designations' table)
use App\Models\Grade; // Import the Grade model for linking positions to grades
use Illuminate\Http\Request;
use Illuminate\Validation\Rule; // Import Rule for uniqueness validation
use Illuminate\Support\Facades\Log; // Import Log facade for logging
use Illuminate\Support\Facades\Gate; // For manual authorization checks if not using policies only
use Illuminate\Support\Facades\Auth; // Import Auth facade
use Exception; // Import Exception for error handling
use Illuminate\Database\QueryException; // Import QueryException for specific error handling

class DesignationController extends Controller // The controller name remains DesignationController
{
  /**
   * Display a listing of the resource.
   */
  public function index()
  {
    // Optional: Add authorization check using a Policy
    // $this->authorize('viewAny', Position::class); // Use Position model in policy check

    // Fetch all positions (using the Position model) with their associated grades
    // Assuming a 'grade' relationship exists on the Position model
    $designations = Position::with('grade')->get(); // Fetch Position models

    // Return the view with the list of positions
    // Assuming your admin views are in resources/views/admin/designations
    return view('admin.designations.index', compact('designations')); // Pass Position models as 'designations'
  }

  /**
   * Show the form for creating a new resource.
   */
  public function create()
  {
    // Optional: Add authorization check using a Policy
    // $this->authorize('create', Position::class); // Use Position model in policy check

    // Fetch all grades to populate the dropdown
    $grades = Grade::all();

    // Return the view for creating a position
    return view('admin.designations.create', compact('grades'));
  }

  /**
   * Store a newly created resource in storage.
   */
  public function store(Request $request)
  {
    // Optional: Add authorization check using a Policy
    // $this->authorize('create', Position::class); // Use Position model in policy check

    // 1. Validate the incoming request data
    // Validate against the 'designations' table
    $validatedData = $request->validate([
      'name' => 'required|string|max:255|unique:designations,name', // Position name unique in 'designations' table
      'grade_id' => 'nullable|exists:grades,id', // Link to Grade
      'description' => 'nullable|string|max:500', // Optional description field
      'vacancies_count' => 'nullable|integer|min:0', // Validation for vacancies_count
    ]);

    // 2. Create the new position (using the Position model) in the database
    $designation = Position::create($validatedData); // Create a Position model instance

    // Optional: Log the creation
    $loggedInUserId = Auth::check() ? Auth::user()->id : null;
    Log::info('Position created', [ // Log message updated to reflect 'Position'
      'position_id' => $designation->id, // Use the Position model instance
      'name' => $designation->name,
      'grade_id' => $designation->grade_id,
      'created_by' => $loggedInUserId
    ]);

    // 3. Redirect to the index page with a success message
    return redirect()->route('admin.designations.index')->with('success', 'Jawatan (Position) berjaya ditambah.'); // Malay success message
  }

  /**
   * Display the specified resource.
   */
  public function show(Position $designation) // Route model binding for Position model
  {
    // Optional: Add authorization check using a Policy
    // $this->authorize('view', $designation); // Use Position model in policy check

    // Eager load the grade relationship if needed for the show view
    $designation->load('grade');

    // Return the view to show position details
    return view('admin.designations.show', compact('designation')); // Pass Position model as 'designation'
  }

  /**
   * Show the form for editing the specified resource.
   */
  public function edit(Position $designation) // Route model binding for Position model
  {
    // Optional: Add authorization check using a Policy
    // $this->authorize('update', $designation); // Use Position model in policy check

    // Fetch all grades to populate the dropdown
    $grades = Grade::all();

    // Return the view for editing a position
    return view('admin.designations.edit', compact('designation', 'grades')); // Pass Position model as 'designation'
  }

  /**
   * Update the specified resource in storage.
   */
  public function update(Request $request, Position $designation) // Route model binding for Position model
  {
    // Optional: Add authorization check using a Policy
    // $this->authorize('update', $designation); // Use Position model in policy check

    // 1. Validate the incoming request data for update
    // Use Rule::unique ignore to allow the current position's name
    // Validate against the 'designations' table
    $validatedData = $request->validate([
      'name' => ['required', 'string', 'max:255', Rule::unique('designations', 'name')->ignore($designation->id)],
      'grade_id' => 'nullable|exists:grades,id', // Link to Grade
      'description' => 'nullable|string|max:500', // Optional description field
      'vacancies_count' => 'nullable|integer|min:0', // Validation for vacancies_count
    ]);

    // 2. Update the position model
    $designation->update($validatedData); // Update the Position model instance

    // Optional: Log the update
    $loggedInUserId = Auth::check() ? Auth::user()->id : null;
    Log::info('Position updated', [ // Log message updated to reflect 'Position'
      'position_id' => $designation->id, // Use the Position model instance
      'name' => $designation->name,
      'grade_id' => $designation->grade_id,
      'updated_by' => $loggedInUserId
    ]);

    // 3. Redirect to the details page or index page
    return redirect()->route('admin.designations.show', $designation)->with('success', 'Jawatan (Position) berjaya dikemaskini.'); // Malay success message
  }

  /**
   * Remove the specified resource from storage.
   */
  public function destroy(Position $designation) // Route model binding for Position model
  {
    // Optional: Add authorization check using a Policy
    // $this->authorize('delete', $designation); // Use Position model in policy check

    // 1. Delete the position (using the Position model)
    // Consider checking for related records (e.g., users assigned to this position) before deleting
    // to prevent database integrity issues. You might soft delete instead or require manual unassignment.
    try {
      $designationId = $designation->id; // Store ID before deletion attempt
      $designationName = $designation->name; // Store name before deletion attempt

      $designation->delete(); // Delete the Position model instance

      // Optional: Log the deletion
      $loggedInUserId = Auth::check() ? Auth::user()->id : null;
      Log::info('Position deleted', [ // Log message updated to reflect 'Position'
        'position_id' => $designationId, // Use stored ID
        'name' => $designationName, // Use stored name
        'deleted_by' => $loggedInUserId
      ]);

      return redirect()->route('admin.designations.index')->with('success', 'Jawatan (Position) berjaya dibuang.'); // Malay success message

    } catch (QueryException $e) { // Use the imported QueryException
      // Catch potential foreign key constraint violations if there are related users
      Log::error('Failed to delete Position ID ' . ($designation->id ?? 'unknown') . ' due to related records: ' . $e->getMessage()); // Log with potentially available ID
      return redirect()->route('admin.designations.index')->with('error', 'Gagal membuang Jawatan (Position). Terdapat rekod berkaitan (cth: pengguna) yang masih menggunakan jawatan ini.'); // Malay error message
    } catch (Exception $e) {
      // Catch any other exceptions
      Log::error('An error occurred while deleting Position ID ' . ($designation->id ?? 'unknown') . ': ' . $e->getMessage()); // Log with potentially available ID
      return redirect()->route('admin.designations.index')->with('error', 'Gagal membuang Jawatan (Position) disebabkan ralat tidak dijangka.'); // Malay error message
    }
  }

  // You can add other admin-specific methods here if needed
}
