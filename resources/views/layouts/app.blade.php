@isset($pageConfigs)
    {{-- Assuming Helper::updatePageConfig exists and is used to apply page-specific configurations --}}
    {!! Helper::updatePageConfig($pageConfigs) !!}
@endisset

@php
    // Helper function assumed to retrieve application class configurations
    $configData = Helper::appClasses();

    // Define variables for layout structure visibility and classes
    // These variables are typically passed down or set in the component/controller rendering this layout
    $contentNavbar = $contentNavbar ?? true; // Controls if the content navbar is shown
    $containerNav = $containerNav ?? 'container-xxl'; // Class for the main content container
    $isNavbar = $isNavbar ?? true; // Controls if the main navbar is shown
    $isMenu = $isMenu ?? true; // Controls if the vertical menu is shown
    $isFlex = $isFlex ?? false; // Controls layout flex properties
    $isFooter = $isFooter ?? true; // Controls if the footer is shown
    $customizerHidden = $customizerHidden ?? ''; // Class for hiding customizer
    $pricingModal = $pricingModal ?? false; // Controls if a pricing modal is included

    /* HTML Classes based on config */
    $navbarDetached = 'navbar-detached'; // Example class, might depend on config
    $menuFixed = isset($configData['menuFixed']) ? $configData['menuFixed'] : ''; // Class for fixed menu
    $navbarFixed = isset($configData['navbarFixed']) ? $configData['navbarFixed'] : ''; // Class for fixed navbar
    $footerFixed = isset($configData['footerFixed']) ? $configData['footerFixed'] : ''; // Class for fixed footer
    $menuCollapsed = isset($configData['menuCollapsed']) ? $configData['menuCollapsed'] : ''; // Class for collapsed menu

    /* Content classes */
    $container = $container ?? 'container-xxl'; // Class for the main content wrapper

@endphp

{{-- Extends the base common master layout --}}
@extends('layouts/commonMaster')

{{-- Start of the layout content section defined in commonMaster --}}
@section('layoutContent')
    {{-- Layout Wrapper with classes based on $isMenu --}}
    <div class="layout-wrapper layout-content-navbar {{ $isMenu ? '' : 'layout-without-menu' }}">
        {{-- Layout Container --}}
        <div class="layout-container">

            {{-- Vertical Menu (conditional based on $isMenu) --}}
            @if ($isMenu)
                {{-- Render the vertical menu Livewire component --}}
                {{-- The component view (vertical-menu.blade.php) handles its own data fetching ($menuData) and role checks --}}
                @livewire('sections.menu.verticalMenu')
            @endif

            <div class="layout-page">

                {{-- Jetstream Banner Component (commented out note) --}}
                {{-- Below commented code read by artisan command while installing jetstream. !! Do not remove if you want to use jetstream. --}}
                <x-banner />

                {{-- Main Navbar (conditional based on $isNavbar) --}}
                @if ($isNavbar)
                    {{-- Render the navbar Livewire component --}}
                    {{-- The component view handles its own data fetching --}}
                    @livewire('sections.navbar.navbar')
                @endif
                <div class="content-wrapper">

                    {{-- Main Content Area --}}
                    {{-- Container div with classes based on $container and $isFlex --}}
                    @if ($isFlex)
                        <div class="{{ $container }} d-flex align-items-stretch flex-grow-1 p-0">
                        @else
                            <div class="{{ $container }} flex-grow-1 container-p-y">
                    @endif

                    {{-- Render the content of the specific page view using the $slot variable --}}
                    {{ $slot }}

                    {{-- Optional Pricing Modal Include --}}
                    @if ($pricingModal)
                        @include('_partials/_modals/modal-pricing') {{-- Ensure this path is correct --}}
                    @endif
                </div>
                {{-- Main Footer (conditional based on $isFooter) --}}
                @if ($isFooter)
                    {{-- Render the footer Livewire component --}}
                    {{-- The component view handles its own content --}}
                    @livewire('sections.footer.footer')
                @endif
                {{-- Content Backdrop (often used for mobile menu overlay) --}}
                <div class="content-backdrop fade"></div>
            </div>
        </div>
    </div>

    {{-- Layout Overlay (often used with offcanvas menus) --}}
    @if ($isMenu)
        <div class="layout-overlay layout-menu-toggle"></div>
    @endif

    {{-- Drag Target Area (often for swiping menus on touch devices) --}}
    <div class="drag-target"></div>
    </div>
@endsection {{-- End of layoutContent section --}}

{{-- Any scripts pushed to stacks (like 'custom-scripts') will be rendered here if the commonMaster layout has @stack directives --}}

{{-- Note: Scripts for initializing template-specific features (like menu toggles, perfect scrollbar, etc.)
     are typically included in the commonMaster layout or via @push directives. --}}
