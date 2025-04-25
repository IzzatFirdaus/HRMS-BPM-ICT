<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth; // Import Auth facade
use Illuminate\Validation\Rule; // Import Rule for validation

class StoreLoanApplicationRequest extends FormRequest
{
  /**
   * Determine if the user is authorized to make this request.
   * This method uses the LoanApplicationPolicy to check authorization.
   *
   * @return bool
   */
  public function authorize(): bool
  {
    // Check if the authenticated user can create a LoanApplication.
    // This delegates the authorization check to the LoanApplicationPolicy.
    // The policy's 'create' method will be called, receiving the authenticated user.
    return Auth::check() && Auth::user()->can('create', \App\Models\LoanApplication::class);
  }

  /**
   * Get the validation rules that apply to the request.
   * These rules validate the data submitted from the loan application form (Parts 1-4).
   *
   * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
   */
  public function rules(): array
  {
    return [
      // Part 1: Loan Details
      'purpose' => ['required', 'string', 'max:500'], // Tujuan Permohonan
      'location' => ['required', 'string', 'max:255'], // Lokasi
      'loan_start_date' => ['required', 'date', 'after_or_equal:today'], // Tarikh Pinjaman
      'loan_end_date' => ['required', 'date', 'after_or_equal:loan_start_date'], // Tarikh Dijangka Pulang

      // Part 2: Responsible Officer (Conditional)
      // 'is_applicant_responsible' is a boolean checkbox handled by the Livewire component state.
      // The 'responsible_officer_id' is required IF the applicant is NOT responsible.
      // The Livewire component's submit method should handle this conditional requirement based on its state.
      // However, we can add a basic validation rule here that is nullable by default.
      'responsible_officer_id' => ['nullable', 'exists:users,id'],

      // Part 3: Equipment Items (Array of items)
      'items' => ['required', 'array', 'min:1'], // Must have at least one item row
      'items.*.equipment_type' => ['required', 'string', 'max:255'], // Jenis Peralatan for each item
      'items.*.quantity_requested' => ['required', 'integer', 'min:1'], // Kuantiti for each item, must be at least 1
      'items.*.notes' => ['nullable', 'string', 'max:500'], // Catatan for each item

      // Part 4: Applicant Confirmation
      // This checkbox confirms the applicant accepts responsibility.
      'applicant_confirmation' => ['required', 'accepted'], // Requires the checkbox to be present and its value to be '1'

      // Note: Applicant's personal details (Nama Penuh, Jawatan & Gred, Bahagian/Unit, No.Telefon)
      // are assumed to be fetched from the authenticated User model and not submitted via this form request,
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
      'purpose.required' => 'Tujuan Permohonan wajib diisi.',
      'location.required' => 'Lokasi wajib diisi.',
      'loan_start_date.required' => 'Tarikh Pinjaman wajib diisi.',
      'loan_start_date.date' => 'Format Tarikh Pinjaman tidak sah.',
      'loan_start_date.after_or_equal' => 'Tarikh Pinjaman mestilah pada atau selepas hari ini.',
      'loan_end_date.required' => 'Tarikh Dijangka Pulang wajib diisi.',
      'loan_end_date.date' => 'Format Tarikh Dijangka Pulang tidak sah.',
      'loan_end_date.after_or_equal' => 'Tarikh Dijangka Pulang mestilah pada atau selepas Tarikh Pinjaman.',
      'responsible_officer_id.exists' => 'Pegawai Bertanggungjawab yang dipilih tidak sah.',
      'items.required' => 'Sila tambah sekurang-kurangnya satu item peralatan.',
      'items.array' => 'Format item peralatan tidak sah.',
      'items.min' => 'Sila tambah sekurang-kurangnya satu item peralatan.',
      'items.*.equipment_type.required' => 'Jenis Peralatan wajib diisi untuk setiap item.',
      'items.*.quantity_requested.required' => 'Kuantiti wajib diisi untuk setiap item.',
      'items.*.quantity_requested.integer' => 'Kuantiti mestilah nombor bulat.',
      'items.*.quantity_requested.min' => 'Kuantiti mestilah sekurang-kurangnya 1 untuk setiap item.',
      'applicant_confirmation.required' => 'Anda mesti mengesahkan perakuan pemohon.',
      'applicant_confirmation.accepted' => 'Anda mesti bersetuju dengan perakuan pemohon.',
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
    // Example: Handle the conditional requirement for responsible_officer_id
    // If 'is_applicant_responsible' is true (or '1'), set 'responsible_officer_id' to null
    // This assumes the Livewire component sends 'is_applicant_responsible' as a boolean or string '1'/'0'
    // The 'required_if' rule in the Livewire component's submit method is often a cleaner way to handle this.
    // If you rely solely on this FormRequest, you might need a more complex 'sometimes' or 'required_if' rule here.

    // For now, assuming Livewire handles the conditional requirement before calling this FormRequest's validation on submit.
    // This FormRequest validates the basic structure and types.
  }
}
