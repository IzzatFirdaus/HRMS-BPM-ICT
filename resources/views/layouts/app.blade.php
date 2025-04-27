@isset($pageConfigs)
    {!! Helper::updatePageConfig($pageConfigs) !!}
@endisset

@php
    $configData = Helper::appClasses();
@endphp

{{--
    This layout file extends a common master layout and provides the structure
    for the main application content, including menu, navbar, and footer,
    often used with Livewire components.
--}}
@extends('layouts/commonMaster')

@php
    /* Display elements */
    $contentNavbar = $contentNavbar ?? true;
    $containerNav = $containerNav ?? 'container-xxl';
    $isNavbar = $isNavbar ?? true;
    $isMenu = $isMenu ?? true;
    $isFlex = $isFlex ?? false;
    $isFooter = $isFooter ?? true;
    $customizerHidden = $customizerHidden ?? '';
    $pricingModal = $pricingModal ?? false;

    /* HTML Classes */
    $navbarDetached = 'navbar-detached';
    $menuFixed = isset($configData['menuFixed']) ? $configData['menuFixed'] : '';
    $navbarFixed = isset($configData['navbarFixed']) ? $configData['navbarFixed'] : '';
    $footerFixed = isset($configData['footerFixed']) ? $configData['footerFixed'] : '';
    $menuCollapsed = isset($configData['menuCollapsed']) ? $configData['menuCollapsed'] : '';

    /* Content classes */
    $container = $container ?? 'container-xxl';
@endphp

{{--
    Add necessary styles here.
    If your commonMaster layout has a @stack('styles') in the <head>,
    you can push styles there. Otherwise, you might need to add the Tailwind
    CDN link directly here or ensure your asset compilation includes Tailwind.
    For simplicity, adding CDN here. Ensure this ends up in the <head> section
    rendered by commonMaster.
--}}
@section('styles')
    @parent {{-- Include styles from the parent layout --}}
    {{-- Include Tailwind CSS via CDN. In production, you should compile Tailwind with your assets. --}}
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@^2.0/dist/tailwind.min.css" rel="stylesheet">

    {{--
        Include custom styles for components like badges, cards etc.
        if they are not part of your main compiled CSS or Tailwind setup.
        These styles seem to mimic some common UI components.
    --}}
    <style>
        .alert {
            padding: 1rem;
            border-radius: 0.25rem;
            margin-bottom: 1rem;
            border-width: 1px;
        }

        .alert-success {
            background-color: #d1fae5;
            border-color: #a7f3d0;
            color: #065f46;
        }

        .alert-danger {
            background-color: #fee2e2;
            border-color: #fecaca;
            color: #991b1b;
        }

        .alert-info {
            background-color: #e0f2f7;
            border-color: #bae6fd;
            color: #0e7490;
        }

        .card {
            border: 1px solid #d1d5db;
            border-radius: 0.5rem;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            background-color: #fff;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
        }

        .card-title {
            font-size: 1.25rem;
            font-weight: bold;
            margin-bottom: 1rem;
            color: #1f2937;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .form-control {
            width: 100%;
            padding: 0.5rem 0.75rem;
            border: 1px solid #d1d5db;
            border-radius: 0.25rem;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.5rem 1.25rem;
            border-radius: 0.375rem;
            font-weight: 600;
            transition: background-color 0.15s ease-in-out;
            cursor: pointer;
            /* Add cursor pointer for better UX */
        }

        .btn-primary {
            background-color: #3b82f6;
            color: #fff;
        }

        .btn-secondary {
            background-color: #e5e7eb;
            color: #1f2937;
        }

        .btn-success {
            background-color: #48bb78;
            color: #fff;
        }

        .btn-danger {
            background-color: #f56565;
            color: #fff;
        }

        .btn-info {
            background-color: #38b2ac;
            color: #fff;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
            line-height: 1;
            text-transform: capitalize;
        }

        .badge-info {
            background-color: #bfdbfe;
            color: #1e40af;
        }

        .badge-success {
            background-color: #d1fae5;
            color: #065f46;
        }

        .badge-warning {
            background-color: #fef3c7;
            color: #b45309;
        }

        .badge-danger {
            background-color: #fee2e2;
            color: #991b1b;
        }

        .badge-secondary {
            background-color: #e5e7eb;
            color: #374151;
        }

        .badge-teal {
            background-color: #b2f5ea;
            color: #2c7a7b;
        }

        /* Custom badge for 'issued' */
        .badge-purple {
            background-color: #e9d8fd;
            color: #6b46c1;
        }

        /* Custom badge for 'returned' */
        .badge-red {
            background-color: #feb2b2;
            color: #c53030;
        }

        /* Custom badge for 'overdue' */
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }

        .table th,
        .table td {
            padding: 0.75rem;
            border: 1px solid #e5e7eb;
            text-align: left;
        }

        .table th {
            background-color: #f9fafb;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 0.75rem;
            color: #4b5563;
        }

        .table tbody tr:nth-child(odd) {
            background-color: #f9fafb;
        }

        .table tbody tr:hover {
            background-color: #f3f4f6;
        }

        .item-list {
            list-style: disc;
            padding-left: 1.5rem;
        }

        .item-list li {
            margin-bottom: 0.25rem;
        }
    </style>
@endsection


@section('layoutContent')
    <div class="layout-wrapper layout-content-navbar {{ $isMenu ? '' : 'layout-without-menu' }}">
        <div class="layout-container">

            @if ($isMenu)
                {{-- @include('layouts/sections/menu/verticalMenu') --}}
                @livewire('sections.menu.verticalMenu')
            @endif

            <div class="layout-page">

                {{-- Below commented code read by artisan command while installing jetstream. !! Do not remove if you want to use jetstream. --}}
                <x-banner />

                @if ($isNavbar)
                    {{-- @include('layouts/sections/navbar/navbar') --}}
                    @livewire('sections.navbar.navbar')
                @endif
                <div class="content-wrapper">

                    @if ($isFlex)
                        <div class="{{ $container }} d-flex align-items-stretch flex-grow-1 p-0">
                        @else
                            <div class="{{ $container }} flex-grow-1 container-p-y">
                    @endif

                    {{--
                        Section for displaying session flash messages (notifications).
                        These are typically set in controllers after an action (e.g., success, error).
                    --}}
                    @if (session()->has('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if (session()->has('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                    @endif

                    @if (session()->has('info'))
                        <div class="alert alert-info">
                            {{ session('info') }}
                        </div>
                    @endif

                    {{--
                        This slot renders the main content of the page, which is typically
                        the view for the current route (e.g., from your controllers).
                    --}}
                    {{ $slot }}

                    @if ($pricingModal)
                        @include('_partials/_modals/modal-pricing')
                    @endif
                </div>
                @if ($isFooter)
                    {{-- @include('layouts/sections/footer/footer') --}}
                    @livewire('sections.footer.footer')
                @endif
                <div class="content-backdrop fade"></div>
            </div>

        </div>
    </div>

    @if ($isMenu)
        <div class="layout-overlay layout-menu-toggle"></div>
    @endif

    <div class="drag-target"></div>
    </div>
@endsection
