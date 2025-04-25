<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth; // Import Auth facade
use Illuminate\Validation\Rule; // Import Rule for validation
use Illuminate\Support\Facades\Hash; // Import Hash facade for password hashing

class UpdateUserRequest extends FormRequest
{
  /**
   * Determine if the user is authorized to make this request.
   * This method uses the UserPolicy to check authorization for updating the specific user.
   *
   * @return bool
   */
  public function authorize(): bool
  {
    // Retrieve the User instance being updated.
    // Assuming the route parameter is named 'user' and uses route model binding.
    $user = $this->route('user');

    // Check if the authenticated user can update this specific User record.
    // This delegates the authorization check to the UserPolicy.
    // The policy's 'update' method will be called, receiving the authenticated user and the User instance being updated.
    // Assumes you have a UserPolicy defined and registered.
    return Auth::check() && Auth::user()->can('update', $user);
  }

  /**
   * Get the validation rules that apply to the request.
   * These rules validate the data submitted when updating an existing user account.
   * Fields are generally nullable to allow partial updates, but validation rules apply if data is present.
   * Unique rules use 'ignore' to exclude the current user's record.
   *
   * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
   */
  public function rules(): array
  {
    // Retrieve the User instance being updated for unique rule exclusion.
    // This is safe because authorize() would have already failed if the model wasn't found or authorized.
    $userId = $this->route('user')->id;

    return [
      'name' => ['nullable', 'string', 'max:255'], // Full name or primary name field
      // If using first_name, last_name:
      // 'first_name' => ['nullable', 'string', 'max:255'],
      // 'last_name' => ['nullable', 'string', 'max:255'],

      // Primary email (e.g., for login), must be unique, but ignore the current user's email
      'email' => ['nullable', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId)],
      // Password is optional on update. If provided, it must meet length/confirmation rules.
      'password' => ['nullable', 'string', 'min:8', 'confirmed'],

      // Optional personal email, must be unique if provided, ignore current user's personal email
      'personal_email' => ['nullable', 'string', 'email', 'max:255', Rule::unique('users', 'personal_email')->ignore($userId)],

      // IC Number, optional on update, must be unique if provided, ignore current user's IC number
      'ic_number' => ['nullable', 'string', 'max:20', Rule::unique('users', 'ic_number')->ignore($userId)],
      'grade_id' => ['nullable', 'integer', 'exists:grades,id'], // Link to Grade model, optional, must exist if provided
      'position' => ['nullable', 'string', 'max:255'], // User's job title/position, optional
      'department_id' => ['nullable', 'integer', 'exists:departments,id'], // Link to Department model, optional, must exist if provided
      'phone_number' => ['nullable', 'string', 'max:20'], // Mobile phone number, optional

      // If using a roles/permissions package and updating roles:
      'role_ids' => ['nullable', 'array'], // Optional array of role IDs to assign
      'role_ids.*' => ['integer', 'exists:roles,id'], // Each role ID must be an integer and exist in the roles table (assuming Spatie roles table structure)

      'is_admin' => ['sometimes', 'boolean'], // Optional flag, only validate if present
    ];
  }

  /**
   * Get the error messages for the defined validation rules.
   *
   * @return array<string, string>
   */
  public function messages(): array
  {
    return [
      'email.email' => 'Format alamat e-mel tidak sah.',
      'email.unique' => 'Alamat e-mel ini sudah digunakan.',
      'password.min' => 'Kata laluan mestilah sekurang-kurangnya 8 aksara.',
      'password.confirmed' => 'Pengesahan kata laluan tidak sepadan.',
      'personal_email.email' => 'Format e-mel peribadi tidak sah.',
      'personal_email.unique' => 'E-mel peribadi ini sudah digunakan.',
      'ic_number.unique' => 'Nombor Kad Pengenalan ini sudah wujud dalam sistem.',
      'grade_id.exists' => 'Gred yang dipilih tidak sah.',
      'department_id.exists' => 'Bahagian/Unit yang dipilih tidak sah.',
      'role_ids.*.exists' => 'Satu atau lebih peranan yang dipilih tidak sah.',
      // Add custom messages for other rules as needed
    ];
  }

  /**
   * Prepare the data for validation.
   * This method can be used to modify or add data to the request before validation occurs.
   * For updating users, hash the password only if a new one is provided.
   *
   * @return void
   */
  protected function prepareForValidation(): void
  {
    // Hash the password before validation, but only if it's present in the request.
    // This allows updating other fields without requiring a password change.
    if ($this->has('password') && $this->input('password') !== null) {
      $this->merge([
        'password' => Hash::make($this->input('password')),
      ]);
    } else {
      // If password is not provided or is null, remove it from the request
      // so it doesn't overwrite the existing password with null or an empty string.
      $this->request->remove('password');
    }

    // Ensure role_ids is an array even if null
    if (!is_array($this->input('role_ids'))) {
      $this->merge(['role_ids' => []]);
    }

    // If using separate name fields, merge them into a single 'name' if needed by the User model
    // if ($this->has('first_name') && $this->has('last_name')) {
    //      $this->merge(['name' => trim($this->input('first_name') . ' ' . $this->input('last_name'))]);
    // }
  }
}
