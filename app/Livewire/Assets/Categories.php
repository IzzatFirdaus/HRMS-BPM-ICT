<?php

namespace App\Livewire\Assets;

use App\Models\Category;
use App\Models\SubCategory;
use Livewire\Component;
use Livewire\WithPagination;

class Categories extends Component
{
  use WithPagination;

  public $search_term_categories = '';
  public $search_term_sub_categories = '';

  public $category;
  public $categoryInfo;
  public $subCategory;

  public $categoryName;
  public $subCategoryName;

  public $isEdit = false;
  public $confirmedCategoryId;
  public $confirmedSubCategoryId;

  public function mount()
  {
    $this->showCategoryInfo(1); // Load first category info by default
  }

  public function render()
  {
    $categories = Category::query()
      ->where('id', 'like', '%' . $this->search_term_categories . '%')
      ->orWhere('name', 'like', '%' . $this->search_term_categories . '%')
      ->paginate(6);

    $subCategories = SubCategory::query()
      ->where('id', 'like', '%' . $this->search_term_sub_categories . '%')
      ->orWhere('name', 'like', '%' . $this->search_term_sub_categories . '%')
      ->paginate(6);

    return view('livewire.assets.categories', [
      'categories' => $categories,
      'subCategories' => $subCategories,
    ]);
  }

  public function showCategoryInfo($categoryId)
  {
    $category = Category::with('subCategories')->find($categoryId);
    $this->categoryInfo = $category;
  }

  // --------------------
  // Category Methods
  // --------------------

  public function showNewCategoryModal()
  {
    $this->reset('isEdit', 'categoryName', 'category');
  }

  public function submitCategory()
  {
    $this->isEdit ? $this->editCategory() : $this->addCategory();
  }

  public function addCategory()
  {
    Category::create([
      'name' => $this->categoryName,
    ]);

    $this->dispatch('closeModal', elementId: '#categoryModal');
    $this->dispatch('toastr', type: 'success', message: __('Category Added Successfully!'));

    $this->reset('isEdit', 'categoryName', 'category');
  }

  public function showEditCategoryModal(Category $category)
  {
    $this->reset('isEdit', 'categoryName', 'category');
    $this->isEdit = true;
    $this->category = $category;
    $this->categoryName = $category->name;
  }

  public function editCategory()
  {
    $this->category->update([
      'name' => $this->categoryName,
    ]);

    $this->dispatch('closeModal', elementId: '#categoryModal');
    $this->dispatch('toastr', type: 'success', message: __('Category Updated Successfully!'));

    $this->reset('isEdit', 'categoryName', 'category');
  }

  public function confirmDeleteCategory($id)
  {
    $this->confirmedCategoryId = $id;
  }

  public function deleteCategory(Category $category)
  {
    $category->delete();

    $this->dispatch('toastr', type: 'success', message: __('Category Deleted Successfully!'));
    $this->reset('confirmedCategoryId');
  }

  // --------------------
  // SubCategory Methods
  // --------------------

  public function showNewSubCategoryModal()
  {
    $this->reset('isEdit', 'subCategoryName', 'subCategory');
  }

  public function submitSubCategory()
  {
    $this->isEdit ? $this->editSubCategory() : $this->addSubCategory();
  }

  public function addSubCategory()
  {
    if (!$this->categoryInfo) {
      $this->dispatch('toastr', type: 'error', message: __('Please select a Category first.'));
      return;
    }

    SubCategory::create([
      'name' => $this->subCategoryName,
      'category_id' => $this->categoryInfo->id, // Link subcategory to current category
    ]);

    $this->dispatch('closeModal', elementId: '#subCategoryModal');
    $this->dispatch('toastr', type: 'success', message: __('Sub-Category Added Successfully!'));

    $this->reset('isEdit', 'subCategoryName', 'subCategory');
  }

  public function showEditSubCategoryModal(SubCategory $subCategory)
  {
    $this->reset('isEdit', 'subCategoryName', 'subCategory');
    $this->isEdit = true;
    $this->subCategory = $subCategory;
    $this->subCategoryName = $subCategory->name;
  }

  public function editSubCategory()
  {
    $this->subCategory->update([
      'name' => $this->subCategoryName,
    ]);

    $this->dispatch('closeModal', elementId: '#subCategoryModal');
    $this->dispatch('toastr', type: 'success', message: __('Sub-Category Updated Successfully!'));

    $this->reset('isEdit', 'subCategoryName', 'subCategory');
  }

  public function confirmDeleteSubCategory($id)
  {
    $this->confirmedSubCategoryId = $id;
  }

  public function deleteSubCategory(SubCategory $subCategory)
  {
    $subCategory->delete();

    $this->dispatch('toastr', type: 'success', message: __('Sub-Category Deleted Successfully!'));
    $this->reset('confirmedSubCategoryId');
  }
}
