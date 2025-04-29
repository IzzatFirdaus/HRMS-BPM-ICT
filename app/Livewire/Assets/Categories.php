<?php

namespace App\Livewire\Assets;

use App\Models\Category; // Assumed to exist
use App\Models\SubCategory; // Assumed to exist
use Livewire\Component; // Base Livewire component
use Livewire\WithPagination; // Trait for pagination
use Livewire\Attributes\Computed; // For Livewire v3+ computed properties
use Livewire\Attributes\On; // For Livewire v3+ event listeners
use Livewire\Attributes\Locked; // For Livewire v3+ locked properties - not used but good to keep if it was from template
use Livewire\Attributes\Validate; // For Livewire v3+ property validation
use Illuminate\Database\Eloquent\Builder; // For type hinting query builders
use Illuminate\Support\Facades\Auth; // Import Auth Facade
use Exception; // Import the Exception class
use Illuminate\Support\Facades\Log; // Import Log Facade
use Illuminate\Support\Collection; // Import Collection class
use Illuminate\Pagination\LengthAwarePaginator; // Import LengthAwarePaginator class
use Illuminate\Pagination\Paginator; // Import Paginator class
use Illuminate\Validation\Rule; // Import Rule class for unique validation


// Assumptions:
// 1. Category model exists and has 'name' attribute and a 'subCategories' hasMany relationship.
// 2. SubCategory model exists and has 'name', 'category_id' attributes and a 'category' belongsTo relationship.
// 3. Livewire v3+ is used for attributes.
// 4. The view uses Bootstrap pagination styling.
// 5. Modals are controlled via JS dispatch events like 'closeModal', 'toastr'.

class Categories extends Component
{
  use WithPagination;

  // Pagination specific setting (assuming Bootstrap styling)
  protected $paginationTheme = 'bootstrap';

  // 👉 State Variables (Public properties that sync with the view)

  #[Validate] // Apply validation rules to this property
  public string $search_term_categories = ''; // Search input for categories list

  #[Validate] // Apply validation rules to this property
  public string $search_term_sub_categories = ''; // Search input for sub-categories list

  public ?Category $category = null; // State: Category model being edited
  public ?SubCategory $subCategory = null; // State: SubCategory model being edited
  public ?Category $categoryInfo = null; // State: Category model whose details and sub-cats are shown

  #[Validate] // Apply validation rules to this property
  public string $categoryName = ''; // Form field for category name

  #[Validate] // Apply validation rules to this property
  public string $subCategoryName = ''; // Form field for sub-category name

  public bool $isEdit = false; // State: Is a modal/form in edit mode

  public ?int $confirmedCategoryId = null; // State: ID of the category pending deletion confirmation
  public ?int $confirmedSubCategoryId = null; // State: ID of the sub-category pending deletion confirmation


  // 👉 Computed Properties (Livewire v3+ - Cached data that depends on state)

  #[Computed]
  public function categories(): LengthAwarePaginator // Use imported class
  {
    // Fetch and filter categories based on search term
    // Search by 'name' (case-insensitive if database/collation supports), optional search by 'id'
    return Category::query()
      ->when($this->search_term_categories, function (Builder $query, string $term) {
        $query->where('name', 'like', '%' . $term . '%');
        // Optionally add searching by ID if needed, but using LIKE on ID is unusual
        // ->orWhere('id', 'like', '%' . $term . '%');
      })
      ->orderBy('name') // Order categories alphabetically
      ->paginate(6, pageName: 'categoriesPage'); // Paginate categories, use distinct pageName
  }

