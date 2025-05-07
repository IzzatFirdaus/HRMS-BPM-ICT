<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Position; // Import the Position model
use App\Models\Grade; // Import the Grade model
use Illuminate\Http\Request;
use Illuminate\Validation\Rule; // Import Rule for uniqueness validation
use Illuminate\Support\Facades\Log; // Import Log facade for logging
use Illuminate\Support\Facades\Auth; // Import Auth facade for getting logged-in user
use Illuminate\Support\Facades\DB; // FIX: Import the DB facade for transactions
use Exception; // Import Exception for general error handling
use Illuminate\Database\QueryException; // Import QueryException for specific database error handling
use Illuminate\View\View; // Import View for return type hint
use Illuminate\Http\RedirectResponse; // Import RedirectResponse for return type hint

// Controller for managing Job Positions (formerly Designations)
class PositionController extends Controller
{
  /**
   * Display a listing of the resource.
   */
  public function index(): View
  {
    // Optional: Add authorization check using a Policy
    // $this->authorize('viewAny', Position::class);

    // Fetch all positions (using the Position model) with their associated grades
    // Assuming a 'grade' relationship exists on the Position model (BelongsTo)
    // Also, assuming a 'users' relationship exists to count associated users (HasMany)
    $positions = Position::with('grade')->withCount('users')->get();

    // Return the view with the list of positions
    // Assumes a Blade view file exists at resources/views/admin/positions/index.blade.php
    return view('admin.positions.index', compact('positions'));
  }

  /**
   * Show the form for creating a new resource.
   */
  public function create(): View
  {
    // Optional: Add authorization check (e.g., 'create positions')
    // $this->authorize('create', Position::class);

    // Fetch data needed for the create form (e.g., list of grades for a dropdown)
    $grades = Grade::all(); // Assuming you need to select a grade when creating a position

    // Return the view for creating a position
    // Assumes a Blade view file exists at resources/views/admin/positions/create.blade.php
    return view('admin.positions.create', compact('grades'));
  }

  /**
   * Store a newly created resource in storage.
   */
  public function store(Request $request): RedirectResponse
  {
    // Optional: Add authorization check (e.g., 'create positions')
    // $this->authorize('create', Position::class);

    // Validate the incoming request data
    $validatedData = $request->validate([
      // FIX: Removed ->ignore($position->id) because $position is not defined when creating
      'name' => ['required', 'string', 'max:255', 'unique:positions,name'], // Validate uniqueness
      'vacancies_count' => ['required', 'integer', 'min:0'],
      'grade_id' => ['nullable', 'exists:grades,id'],
      // Add validation rules for other position attributes if they exist
    ]);

    DB::beginTransaction(); // Start database transaction
    try {
      // Create a new position using the validated data
      // The CreatedUpdatedDeletedBy trait is assumed to handle created_by automatically from Auth::id()
      Position::create($validatedData);

      DB::commit(); // Commit the transaction

      // Log the successful creation
      Log::info('Position created successfully.', [
        'name' => $validatedData['name'],
        'created_by' => Auth::id(),
      ]);

      // Redirect the user to the index page with a success message
      // Assuming a named route like 'admin.positions.index'
      return redirect()->route('admin.positions.index')->with('success', 'Jawatan (Position) berjaya ditambah.'); // Malay success message

    } catch (Exception $e) {
      DB::rollBack(); // Rollback the transaction on error

      // Log the error
      Log::error('Failed to create position: ' . $e->getMessage(), [
        'request_data' => $request->all(),
        'error' => $e->getMessage(),
        'created_by' => Auth::id(),
      ]);

      // Redirect back with input and an error message
      return redirect()->back()->withInput()->with('error', 'Gagal menambah Jawatan (Position). Sila cuba lagi.'); // Malay error message
    }
  }

  /**
   * Display the specified resource.
   */
  public function show(Position $position): View
  {
    // Optional: Add authorization check (e.g., 'view positions')
    // $this->authorize('view', $position);

    // Return the view for showing a specific position
    // Assumes a Blade view file exists at resources/views/admin/positions/show.blade.php
    return view('admin.positions.show', compact('position'));
  }

  /**
   * Show the form for editing the specified resource.
   */
  public function edit(Position $position): View
  {
    // Optional: Add authorization check (e.g., 'update positions')
    // $this->authorize('update', $position);

    // Fetch data needed for the edit form (e.g., list of grades for a dropdown)
    $grades = Grade::all();

    // Return the view for editing a position
    // Assumes a Blade view file exists at resources/views/admin/positions/edit.blade.php
    return view('admin.positions.edit', compact('position', 'grades'));
  }

