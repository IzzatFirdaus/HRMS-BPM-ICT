<?php

namespace App\Livewire\HumanResource\Structure;

use App\Models\Department;
use App\Models\User;
use Livewire\Attributes\Rule;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;
use Illuminate\Validation\Rule as ValidationRule;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Pagination\LengthAwarePaginator;

class Departments extends Component
{
  use WithPagination;

  protected string $paginationTheme = 'bootstrap';

  public bool $showModal = false;
  public bool $isEdit = false;
  public ?Department $department = null;

  #[Rule(['required', 'string', 'max:255'])]
  public string $name = '';

  #[Rule(['nullable', 'string', 'max:50'])]
  public ?string $branch_type = null;

  #[Rule(['nullable', 'string', 'max:20'])]
  public ?string $code = null;

  #[Rule(['nullable', 'string'])]
  public ?string $description = null;

  #[Rule(['required', 'boolean'])]
  public bool $is_active = true;

  #[Rule(['nullable', 'exists:users,id'])]
  public ?int $head_of_department_id = null;

  public ?int $confirmedId = null;

  // Keep computed property, but render method explicitly passes data as a fallback test
  #[Computed]
  public function departments()
  {
    $query = Department::query()
      ->withCount('users')
      ->orderBy('name');

    return $query->paginate(10);
  }

  // Fetch users for the Head of Department dropdown
  #[Computed]
  public function users()
  {
    try {
      return User::orderBy('name')->get();
    } catch (Exception $e) {
      Log::error('Error fetching users for departments component: ' . $e->getMessage(), ['exception' => $e]);
      return collect();
    }
  }

  // Render Method - Explicitly fetch and pass data as a fallback test
  public function render(): View
  {
    $departments = Department::query()
      ->withCount('users')
      ->orderBy('name')
      ->paginate(10);

    // Pass users if needed in the main view table or other parts outside the modal
    // If the users dropdown is ONLY in the modal, it will use the computed property when modal renders via JS event
    $users = $this->users; // Fetch users using the computed property here

    return view('livewire.human-resource.structure.departments', [
      'departments' => $departments,
      'users' => $users, // Pass users to the main view
    ]);
  }

  /**
   * Save a new or update an existing department.
   */
  public function saveDepartment()
  {
    $this->rules['code'] = [
      'nullable',
      'string',
      'max:20',
      ValidationRule::unique('departments', 'code')->ignore($this->department?->id),
    ];

    $this->validate();

    DB::beginTransaction();
    try {
      if ($this->isEdit && $this->department) {
        $this->department->update([
          'name' => $this->name,
          'branch_type' => $this->branch_type,
          'code' => $this->code,
          'description' => $this->description,
          'is_active' => $this->is_active,
          'head_of_department_id' => $this->head_of_department_id,
        ]);
        Log::info('Department updated.', ['department_id' => $this->department->id, 'user_id' => Auth::id()]);
      } else {
        Department::create([
          'name' => $this->name,
          'branch_type' => $this->branch_type,
          'code' => $this->code,
          'description' => $this->description,
          'is_active' => $this->is_active,
          'head_of_department_id' => $this->head_of_department_id,
        ]);
        Log::info('Department created.', ['user_id' => Auth::id()]);
      }

      DB::commit();

      $this->showModal = false;
      session()->flash('message', __('Department saved successfully.'));
      $this->dispatch('hide-department-modal');
    } catch (Exception $e) {
      DB::rollBack();
      Log::error('Error saving department: ' . $e->getMessage(), ['exception' => $e, 'user_id' => Auth::id()]);
      session()->flash('error', __('An error occurred while saving the department: ') . $e->getMessage());
    }

    $this->resetForm();
  }

  /**
   * Show modal for editing an existing department.
   */
  public function showEditDepartmentModal(Department $department): void
  {
    $this->resetValidation();

    $this->isEdit = true;
    $this->department = $department;
    $this->name = $department->name;
    $this->branch_type = $department->branch_type;
    $this->code = $department->code;
    $this->description = $department->description;
    $this->is_active = $department->is_active;
    $this->head_of_department_id = $department->head_of_department_id;

    $this->showModal = true;
    $this->dispatch('departmentModalShown');

    Log::info('Edit modal shown for department ID: ' . $department->id);
  }

  /**
   * Set the department ID for deletion confirmation.
   */
  public function confirmDeletion(int $departmentId): void
  {
    $this->confirmedId = $departmentId;
    Log::info('Deletion confirmation initiated for department ID: ' . $departmentId);
  }

  /**
   * Delete the confirmed department.
   */
  public function deleteDepartment(): void
  {
    if (is_null($this->confirmedId)) {
      Log::warning('Attempted to delete department without confirmed ID.');
      return;
    }

    $departmentToDelete = Department::find($this->confirmedId);

    if (!$departmentToDelete) {
      Log::warning('Attempted to delete non-existent department.', ['department_id' => $this->confirmedId, 'user_id' => Auth::id()]);
      session()->flash('error', __('Department not found.'));
      $this->dispatch('hide-delete-confirmation-modal');
      $this->confirmedId = null;
      return;
    }

    DB::beginTransaction();
    try {
      if ($departmentToDelete->users()->exists()) {
        DB::rollBack();
        session()->flash('error', __('Cannot delete department because it has associated users. Reassign users first.'));
        $this->confirmedId = null;
        $this->dispatch('hide-delete-confirmation-modal');
        Log::warning('Attempted to delete department with associated users.', ['department_id' => $departmentToDelete->id, 'user_id' => Auth::id()]);
        return;
      }

      $departmentToDelete->delete();

      DB::commit();

      Log::info('Department deleted successfully.', ['department_id' => $departmentToDelete->id, 'user_id' => Auth::id()]);
      session()->flash('message', __('Department deleted successfully.'));
      $this->dispatch('hide-delete-confirmation-modal');
    } catch (Exception $e) {
      DB::rollBack();
      Log::error('Error deleting department: ' . $e->getMessage(), ['exception' => $e, 'user_id' => Auth::id(), 'department_id' => $this->confirmedId]);
      session()->flash('error', __('An error occurred while deleting the department: ') . $e->getMessage());
      $this->dispatch('hide-delete-confirmation-modal');
    }

    $this->confirmedId = null;
  }

  /**
   * Close the deletion confirmation modal and reset confirmed ID.
   */
  public function cancelDeletion(): void
  {
    $this->confirmedId = null;
    Log::info('Department deletion cancelled.');
  }

  /**
   * Show modal for adding a new department.
   */
  public function showNewDepartmentModal(): void
  {
    $this->reset(['isEdit', 'department', 'name', 'branch_type', 'code', 'description', 'head_of_department_id', 'confirmedId']);
    $this->resetValidation();

    $this->isEdit = false;
    $this->is_active = true;

    $this->showModal = true;
    $this->dispatch('departmentModalShown');

    Log::info('New department modal shown.');
  }

  /**
   * Reset the form fields and state.
   */
  public function resetForm(): void
  {
    $this->reset(['isEdit', 'department', 'name', 'branch_type', 'code', 'description', 'is_active', 'head_of_department_id', 'confirmedId']);
    $this->resetValidation();
    $this->is_active = true;

    Log::info('Department form reset.');
  }
}