  #[Computed]
  public function subCategories(): LengthAwarePaginator // Use imported class
  {
    // Fetch and filter sub-categories based on the SELECTED category ($this->categoryInfo)
    // and the sub-category search term
    $query = SubCategory::query()
      ->when($this->search_term_sub_categories, function (Builder $query, string $term) {
        $query->where('name', 'like', '%' . $term . '%');
        // Optionally add searching by ID if needed
        // ->orWhere('id', 'like', '%' . $term . '%');
      })
      ->orderBy('name'); // Order sub-categories alphabetically


    // --- IMPORTANT ---
    // Filter sub-categories by the currently selected category ($this->categoryInfo)
    if ($this->categoryInfo) {
      $query->where('category_id', $this->categoryInfo->id);
    } else {
      // If no category is selected/loaded, return an empty paginator
      return $this->emptyPaginator();
    }


    // Paginate sub-categories, use distinct pageName
    return $query->paginate(6, pageName: 'subCategoriesPage');
  }

  // Helper to return an empty paginator
  protected function emptyPaginator(): LengthAwarePaginator // Use imported class
  {
    $collection = new Collection(); // Use imported class
    return new LengthAwarePaginator( // Use imported class
      $collection,
      $collection->count(),
      6,
      Paginator::resolveCurrentPage('subCategoriesPage'), // Use imported class
      ['path' => Paginator::resolveCurrentPath(), 'pageName' => 'subCategoriesPage'] // Use imported class
    );
  }


  // 👉 Lifecycle Hooks

  public function mount()
  {
    // Load the first category info by default if any categories exist
    $firstCategory = Category::orderBy('id')->first(); // Find the first category

    if ($firstCategory) {
      $this->showCategoryInfo($firstCategory->id); // Load its info
    } else {
      $this->categoryInfo = null; // Set to null if no categories exist
    }
    // Initial data for categories and sub-categories lists is implicitly loaded by Computed properties
  }

  // Hooks to reset pagination when search terms change
  public function updatedSearchTermCategories(): void
  {
    $this->resetPage('categoriesPage'); // Reset category list pagination
  }

  public function updatedSearchTermSubCategories(): void
  {
    $this->resetPage('subCategoriesPage'); // Reset sub-category list pagination
  }

  // Hook to reset sub-category pagination when the selected category changes (via showCategoryInfo)
  #[On('categoryInfoSelected')] // Listen for event dispatched by showCategoryInfo
  public function resetSubCategoryPage(): void
  {
    $this->resetPage('subCategoriesPage'); // Reset sub-category list pagination
  }


  // Render method simply returns the view
  public function render()
  {
    // Computed properties ($this->categories, $this->subCategories, $this->employees, etc.)
    // are accessed directly in the view.
    return view('livewire.assets.categories');
  }


  // 👉 Category Info Display

  // Show details of a selected category and load its sub-categories
  public function showCategoryInfo(int $categoryId): void // Accept ID, void return type
  {
    // Find the category by ID
    $category = Category::find($categoryId);

    if (!$category) {
      // Handle case where category is not found (e.g., deleted)
      // Maybe select the first available category or clear categoryInfo
      $firstCategory = Category::orderBy('id')->first();
      if ($firstCategory) {
        session()->flash('warning', __('Selected category not found. Showing the first available one.'));
        $this->categoryInfo = $firstCategory;
        $this->dispatch('categoryInfoSelected'); // Dispatch to reset sub-cat pagination
      } else {
        session()->flash('warning', __('Selected category not found. No categories available.'));
        $this->categoryInfo = null; // Clear category info
        $this->dispatch('categoryInfoSelected'); // Dispatch to reset sub-cat pagination
      }
      return;
    }

    $this->categoryInfo = $category; // Store the category model for display

    // Dispatch an event to notify the component (and view) that the selected category has changed
    // This is important for resetting the sub-category list pagination
    $this->dispatch('categoryInfoSelected');
  }


  // --------------------
  // Category Methods
  // --------------------

  // Show modal for adding a new category
  public function showNewCategoryModal(): void // Void return type
  {
    $this->resetValidation(); // Clear validation errors
    $this->reset('isEdit', 'categoryName', 'category'); // Reset category modal state
    $this->isEdit = false; // Explicitly set mode
    // Dispatch JS event to open modal if not handled by wire:click
    // $this->dispatch('openModal', elementId: '#categoryModal');
  }

