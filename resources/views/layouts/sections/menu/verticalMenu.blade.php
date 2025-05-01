@php
    // Retrieve application configuration data using the Helper facade
    // This data is likely needed for theme-specific settings, like layout type for active class logic.
    $configData = Helper::appClasses();

    // Safely get the authenticated user and their roles using Laravel's Auth facade
// This block is for determining the user's primary role for menu display logic in the existing data-driven menu.
    $user = Auth::user();
    $role = null; // Initialize $role variable

    // Determine the user's primary role based on spatie/laravel-permission package
if ($user) {
    // Get the user's role names using getRoleNames(), provide fallback
        $userRoles = $user->getRoleNames() ?? collect();

        if ($userRoles->contains('Admin')) {
            // Prioritize 'Admin' role for access checks
            $role = 'Admin';
        } elseif ($userRoles->isNotEmpty()) {
            // If user has other roles but not 'Admin', use the first role found
            $role = $userRoles->first();
        } else {
            // If user is authenticated but has no roles assigned (edge case)
            $role = 'Authenticated User'; // Assign a default status role
        }
    } else {
        // If no user is authenticated (should not happen within authenticated routes)
        $role = 'Guest'; // Assign 'Guest' role for logic
    }

    // Assuming $menuData is a public property available in the Livewire component,
    // containing the menu structure for the data-driven part. Provide a fallback.
    $menuItems = $menuData->menu ?? [];

@endphp

