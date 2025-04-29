{{--
    resources/views/livewire/human-resource/structure/departments.blade.php

    This view displays a table of departments and includes Livewire actions for management.
    It is assumed this view is rendered by a Livewire component class.
    The layout is handled by a parent layout file that renders this component.
    (Note: The original code included @extends and @section directives, which are typically
     found in parent layout files, not Livewire component views. The edited code below
     assumes this file is the content *inside* a @section('content') in a parent layout.)
--}}

{{-- The content starts directly here for a Livewire component view --}}

<div class="demo-inline-spacing">
    {{-- Button to open the modal for adding a new department --}}
    {{-- wire:click.prevent targets a public method in the Livewire component --}}
    <button wire:click.prevent='showNewDepartmentModal' type="button" class="btn btn-primary" data-bs-toggle="modal"
        data-bs-target="#departmentModal">
        {{-- Icons from a library like Tabler Icons (ti) --}}
        <span class="ti-xs ti ti-plus me-1"></span>{{ __('Add New Department') }} {{-- Translated button text --}}
    </button>
</div>

<br>

<div class="card"> {{-- Bootstrap card for styling the table container --}}
    {{-- Card header with icon and title --}}
    <h5 class="card-header">
        <i class="ti ti-building ti-lg text-info me-3"></i> {{-- Bootstrap classes and icon --}}
        {{ __('Departments') }} {{-- Translated header text --}}
    </h5>

    <div class="table-responsive text-nowrap"> {{-- Bootstrap class for responsive table scrolling --}}
        <table class="table"> {{-- Bootstrap table class --}}
            <thead>
                <tr>
                    {{-- Table headers with translation --}}
                    <th>{{ __('ID') }}</th>
                    <th>{{ __('Name') }}</th>
                    {{-- <th>Coordinator</th> --}} {{-- Commented out column --}}
                    <th>{{ __('Members Count') }}</th>
                    <th>{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody class="table-border-bottom-0"> {{-- Bootstrap table class --}}
                {{-- Loop through the departments passed from the Livewire component --}}
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
                            {{-- FIX: Access members count directly from the department model --}}
                            {{-- This assumes the $departments collection was loaded in the Livewire component's render method
                               using Eloquent's withCount('members') method.
                               E.g., Department::withCount('members')->get(); --}}
                            {{ $department->members_count ?? 0 }} {{-- Accesses the count added by withCount, defaults to 0 if not found --}}
                        </td>

                        <td>
                            {{-- Action buttons/dropdown for each department --}}
                            {{-- Uses a mix of inline style and Bootstrap classes --}}
                            <div style="display: flex"> {{-- Consider using Bootstrap 'd-flex' for consistency --}}
                                <div class="dropdown"> {{-- Bootstrap dropdown container --}}
                                    {{-- Dropdown toggle button with icon --}}
                                    <button type="button" class="btn p-0 dropdown-toggle hide-arrow"
                                        data-bs-toggle="dropdown">
                                        <i class="ti ti-dots-vertical"></i> {{-- Icon --}}
                                    </button>
                                    <div class="dropdown-menu"> {{-- Bootstrap dropdown menu container --}}
                                        {{-- Edit action, triggers Livewire method and opens modal --}}
                                        <a wire:click.prevent='showEditDepartmentModal({{ $department->id }})'
                                            {{-- Pass ID to Livewire method --}} data-bs-toggle="modal"
                                            data-bs-target="#departmentModal" class="dropdown-item" href="#">
                                            {{-- Bootstrap dropdown item class --}}
                                            <i class="ti ti-pencil me-1"></i> {{-- Icon --}}
                                            {{ __('Edit') }} {{-- Translated text --}}
                                        </a>
                                        {{-- Delete action, triggers Livewire confirmation method --}}
                                        <a wire:click.prevent='confirmDeleteDepartment({{ $department->id }})'
                                            class="dropdown-item" href="#"> {{-- Bootstrap dropdown item class --}}
                                            <i class="ti ti-trash me-1"></i> {{-- Icon --}}
                                            {{ __('Delete') }} {{-- Translated text --}}
                                        </a>
                                    </div>
                                </div>
                                {{-- Conditional Delete Confirmation Button --}}
                                {{-- Appears only when $confirmedId matches the current department's ID --}}
                                {{-- Triggers the final delete method --}}
                                @if (isset($confirmedId) && $confirmedId === $department->id)
                                    {{-- Added isset check for safety --}}
                                    {{-- Note: wire:click='deleteDepartment' implies the method uses the stored $confirmedId --}}
                                    <button wire:click.prevent='deleteDepartment' type="button"
                                        class="btn btn-sm btn-danger waves-effect waves-light ms-2">
                                        {{-- Bootstrap classes, added ms-2 for spacing --}}
                                        Sure? {{-- Confirmation text --}}
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    {{-- Message displayed when no departments are found --}}
                    <tr>
                        {{-- Colspan spans across all columns (currently 4 visible: ID, Name, Count, Actions) --}}
                        <td colspan="4">
                            <div class="mt-2 mb-2 text-center"> {{-- Changed inline style to Bootstrap text-center --}}
                                <h3 class="mb-1 mx-2">{{ __('Oopsie-doodle!') }}</h3> {{-- Translated text --}}
                                <p class="mb-4 mx-2">
                                    {{ __('No data found, please sprinkle some data in my virtual bowl, and let the fun begin!') }}
                                    {{-- Translated text --}}
                                </p>
                                {{-- Button to add a new department in the empty state --}}
                                <button wire:click.prevent='showNewDepartmentModal' class="btn btn-label-primary mb-4"
                                    data-bs-toggle="modal" data-bs-target="#departmentModal"> {{-- Bootstrap classes, added wire:click --}}
                                    {{ __('Add New Department') }} {{-- Translated button text --}}
                                </button>
                                {{-- Illustration image --}}
                                <div>
                                    <img src="{{ asset('assets/img/illustrations/page-misc-under-maintenance.png') }}"
                                        width="200" class="img-fluid"> {{-- Bootstrap class --}}
                                </div>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Include the modal partial --}}
{{-- This modal is likely controlled by the Livewire component's properties and methods --}}
@include('_partials/_modals/modal-department')

{{-- Note: If this is a Livewire component view, the outer div and layout directives
   (@extends, @section, @php) should typically be in a parent layout file.
   The Livewire component view provides only the HTML content for the component itself.
   The provided code appears to mix standard Blade layout structure with Livewire component content.
   Assuming the goal was a Livewire component view, the layout directives would be removed.
   However, based on the provided code block, only the "$this->" call is directly fixed here.
--}}

{{-- The outer div wrapping the content (removed if this is intended as a pure Livewire component view) --}}
{{-- </div> --}}
