{{-- resources/views/reports/loan-applications.blade.php --}}
{{-- This Blade view file displays a report of ICT Equipment Loan Applications. --}}
{{-- It receives a paginated collection named $loanApplications from the controller. --}}

{{-- We are extending a layout, so remove full HTML document structure tags --}}
{{-- Removed: <!DOCTYPE html> tags --}}
{{-- Removed: <html lang="..."> tags --}}
{{-- Removed: <head> tags --}}
{{-- Removed: <meta charset="..."> tags --}}
{{-- Removed: <meta name="viewport"...> tags --}}
{{-- Removed: <title> tag (handled by @section('title')) --}}
{{-- Removed: <script src="https://cdn.tailwindcss.com"></script> (should be in the layout) --}}
{{-- Custom styles can be included here if needed for this specific page, or in the layout --}}

{{-- Extend your main application layout --}}
@extends('layouts.app') {{-- <--- **IMPORTANT:** Adjust 'layouts.app' if your main layout file is named differently --}}

{{-- Set a title for the page --}}
@section('title', __('Laporan Permohonan Pinjaman Peralatan ICT'))

{{-- Define the main content section --}}
@section('content')
    <div class="container mx-auto py-6"> {{-- Use container styling consistent with other views --}}
        <h1 class="text-2xl font-bold mb-6 text-gray-800">{{ __('Laporan Permohonan Pinjaman Peralatan ICT') }}</h1>
        {{-- Page title --}}

        {{-- Display success messages from session if needed --}}
        @if (session()->has('success'))
            <div class="bg-green-100 border border-green-200 text-green-800 p-4 rounded-md mb-4">
                {{ session('success') }} {{-- Display the success message --}}
            </div>
        @endif

        {{-- Display error messages from session if needed --}}
        @if (session()->has('error'))
            <div class="bg-red-100 border border-red-200 text-red-800 p-4 rounded-md mb-4">
                {{ session('error') }} {{-- Display the error message --}}
            </div>
        @endif

        {{-- Table to display loan applications for the report --}}
        {{-- Check if the $loanApplications collection is empty --}}
        @if ($loanApplications->isEmpty()) {{-- Corrected variable name from $applications to $loanApplications --}}
            {{-- Message if no applications - Applied styling matching other views empty message --}}
            <p class="text-gray-600">{{ __('Tiada permohonan pinjaman peralatan ICT ditemui untuk laporan ini.') }}</p>
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
                                Pegawai Bertanggungjawab
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Tujuan
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Lokasi Pinjaman
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Tarikh Pinjaman
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Tarikh Dijangka Pulang
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status
                            </th>
                            {{-- Add more headers as needed based on the LoanApplication model and relationships --}}
                        </tr>
                    </thead>
                    {{-- Table body - Applied Tailwind tbody and tr/td classes --}}
                    <tbody class="bg-white divide-y divide-gray-200">
                        {{-- Loop through the loan applications --}}
                        @foreach ($loanApplications as $application)
                            {{-- Corrected variable name --}}
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $application->id }} {{-- Display Application ID --}}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $application->user->full_name ?? ($application->user->name ?? 'N/A') }}
                                    {{-- Display Applicant's name, using nullish coalescing --}}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $application->responsibleOfficer->full_name ?? ($application->responsibleOfficer->name ?? 'Sama seperti Pemohon') }}
                                    {{-- Display Responsible Officer's name --}}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $application->purpose ?? 'N/A' }} {{-- Display Purpose --}}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $application->location ?? 'N/A' }} {{-- Assuming location is stored directly or is a relationship name/accessor --}}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $application->loan_start_date?->format('Y-m-d') ?? 'N/A' }} {{-- Display Loan Start Date, format if Carbon instance, use nullsafe operator --}}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $application->loan_end_date?->format('Y-m-d') ?? 'N/A' }} {{-- Display Expected Return Date, format if Carbon instance, use nullsafe operator --}}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $application->status ?? 'N/A' }} {{-- Display Status --}}
                                </td>
                                {{-- Add more data cells for other fields (e.g., links to view details, actions) --}}
                                {{-- Example: Display requested items (optional, might be too verbose for a report list) --}}
                                {{-- <td class="px-6 py-4 text-sm text-gray-900">
                                     @if ($application->items->count())
                                         <ul>
                                             @foreach ($application->items as $item)
                                                 <li>{{ $item->quantity_requested }} x {{ $item->equipment_type }}</li>
                                             @endforeach
                                         </ul>
                                     @else
                                         Tiada Item
                                     @endif
                                 </td> --}}
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination links --}}
            {{-- Ensure $loanApplications is a paginator instance before calling links() --}}
            @if ($loanApplications instanceof \Illuminate\Pagination\LengthAwarePaginator)
                <div class="mt-4"> {{-- Add margin top --}}
                    {{ $loanApplications->links() }} {{-- Renders Tailwind-styled pagination links --}}
                </div>
            @endif

        @endif {{-- End of if ($loanApplications->isEmpty()) --}}

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

    </div> {{-- End container --}}
@endsection

{{-- Removed: </body> tags --}}
{{-- Removed: </html> tags --}}
