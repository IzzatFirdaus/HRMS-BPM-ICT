<?php

namespace App\Livewire\Settings;

use App\Models\User; // Import User model
use Livewire\Component;
use Illuminate\Validation\Rule; // Import Rule for validation (e.g., unique email)
// use Spatie\Permission\Models\Role; // Import Role model if managing roles
// use App\Models\Department; // Import Department model if selecting department
// use App\Models\Position; // Import Position model if selecting position
// use App\Models\Grade; // Import Grade model if selecting grade


class EditUser extends Component
{
  // Define a public property to hold the User model instance being edited
  public User $user;

  // Define public properties for the form fields
  public $name;
  public $email;
  // Add other public properties corresponding to the form fields in the view
  // public $department_id;
  // public $position_id;
  // public $grade_id;
  // public $mobile_number;
  // public $service_status;
  // public $selectedRoles = []; // Array to hold selected role names


  // Define validation rules for the form properties
  protected function rules()
  {
    return [
      'name' => 'required|string|max:255',
      // Email must be unique in the users table, except for the current user's email
      'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($this->user->id)],
      // Add validation rules for other properties
      // 'department_id' => 'required|exists:departments,id',
      // 'position_id' => 'required|exists:positions,id',
      // 'grade_id' => 'required|exists:grades,id',
      // 'mobile_number' => 'nullable|string|max:20',
      // 'service_status' => ['required', Rule::in(['permanent', 'contract', 'mystep', 'intern', 'other_agency'])],
      // 'selectedRoles' => 'nullable|array',
      // 'selectedRoles.*' => 'exists:roles,name', // Validate each selected role name exists
    ];
  }


  // The mount method receives the User model (via route model binding)
  // This method runs once when the component is initialized
  public function mount(User $user)
  {
    $this->user = $user;
    // Initialize form properties from the user model's current data
    $this->name = $user->name; // Or $user->full_name if that's the field
    $this->email = $user->email;
    // Initialize other properties from the user model
    // $this->department_id = $user->department_id;
    // $this->position_id = $user->position_id;
    // $this->grade_id = $user->grade_id;
    // $this->mobile_number = $user->mobile_number;
    // $this->service_status = $user->service_status;
    // Initialize selectedRoles if managing roles
    // $this->selectedRoles = $user->getRoleNames()->toArray(); // Get current role names as array
  }

  /**
   * Render the component's view.
   *
   * @return \Illuminate\Contracts\View\View|\Closure|string
   */
  public function render()
  {
    // Pass any additional data needed by the view (e.g., lists of departments, roles)
    // $departments = Department::all(); // Example
    // $allRoles = Role::all(); // Example

    return view('livewire.settings.edit-user', [
      'user' => $this->user, // Pass the user instance to the view
      // 'departments' => $departments, // Pass departments list to the view
      // 'allRoles' => $allRoles, // Pass roles list to the view
    ]);
  }

  // Method to handle form submission and update the user
  public function saveUser()
  {
    $validatedData = $this->validate(); // Validate the form data using the rules() method

    // Update the user model with validated data
    $this->user->update([
      'name' => $validatedData['name'],
      'email' => $validatedData['email'],
      // Update other properties based on validatedData
      // 'department_id' => $validatedData['department_id'],
      // // ... update other properties ...
    ]);

    // Handle roles update (example)
    // if (isset($validatedData['selectedRoles'])) {
    //     $this->user->syncRoles($validatedData['selectedRoles']); // Sync roles using Spatie
    // }


    // Optional: Redirect or emit an event after saving
    session()->flash('message', __('User updated successfully.')); // Flash a success message

    // Redirect back to the user details page or list page
    return redirect()->route('settings-users.show', $this->user);
    // Or redirect to the list page: return redirect()->route('settings-users');
  }

  // Optional: Add methods like updated() to perform actions when a property is updated

}
