<?php

namespace App\Livewire\HumanResource\Attendance;

use App\Exports\ExportFingerprints;
use App\Imports\ImportFingerprints;
use App\Livewire\Sections\Navbar\Navbar;
use App\Models\Employee; // Make sure this is imported
use App\Models\Fingerprint; // Assumed to exist and have static filteredFingerprints method
use App\Models\Import;
use App\Notifications\DefaultNotification;
use Carbon\Carbon;
use Exception; // Use the non-namespaced Exception now
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Renderless;
use Livewire\Attributes\Computed; // Make sure this is imported
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Validators\ValidationException;
use Maatwebsite\Excel\DefaultValueBinder; // Keep if needed elsewhere, but not for the anonymous class extension

use Illuminate\Support\Facades\DB; // Example
use Illuminate\Validation\Rule; // Example
use Illuminate\View\View;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection as SupportCollection;
use Maatwebsite\Excel\Concerns\FromArray; // Import necessary concern


class Fingerprints extends Component
{
  use WithPagination, WithFileUploads;

  // Define public properties used in the view (e.g., for wire:model)
  public $selectedEmployeeId = null;
  public $fromDate = null;
  public $toDate = null;
  public $isAbsence = false;
  public $isOneFingerprint = false; // Added based on Fingerprint model scope
  public $file; // For file upload
  public $showImportModal = false; // To control import modal visibility
  public $importing = false; // To show importing status
  public $importMessages = []; // To store import feedback
  public $search = ''; // For searching fingerprints
  public $selectedFingerprintId = null; // For editing

  // Properties for editing/creating a single fingerprint record
  public $date = null;
  public $checkIn = null;
  public $checkOut = null;
  public $excuse = null;
  public $isEdit = false;


  // Add this computed property to fetch employees for the dropdown
  #[Computed]
  public function employees(): EloquentCollection // Added return type hint
  {
    // Fetch all employees, ordered by name for the dropdown
    // You can adjust the query as needed (e.g., filter by status)
    return Employee::orderBy('full_name')->get();
  }

  // Computed property for the paginated list of fingerprints
  #[Computed]
  public function fingerprints() // Removed return type hint if it causes issues, or specify based on pagination result
  {
    // Get the selected employee ID (use the property)
    $employeeId = $this->selectedEmployeeId;

    // Ensure valid dates are set if filtering by date
    $fromDate = $this->fromDate ? Carbon::parse($this->fromDate)->format('Y-m-d') : null;
    // FIX: Corrected variable name inside Carbon::parse()
    $toDate = $this->toDate ? Carbon::parse($this->toDate)->format('Y-m-d') : null;

    // Start with the base query
    $query = Fingerprint::query();

    // Apply filters based on component properties
    if ($employeeId) {
      $query->where('employee_id', $employeeId);
    }

    if ($fromDate && $toDate) {
      $query->whereBetween('date', [$fromDate, $toDate]);
    } elseif ($fromDate) {
      $query->where('date', '>=', $fromDate);
    } elseif ($toDate) {
      $query->where('date', '<=', $toDate);
    }


    if ($this->isAbsence) {
      $query->whereNull('log');
    }

    if ($this->isOneFingerprint) {
      $query->whereNotNull('check_in')->whereNull('check_out');
    }

    // Apply search filter if search term is present
    if ($this->search) {
      $query->where(function (Builder $q) {
        $q->where('log', 'like', '%' . $this->search . '%')
          ->orWhere('excuse', 'like', '%' . $this->search . '%')
          ->orWhereHas('employee', function (Builder $eq) {
            $eq->where('full_name', 'like', '%' . $this->search . '%')
              ->orWhere('nric', 'like', '%' . $this->search . '%')
              ->orWhere('id', 'like', '%' . $this->search . '%');
          });
      });
    }


    // Order the results
    $query->orderBy('date', 'desc'); // Order by date descending by default

    // Paginate the results
    return $query->paginate(10); // Adjust pagination per page as needed
  }


  // Add listener for select2 change event (assuming you use it for selectedEmployeeId)
  // You might need a specific JavaScript event dispatch in your blade for this to work correctly with wire:model
  #[On('employeeSelected')]
  public function employeeSelected($employeeId)
  {
    $this->selectedEmployeeId = $employeeId;
    // Optionally reset dates or other filters here if needed
    // $this->fromDate = null;
    // $this->toDate = null;
  }

  // Reset pagination when filters change
  public function updated($property)
  {
    // Check if the updated property is one of the filter properties
    if (in_array($property, ['selectedEmployeeId', 'fromDate', 'toDate', 'isAbsence', 'isOneFingerprint', 'search'])) {
      $this->resetPage();
    }
  }


