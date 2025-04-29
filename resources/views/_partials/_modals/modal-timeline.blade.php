{{-- resources\views\_partials\_modals\modal-timeline.blade.php --}}

{{-- Push custom CSS for external libraries --}}
@push('custom-css')
    {{-- Link to Select2 CSS --}}
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/select2/select2.css') }}" />
    {{-- Link to Flatpickr CSS --}}
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/flatpickr/flatpickr.css') }}" />

    <style>
        /* Custom CSS to hide the default spin buttons on number input fields (if any are added later) */
        input::-webkit-outer-spin-button,
        input::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
            /* Removes default margin */
        }

        input[type="number"] {
            -moz-appearance: textfield;
        }

        /* Adjustments for Select2 in Bootstrap modals */
        .modal-body .select2-container {
            width: 100% !important;
            /* Ensure Select2 takes full width in modal */
        }

        .modal-body .select2-container--bootstrap5 .select2-dropdown {
            z-index: 2000;
            /* Ensure Select2 dropdown is above modal */
        }
    </style>
@endpush

{{--
    Bootstrap Modal Structure for Employee Timeline Management
    wire:ignore.self: Essential for Livewire to leave Bootstrap's modal handling to Bootstrap JS.
    id: The ID used by Bootstrap to target the modal.
    tabindex="-1": Makes the modal accessible.
    aria-hidden="true": Hides the modal from assistive technologies when closed.
    modal-xl: Makes the modal extra large.
--}}
<div wire:ignore.self class="modal fade" id="timelineModal" tabindex="-1" aria-hidden="true">
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
                    {{-- Dynamic Title (assuming timeline modal is always for 'New', adjust if edit is possible) --}}
                    <h3 class="mb-2">{{ __('New Timeline') }}</h3>
                    <p class="text-muted">{{ __('Please fill out the following information') }}</p>
                </div>

                {{--
                    Form for Timeline Submission
                    wire:submit.prevent="submitTimeline": Calls the 'submitTimeline' method on the Livewire component when the form is submitted.
                                                       .prevent is crucial to stop default browser form submission.
                    row g-3 mt-2: Bootstrap grid classes for form layout with top margin.
                --}}
                <form wire:submit.prevent="submitTimeline" class="row g-3 mt-2"> {{-- Added .prevent --}}

                    {{-- Center Select2 Field --}}
                    {{-- wire:ignore: Necessary for Select2 to function correctly with Livewire --}}
                    <div wire:ignore class="col-md-4 col-12 mb-4"> {{-- Added mb-4 for bottom margin --}}
                        <label class="form-label" for="selectCenters">{{ __('Center') }}</label> {{-- Added 'for' attribute --}}
                        {{--
                             wire:model="employeeTimelineInfo.centerId": Binds the select value to the Livewire property.
                                                                       Use .defer if you only need update on submit.
                             id="selectCenters": ID used by JavaScript to initialize Select2.
                             class="select2 form-select": Bootstrap 5 Select2 classes.
                             @error(...): Adds validation styling.
                         --}}
                        <select wire:model.defer="employeeTimelineInfo.centerId" {{-- Use .defer with wire:ignore and manual updates --}}
                            id="selectCenters"
                            class="select2 form-select @error('employeeTimelineInfo.centerId') is-invalid @enderror"
                            data-allow-clear="true"> {{-- Optional: allows clearing selection --}}
                            <option value="">{{ __('Select Center') }}</option> {{-- Added placeholder option --}}
                            {{-- Loop through centers collection (assumed available in Livewire component) --}}
                            @foreach ($centers as $Center)
                                <option value="{{ $Center->id }}"> {{ $Center->name }}</option>
                            @endforeach
                        </select>
                        {{-- 👇 ADDED: Display Validation Error Message for Center 👇 --}}
                        @error('employeeTimelineInfo.centerId')
                            <div class="invalid-feedback"> {{-- Using invalid-feedback for consistency --}}
                                {{ $message }}
                            </div>
                        @enderror
                        {{-- ☝️ END ADDED ☝️ --}}
                    </div>

                    {{-- Department Select2 Field --}}
                    <div wire:ignore class="col-md-4 col-12 mb-4"> {{-- Added mb-4 --}}
                        <label class="form-label" for="selectDepartment">{{ __('Department') }}</label>
                        {{-- Added 'for' attribute --}}
                        {{--
                             wire:model="employeeTimelineInfo.departmentId": Binds the select value.
                             id="selectDepartment": ID used by JavaScript.
                         --}}
                        <select wire:model.defer="employeeTimelineInfo.departmentId" {{-- Use .defer --}}
                            id="selectDepartment"
                            class="select2 form-select @error('employeeTimelineInfo.departmentId') is-invalid @enderror"
                            data-allow-clear="true">
                            <option value="">{{ __('Select Department') }}</option> {{-- Added placeholder option --}}
                            {{-- Loop through departments collection (assumed available and potentially filtered by Center) --}}
                            @foreach ($departments as $department)
                                <option value="{{ $department->id }}"> {{ $department->name }}</option>
                            @endforeach
                        </select>
                        {{-- 👇 ADDED: Display Validation Error Message for Department 👇 --}}
                        @error('employeeTimelineInfo.departmentId')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                        {{-- ☝️ END ADDED ☝️ --}}
                    </div>

                    {{-- Position Select2 Field --}}
                    <div wire:ignore class="col-md-4 col-12 mb-4"> {{-- Added mb-4 --}}
                        <label class="form-label" for="selectPosition">{{ __('Position') }}</label>
                        {{-- Added 'for' attribute --}}
                        {{--
                             wire:model="employeeTimelineInfo.positionId": Binds the select value.
                             id="selectPosition": ID used by JavaScript.
                         --}}
                        <select wire:model.defer="employeeTimelineInfo.positionId" {{-- Use .defer --}}
                            id="selectPosition"
                            class="select2 form-select @error('employeeTimelineInfo.positionId') is-invalid @enderror"
                            data-allow-clear="true">
                            <option value="">{{ __('Select Position') }}</option> {{-- Added placeholder option --}}
                            {{-- Loop through positions collection (assumed available and potentially filtered by Department) --}}
                            @foreach ($positions as $position)
                                <option value="{{ $position->id }}"> {{ $position->name }}</option>
                            @endforeach
                        </select>
                        {{-- 👇 ADDED: Display Validation Error Message for Position 👇 --}}
                        @error('employeeTimelineInfo.positionId')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                        {{-- ☝️ END ADDED ☝️ --}}
                    </div>

                    {{-- Start Date Flatpickr Field --}}
                    <div class="col-md-4 col-12 mb-4"> {{-- Added mb-4 --}}
                        <label class="form-label w-100" for="startDate">{{ __('Start Date') }}</label>
                        {{-- Added 'for' attribute --}}
                        {{--
                             wire:model="employeeTimelineInfo.startDate": Binds the input value.
                             id="startDate": ID used by JavaScript to initialize Flatpickr. (Corrected ID)
                             Flatpickr JS will be initialized on this input and update the model on close.
                         --}}
                        <input wire:model="employeeTimelineInfo.startDate" type="text" id="startDate"
                            {{-- Corrected ID --}}
                            class="form-control @error('employeeTimelineInfo.startDate') is-invalid @enderror"
                            placeholder="YYYY-MM-DD" {{-- Adjusted placeholder format --}} autocomplete="off" readonly="readonly"
                            {{-- Make input non-editable directly --}} />
                        {{-- 👇 ADDED: Display Validation Error Message for Start Date 👇 --}}
                        @error('employeeTimelineInfo.startDate')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                        {{-- ☝️ END ADDED ☝️ --}}
                    </div>

                    {{-- End Date Flatpickr Field --}}
                    <div class="col-md-4 col-12 mb-4"> {{-- Added mb-4 --}}
                        <label class="form-label w-100" for="endDate">{{ __('End Date') }}</label>
                        {{-- Added 'for' attribute --}}
                        {{--
                             wire:model="employeeTimelineInfo.endDate": Binds the input value.
                             id="endDate": ID used by JavaScript to initialize Flatpickr. (Corrected ID)
                             Flatpickr JS will be initialized on this input and update the model on close.
                         --}}
                        <input wire:model="employeeTimelineInfo.endDate" type="text" id="endDate"
                            {{-- Corrected ID --}}
                            class="form-control @error('employeeTimelineInfo.endDate') is-invalid @enderror"
                            placeholder="YYYY-MM-DD" {{-- Adjusted placeholder format --}} autocomplete="off" readonly="readonly" />
                        {{-- 👇 ADDED: Display Validation Error Message for End Date 👇 --}}
                        @error('employeeTimelineInfo.endDate')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                        {{-- ☝️ END ADDED ☝️ --}}
                    </div>

                    {{-- Sequential Status (Standard Select Dropdown) --}}
                    <div class="col-md-4 col-12 mb-4"> {{-- Added mb-4 --}}
                        <label class="form-label" for="isSequent">{{ __('Sequential') }}</label> {{-- Added 'for' attribute --}}
                        {{-- wire:model="employeeTimelineInfo.isSequent": Binds the select value. --}}
                        <select wire:model="employeeTimelineInfo.isSequent" id="isSequent" {{-- Added ID matching 'for' --}}
                            class="form-select @error('employeeTimelineInfo.isSequent') is-invalid @enderror">
                            <option value="">{{ __('Select Sequential Status') }}</option> {{-- Added placeholder option --}}
                            <option value="1">{{ __('Sequent') }}</option>
                            <option value="0">{{ __('Non-Sequent') }}</option>
                        </select>
                        {{-- 👇 ADDED: Display Validation Error Message for Sequential 👇 --}}
                        @error('employeeTimelineInfo.isSequent')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                        {{-- ☝️ END ADDED ☝️ --}}
                    </div>

                    {{-- Note (Text Input) --}}
                    <div class="col-md-12 col-12 mb-4"> {{-- Corrected column size and added mb-4 --}}
                        <label class="form-label w-100" for="timelineNotes">{{ __('Note') }}</label>
                        {{-- Added 'for' attribute --}}
                        {{--
                             wire:model='employeeTimelineInfo.notes': Binds the input value to 'notes' property.
                             @error(...): Added validation styling.
                             Note: Check if the Livewire rule uses 'note' or 'notes'. Assuming 'notes' is correct.
                         --}}
                        <input wire:model='employeeTimelineInfo.notes' id="timelineNotes" {{-- Added ID matching 'for' --}}
                            class="form-control @error('employeeTimelineInfo.notes') is-invalid @enderror"
                            {{-- Check error key 'employeeTimelineInfo.notes' --}} type="text" placeholder="{{ __('Add any relevant notes') }}" />
                        {{-- 👇 ADDED: Display Validation Error Message for Note 👇 --}}
                        {{-- Using 'employeeTimelineInfo.notes' assuming this matches the @error key in the component --}}
                        @error('employeeTimelineInfo.notes')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                        {{-- ☝️ END ADDED ☝️ --}}
                    </div>


                    {{-- Submit and Cancel Buttons --}}
                    <div class="col-12 text-center">
                        {{-- Submit Button --}}
                        <button type="submit" class="btn btn-primary me-sm-3 me-1">
                            {{-- Display different text while submitting --}}
                            <span wire:loading.remove wire:target="submitTimeline">{{ __('Submit') }}</span>
                            {{-- Optional spinner while submitting --}}
                            <span wire:loading wire:target="submitTimeline" class="spinner-border spinner-border-sm"
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
    {{-- Link to Select2 JS --}}
    <script src="{{ asset('assets/vendor/libs/select2/select2.js') }}"></script>
    {{-- Link to Flatpickr JS --}}
    <script src="{{ asset('assets/vendor/libs/flatpickr/flatpickr.js') }}"></script>
    {{-- Add other necessary library scripts here --}}

    <script>
        'use strict';

        // Function to initialize components that need JS (like Select2, Flatpickr) inside the modal
        // This function should be called when the modal's HTML is present and visible (or about to be visible).
        function initializeTimelineModalComponents() {
            console.log('Initializing timeline modal components...');

            // Select2 Initialization and Livewire Integration
            const selectCenters = $('#selectCenters');
            if (selectCenters.length) {
                // Destroy existing Select2 instance before re-initializing
                if (selectCenters.data('select2')) {
                    selectCenters.select2('destroy');
                }
                // Initialize Select2
                selectCenters.select2({
                    placeholder: '{{ __('Select Center') }}', // Use translation helper
                    dropdownParent: selectCenters.parent() // Helps with positioning in modals
                });
                // Manually update Livewire property on change
                selectCenters.on('change', function() {
                    // Get the selected value(s)
                    var data = $(this).select2("val");
                    // Set the correct Livewire property (employeeTimelineInfo.centerId)
                    @this.set('employeeTimelineInfo.centerId', data);
                    console.log('employeeTimelineInfo.centerId updated:', data);
                });
                console.log('Select2 initialized for #selectCenters');
            }

            const selectDepartment = $('#selectDepartment');
            if (selectDepartment.length) {
                if (selectDepartment.data('select2')) {
                    selectDepartment.select2('destroy');
                }
                selectDepartment.select2({
                    placeholder: '{{ __('Select Department') }}', // Use translation helper
                    dropdownParent: selectDepartment.parent()
                });
                selectDepartment.on('change', function() {
                    var data = $(this).select2("val");
                    // Set the correct Livewire property (employeeTimelineInfo.departmentId)
                    @this.set('employeeTimelineInfo.departmentId', data);
                    console.log('employeeTimelineInfo.departmentId updated:', data);
                });
                console.log('Select2 initialized for #selectDepartment');
            }

            const selectPosition = $('#selectPosition');
            if (selectPosition.length) {
                if (selectPosition.data('select2')) {
                    selectPosition.select2('destroy');
                }
                selectPosition.select2({
                    placeholder: '{{ __('Select Position') }}', // Use translation helper
                    dropdownParent: selectPosition.parent()
                });
                selectPosition.on('change', function() {
                    var data = $(this).select2("val");
                    // Set the correct Livewire property (employeeTimelineInfo.positionId)
                    @this.set('employeeTimelineInfo.positionId', data);
                    console.log('employeeTimelineInfo.positionId updated:', data);
                });
                console.log('Select2 initialized for #selectPosition');
            }


            // Flatpickr Initialization and Livewire Integration for DATE fields

            // Start Date
            const startDateInput = document.querySelector('#startDate'); // Corrected selector ID
            if (startDateInput) {
                // Destroy existing Flatpickr instance if it exists
                if (startDateInput._flatpickr) {
                    startDateInput._flatpickr.destroy();
                }
                startDateInput.flatpickr({
                    dateFormat: "Y-m-d", // Ensure format matches your database/backend expectation
                    // 👇 Update Livewire property on close 👇
                    onClose: function(selectedDates, dateStr, instance) {
                        if (selectedDates.length > 0) {
                            @this.set('employeeTimelineInfo.startDate',
                            dateStr); // Set the correct Livewire property
                            console.log('employeeTimelineInfo.startDate updated:', dateStr);
                        } else {
                            @this.set('employeeTimelineInfo.startDate', null); // Set to null if cleared
                            console.log('employeeTimelineInfo.startDate cleared');
                        }
                    }
                    // ☝️ END Update Livewire property on close ☝️
                });
                console.log('Flatpickr initialized for #startDate');
            }

            // End Date
            const endDateInput = document.querySelector('#endDate'); // Corrected selector ID
            if (endDateInput) {
                if (endDateInput._flatpickr) {
                    endDateInput._flatpickr.destroy();
                }
                endDateInput.flatpickr({
                    dateFormat: "Y-m-d",
                    onClose: function(selectedDates, dateStr, instance) {
                        if (selectedDates.length > 0) {
                            @this.set('employeeTimelineInfo.endDate',
                            dateStr); // Set the correct Livewire property
                            console.log('employeeTimelineInfo.endDate updated:', dateStr);
                        } else {
                            @this.set('employeeTimelineInfo.endDate', null);
                            console.log('employeeTimelineInfo.endDate cleared');
                        }
                    }
                });
                console.log('Flatpickr initialized for #endDate');
            }


            console.log('Timeline modal components initialization complete.');
        }


        // Example: Clear Livewire properties when the modal is hidden (closed)
        // This helps reset the form state for the next time the modal is opened (for create or edit).
        const timelineModalElement = document.getElementById('timelineModal');
        if (timelineModalElement) {
            // Add event listener to initialize JS components when the modal is shown
            // This is crucial if modal content is dynamic or uses libraries that manipulate the DOM
            timelineModalElement.addEventListener('shown.bs.modal', function(event) {
                console.log('Timeline Modal shown event triggered, initializing components...');
                // Initialize components like Select2 and Flatpickr within the modal
                initializeTimelineModalComponents();

                // Optional: Set initial values for JS components after Livewire has updated the DOM
                // This is needed for edit mode or if default values are set in Livewire
                // You might need to use Livewire.on('modalDataLoaded') from your component
                // or Livewire.hook('dom-updated') with checks.
                // Example for Select2 (assuming employeeTimelineInfo properties are set in Livewire when modal shows):
                // $('#selectCenters').val(@this.get('employeeTimelineInfo.centerId')).trigger('change');
                // $('#selectDepartment').val(@this.get('employeeTimelineInfo.departmentId')).trigger('change');
                // $('#selectPosition').val(@this.get('employeeTimelineInfo.positionId')).trigger('change');
                // Example for Flatpickr:
                // const startDateInput = document.querySelector('#startDate');
                // if(startDateInput && @this.get('employeeTimelineInfo.startDate')) {
                //      startDateInput._flatpickr.setDate(@this.get('employeeTimelineInfo.startDate'), true); // true to trigger change event if needed
                // }
                // const endDateInput = document.querySelector('#endDate');
                // if(endDateInput && @this.get('employeeTimelineInfo.endDate')) {
                //      endDateInput._flatpickr.setDate(@this.get('employeeTimelineInfo.endDate'), true);
                // }


                // Optional: Focus on the first input (often useful)
                const firstInput = document.getElementById('selectCenters'); // Or the first visible input
                // Need a slight delay for Select2 to be fully ready sometimes
                setTimeout(() => {
                    if (firstInput) {
                        $(firstInput).select2('open');
                    }
                }, 100); // Open Select2 or focus input
            });

            // Add event listener to clear Livewire properties when the modal is hidden (closed)
            timelineModalElement.addEventListener('hidden.bs.modal', function() {
                console.log('Timeline Modal hidden, attempting to reset form...');
                // Call a public method in your Livewire component to reset properties and errors.
                // Make sure your Livewire component has a method named 'resetForm' (or similar).
                // The @this syntax works within a Livewire component's Blade file.
                @this.call('resetForm'); // Assuming your component has a resetForm method
            });

        } else {
            console.error('Timeline modal element with ID #timelineModal not found.');
        }

        // Note: Removed the old $(function) and $(document).ready() blocks as initialization
        // now happens on modal 'shown'. Also removed 'setSelect2Values'/'clearSelect2Values'
        // listeners as Select2 updates now directly call @this.set().
    </script>
@endpush
