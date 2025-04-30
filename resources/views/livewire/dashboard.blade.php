<div>

    @php
        $configData = Helper::appClasses();
        use App\Models\User; // Use the User model directly
        use Carbon\Carbon;

        // Access computed properties and assign them to local variables
        // Doing this explicitly in the Blade can sometimes work around variable scoping issues
        $dashboardMessagesStatus = $this->messagesStatus; // Fetch messagesStatus
        // $dashboardUserNotifications = $this->userNotifications; // Already handled by explicit assignment below
        $dashboardLeaveRecords = $this->leaveRecords; // Fetch leaveRecords
        $dashboardUserEmailApplications = $this->userEmailApplications; // Fetch email applications
        $dashboardUserLoanApplications = $this->userLoanApplications; // Fetch loan applications
        $dashboardChangelogs = $this->changelogs; // Fetch changelogs
        // Note: activeEmployees, confirmedId, leaveTypes, selectedEmployeeId,
        // newLeaveInfo, isEdit, employeeLeaveId, employeeLeaveRecord, fromDateLimit,
        // employeePhoto are public properties and should ideally be available automatically.
    @endphp

    @section('title', 'Dashboard')

    @section('vendor-style')

    @endsection

    @section('page-style')
        <style>
            .match-height>[class*='col'] {
                display: flex;
                flex-flow: column;
            }

            .match-height>[class*='col']>.card {
                flex: 1 1 auto;
            }

            .btn-tr {
                opacity: 0;
            }

            tr:hover .btn-tr {
                display: inline-block;
                opacity: 1;
            }

            tr:hover .td {
                color: #7367f0 !important;
            }
        </style>
    @endsection

    {{-- Alerts --}}
    {{-- Ensure the path '_partials/_alerts/alert-general' is correct for your project --}}
    @include('_partials/_alerts/alert-general')

    {{-- <nav aria-label="breadcrumb">
  <ol class="breadcrumb">
    <li class="breadcrumb-item">
      <a href="{{ route('dashboard') }}">Dashboard</a>
    </li>
  </ol>
</nav> --}}

    <div class="row match-height">
        <div class="col-xl-4 mb-4 col-lg-5 col-12">
            {{-- If you find <x-card> here or around the card structure, comment it out --}}
            <div class="card h-100"> {{-- Currently using div class="card" --}}
                <div class="card-header pb-0">
                    <div class="d-flex justify-content-between">
                        <div class="card-title mb-0">
                            {{-- Use Auth::user() and its attributes directly --}}
                            <h4 class="card-title mb-1">{{ __('Hi,') }}
                                {{ Auth::user()->full_name ?? Auth::user()->name }}! 👋</h4>
                            <small class="text-muted">{{ __('Welcome to your dashboard') }}</small> {{-- Adjusted welcome message --}}
                        </div>
                    </div>
                </div>

                <div class="d-flex align-items-end row h-100">
                    <div class="col-7">
                        <div class="card-body text-nowrap">
                            <h5 id="date" class="text-primary mt-3 mb-1"></h5>
                            <h5 id="time" class="text-primary mb-2"></h5>
                            <div class="btn-group dropend">
                                <button type="button" class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown"
                                    aria-haspopup="true" aria-expanded="false"><i
                                        class="ti ti-menu-2 ti-xs me-1"></i>{{ __('Add New') }}</button>
                                <ul class="dropdown-menu">
                                    {{-- Existing HR Links --}}
                                    @can('create employees')
                                        {{-- Example permission --}}
                                        <li><a class="dropdown-item" href="{{ route('structure-employees') }}"><i
                                                    class="ti ti-user ti-xs me-1"></i> {{ __('Employee') }}</a></li>
                                        {{-- Adjusted icon --}}
                                        <li>
                                            <hr class="dropdown-divider">
                                        </li>
                                    @endcan
                                    @can('create fingerprints')
                                        {{-- Example permission --}}
                                        <li><a class="dropdown-item" href="{{ route('attendance-fingerprints') }}"><i
                                                    class="ti ti-fingerprint ti-xs me-1"></i>{{ __('Fingerprint') }}</a>
                                        </li> {{-- Adjusted icon --}}
                                    @endcan
                                    @can('create leaves')
                                        {{-- Example permission --}}
                                        {{-- Assuming showCreateLeaveModal and leaveModal are handled by the Livewire component --}}
                                        <li><a wire:click='showCreateLeaveModal' class="dropdown-item"
                                                data-bs-toggle="modal" data-bs-target="#leaveModal" href="#"><i
                                                    class="ti ti-calendar-event ti-xs me-1"></i>{{ __('Leave Request') }}</a>
                                            {{-- Adjusted icon and text --}}
                                        </li>
                                        <li>
                                            <hr class="dropdown-divider">
                                        </li> {{-- Divider before new items --}}
                                    @endcan

                                    {{-- 👇 New MOTAC Resource Management Links 👇 --}}
                                    {{-- Using the full, correctly registered route names --}}
                                    <li>
                                        <a class="dropdown-item"
                                            href="{{ route('resource-management.email-applications.create') }}">
                                            <i class="ti ti-mail ti-xs me-1"></i>
                                            {{ __('Email/User ID Request') }}
                                        </a>
                                    </li> {{-- Updated link --}}
                                    <li>
                                        <a class="dropdown-item"
                                            href="{{ route('resource-management.loan-applications.create') }}">
                                            <i class="ti ti-laptop ti-xs me-1"></i>
                                            {{ __('ICT Equipment Loan') }}
                                        </a>
                                    </li> {{-- Updated link --}}
                                    {{-- ☝️ End New MOTAC Resource Management Links ☝️ --}}

                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="col-5 text-center text-sm-left h-100 d-flex align-items-end">
                        <div class="card-body pb-0 px-0 px-md-4 w-100">
                            {{-- Adjust asset path if needed --}}
                            <img src="{{ asset('assets/img/illustrations/card-advance-sale.png') }}" class="img-fluid"
                                alt="view sales" style="object-fit: contain; width: 100%; height: auto;">
                        </div>
                    </div>
                </div>
            </div>
            {{-- If you find </x-card> here, comment it out --}}
        </div>

        {{-- Existing Statistics Card --}}
        <div class="col-xl-8 mb-4 col-lg-7 col-12">
            {{-- If you find <x-card> here or around the card structure, comment it out --}}
            <div class="card h-100"> {{-- Currently using div class="card" --}}
                <div class="card-header">
                    <div class="d-flex justify-content-between mb-3">
                        <h5 class="card-title mb-0">{{ __('Statistics') }}</h5>
                        {{-- Keep existing statistics update status --}}
                        @can('read sms')
                            {{-- Example permission --}}
                            <small
                                class="text-muted">{{ $accountBalance['status'] == 200 ? __('Updated recently') : __('Error, Update unavailable') }}</small>
                        @endcan
                    </div>
                </div>
                {{-- Keep existing statistics display --}}
                @can('read sms')
                    {{-- Example permission --}}
                    <div class="card-body">
                        <div class="row gy-3">
                            <div class="col-md-3 col-6">
                                <div class="d-flex align-items-center">
                                    <div class="badge rounded-pill bg-label-primary me-3 p-2"><i
                                            class="ti ti-activity ti-sm"></i></div>
                                    <div class="card-info">
                                        <h5 class="mb-0">{{ $accountBalance['is_active'] }}</h5>
                                        <small>{{ __('API Status') }}</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 col-6">
                                <div class="d-flex align-items-center">
                                    <div class="badge rounded-pill bg-label-primary me-3 p-2"><i
                                            class="ti ti-calculator ti-sm"></i></div>
                                    <div class="card-info">
                                        <h5 class="mb-0">{{ $accountBalance['balance'] }}</h5>
                                        <small>{{ __('API Balance') }}</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 col-6">
                                <div class="d-flex align-items-center">
                                    <div class="badge rounded-pill bg-label-success me-3 p-2"><i
                                            class="ti ti-speakerphone ti-sm"></i></div>
                                    <div class="card-info">
                                        {{-- Use the new local variable --}}
                                        <h5 class="mb-0">{{ $dashboardMessagesStatus['sent'] }}</h5>
                                        <small>{{ __('Successful SMS') }}</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 col-6">
                                <div class="d-flex align-items-center">
                                    {{-- Assuming sendPendingMessages is a Livewire action --}}
                                    <div wire:click='sendPendingMessages'
                                        class="badge rounded-pill bg-label-danger me-3 p-2" style="cursor: pointer"><i
                                            class="ti ti-send ti-sm"></i></div>
                                    <div class="card-info">
                                        {{-- Use the new local variable --}}
                                        <h5 class="mb-0">{{ $dashboardMessagesStatus['unsent'] }}</h5>
                                        <small>{{ __('Pending SMS') }}</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endcan
                @can('create leaves')
                    {{-- Example permission --}}
                    <div class="card-body pt-0">
                        <div class="row gy-3">
                            <div class="col-md-3 col-6">
                                <div class="d-flex align-items-center">
                                    <div class="badge rounded-pill bg-label-secondary me-3 p-2"><i
                                            class="ti ti-users ti-sm"></i></div>
                                    <div class="card-info">
                                        <h5 class="mb-0">{{ count($activeEmployees) }}</h5>
                                        <small>{{ __('Active Employees') }}</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 col-6">
                                <div class="d-flex align-items-center">
                                    <div class="badge rounded-pill bg-label-secondary me-3 p-2"><i
                                            class="ti ti-calendar ti-sm"></i></div>
                                    <div class="card-info">
                                        {{-- Use the new local variable --}}
                                        <h5 class="mb-0">{{ count($dashboardLeaveRecords) }}</h5>
                                        <small>{{ __('Today Records') }}</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endcan
            </div>
            {{-- If you find </x-card> here, comment it out --}}
        </div>

        {{-- Kept commented out sections for reference --}}
        {{-- <div class="col-xl-4 col-12"> ... </div> --}}
        {{-- <div class="col-12 col-xl-8 mb-4 col-lg-7"> ... </div> --}}
    </div>

    {{-- 👇 New MOTAC Resource Management Overview for User 👇 --}}
    <div class="row mt-4">
        <div class="col-md-6 mb-4">
            {{-- If you find <x-card> here or around the card structure, comment it out --}}
            <div class="card"> {{-- Currently using div class="card" --}}
                <h5 class="card-header">{{ __('My Pending Applications') }}</h5>
                <div class="table-responsive text-nowrap">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>{{ __('Type') }}</th>
                                <th>{{ __('Purpose') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th>{{ __('Submitted At') }}</th>
                                <th>{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="table-border-bottom-0">
                            {{-- Loop through user's pending Email Applications (using the new local variable) --}}
                            @forelse($dashboardUserEmailApplications as $application)
                                <tr>
                                    <td><i class="ti ti-mail me-2"></i> <strong>{{ __('Email/User ID') }}</strong>
                                    </td>
                                    <td>{{ \Illuminate\Support\Str::limit($application->purpose, 50) }}</td>
                                    {{-- Limit text length --}}
                                    <td><span class="badge bg-label-warning">{{ $application->status }}</span></td>
                                    {{-- Use approval-status-badge component here --}}
                                    <td>{{ $application->created_at->format('Y-m-d') }}</td>
                                    <td>
                                        {{-- Ensure the route 'my-applications.email.show' is correctly defined in your web.php --}}
                                        <a href="{{ route('my-applications.email.show', $application) }}"
                                            class="btn btn-sm btn-outline-primary waves-effect">View</a>
                                        {{-- Link to show page --}}
                                    </td>
                                </tr>
                            @empty
                                {{-- Handle no pending email applications --}}
                            @endforelse

                            {{-- Loop through user's pending Loan Applications (using the new local variable) --}}
                            @forelse($dashboardUserLoanApplications as $application)
                                <tr>
                                    <td><i class="ti ti-laptop me-2"></i> <strong>{{ __('Equipment Loan') }}</strong>
                                    </td>
                                    <td>{{ \Illuminate\Support\Str::limit($application->purpose, 50) }}</td>
                                    {{-- Limit text length --}}
                                    <td><span class="badge bg-label-warning">{{ $application->status }}</span></td>
                                    {{-- Use approval-status-badge component here --}}
                                    <td>{{ $application->created_at->format('Y-m-d') }}</td>
                                    <td>
                                        {{-- Ensure the route 'my-applications.loan.show' is correctly defined in your web.php --}}
                                        <a href="{{ route('my-applications.loan.show', $application) }}"
                                            class="btn btn-sm btn-outline-primary waves-effect">View</a>
                                        {{-- Link to show page --}}
                                    </td>
                                </tr>
                            @empty
                                {{-- Display 'No pending applications' if both lists are empty --}}
                            @endforelse

                            {{-- Display 'No pending applications' if both lists are empty --}}
                            @if (count($dashboardUserEmailApplications) === 0 && count($dashboardUserLoanApplications) === 0)
                                <tr>
                                    <td colspan="5" class="text-center">
                                        <div class="mt-2 mb-2" style="text-align: center">
                                            <h3 class="mb-1 mx-2">{{ __('Great!') }}</h3>
                                            {{-- Adjusted empty state message --}}
                                            <p class="mb-4 mx-2">
                                                {{ __('No pending applications at this time. Your dashboard looks clear!') }}
                                            </p>
                                            <div>
                                                {{-- Adjust asset path if needed --}}
                                                <img src="{{ asset('assets/img/illustrations/page-misc-check-email.png') }}"
                                                    width="200" class="img-fluid">
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
            {{-- If you find </x-card> here, comment it out --}}
        </div>

        <div class="col-md-6 mb-4">
            {{-- If you find <x-card> here or around the card structure, comment it out --}}
            <div class="card"> {{-- Currently using div class="card" --}}
                <h5 class="card-header">{{ __('Recent Notifications') }}</h5>
                <div class="table-responsive text-nowrap" style="max-height: 300px; overflow-y: auto;">
                    {{-- Added max height and scroll --}}
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>{{ __('Type') }}</th>
                                <th>{{ __('Message') }}</th>
                                <th>{{ __('Time') }}</th>
                                {{-- <th>{{ __('Actions') }}</th> --}} {{-- Optional actions column --}}
                            </tr>
                        </thead>
                        <tbody class="table-border-bottom-0">
                            {{-- Debug line was here --}}
                            {{-- Explicitly get the computed property value into a local variable --}}
                            @php
                                // This was already added for userNotifications
                                $recentNotifications = $this->userNotifications; // Access the computed property and assign it
                            @endphp


                            {{-- Loop through user's recent notifications using the local variable --}}
                            @forelse($recentNotifications as $notification)
                                <tr>
                                    <td><span
                                            class="badge bg-label-info">{{ $notification->data['type'] ?? 'Update' }}</span>
                                    </td> {{-- Assuming notification type is in data --}}
                                    <td>{{ \Illuminate\Support\Str::limit($notification->data['message'] ?? '...', 80) }}
                                    </td> {{-- Assuming message is in data --}}
                                    <td>{{ $notification->created_at->diffForHumans() }}</td>
                                    {{-- Optional actions --}}
                                    {{-- <td>
                                  <a href="{{ $notification->data['link'] ?? '#' }}" class="btn btn-sm btn-outline-secondary waves-effect">View</a>
                                  </td> --}}
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center">{{ __('No recent notifications.') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            {{-- If you find </x-card> here, comment it out --}}
        </div>
    </div>
    {{-- ☝️ End New MOTAC Resource Management Overview for User ☝️ --}}


    {{-- Existing Today Leaves Card --}}
    <div class="row mt-4"> {{-- Adjusted margin top --}}
        <div class="col">
            {{-- If you find <x-card> here or around the card structure, comment it out --}}
            <div class="card"> {{-- Currently using div class="card" --}}
                <h5 class="card-header">{{ __('Today Leaves') }}</h5>
                <div class="table-responsive text-nowrap">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th class="col-1">{{ __('ID') }}</th>
                                <th>{{ __('Employee') }}</th>
                                <th class="col-1">{{ __('Type') }}</th>
                                <th style="text-align: center">{{ __('Details') }}</th>
                                <th style="text-align: center">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="table-border-bottom-0">
                            {{-- Loop through leave records (using the new local variable) --}}
                            @forelse($dashboardLeaveRecords as $leave)
                                {{-- Assuming $leaveRecords is available and employee/leaveType relationships are eager loaded --}}
                                <tr>
                                    <td><strong>{{ $leave->id }}</strong></td>
                                    {{-- Accessing employee name via relationship --}}
                                    <td class="td">
                                        {{ $leave->employee->full_name ?? ($leave->employee->name ?? 'N/A') }}</td>
                                    {{-- Accessing leave type via relationship --}}
                                    <td>{{ $leave->leaveType->name ?? 'N/A' }}</td>
                                    <td style="text-align: center">
                                        <span class="badge bg-label-primary mb-2 me-1"
                                            style="font-size: 14px">{{ $leave->from_date . ' -->' . $leave->to_date }}</span>
                                        <br>
                                        @if ($leave->start_at !== null)
                                            <span
                                                class="badge bg-label-secondary me-1">{{ Carbon::parse($leave->start_at)->format('H:i') . ' -->' . Carbon::parse($leave->end_at)->format('H:i') }}</span>
                                        @endif
                                    </td>
                                    <td style="text-align: center">
                                        <button type="button"
                                            class="btn btn-sm btn-tr rounded-pill btn-icon btn-outline-secondary waves-effect">
                                            {{-- Assuming showEditLeaveModal and confirmDestroyLeave are Livewire actions --}}
                                            <span wire:click.prevent="showEditLeaveModal({{ $leave->id }})"
                                                data-bs-toggle="modal" data-bs-target="#leaveModal"
                                                class="ti ti-pencil"></span> {{-- Adjusted icon --}}
                                        </button>
                                        <button type="button"
                                            class="btn btn-sm btn-tr rounded-pill btn-icon btn-outline-danger waves-effect">
                                            <span wire:click.prevent="confirmDestroyLeave({{ $leave->id }})"
                                                class="ti ti-trash"></span> {{-- Adjusted icon --}}
                                        </button>
                                        @if ($confirmedId === $leave->id)
                                            {{-- Assuming confirmedId is Livewire component property --}}
                                            <button wire:click.prevent="destroyLeave" type="button"
                                                class="btn btn-xs btn-danger waves-effect waves-light">
                                                {{ __('Sure?') }}
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6">
                                        <div class="mt-2 mb-2" style="text-align: center">
                                            <h3 class="mb-1 mx-2">{{ __('Oopsie-doodle!') }}</h3>
                                            {{-- Adjusted empty state message --}}
                                            <p class="mb-4 mx-2">
                                                {{ __('No data found, please sprinkle some data in my virtual bowl, and let the fun begin!') }}
                                            </p>
                                            <button class="btn btn-label-primary mb-4" data-bs-toggle="modal"
                                                data-bs-target="#leaveModal">
                                                {{ __('Add New Leave') }}
                                            </button>
                                            <div>
                                                {{-- Adjust asset path if needed --}}
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
            {{-- If you find </x-card> here, comment it out --}}
        </div>
    </div>

    {{-- Existing Changelog Card --}}
    <div class="row mt-4">
        <div class="col">
            {{-- If you find <x-card> here or around the card structure, comment it out --}}
            <div class="card"> {{-- Currently using div class="card" --}}
                <h5 class="card-header">{{ __('Changelog') }}</h5>
                <div class="card-body">
                    @foreach ($dashboardChangelogs as $changelog)
                        {{-- Assuming $changelogs is available --}}
                        <small all class="text-light fw-semibold">{{ $changelog->version }}</small>
                        <dl class="row mt-2">
                            <dt class="col-sm-3">{{ $changelog->title }}</dt>
                            <dd class="col-sm-9">{{ $changelog->description }}</dd>
                        </dl>
                    @endforeach
                </div>
            </div>
            {{-- If you find </x-card> here, comment it out --}}
        </div>
    </div>

    {{-- Existing Modals --}}
    {{-- Ensure the path '_partials/_modals/modal-leaveWithEmployee' is correct for your project --}}
    {{-- CHECK THIS PARTIAL for <x-card> tags if the error persists --}}
    @include('_partials/_modals/modal-leaveWithEmployee')

    @push('custom-scripts')
        {{-- Assuming this works in Livewire view, often needs to be in main layout --}}
        <script>
            // Existing clock script
            function updateClock() {
                const now = new Date();
                const dateOptions = {
                    weekday: 'long',
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric'
                };
                const timeOptions = {
                    hour: '2-digit',
                    minute: '2-digit',
                    second: '2-digit',
                    hour12: false
                };

                const formattedDate = now.toLocaleDateString('en-US', dateOptions);
                const formattedTime = now.toLocaleTimeString('en-US', timeOptions);

                document.getElementById('date').innerHTML = formattedDate;
                document.getElementById('time').innerHTML = formattedTime;
            }

            setInterval(updateClock, 1000); // Update every second
            updateClock(); // Initial call to display clock immediately
        </script>
    @endpush
</div>
