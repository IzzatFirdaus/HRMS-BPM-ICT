{{-- resources\views\_partials\_modals\modal-departmentModal.blade.php --}}
{{-- (Assuming the file name is modal-departmentModal.blade.php based on modal ID) --}}

{{-- Push custom CSS specific to this modal (none needed in this case, but stack remains) --}}
@push('custom-css')
    {{-- Add any custom CSS here --}}
@endpush

{{--
    Bootstrap Modal Structure for Department Management
    wire:ignore.self: Essential for Livewire to leave Bootstrap's modal handling to Bootstrap JS.
    id: The ID used by Bootstrap to target the modal.
    tabindex="-1": Makes the modal accessible.
    aria-hidden="true": Hides the modal from assistive technologies when closed.
--}}
<div wire:ignore.self class="modal fade" id="departmentModal" tabindex="-1" aria-hidden="true">
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
                    {{-- Dynamic Title: Displays "Update Department" or "New Department" based on the $isEdit Livewire property --}}
                    <h3 class="mb-2">{{ $isEdit ? __('Update Department') : __('New Department') }}</h3>
                    <p class="text-muted">{{ __('Please fill out the following information') }}</p>
                </div>

                {{--
                    Form for Department Submission
                    wire:submit.prevent="submitDepartment": Calls the 'submitDepartment' method on the Livewire component when the form is submitted.
                                                    .prevent is crucial to stop default browser form submission.
                    row g-3: Bootstrap grid classes for form layout.
                --}}
                <form wire:submit.prevent="submitDepartment" class="row g-3"> {{-- Added .prevent to wire:submit --}}

                    {{-- Department Name Input Field --}}
                    <div class="col-12 mb-4">
                        <label class="form-label w-100" for="departmentName">{{ __('Name') }}</label>
                        {{-- Added 'for' attribute for accessibility --}}
                        {{--
                            wire:model='name': Binds the input value to the 'name' public property in the Livewire component.
                            @error('name') is-invalid @enderror: Adds the 'is-invalid' Bootstrap class if there's a validation error for 'name'.
                        --}}
                        <input wire:model='name' id="departmentName" {{-- Added ID matching the label's 'for' attribute --}}
                            class="form-control @error('name') is-invalid @enderror" type="text"
                            placeholder="{{ __('Enter department name') }}" {{-- Added placeholder --}} />
                        {{-- 👇 ADDED: Display Validation Error Message for Name 👇 --}}
                        @error('name')
                            {{-- Display the error message in red text below the input --}}
                            <div class="text-danger mt-1">{{ $message }}</div>
                            {{-- The commented-out invalid-feedback is an alternative Bootstrap way,
                                but text-danger mt-1 is also common and provides spacing. --}}
                            {{-- <div class="invalid-feedback">{{ $message }}</div> --}}
                        @enderror
                        {{-- ☝️ END ADDED ☝️ --}}
                    </div>

                    {{-- Add other form fields here if needed --}}

                    {{-- Submit and Cancel Buttons --}}
                    <div class="col-12 text-center">
                        {{-- Submit Button --}}
                        <button type="submit" class="btn btn-primary me-sm-3 me-1">
                            {{-- Display different text while submitting --}}
                            <span wire:loading.remove wire:target="submitDepartment">{{ __('Submit') }}</span>
                            {{-- Optional spinner while submitting --}}
                            <span wire:loading wire:target="submitDepartment" class="spinner-border spinner-border-sm"
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
        // Example: Add an event listener to clear the form when the modal is hidden
        const departmentModalElement = document.getElementById('departmentModal');
        if (departmentModalElement) {
            departmentModalElement.addEventListener('hidden.bs.modal', function() {
                console.log('Department Modal hidden, attempting to reset form...');
                // Call a public method in your Livewire component to reset properties and errors.
                // Make sure your Livewire component (e.g., Departments.php) has a method named 'resetForm' or similar.
                @this.call('resetForm');
            });

            // Example: Focus on the input when the modal is shown
            departmentModalElement.addEventListener('shown.bs.modal', function(event) {
                const departmentNameInput = document.getElementById('departmentName');
                if (departmentNameInput) {
                    departmentNameInput.focus();
                }
            });
        } else {
            console.error('Department modal element #departmentModal not found.');
        }
    </script>
@endpush
