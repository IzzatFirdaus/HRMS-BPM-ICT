{{--
    resources/views/reports/email-accounts.blade.php

    This Blade view file displays a report of ICT Email Accounts.
    It has been revised to use the specified structure:
    - Includes full HTML boilerplate.
    - Includes the Tailwind CSS via CDN.
    - Extends 'layouts.app' and places content within @section('content').
    - Uses Tailwind CSS classes for styling, aligning with the grades/equipment/users design.
    - Displays relevant email application details for reporting.
    - Assumes a layout file 'layouts.app' exists and has a @yield('content') section.
    - Assumes the EmailApplication model has a user relationship with 'name',
      'status', 'final_assigned_email', 'proposed_email', 'created_at', and 'purpose' attributes.
    - Assumes a paginated collection of EmailApplication models is passed as $applications.
--}}

<!DOCTYPE html>
{{-- HTML document with language attribute set based on application locale --}}
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    {{-- Character set declaration --}}
    <meta charset="utf-8">
    {{-- Viewport settings for responsive design --}}
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    {{-- Page title, translated --}}
    <title>{{ __('Laporan Akaun Emel ICT') }}</title> {{-- Updated title --}}

    {{-- Link Tailwind CSS via CDN for styling. --}}
    <script src="https://cdn.tailwindcss.com"></script>

    {{-- Add any necessary custom CSS here if Tailwind alone is insufficient for specific styling requirements --}}
</head>

{{-- Body element with Tailwind background color and padding applied --}}

<body class="bg-gray-100 p-6">

    {{-- Extend your main layout file. Assuming 'layouts.app' exists and has a @yield('content') section. --}}
    @extends('layouts.app')

    {{-- Define the content section where the report content will be placed within the layout --}}
    @section('content')
        {{-- Main container for the content. Sets max-width, centers horizontally, and adds padding, matching other views. --}}
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 py-6">

            {{-- Page Title - Matching other views (h1 equivalent styling) --}}
            <h1 class="text-2xl font-bold mb-6 text-gray-800">{{ __('Laporan Akaun Emel ICT') }}</h1> {{-- Using h1 tag for main page title --}}

            {{-- Display success messages (using flash messages) --}}
            {{-- Applied styling exactly matching other views message boxes --}}
            @if (session()->has('success'))
                <div class="bg-green-100 border border-green-200 text-green-800 p-4 rounded-md mb-4">
                    {{ session('success') }} {{-- Display the success message --}}
                </div>
            @endif

            {{-- Display error messages from session if needed (added for consistency) --}}
            @if (session()->has('error'))
                <div class="bg-red-100 border border-red-200 text-red-800 p-4 rounded-md mb-4">
                    {{ session('error') }} {{-- Display the error message --}}
                </div>
            @endif

            {{-- Table to display email applications for the report --}}
            {{-- Check if the $applications collection is empty --}}
            @if ($applications->isEmpty())
                {{-- Message if no applications - Applied styling matching other views empty message --}}
                <p class="text-gray-600">{{ __('Tiada permohonan akaun emel ICT ditemui untuk laporan ini.') }}</p>
            @else
                {{-- Added overflow-x-auto for responsiveness --}}
                {{-- Applied table container styling matching other views --}}
                <div class="overflow-x-auto shadow-sm rounded-lg border border-gray-200">
                    {{-- Applied Tailwind table classes --}}
                    <table class="min-w-full divide-y divide-gray-200">
                        {{-- Table header - Applied Tailwind th classes --}}
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{-- Removed border-b as divide-y handles borders --}}
                                    {{ __('Pemohon') }}
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{-- Removed border-b as divide-y handles borders --}}
                                    {{ __('Status Permohonan') }}
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{-- Removed border-b as divide-y handles borders --}}
                                    {{ __('E-mel Rasmi MOTAC / Cadangan E-mel') }}
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{-- Removed border-b as divide-y handles borders --}}
                                    {{ __('Tarikh Hantar') }}
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{-- Removed border-b as divide-y handles borders --}}
                                    {{ __('Tujuan') }}
                                </th>
                            </tr>
                        </thead>
                        {{-- Table body - Applied Tailwind td classes --}}
                        <tbody class="bg-white divide-y divide-gray-200">
                            {{-- Loop through the collection of email applications --}}
                            @foreach ($applications as $app)
                                <tr class="hover:bg-gray-100"> {{-- Added hover effect --}}
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"> {{-- Changed text color to gray-900, removed border-b --}}
                                        {{ optional($app->user)->name ?? 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"> {{-- Changed text color to gray-900, removed border-b --}}
                                        {{-- Display status with a colored badge - Applied badge styling matching other views --}}
                                        @php
                                            $statusValue = optional($app)->status;
                                            $badgeClass = match ($statusValue) {
                                                'draft' => 'bg-gray-100 text-gray-800',
                                                'pending_support',
                                                'pending_admin',
                                                'processing'
                                                    => 'bg-yellow-100 text-yellow-800',
                                                'approved' => 'bg-blue-100 text-blue-800', // Using blue for approved
                                                'completed' => 'bg-green-100 text-green-800',
                                                'rejected', 'provision_failed' => 'bg-red-100 text-red-800',
                                                default => 'bg-gray-100 text-gray-800',
                                            };
                                        @endphp
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $badgeClass }}">
                                            {{-- Applied badge classes from other views --}}
                                            {{ __(ucfirst(str_replace('_', ' ', $statusValue ?? 'N/A'))) }}
                                            {{-- Translated and formatted status with fallback --}}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"> {{-- Changed text color to gray-900, removed border-b --}}
                                        {{-- Display final assigned email if available, otherwise proposed email --}}
                                        {{ optional($app)->final_assigned_email ?? (optional($app)->proposed_email ?? '-') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"> {{-- Changed text color to gray-900, removed border-b --}}
                                        {{ optional($app)->created_at?->format('Y-m-d') ?? 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900"> {{-- Changed text color to gray-900, removed border-b --}}
                                        {{ Str::limit(optional($app)->purpose, 50) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div> {{-- End overflow-x-auto --}}

                {{-- Pagination links --}}
                {{-- This block expects a paginated collection ($applications) from the controller --}}
                @if ($applications instanceof \Illuminate\Pagination\LengthAwarePaginator && $applications->hasPages())
                    {{-- Added check for LengthAwarePaginator --}}
                    <div class="mt-4"> {{-- Added margin top --}}
                        {{ $applications->links() }} {{-- Renders Tailwind-styled pagination links --}}
                    </div>
                @endif
            @endif {{-- End of if ($applications->isEmpty()) --}}

            {{-- Optional: Back button to a reports dashboard or home --}}
            {{-- Keeping this commented out as it was in the original --}}
            {{--
            <div class="mt-6 text-center">
                <a href="{{ route('admin.reports.index') }}" class="inline-flex items-center justify-center px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold rounded shadow-sm focus:outline-none focus:ring focus:ring-gray-300 transition">
                     <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                         <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                     </svg>
                     {{ __('Kembali ke Laporan') }}
                 </a>
            </div>
            --}}

        </div> {{-- End max-w-7xl container --}}

    @endsection {{-- End of content section --}}

</body>

</html>
