{{-- resources/views/livewire/sections/navbar/navbar.blade.php --}}
{{-- This view is a Livewire component view, intended to be included within a main layout file. --}}

@php
    // Provide default values for configuration variables if they are not set.
    // These variables should ideally be passed as public properties to the Livewire component.
    $containerNav = $containerNav ?? 'container-fluid'; // Default container class for the navbar
    $navbarDetached = $navbarDetached ?? ''; // Default value for navbar detached status
@endphp

{{-- Wrap the content in a div as it's a Livewire component root element.
     Livewire components must have a single root element. --}}
<div>
    {{-- Conditionally render the <nav> element based on the $navbarDetached variable --}}
    @if (isset($navbarDetached) && $navbarDetached == 'navbar-detached')
        {{-- Navbar structure for 'detached' layout --}}
        <nav class="layout-navbar {{ $containerNav }} navbar navbar-expand-xl {{ $navbarDetached }} align-items-center bg-navbar-theme"
            id="layout-navbar">
    @endif

    @if (isset($navbarDetached) && $navbarDetached == '')
        {{-- Navbar structure for 'not detached' (likely 'fixed') layout --}}
        <nav class="layout-navbar navbar navbar-expand-xl align-items-center bg-navbar-theme" id="layout-navbar">
            {{-- Include container div when navbar is not detached --}}
            <div class="{{ $containerNav }}">
    @endif

    {{-- Brand demo (display only for navbar-full and hide on below xl) --}}
    {{-- Conditionally display the application brand/logo section if $navbarFull is set (assuming it's a boolean) --}}
    @if (isset($navbarFull) && $navbarFull)
        {{-- Brand container with theme classes for full navbar --}}
        <div class="navbar-brand app-brand demo d-none d-xl-flex py-0 me-4">
            {{-- Link to the application's root or dashboard --}}
            <a href="{{ url('/') }}" class="app-brand-link gap-2">
                {{-- Include a partial for the application logo/macros --}}
                {{-- Adjust path '_partials.macros' if needed --}}
                <span class="app-brand-logo demo">
                    @include('_partials.macros', ['height' => 20]) {{-- Pass height variable to the partial --}}
                </span>
                {{-- Application Name displayed next to the logo, fetched from Laravel config --}}
                {{-- Adjust brand name if needed by changing 'variables.templateName' in your config --}}
                <span class="app-brand-text demo menu-text fw-bold">{{ config('variables.templateName') }}</span>
            </a>
        </div>
    @endif {{-- End conditional brand display --}}

    {{-- ! Not required for layout-without-menu --}}
    {{-- Conditional display for the layout menu toggle button --}}
    @if (!isset($navbarHideToggle) || !$navbarHideToggle)
        {{-- Check if $navbarHideToggle is not set or is false --}}
        <div
            class="layout-menu-toggle navbar-nav align-items-xl-center me-3 me-xl-0{{ isset($menuHorizontal) ? ' d-xl-none ' : '' }} {{ isset($contentNavbar) ? ' d-xl-none ' : '' }}">
            <a class="nav-item nav-link px-0 me-xl-4" href="javascript:void(0)">
                {{-- Icon for the menu toggle --}}
                <i class="ti ti-menu-2 ti-sm"></i>
            </a>
        </div>
    @endif {{-- End conditional menu toggle display --}}

    {{-- Container for the right side of the navbar --}}
    <div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">

        {{-- Element for theme style switching --}}
        <div class="navbar-nav align-items-center">
            <a class="nav-link style-switcher-toggle hide-arrow" href="javascript:void(0);">
                <i class='ti ti-sm'></i> {{-- Icon class for style switcher (controlled by JS) --}}
            </a>
        </div>
        {{-- List of navbar items on the right side --}}
        <ul class="navbar-nav flex-row align-items-center ms-auto">

            <li class="nav-item dropdown-language dropdown me-2 me-xl-1">
                {{-- Dropdown toggle link for language selection --}}
                <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown">
                    {{-- Flag icon based on the current application locale --}}
                    {{-- Uses 'fi fi-[country-code]' classes and 'fis' for square flags --}}
                    <i
                        class="fi fi-{{ app()->getLocale() == 'ar' ? 'sy' : (app()->getLocale() == 'ms' ? 'my' : 'us') }} fis rounded-circle me-1 fs-3"></i>
                </a>
                {{-- Dropdown menu for language options --}}
                <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                        {{-- Link to swap language to Arabic --}}
                        {{-- Apply 'selected' class if the current locale is Arabic --}}
                        {{-- Uses a named route 'language.swap' with locale parameter --}}
                        {{-- Includes data attributes for language code and text direction (rtl) --}}
                        <a class="dropdown-item {{ app()->getLocale() == 'ar' ? 'selected' : '' }}"
                            href="{{ route('language.swap', 'ar') }}" data-language="ar" data-text-direction="rtl">
                            <i class="fi fi-sy fis rounded-circle me-1 fs-3"></i> {{-- Syria flag for Arabic --}}
                            <span class="align-middle">العربية</span> {{-- Arabic text --}}
                        </a>
                    </li>
                    <li>
                        {{-- Link to swap language to English --}}
                        {{-- Apply 'selected' class if the current locale is English --}}
                        {{-- Uses a named route 'language.swap' with locale parameter --}}
                        {{-- Includes data attributes for language code and text direction (ltr) --}}
                        <a class="dropdown-item {{ app()->getLocale() == 'en' ? 'selected' : '' }}"
                            href="{{ route('language.swap', 'en') }}" data-language="en" data-text-direction="ltr">
                            <i class="fi fi-us fis rounded-circle me-1 fs-3"></i> {{-- US flag for English --}}
                            <span class="align-middle">English</span> {{-- English text --}}
                        </a>
                    </li>
                    <li>
                        {{-- Link to swap language to Bahasa Melayu --}}
                        {{-- Apply 'selected' class if the current locale is Malay --}}
                        {{-- Uses a named route 'language.swap' with locale parameter --}}
                        {{-- Includes data attributes for language code and text direction (ltr) --}}
                        <a class="dropdown-item {{ app()->getLocale() == 'ms' ? 'selected' : '' }}"
                            href="{{ route('language.swap', 'ms') }}" data-language="ms" data-text-direction="ltr">
                            <i class="fi fi-my fis rounded-circle me-1 fs-3"></i> {{-- Malaysia flag for Malay --}}
                            <span class="align-middle">Bahasa Melayu</span> {{-- Malay text --}}
                        </a>
                    </li>
                </ul> {{-- End language dropdown menu --}}
            </li> {{-- End language dropdown --}}

            <li class="nav-item navbar-dropdown dropdown-user dropdown">
                {{-- Dropdown toggle link for user menu --}}
                <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown">
                    {{-- User avatar, uses profile_photo_url from authenticated user or a default image --}}
                    <div class="avatar avatar-online">
                        <img src="{{ Auth::user() ? Auth::user()->profile_photo_url : asset('assets/img/avatars/1.png') }}"
                            alt class="w-px-40 h-auto rounded-circle">
                    </div>
                </a>
                {{-- User dropdown menu --}}
                <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                        {{-- Link to user's profile page --}}
                        {{-- Checks if 'profile.show' route exists (common with Jetstream), otherwise uses javascript:void(0) --}}
                        <a class="dropdown-item"
                            href="{{ Route::has('profile.show') ? route('profile.show') : 'javascript:void(0);' }}">
                            <div class="d-flex">
                                <div class="flex-shrink-0 me-3">
                                    {{-- User avatar within the dropdown header --}}
                                    <div class="avatar avatar-online">
                                        <img src="{{ Auth::user() ? Auth::user()->profile_photo_url : asset('assets/img/avatars/1.png') }}"
                                            alt class="w-px-40 h-auto rounded-circle">
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    {{-- Display authenticated user's name or a placeholder --}}
                                    <span class="fw-semibold d-block">
                                        @if (Auth::check())
                                            {{ Auth::user()->name }}
                                        @else
                                            John Doe
                                        @endif
                                    </span>
                                    {{-- Display user's primary role dynamically --}}
                                    {{-- Assuming $role variable is calculated in the Livewire component like in the vertical menu --}}
                                    <small class="text-muted">{{ $role ?? 'User' }}</small> {{-- Display determined role or default 'User' --}}
                                </div>
                            </div>
                        </a>
                    </li>
                    <li>
                        <div class="dropdown-divider"></div> {{-- Divider --}}
                    </li>
                    <li>
                        {{-- Link to My Profile --}}
                        {{-- Checks if 'profile.show' route exists --}}
                        <a class="dropdown-item"
                            href="{{ Route::has('profile.show') ? route('profile.show') : 'javascript:void(0);' }}">
                            <i class="ti ti-user-check me-2 ti-sm"></i> {{-- Icon --}}
                            <span class="align-middle">My Profile</span> {{-- Label --}}
                        </a>
                    </li>
                    {{-- Conditional link to API Tokens page if Jetstream has API features enabled --}}
                    @if (Auth::check() && Laravel\Jetstream\Jetstream::hasApiFeatures())
                        <li>
                            <a class="dropdown-item" href="{{ route('api-tokens.index') }}">
                                <i class='ti ti-key me-2 ti-sm'></i> {{-- Icon --}}
                                <span class="align-middle">API Tokens</span> {{-- Label --}}
                            </a>
                        </li>
                    @endif {{-- End Jetstream API features check --}}
                    <li>
                        {{-- Example link for Billing (placeholder/static) --}}
                        <a class="dropdown-item" href="javascript:void(0);">
                            <span class="d-flex align-items-center align-middle">
                                <i class="flex-shrink-0 ti ti-credit-card me-2 ti-sm"></i> {{-- Icon --}}
                                <span class="flex-grow-1 align-middle">Billing</span> {{-- Label --}}
                                {{-- Example badge --}}
                                <span
                                    class="flex-shrink-0 badge badge-center rounded-pill bg-label-danger w-px-20 h-px-20">2</span>
                            </span>
                        </a>
                    </li>
                    {{-- Conditional section for Team Management if authenticated and Jetstream has Team features enabled --}}
                    @if (Auth::User() && Laravel\Jetstream\Jetstream::hasTeamFeatures())
                        <li>
                            <div class="dropdown-divider"></div> {{-- Divider --}}
                        </li>
                        <li>
                            <h6 class="dropdown-header">Manage Team</h6> {{-- Section header --}}
                        </li>
                        <li>
                            <div class="dropdown-divider"></div> {{-- Divider --}}
                        </li>
                        <li>
                            {{-- Link to the current team's settings page --}}
                            <a class="dropdown-item"
                                href="{{ Auth::user() ? route('teams.show', Auth::user()->currentTeam->id) : 'javascript:void(0)' }}">
                                <i class='ti ti-settings me-2'></i> {{-- Icon --}}
                                <span class="align-middle">Team Settings</span> {{-- Label --}}
                            </a>
                        </li>
                        {{-- Conditional link to create a new team if the user can create teams --}}
                        @can('create', Laravel\Jetstream\Jetstream::newTeamModel())
                            <li>
                                <a class="dropdown-item" href="{{ route('teams.create') }}">
                                    <i class='ti ti-user me-2'></i> {{-- Icon --}}
                                    <span class="align-middle">Create New Team</span> {{-- Label --}}
                                </a>
                            </li>
                        @endcan {{-- End can create team check --}}
                        <li>
                            <div class="dropdown-divider"></div> {{-- Divider --}}
                        </li>
                        <lI>
                            <h6 class="dropdown-header">Switch Teams</h6> {{-- Section header --}}
                        </lI>
                        <li>
                            <div class="dropdown-divider"></div> {{-- Divider --}}
                        </li>
                        {{-- Loop through all teams the user belongs to and display switchable team links --}}
                        @if (Auth::user())
                            @foreach (Auth::user()->allTeams() as $team)
                                {{-- Below commented code read by artisan command while installing jetstream. !! Do not remove if you want to use jetstream. --}}
                                {{-- Jetstream component for switching teams --}}
                                <x-switchable-team :team="$team" />
                            @endforeach
                        @endif {{-- End check for authenticated user before looping teams --}}
                    @endif {{-- End Jetstream Team features check --}}
                    <li>
                        <div class="dropdown-divider"></div> {{-- Divider --}}
                    </li>
                    {{-- Conditional Logout or Login link --}}
                    @if (Auth::check())
                        {{-- Logout link for authenticated users, triggers hidden form submission --}}
                        <li>
                            <a class="dropdown-item" href="{{ route('logout') }}"
                                onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                <i class='ti ti-logout me-2'></i> {{-- Icon --}}
                                <span class="align-middle">Logout</span> {{-- Label --}}
                            </a>
                        </li>
                        {{-- Hidden form for POST logout request --}}
                        <form method="POST" id="logout-form" action="{{ route('logout') }}">
                            @csrf {{-- CSRF token for security --}}
                        </form>
                    @else
                        {{-- Login link for guest users --}}
                        <li>
                            {{-- Checks if 'login' route exists, otherwise uses a fallback URL --}}
                            <a class="dropdown-item"
                                href="{{ Route::has('login') ? route('login') : url('auth/login-basic') }}">
                                <i class='ti ti-login me-2'></i> {{-- Icon --}}
                                <span class="align-middle">Login</span> {{-- Label --}}
                            </a>
                        </li>
                    @endif {{-- End Auth check for Logout/Login --}}
                </ul> {{-- End user dropdown menu --}}
            </li> {{-- End user dropdown --}}
        </ul> {{-- End navbar-nav flex-row --}}
    </div> {{-- End navbar-nav-right --}}

    {{-- Conditional closing of the container div based on $navbarDetached --}}
    @if (!isset($navbarDetached) || $navbarDetached == '')
        {{-- Close div if navbar is not detached --}}
</div> {{-- End container div --}}
@endif
</nav> {{-- End nav --}}
</div> {{-- End Livewire root div --}}
