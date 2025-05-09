<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth; // Import Auth facade
use Illuminate\Validation\Rule; // Import Rule for validation

class StoreEmailApplicationRequest extends FormRequest
{
  /**
   * Determine if the user is authorized to make this request.
   * This method uses the EmailApplicationPolicy to check authorization.
   *
   * @return bool
   */
  public function authorize(): bool
  {
    // Check if the authenticated user can create an EmailApplication.
    // This delegates the authorization check to the EmailApplicationPolicy.
    // The policy's 'create' method will be called, receiving the authenticated user.
    return Auth::check() && Auth::user()->can('create', \App\Models\EmailApplication::class);
  }

  /**
   * Get the validation rules that apply to the request.
   * These rules validate the data submitted from the email application form.
   *
   * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
   */
  public function rules(): array
  {
    return [
      // 'service_status' (Taraf Perkhidmatan) - Required, must be one of the allowed types
      'service_status' => ['required', 'string', Rule::in(['Kakitangan Tetap', 'Lantikan Kontrak', 'Personel MySTEP', 'Pelajar Latihan Industri', 'E-mel Sandaran MOTAC'])], // Adjust allowed types based on your actual options

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

      // 'certification_accepted' (Pengesahan Pemohon checkbox) - Required and must be accepted (value '1' when checked)
      // This field corresponds to the three checkboxes in the PDF (Page 1).
      // The Livewire component might handle combining these into a single boolean or require all three to be checked.
      // Assuming a single checkbox input representing acceptance of all terms.
      'certification_accepted' => ['required', 'accepted'], // Requires the checkbox to be present and its value to be '1'

      // Note: Applicant's personal details (Nama Penuh, IC, Gred, Jawatan, etc.) are assumed
      // to be fetched from the authenticated User model and not submitted via this form request,
      // so they are not validated here.
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
      'service_status.required' => 'Sila pilih Taraf Perkhidmatan.',
      'service_status.in' => 'Taraf Perkhidmatan yang dipilih tidak sah.',
      'proposed_email.email' => 'Format Cadangan E-mel/ID tidak sah.',
      'group_admin_email.email' => 'Format E-mel Admin/EO/CC tidak sah.',
      'certification_accepted.required' => 'Anda mesti mengesahkan perakuan pemohon.',
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