{{-- Vertical menu aside element with theme classes and a unique ID --}}
<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">

    {{-- Application Brand/Logo Section --}}
    {{-- Conditional display: Hide the brand section if $navbarFull is set (assuming it's a boolean) --}}
    @if (!isset($navbarFull) || !$navbarFull)
        {{-- Check if $navbarFull is not set or is false --}}
        <div class="app-brand demo">
            {{-- Link to your application's root or dashboard --}}
            <a href="{{ url('/') }}" class="app-brand-link">
                {{-- Include a partial for the application logo/macros --}}
                {{-- Adjust path '_partials.macros' if needed --}}
                <span class="app-brand-logo demo">
                    @include('_partials.macros', ['height' => 20]) {{-- Pass height variable to the partial --}}
                </span>
                {{-- Application Name displayed next to the logo, fetched from Laravel config --}}
                {{-- Adjust brand name if needed by changing 'variables.templateName' in your config --}}
                <span class="app-brand-text demo menu-text fw-bold">{{ config('variables.templateName') }}</span>
            </a>

            {{-- Menu Toggle Button (for mobile/collapsed state) --}}
            {{-- 'style="visibility: hidden"' is likely controlled by CSS or JS for responsiveness --}}
            <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto">
                {{-- Icons for toggling the menu --}}
                {{-- Adjust icon classes if needed --}}
                <i class="ti menu-toggle-icon d-none d-xl-block ti-sm align-middle"></i> {{-- Icon for desktop toggle --}}
                <i class="ti ti-x d-block d-xl-none ti-sm align-middle"></i> {{-- Icon for mobile toggle (e.g., close) --}}
            </a>
        </div>
    @endif {{-- End conditional brand display --}}

    {{-- Menu Inner Shadow (theme-specific styling element) --}}
    <div class="menu-inner-shadow"></div>

    {{-- Main Menu Navigation List --}}
    <ul class="menu-inner py-1">
        {{-- Existing menu items generated from $menuData (data-driven part) --}}
        {{-- Added check to ensure $menuItems is iterable before looping --}}
        @foreach ($menuItems as $menu)
            {{-- Determine active and open classes based on the current route --}}
            @php
                $activeClass = null; // Initialize active class variable
                // Get the current route name safely
                $currentRouteName = Route::currentRouteName();

                // Check if the current route name exactly matches the menu item's slug/route name (if it has one)
if (isset($menu->slug) && $currentRouteName === $menu->slug) {
    $activeClass = 'active';
}
// If the menu item has a submenu, check for active state based on its slugs
elseif (isset($menu->submenu)) {
    // Handle case where slug is an array
    if (gettype($menu->slug) === 'array') {
        foreach ($menu->slug as $slug) {
            // Check if current route name exists and starts with any of the submenu slugs
            if ($currentRouteName && str_starts_with($currentRouteName, $slug)) {
                $activeClass = 'active open'; // Apply active and open classes
                break; // Found a match, exit inner loop
            }
        }
    }
    // Handle case where slug is a string and check if current route name starts with it
    elseif (isset($menu->slug) && str_starts_with($currentRouteName, $menu->slug)) {
        $activeClass = 'active open'; // Apply active and open classes
    } else {
        // Fallback check: Iterate through submenu items to see if any child route matches or starts with current route
        // This is less efficient but ensures parent is opened if a child is active when parent slug doesn't match directly.
                        foreach ($menu->submenu as $submenuItem) {
                            if (isset($submenuItem->slug) && str_starts_with($currentRouteName, $submenuItem->slug)) {
                                $activeClass = 'active open'; // Apply active and open classes
                                break 2; // Found a match in submenu, break both loops (inner submenu loop and outer main menu loop)
                            }
                        }
                    }
                }
            @endphp

            {{-- Render the main menu item --}}
            {{-- Apply the determined activeClass --}}
            <li class="menu-item {{ $activeClass }}">
                {{-- Link element for the menu item --}}
                {{-- Determine href based on 'url' property, if it has a submenu (use javascript:void(0)), or a fallback '#' --}}
                <a href="{{ isset($menu->url) ? url($menu->url) : (isset($menu->submenu) ? 'javascript:void(0);' : '#') }}"
                    {{-- Apply 'menu-toggle' class if the item has a submenu to enable toggle functionality --}} class="{{ isset($menu->submenu) ? 'menu-link menu-toggle' : 'menu-link' }}"
                    {{-- Add target="_blank" if the 'target' property is set and not empty --}} @if (isset($menu->target) and !empty($menu->target)) target="_blank" @endif>
                    {{-- Icon for the menu item (optional) --}}
                    @isset($menu->icon)
                        <i class="{{ $menu->icon }}"></i> {{-- Display the icon using its class --}}
                    @endisset
                    {{-- Title of the menu item, translated. Use ?? '' for safe access. --}}
                    <div data-i18n="{{ $menu->name ?? '' }}">{{ isset($menu->name) ? __($menu->name) : '' }}</div>
                    {{-- Optional Badge --}}
                    @isset($menu->badge)
                        {{-- Assuming badge is provided as an array [color, text] --}}
                        <div class="badge bg-label-{{ $menu->badge[0] ?? 'primary' }} rounded-pill ms-auto">
                            {{ $menu->badge[1] ?? '' }}</div>
                        {{-- Display the badge with color and text, provide fallbacks --}}
                    @endisset
                </a>

                {{-- Include the submenu partial if it exists for this menu item --}}
                @isset($menu->submenu)
                    {{-- Adjust path 'layouts.sections.menu.submenu' if needed --}}
                    {{-- Note: Variables like $role, $user, $configData might need to be explicitly passed to the submenu partial --}}
                    @include('layouts.sections.menu.submenu', [
                        'menu' => $menu->submenu, // Pass the submenu data
                        'role' => $role, // Pass the determined user role
                        'user' => $user, // Pass the authenticated user object
                        'configData' => $configData, // Pass application configuration data
                    ])
                @endisset {{-- End submenu inclusion --}}
            </li> {{-- End main menu item --}}
        @endforeach {{-- End loop through data-driven menu items --}}

        {{-- 👇 New MOTAC Integrated Resource Management Menu Items 👇 --}}
        {{-- Hardcoded menu items after the data-driven loop --}}

        {{-- Check if the user has access to *any* RM feature before showing the header --}}
        {{-- This check uses policy checks (viewAny, create) and role checks (Admin, BPM) --}}
        @php
            $hasResourceManagementAccess =
                Auth::user()?->can('create', \App\Models\EmailApplication::class) || // Check if user can create Email Applications
                Auth::user()?->can('create', \App\Models\LoanApplication::class) || // Check if user can create Loan Applications
                Auth::user()?->can('viewAny', \App\Models\EmailApplication::class) || // Check if user can view any Email Application (for My Applications list)
                Auth::user()?->can('viewAny', \App\Models\LoanApplication::class) || // Check if user can view any Loan Application (for My Applications list)
                Auth::user()?->can('view-approvals-dashboard') || // Example permission for approvals dashboard
                Auth::user()?->hasRole('Admin') || // Check if user has 'Admin' role
                Auth::user()?->hasRole('BPM'); // Check if user has 'BPM' role
        @endphp

        @if ($hasResourceManagementAccess) {{-- Show the entire RM section if user has any relevant access --}}
            {{-- Menu Header for the new Resource Management section --}}
            <li class="menu-header small text-uppercase">
                <span class="menu-header-text">{{ __('Resource Management') }}</span> {{-- Header text, Localized --}}
            </li>

            {{-- Link to Email/User ID Request Form --}}
            {{-- Check if the user can create an email application using the Policy --}}
            @can('create', \App\Models\EmailApplication::class)
                {{-- Apply 'active' class if the current route matches the create email application route --}}
                <li class="menu-item {{ request()->routeIs('email-applications.create') ? 'active' : '' }}">
                    {{-- Use the correct named route for creating email applications --}}
                    <a href="{{ route('email-applications.create') }}" class="menu-link">
                        <i class="menu-icon tf-icons ti ti-mail"></i> {{-- Example icon class --}}
                        <div>{{ __('Email/User ID Request') }}</div> {{-- Localized label --}}
                    </a>
                </li>
            @endcan {{-- End can create email application --}}

            {{-- Link to ICT Equipment Loan Form --}}
            {{-- Check if the user can create a loan application using the Policy --}}
            @can('create', \App\Models\LoanApplication::class)
                {{-- Apply 'active' class if the current route matches the create loan application route --}}
                <li class="menu-item {{ request()->routeIs('loan-applications.create') ? 'active' : '' }}">
                    {{-- Use the correct named route for creating loan applications --}}
                    <a href="{{ route('loan-applications.create') }}" class="menu-link">
                        <i class="menu-icon tf-icons ti ti-laptop"></i> {{-- Example icon class --}}
                        <div>{{ __('ICT Equipment Loan') }}</div> {{-- Localized label --}}
                    </a>
                </li>
            @endcan {{-- End can create loan application --}}

            {{-- Link to My Applications (List of user's submitted applications) --}}
            {{-- This link is for the applicant to see their own applications --}}
            {{-- Check if the user can view ANY email OR loan application (using Policy viewAny) --}}
            @if (Auth::user()?->can('viewAny', \App\Models\EmailApplication::class) ||
                    Auth::user()?->can('viewAny', \App\Models\LoanApplication::class))
                {{-- Apply 'active open' classes if any route under 'my-applications.*' is active --}}
                <li class="menu-item {{ request()->routeIs('my-applications.*') ? 'active open' : '' }}">
                    {{-- This is a parent menu item for a submenu, so use javascript:void(0) and menu-toggle --}}
                    <a href="javascript:void(0);" class="menu-link menu-toggle">
                        <i class="menu-icon tf-icons ti ti-file-alt"></i> {{-- Example icon --}}
                        <div>{{ __('My Applications') }}</div> {{-- Localized label --}}
                    </a>
                    {{-- Submenu for My Applications --}}
                    <ul class="menu-sub">
                        {{-- Link to user's email applications list --}}
                        {{-- Check if user can view ANY email application (Policy viewAny) --}}
                        @can('viewAny', \App\Models\EmailApplication::class)
                            {{-- Apply 'active' class if the current route matches any route under 'my-applications.email.*' --}}
                            <li class="menu-item {{ request()->routeIs('my-applications.email.*') ? 'active' : '' }}">
                                {{-- Use the correct named route for the email applications index --}}
                                <a href="{{ route('my-applications.email.index') }}" class="menu-link">
                                    <div>{{ __('Email Accounts') }}</div> {{-- Localized label --}}
                                </a>
                            </li>
                        @endcan {{-- End can view any email application --}}
                        {{-- Link to user's loan applications list --}}
                        {{-- Check if user can view ANY loan application (Policy viewAny) --}}
                        @can('viewAny', \App\Models\LoanApplication::class)
                            {{-- Apply 'active' class if the current route matches any route under 'my-applications.loan.*' --}}
                            <li class="menu-item {{ request()->routeIs('my-applications.loan.*') ? 'active' : '' }}">
                                {{-- Use the correct named route for the loan applications index --}}
                                <a href="{{ route('my-applications.loan.index') }}" class="menu-link">
                                    <div>{{ __('Equipment Loans') }}</div> {{-- Localized label --}}
                                </a>
                            </li>
                        @endcan {{-- End can view any loan application --}}
                    </ul> {{-- End My Applications submenu --}}
                </li> {{-- End My Applications parent menu item --}}
            @endif {{-- End check for viewing any applications --}}


            {{-- Approvals Menu (Show only for users with approval permissions/roles) --}}
            {{-- Check if the user can view the approvals dashboard or any specific approval list --}}
            {{-- Assuming 'view-approvals-dashboard' permission or similar gate is used --}}
            @can('view-approvals-dashboard')
                {{-- Example permission, adjust based on your setup --}}
                {{-- Apply 'active open' classes if any route under 'approvals.*' is active --}}
                <li class="menu-item {{ request()->routeIs('approvals.*') ? 'active open' : '' }}">
                    {{-- This is a parent menu item for a submenu --}}
                    <a href="javascript:void(0);" class="menu-link menu-toggle">
                        <i class="menu-icon tf-icons ti ti-checks"></i> {{-- Example icon class --}}
                        <div>{{ __('Approvals') }}</div> {{-- Localized label --}}
                    </a>
                    {{-- Submenu for Approvals --}}
                    <ul class="menu-sub">
                        {{-- Link to Pending Approvals list --}}
                        {{-- Check if the user can view pending approvals (adjust permission/gate name) --}}
                        @can('view-pending-approvals')
                            {{-- Example permission --}}
                            {{-- Apply 'active' class if the current route matches the approvals index route --}}
                            <li class="menu-item {{ request()->routeIs('approvals.index') ? 'active' : '' }}">
                                {{-- Use the correct named route for the pending approvals index --}}
                                <a href="{{ route('approvals.index') }}" class="menu-link">
                                    <div>{{ __('Pending Approvals') }}</div> {{-- Localized label --}}
                                </a>
                            </li>
                        @endcan {{-- End can view pending approvals --}}
                        {{-- Link to Approval History --}}
                        {{-- Check if the user can view approval history (adjust permission/gate name) --}}
                        @can('view-approval-history')
                            {{-- Example permission -- شرق آسيا --}}
                            {{-- Apply 'active' class if the current route matches the approvals history route --}}
                            <li class="menu-item {{ request()->routeIs('approvals.history') ? 'active' : '' }}">
                                {{-- Use the correct named route for the approval history --}}
                                <a href="{{ route('approvals.history') }}" class="menu-link">
                                    <div>{{ __('Approval History') }}</div> {{-- Localized label --}}
                                </a>
                            </li>
                        @endcan {{-- End can view approval history --}}
                    </ul> {{-- End Approvals submenu --}}
                </li> {{-- End Approvals parent menu item --}}
            @endcan {{-- End can view approvals dashboard --}}

            {{-- Admin and BPM Staff Routes for Resource Management --}}
            {{-- Check if the user has Admin or BPM role for accessing the RM Administration section --}}
            @if (Auth::user()?->hasRole('Admin') || Auth::user()?->hasRole('BPM')) {{-- Example: Check roles --}}
                {{-- Apply 'active open' classes if any route under 'resource-management.admin.*' is active --}}
                <li class="menu-item {{ request()->routeIs('resource-management.admin.*') ? 'active open' : '' }}">
                    {{-- This is a parent menu item for a submenu --}}
                    <a href="javascript:void(0);" class="menu-link menu-toggle">
                        <i class="menu-icon tf-icons ti ti-tools"></i> {{-- Example icon --}}
                        <div>{{ __('RM Administration') }}</div> {{-- Localized label --}}
                    </a>
                    {{-- Submenu for RM Administration --}}
                    <ul class="menu-sub">
                        {{-- User Management (Typically Admin only) --}}
                        {{-- Check if user can view users (Policy) AND has the 'Admin' role (as per routes file) --}}
                        @can('viewAny', \App\Models\User::class)
                            @role('Admin')
                                {{-- Also check for Admin role explicitly --}}
                                {{-- Apply 'active' class if the current route matches any route under 'resource-management.admin.users.*' --}}
                                <li
                                    class="menu-item {{ request()->routeIs('resource-management.admin.users.*') ? 'active' : '' }}">
                                    {{-- Use the correct named route for the user index --}}
                                    <a href="{{ route('resource-management.admin.users.index') }}" class="menu-link">
                                        <div>{{ __('Manage Users') }}</div> {{-- Localized label --}}
                                    </a>
                                </li>
                            @endrole {{-- End role Admin check --}}
                        @endcan {{-- End can view any user --}}

                        {{-- Equipment Management (Typically Admin/BPM) --}}
                        {{-- Check if user can view equipment (Policy) --}}
                        @can('viewAny', \App\Models\Equipment::class)
                            {{-- Apply 'active' class if the current route matches any route under 'resource-management.admin.equipment.*' --}}
                            <li
                                class="menu-item {{ request()->routeIs('resource-management.admin.equipment.*') ? 'active' : '' }}">
                                {{-- Use the correct named route for the equipment index --}}
                                <a href="{{ route('resource-management.admin.equipment.index') }}" class="menu-link">
                                    <div>{{ __('Equipment Inventory') }}</div> {{-- Localized label --}}
                                </a>
                            </li>
                        @endcan {{-- End can view any equipment --}}

                        {{-- Organizational Data (Typically Admin only) --}}
                        {{-- Assuming a policy or permission like 'viewAny' for Grade model --}}
                        {{-- Check if user can view grades (Policy) AND has the 'Admin' role (as per routes file) --}}
                        @can('viewAny', \App\Models\Grade::class)
                            @role('Admin')
                                {{-- Also check for Admin role explicitly --}}
                                {{-- Apply 'active' class if the current route matches any route under 'resource-management.admin.grades.*' --}}
                                <li
                                    class="menu-item {{ request()->routeIs('resource-management.admin.grades.*') ? 'active' : '' }}">
                                    {{-- Use the correct named route for the grades index --}}
                                    <a href="{{ route('resource-management.admin.grades.index') }}" class="menu-link">
                                        <div>{{ __('Manage Grades') }}</div> {{-- Localized label --}}
                                    </a>
                                </li>
                            @endrole {{-- End role Admin check --}}
                        @endcan {{-- End can view any grade --}}


                        {{-- BPM Operations Links (Issuance/Return/Transactions) --}}
                        {{-- Check if the user has Admin or BPM role for accessing BPM operations --}}
                        @if (Auth::user()?->hasRole('Admin') || Auth::user()?->hasRole('BPM'))
                            {{-- Check roles --}}
                            {{-- Apply 'active open' classes if any route under 'resource-management.admin.bpm.*' is active --}}
                            <li
                                class="menu-item {{ request()->routeIs('resource-management.admin.bpm.*') ? 'active open' : '' }}">
                                {{-- This is a parent menu item for a submenu --}}
                                <a href="javascript:void(0);" class="menu-link menu-toggle">
                                    <div>{{ __('BPM Operations') }}</div> {{-- Localized label --}}
                                </a>
                                {{-- Submenu for BPM Operations --}}
                                <ul class="menu-sub">
                                    {{-- Link to a view listing outstanding loans for issuance --}}
                                    {{-- Check if user can issue loan equipment (adjust permission/gate name) --}}
                                    @can('issue-loan-equipment')
                                        {{-- Example permission --}}
                                        {{-- Apply 'active' class if the current route matches the outstanding loans route --}}
                                        <li
                                            class="menu-item {{ request()->routeIs('resource-management.admin.bpm.outstanding-loans') ? 'active' : '' }}">
                                            {{-- Use the correct named route for outstanding loans --}}
                                            <a href="{{ route('resource-management.admin.bpm.outstanding-loans') }}"
                                                class="menu-link">
                                                <div>{{ __('Outstanding Loans') }}</div> {{-- Localized label --}}
                                            </a>
                                        </li>
                                    @endcan {{-- End can issue loan equipment --}}
                                    {{-- Link to a view listing issued loans for return --}}
                                    {{-- Check if user can return loan equipment (adjust permission/gate name) --}}
                                    @can('return-loan-equipment')
                                        {{-- Example permission --}}
                                        {{-- Apply 'active' class if the current route matches the issued loans route --}}
                                        <li
                                            class="menu-item {{ request()->routeIs('resource-management.admin.bpm.issued-loans') ? 'active' : '' }}">
                                            {{-- Use the correct named route for issued loans --}}
                                            <a href="{{ route('resource-management.admin.bpm.issued-loans') }}"
                                                class="menu-link">
                                                <div>{{ __('Issued Equipment') }}</div> {{-- Localized label --}}
                                            </a>
                                        </li>
                                    @endcan {{-- End can return loan equipment --}}
                                    {{-- Link to view all transactions --}}
                                    {{-- Check if user can view any loan transaction (Policy) --}}
                                    @can('viewAny', \App\Models\LoanTransaction::class)
                                        {{-- Example Policy check --}}
                                        {{-- Apply 'active' class if the current route matches any route under 'resource-management.admin.bpm.transactions.*' --}}
                                        {{-- Assuming a list route for transactions exists --}}
                                        <li
                                            class="menu-item {{ request()->routeIs('resource-management.admin.bpm.transactions.*') ? 'active' : '' }}">
                                            {{-- Use the correct named route for the transactions index, if it exists --}}
                                            {{-- For now, linking to the outstanding loans list as a placeholder if index route is unknown --}}
                                            <a href="{{ route('resource-management.admin.bpm.transactions.index') }}"
                                                class="menu-link"> {{-- Adjust this route as needed (e.g., ->index) --}}
                                                <div>{{ __('All Transactions') }}</div> {{-- Localized label --}}
                                            </a>
                                        </li>
                                    @endcan {{-- End can view any loan transaction --}}
                                </ul> {{-- End BPM Operations submenu --}}
                            </li> {{-- End BPM Operations parent menu item --}}
                        @endif {{-- End Admin/BPM role check for BPM Operations --}}


                        {{-- Reports Links --}}
                        {{-- Check if the user can view reports (adjust permission/gate name) --}}
                        @can('view-reports')
                            {{-- Example permission, adjust based on your setup --}}
                            {{-- Apply 'active open' classes if any route under 'resource-management.admin.reports.*' is active --}}
                            <li
                                class="menu-item {{ request()->routeIs('resource-management.admin.reports.*') ? 'active open' : '' }}">
                                {{-- This is a parent menu item for a submenu --}}
                                <a href="javascript:void(0);" class="menu-link menu-toggle">
                                    <div>{{ __('Reports') }}</div> {{-- Localized label --}}
                                </a>
                                {{-- Submenu for Reports --}}
                                <ul class="menu-sub">
                                    {{-- Link to Equipment Report --}}
                                    {{-- Check if user can view equipment report (adjust permission/gate name) --}}
                                    @can('view-equipment-report')
                                        {{-- Example permission --}}
                                        {{-- Apply 'active' class if the current route matches the equipment report route --}}
                                        <li
                                            class="menu-item {{ request()->routeIs('resource-management.admin.reports.equipment') ? 'active' : '' }}">
                                            {{-- Use the correct named route for the equipment report --}}
                                            <a href="{{ route('resource-management.admin.reports.equipment') }}"
                                                class="menu-link">
                                                <div>{{ __('Equipment Inventory Report') }}</div> {{-- Localized label --}}
                                            </a>
                                        </li>
                                    @endcan {{-- End can view equipment report --}}
                                    {{-- Link to Email Accounts Report --}}
                                    {{-- Check if user can view email report (adjust permission/gate name) --}}
                                    @can('view-email-accounts-report')
                                        {{-- Example permission --}}
                                        {{-- Apply 'active' class if the current route matches the email accounts report route --}}
                                        <li
                                            class="menu-item {{ request()->routeIs('resource-management.admin.reports.email-accounts') ? 'active' : '' }}">
                                            {{-- Use the correct named route for the email accounts report --}}
                                            <a href="{{ route('resource-management.admin.reports.email-accounts') }}"
                                                class="menu-link">
                                                <div>{{ __('Email Accounts Report') }}</div> {{-- Localized label --}}
                                            </a>
                                        </li>
                                    @endcan {{-- End can view email accounts report --}}
                                    {{-- Link to Loan Applications Report --}}
                                    {{-- Check if user can view loan report (adjust permission/gate name) --}}
                                    @can('view-loan-applications-report')
                                        {{-- Example permission --}}
                                        {{-- Apply 'active' class if the current route matches the loan applications report route --}}
                                        <li
                                            class="menu-item {{ request()->routeIs('resource-management.admin.reports.loan-applications') ? 'active' : '' }}">
                                            {{-- Use the correct named route for the loan applications report --}}
                                            <a href="{{ route('resource-management.admin.reports.loan-applications') }}"
                                                class="menu-link">
                                                <div>{{ __('Loan Applications Report') }}</div> {{-- Localized label --}}
                                            </a>
                                        </li>
                                    @endcan {{-- End can view loan applications report --}}
                                    {{-- Link to User Activity Report --}}
                                    {{-- Check if user can view activity report (adjust permission/gate name) --}}
                                    @can('view-user-activity-report')
                                        {{-- Example permission --}}
                                        {{-- Apply 'active' class if the current route matches the user activity report route --}}
                                        <li
                                            class="menu-item {{ request()->routeIs('resource-management.admin.reports.user-activity') ? 'active' : '' }}">
                                            {{-- Use the correct named route for the user activity report --}}
                                            <a href="{{ route('resource-management.admin.reports.user-activity') }}"
                                                class="menu-link">
                                                <div>{{ __('User Activity Report') }}</div> {{-- Localized label --}}
                                            </a>
                                        </li>
                                    @endcan {{-- End can view user activity report --}}
                                </ul> {{-- End Reports submenu --}}
                            </li> {{-- End Reports parent menu item --}}
                        @endcan {{-- End can view reports --}}


                        {{-- Add other admin/management links here (e.g., for managing Departments, Positions specific to RM if needed) --}}
                        {{-- Example: Link to Admin User Management (if not already covered) --}}
                        {{-- If you need a link specifically for the AdminUserController from routes/web.php --}}
                        {{-- @can('viewAny', \App\Models\User::class) --}}
                        {{--    <li class="menu-item {{ request()->routeIs('resource-management.admin.users.index') ? 'active' : '' }}"> --}}
                        {{--        <a href="{{ route('resource-management.admin.users.index') }}" class="menu-link"> --}}
                        {{--            <div>{{ __('Admin Users') }}</div> --}}
                        {{--        </a> --}}
                        {{--    </li> --}}
                        {{-- @endcan --}}

                    </ul> {{-- End RM Administration submenu --}}
                </li> {{-- End RM Administration parent menu item --}}
            @endif {{-- End Admin/BPM role check for RM Administration parent menu --}}
            {{-- @endcan --}}{{-- End general RM admin permission check if you use one --}}

        @endif {{-- End general RM access check for the entire section --}}
        {{-- ☝️ End New MOTAC Integrated Resource Management Menu Items ☝️ --}}


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
