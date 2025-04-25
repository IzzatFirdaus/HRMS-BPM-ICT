<?php

namespace App\Http\Controllers\Admin; // Ensure the namespace is correct for your project (likely Admin)

use App\Http\Controllers\Controller; // Extend the base Controller
use App\Models\Equipment; // Import the Equipment model
use App\Models\User; // Import User if needed for relationships (e.g., assigned_to)
use App\Models\Department; // Import Department if needed for relationships
use App\Models\Position; // Import Position (or Designation) if needed for relationships
use Illuminate\Http\Request; // Standard Request object
use Illuminate\Validation\Rule; // Import Rule for validation rules (e.g., unique, in)
use Illuminate\Support\Facades\Auth; // Import Auth facade if needed
use Illuminate\Support\Facades\Gate; // Import Gate if needed (Policies are preferred with $this->authorize)

// Import Form Requests if you create them for validation
// use App\Http\Requests\Admin\StoreEquipmentRequest;
// use App\Http\Requests\Admin\UpdateEquipmentRequest;


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
    // Apply admin middleware if this controller is only for admin access
    // $this->middleware('admin'); // Assuming you have an 'admin' middleware alias
    // Or apply a permission check middleware
    // $this->middleware('can:manage-equipment'); // Assuming a Spatie permission or Gate
  }

  /**
   * Display a listing of the resource (Equipment).
   *
   * @return \Illuminate\View\View
   */
  public function index()
  {
    // Authorize if the user can view any equipment (using a Policy)
    $this->authorize('viewAny', Equipment::class); // Assuming an EquipmentPolicy exists

    // Fetch all equipment, eager-loading necessary relationships (e.g., current loan transaction)
    // Order by latest creation date or tag ID
    $equipment = Equipment::with(['activeLoanTransaction.user', 'department', 'position'])->latest()->paginate(10); // Eager load active loan and user on it

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
    // Authorize if the user can create equipment
    $this->authorize('create', Equipment::class); // Assuming an EquipmentPolicy exists

    // Load data needed for the form (e.g., departments, positions, equipment types, statuses)
    $departments = Department::all(); // Assuming Department model exists
    $positions = Position::all(); // Assuming Position (or Designation) model exists
    // Define equipment types and statuses as arrays or from a config file
    $equipmentTypes = ['Laptop', 'Desktop', 'Monitor', 'Printer', 'Projector', 'Other']; // Example types
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
  public function store(Request $request) // Use StoreEquipmentRequest if created
  {
    // Authorize if the user can create equipment
    $this->authorize('create', Equipment::class); // Assuming an EquipmentPolicy exists

    // 1. Validate the incoming request data
    // Adjust validation rules based on the fields in your 'equipment' table and your form
    // If using a Form Request (StoreEquipmentRequest), validation is handled there.
    $validatedData = $request->validate([
      'tag_id' => 'required|string|max:50|unique:equipment,tag_id', // Tag ID should be unique
      'asset_type' => ['required', 'string', 'max:50', Rule::in(['Laptop', 'Desktop', 'Monitor', 'Printer', 'Projector', 'Other'])], // Validate against defined types
      'brand' => 'nullable|string|max:100',
      'model' => 'nullable|string|max:100',
      'serial_number' => 'nullable|string|max:100|unique:equipment,serial_number', // Serial number should be unique if provided
      'description' => 'nullable|string',
      'purchase_date' => 'nullable|date',
      'warranty_expiry_date' => 'nullable|date|after_or_equal:purchase_date',
      'status' => ['required', Rule::in(['available', 'on_loan', 'under_maintenance', 'disposed'])], // Validate against defined statuses
      'notes' => 'nullable|string',
      // Add validation for relationships if setting them on creation (less common)
      // 'department_id' => 'nullable|exists:departments,id',
      // 'position_id' => 'nullable|exists:designations,id', // Assuming designations table
    ]);

    // 2. Create the new equipment record in the database
    $equipment = Equipment::create($validatedData);

    // 3. Redirect to the equipment index page or show page with a success message
    return redirect()->route('admin.equipment.index')->with('success', 'Equipment added successfully!');
    // Or redirect to show: return redirect()->route('admin.equipment.show', $equipment)->with('success', 'Equipment added successfully!');
  }

  /**
   * Display the specified resource (Equipment).
   *
   * @param  \App\Models\Equipment  $equipment  The equipment instance resolved by route model binding.
   * @return \Illuminate\View\View
   */
  public function show(Equipment $equipment) // Use route model binding
  {
    // Authorize if the user can view this specific equipment
    $this->authorize('view', $equipment); // Assuming an EquipmentPolicy exists

    // Eager load relationships needed for the show view (e.g., department, position, loan history)
    $equipment->load(['department', 'position', 'loanTransactions.user', 'loanTransactions.issuingOfficer', 'loanTransactions.returnAcceptingOfficer']); // Load loan history and related users

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
    // Authorize if the user can update this equipment
    $this->authorize('update', $equipment); // Assuming an EquipmentPolicy exists

    // Load data needed for the form (e.g., departments, positions, equipment types, statuses)
    $departments = Department::all();
    $positions = Position::all(); // Assuming Position (or Designation) model exists
    $equipmentTypes = ['Laptop', 'Desktop', 'Monitor', 'Printer', 'Projector', 'Other']; // Example types
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
  public function update(Request $request, Equipment $equipment) // Use UpdateEquipmentRequest if created
  {
    // Authorize if the user can update this equipment
    $this->authorize('update', $equipment); // Assuming an EquipmentPolicy exists

    // 1. Validate the incoming request data for update
    // Use Rule::unique ignore to allow the equipment's current tag ID/serial number
    // If using a Form Request (UpdateEquipmentRequest), validation is handled there.
    $validatedData = $request->validate([
      'tag_id' => ['required', 'string', 'max:50', Rule::unique('equipment', 'tag_id')->ignore($equipment->id)],
      'asset_type' => ['required', 'string', 'max:50', Rule::in(['Laptop', 'Desktop', 'Monitor', 'Printer', 'Projector', 'Other'])],
      'brand' => 'nullable|string|max:100',
      'model' => 'nullable|string|max:100',
      'serial_number' => ['nullable', 'string', 'max:100', Rule::unique('equipment', 'serial_number')->ignore($equipment->id)],
      'description' => 'nullable|string',
      'purchase_date' => 'nullable|date',
      'warranty_expiry_date' => 'nullable|date|after_or_equal:purchase_date',
      'status' => ['required', Rule::in(['available', 'on_loan', 'under_maintenance', 'disposed'])],
      'notes' => 'nullable|string',
      // Add validation for relationships if updating them
      // 'department_id' => 'nullable|exists:departments,id',
      // 'position_id' => 'nullable|exists:designations,id',
    ]);

    // 2. Update the equipment model
    $equipment->update($validatedData);

    // 3. Redirect to the equipment show page or index page with a success message
    return redirect()->route('admin.equipment.show', $equipment)->with('success', 'Equipment updated successfully.');
    // Or redirect to index: return redirect()->route('admin.equipment.index')->with('success', 'Equipment updated successfully.');
  }

  /**
   * Remove the specified resource (Equipment) from storage.
   *
   * @param  \App\Models\Equipment  $equipment  The equipment instance resolved by route model binding.
   * @return \Illuminate\Http\RedirectResponse
   */
  public function destroy(Equipment $equipment) // Use route model binding
  {
    // Authorize if the user can delete this equipment
    $this->authorize('delete', $equipment); // Assuming an EquipmentPolicy exists

    // Consider if equipment with active loans or history should be deleted.
    // You might prevent deletion if it has related loan transactions.
    if ($equipment->loanTransactions()->exists()) {
      return redirect()->back()->with('error', 'Cannot delete equipment with existing loan history.');
    }


    // 1. Delete the equipment
    $equipment->delete();

    // 2. Redirect to the index page with a success message
    return redirect()->route('admin.equipment.index')->with('success', 'Equipment deleted successfully.');
  }

  // You can add other methods here if needed, e.g., for bulk actions or specific reports
}
