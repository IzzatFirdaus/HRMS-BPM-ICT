<div> {{-- Livewire component root element --}}

    @php
        $configData = Helper::appClasses(); // Assuming Helper::appClasses() is available globally for layout configuration
    @endphp

    {{-- Define the title section for the layout --}}
    @section('title', 'Employee Info - Structure') {{-- Page title --}}

    {{-- Define page-specific styles --}}
    @section('page-style')
        <style>
            /* Combined styles for hover effects on action icons and buttons */
            /* Timeline icon hover effect */
            .timeline-icon {
                cursor: pointer;
                opacity: 0;
                /* Hide by default */
            }

            /* Table row hover effect to show timeline icons and table action buttons */
            .timeline-row:hover .timeline-icon,
            tr:hover .btn-tr {
                display: inline-block;
                /* Ensure they are displayed */
                opacity: 1;
                /* Make visible on hover */
            }

            /* Styles for table action buttons on hover */
            .btn-tr {
                opacity: 0;
                /* Hide by default */
            }

            /* Optional: Style for text cells on row hover */
            /* Note: The class '.td' is not currently applied to any table cells in the HTML below. */
            /* If intended, apply class="td" to your <td> elements. */
            /* Using !important might be necessary if other styles override, consider refactoring if possible */
            tr:hover .td {
                color: #7367f0 !important;
            }
        </style>
        {{-- Include any necessary external CSS files here (e.g., for icons, modals, etc.) --}}
    @endsection

    {{-- Include a general alert partial for displaying messages --}}
    @include('_partials/_alerts/alert-general')

    {{-- Main row container for profile sections --}}
    <div class="row">
        <div class="col-12">
            {{-- Employee Profile Header Card --}}
            <div class="card mb-4">
                {{-- Optional banner image --}}
                {{-- <div class="user-profile-header-banner">
                  <img src="{{ asset('assets/img/pages/profile-banner.png') }}" alt="Banner image" class="rounded-top">
              </div> --}}
                {{-- Profile details section --}}
                <div class="user-profile-header d-flex flex-column flex-sm-row text-sm-start text-center mb-4">
                    {{-- Profile Photo --}}
                    <div class="flex-shrink-0 mt-n2 mx-sm-0 mx-auto">
                        {{-- Display profile photo with fallback to a default image --}}
                        {{-- Assumes profile_photo_path is an attribute on the Employee model --}}
                        <img src="{{ Storage::disk('public')->exists($employee->profile_photo_path) ? Storage::disk('public')->url($employee->profile_photo_path) : Storage::disk('public')->url('profile-photos/.default-photo.jpg') }}"
                            alt="{{ $employee->fullName ?? 'Employee' }} Profile Photo" {{-- Added localized alt text, provide fallback --}}
                            class="d-block h-auto ms-0 ms-sm-4 rounded user-profile-img" width="100px">
                        {{-- Image styling --}}
                    </div>
                    {{-- Employee Info --}}
                    <div class="flex-grow-1 mt-3 mt-sm-5">
                        {{-- Flex container for name/details and status button --}}
                        <div
                            class="d-flex align-items-md-end align-items-sm-start align-items-center justify-content-md-between justify-content-start mx-4 flex-md-row flex-column gap-4">
                            <div class="user-profile-info">
                                <h4>{{ $employee->fullName ?? 'N/A' }}</h4> {{-- Display full name, provide fallback --}}
                                {{-- Key employee attributes list --}}
                                <ul
                                    class="list-inline mb-0 d-flex align-items-center flex-wrap justify-content-sm-start justify-content-center gap-2">
                                    <li class="list-inline-item">
                                        <span class="badge rounded-pill bg-label-primary"><i
                                                class="ti ti-id ti-xs me-1"></i> {{ $employee->id }}</span>
                                        {{-- Display ID badge --}}
                                    </li>
                                    <li class="list-inline-item">
                                        <i class="ti ti-building-community ti-xs me-1"></i>
                                        {{ $employee->current_center ?? 'N/A' }} {{-- Display current center, provide fallback --}}
                                    </li>
                                    <li class="list-inline-item">
                                        <i class="ti ti-building ti-xs me-1"></i>
                                        {{ $employee->current_department ?? 'N/A' }} {{-- Display current department, provide fallback --}}
                                    </li>
                                    <li class="list-inline-item">
                                        <i class="ti ti-map-pin ti-xs me-1"></i>
                                        {{ $employee->current_position ?? 'N/A' }} {{-- Display current position, provide fallback --}}
                                    </li>
                                    <li class="list-inline-item">
                                        <i class="ti ti-rocket ti-xs me-1"></i>
                                        {{ $employee->join_at_short_form ?? 'N/A' }} {{-- Display join date short form, provide fallback --}}
                                    </li>
                                    <li class="list-inline-item">
                                        <i class="ti ti-player-track-next ti-xs me-1"></i>
                                        {{ __('Continuity') . ': ' . ($employee->worked_years ?? 'N/A') . ' ' . __('years') }}
                                        {{-- Display worked years, translate, provide fallback --}}
                                    </li>
                                </ul>
                            </div>
                            {{-- Toggle Status Button --}}
                            {{-- wire:click calls the Livewire method to change status --}}
                            <button wire:click='toggleActive' type="button"
                                class="btn @if ($employee->is_active == 1) btn-success @else btn-danger @endif waves-effect waves-light">
                                {{-- Conditional icon based on status --}}
                                <span
                                    class="ti @if ($employee->is_active == 1) ti-user-check @else ti-user-x @endif me-1"></span>
                                {{-- Conditional text based on status --}}
                                @if ($employee->is_active == 1)
                                    {{ __('Active') }}
                                @else
                                    {{ __('Inactive') }}
                                @endif {{-- Translate button text --}}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {{-- This section for navigation tabs is commented out --}}
    {{-- <div class="row">
      <div class="col-md-12">
          <ul class="nav nav-pills flex-column flex-sm-row mb-4">
              <li class="nav-item"><a class="nav-link active" href="javascript:void(0);"><i class='ti-xs ti ti-user-check me-1'></i> Profile</a></li>
              <li class="nav-item"><a class="nav-link" href="{{url('pages/profile-teams')}}"><i class='ti-xs ti ti-users me-1'></i> Teams</a></li>
              <li class="nav-item"><a class="nav-link" href="{{url('pages/profile-projects')}}"><i class='ti-xs ti ti-layout-grid me-1'></i> Projects</a></li>
              <li class="nav-item"><a class="nav-link" href="{{url('pages/profile-connections')}}"><i class='ti-xs ti ti-link me-1'></i> Connections</a></li>
          </ul>
      </div>
  </div> --}}
    {{-- Row for Assets, Details, and Timeline sections --}}
    <div class="row">
        <div class="col-12"> {{-- Full width column --}}
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">{{ __('Assets') }}</h5> {{-- Translate title --}}
                </div>
                {{-- Responsive table container --}}
                <div class="table-responsive text-nowrap">
                    {{-- Assets Table --}}
                    <table class="table table-hover"> {{-- Added table-hover for row hover effect --}}
                        <thead>
                            <tr>
                                <th class="col-1">{{ __('ID') }}</th> {{-- Translate header --}}
                                <th class="col-1">{{ __('Category') }}</th> {{-- Translate header --}}
                                <th class="col-1">{{ __('Sub-Category') }}</th> {{-- Translate header --}}
                                <th>{{ __('Serial Number') }}</th> {{-- Translate header --}}
                                <th>{{ __('Handed Date') }}</th> {{-- Translate header --}}
                                <th>{{ __('Actions') }}</th> {{-- Translate header --}}
                            </tr>
                        </thead>
                        <tbody class="table-border-bottom-0">
                            {{-- Loop through employee assets, handling empty state --}}
                            {{-- Ensure $employeeAssets relationship is eager loaded in Livewire component --}}
                            {{-- TODO: If using relationships for Category/Sub-Category, eager load employeeAssets.asset.category and employeeAssets.asset.subcategory --}}
                            @forelse ($employeeAssets as $asset)
                                <tr>
                                    {{-- Asset ID cell - clickable --}}
                                    {{-- wire:click='showAsset' implies a Livewire method to show asset details (perhaps in a modal) --}}
                                    <td wire:click='showAsset' class="td" style="cursor: pointer;">
                                        <i class="ti ti-tag ti-sm text-danger me-3"></i>
                                        <strong>{{ $asset->asset_id }}</strong>
                                    </td>
                                    {{-- Display Category Name --}}
                                    {{-- Assumes getCategory() is a method/accessor on the Asset model --}}
                                    {{-- Consider eager loading relationships instead: $asset->asset->category->name ?? 'N/A' --}}
                                    <td>{{ $asset->getCategory($asset->asset_id)->name ?? 'N/A' }}</td>
                                    {{-- Provide fallback --}}
                                    {{-- Display Sub-Category Name --}}
                                    {{-- Assumes getSubCategory() is a method/accessor on the Asset model --}}
                                    {{-- Consider eager loading relationships instead: $asset->asset->subcategory->name ?? 'N/A' --}}
                                    <td>{{ $asset->getSubCategory($asset->asset_id)->name ?? 'N/A' }}</td>
                                    {{-- Provide fallback --}}
                                    {{-- Display Serial Number (assuming asset relationship is loaded) --}}
                                    {{-- TODO: Eager load employeeAssets.asset relation in component --}}
                                    <td>{{ $asset->asset->serial_number ?? 'N/A' }}</td> {{-- Provide fallback --}}
                                    {{-- Display Handed Date --}}
                                    {{-- Assuming handed_date is a Carbon instance or formatted string --}}
                                    <td><span
                                            class="badge rounded-pill bg-label-secondary">{{ $asset->handed_date ?? 'N/A' }}</span>
                                    </td> {{-- Provide fallback --}}
                                    <td>
                                        {{-- Asset Action Buttons --}}
                                        {{-- 'Guide' Button - Currently has no wire:click --}}
                                        <button type="button"
                                            class="btn btn-sm btn-tr rounded-pill btn-icon btn-outline-secondary waves-effect">
                                            <span class="ti ti-arrow-guide"></span> {{-- Icon class --}}
                                        </button>
                                        {{-- Edit Asset Button - Triggers Livewire method and opens modal --}}
                                        {{-- wire:click is on the span, could be moved to button --}}
                                        <button type="button"
                                            class="btn btn-sm btn-tr rounded-pill btn-icon btn-outline-secondary waves-effect">
                                            <span wire:click.prevent='showEditAssetModal({{ $asset }})'
                                                data-bs-toggle="modal" data-bs-target="#assetModal"
                                                class="ti ti-pencil"></span> {{-- Icon class --}}
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                {{-- Content displayed when no assets are found --}}
                                <tr>
                                    <td colspan="6"> {{-- Span across all columns --}}
                                        <div class="mt-2 mb-2" style="text-align: center">
                                            <h3 class="mb-1 mx-2">{{ __('Oopsie-doodle!') }}</h3>
                                            {{-- Translate message --}}
                                            <p class="mb-4 mx-2">
                                                {{ __('No data found, please sprinkle some data in my virtual bowl, and let the fun begin!') }}
                                                {{-- Translate message --}}
                                            </p>
                                            {{-- Button to add new asset, opening the modal --}}
                                            {{-- TODO: Add wire:click to the button if modal is managed by Livewire --}}
                                            <button class="btn btn-label-primary mb-4" data-bs-toggle="modal"
                                                data-bs-target="#assetModal">
                                                {{ __('Add New Asset') }} {{-- Translate button text --}}
                                            </button>
                                            <div>
                                                {{-- Display an illustration image --}}
                                                <img src="{{ asset('assets/img/illustrations/page-misc-under-maintenance.png') }}"
                                                    width="200" class="img-fluid">
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        {{-- Layout column for medium, large, and extra-large screens --}}
        <div class="col-xl-4 col-lg-5 col-md-5">
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-action-title mb-0">{{ __('Details') }}</h5> {{-- Translate title --}}
                    {{-- Details List --}}
                    {{-- Using multiple ul elements, could potentially be a single ul with appropriate list items --}}
                    <ul class="list-unstyled mb-4 mt-3">
                        <li class="d-flex align-items-center mb-3"><i class="ti ti-home ti-xs me-2"></i><span
                                class="fw-bold mx-2">{{ __('Address') }}:</span>
                            <span>{{ $employee->address ?? 'N/A' }}</span></li> {{-- Display address, provide fallback --}}
                    </ul>
                    {{-- Mobile Number Detail --}}
                    <ul class="list-unstyled mb-4 mt-3">
                        <li class="d-flex align-items-center mb-3"><i class="ti ti-phone-call ti-xs me-2"></i><span
                                class="fw-bold mx-2">{{ __('Mobile') }}:</span> <span
                                style="direction: ltr">{{ '+963 ' . number_format($employee->mobile_number ?? 0, 0, '', ' ') }}</span>
                        </li> {{-- Display mobile number with formatting, provide fallback for number --}}
                    </ul>
                    {{-- Started (Join Date) Detail --}}
                    <ul class="list-unstyled mb-4 mt-3">
                        <li class="d-flex align-items-center mb-3"><i class="ti ti-rocket ti-xs me-2"></i><span
                                class="fw-bold mx-2">{{ __('Started') }}:</span>
                            <span>{{ $employee->join_at ?? 'N/A' }}</span></li> {{-- Display join date, provide fallback --}}
                    </ul>

                    {{-- Counters Section --}}
                    <h5 class="card-action-title mb-0">{{ __('Counters') }}</h5> {{-- Translate title --}}
                    {{-- Counters List --}}
                    <ul class="list-unstyled mb-0 mt-3">
                        <li class="d-flex align-items-center mb-3"><i class="ti ti-calendar ti-xs me-2"></i><span
                                class="fw-bold mx-2">{{ __('Leaves Balance') }}:</span> <span
                                class="badge bg-label-secondary">{{ ($employee->max_leave_allowed ?? 0) . ' ' . __('Day') }}</span>
                        </li> {{-- Display leaves balance, provide fallback, translate --}}
                        <li class="d-flex align-items-center mb-3"><i class="ti ti-alarm ti-xs me-2"></i><span
                                class="fw-bold mx-2">{{ __('Hourly') }}:</span> <span
                                class="badge bg-label-secondary">{{ $employee->hourly_counter ?? 0 }}</span></li>
                        {{-- Display hourly counter, provide fallback --}}
                        <li class="d-flex align-items-center mb-3"><i class="ti ti-hourglass ti-xs me-2"></i><span
                                class="fw-bold mx-2">{{ __('Delay') }}:</span> <span
                                class="badge bg-label-secondary">{{ $employee->delay_counter ?? 0 }}</span></li>
                        {{-- Display delay counter, provide fallback --}}
                    </ul>
                </div>
            </div>
        </div>
        {{-- Layout column for medium, large, and extra-large screens --}}
        <div class="col-xl-8 col-lg-7 col-md-7">
            <div class="card card-action mb-4">
                {{-- Card header with title and dropdown menu --}}
                <div class="card-header align-items-center">
                    <h5 class="card-action-title mb-0">{{ __('Timelines') }}</h5> {{-- Translate title --}}
                    <div class="card-action-element">
                        {{-- Dropdown menu for timeline actions --}}
                        <div class="dropdown">
                            <button type="button" class="btn dropdown-toggle hide-arrow p-0"
                                data-bs-toggle="dropdown" aria-expanded="false"><i
                                    class="ti ti-dots-vertical text-muted"></i></button> {{-- Dropdown toggle icon --}}
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    {{-- Link/button to add new timeline entry, triggers Livewire method and opens modal --}}
                                    <a wire:click='showStoreTimelineModal()' class="dropdown-item"
                                        data-bs-toggle="modal"
                                        data-bs-target="#timelineModal">{{ __('Add New Position') }}</a>
                                    {{-- Translate menu item --}}
                                </li>
                                {{-- Example commented-out menu item --}}
                                {{-- <li><a class="dropdown-item" href="javascript:void(0);">Edit timeline</a></li> --}}
                            </ul>
                        </div>
                    </div>
                </div>
                {{-- Card body for timeline content --}}
                <div class="card-body pb-0">
                    {{-- Timeline list --}}
                    <ul class="timeline ms-1 mb-0"> {{-- Assumed Bootstrap timeline classes --}}
                        {{-- Loop through employee timelines --}}
                        {{-- Ensure $employeeTimelines relationship is eager loaded with nested relationships (position, center) in component --}}
                        @foreach ($employeeTimelines as $timeline)
                            {{-- Timeline item with conditional border for the last item --}}
                            <li
                                class="timeline-item timeline-item-transparent @if ($loop->last) border-0 @endif">
                                {{-- Timeline point with conditional color for the first item --}}
                                <span
                                    class="timeline-point @if ($loop->first) timeline-point-primary @else timeline-point-info @endif"></span>
                                <div class="timeline-event">
                                    <div class="timeline-header">
                                        {{-- Flex row for position title and action icons --}}
                                        {{-- Added timeline-row class for hover effect CSS --}}
                                        <div class="timeline-row d-flex m-0">
                                            <h6 class="m-0">{{ $timeline->position->name ?? 'N/A' }}</h6>
                                            {{-- Display position name, provide fallback --}}
                                            {{-- Action icons with wire:click handlers and timeline-icon class for hover effect --}}
                                            {{-- 'Set Present' icon --}}
                                            <i wire:click='setPresentTimeline({{ $timeline }})'
                                                class="timeline-icon text-success ti ti-refresh mx-1"></i>
                                            {{-- 'Edit' icon - Triggers Livewire method and opens modal --}}
                                            {{-- wire:click is on the icon, could be moved to a wrapper for cleaner structure --}}
                                            <i wire:click='showUpdateTimelineModal({{ $timeline }})'
                                                class="timeline-icon text-info ti ti-edit" data-bs-toggle="modal"
                                                data-bs-target="#timelineModal"></i>
                                            {{-- 'Delete' icon - Triggers the first step of inline confirmation --}}
                                            {{-- wire:click is on the icon, could be moved to a wrapper for cleaner structure --}}
                                            <i wire:click='confirmDeleteTimeline({{ $timeline }})'
                                                class="timeline-icon text-danger ti ti-trash mx-1"></i>
                                            {{-- "Sure?" Confirmation Button: Conditionally displayed --}}
                                            @if ($confirmedId === $timeline->id)
                                                {{-- wire:click.prevent triggers the actual delete Livewire method --}}
                                                <button wire:click.prevent='deleteTimeline({{ $timeline }})'
                                                    type="button"
                                                    class="btn btn-xs btn-danger waves-effect waves-light mx-1">{{ __('Sure?') }}
                                                    {{-- Translate button text --}}
                                                </button>
                                            @endif
                                        </div>
                                        {{-- Timeline dates --}}
                                        <small class="text-muted">
                                            @if ($timeline->end_date == null)
                                                {{ __('Present') }} {{-- Translate 'Present' --}}
                                            @else
                                                {{ $timeline->start_date?->format('Y-m-d') ?? 'N/A' }} -->
                                                {{ $timeline->end_date?->format('Y-m-d') ?? 'N/A' }}
                                                {{-- Format dates, provide fallbacks --}}
                                            @endif
                                        </small>
                                    </div>
                                    {{-- Center Name --}}
                                    {{-- Ensure center relationship is eager loaded --}}
                                    <p class="mb-2">{{ $timeline->center->name ?? 'N/A' }}</p>
                                    {{-- Display center name, provide fallback --}}
                                </div>
                            </li>
                        @endforeach
                        {{-- TODO: Add an @empty block here for the timeline list if needed --}}
                        {{-- Example @empty block: --}}
                        {{-- @empty
                           <li class="timeline-item timeline-item-transparent border-0">
                                <div class="timeline-event">
                                     <div class="timeline-header">
                                          <h6 class="mb-0">{{ __('No employment history recorded.') }}</h6>
                                     </div>
                                </div>
                           </li>
                      @endforelse --}}
                    </ul>
                </div> {{-- End card-body --}}
            </div> {{-- End card --}}
        </div>
    </div> {{-- End row --}}

    {{-- Include the timeline modal partial --}}
    {{-- This partial is assumed to contain the Bootstrap modal structure and Livewire bindings --}}
    @include('_partials\_modals\modal-timeline')

    {{-- Push custom scripts to the stack --}}
    @push('custom-scripts')
        {{-- Script to automatically show the timeline modal if a session flag is set (e.g., after a redirect with input) --}}
        @if (session('openTimelineModal'))
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    // Use jQuery to show the modal by its ID
                    $('#timelineModal').modal('show');
                });
            </script>
        @endif
        {{-- TODO: Add JavaScript for initializing components within the timeline modal if needed (similar to modal-employee.blade.php) --}}
        {{-- Example: document.getElementById('timelineModal').addEventListener('shown.bs.modal', function() { initializeTimelineModalComponents(); }); --}}
        {{-- Example: document.getElementById('timelineModal').addEventListener('hidden.bs.modal', function() { @this.call('resetTimelineForm'); }); --}}
    @endpush

</div> {{-- End Livewire component root element --}}
