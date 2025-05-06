<?php

namespace App\Livewire\HumanResource;

use App\Exports\ExportDiscounts;
use App\Exports\ExportSummary;
use App\Models\Discount; // Make sure this model exists and is correctly namespaced
use App\Models\Employee; // Make sure this model exists and is correctly namespaced
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Collection; // Import Collection for type hinting
use Illuminate\Database\Eloquent\Builder; // Import Builder for type hinting in whereHas callbacks
use Illuminate\Support\Facades\Log; // Import Log facade

class Statistics extends Component
{
  public Collection $batches; // Type hint as Collection

  public ?string $selectedBatch = null; // Use nullable string for selected batch

  public function mount()
  {
    // Ensure the Discount model and 'batch' column exist
    // Ensure the 'discounts' relationship exists on the Employee model
    // Use get() and then pluck() on the resulting collection
    try {
      $this->batches = Discount::orderBy('batch', 'desc')
        ->distinct()
        ->get() // Get the results first
        ->pluck('batch'); // Then pluck the batch column

      // Check if batches is not empty before trying to access the first element
      if ($this->batches->isNotEmpty()) {
        $this->selectedBatch = $this->batches->first(); // Use first() method on Collection
      } else {
        // Handle the case where there are no discount batches
        $this->selectedBatch = null; // No batches available
        Log::warning('No discount batches found in the database for statistics.');
      }
    } catch (\Exception $e) {
      // Log error if fetching batches fails
      Log::error('Failed to fetch discount batches for statistics: ' . $e->getMessage(), ['exception' => $e]);
      $this->batches = Collection::make(); // Assign empty collection on error
      $this->selectedBatch = null;
      session()->flash('error', 'Gagal memuatkan senarai batch diskaun.'); // Malay message
    }
  }

  public function render()
  {
    // Only attempt to get discounts if a batch is selected
    $employeeDiscounts = $this->selectedBatch ? $this->getEmployeeDiscounts() : Collection::make();

    return view('livewire.human-resource.statistics', ['employeeDiscounts' => $employeeDiscounts]);
  }

  public function getEmployeeDiscounts(): Collection // Added return type hint
  {
    // Return empty collection if no batch is selected
    if (!$this->selectedBatch) {
      return Collection::make();
    }

    try {
      // Ensure Employee model exists and has a 'discounts' HasMany relationship
      // Ensure Discount model exists and has an 'employee_id' foreign key and 'batch' column
      return Employee::whereHas('discounts', function (Builder $query) { // Use Builder type hint
        $query->where('batch', $this->selectedBatch);
      })
        ->with([
          // Eager load the discounts relationship, filtering by selected batch
          'discounts' => function ($query) { // No need for Builder type hint here, it's a relationship query builder
            $query->where('batch', $this->selectedBatch);
          },
          'user', // Assuming Employee belongs to a User and you might need user info
        ])
        ->get() // Execute the query to get the Employee models
        ->each(function ($employee) { // Process each Employee model after relationships are loaded
          // --- FIX for PHP2436: Cannot modify readonly property ---
          // Instead of reassigning $employee->discounts, use the loaded collection directly or assign to a different property.
          // The goal was likely to sort discounts for display or calculate count.
          // Sorting the loaded collection locally is fine, but don't assign it back to the relationship property.

          // If you need sorted discounts in the view, consider:
          // 1. Sorting the main $employeeDiscounts collection of employees *after* this ->each() loop.
          // 2. Adding a specific accessor to the Employee model that returns sorted discounts.
          // For calculating count *within* the loop, sorting is not needed on the discounts themselves.

          // Calculate cash_discounts_count using the loaded 'discounts' relationship
          $employee->cash_discounts_count = $employee->discounts // Access the already loaded relationship collection
            ->filter(function ($discount) {
              // Ensure Discount model exists and has a 'rate' property
              return $discount->rate > 0; // Assuming 'rate' is the column for the discount value
            })
            ->count();

          // Optional: If you absolutely need sorted discounts accessible on the employee object
          // without triggering the readonly error, you could add a temporary property:
          // $employee->sorted_discounts = $employee->discounts->sortBy('date');
          // And then use $employee->sorted_discounts in the view. However, this mutates
          // the collection of employees returned from get().

        })
        ->sortBy('first_name') // Sort the Employee collection by first name
        ->sortByDesc('cash_discounts_count'); // Then sort by cash discounts count descending

    } catch (\Exception $e) {
      // Log error if fetching employee discounts fails
      Log::error('Failed to fetch employee discounts for batch "' . ($this->selectedBatch ?? 'N/A') . '": ' . $e->getMessage(), ['exception' => $e]);
      session()->flash('error', 'Gagal memuatkan data diskaun pekerja.'); // Malay message
      return Collection::make(); // Return empty collection on error
    }
  }

  // Assuming ExportDiscounts and ExportSummary exist and are correctly namespaced
  // Assuming Maatwebsite\Excel is installed and configured
  public function exportDiscounts()
  {
    // Ensure getEmployeeDiscounts returns a Collection that the Export class can handle
    try {
      $employeeDiscounts = $this->getEmployeeDiscounts();
      if ($employeeDiscounts->isEmpty()) {
        session()->flash('warning', 'Tiada data diskaun untuk batch ini bagi tujuan eksport.'); // Malay message
        return; // Stop if no data
      }
      // Ensure ExportDiscounts class exists
      return Excel::download(
        new ExportDiscounts($employeeDiscounts), // Pass the Collection to the Exporter
        'Discounts - ' . $this->selectedBatch . '.xlsx'
      );
    } catch (\Exception $e) {
      Log::error('Failed to export discounts for batch "' . ($this->selectedBatch ?? 'N/A') . '": ' . $e->getMessage(), ['exception' => $e]);
      session()->flash('error', 'Gagal mengeksport data diskaun.'); // Malay message
      return; // Stop on error
    }
  }

  public function exportSummary()
  {
    try {
      $employeeDiscounts = $this->getEmployeeDiscounts();
      if ($employeeDiscounts->isEmpty()) {
        session()->flash('warning', 'Tiada data ringkasan untuk batch ini bagi tujuan eksport.'); // Malay message
        return; // Stop if no data
      }
      // Ensure ExportSummary class exists
      return Excel::download(
        new ExportSummary($employeeDiscounts), // Pass the Collection to the Exporter
        'Summary - ' . $this->selectedBatch . '.xlsx'
      );
    } catch (\Exception $e) {
      Log::error('Failed to export summary for batch "' . ($this->selectedBatch ?? 'N/A') . '": ' . $e->getMessage(), ['exception' => $e]);
      session()->flash('error', 'Gagal mengeksport data ringkasan.'); // Malay message
      return; // Stop on error
    }
  }
}
