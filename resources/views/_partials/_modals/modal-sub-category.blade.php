{{-- resources\views\_partials\_modals\modal-sub-category.blade.php --}}

{{-- Push custom CSS specific to this modal (none needed in this case, but stack remains) --}}
@push('custom-css')
    {{-- Add any custom CSS here --}}
@endpush

{{--
    Bootstrap Modal Structure for SubCategory Management
    wire:ignore.self: Essential for Livewire to leave Bootstrap's modal handling to Bootstrap JS.
    id: The ID used by Bootstrap to target the modal.
    tabindex="-1": Makes the modal accessible.
    aria-hidden="true": Hides the modal from assistive technologies when closed.
--}}
<div wire:ignore.self class="modal fade" id="subCategoryModal" tabindex="-1" aria-hidden="true">
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
                    <h3 class="mb-2">{{ $isEdit ? __('Update SubCategory') : __('New SubCategory') }}</h3>
                    <p class="text-muted">{{ __('Please fill out the following information') }}</p>
                </div>

                {{--
                    Form for SubCategory Submission
                    wire:submit.prevent="submitSubCategory": Calls the 'submitSubCategory' method on the Livewire component when the form is submitted.
                                                    .prevent is crucial to stop default browser form submission.
                    row g-3: Bootstrap grid classes for form layout.
                --}}
                <form wire:submit.prevent="submitSubCategory" class="row g-3"> {{-- Added .prevent to wire:submit --}}

                    {{-- SubCategory Name Input Field --}}
                    <div class="col-12 mb-4">
                        <label class="form-label w-100" for="subCategoryName">{{ __('Name') }}</label>
                        {{-- Added 'for' attribute for accessibility --}}
                        {{--
                            wire:model='subCategoryName': Binds input value to the 'subCategoryName' property in the Livewire component.
                            @error(...): Adds validation styling.
                        --}}
                        <input wire:model='subCategoryName' id="subCategoryName" {{-- Added ID matching 'for' --}}
                            class="form-control @error('subCategoryName') is-invalid @enderror" type="text"
                            placeholder="{{ __('Enter subcategory name') }}" {{-- Added placeholder --}} />
                        {{-- 👇 ADDED: Display Validation Error Message for Name 👇 --}}
                        @error('subCategoryName')
                            <div class="invalid-feedback"> {{-- Using invalid-feedback for consistency --}}
                                {{ $message }}
                            </div>
                        @enderror
                        {{-- ☝️ END ADDED ☝️ --}}
                    </div>

                    {{-- Add other form fields here if needed --}}
                    {{-- If a subcategory belongs to a category, you'd add a select dropdown here: --}}
                    {{--
                    <div class="col-12 mb-4">
                         <label class="form-label w-100" for="parentCategory">{{ __('Parent Category') }}</label>
                         <select wire:model='category_id' id="parentCategory" class="form-select @error('category_id') is-invalid @enderror">
                             <option value="">{{ __('Select Parent Category') }}</option>
                             @foreach ($categories as $category) // Assuming $categories is available in component
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
                            <span wire:loading.remove wire:target="submitSubCategory">{{ __('Submit') }}</span>
                            {{-- Optional spinner while submitting --}}
                            <span wire:loading wire:target="submitSubCategory" class="spinner-border spinner-border-sm"
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
    {{-- Add any custom JS here (e.g., Select2 initialization if you add a parent category select) --}}
    <script>
        'use strict';

        // Example: Add an event listener to clear the form when the modal is hidden
        const subCategoryModalElement = document.getElementById('subCategoryModal');
        if (subCategoryModalElement) {
            // Add event listener to focus on the first input when modal is shown
            subCategoryModalElement.addEventListener('shown.bs.modal', function(event) {
                const subCategoryNameInput = document.getElementById('subCategoryName');
                if (subCategoryNameInput) {
                    subCategoryNameInput.focus();
                }
            });


            // Add event listener to clear the form when the modal is hidden
            subCategoryModalElement.addEventListener('hidden.bs.modal', function() {
                console.log('SubCategory Modal hidden, attempting to reset form...');
                // Call a public method in your Livewire component to reset properties and errors.
                // Make sure your Livewire component has a method named 'resetForm'.
                @this.call('resetForm'); // Assuming your component has a resetForm method
            });
        } else {
            console.error('SubCategory modal element #subCategoryModal not found.');
        }
    </script>
@endpush
