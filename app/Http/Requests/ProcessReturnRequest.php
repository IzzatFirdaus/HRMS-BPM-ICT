<?php

namespace App\Http\Requests; // Ensure this namespace is correct

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth; // Import Auth facade
use Illuminate\Validation\Rule; // Import Rule if needed for unique, exists, in

class ProcessReturnRequest extends FormRequest
{
  /**
   * Determine if the user is authorized to make this request.
   *
   * @return bool
   */
  public function authorize(): bool
  {
    // You might need to check if the authenticated user has the 'process return' permission
    // or the 'bpm_staff' role.
    // Example assuming Spatie/Laravel-Permission:
    // return Auth::check() && Auth::user()->can('process return');

    // For now, we'll return true, but ensure proper authorization is handled either here
    // or by policies/middleware applied to the controller method.
    return true;
  }

  /**
   * Get the validation rules that apply to the request.
   *
   * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
   */
  public function rules(): array
  {
    return [
      // Validate the ID of the user returning the equipment
      // This user might be the original applicant or someone else returning on their behalf
      'returning_user_id' => ['required', 'integer', Rule::exists('users', 'id')],
      // Validate optional fields from the return form
      'accessories_on_return' => ['nullable', 'string', 'max:500'], // Notes about accessories on return
      'return_notes' => ['nullable', 'string'], // General return notes
      // Validate the condition of the equipment on return
      'equipment_condition' => ['required', Rule::in(['good', 'damaged', 'needs_repair'])], // Match enum/definition

      // The return accepting officer ID is typically the authenticated user,
      // so it might not be needed in the form/validation, but included here if collected.
      // 'return_accepting_user_id' => ['required', 'integer', Rule::exists('users', 'id')],
    ];
  }

  /**
   * Get custom validation messages.
   *
   * @return array<string, string>
   */
  public function messages(): array
  {
    return [
      'returning_user_id.required' => 'Pegawai Yang Memulangkan perlu dinyatakan.', // Malay message
      'returning_user_id.exists' => 'Pegawai Yang Memulangkan yang dipilih tidak sah.', // Malay message
      'equipment_condition.required' => 'Keadaan peralatan semasa pemulangan perlu dinyatakan.', // Malay message
      'equipment_condition.in' => 'Keadaan peralatan yang dinyatakan tidak sah.', // Malay message
      'accessories_on_return.max' => 'Maklumat aksesori semasa pemulangan tidak boleh melebihi :max aksara.', // Malay message
      // Add custom messages for other fields if needed
    ];
  }

  /**
   * Get custom attributes for validator errors.
   *
   * @return array<string, string>
   */
  public function attributes(): array
  {
    return [
      // Define custom attribute names if needed (e.g., 'returning_user_id' => 'Pegawai Yang Memulangkan')
    ];
  }
}
