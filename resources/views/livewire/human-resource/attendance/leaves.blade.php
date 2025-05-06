{{-- resources/views/livewire/human-resource/attendance/leaves.blade.php --}}
{{-- This view is the template for the App\Livewire\HumanResource\Attendance\Leaves Livewire component. --}}
{{-- It should contain the HTML structure for the attendance leaves management page. --}}

{{-- PHPDoc hint for static analysis tools like Intelephense --}}
@php
    /** @var \App\Livewire\HumanResource\Attendance\Leaves $this */
@endphp

<div>

    @php
        // Assuming Helper::appClasses() provides configuration data
        $configData = Helper::appClasses();
        use Carbon\Carbon; // Carbon is used in the component, can be used here if needed
    @endphp

    {{-- Set page title --}}
    @section('title', 'Attendance - Leaves')

    {{-- Include vendor styles if needed --}}
    @section('vendor-style')
        <link rel="stylesheet" href="{{ asset('assets/vendor/libs/flatpickr/flatpickr.css') }}" />
        <link rel="stylesheet" href="{{ asset('assets/vendor/libs/select2/select2.css') }}" />
    @endsection

    {{-- Include page-specific styles if needed --}}
    @section('page-style')
        <link rel="stylesheet" href="{{ asset('assets/vendor/css/pages/app-calendar.css') }}" />

        <style>
            /* Custom styles */
            tr.disabled {
                opacity: 0.5;
                pointer-events: none;
                text-decoration: line-through;
            }

            tr.disabled i {
                display: none;
            }

            /* Optional: Style for file upload input */
            .file-upload-input {
                border: 1px solid #d1d5db;
                border-radius: 0.25rem;
                padding: 0.5rem 0.75rem;
            }
        </style>
    @endsection

    {{-- Alerts --}}
    {{-- Assuming this partial displays session flash messages or Livewire-dispatched alerts --}}
    @include('_partials/_alerts/alert-general')


    <div class="card app-calendar-wrapper"> {{-- Outer container div --}}
        <div class="row g-0"> {{-- Row for sidebar and main content --}}

            <!-- Sidebar -->
            <div class="col app-calendar-sidebar" id="app-calendar-sidebar">
                {{-- Employee Filter --}}
                <div class="border-bottom p-3 my-sm-0 mb-3">
                    <div class="d-grid">
                        <div class="sidebar-header">
                            <div class="d-flex align-items-center me-3 me-lg-0">
                                {{-- wire:ignore is used to prevent Livewire from updating the select2 element --}}
                                <div wire:ignore class="col-12">
                                    <label class="form-label">{{ __('Employee') }}</label>
                                    {{-- select2 selectedEmployeeId binding --}}
                                    <select wire:model='selectedEmployeeId' class="select2 form-control"
                                        id="select2selectedEmployeeId">
                                        <option value="">{{ __('Select Employee') }}</option>
                                        {{-- Default option --}}
                                        {{-- Loop through $activeEmployees collection passed from the component --}}
                                        {{-- This is where the Undefined variable error occurs if $activeEmployees is not provided --}}
                                        @forelse ($activeEmployees as $employee)
                                            <option value="{{ $employee->id }}">
                                                {{-- Display employee name; adjust field names as per your Employee model --}}
                                                {{ $employee->id . ' - ' . $employee->first_name . ' ' . $employee->father_name . ' ' . $employee->last_name }}
                                            </option>
                                        @empty
                                            <option value="0" disabled>{{ __('No Employees Found!') }}</option>
                                        @endforelse
                                    </select>
                                    {{-- Validation error for selectedEmployeeId if needed (likely below the save form field) --}}
                                    {{-- Using $errors bag for linter happiness --}}
                                    @if ($errors->has('selectedEmployeeId'))
                                        <span class="text-danger">{{ $errors->first('selectedEmployeeId') }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Date Range Filter --}}
                <div class="border-bottom p-3 my-sm-0 mb-3">
                    <div class="col-12">
                        <label class="form-label">{{ __('Date Range') }}</label>
                        {{-- Flatpickr date range input --}}
                        {{-- wire:model.live.debounce.500ms to update Livewire property as date changes --}}
                        <input type="text" wire:model.live.debounce.500ms="startDate" class="form-control"
                            placeholder="YYYY-MM-DD" id="flatpickr-range"> {{-- Connect to flatpickr JS --}}
                        {{-- You might need separate inputs for start and end date or handle the range picker output in JS --}}
                        {{-- Or use two separate wire:model properties for start and end date --}}
                        {{-- Example with two inputs: --}}
                        {{-- <input type="date" wire:model.live="startDate" class="form-control mb-2"> --}}
                        {{-- <input type="date" wire:model.live="endDate" class="form-control"> --}}
                    </div>
                    {{-- Example button to apply filter (optional, could use wire:model.live directly) --}}
                    {{-- <div class="mt-3 d-grid">
               <button wire:click="applyDateFilter" class="btn btn-primary">{{ __('Apply Filter') }}</button>
           </div> --}}
                </div>

                {{-- Leave Type Filter --}}
                <div class="border-bottom p-3 my-sm-0 mb-3">
                    <div class="col-12">
                        <label class="form-label">{{ __('Leave Type') }}</label>
                        <div wire:ignore> {{-- wire:ignore for select2 --}}
                            <select wire:model.live="selectedLeaveId" class="select2 form-control"
                                id="select2selectedLeaveId">
                                <option value="">{{ __('Select Leave Type') }}</option> {{-- Default option --}}
                                @foreach ($leaveTypes as $leaveType)
                                    {{-- Loop through $leaveTypes --}}
                                    <option value="{{ $leaveType->id }}">{{ $leaveType->name }}</option>
                                    {{-- Display leave type name --}}
                                @endforeach
                            </select>
                            {{-- Validation error for selectedLeaveId if needed (likely below the save form field) --}}
                            {{-- Using $errors bag for linter happiness --}}
                            @if ($errors->has('selectedLeaveId'))
                                <span class="text-danger">{{ $errors->first('selectedLeaveId') }}</span>
                            @endif
                        </div>
                    </div>
                    {{-- Example button to apply filter (optional) --}}
                    {{-- <div class="mt-3 d-grid">
                  <button wire:click="applyFilters" class="btn btn-primary">{{ __('Apply Filter') }}</button>
              </div> --}}
                </div>

                {{-- Section to add a new leave request (Form) --}}
                <div class="p-3">
                    <h5 class="mb-4">{{ __('Add New Leave Request') }}</h5>
                    <form wire:submit.prevent="saveLeave"> {{-- Form to save a new leave --}}

                        {{-- Employee selection (if not already selected via filter) --}}
                        {{-- If the filter select box above is used for saving, remove this --}}
                        {{-- <div class="mb-3">
                    <label class="form-label">{{ __('Employee') }}</label>
                     <select wire:model="selectedEmployeeId" class="form-control">
                         <option value="">{{ __('Select Employee') }}</option>
                          @foreach ($activeEmployees as $employee)
                              <option value="{{ $employee->id }}">{{ $employee->first_name . ' ' . $employee->last_name }}</option>
                          @endforeach
                     </select>
                     @if ($errors->has('selectedEmployeeId')) <span class="text-danger">{{ $errors->first('selectedEmployeeId') }}</span> @endif
                 </div> --}}


                        {{-- Leave Type selection (if not already selected via filter) --}}
                        {{-- If the filter select box above is used for saving, remove this --}}
                        {{-- <div class="mb-3">
                     <label class="form-label">{{ __('Leave Type') }}</label>
                     <select wire:model="selectedLeaveId" class="form-control">
                          <option value="">{{ __('Select Leave Type') }}</option>
                          @foreach ($leaveTypes as $leaveType)
                              <option value="{{ $leaveType->id }}">{{ $leaveType->name }}</option>
                          @endforeach
                     </select>
                     @if ($errors->has('selectedLeaveId')) <span class="text-danger">{{ $errors->first('selectedLeaveId') }}</span> @endif
                 </div> --}}


                        {{-- Date inputs --}}
                        <div class="mb-3">
                            <label class="form-label">{{ __('From Date') }}</label>
                            <input type="date" wire:model="startDate" class="form-control"> {{-- Wire model to property --}}
                            {{-- Using $errors bag for linter happiness --}}
                            @if ($errors->has('startDate'))
                                <span class="text-danger">{{ $errors->first('startDate') }}</span>
                            @endif
                        </div>

                        <div class="mb-3">
                            <label class="form-label">{{ __('To Date') }}</label>
                            <input type="date" wire:model="endDate" class="form-control"> {{-- Wire model to property --}}
                            {{-- Using $errors bag for linter happiness --}}
                            @if ($errors->has('endDate'))
                                <span class="text-danger">{{ $errors->first('endDate') }}</span>
                            @endif
                        </div>

                        {{-- Conditional Time inputs for hourly leaves --}}
                        @php
                            // Pulled the method call into a PHP block for linter happiness
                            $isHourlyLeave = $this->isHourly($selectedLeaveId);
                        @endphp
                        @if ($isHourlyLeave) {{-- Now checking the local variable --}}
                            <div class="mb-3">
                                <label class="form-label">{{ __('Start Time') }}</label>
                                <input type="time" wire:model="startTime" class="form-control">
                                {{-- Wire model --}}
                                {{-- Using $errors bag for linter happiness --}}
                                @if ($errors->has('startTime'))
                                    <span class="text-danger">{{ $errors->first('startTime') }}</span>
                                @endif
                            </div>

                            <div class="mb-3">
                                <label class="form-label">{{ __('End Time') }}</label>
                                <input type="time" wire:model="endTime" class="form-control"> {{-- Wire model --}}
                                {{-- Using $errors bag for linter happiness --}}
                                @if ($errors->has('endTime'))
                                    <span class="text-danger">{{ $errors->first('endTime') }}</span>
                                @endif
                            </div>
                        @endif


                        {{-- Note input --}}
                        <div class="mb-3">
                            <label class="form-label">{{ __('Note') }}</label>
                            <textarea wire:model="note" class="form-control"></textarea> {{-- Wire model --}}
                            {{-- Using $errors bag for linter happiness --}}
                            @if ($errors->has('note'))
                                <span class="text-danger">{{ $errors->first('note') }}</span>
                            @endif
                        </div>

                        {{-- File Upload input --}}
                        <div class="mb-3">
                            <label class="form-label">{{ __('Upload Supporting Document (Optional)') }}</label>
                            <input type="file" wire:model="leaveFile" class="form-control file-upload-input">
                            {{-- Wire model for file upload --}}
                            {{-- Using $errors bag for linter happiness --}}
                            @if ($errors->has('leaveFile'))
                                <span class="text-danger">{{ $errors->first('leaveFile') }}</span>
                            @endif
                        </div>

                        {{-- Checkbox (e.g., for acknowledgment) --}}
                        <div class="mb-3 form-check"> {{-- Use form-check class for alignment --}}
                            <input type="checkbox" wire:model="is_checked" class="form-check-input" id="is_checked">
                            {{-- Wire model and ID --}}
                            <label class="form-check-label"
                                for="is_checked">{{ __('Acknowledge and confirm') }}</label>
                            {{-- Using $errors bag for linter happiness --}}
                            @if ($errors->has('is_checked'))
                                <span class="text-danger d-block">{{ $errors->first('is_checked') }}</span>
                                {{-- d-block to show below checkbox --}}
                            @endif
                        </div>


                        {{-- Submit button --}}
                        <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                            <span wire:loading.remove>{{ __('Save Leave Request') }}</span>
                            <span wire:loading>{{ __('Saving...') }}</span>
                        </button>
                    </form>
                </div>

            </div> {{-- End Sidebar --}}

            <!-- Calendar content -->
            {{-- This section would typically contain the calendar display or a table of leaves --}}
            {{-- Based on your blade code snippet, the list/table of leaves might be here --}}
            <div class="col app-calendar-content">
                {{-- Main content area, like a calendar or list/table --}}
                <div class="p-3">
                    <h2>{{ __('Leaves List') }}</h2>
                    {{-- Display the list of leaves using the $leaves computed property --}}
                    @if ($leaves->count() > 0)
                        <div class="overflow-x-auto shadow-sm rounded-lg border border-gray-200">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            {{ __('Employee') }}</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            {{ __('Leave Type') }}</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            {{ __('From Date') }}</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            {{ __('To Date') }}</th>
                                        @if ($leaves->first() && $leaves->first()->start_at)
                                            {{-- Only show time headers if any leave has times --}}
                                            <th
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                {{ __('Start Time') }}</th>
                                            <th
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                {{ __('End Time') }}</th>
                                        @endif
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            {{ __('Note') }}</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            {{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach ($leaves as $leave)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                {{ $leave->employee->first_name . ' ' . $leave->employee->last_name }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">{{ $leave->leave->name }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                {{ $leave->from_date->format('Y-m-d') }}</td> {{-- Format date --}}
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                {{ $leave->to_date->format('Y-m-d') }}</td> {{-- Format date --}}
                                            @if ($leave->start_at)
                                                {{-- Display times only if they exist --}}
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    {{ Carbon::parse($leave->start_at)->format('H:i') }}</td>
                                                {{-- Format time --}}
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    {{ Carbon::parse($leave->end_at)->format('H:i') }}</td>
                                                {{-- Format time --}}
                                            @else
                                                <td></td> {{-- Empty cells for alignment --}}
                                                <td></td>
                                            @endif
                                            <td class="px-6 py-4 whitespace-nowrap">{{ $leave->note ?? '-' }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                {{-- Add Edit/Delete buttons here --}}
                                                <button wire:click="confirmDelete({{ $leave->id }})"
                                                    class="text-red-600 hover:text-red-900">{{ __('Delete') }}</button>
                                                {{-- Add Edit button later if needed --}}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        {{-- Pagination links --}}
                        <div class="mt-4">
                            {{ $leaves->links() }}
                        </div>
                    @else
                        <p class="text-gray-600">{{ __('No leave requests found for the selected filters.') }}</p>
                    @endif

                    {{-- Confirmation Modal for Deletion (Basic example) --}}
                    @if ($confirmingLeaveDeletion)
                        <div class="fixed inset-0 bg-gray-600 bg-opacity50 overflow-y-auto h-full w-full"
                            id="my-modal">
                            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                                <h3 class="text-lg font-medium leading-6 text-gray-900">{{ __('Confirm Deletion') }}
                                </h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500">
                                        {{ __('Are you sure you want to delete this leave request?') }}</p>
                                </div>
                                <div class="mt-4 flex justify-end">
                                    <button wire:click="$set('confirmingLeaveDeletion', false)"
                                        class="mr-2 px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300">{{ __('Cancel') }}</button>
                                    <button wire:click="deleteLeave({{ $leaveToDeleteId }})"
                                        class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">{{ __('Delete') }}</button>
                                </div>
                            </div>
                        </div>
                    @endif


                </div> {{-- End main content area padding --}}
            </div> {{-- End Calendar content --}}

        </div> {{-- End row --}}
    </div> {{-- End card --}}


    {{-- Include vendor scripts if needed --}}
    @section('vendor-script')
        <script src="{{ asset('assets/vendor/libs/flatpickr/flatpickr.js') }}"></script>
        <script src="{{ asset('assets/vendor/libs/select2/select2.js') }}"></script>
        {{-- Add any other vendor scripts required for this page --}}
    @endsection

    {{-- Include page-specific scripts --}}
    @section('page-script')
        {{-- Add JS to initialize flatpickr and select2 and handle wire:model binding --}}
        <script>
            document.addEventListener('livewire:initialized', () => { // Use livewire:initialized
                const select2selectedEmployeeId = $('#select2selectedEmployeeId');
                const select2selectedLeaveId = $('#select2selectedLeaveId');

                // Initialize select2 for Employee
                if (select2selectedEmployeeId.length) {
                    // Removed .each() as it's usually not needed for a single element ID
                    select2selectedEmployeeId.select2({
                        placeholder: "{{ __('Select..') }}",
                        dropdownParent: select2selectedEmployeeId.parent()
                    });
                    // Listen for changes and update Livewire property
                    select2selectedEmployeeId.on('change', function(e) {
                        @this.set('selectedEmployeeId', $(this)
                    .val()); // This was around line 180 in the uploaded snippet
                    });
                    // Set initial value if it exists
                    @this.on('selectedEmployeeIdUpdated', (value) => {
                        select2selectedEmployeeId.val(value).trigger('change.select2');
                    });
                }

                // Initialize select2 for Leave Type
                if (select2selectedLeaveId.length) {
                    // Removed .each()
                    select2selectedLeaveId.select2({
                        placeholder: "{{ __('Select..') }}",
                        allowClear: true,
                        dropdownParent: select2selectedLeaveId.parent()
                    });
                    // Listen for changes and update Livewire property
                    select2selectedLeaveId.on('change', function(e) {
                        @this.set('selectedLeaveId', $(this)
                    .val()); // This was around line 195 in the uploaded snippet
                    });
                    // Set initial value if it exists
                    @this.on('selectedLeaveIdUpdated', (value) => {
                        select2selectedLeaveId.val(value).trigger('change.select2');
                    });
                }


                // Initialize Flatpickr for Date Range
                const flatpickrRange = document.querySelector('#flatpickr-range');
                let fp = null; // Variable to hold flatpickr instance

                if (flatpickrRange) {
                    fp = flatpickr(flatpickrRange, {
                        mode: 'range',
                        dateFormat: 'Y-m-d', // Ensure date format matches expected backend format
                        // Use onChange to update Livewire properties when dates are selected
                        onChange: function(selectedDates, dateStr, instance) {
                            if (selectedDates.length === 2) {
                                @this.set('startDate', instance.formatDate(selectedDates[0], 'Y-m-d'));
                                @this.set('endDate', instance.formatDate(selectedDates[1], 'Y-m-d'));
                            } else {
                                // Handle case where only one date is selected or cleared
                                @this.set('startDate', null);
                                @this.set('endDate', null);
                            }
                            // Optional: Immediately apply filter after selecting range
                            // @this.call('applyDateFilter');
                        }
                    });

                    // Optional: Watch for Livewire property updates to set Flatpickr dates if they change from the component
                    @this.on('startDateUpdated', (date) => {
                        if (fp) {
                            fp.setDate([date, @this.get('endDate')]);
                        }
                    });
                    @this.on('endDateUpdated', (date) => {
                        if (fp) {
                            fp.setDate([@this.get('startDate'), date]);
                        }
                    });
                    // Set initial dates from Livewire properties
                    if (@this.get('startDate') && @this.get('endDate')) {
                        fp.setDate([@this.get('startDate'), @this.get('endDate')]);
                    }

                } // End if flatpickrRange exists


                // Example of listening for an event dispatched from Livewire after save/delete
                @this.on('leaveSaved', () => {
                    // Optional: Show a client-side notification (e.g., using Toastr or SweetAlert2)
                    // Optional: Close a modal if the form was in a modal
                    console.log('Leave saved event received');
                    // If using session()->flash, the alert partial should handle display on next request/render
                });

                @this.on('leaveDeleted', () => {
                    // Optional: Show client-side notification
                    console.log('Leave deleted event received');
                });


            }); // End livewire:initialized

            // Ensure scripts inside blade are wrapped in @section('page-script') or similar
            // and included in your main layout using undefined or undefined
            // or included directly if this Livewire view is the main page content.
        </script>
    @endsection

</div> {{-- End outer div --}}
