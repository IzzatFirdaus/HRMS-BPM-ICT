{{--
    resources/views/reports/equipment.blade.php

    This Blade view file displays a report of ICT Equipment.
    It has been revised to use the specified structure:
    - Includes full HTML boilerplate.
    - Includes the Tailwind CSS via CDN.
    - Extends 'layouts.app' and places content within @section('content').
    - Uses Tailwind CSS classes for styling, aligning with the grades/equipment/users design.
    - Displays relevant equipment details for reporting.
    - Assumes a layout file 'layouts.app' exists and has a @yield('content') section.
    - Assumes the Equipment model has attributes like tag_id, asset_type, brand, model, serial_number,
      availability_status, condition_status, and relationships for department, position,
      and activeLoanTransaction (which has a user relationship and issue_timestamp).
    - Assumes a collection (likely not paginated) of Equipment models is passed as $equipment.
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
    <title>{{ __('Laporan Peralatan ICT') }}</title> {{-- Updated title --}}

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
            <h1 class="text-2xl font-bold mb-6 text-gray-800">{{ __('Laporan Peralatan ICT') }}</h1> {{-- Using h1 tag for main page title --}}


            {{-- Display success messages (if needed, though less common on a report page) --}}
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

            {{-- Table to display equipment for the report --}}
            {{-- Check if the $equipment collection is empty --}}
            @if ($equipment->isEmpty())
                {{-- Message if no equipment - Applied styling matching other views empty message --}}
                <p class="text-gray-600">{{ __('Tiada peralatan ICT ditemui untuk laporan ini.') }}</p>
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
                                    {{ __('Asset Tag ID') }}</th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('Jenis Aset') }}</th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('Brand') }}</th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('Model') }}</th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('Nombor Siri') }}</th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('Status Ketersediaan') }}</th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('Status Kondisi') }}</th>

                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('Jabatan') }}</th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('Jawatan') }}</th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('Pengguna Semasa (Jika Dipinjam)') }}</th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('Tarikh Pinjaman (Jika Dipinjam)') }}</th>


                                {{-- Add other relevant headers for equipment fields if needed --}}
                                {{-- <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Purchase Date</th> --}}
                                {{-- <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Warranty Expiry</th> --}}
                                {{-- <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Value</th> --}}
                                {{-- <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Location Details</th> --}}
                            </tr>
                        </thead>
                        {{-- Table body - Applied Tailwind td classes --}}
                        <tbody class="bg-white divide-y divide-gray-200">
                            {{-- Loop through the $equipment collection --}}
                            @foreach ($equipment as $item)
                                <tr class="hover:bg-gray-100"> {{-- Added hover effect --}}
                                    {{-- Display equipment properties --}}
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
                                        {{ optional($item)->serial_number ?? 'N/A' }}
                                    </td>
                                    {{-- Use translated accessors if available, fallback to raw data --}}
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{-- Applied badge styling matching other views --}}
                                        @php
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
                                        {{-- Applied badge styling matching other views --}}
                                        @php
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

                                    {{-- Display related data using relationships --}}
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ optional(optional($item)->department)->name ?? 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ optional(optional($item)->position)->name ?? 'N/A' }}
                                    </td>

                                    {{-- Display active loan info if activeLoanTransaction exists --}}
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        @if (optional($item)->activeLoanTransaction && optional(optional($item)->activeLoanTransaction)->user)
                                            {{ optional(optional($item)->activeLoanTransaction)->user->full_name ?? (optional(optional($item)->activeLoanTransaction)->user->name ?? 'N/A') }}
                                            {{-- Prioritize full_name --}}
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                    {{-- Display issue timestamp from active loan --}}
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        @if (optional($item)->activeLoanTransaction)
                                            {{ optional(optional($item)->activeLoanTransaction)->issue_timestamp?->format('Y-m-d H:i') ?? 'N/A' }}
                                        @else
                                            N/A
                                        @endif
                                    </td>


                                    {{-- Add other relevant data cells for equipment fields if needed --}}
                                    {{-- <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ optional($item)->purchase_date?->format('Y-m-d') ?? 'N/A' }}</td> --}}
                                    {{-- <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ optional($item)->warranty_expiry_date?->format('Y-m-d') ?? 'N/A' }}</td> --}}
                                    {{-- <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ optional($item)->value ?? 'N/A' }}</td> --}}
                                    {{-- <td class="px-6 py-4 text-sm text-gray-900">{{ optional($item)->location_details ?? 'N/A' }}</td> --}}
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div> {{-- End overflow-x-auto --}}

                {{-- Pagination links (only if using paginate() in controller) --}}
                {{-- Keeping this commented out as it was in the original --}}
                {{-- @if ($equipment instanceof \Illuminate\Pagination\LengthAwarePaginator && $equipment->hasPages()) --}}
                {{--    <div class="mt-4"> --}}
                {{--        {{ $equipment->links() }} --}}
                {{--    </div> --}}
                {{-- @endif --}}
            @endif {{-- End of if ($equipment->isEmpty()) --}}

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