  // Method to handle file upload and import
  public function importFingerprints()
  {
    $this->validate([
      'file' => 'required|mimes:xlsx,xls,csv|max:10240', // Max 10MB
    ]);

    $this->importing = true;
    $this->importMessages = []; // Clear previous messages

    $filePath = $this->file->store('imports/fingerprints');
    $fullPath = Storage::path($filePath);

    try {
      // Using try-catch for ValidationException specific to Maatwebsite/Excel
      // Note: The ImportFingerprints class constructor expects $user_id and $file_id
      // Make sure you are passing these correctly when instantiating it if needed
      // Example: Excel::import(new ImportFingerprints(Auth::id(), $importRecord->id), $fullPath);
      // Assuming for now that ImportFingerprints can be instantiated without args or gets them differently
      // Based on the ImportFingerprints.php you provided, its constructor *does* take $user_id and $file_id.
      // You need to pass these here. Let's assume you have an 'Import' model
      // to track imports and get a $file_id.

      // Create a record in the 'imports' table first to get an ID
      $importRecord = Import::create([
        'file_path' => $filePath,
        'user_id' => Auth::id(),
        'status' => 'pending', // Or 'processing'
        'type' => 'fingerprint',
      ]);

      // Now pass the user ID and import record ID to the importer
      Excel::import(new ImportFingerprints(Auth::id(), $importRecord->id), $fullPath);


      // Log successful import attempt
      Log::info('Fingerprint import initiated successfully.', [
        'file_path' => $filePath,
        'user_id' => Auth::id(),
        'import_id' => $importRecord->id, // Log the import ID
      ]);

      // The ImportFingerprints class likely handles updating the import record status and details
      // within its event listeners (e.g., AfterImport, ImportFailed).
      // You might retrieve final messages from the import record or rely on the events
      // dispatched by the importer itself.

      // For immediate feedback, show a message that the process has started.
      $this->importMessages[] = ['type' => 'success', 'text' => __('Import process started successfully. Results will be available soon.')];

      // Dispatch an event to the blade to show a generic success notification immediately
      $this->dispatch('importFinished', [['type' => 'success', 'text' => __('Import process started. Check the import history or log for detailed results.')]]);
    } catch (ValidationException $e) {
      // Handle validation errors from the import file
      $failures = $e->failures();
      $errorMessages = [['type' => 'error', 'text' => __('Validation errors found in the import file:')]];
      foreach ($failures as $failure) {
        $errorMessages[] = ['type' => 'error', 'text' => "Row {$failure->row()}: " . implode(", ", $failure->errors())];
      }
      $this->importMessages = array_merge($this->importMessages, $errorMessages);
      Log::error('Fingerprint import validation failed.', [
        'file_path' => $filePath,
        'user_id' => Auth::id(),
        'failures' => $failures,
        'exception' => $e,
      ]);
      $this->dispatch('importFinished', $errorMessages);

      // Update import record status to failed if it was created
      if (isset($importRecord) && $importRecord) {
        $importRecord->update([
          'status' => 'failed',
          'details' => json_encode($errorMessages), // Store validation errors
        ]);
      }
    } catch (Exception $e) { // Using non-namespaced Exception
      // Handle other exceptions during import
      $errorMessage = __('An error occurred during import: ') . $e->getMessage();
      $this->importMessages[] = ['type' => 'error', 'text' => $errorMessage];
      Log::error('Fingerprint import failed.', [
        'file_path' => $filePath,
        'user_id' => Auth::id(),
        'exception' => $e,
      ]);
      $this->dispatch('importFinished', [['type' => 'error', 'text' => $errorMessage]]);

      // Update import record status to failed if it was created
      if (isset($importRecord) && $importRecord) {
        $importRecord->update([
          'status' => 'failed',
          'details' => $e->getMessage(),
        ]);
      }
    } finally {
      $this->importing = false;
      $this->file = null; // Clear the file input
      // You might want to close the modal here or dispatch an event to do so
      // $this->showImportModal = false; // Or dispatch an event
    }
  }

