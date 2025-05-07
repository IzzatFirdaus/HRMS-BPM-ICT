{{-- resources/views/livewire/human-resource/structure/employees.blade.php --}}
{{-- This view displays a table of employees and includes Livewire search and pagination. --}}

<div> {{-- Livewire component root element --}}

    @php
        $configData = Helper::appClasses();
        // Carbon is used in the component, but not directly in this view snippet. Keep if needed elsewhere.
        // use Carbon\Carbon;
    @endphp

    @section('title', 'Employees - Structure')

    @section('page-style')
        <style>
            /* Styles for hover effects on action buttons */
            .btn-tr {
                opacity: 0;
            }

            tr:hover .btn-tr {
                display: inline-block;
                opacity: 1;
            }

            /* Optional: Style for text cells on row hover */
            /* tr:hover .td { color: #7367f0 !important; } */
        </style>
    @endsection

    {{-- Add New Employee Button --}}
    <div class="demo-inline-spacing">
        {{-- Assuming showCreateEmployeeModal exists in the component and a modal with ID #employeeModal exists --}}
        <button wire:click='showCreateEmployeeModal' type="button" class="btn btn-primary" data-bs-toggle="modal"
            data-bs-target="#employeeModal">
            <span class="ti-xs ti ti-plus me-1"></span>{{ __('Add New Employee') }}
        </button>
    </div>

    <div class="card mt-3"> {{-- Bootstrap card for the table container --}}
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title m-0 me-2">{{ __('Employees') }}</h5>
            <div class="col-md-4 col-12">
                {{-- Search input - Bind to searchTerm public property --}}
                <input wire:model.live="searchTerm" autofocus type="text" class="form-control"
                    placeholder="{{ __('Search (ID, Name...)') }}">
            </div>
        </div>

        <div class="table-responsive text-nowrap">
            <table class="table">
                <thead>
                    <tr>
                        <th class="col-1">{{ __('ID') }}</th>
                        <th class="col-5">{{ __('Name') }}</th>
                        <th class="col-2">{{ __('Mobile') }}</th>
                        <th class="col-2">{{ __('Status') }}</th>
                        <th class="col-2">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="table-border-bottom-0">
                    {{-- Loop through the employees - Access via the computed property $this->employees --}}
                    @forelse($this->employees as $employee)
                        <tr>
                            <td>{{ $employee->id }}</td>
                            <td>
                                <ul class="list-unstyled users-list m-0 avatar-group d-flex align-items-center">
                                    <li class="avatar avatar-xs pull-up">
                                        {{-- Display profile photo using the profile_photo_url accessor --}}
                                        <a href="{{ route('structure-employees-info', $employee->id) }}">
                                            <img src="{{ $employee->profile_photo_url }}" alt="Avatar"
                                                class="rounded-circle">
                                            {{ $employee->full_name }}
                                        </a>
                                    </li>
                                </ul>
                            </td>
                            {{-- Display mobile number with formatting --}}
                            <td style="direction: ltr">
                                {{ '+963 ' . number_format((int) str_replace(' ', '', $employee->mobile_number ?? 0), 0, '', ' ') }}
                            </td>
                            {{-- Display status badge --}}
                            <td><span
                                    class="badge bg-label-{{ $employee->status == 'active' ? 'success' : 'danger' }}">{{ $employee->status }}</span>
                            </td>

                            <td>
                                <div class="d-flex align-items-center">
                                    {{-- View button --}}
                                    <a href="{{ route('structure-employees-info', $employee->id) }}"
                                        class="btn btn-sm btn-icon item-edit me-2" title="{{ __('View') }}"><i
                                            class="ti ti-eye text-info"></i></a>
                                    {{-- TODO: Add Edit button if applicable (e.g., <button wire:click='showEditEmployeeModal({{ $employee->id }})' class="btn btn-sm btn-icon item-edit me-2" title="{{ __('Edit') }}"><i class="ti ti-pencil text-primary"></i></button>) --}}
                                    {{-- Delete button - Triggers confirmation state --}}
                                    <button wire:click.prevent='confirmDeleteEmployee({{ $employee->id }})'
                                        type="button" class="btn btn-sm btn-icon item-delete"
                                        title="{{ __('Delete') }}">
                                        <i class="ti ti-trash text-danger"></i>
                                    </button>
                                    {{-- Show confirmation button only if this employee's ID is confirmed --}}
                                    @if ($confirmedId === $employee->id)
                                        <button wire:click.prevent='deleteEmployee' type="button"
                                            class="btn btn-sm btn-danger waves-effect waves-light ms-2">{{ __('Sure?') }}</button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        {{-- Message when no employees are found --}}
                        <tr>
                            <td colspan="5">
                                <div class="mt-2 mb-2 text-center">
                                    <h3 class="mb-1 mx-2">{{ __('Oopsie-doodle!') }}</h3>
                                    <p class="mb-4 mx-2">
                                        {{ __('No employee data found matching your criteria.') }}
                                    </p>
                                    {{-- TODO: Add button to clear filters or add new employee --}}
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination links --}}
        @if ($this->employees instanceof \Illuminate\Pagination\LengthAwarePaginator)
            <div class="mt-3 px-4">
                {{ $this->employees->links() }}
            </div>
        @endif

    </div> {{-- End card --}}

    {{-- TODO: Include modal partials here if needed (e.g., modal-employee for add/edit) --}}
    {{-- @include('_partials._modals.modal-employee') --}}

</div> {{-- End Livewire component root element --}}
