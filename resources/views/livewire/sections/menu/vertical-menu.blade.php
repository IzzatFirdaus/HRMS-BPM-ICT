@php
    // Accessing the Livewire component instance to use its methods and properties
    // $this refers to the App\Livewire\Sections\Menu\VerticalMenu component
    // $userRoles is available from $this->userRoles
@endphp

<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">

    <!-- ! Hide app brand if navbar-full -->
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
            Iterate through your menu structure here.
            This example uses hardcoded items matching your web.php and applies
            role checks using the hasRequiredRole method from the Livewire component ($this).
            The canSee boolean is calculated using $this->hasRequiredRole() and passed to the submenu.
        --}}

        {{-- Dashboard --}}
        @php
            // Check if the user has any of the roles required for the dashboard route
            // Roles from web.php: Admin|AM|CC|CR|HR
            $canSeeDashboard = $this->hasRequiredRole(['Admin', 'AM', 'CC', 'CR', 'HR']);
        @endphp
        @if ($canSeeDashboard)
            <li class="menu-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <a href="{{ route('dashboard') }}" class="menu-link">
                    <i class="menu-icon tf-icons ti ti-smart-home"></i> {{-- Example icon --}}
                    <div>{{ __('Dashboard') }}</div>
                </a>
            </li>
        @endif

        {{-- Human Resource Section --}}
        @php
            // Check if the user has any role that grants access to the HR parent section
            // Roles from web.php children: Admin|HR|CC
            $canSeeHrSection = $this->hasRequiredRole(['Admin', 'HR', 'CC']); // Example roles for HR parent
        @endphp
        @if ($canSeeHrSection)
            <li class="menu-item {{ request()->routeIs('hr.*') ? 'active open' : '' }}">
                <a href="javascript:void(0);" class="menu-link menu-toggle">
                    <i class="menu-icon tf-icons ti ti-users"></i> {{-- Example icon --}}
                    <div>{{ __('Human Resource') }}</div>
                </a>
                <ul class="menu-sub">
                    {{-- HR Attendance Sub-section --}}
                    @php
                        // Check if the user has any role for the Attendance sub-section
                        // Roles from web.php: Admin|HR|CC
                        $canSeeHrAttendance = $this->hasRequiredRole(['Admin', 'HR', 'CC']);
                    @endphp
                    @if ($canSeeHrAttendance)
                        <li class="menu-item {{ request()->routeIs('hr.attendance.*') ? 'active open' : '' }}">
                            <a href="javascript:void(0);" class="menu-link menu-toggle">
                                <div>{{ __('Attendance') }}</div>
                            </a>
                            <ul class="menu-sub">
                                {{-- Fingerprints --}}
                                @php $canSeeFingerprints = $this->hasRequiredRole(['Admin', 'HR']); @endphp
                                @if ($canSeeFingerprints)
                                    <li
                                        class="menu-item {{ request()->routeIs('attendance-fingerprints') ? 'active' : '' }}">
                                        <a href="{{ route('attendance-fingerprints') }}" class="menu-link">
                                            <div>{{ __('Fingerprints') }}</div>
                                        </a>
                                    </li>
                                @endif
                                {{-- Leaves --}}
                                @php $canSeeLeaves = $this->hasRequiredRole(['Admin', 'HR', 'CC']); @endphp
                                @if ($canSeeLeaves)
                                    <li
                                        class="menu-item {{ request()->routeIs('attendance-leaves') ? 'active' : '' }}">
                                        <a href="{{ route('attendance-leaves') }}" class="menu-link">
                                            <div>{{ __('Leaves') }}</div>
                                        </a>
                                    </li>
                                @endif
                            </ul>
                        </li>
                    @endif

                    {{-- HR Structure Sub-section --}}
                    @php
                        // Check if the user has any role for the Structure sub-section
                        // Roles from web.php: Admin|HR
                        $canSeeHrStructure = $this->hasRequiredRole(['Admin', 'HR']);
                    @endphp
                    @if ($canSeeHrStructure)
                        <li class="menu-item {{ request()->routeIs('hr.structure.*') ? 'active open' : '' }}">
                            <a href="javascript:void(0);" class="menu-link menu-toggle">
                                <div>{{ __('Structure') }}</div>
                            </a>
                            <ul class="menu-sub">
                                {{-- Centers --}}
                                @php $canSeeCenters = $this->hasRequiredRole(['Admin', 'HR']); @endphp
                                @if ($canSeeCenters)
                                    <li
                                        class="menu-item {{ request()->routeIs('structure-centers') ? 'active' : '' }}">
                                        <a href="{{ route('structure-centers') }}" class="menu-link">
                                            <div>{{ __('Centers') }}</div>
                                        </a>
                                    </li>
                                @endif
                                {{-- Departments --}}
                                @php $canSeeDepartments = $this->hasRequiredRole(['Admin', 'HR']); @endphp
                                @if ($canSeeDepartments)
                                    <li
                                        class="menu-item {{ request()->routeIs('structure-departments') ? 'active' : '' }}">
                                        <a href="{{ route('structure-departments') }}" class="menu-link">
                                            <div>{{ __('Departments') }}</div>
                                        </a>
                                    </li>
                                @endif
                                {{-- Positions --}}
                                {{-- Note: web.php uses 'structure-positions', not 'positions'. Ensure consistency. --}}
                                @php $canSeePositions = $this->hasRequiredRole(['Admin', 'HR']); @endphp
                                @if ($canSeePositions)
                                    <li
                                        class="menu-item {{ request()->routeIs('structure-positions') ? 'active' : '' }}">
                                        <a href="{{ route('structure-positions') }}" class="menu-link">
                                            <div>{{ __('Positions') }}</div>
                                        </a>
                                    </li>
                                @endif
                                {{-- Employees (List) --}}
                                @php $canSeeEmployees = $this->hasRequiredRole(['Admin', 'HR']); @endphp
                                @if ($canSeeEmployees)
                                    <li
                                        class="menu-item {{ request()->routeIs('structure-employees') ? 'active' : '' }}">
                                        {{-- Note: web.php uses 'structure-employees', not 'employees'. Ensure consistency. --}}
                                        <a href="{{ route('structure-employees') }}" class="menu-link">
                                            <div>{{ __('Employees') }}</div>
                                        </a>
                                    </li>
                                @endif
                                {{-- Employee Info (Individual employee detail - may not need direct menu link) --}}
                                {{-- @php $canSeeEmployeeInfo = $this->hasRequiredRole(['Admin', 'HR']); @endphp
                                @if ($canSeeEmployeeInfo)
                                    <li class="menu-item {{ request()->routeIs('structure-employees-info') ? 'active' : '' }}">
                                        <a href="{{ route('structure-employees-info') }}" class="menu-link">
                                            <div>{{ __('Employee Info') }}</div>
                                        </a>
                                    </li>
                                @endif --}}
                            </ul>
                        </li>
                    @endif

                    {{-- Other HR Items --}}
                    @php
                        // Roles from web.php: Admin|HR
                        $canSeeOtherHr = $this->hasRequiredRole(['Admin', 'HR']);
                    @endphp
                    @if ($canSeeOtherHr)
                        {{-- Messages --}}
                        {{-- Note: web.php uses 'messages' route name --}}
                        <li class="menu-item {{ request()->routeIs('messages') ? 'active' : '' }}">
                            <a href="{{ route('messages') }}" class="menu-link">
                                <div>{{ __('Messages') }}</div>
                            </a>
                        </li>
                        {{-- Discounts --}}
                        {{-- Note: web.php uses 'discounts' route name --}}
                        <li class="menu-item {{ request()->routeIs('discounts') ? 'active' : '' }}">
                            <a href="{{ route('discounts') }}" class="menu-link">
                                <div>{{ __('Discounts') }}</div>
                            </a>
                        </li>
                        {{-- Holidays --}}
                        {{-- Note: web.php uses 'holidays' route name --}}
                        <li class="menu-item {{ request()->routeIs('holidays') ? 'active' : '' }}">
                            <a href="{{ route('holidays') }}" class="menu-link">
                                <div>{{ __('Holidays') }}</div>
                            </a>
                        </li>
                        {{-- Statistics --}}
                        {{-- Note: web.php uses 'statistics' route name --}}
                        <li class="menu-item {{ request()->routeIs('statistics') ? 'active' : '' }}">
                            <a href="{{ route('statistics') }}" class="menu-link">
                                <div>{{ __('Statistics') }}</div>
                            </a>
                        </li>
                    @endif
                </ul>
            </li>
        @endif {{-- End HR parent check --}}

        {{-- Settings Section --}}
        @php
            // Roles from web.php: Admin
            $canSeeSettings = $this->hasRequiredRole(['Admin']);
        @endphp
        @if ($canSeeSettings)
            <li class="menu-item {{ request()->routeIs('settings.*') ? 'active open' : '' }}">
                <a href="javascript:void(0);" class="menu-link menu-toggle">
                    <i class="menu-icon tf-icons ti ti-settings"></i> {{-- Example icon --}}
                    <div>{{ __('Settings') }}</div>
                </a>
                <ul class="menu-sub">
                    {{-- Users --}}
                    {{-- Note: web.php uses 'settings-users' route name --}}
                    @php $canSeeSettingsUsers = $this->hasRequiredRole(['Admin']); @endphp
                    @if ($canSeeSettingsUsers)
                        <li class="menu-item {{ request()->routeIs('settings-users') ? 'active' : '' }}">
                            <a href="{{ route('settings-users') }}" class="menu-link">
                                <div>{{ __('Users') }}</div>
                            </a>
                        </li>
                    @endif
                    {{-- Roles (Coming Soon) --}}
                    {{-- Note: web.php uses 'settings-roles' route name --}}
                    @php $canSeeSettingsRoles = $this->hasRequiredRole(['Admin']); @endphp
                    @if ($canSeeSettingsRoles)
                        <li class="menu-item {{ request()->routeIs('settings-roles') ? 'active' : '' }}">
                            <a href="{{ route('settings-roles') }}" class="menu-link">
                                <div>{{ __('Roles') }}</div>
                            </a>
                        </li>
                    @endif
                    {{-- Permissions (Coming Soon) --}}
                    {{-- Note: web.php uses 'settings-permissions' route name --}}
                    @php $canSeeSettingsPermissions = $this->hasRequiredRole(['Admin']); @endphp
                    @if ($canSeeSettingsPermissions)
                        <li class="menu-item {{ request()->routeIs('settings-permissions') ? 'active' : '' }}">
                            <a href="{{ route('settings-permissions') }}" class="menu-link">
                                <div>{{ __('Permissions') }}</div>
                            </a>
                        </li>
                    @endif
                </ul>
            </li>
        @endif {{-- End Settings parent check --}}

        {{-- Assets Section (Existing HRMS Assets) --}}
        @php
            // Check if the user has any role for the Assets parent section
            // Roles from web.php children: Admin|AM, Admin|AM|HR
            $canSeeAssetsSection = $this->hasRequiredRole(['Admin', 'AM', 'HR']); // Example roles for Assets parent
        @endphp
        @if ($canSeeAssetsSection)
            <li
                class="menu-item {{ request()->routeIs('assets.*') || request()->routeIs('reports.index') ? 'active open' : '' }}">
                {{-- Include reports.index route --}}
                <a href="javascript:void(0);" class="menu-link menu-toggle">
                    <i class="menu-icon tf-icons ti ti-server"></i> {{-- Example icon --}}
                    <div>{{ __('Assets') }}</div>
                </a>
                <ul class="menu-sub">
                    {{-- Inventory --}}
                    @php $canSeeInventory = $this->hasRequiredRole(['Admin', 'AM']); @endphp
                    @if ($canSeeInventory)
                        <li class="menu-item {{ request()->routeIs('inventory') ? 'active' : '' }}">
                            <a href="{{ route('inventory') }}" class="menu-link">
                                <div>{{ __('Inventory') }}</div>
                            </a>
                        </li>
                    @endif
                    {{-- Categories --}}
                    @php $canSeeCategories = $this->hasRequiredRole(['Admin', 'AM']); @endphp
                    @if ($canSeeCategories)
                        <li class="menu-item {{ request()->routeIs('categories') ? 'active' : '' }}">
                            <a href="{{ route('categories') }}" class="menu-link">
                                <div>{{ __('Categories') }}</div>
                            </a>
                        </li>
                    @endif
                    {{-- Reports Index --}}
                    @php $canSeeReportsIndex = $this->hasRequiredRole(['Admin', 'AM', 'HR']); @endphp
                    @if ($canSeeReportsIndex)
                        <li class="menu-item {{ request()->routeIs('reports.index') ? 'active' : '' }}">
                            <a href="{{ route('reports.index') }}" class="menu-link">
                                <div>{{ __('Reports') }}</div>
                            </a>
                        </li>
                    @endif
                </ul>
            </li>
        @endif {{-- End Assets parent check --}}


        {{-- 👇 New MOTAC Integrated Resource Management (IRM) Section 👇 --}}
        {{-- Check if user can access *any* item in this section --}}
        @php
            // Define all roles that grant access to *any* RM item
            $allRmRoles = ['Admin', 'Approver', 'BPM', 'User']; // Assuming 'User' is for My Applications/Apply
            $canSeeRmSectionHeader = $this->hasRequiredRole($allRmRoles);
        @endphp
        @if ($canSeeRmSectionHeader)
            <li class="menu-header small text-uppercase">
                <span class="menu-header-text">{{ __('Resource Management (IRM)') }}</span>
            </li>
        @endif

        {{-- My Applications (Accessible to all authenticated users) --}}
        {{-- Middleware: auth (handled by the main route group) --}}
        @auth {{-- This check is generally sufficient if routes are protected by auth middleware --}}
            @php
                // Decide if the parent 'My Applications' should be shown if *any* child is visible
                $canSeeMyApplications = $this->hasRequiredRole(['User', 'Admin', 'Approver', 'BPM']); // Assuming any authenticated user can see their own apps
                // Alternative: Check specific child routes' roles if needed
