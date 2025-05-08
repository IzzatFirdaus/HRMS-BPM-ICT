{{--
    resources/views/livewire/sections/menu/vertical-menu.blade.php

    This Blade view file for the App\Livewire\Sections\Menu\VerticalMenu component
    displays the vertical sidebar menu based on the user's roles.
    It hardcodes the menu structure and uses the component's hasRequiredRole method
    to determine which items to display.
    It uses a separate submenu partial (layouts.sections.menu.submenu) for nested items.

    Ensure this file matches the one being loaded if you have multiple files with similar names.
    The error trace indicates this specific file ('vertical-menu.blade.php' with hyphen).
--}}

@php
    // Accessing the Livewire component instance to use its methods and properties
    // $this refers to the App\Livewire\Sections\Menu\VerticalMenu component
    // $userRoles is available from $this->userRoles (a Collection of role names)
    // $configData is assumed to be available globally or passed via a view composer/layout
    // Example: $configData = \App\Helpers\Helpers::appClasses(); // If using a global helper
@endphp

<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">

    {{-- Assuming this section is handled by your main layout and passed down --}}
    @if (!isset($navbarFull))
        <div class="app-brand demo">
            <a href="{{ url('/') }}" class="app-brand-link">
                <span class="app-brand-logo demo">
                    {{-- Assuming _partials.macros includes your SVG logo --}}
                    {{-- Ensure this path is correct for your project --}}
                    @include('_partials.macros', ['height' => 20])
                </span>
                {{-- Assuming config('variables.templateName') exists --}}
                <span class="app-brand-text demo menu-text fw-bold">{{ config('variables.templateName') }}</span>
            </a>

            <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto">
                <i class="ti menu-toggle-icon d-none d-xl-block ti-sm align-middle"></i>
                <i class="ti ti-x d-block d-xl-none ti-sm align-middle"></i>
            </a>
        </div>
    @endif

    <div class="menu-inner-shadow"></div>

    {{-- Main vertical menu list --}}
    <ul class="menu-inner py-1">
        {{--
            This section defines the hardcoded menu structure.
            Each menu item's visibility is controlled by calling $this->hasRequiredRole()
            using the roles that are allowed to see that item.
            Active state is determined using request()->routeIs().
        --}}

        {{-- Dashboard --}}
        {{-- Route name: dashboard, Roles from web.php: Admin|AM|CC|CR|HR --}}
        @php $canSeeDashboard = $this->hasRequiredRole(['Admin', 'AM', 'CC', 'CR', 'HR']); @endphp
        @if ($canSeeDashboard)
            <li class="menu-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                {{-- Using route() helper with the defined route name --}}
                <a href="{{ route('dashboard') }}" class="menu-link">
                    <i class="menu-icon tf-icons ti ti-smart-home"></i> {{-- Example icon --}}
                    <div>{{ __('Dashboard') }}</div>
                </a>
            </li>
        @endif

        {{-- Human Resource Section --}}
        {{-- Check if the user has any role that grants access to the HR parent section --}}
        {{-- Roles from web.php children: Admin|HR|CC (from hr.attendance.*), Admin|HR (from hr.structure.* / hr.*) --}}
        @php $canSeeHrSection = $this->hasRequiredRole(['Admin', 'HR', 'CC']); @endphp
        @if ($canSeeHrSection)
            {{-- 'active open' if any route starting with 'hr.' is current --}}
            <li class="menu-item {{ request()->routeIs('hr.*') ? 'active open' : '' }}">
                <a href="javascript:void(0);" class="menu-link menu-toggle">
                    <i class="menu-icon tf-icons ti ti-users"></i> {{-- Example icon --}}
                    <div>{{ __('Human Resource') }}</div>
                </a>
                <ul class="menu-sub">
                    {{-- HR Attendance Sub-section --}}
                    {{-- Check if the user has any role for the Attendance sub-section --}}
                    {{-- Roles from web.php: Admin|HR|CC (from group middleware) --}}
                    @php $canSeeHrAttendance = $this->hasRequiredRole(['Admin', 'HR', 'CC']); @endphp
                    @if ($canSeeHrAttendance)
                        {{-- 'active open' if any route starting with 'hr.attendance.' is current --}}
                        <li class="menu-item {{ request()->routeIs('hr.attendance.*') ? 'active open' : '' }}">
                            <a href="javascript:void(0);" class="menu-link menu-toggle">
                                <div>{{ __('Attendance') }}</div>
                            </a>
                            <ul class="menu-sub">
                                {{-- Fingerprints --}}
                                {{-- Route name: hr.attendance.fingerprints, Roles: Admin|HR --}}
                                @php $canSeeFingerprints = $this->hasRequiredRole(['Admin', 'HR']); @endphp
                                @if ($canSeeFingerprints)
                                    {{-- 'active' if the current route is exactly 'hr.attendance.fingerprints' --}}
                                    <li
                                        class="menu-item {{ request()->routeIs('hr.attendance.fingerprints') ? 'active' : '' }}">
                                        {{-- Using route() helper with the defined route name --}}
                                        <a href="{{ route('hr.attendance.fingerprints') }}" class="menu-link">
                                            <div>{{ __('Fingerprints') }}</div>
                                        </a>
                                    </li>
                                @endif
                                {{-- Leaves --}}
                                {{-- Route name: hr.attendance.leaves, Roles: Admin|HR|CC --}}
                                @php $canSeeLeaves = $this->hasRequiredRole(['Admin', 'HR', 'CC']); @endphp
                                @if ($canSeeLeaves)
                                    {{-- 'active' if the current route is exactly 'hr.attendance.leaves' --}}
                                    <li
                                        class="menu-item {{ request()->routeIs('hr.attendance.leaves') ? 'active' : '' }}">
                                        {{-- Using route() helper with the defined route name --}}
                                        <a href="{{ route('hr.attendance.leaves') }}" class="menu-link">
                                            <div>{{ __('Leaves') }}</div>
                                        </a>
                                    </li>
                                @endif
                            </ul>
                        </li>
                    @endif

                    {{-- HR Structure Sub-section --}}
                    {{-- Check if the user has any role for the Structure sub-section --}}
                    {{-- Roles from web.php: Admin|HR (from group middleware) --}}
                    @php $canSeeHrStructure = $this->hasRequiredRole(['Admin', 'HR']); @endphp
                    @if ($canSeeHrStructure)
                        {{-- 'active open' if any route starting with 'hr.structure.' is current --}}
                        <li class="menu-item {{ request()->routeIs('hr.structure.*') ? 'active open' : '' }}">
                            <a href="javascript:void(0);" class="menu-link menu-toggle">
                                <div>{{ __('Structure') }}</div>
                            </a>
                            <ul class="menu-sub">
                                {{-- Centers --}}
                                {{-- Route name: hr.structure.centers, Roles: Admin|HR --}}
                                @php $canSeeCenters = $this->hasRequiredRole(['Admin', 'HR']); @endphp
                                @if ($canSeeCenters)
                                    {{-- 'active' if the current route is exactly 'hr.structure.centers' --}}
                                    <li
                                        class="menu-item {{ request()->routeIs('hr.structure.centers') ? 'active' : '' }}">
                                        {{-- Using route() helper with the defined route name --}}
                                        <a href="{{ route('hr.structure.centers') }}" class="menu-link">
                                            <div>{{ __('Centers') }}</div>
                                        </a>
                                    </li>
                                @endif
                                {{-- Departments --}}
                                {{-- Route name: hr.structure.departments, Roles: Admin|HR --}}
                                @php $canSeeDepartments = $this->hasRequiredRole(['Admin', 'HR']); @endphp
                                @if ($canSeeDepartments)
                                    {{-- 'active' if the current route is exactly 'hr.structure.departments' --}}
                                    <li
                                        class="menu-item {{ request()->routeIs('hr.structure.departments') ? 'active' : '' }}">
                                        {{-- Using route() helper with the defined route name --}}
                                        <a href="{{ route('hr.structure.departments') }}" class="menu-link">
                                            <div>{{ __('Departments') }}</div>
                                        </a>
                                    </li>
                                @endif
                                {{-- Positions --}}
                                {{-- Route name: hr.structure.positions, Roles: Admin|HR --}}
                                @php $canSeePositions = $this->hasRequiredRole(['Admin', 'HR']); @endphp
                                @if ($canSeePositions)
                                    {{-- 'active' if the current route is exactly 'hr.structure.positions' --}}
                                    <li
                                        class="menu-item {{ request()->routeIs('hr.structure.positions') ? 'active' : '' }}">
                                        {{-- Using route() helper with the defined route name --}}
                                        <a href="{{ route('hr.structure.positions') }}" class="menu-link">
                                            <div>{{ __('Positions') }}</div>
                                        </a>
                                    </li>
                                @endif
                                {{-- Employees (List) --}}
                                {{-- Route name: hr.structure.employees, Roles: Admin|HR --}}
                                @php $canSeeEmployees = $this->hasRequiredRole(['Admin', 'HR']); @endphp
                                @if ($canSeeEmployees)
                                    {{-- 'active' if the current route is exactly 'hr.structure.employees' --}}
                                    <li
                                        class="menu-item {{ request()->routeIs('hr.structure.employees') ? 'active' : '' }}">
                                        {{-- Using route() helper with the defined route name --}}
                                        <a href="{{ route('hr.structure.employees') }}" class="menu-link">
                                            <div>{{ __('Employees') }}</div>
                                        </a>
                                    </li>
                                @endif
                                {{-- Employee Info (Individual employee detail - likely accessed from list, not main menu) --}}
                                {{-- @php $canSeeEmployeeInfo = $this->hasRequiredRole(['Admin', 'HR']); @endphp
                                @if ($canSeeEmployeeInfo)
                                    <li class="menu-item {{ request()->routeIs('hr.structure.employees.show') ? 'active' : '' }}">
                                        <a href="{{ route('hr.structure.employees.show', ['employee' => 0]) }}" class="menu-link"> // Placeholder ID
                                            <div>{{ __('Employee Info') }}</div>
                                        </a>
                                    </li>
                                @endif --}}
                            </ul>
                        </li>
                    @endif

                    {{-- Other HR Items --}}
                    {{-- Roles from web.php: Admin|HR --}}
                    @php $canSeeOtherHr = $this->hasRequiredRole(['Admin', 'HR']); @endphp
                    @if ($canSeeOtherHr)
                        {{-- Messages --}}
                        {{-- Route name: hr.messages --}}
                        <li class="menu-item {{ request()->routeIs('hr.messages') ? 'active' : '' }}">
                            {{-- Using route() helper --}}
                            <a href="{{ route('hr.messages') }}" class="menu-link">
                                <div>{{ __('Messages') }}</div>
                            </a>
                        </li>
                        {{-- Discounts --}}
                        {{-- Route name: hr.discounts --}}
                        <li class="menu-item {{ request()->routeIs('hr.discounts') ? 'active' : '' }}">
                            {{-- Using route() helper --}}
                            <a href="{{ route('hr.discounts') }}" class="menu-link">
                                <div>{{ __('Discounts') }}</div>
                            </a>
                        </li>
                        {{-- Holidays --}}
                        {{-- Route name: hr.holidays --}}
                        <li class="menu-item {{ request()->routeIs('hr.holidays') ? 'active' : '' }}">
                            {{-- Using route() helper --}}
                            <a href="{{ route('hr.holidays') }}" class="menu-link">
                                <div>{{ __('Holidays') }}</div>
                            </a>
                        </li>
                        {{-- Statistics --}}
                        {{-- Route name: hr.statistics --}}
                        <li class="menu-item {{ request()->routeIs('hr.statistics') ? 'active' : '' }}">
                            {{-- Using route() helper --}}
                            <a href="{{ route('hr.statistics') }}" class="menu-link">
                                <div>{{ __('Statistics') }}</div>
                            </a>
                        </li>
                    @endif
                </ul>
            </li>
        @endif {{-- End HR parent check --}}

        {{-- Assets Section (Existing HRMS Assets) --}}
        {{-- Check if the user has any role for the Assets parent section --}}
        {{-- Roles from web.php children: Admin|AM, Admin|AM|HR (from reports.index if in assets group) --}}
        {{-- Active if any route starting with 'assets.' is current --}}
        @php $canSeeAssetsSection = $this->hasRequiredRole(['Admin', 'AM', 'HR']); @endphp {{-- Added HR based on reports.index in web.php --}}
        @if ($canSeeAssetsSection)
            <li class="menu-item {{ request()->routeIs('assets.*') ? 'active open' : '' }}">
                <a href="javascript:void(0);" class="menu-link menu-toggle">
                    <i class="menu-icon tf-icons ti ti-server"></i> {{-- Example icon --}}
                    <div>{{ __('Assets') }}</div>
                </a>
                <ul class="menu-sub">
                    {{-- Inventory --}}
                    {{-- Route name: assets.inventory, Roles: Admin|AM --}}
                    @php $canSeeInventory = $this->hasRequiredRole(['Admin', 'AM']); @endphp
                    @if ($canSeeInventory)
                        {{-- 'active' if the current route is exactly 'assets.inventory' --}}
                        <li class="menu-item {{ request()->routeIs('assets.inventory') ? 'active' : '' }}">
                            {{-- Using route() helper --}}
                            <a href="{{ route('assets.inventory') }}" class="menu-link">
                                <div>{{ __('Inventory') }}</div>
                            </a>
                        </li>
                    @endif
                    {{-- Categories --}}
                    {{-- Route name: assets.categories, Roles: Admin|AM --}}
                    @php $canSeeAssetsCategories = $this->hasRequiredRole(['Admin', 'AM']); @endphp
                    @if ($canSeeAssetsCategories)
                        {{-- 'active' if the current route is exactly 'assets.categories' --}}
                        <li class="menu-item {{ request()->routeIs('assets.categories') ? 'active' : '' }}">
                            {{-- Using route() helper --}}
                            <a href="{{ route('assets.categories') }}" class="menu-link">
                                <div>{{ __('Categories') }}</div>
                            </a>
                        </li>
                    @endif
                    {{-- Reports Index (Moved under Admin IRM Reports in web.php, keeping here if needed separately) --}}
                    {{-- Note: Route name is 'reports.index' if defined in assets group, or potentially something else --}}
                    {{-- @php $canSeeAssetsReportsIndex = $this->hasRequiredRole(['Admin', 'AM', 'HR']); @endphp
                      @if ($canSeeAssetsReportsIndex)
                         <li class="menu-item {{ request()->routeIs('assets.reports.index') ? 'active' : '' }}"> // Example route name if nested
                             <a href="{{ route('assets.reports.index') }}" class="menu-link"> // Example route name if nested
                                 <div>{{ __('Reports') }}</div>
                             </a>
                         </li>
                     @endif --}}
                </ul>
            </li>
        @endif {{-- End Assets parent check --}}


        {{-- --- New MOTAC Integrated Resource Management (IRM) Section --- --}}
        {{-- Check if user can access *any* item in this section to show the header --}}
        {{-- Define all roles that grant access to *any* RM item based on web.php --}}
        {{-- Roles: Admin, Approver, BPM, User (from my-applications/apply), Admin|Approver|BPM (from approvals), Admin|BPM (from admin group) --}}
        @php
            $allRmRoles = ['Admin', 'Approver', 'BPM', 'User'];
            $canSeeRmSectionHeader = $this->hasRequiredRole($allRmRoles);
        @endphp
        @if ($canSeeRmSectionHeader)
            <li class="menu-header small text-uppercase">
                <span class="menu-header-text">{{ __('Resource Management (IRM)') }}</span>
            </li>
        @endif

        {{-- My Applications (Accessible to users viewing their own apps) --}}
        {{-- Middleware: auth (handled by the main route group) --}}
        {{-- Decide if the parent 'My Applications' should be shown if *any* child is visible --}}
        {{-- Child route roles: User|Admin --}}
        {{-- Active if any route starting with 'my-applications.' is current --}}
        @php $canSeeMyApplications = $this->hasRequiredRole(['User', 'Admin']); @endphp
        @if ($canSeeMyApplications)
            <li class="menu-item {{ request()->routeIs('my-applications.*') ? 'active open' : '' }}">
                <a href="javascript:void(0);" class="menu-link menu-toggle">
                    <i class="menu-icon tf-icons ti ti-folder"></i> {{-- Example icon --}}
                    <div>{{ __('My Applications') }}</div>
                </a>
                <ul class="menu-sub">
                    {{-- Email Applications List --}}
                    {{-- Route name: my-applications.email.index, Roles: User|Admin --}}
                    @php $canSeeMyEmailAppsList = $this->hasRequiredRole(['User', 'Admin']); @endphp
                    @if ($canSeeMyEmailAppsList)
                        {{-- 'active' if the current route is exactly 'my-applications.email.index' or starts with 'my-applications.email.' (e.g., show route) --}}
                        <li class="menu-item {{ request()->routeIs('my-applications.email.*') ? 'active' : '' }}">
                            {{-- Using route() helper --}}
                            <a href="{{ route('my-applications.email.index') }}" class="menu-link">
                                <div>{{ __('Email Applications') }}</div>
                            </a>
                        </li>
                    @endif
                    {{-- Loan Applications List --}}
                    {{-- Route name: my-applications.loan.index, Roles: User|Admin --}}
                    @php $canSeeMyLoanAppsList = $this->hasRequiredRole(['User', 'Admin']); @endphp
                    @if ($canSeeMyLoanAppsList)
                        {{-- 'active' if the current route is exactly 'my-applications.loan.index' or starts with 'my-applications.loan.' (e.g., show route) --}}
                        <li class="menu-item {{ request()->routeIs('my-applications.loan.*') ? 'active' : '' }}">
                            {{-- Using route() helper --}}
                            <a href="{{ route('my-applications.loan.index') }}" class="menu-link">
                                <div>{{ __('Loan Applications') }}</div>
                            </a>
                        </li>
                    @endif
                </ul>
            </li>
        @endif {{-- End My Applications parent check --}}


        {{-- Apply for Resources (Accessible to users who can submit forms) --}}
        {{-- Middleware: auth (handled by the main route group) --}}
        {{-- Decide if the parent 'Apply' should be shown if *any* child is visible --}}
        {{-- Child route roles: User|Admin --}}
        {{-- Active if either of the create routes is current --}}
        @php $canSeeApplySection = $this->hasRequiredRole(['User', 'Admin']); @endphp
        @if ($canSeeApplySection)
            <li
                class="menu-item {{ request()->routeIs('resource-management.email-applications.create') || request()->routeIs('resource-management.loan-applications.create') ? 'active open' : '' }}">
                <a href="javascript:void(0);" class="menu-link menu-toggle">
                    <i class="menu-icon tf-icons ti ti-file-invoice"></i> {{-- Example icon --}}
                    <div>{{ __('Apply for Resources') }}</div>
                </a>
                <ul class="menu-sub">
                    {{-- Email Application Form --}}
                    {{-- Route name: resource-management.email-applications.create, Roles: User|Admin --}}
                    @php $canSeeEmailApplyForm = $this->hasRequiredRole(['User', 'Admin']); @endphp
                    @if ($canSeeEmailApplyForm)
                        {{-- 'active' if the current route is exactly 'resource-management.email-applications.create' --}}
                        <li
                            class="menu-item {{ request()->routeIs('resource-management.email-applications.create') ? 'active' : '' }}">
                            {{-- Using route() helper --}}
                            <a href="{{ route('resource-management.email-applications.create') }}" class="menu-link">
                                <div>{{ __('Email Account Application') }}</div>
                            </a>
                        </li>
                    @endif
                    {{-- Loan Application Form --}}
                    {{-- Route name: resource-management.loan-applications.create, Roles: User|Admin --}}
                    @php $canSeeLoanApplyForm = $this->hasRequiredRole(['User', 'Admin']); @endphp
                    @if ($canSeeLoanApplyForm)
                        {{-- 'active' if the current route is exactly 'resource-management.loan-applications.create' --}}
                        <li
                            class="menu-item {{ request()->routeIs('resource-management.loan-applications.create') ? 'active' : '' }}">
                            {{-- Using route() helper --}}
                            <a href="{{ route('resource-management.loan-applications.create') }}" class="menu-link">
                                <div>{{ __('ICT Equipment Loan Application') }}</div>
                            </a>
                        </li>
                    @endif
                </ul>
            </li>
        @endif {{-- End Apply for Resources check --}}


        {{-- Approval Dashboard (Accessible to users with required grade AND Approver/Admin role) --}}
        {{-- Route name: approvals.index, Middleware: grade:X, role:Approver|Admin --}}
        {{-- Approximate check in menu using roles typically assigned to approvers --}}
        @php $canSeeApprovalsDashboard = $this->hasRequiredRole(['Approver', 'Admin']); @endphp
        @if ($canSeeApprovalsDashboard)
            {{-- 'active' if the current route is exactly 'approvals.index' --}}
            <li class="menu-item {{ request()->routeIs('approvals.index') ? 'active' : '' }}">
                {{-- Using route() helper --}}
                <a href="{{ route('approvals.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons ti ti-checks"></i> {{-- Example icon --}}
                    <div>{{ __('Approval Dashboard') }}</div>
                </a>
            </li>
        @endif {{-- End Approvals Dashboard check --}}

        {{-- Approval History (Accessible to approvers, Admin, BPM) --}}
        {{-- Route name: approvals.history, Middleware: role:Approver|Admin|BPM --}}
        @php $canSeeApprovalHistory = $this->hasRequiredRole(['Approver', 'Admin', 'BPM']); @endphp
        @if ($canSeeApprovalHistory)
            {{-- 'active' if the current route is exactly 'approvals.history' --}}
            <li class="menu-item {{ request()->routeIs('approvals.history') ? 'active' : '' }}">
                {{-- Using route() helper --}}
                <a href="{{ route('approvals.history') }}" class="menu-link">
                    <i class="menu-icon tf-icons ti ti-history"></i> {{-- Example icon --}}
                    <div>{{ __('Approval History') }}</div>
                </a>
            </li>
        @endif {{-- End Approval History check --}}


        {{-- Resource Management Admin/BPM Section --}}
        {{-- Check if the user has Admin or BPM roles for this parent section --}}
        {{-- Roles from web.php children: Admin (users, grades, reports), Admin|BPM (equipment, group middleware), BPM (bpm group middleware) --}}
        {{-- Active if any route starting with 'admin.' is current --}}
        @php $canSeeIrmAdministration = $this->hasRequiredRole(['Admin', 'BPM']); @endphp
        @if ($canSeeIrmAdministration)
            {{-- Using 'admin.' prefix for routes inside this group --}}
            <li class="menu-item {{ request()->routeIs('admin.*') ? 'active open' : '' }}">
                <a href="javascript:void(0);" class="menu-link menu-toggle">
                    <i class="menu-icon tf-icons ti ti-building-warehouse"></i> {{-- Example icon --}}
                    <div>{{ __('IRM Administration') }}</div>
                </a>
                <ul class="menu-sub">
                    {{-- Manage Users (Admin only) --}}
                    {{-- Route name: admin.users.index, Middleware: role:Admin --}}
                    @php $canSeeManageUsers = $this->hasRequiredRole(['Admin']); @endphp
                    @if ($canSeeManageUsers)
                        {{-- 'active' if any route starting with 'admin.users.' is current --}}
                        <li class="menu-item {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                            {{-- Use wildcard for active state --}}
                            {{-- Using route() helper --}}
                            <a href="{{ route('admin.users.index') }}" class="menu-link">
                                <div>{{ __('Manage Users') }}</div>
                            </a>
                        </li>
                    @endif
                    {{-- Manage Equipment Assets (Admin/BPM) --}}
                    {{-- Route name: admin.equipment.index, Middleware: role:Admin|BPM (from group) --}}
                    @php $canSeeManageEquipment = $this->hasRequiredRole(['Admin', 'BPM']); @endphp
                    @if ($canSeeManageEquipment)
                        {{-- 'active' if any route starting with 'admin.equipment.' is current --}}
                        <li class="menu-item {{ request()->routeIs('admin.equipment.*') ? 'active' : '' }}">
                            {{-- Use wildcard for active state --}}
                            {{-- Using route() helper --}}
                            <a href="{{ route('admin.equipment.index') }}" class="menu-link">
                                <div>{{ __('Manage Equipment') }}</div>
                            </a>
                        </li>
                    @endif
                    {{-- Manage Grades (Admin only) --}}
                    {{-- Route name: admin.grades.index, Middleware: role:Admin --}}
                    @php $canSeeManageGrades = $this->hasRequiredRole(['Admin']); @endphp
                    @if ($canSeeManageGrades)
                        {{-- 'active' if any route starting with 'admin.grades.' is current --}}
                        <li class="menu-item {{ request()->routeIs('admin.grades.*') ? 'active' : '' }}">
                            {{-- Use wildcard for active state --}}
                            {{-- Using route() helper --}}
                            <a href="{{ route('admin.grades.index') }}" class="menu-link">
                                <div>{{ __('Manage Grades') }}</div>
                            </a>
                        </li>
                    @endif

                    {{-- BPM Staff Interface (BPM only) --}}
                    {{-- Check if the user has the BPM role for this sub-section --}}
                    {{-- Roles from web.php: BPM (from group middleware) --}}
                    {{-- Active if any route starting with 'admin.bpm.' is current --}}
                    @php $canSeeBpmTasks = $this->hasRequiredRole(['BPM']); @endphp
                    @if ($canSeeBpmTasks)
                        <li class="menu-item {{ request()->routeIs('admin.bpm.*') ? 'active open' : '' }}">
                            <a href="javascript:void(0);" class="menu-link menu-toggle">
                                <div>{{ __('BPM Tasks') }}</div>
                            </a>
                            <ul class="menu-sub">
                                {{-- Outstanding Loans (Ready for Issuance) --}}
                                {{-- Route name: admin.bpm.outstanding-loans, Middleware: role:BPM --}}
                                @php $canSeeOutstandingLoans = $this->hasRequiredRole(['BPM']); @endphp
                                @if ($canSeeOutstandingLoans)
                                    {{-- 'active' if the current route is exactly 'admin.bpm.outstanding-loans' --}}
                                    <li
                                        class="menu-item {{ request()->routeIs('admin.bpm.outstanding-loans') ? 'active' : '' }}">
                                        {{-- Using route() helper --}}
                                        <a href="{{ route('admin.bpm.outstanding-loans') }}" class="menu-link">
                                            <div>{{ __('Outstanding Loans') }}</div>
                                        </a>
                                    </li>
                                @endif
                                {{-- Issued Loans (Ready for Return) --}}
                                {{-- Route name: admin.bpm.issued-loans, Middleware: role:BPM --}}
                                @php $canSeeIssuedLoans = $this->hasRequiredRole(['BPM']); @endphp
                                @if ($canSeeIssuedLoans)
                                    {{-- 'active' if the current route is exactly 'admin.bpm.issued-loans' --}}
                                    <li
                                        class="menu-item {{ request()->routeIs('admin.bpm.issued-loans') ? 'active' : '' }}">
                                        {{-- Using route() helper --}}
                                        <a href="{{ route('admin.bpm.issued-loans') }}" class="menu-link">
                                            <div>{{ __('Issued Loans') }}</div>
                                        </a>
                                    </li>
                                @endif
                                {{-- Transaction Details (Show view) - likely not in main menu, accessed from list --}}
                                {{-- @php $canSeeTransactionDetails = $this->hasRequiredRole(['BPM', 'Admin']); @endphp
                                   @if ($canSeeTransactionDetails)
                                       <li class="menu-item {{ request()->routeIs('admin.bpm.transactions.show') ? 'active' : '' }}">
                                           <a href="{{ route('admin.bpm.transactions.show', ['loanTransaction' => 0]) }}" class="menu-link"> // Placeholder ID for route model binding example
                                               <div>{{ __('Transaction Details') }}</div>
                                           </a>
                                       </li>
                                   @endif --}}
                            </ul>
                        </li>
                    @endif {{-- End BPM Tasks check --}}

                    {{-- Admin Reports (Admin only) --}}
                    {{-- Check if the user has the Admin role for this sub-section --}}
                    {{-- Roles from web.php: Admin (from group middleware) --}}
                    {{-- Active if any route starting with 'admin.reports.' is current --}}
                    @php $canSeeAdminReports = $this->hasRequiredRole(['Admin']); @endphp
                    @if ($canSeeAdminReports)
                        <li class="menu-item {{ request()->routeIs('admin.reports.*') ? 'active open' : '' }}">
                            <a href="javascript:void(0);" class="menu-link menu-toggle">
                                <div>{{ __('Reports') }}</div>
                            </a>
                            <ul class="menu-sub">
                                {{-- Equipment Report --}}
                                {{-- Route name: admin.reports.equipment, Middleware: role:Admin --}}
                                @php $canSeeEquipmentReport = $this->hasRequiredRole(['Admin']); @endphp
                                @if ($canSeeEquipmentReport)
                                    {{-- 'active' if the current route is exactly 'admin.reports.equipment' --}}
                                    <li
                                        class="menu-item {{ request()->routeIs('admin.reports.equipment') ? 'active' : '' }}">
                                        {{-- Using route() helper --}}
                                        <a href="{{ route('admin.reports.equipment') }}" class="menu-link">
                                            <div>{{ __('Equipment Report') }}</div>
                                        </a>
                                    </li>
                                @endif
                                {{-- Email Accounts Report --}}
                                {{-- Route name: admin.reports.email-accounts, Middleware: role:Admin --}}
                                @php $canSeeEmailAccountsReport = $this->hasRequiredRole(['Admin']); @endphp
                                @if ($canSeeEmailAccountsReport)
                                    {{-- 'active' if the current route is exactly 'admin.reports.email-accounts' --}}
                                    <li
                                        class="menu-item {{ request()->routeIs('admin.reports.email-accounts') ? 'active' : '' }}">
                                        {{-- Using route() helper --}}
                                        <a href="{{ route('admin.reports.email-accounts') }}" class="menu-link">
                                            <div>{{ __('Email Accounts Report') }}</div>
                                        </a>
                                    </li>
                                @endif
                                {{-- Loan Applications Report --}}
                                {{-- Route name: admin.reports.loan-applications, Middleware: role:Admin --}}
                                @php $canSeeLoanAppsReport = $this->hasRequiredRole(['Admin']); @endphp
                                @if ($canSeeLoanAppsReport)
                                    {{-- 'active' if the current route is exactly 'admin.reports.loan-applications' --}}
                                    <li
                                        class="menu-item {{ request()->routeIs('admin.reports.loan-applications') ? 'active' : '' }}">
                                        {{-- Using route() helper --}}
                                        <a href="{{ route('admin.reports.loan-applications') }}" class="menu-link">
                                            <div>{{ __('Loan Applications Report') }}</div>
                                        </a>
                                    </li>
                                @endif
                                {{-- User Activity Report --}}
                                {{-- Route name: admin.reports.user-activity, Middleware: role:Admin --}}
                                @php $canSeeUserActivityReport = $this->hasRequiredRole(['Admin']); @endphp
                                @if ($canSeeUserActivityReport)
                                    {{-- 'active' if the current route is exactly 'admin.reports.user-activity' --}}
                                    <li
                                        class="menu-item {{ request()->routeIs('admin.reports.user-activity') ? 'active' : '' }}">
                                        {{-- Using route() helper --}}
                                        <a href="{{ route('admin.reports.user-activity') }}" class="menu-link">
                                            <div>{{ __('User Activity Report') }}</div>
                                        </a>
                                    </li>
                                @endif
                                {{-- Add other reports here based on ReportController --}}
                            </ul>
                        </li>
                    @endif {{-- End Admin Reports check --}}
                </ul> {{-- End IRM Administration submenu --}}
            </li>
        @endif {{-- End IRM Administration parent check --}}

        {{-- ☝️ End New MOTAC Integrated Resource Management Menu Items ☝️ --}}

        {{-- Separator (Optional) --}}
        {{-- <li class="menu-header small text-uppercase">
            <span class="menu-header-text">{{ __('Others') }}</span>
        </li> --}}

        {{-- Settings Section (Existing HRMS Settings) --}}
        {{-- Check if the user has the Admin role for this parent section --}}
        {{-- Roles from web.php: Admin (from group middleware) --}}
        {{-- Active if any route starting with 'settings.' is current --}}
        @php $canSeeSettings = $this->hasRequiredRole(['Admin']); @endphp
        @if ($canSeeSettings)
            <li class="menu-item {{ request()->routeIs('settings.*') ? 'active open' : '' }}">
                <a href="javascript:void(0);" class="menu-link menu-toggle">
                    <i class="menu-icon tf-icons ti ti-settings"></i> {{-- Example icon --}}
                    <div>{{ __('System Settings') }}</div> {{-- Label from web.php context --}}
                </a>
                <ul class="menu-sub">
                    {{-- Users --}}
                    {{-- Route name: settings.users.index, Middleware: role:Admin --}}
                    @php $canSeeSettingsUsers = $this->hasRequiredRole(['Admin']); @endphp
                    @if ($canSeeSettingsUsers)
                        {{-- 'active' if any route starting with 'settings.users.' is current --}}
                        <li class="menu-item {{ request()->routeIs('settings.users.*') ? 'active' : '' }}">
                            {{-- Using route() helper --}}
                            <a href="{{ route('settings.users.index') }}" class="menu-link">
                                <div>{{ __('Users') }}</div>
                            </a>
                        </li>
                    @endif
                    {{-- Roles --}}
                    {{-- Route name: settings.roles.index, Middleware: role:Admin --}}
                    @php $canSeeSettingsRoles = $this->hasRequiredRole(['Admin']); @endphp
                    @if ($canSeeSettingsRoles)
                        {{-- 'active' if the current route is exactly 'settings.roles.index' --}}
                        <li class="menu-item {{ request()->routeIs('settings.roles.index') ? 'active' : '' }}">
                            {{-- Using route() helper --}}
                            <a href="{{ route('settings.roles.index') }}" class="menu-link">
                                <div>{{ __('Roles') }}</div>
                            </a>
                        </li>
                    @endif
                    {{-- Permissions --}}
                    {{-- Route name: settings.permissions.index, Middleware: role:Admin --}}
                    @php $canSeeSettingsPermissions = $this->hasRequiredRole(['Admin']); @endphp
                    @if ($canSeeSettingsPermissions)
                        {{-- 'active' if the current route is exactly 'settings.permissions.index' --}}
                        <li class="menu-item {{ request()->routeIs('settings.permissions.index') ? 'active' : '' }}">
                            {{-- Using route() helper --}}
                            <a href="{{ route('settings.permissions.index') }}" class="menu-link">
                                <div>{{ __('Permissions') }}</div>
                            </a>
                        </li>
                    @endif
                    {{-- Add other settings items here --}}
                </ul>
            </li>
        @endif {{-- End Settings parent check --}}


        {{-- Logout Link (Keep this as a standard menu item, visible to all authenticated users) --}}
        {{-- No role check needed if only for authenticated users, which is handled by the main route group --}}
        @auth {{-- Only show if a user is logged in --}}
            <li class="menu-item">
                {{-- Link that triggers the logout form submission via JavaScript --}}
                {{-- Points to the standard 'logout' route provided by Laravel/Jetstream/Fortify --}}
                <a href="#" class="menu-link"
                    onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                    <i class="menu-icon tf-icons ti ti-logout"></i> {{-- Example icon class for logout --}}
                    <div>{{ __('Logout') }}</div> {{-- Localized label for Logout --}}
                </a>
            </li>
            {{-- Hidden form for POST logout request --}}
            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                @csrf {{-- CSRF token for security --}}
            </form>
        @endauth


    </ul> {{-- End main menu-inner list --}}

</aside> {{-- End aside layout-menu --}}
