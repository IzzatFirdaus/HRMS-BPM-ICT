<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth; // Import Auth facade
use Illuminate\Validation\Rule; // Import Rule for validation

class UpdateEquipmentRequest extends FormRequest
{
  /**
   * Determine if the user is authorized to make this request.
   * This method uses the EquipmentPolicy to check authorization for updating the specific equipment.
   *
   * @return bool
   */
  public function authorize(): bool
  {
    // Retrieve the Equipment instance being updated.
    // Assuming the route parameter is named 'equipment' and uses route model binding.
    $equipment = $this->route('equipment');

    // Check if the authenticated user can update this specific Equipment record.
    // This delegates the authorization check to the EquipmentPolicy.
    // The policy's 'update' method will be called, receiving the authenticated user and the Equipment instance.
    return Auth::check() && Auth::user()->can('update', $equipment);
  }

  /**
   * Get the validation rules that apply to the request.
   * These rules validate the data submitted when updating an equipment asset in the inventory.
   * Fields are generally nullable to allow partial updates, but validation rules apply if data is present.
   *
   * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
   */
  public function rules(): array
  {
    // Retrieve the Equipment instance being updated for unique rule exclusion.
    // This is safe because authorize() would have already failed if the model wasn't found or authorized.
    $equipmentId = $this->route('equipment')->id;

    return [
      'asset_type' => ['nullable', 'string', 'max:255'], // e.g., 'Laptop', 'Projector', 'Camera'
      'brand' => ['nullable', 'string', 'max:255'],
      'model' => ['nullable', 'string', 'max:255'],
      // Serial number must be unique, but exclude the current equipment's serial number
      'serial_number' => ['nullable', 'string', 'max:255', Rule::unique('equipment', 'serial_number')->ignore($equipmentId)],
      // Tag ID must be unique if provided, exclude the current equipment's tag ID
      'tag_id' => ['nullable', 'string', 'max:255', Rule::unique('equipment', 'tag_id')->ignore($equipmentId)],
      'purchase_date' => ['nullable', 'date'], // Purchase date is optional but must be a valid date if provided
      'warranty_expiry_date' => ['nullable', 'date', 'after_or_equal:purchase_date'], // Warranty expiry is optional, must be a date, and after purchase date
      // Status is required if updating, and must be one of these predefined values
      'status' => ['nullable', 'string', Rule::in(['available', 'on_loan', 'under_maintenance', 'disposed'])],
      'current_location' => ['nullable', 'string', 'max:255'], // Physical location of the equipment
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
      'serial_number.unique' => 'Nombor siri ini sudah wujud dalam inventori.',
      'tag_id.unique' => 'ID tag ini sudah wujud dalam inventori.',
      'purchase_date.date' => 'Format tarikh pembelian tidak sah.',
      'warranty_expiry_date.date' => 'Format tarikh tamat waranti tidak sah.',
      'warranty_expiry_date.after_or_equal' => 'Tarikh tamat waranti mestilah pada atau selepas tarikh pembelian.',
      'status.in' => 'Status peralatan yang dipilih tidak sah.',
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
