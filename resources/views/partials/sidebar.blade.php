{{--
    resources/views/partials/sidebar.blade.php

    This file contains the HTML structure for the application's sidebar menu.
    It includes navigation links for both existing HRMS features and the new
    MOTAC Integrated Resource Management System features.
--}}

{{-- Assuming you are using AdminLTE or a similar Bootstrap-based theme --}}
<aside class="main-sidebar sidebar-dark-primary elevation-4">
    {{-- Brand Logo - You might need to adjust the href and img src --}}
    <a href="{{ url('/') }}" class="brand-link">
        {{-- Replace with your logo --}}
        {{-- Ensure you have a default logo path if profile_photo_path is null --}}
        <img src="{{ asset('path/to/your/logo.png') }}" alt="MOTAC Logo" class="brand-image img-circle elevation-3"
            style="opacity: .8">
        <span class="brand-text font-weight-light">Sistem MOTAC</span> {{-- Adjust brand name to Malay --}}
    </a>

    {{-- Sidebar --}}
    <div class="sidebar">
        {{-- Sidebar user panel (optional) --}}
        <div class="user-panel mt-3 pb-3 mb-3 d-flex">
            <div class="image">
                {{-- User profile image --}}
                {{-- Use Auth::user()->profile_photo_url if using Jetstream or similar, otherwise adjust path --}}
                <img src="{{ Auth::user()->profile_photo_url ?? asset('path/to/default/profile.png') }}"
                    class="img-circle elevation-2" alt="User Image">
            </div>
            <div class="info">
                {{-- User Name --}}
                {{-- Link to user profile - replace '#' with your actual route --}}
                {{-- Use Auth::user()->name or Auth::user()->full_name depending on your User model --}}
                <a href="#" class="d-block">{{ Auth::user()->name ?? 'Pengguna' }}</a> {{-- Assuming 'name' or 'full_name' --}}
                {{-- User Role/Grade (Optional) --}}
                {{-- Assuming relationships 'grade' and 'position' exist on the User model or related Employee model --}}
                <span
                    class="d-block text-muted text-sm">{{ Auth::user()->grade->name ?? (Auth::user()->position->name ?? '') }}</span>
                {{-- Display Grade or Position --}}
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
                    {{-- Ensure 'dashboard' route exists --}}
                    <a href="{{ route('dashboard') }}"
                        class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-tachometer-alt"></i> {{-- Example icon --}}
                        <p>Dashboard</p>
                    </a>
                </li>

                {{-- Example HR Menu Item (using a treeview) --}}
                {{-- Uncomment and adjust routes/permissions as needed for your existing HR module --}}
                {{-- <li class="nav-item has-treeview {{ request()->routeIs('hr.*') ? 'menu-open' : '' }}">
                 <a href="#" class="nav-link {{ request()->routeIs('hr.*') ? 'active' : '' }}">
                     <i class="nav-icon fas fa-users"></i>
                     <p>
                         Sumber Manusia
                         <i class="right fas fa-angle-left"></i>
                     </p>
                 </a>
                 <ul class="nav nav-treeview">
                     <li class="nav-item">
                         <a href="{{ route('hr.employees.index') }}" class="nav-link {{ request()->routeIs('hr.employees.*') ? 'active' : '' }}">
                             <i class="far fa-circle nav-icon"></i>
                             <p>Senarai Pekerja</p>
                         </a>
                     </li>
                     --}}{{-- Add other HR sub-menu items here --}}{{--
                 </ul>
              </li> --}}

                {{-- Example Settings Menu Item --}}
                {{-- Uncomment and adjust routes/permissions as needed --}}
                {{-- <li class="nav-item">
                 <a href="{{ route('settings.index') }}" class="nav-link {{ request()->routeIs('settings.*') ? 'active' : '' }}">
                     <i class="nav-icon fas fa-cogs"></i>
                     <p>Tetapan Sistem</p>
                 </a>
              </li> --}}

                {{-- Example Assets Menu Item (if not part of the new RM module) --}}
                {{-- Uncomment and adjust routes/permissions as needed --}}
                {{-- <li class="nav-item">
                 <a href="{{ route('inventory.index') }}" class="nav-link {{ request()->routeIs('inventory.*') ? 'active' : '' }}">
                     <i class="nav-icon fas fa-boxes"></i>
                     <p>Inventori</p>
                 </a>
              </li> --}}
                {{-- End Existing HRMS Menu Items --}}


                {{-- 👇 New MOTAC Integrated Resource Management Menu Items 👇 --}}
                {{-- Check if the user has access to any RM features before showing the header --}}
                {{-- Assuming 'access-resource-management' is a permission that broadly covers access to the RM module --}}
                @can('access-resource-management')
                    <li class="nav-header">PENGURUSAN SUMBER ICT</li> {{-- Header for the new section (Malay) --}}

                    {{-- Link to Email/User ID Request Form --}}
                    {{-- Check if the user can create an email application --}}
                    @can('create', \App\Models\EmailApplication::class)
                        <li class="nav-item">
                            {{-- Assuming 'email-applications.create' route exists --}}
                            <a href="{{ route('email-applications.create') }}"
                                class="nav-link {{ request()->routeIs('email-applications.create') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-envelope"></i> {{-- Example icon --}}
                                <p>Permohonan Akaun Emel</p> {{-- Malay label --}}
                            </a>
                        </li>
                    @endcan

                    {{-- Link to ICT Equipment Loan Form --}}
                    {{-- Check if the user can create a loan application --}}
                    @can('create', \App\Models\LoanApplication::class)
                        <li class="nav-item">
                            {{-- Assuming 'loan-applications.create' route exists --}}
                            <a href="{{ route('loan-applications.create') }}"
                                class="nav-link {{ request()->routeIs('loan-applications.create') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-laptop"></i> {{-- Example icon --}}
                                <p>Permohonan Pinjaman Peralatan</p> {{-- Malay label --}}
                            </a>
                        </li>
                    @endcan

                    {{-- Link to My Applications (List of user's submitted applications) --}}
                    {{-- This link is for the applicant to see their own applications --}}
                    {{-- Assuming a route named 'my-applications.index' or similar, and sub-routes --}}
                    {{-- Check if the user can view their own applications (e.g., viewAny on EmailApplication or LoanApplication) --}}
                    {{-- A simpler check might be needed here if 'my-applications.*' routes don't have specific permissions --}}
                    @if (Auth::user()->can('viewAny', \App\Models\EmailApplication::class) ||
                            Auth::user()->can('viewAny', \App\Models\LoanApplication::class))
                        <li class="nav-item has-treeview {{ request()->routeIs('my-applications.*') ? 'menu-open' : '' }}">
                            <a href="#"
                                class="nav-link {{ request()->routeIs('my-applications.*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-file-alt"></i> {{-- Example icon --}}
                                <p>
                                    Permohonan Saya
                                    <i class="right fas fa-angle-left"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview">
                                {{-- Link to user's email applications list --}}
                                {{-- Assuming 'my-applications.email.index' route exists and user can viewAny EmailApplication --}}
                                @can('viewAny', \App\Models\EmailApplication::class)
                                    <li class="nav-item">
                                        <a href="{{ route('my-applications.email.index') }}"
                                            class="nav-link {{ request()->routeIs('my-applications.email.*') ? 'active' : '' }}">
                                            <i class="far fa-circle nav-icon"></i>
                                            <p>Akaun Emel ICT</p>
                                        </a>
                                    </li>
                                @endcan
                                {{-- Link to user's loan applications list --}}
                                {{-- Assuming 'my-applications.loan.index' route exists and user can viewAny LoanApplication --}}
                                @can('viewAny', \App\Models\LoanApplication::class)
                                    <li class="nav-item">
                                        <a href="{{ route('my-applications.loan.index') }}"
                                            class="nav-link {{ request()->routeIs('my-applications.loan.*') ? 'active' : '' }}">
                                            <i class="far fa-circle nav-icon"></i>
                                            <p>Pinjaman Peralatan ICT</p>
                                        </a>
                                    </li>
                                @endcan
                            </ul>
                        </li>
                    @endif


                    {{-- Approvals Menu (Show only for users with approval permissions/roles) --}}
                    {{-- Check if the user can view the approvals dashboard or any approval list --}}
                    {{-- Assuming 'view-approvals-dashboard' or similar permission --}}
                    @can('view-approvals-dashboard') {{-- Or a more specific permission like 'viewAny', \App\Models\Approval::class --}}
                        <li class="nav-item has-treeview {{ request()->routeIs('approvals.*') ? 'menu-open' : '' }}">
                            <a href="#" class="nav-link {{ request()->routeIs('approvals.*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-check-circle"></i> {{-- Example icon --}}
                                <p>
                                    Kelulusan
                                    <i class="right fas fa-angle-left"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview">
                                {{-- Link to Pending Approvals list --}}
                                {{-- Assuming 'approvals.index' route exists and user can view pending approvals --}}
                                @can('view-pending-approvals') {{-- Assuming a specific permission for viewing pending approvals --}}
                                    <li class="nav-item">
                                        <a href="{{ route('approvals.index') }}"
                                            class="nav-link {{ request()->routeIs('approvals.index') ? 'active' : '' }}">
                                            <i class="far fa-circle nav-icon"></i>
                                            <p>Kelulusan Tertunda</p> {{-- Malay label --}}
                                        </a>
                                    </li>
                                @endcan
                                {{-- Link to Approval History --}}
                                {{-- Assuming 'approvals.history' route exists and user can view approval history --}}
                                @can('view-approval-history') {{-- Assuming a specific permission for viewing history --}}
                                    <li class="nav-item">
                                        <a href="{{ route('approvals.history') }}"
                                            class="nav-link {{ request()->routeIs('approvals.history') ? 'active' : '' }}">
                                            <i class="far fa-circle nav-icon"></i>
                                            <p>Sejarah Kelulusan</p> {{-- Malay label --}}
                                        </a>
                                    </li>
                                @endcan
                            </ul>
                        </li>
                    @endcan

                    {{-- Admin and BPM Specific Resource Management Links --}}
                    {{-- Wrap these in checks for Admin role or specific permissions --}}
                    {{-- Assuming 'access-rm-admin' is a permission for general RM Admin access --}}
                    @can('access-rm-admin')
                        <li
                            class="nav-item has-treeview {{ request()->routeIs('equipment.*') || request()->routeIs('loan-transactions.*') || request()->routeIs('reports.*') ? 'menu-open' : '' }}">
                            {{-- Use route names from the views we created --}}
                            <a href="#"
                                class="nav-link {{ request()->routeIs('equipment.*') || request()->routeIs('loan-transactions.*') || request()->routeIs('reports.*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-tools"></i> {{-- Example icon --}}
                                <p>
                                    Pentadbiran RM
                                    <i class="right fas fa-angle-left"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview">
                                {{-- Equipment Management Links --}}
                                {{-- Check if the user can view equipment list --}}
                                @can('view-equipment') {{-- Assuming a permission for viewing equipment --}}
                                    <li class="nav-item">
                                        {{-- Assuming 'equipment.index' route exists --}}
                                        <a href="{{ route('equipment.index') }}"
                                            class="nav-link {{ request()->routeIs('equipment.*') ? 'active' : '' }}">
                                            {{-- Match equipment.* routes --}}
                                            <i class="far fa-circle nav-icon"></i>
                                            <p>Inventori Peralatan</p> {{-- Malay label --}}
                                        </a>
                                    </li>
                                @endcan

                                {{-- BPM Operations Links (Issuance/Return) --}}
                                {{-- Check if the user can access BPM operations --}}
                                @can('access-bpm-operations') {{-- Assuming a permission for BPM operations --}}
                                    <li
                                        class="nav-item has-treeview {{ request()->routeIs('loan-transactions.*') ? 'menu-open' : '' }}">
                                        {{-- Match loan-transactions.* routes --}}
                                        <a href="#"
                                            class="nav-link {{ request()->routeIs('loan-transactions.*') ? 'active' : '' }}">
                                            <i class="far fa-circle nav-icon"></i>
                                            <p>
                                                Operasi BPM
                                                <i class="right fas fa-angle-left"></i>
                                            </p>
                                        </a>
                                        <ul class="nav nav-treeview">
                                            {{-- Link to a view listing outstanding loans for issuance --}}
                                            {{-- Assuming 'loan-transactions.outstanding-loans' route exists and user can issue loans --}}
                                            @can('issue-loan-equipment') {{-- Assuming a permission for issuing loans --}}
                                                <li class="nav-item">
                                                    {{-- This view would list applications ready for issuance --}}
                                                    <a href="{{ route('loan-transactions.outstanding-loans') }}"
                                                        class="nav-link {{ request()->routeIs('loan-transactions.outstanding-loans') ? 'active' : '' }}">
                                                        <i class="far fa-dot-circle nav-icon"></i>
                                                        <p>Pinjaman Menunggu Pengeluaran</p> {{-- Malay label --}}
                                                    </a>
                                                </li>
                                            @endcan
                                            {{-- Link to a view listing issued loans for return --}}
                                            {{-- Assuming 'loan-transactions.issued-loans' route exists and user can return loans --}}
                                            @can('return-loan-equipment') {{-- Assuming a permission for returning loans --}}
                                                <li class="nav-item">
                                                    {{-- This view would list transactions ready for return --}}
                                                    <a href="{{ route('loan-transactions.issued-loans') }}"
                                                        class="nav-link {{ request()->routeIs('loan-transactions.issued-loans') ? 'active' : '' }}">
                                                        <i class="far fa-dot-circle nav-icon"></i>
                                                        <p>Peralatan Sedang Dipinjam</p> {{-- Malay label --}}
                                                    </a>
                                                </li>
                                            @endcan
                                        </ul>
                                    </li>
                                @endcan

                                {{-- Reports Links --}}
                                {{-- Check if the user can view reports --}}
                                @can('view-reports') {{-- Assuming a permission for viewing reports --}}
                                    <li class="nav-item has-treeview {{ request()->routeIs('reports.*') ? 'menu-open' : '' }}">
                                        {{-- Match reports.* routes --}}
                                        <a href="#" class="nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}">
                                            <i class="far fa-circle nav-icon"></i>
                                            <p>
                                                Laporan
                                                <i class="right fas fa-angle-left"></i>
                                            </p>
                                        </a>
                                        <ul class="nav nav-treeview">
                                            {{-- Link to Equipment Report --}}
                                            {{-- Assuming 'reports.equipment' route exists and user can view equipment report --}}
                                            @can('view-equipment-report') {{-- Specific permission for equipment report --}}
                                                <li class="nav-item">
                                                    <a href="{{ route('reports.equipment') }}"
                                                        class="nav-link {{ request()->routeIs('reports.equipment') ? 'active' : '' }}">
                                                        <i class="far fa-dot-circle nav-icon"></i>
                                                        <p>Laporan Inventori Peralatan</p> {{-- Malay label --}}
                                                    </a>
                                                </li>
                                            @endcan
                                            {{-- Link to Email Accounts Report --}}
                                            {{-- Assuming 'reports.email-accounts' route exists and user can view email report --}}
                                            @can('view-email-accounts-report') {{-- Specific permission for email report --}}
                                                <li class="nav-item">
                                                    <a href="{{ route('reports.email-accounts') }}"
                                                        class="nav-link {{ request()->routeIs('reports.email-accounts') ? 'active' : '' }}">
                                                        <i class="far fa-dot-circle nav-icon"></i>
                                                        <p>Laporan Akaun Emel</p> {{-- Malay label --}}
                                                    </a>
                                                </li>
                                            @endcan
                                            {{-- Link to Loan Applications Report --}}
                                            {{-- Assuming 'reports.loan-applications' route exists and user can view loan report --}}
                                            @can('view-loan-applications-report') {{-- Specific permission for loan report --}}
                                                <li class="nav-item">
                                                    <a href="{{ route('reports.loan-applications') }}"
                                                        class="nav-link {{ request()->routeIs('reports.loan-applications') ? 'active' : '' }}">
                                                        <i class="far fa-dot-circle nav-icon"></i>
                                                        <p>Laporan Pinjaman Peralatan</p> {{-- Malay label --}}
                                                    </a>
                                                </li>
                                            @endcan
                                            {{-- Link to User Activity Report --}}
                                            {{-- Assuming 'reports.user-activity' route exists and user can view activity report --}}
                                            @can('view-user-activity-report') {{-- Specific permission for activity report --}}
                                                <li class="nav-item">
                                                    <a href="{{ route('reports.user-activity') }}"
                                                        class="nav-link {{ request()->routeIs('reports.user-activity') ? 'active' : '' }}">
                                                        <i class="far fa-dot-circle nav-icon"></i>
                                                        <p>Laporan Aktiviti Pengguna</p> {{-- Malay label --}}
                                                    </a>
                                                </li>
                                            @endcan
                                        </ul>
                                    </li>
                                @endcan

                                {{-- Add other admin/management links here (e.g., for Grades, etc.) --}}

                            </ul>
                        </li>
                    @endcan

                    @endif {{-- End general RM access check --}}
                    {{-- ☝️ End New MOTAC Integrated Resource Management Menu Items ☝️ --}}


                    {{-- Logout Link --}}
                    <li class="nav-item">
                        {{-- Ensure 'logout' route exists --}}
                        <a href="#" class="nav-link"
                            onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            <i class="nav-icon fas fa-sign-out-alt"></i> {{-- Example icon --}}
                            <p>Log Keluar</p> {{-- Malay label --}}
                        </a>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                            @csrf
                        </form>
                    </li>

                </ul>
            </nav>
            {{-- /.sidebar-menu --}}
        </div>
        {{-- /.sidebar --}}
    </aside>
