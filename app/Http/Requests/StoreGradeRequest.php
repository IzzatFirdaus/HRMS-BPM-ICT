<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth; // Import Auth facade
use Illuminate\Validation\Rule; // Import Rule for validation

class StoreGradeRequest extends FormRequest
{
  /**
   * Determine if the user is authorized to make this request.
   * This method uses the GradePolicy to check authorization for creating grades.
   *
   * @return bool
   */
  public function authorize(): bool
  {
    // Check if the authenticated user can create a Grade record.
    // This delegates the authorization check to the GradePolicy.
    // The policy's 'create' method will be called, receiving the authenticated user.
    // Assumes you have a GradePolicy defined and registered.
    return Auth::check() && Auth::user()->can('create', \App\Models\Grade::class);
  }

  /**
   * Get the validation rules that apply to the request.
   * These rules validate the data submitted when creating a new grade record.
   * Validation rules are based on the Grade model's fillable properties (name, level).
   *
   * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
   */
  public function rules(): array
  {
    return [
      'name' => ['required', 'string', 'max:255', 'unique:grades,name'], // Grade name, required, unique
      'level' => ['required', 'integer', 'min:1', 'unique:grades,level'], // Grade level (integer), required, unique, min 1
      // Add validation for any other fields in your grades table if necessary
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
      'name.required' => 'Nama gred wajib diisi.',
      'name.unique' => 'Nama gred ini sudah wujud.',
      'level.required' => 'Tahap gred wajib diisi.',
      'level.integer' => 'Tahap gred mestilah nombor bulat.',
      'level.min' => 'Tahap gred mestilah sekurang-kurangnya 1.',
      'level.unique' => 'Tahap gred ini sudah wujud.',
      // Add custom messages for other rules as needed
    ];
  }

  /**
   * Prepare the data for validation.
   * This method can be used to modify or add data to the request before validation occurs.
   *
   * @return void
   */
  protected function prepareForValidation(): void
  {
    // Example: Trim whitespace from string inputs
    // $this->merge([
    //     'name' => trim($this->input('name')),
    // ]);
  }
}
