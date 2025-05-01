{{-- This is the Livewire Navbar component view --}}
{{-- Assuming this view is rendered by a Livewire component that passes necessary data --}}

<div>
    @php
        // Assuming Helper is a facade or globally available.
        // Ideally, pass $configData from the Livewire component.
        // use App\Helpers\Helper; // Uncomment if Helper is a facade and not autoloaded
        $customizerHidden = 'customizer-hide'; // This variable doesn't seem directly used in the visible part of this snippet

        // Ensure $configData is available. Provide a fallback if not passed from component.
        // Better practice: Always pass required data like $configData from the component.
        $configData =
            $configData ?? (class_exists(\App\Helpers\Helper::class) ? \App\Helpers\Helper::appClasses() : []);

        // Importing facades and models used directly in the view.
        // For performance and separation of concerns, fetching data like $unreadNotifications
        // should be done in the Livewire component and passed to the view.
        use App\Models\Employee; // Used for fetching profile photos in notifications (potential N+1)
        use Illuminate\Support\Facades\Storage; // For Storage::disk()
        use Illuminate\Support\Facades\Auth; // For Auth::user()
        use Illuminate\Support\Facades\App; // For App::getLocale()
        use Illuminate\Support\Facades\Route; // For Route::has()
        use Illuminate\Support\Carbon; // For date formatting in notifications
    @endphp

    {{-- Push custom CSS specific to this component to the 'custom-css' stack in your layout --}}
    @push('custom-css')
        <style>
            /* Custom animations for visual feedback */
            .animation-fade {
                animation: fade 2s infinite;
            }

            .animation-rotate {
                animation: rotation 2s linear infinite;
                /* Added linear for consistent speed */
            }

            @keyframes fade {

                0%,
                100% {
                    /* Start and end opacity */
                    opacity: 1;
                }

                50% {
                    /* Midpoint opacity */
                    opacity: 0.5;
                    /* Adjusted for smoother fade */
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
        // Set default values for layout variables if they are not passed from the component
        $containerNav = $containerNav ?? 'container-fluid';
        $navbarDetached = $navbarDetached ?? 'navbar-detached';

        // Determine if the menu toggle should be hidden based on layout configuration
        // Adjusted logic based on common theme implementations
        $navbarHideToggle = isset($navbarDetached) ? $navbarDetached !== 'navbar-detached' : true;
    @endphp

    {{-- Conditional navbar wrapper based on $navbarDetached layout setting --}}
    @if (isset($navbarDetached) && $navbarDetached === 'navbar-detached')
        {{-- Use strict comparison === --}}
        <nav class="layout-navbar {{ $containerNav }} navbar navbar-expand-xl {{ $navbarDetached }} align-items-center bg-navbar-theme"
            id="layout-navbar">
        @else
            {{-- Assume non-detached or empty means wrap content in containerNav --}}
            <nav class="layout-navbar navbar navbar-expand-xl align-items-center bg-navbar-theme" id="layout-navbar">
                <div class="{{ $containerNav }}">
    @endif

    {{-- Brand demo (display only for navbar-full and hide on below xl) --}}
    {{-- Assumes $navbarFull is passed from the component if applicable --}}
    @if (isset($navbarFull))
        <div class="navbar-brand app-brand demo d-none d-xl-flex py-0 me-4">
            <a href="{{ url('/') }}" class="app-brand-link gap-2">
                <span class="app-brand-logo demo">
                    {{-- Assumes _partials.macros exists and handles SVG output with dynamic height --}}
                    @include('_partials.macros', ['height' => 20])
                </span>
                {{-- App Brand Text (safely access config variable) --}}
                <span
                    class="app-brand-text demo menu-text fw-bold">{{ config('variables.templateName') ?? 'AppName' }}</span>
            </a>
        </div>
    @endif

    {{-- ! Not required for layout-without-menu --}}
    {{-- Conditionally display menu toggle based on layout configuration --}}
    {{-- Assumes $menuHorizontal and $contentNavbar are passed from the component if applicable --}}
    @if (!($navbarHideToggle ?? false))
        {{-- Safely check $navbarHideToggle, defaulting to false if not set --}}
        <div
            class="layout-menu-toggle navbar-nav align-items-xl-center me-3 me-xl-0 {{ isset($menuHorizontal) ? ' d-xl-none ' : '' }} {{ isset($contentNavbar) ? ' d-xl-none ' : '' }} d-xl-none">
            <a class="nav-item nav-link px-0 me-xl-4" href="javascript:void(0)">
                <i class="ti ti-menu-2 ti-sm"></i>
            </a>
        </div>
    @endif

    {{-- Navbar content (items on the right side) --}}
    <div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">
        <div class="navbar-nav d-flex flex-row align-items-center">
            {{-- wire:ignore prevents Livewire from updating this element and its children --}}
            <a wire:ignore class="nav-link style-switcher-toggle hide-arrow" href="javascript:void(0);">
                {{-- The actual switcher icon will be added by JS that wire:ignore protects --}}
                <i class='ti ti-sm'></i>
            </a>
            {{-- wire:offline displays content only when the browser is offline --}}
            <div wire:offline>
                <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" title="{{ __('Offline') }}">
                    {{-- Added title for tooltip --}}
                    <i class="animation-fade ti ti-wifi-off fs-3 mx-2"></i>
                </a>
            </div>
        </div>

        {{-- Navbar items list --}}
        <ul class="navbar-nav flex-row align-items-center ms-auto">

            {{-- Assumes $activeProgressBar and $percentage are public properties in the Livewire component --}}
            {{-- The wire:poll.1s should ideally be conditional in the component to avoid unnecessary polling when progress is complete or not active --}}
            @if (($activeProgressBar ?? false) && ($percentage ?? 0) < 100) {{-- Check if active and not complete, safely accessing properties --}}
                <li wire:poll.1s="updateProgressBar" class="nav-item mx-3" style="width: 250px;">
                    <div class="progress" style="height: 20px;">
                        <div class="progress-bar progress-bar-striped progress-bar-animated bg-info" role="progressbar"
                            style="width: {{ $percentage ?? 0 }}%;" aria-valuenow="{{ $percentage ?? 0 }}"
                            {{-- Safely access percentage --}} aria-valuemin="0" aria-valuemax="100">{{ $percentage ?? 0 }}%</div>
                        {{-- Safely access percentage --}}
                    </div>
                </li>
            @else
                {{-- Display session messages if no active progress bar --}}
                {{-- These messages should ideally be managed as Livewire component properties/flashes too --}}
                @if (session()->has('success'))
                    <li class="nav-item mx-3 text-success" role="alert"> {{-- Added role="alert" for accessibility --}}
                        {{ session('success') }}
                    </li>
                @endif
                @if (session()->has('error'))
                    <li class="nav-item mx-3 text-danger" role="alert"> {{-- Added role="alert" for accessibility --}}
                        {{ session('error') }}
                    </li>
                @endif
            @endif
            {{-- wire:ignore.self is not needed here unless there's complex JS interaction that Livewire interferes with --}}
            <li class="nav-item dropdown-language dropdown me-2 me-xl-1">
                <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown">
                    {{-- Display the current locale's flag. Requires flag icons CSS (e.g., `fi fi-us fis`). --}}
                    {{-- The default icon should likely reflect the current App locale --}}
                    <i class="fi {{ 'fi-' . App::getLocale() }} fis rounded-circle me-1 fs-3"></i>
                    {{-- Dynamically set flag based on locale --}}
                    {{-- Original code had fi-us always, which is incorrect for other locales --}}
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    {{-- Language option for Arabic --}}
                    <li>
                        {{-- Link to switch locale to 'ar'. The route 'lang/{locale}' should be defined. --}}
                        <a class="dropdown-item {{ App::getLocale() == 'ar' ? 'selected' : '' }}"
                            href="{{ url('lang/ar') }}" data-language="ar" data-text-direction="rtl">
                            <i class="fi fi-sy fis rounded-circle me-1 fs-3"></i> {{-- Syrian flag often used for Arabic, adjust if needed --}}
                            <span class="align-middle">{{ __('العربية') }}</span> {{-- Localized language name --}}
                        </a>
                    </li>
                    {{-- Language option for English --}}
                    <li>
                        {{-- Link to switch locale to 'en'. --}}
                        <a class="dropdown-item {{ App::getLocale() == 'en' ? 'selected' : '' }}"
                            href="{{ url('lang/en') }}" data-language="en" data-text-direction="ltr">
                            <i class="fi fi-us fis rounded-circle me-1 fs-3"></i> {{-- US flag often used for English --}}
                            <span class="align-middle">{{ __('English') }}</span> {{-- Localized language name --}}
                        </a>
                    </li>
                    {{-- TODO: Add more language options if needed --}}
                </ul>
            </li>
            {{-- wire:ignore.self prevents Livewire from interfering with Bootstrap's dropdown JS --}}
            <li class="nav-item dropdown-notifications navbar-dropdown dropdown me-3 me-xl-2">
                <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown"
                    data-bs-auto-close="outside" aria-expanded="false">
                    <i class="ti ti-bell ti-md"></i>
                    {{-- Display notification count badge if there are unread notifications --}}
                    {{-- unreadNotifications should be a public property in the Livewire component, ideally eager-loaded with the user --}}
                    @if (count($unreadNotifications ?? []))
                        {{-- Safely check if the collection/array is not empty --}}
                        <span
                            class="badge bg-danger rounded-pill badge-notifications">{{ count($unreadNotifications) }}</span>
                    @endif
                </a>
                {{-- wire:ignore.self on the dropdown menu to manage Bootstrap's JS handling --}}
                <ul wire:ignore.self class="dropdown-menu dropdown-menu-end py-0">
                    <li class="dropdown-menu-header border-bottom">
                        <div class="dropdown-header d-flex align-items-center py-3">
                            <h5 class="text-body mb-0 me-auto">{{ __('Notifications') }}</h5> {{-- Localized title --}}
                            {{-- Display 'Mark all as read' link if there are unread notifications --}}
                            @if (count($unreadNotifications ?? []))
                                {{-- Safely check if not empty --}}
                                {{-- wire:click.prevent calls a Livewire method, preventing default link behavior --}}
                                <a wire:click.prevent='markAllNotificationsAsRead()' href="javascript:void(0);"
                                    {{-- Added href="javascript:void(0);" as a safe fallback --}} class="dropdown-notifications-all text-body mx-2"
                                    title="{{ __('Mark all as read') }}"> {{-- Added title for tooltip --}}
                                    <i class="ti ti-mail-opened fs-4"></i>
                                </a>
                            @endif
                            {{-- Refresh button with loading animation --}}
                            {{-- wire:loading.class applies a class while a Livewire action is loading (here, $refresh) --}}
                            {{-- wire:click.prevent='$refresh' triggers a component refresh --}}
                            <div wire:loading.class='animation-rotate' wire:target='$refresh'
                                title="{{ __('Refresh') }}"> {{-- Added wire:target and title --}}
                                <a wire:click.prevent='$refresh' href="javascript:void(0);" {{-- Added href="javascript:void(0);" as a safe fallback --}}
                                    class="dropdown-notifications-all text-body">
                                    <i class="ti ti-refresh fs-4"></i>
                                </a>
                            </div>
                        </div>
                    </li>
                    {{-- Notification list container --}}
                    {{-- scrollable-container and ps classes suggest use of Perfect Scrollbar or similar --}}
                    <li class="dropdown-notifications-list scrollable-container ps">
                        <ul class="list-group list-group-flush">
                            {{-- Iterate through unread notifications. unreadNotifications should be passed from the component. --}}
                            @forelse (($unreadNotifications ?? []) as $notification)
                                {{-- Safely iterate over the collection --}}
                                <li
                                    class="list-group-item list-group-item-action dropdown-notifications-item {{ $notification->read_at ? 'marked-as-read' : '' }}">
                                    {{-- Apply 'marked-as-read' class based on read_at --}}
                                    <div class="d-flex">
                                        {{-- Avatar Section --}}
                                        <div class="flex-shrink-0 me-3">
                                            <div class="avatar">
                                                @php
                                                    // Danger: This performs a database query (Employee::find) for *each* notification
                                                    // and a file system check (Storage::disk('public')->exists) for *each* notification.
                                                    // This is an N+1 problem and very inefficient.
                                                    // Refactor: Eager load the 'employee' relationship with notifications in the Livewire component.
                                                    // Then access $notification->employee?->profile_photo_path.

                                                    $employeeId = data_get($notification->data, 'employee_id');
                                                    $employeeProfilePhotoPath = null;

                                                    // Check if the employee relationship was eager loaded or manually fetched
                                                    if ($notification->relationLoaded('employee')) {
                                                        $employeeProfilePhotoPath =
                                                            $notification->employee?->profile_photo_path;
                                                    } else {
                                                        // Fallback to direct lookup (inefficient!)
                                                        $employee = $employeeId ? Employee::find($employeeId) : null;
                                                        $employeeProfilePhotoPath = $employee?->profile_photo_path;
                                                    }

                                                    // Determine the image source: employee photo, user photo, or default
                                                    // Assumes user profile photos are stored publicly.
                                                    $imageSrc =
                                                        Auth::user()?->profile_photo_url ??
                                                        asset('assets/img/avatars/1.png'); // Default to logged-in user's photo or generic default

// If an employee is linked and has a profile photo, use that
if (
    $employeeProfilePhotoPath &&
    Storage::disk('public')->exists($employeeProfilePhotoPath)
) {
    $imageSrc = Storage::disk('public')->url(
        $employeeProfilePhotoPath,
    );
} else {
    // If notification data has a user_id or links to Auth user, try their photo
    $notifyingUserId = data_get($notification->data, 'user_id'); // Assuming notification data *might* include user_id
    if ($notifyingUserId && $notifyingUserId === Auth::id()) {
        // Already handled by the default $imageSrc above
    } else {
        // Fallback to generic default if no specific user/employee photo found
        $imageSrc = asset('assets/img/avatars/1.png');
                                                        }
                                                    }

                                                @endphp
                                                <img src="{{ $imageSrc }}" alt="User Avatar" {{-- Added alt text for accessibility --}}
                                                    class="w-px-40 h-auto rounded-circle"> {{-- Added alt text and fixed class --}}
                                                {{-- Alternative: Use an avatar initial/icon if no image --}}
                                                {{-- <span class="avatar-initial rounded-circle bg-label-success"><i class="ti ti-chart-pie"></i></span> --}}
                                            </div>
                                        </div>
                                        {{-- Notification Content --}}
                                        <div class="flex-grow-1">
                                            {{-- Safely access notification data for title/user and localize --}}
                                            {{-- Assumes notification data contains 'user' for the name and 'message' for the main text --}}
                                            <h6 class="mb-1">
                                                {{ data_get($notification->data, 'user', __('Notification')) }}</h6>
                                            {{-- Safely access message from notification data and localize --}}
                                            <p class="mb-0">
                                                {{ __(data_get($notification->data, 'message', __('New notification'))) }}
                                            </p>
                                            {{-- Safely access created_at timestamp and format using Carbon's diffForHumans --}}
                                            <small
                                                class="text-muted">{{ $notification->created_at?->diffForHumans() ?? __('Just now') }}</small>
                                            {{-- Use null-safe operator and localized fallback --}}
                                        </div>
                                        {{-- Notification Actions (e.g., Mark as Read) --}}
                                        <div class="flex-shrink-0 dropdown-notifications-actions">
                                            {{-- wire:click calls a Livewire method, passing the notification ID --}}
                                            {{-- Added href="javascript:void(0);" as a safe fallback --}}
                                            @if (!$notification->read_at)
                                                {{-- Only show mark as read if unread --}}
                                                <a wire:click="markNotificationAsRead('{{ $notification->id }}')"
                                                    href="javascript:void(0);" class="dropdown-notifications-read"
                                                    title="{{ __('Mark as read') }}"> {{-- Added title for tooltip --}}
                                                    <i class='ti ti-x ti-sm text-body'></i> {{-- Use an icon for 'Mark as read' --}}
                                                    {{-- Original code used a button, icon is more common for these small actions --}}
                                                    {{-- <button class="btn btn-xs rounded-pill btn-outline-primary waves-effect">{{ __('Mark as read') }}</button> --}}
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                </li>
                            @empty
                                {{-- Display message when there are no unread notifications --}}
                                <li class="border-top">
                                    <p class="d-flex justify-content-center text-muted m-3 p-2 h-px-40 align-items-center"
                                        style="text-align: center"> {{-- text-align: center might be redundant with justify-content-center --}}
                                        {{ __('Time to relax!') }}<br>{{ __('No new updates to worry about') }}
                                        {{-- Localized messages --}}
                                    </p>
                                </li>
                            @endforelse
                        </ul>
                        {{-- Perfect Scrollbar elements (kept as in original, assume necessary for styling) --}}
                        <div class="ps__rail-x" style="left: 0px; bottom: 0px;">
                            <div class="ps__thumb-x" tabindex="0" style="left: 0px; width: 0px;"></div>
                        </div>
                        <div class="ps__rail-y" style="top: 0px; right: 0px;">
                            <div class="ps__thumb-y" tabindex="0" style="top: 0px; height: 0px;"></div>
                        </div>
                    </li>
                    {{-- Dropdown Footer --}}
                    <li class="dropdown-menu-footer border-top">
                        {{-- Localized "View all notifications" link --}}
                        {{-- Kept disabled as in original code. Consider enabling this link if you have a notifications index page. --}}
                        <a href="#" {{-- Use '#' or a route to the notifications index --}}
                            class="dropdown-item d-flex justify-content-center text-primary p-2 h-px-40 mb-1 align-items-center"
                            style="opacity: 0.5;pointer-events: none;"> {{-- Styles to keep it disabled --}}
                            {{ __('View all notifications') }} {{-- Localized string --}}
                        </a>
                    </li>
                </ul>
            </li>
            {{-- wire:ignore.self is not needed here unless there's complex JS interaction --}}
            <li class="nav-item navbar-dropdown dropdown-user dropdown">
                <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown">
                    <div class="avatar avatar-online">
                        {{-- Safely access logged-in user's profile photo URL --}}
                        {{-- Auth::user()?->profile_photo_url comes from the HasProfilePhoto trait --}}
                        {{-- Provide a default fallback image if no photo is set or user is not logged in --}}
                        <img src="{{ Auth::user()?->profile_photo_url ?? asset('assets/img/avatars/1.png') }}"
                            {{-- Use asset() for public path --}} alt="{{ __('User Avatar') }}"
                            class="w-px-40 h-auto rounded-circle"> {{-- Added localized alt text and fixed class --}}
                    </div>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                        {{-- User name and role display in dropdown header --}}
                        {{-- Safely check if user is authenticated before accessing properties --}}
                        @if (Auth::check())
                            {{-- Link to user profile, checking if the 'profile.show' route exists (Jetstream/Fortify default) --}}
                            <a class="dropdown-item"
                                href="{{ Route::has('profile.show') ? route('profile.show') : 'javascript:void(0);' }}">
                                <div class="d-flex">
                                    <div class="flex-shrink-0 me-3">
                                        <div class="avatar avatar-online">
                                            {{-- Display the same user avatar as in the navbar toggle --}}
                                            <img src="{{ Auth::user()?->profile_photo_url ?? asset('assets/img/avatars/1.png') }}"
                                                alt="{{ __('User Avatar') }}" class="w-px-40 h-auto rounded-circle">
                                            {{-- Added localized alt text --}}
                                        </div>
                                    </div>
                                    <div class="flex-grow-1">
                                        {{-- Safely display user name (using name property, or adjust if using full_name/accessor) --}}
                                        <span
                                            class="fw-semibold d-block">{{ Auth::user()?->name ?? __('!!No Name!!') }}</span>
                                        {{-- Localized fallback --}}
                                        {{-- Safely display user role (assuming Spatie HasRoles and getRoleNames()) and localize --}}
                                        {{-- getRoleNames() returns a collection, use first() to get the primary role name string --}}
                                        <small
                                            class="text-muted">{{ Auth::user()?->getRoleNames()?->first() ?? __('No Role') }}</small>
                                        {{-- Use null-safe operators and localized fallback --}}
                                    </div>
                                </div>
                            </a>
                        @else
                            {{-- Display something if user is not logged in (though this dropdown is likely hidden) --}}
                            <span class="fw-semibold d-block">{{ __('Guest') }}</span> {{-- Localized --}}
                        @endif
                    </li>
                    {{-- Commented out profile/billing links (kept as in original). Uncomment and adjust routes/localization if needed. --}}
                    {{-- <li> <div class="dropdown-divider"></div> </li> --}}
                    {{-- <li>
                      <a class="dropdown-item" href="{{ Route::has('profile.show') ? route('profile.show') : 'javascript:void(0);' }}">
                          <i class="ti ti-user-check me-2 ti-sm"></i>
                          <span class="align-middle">{{ __('My Profile') }}</span> // Localize this
                      </a>
                  </li> --}}
                    {{-- <li>
                      <a class="dropdown-item" href="javascript:void(0);">
                          <span class="d-flex align-items-center align-middle">
                              <i class="flex-shrink-0 ti ti-credit-card me-2 ti-sm"></i>
                              <span class="flex-grow-1 align-middle">{{ __('Billing') }}</span> // Localize this
                              <span class="flex-shrink-0 badge badge-center rounded-pill bg-label-danger w-px-20 h-px-20">2</span>
                          </span>
                      </a>
                  </li> --}}
                    <li>
                        <div class="dropdown-divider"></div> {{-- Divider --}}
                    </li>
                    {{-- Logout or Login link based on authentication status --}}
                    @if (Auth::check())
                        <li>
                            {{-- Logout link and form --}}
                            {{-- Use a form submission for logout (standard for security) --}}
                            <a class="dropdown-item" href="{{ route('logout') }}"
                                onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                <i class='ti ti-logout me-2'></i>
                                <span class="align-middle">{{ __('Sign out') }}</span> {{-- Localized --}}
                            </a>
                        </li>
                        {{-- Hidden logout form --}}
                        <form method="POST" id="logout-form" action="{{ route('logout') }}">
                            @csrf
                        </form>
                    @else
                        <li>
                            {{-- Login link if not authenticated --}}
                            {{-- Check if the 'login' route exists (Fortify/Jetstream default), otherwise use a fallback URL --}}
                            <a class="dropdown-item"
                                href="{{ Route::has('login') ? route('login') : url('auth/login-basic') }}">
                                <i class='ti ti-login me-2'></i>
                                <span class="align-middle">{{ __('Login') }}</span> {{-- Localized --}}
                            </a>
                        </li>
                    @endif
                </ul>
            </li>
        </ul>
    </div>
    {{-- Closing div for containerNav if not detached --}}
    {{-- Use || for clarity, matches the opening if conditions. Ensure the closing div structure matches the opening conditional nav structure. --}}
    @if (!isset($navbarDetached) || $navbarDetached !== 'navbar-detached')
        {{-- Use !== to match the opening if --}}
</div> {{-- Close {{ $containerNav }} div --}}
@endif
</nav>
</div>
