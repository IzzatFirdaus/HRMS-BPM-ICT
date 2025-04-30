<?php

namespace App\Http\Controllers\Admin; // Ensure the namespace is correct for your project

use App\Http\Controllers\Controller; // Extend the base Controller
use App\Models\Equipment; // Import the Equipment model
use App\Models\Department; // Import Department for relationships/form data
use App\Models\Position; // Import Position for relationships/form data
use Illuminate\Http\Request; // Standard Request object
use Illuminate\Validation\Rule; // Import Rule for validation rules (e.g., unique, in)
use Illuminate\Support\Facades\Auth; // Import Auth facade for getting logged-in user
use Illuminate\Support\Facades\Log; // Import Log facade for logging
use Illuminate\Database\QueryException; // Import QueryException for database errors
use Exception; // Import Exception for general errors


class EquipmentController extends Controller
{
  /**
   * Apply authentication and admin middleware to all methods in this controller.
   * You might also apply specific permissions via middleware or policies.
   */
  public function __construct()
  {
    // Apply authentication middleware
    $this->middleware('auth');
    // Apply authorization policy checks automatically
    $this->authorizeResource(Equipment::class, 'equipment');
    // Note: When using authorizeResource, you don't need separate $this->authorize() calls
    // in each method (index, create, store, show, edit, update, destroy),
    // provided your EquipmentPolicy is set up correctly.
  }

  /**
   * Display a listing of the resource (Equipment).
   *
   * @return \Illuminate\View\View
   */
  public function index()
  {
    // Authorization is handled by authorizeResource in the constructor

    // Fetch all equipment, eager-loading necessary relationships
    // Eager load active loan transaction and the user associated with it
    // Also eager load department and position if equipment is linked to them
    $equipment = Equipment::with(['activeLoanTransaction.user', 'department', 'position'])
      ->latest() // Order by latest creation date
      ->paginate(10); // Paginate for better performance

    // Return the view with the list of equipment
    // Ensure your view file name matches: resources/views/admin/equipment/index.blade.php
    return view('admin.equipment.index', compact('equipment'));
  }

  /**
   * Show the form for creating a new resource (Equipment).
   *
   * @return \Illuminate\View\View
   */
  public function create()
  {
    // Authorization is handled by authorizeResource in the constructor

    // Load data needed for the form (e.g., departments, positions, equipment types, statuses)
    $departments = Department::all(); // Assuming Department model exists
    $positions = Position::all(); // Assuming Position model exists

    // Define equipment types and statuses as arrays or from a config file
    // Aligned asset types with the system design document's enum examples (lowercase)
    $equipmentTypes = ['laptop', 'projector', 'printer', 'monitor', 'desktop', 'other']; // Match enum in migration
    $equipmentStatuses = ['available', 'on_loan', 'under_maintenance', 'disposed']; // Match enum in migration

    // Return the view for creating equipment
    // Ensure your view file name matches: resources/views/admin/equipment/create.blade.php
    return view('admin.equipment.create', compact('departments', 'positions', 'equipmentTypes', 'equipmentStatuses'));
  }

