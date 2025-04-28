{{-- resources\views\_partials\_modals\modal-inventory.blade.php --}}

{{-- Push custom CSS for external libraries and custom styles --}}
@push('custom-css')
    {{-- Link to Select2 CSS (used for potential future dropdowns) --}}
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/select2/select2.css') }}" />
    {{-- Link to Flatpickr CSS (used for date pickers) --}}
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/flatpickr/flatpickr.css') }}" />

    <style>
        /* Custom CSS to hide the default spin buttons on number input fields */
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

        /* Optional: Adjustments for Select2 in Bootstrap modals if needed */
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
    Bootstrap Modal Structure for Asset/Equipment Management
    wire:ignore.self: Essential for Livewire to leave Bootstrap's modal handling to Bootstrap JS.
    id: The ID used by Bootstrap to target the modal.
    tabindex="-1": Makes the modal accessible.
    aria-hidden="true": Hides the modal from assistive technologies when closed.
    modal-xl: Makes the modal extra large.
--}}
<div wire:ignore.self class="modal fade" id="assetModal" tabindex="-1" aria-hidden="true">
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
                    <h3 class="mb-2">{{ $isEdit ? __('Update Asset') : __('New Asset') }}</h3>
                    <p class="text-muted">{{ __('Please fill out the following information') }}</p>
                </div>

                {{--
                    Form for Asset Submission
                    wire:submit.prevent="submitAsset": Calls the 'submitAsset' method on the Livewire component when the form is submitted.
                                                     .prevent is crucial to stop default browser form submission.
                    row g-3: Bootstrap grid classes for form layout.
                --}}
                <form wire:submit.prevent="submitAsset" class="row g-3"> {{-- Added .prevent to wire:submit --}}

                    {{-- Asset Tag ID (Number Input) --}}
                    <div class="col-md-2 col-12"> {{-- Removed mb-4 for consistency in this row --}}
                        <label class="form-label w-100" for="assetId">{{ __('Tag ID') }}</label>
                        {{-- Added 'for' attribute --}}
                        {{--
                            wire:model='assetId': Binds input value to 'assetId'.
                            @error(...): Adds validation styling. Note: Livewire rules don't include 'assetId', but error check remains.
                        --}}
                        <input wire:model='assetId' id="assetId" {{-- Added ID matching 'for' --}}
                            class="form-control @error('assetId') is-invalid @enderror" type="number"
                            placeholder="{{ __('e.g., 1001') }}" {{-- Added placeholder --}} />
                        {{-- Display Validation Error Message for Asset ID --}}
                        @error('assetId')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    {{-- Old ID (Number Input) --}}
                    <div class="col-md-2 col-12"> {{-- Removed mb-4 --}}
                        <label class="form-label w-100" for="oldId">{{ __('Old ID') }}</label>
                        {{-- Added 'for' attribute --}}
                        <input wire:model='oldId' id="oldId" {{-- Added ID matching 'for' --}}
                            class="form-control @error('oldId') is-invalid @enderror" type="number"
                            placeholder="{{ __('e.g., 500') }}" {{-- Added placeholder --}} />
                        {{-- Display Validation Error Message for Old ID --}}
                        @error('oldId')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    {{-- Serial Number (Text Input) --}}
                    <div class="col-md-2 col-12"> {{-- Removed mb-4 --}}
                        <label class="form-label w-100" for="serialNumber">{{ __('Serial Number') }}</label>
                        {{-- Added 'for' attribute --}}
                        <input wire:model='serialNumber' id="serialNumber" {{-- Added ID matching 'for' --}}
                            class="form-control @error('serialNumber') is-invalid @enderror" type="text"
                            placeholder="{{ __('Enter serial number') }}" {{-- Added placeholder --}} />
                        {{-- Display Validation Error Message for Serial Number --}}
                        @error('serialNumber')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    {{-- 👇 ADDED: Asset Type (Text Input - based on Livewire rule) 👇 --}}
                    <div class="col-md-2 col-12">
                        <label class="form-label w-100" for="assetType">{{ __('Asset Type') }}</label>
                        <input wire:model='assetType' id="assetType"
                            class="form-control @error('assetType') is-invalid @enderror" type="text"
                            placeholder="{{ __('e.g., Laptop, Monitor') }}" />
                        @error('assetType')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
                    {{-- ☝️ END ADDED ☝️ --}}

                    {{-- 👇 ADDED: Brand (Text Input - based on Livewire rule) 👇 --}}
                    <div class="col-md-2 col-12">
                        <label class="form-label w-100" for="brand">{{ __('Brand') }}</label>
                        <input wire:model='brand' id="brand"
                            class="form-control @error('brand') is-invalid @enderror" type="text"
                            placeholder="{{ __('e.g., Dell, HP') }}" />
                        @error('brand')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
                    {{-- ☝️ END ADDED ☝️ --}}

                    {{-- 👇 ADDED: Model (Text Input - based on Livewire rule) 👇 --}}
                    <div class="col-md-2 col-12 mb-4"> {{-- Added mb-4 to last item in row --}}
                        <label class="form-label w-100" for="modelName">{{ __('Model') }}</label>
                        <input wire:model='model_name' id="modelName"
                            class="form-control @error('model_name') is-invalid @enderror" type="text"
                            placeholder="{{ __('e.g., Latitude E7470') }}" />
                        @error('model_name')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
                    {{-- ☝️ END ADDED ☝️ --}}


                    {{-- Condition Status (Select Dropdown) --}}
                    {{-- 👇 ADDED: Added ID and for attribute, and error message display 👇 --}}
                    <div class="col-md-2 col-12">
                        <label class="form-label w-100" for="conditionStatus">{{ __('Status') }}</label>
                        <select wire:model='condition_status' id="conditionStatus"
                            class="form-select @error('condition_status') is-invalid @enderror" data-allow-clear="true">
                            <option value="">{{ __('Select Status') }}</option> {{-- Added placeholder option --}}
                            <option value="Good">{{ __('Good') }}</option>
                            <option value="Fine">{{ __('Fine') }}</option>
                            <option value="Bad">{{ __('Bad') }}</option>
                            <option value="Damaged">{{ __('Damaged') }}</option>
                        </select>
                        @error('condition_status')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
                    {{-- ☝️ END ADDED ☝️ --}}

                    {{-- Availability Status (Select Dropdown) --}}
                    {{-- 👇 ADDED: Added ID and for attribute, and error message display 👇 --}}
                    <div class="col-md-2 col-12">
                        <label class="form-label w-100" for="availabilityStatus">{{ __('Availability') }}</label>
                        <select wire:model='availabilityStatus' id="availabilityStatus"
                            class="form-select @error('availabilityStatus') is-invalid @enderror"
                            data-allow-clear="true">
                            <option value="">{{ __('Select Availability') }}</option> {{-- Added placeholder option --}}
                            <option value="available">{{ __('Available') }}</option>
                            <option value="on_loan">{{ __('On Loan') }}</option>
                            <option value="maintenance">{{ __('Maintenance') }}</option>
                            <option value="disposed">{{ __('Disposed') }}</option>
                            <option value="assigned">{{ __('Assigned') }}</option>
                        </select>
                        @error('availabilityStatus')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
                    {{-- ☝️ END ADDED ☝️ --}}


                    {{-- In Service (Select Dropdown) --}}
                    {{-- 👇 ADDED: Added ID and for attribute, and error message display 👇 --}}
                    <div class="col-md-2 col-12">
                        <label class="form-label w-100" for="inService">{{ __('In Service') }}</label>
                        <select wire:model='inService' id="inService"
                            class="form-select @error('inService') is-invalid @enderror" data-allow-clear="true">
                            <option value="">{{ __('Select Status') }}</option> {{-- Added placeholder option --}}
                            <option value="1">{{ __('Active') }}</option>
                            <option value="0">{{ __('Inactive') }}</option>
                        </select>
                        @error('inService')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
                    {{-- ☝️ END ADDED ☝️ --}}


                    {{-- Funded By (Select Dropdown) --}}
                    {{-- 👇 ADDED: Added ID and for attribute, and error message display 👇 --}}
                    <div class="col-md-2 col-12">
                        <label class="form-label w-100" for="fundedBy">{{ __('Funded By') }}</label>
                        <select wire:model='fundedBy' id="fundedBy"
                            class="form-select @error('fundedBy') is-invalid @enderror" data-allow-clear="true">
                            <option value="">{{ __('Select Source') }}</option> {{-- Added placeholder option --}}
                            <option value="By Namaa">{{ __('By Namaa') }}</option>
                            <option value="By UNHCR">{{ __('By UNHCR') }}</option>
                            <option value="By Taalouf">{{ __('By Taalouf') }}</option>
                            {{-- Add other options as needed --}}
                        </select>
                        @error('fundedBy')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
                    {{-- ☝️ END ADDED ☝️ --}}


                    {{-- Real Price (Number Input) --}}
                    {{-- 👇 ADDED: Added ID and for attribute, and error message display 👇 --}}
                    <div class="col-md-2 col-12">
                        <label class="form-label w-100" for="realPrice">{{ __('Real Price') }}</label>
                        <input wire:model='realPrice' id="realPrice" {{-- Added ID --}}
                            class="form-control @error('realPrice') is-invalid @enderror" type="number"
                            step="0.01" {{-- Allow decimal values for currency --}} placeholder="{{ __('e.g., 500.00') }}" />
                        @error('realPrice')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
                    {{-- ☝️ END ADDED ☝️ --}}

                    {{-- Expected Price (Number Input) --}}
                    {{-- 👇 ADDED: Added ID and for attribute, and error message display 👇 --}}
                    <div class="col-md-2 col-12 mb-4"> {{-- Added mb-4 to last item in row --}}
                        <label class="form-label w-100" for="expectedPrice">{{ __('Expected Price') }}</label>
                        <input wire:model='expectedPrice' id="expectedPrice" {{-- Added ID --}}
                            class="form-control @error('expectedPrice') is-invalid @enderror" type="number"
                            step="0.01" {{-- Allow decimal values --}} placeholder="{{ __('e.g., 450.00') }}" />
                        @error('expectedPrice')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
                    {{-- ☝️ END ADDED ☝️ --}}


                    {{-- Acquisition Date (Flatpickr Date Input) --}}
                    {{-- 👇 ADDED: Added ID and for attribute, and error message display 👇 --}}
                    <div class="col-md-4 col-12">
                        <label class="form-label w-100" for="acquisitionDate">{{ __('Acquisition Date') }}</label>
                        {{-- The class 'flatpickr-input' is often added by the JS initializer --}}
                        <input wire:model='acquisitionDate' type="text" id="acquisitionDate"
                            {{-- Corrected ID to match label/JS --}} class="form-control @error('acquisitionDate') is-invalid @enderror"
                            placeholder="YYYY-MM-DD" autocomplete="off" readonly="readonly" {{-- Often makes Flatpickr input non-editable directly --}} />
                        @error('acquisitionDate')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
                    {{-- ☝️ END ADDED ☝️ --}}

                    {{-- 👇 ADDED: Last Service Date (Flatpickr Date Input - based on Livewire rule) 👇 --}}
                    <div class="col-md-4 col-12">
                        <label class="form-label w-100" for="lastServiceDate">{{ __('Last Service Date') }}</label>
                        <input wire:model='lastServiceDate' type="text" id="lastServiceDate"
                            class="form-control @error('lastServiceDate') is-invalid @enderror"
                            placeholder="YYYY-MM-DD" autocomplete="off" readonly="readonly" />
                        @error('lastServiceDate')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
                    {{-- ☝️ END ADDED ☝️ --}}

                    {{-- 👇 ADDED: Next Service Date (Flatpickr Date Input - based on Livewire rule) 👇 --}}
                    <div class="col-md-4 col-12 mb-4"> {{-- Added mb-4 to last item in row --}}
                        <label class="form-label w-100" for="nextServiceDate">{{ __('Next Service Date') }}</label>
                        <input wire:model='nextServiceDate' type="text" id="nextServiceDate"
                            class="form-control @error('nextServiceDate') is-invalid @enderror"
                            placeholder="YYYY-MM-DD" autocomplete="off" readonly="readonly" />
                        @error('nextServiceDate')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
                    {{-- ☝️ END ADDED ☝️ --}}


                    {{-- Description (Text Input) --}}
                    {{-- 👇 ADDED: Added ID and for attribute, and error message display 👇 --}}
                    <div class="col-md-12 col-12"> {{-- Removed mb-4 --}}
                        <label class="form-label w-100" for="assetDescription">{{ __('Description') }}</label>
                        <input wire:model='description' id="assetDescription" {{-- Added ID --}}
                            class="form-control @error('description') is-invalid @enderror" type="text"
                            placeholder="{{ __('Brief description of the asset') }}" />
                        @error('description')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
                    {{-- ☝️ END ADDED ☝️ --}}


                    {{-- Note (Text Input) --}}
                    {{-- 👇 ADDED: Added ID and for attribute, and error message display 👇 --}}
                    <div class="col-md-12 col-12 mb-4"> {{-- Added mb-4 --}}
                        <label class="form-label w-100" for="assetNote">{{ __('Note') }}</label>
                        <input wire:model='note' id="assetNote" {{-- Added ID --}}
                            class="form-control @error('note') is-invalid @enderror" type="text"
                            placeholder="{{ __('Any additional notes') }}" />
                        @error('note')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
                    {{-- ☝️ END ADDED ☝️ --}}


                    {{-- TODO: Add fields for relationships if needed (e.g., Category, SubCategory, Department, Position, Center, Assigned Employee) --}}
                    {{-- These would likely use Select2 and require JS integration similar to the Center modal Weekends field --}}
                    {{-- Example Select2 for Category:
                    <div wire:ignore class="col-md-6 col-12 mb-4">
                         <label class="form-label" for="assetCategory">{{ __('Category') }}</label>
                         <select wire:model='category_id' id="assetCategory" class="select2 form-select @error('category_id') is-invalid @enderror">
                             <option value="">{{ __('Select Category') }}</option>
                             @foreach ($categories as $category) // Assuming $categories is available
                                 <option value="{{ $category->id }}">{{ $category->name }}</option>
                             @endforeach
                         </select>
                         @error('category_id')
                             <div class="invalid-feedback">{{ $message }}</div>
                         @enderror
                    </div>
                    --}}


                    {{-- Submit and Cancel Buttons --}}
                    <div class="col-12 text-center">
                        {{-- Submit Button --}}
                        <button type="submit" class="btn btn-primary me-sm-3 me-1">
                            {{-- Display different text while submitting --}}
                            <span wire:loading.remove wire:target="submitAsset">{{ __('Submit') }}</span>
                            {{-- Optional spinner while submitting --}}
                            <span wire:loading wire:target="submitAsset" class="spinner-border spinner-border-sm"
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
    {{-- Link to Flatpickr JS --}}
    <script src="{{ asset('assets/vendor/libs/flatpickr/flatpickr.js') }}"></script>
    {{-- Link to Select2 JS (if you added fields that use it) --}}
    <script src="{{ asset('assets/vendor/libs/select2/select2.js') }}"></script>
    {{-- Add other necessary library scripts here --}}

    <script>
        'use strict';

        // Function to initialize components that need JS (like Select2, Flatpickr) inside the modal
        // Call this function when the modal is loaded/shown if its HTML is added dynamically.
        // If the modal HTML is part of the initial page load and just hidden, $(function) is usually sufficient,
        // but re-initialization on modal show is safer if Livewire updates the modal content.
        function initializeAssetModalComponents() {
            console.log('Initializing asset modal components...');

            // Flatpickr Initialization and Livewire Integration for DATE fields

            // Acquisition Date
            const acquisitionDateInput = document.querySelector('#acquisitionDate');
            if (acquisitionDateInput) {
                // Destroy existing Flatpickr instance if it exists
                if (acquisitionDateInput._flatpickr) {
                    acquisitionDateInput._flatpickr.destroy();
                }
                acquisitionDateInput.flatpickr({
                    dateFormat: "Y-m-d", // Ensure format matches your database/backend expectation
                    // 👇 Update Livewire property on close 👇
                    onClose: function(selectedDates, dateStr, instance) {
                        if (selectedDates.length > 0) {
                            @this.set('acquisitionDate', dateStr);
                            console.log('acquisitionDate updated:', dateStr);
                        } else {
                            @this.set('acquisitionDate', null); // Set to null if cleared
                            console.log('acquisitionDate cleared');
                        }
                    }
                    // ☝️ END Update Livewire property on close ☝️
                });
                console.log('Flatpickr initialized for #acquisitionDate');
            }

            // Last Service Date
            const lastServiceDateInput = document.querySelector('#lastServiceDate');
            if (lastServiceDateInput) {
                if (lastServiceDateInput._flatpickr) {
                    lastServiceDateInput._flatpickr.destroy();
                }
                lastServiceDateInput.flatpickr({
                    dateFormat: "Y-m-d",
                    onClose: function(selectedDates, dateStr, instance) {
                        if (selectedDates.length > 0) {
                            @this.set('lastServiceDate', dateStr);
                            console.log('lastServiceDate updated:', dateStr);
                        } else {
                            @this.set('lastServiceDate', null);
                            console.log('lastServiceDate cleared');
                        }
                    }
                });
                console.log('Flatpickr initialized for #lastServiceDate');
            }

            // Next Service Date
            const nextServiceDateInput = document.querySelector('#nextServiceDate');
            if (nextServiceDateInput) {
                if (nextServiceDateInput._flatpickr) {
                    nextServiceDateInput._flatpickr.destroy();
                }
                nextServiceDateInput.flatpickr({
                    dateFormat: "Y-m-d",
                    onClose: function(selectedDates, dateStr, instance) {
                        if (selectedDates.length > 0) {
                            @this.set('nextServiceDate', dateStr);
                            console.log('nextServiceDate updated:', dateStr);
                        } else {
                            @this.set('nextServiceDate', null);
                            console.log('nextServiceDate cleared');
                        }
                    }
                });
                console.log('Flatpickr initialized for #nextServiceDate');
            }


            // TODO: Add Initialization logic for Select2 fields here if you added them.
            // Remember to destroy existing instances before re-initializing.
            // Remember to bind 'change' events to @this.set() for Select2 fields with wire:ignore.
            // Example for a Select2 field with ID 'assetCategory':
            // const assetCategorySelect = $('#assetCategory');
            // if (assetCategorySelect.length) {
            //      if (assetCategorySelect.data('select2')) { assetCategorySelect.select2('destroy'); }
            //      assetCategorySelect.select2({ placeholder: '{{ __('Select Category') }}', dropdownParent: assetCategorySelect.parent() });
            //      assetCategorySelect.on('change', function() { @this.set('category_id', $(this).val()); });
            //      console.log('Select2 initialized for #assetCategory');
            // }


            console.log('Asset modal components initialization complete.');
        }


        // Example: Clear Livewire properties when the modal is hidden (closed)
        // This helps reset the form state for the next time the modal is opened (for create or edit).
        const assetModalElement = document.getElementById('assetModal');
        if (assetModalElement) {
            // Add event listener to initialize JS components when the modal is shown
            // This is safer than $(function) if modal content is dynamic
            assetModalElement.addEventListener('shown.bs.modal', function(event) {
                console.log('Asset Modal shown event triggered, initializing components...');
                // Initialize components like Select2 and Flatpickr within the modal
                initializeAssetModalComponents();

                // Optional: Set initial values for JS components after Livewire has updated the DOM
                // You might need to use Livewire.on('modalDataLoaded') from your component
                // or Livewire.hook('dom-updated') with checks.
                // Example for Flatpickr (assuming Livewire property is already set when modal shows):
                // const acquisitionDateInput = document.querySelector('#acquisitionDate');
                // if(acquisitionDateInput && @this.get('acquisitionDate')) {
                //      acquisitionDateInput._flatpickr.setDate(@this.get('acquisitionDate'), true); // true to trigger change event if needed
                // }
                // Similarly for lastServiceDate, nextServiceDate, and Select2 fields.


                // Optional: Focus on the first input
                const firstInput = document.getElementById('assetId'); // Or the first visible input
                if (firstInput) {
                    firstInput.focus();
                }
            });

            // Add event listener to clear Livewire properties when the modal is hidden (closed)
            assetModalElement.addEventListener('hidden.bs.modal', function() {
                console.log('Asset Modal hidden, attempting to reset form...');
                // Call a public method in your Livewire component to reset properties and errors.
                // Make sure your Livewire component (e.g., Inventory.php) has a method named 'resetForm'.
                @this.call('resetForm');
            });

        } else {
            console.error('Asset modal element with ID #assetModal not found.');
        }

        // Initial call if the modal HTML is present on page load (less common with Livewire modals,
        // but included for completeness - remove if your modal is dynamically added/rendered)
        // $(function () {
        //     // If your modal is always in the DOM but hidden, you might uncomment this line.
        //     // However, initializing on 'shown.bs.modal' is often safer with Livewire.
        //     // initializeAssetModalComponents();
        // });
    </script>
@endpush
