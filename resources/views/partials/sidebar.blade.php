{{--
    resources/views/partials/sidebar.blade.php

    This file contains the HTML structure for the application's sidebar menu.
    It includes navigation links for both existing HRMS features and the new
    MOTAC Integrated Resource Management System features.
--}}

<aside class="main-sidebar sidebar-dark-primary elevation-4">
    {{-- Brand Logo - You might need to adjust the href and img src --}}
    <a href="{{ url('/') }}" class="brand-link">
        {{-- Replace with your logo --}}
        <img src="{{ asset('path/to/your/logo.png') }}" alt="MOTAC Logo" class="brand-image img-circle elevation-3"
            style="opacity: .8">
        <span class="brand-text font-weight-light">MOTAC System</span> {{-- Adjust brand name --}}
    </a>

    {{-- Sidebar --}}
    <div class="sidebar">
        {{-- Sidebar user panel (optional) --}}
        <div class="user-panel mt-3 pb-3 mb-3 d-flex">
            <div class="image">
                {{-- User profile image --}}
                <img src="{{ Auth::user()->profile_photo_url ?? asset('path/to/default/profile.png') }}"
                    class="img-circle elevation-2" alt="User Image">
            </div>
            <div class="info">
                {{-- User Name --}}
                <a href="{{-- Link to user profile --}}" class="d-block">{{ Auth::user()->full_name ?? Auth::user()->name }}</a>
                {{-- User Role/Grade (Optional) --}}
                <span
                    class="d-block text-muted text-sm">{{ Auth::user()->grade->name ?? (Auth::user()->position->name ?? '') }}</span>
            </div>
        </div>

        {{-- Sidebar Menu --}}
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu"
                data-accordion="false">
                {{-- Add icons with the class .nav-icon to the links using the Font Awesome or other icon fonts library --}}

                {{-- Existing HRMS Menu Items (Placeholders) --}}
                {{-- You will need to integrate your actual existing menu items here --}}
                {{-- Example Dashboard link --}}
                <li class="nav-item">
                    <a href="{{ route('dashboard') }}"
                        class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-tachometer-alt"></i> {{-- Example icon --}}
                        <p>Dashboard</p>
                    </a>
                </li>

                {{-- Example HR Menu Item (using a treeview) --}}
                {{-- <li class="nav-item has-treeview {{ request()->routeIs('hr.*') ? 'menu-open' : '' }}">
                  <a href="#" class="nav-link {{ request()->routeIs('hr.*') ? 'active' : '' }}">
                      <i class="nav-icon fas fa-users"></i>
                      <p>
                          Human Resource
                          <i class="right fas fa-angle-left"></i>
                      </p>
                  </a>
                  <ul class="nav nav-treeview">
                      <li class="nav-item">
                          <a href="{{ route('structure-employees') }}" class="nav-link {{ request()->routeIs('structure-employees') ? 'active' : '' }}">
                              <i class="far fa-circle nav-icon"></i>
                              <p>Employees</p>
                          </a>
                      </li>
                      {{-- Add other HR sub-menu items here --}}
                {{-- </ul>
              </li> --}}

                {{-- Example Settings Menu Item --}}
                {{-- <li class="nav-item">
                  <a href="{{ route('settings-users') }}" class="nav-link {{ request()->routeIs('settings-users') ? 'active' : '' }}">
                      <i class="nav-icon fas fa-cogs"></i>
                      <p>Settings</p>
                  </a>
              </li> --}}

                {{-- Example Assets Menu Item --}}
                {{-- <li class="nav-item">
                  <a href="{{ route('inventory') }}" class="nav-link {{ request()->routeIs('inventory') ? 'active' : '' }}">
                      <i class="nav-icon fas fa-boxes"></i>
                      <p>Assets</p>
                  </a>
              </li> --}}
                {{-- End Existing HRMS Menu Items --}}


                {{-- 👇 New MOTAC Integrated Resource Management Menu Items 👇 --}}
                <li class="nav-header">RESOURCE MANAGEMENT</li> {{-- Header for the new section --}}

                {{-- Link to Email/User ID Request Form --}}
                <li class="nav-item">
                    <a href="{{ route('request-email') }}"
                        class="nav-link {{ request()->routeIs('request-email') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-envelope"></i> {{-- Example icon --}}
                        <p>Email/User ID Request</p>
                    </a>
                </li>

                {{-- Link to ICT Equipment Loan Form --}}
                <li class="nav-item">
                    <a href="{{ route('request-loan') }}"
                        class="nav-link {{ request()->routeIs('request-loan') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-laptop"></i> {{-- Example icon --}}
                        <p>ICT Equipment Loan</p>
                    </a>
                </li>

                {{-- Link to Approvals Dashboard (Show only for users with approval grade) --}}
                {{-- Use @can or @if (Auth::user()->hasApprovalGrade()) based on your authorization setup --}}
                @if (Auth::user()->hasApprovalGrade())
                    {{-- Check using the helper method on User model --}}
                    {{-- @can('access-approvals-dashboard') --}} {{-- Or use a Spatie permission/gate --}}
                    <li class="nav-item">
                        <a href="{{ route('approvals') }}"
                            class="nav-link {{ request()->routeIs('approvals') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-check-circle"></i> {{-- Example icon --}}
                            <p>Approvals</p>
                        </a>
                    </li>
                    {{-- @endcan --}}
                @endif

                {{-- Admin and BPM Specific Resource Management Links --}}
                {{-- Wrap these in checks for Admin role or specific permissions --}}
                @can('manage-resource-management') {{-- Example permission for admin section access --}}
                    {{-- @role('Admin') --}} {{-- Or check for Admin role --}}
                    <li
                        class="nav-item has-treeview {{ request()->routeIs('admin.equipment.*') || request()->routeIs('admin.users.*') || request()->routeIs('admin.bpm.*') ? 'menu-open' : '' }}">
                        <a href="#"
                            class="nav-link {{ request()->routeIs('admin.equipment.*') || request()->routeIs('admin.users.*') || request()->routeIs('admin.bpm.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-cogs"></i> {{-- Example icon --}}
                            <p>
                                RM Admin
                                <i class="right fas fa-angle-left"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            {{-- Equipment Management Links --}}
                            @can('manage-equipment')
                                {{-- Permission for equipment management --}}
                                <li class="nav-item">
                                    <a href="{{ route('admin.equipment.index') }}"
                                        class="nav-link {{ request()->routeIs('admin.equipment.index') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>List Equipment</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('admin.equipment.create') }}"
                                        class="nav-link {{ request()->routeIs('admin.equipment.create') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Add Equipment</p>
                                    </a>
                                </li>
                            @endcan

                            {{-- User Management Link (if managed separately in Admin) --}}
                            @can('manage-users')
                                {{-- Permission for user management --}}
                                <li class="nav-item">
                                    <a href="{{ route('admin.users.index') }}"
                                        class="nav-link {{ request()->routeIs('admin.users.index') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Manage Users</p>
                                    </a>
                                </li>
                            @endcan

                            {{-- BPM Issuance/Return Links (if separate from equipment listing) --}}
                            @can('access-bpm-interface')
                                {{-- Permission for BPM interface access --}}
                                <li class="nav-item has-treeview {{ request()->routeIs('admin.bpm.*') ? 'menu-open' : '' }}">
                                    <a href="#" class="nav-link {{ request()->routeIs('admin.bpm.*') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>
                                            BPM Operations
                                            <i class="right fas fa-angle-left"></i>
                                        </p>
                                    </a>
                                    <ul class="nav nav-treeview">
                                        {{-- Link to a view listing outstanding loans for issuance --}}
                                        {{-- <li class="nav-item">
                                          <a href="{{ route('admin.bpm.outstanding-loans') }}" class="nav-link {{ request()->routeIs('admin.bpm.outstanding-loans') ? 'active' : '' }}">
                                              <i class="far fa-dot-circle nav-icon"></i>
                                              <p>Outstanding Loans</p>
                                          </a>
                                      </li> --}}
                                        {{-- You might add links to views/components specifically for Issue/Return actions if needed --}}
                                    </ul>
                                </li>
                            @endcan

                            {{-- Add other admin/management links here (e.g., for Grades, Reports) --}}

                        </ul>
                    </li>
                @endcan
                {{-- @endrole --}}
                {{-- ☝️ End New MOTAC Integrated Resource Management Menu Items ☝️ --}}


            </ul>
        </nav>
        {{-- /.sidebar-menu --}}
    </div>
    {{-- /.sidebar --}}
</aside>
