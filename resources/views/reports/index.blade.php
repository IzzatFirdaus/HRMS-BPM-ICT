{{--
    resources/views/reports/index.blade.php

    This Blade view displays a list of available reports with links to them.
    It assumes that report routes are correctly named and accessible.
--}}
@extends('layouts.app') {{-- Assumes a layout file named 'app.blade.php' exists --}}

{{-- Set the page title --}}
@section('title', __('Available Reports')) {{-- Translated Page Title --}}

{{-- Define the content section --}}
@section('content')

    <div class="container mx-auto px-4 py-6"> {{-- Basic container with Tailwind padding --}}

        {{-- Page Title --}}
        <h2 class="text-2xl font-bold mb-6 text-gray-800">{{ __('Available Reports') }}</h2> {{-- Translated Page Title --}}

        {{-- Grid for report cards/links --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

            {{-- Equipment Report Link --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-xl font-semibold mb-3 text-gray-800">{{ __('Equipment Report') }}</h3> {{-- Translated title --}}
                <p class="text-gray-600 mb-4">{{ __('View reports on equipment loan applications.') }}</p>
                {{-- Translated description --}}
                {{-- CORRECTED ROUTE NAME: Use the full registered name 'admin.reports.equipment' --}}
                {{-- @can('viewEquipment', App\Http\Controllers\ReportController::class) --}} {{-- Optional: Check policy for visibility --}}
                <a href="{{ route('admin.reports.equipment') }}" class="btn btn-primary"> {{-- Assuming 'btn btn-primary' Tailwind classes are defined or custom CSS handles them --}}
                    {{ __('View Report') }} {{-- Translated button text --}}
                </a>
                {{-- @endcan --}}
            </div>

            {{-- Email Accounts Report Link --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-xl font-semibold mb-3 text-gray-800">{{ __('Email Accounts Report') }}</h3>
                {{-- Translated title --}}
                <p class="text-gray-600 mb-4">{{ __('View reports on email account applications.') }}</p>
                {{-- Translated description --}}
                {{-- CORRECTED ROUTE NAME: Use the full registered name 'admin.reports.email-accounts' (correcting casing too) --}}
                {{-- @can('viewEmailAccounts', App\Http\Controllers\ReportController::class) --}} {{-- Optional: Check policy for visibility --}}
                <a href="{{ route('admin.reports.email-accounts') }}" class="btn btn-primary"> {{-- Assuming 'btn btn-primary' Tailwind classes are defined or custom CSS handles them --}}
                    {{ __('View Report') }} {{-- Translated button text --}}
                </a>
                {{-- @endcan --}}
            </div>

            {{-- User Activity Report Link --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-xl font-semibold mb-3 text-gray-800">{{ __('User Activity Report') }}</h3>
                {{-- Translated title --}}
                <p class="text-gray-600 mb-4">{{ __('View reports on user application activity.') }}</p>
                {{-- Translated description --}}
                {{-- CORRECTED ROUTE NAME: Use the full registered name 'admin.reports.user-activity' --}}
                {{-- @can('viewUserActivity', App\Http\Controllers\ReportController::class) --}} {{-- Optional: Check policy for visibility --}}
                <a href="{{ route('admin.reports.user-activity') }}" class="btn btn-primary"> {{-- Assuming 'btn btn-primary' Tailwind classes are defined or custom CSS handles them --}}
                    {{ __('View Report') }} {{-- Translated button text --}}
                </a>
                {{-- @endcan --}}
            </div>

            {{-- Loan Applications Report Link --}}
            {{-- Adding link for loan applications report based on ReportController --}}
            {{-- @can('viewLoanApplications', App\Http\Controllers\ReportController::class) --}} {{-- Optional: Check policy for visibility --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-xl font-semibold mb-3 text-gray-800">{{ __('Loan Applications Report') }}</h3>
                {{-- Translated title --}}
                <p class="text-gray-600 mb-4">{{ __('View reports on loan applications status and history.') }}</p>
                {{-- Translated description --}}
                {{-- CORRECTED ROUTE NAME: Use the full registered name 'admin.reports.loan-applications' --}}
                <a href="{{ route('admin.reports.loan-applications') }}" class="btn btn-primary">
                    {{ __('View Report') }} {{-- Translated button text --}}
                </a>
            </div>
            {{-- @endcan --}}

            {{-- Loan History Report Link --}}
            {{-- Adding link for loan history report based on ReportController --}}
            {{-- @can('viewLoanHistory', App\Http\Controllers\ReportController::class) --}} {{-- Optional: Check policy for visibility --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-xl font-semibold mb-3 text-gray-800">{{ __('Loan History Report') }}</h3>
                {{-- Translated title --}}
                <p class="text-gray-600 mb-4">{{ __('View detailed history of equipment loan transactions.') }}</p>
                {{-- Translated description --}}
                {{-- CORRECTED ROUTE NAME: Use the full registered name 'admin.reports.loan-history' --}}
                <a href="{{ route('admin.reports.loan-history') }}" class="btn btn-primary">
                    {{ __('View Report') }} {{-- Translated button text --}}
                </a>
            </div>
            {{-- @endcan --}}

            {{-- Add more report links here as needed --}}

        </div> {{-- End grid --}}

    </div> {{-- End container --}}

@endsection {{-- End content section --}}

{{-- Optional: Add custom styles for 'btn btn-primary' if not handled by Tailwind or layout --}}
{{-- You might push these styles to a stack defined in your layout --}}
{{-- @push('styles')
<style>
    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0.5rem 1.25rem;
        border-radius: 0.375rem;
        font-weight: 600;
        transition: background-color 0.15s ease-in-out, border-color 0.15s ease-in-out, color 0.15s ease-in-out;
        outline: none;
        cursor: pointer;
    }

    .btn-primary {
        background-color: #3b82f6;
        color: #fff;
        border: 1px solid #3b82f6;
    }

    .btn-primary:hover {
        background-color: #2563eb;
        border-color: #2563eb;
    }
</style>
@endpush --}}
