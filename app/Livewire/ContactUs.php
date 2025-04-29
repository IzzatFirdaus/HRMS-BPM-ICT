<?php

namespace App\Livewire;

use Livewire\Component; // Base Livewire component


// This is a simple Livewire component to render the contact us view.
// It currently doesn't contain form handling logic, which would likely be in the view.

class ContactUs extends Component
{
  /**
   * Render the component's view.
   *
   * @return \Illuminate\Contracts\View\View
   */
  public function render(): \Illuminate\Contracts\View\View // Added return type hint
  {
    // This method simply returns the Blade view for the contact us page.
    // The contact form UI and logic would typically be within the Blade file.
    return view('livewire.contact-us');
  }
}
