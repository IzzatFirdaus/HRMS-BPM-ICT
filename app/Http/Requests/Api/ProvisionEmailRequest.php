<?php

namespace App\Http\Requests\Api; // Adjust namespace based on your project structure

use Illuminate\Foundation\Http\FormRequest;

class ProvisionEmailRequest extends FormRequest
{
  /**
   * Determine if the user is authorized to make this request.
   */
  public function authorize(): bool
  {
    // Determine authorization logic.
    // For an API endpoint, this might involve checking API tokens,
    // user permissions based on the authenticated user (if any),
    // or other custom checks depending on your security middleware.
    // If middleware handles authorization (like 'auth:api'), you might return true here.
    // For a simple API key check via middleware, `true` might be acceptable here,
    // assuming the middleware runs before the request gets here.
    return true; // Adjust based on your specific authorization needs
  }

  /**
   * Get the validation rules that apply to the request.
   *
   * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
   */
  public function rules(): array
  {
    return [
      'application_id' => 'required|integer|exists:email_applications,id',
      // Add any other validation rules for data needed for provisioning,
      // e.g., if the final assigned email/user ID is sent in the request payload.
      // 'final_email' => 'required_without:final_user_id|email|max:255',
      // 'final_user_id' => 'required_without:final_email|integer|exists:users,id',
    ];
  }

  /**
   * Get the error messages for the defined validation rules. (Optional)
   *
   * @return array<string, string>
   */
  // public function messages(): array
  // {
  //     return [
  //         'application_id.required' => 'The application ID is required.',
  //         'application_id.integer' => 'The application ID must be an integer.',
  //         'application_id.exists' => 'The selected application ID is invalid.',
  //         // Add custom messages for other fields
  //     ];
  // }
}
