<?php

namespace App\Livewire\HumanResource\Structure;

use App\Models\Position;
use App\Models\Grade;
use Livewire\Attributes\Rule;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception; // Import Exception class
use Illuminate\Validation\Rule as ValidationRule;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;


class Positions extends Component
{
  use WithPagination;

  protected string $paginationTheme = 'bootstrap';

  public string $search = '';

  public bool $showModal = false;
  public bool $isEdit = false;
  public ?Position $position = null;

  #[Rule(['required', 'string', 'max:255'])]
  public string $name = '';

  #[Rule(['required', 'integer', 'min:0'])]
  public int $vacanciesCount = 0;

  #[Rule(['nullable', 'exists:grades,id'])]
  public ?int $grade_id = null;

  public ?int $confirmedId = null;

  // Fetch paginated positions with grade and user count for the table list
  #[Computed]
  public function positions()
  {
    $query = Position::query()
      ->with('grade') // Eager load the grade relationship
      ->withCount('users') // Eager load the count of assigned users
      ->orderBy('name');

    if ($this->search) {
      $query->where('name', 'like', '%' . $this->search . '%');
      // Add other searchable columns/relationships if needed
    }

    return $query->paginate(10);
  }

  // Fetch grades for the Grade dropdown
  #[Computed]
  public function grades()
  {
    try {
      return Grade::orderBy('name')->get();
    } catch (Exception $e) {
      Log::error('Error fetching grades for positions component: ' . $e->getMessage(), ['exception' => $e]);
      return collect();
    }
  }

  // Render Method - Rely on #[Computed] properties
  public function render(): View
  {
    // The #[Computed] properties `positions` and `grades` are automatically available
    // in the view as $positions and $grades. Just return the view name.
    return view('livewire.human-resource.structure.positions');
  }

  /**
   * Save a new or update an existing position.
   */
  public function savePosition()
  {
    $this->rules['name'] = [
      'required',
      'string',
      'max:255',
      ValidationRule::unique('positions', 'name')->ignore($this->position?->id),
    ];
    $this->rules['vacanciesCount'] = ['required', 'integer', 'min:0'];
    $this->rules['grade_id'] = ['nullable', 'exists:grades,id'];

    $this->validate();

    DB::beginTransaction();
    try {
      if ($this->isEdit && $this->position) {
        $this->position->update([
          'name' => $this->name,
          'vacancies_count' => $this->vacanciesCount,
          'grade_id' => $this->grade_id,
        ]);
        Log::info('Position updated.', ['position_id' => $this->position->id, 'user_id' => Auth::id()]);
      } else {
        $position = Position::create([
          'name' => $this->name,
          'vacancies_count' => $this->vacanciesCount,
          'grade_id' => $this->grade_id,
        ]);
        Log::info('Position created.', ['position_id' => $position->id, 'user_id' => Auth::id()]);
      }

      DB::commit();

      $this->showModal = false;
      session()->flash('message', __('Position saved successfully.'));
      $this->dispatch('hide-position-modal');
    } catch (Exception $e) { // FIX: Added $ before e
      DB::rollBack();
      Log::error('Error saving position: ' . $e->getMessage(), ['exception' => $e, 'user_id' => Auth::id()]); // FIX: Added $ before e
      session()->flash('error', __('An error occurred while saving the position: ') . $e->getMessage()); // FIX: Added $ before e
    }

    $this->resetForm();
  }

  /**
   * Show modal for editing an existing position.
   */
  public function showEditPositionModal(Position $position): void
  {
    $this->resetValidation();

    $this->isEdit = true;
    $this->position = $position;
    $this->name = $position->name;
    $this->vacanciesCount = $position->vacancies_count;
    $this->grade_id = $position->grade_id;

    $this->showModal = true;
    $this->dispatch('positionModalShown');

    Log::info('Edit modal shown for position ID: ' . $position->id);
  }

  /**
   * Set the position ID for deletion confirmation.
   */
  public function confirmDeletePosition(int $positionId): void
  {
    $this->confirmedId = $positionId;
    Log::info('Deletion confirmation initiated for position ID: ' . $positionId);
  }

  /**
   * Delete the confirmed position.
   */
  public function deletePosition(): void
  {
    if (is_null($this->confirmedId)) {
      Log::warning('Attempted to delete position without confirmed ID.');
      return;
    }

    $positionToDelete = Position::find($this->confirmedId);

    if (!$positionToDelete) {
      Log::warning('Attempted to delete non-existent position.', ['position_id' => $this->confirmedId, 'user_id' => Auth::id()]);
      session()->flash('error', __('Position not found.'));
      $this->confirmedId = null;
      return;
    }

    DB::beginTransaction();
    try {
      if ($positionToDelete->users()->exists()) {
        DB::rollBack();
        session()->flash('error', __('Cannot delete position because it has associated users. Reassign users first.'));
        $this->confirmedId = null;
        Log::warning('Attempted to delete position with associated users.', ['position_id' => $positionToDelete->id, 'user_id' => Auth::id()]);
        return;
      }

      $positionToDelete->delete();

      DB::commit();

      Log::info('Position deleted successfully.', ['position_id' => $positionToDelete->id, 'user_id' => Auth::id()]);
      session()->flash('message', __('Position deleted successfully.'));
    } catch (Exception $e) { // FIX: Added $ before e
      DB::rollBack();
      // FIX: Added $ before e in getMessage() calls and $this->confirmedId
      Log::error('Error deleting position: ' . $e->getMessage(), ['exception' => $e, 'user_id' => Auth::id(), 'position_id' => $this->confirmedId]);
      session()->flash('error', __('An error occurred while deleting the position: ') . $e->getMessage()); // FIX: Added $ before e
    }

    $this->confirmedId = null;
  }

  /**
   * Resets the form properties and validation errors.
   */
  public function resetForm(): void
  {
    $this->reset([
      'name',
      'vacanciesCount',
      'grade_id',
      'isEdit',
      'position',
    ]);
    $this->resetValidation();
    $this->vacanciesCount = 0; // Reset default for new
    Log::info('Position form reset.');
  }

  /**
   * Show modal for adding a new position.
   */
  public function showNewPositionModal(): void
  {
    $this->reset(['isEdit', 'position', 'name', 'vacanciesCount', 'grade_id', 'confirmedId']);
    $this->resetValidation();

    $this->isEdit = false;
    $this->vacanciesCount = 0;

    $this->showModal = true;
    $this->dispatch('positionModalShown');

    Log::info('New position modal shown.');
  }
}
