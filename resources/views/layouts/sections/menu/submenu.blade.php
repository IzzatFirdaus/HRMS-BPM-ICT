{{--
    resources/views/layouts/sections/menu/submenu.blade.php

    This partial is used to render nested menu items recursively.
    It expects $submenu (an array of menu items) and $userRoles (Collection of role names).
    It calculates the visibility of each item based on the passed roles and renders it if visible.
    It uses request()->routeIs() for determining active state.
--}}

@php
    // $submenu (array of menu items) and $userRoles (Collection of role names)
    // are passed down from the parent menu view (vertical-menu.blade.php) or a recursive call to this partial.

    // Import necessary facades
    use Illuminate\Support\Facades\Route; // For active state check
    use Illuminate\Support\Facades\Auth; // For authenticated check in role logic (optional, userRoles implies auth)
    use Illuminate\Support\Collection; // For type hinting and collection methods

    // Ensure $userRoles is a Collection, fallback to empty if somehow not passed correctly
    $userRoles = $userRoles ?? collect();

@endphp

{{-- Check if the submenu exists and has items to render a <ul> --}}
@if (isset($submenu) && count($submenu) > 0)
    <ul class="menu-sub">
        {{-- Loop through each item in the current submenu level --}}
        @foreach ($submenu as $menuItem)
            @php
                // --- Role-Based Visibility Check for this specific submenu item ---
                // This logic MUST replicate the hasRequiredRole method in the VerticalMenu Livewire component,
                // but uses the $userRoles collection passed down instead of $this->userRoles.

                $itemCanSee = false; // Default to not visible

                // Rule 1: Admin role (Super Admin) sees everything.
                // Check if the user's roles collection (passed as $userRoles) contains the 'Admin' role.
if ($userRoles->contains('Admin')) {
    $itemCanSee = true;
} else {
    // If not Admin, check specific roles required by the menu item.
    // Access the 'roles' property of the menu item object/array.
    // Ensure $menuItem->roles is treated as a collection. Handle null or missing 'roles' property.
    $requiredRolesCollection = collect($menuItem->roles ?? []);

    // Rule 2: If the menu item has NO specific role restriction defined (empty array or null 'roles'),
    // assume it's visible to all authenticated users.
                    // Since this partial is typically included within views rendered by authenticated routes, Auth::check() should generally be true here.
                    if ($requiredRolesCollection->isEmpty()) {
                        $itemCanSee = Auth::check(); // Only show to authenticated users if no roles are specified for the item
                    } else {
                        // Rule 3: If specific roles ARE defined, check if the authenticated user has at least one of them.
                        // Find the intersection of the user's roles ($userRoles) and the required roles ($requiredRolesCollection).
                        $intersectingRoles = $userRoles->intersect($requiredRolesCollection);

                        // If the intersection is not empty, the user has at least one required role.
                        $itemCanSee = $intersectingRoles->isNotEmpty();
                    }
                }
                // --- End Role-Based Visibility Check ---
            @endphp

            {{-- Only render the menu item if itemCanSee is true based on the role check --}}
            @if ($itemCanSee)
                @php
                    /**
                     * Determine active/open state for the submenu item.
                     * - 'active' class for the current page link.
                     * - 'active open' for a parent item whose submenu contains the current page link.
                     *
                     * This logic relies on matching the current route name (from Route::currentRouteName())
                     * against the 'slug' property of the menu item, which is assumed to be the route name
                     * for direct links, or a prefix/array of child route names for parent items.
                     */
                    $activeClass = null; // Initialize active state class
                    $currentRouteName = Route::currentRouteName(); // Get the current route name

                    // Check if the current menu item's 'slug' (route name) matches the current application route name
