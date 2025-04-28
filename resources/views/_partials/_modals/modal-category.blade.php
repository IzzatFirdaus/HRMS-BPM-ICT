{{-- This Blade partial represents a Livewire modal component for creating or updating categories. --}}

@push('custom-css')
    {{-- Add any custom CSS specific to this modal/component here --}}
@endpush

{{--
    Bootstrap Modal Structure
    wire:ignore.self: Prevents Livewire from touching the modal's root element, allowing Bootstrap's JS to control it.
    id: The ID used by Bootstrap to target the modal.
    tabindex="-1": Makes the modal accessible.
    aria-hidden="true": Hides the modal from assistive technologies when closed.
--}}
<div wire:ignore.self class="modal fade" id="categoryModal" tabindex="-1" aria-hidden="true">
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
                    {{-- Dynamic Title: Displays "Update Category" or "New Category" based on the $isEdit Livewire property --}}
                    <h3 class="mb-2">{{ $isEdit ? __('Update Category') : __('New Category') }}</h3>
                    <p class="text-muted">{{ __('Please fill out the following information') }}</p>
                </div>

                {{--
                    Form for Category Submission
                    wire:submit="submitCategory": Calls the 'submitCategory' method on the Livewire component when the form is submitted.
                    row g-3: Bootstrap grid classes for form layout.
                --}}
                <form wire:submit.prevent="submitCategory" class="row g-3"> {{-- Added .prevent to wire:submit --}}

                    {{-- Category Name Input Field --}}
                    <div class="col-12 mb-4">
                        <label class="form-label w-100" for="categoryName">{{ __('Name') }}</label>
                        {{-- Added 'for' attribute for accessibility --}}
                        {{--
                            wire:model='categoryName': Binds the input value to the 'categoryName' public property in the Livewire component.
                            @error('categoryName') is-invalid @enderror: Adds the 'is-invalid' Bootstrap class if there's a validation error for 'categoryName'.
                        --}}
                        <input wire:model='categoryName' id="categoryName" {{-- Added ID matching the label's 'for' attribute --}}
                            class="form-control @error('categoryName') is-invalid @enderror" type="text"
                            placeholder="{{ __('Enter category name') }}" {{-- Added placeholder --}} />

                        {{-- 👇 ADDED: Display Validation Error Message 👇 --}}
                        {{--
                            @error('categoryName') ... @enderror: Blade directive that only renders the content
                            if there is a validation error for the 'categoryName' field.
                            $message: A special variable available within the @error directive, containing the first error message for the field.
                        --}}
                        @error('categoryName')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                        {{-- ☝️ END ADDED ☝️ --}}
                    </div>

                    {{-- Add other form fields here if needed (e.g., description, etc.) --}}
                    {{-- Example:
                    <div class="col-12 mb-4">
                        <label class="form-label w-100" for="categoryDescription">{{ __('Description') }}</label>
                        <textarea wire:model='categoryDescription' id="categoryDescription" class="form-control @error('categoryDescription') is-invalid @enderror"></textarea>
                         @error('categoryDescription')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    --}}

                    {{-- Submit and Cancel Buttons --}}
                    <div class="col-12 text-center">
                        {{-- Submit Button --}}
                        <button type="submit" class="btn btn-primary me-sm-3 me-1">
                            {{-- Display different text while submitting --}}
                            <span wire:loading.remove wire:target="submitCategory">{{ __('Submit') }}</span>
                            <span wire:loading wire:target="submitCategory" class="spinner-border spinner-border-sm"
                                role="status" aria-hidden="true"></span> {{-- Optional spinner while submitting --}}
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

@push('custom-scripts')
    {{-- Add any custom JavaScript specific to this modal/component here --}}
    <script>
        // Example: Clear the form when the modal is hidden (closed)
        // Assuming your Livewire component has a public method called 'resetForm' or similar
        // that clears the categoryName property and resets validation errors.
        // The event 'hidden.bs.modal' is a Bootstrap event.
        document.getElementById('categoryModal').addEventListener('hidden.bs.modal', function(event) {
            // You might emit a Livewire event or call a Livewire method here
            // Example using Livewire.dispatch:
            // Livewire.dispatch('modalClosed'); // Add a listener for 'modalClosed' in your Livewire component
            // Or if targeting a specific component instance (more complex):
            // @this.call('resetForm'); // This syntax might vary slightly based on Livewire version/setup
            // A simpler approach is often handled directly in the Livewire component's JS or listeners
        });

        // Example: Focus on the first input when the modal is shown
        document.getElementById('categoryModal').addEventListener('shown.bs.modal', function(event) {
            document.getElementById('categoryName').focus();
        });
    </script>
@endpush
