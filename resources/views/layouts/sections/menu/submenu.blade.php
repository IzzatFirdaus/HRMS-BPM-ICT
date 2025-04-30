@php
    // Safely get the authenticated user and their roles
    $user = Auth::user();
    $role = null; // Initialize $role variable

    // Determine the user's primary role for menu display logic
if ($user) {
    // Get the user's role names using the role package (e.g., Spatie/laravel-permission)
        // Use optional chaining just in case getRoleNames() is not available or returns unexpected
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
        // If no user is authenticated (should not happen within authenticated routes, but safe)
        $role = 'Guest'; // Assign 'Guest' role for logic
    }

    // Assuming $menuData is available and has a menu property for looping
    // This data is typically fetched by the Livewire component App\Livewire\Sections\Menu\VerticalMenu
    $menuItems = $menuData->menu ?? [];

    // Retrieve application configuration data, needed for active state logic in menu/submenu
    $configData = Helper::appClasses();

@endphp

{{-- Wrap the content in a div as it's a Livewire component root element --}}
<div>
    <aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">

        {{-- Application Brand/Logo Section --}}
        {{-- ! Hide app brand if navbar-full (assuming $navbarFull is a boolean variable passed or defined) --}}
        @if (!isset($navbarFull) || !$navbarFull)
            {{-- Ensure $navbarFull is false or not set to show brand --}}
            <div class="app-brand demo">
                {{-- Link to your application's root or dashboard --}}
                <a href="{{ url('/') }}" class="app-brand-link">
                    {{-- Include the logo partial --}}
                    <span class="app-brand-logo demo">
                        @include('_partials.macros', ['height' => 20]) {{-- Ensure this path is correct --}}
                    </span>
                    {{-- Application Name from config --}}
                    <span class="app-brand-text demo menu-text fw-bold">{{ config('variables.templateName') }}</span>
                </a>

                {{-- Menu Toggle Button (for mobile/collapsed state) --}}
                {{-- style="visibility: hidden" likely controlled by CSS or JS for responsiveness --}}
                <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto"
                    style="visibility: hidden">
                    {{-- Icons for toggling --}}
                    <i class="ti menu-toggle-icon d-none d-xl-block ti-sm align-middle"></i>
                    <i class="ti ti-x d-block d-xl-none ti-sm align-middle"></i>
                </a>
            </div>
        @endif

        {{-- Menu Inner Shadow --}}
        <div class="menu-inner-shadow"></div>

        {{-- Main Menu Navigation List --}}
        <ul class="menu-inner py-1">
            {{-- Loop through the main menu items --}}
            {{-- Added check for $menuItems being iterable --}}
            @foreach ($menuItems as $menu)
                {{-- Condition to display the main menu item based on the user's determined role --}}
                {{-- Check if the user is 'Admin' OR if the menu item has specific role restrictions (array) and the user's primary role is among them --}}
                @if ($role === 'Admin' || (isset($menu->role) && is_array($menu->role) && in_array($role, $menu->role)))
                    {{-- Handle Menu Headers (e.g., "Human Resources", "Resource Management") --}}
                    @if (isset($menu->menuHeader))
                        <li class="menu-header small text-uppercase">
                            {{-- Display the header text (translated) --}}
                            <span class="menu-header-text">{{ __($menu->menuHeader ?? '') }}</span>
                            {{-- Safely access menuHeader --}}
                        </li>
                    @else
                        {{-- Handle Standard Menu Items or Parent Items with Submenus --}}

                        {{-- Determine active state class based on the current route --}}
                        @php
                            $activeClass = ''; // Initialize active class
                            $currentRouteName = Route::currentRouteName(); // Get current route name safely
                            $isParentActive = false; // Flag to check if this parent item should be 'active open'

                            // Check if the current route name exactly matches the menu item's slug/route name (if it has one)
if (isset($menu->slug) && $currentRouteName === $menu->slug) {
    $activeClass = 'active';
    $isParentActive = true;
}
// If the menu item has a submenu, check if any of its defined slugs match the start of the current route name
elseif (isset($menu->submenu)) {
    $slugsToCheck = is_array($menu->slug)
        ? $menu->slug
        : (isset($menu->slug)
            ? [$menu->slug]
            : []); // Get slugs as array
    foreach ($slugsToCheck as $slug) {
        // Check if the current route name starts with this slug
        if ($currentRouteName && Str::startsWith($currentRouteName, $slug)) {
            // Set 'active open' class based on the layout configuration type
            $activeClass =
                ($configData['layout'] ?? null) === 'vertical' ? 'active open' : 'active';
            $isParentActive = true; // Mark as parent active
            break; // Found a match, no need to check other slugs
        }
    }
    // Alternative/Additional check: Check if any child route matches exactly or starts with current route
    // This might be needed if the parent slug check above isn't comprehensive
                                // This logic could live here or ideally be part of the menu data preparation
                            }
                            // If the menu item is a parent with a specific URL (not just javascript:void(0)),
                            // you might need additional logic to determine if it should be active/open
                            // based on the active state of its children, if the slug check above isn't sufficient.
                        @endphp

                        {{-- Main Menu Item element --}}
                        {{-- Apply 'active' and/or 'open' classes determined above --}}
                        <li class="menu-item {{ $activeClass }}">
                            {{-- Link element for the menu item --}}
                            {{-- Determine href based on url or if it has a submenu (toggle) --}}
                            <a href="{{ isset($menu->url) ? url($menu->url) : (isset($menu->submenu) ? 'javascript:void(0);' : '#') }}"
                                class="{{ isset($menu->submenu) ? 'menu-link menu-toggle' : 'menu-link' }}"
                                @if (isset($menu->target) && !empty($menu->target)) target="_blank" @endif>
                                {{-- Icon for the menu item (optional) --}}
                                @isset($menu->icon)
                                    <i class="{{ $menu->icon }}"></i>
                                @endisset
                                {{-- Title of the menu item (translated) --}}
                                <div data-i18n="{{ $menu->name ?? '' }}">{{ __($menu->name ?? '') }}</div>
                                {{-- Safely access menu name --}}
                                {{-- Optional Badge --}}
                                @isset($menu->badge)
                                    {{-- Assuming badge is an array [color, text] --}}
                                    @if (is_array($menu->badge) && count($menu->badge) === 2)
                                        <div class="badge bg-label-{{ $menu->badge[0] ?? 'primary' }} rounded-pill ms-auto">
                                            {{ $menu->badge[1] ?? '' }}</div>
                                    @endif
                                @endisset
                            </a>

                            {{-- Handle Submenu if it exists --}}
                            @isset($menu->submenu)
                                {{-- Include the submenu partial, explicitly passing $menu->submenu, $role, $user, AND $configData --}}
                                {{-- Ensure the path 'layouts.sections.menu.submenu' is correct --}}
                                @include('layouts.sections.menu.submenu', [
                                    'menu' => $menu->submenu,
                                    'role' => $role,
                                    'user' => $user,
                                    'configData' => $configData, // Pass configData to the submenu partial
                                ])
                            @endisset
                        </li> {{-- End menu-item --}}
                    @endif {{-- End menu header check --}}
                @endif {{-- End main menu role check --}}
            @endforeach {{-- End main menu loop --}}
        </ul> {{-- End menu-inner py-1 --}}

    </aside> {{-- End aside layout-menu --}}
</div> {{-- End Livewire root div --}}
