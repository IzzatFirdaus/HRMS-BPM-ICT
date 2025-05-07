{{--
    resources/views/livewire/human-resource/structure/departments.blade.php

    This view displays a table of departments and includes Livewire actions for management.
    It is assumed this view is rendered by a Livewire component class.
    The layout is handled by a parent layout file that renders this component.
    (Note: The original code included @extends and @section directives, which are typically
     found in parent layout files, not Livewire component views. The edited code below
     assumes this file is the content *inside* a @section('content') in a parent layout.)
--}}

{{-- FIX: Wrap all content in a single root element for Livewire --}}
<div class="departments-container"> {{-- Use a single wrapping div --}}

    <div class="demo-inline-spacing">
        {{-- Button to open the modal for adding a new department --}}
        <button wire:click.prevent='showNewDepartmentModal' type="button" class="btn btn-primary" data-bs-toggle="modal"
            data-bs-target="#departmentModal">
            <span class="ti-xs ti ti-plus me-1"></span>{{ __('Add New Department') }} {{-- Translated button text --}}
        </button>
    </div>

    <br> {{-- Keep the br tag, but it's now inside the root div --}}

    <div class="card"> {{-- Bootstrap card for styling the table container --}}
        <h5 class="card-header">
            <i class="ti ti-building ti-lg text-info me-3"></i> {{-- Bootstrap classes and icon --}}
            {{ __('Departments') }} {{-- Translated section title --}}
        </h5>

        <div class="table-responsive text-nowrap"> {{-- Bootstrap responsive table wrapper --}}
            <table class="table"> {{-- Bootstrap table class --}}
                <thead>
                    <tr>
                        <th>{{ __('ID') }}</th>
                        <th>{{ __('Name') }}</th>
                        {{-- <th>Coordinator</th> --}} {{-- Commented out column --}}
                        <th>{{ __('Members Count') }}</th>
                        <th>{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="table-border-bottom-0"> {{-- Bootstrap table class --}}
                    {{-- Loop through the departments passed from the Livewire component --}}
                    {{-- Assumes $departments variable is available from the Livewire component --}}
                    @forelse($departments as $department)
                        <tr>
                            {{-- Display department data --}}
                            <td>{{ $department->id }}</td>
                            <td><strong>{{ $department->name }}</strong></td>
                            {{-- Commented out Coordinator cell --}}
                            {{-- <td>
                            <ul class="list-unstyled users-list m-0 avatar-group d-flex align-items-center">
                                <li class="avatar avatar-xs pull-up"> Name 2
                                    <a href="#">
                                        <img src="{{asset('assets/img/avatars/5.png')}}" alt="Avatar" class="rounded-circle">

                                    </a>
                                </li>
                            </ul>
                        </td> --}}

                            <td>
                                {{-- Access members count directly from the department model using the withCount result --}}
                                {{ $department->users_count }} {{-- Assumes 'users' relationship and withCount in component --}}
                            </td>

                            <td>
                                {{-- Action buttons for editing and deleting --}}
                                {{-- wire:click.prevent targets methods in the Livewire component --}}
                                <div class="dropdown"> {{-- Bootstrap dropdown for action buttons --}}
                                    <button type="button" class="btn p-0 dropdown-toggle hide-arrow"
                                        data-bs-toggle="dropdown"><i class="ti ti-dots-vertical"></i></button>
                                    <div class="dropdown-menu">
                                        {{-- Edit button --}}
                                        {{-- Pass the department model to the showEditDepartmentModal method --}}
                                        <a class="dropdown-item" href="#"
                                            wire:click.prevent="showEditDepartmentModal({{ $department }})"
                                            data-bs-toggle="modal" data-bs-target="#departmentModal"><i
                                                class="ti ti-pencil me-1"></i>
                                            {{ __('Edit') }}</a>
                                        {{-- Delete button --}}
                                        {{-- Use confirmDeletion method to set ID before showing a confirmation modal --}}
                                        <a class="dropdown-item" href="#"
                                            wire:click.prevent="confirmDeletion({{ $department->id }})">
                                            <i class="ti ti-trash me-1"></i> {{ __('Delete') }}
                                        </a>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        {{-- Message when no departments are found --}}
                        <tr>
                            <td colspan="4" class="text-center py-4">
                                <div class="d-flex flex-column align-items-center">
                                    <p>{{ __('No departments found.') }}</p>
                                    {{-- Optional: Add an illustration or icon --}}
                                    <div class="mt-3">
                                        <img src="{{ asset('assets/img/illustrations/nothing-found.png') }}"
                                            alt="{{ __('No results found') }}" width="150">
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination links --}}
        {{-- Assumes $departments is a paginated collection --}}
        @if ($departments instanceof \Illuminate\Pagination\LengthAwarePaginator)
            {{-- Check if it's a paginator --}}
            <div class="mt-3 px-4"> {{-- Add padding/margin for pagination --}}
                {{ $departments->links() }} {{-- Renders pagination links (styled by paginationTheme in component) --}}
            </div>
        @endif

    </div> {{-- End card --}}

    {{-- Include the modal partial for add/edit --}}
    {{-- This modal is likely controlled by the Livewire component's properties ($showModal) and methods --}}
    {{-- Ensure the modal partial itself has a single root element inside it --}}
    @include('_partials._modals.modal-department')

    {{-- Include a confirmation modal partial if you use a separate one for deletion --}}
    {{-- @include('_partials/_modals/modal-confirm-delete') --}}


</div> {{-- End of the single wrapping div --}}

{{-- The outer div wrapping the content (removed if this is intended as a pure Livewire component view) --}}
{{-- </div> --}}
