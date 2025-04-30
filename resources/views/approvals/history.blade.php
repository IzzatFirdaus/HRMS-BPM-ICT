{{-- resources/views/approval-history.blade.php --}}
{{--
    This view displays the approval history for various applications.
    It expects an $approvals variable to be passed to it, which is a paginated collection
    of Approval model instances.
    It should be extended by a layout that provides the main HTML structure,
    Tailwind CSS, and any necessary scripts.
--}}

@extends('layouts.app') {{-- Extend your base layout --}}

@section('content') {{-- Define the main content section --}}
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 py-6"> {{-- Container for the content --}}
        <h2 class="text-2xl font-bold mb-6 text-gray-800">{{ __('Sejarah Kelulusan') }}</h2> {{-- Title --}}

        {{-- Display success or error messages using Tailwind alert classes --}}
        @if (session()->has('success'))
            <div class="p-4 mb-4 text-sm text-green-800 rounded-lg bg-green-50 dark:bg-gray-800 dark:text-green-400"
                role="alert">
                {{ session('success') }}
            </div>
        @endif

        @if (session()->has('error'))
            <div class="p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50 dark:bg-gray-800 dark:text-red-400" role="alert">
                {{ session('error') }}
            </div>
        @endif

        {{-- Table to display approval history --}}
        @if ($approvals->isEmpty())
            <p class="text-gray-600">{{ __('Tiada sejarah kelulusan ditemui.') }}</p> {{-- Message if no history --}}
        @else
            <div class="overflow-x-auto shadow-sm rounded-md border border-gray-200"> {{-- Added overflow and shadow for table container --}}
                {{-- Use Tailwind table classes --}}
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50"> {{-- Added header background --}}
                        <tr>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('Permohonan') }} {{-- Localized --}}
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('Pegawai') }} {{-- Localized --}}
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('Status Kelulusan') }} {{-- Localized --}}
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('Peringkat') }} {{-- Localized --}}
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('Catatan') }} {{-- Localized --}}
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('Tarikh & Masa') }} {{-- Localized --}}
                            </th>
                        </tr>
                    </thead>
                    {{-- Use Tailwind classes for tbody and tr hover/odd colors --}}
                    <tbody class="bg-white divide-y divide-gray-200 odd:bg-gray-50 hover:bg-gray-100">
                        {{-- Loop through the collection of approval records --}}
                        @foreach ($approvals as $approval)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{-- Display application type and link to show page --}}
                                    {{-- Use null-safe operator for approvable relationship and ID --}}
                                    @if ($approval->approvable instanceof \App\Models\EmailApplication)
                                        <a href="{{ $approval->approvable?->id ? route('email-applications.show', $approval->approvable) : '#' }}"
                                            class="text-blue-600 hover:text-blue-900 font-semibold">
                                            {{ __('E-mel ICT') }} (#{{ $approval->approvable?->id ?? 'N/A' }})
                                            {{-- Localized and safe ID --}}
                                        </a>
                                    @elseif ($approval->approvable instanceof \App\Models\LoanApplication)
                                        <a href="{{ $approval->approvable?->id ? route('loan-applications.show', $approval->approvable) : '#' }}"
                                            class="text-blue-600 hover:text-blue-900 font-semibold">
                                            {{ __('Pinjaman Peralatan ICT') }}
                                            (#{{ $approval->approvable?->id ?? 'N/A' }}) {{-- Localized and safe ID --}}
                                        </a>
                                    @else
                                        {{ __('Jenis Tidak Diketahui') }} {{-- Localized --}}
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{-- Display officer name safely --}}
                                    {{ $approval->officer?->name ?? 'N/A' }} {{-- Use null-safe operator --}}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{-- Display status with a colored badge using Tailwind classes --}}
                                    <span
                                        class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold leading-none {{ match ($approval->status ?? '' /* Add ?? '' for safety */) {
                                            'pending' => 'bg-yellow-100 text-yellow-800',
                                            'approved' => 'bg-green-100 text-green-800',
                                            'rejected' => 'bg-red-100 text-red-800', /* Use Tailwind red */
                                            /* Use Tailwind red */
                                            /* Add more status mappings if needed based on Approval model */
                                            default => 'bg-gray-200 text-gray-700', /* Default secondary badge */
                                        } }}">
                                        {{-- Translate status name safely --}}
                                        {{ __(ucfirst(str_replace('_', ' ', $approval->status ?? 'N/A'))) }}
                                        {{-- Add ?? 'N/A' for safety before translation --}}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{-- Display the approval stage safely --}}
                                    {{ $approval->stage ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900"> {{-- Removed whitespace-nowrap for comments --}}
                                    {{-- Display comments safely --}}
                                    {{ $approval->comments ?? '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{-- Display timestamp safely --}}
                                    {{ $approval->approval_timestamp?->format('Y-m-d H:i') ?? 'N/A' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div> {{-- End overflow-x-auto --}}

            {{-- Pagination links --}}
            @if ($approvals->hasPages())
                <div class="mt-4">
                    {{ $approvals->links() }}
                </div>
            @endif
        @endif

        {{-- Back Button --}}
        <div class="mt-6 text-center"> {{-- Centered the back button --}}
            {{-- Assuming a route named 'approval-dashboard.index' for the main dashboard --}}
            {{-- Use Tailwind classes for the button --}}
            <a href="{{ route('approval-dashboard.index') }}"
                class="inline-flex items-center px-5 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                {{-- SVG icon --}}
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                {{ __('Kembali ke Dashboard Kelulusan') }} {{-- Localized --}}
            </a>
        </div>

    </div> {{-- End container --}}
@endsection {{-- End the content section --}}

{{-- Remove the full HTML structure, Tailwind script include, and custom CSS --}}
{{-- These should be handled by the layout file (layouts.app) --}}
{{-- <head> ... </head> --}}
{{-- <body class="bg-gray-100 p-6"> ... </body> --}}
{{-- </html> --}}
