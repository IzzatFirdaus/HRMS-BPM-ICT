<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Equipment; // Import the Equipment model to use static properties
use Illuminate\Support\Facades\Auth; // Import Auth if you plan to use it in authorize()

class StoreEquipmentRequest extends FormRequest
{
  /**
   * Determine if the user is authorized to make this request.
   *
   * @return bool
   */
  public function authorize(): bool
  {
    // Since authorization is handled by authorizeResource in the controller's constructor
    // which uses the EquipmentPolicy, you can typically return true here.
    // Alternatively, you could duplicate the policy check here:
    // return Auth::user()->can('create', Equipment::class);
    return true;
  }

  /**
   * Get the validation rules that apply to the request.
   *
   * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
   */
  public function rules(): array
  {
    // These rules were previously in the store() method of EquipmentController
    return [
      'tag_id' => 'required|string|max:50|unique:equipment,tag_id', // Tag ID should be unique
      // Validate against the defined lowercase enum values from the model static property
      'asset_type' => ['required', 'string', 'max:50', Rule::in(Equipment::$equipmentTypes)], // Use static property
      'brand' => 'nullable|string|max:100',
      'model' => 'nullable|string|max:100',
      // Serial number should be unique if provided
      'serial_number' => 'nullable|string|max:100|unique:equipment,serial_number',
      'description' => 'nullable|string',
      'purchase_date' => 'nullable|date',
      'warranty_expiry_date' => 'nullable|date|after_or_equal:purchase_date',
      // Validate against the defined lowercase enum values from the model static property
      'availability_status' => ['required', Rule::in(Equipment::$availabilityStatuses)], // Use static property
      'condition_status' => ['required', Rule::in(Equipment::$conditionStatuses)], // Use static property
      'notes' => 'nullable|string',
      'current_location' => 'nullable|string|max:255', // Validation based on model property
      'is_active' => 'boolean', // Validation based on model property
      'in_service' => 'boolean', // Validation based on model property
      'is_gpr' => 'boolean', // Validation based on model property
      'value' => 'nullable|numeric', // Validation for value
      'real_price' => 'nullable|numeric', // Validation for real_price
      'expected_price' => 'nullable|numeric', // Validation for expected_price
      'old_id' => 'nullable|string|max:255', // Validation for old_id
      'acquisition_date' => 'nullable|date', // Validation for acquisition_date
      'acquisition_type' => 'nullable|string|max:255', // Validation for acquisition_type
      'funded_by' => 'nullable|string|max:255', // Validation for funded_by

      // Add validation for relationships if setting them on creation form
      'department_id' => 'nullable|exists:departments,id',
      'center_id' => 'nullable|exists:centers,id',
      'assigned_to_employee_id' => 'nullable|exists:employees,id', // If assigning employee on creation
    ];
  }

  /**
   * Get the error messages for the defined validation rules.
   *
   * @return array<string, string>
   */
  // public function messages(): array
  // {
  //     return [
  //         'tag_id.required' => 'The asset tag ID is required.',
  //         'tag_id.unique' => 'This asset tag ID has already been taken.',
  //         // Define custom messages for other rules as needed
  //     ];
  // }
}
