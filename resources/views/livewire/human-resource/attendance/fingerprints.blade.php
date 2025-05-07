{{-- resources/views/livewire/human-resource/attendance/fingerprints.blade.php --}}
{{-- This view is the template for the App\Livewire\HumanResource\Attendance\Fingerprints Livewire component. --}}
{{-- It should contain the HTML structure for the attendance fingerprints management page. --}}

{{-- This file relies on the corresponding Livewire component (App\Livewire\HumanResource\Attendance\Fingerprints.php)
     to provide the necessary variables, such as $employees. --}}

<div>

    @php
        $configData = Helper::appClasses();
    @endphp

    @section('title', 'Attendance - Fingerprints')

    @section('vendor-style')
        <link rel="stylesheet" href="{{ asset('assets/vendor/libs/flatpickr/flatpickr.css') }}" />
        <link rel="stylesheet" href="{{ asset('assets/vendor/libs/select2/select2.css') }}" />
    @endsection

    @section('page-style')
        <link rel="stylesheet" href="{{ asset('assets/vendor/css/pages/app-calendar.css') }}" />
    @endsection

    {{-- Alerts --}}
    @include('_partials/_alerts/alert-general')

    <div class="card app-calendar-wrapper">
        <div class="row g-0">

            <!-- Sidebar -->
            <div class="col app-calendar-sidebar" id="app-calendar-sidebar">
                <div class="border-bottom p-3 my-sm-0 mb-3">
                    <div class="d-grid">
                        <div class="sidebar-header">
                            <div class="d-flex align-items-center me-3 me-lg-0">
                                <div wire:ignore class="col-12">
                                    <label class="form-label">{{ __('Employee') }}</label>
                                    {{-- select2 selectedEmployeeId binding --}}
                                    <select wire:model='selectedEmployeeId' class="select2 form-control"
                                        id="select2selectedEmployeeId">
                                        {{-- Loop through $employees collection provided by the component --}}
                                        {{-- This is line ~34 where the Undefined variable error occurred --}}
                                        @forelse ($employees as $employee)
                                            <option value="{{ $employee->id }}">
                                                {{-- Adjust the employee property access based on your Employee model --}}
                                                {{-- Assuming Employee model has 'id' and 'full_name' --}}
                                                {{ $employee->id . ' - ' . $employee->full_name }}</option>
                                        @empty
                                            <option value="0" disabled>{{ __('No Employees Found!') }}</option>
                                        @endforelse
                                    </select>
                                    {{-- Add validation error display if needed --}}
                                    {{-- @if ($errors->has('selectedEmployeeId'))
                                          <span class="text-danger">{{ $errors->first('selectedEmployeeId') }}</span>
                                      @endif --}}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Add other sidebar content/filters here --}}
                <div class="border-bottom p-3 my-sm-0 mb-3">
                    <div class="col-12">
                        <label class="form-label">{{ __('Date Range') }}</label>
                        {{-- Example date range input for filtering/selection --}}
                        <input type="text" wire:model.live.debounce.500ms="fromDate" class="form-control mb-2"
                            placeholder="From Date">
                        <input type="date" wire:model.live.debounce.500ms="toDate" class="form-control"
                            placeholder="To Date">
                        {{-- Or use a single flatpickr range input like in the leaves view --}}
                        {{-- <input type="text" wire:model.live.debounce.500ms="dateRange" class="form-control" placeholder="Date Range" id="flatpickr-range"> --}}
                    </div>
                </div>

                <div class="border-bottom p-3 my-sm-0 mb-3">
                    <div class="col-12 form-check">
                        <input class="form-check-input" type="checkbox" wire:model.live="isAbsence" id="isAbsenceCheck">
                        <label class="form-check-label" for="isAbsenceCheck">
                            {{ __('Show Absences Only') }}
                        </label>
                    </div>
                </div>

                <div class="border-bottom p-3 my-sm-0 mb-3">
                    <div class="col-12 form-check">
                        <input class="form-check-input" type="checkbox" wire:model.live="isOneFingerprint"
                            id="isOneFingerprintCheck">
                        <label class="form-check-label" for="isOneFingerprintCheck">
                            {{ __('One Fingerprint Per Day') }}
                        </label>
                    </div>
                </div>


                {{-- Example button to apply filters (if not using wire:model.live) --}}
                {{-- <div class="mt-3 d-grid">
                      <button wire:click="applyFilters" class="btn btn-primary">{{ __('Apply Filters') }}</button>
                  </div> --}}

                {{-- Import Button --}}
                <div class="p-3 d-grid">
                    <button wire:click="openImportModal" class="btn btn-success">
                        <i class="ti ti-file-import me-1"></i> {{ __('Import Fingerprints') }}
                    </button>
                </div>

                {{-- Export Button --}}
                <div class="p-3 d-grid">
                    <button wire:click="exportFingerprints" class="btn btn-info">
                        <i class="ti ti-file-export me-1"></i> {{ __('Export Fingerprints') }}
                    </button>
                </div>


            </div> {{-- End Sidebar --}}

            <!-- Main Content Area -->
            <div class="col app-calendar-content">
                {{-- Main content area, e.g., table or list of fingerprint records --}}
                <div class="p-3">
                    <h2>{{ __('Filtered Fingerprint Records') }}</h2>

                    {{-- Display the list of fingerprints using the $fingerprints computed property --}}
                    @if ($fingerprints->count() > 0)
                        <div class="overflow-x-auto shadow-sm rounded-lg border border-gray-200">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            {{ __('Employee') }}</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            {{ __('Timestamp') }}</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            {{ __('Device ID') }}</th>
                                        {{-- Add other columns relevant to your Fingerprint model --}}
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            {{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach ($fingerprints as $fingerprint)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                {{ $fingerprint->employee->full_name ?? 'N/A' }}</td>
                                            {{-- Access employee relationship --}}
                                            <td class="px-6 py-4 whitespace-nowrap">{{ $fingerprint->timestamp }}</td>
                                            {{-- Assuming timestamp field exists --}}
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                {{ $fingerprint->device_id ?? 'N/A' }}</td> {{-- Assuming device_id exists --}}
                                            {{-- Add other data cells --}}
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                {{-- Add action buttons here, e.g., to view/edit in modal --}}
                                                {{-- <button wire:click="openFingerprintModal({{ $fingerprint->id }})" class="text-blue-600 hover:text-blue-900">View</button> --}}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        {{-- Pagination links --}}
                        <div class="mt-4">
                            {{ $fingerprints->links() }}
                        </div>
                    @else
                        <p class="text-gray-600">{{ __('No fingerprint records found matching the current filters.') }}
                        </p>
                    @endif

                </div>
            </div> {{-- End Main Content Area --}}

        </div> {{-- End row --}}
    </div> {{-- End card --}}

    {{-- Include modals here --}}
    {{-- Assuming you have modals for import and viewing/editing fingerprints --}}
    @include('_partials/_modals/modal-fingerprint') {{-- Example modal include --}}
    @include('_partials/_modals/modal-import') {{-- Example import modal include --}}


    {{-- Include vendor scripts if needed (e.g., for select2, flatpickr) --}}
    @push('custom-scripts')
        {{-- Use @push if your layout uses @stack --}}
        <script src="{{ asset('assets/vendor/libs/select2/select2.js') }}"></script>
        <script src="{{ asset('assets/vendor/libs/flatpickr/flatpickr.js') }}"></script>

        <script>
            document.addEventListener('livewire:initialized', () => { // Use livewire:initialized
                const select2selectedEmployeeId = $('#select2selectedEmployeeId');
                // const flatpickrRange = document.querySelector('#flatpickr-range'); // If using date range picker

                // Initialize select2 for Employee dropdown
                if (select2selectedEmployeeId.length) {
                    // Removed .each() as typically not needed for a single ID
                    select2selectedEmployeeId.select2({
                        placeholder: "{{ __('Select Employee') }}",
                        allowClear: true, // Allow clearing the selection
                        dropdownParent: select2selectedEmployeeId.parent() // Ensure dropdown is contained
                    });

                    // Listen for changes on the Select2 dropdown and update Livewire property
                    select2selectedEmployeeId.on('change', function(e) {
                        @this.set('selectedEmployeeId', $(this).val());
                    });

                    // Optional: Listen for Livewire property updates to set Select2 value
                    // @this.on('selectedEmployeeIdUpdated', (value) => {
                    //     select2selectedEmployeeId.val(value).trigger('change.select2');
                    // });
                }

                // Initialize Flatpickr for Date Range (if using a single input for range)
                // if (flatpickrRange) {
                //      flatpickr(flatpickrRange, {
                //          mode: 'range',
                //          dateFormat: 'Y-m-d',
                //          onChange: function(selectedDates, dateStr, instance) {
                //              if (selectedDates.length === 2) {
                //                  @this.set('fromDate', instance.formatDate(selectedDates[0], 'Y-m-d'));
                //                  @this.set('toDate', instance.formatDate(selectedDates[1], 'Y-m-d'));
                //              } else {
                //                  @this.set('fromDate', null);
                //                  @this.set('toDate', null);
                //              }
                //          }
                //      });
                //       // Optional: Sync Flatpickr with Livewire properties on mount/update
                //       @this.on('fromDateUpdated', (date) => { if (fp) fp.setDate([date, @this.get('toDate')]); });
                //       @this.on('toDateUpdated', (date) => { if (fp) fp.setDate([@this.get('fromDate'), date]); });
                //       // Set initial dates
                //      if (@this.get('fromDate') && @this.get('toDate')) {
                //           flatpickrRange.flatpickr.setDate([@this.get('fromDate'), @this.get('toDate')]);
                //      }
                // }


                // Example of listening for modal events dispatched from Livewire
                @this.on('openImportModal', () => {
                    $('#modalImport').modal('show');
                }); // Assuming modal has ID modalImport
                @this.on('closeImportModal', () => {
                    $('#modalImport').modal('hide');
                });
                @this.on('openFingerprintModal', () => {
                    $('#modalFingerprint').modal('show');
                }); // Assuming modal has ID modalFingerprint
                @this.on('closeFingerprintModal', () => {
                    $('#modalFingerprint').modal('hide');
                });

                // Example of showing import success/error messages
                @this.on('importFinished', (messages) => {
                    // Assuming messages is an array like [{type: 'success/error/warning', text: '...'}]
                    messages.forEach(msg => {
                        if (msg.type === 'success') {
                            // Show success notification (e.g., Toastr)
                            toastr.success(msg.text); // Assuming Toastr is available
                        } else if (msg.type === 'error') {
                            toastr.error(msg.text);
                        } else if (msg.type === 'warning') {
                            toastr.warning(msg.text);
                        }
                    });
                    // Optional: Refresh the fingerprints list after import
                    @this.call('$refresh'); // Refresh the component
                });

            }); // End livewire:initialized event listener
        </script>
        {{-- Ensure scripts inside blade are wrapped in @section('page-script') or @push('custom-scripts') etc.
           and included in your main layout using @stack('scripts') or @yield('page-script') --}}
    @endpush

</div>
