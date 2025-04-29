<?php

namespace App\Livewire\Sections\Footer;

use Livewire\Component; // Base Livewire component


// This is a simple Livewire component to render the footer section view.
// It doesn't manage state or handle user interactions within the component itself.

class Footer extends Component
{
  /**
   * Render the component's view.
   *
   * @return \Illuminate\Contracts\View\View
   */
  public function render(): \Illuminate\Contracts\View\View // Added return type hint
  {
    // This method simply returns the Blade view for the footer section.
    // All footer content and styling are expected to be defined within the Blade file itself.
    return view('livewire.sections.footer.footer');
  }
}
