<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth; // Import Auth facade
use Illuminate\Validation\Rule; // Import Rule for validation

class UpdateLoanApplicationRequest extends FormRequest
{
  /**
   * Determine if the user is authorized to make this request.
   * This method uses the LoanApplicationPolicy to check authorization for updating the specific application.
   *
   * @return bool
   */
  public function authorize(): bool
  {
    // Retrieve the LoanApplication instance being updated.
    // Assuming the route parameter is named 'loanApplication' and uses route model binding.
    $loanApplication = $this->route('loanApplication');

    // Check if the authenticated user can update this specific LoanApplication.
    // This delegates the authorization check to the LoanApplicationPolicy.
    // The policy's 'update' method will be called, receiving the authenticated user and the LoanApplication instance.
    return Auth::check() && Auth::user()->can('update', $loanApplication);
  }

  /**
   * Get the validation rules that apply to the request.
   * These rules validate the data submitted when updating a loan application.
   * Note: These rules are typically for updating a *draft* application,
   * so some fields might be nullable compared to the store request.
   * The Livewire component's 'submitApplication' method should apply stricter rules for final submission.
   *
   * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
   */
  public function rules(): array
  {
    return [
      // Part 1: Loan Details (Nullable for drafts)
      'purpose' => ['nullable', 'string', 'max:500'], // Tujuan Permohonan
      'location' => ['nullable', 'string', 'max:255'], // Lokasi
      // Dates can be nullable for drafts, but must be valid dates if present
      'loan_start_date' => ['nullable', 'date', 'after_or_equal:today'], // Tarikh Pinjaman
      'loan_end_date' => ['nullable', 'date', 'after_or_equal:loan_start_date'], // Tarikh Dijangka Pulang

      // Part 2: Responsible Officer (Conditional - handled by component's submit validation)
      // The 'responsible_officer_id' is required IF the applicant is NOT responsible.
      // For draft updates, it can be nullable here.
      'responsible_officer_id' => ['nullable', 'exists:users,id'],

      // Part 3: Equipment Items (Array of items)
      // Items array can be nullable or empty for drafts, but individual item fields might have rules if present.
      'items' => ['nullable', 'array'], // Array can be empty or null for draft
      // Rules for individual items if the array is not empty
      'items.*.equipment_type' => ['nullable', 'string', 'max:255'], // Jenis Peralatan for each item (nullable in draft)
      'items.*.quantity_requested' => ['nullable', 'integer', 'min:1'], // Kuantiti (nullable in draft, min 1 if type is present - handled by component)
      'items.*.notes' => ['nullable', 'string', 'max:500'], // Catatan for each item

      // Part 4: Applicant Confirmation
      // This checkbox is optional for draft updates.
      // The Livewire component's 'submitApplication' method will make this required and 'accepted' for final submission.
      'applicant_confirmation' => ['nullable', 'accepted'],

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
      'loan_start_date.date' => 'Format Tarikh Pinjaman tidak sah.',
      'loan_start_date.after_or_equal' => 'Tarikh Pinjaman mestilah pada atau selepas hari ini.',
      'loan_end_date.date' => 'Format Tarikh Dijangka Pulang tidak sah.',
      'loan_end_date.after_or_equal' => 'Tarikh Dijangka Pulang mestilah pada atau selepas Tarikh Pinjaman.',
      'responsible_officer_id.exists' => 'Pegawai Bertanggungjawab yang dipilih tidak sah.',
      'items.array' => 'Format item peralatan tidak sah.',
      'items.*.quantity_requested.integer' => 'Kuantiti mestilah nombor bulat.',
      'items.*.quantity_requested.min' => 'Kuantiti mestilah sekurang-kurangnya 1 untuk setiap item.', // This rule is less strict here, component enforces it on submit
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
    // Example: Convert boolean checkbox value to string '1' or '0' if needed by the 'accepted' rule
    // If your checkbox input sends 'true'/'false' or 1/0 directly, this might not be necessary.
    // $this->merge([
    //     'applicant_confirmation' => $this->boolean('applicant_confirmation') ? '1' : '0',
    // ]);

    // Ensure 'items' is an array even if null or empty in the request, to prevent errors in nested validation
    if (!is_array($this->input('items'))) {
      $this->merge(['items' => []]);
    }
  }
}
