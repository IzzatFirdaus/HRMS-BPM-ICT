<?php

namespace App\Http\Requests; // Ensure this namespace is correct

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth; // Import Auth facade
use Illuminate\Validation\Rule; // Import Rule if needed for unique, exists, in

class IssueEquipmentRequest extends FormRequest
{
  /**
   * Determine if the user is authorized to make this request.
   *
   * @return bool
   */
  public function authorize(): bool
  {
    // You might need to check if the authenticated user has the 'issue equipment' permission
    // or the 'bpm_staff' role.
    // Example assuming Spatie/Laravel-Permission:
    // return Auth::check() && Auth::user()->can('issue equipment');

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
      // Validate the ID of the equipment being issued
      'equipment_id' => ['required', 'integer', Rule::exists('equipment', 'id')],
      // Validate the ID of the user receiving the equipment (should be the applicant or another user)
      'receiving_user_id' => ['required', 'integer', Rule::exists('users', 'id')],
      // Validate optional fields from the issuance form
      'accessories' => ['nullable', 'string', 'max:500'], // Notes about accessories
      'notes' => ['nullable', 'string'], // General issuance notes
      // Add validation for any other fields related to the issuance form
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
      'equipment_id.required' => 'Sila pilih peralatan yang dikeluarkan.', // Malay message
      'equipment_id.exists' => 'Peralatan yang dipilih tidak sah.', // Malay message
      'receiving_user_id.required' => 'Pegawai Penerima perlu dinyatakan.', // Malay message
      'receiving_user_id.exists' => 'Pegawai Penerima yang dipilih tidak sah.', // Malay message
      // Add custom messages for other fields if needed
      'accessories.max' => 'Maklumat aksesori tidak boleh melebihi :max aksara.', // Malay message
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
      // Define custom attribute names if needed for messages (e.g., 'equipment_id' => 'Peralatan')
    ];
  }
}
