<?php

namespace App\Http\Controllers\Admin; // Set the namespace to Admin

use App\Http\Controllers\Controller; // Extend the base Controller
use App\Models\Equipment; // Import the Equipment model
use App\Models\Department; // Import Department for relationships/form data
use App\Models\Position; // Import Position (needed for create/edit forms, not necessarily index eager load)
use App\Models\Center; // Import Center for relationships/form data
// Import other models if directly used in this controller (e.g., User for audit, Employee if assigning)
// use App\Models\User;
// use App\Models\Employee;

use Illuminate\Http\Request; // Standard Request object
use Illuminate\Validation\Rule; // Import Rule for validation rules (e.g., unique, in)
use Illuminate\Support\Facades\Auth; // Import Auth facade for accessing authenticated user
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

    // Apply authorization policy checks automatically for resource methods
    // Assumes an EquipmentPolicy exists and is registered in AuthServiceProvider.
    // Policy methods: viewAny, view, create, update, delete
    $this->authorizeResource(Equipment::class, 'equipment'); // Use 'equipment' as parameter name
  }

  /**
   * Display a listing of the resource (Equipment).
   * Fetches equipment records the user is authorized to view.
   *
   * @return \Illuminate\View\View
   */
  public function index(): \Illuminate\View\View // Added return type hint
  {
    // Authorization is handled by authorizeResource in the constructor ('viewAny').
    // The policy's 'viewAny' method should ideally use query scopes to filter
    // the equipment shown based on user roles/permissions (e.g., non-admins only see certain statuses).

    // Fetch all equipment, eager-loading necessary relationships.
    // Eager load active loan transaction, its associated loan application, AND that application's user (the applicant).
    // Also eager load department and center if equipment is linked to them directly.
    // REMOVED 'position' from direct eager loading on Equipment model
    $equipment = Equipment::with(['activeLoanTransaction.loanApplication.user', 'department', 'center']) // <-- REMOVED 'position'
      ->latest() // Order by latest creation date
      ->paginate(10); // Paginate for better performance

    // If you need the position of the assigned employee, you would eager load like this:
    // $equipment = Equipment::with(['activeLoanTransaction.loanApplication.user', 'department', 'center', 'assignedToEmployee.position'])
    //     ->latest()
    //     ->paginate(10);
    // This requires 'assignedToEmployee' relationship in Equipment model and 'position' relationship in Employee model.

    // Return the view with the list of equipment
    // Ensure your view file name matches: resources/views/admin/equipment/index.blade.php
    // Assuming Admin namespace and corresponding view path
    return view('admin.equipment.index', compact('equipment'));
  }

  /**
   * Show the form for creating a new resource (Equipment).
   * Displays the form to add a new equipment item to the inventory.
   *
   * @return \Illuminate\View\View
   */
  public function create(): \Illuminate\View\View // Add return type hint
  {
    // Authorization handled by authorizeResource in the constructor ('create')

    // Load data needed for the form (e.g., lists for dropdowns)
    $departments = Department::all(); // Assuming Department model exists for linking equipment location
    $positions = Position::all(); // Assuming Position model exists for linking equipment location
    $centers = Center::all(); // Assuming Center model exists for linking equipment location
    // You might need Employee model if assigning equipment on creation
    // $employees = Employee::all();

    // Get equipment types and statuses from static properties/methods in the Equipment model
    // This ensures consistency with validation and provides a single source of truth.
    // Ensure these static properties ($equipmentTypes, $availabilityStatuses, $conditionStatuses) exist in App\Models\Equipment.php
    $equipmentTypes = Equipment::$equipmentTypes ?? []; // Use static property from Equipment model, default to empty array if not defined
    $availabilityStatuses = Equipment::$availabilityStatuses ?? []; // Use static property from Equipment model, default to empty array if not defined
    $conditionStatuses = Equipment::$conditionStatuses ?? []; // Use static property from Equipment model, default to empty array if not defined


    // Return the view for creating equipment
    // Ensure your view file name matches: resources/views/admin/equipment/create.blade.php
    // Adjusted view path assuming Admin namespace
    return view('admin.equipment.create', compact('departments', 'positions', 'centers', 'equipmentTypes', 'availabilityStatuses', 'conditionStatuses'));
    // Add 'employees' to compact if loaded: compact('departments', 'positions', 'centers', 'employees', 'equipmentTypes', 'availabilityStatuses', 'conditionStatuses')
  }

  /**
   * Store a newly created resource (Equipment) in storage.
   * Handles validation and delegates creation.
   *
   * @param  \Illuminate\Http\Request  $request  The incoming request.
   * @return \Illuminate\Http\RedirectResponse
   */
  public function store(Request $request): \Illuminate\Http\RedirectResponse // Use Standard Request
  {
    // Authorization handled by authorizeResource in the constructor ('create')

    // 1. Validate the incoming request data directly in the controller
    // Use Equipment model static properties/constants for validation consistency.
    // Ensure static properties ($equipmentTypes, etc.) exist in App\Models\Equipment.php
    $validatedData = $request->validate([
      'tag_id' => 'required|string|max:50|unique:equipment,tag_id', // Tag ID should be unique
      // Validate against the defined enum values from the model static property
      'asset_type' => ['required', 'string', 'max:50', Rule::in(Equipment::$equipmentTypes ?? [])], // Use static property, default to empty array
      'brand' => 'nullable|string|max:100',
      'model' => 'nullable|string|max:100',
      // Serial number should be unique if provided
      'serial_number' => 'nullable|string|max:100|unique:equipment,serial_number',
      'description' => 'nullable|string',
      'purchase_date' => 'nullable|date',
      'warranty_expiry_date' => 'nullable|date|after_or_equal:purchase_date',
      // Validate against the defined enum values from the model static property
      'availability_status' => ['required', Rule::in(Equipment::$availabilityStatuses ?? [])], // Use static property, default to empty array
      'condition_status' => ['required', Rule::in(Equipment::$conditionStatuses ?? [])], // Use static property, default to empty array
      'notes' => 'nullable|string',
      'current_location' => 'nullable|string|max:255',
      'is_active' => 'boolean',
      'in_service' => 'boolean',
      'is_gpr' => 'boolean',
      'value' => 'nullable|numeric',
      'real_price' => 'nullable|numeric',
      'expected_price' => 'nullable|numeric',
      'old_id' => 'nullable|string|max:255',
      'acquisition_date' => 'nullable|date',
      'acquisition_type' => 'nullable|string|max:255',
      'funded_by' => 'nullable|string|max:255',

      // Add validation for relationships if setting them on creation form
      'department_id' => 'nullable|exists:departments,id',
      'center_id' => 'nullable|exists:centers,id',
      'assigned_to_employee_id' => 'nullable|exists:employees,id', // If assigning employee on creation
    ]);


    // Log the creation attempt
    Log::info('Attempting to create new equipment item.', [
      'user_id' => Auth::id(), // Log the user creating the item
      'ip_address' => $request->ip(),
      'validated_data_keys' => array_keys($validatedData), // Log keys
    ]);

    try {
      // 2. Create the new equipment record in the database
      // Assumes CreatedUpdatedDeletedBy trait handles 'created_by'
      $equipment = Equipment::create($validatedData);

      // Log successful creation
      Log::info('Equipment item created successfully.', [
        'equipment_id' => $equipment->id,
        'tag_id' => $equipment->tag_id,
        'asset_type' => $equipment->asset_type,
        'created_by' => Auth::id(), // Log the creator's ID
      ]);

      // 3. Redirect to the 'show' route for the newly created equipment with a success message
      // Changed message to Malay, Adjusted route name assuming Admin namespace
      return redirect()->route('admin.equipment.show', $equipment)
        ->with('success', 'Peralatan berjaya ditambah.'); // Malay success message

    } catch (QueryException $e) {
      // Log specific database errors
      Log::error('Error creating equipment item due to database error.', [
        'user_id' => Auth::id(),
        'error' => $e->getMessage(),
        'ip_address' => $request->ip(),
        'validated_data' => $validatedData, // Log validated data on error
      ]);
      // Changed message to Malay
      return redirect()->back()->withInput()->with('error', 'Gagal menambah peralatan disebabkan ralat pangkalan data.'); // Malay error message
    } catch (Exception $e) {
      // Log any exceptions during creation
      Log::error('Error creating equipment item.', [
        'user_id' => Auth::id(),
        'error' => $e->getMessage(),
        'ip_address' => $request->ip(),
        'validated_data' => $validatedData, // Log validated data on error for debugging
      ]);
      // Changed message to Malay
      return redirect()->back()->withInput()->with('error', 'Gagal menambah peralatan disebabkan ralat: ' . $e->getMessage()); // Malay error message
    }
  }

  /**
   * Display the specified resource (Equipment).
   * Shows details of a specific equipment item.
   *
   * @param  \App\Models\Equipment  $equipment  The equipment instance resolved by route model binding.
   * @return \Illuminate\View\View
   */
  public function show(Equipment $equipment): \Illuminate\View\View // Add return type hint
  {
    // Authorization handled by authorizeResource in the constructor ('view' on the specific $equipment).
    // The policy's 'view' method should verify if the user has permission to see this item (e.g., based on status or role).

    // Log viewing attempt
    Log::info('Viewing equipment item.', [
      'equipment_id' => $equipment->id,
      'tag_id' => $equipment->tag_id,
      'user_id' => Auth::id(), // Log the user viewing the item
    ]);

    // Eager load related data needed for the show view:
    // - Department and Center (if linked directly)
    // - Loan transactions history and related users/officers/applicant on those transactions
    // Ensure loanApplication and specific officer relationships are defined in LoanTransaction model.
    // Ensure user relationship is defined in LoanApplication model.
    // REMOVED 'position' from eager loading on Equipment model in show view
    $equipment->load([
      'department',
      // REMOVED 'position' from here
      'center', // Assuming you want to show Center in the show view
      'loanTransactions.loanApplication.user', // Link transaction to application to get applicant user
      'loanTransactions.issuingOfficer', // Officer who issued
      'loanTransactions.returnAcceptingOfficer', // Officer who accepted return
      // Add other officer relationships if you display them
      // 'loanTransactions.receivingOfficer', // Officer who received at issue
      // 'loanTransactions.returningOfficer', // Officer who returned
    ]);

    // If you need the position of the assigned employee in the show view, you would load it like this:
    // $equipment->load('assignedToEmployee.position');
    // This requires 'assignedToEmployee' relationship in Equipment model and 'position' relationship in Employee model.


    // Return the view to show equipment details
    // Ensure your view file name matches: resources/views/admin/equipment/show.blade.php
    // Adjusted view path assuming Admin namespace
    return view('admin.equipment.show', compact('equipment'));
  }

  /**
   * Show the form for editing the specified resource (Equipment).
   * Displays the form to edit an existing equipment item.
   *
   * @param  \App\Models\Equipment  $equipment  The equipment instance resolved by route model binding.
   * @return \Illuminate\View\View
   */
  public function edit(Equipment $equipment): \Illuminate\View\View // Add return type hint
  {
    // Authorization handled by authorizeResource in the constructor ('update' on the specific $equipment).
    // The policy's 'update' method should verify if the user has permission to edit this item (e.g., based on role or status).

    // Load data needed for the form (e.g., lists for dropdowns)
    $departments = Department::all();
    $positions = Position::all(); // Position is needed here to list positions for dropdowns
    $centers = Center::all(); // Assuming Center model exists
    // You might need Employee model if assigned_to_employee_id can be edited
    // $employees = Employee::all();

    // Get equipment types and statuses from static properties/methods in the Equipment model
    // Ensure these static properties ($equipmentTypes, etc.) exist in App\Models\Equipment.php
    $equipmentTypes = Equipment::$equipmentTypes ?? []; // Use static property from Equipment model, default to empty array if not defined
    $availabilityStatuses = Equipment::$availabilityStatuses ?? []; // Use static property from Equipment model, default to empty array if not defined
    $conditionStatuses = Equipment::$conditionStatuses ?? []; // Use static property from Equipment model, default to empty array if not defined


    // Return the view for editing, passing the equipment data and supporting lists
    // Ensure your view file name matches: resources/views/admin/equipment/edit.blade.php
    // Adjusted view path assuming Admin namespace
    return view('admin.equipment.edit', compact('equipment', 'departments', 'positions', 'centers', 'equipmentTypes', 'availabilityStatuses', 'conditionStatuses'));
    // Add 'employees' to compact if loaded: compact('equipment', 'departments', 'positions', 'centers', 'employees', 'equipmentTypes', 'availabilityStatuses', 'conditionStatuses')
  }

  /**
   * Update the specified resource (Equipment) in storage.
   * Handles validation and updates the resource.
   *
   * @param  \Illuminate\Http\Request  $request  The incoming request.
   * @param  \App\Models\Equipment  $equipment  The equipment instance resolved by route model binding.
   * @return \Illuminate\Http\RedirectResponse
   */
  public function update(Request $request, Equipment $equipment): \Illuminate\Http\RedirectResponse // Use Standard Request
  {
    // Authorization handled by authorizeResource in the constructor ('update' on the specific $equipment).

    // 1. Validate the incoming request data directly in the controller
    // Use Rule::unique ignore to allow the equipment's current tag ID/serial number
    // Use Equipment model static properties/constants for validation consistency.
    $validatedData = $request->validate([
      'tag_id' => ['required', 'string', 'max:50', Rule::unique('equipment', 'tag_id')->ignore($equipment->id)],
      // Validate against the defined enum values from the model static property
      'asset_type' => ['required', 'string', 'max:50', Rule::in(Equipment::$equipmentTypes ?? [])], // Use static property, default to empty array
      'brand' => 'nullable|string|max:100',
      'model' => 'nullable|string|max:100',
      // Serial number should be unique if provided, ignoring the current equipment
      'serial_number' => ['nullable', 'string', 'max:100', Rule::unique('equipment', 'serial_number')->ignore($equipment->id)],
      'description' => 'nullable|string',
      'purchase_date' => 'nullable|date',
      'warranty_expiry_date' => 'nullable|date|after_or_equal:purchase_date',
      // Validate against the defined enum values from the model static property
      'availability_status' => ['required', Rule::in(Equipment::$availabilityStatuses ?? [])], // Use static property, default to empty array
      'condition_status' => ['required', Rule::in(Equipment::$conditionStatuses ?? [])], // Use static property, default to empty array
      'notes' => 'nullable|string',
      'current_location' => 'nullable|string|max:255',
      'is_active' => 'boolean',
      'in_service' => 'boolean',
      'is_gpr' => 'boolean',
      'value' => 'nullable|numeric',
      'real_price' => 'nullable|numeric',
      'expected_price' => 'nullable|numeric',
      'old_id' => 'nullable|string|max:255',
      'acquisition_date' => 'nullable|date',
      'acquisition_type' => 'nullable|string|max:255',
      'funded_by' => 'nullable|string|max:255',

      // Add validation for relationships if updating them via the form
      'department_id' => 'nullable|exists:departments,id',
      'center_id' => 'nullable|exists:centers,id',
      'assigned_to_employee_id' => 'nullable|exists:employees,id', // If updating assigned employee
    ]);


    // Log update attempt
    Log::info('Attempting to update equipment item.', [
      'equipment_id' => $equipment->id,
      'tag_id' => $equipment->tag_id,
      'user_id' => Auth::id(),
      'ip_address' => $request->ip(),
      'validated_data_keys' => array_keys($validatedData), // Log keys
    ]);

    try {
      // 2. Update the equipment model
      // Assumes CreatedUpdatedDeletedBy trait handles 'updated_by'
      $equipment->update($validatedData);

      // Log successful update
      Log::info('Equipment item updated successfully.', [
        'equipment_id' => $equipment->id,
        'user_id' => Auth::id(),
      ]);

      // 3. Redirect to the equipment show page or index page with a success message
      // Changed message to Malay, Adjusted route name assuming Admin namespace
      return redirect()->route('admin.equipment.show', $equipment)
        ->with('success', 'Peralatan berjaya dikemaskini.'); // Malay success message

    } catch (QueryException $e) {
      // Log specific database errors
      Log::error('Error updating equipment item due to database error.', [
        'equipment_id' => $equipment->id,
        'user_id' => Auth::id(),
        'error' => $e->getMessage(),
        'ip_address' => $request->ip(),
        'validated_data' => $validatedData, // Log validated data on error
      ]);
      // Changed message to Malay
      return redirect()->back()->withInput()->with('error', 'Gagal mengemaskini peralatan disebabkan ralat pangkalan data.'); // Malay error message
    } catch (Exception $e) {
      // Log any exceptions during update
      Log::error('Error updating equipment item.', [
        'equipment_id' => $equipment->id,
        'user_id' => Auth::id(),
        'error' => $e->getMessage(),
        'ip_address' => $request->ip(),
        'validated_data' => $validatedData, // Log validated data on error
      ]);
      // Changed message to Malay
      return redirect()->back()->withInput()->with('error', 'Gagal mengemaskini peralatan disebabkan ralat: ' . $e->getMessage()); // Malay error message
    }
  }

  /**
   * Remove the specified resource (Equipment) from storage.
   * Typically only allowed if the equipment has no active loans or loan history.
   *
   * @param  \App\Models\Equipment  $equipment  The equipment instance resolved by route model binding.
   * @return \Illuminate\Http\RedirectResponse
   */
  public function destroy(Equipment $equipment): \Illuminate\Http\RedirectResponse // Add return type hint
  {
    // Authorization handled by authorizeResource in the constructor ('delete' on the specific $equipment).
    // The policy's 'delete' method should verify user permission (e.g., admin) and if deletion is allowed based on status/history.

    // Log deletion attempt
    Log::info('Attempting to delete equipment item.', [
      'equipment_id' => $equipment->id,
      'tag_id' => $equipment->tag_id,
      'user_id' => Auth::id(),
      'current_status' => $equipment->availability_status, // Use availability status property
      'ip_address' => request()->ip(),
    ]);


    // Prevent deletion if the equipment has any associated loan transactions (active or historical).
    // This is a critical business rule to maintain data integrity.
    // The loanTransactions relationship must be defined in the Equipment model.
    if ($equipment->loanTransactions()->exists()) {
      Log::warning('Attempted to delete equipment with existing loan history.', [
        'equipment_id' => $equipment->id,
        'user_id' => Auth::id(),
      ]);
      // Changed message to Malay
      return redirect()->back()->with('error', 'Tidak dapat memadam peralatan kerana terdapat rekod pinjaman berkaitan.'); // Malay error message
    }

    // Consider using Soft Deletes for Equipment model if retaining historical inventory data is needed.
    // If Soft Deletes are used, $equipment->delete() will perform a soft delete.

    try {
      // Delete the equipment (soft delete if trait is used)
      $equipmentId = $equipment->id; // Store ID before deletion
      $equipmentTag = $equipment->tag_id; // Store Tag ID before deletion

      // Assumes CreatedUpdatedDeletedBy trait or an observer sets deleted_by and SoftDeletes handles deleted_at
      $equipment->delete(); // Performs soft delete if SoftDeletes trait is used

      // Log successful deletion (soft or permanent)
      Log::info('Equipment item deleted successfully.', [
        'equipment_id' => $equipmentId, // Use stored ID
        'tag_id' => $equipmentTag, // Use stored Tag ID
        'user_id' => Auth::id(),
      ]);


      // Redirect to the index page with a success message
      // Changed message to Malay, Adjusted route name assuming Admin namespace
      return redirect()->route('admin.equipment.index')
        ->with('success', 'Peralatan berjaya dibuang.'); // Malay success message

    } catch (QueryException $e) {
      // Log specific database errors during deletion (e.g., foreign key constraints from other tables not caught by the exists() check)
      Log::error('Failed to delete equipment item due to database constraint.', [
        'equipment_id' => $equipment->id ?? 'unknown',
        'user_id' => Auth::id(),
        'error' => $e->getMessage(),
        'ip_address' => request()->ip(),
      ]);
      // Changed message to Malay
      return redirect()->back()->with('error', 'Gagal memadam peralatan disebabkan ralat pangkalan data.'); // Malay error message
    } catch (Exception $e) {
      // Log any other unexpected errors during deletion
      Log::error('An unexpected error occurred during equipment item deletion.', [
        'equipment_id' => $equipment->id ?? 'unknown',
        'user_id' => Auth::id(),
        'error' => $e->getMessage(),
        'ip_address' => request()->ip(),
      ]);
      // Changed message to Malay
      return redirect()->back()->with('error', 'Gagal membuang Peralatan ICT disebabkan ralat tidak dijangka.'); // Malay error message
    }
  }

  // You can add other methods here if needed, e.g., for bulk actions or specific reports
}
