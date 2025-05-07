{{-- resources/views/livewire/human-resource/structure/employee-info.blade.php --}}

<div> {{-- Livewire component root element --}}

    @php
        $configData = Helper::appClasses();
    @endphp

    @section('title', 'Employee Info - Structure')

    @section('page-style')
        <style>
            /* Styles for hover effects on action icons and buttons */
            .timeline-icon {
                cursor: pointer;
                opacity: 0;
            }

            .timeline-row:hover .timeline-icon,
            tr:hover .btn-tr {
                display: inline-block;
                opacity: 1;
            }

            .btn-tr {
                opacity: 0;
            }
        </style>
    @endsection


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
                        {{-- Display profile photo using the profile_photo_url accessor --}}
                        <img src="{{ $employee->profile_photo_url }}"
                            alt="{{ $employee->fullName ?? 'Employee' }} Profile Photo"
                            class="d-block h-auto ms-0 ms-sm-4 rounded user-profile-img" width="100px">
                    </div>

                    {{-- Employee Info --}}
                    <div class="flex-grow-1 mt-3 mt-sm-5">
                        <div
                            class="d-flex align-items-md-end align-items-sm-start align-items-center justify-content-md-between justify-content-start mx-4 flex-md-row flex-column gap-4">
                            <div class="user-profile-info">
                                <h4>{{ $employee->fullName ?? 'N/A' }}</h4>
                                <ul
                                    class="list-inline mb-0 d-flex align-items-center flex-wrap justify-content-sm-start justify-content-center gap-2">
                                    {{-- Display employee attributes --}}
                                    <li class="list-inline-item">
                                        <i class='ti ti-color-filter'></i>
                                        {{ $employee->department->name ?? 'N/A Department' }}
                                    </li>
                                    <li class="list-inline-item">
                                        <i class='ti ti-briefcase'></i>
                                        {{ $employee->position->name ?? 'N/A Position' }}
                                    </li>
                                    <li class="list-inline-item">
                                        <i class='ti ti-building'></i> {{ $employee->grade->name ?? 'N/A Grade' }}
                                    </li>
                                    <li class="list-inline-item">
                                        <i class='ti ti-map-pin'></i> {{ $employee->status ?? 'N/A Status' }}
                                    </li>
                                </ul>
                            </div>
                            {{-- Optional: Status/Action button --}}
                            <a href="#" class="btn btn-primary">
                                <i class='ti ti-user-check me-1'></i>{{ __('Connected') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Employee details tabs --}}
    <div class="row">
        <div class="col-xl-12">
            <div class="nav-align-top mb-4">
                <ul class="nav nav-pills flex-column flex-md-row mb-4">
                    <li class="nav-item"><button class="nav-link active" data-bs-toggle="pill"
                            data-bs-target="#profile-overview" type="button" role="tab"
                            aria-controls="profile-overview" aria-selected="true"><i
                                class="ti ti-user-check me-1 ti-xs"></i>Overview</button></li>
                    <li class="nav-item"><button class="nav-link" data-bs-toggle="pill"
                            data-bs-target="#profile-timelines" type="button" role="tab"
                            aria-controls="profile-timelines" aria-selected="false"><i
                                class="ti ti-calendar me-1 ti-xs"></i>Timelines</button></li>
                    <li class="nav-item"><button class="nav-link" data-bs-toggle="pill"
                            data-bs-target="#profile-documents" type="button" role="tab"
                            aria-controls="profile-documents" aria-selected="false"><i
                                class="ti ti-file-description me-1 ti-xs"></i>Documents</button></li>
                </ul>

                <div class="tab-content">
                    {{-- Overview Tab Content --}}
                    <div class="tab-pane fade show active" id="profile-overview" role="tabpanel">
                        <div class="card-body pb-0">
                            <p class="text-uppercase small text-muted">{{ __('Details') }}</p>
                            <ul class="list-unstyled mb-0">
                                {{-- Display additional employee details --}}
                                <li class="mb-2"><span
                                        class="fw-medium me-1">{{ __('Birth Info:') }}</span><span>{{ $employee->birth_and_place ?? 'N/A' }}</span>
                                </li>
                                <li class="mb-2"><span
                                        class="fw-medium me-1">{{ __('Gender:') }}</span><span>{{ $employee->gender ?? 'N/A' }}</span>
                                </li>
                                <li class="mb-2"><span
                                        class="fw-medium me-1">{{ __('National Number/NRIC:') }}</span><span>{{ $employee->national_number ?? 'N/A' }}</span>
                                </li>
                                <li class="mb-2"><span
                                        class="fw-medium me-1">{{ __('Personal Email:') }}</span><span>{{ $employee->personal_email ?? 'N/A' }}</span>
                                </li>
                                <li class="mb-2"><span
                                        class="fw-medium me-1">{{ __('MOTAC Email:') }}</span><span>{{ $employee->motac_email ?? 'N/A' }}</span>
                                </li>
                                <li class="mb-2"><span
                                        class="fw-medium me-1">{{ __('Mobile Number:') }}</span><span>{{ $employee->mobile_number ?? 'N/A' }}</span>
                                </li>
                                <li class="mb-2"><span
                                        class="fw-medium me-1">{{ __('Degree:') }}</span><span>{{ $employee->degree ?? 'N/A' }}</span>
                                </li>
                                <li class="mb-2"><span
                                        class="fw-medium me-1">{{ __('Address:') }}</span><span>{{ $employee->address ?? 'N/A' }}</span>
                                </li>
                                <li class="mb-2"><span
                                        class="fw-medium me-1">{{ __('Service Status:') }}</span><span>{{ $employee->service_status_translated ?? ($employee->service_status ?? 'N/A') }}</span>
                                </li>
                                <li class="mb-2"><span
                                        class="fw-medium me-1">{{ __('Appointment Type:') }}</span><span>{{ $employee->appointment_type ?? 'N/A' }}</span>
                                </li>
                                <li class="mb-2"><span
                                        class="fw-medium me-1">{{ __('Status:') }}</span><span>{{ $employee->status ?? 'N/A' }}</span>
                                </li>
                                <li class="mb-2"><span
                                        class="fw-medium me-1">{{ __('Notes:') }}</span><span>{{ $employee->notes ?? 'N/A' }}</span>
                                </li>
                            </ul>
                        </div>
                    </div>

                    {{-- Timelines Tab Content --}}
                    <div class="tab-pane fade" id="profile-timelines" role="tabpanel">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title m-0 me-2">{{ __('Timelines') }}</h5>
                            <button wire:click.prevent='showNewTimelineModal' type="button"
                                class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#timelineModal">
                                <span class="ti-xs ti ti-plus me-1"></span>{{ __('Add New Timeline') }}
                            </button>
                        </div>
                        <div class="card-body">
                            {{-- Display employee timelines --}}
                            @forelse($this->employeeTimelines as $timeline)
                                <div class="mb-3 timeline-row">
                                    <h6 class="text-muted">{{ $timeline->start_date?->format('Y-m-d') }} -
                                        {{ $timeline->end_date?->format('Y-m-d') ?? __('Present') }}</h6>
                                    <p class="mb-1">
                                        {{ $timeline->position->name ?? 'N/A Position' }} at
                                        {{ $timeline->department->name ?? 'N/A Department' }}
                                        ({{ $timeline->center->name ?? 'N/A Center' }})
                                    </p>
                                    {{-- Actions for timeline --}}
                                    <div class="d-flex align-items-center">
                                        <button wire:click.prevent="showEditTimelineModal({{ $timeline->id }})"
                                            class="btn btn-sm btn-icon item-edit timeline-icon me-2"
                                            title="{{ __('Edit Timeline') }}">
                                            <i class="ti ti-pencil text-primary"></i>
                                        </button>
                                        <button wire:click.prevent="confirmDeleteTimeline({{ $timeline->id }})"
                                            class="btn btn-sm btn-icon item-delete timeline-icon"
                                            title="{{ __('Delete Timeline') }}">
                                            <i class="ti ti-trash text-danger"></i>
                                        </button>
                                        @if ($timeline->end_date !== null)
                                            <button wire:click.prevent="setPresentTimeline({{ $timeline->id }})"
                                                class="btn btn-sm btn-label-success waves-effect waves-light timeline-icon ms-2"
                                                title="{{ __('Set as Present') }}">
                                                {{ __('Set Present') }}
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <p>{{ __('No timeline entries found for this employee.') }}</p>
                            @endforelse
                        </div>
                    </div>

                    {{-- Documents Tab Content --}}
                    <div class="tab-pane fade" id="profile-documents" role="tabpanel">
                        <div class="card-header">
                            <h5 class="card-title m-0 me-2">{{ __('Documents') }}</h5>
                        </div>
                        <div class="card-body">
                            {{-- TODO: Implement Document Management --}}
                            <p>{{ __('Document management section is under development.') }}</p>
                        </div>
                    </div>
                </div> {{-- End tab-content --}}
            </div> {{-- End nav-align-top --}}
        </div> {{-- End col --}}
    </div> {{-- End row --}}

    {{-- Include the timeline modal partial and pass necessary variables --}}
    {{-- FIX: Pass the centers, departments, and positions computed properties to the partial --}}
    @include('_partials._modals.modal-timeline', [
        'centers' => $this->centers,
        'departments' => $this->departments,
        'positions' => $this->positions,
        // Pass any other variables needed by the modal partial here
    ])

    {{-- Push custom scripts to the stack --}}
    @push('custom-scripts')
        @if (session('openTimelineModal'))
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    $('#timelineModal').modal('show');
                });
            </script>
        @endif
        {{-- TODO: Add JavaScript for initializing components within the timeline modal if needed (e.g., date pickers, Select2) --}}
        {{-- This part would typically trigger JS initialization based on the 'timelineModalShown' event dispatched by the Livewire component --}}
    @endpush

</div> {{-- End Livewire component root element --}}
