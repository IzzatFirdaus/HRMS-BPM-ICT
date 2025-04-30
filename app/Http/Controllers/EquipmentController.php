<?php

namespace App\Http\Controllers; // Ensure the namespace is correct for your project. Consider App\Http\Controllers\Admin if equipment management is an admin function.

use App\Models\Equipment; // Import the Equipment model
use App\Models\Department; // Import Department for relationships/form data (if equipment is linked directly)
use App\Models\Position; // Import Position for relationships/form data (if equipment is linked directly)
// Assuming an EquipmentService handles equipment inventory management logic
use App\Services\EquipmentService; // Import EquipmentService
// Assuming a StoreEquipmentRequest Form Request exists for validation
use App\Http\Requests\StoreEquipmentRequest; // Import Form Request for creation validation
// Assuming an UpdateEquipmentRequest Form Request exists for update validation
use App\Http\Requests\UpdateEquipmentRequest; // Import Form Request for update validation (create this if it doesn't exist)
use Illuminate\Http\Request; // Standard Request object (less needed with Form Requests)
use Illuminate\Validation\Rule; // Import Rule for validation rules (e.g., unique, in)
use Illuminate\Support\Facades\Auth; // Import Auth facade for accessing authenticated user
use Illuminate\Support\Facades\Gate; // Import Gate (less needed with Policies)
use Illuminate\Support\Facades\Log; // Import Log facade for logging
use Exception; // Import Exception for general errors
use Illuminate\Database\QueryException; // Import QueryException for database errors


class EquipmentController extends Controller
{
  protected $equipmentService; // Use the EquipmentService

  /**
   * Inject the EquipmentService and apply authentication/authorization middleware.
   *
   * @param \App\Services\EquipmentService $equipmentService The equipment service instance.
   */
  public function __construct(EquipmentService $equipmentService) // Inject EquipmentService
  {
    // Apply authentication middleware to all methods in this controller
    $this->middleware('auth');

    // Apply authorization policy checks automatically for resource methods
    // Assumes an EquipmentPolicy exists and is registered.
    // Policy methods: viewAny, view, create, update, delete
    $this->authorizeResource(Equipment::class, 'equipment'); // Use 'equipment' as parameter name

    $this->equipmentService = $equipmentService; // Assign the injected service
  }

  /**
   * Display a listing of the resource (Equipment).
   * Fetches equipment records the user is authorized to view.
   *
   * @return \Illuminate\View\View
   */
  public function index(): \Illuminate\View\View // Add return type hint
  {
    // Authorization handled by authorizeResource in the constructor ('viewAny').
    // The policy's 'viewAny' method should ideally handle filtering based on roles/permissions (using scopes).
    // For equipment, most users might see all available/on_loan items, while admins see all including disposed/maintenance.

    // Fetch all equipment with relationships, ordered by latest.
    // Eager load active loan transaction and the user associated with it for quick view status.
    // Also eager load department and position if equipment is linked to them directly.
    $equipment = Equipment::query()
      ->with(['activeLoanTransaction.user', 'department', 'position']) // Eager load active loan and user on it, plus direct links
      ->latest(); // Order by latest creation date

    // --- Optional Filtering (If not fully handled by Policy Scopes) ---
    // Example: If non-admin users should only see Available or On Loan equipment.
    // if (!Auth::user()->can('viewAllEquipment')) { // Assuming a permission check
    //      $equipment->whereIn('status', ['available', 'on_loan']);
    // }
    // --- End Filtering ---

    $equipment = $equipment->paginate(10); // Paginate for better performance

    // Return the view with the list of equipment
    // Ensure your view file name matches: resources/views/equipment/index.blade.php
    return view('equipment.index', compact('equipment')); // Pass as 'equipment'
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

    // Define equipment types and statuses as arrays or from a config file
    // Align asset types with the system design document's enum examples (lowercase)
    $equipmentTypes = ['laptop', 'projector', 'printer', 'monitor', 'desktop', 'other']; // Match enum in migration/design
    $equipmentStatuses = ['available', 'on_loan', 'under_maintenance', 'disposed']; // Match enum in migration/design

    // Return the view for creating equipment
    // Ensure your view file name matches: resources/views/equipment/create.blade.php
    return view('equipment.create', compact('departments', 'positions', 'equipmentTypes', 'equipmentStatuses'));
  }

