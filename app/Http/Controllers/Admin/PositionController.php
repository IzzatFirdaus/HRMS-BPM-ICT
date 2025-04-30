<?php

namespace App\Http\Controllers\Admin; // Ensure the namespace is correct for your project

use App\Http\Controllers\Controller; // Extend the base Controller
use App\Models\Position; // Import the Position model (which maps to the 'positions' table based on your system design)
use App\Models\Grade; // Import the Grade model for linking positions to grades
use Illuminate\Http\Request;
use Illuminate\Validation\Rule; // Import Rule for uniqueness validation
use Illuminate\Support\Facades\Log; // Import Log facade for logging
use Illuminate\Support\Facades\Auth; // Import Auth facade for getting logged-in user
use Exception; // Import Exception for general error handling
use Illuminate\Database\QueryException; // Import QueryException for specific database error handling

// Changed controller name from DesignationController to PositionController
class PositionController extends Controller
{
  /**
   * Display a listing of the resource.
   */
  public function index()
  {
    // Optional: Add authorization check using a Policy
    // $this->authorize('viewAny', Position::class); // Use Position model in policy check

    // Fetch all positions (using the Position model) with their associated grades
    // Assuming a 'grade' relationship exists on the Position model (belongsTo)
    // Also, assuming a 'users' relationship exists to count associated users (hasMany)
    $positions = Position::with('grade')->withCount('users')->get();

    // Return the view with the list of positions
    // Assuming your admin views are in resources/views/admin/positions (changed from designations)
    return view('admin.positions.index', compact('positions')); // Pass Position models as 'positions'
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
    // Assuming your admin views are in resources/views/admin/positions (changed from designations)
    return view('admin.positions.create', compact('grades'));
  }

  /**
   * Store a newly created resource in storage.
   */
  public function store(Request $request)
  {
    // Optional: Add authorization check using a Policy
    // $this->authorize('create', Position::class); // Use Position model in policy check

    // 1. Validate the incoming request data
    // Validate against the 'positions' table as per system design (corrected from 'designations')
    $validatedData = $request->validate([
      'name' => 'required|string|max:255|unique:positions,name', // Position name unique in 'positions' table
      'grade_id' => 'nullable|exists:grades,id', // Link to Grade, ensure 'grades' table exists
      'description' => 'nullable|string|max:500', // Optional description field
      // Removed 'vacancies_count' validation as it wasn't in the system design table structure
      // You can add it back if 'vacancies_count' is a required field in your positions table
    ]);

    // 2. Create the new position (using the Position model) in the database
    $position = Position::create($validatedData); // Create a Position model instance

    // Optional: Log the creation
    $loggedInUserId = Auth::id(); // Auth::id() is a shorthand for Auth::user()->id
    Log::info('Position created', [ // Log message updated to reflect 'Position'
      'position_id' => $position->id, // Use the Position model instance
      'name' => $position->name,
      'grade_id' => $position->grade_id,
      'created_by' => $loggedInUserId
    ]);

    // 3. Redirect to the index page with a success message
    // Assuming you have a named route for the admin position index like 'admin.positions.index' (changed from designations)
    return redirect()->route('admin.positions.index')->with('success', 'Jawatan (Position) berjaya ditambah.'); // Malay success message
  }

  /**
   * Display the specified resource.
   */
  public function show(Position $position) // Route model binding for Position model (variable name changed)
  {
    // Optional: Add authorization check using a Policy
    // $this->authorize('view', $position); // Use Position model in policy check

    // Eager load the grade relationship if needed for the show view
    $position->load('grade');

    // Return the view to show position details
    // Assuming your admin views are in resources/views/admin/positions (changed from designations)
    return view('admin.positions.show', compact('position')); // Pass Position model as 'position'
  }

  /**
   * Show the form for editing the specified resource.
   */
  public function edit(Position $position) // Route model binding for Position model (variable name changed)
  {
    // Optional: Add authorization check using a Policy
    // $this->authorize('update', $position); // Use Position model in policy check

    // Fetch all grades to populate the dropdown
    $grades = Grade::all();

    // Return the view for editing a position
    // Assuming your admin views are in resources/views/admin/positions (changed from designations)
    return view('admin.positions.edit', compact('position', 'grades')); // Pass Position model as 'position'
  }

