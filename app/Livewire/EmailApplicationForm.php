<?php

namespace App\Livewire;

use App\Models\Grade;
use Livewire\Component;
use App\Models\Department;

class EmailApplicationForm extends Component
{
  public $service_status;
  public $certification = false;

  public function render()
  {
    return view('livewire.email-application-form', [
      'grades' => Grade::all(),
      'departments' => Department::all()
    ]);
  }

  public function submit()
  {
    $this->validate([
      'service_status' => 'required|in:permanent,contract,mystep,intern',
      'certification' => 'accepted'
    ]);

    // Save logic
  }
}
