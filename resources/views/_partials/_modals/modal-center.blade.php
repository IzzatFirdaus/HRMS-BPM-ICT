{{-- This Blade partial represents a Livewire modal component for managing Centers. --}}

{{--
    Tailwind CSS Modal Structure for Center Management
    Controlled by a Livewire property (e.g., $showModal) and Alpine.js (x-show, @entangle).
--}}
{{-- The outer div handles the modal overlay and positioning. wire:ignore.self is not needed as Livewire/Alpine controls visibility. --}}
<div x-data="{ show: @entangle('showModal').defer }" {{-- Entangle Alpine's 'show' with Livewire's 'showModal', defer updates for better performance --}} x-show="show" {{-- Alpine.js directive to show/hide the modal --}}
    class="fixed inset-0 z-50 overflow-y-auto" {{-- Tailwind classes for fixed positioning, z-index, overflow --}} aria-labelledby="modal-title" {{-- Accessibility attributes --}}
    role="dialog" aria-modal="true" {{-- You might still need the id if Bootstrap JS is used elsewhere to target this modal --}} id="centerModal" tabindex="-1" aria-hidden="true">
    {{-- Inner div for centering the modal content --}}
    <div class="flex items-center justify-center min-h-screen px-4 py-12 text-center sm:block sm:p-0">

        {{-- Tailwind classes for overlay with transitions --}}
        <div x-show="show" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
            class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" aria-hidden="true"></div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        {{-- Tailwind classes for modal content, positioning, transitions, border, shadow, bg, etc. --}}
        <div x-show="show" x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            class="inline-block w-full max-w-lg p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-white rounded-lg shadow-xl">

            {{-- Converted btn-close and data-bs-dismiss to Tailwind and Alpine click --}}
            <button @click="show = false; $wire.call('resetForm')" type="button" {{-- Set Alpine show property to false, call Livewire resetForm --}}
                class="absolute top-3 right-3 text-gray-400 hover:text-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <span class="sr-only">{{ __('Close') }}</span> {{-- Accessibility text --}}
                {{-- Simple SVG icon for close --}}
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                    xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                    </path>
                </svg>
            </button>

            <div class="text-center mb-6"> {{-- Increased bottom margin --}}
                {{-- Dynamic Title: Displays "Update Center" or "New Center" based on the $isEdit Livewire property --}}
                <h3 class="text-2xl font-bold text-gray-900 mb-2" id="modal-title">
                    {{ $isEdit ? __('Update Center') : __('New Center') }}</h3> {{-- Translated string, added text size, font-bold, color, mb --}}
                <p class="text-sm text-gray-500">{{ __('Please fill out the following information') }}</p>
                {{-- Translated string, added text size and color --}}
            </div>

            {{-- Replaced row g-3 with Tailwind flex and gap --}}
            <form wire:submit.prevent="submitCenter" class="flex flex-col gap-4"> {{-- Use flex flex-col and gap for vertical spacing --}}

                {{-- Center Name Input Field --}}
                <div> {{-- Each form field block --}}
                    <label class="block text-gray-700 text-sm font-bold mb-2"
                        for="centerName">{{ __('Name') }}:</label> {{-- Converted form-label, added colon --}}
                    {{-- Converted form-control and is-invalid to Tailwind classes --}}
                    <input wire:model='name' id="centerName"
                        class="shadow-sm appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('name') border-red-500 @enderror"
                        type="text" placeholder="{{ __('Enter center name') }}" /> {{-- Translated placeholder --}}
                    @error('name')
                        <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p> {{-- Converted text-danger --}}
                    @enderror
                </div>

                {{-- Weekends Select2 Field --}}
                {{-- wire:ignore is essential for Select2 to prevent Livewire from interfering with its DOM --}}
                <div wire:ignore> {{-- Each form field block --}}
                    <label class="block text-gray-700 text-sm font-bold mb-2"
                        for="select2Weekends">{{ __('Weekends') }}:</label> {{-- Converted form-label, added colon --}}
                    {{-- Added a custom class for targeted styling in the CSS below --}}
                    {{-- Note: The appearance will depend heavily on Select2's own styling and the custom CSS overrides --}}
                    <select wire:model='weekends' id="select2Weekends"
                        class="select2-tailwind-styled w-full @error('weekends') border-red-500 @enderror"
                        data-allow-clear="true" multiple>
                        {{-- Options with translated strings --}}
                        <option value="0">{{ __('Sunday') }}</option>
                        <option value="1">{{ __('Monday') }}</option>
                        <option value="2">{{ __('Tuesday') }}</option>
                        <option value="3">{{ __('Wednesday') }}</option>
                        <option value="4">{{ __('Thursday') }}</option>
                        <option value="5">{{ __('Friday') }}</option>
                        <option value="6">{{ __('Saturday') }}</option>
                    </select>
                    @error('weekends')
                        <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p> {{-- Converted text-danger --}}
                    @enderror
                </div>

                {{-- Work Start Time and End Time Flatpickr Fields --}}
                {{-- Used Tailwind flexbox for side-by-side layout on medium screens --}}
                <div class="flex flex-wrap -mx-2"> {{-- Container for the two time fields with negative horizontal margin --}}
                    <div class="px-2 w-full md:w-1/2"> {{-- Use padding and width classes for layout --}}
                        <label class="block text-gray-700 text-sm font-bold mb-2"
                            for="startWorkHour">{{ __('Work start at') }}:</label> {{-- Converted form-label, added colon --}}
                        {{-- Added a custom class for targeted styling in the CSS below --}}
                        {{-- Note: The appearance will depend heavily on Flatpickr's own styling and the custom CSS overrides --}}
                        <input wire:model='startWorkHour' type="text" id="startWorkHour"
                            class="flatpickr-tailwind-styled shadow-sm appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('startWorkHour') border-red-500 @enderror"
                            placeholder="HH:MM" autocomplete="off" />
                        @error('startWorkHour')
                            <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p> {{-- Converted text-danger --}}
                        @enderror
                    </div>

                    <div class="px-2 w-full md:w-1/2 mt-4 md:mt-0"> {{-- Use padding and width classes for layout, add top margin on small screens --}}
                        <label class="block text-gray-700 text-sm font-bold mb-2"
                            for="endWorkHour">{{ __('Work end at') }}:</label> {{-- Converted form-label, added colon --}}
                        {{-- Added a custom class for targeted styling in the CSS below --}}
                        {{-- Note: The appearance will depend heavily on Flatpickr's own styling and the custom CSS overrides --}}
                        <input wire:model='endWorkHour' type="text" id="endWorkHour"
                            class="flatpickr-tailwind-styled shadow-sm appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('endWorkHour') border-red-500 @enderror"
                            placeholder="HH:MM" autocomplete="off" />
                        @error('endWorkHour')
                            <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p> {{-- Converted text-danger --}}
                        @enderror
                    </div>
                </div> {{-- End time fields container --}}


                {{-- Submit and Cancel Buttons --}}
                <div class="mt-6 flex justify-center space-x-4"> {{-- Used Tailwind flex, justify-center, mt, space-x --}}
                    {{-- Submit Button --}}
                    {{-- Converted btn btn-primary me-sm-3 me-1 to Tailwind classes --}}
                    <button type="submit"
                        class="inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:text-sm">
                        {{-- Display different text/spinner while submitting --}}
                        <span wire:loading.remove wire:target="submitCenter">{{ __('Submit') }}</span>
                        {{-- Translated string --}}
                        {{-- Optional spinner while submitting (converted from Bootstrap spinner to SVG) --}}
                        <span wire:loading wire:target="submitCenter" class="flex items-center"> {{-- Use flex to align spinner and text --}}
                            {{-- Simple SVG spinner example --}}
                            <svg class="animate-spin -ml-1 mr-2 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg"
                                fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10"
                                    stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 004 12H0c0 3.042 1.135 5.824 3 7.938l2-2.647z">
                                </path>
                            </svg>
                            {{ __('Submitting...') }} {{-- Optional text while submitting --}}
                        </span>
                    </button>
                    {{-- Cancel/Reset Button --}}
                    {{-- Converted btn btn-label-secondary btn-reset data-bs-dismiss="modal" to Tailwind and Alpine click --}}
                    <button type="button" @click="show = false; $wire.call('resetForm')" {{-- Set Alpine show property to false, call Livewire resetForm --}}
                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        {{-- Adjusted size and spacing for responsiveness --}}
                        {{ __('Cancel') }} {{-- Translated string --}}
                    </button>
                </div>
            </form>

        </div> {{-- End Modal Content --}}
    </div> {{-- End Modal Dialog container (used for centering) --}}
</div> {{-- End Modal Container (controlled by x-show) --}}


{{-- Push custom CSS for Select2 and Flatpickr to the 'custom-css' stack --}}
@push('custom-css')
    {{-- Link to Select2 CSS (Note: may need a Tailwind-compatible theme) --}}
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/select2/select2.css') }}" />
    {{-- Link to Flatpickr CSS (Note: may need a Tailwind-compatible theme) --}}
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/flatpickr/flatpickr.css') }}" />
    {{-- Add basic Tailwind overrides for Select2/Flatpickr elements if needed --}}
    <style>
        /* Basic Tailwind overrides for Select2 elements */
        /* These styles attempt to make Select2 inputs look like standard Tailwind form inputs */
        .select2-container--default .select2-selection--single,
        .select2-container--default .select2-selection--multiple {
            border: 1px solid #d1d5db;
            /* gray-300 */
            border-radius: 0.375rem;
            /* rounded-md */
            min-height: 2.5rem;
            /* Matches py-2 px-3 input height */
            padding: 0.375rem 0.75rem;
            /* Vertical padding adjusted */
            font-size: 1rem;
            /* text-base */
            width: 100% !important;
            /* Ensure it takes full width */
            display: block;
            /* Ensure it's a block element */
        }

        .select2-container--default .select2-selection--multiple {
            padding-top: 0.25rem;
            /* Adjust padding for multiple selections */
            padding-bottom: 0.25rem;
        }


        .select2-container--default .select2-selection--multiple .select2-selection__choice {
            background-color: #e5e7eb;
            /* gray-200 */
            border: 1px solid #d1d5db;
            /* gray-300 */
            border-radius: 0.25rem;
            /* rounded */
            padding: 0.125rem 0.5rem;
            /* py-0.5 px-2 */
            font-size: 0.875rem;
            /* text-sm */
            margin-top: 0.25rem;
            /* mt-1 */
            margin-right: 0.25rem;
            /* mr-1 */
        }

        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
            color: #4b5563;
            /* gray-600 */
            margin-right: 0.25rem;
            /* mr-1 */
        }

        .select2-container--default.select2-container--focus .select2-selection--single,
        .select2-container--default.select2-container--focus .select2-selection--multiple {
            border-color: #3b82f6;
            /* blue-500 */
            outline: 0;
            box-shadow: 0 0 0 0.2rem rgba(59, 130, 246, 0.5);
            /* ring-blue-500 with opacity */
        }

        /* Style for the dropdown results */
        .select2-container--default .select2-results__option--highlighted.select2-results__option--selectable {
            background-color: #eff6ff;
            /* blue-100 */
            color: #1e3a8a;
            /* blue-900 */
        }

        .select2-container--default .select2-results__option--selectable {
            padding: 0.5rem 0.75rem;
            /* py-2 px-3 */
        }

        /* Basic Tailwind overrides for Flatpickr input */
        /* The Flatpickr calendar/time picker itself has complex styling and might need more specific overrides */
        .flatpickr-tailwind-styled.form-control[readonly] {
            background-color: #f9fafb;
            /* gray-50 */
            opacity: 1;
            /* Ensure it's not faded */
        }

        .flatpickr-input {
            border: 1px solid #d1d5db;
            /* gray-300 */
            border-radius: 0.375rem;
            /* rounded-md */
            padding: 0.5rem 0.75rem;
            /* py-2 px-3 */
            font-size: 1rem;
            /* text-base */
            width: 100%;
            /* w-full */
            line-height: 1.25;
            /* leading-tight */
        }

        .flatpickr-input:focus {
            outline: none;
            border-color: #3b82f6;
            /* blue-500 */
            box-shadow: 0 0 0 0.2rem rgba(59, 130, 246, 0.5);
            /* ring-blue-500 with opacity */
        }

        /* Style for validation errors on these components */
        /* Applies red border to the Select2 container when the original select has the error class */
        /* Note: This requires watching the original select element for the class */
        .select2-container--default .select2-selection--multiple .select2-selection__rendered.is-invalid+.select2-search--inline {
            /* Complex selector targeting based on neighboring elements */
            border-color: #ef4444 !important;
            /* red-500 */
        }

        .flatpickr-tailwind-styled.border-red-500 {
            border-color: #ef4444;
            /* red-500 */
        }
    </style>
@endpush

{{-- Push custom JavaScript for Select2 and Flatpickr initialization and Livewire integration --}}
@push('custom-scripts')
    {{-- Link to Select2 JS (Ensure jQuery is loaded before this) --}}
    <script src="{{ asset('assets/vendor/libs/select2/select2.js') }}"></script>
    {{-- Link to Flatpickr JS --}}
    <script src="{{ asset('assets/vendor/libs/flatpickr/flatpickr.js') }}"></script>


    <script>
        'use strict';

        // Function to initialize Select2 and Flatpickr and handle Livewire integration
        // This function needs to be called when the modal content is available in the DOM,
        // and specifically when Livewire has finished rendering elements within the modal.
        function initializeCenterModalComponents() {
            console.log('Attempting to initialize Center modal components...');

            // Select2 Initialization and Livewire Integration
            // Target the select element and check if it's already initialized
            const select2Weekends = $('#select2Weekends');
            if (select2Weekends.length && !select2Weekends.hasClass('select2-initialized')) {
                console.log('Initializing Select2 for #select2Weekends...');

                // Destroy existing Select2 instance to prevent issues with re-initialization on subsequent Livewire updates
                if (select2Weekends.hasClass('select2-hidden-accessible')) { // Check if Select2 has initialized itself
                    try {
                        select2Weekends.select2('destroy');
                        console.log('Destroyed existing Select2 instance for #select2Weekends');
                    } catch (e) {
                        console.warn('Could not destroy Select2 instance:', e);
                    }
                }

                // Initialize Select2
                select2Weekends.select2({
                    placeholder: '{{ __('Select value') }}', // Use translation helper
                    dropdownParent: $('#centerModal'), // Use the modal's ID as the parent for correct positioning
                    width: 'resolve' // Helps resolve width issues in flexible layouts
                });
                select2Weekends.addClass('select2-initialized'); // Mark as initialized
                console.log('Initialized Select2 for #select2Weekends');

                // Manually update Livewire property when Select2's value changes
                // Use .on() to ensure the event listener is attached reliably
                select2Weekends.on('change', function(e) {
                    var data = $(this).select2("val");
                    // Only update Livewire if the value is different to prevent unnecessary component updates
                    if (JSON.stringify(data) !== JSON.stringify(@this.get('weekends'))) {
                        console.log('Select2 #select2Weekends value changed, setting Livewire property:', data);
                        @this.set('weekends', data);
                    } else {
                        console.log(
                            'Select2 #select2Weekends value changed, but Livewire property is already in sync.');
                    }
                });

                // --- Handle Livewire -> Select2 Updates ---
                // This is needed when Livewire properties are updated (e.g., when opening for edit, or after validation errors)
                // A common way is to listen for Livewire updates to the specific element.
                Livewire.hook('element.updated', ({
                    el,
                    component
                }) => {
                    // Check if the updated element is our Select2 input and its bound Livewire property
                    if (el.id === 'select2Weekends' && el.__livewire_model === 'weekends') {
                        // Use a slight delay to ensure Select2's DOM elements are ready after Livewire update
                        setTimeout(() => {
                            const currentValue = $(el).val();
                            const livewireValue = @this.get('weekends');

                            // Only update Select2 if its current value doesn't match the Livewire property
                            // This prevents infinite loops or unnecessary updates
                            if (JSON.stringify(currentValue) !== JSON.stringify(livewireValue)) {
                                console.log(
                                    'Livewire hook: Updating Select2 #select2Weekends value to match Livewire property:',
                                    livewireValue);
                                $(el).val(livewireValue).trigger(
                                'change.select2'); // Trigger Select2's own change event
                            } else {
                                console.log(
                                    'Livewire hook: Select2 #select2Weekends value already matches Livewire property.'
                                    );
                            }
                        }, 50); // Adjust delay if needed
                    }
                });

                // --- Handle Initial Value Setting on Modal Show ---
                // When the modal is shown (either by Livewire/Alpine or Bootstrap JS), set the initial Select2 value
                // We'll handle this via the 'shown.bs.modal' event listener if still using Bootstrap modal JS to show.
                // If using Livewire/Alpine purely, you'd use a Livewire event or watch the 'showModal' property in Alpine.
            } // End Select2 initialization block


            // Flatpickr Initialization and Livewire Integration
            // Target inputs that haven't been initialized using a data attribute
            const startWorkHourInput = document.querySelector('#startWorkHour:not([data-flatpickr-initialized])');
            if (startWorkHourInput) {
                console.log('Initializing Flatpickr for #startWorkHour...');
                // Initialize Flatpickr as a time picker
                const startWorkHourFP = startWorkHourInput.flatpickr({
                    enableTime: true, // Enable time selection
                    noCalendar: true, // Disable calendar view
                    time_24hr: true, // Use 24-hour format
                    dateFormat: "H:i", // Format the output time (matches HH:MM placeholder)
                    // Set initial value using defaultValue or setDate later
                    // defaultHour: 9, // Optional: Default hour when opening empty
                    // defaultMinute: 0, // Optional: Default minute when opening empty
                    // Update Livewire property on close
                    onClose: function(selectedDates, dateStr, instance) {
                        console.log('Flatpickr #startWorkHour onClose event:', dateStr);
                        @this.set('startWorkHour', dateStr); // Set Livewire property with formatted time string
                    },
                    // Add a handler for clearing the input manually
                    onInputChange: function(selectedDates, dateStr, instance) {
                        if (dateStr === "") {
                            console.log(
                                'Flatpickr #startWorkHour input cleared, setting Livewire property to null');
                            @this.set('startWorkHour', null);
                        }
                    }
                });
                startWorkHourInput.setAttribute('data-flatpickr-initialized', 'true'); // Mark as initialized
                console.log('Initialized Flatpickr for #startWorkHour');

                // --- Handle Livewire -> Flatpickr Updates ---
                // Set initial value from Livewire property when component updates (e.g., after Livewire rerender)
                Livewire.hook('element.updated', ({
                    el,
                    component
                }) => {
                    if (el.id === 'startWorkHour' && el.__livewire_model === 'startWorkHour') {
                        console.log(
                            'Livewire hook: Updating Flatpickr #startWorkHour value to match Livewire property:',
                            @this.get('startWorkHour'));
                        if (el._flatpickr) { // Check if Flatpickr instance exists
                            el._flatpickr.setDate(@this.get('startWorkHour'),
                            false); // false means don't trigger onClose again
                        }
                    }
                });

                // --- Handle Initial Value Setting on Modal Show ---
                // We'll handle this via the 'shown.bs.modal' event listener below.
            } // End Flatpickr startWorkHour initialization


            const endWorkHourInput = document.querySelector('#endWorkHour:not([data-flatpickr-initialized])');
            if (endWorkHourInput) {
                console.log('Initializing Flatpickr for #endWorkHour...');
                // Initialize Flatpickr as a time picker
                const endWorkHourFP = endWorkHourInput.flatpickr({
                    enableTime: true, // Enable time selection
                    noCalendar: true, // Disable calendar view
                    time_24hr: true, // Use 24-hour format
                    dateFormat: "H:i", // Format the output time (matches HH:MM placeholder)
                    // Set initial value using defaultValue or setDate later
                    // defaultHour: 15, // Optional: Default hour
                    // defaultMinute: 30, // Optional: Default minute
                    // Update Livewire property on close
                    onClose: function(selectedDates, dateStr, instance) {
                        console.log('Flatpickr #endWorkHour onClose event:', dateStr);
                        @this.set('endWorkHour', dateStr); // Set Livewire property with formatted time string
                    },
                    // Add a handler for clearing the input
                    onInputChange: function(selectedDates, dateStr, instance) {
                        if (dateStr === "") {
                            console.log(
                                'Flatpickr #endWorkHour input cleared, setting Livewire property to null');
                            @this.set('endWorkHour', null);
                        }
                    }
                });
                endWorkHourInput.setAttribute('data-flatpickr-initialized', 'true'); // Mark as initialized
                console.log('Initialized Flatpickr for #endWorkHour');

                // --- Handle Livewire -> Flatpickr Updates ---
                // Set initial value from Livewire property when component updates (e.g., after Livewire rerender)
                Livewire.hook('element.updated', ({
                    el,
                    component
                }) => {
                    if (el.id === 'endWorkHour' && el.__livewire_model === 'endWorkHour') {
                        console.log(
                            'Livewire hook: Updating Flatpickr #endWorkHour value to match Livewire property:',
                            @this.get('endWorkHour'));
                        if (el._flatpickr) { // Check if Flatpickr instance exists
                            el._flatpickr.setDate(@this.get('endWorkHour'),
                            false); // false means don't trigger onClose again
                        }
                    }
                });

                // --- Handle Initial Value Setting on Modal Show ---
                // We'll handle this via the 'shown.bs.modal' event listener below.
            } // End Flatpickr endWorkHour initialization


            console.log('Center modal components initialization function finished.');
        }

        // --- Bootstrap Modal Events and Livewire Integration ---
        // These listeners assume Bootstrap JS is still managing the modal's show/hide state
        const centerModalElement = document.getElementById('centerModal');
        if (centerModalElement) {

            // Listen for the modal being fully shown by Bootstrap JS
            // This is the best place to initialize external JS components and set initial values
            centerModalElement.addEventListener('shown.bs.modal', function() {
                console.log('Bootstrap modal "shown.bs.modal" event fired.');

                // Initialize components within the modal
                initializeCenterModalComponents();

                // Manually set initial values for Select2/Flatpickr from Livewire properties
                // This is crucial when the modal is opened, especially for editing.
                // Use a slight delay to ensure components are fully rendered before setting values.
                setTimeout(() => {
                    const select2Weekends = $('#select2Weekends');
                    if (select2Weekends.length && @this.has(
                        'weekends')) { // Check if Livewire property exists
                        const livewireWeekends = @this.get('weekends');
                        console.log(
                            'shown.bs.modal: Setting Select2 #select2Weekends initial value from Livewire:',
                            livewireWeekends);
                        // Set the value and trigger Select2's internal change event
                        // This might also trigger the .on('change') listener above, which is fine if it checks for differences.
                        select2Weekends.val(livewireWeekends).trigger('change.select2');
                    } else if (select2Weekends.length) {
                        // If the Livewire property doesn't exist or is null, clear Select2
                        select2Weekends.val(null).trigger('change.select2');
                        console.log('shown.bs.modal: Clearing Select2 #select2Weekends.');
                    }


                    const startWorkHourInput = document.querySelector('#startWorkHour');
                    if (startWorkHourInput && startWorkHourInput._flatpickr && @this.has('startWorkHour') &&
                        @this.get('startWorkHour')) {
                        const livewireStartHour = @this.get('startWorkHour');
                        console.log(
                            'shown.bs.modal: Setting Flatpickr #startWorkHour initial value from Livewire:',
                            livewireStartHour);
                        startWorkHourInput._flatpickr.setDate(livewireStartHour,
                        false); // false to prevent triggering onClose immediately
                    } else if (startWorkHourInput && startWorkHourInput._flatpickr) {
                        // If Livewire property doesn't exist or is empty, clear Flatpickr
                        startWorkHourInput._flatpickr.clear();
                        console.log('shown.bs.modal: Clearing Flatpickr #startWorkHour.');
                    }


                    const endWorkHourInput = document.querySelector('#endWorkHour');
                    if (endWorkHourInput && endWorkHourInput._flatpickr && @this.has('endWorkHour') && @this
                        .get('endWorkHour')) {
                        const livewireEndHour = @this.get('endWorkHour');
                        console.log(
                            'shown.bs.modal: Setting Flatpickr #endWorkHour initial value from Livewire:',
                            livewireEndHour);
                        endWorkHourInput._flatpickr.setDate(livewireEndHour,
                        false); // false to prevent triggering onClose immediately
                    } else if (endWorkHourInput && endWorkHourInput._flatpickr) {
                        // If Livewire property doesn't exist or is empty, clear Flatpickr
                        endWorkHourInput._flatpickr.clear();
                        console.log('shown.bs.modal: Clearing Flatpickr #endWorkHour.');
                    }

                }, 150); // Adjust delay as needed


            });

            // Listen for the modal being fully hidden by Bootstrap JS
            // This is where you typically reset the form state in the Livewire component.
            centerModalElement.addEventListener('hidden.bs.modal', function() {
                console.log('Bootstrap modal "hidden.bs.modal" event fired. Calling resetForm...');
                // Call a Livewire method on the component to reset its state
                // Make sure your Livewire component has a public method like 'resetForm'
                @this.call('resetForm');

                // Optional: Manually clear the Select2 selection and Flatpickr values on hide
                // This can be a fallback or prevent flickering if resetForm is async
                const select2Weekends = $('#select2Weekends');
                if (select2Weekends.length && select2Weekends.hasClass('select2-initialized')) {
                    select2Weekends.val(null).trigger('change.select2');
                    console.log('Manually cleared Select2 #select2Weekends on modal hide.');
                }
                const startWorkHourInput = document.querySelector('#startWorkHour');
                if (startWorkHourInput && startWorkHourInput._flatpickr) {
                    startWorkHourInput._flatpickr.clear();
                    console.log('Manually cleared Flatpickr #startWorkHour on modal hide.');
                }
                const endWorkHourInput = document.querySelector('#endWorkHour');
                if (endWorkHourInput && endWorkHourInput._flatpickr) {
                    endWorkHourInput._flatpickr.clear();
                    console.log('Manually cleared Flatpickr #endWorkHour on modal hide.');
                }
            });

        } else {
            console.warn('Center modal element (#centerModal) not found in the DOM.');
        }

        // If you are controlling the modal show state purely via Livewire property (e.g., $showModal)
        // and *not* using Bootstrap JS to show it initially, you might need an observer or
        // Livewire.hook('dom-updated') to detect when the modal becomes visible and trigger
        // initializeCenterModalComponents() and initial value setting. However, the 'shown.bs.modal'
        // event is the most reliable trigger if Bootstrap JS is involved in showing it.
    </script>
@endpush
