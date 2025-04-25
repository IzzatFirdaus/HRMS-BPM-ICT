<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth; // Import Auth facade
use Illuminate\Validation\Rule; // Import Rule for validation
use Illuminate\Support\Facades\Hash; // Import Hash facade for password hashing

class StoreUserRequest extends FormRequest
{
  /**
   * Determine if the user is authorized to make this request.
   * This method uses the UserPolicy to check authorization for creating users.
   *
   * @return bool
   */
  public function authorize(): bool
  {
    // Check if the authenticated user can create a User record.
    // This delegates the authorization check to the UserPolicy.
    // The policy's 'create' method will be called, receiving the authenticated user.
    // Assumes you have a UserPolicy defined and registered.
    return Auth::check() && Auth::user()->can('create', \App\Models\User::class);
  }

  /**
   * Get the validation rules that apply to the request.
   * These rules validate the data submitted when creating a new user account.
   * Validation rules are based on typical user fields, including those implied by PDFs (IC No., Grade, Dept, Phone, Personal Email).
   *
   * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
   */
  public function rules(): array
  {
    return [
      'name' => ['required', 'string', 'max:255'], // Full name or primary name field
      // If using first_name, last_name:
      // 'first_name' => ['required', 'string', 'max:255'],
      // 'last_name' => ['required', 'string', 'max:255'],

      'email' => ['required', 'string', 'email', 'max:255', 'unique:users'], // Primary email (e.g., for login), must be unique
      'password' => ['required', 'string', 'min:8', 'confirmed'], // Password, min length 8, requires password_confirmation field

      'personal_email' => ['nullable', 'string', 'email', 'max:255', 'unique:users,personal_email'], // Optional personal email, must be unique if provided

      'ic_number' => ['required', 'string', 'max:20', 'unique:users,ic_number'], // IC Number, required, unique
      'grade_id' => ['required', 'integer', 'exists:grades,id'], // Link to Grade model, required, must exist
      'position' => ['required', 'string', 'max:255'], // User's job title/position
      'department_id' => ['required', 'integer', 'exists:departments,id'], // Link to Department model, required, must exist
      'phone_number' => ['required', 'string', 'max:20'], // Mobile phone number

      // If using a roles/permissions package and assigning roles during creation:
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
      'name.required' => 'Nama penuh wajib diisi.',
      'email.required' => 'Alamat e-mel wajib diisi.',
      'email.email' => 'Format alamat e-mel tidak sah.',
      'email.unique' => 'Alamat e-mel ini sudah digunakan.',
      'password.required' => 'Kata laluan wajib diisi.',
      'password.min' => 'Kata laluan mestilah sekurang-kurangnya 8 aksara.',
      'password.confirmed' => 'Pengesahan kata laluan tidak sepadan.',
      'personal_email.email' => 'Format e-mel peribadi tidak sah.',
      'personal_email.unique' => 'E-mel peribadi ini sudah digunakan.',
      'ic_number.required' => 'Nombor Kad Pengenalan wajib diisi.',
      'ic_number.unique' => 'Nombor Kad Pengenalan ini sudah wujud dalam sistem.',
      'grade_id.required' => 'Gred wajib dipilih.',
      'grade_id.exists' => 'Gred yang dipilih tidak sah.',
      'position.required' => 'Jawatan wajib diisi.',
      'department_id.required' => 'Bahagian/Unit wajib dipilih.',
      'department_id.exists' => 'Bahagian/Unit yang dipilih tidak sah.',
      'phone_number.required' => 'Nombor telefon wajib diisi.',
      'role_ids.*.exists' => 'Satu atau lebih peranan yang dipilih tidak sah.',
      // Add custom messages for other rules as needed
    ];
  }

  /**
   * Prepare the data for validation.
   * This method can be used to modify or add data to the request before validation occurs.
   * For creating users, it's common practice to hash the password here.
   *
   * @return void
   */
  protected function prepareForValidation(): void
  {
    // Hash the password before validation, but only if it's present.
    // The 'required' rule will still enforce that it must be present on submit.
    if ($this->has('password')) {
      $this->merge([
        'password' => Hash::make($this->input('password')),
      ]);
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
