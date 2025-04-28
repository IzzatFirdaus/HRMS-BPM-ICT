{{-- This Blade partial represents a Livewire modal component for managing Centers. --}}

{{-- Push custom CSS for Select2 and Flatpickr to the 'custom-css' stack --}}
@push('custom-css')
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/select2/select2.css') }}" /> {{-- Link to Select2 CSS --}}
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/flatpickr/flatpickr.css') }}" /> {{-- Link to Flatpickr CSS --}}
    {{-- Add other custom CSS specific to this modal here --}}
@endpush

{{--
    Bootstrap Modal Structure for Center Management
    wire:ignore.self: Essential for Livewire to leave Bootstrap's modal handling to Bootstrap JS.
    id: The ID used by Bootstrap to target the modal.
    tabindex="-1": Makes the modal accessible.
    aria-hidden="true": Hides the modal from assistive technologies when closed.
--}}
<div wire:ignore.self class="modal fade" id="centerModal" tabindex="-1" aria-hidden="true">
    {{--
        Modal Dialog
        modal-dialog: Base Bootstrap class for modal dialog.
        modal-simple: Custom class for potentially simpler styling.
        modal-lg, modal-xl, etc.: Use Bootstrap size classes here if needed.
    --}}
    <div class="modal-dialog modal-simple">
        {{-- Modal Content --}}
        <div class="modal-content p-0 p-md-5">
            {{-- Modal Body --}}
            <div class="modal-body">
                {{-- Close Button --}}
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>

                {{-- Modal Header/Title Section --}}
                <div class="text-center mb-4">
                    {{-- Dynamic Title: Displays "Update Center" or "New Center" based on the $isEdit Livewire property --}}
                    <h3 class="mb-2">{{ $isEdit ? __('Update Center') : __('New Center') }}</h3>
                    <p class="text-muted">{{ __('Please fill out the following information') }}</p>
                </div>

                {{--
                    Form for Center Submission
                    wire:submit.prevent="submitCenter": Calls the 'submitCenter' method on the Livewire component when the form is submitted.
                                                  .prevent is crucial to stop default browser form submission.
                    row g-3: Bootstrap grid classes for form layout.
                --}}
                <form wire:submit.prevent="submitCenter" class="row g-3">

                    {{-- Center Name Input Field --}}
                    <div class="col-12">
                        <label class="form-label w-100" for="centerName">{{ __('Name') }}</label>
                        {{-- Added 'for' attribute for accessibility --}}
                        {{--
                            wire:model='name': Binds the input value to the 'name' public property in the Livewire component.
                            @error('name') is-invalid @enderror: Adds the 'is-invalid' Bootstrap class if there's a validation error for 'name'.
                        --}}
                        <input wire:model='name' id="centerName" {{-- Added ID matching the label's 'for' attribute --}}
                            class="form-control @error('name') is-invalid @enderror" type="text"
                            placeholder="{{ __('Enter center name') }}" {{-- Added placeholder --}} />
                        {{-- 👇 ADDED: Display Validation Error Message for Name 👇 --}}
                        @error('name')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                        {{-- ☝️ END ADDED ☝️ --}}
                    </div>

                    {{-- Weekends Select2 Field --}}
                    {{--
                        wire:ignore: Tells Livewire to ignore updates to this element and its children. Necessary for Select2.
                        Manual JS is used to update the Livewire property on change (see custom-scripts).
                    --}}
                    <div wire:ignore class="col-md-12">
                        <label class="form-label" for="select2Weekends">{{ __('Weekends') }}</label>
                        {{-- Added 'for' attribute --}}
                        {{--
                             wire:model='weekends': Although wire:ignore is used, this might be present initially or used for setting the initial value.
                                                    The change event handler explicitly sets the model.
                             multiple: Allows selecting multiple options.
                             data-allow-clear="true": Adds a clear button to Select2.
                         --}}
                        <select wire:model='weekends' id="select2Weekends"
                            class="select2 form-select form-select-lg @error('weekends') is-invalid @enderror"
                            data-allow-clear="true" multiple>
                            {{-- Options for days of the week. Value '0' for Sunday, '6' for Saturday. --}}
                            <option value="0">{{ __('Sunday') }}</option>
                            <option value="1">{{ __('Monday') }}</option>
                            <option value="2">{{ __('Tuesday') }}</option>
                            <option value="3">{{ __('Wednesday') }}</option>
                            <option value="4">{{ __('Thursday') }}</option>
                            <option value="5">{{ __('Friday') }}</option>
                            <option value="6">{{ __('Saturday') }}</option>
                        </select>
                        {{-- 👇 ADDED: Display Validation Error Message for Weekends 👇 --}}
                        @error('weekends')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                        {{-- ☝️ END ADDED ☝️ --}}
                    </div>

                    {{-- Work Start Time Flatpickr Field --}}
                    <div class="col-md-6 col-12 mb-4">
                        <label class="form-label" for="startWorkHour">{{ __('Work start at') }}</label>
                        {{-- Added 'for' attribute --}}
                        {{--
                             wire:model='startWorkHour': Binds the input value.
                             Flatpickr JS will be initialized on this input.
                             Manual JS will update the model property on Flatpickr events (see custom-scripts).
                         --}}
                        <input wire:model='startWorkHour' type="text" id="startWorkHour" {{-- Added ID matching the label's 'for' attribute --}}
                            class="form-control @error('startWorkHour') is-invalid @enderror" placeholder="HH:MM"
                            autocomplete="off" />
                        {{-- 👇 ADDED: Display Validation Error Message for Start Time 👇 --}}
                        @error('startWorkHour')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                        {{-- ☝️ END ADDED ☝️ --}}
                    </div>

                    {{-- Work End Time Flatpickr Field --}}
                    <div class="col-md-6 col-12 mb-4">
                        <label class="form-label" for="endWorkHour">{{ __('Work end at') }}</label>
                        {{-- Added 'for' attribute --}}
                        {{--
                             wire:model='endWorkHour': Binds the input value.
                             Flatpickr JS will be initialized on this input.
                             Manual JS will update the model property on Flatpickr events (see custom-scripts).
                         --}}
                        <input wire:model='endWorkHour' type="text" id="endWorkHour" {{-- Added ID matching the label's 'for' attribute --}}
                            class="form-control @error('endWorkHour') is-invalid @enderror" placeholder="HH:MM"
                            autocomplete="off" />
                        {{-- 👇 ADDED: Display Validation Error Message for End Time 👇 --}}
                        @error('endWorkHour')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                        {{-- ☝️ END ADDED ☝️ --}}
                    </div>

                    {{-- Submit and Cancel Buttons --}}
                    <div class="col-12 text-center">
                        {{-- Submit Button --}}
                        <button type="submit" class="btn btn-primary me-sm-3 me-1">
                            {{-- Display different text while submitting --}}
                            <span wire:loading.remove wire:target="submitCenter">{{ __('Submit') }}</span>
                            {{-- Optional spinner while submitting --}}
                            <span wire:loading wire:target="submitCenter" class="spinner-border spinner-border-sm"
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

{{-- Push custom JavaScript for Select2 and Flatpickr initialization and Livewire integration --}}
@push('custom-scripts')
    <script src="{{ asset('assets/vendor/libs/select2/select2.js') }}"></script> {{-- Link to Select2 JS --}}
    <script src="{{ asset('assets/vendor/libs/flatpickr/flatpickr.js') }}"></script> {{-- Link to Flatpickr JS --}}

    <script>
        'use strict';

        // Function to initialize Select2 and Flatpickr and handle Livewire integration
        // This function should be called when the modal is loaded/shown if its HTML is added dynamically.
        // If the modal HTML is part of the initial page load and just hidden, $(function) is usually sufficient.
        function initializeCenterModalComponents() {
            // Select2 Initialization and Livewire Integration
            const select2Weekends = $('#select2Weekends');
            if (select2Weekends.length) {
                // Destroy existing Select2 instance if it exists
                if (select2Weekends.data('select2')) {
                    select2Weekends.select2('destroy');
                }

                // Initialize Select2
                select2Weekends.select2({
                    placeholder: '{{ __('Select value') }}', // Use translation helper
                    dropdownParent: select2Weekends.parent() // Helps with positioning in modals
                });

                // Manually update Livewire property on change
                select2Weekends.on('change', function(e) {
                    // Get the selected values from Select2
                    var data = $(this).select2("val");
                    // Set the 'weekends' Livewire property
                    @this.set('weekends', data);
                });

                // Optional: If you want to set the initial Select2 value from Livewire when it's loaded,
                // you might need a Livewire event or method to call select2Weekends.val(@this.get('weekends')).trigger('change');
            }

            // Flatpickr Initialization and Livewire Integration
            const startWorkHourInput = document.querySelector('#startWorkHour');
            if (startWorkHourInput) {
                // Initialize Flatpickr as a time picker
                startWorkHourInput.flatpickr({
                    enableTime: true, // Enable time selection
                    noCalendar: true, // Disable calendar view
                    time_24hr: true, // Use 24-hour format
                    dateFormat: "H:i", // Format the output time (matches HH:MM placeholder)
                    defaultHour: 9, // Default hour when opening
                    // 👇 ADDED: Update Livewire property on close 👇
                    onClose: function(selectedDates, dateStr, instance) {
                        // Check if a date was actually selected
                        if (selectedDates.length > 0) {
                            @this.set('startWorkHour',
                            dateStr); // Set Livewire property with formatted time string
                            console.log('startWorkHour updated:', dateStr);
                        } else {
                            @this.set('startWorkHour', null); // Set to null if cleared
                            console.log('startWorkHour cleared');
                        }
                    }
                    // ☝️ END ADDED ☝️
                });
            }

            const endWorkHourInput = document.querySelector('#endWorkHour');
            if (endWorkHourInput) {
                // Initialize Flatpickr as a time picker
                endWorkHourInput.flatpickr({
                    enableTime: true, // Enable time selection
                    noCalendar: true, // Disable calendar view
                    time_24hr: true, // Use 24-hour format
                    dateFormat: "H:i", // Format the output time (matches HH:MM placeholder)
                    defaultHour: 15, // Default hour
                    defaultMinute: 30, // Default minute
                    // 👇 ADDED: Update Livewire property on close 👇
                    onClose: function(selectedDates, dateStr, instance) {
                        // Check if a date was actually selected
                        if (selectedDates.length > 0) {
                            @this.set('endWorkHour',
                            dateStr); // Set Livewire property with formatted time string
                            console.log('endWorkHour updated:', dateStr);
                        } else {
                            @this.set('endWorkHour', null); // Set to null if cleared
                            console.log('endWorkHour cleared');
                        }
                    }
                    // ☝️ END ADDED ☝️
                });
            }

            console.log('Center modal components initialized.');
        }

        // Call the initialization function when the document is ready (if modal HTML is initially present)
        $(function() {
            initializeCenterModalComponents();
        });

        // Alternatively, if the modal HTML is added dynamically by Livewire,
        // listen for a Livewire event (e.g., from your component's mount() or updated() method)
        // or use Livewire.hook('dom-updated') to re-initialize components inside the modal.
        // Example using a custom event emitted by the Livewire component when modal data is ready:
        // Livewire.on('centerModalReady', function() {
        //    initializeCenterModalComponents();
        //    // Optional: Set initial values for Select2/Flatpickr from Livewire properties
        //    $('#select2Weekends').val(@this.get('weekends')).trigger('change');
        //    document.querySelector('#startWorkHour')._flatpickr.setDate(@this.get('startWorkHour'));
        //    document.querySelector('#endWorkHour')._flatpickr.setDate(@this.get('endWorkHour'));
        // });

        // Or using Livewire hook (less specific, might run too often):
        // Livewire.hook('dom-updated', (component) => {
        //     if (component.el.id === 'centerModal') { // Check if the updated element is the modal
        //          initializeCenterModalComponents();
        //          // Need more complex logic here to avoid re-initializing already initialized components
        //     }
        // });


        // Example: Clear Livewire properties when the modal is hidden (closed)
        // This helps reset the form state for the next time the modal is opened (for create or edit).
        const centerModalElement = document.getElementById('centerModal');
        if (centerModalElement) {
            centerModalElement.addEventListener('hidden.bs.modal', function() {
                console.log('Center Modal hidden, resetting Livewire properties...');
                // Call a Livewire method on the component to reset its state
                // Make sure your Livewire component has a public method like 'resetForm'
                // The @this syntax works within a Livewire component's Blade file.
                @this.call('resetForm');
            });
        }
    </script>
@endpush
