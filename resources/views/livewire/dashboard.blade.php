{{--
    resources/views/livewire/dashboard.blade.php

    This Blade view file for the App\Livewire\Dashboard component is revised to
    serve as an integrated dashboard reflecting the MOTAC Integrated Resource Management System design.
    It provides a role-based overview of Email/User ID and ICT Equipment Loan applications,
    while maintaining the visual style of the HRMS template.
    Updated route names to match web.php definitions and added null safety checks using optional() and ??.
    This view relies on the corresponding Livewire component to fetch the necessary data.
    Routes for HR sections corrected to match web.php definition (no 'hr.structure.' prefix on names).
--}}

{{-- PHPDoc to inform static analyzers about the component type of $this --}}
@php
    /** @var \App\Livewire\Dashboard $this */
@endphp

<div>
    @php
        // Use the corrected Helpers class (assuming App\Helpers\Helpers exists in your project)
        use App\Helpers\Helpers;
        // Employee model might still be useful, keep if needed for helper methods or relations
        use App\Models\Employee;
        use Carbon\Carbon; // Keep if needed for date formatting in the view
        use Illuminate\Support\Facades\Auth; // Import Auth facade

        // Get application classes (used by the layout, but accessed here)
        $configData = Helpers::appClasses();

        // Assume roles are managed by a package like Spatie (e.g., Spatie\Permission)
        // You'll need to ensure user roles are loaded and available via Auth::user()->hasRole() or similar
$user = Auth::user();
// Use the exact role names defined in your Spatie permissions setup
// Consider using permissions with @can directives directly in the HTML for more granular control
$isAdmin = optional($user)->hasRole('Admin') ?? false;
$isApprover =
    optional($user)->hasRole('Approver') || // Base Approver role
        optional($user)->hasRole('AM') ||
        optional($user)->hasRole('CC') ||
        optional($user)->hasRole('CR') ||
        optional($user)->hasRole('HR') ?? false; // Assuming these HRMS roles also function as Approvers
