{{-- This is a main layout file orchestrated by Livewire --}}
{{-- It extends a common master layout and includes Livewire components for sections --}}

@php
    // Assuming Helper is a facade or globally available
    use App\Helpers\Helper; // Explicitly use the Helper facade/class
@endphp

{{-- Update page configurations if provided --}}
@isset($pageConfigs)
    {!! Helper::updatePageConfig($pageConfigs) !!}
@endisset

@php
    // Get application classes configuration safely
    $configData = $configData ?? Helper::appClasses(); // Provide fallback for $configData if not set

    /* Display elements - Provide default values safely */
    $contentNavbar = $contentNavbar ?? true;
    $containerNav = $containerNav ?? 'container-xxl';
    $isNavbar = $isNavbar ?? true;
    $isMenu = $isMenu ?? true;
    $isFlex = $isFlex ?? false;
    $isFooter = $isFooter ?? true;
    $customizerHidden = $customizerHidden ?? '';
    $pricingModal = $pricingModal ?? false;
    $navbarFull = $navbarFull ?? false; // Added fallback for navbarFull if used by child views/components
    $navbarHideToggle = $navbarHideToggle ?? ($navbarDetached ?? 'navbar-detached') !== 'navbar-detached'; // Adjusted logic based on typical theme structure

    /* HTML Classes - Get from configData safely */
    $navbarDetached = 'navbar-detached'; // This seems hardcoded, maybe intended?
    $menuFixed = data_get($configData, 'menuFixed', ''); // Use data_get for safer nested access
    $navbarFixed = data_get($configData, 'navbarFixed', ''); // Use data_get
    $footerFixed = data_get($configData, 'footerFixed', ''); // Use data_get
    $menuCollapsed = data_get($configData, 'menuCollapsed', ''); // Use data_get

    /* Content classes - Provide default safely */
    $container = $container ?? 'container-xxl'; // Already uses ??
@endphp

{{-- Extend the common master layout --}}
@extends('layouts/commonMaster')

{{-- Define the layout content section --}}
@section('layoutContent')
    <div class="layout-wrapper layout-content-navbar {{ $isMenu ? '' : 'layout-without-menu' }}">
        <div class="layout-container">

            {{-- Vertical Menu (Livewire Component) --}}
            @if ($isMenu)
                {{-- @include('layouts/sections/menu/verticalMenu') --}} {{-- Commented out Include --}}
                @livewire('sections.menu.verticalMenu') {{-- Livewire Component --}}
            @endif

            <!-- Layout page -->
            <div class="layout-page">

                {{-- Below commented code read by artisan command while installing jetstream. !! Do not remove if you want to use jetstream. --}}
                <x-banner /> {{-- Jetstream Banner --}}

                <!-- BEGIN: Navbar-->
                @if ($isNavbar)
                    {{-- @include('layouts/sections/navbar/navbar') --}} {{-- Commented out Include --}}
                    {{-- Pass variables needed by the navbar component --}}
                    @livewire('sections.navbar.navbar', ['containerNav' => $containerNav, 'navbarDetached' => $navbarDetached, 'navbarFull' => $navbarFull, 'navbarHideToggle' => $navbarHideToggle]) {{-- Livewire Component with passed vars --}}
                @endif
                <!-- END: Navbar-->

                <!-- Content wrapper -->
                <div class="content-wrapper">

                    <!-- Content -->
                    {{-- Main content area, dynamically sized and padded --}}
                    @if ($isFlex)
                        <div class="{{ $container }} d-flex align-items-stretch flex-grow-1 p-0">
                        @else
                            <div class="{{ $container }} flex-grow-1 container-p-y">
                    @endif

                    {{-- Main content slot (for components or full-page Livewire components) --}}
                    {{-- @yield('content') --}} {{-- Commented out Yield --}}
                    {{ $slot }}

                    <!-- pricingModal -->
                    @if ($pricingModal)
                        @include('_partials/_modals/modal-pricing') {{-- Include modal partial --}}
                    @endif
                    <!--/ pricingModal -->

                </div>
                <!-- / Content -->

                <!-- Footer -->
                @if ($isFooter)
                    {{-- @include('layouts/sections/footer/footer') --}} {{-- Commented out Include --}}
                    @livewire('sections.footer.footer') {{-- Livewire Component --}}
                @endif
                <!-- / Footer -->

                <div class="content-backdrop fade"></div> {{-- Content backdrop --}}
            </div>
            <!--/ Content wrapper -->
        </div>
        <!-- / Layout page -->
    </div>

    {{-- Overlay for mobile menu toggle --}}
    @if ($isMenu)
        <!-- Overlay -->
        <div class="layout-overlay layout-menu-toggle"></div>
    @endif

    <!-- Drag Target Area To SlideIn Menu On Small Screens -->
    <div class="drag-target"></div> {{-- Drag target --}}
    </div>
    <!-- / Layout wrapper -->
@endsection {{-- End layoutContent section --}}

{{-- The commonMaster layout is expected to have @yield('layoutContent') --}}
