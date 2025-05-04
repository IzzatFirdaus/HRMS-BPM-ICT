{{--
    resources/views/layouts/app.blade.php

    This layout file is designed to be extended by views using @extends and @section directives.
    It integrates with a common master layout ('layouts/commonMaster') and includes Livewire components
    for the main structural elements like the menu, navbar, and footer.
    It uses the App\Helpers\Helpers class for layout configuration and dynamic class application.
--}}

@php
    // Ensure the Helpers class is imported and available
    use App\Helpers\Helpers;
@endphp

{{-- Apply page-specific configurations if provided --}}
@isset($pageConfigs)
    {!! Helpers::updatePageConfig($pageConfigs) !!}
@endisset

@php
    // Retrieve application layout classes and configurations
    // Provide a fallback to the Helpers class if configData is not already set
    $configData = $configData ?? Helpers::appClasses();

    /* Layout Structure Visibility and Classes - Provide default values safely */
    // These variables control which layout elements are displayed and their base classes.
    // They can be set in the view using this layout to customize the page.
    $contentNavbar = $contentNavbar ?? true; // Controls if the content navbar area is shown
    $containerNav = $containerNav ?? 'container-xxl'; // Default class for the main navbar container width (Bootstrap)
    $isNavbar = $isNavbar ?? true; // Controls if the entire navbar component is shown
    $isMenu = $isMenu ?? true; // Controls if the vertical menu component is shown
    $isFlex = $isFlex ?? false; // Controls if the main content area uses flex properties
    $isFooter = $isFooter ?? true; // Controls if the footer component is shown
    $customizerHidden = $customizerHidden ?? ''; // Class to potentially hide a layout customizer element
    $pricingModal = $pricingModal ?? false; // Include a pricing modal partial if true
    $navbarFull = $navbarFull ?? false; // Specific flag for the navbar component

    // Determine if the navbar hide toggle should be active based on the navbarDetached class
    // Provides a default value, useful for responsive behavior
    $navbarHideToggle = $navbarHideToggle ?? ($navbarDetached ?? 'navbar-detached') !== 'navbar-detached';

    /* HTML Classes from Configuration - Get safely from $configData or provide defaults */
    // These classes likely control fixed/sticky positions and collapsed states, often theme-specific (Bootstrap)
    $navbarDetached = 'navbar-detached'; // This appears to be a hardcoded class for a specific layout style
    $menuFixed = data_get($configData, 'menuFixed', ''); // Get class for fixed menu
    $navbarFixed = data_get($configData, 'navbarFixed', ''); // Get class for fixed navbar
    $footerFixed = data_get($configData, 'footerFixed', ''); // Get class for fixed footer
    $menuCollapsed = data_get($configData, 'menuCollapsed', ''); // Get class for collapsed menu

    /* Main Content Container Class - Provide default safely */
    $container = $container ?? 'container-xxl'; // Default class for the main content area container width (Bootstrap)

@endphp

{{-- Extend the common master layout --}}
{{-- This master layout ('layouts/commonMaster') should define the base HTML structure and have @yield('layoutContent') --}}
@extends('layouts/commonMaster')

{{-- Define the section where the content of this layout will be placed in the commonMaster --}}
@section('layoutContent')
    {{-- Main layout wrapper div. Classes control overall layout behavior (Bootstrap) --}}
    <div class="layout-wrapper layout-content-navbar {{ $isMenu ? '' : 'layout-without-menu' }}">
        {{-- Inner container for the main layout sections --}}
        <div class="layout-container">

            {{-- Vertical Menu - Rendered as a Livewire component if $isMenu is true --}}
            {{-- The Livewire component ('sections.menu.verticalMenu') handles its own data and rendering --}}
            @if ($isMenu)
                @livewire('sections.menu.verticalMenu')
            @endif

            {{-- Container for the page content area (navbar, content, footer) --}}
            <div class="layout-page">

                {{-- Jetstream Banner Component (typically displays success/error messages) --}}
                {{-- This component is often required by Jetstream for its built-in features --}}
                <x-banner />

                {{-- Main Navbar - Rendered as a Livewire component if $isNavbar is true --}}
                {{-- Pass necessary layout variables to the navbar component --}}
                @if ($isNavbar)
                    @livewire('sections.navbar.navbar', [
                        'containerNav' => $containerNav,
                        'navbarDetached' => $navbarDetached,
                        'navbarFull' => $navbarFull,
                        'navbarHideToggle' => $navbarHideToggle,
                    ])
                @endif

                {{-- Wrapper for the main content area and footer --}}
                <div class="content-wrapper">

                    {{-- Main Content Area --}}
                    {{-- Container div with classes controlling width and padding (Bootstrap) --}}
                    {{-- The classes change based on the $isFlex variable --}}
                    @if ($isFlex)
                        <div class="{{ $container }} d-flex align-items-stretch flex-grow-1 p-0">
                        @else
                            <div class="{{ $container }} flex-grow-1 container-p-y">
                    @endif

                    {{-- The main content from the @section('content') in the extending view is rendered here --}}
                    @yield('content') {{-- CORRECTED: Changed from {{ $slot }} to @yield('content') --}}

                    {{-- Include pricing modal partial if $pricingModal is true --}}
                    @if ($pricingModal)
                        @include('_partials/_modals/modal-pricing')
                    @endif

                </div> {{-- Close main content container --}}

                {{-- Main Footer - Rendered as a Livewire component if $isFooter is true --}}
                {{-- The Livewire component ('sections.footer.footer') handles its own content --}}
                @if ($isFooter)
                    @livewire('sections.footer.footer')
                @endif

                {{-- Content backdrop (used for overlays, often with collapsed menus on mobile) (Bootstrap) --}}
                <div class="content-backdrop fade"></div>

            </div> {{-- End content-wrapper --}}
        </div> {{-- End layout-page --}}
    </div> {{-- End layout-container --}}

    {{-- Layout Overlay (used for screen dimming when menu is open on mobile) (Bootstrap) --}}
    @if ($isMenu)
        <div class="layout-overlay layout-menu-toggle"></div>
    @endif

    {{-- Drag target element (often used for swiping to open menus on touch devices) (Bootstrap) --}}
    <div class="drag-target"></div>

    </div> {{-- End layout-wrapper --}}
@endsection {{-- End of layoutContent section --}}

{{--
    Note: Scripts specific to this template (like menu initialization, scrollbars, etc.)
    are typically included in the commonMaster layout or pushed to stacks within commonMaster.
    Ensure commonMaster has @stack directives (e.g., @stack('scripts'), @stack('page-scripts'))
    in the appropriate location (usually before the closing </body> tag) to include scripts
    pushed by views using this layout via @push('scripts').
--}}
