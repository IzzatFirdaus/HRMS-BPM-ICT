{{-- This is the Livewire Navbar component view --}}

<div>
    @php
        // Assuming Helper is a facade or globally available
        // use App\Helpers\Helper; // Uncomment if Helper is a facade
        $customizerHidden = 'customizer-hide';
        // Ensure $configData is available, provide a fallback
        $configData = $configData ?? Helper::appClasses();
        // Assuming Employee model is used directly in the view (consider eager loading in component)
        use App\Models\Employee;
        use Illuminate\Support\Facades\Storage; // For Storage::disk()
        use Illuminate\Support\Facades\Auth; // For Auth::user()
        use Illuminate\Support\Facades\App; // For App::getLocale()
        use Illuminate\Support\Facades\Route; // For Route::has()
    @endphp

    @push('custom-css')
        <style>
            /* Custom animations */
            .animation-fade {
                animation: fade 2s infinite;
            }

            .animation-rotate {
                animation: rotation 2s infinite;
            }

            @keyframes fade {
                0% {
                    opacity: 1;
                }

                50% {
                    opacity: 0;
                }

                100% {
                    opacity: 1;
                }
            }

            @keyframes rotation {
                from {
                    transform: rotate(0deg);
                }

                to {
                    transform: rotate(360deg);
                }
            }
        </style>
    @endpush

    @php
        $containerNav = $containerNav ?? 'container-fluid';
        $navbarDetached = $navbarDetached ?? 'navbar-detached';
        // $navbarDetached = ($navbarDetached ?? ''); // Original commented line
        // $navbarHideToggle = ($navbarDetached ?? true); // Original commented line - logic might be off here
        $navbarHideToggle = isset($navbarDetached) ? $navbarDetached !== 'navbar-detached' : true; // Adjusted logic based on common themes
    @endphp

    <!-- Navbar -->
    {{-- Conditional navbar wrapper based on $navbarDetached --}}
    @if (isset($navbarDetached) && $navbarDetached == 'navbar-detached')
        <nav class="layout-navbar {{ $containerNav }} navbar navbar-expand-xl {{ $navbarDetached }} align-items-center bg-navbar-theme"
            id="layout-navbar">
        @else
            {{-- Assume non-detached or empty means wrap content in containerNav --}}
            <nav class="layout-navbar navbar navbar-expand-xl align-items-center bg-navbar-theme" id="layout-navbar">
                <div class="{{ $containerNav }}">
    @endif

    <!-- Brand demo (display only for navbar-full and hide on below xl) -->
    @if (isset($navbarFull))
        <div class="navbar-brand app-brand demo d-none d-xl-flex py-0 me-4">
            <a href="{{ url('/') }}" class="app-brand-link gap-2">
                <span class="app-brand-logo demo">
                    {{-- Assumes _partials.macros exists and handles height --}}
                    @include('_partials.macros', ['height' => 20])
                </span>
                {{-- App Brand Text (safely access config) --}}
                <span
                    class="app-brand-text demo menu-text fw-bold">{{ config('variables.templateName') ?? 'AppName' }}</span>
            </a>
        </div>
    @endif

    <!-- ! Not required for layout-without-menu -->
    {{-- Conditionally display menu toggle --}}
    @if (!($navbarHideToggle ?? false))
        {{-- Check $navbarHideToggle safely --}}
        <div
            class="layout-menu-toggle navbar-nav align-items-xl-center me-3 me-xl-0 {{ isset($menuHorizontal) ? ' d-xl-none ' : '' }} {{ isset($contentNavbar) ? ' d-xl-none ' : '' }} d-xl-none">
            <a class="nav-item nav-link px-0 me-xl-4" href="javascript:void(0)">
                <i class="ti ti-menu-2 ti-sm"></i>
            </a>
        </div>
    @endif

    <div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">
        <div class="navbar-nav d-flex flex-row align-items-center">
            <!-- Style Switcher -->
            <a wire:ignore class="nav-link style-switcher-toggle hide-arrow" href="javascript:void(0);">
                <i class='ti ti-sm'></i>
            </a>
            <!--/ Style Switcher -->

            <!-- Offline Indicator -->
            <div wire:offline>
                <a class="nav-link dropdown-toggle hide-arrow">
                    <i class="animation-fade ti ti-wifi-off fs-3 mx-2"></i>
                </a>
            </div>
            <!-- Offline Indicator -->
        </div>

        <ul class="navbar-nav flex-row align-items-center ms-auto">

            <!-- Progress Bar or Session Messages -->
            {{-- Assumes $activeProgressBar and $percentage are public properties --}}
            @if (($activeProgressBar ?? false) && ($percentage ?? 0) < 100) {{-- Check if active and not complete --}}
                {{-- wire:poll should ideally be conditional or handled in the component for performance --}}
                <li wire:poll.1s="updateProgressBar" class="nav-item mx-3" style="width: 250px;">
                    <div class="progress" style="height: 20px;">
                        <div class="progress-bar progress-bar-striped progress-bar-animated bg-info" role="progressbar"
                            style="width: {{ $percentage ?? 0 }}%;" aria-valuenow="{{ $percentage ?? 0 }}"
                            aria-valuemin="0" aria-valuemax="100">{{ $percentage ?? 0 }}%</div> {{-- Safely access percentage --}}
                    </div>
                </li>
            @else
                {{-- Display session messages if no active progress bar --}}
                @if (session()->has('success'))
                    <li class="nav-item mx-3 text-success" role="alert"> {{-- Added role="alert" --}}
                        {{ session('success') }}
                    </li>
                @endif
                @if (session()->has('error'))
                    <li class="nav-item mx-3 text-danger" role="alert"> {{-- Added role="alert" --}}
                        {{ session('error') }}
                    </li>
                @endif
            @endif
            <!-- /Progress Bar or Session Messages -->

            <!-- Language -->
            <li class="nav-item dropdown-language dropdown me-2 me-xl-1">
                <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown">
                    {{-- Assuming current locale is displayed by default icon --}}
                    <i class="fi fi-us fis rounded-circle me-1 fs-3"></i>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                        {{-- Localized Arabic link --}}
                        <a class="dropdown-item {{ App::getLocale() == 'ar' ? 'selected' : '' }}"
                            href="{{ url('lang/ar') }}" data-language="ar" data-text-direction="rtl">
                            <i class="fi fi-sy fis rounded-circle me-1 fs-3"></i> {{-- Syrian flag for Arabic? Adjust if needed --}}
                            <span class="align-middle">{{ __('العربية') }}</span> {{-- Localized language name --}}
                        </a>
                    </li>
                    <li>
                        {{-- Localized English link --}}
                        <a class="dropdown-item {{ App::getLocale() == 'en' ? 'selected' : '' }}"
                            href="{{ url('lang/en') }}" data-language="en" data-text-direction="ltr">
                            <i class="fi fi-us fis rounded-circle me-1 fs-3"></i>
                            <span class="align-middle">{{ __('English') }}</span> {{-- Localized language name --}}
                        </a>
                    </li>
                </ul>
            </li>
            <!-- Language -->

            <!-- Notification -->
            <li class="nav-item dropdown-notifications navbar-dropdown dropdown me-3 me-xl-2">
                <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown"
                    data-bs-auto-close="outside" aria-expanded="false">
                    <i class="ti ti-bell ti-md"></i>
                    @if (count($unreadNotifications ?? []))
                        {{-- Check if collection is not empty safely --}}
                        <span
                            class="badge bg-danger rounded-pill badge-notifications">{{ count($unreadNotifications) }}</span>
                    @endif
                </a>
                <ul wire:ignore.self class="dropdown-menu dropdown-menu-end py-0">
                    <li class="dropdown-menu-header border-bottom">
                        <div class="dropdown-header d-flex align-items-center py-3">
                            <h5 class="text-body mb-0 me-auto">{{ __('Notifications') }}</h5> {{-- Localized title --}}
                            @if (count($unreadNotifications ?? []))
                                {{-- Check if collection is not empty safely --}}
                                {{-- Localized "Mark all as read" --}}
                                <a wire:click.prevent='markAllNotificationsAsRead()' href="javascript:void(0);"
                                    class="dropdown-notifications-all text-body mx-2"><i
                                        class="ti ti-mail-opened fs-4"></i></a> {{-- Added href="javascript:void(0);" --}}
                            @endif
                            {{-- Refresh button with loading animation --}}
                            <div wire:loading.class='animation-rotate'>
                                <a wire:click.prevent='$refresh' href="javascript:void(0);"
                                    class="dropdown-notifications-all text-body"><i class="ti ti-refresh fs-4"></i></a>
                                {{-- Added href="javascript:void(0);" --}}
                            </div>
                        </div>
                    </li>
                    <li class="dropdown-notifications-list scrollable-container ps">
                        <ul class="list-group list-group-flush">
                            {{-- Iterate through unread notifications safely --}}
                            @forelse (($unreadNotifications ?? []) as $notification)
                                <li
                                    class="list-group-item list-group-item-action dropdown-notifications-item marked-as-read">
                                    <div class="d-flex">
                                        <div class="flex-shrink-0 me-3">
                                            <div class="avatar">
                                                @php
                                                    // Safely get employee ID from notification data
                                                    $employeeId = data_get($notification->data, 'employee_id');
                                                    $imageSrc = Storage::disk('public')->url(
                                                        'profile-photos/.default-photo.jpg',
                                                    ); // Default image
                                                    if ($employeeId) {
                                                        // Eager loading Employee in the component is highly recommended to avoid N+1
                                                        // If not eager loaded, this line queries the DB for each notification
                                                        $employee = Employee::find($employeeId);
                                                        if (
                                                            $employee &&
                                                            Storage::disk('public')->exists(
                                                                $employee->profile_photo_path ?? '',
                                                            )
                                                        ) {
                                                            // Safely check path
                                                            $imageSrc = Storage::disk('public')->url(
                                                                $employee->profile_photo_path,
                                                            );
                                                        }
                                                    }
                                                @endphp
                                                <img src="{{ $imageSrc }}" alt="User Avatar"
                                                    class="w-px-40 h-auto rounded-circle"> {{-- Added alt text and fixed class --}}
                                                {{-- <span class="avatar-initial rounded-circle bg-label-success"><i class="ti ti-chart-pie"></i></span> --}}
                                            </div>
                                        </div>
                                        <div class="flex-grow-1">
                                            {{-- Safely access notification data and localize message --}}
                                            <h6 class="mb-1">{{ data_get($notification->data, 'user', 'N/A') }}</h6>
                                            {{-- Safely access user name --}}
                                            <p class="mb-0">{{ __(data_get($notification->data, 'message', '')) }}
                                            </p> {{-- Safely access message and localize --}}
                                            {{-- Safely access created_at and format --}}
                                            <small
                                                class="text-muted">{{ $notification->created_at?->diffForHumans() ?? 'N/A' }}</small>
                                            {{-- Use null-safe operator --}}
                                        </div>
                                        <div class="flex-shrink-0 dropdown-notifications-actions">
                                            {{-- Localized "Mark as read" button --}}
                                            <a wire:click="markNotificationAsRead('{{ $notification->id }}')"
                                                href="javascript:void(0);" class="dropdown-notifications-read"><button
                                                    class="btn btn-xs rounded-pill btn-outline-primary waves-effect">{{ __('Mark as read') }}</button></a>
                                            {{-- Added href="javascript:void(0);" --}}
                                        </div>
                                    </div>
                                </li>
                            @empty
                                {{-- Localized "No notifications" message --}}
                                <li class="border-top">
                                    <p class="d-flex justify-content-center text-muted m-3 p-2 h-px-40 align-items-center"
                                        style="text-align: center">
                                        {{ __('Time to relax!') }}
                                        <br>
                                        {{ __('No new updates to worry about') }}
                                    </p>
                                </li>
                            @endforelse
                        </ul>
                        {{-- Perfect Scrollbar elements (kept as in original) --}}
                        <div class="ps__rail-x" style="left: 0px; bottom: 0px;">
                            <div class="ps__thumb-x" tabindex="0" style="left: 0px; width: 0px;"></div>
                        </div>
                        <div class="ps__rail-y" style="top: 0px; right: 0px;">
                            <div class="ps__thumb-y" tabindex="0" style="top: 0px; height: 0px;"></div>
                        </div>
                    </li>
                    <li class="dropdown-menu-footer border-top">
                        {{-- Localized "View all notifications" link (kept disabled as in original) --}}
                        <a href="#"
                            class="dropdown-item d-flex justify-content-center text-primary p-2 h-px-40 mb-1 align-items-center"
                            style="opacity: 0.5;pointer-events: none;">
                            {{ __('View all notifications') }}
                        </a>
                    </li>
                </ul>
            </li>
            <!-- Notification -->

            <!-- User -->
            <li class="nav-item navbar-dropdown dropdown-user dropdown">
                <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown">
                    <div class="avatar avatar-online">
                        {{-- Safely access user profile photo URL --}}
                        <img src="{{ Auth::user()?->profile_photo_url ?? asset('assets/img/avatars/1.png') }}"
                            alt="User Avatar" class="w-px-40 h-auto rounded-circle"> {{-- Added alt text --}}
                    </div>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                        {{-- User name and role display in dropdown header --}}
                        {{-- Safely check if user is authenticated before accessing properties --}}
                        @if (Auth::check())
                            <a class="dropdown-item"
                                href="{{ Route::has('profile.show') ? route('profile.show') : 'javascript:void(0);' }}">
                                {{-- Link to profile --}}
                                <div class="d-flex">
                                    <div class="flex-shrink-0 me-3">
                                        <div class="avatar avatar-online">
                                            {{-- Safely access user profile photo URL --}}
                                            <img src="{{ Auth::user()?->profile_photo_url ?? asset('assets/img/avatars/1.png') }}"
                                                alt="User Avatar" class="w-px-40 h-auto rounded-circle">
                                            {{-- Added alt text --}}
                                        </div>
                                    </div>
                                    <div class="flex-grow-1">
                                        {{-- Safely display user name --}}
                                        <span
                                            class="fw-semibold d-block">{{ Auth::user()?->name ?? __('!!No Name!!') }}</span>
                                        {{-- Localized fallback --}}
                                        {{-- Safely display user role and localize --}}
                                        <small
                                            class="text-muted">{{ Auth::user()?->getRoleNames()?->first() ?? __('No Role') }}</small>
                                        {{-- Use null-safe operators and localized fallback --}}
                                    </div>
                                </div>
                            </a>
                        @else
                            {{-- Display something if user is not logged in (though this dropdown likely hidden) --}}
                            <span class="fw-semibold d-block">{{ __('Guest') }}</span> {{-- Localized --}}
                        @endif
                    </li>
                    {{-- Commented out profile/billing links (kept as in original) --}}
                    {{-- <li> <div class="dropdown-divider"></div> </li> --}}
                    {{-- <li>
                <a class="dropdown-item" href="{{ Route::has('profile.show') ? route('profile.show') : 'javascript:void(0);' }}">
                  <i class="ti ti-user-check me-2 ti-sm"></i>
                  <span class="align-middle">My Profile</span> Localize this
                </a>
              </li> --}}
                    {{-- <li>
                <a class="dropdown-item" href="javascript:void(0);">
                  <span class="d-flex align-items-center align-middle">
                    <i class="flex-shrink-0 ti ti-credit-card me-2 ti-sm"></i>
                    <span class="flex-grow-1 align-middle">Billing</span> Localize this
                    <span class="flex-shrink-0 badge badge-center rounded-pill bg-label-danger w-px-20 h-px-20">2</span>
                  </span>
                </a>
              </li> --}}
                    <li>
                        <div class="dropdown-divider"></div> {{-- Divider --}}
                    </li>
                    {{-- Logout or Login link --}}
                    @if (Auth::check())
                        <li>
                            {{-- Logout link and form --}}
                            <a class="dropdown-item" href="{{ route('logout') }}"
                                onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                <i class='ti ti-logout me-2'></i>
                                <span class="align-middle">{{ __('Sign out') }}</span> {{-- Localized --}}
                            </a>
                        </li>
                        <form method="POST" id="logout-form" action="{{ route('logout') }}">
                            @csrf
                        </form>
                    @else
                        <li>
                            {{-- Login link --}}
                            <a class="dropdown-item"
                                href="{{ Route::has('login') ? route('login') : url('auth/login-basic') }}">
                                {{-- Fallback URL --}}
                                <i class='ti ti-login me-2'></i>
                                <span class="align-middle">{{ __('Login') }}</span> {{-- Localized --}}
                            </a>
                        </li>
                    @endif
                </ul>
            </li>
            <!--/ User -->

        </ul>
    </div>
    {{-- Closing div for containerNav if not detached --}}
    @if (!isset($navbarDetached) || $navbarDetached == '')
        {{-- Use || for clarity, matches the opening if --}}
</div>
@endif
</nav>
<!-- / Navbar -->
</div>