// $canSeeEmailApps = $this->hasRequiredRole(['User', 'Admin']); // Example roles
// $canSeeLoanApps = $this->hasRequiredRole(['User', 'Admin']); // Example roles
                // $canSeeMyApplications = $canSeeEmailApps || $canSeeLoanApps;
            @endphp
            @if ($canSeeMyApplications)
                <li class="menu-item {{ request()->routeIs('my-applications.*') ? 'active open' : '' }}">
                    <a href="javascript:void(0);" class="menu-link menu-toggle">
                        <i class="menu-icon tf-icons ti ti-folder"></i> {{-- Example icon --}}
                        <div>{{ __('My Applications') }}</div>
                    </a>
                    <ul class="menu-sub">
                        {{-- Email Applications List --}}
                        @php $canSeeEmailAppsList = $this->hasRequiredRole(['User', 'Admin']); @endphp {{-- Example roles --}}
                        @if ($canSeeEmailAppsList)
                            {{-- Route name: my-applications.email.index --}}
                            <li class="menu-item {{ request()->routeIs('my-applications.email.*') ? 'active' : '' }}">
                                {{-- Wildcard for show route --}}
                                <a href="{{ route('my-applications.email.index') }}" class="menu-link">
                                    <div>{{ __('Email Applications') }}</div>
                                </a>
                            </li>
                        @endif
                        {{-- Loan Applications List --}}
                        @php $canSeeLoanAppsList = $this->hasRequiredRole(['User', 'Admin']); @endphp {{-- Example roles --}}
                        @if ($canSeeLoanAppsList)
                            {{-- Route name: my-applications.loan.index --}}
                            <li class="menu-item {{ request()->routeIs('my-applications.loan.*') ? 'active' : '' }}">
                                {{-- Wildcard for show route --}}
                                <a href="{{ route('my-applications.loan.index') }}" class="menu-link">
                                    <div>{{ __('Loan Applications') }}</div>
                                </a>
                            </li>
                        @endif
                    </ul>
                </li>
            @endif {{-- End My Applications parent check --}}
        @endauth {{-- End @auth check --}}

        {{-- Apply for Resources (Accessible to all authenticated users) --}}
        {{-- Middleware: auth (handled by the main route group) --}}
        @auth {{-- This check is generally sufficient if routes are protected by auth middleware --}}
            @php
                // Decide if the parent 'Apply' should be shown if *any* child is visible
                $canSeeApplySection = $this->hasRequiredRole(['User', 'Admin']); // Example roles
                // Alternative: Check specific child routes' roles if needed