  // Submit category creation or update form
  public function submitCategory(): void // Void return type
  {
    // Use $this->validate() to run validation rules
    $this->validate([
      'categoryName' => ['required', 'string', 'max:255', Rule::unique('categories', 'name')->ignore($this->isEdit ? $this->category->id : null)], // Validate uniqueness, ignore current category if editing, Use imported Rule
    ]);

    // Use a transaction for atomicity
    try {
      \Illuminate\Support\Facades\DB::transaction(function () {
        if ($this->isEdit) {
          $this->editCategory();
        } else {
          $this->addCategory();
        }
      });

      // Success feedback and dispatch events after transaction
      session()->flash('success', $this->isEdit ? __('Category Updated Successfully!') : __('Category Added Successfully!'));
      $this->dispatch('closeModal', elementId: '#categoryModal'); // Assuming JS event
      $this->dispatch('toastr', type: 'success', message: __('Operation Successful!')); // Assuming JS event

      // Refresh the categories list by resetting its pagination
      $this->resetPage('categoriesPage');
    } catch (Exception $e) { // Use imported Exception
      // Log the error
      Log::error('Category submit failed: ' . $e->getMessage(), ['user_id' => Auth::id(), 'category_name' => $this->categoryName, 'is_edit' => $this->isEdit]); // Use imported Log and Auth

      // Flash and dispatch error feedback
      session()->flash('error', __('An error occurred while saving the category.')); // Generic error message
      $this->dispatch('toastr', type: 'error', message: __('Operation Failed!')); // Assuming JS event
      // Keep modal open on error? Original closed. Let's keep original behavior.
      // If you want to keep modal open on validation errors, remove this close dispatch
      $this->dispatch('closeModal', elementId: '#categoryModal');
    } finally {
      // Reset modal state regardless of success or failure
      $this->reset('isEdit', 'categoryName', 'category');
    }
  }

  // Add a new category record
  protected function addCategory(): void // Use protected as called internally, void return type
  {
    // Validation is handled in submitCategory()
    $newCategory = Category::create([
      'name' => $this->categoryName,
      // Add created_by if using audit columns/trait
      // 'created_by' => Auth::id(), // Use imported Auth
    ]);

    // Refresh the category detail panel if no category was previously selected
    if (!$this->categoryInfo) {
      // Load info for the newly created category
      $this->showCategoryInfo($newCategory->id);
    }
    // Success feedback and dispatching is handled in submitCategory()
  }

  // Show modal for editing an existing category
  public function showEditCategoryModal(Category $category): void // Use route model binding, void return type
  {
    $this->resetValidation(); // Clear validation errors
    $this->reset('isEdit', 'categoryName', 'category'); // Reset category modal state
    $this->isEdit = true; // Set edit mode
    $this->category = $category; // Store the model for update
    $this->categoryName = $category->name; // Populate form field

    // Dispatch JS event to open modal if not handled by wire:click
    // $this->dispatch('openModal', elementId: '#categoryModal');
  }

  // Update an existing category record
  protected function editCategory(): void // Use protected as called internally, void return type
  {
    // Validation is handled in submitCategory()
    if (!$this->category) {
      // Should not happen if flow is correct, but defensive check
      Log::error('Categories component: Attempted to update non-existent category model.', ['user_id' => Auth::id(), 'is_edit' => $this->isEdit]); // Use imported Log and Auth
      throw new Exception(__('Category record not found for update.')); // Throw exception for transaction rollback, Use imported Exception
    }

    $this->category->update([
      'name' => $this->categoryName,
      // Add updated_by if using audit columns/trait
      // 'updated_by' => Auth::id(), // Use imported Auth
    ]);

    // Refresh the category detail panel if the updated category is the currently displayed one
    if ($this->categoryInfo && $this->categoryInfo->id === $this->category->id) {
      // Simply update the name in categoryInfo without a full reload for performance/smoothness
      $this->categoryInfo->name = $this->categoryName;
      // If other fields were updated, would need to re-fetch or update more properties of categoryInfo
      // For complex updates, re-calling showCategoryInfo might be safer:
      // $this->showCategoryInfo($this->categoryInfo->id);
    }

    // Success feedback and dispatching is handled in submitCategory()
  }

