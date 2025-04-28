{{-- resources\views\_partials\_modals\modal-employee.blade.php --}}

{{-- Push custom CSS (e.g., to hide number input spin buttons) --}}
@push('custom-css')
    <style>
        /* CSS to hide the default spin buttons on number input fields */
        input::-webkit-outer-spin-button,
        input::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
            /* Removes default margin */
        }

        /* Firefox specific rule */
        input[type="number"] {
            -moz-appearance: textfield;
        }
    </style>
    {{-- Add CSS for Select2, Flatpickr, etc., if you add fields that use them --}}
    {{-- <link rel="stylesheet" href="{{asset('assets/vendor/libs/select2/select2.css')}}" /> --}}
    {{-- <link rel="stylesheet" href="{{asset('assets/vendor/libs/flatpickr/flatpickr.css')}}" /> --}}
@endpush

{{--
    Bootstrap Modal Structure for Employee Management
    wire:ignore.self: Essential for Livewire to leave Bootstrap's modal handling to Bootstrap JS.
    id: The ID used by Bootstrap to target the modal.
    tabindex="-1": Makes the modal accessible.
    aria-hidden="true": Hides the modal from assistive technologies when closed.
    modal-xl: Makes the modal extra large.
--}}
<div wire:ignore.self class="modal fade" id="employeeModal" tabindex="-1" aria-hidden="true">
    {{-- Modal Dialog (modal-xl makes it wider) --}}
    <div class="modal-dialog modal-xl modal-simple">
        {{-- Modal Content --}}
        <div class="modal-content p-0 p-md-5">
            {{-- Modal Body --}}
            <div class="modal-body">
                {{-- Close Button --}}
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>

                {{-- Modal Header/Title Section --}}
                <div class="text-center mb-4">
                    {{-- Dynamic Title based on $isEdit Livewire property --}}
                    <h3 class="mb-2">{{ $isEdit ? __('Update Employee') : __('New Employee') }}</h3>
                    <p class="text-muted">{{ __('Please fill out the following information') }}</p>
                </div>

                {{--
                    Form for Employee Submission
                    wire:submit.prevent="submitEmployee": Calls the 'submitEmployee' method on the Livewire component when the form is submitted.
                                                     .prevent is crucial to stop default browser form submission.
                    row g-3: Bootstrap grid classes for form layout.
                --}}
                <form wire:submit.prevent="submitEmployee" class="row g-3"> {{-- Added .prevent to wire:submit --}}

                    {{-- Employee ID (Number Input) --}}
                    <div class="col-md-3 col-12 mb-4">
                        <label class="form-label" for="employeeId">{{ __('ID') }}</label> {{-- Added 'for' attribute --}}
                        {{--
                            wire:model='employeeInfo.id': Binds input to the 'id' property within 'employeeInfo'.
                            @if ($isEdit) disabled @endif: Disables the input when in edit mode.
                            @error(...): Adds validation styling.
                        --}}
                        <input wire:model='employeeInfo.id' id="employeeId" {{-- Added ID matching the label's 'for' attribute --}}
                            @if ($isEdit) disabled @endif
                            class="form-control @error('employeeInfo.id') is-invalid @enderror" type="number"
                            placeholder="{{ __('e.g., 12345') }}" {{-- Added placeholder --}} />
                        {{-- Display Validation Error Message for ID --}}
                        @error('employeeInfo.id')
                            <div class="invalid-feedback"> {{-- Using invalid-feedback for consistency --}}
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    {{-- Contract ID (Select Dropdown) --}}
                    <div class="col-md-3 col-12 mb-4">
                        <label class="form-label w-100" for="employeeInfo.contractId">{{ __('Contract ID') }}</label>
                        {{-- Corrected 'for' attribute --}}
                        {{--
                             wire:model.defer="employeeInfo.contractId": Binds select value, deferring updates until an action occurs.
                             @error(...): Adds validation styling.
                         --}}
                        <select wire:model.defer="employeeInfo.contractId"
                            class="form-select @error('employeeInfo.contractId') is-invalid @enderror"
                            id="employeeInfo.contractId">
                            <option value="">{{ __('Select Contract') }}</option> {{-- Added placeholder option text --}}
                            {{-- Loop through $contracts collection (assumed to be available in Livewire component) --}}
                            @foreach ($contracts as $contract)
                                <option value="{{ $contract->id }}">{{ $contract->name }}</option>
                            @endforeach
                        </select>
                        {{-- Display Validation Error Message for Contract ID --}}
                        @error('employeeInfo.contractId')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    {{-- National Number (Text Input) --}}
                    <div class="col-md-3 col-12 mb-4">
                        <label class="form-label w-100" for="nationalNumber">{{ __('National Number') }}</label>
                        {{-- Corrected ID match --}}
                        <input wire:model.defer="employeeInfo.nationalNumber"
                            class="form-control @error('employeeInfo.nationalNumber') is-invalid @enderror"
                            id="nationalNumber" placeholder="02000000000" type="text" maxlength="11">
                        {{-- Error display already present --}}
                        @error('employeeInfo.nationalNumber')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    {{-- Mobile Number (Text Input) --}}
                    <div class="col-md-3 col-12 mb-4">
                        <label class="form-label w-100" for="mobileNumber">{{ __('Mobile') }}</label>
                        {{-- Corrected ID match --}}
                        <input wire:model.defer="employeeInfo.mobileNumber"
                            class="form-control @error('employeeInfo.mobileNumber') is-invalid @enderror"
                            id="mobileNumber" placeholder="900000000" type="text" maxlength="9">
                        {{-- Error display already present --}}
                        @error('employeeInfo.mobileNumber')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    {{-- First Name (Text Input) --}}
                    <div class="col-md-2 col-12 mb-4">
                        <label class="form-label w-100" for="firstName">{{ __('First Name') }}</label>
                        {{-- Added 'for' attribute --}}
                        <input wire:model='employeeInfo.firstName' id="firstName" {{-- Added ID --}}
                            class="form-control @error('employeeInfo.firstName') is-invalid @enderror" type="text"
                            placeholder="{{ __('Enter first name') }}" {{-- Added placeholder --}} />
                        {{-- Display Validation Error Message for First Name --}}
                        @error('employeeInfo.firstName')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    {{-- Father Name (Text Input) --}}
                    <div class="col-md-2 col-12 mb-4">
                        <label class="form-label w-100" for="fatherName">{{ __('Father Name') }}</label>
                        {{-- Added 'for' attribute --}}
                        <input wire:model='employeeInfo.fatherName' id="fatherName" {{-- Added ID --}}
                            class="form-control @error('employeeInfo.fatherName') is-invalid @enderror" type="text"
                            placeholder="{{ __('Enter father name') }}" {{-- Added placeholder --}} />
                        {{-- Display Validation Error Message for Father Name --}}
                        @error('employeeInfo.fatherName')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    {{-- Last Name (Text Input) --}}
                    <div class="col-md-2 col-12 mb-4">
                        <label class="form-label w-100" for="lastName">{{ __('Last Name') }}</label>
                        {{-- Added 'for' attribute --}}
                        <input wire:model='employeeInfo.lastName' id="lastName" {{-- Added ID --}}
                            class="form-control @error('employeeInfo.lastName') is-invalid @enderror" type="text"
                            placeholder="{{ __('Enter last name') }}" {{-- Added placeholder --}} />
                        {{-- Display Validation Error Message for Last Name --}}
                        @error('employeeInfo.lastName')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    {{-- Mother Name (Text Input) --}}
                    <div class="col-md-2 col-12 mb-4">
                        <label class="form-label w-100" for="motherName">{{ __('Mother Name') }}</label>
                        {{-- Added 'for' attribute --}}
                        <input wire:model='employeeInfo.motherName' id="motherName" {{-- Added ID --}}
                            class="form-control @error('employeeInfo.motherName') is-invalid @enderror" type="text"
                            placeholder="{{ __('Enter mother name') }}" {{-- Added placeholder --}} />
                        {{-- Display Validation Error Message for Mother Name --}}
                        @error('employeeInfo.motherName')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    {{-- Birth & Place (Text Input) --}}
                    <div class="col-md-4 col-12 mb-4">
                        <label class="form-label w-100" for="birthAndPlace">{{ __('Birth & Place') }}</label>
                        <input wire:model.defer="employeeInfo.birthAndPlace" type="text" id="birthAndPlace"
                            class="form-control @error('employeeInfo.birthAndPlace') is-invalid @enderror"
                            placeholder="{{ __('e.g., 1990-01-01, Cairo') }}" {{-- Added placeholder --}} />
                        {{-- Display Validation Error Message for Birth & Place --}}
                        @error('employeeInfo.birthAndPlace')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                        {{-- TODO: Consider using a Datepicker here (Flatpickr) --}}
                    </div>

                    {{-- Gender (Select Dropdown) --}}
                    <div class="col-md-2 col-12 mb-4">
                        <label class="form-label w-100" for="gender">{{ __('Gender') }}</label>
                        {{-- Corrected ID match --}}
                        <select wire:model.defer="employeeInfo.gender"
                            @error('employeeInfo.gender') is-invalid @enderror id="gender" class="form-select">
                            <option value="">{{ __('Select Gender') }}</option> {{-- Added placeholder option text --}}
                            <option value="1">{{ __('Male') }}</option>
                            <option value="0">{{ __('Female') }}</option>
                        </select>
                        {{-- Display Validation Error Message for Gender --}}
                        @error('employeeInfo.gender')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    {{-- Degree (Text Input) --}}
                    <div class="col-md-4 col-12 mb-4">
                        <label class="form-label w-100" for="degree">{{ __('Degree') }}</label>
                        {{-- Corrected ID match --}}
                        <input wire:model.defer="employeeInfo.degree" type="text" id="degree"
                            class="form-control @error('employeeInfo.degree') is-invalid @enderror"
                            placeholder="{{ __('e.g., Bachelor\'s Degree') }}" {{-- Added placeholder --}} />
                        {{-- Display Validation Error Message for Degree --}}
                        @error('employeeInfo.degree')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    {{-- Address (Text Input) --}}
                    <div class="col-md-6 col-12 mb-4">
                        <label class="form-label w-100" for="address">{{ __('Address') }}</label>
                        {{-- Corrected ID match --}}
                        <input wire:model.defer="employeeInfo.address" type="text" id="address"
                            {{-- Added ID --}}
                            class="form-control @error('employeeInfo.address') is-invalid @enderror"
                            placeholder="{{ __('Enter address') }}" {{-- Added placeholder --}} />
                        {{-- Display Validation Error Message for Address --}}
                        @error('employeeInfo.address')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    {{-- Note (Text Input) --}}
                    <div class="col-12 mb-4">
                        <label class="form-label w-100" for="employeeNotes">{{ __('Note') }}</label>
                        {{-- Added 'for' attribute --}}
                        <input wire:model='employeeInfo.notes' id="employeeNotes" {{-- Added ID --}}
                            class="form-control @error('employeeInfo.notes') is-invalid @enderror" type="text"
                            placeholder="{{ __('Add any relevant notes') }}" {{-- Added placeholder --}} />
                        {{-- Display Validation Error Message for Note --}}
                        @error('employeeInfo.notes')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    {{-- TODO: Add fields for relationships if needed (e.g., Department, Position, Center, Grade) --}}
                    {{-- These might use Select2 and require JS integration similar to the Center modal Weekends field --}}
                    {{-- Example Select2 for Department:
                    <div wire:ignore class="col-md-6 col-12 mb-4">
                         <label class="form-label" for="employeeDepartment">{{ __('Department') }}</label>
                         <select wire:model='employeeInfo.department_id' id="employeeDepartment" class="select2 form-select @error('employeeInfo.department_id') is-invalid @enderror">
                             <option value="">{{ __('Select Department') }}</option>
                             @foreach ($departments as $department) // Assuming $departments is available
                                 <option value="{{ $department->id }}">{{ $department->name }}</option>
                             @endforeach
                         </select>
                         @error('employeeInfo.department_id')
                             <div class="invalid-feedback">{{ $message }}</div>
                         @enderror
                    </div>
                    --}}

                    {{-- TODO: Add Datepickers for dates like Birth Date, Hire Date, etc. --}}
                    {{-- These would use Flatpickr and require JS integration similar to the Center modal Work Hours fields --}}
                    {{-- Example Flatpickr for Birth Date:
                     <div class="col-md-4 col-12 mb-4">
                         <label class="form-label" for="birthDate">{{ __('Birth Date') }}</label>
                         <input wire:model='employeeInfo.birth_date'
                                type="text"
                                id="birthDate"
                                class="form-control flatpickr @error('employeeInfo.birth_date') is-invalid @enderror"
                                placeholder="YYYY-MM-DD"
                                autocomplete="off"
                                />
                         @error('employeeInfo.birth_date')
                             <div class="invalid-feedback">{{ $message }}</div>
                         @enderror
                     </div>
                     --}}


                    {{-- Submit and Cancel Buttons --}}
                    <div class="col-12 text-center">
                        {{-- Submit Button --}}
                        <button type="submit" class="btn btn-primary me-sm-3 me-1">
                            {{-- Display different text while submitting --}}
                            <span wire:loading.remove wire:target="submitEmployee">{{ __('Submit') }}</span>
                            {{-- Optional spinner while submitting --}}
                            <span wire:loading wire:target="submitEmployee" class="spinner-border spinner-border-sm"
                                role="status" aria-hidden="true"></span>
                        </button>
                        {{-- Cancel/Reset Button --}}
                        <button type="reset" class="btn btn-label-secondary btn-reset" data-bs-dismiss="modal"
                            aria-label="Close">
                            {{ __('Cancel') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Push custom JavaScript for modal behavior and external library initialization --}}
@push('custom-scripts')
    {{-- Add JS for Select2, Flatpickr, etc., if you added fields that use them --}}
    {{-- <script src="{{asset('assets/vendor/libs/select2/select2.js')}}"></script> --}}
    {{-- <script src="{{asset('assets/vendor/libs/flatpickr/flatpickr.js')}}"></script> --}}
    {{-- Add other necessary library scripts here --}}

    <script>
        'use strict';

        // Function to initialize components that need JS (like Select2, Flatpickr) inside the modal
        // Call this when the modal HTML is loaded or shown.
        function initializeEmployeeModalComponents() {
            console.log('Initializing employee modal components...');

            // TODO: Add initialization logic for Select2 fields here if you added them.
            // Remember to destroy existing instances before re-initializing.
            // Remember to bind 'change' events to @this.set() for Select2 fields with wire:ignore.
            // Example for a Select2 field with ID 'employeeDepartment':
            // const employeeDepartmentSelect = $('#employeeDepartment');
            // if (employeeDepartmentSelect.length) {
            //      if (employeeDepartmentSelect.data('select2')) { employeeDepartmentSelect.select2('destroy'); }
            //      employeeDepartmentSelect.select2({ placeholder: '{{ __('Select Department') }}', dropdownParent: employeeDepartmentSelect.parent() });
            //      employeeDepartmentSelect.on('change', function() { @this.set('employeeInfo.department_id', $(this).val()); });
            // }


            // TODO: Add initialization logic for Flatpickr date/time picker fields here if you added them.
            // Remember to destroy existing instances.
            // Remember to use 'onClose' or 'onChange' event to @this.set() for Flatpickr fields.
            // Example for a Flatpickr date picker field with ID 'birthDate':
            // const birthDateInput = document.querySelector('#birthDate');
            // if (birthDateInput) {
            //      if (birthDateInput._flatpickr) { birthDateInput._flatpickr.destroy(); }
            //      birthDateInput.flatpickr({
            //           dateFormat: "Y-m-d", // Adjust format as needed
            //           onClose: function(selectedDates, dateStr, instance) {
            //               @this.set('employeeInfo.birth_date', dateStr);
            //           }
            //      });
            // }


            console.log('Employee modal components initialization complete.');
        }


        // Example: Clear Livewire properties when the modal is hidden (closed)
        // This helps reset the form state for the next time the modal is opened (for create or edit).
        const employeeModalElement = document.getElementById('employeeModal');
        if (employeeModalElement) {
            // Add event listener to initialize JS components when the modal is shown
            employeeModalElement.addEventListener('shown.bs.modal', function(event) {
                console.log('Employee Modal shown event triggered, initializing components...');
                // Initialize components like Select2 and Flatpickr within the modal
                initializeEmployeeModalComponents();
                // Optional: Focus on the first input
                const firstInput = document.getElementById('employeeId'); // Or the first visible input
                if (firstInput) {
                    firstInput.focus();
                }
            });

            // Add event listener to clear Livewire properties when the modal is hidden (closed)
            employeeModalElement.addEventListener('hidden.bs.modal', function() {
                console.log('Employee Modal hidden, attempting to reset form...');
                // Call a public method in your Livewire component to reset properties and errors.
                // Make sure your Livewire component (e.g., Employees.php) has a method named 'resetForm'.
                @this.call('resetForm');
            });

        } else {
            console.error('Employee modal element with ID #employeeModal not found.');
        }
    </script>
@endpush
