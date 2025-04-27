<?php

namespace App\Livewire\Assets;

use App\Models\Equipment;
use Livewire\Attributes\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class Inventory extends Component
{
  use WithPagination;

  public Equipment $equipment;

  public $search_term = '';
  public $equipmentId;
  public $oldId;
  public $serialNumber;
  #[Rule('required')]
  public $assetType;
  public $brand;
  public $model_name;
  #[Rule('required')]
  public $condition_status;
  #[Rule('required')]
  public $availabilityStatus;
  public $realPrice;
  public $expectedPrice;
  #[Rule('required')]
  public $acquisitionDate;
  #[Rule('required')]
  public $acquisitionType;
  #[Rule('required')]
  public $fundedBy;
  public $notes;
  public $lastServiceDate;
  public $nextServiceDate;
  public $isEdit = false;
  public $confirmedId;

  protected string $paginationTheme = 'bootstrap';

  public $colors = [
    'Good' => 'success',
    'Fine' => 'primary',
    'Bad' => 'warning',
    'Damaged' => 'danger',
    'available' => 'success',
    'on_loan' => 'warning',
    'maintenance' => 'info',
    'disposed' => 'secondary',
    'assigned' => 'primary',
  ];

  public function updatedSearchTerm()
  {
    $this->resetPage();
  }

  public function render()
  {
    $assets = Equipment::query()
      ->where(function ($query) {
        $query->where('id', 'like', '%' . $this->search_term . '%')
          ->orWhere('old_id', 'like', '%' . $this->search_term . '%')
          ->orWhere('serial_number', 'like', '%' . $this->search_term . '%')
          ->orWhere('asset_type', 'like', '%' . $this->search_term . '%')
          ->orWhere('brand', 'like', '%' . $this->search_term . '%')
          ->orWhere('model', 'like', '%' . $this->search_term . '%')
          ->orWhere('condition_status', 'like', '%' . $this->search_term . '%')
          ->orWhere('availability_status', 'like', '%' . $this->search_term . '%')
          ->orWhere('acquisition_type', 'like', '%' . $this->search_term . '%')
          ->orWhere('funded_by', 'like', '%' . $this->search_term . '%')
          ->orWhere('notes', 'like', '%' . $this->search_term . '%');
      })
      ->paginate(10);

    return view('livewire.assets.inventory', ['assets' => $assets]);
  }

  public function submit()
  {
    $this->isEdit ? $this->updateAsset() : $this->storeAsset();
  }

  public function create()
  {
    $this->resetExcept('paginationTheme', 'colors');
    $this->isEdit = false;
    $this->dispatch('showModal', elementId: '#assetModal');
  }

  public function edit(Equipment $equipment)
  {
    $this->resetExcept('paginationTheme', 'colors');

    $this->isEdit = true;
    $this->equipment = $equipment;

    $this->equipmentId = $equipment->id;
    $this->serialNumber = $equipment->serial_number;
    $this->assetType = $equipment->asset_type;
    $this->brand = $equipment->brand;
    $this->model_name = $equipment->model;
    $this->condition_status = $equipment->condition_status;
    $this->availabilityStatus = $equipment->availability_status;
    $this->realPrice = $equipment->real_price;
    $this->expectedPrice = $equipment->expected_price;
    $this->acquisitionDate = optional($equipment->acquisition_date)->format('Y-m-d');
    $this->acquisitionType = $equipment->acquisition_type;
    $this->fundedBy = $equipment->funded_by;
    $this->notes = $equipment->notes;
    $this->lastServiceDate = optional($equipment->last_service_date)->format('Y-m-d');
    $this->nextServiceDate = optional($equipment->next_service_date)->format('Y-m-d');

    $this->dispatch('showModal', elementId: '#assetModal');
  }

  protected function rules()
  {
    return [
      'assetType' => 'required|string|max:255',
      'condition_status' => 'required|string|max:255',
      'availabilityStatus' => 'required|string|max:255',
      'acquisitionDate' => 'required|date',
      'acquisitionType' => 'required|string|max:255',
      'fundedBy' => 'required|string|max:255',
      'serialNumber' => 'nullable|string|max:255|unique:equipment,serial_number,' . ($this->equipment->id ?? 'null'),
      'brand' => 'nullable|string|max:255',
      'model_name' => 'nullable|string|max:255',
      'realPrice' => 'nullable|numeric',
      'expectedPrice' => 'nullable|numeric',
      'notes' => 'nullable|string',
      'lastServiceDate' => 'nullable|date',
      'nextServiceDate' => 'nullable|date|after_or_equal:lastServiceDate',
    ];
  }

  public function storeAsset()
  {
    $validated = $this->validate();

    Equipment::create([
      'asset_type' => $this->assetType,
      'brand' => $this->brand,
      'model' => $this->model_name,
      'serial_number' => $this->serialNumber,
      'condition_status' => $this->condition_status,
      'availability_status' => $this->availabilityStatus,
      'real_price' => $this->realPrice,
      'expected_price' => $this->expectedPrice,
      'acquisition_date' => $this->acquisitionDate,
      'acquisition_type' => $this->acquisitionType,
      'funded_by' => $this->fundedBy,
      'notes' => $this->notes,
      'last_service_date' => $this->lastServiceDate,
      'next_service_date' => $this->nextServiceDate,
    ]);

    $this->dispatch('closeModal', elementId: '#assetModal');
    $this->dispatch('toastr', type: 'success', message: __('Equipment Added Successfully!'));
    $this->resetExcept('paginationTheme', 'colors');
    $this->resetPage();
  }

  public function updateAsset()
  {
    if (!isset($this->equipment)) {
      $this->dispatch('toastr', type: 'error', message: __('No equipment selected for update.'));
      return;
    }

    $validated = $this->validate();

    $this->equipment->update([
      'asset_type' => $this->assetType,
      'brand' => $this->brand,
      'model' => $this->model_name,
      'serial_number' => $this->serialNumber,
      'condition_status' => $this->condition_status,
      'availability_status' => $this->availabilityStatus,
      'real_price' => $this->realPrice,
      'expected_price' => $this->expectedPrice,
      'acquisition_date' => $this->acquisitionDate,
      'acquisition_type' => $this->acquisitionType,
      'funded_by' => $this->fundedBy,
      'notes' => $this->notes,
      'last_service_date' => $this->lastServiceDate,
      'next_service_date' => $this->nextServiceDate,
    ]);

    $this->dispatch('closeModal', elementId: '#assetModal');
    $this->dispatch('toastr', type: 'success', message: __('Equipment Updated Successfully!'));
    $this->resetExcept('paginationTheme', 'colors');
    $this->resetPage();
  }

  public function confirmDelete($id)
  {
    $this->confirmedId = $id;
    $this->dispatch('showModal', elementId: '#deleteConfirmModal');
  }

  public function delete()
  {
    if (!$this->confirmedId) {
      $this->dispatch('toastr', type: 'warning', message: __('No equipment selected for deletion!'));
      return;
    }

    $equipment = Equipment::find($this->confirmedId);

    if ($equipment) {
      $equipment->delete();
      $this->dispatch('toastr', type: 'success', message: __('Equipment Deleted Successfully!'));
    } else {
      $this->dispatch('toastr', type: 'error', message: __('Equipment not found!'));
    }

    $this->confirmedId = null;
    $this->dispatch('closeModal', elementId: '#deleteConfirmModal');
    $this->resetPage();
  }
}
