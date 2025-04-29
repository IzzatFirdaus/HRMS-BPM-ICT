<?php

namespace App\Livewire\HumanResource\Attendance;

use App\Exports\ExportFingerprints; // Assumed to work with the fetched data
use App\Imports\ImportFingerprints; // Assumed to work and accept parameters
use App\Livewire\Sections\Navbar\Navbar; // Assumed to exist for event dispatch
use App\Models\Employee; // Assumed to exist
use App\Models\Fingerprint; // Assumed to exist and have static filteredFingerprints method
use App\Models\Import; // Assumed to exist for tracking imports
use App\Notifications\DefaultNotification; // Assumed to exist
use Carbon\Carbon; // For date/time manipulation
use Exception; // General exception handling
use Illuminate\Support\Facades\Auth; // For authenticated user
use Illuminate\Support\Facades\Notification; // For sending notifications
use Illuminate\Support\Facades\Storage; // For file storage
use Illuminate\Support\Facades\Log; // Added for logging
use Livewire\Attributes\Renderless; // For Livewire v3+ renderless methods
use Livewire\Attributes\Computed; // For Livewire v3+ computed properties
use Livewire\Attributes\Locked; // For Livewire v3+ locked properties
use Livewire\Attributes\On; // For Livewire v3+ event listeners
use Livewire\Component; // Base Livewire component
use Livewire\WithFileUploads; // Trait for file uploads
use Livewire\WithPagination; // Trait for pagination
use Maatwebsite\Excel\Facades\Excel; // Facade for Excel operations
use Maatwebsite\Excel\Validators\ValidationException; // Specific import validation exception


// Assumptions:
// 1. Fingerprint model exists and has columns: 'employee_id', 'date', 'check_in', 'check_out', 'log'.
// 2. Fingerprint model has a static method or scope 'filteredFingerprints($employeeId, $fromDate, $toDate, $isAbsence, $isOneFingerprint)'
//    which returns an Eloquent query builder.
// 3. Fingerprint model casts 'date' to 'date' and 'check_in'/'check_out' to appropriate time/datetime formats.
// 4. Employee model exists and has 'id' and other attributes.
// 5. Import model exists and has fillable attributes for tracking imports.
// 6. ExportFingerprints and ImportFingerprints classes work correctly with the data structure.
// 7. Navbar component exists for event dispatching.
// 8. DefaultNotification exists.
// 9. Livewire v3+ is used for attributes.

class Fingerprints extends Component
{
  use WithFileUploads, WithPagination;

  // Pagination specific setting (assuming Bootstrap styling in the view)
  protected $paginationTheme = 'bootstrap';

  // 👉 State Variables (Public properties that sync with the view)

  public $selectedEmployeeId; // Filter: Employee dropdown value
  public $dateRange; // Filter: Date range input string
  public bool $isAbsence = false; // Filter: Toggle absence records
  public bool $isOneFingerprint = false; // Filter: Toggle one fingerprint records

  public ?int $confirmedId = null; // State: ID of the fingerprint record pending deletion confirmation

  public bool $isEdit = false; // State: Is the modal/sidebar in edit mode
  public ?Fingerprint $fingerprint = null; // State: Fingerprint model being edited

  // State for create/edit form fields
  public string $date = '';
  public string $checkIn = ''; // Use string for time input binding (HH:MM)
  public string $checkOut = ''; // Use string for time input binding (HH:MM)

  #[Locked] // Prevent external manipulation via view
  public $file; // State: Uploaded file for import

  // Internal properties derived from state (optional, could be Computed)
  protected $fromDate;
  protected $toDate;


  // 👉 Computed Properties (Livewire v3+ - Cached data that depends on state)

  #[Computed]
  public function employees(): \Illuminate\Database\Eloquent\Collection
  {
    // Cache all employees for the filter dropdown
    // Consider optimizing this for large employee lists (e.g., search dropdown)
    return Employee::all();
  }

  #[Computed]
  public function selectedEmployee(): ?Employee
  {
    // Fetch the selected employee model if an ID is set
    return $this->selectedEmployeeId ? Employee::find($this->selectedEmployeeId) : null;
  }

