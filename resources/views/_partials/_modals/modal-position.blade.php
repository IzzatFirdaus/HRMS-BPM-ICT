{{-- resources\views\_partials\_modals\modal-position.blade.php --}}

{{-- Push custom CSS specific to this modal (none needed in this case, but stack remains) --}}
@push('custom-css')
    {{-- Add any custom CSS here --}}
@endpush

{{--
    Bootstrap Modal Structure for Position Management
    wire:ignore.self: Essential for Livewire to leave Bootstrap's modal handling to Bootstrap JS.
    id: The ID used by Bootstrap to target the modal.
    tabindex="-1": Makes the modal accessible.
    aria-hidden="true": Hides the modal from assistive technologies when closed.
--}}
<div wire:ignore.self class="modal fade" id="positionModal" tabindex="-1" aria-hidden="true">
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
                    {{-- Dynamic Title based on $isEdit Livewire property --}}
                    <h3 class="mb-2">{{ $isEdit ? __('Update Position') : __('New Position') }}</h3>
                    <p class="text-muted">{{ __('Please fill out the following information') }}</p>
                </div>

                {{--
                    Form for Position Submission
                    wire:submit.prevent="submitPosition": Calls the 'submitPosition' method on the Livewire component when the form is submitted.
                                                    .prevent is crucial to stop default browser form submission.
                    row g-3: Bootstrap grid classes for form layout.
                --}}
                <form wire:submit.prevent="submitPosition" class="row g-3"> {{-- Added .prevent to wire:submit --}}

                    {{-- Position Name Input Field --}}
                    {{-- 👇 REFACTORED: Each input field in its own column/div 👇 --}}
                    <div class="col-12 mb-4">
                        <label class="form-label w-100" for="positionName">{{ __('Name') }}</label>
                        {{-- Added 'for' attribute --}}
                        {{--
                            wire:model='name': Binds input value to the 'name' property.
                            @error(...): Adds validation styling.
                        --}}
                        <input wire:model='name' id="positionName" {{-- Added ID matching 'for' --}}
                            class="form-control @error('name') is-invalid @enderror" type="text"
                            placeholder="{{ __('Enter position name') }}" {{-- Added placeholder --}} />
                        {{-- 👇 ADDED: Display Validation Error Message for Name 👇 --}}
                        @error('name')
                            <div class="invalid-feedback"> {{-- Using invalid-feedback for consistency --}}
                                {{ $message }}
                            </div>
                        @enderror
                        {{-- ☝️ END ADDED ☝️ --}}
                    </div>

                    {{-- Vacancies Count Input Field --}}
                    {{-- 👇 REFACTORED: Each input field in its own column/div 👇 --}}
                    <div class="col-12 mb-4">
                        <label class="form-label w-100" for="vacanciesCount">{{ __('Vacancies Count') }}</label>
                        {{-- Added 'for' attribute --}}
                        {{--
                             wire:model='vacanciesCount': Binds input value to the 'vacanciesCount' property.
                             min="1": HTML5 validation (basic browser validation).
                             @error(...): Adds validation styling.
                         --}}
                        <input wire:model='vacanciesCount' id="vacanciesCount" {{-- Added ID matching 'for' --}}
                            class="form-control @error('vacanciesCount') is-invalid @enderror" type="number"
                            min="0" {{-- Changed min to 0, as a position can have 0 vacancies --}} placeholder="{{ __('e.g., 5') }}"
                            {{-- Added placeholder --}} />
                        {{-- 👇 ADDED: Display Validation Error Message for Vacancies Count 👇 --}}
                        @error('vacanciesCount')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                        {{-- ☝️ END ADDED ☝️ --}}
                    </div>

                    {{-- Add other form fields here if needed --}}

                    {{-- Submit and Cancel Buttons --}}
                    <div class="col-12 text-center">
                        {{-- Submit Button --}}
                        <button type="submit" class="btn btn-primary me-sm-3 me-1">
                            {{-- Display different text while submitting --}}
                            <span wire:loading.remove wire:target="submitPosition">{{ __('Submit') }}</span>
                            {{-- Optional spinner while submitting --}}
                            <span wire:loading wire:target="submitPosition" class="spinner-border spinner-border-sm"
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

{{-- Push custom JavaScript specific to this modal (none needed in this case, but stack remains) --}}
@push('custom-scripts')
    {{-- Add any custom JS here --}}
    <script>
        'use strict';

        // Example: Add an event listener to clear the form when the modal is hidden
        const positionModalElement = document.getElementById('positionModal');
        if (positionModalElement) {
            // Add event listener to focus on the first input when modal is shown
            positionModalElement.addEventListener('shown.bs.modal', function(event) {
                const positionNameInput = document.getElementById('positionName');
                if (positionNameInput) {
                    positionNameInput.focus();
                }
            });

            // Add event listener to clear the form when the modal is hidden
            positionModalElement.addEventListener('hidden.bs.modal', function() {
                console.log('Position Modal hidden, attempting to reset form...');
                // Call a public method in your Livewire component to reset properties and errors.
                // Make sure your Livewire component (e.g., Positions.php) has a method named 'resetForm'.
                @this.call('resetForm'); // Assuming your component has a resetForm method
            });
        } else {
            console.error('Position modal element #positionModal not found.');
        }
    </script>
@endpush
