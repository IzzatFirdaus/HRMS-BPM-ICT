@php
    $configData = Helper::appClasses();
@endphp

<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">

    @if (!isset($navbarFull))
        <div class="app-brand demo">
            <a href="{{ url('/') }}" class="app-brand-link">
                <span class="app-brand-logo demo">
                    @include('_partials.macros', ['height' => 20]) {{-- Adjust path if needed --}}
                </span>
                <span class="app-brand-text demo menu-text fw-bold">{{ config('variables.templateName') }}</span>
                {{-- Adjust brand name --}}
            </a>

            <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto">
                <i class="ti menu-toggle-icon d-none d-xl-block ti-sm align-middle"></i> {{-- Adjust icon classes --}}
                <i class="ti ti-x d-block d-xl-none ti-sm align-middle"></i> {{-- Adjust icon classes --}}
            </a>
        </div>
    @endif

    <div class="menu-inner-shadow"></div>

    <ul class="menu-inner py-1">
        {{-- Existing menu items generated from $menuData --}}
        @foreach ($menuData->menu as $menu)
            {{-- adding active and open class if child is active --}}
            @php
                $activeClass = null;
                $currentRouteName = Route::currentRouteName();

                if (isset($menu->slug) && $currentRouteName === $menu->slug) {
                    $activeClass = 'active';
                } elseif (isset($menu->submenu)) {
                    if (gettype($menu->slug) === 'array') {
                        foreach ($menu->slug as $slug) {
                            // Check if current route name starts with any of the submenu slugs
                            if (str_starts_with($currentRouteName, $slug)) {
                                $activeClass = 'active open';
                                break; // Found a match, no need to check other slugs for this menu item
                            }
                        }
                    } elseif (isset($menu->slug) && str_starts_with($currentRouteName, $menu->slug)) {
                        // Check if current route name starts with the menu slug
                        $activeClass = 'active open';
                    } else {
                        // Fallback check for submenu items if parent slug doesn't match directly
        foreach ($menu->submenu as $submenuItem) {
            if (isset($submenuItem->slug) && str_starts_with($currentRouteName, $submenuItem->slug)) {
                $activeClass = 'active open';
                                break 2; // Found a match in submenu, break both loops
                            }
                        }
                    }
                }
            @endphp

            {{-- main menu --}}
            <li class="menu-item {{ $activeClass }}">
                <a href="{{ isset($menu->url) ? url($menu->url) : (isset($menu->submenu) ? 'javascript:void(0);' : '#') }}"
                    class="{{ isset($menu->submenu) ? 'menu-link menu-toggle' : 'menu-link' }}"
                    @if (isset($menu->target) and !empty($menu->target)) target="_blank" @endif>
                    @isset($menu->icon)
                        <i class="{{ $menu->icon }}"></i> {{-- Existing icon --}}
                    @endisset
                    <div>{{ isset($menu->name) ? __($menu->name) : '' }}</div>
                    @isset($menu->badge)
                        <div class="badge bg-label-{{ $menu->badge[0] }} rounded-pill ms-auto">{{ $menu->badge[1] }}</div>
                        {{-- Existing badge --}}
                    @endisset
                </a>

                {{-- submenu --}}
                @isset($menu->submenu)
                    @include('layouts.sections.menu.submenu', ['menu' => $menu->submenu]) {{-- Adjust path if needed --}}
                @endisset
            </li>
        @endforeach

        {{-- 👇 New MOTAC Integrated Resource Management Menu Items 👇 --}}
        {{-- Hardcoded menu items after the data-driven loop --}}

        {{-- Check if the user has access to *any* RM feature before showing the header --}}
        {{-- You'll need to define a Gate or Policy method like 'accessAnyResourceManagementFeature' --}}
        {{-- Or simply check against a set of required permissions/roles --}}
        @php
            $hasResourceManagementAccess =
                Auth::user()->can('create', \App\Models\EmailApplication::class) ||
                Auth::user()->can('create', \App\Models\LoanApplication::class) ||
                Auth::user()->can('viewAny', \App\Models\EmailApplication::class) || // For My Applications
                Auth::user()->can('viewAny', \App\Models\LoanApplication::class) || // For My Applications
                Auth::user()->can('view-approvals-dashboard') || // Example permission for approvals
                Auth::user()->hasRole('Admin') || // Check for Admin role
                Auth::user()->hasRole('BPM'); // Check for BPM role
        @endphp

        @if ($hasResourceManagementAccess)
            <li class="menu-header small text-uppercase">
                <span class="menu-header-text">{{ __('Resource Management') }}</span> {{-- Header for the new section (Localized) --}}
            </li>

            {{-- Link to Email/User ID Request Form --}}
            {{-- Check if the user can create an email application (using Policy) --}}
            @can('create', \App\Models\EmailApplication::class)
                <li class="menu-item {{ request()->routeIs('email-applications.create') ? 'active' : '' }}">
                    {{-- CORRECTED ROUTE NAME HERE --}}
                    <a href="{{ route('email-applications.create') }}" class="menu-link">
                        <i class="menu-icon tf-icons ti ti-mail"></i> {{-- Example icon class --}}
                        <div>{{ __('Email/User ID Request') }}</div> {{-- Localized label --}}
                    </a>
                </li>
            @endcan

            {{-- Link to ICT Equipment Loan Form --}}
            {{-- Check if the user can create a loan application (using Policy) --}}
            @can('create', \App\Models\LoanApplication::class)
                <li class="menu-item {{ request()->routeIs('loan-applications.create') ? 'active' : '' }}">
                    {{-- CORRECTED ROUTE NAME HERE --}}
                    <a href="{{ route('loan-applications.create') }}" class="menu-link">
                        <i class="menu-icon tf-icons ti ti-laptop"></i> {{-- Example icon class --}}
                        <div>{{ __('ICT Equipment Loan') }}</div> {{-- Localized label --}}
                    </a>
                </li>
            @endcan

            {{-- Link to My Applications (List of user's submitted applications) --}}
            {{-- This link is for the applicant to see their own applications --}}
            {{-- Check if the user can view ANY email or loan application (using Policy viewAny) --}}
            @if (Auth::user()->can('viewAny', \App\Models\EmailApplication::class) ||
                    Auth::user()->can('viewAny', \App\Models\LoanApplication::class))
                <li class="menu-item {{ request()->routeIs('my-applications.*') ? 'active open' : '' }}">
                    <a href="javascript:void(0);" class="menu-link menu-toggle">
                        <i class="menu-icon tf-icons ti ti-file-alt"></i> {{-- Example icon --}}
                        <div>{{ __('My Applications') }}</div> {{-- Localized label --}}
                    </a>
                    <ul class="menu-sub">
                        {{-- Link to user's email applications list --}}
                        {{-- Check if user can view ANY email application (Policy viewAny) --}}
                        @can('viewAny', \App\Models\EmailApplication::class)
                            <li class="menu-item {{ request()->routeIs('my-applications.email.*') ? 'active' : '' }}">
                                <a href="{{ route('my-applications.email.index') }}" class="menu-link">
                                    <div>{{ __('Email Accounts') }}</div> {{-- Localized label --}}
                                </a>
                            </li>
                        @endcan
                        {{-- Link to user's loan applications list --}}
                        {{-- Check if user can view ANY loan application (Policy viewAny) --}}
                        @can('viewAny', \App\Models\LoanApplication::class)
                            <li class="menu-item {{ request()->routeIs('my-applications.loan.*') ? 'active' : '' }}">
                                <a href="{{ route('my-applications.loan.index') }}" class="menu-link">
                                    <div>{{ __('Equipment Loans') }}</div> {{-- Localized label --}}
                                </a>
                            </li>
                        @endcan
                    </ul>
                </li>
            @endif


            {{-- Approvals Menu (Show only for users with approval permissions/roles) --}}
            {{-- Check if the user can view the approvals dashboard or any approval list --}}
            {{-- Assuming 'view-approvals-dashboard' permission or similar gate is used --}}
            @can('view-approvals-dashboard')
                {{-- Example permission, adjust based on your setup --}}
                <li class="menu-item {{ request()->routeIs('approvals.*') ? 'active open' : '' }}">
                    <a href="javascript:void(0);" class="menu-link menu-toggle">
                        <i class="menu-icon tf-icons ti ti-checks"></i> {{-- Example icon class --}}
                        <div>{{ __('Approvals') }}</div> {{-- Localized label --}}
                    </a>
                    <ul class="menu-sub">
                        {{-- Link to Pending Approvals list --}}
                        {{-- Check if the user can view pending approvals (adjust permission/gate name) --}}
                        @can('view-pending-approvals')
                            {{-- Example permission --}}
                            <li class="menu-item {{ request()->routeIs('approvals.index') ? 'active' : '' }}">
                                {{-- CORRECTED ROUTE NAME HERE --}}
                                <a href="{{ route('approvals.index') }}" class="menu-link">
                                    <div>{{ __('Pending Approvals') }}</div> {{-- Localized label --}}
                                </a>
                            </li>
                        @endcan
                        {{-- Link to Approval History --}}
                        {{-- Check if the user can view approval history (adjust permission/gate name) --}}
                        @can('view-approval-history')
                            {{-- Example permission --}}
                            <li class="menu-item {{ request()->routeIs('approvals.history') ? 'active' : '' }}">
                                {{-- CORRECTED ROUTE NAME HERE --}}
                                <a href="{{ route('approvals.history') }}" class="menu-link">
                                    <div>{{ __('Approval History') }}</div> {{-- Localized label --}}
                                </a>
                            </li>
                        @endcan
                    </ul>
                </li>
            @endcan

            {{-- Admin and BPM Staff Routes for Resource Management --}}
            {{-- Check for admin/BPM specific permissions or roles --}}
            {{-- Assuming 'access-rm-admin' is a permission for general RM Admin section access, or check roles directly --}}
            @if (Auth::user()->hasRole('Admin') || Auth::user()->hasRole('BPM')) {{-- Example: Check if user has Admin or BPM role --}}
                {{-- @can('access-rm-admin') --}}{{-- Or use a permission --}}
                <li
                    class="menu-item has-submenu {{ request()->routeIs('resource-management.admin.*') ? 'active open' : '' }}">
                    {{-- Use the resource-management.admin.* prefix for checking active state --}}
                    <a href="javascript:void(0);" class="menu-link menu-toggle">
                        <i class="menu-icon tf-icons ti ti-tools"></i> {{-- Example icon --}}
                        <div>{{ __('RM Administration') }}</div> {{-- Localized label --}}
                    </a>
                    <ul class="menu-sub">
                        {{-- User Management (Admin only) --}}
                        @can('viewAny', \App\Models\User::class)
                            {{-- Check if user can view users (Policy) --}}
                            @role('Admin')
                                {{-- Also check for Admin role as per routes file --}}
                                <li
                                    class="menu-item {{ request()->routeIs('resource-management.admin.users.*') ? 'active' : '' }}">
                                    {{-- CORRECTED ROUTE NAME HERE --}}
                                    <a href="{{ route('resource-management.admin.users.index') }}" class="menu-link">
                                        <div>{{ __('Manage Users') }}</div> {{-- Localized label --}}
                                    </a>
                                </li>
                            @endrole
                        @endcan

                        {{-- Equipment Management (Admin/BPM) --}}
                        @can('viewAny', \App\Models\Equipment::class)
                            {{-- Check if user can view equipment (Policy) --}}
                            <li
                                class="menu-item {{ request()->routeIs('resource-management.admin.equipment.*') ? 'active' : '' }}">
                                {{-- CORRECTED ROUTE NAME HERE --}}
                                <a href="{{ route('resource-management.admin.equipment.index') }}" class="menu-link">
                                    <div>{{ __('Equipment Inventory') }}</div> {{-- Localized label --}}
                                </a>
                            </li>
                        @endcan

                        {{-- Organizational Data (Admin only) --}}
                        {{-- Assuming a policy or permission like 'viewAny' for Grade model --}}
                        @can('viewAny', \App\Models\Grade::class)
                            {{-- Example Policy check --}}
                            @role('Admin')
                                {{-- Also check for Admin role as per routes file --}}
                                <li
                                    class="menu-item {{ request()->routeIs('resource-management.admin.grades.*') ? 'active' : '' }}">
                                    {{-- CORRECTED ROUTE NAME HERE --}}
                                    <a href="{{ route('resource-management.admin.grades.index') }}" class="menu-link">
                                        <div>{{ __('Manage Grades') }}</div> {{-- Localized label --}}
                                    </a>
                                </li>
                            @endrole
                        @endcan


                        {{-- BPM Operations Links (Issuance/Return) --}}
                        {{-- Check if the user can access BPM operations (adjust permission/gate name) --}}
                        @if (Auth::user()->hasRole('Admin') || Auth::user()->hasRole('BPM'))
                            {{-- Check Admin or BPM role as per routes file --}}
                            {{-- @can('access-bpm-interface') --}}{{-- Or use a permission --}}
                            <li
                                class="menu-item {{ request()->routeIs('resource-management.admin.bpm.*') ? 'active open' : '' }}">
                                {{-- Use the resource-management.admin.bpm.* prefix for active state --}}
                                <a href="javascript:void(0);" class="menu-link menu-toggle">
                                    <div>{{ __('BPM Operations') }}</div> {{-- Localized label --}}
                                </a>
                                <ul class="menu-sub">
                                    {{-- Link to a view listing outstanding loans for issuance --}}
                                    {{-- Check if user can issue loan equipment (adjust permission/gate name) --}}
                                    @can('issue-loan-equipment')
                                        {{-- Example permission --}}
                                        <li
                                            class="menu-item {{ request()->routeIs('resource-management.admin.bpm.outstanding-loans') ? 'active' : '' }}">
                                            {{-- CORRECTED ROUTE NAME HERE --}}
                                            <a href="{{ route('resource-management.admin.bpm.outstanding-loans') }}"
                                                class="menu-link">
                                                <div>{{ __('Outstanding Loans') }}</div> {{-- Localized label --}}
                                            </a>
                                        </li>
                                    @endcan
                                    {{-- Link to a view listing issued loans for return --}}
                                    {{-- Check if user can return loan equipment (adjust permission/gate name) --}}
                                    @can('return-loan-equipment')
                                        {{-- Example permission --}}
                                        <li
                                            class="menu-item {{ request()->routeIs('resource-management.admin.bpm.issued-loans') ? 'active' : '' }}">
                                            {{-- CORRECTED ROUTE NAME HERE --}}
                                            <a href="{{ route('resource-management.admin.bpm.issued-loans') }}"
                                                class="menu-link">
                                                <div>{{ __('Issued Equipment') }}</div> {{-- Localized label --}}
                                            </a>
                                        </li>
                                    @endcan
                                    {{-- Link to view all transactions --}}
                                    {{-- Check if user can view any loan transaction (adjust permission/gate name) --}}
                                    @can('viewAny', \App\Models\LoanTransaction::class)
                                        {{-- Example Policy check --}}
                                        <li
                                            class="menu-item {{ request()->routeIs('resource-management.admin.bpm.transactions.*') ? 'active' : '' }}">
                                            {{-- Assuming a list route for transactions --}}
                                            {{-- If you have an index route for transactions, use it here --}}
                                            {{-- Example: route('resource-management.admin.bpm.transactions.index') --}}
                                            {{-- For now, linking to the outstanding loans list or similar as a placeholder --}}
                                            <a href="{{ route('resource-management.admin.bpm.outstanding-loans') }}"
                                                class="menu-link"> {{-- Adjust this route as needed --}}
                                                <div>{{ __('All Transactions') }}</div> {{-- Localized label --}}
                                            </a>
                                        </li>
                                    @endcan
                                </ul>
                            </li>
                        @endif {{-- End Admin/BPM role check for BPM Operations --}}

                        {{-- Reports Links --}}
                        {{-- Check if the user can view reports (adjust permission/gate name) --}}
                        @can('view-reports')
                            {{-- Example permission --}}
                            <li
                                class="menu-item {{ request()->routeIs('resource-management.admin.reports.*') ? 'active open' : '' }}">
                                {{-- Use the resource-management.admin.reports.* prefix for active state --}}
                                <a href="javascript:void(0);" class="menu-link menu-toggle">
                                    <div>{{ __('Reports') }}</div> {{-- Localized label --}}
                                </a>
                                <ul class="menu-sub">
                                    {{-- Link to Equipment Report --}}
                                    {{-- Check if user can view equipment report (adjust permission/gate name) --}}
                                    @can('view-equipment-report')
                                        {{-- Example permission --}}
                                        <li
                                            class="menu-item {{ request()->routeIs('resource-management.admin.reports.equipment') ? 'active' : '' }}">
                                            {{-- CORRECTED ROUTE NAME HERE --}}
                                            <a href="{{ route('resource-management.admin.reports.equipment') }}"
                                                class="menu-link">
                                                <div>{{ __('Equipment Inventory Report') }}</div> {{-- Localized label --}}
                                            </a>
                                        </li>
                                    @endcan
                                    {{-- Link to Email Accounts Report --}}
                                    {{-- Check if user can view email report (adjust permission/gate name) --}}
                                    @can('view-email-accounts-report')
                                        {{-- Example permission --}}
                                        <li
                                            class="menu-item {{ request()->routeIs('resource-management.admin.reports.email-accounts') ? 'active' : '' }}">
                                            {{-- CORRECTED ROUTE NAME HERE --}}
                                            <a href="{{ route('resource-management.admin.reports.email-accounts') }}"
                                                class="menu-link">
                                                <div>{{ __('Email Accounts Report') }}</div> {{-- Localized label --}}
                                            </a>
                                        </li>
                                    @endcan
                                    {{-- Link to Loan Applications Report --}}
                                    {{-- Check if user can view loan report (adjust permission/gate name) --}}
                                    @can('view-loan-applications-report')
                                        {{-- Example permission --}}
                                        <li
                                            class="menu-item {{ request()->routeIs('resource-management.admin.reports.loan-applications') ? 'active' : '' }}">
                                            {{-- CORRECTED ROUTE NAME HERE --}}
                                            <a href="{{ route('resource-management.admin.reports.loan-applications') }}"
                                                class="menu-link">
                                                <div>{{ __('Loan Applications Report') }}</div> {{-- Localized label --}}
                                            </a>
                                        </li>
                                    @endcan
                                    {{-- Link to User Activity Report --}}
                                    {{-- Check if user can view activity report (adjust permission/gate name) --}}
                                    @can('view-user-activity-report')
                                        {{-- Example permission --}}
                                        <li
                                            class="menu-item {{ request()->routeIs('resource-management.admin.reports.user-activity') ? 'active' : '' }}">
                                            {{-- CORRECTED ROUTE NAME HERE --}}
                                            <a href="{{ route('resource-management.admin.reports.user-activity') }}"
                                                class="menu-link">
                                                <div>{{ __('User Activity Report') }}</div> {{-- Localized label --}}
                                            </a>
                                        </li>
                                    @endcan
                                </ul>
                            </li>
                        @endcan


                        {{-- Add other admin/management links here (e.g., for managing Departments, Positions specific to RM if needed) --}}
                        {{-- Example: Link to Admin User Management (if not already covered) --}}
                        {{-- If you need a link specifically for the AdminUserController from routes/web.php --}}
                        {{-- @can('viewAny', \App\Models\User::class) --}}
                        {{--    <li class="menu-item {{ request()->routeIs('resource-management.admin.users.index') ? 'active' : '' }}"> --}}
                        {{--        <a href="{{ route('resource-management.admin.users.index') }}" class="menu-link"> --}}
                        {{--            <div>{{ __('Admin Users') }}</div> --}}
                        {{--        </a> --}}
                        {{--    </li> --}}
                        {{-- @endcan --}}

                    </ul>
                </li>
            @endif {{-- End Admin/BPM role check for RM Administration parent menu --}}
            {{-- @endcan --}}{{-- End general RM admin permission check --}}

        @endif {{-- End general RM access check --}}
        {{-- ☝️ End New MOTAC Integrated Resource Management Menu Items ☝️ --}}


        {{-- Logout Link (Keep this) --}}
        <li class="menu-item">
            <a href="#" class="menu-link"
                onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                <i class="menu-icon tf-icons ti ti-logout"></i> {{-- Example icon class --}}
                <div>{{ __('Logout') }}</div> {{-- Localized label --}}
            </a>
        </li>
        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
            @csrf
        </form>

    </ul>

</aside>
