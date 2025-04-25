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

                if ($currentRouteName === $menu->slug) {
                    $activeClass = 'active';
                } elseif (isset($menu->submenu)) {
                    if (gettype($menu->slug) === 'array') {
                        foreach ($menu->slug as $slug) {
                            if (str_contains($currentRouteName, $slug) and strpos($currentRouteName, $slug) === 0) {
                                $activeClass = 'active open';
                            }
                        }
                    } else {
                        if (
                            str_contains($currentRouteName, $menu->slug) and
                            strpos($currentRouteName, $menu->slug) === 0
                        ) {
                            $activeClass = 'active open';
                        }
                    }
                }
            @endphp

            {{-- main menu --}}
            <li class="menu-item {{ $activeClass }}">
                <a href="{{ isset($menu->url) ? url($menu->url) : 'javascript:void(0);' }}"
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

        <li class="menu-header small text-uppercase">
            <span class="menu-header-text">RESOURCE MANAGEMENT</span> {{-- Header for the new section --}}
        </li>

        {{-- Link to Email/User ID Request Form --}}
        <li class="menu-item {{ request()->routeIs('request-email') ? 'active' : '' }}">
            <a href="{{ route('request-email') }}" class="menu-link">
                <i class="menu-icon tf-icons ti ti-mail"></i> {{-- Example icon class --}}
                <div>Email/User ID Request</div>
            </a>
        </li>

        {{-- Link to ICT Equipment Loan Form --}}
        <li class="menu-item {{ request()->routeIs('request-loan') ? 'active' : '' }}">
            <a href="{{ route('request-loan') }}" class="menu-link">
                <i class="menu-icon tf-icons ti ti-laptop"></i> {{-- Example icon class --}}
                <div>ICT Equipment Loan</div>
            </a>
        </li>

        {{-- Link to Approvals Dashboard (Show only for users with approval grade) --}}
        {{-- Use @can or @if (Auth::user()->hasApprovalGrade()) based on your authorization setup --}}
        @if (Auth::user()->hasApprovalGrade())
            {{-- Check using the helper method on User model --}}
            {{-- @can('access-approvals-dashboard') --}} {{-- Or use a Spatie permission/gate --}}
            <li class="menu-item {{ request()->routeIs('approvals') ? 'active' : '' }}">
                <a href="{{ route('approvals') }}" class="menu-link">
                    <i class="menu-icon tf-icons ti ti-checks"></i> {{-- Example icon class --}}
                    <div>Approvals</div>
                </a>
            </li>
            {{-- @endcan --}}
        @endif

        {{-- Admin and BPM Specific Resource Management Links --}}
        {{-- Wrap these in checks for Admin role or specific permissions --}}
        @can('manage-resource-management') {{-- Example permission for admin section access --}}
            {{-- @role('Admin') --}} {{-- Or check for Admin role --}}
            <li
                class="menu-item has-submenu {{ request()->routeIs('admin.equipment.*') || request()->routeIs('admin.users.*') || request()->routeIs('admin.bpm.*') || request()->routeIs('admin.grades.*') ? 'active open' : '' }}">
                {{-- Added admin.grades.* --}}
                <a href="javascript:void(0);" class="menu-link menu-toggle">
                    <i class="menu-icon tf-icons ti ti-settings"></i> {{-- Example icon class --}}
                    <div>RM Admin</div>
                </a>
                <ul class="menu-submenu">
                    {{-- Equipment Management Links --}}
                    @can('manage-equipment')
                        {{-- Permission for equipment management --}}
                        <li class="menu-item {{ request()->routeIs('admin.equipment.index') ? 'active' : '' }}">
                            <a href="{{ route('admin.equipment.index') }}" class="menu-link">
                                <div>List Equipment</div>
                            </a>
                        </li>
                        <li class="menu-item {{ request()->routeIs('admin.equipment.create') ? 'active' : '' }}">
                            <a href="{{ route('admin.equipment.create') }}" class="menu-link">
                                <div>Add Equipment</div>
                            </a>
                        </li>
                    @endcan

                    {{-- User Management Link (if managed separately in Admin) --}}
                    @can('manage-users')
                        {{-- Permission for user management --}}
                        <li class="menu-item {{ request()->routeIs('admin.users.index') ? 'active' : '' }}">
                            <a href="{{ route('admin.users.index') }}" class="menu-link">
                                <div>Manage Users</div>
                            </a>
                        </li>
                    @endcan

                    {{-- Grades Management Link --}}
                    @can('manage-grades')
                        {{-- Permission for grades management --}}
                        <li class="menu-item {{ request()->routeIs('admin.grades.index') ? 'active' : '' }}">
                            <a href="{{ route('admin.grades.index') }}" class="menu-link">
                                <div>Manage Grades</div>
                            </a>
                        </li>
                    @endcan

                    {{-- BPM Issuance/Return Links (if separate from equipment listing) --}}
                    @can('access-bpm-interface')
                        {{-- Permission for BPM interface access --}}
                        <li class="menu-item has-submenu {{ request()->routeIs('admin.bpm.*') ? 'active open' : '' }}">
                            <a href="javascript:void(0);" class="menu-link menu-toggle">
                                <div>BPM Operations</div>
                            </a>
                            <ul class="menu-submenu">
                                {{-- Link to a view listing outstanding loans for issuance --}}
                                {{-- <li class="menu-item {{ request()->routeIs('admin.bpm.outstanding-loans') ? 'active' : '' }}">
                  <a href="{{ route('admin.bpm.outstanding-loans') }}" class="menu-link">
                    <div>Outstanding Loans</div>
                  </a>
                </li> --}}
                                {{-- You might add links to views/components specifically for Issue/Return actions if needed --}}
                            </ul>
                        </li>
                    @endcan

                    {{-- Add other admin/management links here (e.g., for Reports) --}}
                    {{-- Reports Links --}}
                    @can('view-reports')
                        {{-- Permission for viewing reports --}}
                        <li class="menu-item has-submenu {{ request()->routeIs('admin.reports.*') ? 'active open' : '' }}">
                            <a href="javascript:void(0);" class="menu-link menu-toggle">
                                <div>Reports</div>
                            </a>
                            <ul class="menu-submenu">
                                <li class="menu-item {{ request()->routeIs('admin.reports.equipment') ? 'active' : '' }}">
                                    <a href="{{ route('admin.reports.equipment') }}" class="menu-link">
                                        <div>Equipment Report</div>
                                    </a>
                                </li>
                                <li class="menu-item {{ request()->routeIs('admin.reports.email-accounts') ? 'active' : '' }}">
                                    <a href="{{ route('admin.reports.email-accounts') }}" class="menu-link">
                                        <div>Email Accounts Report</div>
                                    </a>
                                </li>
                                <li class="menu-item {{ request()->routeIs('admin.reports.user-activity') ? 'active' : '' }}">
                                    <a href="{{ route('admin.reports.user-activity') }}" class="menu-link">
                                        <div>User Activity Report</div>
                                    </a>
                                </li>
                            </ul>
                        </li>
                    @endcan


                </ul>
            </li>
        @endcan
        {{-- @endrole --}}
        {{-- ☝️ End New MOTAC Integrated Resource Management Menu Items ☝️ --}}


    </ul>

</aside>