  // Method to download the export template
  public function downloadTemplate()
  {
    try {
      // Generate a sample empty template or a template with headers
      $templateData = [
        ['employee_id', 'date', 'log', 'check_in', 'check_out', 'excuse'], // Example headers
        // Add sample rows if helpful
        // [1, '2023-10-27', 'Log data 1', '08:00', '17:00', ''],
        // [2, '2023-10-27', 'Log data 2', '08:15', '17:30', 'Late arrival'],
      ];

      $fileName = 'fingerprint_import_template.xlsx';

      // FIX: Removed 'extends DefaultValueBinder' and ensured implements FromArray
      // This is the standard way to use an anonymous class with FromArray
      // The linter warning "Expected 2 arguments" here is likely a false positive
      // confusing this with the ImportFingerprints constructor.
      return Excel::download(new class($templateData) implements FromArray { // Corrected anonymous class definition
        protected $data;

        public function __construct(array $data)
        {
          $this->data = $data;
        }

        public function array(): array
        {
          return $this->data;
        }
      }, $fileName);

      // This log line will not be reached because return exits the method
      // Log::info('Fingerprint import template downloaded.', ['user_id' => Auth::id()]);


    } catch (Exception $e) { // Using non-namespaced Exception
      Log::error('Error downloading fingerprint import template: ' . $e->getMessage(), ['user_id' => Auth::id(), 'exception' => $e]);
      session()->flash('error', __('An error occurred while downloading the template: ') . $e->getMessage());
    }
  }


  // Method to handle exporting fingerprints based on current filters
  public function exportFingerprints()
  {
    try {
      // Get the filtered fingerprints using the same logic as the computed property
      // We fetch *all* matching records, not just one page for export
      $employeeId = $this->selectedEmployeeId;
      $fromDate = $this->fromDate ? Carbon::parse($this->fromDate)->format('Y-m-d') : null;
      // FIX: Corrected variable name inside Carbon::parse()
      $toDate = $this->toDate ? Carbon::parse($this->toDate)->format('Y-m-d') : null;

      $query = Fingerprint::query();

      if ($employeeId) {
        $query->where('employee_id', $employeeId);
      }

      if ($fromDate && $toDate) {
        $query->whereBetween('date', [$fromDate, $toDate]);
      } elseif ($fromDate) {
        $query->where('date', '>=', $fromDate);
      } elseif ($toDate) {
        $query->where('date', '<=', $toDate);
      }


      if ($this->isAbsence) {
        $query->whereNull('log');
      }

      if ($this->isOneFingerprint) {
        $query->whereNotNull('check_in')->whereNull('check_out');
      }

      if ($this->search) {
        $query->where(function (Builder $q) {
          $q->where('log', 'like', '%' . $this->search . '%')
            ->orWhere('excuse', 'like', '%' . $this->search . '%')
            ->orWhereHas('employee', function (Builder $eq) {
              $eq->where('full_name', 'like', '%' . $this->search . '%')
                ->orWhere('nric', 'like', '%' . $this->search . '%')
                ->orWhere('id', 'like', '%' . $this->search . '%');
            });
        });
      }

      $fingerprintsToExport = $query->orderBy('date', 'desc')->get();


      if ($fingerprintsToExport->isEmpty()) {
        session()->flash('warning', __('No records found for the selected filters to export.'));
        Log::info('Fingerprint export attempted with no records found.', ['user_id' => Auth::id(), 'filters' => $this->toArray()]);
        return; // Stop the export process
      }

      // Notify the user that export is starting (optional, but good UX)
      // Notification::route('mail', Auth::user()->email)->notify(new DefaultNotification('Export Started', 'Your fingerprint export is being generated.'));
      // session()->flash('success', __('Export started. You will be notified when it is ready.')); // Or just download directly

      // Dispatch event to blade to show a notification
      $this->dispatch('showToast', type: 'success', message: __('Export Started!'));


      // Generate filename
      $fileName = 'Fingerprints - ' . (Auth::user()->name ?? 'User') . ' - ' . Carbon::now()->format('Y-m-d_H-i-s');
      if ($this->fromDate && $this->toDate) {
        try {
          // Safely format dates - Added Carbon::parse just in case inputs aren't Carbon instances
          $fromDateFormatted = $this->fromDate instanceof Carbon ? $this->fromDate->format('Y-m-d') : (is_string($this->fromDate) ? Carbon::parse($this->fromDate)->format('Y-m-d') : '');
          $toDateFormatted = $this->toDate instanceof Carbon ? $this->toDate->format('Y-m-d') : (is_string($this->toDate) ? Carbon::parse($this->toDate)->format('Y-m-d') : '');

          if (!empty($fromDateFormatted) && !empty($toDateFormatted)) {
            $fileName .= ' (' . $fromDateFormatted . '_to_' . $toDateFormatted . ')';
          }
        } catch (Exception $formatE) { // Using non-namespaced Exception
          Log::warning('Could not format dates for export filename: ' . $formatE->getMessage());
          // This catch block is why the code inside the inner try *after* a potential exception is marked unreachable.
        }
      }


      Log::info('Initiating fingerprint Excel download.', [
        'user_id' => Auth::id(),
        'file_name' => $fileName . '.xlsx',
      ]);

      // Use the Export class to handle the data formatting for the Excel file
      return Excel::download(
        new ExportFingerprints($fingerprintsToExport),
        $fileName . '.xlsx'
      );
    } catch (Exception $e) { // Using non-namespaced Exception
      Log::error('Error exporting fingerprints report: ' . $e->getMessage(), ['user_id' => Auth::id(), 'exception' => $e]);
      session()->flash('error', __('An error occurred while exporting the report: ') . $e->getMessage());
      $this->dispatch('showToast', type: 'error', message: __('Export Failed!'));
    }
  }

