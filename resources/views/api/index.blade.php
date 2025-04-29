{{--
    This file likely serves as a wrapper view for the Livewire API Token Manager component.
    It extends a master layout and sets up basic page elements like title and breadcrumbs.
    Assuming this view is located at resources/views/profile/api-tokens.blade.php or similar.
--}}

{{-- Extend the main master layout --}}
@extends('layouts/layoutMaster')

@php
    // Define breadcrumbs array for navigation
    $breadcrumbs = [
        // Link to the home route with translated name
        ['link' => route('home'), 'name' => __('Home')], // Assuming 'home' is your application's main home route name
    // Current page name with translation
    ['name' => __('API Tokens')],
    ];
@endphp

{{-- Set the page title using translation --}}
@section('title', __('API Tokens'))


@section('page-style')
    {{-- Include page-specific CSS files --}}
    {{-- Ensure the path to your CSS file is correct --}}
    <link rel="stylesheet" href="{{ asset(mix('assets/vendor/css/pages/page-auth.css')) }}">
    {{-- You might need additional styles specific to API token management --}}
    {{-- <link rel="stylesheet" href="{{ asset('path/to/your/api-tokens-styles.css') }}"> --}}
@endsection

@section('content')
    {{-- Render the Livewire API Token Manager component --}}
    {{-- This component contains the main logic and UI for managing API tokens --}}
    @livewire('api.api-token-manager')
@endsection
