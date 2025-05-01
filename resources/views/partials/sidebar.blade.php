{{--
    resources/views/partials/sidebar.blade.php

    This file contains the HTML structure for the application's sidebar menu.
    It includes navigation links for both existing HRMS features and the new
    MOTAC Integrated Resource Management System features.
    Assumes integration with a Bootstrap-based admin theme like AdminLTE.
--}}

{{-- Assuming you are using AdminLTE or a similar Bootstrap-based theme --}}
<aside class="main-sidebar sidebar-dark-primary elevation-4"> {{-- Main sidebar element with theme classes --}}
    {{-- Brand Logo - You will need to adjust the href and img src --}}
    <a href="{{ url('/') }}" class="brand-link"> {{-- Link to the application homepage --}}
        {{-- Replace with your actual logo path --}}
        {{-- Ensure you have a default logo path if profile_photo_path is null --}}
        <img src="{{ asset('path/to/your/logo.png') }}" alt="MOTAC Logo" class="brand-image img-circle elevation-3"
            style="opacity: .8"> {{-- Logo image with styling --}}
        <span class="brand-text font-weight-light">Sistem MOTAC</span> {{-- Adjust brand name to Malay or your preference --}}
    </a>

    {{-- Sidebar content wrapper --}}
    <div class="sidebar">
        {{-- Sidebar user panel (optional) --}}
        <div class="user-panel mt-3 pb-3 mb-3 d-flex"> {{-- User panel styling --}}
            <div class="image">
                {{-- User profile image --}}
                {{-- Use Auth::user()->profile_photo_url if using Jetstream or similar, otherwise adjust path --}}
                {{-- Provide a default profile image path as a fallback --}}
                <img src="{{ Auth::user()->profile_photo_url ?? asset('path/to/default/profile.png') }}"
                    class="img-circle elevation-2" alt="{{ Auth::user()->name ?? 'User' }} Image">
                {{-- User image with styling and alt text --}}
            </div>
            <div class="info">
                {{-- User Name --}}
                {{-- Link to user profile - replace '#' with your actual route to the user's profile --}}
                {{-- Use Auth::user()->name or Auth::user()->full_name depending on your User model attributes --}}
                <a href="#" class="d-block">{{ Auth::user()->name ?? 'Pengguna' }}</a> {{-- Display user name, provide fallback --}}
                {{-- User Role/Grade (Optional) --}}
                {{-- Assuming relationships 'grade' and 'position' exist on the User model or a related Employee model --}}
                {{-- Accessing grade or position name, with fallback to empty string --}}
                <span
                    class="d-block text-muted text-sm">{{ Auth::user()->grade->name ?? (Auth::user()->position->name ?? '') }}</span>
            </div>
        </div>

        {{-- Sidebar Menu --}}
        <nav class="mt-2">
            {{-- Menu list with treeview and accordion behavior --}}
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu"
                data-accordion="false">
                {{-- Add icons with the class .nav-icon to the links using the Font Awesome or other icon fonts library --}}

                {{-- Existing HRMS Menu Items (Placeholders) --}}
                {{-- You will need to integrate your actual existing menu items here, adjusting routes and permissions --}}
                {{-- Example Dashboard link --}}
                <li class="nav-item">
                    {{-- Ensure 'dashboard' route exists --}}
                    {{-- Add 'active' class if the current route name matches 'dashboard' --}}
                    <a href="{{ route('dashboard') }}"
                        class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-tachometer-alt"></i> {{-- Example icon (Font Awesome) --}}
                        <p>Dashboard</p>
                    </a>
                </li>

                {{-- Example HR Menu Item (using a treeview) --}}
                {{-- Uncomment and adjust routes/permissions as needed for your existing HR module --}}
                {{-- <li class="nav-item has-treeview {{ request()->routeIs('hr.*') ? 'menu-open' : '' }}"> // 'menu-open' class for expanded treeview if any sub-route is active
                 <a href="#" class="nav-link {{ request()->routeIs('hr.*') ? 'active' : '' }}">
                     <i class="nav-icon fas fa-users"></i> // Example icon
                     <p>
                         Sumber Manusia // Malay label
                         <i class="right fas fa-angle-left"></i> // Right arrow icon for treeview
                     </p>
                 </a>
                 <ul class="nav nav-treeview"> // Submenu list
                     <li class="nav-item">
                         <a href="{{ route('hr.employees.index') }}" class="nav-link {{ request()->routeIs('hr.employees.*') ? 'active' : '' }}"> // Add 'active' class if route matches
                             <i class="far fa-circle nav-icon"></i> // Submenu item icon
                             <p>Senarai Pekerja</p> // Malay label
                         </a>
                     </li>
                     //-- Add other HR sub-menu items here --//
                 </ul>
              </li> --}}

                {{-- Example Settings Menu Item --}}
                {{-- Uncomment and adjust routes/permissions as needed --}}
                {{-- <li class="nav-item">
                 <a href="{{ route('settings.index') }}" class="nav-link {{ request()->routeIs('settings.*') ? 'active' : '' }}">
                     <i class="nav-icon fas fa-cogs"></i> // Example icon
                     <p>Tetapan Sistem</p> // Malay label
                 </a>
              </li> --}}

                {{-- Example Assets Menu Item (if not part of the new RM module) --}}
                {{-- Uncomment and adjust routes/permissions as needed --}}
                {{-- <li class="nav-item">
                 <a href="{{ route('inventory.index') }}" class="nav-link {{ request()->routeIs('inventory.*') ? 'active' : '' }}">
                     <i class="nav-icon fas fa-boxes"></i> // Example icon
                     <p>Inventori</p> // Malay label
                 </a>
              </li> --}}
                {{-- End Existing HRMS Menu Items --}}


                {{-- 👇 New MOTAC Integrated Resource Management Menu Items 👇 --}}
                {{-- Check if the user has access to any RM features before showing the header --}}
                {{-- Assuming 'access-resource-management' is a permission that broadly covers access to the RM module --}}
                @can('access-resource-management') {{-- Check permission to access the RM module --}}
                    <li class="nav-header">PENGURUSAN SUMBER ICT</li> {{-- Header for the new section (Malay) --}}

                    {{-- Link to Email/User ID Request Form --}}
                    {{-- Check if the user can create an email application (Policy check) --}}
                    @can('create', \App\Models\EmailApplication::class)
                        <li class="nav-item">
                            {{-- Assuming 'email-applications.create' route exists --}}
                            {{-- Add 'active' class if the current route name matches 'email-applications.create' --}}
                            <a href="{{ route('email-applications.create') }}"
                                class="nav-link {{ request()->routeIs('email-applications.create') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-envelope"></i> {{-- Example icon (Font Awesome) --}}
                                <p>Permohonan Akaun Emel</p> {{-- Malay label --}}
                            </a>
                        </li>
                    @endcan

                    {{-- Link to ICT Equipment Loan Form --}}
                    {{-- Check if the user can create a loan application (Policy check) --}}
                    @can('create', \App\Models\LoanApplication::class)
                        <li class="nav-item">
                            {{-- Assuming 'loan-applications.create' route exists --}}
                            {{-- Add 'active' class if the current route name matches 'loan-applications.create' --}}
                            <a href="{{ route('loan-applications.create') }}"
                                class="nav-link {{ request()->routeIs('loan-applications.create') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-laptop"></i> {{-- Example icon (Font Awesome) --}}
                                <p>Permohonan Pinjaman Peralatan</p> {{-- Malay label --}}
                            </a>
                        </li>
                    @endcan

                    {{-- Link to My Applications (List of user's submitted applications) --}}
                    {{-- This link is for the applicant to see their own applications --}}
                    {{-- Check if the user can view any of their own applications (Policy check for viewAny on either model) --}}
                    {{-- Assuming 'my-applications.*' is a route name prefix for the user's applications --}}
                    @if (Auth::user()->can('viewAny', \App\Models\EmailApplication::class) ||
                            Auth::user()->can('viewAny', \App\Models\LoanApplication::class))
                        {{-- Treeview item for 'My Applications' --}}
                        <li class="nav-item has-treeview {{ request()->routeIs('my-applications.*') ? 'menu-open' : '' }}">
                            <a href="#"
                                class="nav-link {{ request()->routeIs('my-applications.*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-file-alt"></i> {{-- Example icon (Font Awesome) --}}
                                <p>
                                    Permohonan Saya {{-- Malay label --}}
                                    <i class="right fas fa-angle-left"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview"> {{-- Submenu list --}}
                                {{-- Link to user's email applications list --}}
                                {{-- Check if user can viewAny EmailApplication --}}
                                @can('viewAny', \App\Models\EmailApplication::class)
                                    <li class="nav-item">
                                        {{-- Assuming 'my-applications.email.index' route exists --}}
                                        <a href="{{ route('my-applications.email.index') }}"
                                            class="nav-link {{ request()->routeIs('my-applications.email.*') ? 'active' : '' }}">
                                            {{-- Match my-applications.email.* routes --}}
                                            <i class="far fa-circle nav-icon"></i> {{-- Submenu item icon --}}
                                            <p>Akaun Emel ICT</p> {{-- Malay label --}}
                                        </a>
                                    </li>
                                @endcan
                                {{-- Link to user's loan applications list --}}
                                {{-- Check if user can viewAny LoanApplication --}}
                                @can('viewAny', \App\Models\LoanApplication::class)
                                    <li class="nav-item">
                                        {{-- Assuming 'my-applications.loan.index' route exists --}}
                                        <a href="{{ route('my-applications.loan.index') }}"
                                            class="nav-link {{ request()->routeIs('my-applications.loan.*') ? 'active' : '' }}">
                                            {{-- Match my-applications.loan.* routes --}}
                                            <i class="far fa-circle nav-icon"></i> {{-- Submenu item icon --}}
                                            <p>Pinjaman Peralatan ICT</p> {{-- Malay label --}}
                                        </a>
                                    </li>
                                @endcan
                            </ul>
                        </li>
                    @endif


                    {{-- Approvals Menu (Show only for users with approval permissions/roles) --}}
                    {{-- Check if the user can view the approvals dashboard or any approval list --}}
                    {{-- Assuming 'view-approvals-dashboard' or a more specific permission --}}
                    @can('view-approvals-dashboard') {{-- Assuming a permission for accessing approvals --}}
                        {{-- Treeview item for 'Approvals' --}}
                        <li class="nav-item has-treeview {{ request()->routeIs('approvals.*') ? 'menu-open' : '' }}">
                            <a href="#" class="nav-link {{ request()->routeIs('approvals.*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-check-circle"></i> {{-- Example icon (Font Awesome) --}}
                                <p>
                                    Kelulusan {{-- Malay label --}}
                                    <i class="right fas fa-angle-left"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview"> {{-- Submenu list --}}
                                {{-- Link to Pending Approvals list --}}
                                {{-- Assuming 'approvals.index' route exists and user has 'view-pending-approvals' permission --}}
                                @can('view-pending-approvals')
                                    {{-- Assuming a specific permission --}}
                                    <li class="nav-item">
                                        <a href="{{ route('approvals.index') }}"
                                            class="nav-link {{ request()->routeIs('approvals.index') ? 'active' : '' }}">
                                            <i class="far fa-circle nav-icon"></i> {{-- Submenu item icon --}}
                                            <p>Kelulusan Tertunda</p> {{-- Malay label --}}
                                        </a>
                                    </li>
                                @endcan
                                {{-- Link to Approval History --}}
                                {{-- Assuming 'approvals.history' route exists and user has 'view-approval-history' permission --}}
                                @can('view-approval-history')
                                    {{-- Assuming a specific permission --}}
                                    <li class="nav-item">
                                        <a href="{{ route('approvals.history') }}"
                                            class="nav-link {{ request()->routeIs('approvals.history') ? 'active' : '' }}">
                                            <i class="far fa-circle nav-icon"></i> {{-- Submenu item icon --}}
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
                    @can('access-rm-admin') {{-- Assuming a permission for accessing RM Administration --}}
                        {{-- Treeview item for 'RM Administration' --}}
                        <li
                            class="nav-item has-treeview {{ request()->routeIs('equipment.*') || request()->routeIs('loan-transactions.*') || request()->routeIs('reports.*') ? 'menu-open' : '' }}">
                            {{-- Use route names from the views we created --}}
                            {{-- Add 'active' class if any sub-route matches --}}
                            <a href="#"
                                class="nav-link {{ request()->routeIs('equipment.*') || request()->routeIs('loan-transactions.*') || request()->routeIs('reports.*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-tools"></i> {{-- Example icon (Font Awesome) --}}
                                <p>
                                    Pentadbiran RM {{-- Malay label --}}
                                    <i class="right fas fa-angle-left"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview"> {{-- Submenu list --}}
                                {{-- Equipment Management Links --}}
                                {{-- Check if the user can view equipment list --}}
                                @can('view-equipment')
                                    {{-- Assuming a permission for viewing equipment --}}
                                    <li class="nav-item">
                                        {{-- Assuming 'equipment.index' route exists --}}
                                        {{-- Add 'active' class if route matches --}}
                                        <a href="{{ route('equipment.index') }}"
                                            class="nav-link {{ request()->routeIs('equipment.*') ? 'active' : '' }}">
                                            {{-- Match equipment.* routes --}}
                                            <i class="far fa-circle nav-icon"></i> {{-- Submenu item icon --}}
                                            <p>Inventori Peralatan</p> {{-- Malay label --}}
                                        </a>
                                    </li>
                                @endcan

                                {{-- BPM Operations Links (Issuance/Return) --}}
                                {{-- Check if the user can access BPM operations --}}
                                @can('access-bpm-operations') {{-- Assuming a permission for accessing BPM operations --}}
                                    {{-- Nested Treeview for BPM Operations --}}
                                    <li
                                        class="nav-item has-treeview {{ request()->routeIs('loan-transactions.*') ? 'menu-open' : '' }}">
                                        {{-- Match loan-transactions.* routes --}}
                                        <a href="#"
                                            class="nav-link {{ request()->routeIs('loan-transactions.*') ? 'active' : '' }}">
                                            <i class="far fa-circle nav-icon"></i> {{-- Submenu item icon --}}
                                            <p>
                                                Operasi BPM {{-- Malay label --}}
                                                <i class="right fas fa-angle-left"></i>
                                            </p>
                                        </a>
                                        <ul class="nav nav-treeview"> {{-- Nested submenu list --}}
                                            {{-- Link to a view listing outstanding loans for issuance --}}
                                            {{-- Assuming 'loan-transactions.outstanding-loans' route exists and user has 'issue-loan-equipment' permission --}}
                                            @can('issue-loan-equipment')
                                                {{-- Assuming a specific permission --}}
                                                <li class="nav-item">
                                                    {{-- This view would list applications ready for issuance --}}
                                                    <a href="{{ route('loan-transactions.outstanding-loans') }}"
                                                        class="nav-link {{ request()->routeIs('loan-transactions.outstanding-loans') ? 'active' : '' }}">
                                                        <i class="far fa-dot-circle nav-icon"></i> {{-- Nested submenu item icon --}}
                                                        <p>Pinjaman Menunggu Pengeluaran</p> {{-- Malay label --}}
                                                    </a>
                                                </li>
                                            @endcan
                                            {{-- Link to a view listing issued loans for return --}}
                                            {{-- Assuming 'loan-transactions.issued-loans' route exists and user has 'return-loan-equipment' permission --}}
                                            @can('return-loan-equipment')
                                                {{-- Assuming a specific permission --}}
                                                <li class="nav-item">
                                                    {{-- This view would list transactions ready for return --}}
                                                    <a href="{{ route('loan-transactions.issued-loans') }}"
                                                        class="nav-link {{ request()->routeIs('loan-transactions.issued-loans') ? 'active' : '' }}">
                                                        <i class="far fa-dot-circle nav-icon"></i> {{-- Nested submenu item icon --}}
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
                                    {{-- Nested Treeview for Reports --}}
                                    <li class="nav-item has-treeview {{ request()->routeIs('reports.*') ? 'menu-open' : '' }}">
                                        {{-- Match reports.* routes --}}
                                        <a href="#" class="nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}">
                                            <i class="far fa-circle nav-icon"></i> {{-- Submenu item icon --}}
                                            <p>
                                                Laporan {{-- Malay label --}}
                                                <i class="right fas fa-angle-left"></i>
                                            </p>
                                        </a>
                                        <ul class="nav nav-treeview"> {{-- Nested submenu list --}}
                                            {{-- Link to Equipment Report --}}
                                            {{-- Assuming 'reports.equipment' route exists and user has 'view-equipment-report' permission --}}
                                            @can('view-equipment-report')
                                                {{-- Specific permission --}}
                                                <li class="nav-item">
                                                    <a href="{{ route('reports.equipment') }}"
                                                        class="nav-link {{ request()->routeIs('reports.equipment') ? 'active' : '' }}">
                                                        <i class="far fa-dot-circle nav-icon"></i> {{-- Nested submenu item icon --}}
                                                        <p>Laporan Inventori Peralatan</p> {{-- Malay label --}}
                                                    </a>
                                                </li>
                                            @endcan
                                            {{-- Link to Email Accounts Report --}}
                                            {{-- Assuming 'reports.email-accounts' route exists and user has 'view-email-accounts-report' permission --}}
                                            @can('view-email-accounts-report')
                                                {{-- Specific permission --}}
                                                <li class="nav-item">
                                                    <a href="{{ route('reports.email-accounts') }}"
                                                        class="nav-link {{ request()->routeIs('reports.email-accounts') ? 'active' : '' }}">
                                                        <i class="far fa-dot-circle nav-icon"></i> {{-- Nested submenu item icon --}}
                                                        <p>Laporan Akaun Emel</p> {{-- Malay label --}}
                                                    </a>
                                                </li>
                                            @endcan
                                            {{-- Link to Loan Applications Report --}}
                                            {{-- Assuming 'reports.loan-applications' route exists and user has 'view-loan-applications-report' permission --}}
                                            @can('view-loan-applications-report')
                                                {{-- Specific permission --}}
                                                <li class="nav-item">
                                                    <a href="{{ route('reports.loan-applications') }}"
                                                        class="nav-link {{ request()->routeIs('reports.loan-applications') ? 'active' : '' }}">
                                                        <i class="far fa-dot-circle nav-icon"></i> {{-- Nested submenu item icon --}}
                                                        <p>Laporan Pinjaman Peralatan</p> {{-- Malay label --}}
                                                    </a>
                                                </li>
                                            @endcan
                                            {{-- Link to User Activity Report --}}
                                            {{-- Assuming 'reports.user-activity' route exists and user has 'view-user-activity-report' permission --}}
                                            @can('view-user-activity-report')
                                                {{-- Specific permission --}}
                                                <li class="nav-item">
                                                    <a href="{{ route('reports.user-activity') }}"
                                                        class="nav-link {{ request()->routeIs('reports.user-activity') ? 'active' : '' }}">
                                                        <i class="far fa-dot-circle nav-icon"></i> {{-- Nested submenu item icon --}}
                                                        <p>Laporan Aktiviti Pengguna</p> {{-- Malay label --}}
                                                    </a>
                                                </li>
                                            @endcan
                                        </ul>
                                    </li>
                                @endcan

                                {{-- Add other admin/management links here (e.g., for Grades, etc.) --}}
                                {{-- Example: Link to Departments Management --}}
                                {{-- Assuming 'resource-management.admin.departments.index' route and 'view-departments' permission --}}
                                {{-- @can('view-departments')
                                  <li class="nav-item">
                                      <a href="{{ route('resource-management.admin.departments.index') }}"
                                          class="nav-link {{ request()->routeIs('resource-management.admin.departments.*') ? 'active' : '' }}">
                                          <i class="far fa-circle nav-icon"></i>
                                          <p>Pengurusan Jabatan</p>
                                      </a>
                                  </li>
                              @endcan --}}
                                {{-- Example: Link to Positions Management --}}
                                {{-- Assuming 'resource-management.admin.positions.index' route and 'view-positions' permission --}}
                                {{-- @can('view-positions')
                                  <li class="nav-item">
                                      <a href="{{ route('resource-management.admin.positions.index') }}"
                                          class="nav-link {{ request()->routeIs('resource-management.admin.positions.*') ? 'active' : '' }}">
                                          <i class="far fa-circle nav-icon"></i>
                                          <p>Pengurusan Jawatan</p>
                                      </a>
                                  </li>
                              @endcan --}}
                                {{-- Example: Link to Centers Management --}}
                                {{-- Assuming 'resource-management.admin.centers.index' route and 'view-centers' permission --}}
                                {{-- @can('view-centers')
                                  <li class="nav-item">
                                      <a href="{{ route('resource-management.admin.centers.index') }}"
                                          class="nav-link {{ request()->routeIs('resource-management.admin.centers.*') ? 'active' : '' }}">
                                          <i class="far fa-circle nav-icon"></i>
                                          <p>Pengurusan Pusat</p>
                                      </a>
                                  </li>
                              @endcan --}}


                            </ul>
                        </li>
                    @endcan

                    {{-- Close the general RM access check if it was opened --}}
                    {{-- Note: The `@endif` for `@can('access-resource-management')` is needed here --}}
                @endcan {{-- End general RM access check --}}
                {{-- ☝️ End New MOTAC Integrated Resource Management Menu Items ☝️ --}}


                {{-- Logout Link --}}
                <li class="nav-item">
                    {{-- Ensure 'logout' route exists --}}
                    {{-- This link triggers a form submission to log the user out --}}
                    <a href="#" class="nav-link"
                        onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        <i class="nav-icon fas fa-sign-out-alt"></i> {{-- Example icon (Font Awesome) --}}
                        <p>Log Keluar</p> {{-- Malay label --}}
                    </a>
                    {{-- Hidden form for the logout request --}}
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                        @csrf {{-- CSRF token --}}
                    </form>
                </li>

            </ul>
        </nav>
        {{-- /.sidebar-menu --}}
    </div>
    {{-- /.sidebar --}}
</aside>
