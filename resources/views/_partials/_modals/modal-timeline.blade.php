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
        }

        input[type="number"] {
            -moz-appearance: textfield;
        }

        /* Adjustments for Select2 in Bootstrap modals */
        .modal-body .select2-container {
            width: 100% !important;
        }

        .modal-body .select2-container--bootstrap5 .select2-dropdown {
            z-index: 2000;
        }
    </style>
@endpush

{{-- Bootstrap Modal Structure for Employee Timeline Management --}}
{{-- wire:ignore.self: Essential for Livewire to leave Bootstrap's modal handling to Bootstrap JS. --}}
<div class="modal fade" id="timelineModal" tabindex="-1" aria-hidden="true" wire:ignore.self>
    <div class="modal-dialog modal-lg modal-dialog-centered modal-simple">
        <div class="modal-content p-3 p-md-5">
            <div class="modal-body">
                {{-- Close button --}}
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                <div class="text-center mb-4">
                    {{-- Conditional Title based on isEditTimeline public property --}}
                    <h3>{{ $isEditTimeline ? __('Edit Timeline') : __('Add New Timeline') }}</h3>
                    <p>{{ __('Manage employee\'s historical and current positions/assignments.') }}</p>
                </div>

                {{-- Timeline Form --}}
                {{-- wire:submit.prevent targets the saveTimeline method in the Livewire component --}}
                <form id="timelineForm" class="row g-3" wire:submit.prevent="saveTimeline">

                    {{-- Start Date Field --}}
                    {{-- wire:ignore is used because Flatpickr will manipulate the DOM element. --}}
                    <div wire:ignore class="col-md-6 col-12 mb-4"> {{-- Added mb-4 for spacing --}}
                        <label class="form-label" for="timelineStartDate">{{ __('Start Date') }}</label>
                        {{-- Added 'for' attribute --}}
                        <input wire:model.defer="timeline_start_date" {{-- Use .defer with wire:ignore and manual updates --}} type="text"
                            id="timelineStartDate" {{-- ID used by JavaScript to initialize Flatpickr --}}
                            class="form-control @error('timeline_start_date') is-invalid @enderror"
                            placeholder="{{ __('YYYY-MM-DD') }}" />
                        {{-- 👇 ADDED: Display Validation Error Message for Start Date 👇 --}}
                        @error('timeline_start_date')
                            <div class="invalid-feedback"> {{-- Using invalid-feedback for consistency --}}
                                {{ $message }}
                            </div>
                        @enderror
                        {{-- ☝️ END ADDED ☝️ --}}
                    </div>

                    {{-- End Date Field --}}
                    {{-- wire:ignore is used because Flatpickr will manipulate the DOM element. --}}
                    <div wire:ignore class="col-md-6 col-12 mb-4"> {{-- Added mb-4 --}}
                        <label class="form-label" for="timelineEndDate">{{ __('End Date') }}</label>
                        {{-- Added 'for' attribute --}}
                        <input wire:model.defer="timeline_end_date" {{-- Use .defer with wire:ignore and manual updates --}} type="text"
                            id="timelineEndDate" {{-- ID used by JavaScript to initialize Flatpickr --}}
                            class="form-control @error('timeline_end_date') is-invalid @enderror"
                            placeholder="{{ __('YYYY-MM-DD or Leave Empty for Present') }}" />
                        {{-- 👇 ADDED: Display Validation Error Message for End Date 👇 --}}
                        @error('timeline_end_date')
                            <div class="invalid-feedback"> {{-- Using invalid-feedback for consistency --}}
                                {{ $message }}
                            </div>
                        @enderror
                        {{-- ☝️ END ADDED ☝️ --}}
                    </div>


                    {{-- Center Select2 Field --}}
                    {{-- wire:ignore is used because Select2 will manipulate the DOM element. --}}
                    <div wire:ignore class="col-md-4 col-12 mb-4"> {{-- Added mb-4 --}}
                        <label class="form-label" for="selectCenters">{{ __('Center') }}</label>
                        {{-- Added 'for' attribute --}}
                        <select wire:model.defer="timeline_center_id" {{-- Use .defer with wire:ignore and manual updates --}} id="selectCenters"
                            {{-- ID used by JavaScript to initialize Select2 --}}
                            class="select2 form-select @error('timeline_center_id') is-invalid @enderror"
                            data-allow-clear="true"> {{-- Optional: allows clearing selection --}}
                            <option value="">{{ __('Select Center') }}</option> {{-- Added placeholder option --}}
                            {{-- Loop through centers collection (Assumed to be passed to the partial) --}}
                            @foreach ($centers as $center)
                                <option value="{{ $center->id }}"> {{ $center->name }}</option>
                            @endforeach
                        </select>
                        {{-- 👇 ADDED: Display Validation Error Message for Center 👇 --}}
                        @error('timeline_center_id')
                            <div class="invalid-feedback"> {{-- Using invalid-feedback for consistency --}}
                                {{ $message }}
                            </div>
                        @enderror
                        {{-- ☝️ END ADDED ☝️ --}}
                    </div>


                    {{-- Department Select2 Field --}}
                    {{-- wire:ignore is used because Select2 will manipulate the DOM element. --}}
                    <div wire:ignore class="col-md-4 col-12 mb-4"> {{-- Added mb-4 --}}
                        <label class="form-label" for="selectDepartment">{{ __('Department') }}</label>
                        {{-- Added 'for' attribute --}}
                        <select wire:model.defer="timeline_department_id" {{-- Use .defer with wire:ignore and manual updates --}} id="selectDepartment"
                            {{-- ID used by JavaScript to initialize Select2 --}}
                            class="select2 form-select @error('timeline_department_id') is-invalid @enderror"
                            data-allow-clear="true"> {{-- Optional: allows clearing selection --}}
                            <option value="">{{ __('Select Department') }}</option> {{-- Added placeholder option --}}
                            {{-- Loop through departments collection (Assumed to be passed to the partial) --}}
                            @foreach ($departments as $department)
                                <option value="{{ $department->id }}"> {{ $department->name }}</option>
                            @endforeach
                        </select>
                        {{-- 👇 ADDED: Display Validation Error Message for Department 👇 --}}
                        @error('timeline_department_id')
                            <div class="invalid-feedback"> {{-- Using invalid-feedback for consistency --}}
                                {{ $message }}
                            </div>
                        @enderror
                        {{-- ☝️ END ADDED ☝️ --}}
                    </div>


                    {{-- Position Select2 Field --}}
                    {{-- wire:ignore is used because Select2 will manipulate the DOM element. --}}
                    <div wire:ignore class="col-md-4 col-12 mb-4"> {{-- Added mb-4 --}}
                        <label class="form-label" for="selectPosition">{{ __('Position') }}</label>
                        {{-- Added 'for' attribute --}}
                        <select wire:model.defer="timeline_position_id" {{-- Use .defer with wire:ignore and manual updates --}} id="selectPosition"
                            {{-- ID used by JavaScript to initialize Select2 --}}
                            class="select2 form-select @error('timeline_position_id') is-invalid @enderror"
                            data-allow-clear="true"> {{-- Optional: allows clearing selection --}}
                            <option value="">{{ __('Select Position') }}</option> {{-- Added placeholder option --}}
                            {{-- Loop through positions collection (Assumed to be passed to the partial) --}}
                            @foreach ($positions as $position)
                                <option value="{{ $position->id }}"> {{ $position->name }}</option>
                            @endforeach
                        </select>
                        {{-- 👇 ADDED: Display Validation Error Message for Position 👇 --}}
                        @error('timeline_position_id')
                            <div class="invalid-feedback"> {{-- Using invalid-feedback for consistency --}}
                                {{ $message }}
                            </div>
                        @enderror
                        {{-- ☝️ END ADDED ☝️ --}}
                    </div>


                    {{-- Form Action Buttons --}}
                    <div class="col-12 text-center">
                        {{-- Submit button --}}
                        <button type="submit" class="btn btn-primary me-sm-3 me-1">
                            {{ $isEditTimeline ? __('Update') : __('Submit') }}
                        </button>
                        {{-- Cancel button - Closes the modal --}}
                        <button type="reset" class="btn btn-label-secondary" data-bs-dismiss="modal"
                            aria-label="Close">
                            {{ __('Cancel') }}
                        </button>
                    </div>
                </form> {{-- End timelineForm --}}
            </div> {{-- End modal-body --}}
        </div> {{-- End modal-content --}}
    </div> {{-- End modal-dialog --}}
</div> {{-- End modal fade --}}

{{-- Push custom scripts for external libraries (Select2, Flatpickr) and modal control --}}
@push('custom-scripts')
    <script>
        // JavaScript to initialize Flatpickr and Select2 within the modal
        // and handle modal shown/hidden events to manage Livewire state.

        // Get the modal element using its ID
        const timelineModalElement = document.getElementById('timelineModal');

        if (timelineModalElement) {
            // Add event listener for when the modal is fully shown
            timelineModalElement.addEventListener('shown.bs.modal', function() {
                console.log('Timeline Modal shown, initializing Flatpickr and Select2...');

                // Initialize Flatpickr for date fields
                const startDateInput = document.getElementById('timelineStartDate');
                const endDateInput = document.getElementById('timelineEndDate');

                if (startDateInput) {
                    flatpickr(startDateInput, {
                        dateFormat: "Y-m-d", // Match database date format
                        // Optional: Add event listeners if needed
                        // onChange: function(selectedDates, dateStr, instance) {
                        //     @this.set('timeline_start_date', dateStr); // Update Livewire property on change
                        // }
                    });
                    // Set initial value for Flatpickr if Livewire property has data
                    if (@this.get('timeline_start_date')) {
                        startDateInput._flatpickr.setDate(@this.get('timeline_start_date'));
                    }
                    // Listen for Livewire property updates from backend
                    @this.on('timeline_start_date_updated', (value) => {
                        startDateInput._flatpickr.setDate(value);
                    });
                } else {
                    console.warn('Flatpickr start date input #timelineStartDate not found.');
                }

                if (endDateInput) {
                    flatpickr(endDateInput, {
                        dateFormat: "Y-m-d", // Match database date format
                        allowInput: true, // Allow manual typing
                        // Optional: Add event listeners if needed
                        // onChange: function(selectedDates, dateStr, instance) {
                        //      @this.set('timeline_end_date', dateStr); // Update Livewire property on change
                        // }
                    });
                    // Set initial value for Flatpickr if Livewire property has data
                    if (@this.get('timeline_end_date')) {
                        endDateInput._flatpickr.setDate(@this.get('timeline_end_date'));
                    }
                    // Listen for Livewire property updates from backend
                    @this.on('timeline_end_date_updated', (value) => {
                        endDateInput._flatpickr.setDate(value);
                    });
                } else {
                    console.warn('Flatpickr end date input #timelineEndDate not found.');
                }


                // Initialize Select2 for dropdowns
                const selectCenter = $('#selectCenters');
                const selectDepartment = $('#selectDepartment');
                const selectPosition = $('#selectPosition');

                if (selectCenter.length) {
                    selectCenter.select2({
                        placeholder: "{{ __('Select Center') }}", // Match placeholder from option
                        allowClear: true,
                        dropdownParent: selectCenter.parent(), // Ensure dropdown is within modal boundaries
                        // Optional: Add event listener to update Livewire property on change
                        // on('change', function() { @this.set('timeline_center_id', $(this).val()); }) // Using .val() for Select2
                    });
                    // Set initial value for Select2 if Livewire property has data
                    if (@this.get('timeline_center_id')) {
                        selectCenter.val(@this.get('timeline_center_id')).trigger(
                        'change.select2'); // Trigger change to update Select2 display
                    }
                    // Listen for Livewire property updates from backend
                    @this.on('timeline_center_id_updated', (value) => {
                        selectCenter.val(value).trigger('change.select2');
                    });
                    // Listen for event dispatched from Livewire component to update value (alternative to property update)
                    @this.on('setTimelineCenter', (value) => {
                        selectCenter.val(value).trigger('change.select2');
                    });
                } else {
                    console.warn('Select2 center input #selectCenters not found.');
                }

                if (selectDepartment.length) {
                    selectDepartment.select2({
                        placeholder: "{{ __('Select Department') }}", // Match placeholder from option
                        allowClear: true,
                        dropdownParent: selectDepartment.parent(),
                        // on('change', function() { @this.set('timeline_department_id', $(this).val()); })
                    });
                    if (@this.get('timeline_department_id')) {
                        selectDepartment.val(@this.get('timeline_department_id')).trigger('change.select2');
                    }
                    @this.on('timeline_department_id_updated', (value) => {
                        selectDepartment.val(value).trigger('change.select2');
                    });
                    @this.on('setTimelineDepartment', (value) => {
                        selectDepartment.val(value).trigger('change.select2');
                    });
                } else {
                    console.warn('Select2 department input #selectDepartment not found.');
                }

                if (selectPosition.length) {
                    selectPosition.select2({
                        placeholder: "{{ __('Select Position') }}", // Match placeholder from option
                        allowClear: true,
                        dropdownParent: selectPosition.parent(),
                        // on('change', function() { @this.set('timeline_position_id', $(this).val()); })
                    });
                    if (@this.get('timeline_position_id')) {
                        selectPosition.val(@this.get('timeline_position_id')).trigger('change.select2');
                    }
                    @this.on('timeline_position_id_updated', (value) => {
                        selectPosition.val(value).trigger('change.select2');
                    });
                    @this.on('setTimelinePosition', (value) => {
                        selectPosition.val(value).trigger('change.select2');
                    });
                } else {
                    console.warn('Select2 position input #selectPosition not found.');
                }


                // Optional: Focus on the first input (often useful)
                const firstInput = document.getElementById('timelineStartDate'); // Or the first visible input
                if (firstInput) {
                    firstInput.focus();
                }


                // Livewire v3 - Manually dispatch event to update Livewire properties when Select2 changes
                // This is necessary when using wire:ignore and wire:model.defer
                selectCenter.on('change', function() {
                    @this.set('timeline_center_id', $(this).val());
                });
                selectDepartment.on('change', function() {
                    @this.set('timeline_department_id', $(this).val());
                });
                selectPosition.on('change', function() {
                    @this.set('timeline_position_id', $(this).val());
                });

                // Livewire v3 - Manually dispatch event to update Livewire properties when Flatpickr changes
                startDateInput.addEventListener('change', function() {
                    @this.set('timeline_start_date', this.value);
                });
                endDateInput.addEventListener('change', function() {
                    @this.set('timeline_end_date', this.value);
                });
            });

            // Add event listener to clear Livewire properties when the modal is hidden (closed)
            // Use the event name defined in the Livewire component's @On attribute
            timelineModalElement.addEventListener('hidden.bs.modal', function() {
                console.log('Timeline Modal hidden, dispatching hide-timeline-modal event...');
                // Dispatch the event that the Livewire component listens for to reset the form
                @this.dispatch('hide-timeline-modal');
            });

        } else {
            console.error('Timeline modal element with ID #timelineModal not found.');
        }
    </script>
@endpush
