<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pinjaman Menunggu Pengeluaran</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Optional: Add custom styles if needed, but prefer Tailwind */
        .alert {
            padding: 1rem;
            border-radius: 0.25rem;
            margin-bottom: 1rem;
            border-width: 1px;
        }

        .alert-success {
            background-color: #d1fae5;
            /* green-100 */
            border-color: #a7f3d0;
            /* green-200 */
            color: #065f46;
            /* green-800 */
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }

        .table th,
        .table td {
            padding: 0.75rem;
            border: 1px solid #e5e7eb;
            /* gray-200 */
            text-align: left;
        }

        .table th {
            background-color: #f9fafb;
            /* gray-50 */
            font-weight: bold;
            text-transform: uppercase;
            font-size: 0.75rem;
            /* text-xs */
            color: #4b5563;
            /* gray-600 */
        }

        .table tbody tr:nth-child(odd) {
            background-color: #f9fafb;
            /* gray-50 */
        }

        .table tbody tr:hover {
            background-color: #f3f4f6;
            /* gray-100 */
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.5rem 1.25rem;
            border-radius: 0.375rem;
            /* rounded-md */
            font-weight: 600;
            /* font-semibold */
            transition: background-color 0.15s ease-in-out, border-color 0.15s ease-in-out, color 0.15s ease-in-out;
            outline: none;
            cursor: pointer;
        }

        .btn-primary {
            background-color: #3b82f6;
            /* blue-500 */
            color: #fff;
            border: 1px solid #3b82f6;
        }

        .btn-primary:hover {
            background-color: #2563eb;
            /* blue-600 */
            border-color: #2563eb;
        }

        .btn-info {
            background-color: #38b2ac;
            /* teal-500 */
            color: #fff;
            border: 1px solid #38b2ac;
        }

        .btn-info:hover {
            background-color: #319795;
            /* teal-600 */
            border-color: #319795;
        }

        .item-list {
            list-style: disc;
            /* Add bullet points */
            padding-left: 1.5rem;
            /* Add left padding for list */
        }

        .item-list li {
            margin-bottom: 0.25rem;
            /* Small margin between list items */
        }
    </style>
</head>

<body class="bg-gray-100 p-6">

    {{-- Extend a layout if you have one, otherwise include basic HTML structure --}}
    @extends('layouts.app')

    @section('content')
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 py-6"> {{-- Converted container to Tailwind --}}
            <h2 class="text-2xl font-bold mb-6 text-gray-800">Senarai Pinjaman Menunggu Pengeluaran</h2>
            {{-- Title --}}

            {{-- Display success messages --}}
            @if (session()->has('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Table to display loan applications ready for issuance --}}
            {{-- Assuming $loanApplications is passed from the controller, filtered for 'approved' or similar status --}}
            @if ($loanApplications->isEmpty())
                <p class="text-gray-600">Tiada permohonan pinjaman menunggu pengeluaran pada masa ini.</p>
                {{-- Message if no applications --}}
            @else
                <div class="overflow-x-auto shadow-sm rounded-md border border-gray-200"> {{-- Added overflow and shadow for table container --}}
                    <table class="min-w-full divide-y divide-gray-200 table"> {{-- Converted table classes --}}
                        <thead class="bg-gray-50"> {{-- Added header background --}}
                            <tr>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">
                                    {{-- Converted th classes --}}
                                    Permohonan #
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">
                                    Pemohon
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">
                                    Tujuan
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">
                                    Tarikh Dijangka Pulang
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">
                                    Item Diluluskan
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">
                                    Tindakan
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200"> {{-- Added body background and divider --}}
                            {{-- Loop through the collection of loan applications --}}
                            @foreach ($loanApplications as $application)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 border-b">
                                        {{-- Converted td classes --}}
                                        {{ $application->id }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 border-b">
                                        {{ $application->user->name ?? 'N/A' }} {{-- Assuming user relationship with 'name' --}}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900 border-b"> {{-- Removed whitespace-nowrap --}}
                                        {{ Str::limit($application->purpose, 50) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 border-b">
                                        {{ $application->loan_end_date?->format('Y-m-d') ?? 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900 border-b"> {{-- Removed whitespace-nowrap to allow list items to wrap --}}
                                        {{-- Display the list of approved items --}}
                                        @if ($application->items->isNotEmpty())
                                            <ul class="item-list"> {{-- Apply list styling --}}
                                                @foreach ($application->items->where('quantity_approved', '>', 0) as $item)
                                                    <li>{{ $item->equipment_type ?? 'N/A' }} (Diluluskan:
                                                        {{ $item->quantity_approved ?? 'N/A' }})</li>
                                                @endforeach
                                            </ul>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 border-b">
                                        {{-- Link to the issuance interface for this application --}}
                                        {{-- Assuming a route named 'loan-transactions.issue' exists --}}
                                        <a href="{{ route('loan-transactions.issue', $application) }}"
                                            class="btn btn-info btn-sm">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M9 12l2 2 4-4m5.615 3.915c-.974 1.11-2.237 1.95-3.593 2.464-1.356.515-2.79.78-4.24.78s-2.884-.265-4.24-.78c-1.356-.514-2.619-1.354-3.593-2.464S1 10.59 1 9c0-1.59.32-3.17.959-4.641C2.598 3.089 3.861 2.249 5.217 1.734 6.573 1.22 8.007.955 9.457.955s2.884.265 4.24.78c1.356.515 2.619 1.355 3.593 2.464S19 7.41 19 9c0 1.59-.32 3.17-.959 4.641z" />
                                            </svg>
                                            Keluarkan Peralatan
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div> {{-- End overflow-x-auto --}}

                {{-- Pagination links --}}
                @if ($loanApplications->hasPages())
                    <div class="mt-4">
                        {{ $loanApplications->links() }}
                    </div>
                @endif
            @endif

            {{-- Back button (Optional - link to BPM dashboard or similar) --}}
            {{-- <div class="mt-6 text-center">
             <a href="{{ route('admin.bpm.index') }}" class="btn btn-secondary">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                 Kembali ke Dashboard BPM
             </a>
         </div> --}}

        </div> {{-- End max-w-7xl container --}}
    @endsection

</body>

</html>
