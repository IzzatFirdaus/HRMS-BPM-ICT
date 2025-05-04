{{-- resources/views/layouts/sections/menu/submenu.blade.php --}}

{{-- This partial is used to render nested menu items recursively. --}}
{{-- It expects $submenu (an array of menu items) and $userRoles (for active state logic). --}}
{{-- It expects $canSee (boolean) to determine if the item should be displayed. --}}

@php
    // $submenu and $userRoles are passed down from the parent menu view.
    // $canSee is passed down from the parent, indicating if the current item should be shown based on roles.
    use Illuminate\Support\Facades\Route; // Import Route facade for active state check
    use Illuminate\Support\Facades\Auth; // Import Auth facade for active state check (if needed, although userRoles is passed)

    // For active state logic, $userRoles is needed.

@endphp

@if (isset($submenu) && count($submenu) > 0)
    <ul class="menu-sub">
        {{-- Loop through the items in the current submenu --}}
        @foreach ($submenu as $menuItem)
            {{-- Use the passed $canSee boolean to determine visibility --}}
            {{-- If $canSee is true, render the item and its submenu. --}}
            {{-- Note: The parent view (verticalMenu.blade.php) is responsible for calculating $canSee --}}
            @php
                // For items within a submenu, we need to recalculate whether *this* specific item is visible
                // based on its own roles. The $canSee passed from the parent only determined if the parent was visible.
                // Let's revert to checking roles directly within the submenu, but carefully.
// Replicating the logic from VerticalMenu::hasRequiredRole() using passed $userRoles:
$itemCanSee =
    $userRoles->contains('Admin') ||
                    (empty($menuItem->roles ?? [])
                        ? Auth::check()
                        : $userRoles->intersect(collect($menuItem->roles))->isNotEmpty());
            @endphp

            {{-- Only render the menu item if itemCanSee is true --}}
            @if ($itemCanSee)
                @php
                    /**
                     * Determine active/open state for the submenu item.
                     * - 'active' class for the current page link.
                     * - 'active open' for a parent item whose submenu contains the current page link.
                     */
                    $activeClass = null;
                    $currentRouteName = Route::currentRouteName(); // Get the current route name

                    // Check if the current menu item's slug matches the current route name
if (($menuItem->slug ?? null) === $currentRouteName) {
    $activeClass = 'active';
} elseif (isset($menuItem->submenu)) {
    // If the item has a submenu, check if any of its children match the current route
    // The parent should be 'active open' if a child route is active.
    // This requires checking if the current route name starts with the parent's slug prefix,
                        // or if the current route name is among an array of child route names defined in the parent's slug.
    // This logic could be simplified/improved, but matching the provided structure.
    if (isset($menuItem->slug) && is_string($menuItem->slug)) {
        if (str_starts_with($currentRouteName, $menuItem->slug)) {
            $activeClass = 'active open';
        }
    } elseif (is_array($menuItem->slug)) {
        if (in_array($currentRouteName, $menuItem->slug)) {
            $activeClass = 'active open';
                            }
                        }
                        // Note: A more robust active state logic might recursively check the submenu items
                        // for the active route, but this logic is common and works for simple cases.
                    }
                @endphp

                {{-- Render the menu item (link) --}}
                <li class="menu-item {{ $activeClass }}">
                    {{-- Link URL: Use the 'url' attribute if present, otherwise a dummy link for parent items --}}
                    <a href="{{ isset($menuItem->url) ? url($menuItem->url) : 'javascript:void(0)' }}"
                        {{-- Add 'menu-toggle' class if the item has a submenu --}}
                        class="{{ isset($menuItem->submenu) ? 'menu-link menu-toggle' : 'menu-link' }}"
                        {{-- Open in a new tab if 'target' attribute is set --}} @if (!empty($menuItem->target)) target="_blank" @endif>
                        {{-- Optional: Include an icon if 'icon' attribute is set --}}
                        @if (!empty($menuItem->icon))
                            <i class="{{ $menuItem->icon }}"></i> {{-- Icons are less common in submenus but included if needed --}}
                        @endif
                        {{-- Display the menu item name (translated) --}}
                        <div>{{ isset($menuItem->name) ? __($menuItem->name) : '' }}</div>
                        {{-- Optional: Display a badge if 'badge' attribute is set --}}
                        @isset($menuItem->badge)
                            <div class="badge bg-label-{{ $menuItem->badge[0] }} rounded-pill ms-auto">
                                {{ $menuItem->badge[1] }}
                            </div>
                        @endisset
                    </a>

                    {{-- Recursively include nested submenus --}}
                    @if (isset($menuItem->submenu))
                        {{-- Pass the $userRoles down to the nested submenu --}}
                        @include('layouts.sections.menu.submenu', [
                            'submenu' => $menuItem->submenu,
                            'userRoles' => $userRoles, // Keep passing userRoles down
                        ])
                    @endif
                </li>
            @endif {{-- End itemCanSee check for submenu item --}}
        @endforeach
    </ul>
@endif