  // Method to open the modal for creating/editing a fingerprint
  public function createFingerprint()
  {
    $this->reset(['selectedFingerprintId', 'date', 'checkIn', 'checkOut', 'excuse', 'selectedEmployeeId']); // Reset fields for new record including employee
    $this->isEdit = false;
    // You might want to set a default date here
    $this->date = Carbon::now()->format('Y-m-d');
    // Dispatch an event to show the modal
    $this->dispatch('showFingerprintModal');
    // Dispatch event to reset Select2 in the modal
    $this->dispatch('resetSelect2Modal'); // Use a specific event name for the modal Select2
  }

  // Method to prepare the modal for editing a fingerprint
  public function editFingerprint($id)
  {
    $fingerprint = Fingerprint::find($id);

    if (!$fingerprint) {
      session()->flash('error', __('Fingerprint record not found.'));
      return;
    }

    $this->selectedFingerprintId = $fingerprint->id;
    $this->selectedEmployeeId = $fingerprint->employee_id; // Pre-select employee if needed
    $this->date = $fingerprint->date ? Carbon::parse($fingerprint->date)->format('Y-m-d') : null;
    $this->checkIn = $fingerprint->check_in; // Time fields might be strings or Carbon instances
    $this->checkOut = $fingerprint->check_out;
    $this->excuse = $fingerprint->excuse;
    $this->isEdit = true;

    // Dispatch an event to show the modal
    $this->dispatch('showFingerprintModal');

    // Dispatch event to inform Select2 to update its selection (if using Select2 for selectedEmployeeId in modal)
    // Ensure you have a JS listener for 'select2SetSelected' that targets the correct element ID
    $this->dispatch('select2SetSelected', id: 'select2SelectedEmployeeIdModal', value: $this->selectedEmployeeId); // Assuming modal select has ID 'select2SelectedEmployeeIdModal'
  }


