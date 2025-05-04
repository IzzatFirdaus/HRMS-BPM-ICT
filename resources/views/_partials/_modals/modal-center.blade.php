{{-- resources/views/livewire/human-resource/structure/centers.blade.php --}}

{{-- This Blade partial represents a Livewire modal component for managing Centers. --}}

{{--
    Tailwind CSS Modal Structure for Center Management
    Controlled by a Livewire property (e.g., $showModal) and Alpine.js (x-show, @entangle).
--}}
{{-- The outer div handles the modal overlay and positioning. wire:ignore.self is not needed as Livewire/Alpine controls visibility. --}}
{{-- Removed Bootstrap modal attributes to rely on Alpine/Livewire visibility control --}}
<div x-data="{ show: @entangle('showModal').defer }" {{-- Entangle Alpine's 'show' with Livewire's 'showModal', defer updates --}} x-show="show" {{-- Alpine.js directive to show/hide the modal --}}
    class="fixed inset-0 z-50 overflow-y-auto" {{-- Tailwind classes for fixed positioning, z-index, overflow --}} aria-labelledby="modal-title" role="dialog"
    aria-modal="true"> {{-- Kept accessibility attributes --}}

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

            {{-- Close Button --}}
            <button @click="show = false; $wire.call('resetForm')" type="button"
                class="absolute top-3 right-3 text-gray-400 hover:text-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <span class="sr-only">{{ __('Close') }}</span>
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                    xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                    </path>
                </svg>
            </button>

            <div class="text-center mb-6">
                {{-- Dynamic Title --}}
                <h3 class="text-2xl font-bold text-gray-900 mb-2" id="modal-title">
                    {{ $isEdit ? __('Update Center') : __('New Center') }}</h3>
                <p class="text-sm text-gray-500">{{ __('Please fill out the following information') }}</p>
            </div>

            <form wire:submit.prevent="submitCenter" class="flex flex-col gap-4">

                {{-- Center Name Input Field --}}
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2"
                        for="centerName">{{ __('Name') }}:</label>
                    <input wire:model='name' id="centerName"
                        class="shadow-sm appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('name') border-red-500 @enderror"
                        type="text" placeholder="{{ __('Enter center name') }}" />
                    @error('name')
                        <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Weekends Select2 Field --}}
                <div wire:ignore>
                    <label class="block text-gray-700 text-sm font-bold mb-2"
                        for="select2Weekends">{{ __('Weekends') }}:</label>
                    {{-- The appearance will depend heavily on Select2's own styling and the custom CSS overrides --}}
                    <select wire:model='weekends' id="select2Weekends"
                        class="select2-tailwind-styled w-full @error('weekends') border-red-500 @enderror"
                        data-allow-clear="true" multiple>
                        {{-- Options with translated strings and NUMBER values (0-6) --}}
                        <option value="0">{{ __('Sunday') }}</option>
                        <option value="1">{{ __('Monday') }}</option>
                        <option value="2">{{ __('Tuesday') }}</option>
                        <option value="3">{{ __('Wednesday') }}</option>
                        <option value="4">{{ __('Thursday') }}</option>
                        <option value="5">{{ __('Friday') }}</option>
                        <option value="6">{{ __('Saturday') }}</option>
                    </select>
                    @error('weekends')
                        <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Work Start Time and End Time Flatpickr Fields --}}
                <div class="flex flex-wrap -mx-2">
                    <div class="px-2 w-full md:w-1/2">
                        <label class="block text-gray-700 text-sm font-bold mb-2"
                            for="startWorkHour">{{ __('Work start at') }}:</label>
                        {{-- The appearance will depend heavily on Flatpickr's own styling and the custom CSS overrides --}}
                        <input wire:model='startWorkHour' type="text" id="startWorkHour"
                            class="flatpickr-tailwind-styled shadow-sm appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('startWorkHour') border-red-500 @enderror"
                            placeholder="HH:MM" autocomplete="off" />
                        @error('startWorkHour')
                            <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="px-2 w-full md:w-1/2 mt-4 md:mt-0">
                        <label class="block text-gray-700 text-sm font-bold mb-2"
                            for="endWorkHour">{{ __('Work end at') }}:</label>
                        {{-- The appearance will depend heavily on Flatpickr's own styling and the custom CSS overrides --}}
                        <input wire:model='endWorkHour' type="text" id="endWorkHour"
                            class="flatpickr-tailwind-styled shadow-sm appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('endWorkHour') border-red-500 @enderror"
                            placeholder="HH:MM" autocomplete="off" />
                        @error('endWorkHour')
                            <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Submit and Cancel Buttons --}}
                <div class="mt-6 flex justify-center space-x-4">
                    {{-- Submit Button --}}
                    <button type="submit"
                        class="inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:text-sm">
                        <span wire:loading.remove wire:target="submitCenter">{{ __('Submit') }}</span>
                        <span wire:loading wire:target="submitCenter" class="flex items-center">
                            <svg class="animate-spin -ml-1 mr-2 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg"
                                fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 004 12H0c0 3.042 1.135 5.824 3 7.938l2-2.647z">
                                </path>
                            </svg>
                            {{ __('Submitting...') }}
                        </span>
                    </button>
                    {{-- Cancel/Reset Button --}}
                    <button type="button" @click="show = false; $wire.call('resetForm')"
                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        {{ __('Cancel') }}
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
    {{-- Link to jQuery (Select2 requires jQuery) --}}
    {{-- Ensure jQuery is loaded BEFORE Select2 --}}
    <script src="{{ asset('assets/vendor/libs/jquery/jquery.js') }}"></script> {{-- Assuming jQuery is here --}}
    {{-- Link to Select2 JS --}}
    <script src="{{ asset('assets/vendor/libs/select2/select2.js') }}"></script>
    {{-- Link to Flatpickr JS --}}
    <script src="{{ asset('assets/vendor/libs/flatpickr/flatpickr.js') }}"></script>


    <script>
        'use strict';

        // Function to initialize Select2 and Flatpickr and handle Livewire integration
        function initializeCenterModalComponents() {
            console.log('Attempting to initialize Center modal components...');

            // Select2 Initialization and Livewire Integration
            const select2Weekends = $('#select2Weekends');
            // Check if element exists and hasn't been fully initialized by Select2
            if (select2Weekends.length && !select2Weekends.hasClass('select2-hidden-accessible')) {
                console.log('Initializing Select2 for #select2Weekends...');

                // Initialize Select2
                select2Weekends.select2({
                    placeholder: '{{ __('Select value') }}',
                    // Use the modal's container element as the parent for correct positioning
                    // This depends on your exact modal DOM structure and where the Select2 dropdown should appear
                    dropdownParent: select2Weekends.parent().parent(), // Adjust selector based on your DOM
                    // Or use a specific modal container ID if available and outside the Alpine x-data scope
                    // dropdownParent: $('#centerModal'), // If you keep the ID on the main modal div
                    width: 'resolve' // Helps resolve width issues in flexible layouts
                });

                // Manually update Livewire property when Select2's value changes
                select2Weekends.on('change', function(e) {
                    var data = $(this).select2("val");
                    // Only update Livewire if the value is different
                    // Using JSON.stringify for deep comparison of arrays
                    if (JSON.stringify(data) !== JSON.stringify(@this.get('weekends'))) {
                        console.log('Select2 #select2Weekends value changed, setting Livewire property:', data);
                        @this.set('weekends', data);
                    } else {
                        console.log(
                            'Select2 #select2Weekends value changed, but Livewire property is already in sync.');
                    }
                });
                console.log('Initialized and configured Select2 for #select2Weekends.');

            } // End Select2 initialization block


            // Flatpickr Initialization and Livewire Integration - Start Work Hour
            // Target the input element and check if it hasn't been initialized using a data attribute
            const startWorkHourInput = document.querySelector('#startWorkHour');
            // Check for both element existence and Flatpickr instance presence
            if (startWorkHourInput && !startWorkHourInput._flatpickr) {
                console.log('Initializing Flatpickr for #startWorkHour...');
                // Initialize Flatpickr as a time picker
                startWorkHourInput.flatpickr({
                    enableTime: true,
                    noCalendar: true,
                    time_24hr: true,
                    dateFormat: "H:i",
                    // Update Livewire property on close
                    onClose: function(selectedDates, dateStr, instance) {
                        console.log('Flatpickr #startWorkHour onClose event:', dateStr);
                        @this.set('startWorkHour', dateStr);
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
                console.log('Initialized and configured Flatpickr for #startWorkHour.');
            } // End Flatpickr startWorkHour initialization


            // Flatpickr Initialization and Livewire Integration - End Work Hour
            const endWorkHourInput = document.querySelector('#endWorkHour');
            // Check for both element existence and Flatpickr instance presence
            if (endWorkHourInput && !endWorkHourInput._flatpickr) {
                console.log('Initializing Flatpickr for #endWorkHour...');
                // Initialize Flatpickr as a time picker
                endWorkHourInput.flatpickr({
                    enableTime: true,
                    noCalendar: true,
                    time_24hr: true,
                    dateFormat: "H:i",
                    // Update Livewire property on close
                    onClose: function(selectedDates, dateStr, instance) {
                        console.log('Flatpickr #endWorkHour onClose event:', dateStr);
                        @this.set('endWorkHour', dateStr);
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
                console.log('Initialized and configured Flatpickr for #endWorkHour.');
            } // End Flatpickr endWorkHour initialization

            console.log('Center modal components initialization function finished.');
        }

        // --- Livewire Events and Lifecycle Hooks for Initialization ---
        // Listen for the custom Livewire event dispatched from the component when the modal should be initialized/re-initialized
        // This is more reliable than listening for Bootstrap modal events when using Alpine/Livewire for control
        Livewire.on('centerModalShown', function() {
            console.log('Livewire event "centerModalShown" received. Re-initializing modal components.');
            initializeCenterModalComponents(); // Initialize or re-initialize the plugins

            // --- Set Initial Values from Livewire Properties ---
            // Set initial values *after* initialization, especially when opening for edit
            // Use a slight delay to ensure plugins are fully ready
            setTimeout(() => {
                const select2Weekends = $('#select2Weekends');
                if (select2Weekends.length && @this.has('weekends')) {
                    const livewireWeekends = @this.get('weekends');
                    console.log('Setting Select2 #select2Weekends initial value from Livewire:',
                        livewireWeekends);
                    // Set the value and trigger Select2's internal change event
                    select2Weekends.val(livewireWeekends).trigger('change.select2');
                } else if (select2Weekends.length) {
                    // Clear Select2 if Livewire property doesn't exist or is null/empty
                    select2Weekends.val(null).trigger('change.select2');
                    console.log('Clearing Select2 #select2Weekends.');
                }

                const startWorkHourInput = document.querySelector('#startWorkHour');
                // Check for Flatpickr instance presence before setting date
                if (startWorkHourInput && startWorkHourInput._flatpickr && @this.has('startWorkHour') &&
                    @this.get('startWorkHour')) {
                    const livewireStartHour = @this.get('startWorkHour');
                    console.log('Setting Flatpickr #startWorkHour initial value from Livewire:',
                        livewireStartHour);
                    startWorkHourInput._flatpickr.setDate(livewireStartHour,
                    false); // false = don't trigger onClose
                } else if (startWorkHourInput && startWorkHourInput._flatpickr) {
                    // Clear Flatpickr if Livewire property doesn't exist or is null
                    startWorkHourInput._flatpickr.clear();
                    console.log('Clearing Flatpickr #startWorkHour.');
                }


                const endWorkHourInput = document.querySelector('#endWorkHour');
                // Check for Flatpickr instance presence before setting date
                if (endWorkHourInput && endWorkHourInput._flatpickr && @this.has('endWorkHour') && @this
                    .get('endWorkHour')) {
                    const livewireEndHour = @this.get('endWorkHour');
                    console.log('Setting Flatpickr #endWorkHour initial value from Livewire:',
                        livewireEndHour);
                    endWorkHourInput._flatpickr.setDate(livewireEndHour,
                    false); // false = don't trigger onClose
                } else if (endWorkHourInput && endWorkHourInput._flatpickr) {
                    // Clear Flatpickr if Livewire property doesn't exist or is null
                    endWorkHourInput._flatpickr.clear();
                    console.log('Clearing Flatpickr #endWorkHour.');
                }

                console.log('Initial values set from Livewire properties.');

            }, 100); // Increased delay slightly to give Select2/Flatpickr time
        });

        // --- Optional: Listen for Livewire component finishes rendering ---
        // Use this hook to re-initialize components after a Livewire rerender if the 'centerModalShown' event isn't sufficient
        // Livewire.hook('component.initialized', ({ component, commit, cleanup }) => {}); // When component is initialized
        // Livewire.hook('element.initialized', ({ el, component }) => {}); // When a new element is added by Livewire
        // Livewire.hook('element.updated', ({ el, component }) => {
        //     // Re-initialize specific elements here if needed after update,
        //     // but the 'centerModalShown' event should handle modal content updates
        // });
        // Livewire.hook('message.processed', (message, component) => {}); // After a message roundtrip

        // --- Listen for modal close and reset form in Livewire ---
        // This handles closing the modal via Alpine or other means
        centerModalElement.addEventListener('hide.bs.modal', function() { // If Bootstrap JS is still used for hiding
            @this.call('resetForm'); // Call Livewire method to reset form state
            console.log('Modal hidden, calling Livewire resetForm.');
        });

        // If only using Alpine/Livewire for hiding, listen to the Alpine 'show' variable changing
        //  Alpine.effect(() => {
        //     if (!Alpine.store('show')) { // Assuming 'showModal' is stored in Alpine store or accessible
        //         @this.call('resetForm');
        //         console.log('Alpine show is false, calling Livewire resetForm.');
        //     }
        // });
    </script>
@endpush
