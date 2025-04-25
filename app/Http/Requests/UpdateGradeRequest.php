<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth; // Import Auth facade
use Illuminate\Validation\Rule; // Import Rule for validation

class UpdateGradeRequest extends FormRequest
{
  /**
   * Determine if the user is authorized to make this request.
   * This method uses the GradePolicy to check authorization for updating the specific grade.
   *
   * @return bool
   */
  public function authorize(): bool
  {
    // Retrieve the Grade instance being updated.
    // Assuming the route parameter is named 'grade' and uses route model binding.
    $grade = $this->route('grade');

    // Check if the authenticated user can update this specific Grade record.
    // This delegates the authorization check to the GradePolicy.
    // The policy's 'update' method will be called, receiving the authenticated user and the Grade instance being updated.
    // Assumes you have a GradePolicy defined and registered.
    return Auth::check() && Auth::user()->can('update', $grade);
  }

  /**
   * Get the validation rules that apply to the request.
   * These rules validate the data submitted when updating an existing grade record.
   * Fields are generally nullable to allow partial updates, but validation rules apply if data is present.
   * Unique rules use 'ignore' to exclude the current grade's record.
   *
   * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
   */
  public function rules(): array
  {
    // Retrieve the Grade instance being updated for unique rule exclusion.
    // This is safe because authorize() would have already failed if the model wasn't found or authorized.
    $gradeId = $this->route('grade')->id;

    return [
      // Grade name, optional on update, must be unique if provided, but ignore the current grade's name
      'name' => ['nullable', 'string', 'max:255', Rule::unique('grades', 'name')->ignore($gradeId)],
      // Grade level (integer), optional on update, must be unique if provided, ignore current grade's level, min 1 if present
      'level' => ['nullable', 'integer', 'min:1', Rule::unique('grades', 'level')->ignore($gradeId)],
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
      'name.unique' => 'Nama gred ini sudah wujud.',
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