  // Confirm deletion of a category
  public function confirmDeleteCategory(int $categoryId): void // Accept ID, void return type
  {
    $this->confirmedCategoryId = $categoryId; // Store ID for confirmation
    // Dispatch JS event to show confirmation modal (e.g., SweetAlert)
    // $this->dispatch('openConfirmModal', elementId: '#deleteCategoryConfirmationModal');
  }

  // Perform the deletion of a category
  // Refactored to use confirmedId for consistency
  public function deleteCategory(): void // Void return type
  {
    // Check if an ID is confirmed
    if ($this->confirmedCategoryId === null) {
      return; // No ID to delete
    }

    // Find the record to delete
    $record = Category::find($this->confirmedCategoryId);

    if (!$record) {
      // Record not found (maybe already deleted)
      session()->flash('error', __('Category record not found for deletion.'));
      $this->dispatch('toastr', type: 'error', message: __('Not Found!'));
    } else {
      // Perform the deletion
      try {
        // Add logic to handle related sub-categories before deleting the category
        // E.g., detach or delete sub-categories if not handled by database CASCADE or model events
        // $record->subCategories()->delete(); // If sub-categories should be deleted with category

        $record->delete(); // Use model delete method (handles soft deletes if enabled)

        session()->flash('success', __('Category Deleted Successfully!'));
        $this->dispatch('toastr', type: 'success', message: __('Going Well!'));

        // --- After deletion, update state ---
        // Reset the confirmed ID
        $this->reset('confirmedCategoryId');
        // If the deleted category was the one whose info is displayed, clear the info panel
        if ($this->categoryInfo && $this->categoryInfo->id === $record->id) {
          $this->categoryInfo = null;
          // Maybe select the first category if any remain
          $firstCategory = Category::orderBy('id')->first();
          if ($firstCategory) {
            $this->showCategoryInfo($firstCategory->id);
          } else {
            // If no categories remain, dispatch event to clear view
            $this->dispatch('clearCategoryInfo'); // Custom event
          }
        }
        // Refresh the categories list by resetting pagination
        $this->resetPage('categoriesPage');
      } catch (Exception $e) { // Use imported Exception
        // Handle potential database errors during deletion (e.g., foreign key constraints)
        Log::error('Categories component: Failed to delete category record.', ['user_id' => Auth::id(), 'category_id' => $this->confirmedCategoryId, 'exception' => $e]); // Use imported Log and Auth
        session()->flash('error', __('An error occurred while deleting the category.') . ' ' . $e->getMessage()); // Show exception message or generic error
        $this->dispatch('toastr', type: 'error', message: __('Operation Failed!'));
        // Reset confirmedId even on failure
        $this->reset('confirmedCategoryId');
      }
    }
    // Dispatch JS event to close confirmation modal (if using one)
    // $this->dispatch('closeConfirmModal', elementId: '#deleteCategoryConfirmationModal');
  }


  // --------------------
  // SubCategory Methods
  // --------------------

  // Show modal for adding a new sub-category
  public function showNewSubCategoryModal(): void // Void return type
  {
    // Reset validation errors
    $this->resetValidation();
    // Reset sub-category modal state, ensure categoryInfo is set as parent is required
    if (!$this->categoryInfo) {
      session()->flash('warning', __('Please select a Category first before adding a sub-category.'));
      $this->dispatch('toastr', type: 'warning', message: __('Action Required!'));
      return; // Stop if no category is selected
    }
    $this->reset('isEdit', 'subCategoryName', 'subCategory'); // Reset sub-category modal state
    $this->isEdit = false; // Explicitly set mode
    // Dispatch JS event to open modal if not handled by wire:click
    // $this->dispatch('openModal', elementId: '#subCategoryModal');
  }

