{{-- resources/views/reports/email-accounts.blade.php --}}
{{-- This view displays a report of email applications. --}}
{{-- It assumes it receives a paginated collection named $emailApplications from the controller. --}}

@extends('layouts.app') {{-- Adjust 'layouts.app' if your main layout file is named differently --}}

@section('title', 'Laporan Akaun Emel ICT') {{-- Set a title for the page --}}

@section('content')
    <div class="container mx-auto py-6"> {{-- Use container styling --}}
        <h1 class="text-2xl font-bold mb-6 text-gray-800">{{ __('Laporan Akaun Emel ICT') }}</h1> {{-- Page title --}}

        {{-- Display success messages from session if needed --}}
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
        {{-- Check if the $emailApplications collection is empty --}}
        @if ($emailApplications->isEmpty()) {{-- Corrected variable name --}}
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
                                ID Permohonan
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Pemohon
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Tujuan
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Cadangan Emel
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Emel Ditetapkan
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                ID Pengguna Ditetapkan
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Tarikh Permohonan
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Tarikh Selesai
                            </th>
                            {{-- Add more table headers for other relevant fields from EmailApplication --}}
                        </tr>
                    </thead>
                    {{-- Table body - Applied Tailwind tbody and tr/td classes --}}
                    <tbody class="bg-white divide-y divide-gray-200">
                        {{-- Loop through the email applications --}}
                        @foreach ($emailApplications as $application)
                            {{-- Corrected variable name --}}
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $application->id }} {{-- Display Application ID --}}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $application->user->full_name ?? ($application->user->name ?? 'N/A') }}
                                    {{-- Display Applicant Name --}}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $application->purpose }} {{-- Display Purpose --}}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $application->proposed_email }} {{-- Display Proposed Email --}}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $application->final_assigned_email ?? 'Belum Ditetapkan' }} {{-- Display Assigned Email --}}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $application->final_assigned_user_id ?? 'Belum Ditetapkan' }} {{-- Display Assigned User ID --}}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{-- You might want to use an accessor or translation for status display --}}
                                    {{ $application->status }} {{-- Display Status --}}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $application->created_at->format('Y-m-d H:i') }} {{-- Display Application Date --}}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $application->completed_at?->format('Y-m-d H:i') ?? 'Belum Selesai' }}
                                    {{-- Display Completion Date (assuming a completed_at column or logic) --}}
                                    {{-- If no completed_at column, you might infer completion from status and updated_at --}}
                                </td>
                                {{-- Add more table cells for other fields --}}
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination links --}}
            <div class="mt-4">
                {{ $emailApplications->links() }} {{-- Corrected variable name --}}
            </div>
        @endif

    </div>
@endsection
