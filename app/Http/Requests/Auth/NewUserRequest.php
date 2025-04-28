<?php

namespace App\Http\Requests; // Or App\Http\Requests\Auth if that's where you put it

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules; // Import for common password rules

// This Form Request is provided by Laravel Fortify or Breeze.
// It handles the validation of user registration data (name, email, password).

class NewUserRequest extends FormRequest
{
  /**
   * Determine if the user is authorized to make this request.
   *
   * For registration, we typically allow all guests to make this request
   * to submit their registration details.
   */
  public function authorize(): bool
  {
    // Allow all guests to make this request to attempt registration.
    return true;
  }

  /**
   * Get the validation rules that apply to the request.
   *
   * These rules define the validation requirements for the fields
   * submitted in the registration form.
   *
   * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
   */
  public function rules(): array
  {
    // Standard validation rules for user registration.
    // 'name' is required, string, max 255 characters.
    // 'email' is required, string, valid email format, max 255 characters, and must be unique in the 'users' table.
    // 'password' is required, string, meets minimum length (e.g., 8), must be confirmed (match 'password_confirmation'),
    // and meets default Laravel complexity rules (Rules\Password::defaults()).
    return [
      'name' => ['required', 'string', 'max:255'], // Validate the user's name
      'email' => ['required', 'string', 'email', 'max:255', 'unique:users'], // Validate the user's email, ensure uniqueness in the 'users' table
      'password' => ['required', 'string', 'min:8', 'confirmed', Rules\Password::defaults()], // Validate the user's password, ensure min length, confirmation, and complexity
      // If you have other fields in your registration form (e.g., department_id), add validation rules here.
      // 'department_id' => ['required', 'integer', 'exists:departments,id'],
    ];
  }

  /**
   * Get the error messages for the defined validation rules. (Optional)
   *
   * You can uncomment and customize these messages if needed.
   *
   * @return array<string, string>
   */
  // public function messages(): array
  // {
  //     return [
  //         'name.required' => 'The name field is required.',
  //         // Add messages for other rules/fields
  //     ];
  // }
}