  // Submit sub-category creation or update form
  public function submitSubCategory(): void // Void return type
  {
    // Check if a parent category is selected before validation/saving
    if (!$this->categoryInfo) {
      // This case should ideally be caught before showing the modal, but adding defensive check
      session()->flash('error', __('Cannot save sub-category without a selected parent category.'));
      $this->dispatch('toastr', type: 'error', message: __('Error!'));
      // Close modal as state is likely invalid
      $this->dispatch('closeModal', elementId: '#subCategoryModal');
      return;
    }

    // Use $this->validate() to run validation rules
    $rules = [
      'subCategoryName' => ['required', 'string', 'max:255'],
    ];

    // Add uniqueness validation scoped to the parent category
    // Check against sub_categories table, 'name' column, where 'category_id' is the parent category ID
    // Ignore the current sub-category's ID if in edit mode
    $rules['subCategoryName'][] = Rule::unique('sub_categories', 'name') // Use imported Rule
      ->where('category_id', $this->categoryInfo->id)
      ->ignore($this->isEdit ? $this->subCategory->id : null);

    $this->validate($rules);


    // Use a transaction for atomicity
    try {
      \Illuminate\Support\Facades\DB::transaction(function () {
        if ($this->isEdit) {
          $this->editSubCategory();
        } else {
          $this->addSubCategory();
        }
      });

      // Success feedback and dispatch events after transaction
      session()->flash('success', $this->isEdit ? __('Sub-Category Updated Successfully!') : __('Sub-Category Added Successfully!'));
      $this->dispatch('closeModal', elementId: '#subCategoryModal'); // Assuming JS event
      $this->dispatch('toastr', type: 'success', message: __('Operation Successful!')); // Assuming JS event

      // Refresh the sub-category list by resetting its pagination for the current category
      $this->resetPage('subCategoriesPage');
      // Re-show category info to ensure the relationship count (if displayed) is updated
      // This also triggers resetSubCategoryPage via listener
      $this->showCategoryInfo($this->categoryInfo->id);
    } catch (Exception $e) { // Use imported Exception
      // Log the error
      Log::error('Sub-Category submit failed: ' . $e->getMessage(), ['user_id' => Auth::id(), 'category_id' => $this->categoryInfo->id, 'sub_category_name' => $this->subCategoryName, 'is_edit' => $this->isEdit]); // Use imported Log and Auth

      // Flash and dispatch error feedback
      session()->flash('error', __('An error occurred while saving the sub-category.')); // Generic error message
      $this->dispatch('toastr', type: 'error', message: __('Operation Failed!')); // Assuming JS event
      // Keep modal open on error? Original closed. Let's keep original behavior.
      // If you want to keep modal open on validation errors, remove this close dispatch
      $this->dispatch('closeModal', elementId: '#subCategoryModal');
    } finally {
      // Reset modal state regardless of success or failure
      $this->reset('isEdit', 'subCategoryName', 'subCategory');
    }
  }


  // Add a new sub-category record
  protected function addSubCategory(): void // Use protected as called internally, void return type
  {
    // Validation and parent category check are handled in submitSubCategory()

    SubCategory::create([
      'name' => $this->subCategoryName,
      'category_id' => $this->categoryInfo->id, // Link subcategory to current category
      // Add created_by if using audit columns/trait
      // 'created_by' => Auth::id(), // Use imported Auth
    ]);

    // Refresh the sub-category list and category info (handled in submitSubCategory)
    // Success feedback and dispatching is handled in submitSubCategory()
  }

  // Show modal for editing an existing sub-category
  public function showEditSubCategoryModal(SubCategory $subCategory): void // Use route model binding, void return type
  {
    // Reset validation errors
    $this->resetValidation();
    $this->reset('isEdit', 'subCategoryName', 'subCategory'); // Reset sub-category modal state
    $this->isEdit = true; // Set edit mode
    $this->subCategory = $subCategory; // Store the model for update
    $this->subCategoryName = $subCategory->name; // Populate form field

    // Ensure the parent category info is loaded if it wasn't already
    // This might happen if editing a sub-category from a list that isn't tied to the detail panel
    if (!$this->categoryInfo || $this->categoryInfo->id !== $subCategory->category_id) {
      $this->showCategoryInfo($subCategory->category_id); // Load/reload the parent category info
    }


    // Dispatch JS event to open modal if not handled by wire:click
    // $this->dispatch('openModal', elementId: '#subCategoryModal');
  }

