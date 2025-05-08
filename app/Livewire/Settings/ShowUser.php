<?php

namespace App\Livewire\Settings;

use App\Models\User; // Import User model
use Livewire\Component;

class ShowUser extends Component
{
  // Define a public property to hold the User model instance
  public User $user;

  // The mount method receives the User model (via route model binding)
  public function mount(User $user)
  {
    $this->user = $user;
  }

  public function render()
  {
    // Renders the view for showing user details
    return view('livewire.settings.show-user', [
      'user' => $this->user, // Pass the user instance to the view
    ]);
  }

  // Add other methods as needed for viewing a user
}
