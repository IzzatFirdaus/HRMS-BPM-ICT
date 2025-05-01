@php
    // $configData is typically loaded in the layout's view composer
// $menuData is typically loaded in the layout's view composer from verticalMenu.json
    // $userRoles is available from the Livewire component
    /** @var \App\Livewire\Sections\Menu\VerticalMenu $this */
@endphp

{{-- Assuming $menuData is passed to this view, typically from a view composer --}}
@if (isset($menuData))
    <aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">

        <!-- ! Hide app brand if navbar-full -->
        @if (!isset($navbarFull))
            <div class="app-brand demo">
                <a href="{{ url('/') }}" class="app-brand-link">
                    <span class="app-brand-logo demo">
                        {{-- Assuming _partials.macros includes your SVG logo --}}
                        @include('_partials.macros', ['height' => 20])
                    </span>
                    <span class="app-brand-text demo menu-text fw-bold">{{ config('variables.templateName') }}</span>
                </a>

                <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto">
                    <i class="ti menu-toggle-icon d-none d-xl-block ti-sm align-middle"></i>
                    <i class="ti ti-x d-block d-xl-none ti-sm align-middle"></i>
                </a>
            </div>
        @endif

        <div class="menu-inner-shadow"></div>

        <ul class="menu-inner py-1">
            {{-- Iterate through top-level menu items --}}
            @foreach ($menuData->menu as $menu)
                {{-- Check if the user has the required role(s) to see this menu item --}}
                {{-- Using the hasRequiredRole helper method from the Livewire component --}}
                @if ($this->hasRequiredRole($menu->role ?? []))
                    {{-- menu headers --}}
                    @if (isset($menu->menuHeader))
                        <li class="menu-header small text-uppercase">
                            <span class="menu-header-text">{{ __($menu->menuHeader) }}</span>
                        </li>
                    @else
                        {{-- Determine if the menu item or any of its children are active --}}
                        @php
                            $activeClass = null;
                            $currentRouteName = Route::currentRouteName();

                            // Check if the current route name matches the menu item's slug exactly
if ($currentRouteName === ($menu->slug ?? null)) {
    $activeClass = 'active';
}
// Check if the menu item has submenus and if any submenu item matches the current route name
elseif (isset($menu->submenu)) {
    // Check against submenu slugs
    $submenuSlugs = collect($menu->submenu)->pluck('slug')->toArray();
    if (in_array($currentRouteName, $submenuSlugs)) {
        $activeClass = 'active open'; // Mark parent as active and open
    }
    // Optional: Check if the current route name starts with the parent slug (useful for prefixes)
    elseif (
        isset($menu->slug) &&
        is_string($menu->slug) &&
        str_starts_with($currentRouteName, $menu->slug . '.')
    ) {
        $activeClass = 'active open'; // Mark parent as active and open if route name is within its slug prefix
                                }
                            }
                        @endphp

                        {{-- main menu item --}}
                        <li class="menu-item {{ $activeClass }}">
                            <a href="{{ isset($menu->url) ? url($menu->url) : 'javascript:void(0);' }}"
                                class="{{ isset($menu->submenu) ? 'menu-link menu-toggle' : 'menu-link' }}"
                                @if (isset($menu->target) && !empty($menu->target)) target="_blank" @endif>
                                @isset($menu->icon)
                                    <i class="{{ $menu->icon }}"></i>
                                @endisset
                                <div>{{ isset($menu->name) ? __($menu->name) : '' }}</div>
                                @isset($menu->badge)
                                    <div class="badge bg-label-{{ $menu->badge[0] }} rounded-pill ms-auto">
                                        {{ $menu->badge[1] }}</div>
                                @endisset
                            </a>

                            {{-- submenu --}}
                            @isset($menu->submenu)
                                {{-- Include the submenu partial, passing the submenu items and the user's roles --}}
                                @include('layouts.sections.menu.submenu', [
                                    'submenu' => $menu->submenu,
                                    'userRoles' => $this->userRoles,
                                ])
                            @endisset
                        </li>
                    @endif
                @endif {{-- End role check for top-level item --}}
            @endforeach
        </ul>

    </aside>
@endif {{-- End check if $menuData is set --}}


{{-- Note: The second identical <aside> block in the original code is likely a duplicate and should be removed. --}}
{{-- The rendering logic should only happen once per Livewire component output. --}}

{{-- Submenu partial (assuming it's in layouts/sections/menu/submenu.blade.php) --}}
{{--
    resources/views/layouts/sections/menu/submenu.blade.php
    (This is an example of what the submenu partial might look like, assuming it's used recursively)
--}}
@if (isset($submenu))
    <ul class="menu-sub">
        {{-- Iterate through submenu items --}}
        @foreach ($submenu as $menu)
            {{-- Check if the user has the required role(s) to see this submenu item --}}
            {{-- Call the hasRequiredRole helper method on the parent component instance ($this->parent) --}}
            {{-- Need to pass userRoles down or access parent component methods --}}
            {{-- A simpler approach is to pass the userRoles collection down recursively --}}
            @if ($this->hasRequiredRole($menu->role ?? []))
                {{-- Determine if the submenu item or any of its children are active --}}
                @php
                    $activeClass = null;
                    $currentRouteName = Route::currentRouteName();

                    // Check if the current route name matches the submenu item's slug exactly
if ($currentRouteName === ($menu->slug ?? null)) {
    $activeClass = 'active';
}
// Check if the submenu item has nested submenus and if any nested item matches the current route name
elseif (isset($menu->submenu)) {
    // Check against nested submenu slugs recursively
    // This requires a recursive helper or adapting the logic
    // For simplicity here, we'll just check the immediate children slugs
                        $nestedSubmenuSlugs = collect($menu->submenu)->pluck('slug')->toArray();
                        if (in_array($currentRouteName, $nestedSubmenuSlugs)) {
                            $activeClass = 'active open'; // Mark parent submenu item as active and open
                        }
                        // Optional: Check if the current route name starts with the submenu item's slug prefix
    elseif (
        isset($menu->slug) &&
        is_string($menu->slug) &&
        str_starts_with($currentRouteName, $menu->slug . '.')
    ) {
        $activeClass = 'active open'; // Mark parent submenu item as active and open
                        }
                    }
                @endphp

                <li class="menu-item {{ $activeClass }}">
                    <a href="{{ isset($menu->url) ? url($menu->url) : 'javascript:void(0);' }}"
                        class="{{ isset($menu->submenu) ? 'menu-link menu-toggle' : 'menu-link' }}"
                        @if (isset($menu->target) && !empty($menu->target)) target="_blank" @endif>
                        @isset($menu->icon)
                            <i class="{{ $menu->icon }}"></i> {{-- Icons are less common in submenus but included if needed --}}
                        @endisset
                        <div>{{ isset($menu->name) ? __($menu->name) : '' }}</div>
                        @isset($menu->badge)
                            <div class="badge bg-label-{{ $menu->badge[0] }} rounded-pill ms-auto">{{ $menu->badge[1] }}
                            </div>
                        @endisset
                    </a>

                    {{-- Recursively include nested submenus --}}
                    @isset($menu->submenu)
                        @include('layouts.sections.menu.submenu', [
                            'submenu' => $menu->submenu,
                            'userRoles' => $userRoles,
                        ]) {{-- Pass userRoles down --}}
                    @endisset
                </li>
            @endif {{-- End role check for submenu item --}}
        @endforeach
    </ul>
@endif
