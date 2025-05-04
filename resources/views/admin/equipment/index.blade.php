{{--
    resources/views/admin/equipment/index.blade.php

    This Blade view file displays the list of ICT Equipment for admin/BPM management.
    It has been revised to use the specified structure:
    - Includes full HTML boilerplate.
    - Includes the Tailwind CSS via CDN.
    - Extends 'layouts.app' and places content within @section('content').
    - Uses Tailwind CSS classes for styling, aligning with the grades/index.blade.php design.
    - Displays relevant equipment details including MOTAC fields and loan status.
    - Correctly uses route names as defined in web.php ('admin.equipment.*').
    - Applies permission checks for actions.
    - Includes the '+' icon in the 'Add New Equipment' button.
    - Uses the full pagination links (with icons and page numbers).
    - Assumes a layout file 'layouts.app' exists and has a @yield('content') section.
    - Assumes the Equipment model has relationships for department, center, activeLoanTransaction,
      activeLoanTransaction.loanApplication, and activeLoanTransaction.loanApplication.user,
      and potentially accessors for translated statuses.
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
    <title>{{ __('Equipment List') }}</title> {{-- Updated title --}}

    {{-- Link Tailwind CSS via CDN for styling. --}}
    <script src="https://cdn.tailwindcss.com"></script>

    {{-- Add any necessary custom CSS here if Tailwind alone is insufficient for specific styling requirements --}}
</head>

{{-- Body element with Tailwind background color and padding applied --}}

<body class="bg-gray-100 p-6">

    {{-- Extend your main layout file. Assuming 'layouts.app' exists and has a @yield('content') section. --}}
    @extends('layouts.app')

    {{-- Define the content section where the list and messages will be placed within the layout --}}
    @section('content')
        {{-- Main container for the content. Sets max-width, centers horizontally, and adds padding, matching grades/equipment design. --}}
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 py-6">

            {{-- Header section with title and Add New Equipment button --}}
            <div class="flex items-center justify-between mb-6">
                {{-- Page Title - Matching grades styling (h2 equivalent styling) --}}
                <h1 class="text-2xl font-bold text-gray-800">{{ __('Equipment List') }}</h1> {{-- Using h1 tag as before --}}

                {{-- Add New Equipment Button --}}
                @can('create equipment')
                    {{-- Link to the create equipment page - Route name is correct --}}
                    {{-- Applied button styling exactly matching grades --}}
                    <a href="{{ route('admin.equipment.create') }}"
                        class="inline-flex items-center justify-center px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white font-bold rounded shadow-sm focus:outline-none focus:ring focus:ring-blue-500 transition">
                        {{-- ADDED the SVG icon element back, matching size from grades --}}
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                        </svg>
                        {{ __('Add New Equipment') }} {{-- Translated button text --}}
                    </a>
                @endcan
            </div>

            {{-- Display success or error messages from session (using flash messages) --}}
            {{-- Applied styling exactly matching grades message boxes --}}
            @if (session('success'))
                <div class="bg-green-100 border border-green-200 text-green-800 p-4 rounded-md mb-4">
                    {{ session('success') }} {{-- Display the success message --}}
                </div>
            @endif
            @if (session('error'))
                <div class="bg-red-100 border border-red-200 text-red-800 p-4 rounded-md mb-4">
                    {{ session('error') }} {{-- Display the error message --}}
                </div>
            @endif


            {{-- Equipment Table Card --}}
            {{-- Applied table container styling exactly matching grades --}}
            <div class="overflow-x-auto shadow-sm rounded-lg border border-gray-200">
                {{-- Equipment data table, applied Tailwind table classes --}}
                <table class="min-w-full divide-y divide-gray-200">
                    {{-- Table header --}}
                    <thead class="bg-gray-50">
                        <tr>
                            {{-- Table header cells, applied Tailwind th classes --}}
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('Asset Tag ID') }}
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('Asset Type') }}
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('Brand') }}
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('Model') }}
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('Availability Status') }}
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('Condition Status') }}
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('Department') }}
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('Center') }}
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('Currently Loaned To') }}
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('Actions') }}
                            </th>
                        </tr>
                    </thead>
                    {{-- Table body - Matching grades styling --}}
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($equipment as $item)
                            <tr class="hover:bg-gray-100">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ optional($item)->tag_id ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ optional($item)->asset_type ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ optional($item)->brand ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ optional($item)->model ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{-- Applied badge styling exactly matching grades --}}
                                    @php
                                        // Determine Tailwind classes based on availability_status
                                        $availabilityStatusValue = optional($item)->availability_status;
                                        $availabilityStatusClass = match ($availabilityStatusValue) {
                                            'available' => 'bg-green-100 text-green-800',
                                            'on_loan' => 'bg-yellow-100 text-yellow-800',
                                            'under_maintenance' => 'bg-blue-100 text-blue-800',
                                            'disposed' => 'bg-gray-100 text-gray-800',
                                            'lost', 'damaged' => 'bg-red-100 text-red-800',
                                            default => 'bg-gray-100 text-gray-800',
                                        };
                                    @endphp
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $availabilityStatusClass }}">
                                        {{ __(optional($item)->availability_status_translated ?? ucfirst($availabilityStatusValue ?? 'N/A')) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{-- Applied badge styling exactly matching grades --}}
                                    @php
                                        // Determine Tailwind classes based on condition_status
                                        $conditionStatusValue = optional($item)->condition_status;
                                        $conditionStatusClass = match ($conditionStatusValue) {
                                            'Good' => 'bg-green-100 text-green-800',
                                            'Fine' => 'bg-yellow-100 text-yellow-800',
                                            'Bad', 'Damaged' => 'bg-red-100 text-red-800',
                                            default => 'bg-gray-100 text-gray-800',
                                        };
                                    @endphp
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $conditionStatusClass }}">
                                        {{ __(optional($item)->condition_status_translated ?? ucfirst($conditionStatusValue ?? 'N/A')) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ optional(optional($item)->department)->name ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ optional(optional($item)->center)->name ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    @php
                                        $loanedTo = optional(
                                            optional(optional($item)->activeLoanTransaction)->loanApplication,
                                        )->user;
                                    @endphp
                                    {{ optional($loanedTo)->full_name ?? (optional($loanedTo)->name ?? 'N/A') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-left text-sm font-medium">
                                    <div class="flex items-center space-x-2">
                                        @can('view equipment')
                                            <a href="{{ route('admin.equipment.show', $item) }}"
                                                class="text-blue-600 hover:text-blue-800">
                                                {{ __('View') }}
                                            </a>
                                        @endcan
                                        @can('update equipment')
                                            <a href="{{ route('admin.equipment.edit', $item) }}"
                                                class="text-indigo-600 hover:text-indigo-800">
                                                {{ __('Edit') }}
                                            </a>
                                        @endcan
                                        @can('delete equipment')
                                            <form action="{{ route('admin.equipment.destroy', $item) }}" method="POST"
                                                onsubmit="return confirm('{{ __('Are you sure you want to delete this equipment?') }}');"
                                                class="inline-block">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-800">
                                                    {{ __('Delete') }}
                                                </button>
                                            </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-600">
                                    {{ __('No equipment found.') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination Links --}}
            @if ($equipment instanceof \Illuminate\Pagination\LengthAwarePaginator && $equipment->hasPages())
                <div class="mt-4">
                    {{ $equipment->links() }}
                </div>
            @endif

        </div>
    @endsection

</body>

</html>