  // Update an existing sub-category record
  protected function editSubCategory(): void // Use protected as called internally, void return type
  {
    // Validation is handled in submitSubCategory()
    if (!$this->subCategory) {
      // Should not happen if flow is correct, but defensive check
      Log::error('Categories component: Attempted to update non-existent subCategory model.', ['user_id' => Auth::id(), 'is_edit' => $this->isEdit]); // Use imported Log and Auth
      throw new Exception(__('Sub-Category record not found for update.')); // Throw exception for transaction rollback, Use imported Exception
    }

    $this->subCategory->update([
      'name' => $this->subCategoryName,
      // Add updated_by if using audit columns/trait
      // 'updated_by' => Auth::id(), // Use imported Auth
    ]);

    // Refresh the category detail panel if the updated sub-category belongs to the currently displayed category
    // This is implicitly handled by showCategoryInfo call in submitSubCategory after resetPage('subCategoriesPage')
    // Success feedback and dispatching is handled in submitSubCategory()
  }

  // Confirm deletion of a sub-category
  public function confirmDeleteSubCategory(int $subCategoryId): void // Accept ID, void return type
  {
    $this->confirmedSubCategoryId = $subCategoryId; // Store ID for confirmation
    // Dispatch JS event to show confirmation modal
    // $this->dispatch('openConfirmModal', elementId: '#deleteSubCategoryConfirmationModal');
  }

  // Perform the deletion of a sub-category
  // Refactored to use confirmedId for consistency
  public function deleteSubCategory(): void // Void return type
  {
    // Check if an ID is confirmed
    if ($this->confirmedSubCategoryId === null) {
      return; // No ID to delete
    }

    // Find the record to delete
    $record = SubCategory::find($this->confirmedSubCategoryId);

    if (!$record) {
      // Record not found (maybe already deleted)
      session()->flash('error', __('Sub-Category record not found for deletion.'));
      $this->dispatch('toastr', type: 'error', message: __('Not Found!'));
    } else {
      // Store parent category ID before deletion to refresh info panel later
      $parentCategoryId = $record->category_id;

      // Perform the deletion
      try {
        $record->delete(); // Use model delete method (handles soft deletes if enabled)

        session()->flash('success', __('Sub-Category Deleted Successfully!'));
        $this->dispatch('toastr', type: 'success', message: __('Going Well!'));

        // --- After deletion, update state ---
        // Reset the confirmed ID
        $this->reset('confirmedSubCategoryId');
        // Refresh the sub-category list by resetting pagination
        $this->resetPage('subCategoriesPage');
        // Re-show the parent category info to update the relationship count (if displayed)
        // This also triggers resetSubCategoryPage via listener
        $this->showCategoryInfo($parentCategoryId);
      } catch (Exception $e) { // Use imported Exception
        // Handle potential database errors during deletion
        Log::error('Categories component: Failed to delete sub-category record.', ['user_id' => Auth::id(), 'sub_category_id' => $this->confirmedSubCategoryId, 'exception' => $e]); // Use imported Log and Auth
        session()->flash('error', __('An error occurred while deleting the sub-category.') . ' ' . $e->getMessage()); // Show exception message or generic error
        $this->dispatch('toastr', type: 'error', message: __('Operation Failed!'));
        // Reset confirmedId even on failure
        $this->reset('confirmedSubCategoryId');
      }
    }
    // Dispatch JS event to close confirmation modal (if using one)
    // $this->dispatch('closeConfirmModal', elementId: '#deleteSubCategoryConfirmationModal');
  }
}