  // Method to save (create or update) a fingerprint record
  public function submitFingerprint()
  {
    // Define validation rules
    $rules = [
      'selectedEmployeeId' => 'required|exists:employees,id',
      'date' => 'required|date_format:Y-m-d',
      'checkIn' => 'nullable|date_format:H:i', // Validate time format
      'checkOut' => 'nullable|date_format:H:i|after:checkIn', // checkOut must be after checkIn if both exist
      'excuse' => 'nullable|string|max:255',
    ];

    // Add conditional rule: if checkIn or checkOut are present, date must be present
    if ($this->checkIn || $this->checkOut) {
      // This rule is already covered by 'required' above if either is present.
      // Keeping it explicit might be clearer, but redundant.
      // $rules['date'] = 'required|date_format:Y-m-d';
    }

    // If creating, ensure no record exists for this employee on this date (unique check)
    if (!$this->isEdit) {
      // Ensure unique combination of employee_id and date
      $rules['date'] .= '|unique:fingerprints,date,NULL,id,employee_id,' . $this->selectedEmployeeId;
    } else {
      // If editing, unique rule needs to ignore the current record being edited
      $rules['date'] .= '|unique:fingerprints,date,' . $this->selectedFingerprintId . ',id,employee_id,' . $this->selectedEmployeeId;
    }

    // If editing, the employee might be disabled in the modal form,
    // but we still need to validate that the submitted selectedEmployeeId is valid.
    // The 'required|exists' rule handles this.

    // Perform validation
    $this->validate($rules);

    try {
      // Find or create the fingerprint record
      if ($this->isEdit) {
        $fingerprint = Fingerprint::findOrFail($this->selectedFingerprintId);
        Log::info('Updating fingerprint record.', [
          'user_id' => Auth::id(),
          'fingerprint_id' => $fingerprint->id,
          'old_data' => $fingerprint->getOriginal(), // Log old data
          'new_data' => $this->toArray(), // Log new data attempt
        ]);
      } else {
        $fingerprint = new Fingerprint();
        $fingerprint->employee_id = $this->selectedEmployeeId; // Set employee_id on creation
        Log::info('Creating new fingerprint record.', [
          'user_id' => Auth::id(),
          'data' => $this->toArray(), // Log data attempt
        ]);
      }

      // Assign validated data
      $fingerprint->date = $this->date;
      $fingerprint->check_in = $this->checkIn;
      $fingerprint->check_out = $this->checkOut;
      $fingerprint->log = null; // Manual entry typically has no device log
      $fingerprint->excuse = $this->excuse;
      // is_checked might be set based on whether check_in/out are present, or left false for manual entries
      $fingerprint->is_checked = ($this->checkIn || $this->checkOut) ? true : false;
      // device_id might be set to null for manual entries
      $fingerprint->device_id = null;

      // Save the record
      $fingerprint->save();

      // Log success
      Log::info($this->isEdit ? 'Fingerprint record updated successfully.' : 'Fingerprint record created successfully.', [
        'user_id' => Auth::id(),
        'fingerprint_id' => $fingerprint->id,
      ]);


      // Flash success message
      session()->flash('success', $this->isEdit ? __('Fingerprint record updated successfully!') : __('Fingerprint record created successfully!'));

      // Dispatch events
      $this->dispatch('closeFingerprintModal'); // Close the modal
      $this->dispatch('showToast', type: 'success', message: $this->isEdit ? __('Record Updated!') : __('Record Created!'));
      $this->dispatch('$refresh'); // Refresh the component to show updated list


    } catch (Exception $e) { // Using non-namespaced Exception
      // Log and show error message
      Log::error('Error saving fingerprint record: ' . $e->getMessage(), [
        'user_id' => Auth::id(),
        'data' => $this->toArray(),
        'exception' => $e
      ]);
      session()->flash('error', __('An error occurred while saving the record: ') . $e->getMessage());
      $this->dispatch('showToast', type: 'error', message: __('Save Failed!'));
    }
  }

  // Method to delete a fingerprint record
  public function deleteFingerprint($id)
  {
    try {
      $fingerprint = Fingerprint::findOrFail($id);

      // Optional: Add a confirmation step before deleting (using Alpine.js or a separate modal)
      // if (!confirm(__('Are you sure you want to delete this record?'))) {
      //     return; // User cancelled
      // }

      $fingerprint->delete();

      Log::info('Fingerprint record deleted successfully.', [
        'user_id' => Auth::id(),
        'fingerprint_id' => $id,
      ]);

      session()->flash('success', __('Fingerprint record deleted successfully!'));
      $this->dispatch('showToast', type: 'success', message: __('Record Deleted!'));
      $this->dispatch('$refresh'); // Refresh the component to show updated list


    } catch (Exception $e) { // Using non-namespaced Exception
      Log::error('Error deleting fingerprint record: ' . $e->getMessage(), [
        'user_id' => Auth::id(),
        'fingerprint_id' => $id,
        'exception' => $e
      ]);
      session()->flash('error', __('An error occurred while deleting the record: ') . $e->getMessage());
      $this->dispatch('showToast', type: 'error', message: __('Deletion Failed!'));
    }
  }


  // You might have other methods here for filtering, searching, etc.
  // based on the public properties like $selectedEmployeeId, $fromDate, $toDate, $isAbsence, $search

  // Example method to clear filters (optional)
  public function clearFilters()
  {
    $this->reset(['selectedEmployeeId', 'fromDate', 'toDate', 'isAbsence', 'isOneFingerprint', 'search']);
    $this->resetPage(); // Reset pagination after clearing filters
    // Dispatch event to reset select2 if necessary via JS (for filter select)
    $this->dispatch('resetSelect2Filter'); // Use a specific event name
  }

  // Add method to show the import modal
  public function showImportModal()
  {
    $this->resetErrorBag(); // Clear validation errors
    $this->reset(['file', 'importMessages']); // Clear file input and previous messages
    $this->showImportModal = true;
    // Dispatch event to open modal if needed
    // $this->dispatch('openImportModal');
  }

  // Add method to hide the import modal
  public function hideImportModal()
  {
    $this->showImportModal = false;
    $this->resetErrorBag(); // Clear validation errors
    $this->reset(['file', 'importMessages']); // Clear file input and previous messages
    // Dispatch event to close modal if needed
    // $this->dispatch('closeImportModal');
  }
}