// $canSeeEmailApply = $this->hasRequiredRole(['User', 'Admin']); // Example roles
// $canSeeLoanApply = $this->hasRequiredRole(['User', 'Admin']); // Example roles
                // $canSeeApplySection = $canSeeEmailApply || $canSeeLoanApply;
            @endphp
            @if ($canSeeApplySection)
                <li
                    class="menu-item {{ request()->routeIs('resource-management.email-applications.create') || request()->routeIs('resource-management.loan-applications.create') ? 'active open' : '' }}">
                    <a href="javascript:void(0);" class="menu-link menu-toggle">
                        <i class="menu-icon tf-icons ti ti-file-invoice"></i> {{-- Example icon --}}
                        <div>{{ __('Apply for Resources') }}</div>
                    </a>
                    <ul class="menu-sub">
                        {{-- Email Application Form --}}
                        @php $canSeeEmailApplyForm = $this->hasRequiredRole(['User', 'Admin']); @endphp {{-- Example roles --}}
                        @if ($canSeeEmailApplyForm)
                            {{-- Route name: resource-management.email-applications.create --}}
                            <li
                                class="menu-item {{ request()->routeIs('resource-management.email-applications.create') ? 'active' : '' }}">
                                <a href="{{ route('resource-management.email-applications.create') }}" class="menu-link">
                                    <div>{{ __('Email Account Application') }}</div>
                                </a>
                            </li>
                        @endif
                        {{-- Loan Application Form --}}
                        @php $canSeeLoanApplyForm = $this->hasRequiredRole(['User', 'Admin']); @endphp {{-- Example roles --}}
                        @if ($canSeeLoanApplyForm)
                            {{-- Route name: resource-management.loan-applications.create --}}
                            <li
                                class="menu-item {{ request()->routeIs('resource-management.loan-applications.create') ? 'active' : '' }}">
                                <a href="{{ route('resource-management.loan-applications.create') }}" class="menu-link">
                                    <div>{{ __('ICT Equipment Loan Application') }}</div>
                                </a>
                            </li>
                        @endif
                    </ul>
                </li>
            @endif {{-- End Apply for Resources check --}}
        @endauth {{-- End @auth check --}}


        {{-- Approvals Dashboard (Accessible to users with required grade for approval) --}}
        {{-- Middleware: grade:config('motac.approval.min_approver_grade_level') --}}
        {{-- We approximate this check in the menu using roles typically assigned to approvers. --}}
        @php
            // Check for roles that are typically assigned to approvers.
            $canSeeApprovalsDashboard = $this->hasRequiredRole(['Approver', 'Admin']); // Example roles
        @endphp
        @if ($canSeeApprovalsDashboard)
            <li class="menu-item {{ request()->routeIs('approvals.index') ? 'active' : '' }}">
                {{-- Route name: approvals.index --}}
                <a href="{{ route('approvals.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons ti ti-checks"></i> {{-- Example icon --}}
                    <div>{{ __('Approval Dashboard') }}</div>
                </a>
            </li>
        @endif {{-- End Approvals Dashboard check --}}

        {{-- Approval History (Accessible to approvers or Admin/BPM) --}}
        {{-- Route name: approvals.history --}}
        {{-- Apply similar role check as the dashboard, or Admin/BPM if they can see history --}}
        @php
            $canSeeApprovalHistory = $this->hasRequiredRole(['Approver', 'Admin', 'BPM']); // Example roles
        @endphp
        @if ($canSeeApprovalHistory)
            <li class="menu-item {{ request()->routeIs('approvals.history') ? 'active' : '' }}">
                <a href="{{ route('approvals.history') }}" class="menu-link">
                    <i class="menu-icon tf-icons ti ti-history"></i> {{-- Example icon --}}
                    <div>{{ __('Approval History') }}</div>
                </a>
            </li>
        @endif {{-- End Approval History check --}}


        {{-- Resource Management Admin/BPM Section --}}
        @php
            // Check if the user has Admin or BPM roles for this parent section
            // Roles from web.php children: Admin|BPM, Admin
            $canSeeIrmAdministration = $this->hasRequiredRole(['Admin', 'BPM']);
        @endphp
        @if ($canSeeIrmAdministration)
            {{-- Using 'admin.' prefix for routes inside this group --}}
            <li class="menu-item {{ request()->routeIs('admin.*') ? 'active open' : '' }}">
                <a href="javascript:void(0);" class="menu-link menu-toggle">
                    <i class="menu-icon tf-icons ti ti-building-warehouse"></i> {{-- Example icon --}}
                    <div>{{ __('IRM Administration') }}</div>
                </a>
                <ul class="menu-sub">
                    {{-- Manage Users (Admin only) --}}
                    @php $canSeeManageUsers = $this->hasRequiredRole(['Admin']); @endphp
                    @if ($canSeeManageUsers)
                        {{-- Route name: admin.users.index --}}
                        <li class="menu-item {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                            {{-- Use wildcard for active state --}}
                            <a href="{{ route('admin.users.index') }}" class="menu-link">
                                <div>{{ __('Manage Users') }}</div>
                            </a>
                        </li>
                    @endif
                    {{-- Manage Equipment Assets (Admin/BPM) --}}
                    @php $canSeeManageEquipment = $this->hasRequiredRole(['Admin', 'BPM']); @endphp
                    @if ($canSeeManageEquipment)
                        {{-- Route name: admin.equipment.index --}}
                        <li class="menu-item {{ request()->routeIs('admin.equipment.*') ? 'active' : '' }}">
                            {{-- Use wildcard for active state --}}
                            <a href="{{ route('admin.equipment.index') }}" class="menu-link">
                                <div>{{ __('Manage Equipment') }}</div>
                            </a>
                        </li>
                    @endif
                    {{-- Manage Grades (Admin only) --}}
                    @php $canSeeManageGrades = $this->hasRequiredRole(['Admin']); @endphp
                    @if ($canSeeManageGrades)
                        {{-- Route name: admin.grades.index --}}
                        <li class="menu-item {{ request()->routeIs('admin.grades.*') ? 'active' : '' }}">
                            {{-- Use wildcard for active state --}}
                            <a href="{{ route('admin.grades.index') }}" class="menu-link">
                                <div>{{ __('Manage Grades') }}</div>
                            </a>
                        </li>
                    @endif

                    {{-- BPM Staff Interface (BPM only) --}}
                    @php
                        // Check if the user has the BPM role for this sub-section
                        // Roles from web.php: BPM
                        $canSeeBpmTasks = $this->hasRequiredRole(['BPM']);
                    @endphp
                    @if ($canSeeBpmTasks)
                        <li class="menu-item {{ request()->routeIs('admin.bpm.*') ? 'active open' : '' }}">
                            <a href="javascript:void(0);" class="menu-link menu-toggle">
                                <div>{{ __('BPM Tasks') }}</div>
                            </a>
                            <ul class="menu-sub">
                                {{-- Outstanding Loans (Ready for Issuance) --}}
                                @php $canSeeOutstandingLoans = $this->hasRequiredRole(['BPM']); @endphp
                                @if ($canSeeOutstandingLoans)
                                    {{-- Route name: admin.bpm.outstanding-loans --}}
                                    <li
                                        class="menu-item {{ request()->routeIs('admin.bpm.outstanding-loans') ? 'active' : '' }}">
                                        <a href="{{ route('admin.bpm.outstanding-loans') }}" class="menu-link">
                                            <div>{{ __('Outstanding Loans') }}</div>
                                        </a>
                                    </li>
                                @endif
                                {{-- Issued Loans (Ready for Return) --}}
                                @php $canSeeIssuedLoans = $this->hasRequiredRole(['BPM']); @endphp
                                @if ($canSeeIssuedLoans)
                                    {{-- Route name: admin.bpm.issued-loans --}}
                                    <li
                                        class="menu-item {{ request()->routeIs('admin.bpm.issued-loans') ? 'active' : '' }}">
                                        <a href="{{ route('admin.bpm.issued-loans') }}" class="menu-link">
                                            <div>{{ __('Issued Loans') }}</div>
                                        </a>
                                    </li>
                                @endif
                                {{-- Transaction Details (Show view) - likely not in main menu, accessed from list --}}
                                {{-- @php $canSeeTransactionDetails = $this->hasRequiredRole(['BPM', 'Admin']); @endphp
                                   @if ($canSeeTransactionDetails)
                                       <li class="menu-item {{ request()->routeIs('admin.bpm.transactions.show') ? 'active' : '' }}">
                                           <a href="{{ route('admin.bpm.transactions.show', ['loanTransaction' => 0]) }}" class="menu-link"> // Placeholder ID
                                               <div>{{ __('Transaction Details') }}</div>
                                           </a>
                                       </li>
                                   @endif --}}
                            </ul>
                        </li>
                    @endif {{-- End BPM Tasks check --}}

                    {{-- Admin Reports (Admin only) --}}
                    @php
                        // Check if the user has the Admin role for this sub-section
                        // Roles from web.php: Admin
                        $canSeeAdminReports = $this->hasRequiredRole(['Admin']);
                    @endphp
                    @if ($canSeeAdminReports)
                        <li class="menu-item {{ request()->routeIs('admin.reports.*') ? 'active open' : '' }}">
                            <a href="javascript:void(0);" class="menu-link menu-toggle">
                                <div>{{ __('Reports') }}</div>
                            </a>
                            <ul class="menu-sub">
                                {{-- Equipment Report --}}
                                @php $canSeeEquipmentReport = $this->hasRequiredRole(['Admin']); @endphp
                                @if ($canSeeEquipmentReport)
                                    {{-- Route name: admin.reports.equipment --}}
                                    <li
                                        class="menu-item {{ request()->routeIs('admin.reports.equipment') ? 'active' : '' }}">
                                        <a href="{{ route('admin.reports.equipment') }}" class="menu-link">
                                            <div>{{ __('Equipment Report') }}</div>
                                        </a>
                                    </li>
                                @endif
                                {{-- Email Accounts Report --}}
                                @php $canSeeEmailAccountsReport = $this->hasRequiredRole(['Admin']); @endphp
                                @if ($canSeeEmailAccountsReport)
                                    {{-- Route name: admin.reports.email-accounts --}}
                                    <li
                                        class="menu-item {{ request()->routeIs('admin.reports.email-accounts') ? 'active' : '' }}">
                                        <a href="{{ route('admin.reports.email-accounts') }}" class="menu-link">
                                            <div>{{ __('Email Accounts Report') }}</div>
                                        </a>
                                    </li>
                                @endif
                                {{-- Loan Applications Report --}}
                                @php $canSeeLoanAppsReport = $this->hasRequiredRole(['Admin']); @endphp
                                @if ($canSeeLoanAppsReport)
                                    {{-- Route name: admin.reports.loan-applications --}}
                                    <li
                                        class="menu-item {{ request()->routeIs('admin.reports.loan-applications') ? 'active' : '' }}">
                                        <a href="{{ route('admin.reports.loan-applications') }}" class="menu-link">
                                            <div>{{ __('Loan Applications Report') }}</div>
                                        </a>
                                    </li>
                                @endif
                                {{-- User Activity Report --}}
                                @php $canSeeUserActivityReport = $this->hasRequiredRole(['Admin']); @endphp
                                @if ($canSeeUserActivityReport)
                                    {{-- Route name: admin.reports.user-activity --}}
                                    <li
                                        class="menu-item {{ request()->routeIs('admin.reports.user-activity') ? 'active' : '' }}">
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

        {{-- Logout Link (Keep this as a standard menu item) --}}
        <li class="menu-item">
            {{-- Link that triggers the logout form submission via JavaScript --}}
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

    </ul> {{-- End main menu-inner list --}}

</aside> {{-- End aside layout-menu --}}
