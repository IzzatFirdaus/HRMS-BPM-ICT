<?php

namespace App\Livewire\Settings;

use App\Models\User; // Import User model
use Livewire\Component;
use Illuminate\Validation\Rule; // Import Rule if needed for validation
use Illuminate\Support\Facades\Hash; // Import Hash facade for password hashing
// use Spatie\Permission\Models\Role; // Import Role model if managing roles
// use App\Models\Department; // Import Department model if selecting department
// use App\Models\Position; // Import Position model if selecting position
// use App\Models\Grade; // Import Grade model if selecting grade


class CreateUser extends Component
{
  // Define public properties for the new user form fields
  public $name = '';
  public $email = '';
  public $password = '';
  // Optional: Password confirmation property if you add it to the form
  // public $password_confirmation = '';
  // Add other public properties corresponding to the form fields in the view
  // public $department_id = '';
  // public $position_id = '';
  // public $grade_id = '';
  // public $mobile_number = '';
  // public $service_status = ''; // Maybe set a default or leave blank
  // public $selectedRoles = []; // Array to hold selected role names for creation


  // Define validation rules for the form properties
  protected function rules()
  {
    return [
      'name' => 'required|string|max:255',
      // Email must be unique in the users table
      'email' => ['required', 'email', Rule::unique('users', 'email')],
      // Password validation - min length
      'password' => 'required|string|min:8',
      // Optional: Password confirmation validation
      // 'password' => 'required|string|min:8|confirmed', // 'confirmed' rule requires password_confirmation field
      // Add validation rules for other properties
      // 'department_id' => 'required|exists:departments,id',
      // 'position_id' => 'required|exists:positions,id',
      // 'grade_id' => 'required|exists:grades,id',
      // 'mobile_number' => 'nullable|string|max:20',
      // 'service_status' => ['required', Rule::in(['permanent', 'contract', 'mystep', 'intern', 'other_agency'])],
      // 'selectedRoles' => 'nullable|array', // Selected roles are optional during creation
      // 'selectedRoles.*' => 'exists:roles,name', // Validate each selected role name exists
    ];
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

    return view('livewire.settings.create-user', [
      // 'departments' => $departments, // Pass departments list to the view
      // 'allRoles' => $allRoles, // Pass roles list to the view
    ]);
  }

  // Method to handle form submission and create the new user
  public function saveUser()
  {
    $validatedData = $this->validate(); // Validate the form data using the rules() method

    // Create the new user in the database using the validated data
    $user = User::create([
      'name' => $validatedData['name'],
      'email' => $validatedData['email'],
      // Hash the password before saving!
      'password' => Hash::make($validatedData['password']),
      // Add other properties from validatedData
      // 'department_id' => $validatedData['department_id'],
      // 'position_id' => $validatedData['position_id'],
      // 'grade_id' => $validatedData['grade_id'],
      // 'mobile_number' => $validatedData['mobile_number'] ?? null, // Use null for nullable fields
      // 'service_status' => $validatedData['service_status'],
      // Add default status like 'active' if not set via form
      'status' => 'active',
      // Set created_by if using the trait and not handled automatically
      // 'created_by' => auth()->id(),
    ]);

    // Assign roles (example, assuming Spatie and selectedRoles property)
    // if (isset($validatedData['selectedRoles']) && !empty($validatedData['selectedRoles'])) {
    //      $user->assignRole($validatedData['selectedRoles']); // Use assignRole for array or syncRoles
    // }


    // Reset the form fields after successful creation
    $this->reset(['name', 'email', 'password', /* add other properties */]);
    // Optional: Reset selected roles separately if needed
    // $this->selectedRoles = [];


    // Optional: Flash a success message
    session()->flash('message', __('User created successfully.'));

    // Optional: Redirect to the user list or show page
    // return redirect()->route('settings-users');
    // Or redirect to the show page: return redirect()->route('settings-users.show', $user);
  }

  // Optional: Add methods like updated() to perform actions when a property is updated

}
