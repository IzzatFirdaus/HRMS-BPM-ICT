<?php

namespace App\Livewire\HumanResource\Structure;

use App\Models\Employee;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Rule;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule as ValidationRule; // If needed for form validation


class Employees extends Component
{
  use WithPagination;

  // Pagination specific setting (assuming Bootstrap styling)
  protected string $paginationTheme = 'bootstrap';

  #[Rule('nullable|string|max:255')]
  public string $searchTerm = '';

  // Keep track of the ID of the employee currently being confirmed for deletion
  public ?int $confirmedId = null;

  // #[Computed] property to fetch employees for the table
  #[Computed]
  public function employees()
  {
    $query = Employee::query()
      // Eager load relationships needed in the table view (e.g., department, position if displayed)
      // ->with('department', 'position')
      // Order by actual name columns (last_name, then first_name) as per database schema
      ->orderBy('last_name')
      ->orderBy('first_name');


    // Apply search filter if searchTerm is not empty
    if ($this->searchTerm) {
      $query->where(function (Builder $q) {
        // Search across relevant columns like names, NRIC, mobile number
        $q->where('first_name', 'like', '%' . $this->searchTerm . '%')
          ->orWhere('father_name', 'like', '%' . $this->searchTerm . '%')
          ->orWhere('last_name', 'like', '%' . $this->searchTerm . '%')
          ->orWhere('nric', 'like', '%' . $this->searchTerm . '%')
          ->orWhere('mobile_number', 'like', '%' . $this->searchTerm . '%');
        // Add other searchable columns as needed
      });
    }

    return $query->paginate(10);
  }

  /**
   * Render the component view.
   */
  public function render()
  {
    // The 'employees' computed property is automatically available to the view.
    return view('livewire.human-resource.structure.employees');
  }

  /**
   * Set the employee ID for deletion confirmation.
   */
  public function confirmDeleteEmployee(int $employeeId): void
  {
    $this->confirmedId = $employeeId;
    Log::info('Deletion confirmation initiated for employee ID: ' . $employeeId);
  }

  /**
   * Delete the confirmed employee record (soft delete).
   */
  public function deleteEmployee(): void
  {
    if (is_null($this->confirmedId)) {
      Log::warning('Attempted to delete employee without confirmed ID.');
      session()->flash('error', __('No employee selected for deletion.'));
      $this->dispatch('toastr', type: 'warning', message: __('No Selection!'));
      return;
    }

    DB::beginTransaction();
    try {
      $record = Employee::find($this->confirmedId);

      if (!$record) {
        Log::warning('Attempted to delete non-existent employee.', ['employee_id' => $this->confirmedId, 'user_id' => Auth::id()]);
        session()->flash('error', __('Employee not found.'));
        $this->dispatch('toastr', type: 'error', message: __('Not Found!'));
        DB::rollBack();
        return;
      }

      // Consider adding logic here to handle related records before soft deleting the employee
      // e.g., soft deleting their timelines, leave requests, etc., if not handled by database cascade or model events.

      $record->delete(); // Soft delete the record

      DB::commit();

      session()->flash('success', __('Employee deleted successfully!'));
      $this->dispatch('toastr', type: 'success', message: __('Going Well!'));
    } catch (Exception $e) {
      DB::rollBack();
      Log::error('Employees component: Failed to delete employee record.', ['user_id' => Auth::id(), 'employee_id' => $this->confirmedId, 'exception' => $e]);
      session()->flash('error', __('An error occurred while deleting the employee.') . ' ' . $e->getMessage());
      $this->dispatch('toastr', type: 'error', message: __('Operation Failed!'));
    }

    $this->confirmedId = null;
    // Dispatch event to close confirmation modal if using one
    // $this->dispatch('closeConfirmModal');
  }

  // TODO: Add methods for creating/editing employees, managing modal state, form properties, validation, etc.
  // Example:
  // public bool $showModal = false;
  // public bool $isEdit = false;
  // public ?Employee $employee = null; // Employee being edited
  // #[Rule(['required', 'string', 'max:255'])] public string $first_name = '';
  // ... other form properties ...
  // public function showCreateEmployeeModal() { ... }
  // public function showEditEmployeeModal(Employee $employee) { ... }
  // public function saveEmployee() { ... }
  // public function resetForm() { ... }
}
