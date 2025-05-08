<?php

namespace App\Livewire\ResourceManagement\Admin\Grades;

use Livewire\Component;
use Livewire\WithPagination; // For pagination
use Illuminate\Foundation\Auth\Access\AuthorizesRequests; // For authorization
use App\Traits\CreatedUpdatedDeletedBy; // Use the audit trait

// Consider using models like Grade, User
// use App\Models\Grade;
// use App\Models\User;

class Index extends Component
{
  use WithPagination, AuthorizesRequests; // Use pagination and authorization
  // Note: CreatedUpdatedDeletedBy is usually used on Models, not Livewire Components.
  // Including it here just to acknowledge it was shared, but you'd use it on your Grade model.
  // use CreatedUpdatedDeletedBy;

  // --- State Properties ---
  // public $showingCreateModal = false;
  // public $showingEditModal = false;

  // --- Computed Properties ---
  // public function getGradesProperty()
  // {
  //     return Grade::latest('level')->paginate(10); // Order by level
  // }

  public function render()
  {
    // Authorization check for the page itself
    // $this->authorize('viewAny', Grade::class); // Assuming a Grade policy or permission

    return view('livewire.resource-management.admin.grades.index', [
      // 'grades' => $this->grades, // Pass computed property
    ]);
  }

  // --- Actions ---
  // public function createGrade() { ... }
  // public function editGrade($gradeId) { ... }
  // public function deleteGrade($gradeId) { ... }
}