  /**
   * Store a newly created resource (Equipment) in storage.
   *
   * @param  \Illuminate\Http\Request  $request  The incoming request.
   * @return \Illuminate\Http\RedirectResponse
   */
  public function store(Request $request)
  {
    // Authorization is handled by authorizeResource in the constructor

    // 1. Validate the incoming request data
    // Adjust validation rules based on the fields in your 'equipment' table and your form
    $validatedData = $request->validate([
      'tag_id' => 'required|string|max:50|unique:equipment,tag_id', // Tag ID should be unique
      // Validate against the defined lowercase enum values from the system design
      'asset_type' => ['required', 'string', 'max:50', Rule::in(['laptop', 'projector', 'printer', 'monitor', 'desktop', 'other'])],
      'brand' => 'nullable|string|max:100',
      'model' => 'nullable|string|max:100',
      // Serial number should be unique if provided
      'serial_number' => 'nullable|string|max:100|unique:equipment,serial_number',
      'description' => 'nullable|string', // Added based on the provided code, not explicitly in system design table but seems reasonable
      'purchase_date' => 'nullable|date',
      'warranty_expiry_date' => 'nullable|date|after_or_equal:purchase_date',
      // Validate against the defined lowercase enum values from the system design
      'status' => ['required', Rule::in(['available', 'on_loan', 'under_maintenance', 'disposed'])],
      'notes' => 'nullable|string',
      // Add validation for relationships if setting them on creation
      // 'department_id' => 'nullable|exists:departments,id', // If you link equipment directly to department
      // 'position_id' => 'nullable|exists:positions,id', // If you link equipment directly to position
    ]);

    // 2. Create the new equipment record in the database
    $equipment = Equipment::create($validatedData);

    // Optional: Log the creation
    Log::info('Equipment created', [
      'equipment_id' => $equipment->id,
      'tag_id' => $equipment->tag_id,
      'asset_type' => $equipment->asset_type,
      'created_by' => Auth::id()
    ]);

    // 3. Redirect to the equipment index page or show page with a success message
    // Changed message to Malay
    return redirect()->route('admin.equipment.index')->with('success', 'Peralatan ICT berjaya ditambah.');
    // Or redirect to show: return redirect()->route('admin.equipment.show', $equipment)->with('success', 'Peralatan ICT berjaya ditambah.');
  }

  /**
   * Display the specified resource (Equipment).
   *
   * @param  \App\Models\Equipment  $equipment  The equipment instance resolved by route model binding.
   * @return \Illuminate\View\View
   */
  public function show(Equipment $equipment) // Use route model binding
  {
    // Authorization is handled by authorizeResource in the constructor

    // Eager load relationships needed for the show view (e.g., department, position, loan history)
    $equipment->load([
      'department',
      'position',
      'loanTransactions.user', // Load the user who was the applicant/responsible officer
      'loanTransactions.issuingOfficer', // Load the officer who issued the equipment
      'loanTransactions.returnAcceptingOfficer' // Load the officer who accepted the return
    ]);

    // Return the view to show equipment details
    // Ensure your view file name matches: resources/views/admin/equipment/show.blade.php
    return view('admin.equipment.show', compact('equipment'));
  }

  /**
   * Show the form for editing the specified resource (Equipment).
   *
   * @param  \App\Models\Equipment  $equipment  The equipment instance resolved by route model binding.
   * @return \Illuminate\View\View
   */
  public function edit(Equipment $equipment) // Use route model binding
  {
    // Authorization is handled by authorizeResource in the constructor

    // Load data needed for the form (e.g., departments, positions, equipment types, statuses)
    $departments = Department::all();
    $positions = Position::all();
    // Aligned asset types with the system design document's enum examples (lowercase)
    $equipmentTypes = ['laptop', 'projector', 'printer', 'monitor', 'desktop', 'other']; // Match enum in migration
    $equipmentStatuses = ['available', 'on_loan', 'under_maintenance', 'disposed']; // Match enum in migration


    // Return the view for editing equipment
    // Ensure your view file name matches: resources/views/admin/equipment/edit.blade.php
    return view('admin.equipment.edit', compact('equipment', 'departments', 'positions', 'equipmentTypes', 'equipmentStatuses'));
  }