  /**
   * Update the specified resource in storage.
   */
  public function update(Request $request, Position $position) // Route model binding for Position model (variable name changed)
  {
    // Optional: Add authorization check using a Policy
    // $this->authorize('update', $position); // Use Position model in policy check

    // 1. Validate the incoming request data for update
    // Use Rule::unique ignore to allow the current position's name
    // Validate against the 'positions' table as per system design (corrected from 'designations')
    $validatedData = $request->validate([
      'name' => ['required', 'string', 'max:255', Rule::unique('positions', 'name')->ignore($position->id)],
      'grade_id' => 'nullable|exists:grades,id', // Link to Grade
      'description' => 'nullable|string|max:500', // Optional description field
      // Removed 'vacancies_count' validation
    ]);

    // 2. Update the position model
    $position->update($validatedData); // Update the Position model instance

    // Optional: Log the update
    $loggedInUserId = Auth::id(); // Auth::id() is a shorthand for Auth::user()->id
    Log::info('Position updated', [ // Log message updated to reflect 'Position'
      'position_id' => $position->id, // Use the Position model instance
      'name' => $position->name,
      'grade_id' => $position->grade_id,
      'updated_by' => $loggedInUserId
    ]);

    // 3. Redirect to the details page or index page
    // Assuming you have named routes like 'admin.positions.show' (changed from designations)
    return redirect()->route('admin.positions.show', $position)->with('success', 'Jawatan (Position) berjaya dikemaskini.'); // Malay success message
    // Or redirect to index: return redirect()->route('admin.positions.index')->with('success', 'Position updated successfully.');
  }

  /**
   * Remove the specified resource from storage.
   */
  public function destroy(Position $position) // Route model binding for Position model (variable name changed)
  {
    // Optional: Add authorization check using a Policy
    // $this->authorize('delete', $position); // Use Position model in policy check

    // Add a check to prevent deleting positions with associated users
    // Assumes a 'users' relationship exists on the Position model (hasMany)
    if ($position->users()->count() > 0) {
      Log::warning('Attempted to delete Position ID ' . $position->id . ' with associated users', [
        'position_id' => $position->id,
        'deleted_by' => Auth::id()
      ]);
      return redirect()->route('admin.positions.index')->with('error', 'Tidak dapat memadam Jawatan (Position) kerana terdapat pengguna yang berkaitan.'); // Malay error message
    }

    // 1. Delete the position (using the Position model)
    try {
      $positionId = $position->id; // Store ID before deletion attempt
      $positionName = $position->name; // Store name before deletion attempt

      $position->delete(); // Delete the Position model instance

      // Optional: Log the deletion
      $loggedInUserId = Auth::id(); // Auth::id() is a shorthand for Auth::user()->id
      Log::info('Position deleted', [ // Log message updated to reflect 'Position'
        'position_id' => $positionId, // Use stored ID
        'name' => $positionName, // Use stored name
        'deleted_by' => $loggedInUserId
      ]);

      // Assuming you have a named route like 'admin.positions.index' (changed from designations)
      return redirect()->route('admin.positions.index')->with('success', 'Jawatan (Position) berjaya dibuang.'); // Malay success message

    } catch (QueryException $e) { // Use the imported QueryException
      // Catch potential foreign key constraint violations if there are related records other than users
      Log::error('Failed to delete Position ID ' . ($position->id ?? 'unknown') . ' due to database constraint: ' . $e->getMessage(), [
        'position_id' => $position->id ?? 'unknown',
        'error' => $e->getMessage(),
        'deleted_by' => Auth::id()
      ]);
      return redirect()->route('admin.positions.index')->with('error', 'Gagal membuang Jawatan (Position) disebabkan ralat pangkalan data.'); // Malay error message
    } catch (Exception $e) {
      // Catch any other exceptions
      Log::error('An unexpected error occurred while deleting Position ID ' . ($position->id ?? 'unknown') . ': ' . $e->getMessage(), [
        'position_id' => $position->id ?? 'unknown',
        'error' => $e->getMessage(),
        'deleted_by' => Auth::id()
      ]);
      return redirect()->route('admin.positions.index')->with('error', 'Gagal membuang Jawatan (Position) disebabkan ralat tidak dijangka.'); // Malay error message
    }
  }

  // You can add other admin-specific methods here if needed
}
