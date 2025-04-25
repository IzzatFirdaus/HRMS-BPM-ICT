<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth; // Import Auth facade
use Illuminate\Validation\Rule; // Import Rule for validation

class UpdateEmailApplicationRequest extends FormRequest
{
  /**
   * Determine if the user is authorized to make this request.
   * This method uses the EmailApplicationPolicy to check authorization for updating the specific application.
   *
   * @return bool
   */
  public function authorize(): bool
  {
    // Retrieve the EmailApplication instance being updated.
    // Assuming the route parameter is named 'emailApplication' and uses route model binding.
    $emailApplication = $this->route('emailApplication');

    // Check if the authenticated user can update this specific EmailApplication.
    // This delegates the authorization check to the EmailApplicationPolicy.
    // The policy's 'update' method will be called, receiving the authenticated user and the EmailApplication instance.
    return Auth::check() && Auth::user()->can('update', $emailApplication);
  }

  /**
   * Get the validation rules that apply to the request.
   * These rules validate the data submitted when updating an email application.
   * Note: These rules are typically for updating a *draft* application,
   * so some fields might be nullable compared to the store request.
   * The Livewire component's 'submitApplication' method should apply stricter rules for final submission.
   *
   * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
   */
  public function rules(): array
  {
    return [
      // 'service_status' (Taraf Perkhidmatan) - Optional for draft updates, but must be one of allowed types if present
      'service_status' => ['nullable', 'string', Rule::in(['Kakitangan Tetap', 'Lantikan Kontrak', 'Personel MySTEP', 'Pelajar Latihan Industri', 'E-mel Sandaran MOTAC'])], // Adjust allowed types

      // 'purpose' (Tujuan/Catatan) - Optional string, max length
      'purpose' => ['nullable', 'string', 'max:500'],

      // 'proposed_email' (Cadangan E-mel/ID) - Optional string, max length, valid email format if present
      'proposed_email' => ['nullable', 'string', 'max:255', 'email'],

      // 'group_email' (Nama Group Email) - Optional string, max length
      'group_email' => ['nullable', 'string', 'max:255'],

      // 'group_admin_name' (Nama Admin/EO/CC) - Optional string, max length
      'group_admin_name' => ['nullable', 'string', 'max:255'],

      // 'group_admin_email' (E-mel Admin/EO/CC) - Optional string, max length, valid email format if present
      'group_admin_email' => ['nullable', 'string', 'max:255', 'email'],

      // 'certification_accepted' (Pengesahan Pemohon checkbox) - Optional for draft updates, but must be 'accepted' if present
      // The Livewire component's 'submitApplication' method will make this required and 'accepted' for final submission.
      'certification_accepted' => ['nullable', 'accepted'],

      // Note: Applicant's personal details are not updated via this form.
      // Status and rejection_reason are typically managed by the workflow/services, not directly updated by the user form.
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
      'service_status.in' => 'Taraf Perkhidmatan yang dipilih tidak sah.',
      'proposed_email.email' => 'Format Cadangan E-mel/ID tidak sah.',
      'group_admin_email.email' => 'Format E-mel Admin/EO/CC tidak sah.',
      'certification_accepted.accepted' => 'Anda mesti bersetuju dengan semua perakuan pemohon.',
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
    // Example: Convert boolean checkbox value to string '1' or '0' if needed by the 'accepted' rule
    // If your checkbox input sends 'true'/'false' or 1/0 directly, this might not be necessary.
    // $this->merge([
    //     'certification_accepted' => $this->boolean('certification_accepted') ? '1' : '0',
    // ]);
  }
}