// Use null coalescing operator for safety in case 'slug' is missing on a menu item
if (($menuItem->slug ?? null) === $currentRouteName) {
    $activeClass = 'active'; // Mark as active if the slug is the current route name
} elseif (isset($menuItem->submenu)) {
    // If the item has a submenu, check if any of its children or routes under its prefix
    // match the current route to mark the parent as 'active open'.
    // This check assumes the parent's 'slug' is either a route name prefix (string)
                        // or an array of specific child route names.

                        if (isset($menuItem->slug)) {
                            if (is_string($menuItem->slug) && str_starts_with($currentRouteName, $menuItem->slug)) {
                                // Mark as active open if the current route name starts with the parent's slug string
            $activeClass = 'active open';
        } elseif (is_array($menuItem->slug) && in_array($currentRouteName, $menuItem->slug)) {
            // Mark as active open if the current route name is in the parent's slug array
                                $activeClass = 'active open';
                            }
                            // Note: A more robust active state check for parent items might involve recursively
                            // checking the *actual* routes linked in the $menuItem->submenu tree.
                        }
                    }
                @endphp

                {{-- Render the menu item as a list item --}}
                <li class="menu-item {{ $activeClass }}">
                    {{-- Render the link within the list item --}}
                    {{-- Determine the href: Use route() if slug is a valid route name, url() if url is provided, fallback to javascript:void(0) --}}
                    @php
                        $linkHref = 'javascript:void(0)'; // Default dummy link for parent items or items without url/slug

                        if (isset($menuItem->slug)) {
                            // If 'slug' is provided, assume it's a route name and try to generate a URL.
    // Check if the route name exists before using route() to prevent errors.
    try {
        if (is_string($menuItem->slug) && Route::has($menuItem->slug)) {
            $linkHref = route($menuItem->slug);
        } else {
            // If slug is not a valid route name, maybe it's intended as a raw URL path?
                                    // Fallback to url() if slug looks like a path (starts with /)
                                    if (is_string($menuItem->slug) && str_starts_with($menuItem->slug, '/')) {
                                        $linkHref = url($menuItem->slug);
                                    } else {
                                        // Optional: Log a warning if slug exists but isn't a registered route or a simple path
                // Log::warning("Menu item slug '{$menuItem->slug}' is not a registered route or a valid URL path. Item name: " . ($menuItem->name ?? 'N/A'));
            }
        }
    } catch (\Throwable $e) {
        // Catch any other potential errors from route() helper (e.g., missing parameters)
        // Log::error("Error generating route for menu item slug '{$menuItem->slug}': " . $e->getMessage());
        // Fallback to a placeholder or dummy link
        $linkHref = '#'; // Use '#' or 'javascript:void(0)' as a fallback
    }
} elseif (isset($menuItem->url)) {
    // If only 'url' attribute is provided, use url() helper
                            $linkHref = url($menuItem->url);
                        }
                    @endphp
                    <a href="{{ $linkHref }}" {{-- Add 'menu-toggle' class if the item has a submenu to enable expansion/collapse --}}
                        class="{{ isset($menuItem->submenu) ? 'menu-link menu-toggle' : 'menu-link' }}"
                        {{-- Open in a new tab if 'target' attribute is set and not empty --}} @if (!empty($menuItem->target)) target="_blank" @endif>

                        {{-- Optional: Include an icon if 'icon' attribute is set and not empty --}}
                        @isset($menuItem->icon)
                            <i class="{{ $menuItem->icon }}"></i> {{-- Render icon element --}}
                        @endisset

                        {{-- Display the menu item name (translated) --}}
                        {{-- Use null coalescing operator for safety --}}
                        <div>{{ isset($menuItem->name) ? __($menuItem->name) : '' }}</div>

                        {{-- Optional: Display a badge if 'badge' attribute is set and not empty --}}
                        @isset($menuItem->badge)
                            {{-- Assuming badge is an array [type, text], e.g., ['danger', 'New'] --}}
                            <div class="badge bg-label-{{ $menuItem->badge[0] }} rounded-pill ms-auto">
                                {{ $menuItem->badge[1] }}
                            </div>
                        @endisset
                    </a>

                    {{-- Recursively include nested submenus --}}
                    @if (isset($menuItem->submenu) && is_array($menuItem->submenu))
                        {{-- Pass the $userRoles and any other necessary variables down to the nested submenu partial --}}
                        @include('layouts.sections.menu.submenu', [
                            'submenu' => $menuItem->submenu, // Pass the next level of submenu items
                            'userRoles' => $userRoles, // Keep passing userRoles down
                            // Ensure other variables needed by submenu (like $configData if used for layout) are passed
                            // 'configData' => $configData ?? null, // Example if configData is needed recursively
                        ])
                    @endif
                </li>
            @endif {{-- End itemCanSee check for submenu item --}}
        @endforeach
    </ul>
@endif