  #[Computed]
  public function fingerprints(): \Illuminate\Contracts\Pagination\LengthAwarePaginator
  {
    // This computed property fetches and filters the fingerprint data
    // It will be automatically re-evaluated when its dependencies ($selectedEmployeeId, $dateRange, $isAbsence, $isOneFingerprint, pagination page) change

    // Parse date range string into Carbon dates
    $fromDate = null;
    $toDate = null;
    if ($this->dateRange) {
      $dates = explode(' to ', $this->dateRange);
      if (count($dates) === 2) {
        try {
          $fromDate = Carbon::parse(trim($dates[0]))->startOfDay();
          $toDate = Carbon::parse(trim($dates[1]))->endOfDay();
        } catch (\Exception $e) {
          // Log invalid date format but don't stop rendering or throw error
          Log::warning('Fingerprints component: Invalid dateRange format received for filtering: ' . $this->dateRange, ['exception' => $e]);
          // Keep fromDate/toDate null, effectively removing the date filter
          $fromDate = null;
          $toDate = null;
        }
      }
    }

    // Store derived dates in properties if needed elsewhere, otherwise remove these lines
    $this->fromDate = $fromDate;
    $this->toDate = $toDate;

    // Use the static filteredFingerprints method from the Fingerprint model
    // Assumes this method exists and handles all the filtering logic
    $query = Fingerprint::filteredFingerprints(
      $this->selectedEmployeeId, // Pass selected employee ID
      $this->fromDate,           // Pass parsed from date
      $this->toDate,             // Pass parsed to date
      $this->isAbsence,          // Pass absence filter state
      $this->isOneFingerprint    // Pass one fingerprint filter state
    );

    // Return paginated results
    return $query->paginate(7);
  }


  // 👉 Lifecycle Hooks

  public function mount()
  {
    // Set initial selected employee ID to the logged-in user's employee ID
    $this->selectedEmployeeId = Auth::user()->employee_id;

    // Initialize date range input string to last month to today
    $currentDate = Carbon::now();
    $previousMonth = $currentDate->copy()->subMonth();
    // Format date range string for the view input (e.g., YYYY-MM-DD to YYYY-MM-DD)
    $this->dateRange = $previousMonth->format('Y-m-d') . ' to ' . $currentDate->format('Y-m-d');

    // Initial data fetch is implicitly handled by the 'fingerprints' computed property when the component renders
  }

  // No explicit updated methods are needed for state properties like $selectedEmployeeId, $dateRange, etc.
  // because the 'fingerprints' computed property depends on them and will re-evaluate automatically.
  // Pagination reset is handled by Livewire when computed property dependencies change.


  // Render method simply returns the view
  public function render()
  {
    // Data ('fingerprints', 'employees', 'selectedEmployee') is accessed directly
    // from computed properties in the view, no need to pass explicitly.
    return view('livewire.human-resource.attendance.fingerprints');
  }


  // 👉 Fingerprint Management Actions

  // Validation rules for adding/editing a fingerprint record
  protected function rules()
  {
    // Note: Assuming 'date' is YYYY-MM-DD, 'checkIn'/'checkOut' are HH:MM strings from input
    return [
      'date' => 'required|date_format:Y-m-d', // Ensure date is required and in correct format
      'checkIn' => 'required|date_format:H:i', // Ensure check-in is required and in HH:MM format
      'checkOut' => 'required|date_format:H:i|after:checkIn', // Ensure check-out is required, HH:MM format, and after check-in
      // Add validation for selectedEmployeeId if the form allows changing it in edit mode
      // 'selectedEmployeeId' => 'required|exists:employees,id', // Example validation
    ];
  }

  // Custom validation messages (optional)
  protected function messages()
  {
    return [
      'date.required' => __('Date is required.'),
      'date.date_format' => __('Date must be in YYYY-MM-DD format.'),
      'checkIn.required' => __('Check-in time is required.'),
      'checkIn.date_format' => __('Check-in time must be in HH:MM format.'),
      'checkOut.required' => __('Check-out time is required.'),
      'checkOut.date_format' => __('Check-out time must be in HH:MM format.'),
      'checkOut.after' => __('Check-out time must be after check-in time.'),
      // Add messages for other rules/fields as needed
    ];
  }


