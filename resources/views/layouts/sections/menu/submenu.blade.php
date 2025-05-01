{{-- resources/views/layouts/sections/menu/submenu.blade.php --}}

@if (isset($submenu) && count($submenu) > 0)
    <ul class="menu-sub">
        @foreach ($submenu as $menuItem)
            @php
                /** @var \App\Livewire\Sections\Menu\VerticalMenu $this */
                $activeClass = null;
                $currentRouteName = Route::currentRouteName();

                if (($menuItem->slug ?? null) === $currentRouteName) {
                    $activeClass = 'active';
                } elseif (isset($menuItem->submenu)) {
                    if (isset($menuItem->slug) && is_string($menuItem->slug)) {
                        if (str_starts_with($currentRouteName, $menuItem->slug)) {
                            $activeClass = 'active open';
                        }
                    } elseif (is_array($menuItem->slug)) {
                        if (in_array($currentRouteName, $menuItem->slug)) {
                            $activeClass = 'active open';
                        }
                    }
                }
            @endphp

            <li class="menu-item {{ $activeClass }}">
                <a href="{{ isset($menuItem->url) ? url($menuItem->url) : 'javascript:void(0)' }}"
                    class="{{ isset($menuItem->submenu) ? 'menu-link menu-toggle' : 'menu-link' }}"
                    @if (!empty($menuItem->target)) target="_blank" @endif>
                    @if (!empty($menuItem->icon))
                        <i class="{{ $menuItem->icon }}"></i>
                    @endif
                    <div>{{ isset($menuItem->name) ? __($menuItem->name) : '' }}</div>
                    @isset($menuItem->badge)
                        <div class="badge bg-label-{{ $menuItem->badge[0] }} rounded-pill ms-auto">
                            {{ $menuItem->badge[1] }}
                        </div>
                    @endisset
                </a>

                {{-- Recursively include nested submenus --}}
                @if (isset($menuItem->submenu))
                    @include('layouts.sections.menu.submenu', [
                        'submenu' => $menuItem->submenu,
                        'userRoles' => $userRoles,
                    ])
                @endif
            </li>
        @endforeach
    </ul>
@endif