$isBpmStaff = optional($user)->hasRole('BPM') ?? false; // Assuming BPM role exists
// Assume 'User' is the default role for regular staff, or check if none of the above roles are assigned
        $isRegularUser = !$isAdmin && !$isApprover && !$isBpmStaff; // Simplified check

    @endphp

    {{-- Styles from the original template --}}
    {{-- Consider moving these styles to a dedicated CSS file or your layout --}}
    <style>
        .match-height>[class*='col'] {
            display: flex;
            flex-flow: column;
        }

        .match-height>[class*='col']>.card {
            flex: 1 1 auto;
        }

        /* Styles for table row hover effects */
        .btn-tr {
            opacity: 0;
            transition: opacity 0.2s ease-in-out;
        }

        tr:hover .btn-tr {
            opacity: 1;
        }

        tr:hover .td {
            color: #7367f0 !important;
            /* Assuming a Bootstrap theme color */
        }
    </style>

    {{-- Alerts - Included directly in the component view --}}
    @include('_partials/_alerts/alert-general')

    {{-- Removed Breadcrumbs for a cleaner dashboard feel --}}

    <div class="row match-height">
        {{-- Welcome Card (Retained from original) --}}
        <div class="col-xl-4 mb-4 col-lg-5 col-12">
            <div class="card h-100">
                <div class="card-header pb-0">
                    <div class="d-flex justify-content-between">
                        <div class="card-title mb-0">
                            {{-- Display employee first name from the component property --}}
                            {{-- Added optional() for $employeeFirstName --}}
                            <h4 class="card-title mb-1">{{ __('Hi,') }} {{ $employeeFirstName ?? 'User' }} 👋!</h4>
                            <small class="text-muted">{{ __('Start your day with a smile') }}</small>
                        </div>
                    </div>
                </div>
                <div class="d-flex align-items-end row h-100">
                    <div class="col-7">
                        <div class="card-body text-nowrap">
                            {{-- Live clock displayed via JS --}}
                            <h5 id="date" class="text-primary mt-3 mb-1"></h5>
                            <h5 id="time" class="text-primary mb-2"></h5>

                            {{-- Integrated "Add New" Dropdown for both new modules --}}
                            <div class="btn-group dropend">
                                <button type="button" class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown"
                                    aria-haspopup="true" aria-expanded="false"><i
                                        class="ti ti-plus me-1"></i>{{ __('Add New Application') }}</button>
                                <ul class="dropdown-menu">
                                    {{-- Option for Email/User ID Application --}}
                                    {{-- Using Spatie permission check --}}
                                    @can('create email applications')
                                        <li>
                                            {{-- Link to the Email Application form - Route name is correct --}}
                                            <a class="dropdown-item"
                                                href="{{ route('resource-management.email-applications.create') }}">
                                                <i class="ti ti-mail ti-xs me-1"></i> {{ __('Email/User ID Application') }}
                                            </a>
                                        </li>
                                    @endcan

                                    {{-- Option for ICT Equipment Loan Application --}}
                                    {{-- Using Spatie permission check --}}
                                    @can('create loan applications')
                                        <li>
                                            <hr class="dropdown-divider">
                                        </li>
                                        <li>
                                            {{-- Link to the Loan Application form - Route name is correct --}}
                                            <a class="dropdown-item"
                                                href="{{ route('resource-management.loan-applications.create') }}">
                                                <i class="ti ti-laptop ti-xs me-1"></i> {{ __('ICT Equipment Loan') }}
                                            </a>
                                        </li>
                                    @endcan

                                    {{-- Keep other "Add New" options if they are still part of the dashboard scope for relevant roles --}}
                                    {{-- Example from original HRMS code, ensure these routes and permissions are still needed --}}
                                    @can('create employees')
                                        <li>
                                            <hr class="dropdown-divider">
                                        </li>
                                        {{-- Corrected route name based on web.php --}}
                                        <li><a class="dropdown-item" href="{{ route('structure-employees') }}"><i
                                                    class="ti ti-users ti-xs me-1"></i> {{ __('Employee') }}</a></li>
                                    @endcan
                                    @can('create fingerprints')
                                        <li>
                                            <hr class="dropdown-divider">
                                        </li>
                                        {{-- Corrected route name based on web.php --}}
                                        <li><a class="dropdown-item" href="{{ route('attendance-fingerprints') }}"><i
                                                    class="ti ti-fingerprint ti-xs me-1"></i>{{ __('Fingerprint') }}</a>
                                        </li>
                                    @endcan
                                    @can('create leaves')
                                        <li>
                                            <hr class="dropdown-divider">
                                        </li>
                                        {{-- Corrected route name based on web.php --}}
                                        {{-- Linking to the Leaves list page as per web.php routing --}}
                                        <li><a class="dropdown-item" href="{{ route('attendance-leaves') }}"><i
                                                    class="ti ti-calendar ti-xs me-1"></i>{{ __('Leave') }}</a>
                                        </li>
                                    @endcan
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="col-5 text-center text-sm-left h-100 d-flex align-items-end">
                        <div class="card-body pb-0 px-0 px-md-4 w-100">
                            {{-- Illustration Image --}}
                            <img src="{{ asset('assets/img/illustrations/card-advance-sale.png') }}" class="img-fluid"
                                alt="view sales" style="object-fit: contain; width: 100%; height: auto;">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Integrated Statistics Card --}}
        <div class="col-xl-8 mb-4 col-lg-7 col-12">
            <div class="card h-100">
                <div class="card-header">
                    <div class="d-flex justify-content-between mb-3">
                        <h5 class="card-title mb-0">{{ __('Overview Statistics') }}</h5>
                        {{-- You might display a general system status or last update time here --}}
                        {{-- @can('view dashboard statistics') --}} {{-- Assuming a permission to view these stats --}}
                        <small class="text-muted">{{ __('Data updated on latest render') }}</small>
                        {{-- Clarify data freshness --}}
                        {{-- @endcan --}}
                    </div>
                </div>
                <div class="card-body">
                    <div class="row gy-3">
                        {{-- --- Email/User ID Statistics --- --}}
                        @if ($isRegularUser || $isAdmin)
                            {{-- Show user's application stats to regular users and admins --}}
                            {{-- Ensure $userEmailApplicationsPendingCount is fetched in component --}}
                            <div class="col-md-4 col-6">
                                <div class="d-flex align-items-center">
                                    <div class="badge rounded-pill bg-label-info me-3 p-2"><i
                                            class="ti ti-mail ti-sm"></i></div>
                                    <div class="card-info">
                                        <h5 class="mb-0">{{ $userEmailApplicationsPendingCount ?? 0 }}</h5>
                                        <small>{{ __('Your Pending Email/User ID Apps') }}</small>
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if ($isApprover || $isAdmin)
                            {{-- Show pending approval stats to approvers and admins --}}
                            {{-- Ensure $pendingEmailApprovalsCount is fetched in component --}}
                            <div class="col-md-4 col-6">
                                <div class="d-flex align-items-center">
                                    <div class="badge rounded-pill bg-label-warning me-3 p-2"><i
                                            class="ti ti-checks ti-sm"></i></div>
                                    <div class="card-info">
                                        <h5 class="mb-0">{{ $pendingEmailApprovalsCount ?? 0 }}</h5>
                                        <small>{{ __('Pending Email/User ID Approvals') }}</small>
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if ($isBpmStaff || $isAdmin)
                            {{-- Show IT processing stats to BPM and admins --}}
                            {{-- Ensure $pendingEmailProvisioningCount is fetched in component --}}
                            <div class="col-md-4 col-6">
                                <div class="d-flex align-items-center">
                                    <div class="badge rounded-pill bg-label-primary me-3 p-2"><i
                                            class="ti ti-server ti-sm"></i></div>
                                    <div class="card-info">
                                        <h5 class="mb-0">{{ $pendingEmailProvisioningCount ?? 0 }}</h5>
                                        <small>{{ __('Pending Email Provisioning') }}</small>
                                    </div>
                                </div>
                            </div>
                        @endif

                        {{-- --- ICT Equipment Loan Statistics --- --}}
                        @if ($isRegularUser || $isAdmin)
                            {{-- Show user's loan stats to regular users and admins --}}
                            {{-- Ensure $userLoanApplicationsPendingCount is fetched in component --}}
                            <div class="col-md-4 col-6">
                                <div class="d-flex align-items-center">
                                    <div class="badge rounded-pill bg-label-info me-3 p-2"><i
                                            class="ti ti-laptop ti-sm"></i></div>
                                    <div class="card-info">
                                        <h5 class="mb-0">{{ $userLoanApplicationsPendingCount ?? 0 }}</h5>
                                        <small>{{ __('Your Pending Loan Apps') }}</small>
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if ($isApprover || $isAdmin)
                            {{-- Show pending loan approval stats to approvers and admins --}}
                            {{-- Ensure $pendingLoanApprovalsCount is fetched in component --}}
                            <div class="col-md-4 col-6">
                                <div class="d-flex align-items-center">
                                    <div class="badge rounded-pill bg-label-warning me-3 p-2"><i
                                            class="ti ti-clipboard-check ti-sm"></i></div>
                                    <div class="card-info">
                                        <h5 class="mb-0">{{ $pendingLoanApprovalsCount ?? 0 }}</h5>
                                        <small>{{ __('Pending Loan Approvals') }}</small>
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if ($isBpmStaff || $isAdmin)
                            {{-- Show BPM processing stats to BPM and admins --}}
                            {{-- Ensure $pendingLoanIssuanceCount is fetched in component --}}
                            <div class="col-md-4 col-6">
                                <div class="d-flex align-items-center">
                                    <div class="badge rounded-pill bg-label-success me-3 p-2"><i
                                            class="ti ti-package ti-sm"></i></div>
                                    <div class="card-info">
                                        <h5 class="mb-0">{{ $pendingLoanIssuanceCount ?? 0 }}</h5>
                                        <small>{{ __('Pending Loan Issuance') }}</small>
                                    </div>
                                </div>
                            </div>
                            {{-- Ensure $equipmentAvailableCount is fetched in component --}}
                            <div class="col-md-4 col-6">
                                <div class="d-flex align-items-center">
                                    <div class="badge rounded-pill bg-label-secondary me-3 p-2"><i
                                            class="ti ti-box ti-sm"></i></div>
                                    <div class="card-info">
                                        <h5 class="mb-0">{{ $equipmentAvailableCount ?? 0 }}</h5>
                                        <small>{{ __('Available Equipment') }}</small>
                                    </div>
                                </div>
                            </div>
                        @endif


                        {{-- Keep other relevant statistics if needed (e.g., Active Employees if still part of scope) --}}
                        @if ($isAdmin || $isBpmStaff)
                            {{-- Example: Active Employees shown to Admin/BPM --}}
                            {{-- Ensure $activeEmployees is initialized and fetched --}}
                            <div class="col-md-4 col-6">
                                <div class="d-flex align-items-center">
                                    <div class="badge rounded-pill bg-label-users me-3 p-2"><i
                                            class="ti ti-users ti-sm"></i></div>
                                    <div class="card-info">
                                        {{-- Added null coalescing for count --}}
                                        <h5 class="mb-0">{{ $activeEmployeesCount ?? 0 }}</h5>
                                        <small>{{ __('Active Employees') }}</small>
                                    </div>
                                </div>
                            </div>
                        @endif

                        {{-- SMS Statistics (conditional based on permission, kept from original) --}}
                        @can('read sms')
                            {{-- Check if user has permission to read SMS stats --}}
                            {{-- Ensure $messagesStatus is initialized and has 'sent' and 'unsent' keys --}}
                            <div class="col-md-4 col-6">
                                <div class="d-flex align-items-center">
                                    <div class="badge rounded-pill bg-label-success me-3 p-2"><i
                                            class="ti ti-speakerphone ti-sm"></i></div>
                                    <div class="card-info">
                                        <h5 class="mb-0">{{ $messagesStatus['sent'] ?? 0 }}</h5>
                                        <small>{{ __('Successful SMS') }}</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 col-6">
                                <div class="d-flex align-items-center">
                                    {{-- Wire click to send pending messages --}}
                                    {{-- Note: This assumes the Dashboard component handles sending SMS. Confirm if this logic remains here. --}}
                                    <div wire:click='sendPendingMessages'
                                        class="badge rounded-pill bg-label-danger me-3 p-2" style="cursor: pointer"><i
                                            class="ti ti-send ti-sm"></i></div>
                                    <div class="card-info">
                                        <h5 class="mb-0">{{ $messagesStatus['unsent'] ?? 0 }}</h5>
                                        <small>{{ __('Pending SMS') }}</small>
                                    </div>
                                </div>
                            </div>
                        @endcan
                    </div>
                </div>
            </div>

            {{-- Commented out Chart Sections - Removed for clarity in integrated design --}}
            {{-- <div class="col-xl-8 mb-4 col-lg-7 col-12"> ... </div> --}}
            {{-- <div class="col-xl-4 mb-4 col-lg-5 col-12 order-1 order-md-0"> ... </div> --}}
            {{-- <div class="col-lg-6 col-12 mb-4"> ... </div> --}}
            {{-- <div class="col-lg-6 col-12 mb-4"> ... </div> --}}
            {{-- <div class="col-lg-6 col-12 mb-4"> ... </div> --}}
            {{-- <div class="col-lg-6 col-12 mb-4"> ... </div> --}}
        </div>

        {{-- Integrated Recent Activity / Pending Items Sections --}}
        <div class="row mt-4">
            {{-- Section for Regular Users: Your Pending Applications --}}
            @if ($isRegularUser)
                <div class="col-12 mb-4">
                    <div class="card">
                        <h5 class="card-header">{{ __('Your Pending Applications') }}</h5>
                        <div class="table-responsive text-nowrap">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>{{ __('Type') }}</th>
                                        <th>{{ __('Details') }}</th>
                                        <th>{{ __('Status') }}</th>
                                        <th>{{ __('Submitted At') }}</th>
                                        <th>{{ __('Actions') }}</th> {{-- e.g., View --}}
                                    </tr>
                                </thead>
                                <tbody class="table-border-bottom-0">
                                    {{-- Loop through user's pending Email Applications and Loan Applications --}}
                                    {{-- You'll need a property like $userPendingApplications in your component --}}
                                    {{-- Ensure $userPendingApplications is a collection/array containing mixed application objects --}}
                                    @forelse($userPendingApplications ?? [] as $application)
                                        <tr>
                                            <td>
                                                {{-- Display type based on the model instance --}}
                                                <strong>
                                                    @if ($application instanceof \App\Models\EmailApplication)
                                                        <span
                                                            class="badge bg-label-info">{{ __('Email/User ID') }}</span>
                                                    @elseif ($application instanceof \App\Models\LoanApplication)
                                                        <span
                                                            class="badge bg-label-primary">{{ __('ICT Loan') }}</span>
                                                    @else
                                                        <span
                                                            class="badge bg-label-secondary">{{ __('Unknown') }}</span>
                                                    @endif
                                                </strong>
                                            </td>
                                            <td>
                                                {{-- Display relevant details based on type --}}
                                                @if ($application instanceof \App\Models\EmailApplication)
                                                    {{ optional($application)->purpose ?? 'N/A' }}
                                                @elseif ($application instanceof \App\Models\LoanApplication)
                                                    {{ optional($application)->purpose ?? 'N/A' }} -
                                                    ({{ optional($application)->loan_start_date ? Carbon::parse(optional($application)->loan_start_date)->format('Y-m-d') : 'N/A' }}
                                                    to
                                                    {{ optional($application)->loan_end_date ? Carbon::parse(optional($application)->loan_end_date)->format('Y-m-d') : 'N/A' }})
                                                @endif
                                            </td>
                                            <td>
                                                {{-- Display status (needs appropriate styling) --}}
                                                {{-- Added optional() for status --}}
                                                {{-- You might want a helper or Livewire method to get localized/styled status --}}
                                                <span
                                                    class="badge bg-label-warning">{{ optional($application)->status ?? __('Unknown Status') }}</span>
                                            </td>
                                            {{-- Added optional() for created_at and Carbon parsing --}}
                                            <td>{{ optional($application)->created_at ? Carbon::parse(optional($application)->created_at)->format('Y-m-d H:i') : 'N/A' }}
                                            </td>
                                            <td>
                                                {{-- Link to view the specific application details - Corrected route names and pass model --}}
                                                {{-- Added check if $application is not null before generating route --}}
                                                @if ($application)
                                                    @if ($application instanceof \App\Models\EmailApplication)
                                                        <a href="{{ route('my-applications.email.show', $application) }}"
                                                            class="btn btn-sm btn-outline-secondary"><i
                                                                class="ti ti-eye"></i>
                                                            {{ __('View') }}</a>
                                                    @elseif ($application instanceof \App\Models\LoanApplication)
                                                        <a href="{{ route('my-applications.loan.show', $application) }}"
                                                            class="btn btn-sm btn-outline-secondary"><i
                                                                class="ti ti-eye"></i>
                                                            {{ __('View') }}</a>
                                                    @endif
                                                @else
                                                    N/A
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center">
                                                {{ __('No pending applications.') }}
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Section for Approvers: Pending Approvals --}}
            @if ($isApprover)
                <div class="col-12 mb-4">
                    <div class="card">
                        <h5 class="card-header">{{ __('Pending Approvals') }}</h5>
                        {{-- Link to the full Approvals Dashboard --}}
                        <div class="card-body">
                            {{-- Ensure 'approvals.index' route exists and leads to a dedicated approval list page --}}
                            <a href="{{ route('approvals.index') }}" class="btn btn-primary mb-3">
                                <i class="ti ti-dashboard me-1"></i> {{ __('View Full Approval Dashboard') }}
                            </a>
                            {{-- You could also show a limited list here if needed, similar to the user's pending apps table --}}
                        </div>
                        {{-- Removed the table here and added a link to the dedicated approvals dashboard --}}
                    </div>
                </div>
            @endif

            {{-- Section for BPM Staff: Items Needing Processing / Due for Return --}}
            {{-- This block seems to contain two tables for BPM staff --}}
            @if ($isBpmStaff)
                {{-- First Table: Pending Equipment Issuance --}}
                <div class="col-lg-6 col-12 mb-4">
                    <div class="card">
                        <h5 class="card-header">{{ __('Pending Equipment Issuance') }}</h5>
                        <div class="table-responsive text-nowrap">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>{{ __('Application ID') }}</th>
                                        <th>{{ __('Applicant') }}</th>
                                        <th>{{ __('Items') }}</th>
                                        <th>{{ __('Actions') }}</th> {{-- e.g., Process Issuance --}}
                                    </tr>
                                </thead>
                                <tbody class="table-border-bottom-0">
                                    {{-- Loop through loan applications pending issuance --}}
                                    {{-- You'll need a property like $pendingLoanIssuanceList in your component --}}
                                    {{-- Ensure $pendingLoanIssuanceList is a collection of LoanApplication objects with 'user' and 'items' relations loaded --}}
                                    @forelse($pendingLoanIssuanceList ?? [] as $loanApplication)
                                        <tr>
                                            {{-- Added optional() for id --}}
                                            <td>#{{ optional($loanApplication)->id ?? 'N/A' }}</td>
                                            {{-- Added optional() for nested user relationship --}}
                                            <td>{{ optional(optional($loanApplication)->user)->full_name ?? 'N/A' }}
                                            </td>
                                            <td>
                                                {{-- List requested/approved items --}}
                                                {{-- Ensure 'items' relation is loaded and it contains relevant info like equipment_type and quantity --}}
                                                {{-- Added optional() for items relationship and item properties --}}
                                                @forelse(optional($loanApplication)->items ?? [] as $item)
                                                    {{ optional($item)->quantity_approved ?? (optional($item)->quantity_requested ?? 0) }}
                                                    x
                                                    {{ optional($item)->equipment_type ?? 'N/A' }}@unless ($loop->last)
                                                    <br>
                                                @endunless
                                            @empty
                                                N/A
                                            @endforelse
                                        </td>
                                        <td>
                                            {{-- Link to the issuance processing page - Corrected route name and pass model --}}
                                            {{-- Added check if $loanApplication is not null before generating route --}}
                                            @if ($loanApplication)
                                                {{-- Ensure 'admin.bpm.issue.form' route exists --}}
                                                <a href="{{ route('admin.bpm.issue.form', $loanApplication) }}"
                                                    class="btn btn-sm btn-outline-success"><i
                                                        class="ti ti-package"></i>
                                                    {{ __('Process Issuance') }}</a>
                                            @else
                                                N/A
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center">
                                            {{ __('No loans pending issuance.') }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Second Table: Items Due for Return --}}
            <div class="col-lg-6 col-12 mb-4">
                <div class="card">
                    <h5 class="card-header">{{ __('Items Due for Return') }}</h5>
                    <div class="table-responsive text-nowrap">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>{{ __('Transaction ID') }}</th> {{-- Changed from Application ID --}}
                                    <th>{{ __('Borrower') }}</th>
                                    <th>{{ __('Due Date') }}</th>
                                    <th>{{ __('Actions') }}</th> {{-- e.g., Process Return --}}
                                </tr>
                            </thead>
                            <tbody class="table-border-bottom-0">
                                {{-- Loop through loan applications/transactions due for return --}}
                                {{-- You'll need a property like $itemsDueForReturnList in your component --}}
                                {{-- This might be a collection of LoanApplication or LoanTransaction objects --}}
                                {{-- Ensure necessary relations are loaded --}}
                                @forelse($itemsDueForReturnList ?? [] as $transaction)
                                    {{-- Looping through transactions --}}
                                    <tr>
                                        {{-- Assuming $transaction is a LoanTransaction model --}}
                                        {{-- Added optional() for properties --}}
                                        <td>#{{ optional($transaction)->id ?? 'N/A' }}</td> {{-- Use transaction ID --}}
                                        {{-- Access borrower through loanApplication relationship --}}
                                        <td>{{ optional(optional(optional($transaction)->loanApplication)->user)->full_name ?? 'N/A' }}
                                        </td>
                                        {{-- Access due date through loanApplication relationship --}}
                                        <td>{{ optional(optional($transaction)->loanApplication)->loan_end_date ? Carbon::parse(optional(optional($transaction)->loanApplication)->loan_end_date)->format('Y-m-d') : 'N/A' }}
                                        </td>
                                        <td>
                                            {{-- Link to the return processing page - Ensure route exists --}}
                                            @if ($transaction)
                                                {{-- Ensure 'admin.bpm.return.form' route exists and pass the LoanTransaction model --}}
                                                <a href="{{ route('admin.bpm.return.form', $transaction) }}"
                                                    class="btn btn-sm btn-outline-primary"><i
                                                        class="ti ti-box"></i>
                                                    {{ __('Process Return') }}</a>
                                            @else
                                                N/A
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center">
                                            {{ __('No items due for return.') }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif

        {{-- Example of a section for Admins - Could show system-wide overview or reports --}}
        @if ($isAdmin)
            <div class="col-12">
                <div class="card">
                    <h5 class="card-header">{{ __('System Overview (Admin)') }}</h5>
                    <div class="card-body">
                        <p>{{ __('This section can display system-wide reports or controls for administrators.') }}
                        </p>
                        {{-- Link to system reports, user management, equipment management etc. --}}
                        {{-- Corrected route name based on web.php --}}
                        <a href="{{ route('admin.equipment.index') }}" class="btn btn-secondary me-2">
                            <i class="ti ti-settings me-1"></i> {{ __('Manage Equipment') }}
                        </a>
                        {{-- Corrected route name based on web.php --}}
                        <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
                            <i class="ti ti-users me-1"></i> {{ __('Manage Users') }}
                        </a>
                        {{-- Add more admin links as needed based on your admin routes --}}
                        {{-- Example: Link to manage grades --}}
                        @can('manage grades')
                            {{-- Assuming a permission for managing grades --}}
                            {{-- Ensure 'admin.grades.index' route exists --}}
                            <a href="{{ route('admin.grades.index') }}" class="btn btn-secondary me-2">
                                <i class="ti ti-award me-1"></i> {{ __('Manage Grades') }}
                            </a>
                        @endcan
                        {{-- Example: Link to admin reports --}}
                        @can('view admin reports')
                            {{-- Assuming a permission for admin reports --}}
                            {{-- Ensure 'admin.reports.equipment' route exists --}}
                            <a href="{{ route('admin.reports.equipment') }}" class="btn btn-secondary me-2">
                                <i class="ti ti-report me-1"></i> {{ __('Equipment Report') }}
                            </a>
                            {{-- Ensure 'admin.reports.email-accounts' route exists --}}
                            <a href="{{ route('admin.reports.email-accounts') }}" class="btn btn-secondary me-2">
                                <i class="ti ti-report-mail me-1"></i> {{ __('Email Accounts Report') }}
                            </a>
                            {{-- Ensure 'admin.reports.loan-applications' route exists --}}
                            <a href="{{ route('admin.reports.loan-applications') }}" class="btn btn-secondary me-2">
                                <i class="ti ti-report-analytics me-1"></i> {{ __('Loan Applications Report') }}
                            </a>
                        @endcan


                    </div>
                </div>
            </div>
        @endif

        {{-- Section for displaying Change Logs --}}
        @if ($changelogs->isNotEmpty()) {{-- Show only if there are changelogs --}}
            <div class="col-12 mt-4">
                <div class="card">
                    <h5 class="card-header">{{ __('Recent Change Logs') }}</h5>
                    <div class="table-responsive text-nowrap">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>{{ __('Version') }}</th>
                                    <th>{{ __('Title') }}</th>
                                    <th>{{ __('Description') }}</th>
                                    {{-- Removed columns not present in changelogs table --}}
                                    <th>{{ __('Created At') }}</th>
                                </tr>
                            </thead>
                            <tbody class="table-border-bottom-0">
                                @forelse($changelogs ?? [] as $changelog)
                                    <tr>
                                        <td><strong>{{ optional($changelog)->version ?? 'N/A' }}</strong></td>
                                        <td>{{ optional($changelog)->title ?? 'N/A' }}</td>
                                        <td>{{ optional($changelog)->description ?? 'N/A' }}</td>
                                        {{-- Added optional() for created_at and Carbon parsing --}}
                                        <td>{{ optional($changelog)->created_at ? Carbon::parse(optional($changelog)->created_at)->format('Y-m-d H:i') : 'N/A' }}
                                        </td>
                                        {{-- Removed columns not present in changelogs table --}}
                                        {{--
                                        <td>{{ optional($changelog)->subject_type ?? 'N/A' }}:
                                            {{ optional($changelog)->subject_id ?? 'N/A' }}</td>
                                        --}}
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center">
                                            {{ __('No recent change logs.') }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
        @endif

    </div> {{-- End match-height row --}}

    {{-- Blade and JS for the clock --}}
    <script>
        function updateClock() {
            const now = new Date();
            const dateOptions = {
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            };
            const timeOptions = {
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
                hour12: true
            };

            const dateString = now.toLocaleDateString(undefined, dateOptions);
            const timeString = now.toLocaleTimeString(undefined, timeOptions);

            const dateElement = document.getElementById('date');
            const timeElement = document.getElementById('time');

            if (dateElement) {
                dateElement.textContent = dateString;
            }
            if (timeElement) {
                timeElement.textContent = timeString;
            }
        }

        // Update the clock every second
        setInterval(updateClock, 1000);
        // Initial call to display the clock immediately
        updateClock();
    </script>

    {{-- Include the modal for creating leaves if it's still used in this dashboard component --}}
    {{-- You'll need to ensure the Livewire component handles showing/hiding this modal --}}
    {{-- @include('livewire.leaves.create') --}} {{-- Adjust the include path if necessary --}}
    {{-- Note: Based on web.php, the 'attendance-leaves' route leads to a Livewire component,
          suggesting leaf creation happens on that page, not via a modal from the dashboard.
          The link in the "Add New" dropdown was updated accordingly. --}}


</div>