  /**
   * Update the specified resource in storage.
   */
  public function update(Request $request, Position $position): RedirectResponse
  {
    // Optional: Add authorization check (e.g., 'update positions')
    // $this->authorize('update', $position);

    // Validate the incoming request data, ignoring the current position's name for the unique rule
    $validatedData = $request->validate([
      'name' => ['required', 'string', 'max:255', Rule::unique('positions', 'name')->ignore($position->id)], // Correctly use ->ignore($position->id) here
      'vacancies_count' => ['required', 'integer', 'min:0'],
      'grade_id' => ['nullable', 'exists:grades,id'],
      // Add validation rules for other position attributes if they exist
    ]);

    DB::beginTransaction(); // Start database transaction
    try {
      // Update the position with the validated data
      // The CreatedUpdatedDeletedBy trait is assumed to handle updated_by automatically from Auth::id()
      $position->update($validatedData);

      DB::commit(); // Commit the transaction

      // Log the successful update
      Log::info('Position updated successfully.', [
        'position_id' => $position->id,
        'updated_by' => Auth::id(),
      ]);

      // Redirect the user to the index page with a success message
      // Assuming a named route like 'admin.positions.index'
      return redirect()->route('admin.positions.index')->with('success', 'Jawatan (Position) berjaya dikemaskini.'); // Malay success message

    } catch (Exception $e) {
      DB::rollBack(); // Rollback the transaction on error

      // Log the error
      Log::error('Failed to update position ID ' . $position->id . ': ' . $e->getMessage(), [
        'position_id' => $position->id,
        'request_data' => $request->all(),
        'error' => $e->getMessage(),
        'updated_by' => Auth::id(),
      ]);

      // Redirect back with input and an error message
      return redirect()->back()->withInput()->with('error', 'Gagal mengemaskini Jawatan (Position). Sila cuba lagi.'); // Malay error message
    }
  }

  /**
   * Remove the specified resource from storage.
   * Note: This performs a soft delete due to the SoftDeletes trait on the Position model.
   */
  public function destroy(Position $position): RedirectResponse
  {
    // Optional: Add authorization check (e.g., 'delete positions')
    // $this->authorize('delete', $position);

    DB::beginTransaction(); // Start database transaction
    try {
      // Check if the position has associated users (or employees) before deleting.
      // If foreign key constraints are set with ON DELETE SET NULL, you might not need to prevent deletion,
      // but your business logic might require reassigning users first.
      if ($position->users()->exists()) {
        DB::rollBack(); // Rollback the transaction
        Log::warning('Attempted to delete Position ID ' . $position->id . ' with associated users.', ['position_id' => $position->id, 'user_id' => Auth::id()]);
        return redirect()->route('admin.positions.index')->with('error', 'Gagal membuang Jawatan (Position). Terdapat pengguna berkaitan dengan jawatan ini.'); // Malay error message
      }


      // Soft delete the position using the delete() method (provided by SoftDeletes trait)
      $positionName = $position->name; // Store name before deletion for logging
      $position->delete(); // This sets the 'deleted_at' timestamp

      DB::commit(); // Commit the transaction

      // Log the successful soft deletion
      Log::info('Position soft deleted successfully.', [
        'position_id' => $position->id,
        'name' => $positionName, // Use stored name
        'deleted_by' => Auth::id() // Assuming CreatedUpdatedDeletedBy trait sets this on delete
      ]);

      // Redirect to the index page with a success message
      return redirect()->route('admin.positions.index')->with('success', 'Jawatan (Position) berjaya dibuang.'); // Malay success message

    } catch (QueryException $e) { // Catch potential foreign key constraint violations if there are related records other than users
      DB::rollBack(); // Rollback the transaction
      Log::error('Failed to delete Position ID ' . ($position->id ?? 'unknown') . ' due to database constraint: ' . $e->getMessage(), [
        'position_id' => $position->id ?? 'unknown',
        'error' => $e->getMessage(),
        'deleted_by' => Auth::id()
      ]);
      return redirect()->route('admin.positions.index')->with('error', 'Gagal membuang Jawatan (Position) disebabkan ralat pangkalan data.'); // Malay error message
    } catch (Exception $e) {
      DB::rollBack(); // Rollback the transaction
      // Catch any other exceptions
      Log::error('An unexpected error occurred while deleting Position ID ' . ($position->id ?? 'unknown') . ': ' . $e->getMessage(), [
        'position_id' => $position->id ?? 'unknown',
        'error' => $e->getMessage(),
        'deleted_by' => Auth::id()
      ]);
      return redirect()->back()->withInput()->with('error', 'Gagal membuang Jawatan (Position) disebabkan ralat tidak dijangka.'); // Malay error message
    }
  }

  // You can add other admin-specific methods here if needed
}