  // Submit the fingerprint creation or update form
  public function submitFingerprint()
  {
    // Validate the form data using the rules() method
    $this->validate();

    // Use a database transaction for atomicity (all or nothing)
    try {
      \Illuminate\Support\Facades\DB::transaction(function () { // Use full namespace for DB in transaction closure
        if ($this->isEdit) {
          $this->editFingerprint();
        } else {
          $this->addFingerprint();
        }
      });

      // Success feedback and dispatch events after successful transaction
      session()->flash('success', $this->isEdit ? __('Success, fingerprint updated successfully!') : __('Success, fingerprint added successfully!'));
      // No scrollToTop in original, keep original dispatch behavior
      $this->dispatch('closeCanvas', elementId: '#addRecordSidebar'); // Assuming a JS event to close sidebar/modal
      $this->dispatch('toastr', type: 'success', message: __('Going Well!')); // Assuming a JS event for toastr

    } catch (\Exception $e) {
      // Log the exception
      Log::error('Fingerprint submit failed: ' . $e->getMessage(), [
        'user_id' => Auth::id(),
        'fingerprint_data' => ['date' => $this->date, 'check_in' => $this->checkIn, 'check_out' => $this->checkOut],
        'is_edit' => $this->isEdit,
        'fingerprint_id' => $this->fingerprint?->id, // Use null-safe operator
        'exception' => $e,
      ]);

      // Flash and dispatch error feedback
      session()->flash('error', __('An error occurred while saving the fingerprint record.') . ' ' . $e->getMessage()); // Show exception message for debugging, or a generic message
      $this->dispatch('toastr', type: 'error', message: __('Operation Failed!')); // Assuming a JS event for toastr
      // Keep sidebar/modal open on error? Original closed. Let's keep original behavior.
      $this->dispatch('closeCanvas', elementId: '#addRecordSidebar');
    } finally {
      // Reset modal state and clear form fields regardless of success or failure
      $this->reset('isEdit', 'fingerprint', 'date', 'checkIn', 'checkOut');
    }
  }


  // Show modal/sidebar for adding a new fingerprint record
  #[Renderless] // Prevents a full render before dispatching JS event
  public function showNewFingerprintModal(): void // Use void return type
  {
    $this->reset('isEdit', 'fingerprint', 'date', 'checkIn', 'checkOut'); // Reset all relevant state
    // Dispatch JS event to open the modal/sidebar
    // $this->dispatch('openModal', elementId: '#addRecordSidebar');
  }

  // Show modal/sidebar for editing an existing fingerprint record
  #[Renderless] // Prevents a full render before dispatching JS event
  public function showEditFingerprintModal(Fingerprint $fingerprint): void // Use route model binding, void return type
  {
    $this->isEdit = true; // Set edit mode
    $this->fingerprint = $fingerprint; // Store the model for update

    // Populate the form fields with the record's data
    // Ensure dates/times are formatted correctly for input fields
    $this->date = $fingerprint->date ? Carbon::parse($fingerprint->date)->format('Y-m-d') : ''; // Format date
    $this->checkIn = $fingerprint->check_in ?? ''; // Use nullish coalescing for empty string default
    $this->checkOut = $fingerprint->check_out ?? ''; // Use nullish coalescing for empty string default

    // Dispatch JS event to open the modal/sidebar if not handled by wire:click
    // $this->dispatch('openModal', elementId: '#addRecordSidebar');
  }

  // Add a new fingerprint record
  protected function addFingerprint(): void // Use protected as called internally, void return type
  {
    // Assumes Fingerprint model is fillable/guarded correctly
    // check_in/check_out might need Carbon parsing if not automatically cast
    Fingerprint::create([
      'employee_id' => $this->selectedEmployeeId, // Assuming employee_id is set and valid
      'date' => $this->date, // Assuming date is in YYYY-MM-DD format
      'check_in' => $this->checkIn, // Assuming check_in is HH:MM string
      'check_out' => $this->checkOut, // Assuming check_out is HH:MM string
      'log' => $this->checkIn . ' ' . $this->checkOut, // Constructing log string
      // Add created_by/updated_by if your model/trait handles it
    ]);

    // Success feedback and dispatching is handled in submitFingerprint() after transaction
  }

  // Update an existing fingerprint record
  protected function editFingerprint(): void // Use protected as called internally, void return type
  {
    // Find the record again within the transaction for safety
    // Use $this->fingerprint which was loaded in showEditFingerprintModal
    if (!$this->fingerprint) {
      // Should not happen if flow is correct, but defensive check
      Log::error('Fingerprints component: Attempted to update non-existent fingerprint model.', ['user_id' => Auth::id(), 'is_edit' => $this->isEdit]);
      throw new Exception(__('Fingerprint record not found for update.')); // Throw exception to trigger transaction rollback and error handling
    }

    // Update the record attributes
    $this->fingerprint->update([
      // 'employee_id' => $this->selectedEmployeeId, // Uncomment if employee can be changed
      'date' => $this->date, // Assuming date is in YYYY-MM-DD format
      'check_in' => $this->checkIn, // Assuming check_in is HH:MM string
      'check_out' => $this->checkOut, // Assuming check_out is HH:MM string
      'log' => $this->checkIn . ' ' . $this->checkOut, // Constructing log string
      // Add updated_by if your model/trait handles it
    ]);

    // Success feedback and dispatching is handled in submitFingerprint() after transaction
    // Reset modal state is handled in submitFingerprint()
  }