  /**
   * Store a newly created resource (Equipment) in storage.
   * Uses the StoreEquipmentRequest Form Request for validation.
   * Delegates the creation logic to the EquipmentService.
   *
   * @param  \App\Http\Requests\StoreEquipmentRequest  $request  The validated incoming request.
   * @return \Illuminate\Http\RedirectResponse
   */
  public function store(StoreEquipmentRequest $request): \Illuminate\Http\RedirectResponse // Use Form Request, add return type hint
  {
    // Authorization handled by authorizeResource in the constructor ('create')
    // Validation handled automatically by StoreEquipmentRequest

    // Log the creation attempt
    Log::info('Attempting to create new equipment item.', [
      'user_id' => Auth::id(), // Log the user creating the item
      'ip_address' => $request->ip(),
      'validated_data_keys' => array_keys($request->validated()), // Log keys, not values for sensitive data
    ]);

    try {
      // Delegate the creation logic to the EquipmentService.
      // The service should handle:
      // - Creating the Equipment model instance with validated data.
      // - Setting initial status (usually 'available').
      // - Saving the equipment record to the database.
      $equipment = $this->equipmentService->createEquipment($request->validated());

      // Log successful creation
      Log::info('Equipment item created successfully.', [
        'equipment_id' => $equipment->id,
        'tag_id' => $equipment->tag_id,
        'asset_type' => $equipment->asset_type,
        'created_by' => Auth::id(),
      ]);

      // Redirect to the 'show' route for the newly created equipment with a success message
      // Changed message to Malay
      return redirect()->route('equipment.show', $equipment)
        ->with('success', 'Peralatan berjaya ditambah.'); // Malay success message

    } catch (Exception $e) {
      // Log any exceptions during creation
      Log::error('Error creating equipment item.', [
        'user_id' => Auth::id(),
        'error' => $e->getMessage(),
        'ip_address' => $request->ip(),
        'validated_data' => $request->validated(), // Log validated data on error for debugging
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
    // - Department and Position (if linked directly)
    // - Loan transactions history and related users/officers on those transactions
    $equipment->load([
      'department',
      'position',
      'loanTransactions.user', // Applicant/Responsible Officer
      'loanTransactions.issuingOfficer', // Officer who issued
      'loanTransactions.returnAcceptingOfficer' // Officer who accepted return
    ]);

    // Return the view to show equipment details
    // Ensure your view file name matches: resources/views/equipment/show.blade.php
    return view('equipment.show', compact('equipment')); // Pass as 'equipment'
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
    $positions = Position::all();
    $equipmentTypes = ['laptop', 'projector', 'printer', 'monitor', 'desktop', 'other']; // Match enum in migration/design
    $equipmentStatuses = ['available', 'on_loan', 'under_maintenance', 'disposed']; // Match enum in migration/design


    // Return the view for editing, passing the equipment data and supporting lists
    // Ensure your view file name matches: resources/views/equipment/edit.blade.php
    return view('equipment.edit', compact('equipment', 'departments', 'positions', 'equipmentTypes', 'equipmentStatuses')); // Pass as 'equipment'
  }

  /**
   * Update the specified resource (Equipment) in storage.
   * Uses the UpdateEquipmentRequest Form Request for validation.
   * Delegates the update logic to the EquipmentService.
   *
   * @param  \App\Http\Requests\UpdateEquipmentRequest  $request  The validated incoming request.
   * @param  \App\Models\Equipment  $equipment  The equipment instance resolved by route model binding.
   * @return \Illuminate\Http\RedirectResponse
   */
  public function update(UpdateEquipmentRequest $request, Equipment $equipment): \Illuminate\Http\RedirectResponse // Use Form Request, add return type hint
  {
    // Authorization handled by authorizeResource in the constructor ('update' on the specific $equipment).
    // Validation handled automatically by UpdateEquipmentRequest.
    // Update policy should verify user permission and status.

    // Log update attempt
    Log::info('Attempting to update equipment item.', [
      'equipment_id' => $equipment->id,
      'tag_id' => $equipment->tag_id,
      'user_id' => Auth::id(),
      'ip_address' => $request->ip(),
      'validated_data_keys' => array_keys($request->validated()), // Log keys, not values
    ]);

    try {
      // Delegate the update logic to the EquipmentService.
      // The service should handle:
      // - Updating the equipment record with the validated data.
      // - Ensuring updates are valid based on the current status (e.g., cannot change status directly unless specific permission).
      // - Saving changes.
      $updated = $this->equipmentService->updateEquipment($equipment, $request->validated()); // Assumes updateEquipment method exists

      if ($updated) {
        // Log successful update
        Log::info('Equipment item updated successfully.', [
          'equipment_id' => $equipment->id,
          'user_id' => Auth::id(),
        ]);

        // Changed message to Malay
        return redirect()->route('equipment.show', $equipment)
          ->with('success', 'Peralatan berjaya dikemaskini.'); // Malay success message
      } else {
        // Log failure (might indicate a service-level rule prevented update)
        Log::warning('Equipment item update failed via service.', [
          'equipment_id' => $equipment->id,
          'user_id' => Auth::id(),
        ]);
        // Changed message to Malay
        return redirect()->back()->withInput()->with('error', 'Gagal mengemaskini peralatan.'); // Malay error message
      }
    } catch (Exception $e) {
      // Log any exceptions during update
      Log::error('Error updating equipment item.', [
        'equipment_id' => $equipment->id,
        'user_id' => Auth::id(),
        'error' => $e->getMessage(),
        'ip_address' => $request->ip(),
        'validated_data' => $request->validated(), // Log validated data on error
      ]);
      // Changed message to Malay
      return redirect()->back()->withInput()->with('error', 'Gagal mengemaskini peralatan disebabkan ralat: ' . $e->getMessage()); // Malay error message
    }
  }

  /**
   * Remove the specified resource (Equipment) from storage.
   * Typically only allowed if the equipment has no active loans or loan history.
   * Delegates deletion logic to the EquipmentService or handles directly after check.
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
      'current_status' => $equipment->status,
      'ip_address' => request()->ip(),
    ]);


    // Prevent deletion if the equipment has any associated loan transactions (active or historical).
    // This is a critical business rule to maintain data integrity.
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
      // Delegate deletion logic to the service if cleanup/related actions are needed
      // $deleted = $this->equipmentService->deleteEquipment($equipment); // Assumes deleteEquipment method exists

      // Or delete directly after the history check:
      $equipmentId = $equipment->id; // Store ID before deletion
      $equipmentTag = $equipment->tag_id; // Store Tag ID before deletion
      $equipment->delete(); // Performs soft delete if SoftDeletes trait is used

      // Log successful deletion (soft or permanent)
      Log::info('Equipment item deleted successfully.', [
        'equipment_id' => $equipmentId, // Use stored ID
        'tag_id' => $equipmentTag, // Use stored Tag ID
        'user_id' => Auth::id(),
      ]);


      // Redirect to the index page with a success message
      // Changed message to Malay
      return redirect()->route('equipment.index')
        ->with('success', 'Peralatan berjaya dibuang.'); // Malay success message

    } catch (QueryException $e) {
      // Log specific database errors during deletion
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
      return redirect()->back()->with('error', 'Gagal memadam peralatan disebabkan ralat tidak dijangka.'); // Malay error message
    }
  }

  // No other standard resource methods are needed for this controller.
  // Loan management (issuance, return) is handled by LoanTransactionController or related logic.
}
