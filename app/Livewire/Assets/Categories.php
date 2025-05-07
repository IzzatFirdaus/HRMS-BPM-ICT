<?php

namespace App\Livewire\Assets;

use App\Models\Category;
use App\Models\SubCategory;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Validate;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Validation\Rule;
use Illuminate\View\View; // Import View return type hint


class Categories extends Component
{
  use WithPagination;

  protected $paginationTheme = 'bootstrap';

  #[Rule('nullable|string|max:255')]
  public string $search_term_categories = '';

  public bool $showModal = false;
  public bool $isEdit = false;
  public ?Category $category = null;

  #[Rule('required|string|max:255')]
  public string $name = '';

  #[Rule('nullable|string|max:500')]
  public ?string $description = null;

  public ?int $confirmedId = null;

  public bool $showInfoModal = false;
  public ?Category $categoryInfo = null;


  /**
   * Computed property to fetch and filter categories.
   * This makes the $categories variable available as $this->categories.
   * In Livewire v3+, it's typically automatically available in the view as $categories.
   */
  #[Computed]
  public function categories()
  {
    $query = Category::query();

    if ($this->search_term_categories) {
      $query->where('name', 'like', '%' . $this->search_term_categories . '%');
    }

    $query->orderBy('id', 'asc');

    return $query->paginate(10);
  }


  /**
   * Render the component view.
   * Explicitly passing computed properties to the view as an alternative.
   */
  public function render(): View // Add return type hint
  {
    // Explicitly pass the result of the computed property to the view
    return view('livewire.assets.categories', [
      'categories' => $this->categories, // Access the computed property's value
    ]);
  }

  /**
   * Show modal for adding a new category.
   */
  public function showNewCategoryModal(): void
  {
    $this->resetForm();
    $this->isEdit = false;
    $this->showModal = true;
    $this->dispatch('categoryModalShown');
  }

  /**
   * Show modal for editing an existing category.
   */
  public function showEditCategoryModal(Category $category): void
  {
    $this->resetForm();

    $this->isEdit = true;
    $this->category = $category;

    $this->name = $category->name;
    $this->description = $category->description;
  }

  /**
   * Show modal for viewing category info.
   */
  public function showCategoryInfo(int $categoryId): void
  {
    $this->categoryInfo = Category::withTrashed()->find($categoryId);
    if (!$this->categoryInfo) {
      session()->flash('error', __('Category not found.'));
      $this->dispatch('toastr', type: 'error', message: __('Not Found!'));
      return;
    }
    $this->showInfoModal = true;
    $this->dispatch('categoryInfoModalShown');
  }


  /**
   * Save a new or update an existing category.
   */
  public function saveCategory(): void
  {
    $rules = [
      'name' => [
        'required',
        'string',
        'max:255',
        Rule::unique('categories', 'name')->ignore($this->category?->id)->whereNull('deleted_at'),
      ],
      'description' => ['nullable', 'string', 'max:500'],
    ];

    $this->validate($rules);

    try {
      if ($this->isEdit && $this->category) {
        $this->category->update([
          'name' => $this->name,
          'description' => $this->description,
        ]);
        Log::info('Category updated.', ['category_id' => $this->category->id, 'user_id' => Auth::id()]);
        session()->flash('success', __('Category updated successfully!'));
        $this->dispatch('toastr', type: 'success', message: __('Going Well!'));
      } else {
        $category = Category::create([
          'name' => $this->name,
          'description' => $this->description,
        ]);
        Log::info('Category created.', ['category_id' => $category->id, 'user_id' => Auth::id()]);
        session()->flash('success', __('Category saved successfully!'));
        $this->dispatch('toastr', type: 'success', message: __('Going Well!'));
      }

      $this->showModal = false;
      $this->resetForm();
    } catch (Exception $e) {
      Log::error('Error saving category: ' . $e->getMessage(), ['user_id' => Auth::id(), 'exception' => $e]);
      session()->flash('error', __('An error occurred while saving the category.') . ' ' . $e->getMessage());
      $this->dispatch('toastr', type: 'error', message: __('Operation Failed!'));
    }
  }

  /**
   * Confirm deletion of a category.
   */
  public function confirmDeleteCategory(int $categoryId): void
  {
    $this->confirmedId = $categoryId;
    Log::info('Deletion confirmation initiated for category ID: ' . $categoryId);
    // TODO: Dispatch JS event to show confirmation modal
  }

  /**
   * Delete the confirmed category record (soft delete).
   */
  public function deleteCategory(): void
  {
    if (is_null($this->confirmedId)) {
      Log::warning('Attempted to delete category without confirmed ID.');
      session()->flash('error', __('No category selected for deletion.'));
      $this->dispatch('toastr', type: 'warning', message: __('No Selection!'));
      return;
    }

    try {
      $record = Category::find($this->confirmedId);

      if (!$record) {
        Log::warning('Attempted to delete non-existent category.', ['category_id' => $this->confirmedId, 'user_id' => Auth::id()]);
        session()->flash('error', __('Category not found.'));
        $this->dispatch('toastr', type: 'error', message: __('Not Found!'));
        return;
      }

      $record->delete();

      Log::info('Category deleted successfully.', ['category_id' => $this->confirmedId, 'user_id' => Auth::id()]);
      session()->flash('success', __('Category deleted successfully!'));
      $this->dispatch('toastr', type: 'success', message: __('Going Well!'));
    } catch (Exception $e) {
      Log::error('Positions component: Failed to delete category record.', ['user_id' => Auth::id(), 'category_id' => $this->confirmedId, 'exception' => $e]);
      session()->flash('error', __('An error occurred while deleting the category.') . ' ' . $e->getMessage());
      $this->dispatch('toastr', type: 'error', message: __('Operation Failed!'));
    }

    $this->confirmedId = null;
    // TODO: Dispatch event to close confirmation modal
  }

  /**
   * Resets the form properties and validation errors.
   */
  #[On('hide-category-modal')]
  #[On('hide-category-info-modal')]
  public function resetForm(): void
  {
    $this->reset([
      'showModal',
      'isEdit',
      'category',
      'name',
      'description',
      'confirmedId',
      'showInfoModal',
      'categoryInfo',
    ]);

    $this->resetValidation();
  }
}
