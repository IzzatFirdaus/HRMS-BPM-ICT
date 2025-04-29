<?php

namespace App\Livewire\Settings;

use Livewire\Component; // Base Livewire component


// This is a simple Livewire component to render the users management view in settings.
// It currently doesn't contain user management logic, which would be in the view or other components.

class Users extends Component
{
  /**
   * Render the component's view.
   *
   * @return \Illuminate\Contracts\View\View
   */
  public function render(): \Illuminate\Contracts\View\View // Added return type hint
  {
    // This method simply returns the Blade view for the users settings section.
    // User listing/management UI and logic would typically be within the Blade file or child components.
    return view('livewire.settings.users');
  }
}
