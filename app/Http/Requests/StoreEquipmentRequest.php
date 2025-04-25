<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth; // Import Auth facade
use Illuminate\Validation\Rule; // Import Rule for validation

class StoreEquipmentRequest extends FormRequest
{
  /**
   * Determine if the user is authorized to make this request.
   * This method uses the EquipmentPolicy to check authorization for creating equipment.
   *
   * @return bool
   */
  public function authorize(): bool
  {
    // Check if the authenticated user can create an Equipment record.
    // This delegates the authorization check to the EquipmentPolicy.
    // The policy's 'create' method will be called, receiving the authenticated user.
    return Auth::check() && Auth::user()->can('create', \App\Models\Equipment::class);
  }

  /**
   * Get the validation rules that apply to the request.
   * These rules validate the data submitted when adding a new equipment asset to the inventory.
   *
   * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
   */
  public function rules(): array
  {
    return [
      'asset_type' => ['required', 'string', 'max:255'], // e.g., 'Laptop', 'Projector', 'Camera'
      'brand' => ['required', 'string', 'max:255'],
      'model' => ['required', 'string', 'max:255'],
      'serial_number' => ['required', 'string', 'max:255', 'unique:equipment,serial_number'], // Serial number should be unique
      'tag_id' => ['nullable', 'string', 'max:255', 'unique:equipment,tag_id'], // Tag ID is optional but should be unique if provided
      'purchase_date' => ['nullable', 'date'], // Purchase date is optional but must be a valid date if provided
      'warranty_expiry_date' => ['nullable', 'date', 'after_or_equal:purchase_date'], // Warranty expiry is optional, must be a date, and after purchase date
      'status' => ['required', 'string', Rule::in(['available', 'on_loan', 'under_maintenance', 'disposed'])], // Status is required and must be one of these predefined values
      'current_location' => ['required', 'string', 'max:255'], // Physical location of the equipment
      'notes' => ['nullable', 'string', 'max:1000'], // Optional notes about the equipment
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
      'asset_type.required' => 'Jenis aset wajib diisi.',
      'brand.required' => 'Jenama wajib diisi.',
      'model.required' => 'Model wajib diisi.',
      'serial_number.required' => 'Nombor siri wajib diisi.',
      'serial_number.unique' => 'Nombor siri ini sudah wujud dalam inventori.',
      'tag_id.unique' => 'ID tag ini sudah wujud dalam inventori.',
      'purchase_date.date' => 'Format tarikh pembelian tidak sah.',
      'warranty_expiry_date.date' => 'Format tarikh tamat waranti tidak sah.',
      'warranty_expiry_date.after_or_equal' => 'Tarikh tamat waranti mestilah pada atau selepas tarikh pembelian.',
      'status.required' => 'Status peralatan wajib diisi.',
      'status.in' => 'Status peralatan yang dipilih tidak sah.',
      'current_location.required' => 'Lokasi semasa wajib diisi.',
      // Add custom messages for other rules as needed
    ];
  }

  /**
   * Prepare the data for validation.
   * You can modify or add data to the request before validation occurs.
   *
   * @return void
   */
  protected function prepareForValidation(): void
  {
    // Example: Sanitize or format data before validation
    // $this->merge([
    //     'serial_number' => strtoupper($this->input('serial_number')),
    // ]);
  }
}