  /**
   * Update the specified resource (Equipment) in storage.
   *
   * @param  \Illuminate\Http\Request  $request  The incoming request.
   * @param  \App\Models\Equipment  $equipment  The equipment instance resolved by route model binding.
   * @return \Illuminate\Http\RedirectResponse
   */
  public function update(Request $request, Equipment $equipment)
  {
    // Authorization is handled by authorizeResource in the constructor

    // 1. Validate the incoming request data for update
    // Use Rule::unique ignore to allow the equipment's current tag ID/serial number
    $validatedData = $request->validate([
      'tag_id' => ['required', 'string', 'max:50', Rule::unique('equipment', 'tag_id')->ignore($equipment->id)],
      // Validate against the defined lowercase enum values from the system design
      'asset_type' => ['required', 'string', 'max:50', Rule::in(['laptop', 'projector', 'printer', 'monitor', 'desktop', 'other'])],
      'brand' => 'nullable|string|max:100',
      'model' => 'nullable|string|max:100',
      // Serial number should be unique if provided, ignoring the current equipment
      'serial_number' => ['nullable', 'string', 'max:100', Rule::unique('equipment', 'serial_number')->ignore($equipment->id)],
      'description' => 'nullable|string', // Added based on the provided code
      'purchase_date' => 'nullable|date',
      'warranty_expiry_date' => 'nullable|date|after_or_equal:purchase_date',
      // Validate against the defined lowercase enum values from the system design
      'status' => ['required', Rule::in(['available', 'on_loan', 'under_maintenance', 'disposed'])],
      'notes' => 'nullable|string',
      // Add validation for relationships if updating them
      // 'department_id' => 'nullable|exists:departments,id',
      // 'position_id' => 'nullable|exists:positions,id',
    ]);

    // 2. Update the equipment model
    $equipment->update($validatedData);

    // Optional: Log the update
    Log::info('Equipment updated', [
      'equipment_id' => $equipment->id,
      'tag_id' => $equipment->tag_id,
      'updated_by' => Auth::id()
    ]);

    // 3. Redirect to the equipment show page or index page with a success message
    // Changed message to Malay
    return redirect()->route('admin.equipment.show', $equipment)->with('success', 'Peralatan ICT berjaya dikemaskini.');
    // Or redirect to index: return redirect()->route('admin.equipment.index')->with('success', 'Peralatan ICT updated successfully.');
  }

  /**
   * Remove the specified resource (Equipment) from storage.
   *
   * @param  \App\Models\Equipment  $equipment  The equipment instance resolved by route model binding.
   * @return \Illuminate\Http\RedirectResponse
   */
  public function destroy(Equipment $equipment) // Use route model binding
  {
    // Authorization is handled by authorizeResource in the constructor

    // Prevent deletion if equipment has any associated loan transactions (active or historical)
    if ($equipment->loanTransactions()->exists()) {
      Log::warning('Attempted to delete Equipment ID ' . $equipment->id . ' with existing loan history', [
        'equipment_id' => $equipment->id,
        'deleted_by' => Auth::id()
      ]);
      // Changed error message to Malay and redirect to index
      return redirect()->route('admin.equipment.index')->with('error', 'Tidak dapat memadam Peralatan ICT kerana terdapat rekod pinjaman berkaitan.');
    }

    // 1. Delete the equipment
    try {
      $equipmentId = $equipment->id; // Store ID before deletion
      $equipmentTag = $equipment->tag_id; // Store Tag ID before deletion

      $equipment->delete();

      // Optional: Log the deletion
      Log::info('Equipment deleted', [
        'equipment_id' => $equipmentId,
        'tag_id' => $equipmentTag,
        'deleted_by' => Auth::id()
      ]);

      // 2. Redirect to the index page with a success message
      // Changed message to Malay
      return redirect()->route('admin.equipment.index')->with('success', 'Peralatan ICT berjaya dibuang.');
    } catch (QueryException $e) {
      Log::error('Failed to delete Equipment ID ' . ($equipment->id ?? 'unknown') . ' due to database constraint: ' . $e->getMessage(), [
        'equipment_id' => $equipment->id ?? 'unknown',
        'error' => $e->getMessage(),
        'deleted_by' => Auth::id()
      ]);
      // Changed error message to Malay
      return redirect()->route('admin.equipment.index')->with('error', 'Gagal membuang Peralatan ICT disebabkan ralat pangkalan data.');
    } catch (Exception $e) {
      Log::error('An unexpected error occurred while deleting Equipment ID ' . ($equipment->id ?? 'unknown') . ': ' . $e->getMessage(), [
        'equipment_id' => $equipment->id ?? 'unknown',
        'error' => $e->getMessage(),
        'deleted_by' => Auth::id()
      ]);
      // Changed error message to Malay
      return redirect()->route('admin.equipment.index')->with('error', 'Gagal membuang Peralatan ICT disebabkan ralat tidak dijangka.');
    }
  }

  // You can add other methods here if needed, e.g., for bulk actions or specific reports
}
