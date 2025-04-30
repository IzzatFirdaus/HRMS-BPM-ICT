{{-- resources/views/approvals/show.blade.php --}}
{{--
    This view displays the details of a single approval record and its associated application.
    It expects an $approval variable to be passed to it, which is an instance of the Approval model.
    It should be extended by a layout that provides the main HTML structure,
    Tailwind CSS, and any necessary scripts.
--}}

@extends('layouts.app') {{-- Extend your base layout --}}

@section('content')
    {{-- Define the main content section --}}
    <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 py-8"> {{-- Tailwind container --}}
        {{-- Card-like container --}}
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border border-gray-200"> {{-- Added border for clarity --}}

            {{-- Title with approval ID, safely accessed and localized --}}
            <h2 class="text-2xl font-bold mb-6 text-gray-800">{{ __('Butiran Kelulusan') }} #{{ $approval->id ?? 'N/A' }}
            </h2>

            {{-- Display success or error messages using Tailwind alert classes --}}
            @if (session()->has('success'))
                <div class="p-4 mb-4 text-sm text-green-800 rounded-lg bg-green-50 dark:bg-gray-800 dark:text-green-400"
                    role="alert">
                    {{ session('success') }}
                </div>
            @endif

            @if (session()->has('error'))
                <div class="p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50 dark:bg-gray-800 dark:text-red-400"
                    role="alert">
                    {{ session('error') }}
                </div>
            @endif

            {{-- Approval Details Card --}}
            {{-- Use Tailwind classes for the card --}}
            <div class="border border-gray-300 rounded-lg p-6 mb-6 bg-white shadow-sm">
                {{-- Use Tailwind classes for the card title --}}
                <h3 class="text-xl font-bold mb-4 text-gray-800">{{ __('Butiran Kelulusan') }}</h3>

                <p class="mb-2">
                    <span class="font-semibold">{{ __('Status Kelulusan:') }}</span>
                    {{-- Display status with a colored badge using Tailwind classes --}}
                    <span
                        class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold leading-none {{ match ($approval->status ?? '' /* Add ?? '' for safety */) {
                            'pending' => 'bg-yellow-100 text-yellow-800',
                            'approved' => 'bg-green-100 text-green-800',
                            'rejected' => 'bg-red-100 text-red-800',
                            /* Add more status mappings if needed based on Approval model */
                            default => 'bg-gray-200 text-gray-700', /* Default secondary badge */
                        } }} text-lg">
                        {{-- text-lg class seems specific, keep it --}}
                        {{-- Translate status name safely --}}
                        {{ __(ucfirst(str_replace('_', ' ', $approval->status ?? 'N/A'))) }} {{-- Add ?? 'N/A' for safety before translation --}}
                    </span>
                </p>
                {{-- Display stage safely and localized --}}
                <p class="mb-2"><span class="font-semibold">{{ __('Peringkat:') }}</span> {{ $approval->stage ?? 'N/A' }}
                </p>
                {{-- Display officer name safely and localized --}}
                <p class="mb-2"><span class="font-semibold">{{ __('Pegawai:') }}</span>
                    {{ $approval->officer?->name ?? 'N/A' }}</p>
                {{-- Display approval timestamp safely and localized --}}
                <p class="mb-2"><span class="font-semibold">{{ __('Tarikh & Masa Keputusan:') }}</span>
                    {{ $approval->approval_timestamp?->format('Y-m-d H:i') ?? 'N/A' }}</p>
                {{-- Display comments safely and localized --}}
                <p class="mb-2"><span class="font-semibold">{{ __('Catatan Pegawai:') }}</span>
                    {{ $approval->comments ?? '-' }}</p>
            </div> {{-- End Approval Details card --}}

            {{-- Associated Application Details Card --}}
            {{-- Use Tailwind classes for the card --}}
            <div class="border border-gray-300 rounded-lg p-6 mb-6 bg-white shadow-sm">
                {{-- Use Tailwind classes for the card title --}}
                <h3 class="text-xl font-bold mb-4 text-gray-800">{{ __('Butiran Permohonan Berkaitan') }}</h3>

                {{-- Check the type of the approvable relationship, safely --}}
                @if ($approval->approvable instanceof \App\Models\EmailApplication)
                    <p class="mb-2"><span class="font-semibold">{{ __('Jenis Permohonan:') }}</span>
                        {{ __('Permohonan E-mel ICT') }}</p>
                    {{-- Display ID safely and localized --}}
                    <p class="mb-2"><span class="font-semibold">{{ __('Nombor Rujukan:') }}</span>
                        #{{ $approval->approvable?->id ?? 'N/A' }}</p>
                    {{-- Display applicant name safely and localized --}}
                    <p class="mb-2"><span class="font-semibold">{{ __('Pemohon:') }}</span>
                        {{ $approval->approvable?->user?->name ?? 'N/A' }}</p>
                    {{-- Display purpose safely and localized --}}
                    <p class="mb-2"><span class="font-semibold">{{ __('Tujuan Permohonan:') }}</span>
                        {{ $approval->approvable?->purpose ?? 'N/A' }}</p>
                    {{-- Link to the specific email application show page --}}
                    <div class="mt-4">
                        {{-- Use Tailwind link classes and safe routing --}}
                        <a href="{{ $approval->approvable?->id ? route('email-applications.show', $approval->approvable) : '#' }}"
                            class="text-blue-600 hover:text-blue-900 font-semibold">{{ __('Lihat Butiran Permohonan E-mel ICT') }}</a>
                    </div>
                @elseif ($approval->approvable instanceof \App\Models\LoanApplication)
                    <p class="mb-2"><span class="font-semibold">{{ __('Jenis Permohonan:') }}</span>
                        {{ __('Permohonan Pinjaman Peralatan ICT') }}</p>
                    {{-- Display ID safely and localized --}}
                    <p class="mb-2"><span class="font-semibold">{{ __('Nombor Rujukan:') }}</span>
                        #{{ $approval->approvable?->id ?? 'N/A' }}</p>
                    {{-- Display applicant name safely and localized --}}
                    <p class="mb-2"><span class="font-semibold">{{ __('Pemohon:') }}</span>
                        {{ $approval->approvable?->user?->name ?? 'N/A' }}</p>
                    {{-- Display purpose safely and localized --}}
                    <p class="mb-2"><span class="font-semibold">{{ __('Tujuan Permohonan:') }}</span>
                        {{ $approval->approvable?->purpose ?? 'N/A' }}</p>
                    {{-- Display loan dates safely and localized --}}
                    <p class="mb-2"><span class="font-semibold">{{ __('Tarikh Pinjaman:') }}</span>
                        {{ $approval->approvable?->loan_start_date?->format('Y-m-d') ?? 'N/A' }}</p>
                    <p class="mb-2"><span class="font-semibold">{{ __('Tarikh Dijangka Pulang:') }}</span>
                        {{ $approval->approvable?->loan_end_date?->format('Y-m-d') ?? 'N/A' }}</p>
                    {{-- Link to the specific loan application show page --}}
                    <div class="mt-4">
                        {{-- Use Tailwind link classes and safe routing --}}
                        <a href="{{ $approval->approvable?->id ? route('loan-applications.show', $approval->approvable) : '#' }}"
                            class="text-blue-600 hover:text-blue-900 font-semibold">{{ __('Lihat Butiran Permohonan Pinjaman Peralatan ICT') }}</a>
                    </div>
                @else
                    {{-- Message for unknown type, localized --}}
                    <p class="text-gray-600">{{ __('Jenis permohonan berkaitan tidak diketahui.') }}</p>
                @endif
            </div> {{-- End Associated Application Details card --}}


            {{-- Back Button --}}
            <div class="mt-6 text-center"> {{-- Centered the back button --}}
                {{-- Assuming a route named 'approval-dashboard.index' for the main dashboard --}}
                {{-- Use Tailwind classes for the button and SVG --}}
                <a href="{{ route('approval-dashboard.index') }}"
                    class="inline-flex items-center px-5 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    {{ __('Kembali ke Dashboard Kelulusan') }} {{-- Localized --}}
                </a>
            </div>

        </div> {{-- End bg-white card-like container --}}
    </div> {{-- End max-w-3xl container --}}
@endsection {{-- End the content section --}}

{{-- Remove the full HTML structure, Tailwind script include, and custom CSS --}}
{{-- These should be handled by the layout file (layouts.app) --}}
{{-- <head> ... </head> --}}
{{-- <body class="bg-gray-100 p-6"> ... </body> --}}
{{-- </html> --}}
