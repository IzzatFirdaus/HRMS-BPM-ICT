{{-- resources/views/loan-applications/index.blade.php --}}

{{-- Extend your main layout file. Remove standalone HTML structure if extending a layout. --}}
@extends('layouts.app')

@section('title', 'Senarai Permohonan Pinjaman Peralatan ICT') {{-- Set a specific title --}}

@section('content') {{-- Start the content section --}}

    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 py-6"> {{-- Container for layout --}}

        {{-- Page Title --}}
        <h2 class="text-2xl font-bold mb-6 text-gray-800">Senarai Permohonan Pinjaman Peralatan ICT</h2>

        {{-- Link to create a new application (Corrected route name) --}}
        <a href="{{ route('resource-management.loan-applications.create') }}" class="btn btn-primary mb-4">
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

        {{-- Table to display loan applications --}}
        @if ($applications->isEmpty())
            {{-- Message if no applications --}}
            <p class="text-gray-600">Tiada permohonan pinjaman peralatan ICT ditemui.</p>
        @else
            <div class="overflow-x-auto shadow-sm rounded-md border border-gray-200"> {{-- Table container --}}
                <table class="min-w-full divide-y divide-gray-200 table"> {{-- Table classes --}}
                    <thead class="bg-gray-50"> {{-- Table header --}}
                        <tr>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">
                                Tujuan Permohonan</th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">
                                Tarikh Pinjaman</th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">
                                Tarikh Dijangka Pulang</th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">
                                Status</th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">
                                Tarikh Hantar</th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">
                                Tindakan</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200"> {{-- Table body --}}
                        {{-- Loop through the collection of loan applications --}}
                        @foreach ($applications as $app)
                            <tr>
                                <td class="px-6 py-4 text-sm text-gray-900 border-b"> {{-- Removed whitespace-nowrap to allow text wrap --}}
                                    {{ Str::limit($app->purpose, 50) }} {{-- Limit purpose text --}}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 border-b">
                                    {{ $app->loan_start_date?->format('d M Y') ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 border-b">
                                    {{ $app->loan_end_date?->format('d M Y') ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 border-b">
                                    {{-- Display status with a colored badge --}}
                                    <span
                                        class="badge {{ match ($app->status) {
                                            'draft' => 'badge-secondary',
                                            'pending_support' => 'badge-warning',
                                            'approved' => 'badge-info',
                                            'partially_issued', 'issued' => 'badge-teal',
                                            'returned' => 'badge-purple',
                                            'overdue' => 'badge-red',
                                            'rejected', 'cancelled' => 'badge-danger',
                                            default => 'badge-secondary',
                                        } }}">
                                        {{ ucfirst(str_replace('_', ' ', $app->status)) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 border-b">
                                    {{ $app->created_at->format('d M Y') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 border-b">
                                    {{-- Link to view application details --}}
                                    {{-- Assuming a route named 'my-applications.loan.show' exists based on route:list --}}
                                    <a href="{{ route('my-applications.loan.show', $app) }}"
                                        class="text-blue-600 hover:text-blue-900 font-semibold">Lihat</a>
                                    {{-- Optional: Edit button if status is 'draft' and user is authorized --}}
                                    {{-- You'll need to define the edit route and logic --}}
                                    {{-- @if ($app->status === 'draft') --}}
                                    {{-- Assuming a route named 'resource-management.loan-applications.edit' or similar --}}
                                    {{-- <a href="{{ route('resource-management.loan-applications.edit', $app) }}" --}}
                                    {{-- class="text-indigo-600 hover:text-indigo-900 font-semibold ml-4">Edit</a> --}}
                                    {{-- @endif --}}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div> {{-- End overflow-x-auto --}}

            {{-- Pagination links --}}
            @if ($applications->hasPages())
                {{-- Check if the collection is paginated --}}
                <div class="mt-4">
                    {{ $applications->links() }}
                </div>
            @endif

        @endif {{-- End of if ($applications->isEmpty()) --}}

        {{-- Optional: Back button --}}
        <div class="mt-6 text-center">
            {{-- Add a back button if needed --}}
        </div>

    </div> {{-- End max-w-7xl container --}}

@endsection {{-- End of content section --}}
