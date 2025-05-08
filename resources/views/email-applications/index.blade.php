{{-- resources/views/my-applications/email/index.blade.php --}}

{{-- Extend a layout if you have one --}}
@extends('layouts.app')

{{-- Set the page title --}}
@section('title', 'Senarai Permohonan E-mel ICT') {{-- Page title --}}

{{-- Define the content section --}}
@section('content')

    {{-- Removed <!DOCTYPE html>, <html>, <head>, <body> tags --}}
    {{-- Removed <script src="https://cdn.tailwindcss.com"></script> - Tailwind should be in the layout --}}
    {{-- Removed <style> block - CSS should be in the layout or separate asset files --}}


    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 py-6"> {{-- Converted container to Tailwind --}}
        <h2 class="text-2xl font-bold mb-6 text-gray-800">Senarai Permohonan E-mel ICT</h2> {{-- Converted h2 --}}
        {{-- CORRECTED ROUTE NAME: Use the full registered name 'resource-management.email-applications.create' --}}
        <a href="{{ route('resource-management.email-applications.create') }}" class="btn btn-primary mb-4">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
            </svg>
            Permohonan Baru
        </a>

        {{-- Display success messages --}}
        @if (session()->has('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        {{-- Table to display email applications --}}
        @if ($applications->isEmpty())
            <p class="text-gray-600">Tiada permohonan e-mel ICT ditemui.</p> {{-- Message if no applications --}}
        @else
            <div class="overflow-x-auto shadow-sm rounded-md border border-gray-200"> {{-- Added overflow and shadow for table container --}}
                <table class="min-w-full divide-y divide-gray-200 table"> {{-- Converted table classes --}}
                    <thead class="bg-gray-50"> {{-- Added header background --}}
                        <tr>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">
                                {{-- Converted th classes --}}
                                Tujuan Permohonan
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">
                                Status
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">
                                Cadangan E-mel / E-mel Akhir
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">
                                Tarikh Hantar
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">
                                Tindakan
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200"> {{-- Added body background and divider --}}
                        {{-- Loop through the collection of email applications --}}
                        @foreach ($applications as $app)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 border-b">
                                    {{-- Converted td classes --}}
                                    {{ Str::limit($app->purpose, 50) }} {{-- Limit purpose text for brevity --}}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 border-b">
                                    {{-- Display status with a colored badge --}}
                                    <span
                                        class="badge {{ match ($app->status) {
                                            'draft' => 'badge-secondary',
                                            'pending_support', 'pending_admin', 'processing' => 'badge-warning',
                                            'approved' => 'badge-info', // Approved but not yet provisioned
                                            'completed' => 'badge-success', // Provisioned successfully
                                            'rejected', 'provision_failed' => 'badge-danger',
                                            default => 'badge-secondary',
                                        } }}">
                                        {{ ucfirst(str_replace('_', ' ', $app->status)) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 border-b">
                                    {{-- Display final assigned email if available, otherwise proposed email --}}
                                    {{ $app->final_assigned_email ?? ($app->proposed_email ?? '-') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 border-b">
                                    {{ $app->created_at->format('d M Y') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 border-b">
                                    {{-- Link to view application details --}}
                                    {{-- CORRECTED ROUTE NAME: Use the full registered name 'my-applications.email.show' --}}
                                    <a href="{{ route('my-applications.email.show', $app->id) }}"
                                        class="text-blue-600 hover:text-blue-900 font-semibold">Lihat</a>
                                    {{-- Optional: Edit button if status is 'draft' and user is authorized --}}
                                    @if ($app->status === 'draft')
                                        {{-- CORRECTED ROUTE NAME: Link to the create/edit form route, passing the application ID --}}
                                        <a href="{{ route('resource-management.email-applications.create', $app->id) }}"
                                            class="text-indigo-600 hover:text-indigo-900 font-semibold ml-4">Edit</a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div> {{-- End overflow-x-auto --}}

            {{-- Pagination links --}}
            @if ($applications->hasPages())
                <div class="mt-4">
                    {{ $applications->links() }}
                </div>
            @endif

        @endif

    </div> {{-- End max-w-7xl container --}}

@endsection {{-- End content section --}}

{{-- The custom styles block should be in your main layout's <head> --}}
{{-- @push('styles')
<style>
    /* ... your custom styles here ... */
</style>
@endpush --}}

{{-- The Tailwind CDN script should be in your main layout's <head> --}}
{{-- @push('scripts')
<script src="https://cdn.tailwindcss.com"></script>
@endpush --}}
