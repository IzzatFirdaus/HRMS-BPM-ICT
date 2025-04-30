{{-- resources/views/approvals/pending.blade.php --}}
{{--
    This view displays the list of pending approval requests for the current officer.
    It expects an $approvals variable to be passed to it, which is a paginated collection
    of Approval model instances where status is 'pending' and the officer matches the current user.
    It should be extended by a layout that provides the main HTML structure,
    Tailwind CSS, and any necessary scripts.
--}}

@extends('layouts.app') {{-- Extend your base layout --}}

@section('content') {{-- Define the main content section --}}
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 py-6"> {{-- Tailwind container --}}
        <h2 class="text-2xl font-bold mb-6 text-gray-800">{{ __('Senarai Kelulusan Tertunda') }}</h2> {{-- Title --}}

        {{-- Display success messages using Tailwind alert classes --}}
        @if (session()->has('success'))
            <div class="p-4 mb-4 text-sm text-green-800 rounded-lg bg-green-50 dark:bg-gray-800 dark:text-green-400"
                role="alert">
                {{ session('success') }}
            </div>
        @endif

        {{-- Table to display pending approvals --}}
        @if ($approvals->isEmpty())
            {{-- Use isEmpty() for Laravel collections --}}
            <p class="text-gray-600">{{ __('Tiada kelulusan tertunda pada masa ini.') }}</p> {{-- Message if no pending approvals --}}
        @else
            <div class="overflow-x-auto shadow-sm rounded-md border border-gray-200"> {{-- Table container --}}
                {{-- Use Tailwind table classes --}}
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50"> {{-- Header background --}}
                        <tr>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('Jenis Permohonan') }} {{-- Localized --}}
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('Pemohon') }} {{-- Localized --}}
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
                                {{ __('Tarikh Dihantar') }} {{-- Localized --}}
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('Tindakan') }} {{-- Localized --}}
                            </th>
                        </tr>
                    </thead>
                    {{-- Use Tailwind classes for tbody and tr hover/odd colors --}}
                    <tbody class="bg-white divide-y divide-gray-200 odd:bg-gray-50 hover:bg-gray-100">
                        {{-- Loop through the collection of pending approval records --}}
                        @foreach ($approvals as $approval)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{-- Display application type based on the polymorphic relationship --}}
                                    {{-- Use null-safe operator for approvable relationship and ID --}}
                                    @if ($approval->approvable instanceof \App\Models\EmailApplication)
                                        {{ __('Permohonan E-mel ICT') }} (#{{ $approval->approvable?->id ?? 'N/A' }})
                                        {{-- Localized and safe ID --}}
                                    @elseif ($approval->approvable instanceof \App\Models\LoanApplication)
                                        {{ __('Permohonan Pinjaman Peralatan ICT') }}
                                        (#{{ $approval->approvable?->id ?? 'N/A' }}) {{-- Localized and safe ID --}}
                                    @else
                                        {{ __('Jenis Tidak Diketahui') }} {{-- Localized --}}
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{-- Display applicant name from the related application's user relationship safely --}}
                                    {{ $approval->approvable?->user?->name ?? 'N/A' }} {{-- Use null-safe operators --}}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{-- Display approval status with a colored badge using Tailwind classes --}}
                                    <span
                                        class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold leading-none {{ match ($approval->status ?? '' /* Add ?? '' for safety */) {
                                            'pending' => 'bg-yellow-100 text-yellow-800',
                                            'approved' => 'bg-green-100 text-green-800', /* Unlikely in pending list */
                                            'rejected' => 'bg-red-100 text-red-800', /* Unlikely in pending list */
                                            /* Unlikely in pending list */
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
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{-- Display the application's submission timestamp safely --}}
                                    {{ $approval->approvable?->created_at?->format('Y-m-d H:i') ?? 'N/A' }}
                                    {{-- Access via approvable relationship --}}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{-- Link to view details of the specific approval record --}}
                                    {{-- Assuming a route named 'approvals.show' exists --}}
                                    {{-- Use Tailwind classes for the button and safe routing --}}
                                    <a href="{{ $approval->id ? route('approvals.show', $approval) : '#' }}"
                                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-teal-600 hover:bg-teal-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500">
                                        {{ __('Lihat Butiran') }} {{-- Localized --}}
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div> {{-- End overflow-x-auto --}}

            {{-- Pagination links --}}
            @if ($approvals->hasPages())
                {{-- Check if the collection is paginated --}}
                <div class="mt-4">
                    {{ $approvals->links() }}
                </div>
            @endif
        @endif

        {{-- Optional: Link to Approval History page --}}
        <div class="mt-6 text-center">
            {{-- Assuming a route named 'approvals.history' exists --}}
            {{-- Use Tailwind classes for the button and SVG --}}
            <a href="{{ route('approvals.history') }}"
                class="inline-flex items-center px-5 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                {{ __('Lihat Sejarah Kelulusan') }} {{-- Localized --}}
            </a>
        </div>


    </div> {{-- End max-w-7xl container --}}
@endsection {{-- End the content section --}}

{{-- Remove the full HTML structure, Tailwind script include, and custom CSS --}}
{{-- These should be handled by the layout file (layouts.app) --}}
{{-- <head> ... </head> --}}
{{-- <body class="bg-gray-100 p-6"> ... </body> --}}
{{-- </html> --}}