  // Confirm deletion of a fingerprint record
  public function confirmDeleteFingerprint(int $fingerprintId): void // Accept ID, void return type
  {
    $this->confirmedId = $fingerprintId; // Store the ID for confirmation
    // Dispatch event to show confirmation modal (e.g., SweetAlert, Bootstrap modal)
    // $this->dispatch('openConfirmModal', elementId: '#deleteConfirmationModal');
  }

  // Perform the deletion after confirmation
  public function deleteFingerprint(): void // Void return type
  {
    // Check if an ID is confirmed
    if ($this->confirmedId === null) {
      return; // No ID to delete
    }

    // Find the record to delete
    $record = Fingerprint::find($this->confirmedId);

    if (!$record) {
      // Record not found (maybe already deleted)
      session()->flash('error', __('Fingerprint record not found for deletion.'));
      $this->dispatch('toastr', type: 'error', message: __('Not Found!'));
    } else {
      // Perform the deletion
      try {
        $record->delete(); // Use model delete method (handles soft deletes if enabled)

        session()->flash('success', __('Success, fingerprint record deleted successfully!'));
        $this->dispatch('toastr', type: 'success', message: __('Going Well!'));
      } catch (\Exception $e) {
        // Handle potential database errors during deletion
        Log::error('Fingerprints component: Failed to delete fingerprint record.', ['user_id' => Auth::id(), 'fingerprint_id' => $this->confirmedId, 'exception' => $e]);
        session()->flash('error', __('An error occurred while deleting the fingerprint record.') . ' ' . $e->getMessage()); // Show exception message or generic error
        $this->dispatch('toastr', type: 'error', message: __('Operation Failed!'));
      }
    }

    // Reset confirmedId and close confirmation modal (if using one)
    $this->confirmedId = null;
    // $this->dispatch('closeConfirmModal', elementId: '#deleteConfirmationModal');
  }

  // 👉 Import/Export Actions

  // Validation rules for import
  protected function importRules()
  {
    return [
      'file' => 'required|mimes:xlsx,xls', // Accept both xlsx and xls
    ];
  }

  // Custom validation messages for import
  protected function importMessages()
  {
    return [
      'file.required' => __('Please select a file to upload.'), // Translated
      'file.mimes' => __('Only Excel files (xlsx, xls) are accepted.'), // Translated
    ];
  }

  // Handle Excel import
  public function importFromExcel()
  {
    // Validate the uploaded file
    $this->validate($this->importRules(), $this->importMessages());

    try {
      // Create a record in the 'imports' table
      $fileRecord = Import::create([
        'file_name' => $this->file->getClientOriginalName(),
        'file_size' => $this->file->getSize(),
        'file_ext' => $this->file->getClientOriginalExtension(),
        'file_type' => $this->file->getClientMimeType(),
        'status' => 'waiting', // Set initial status
        'created_by' => Auth::id(), // Assuming created_by stores User ID
      ]);

      // Store the file
      $destinationPath = 'imports';
      // Use a unique filename to prevent overwriting, maybe include $fileRecord->id
      $fileName = $fileRecord->id . '_' . $this->file->getClientOriginalName();
      $path = Storage::putFileAs($destinationPath, $this->file, $fileName);

      // Dispatch event to activate progress bar (if import is async)
      $this->dispatch('activeProgressBar')->to(Navbar::class);

      // --- Perform the import ---
      // Assuming ImportFingerprints handles the actual data import logic
      // Pass necessary context (user ID, import record ID, file path)
      $import = new ImportFingerprints(Auth::user()->id, $fileRecord->id);

      // Note: If this is synchronous, it might take time and block the UI.
      // Consider making the import process a Queue Job for large files.
      Excel::import($import, Storage::path($path)); // Use Storage::path() to get the local file path

      // --- Handle Import Results (Assuming synchronous import) ---
      // Update import record status based on results
      $fileRecord->status = 'completed'; // Or 'completed_with_errors'
      // Optional: Store success/failure counts from the importer ($import->getSuccessCount(), $import->getFailureCount())
      // $fileRecord->successful_rows = $import->getSuccessCount();
      // $fileRecord->failed_rows = $import->getFailureCount();
      // $fileRecord->error_details = json_encode($import->failures()); // Store detailed errors if available
      $fileRecord->updated_by = Auth::id(); // Assuming updated_by stores User ID
      $fileRecord->save();


      // Send notification upon completion (success or failure summary)
      // Assuming DefaultNotification can handle different messages or data
      $notificationMessage = __('Successfully imported the fingerprint file.');
      // if ($import->failures()->isNotEmpty()) {
      //      $notificationMessage = __('Fingerprint import completed with errors. :failed rows failed.', ['failed' => $import->getFailureCount()]);
      // }
      Notification::send(
        Auth::user(),
        new DefaultNotification(Auth::user()->id, $notificationMessage) // Translated notification message
      );
      $this->dispatch('refreshNotifications')->to(Navbar::class); // Dispatch event to refresh navbar

      // Flash success message
      session()->flash('success', __('Well done! The file has been imported successfully.'));
      // Flash warning/error if there were failures reported by the importer
      // if ($import->failures()->isNotEmpty()) {
      //      session()->flash('warning', $notificationMessage);
      // }


    } catch (ValidationException $e) {
      // Catch validation exceptions specifically from Maatwebsite/Excel
      $failures = $e->failures();
      $errorMessage = __('Import failed due to validation errors:') . ' ' . count($failures) . ' ' . __('rows failed.');
      // Log details of validation failures
      Log::error('Fingerprint import validation failed: ' . $errorMessage, ['user_id' => Auth::id(), 'failures' => $failures]);
      session()->flash('error', $errorMessage);

      // Update import record status to failed
      if (isset($fileRecord)) {
        $fileRecord->status = 'failed_validation';
        // $fileRecord->error_details = json_encode($failures);
        $fileRecord->updated_by = Auth::id();
        $fileRecord->save();
      }
    } catch (Exception $e) {
      // Catch other exceptions during import (file storage, database errors in importer, etc.)
      Log::error('Fingerprint import failed: ' . $e->getMessage(), ['user_id' => Auth::id(), 'file' => $this->file ? $this->file->getClientOriginalName() : 'N/A', 'exception' => $e]);

      // Provide a user-friendly error message
      session()->flash('error', __('An error occurred during import. Please check the file format and content.')); // Generic error message

      // Update import record status to failed
      if (isset($fileRecord)) {
        $fileRecord->status = 'failed';
        $fileRecord->error_details = $e->getMessage(); // Store the exception message
        $fileRecord->updated_by = Auth::id();
        $fileRecord->save();
      }
    } finally {
      // Ensure file property is reset and import modal is closed regardless of success or failure
      $this->reset('file'); // Clear the file input field state
      $this->dispatch('closeModal', elementId: '#importModal'); // Assuming a JS event to close modal
      // Dispatch toastr based on the final flashed message
      $this->dispatch('toastr', type: session()->has('success') ? 'success' : (session()->has('warning') ? 'warning' : 'error'), message: session()->get('success') ?? session()->get('warning') ?? session()->get('error'));

      // Optional: Delete the stored file after processing if not needed for archiving
      // if (isset($path)) { Storage::delete($path); }
    }
  }

  // Handle Excel export
  public function exportToExcel()
  {
    // Re-fetch data for export using the same filtering logic as the table display
    // Use the 'fingerprints' computed property which already applies filters
    $fingerprintsToExport = $this->fingerprints->items(); // Get the current page's items
    // OR, if you want ALL filtered items (not just the current page), re-run the query builder before pagination:
    // $query = Fingerprint::filteredFingerprints( $this->selectedEmployeeId, $this->fromDate, $this->toDate, $this->isAbsence, $this->isOneFingerprint );
    // $fingerprintsToExport = $query->get(); // Get all filtered results

    if ($fingerprintsToExport->isEmpty()) {
      session()->flash('error', __('No fingerprints found matching the current filters to export.'));
      $this->dispatch('toastr', type: 'warning', message: __('No Data!'));
      return; // Stop here if no data
    }

    session()->flash('success', __('Well done! The file has been exported successfully.'));
    $this->dispatch('toastr', type: 'success', message: __('Going Well!')); // Dispatch toastr on success

    // Generate filename
    $fileName = 'Fingerprints - ' . Auth::user()->name . ' - ' . Carbon::now()->format('Y-m-d_H-i'); // Include date and time for uniqueness
    // Add date range to filename if filters are applied
    if ($this->fromDate && $this->toDate) {
      $fileName .= ' (' . $this->fromDate->format('Y-m-d') . '_to_' . $this->toDate->format('Y-m-d') . ')';
    }


    // Return the Excel download response
    return Excel::download(
      new ExportFingerprints($fingerprintsToExport), // Assuming ExportFingerprints accepts a collection
      $fileName . '.xlsx'
    );
  }

  // 👉 Helper Methods (Internal logic, not directly callable from view)

  // Add any helper methods needed here
}
