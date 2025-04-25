<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Pinjaman Peralatan ICT</title> {{-- Updated title --}}
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

        .btn-secondary {
            background-color: #e5e7eb;
            /* gray-200 */
            color: #1f2937;
            /* gray-800 */
            border: 1px solid #e5e7eb;
        }

        .btn-secondary:hover {
            background-color: #d1d5db;
            /* gray-300 */
            border-color: #d1d5db;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            /* rounded-full */
            font-size: 0.75rem;
            /* text-xs */
            font-weight: 600;
            /* font-semibold */
            line-height: 1;
        }

        .badge-info {
            background-color: #bfdbfe;
            /* blue-200 */
            color: #1e40af;
            /* blue-800 */
        }

        .badge-success {
            background-color: #d1fae5;
            /* green-100 */
            color: #065f46;
            /* green-800 */
        }

        .badge-warning {
            background-color: #fef3c7;
            /* yellow-100 */
            color: #b45309;
            /* yellow-800 */
        }

        .badge-danger {
            background-color: #fee2e2;
            /* red-100 */
            border-color: #fecaca;
            /* red-200 */
            color: #991b1b;
            /* red-800 */
        }

        .badge-secondary {
            background-color: #e5e7eb;
            /* gray-200 */
            color: #374151;
            /* gray-700 */
        }

        .badge-teal {
            /* Custom badge for 'issued' */
            background-color: #b2f5ea;
            /* teal-200 */
            color: #2c7a7b;
            /* teal-800 */
        }

        .badge-purple {
            /* Custom badge for 'returned' */
            background-color: #e9d8fd;
            /* purple-200 */
            color: #6b46c1;
            /* purple-800 */
        }

        .badge-red {
            /* Custom badge for 'overdue' */
            background-color: #feb2b2;
            /* red-200 */
            color: #c53030;
            /* red-800 */
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
            <h2 class="text-2xl font-bold mb-6 text-gray-800">Laporan Pinjaman Peralatan ICT</h2> {{-- Updated title --}}

            {{-- Display success messages --}}
            @if (session()->has('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Table to display loan applications for the report --}}
            @if ($applications->isEmpty())
                {{-- Assuming $applications is passed from the controller and contains LoanApplication models --}}
                <p class="text-gray-600">Tiada permohonan pinjaman peralatan ICT ditemui untuk laporan ini.</p>
                {{-- Message if no applications --}}
            @else
                <div class="overflow-x-auto shadow-sm rounded-md border border-gray-200"> {{-- Added overflow and shadow for table container --}}
                    <table class="min-w-full divide-y divide-gray-200 table"> {{-- Converted table classes --}}
                        <thead class="bg-gray-50"> {{-- Added header background --}}
                            <tr>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">
                                    {{-- Converted th classes --}}
                                    Pemohon
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">
                                    Status Permohonan
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">
                                    Item Dimohon
                                </th>
                                {{-- Optional: Add more columns relevant to reports, e.g., Loan Dates, Purpose --}}
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">
                                    Tarikh Pinjaman
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">
                                    Tarikh Dijangka Pulang
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">
                                    Tujuan
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200"> {{-- Added body background and divider --}}
                            {{-- Loop through the collection of loan applications --}}
                            @foreach ($applications as $app)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 border-b">
                                        {{-- Converted td classes --}}
                                        {{ $app->user->name ?? 'N/A' }} {{-- Assuming user relationship with 'name' --}}
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
                                    <td class="px-6 py-4 text-sm text-gray-900 border-b"> {{-- Removed whitespace-nowrap to allow list items to wrap --}}
                                        {{-- Display the list of requested items --}}
                                        @if ($app->items->isNotEmpty())
                                            <ul class="item-list"> {{-- Apply list styling --}}
                                                @foreach ($app->items as $item)
                                                    <li>{{ $item->equipment_type ?? 'N/A' }} (Dimohon:
                                                        {{ $item->quantity_requested ?? 'N/A' }}, Diluluskan:
                                                        {{ $item->quantity_approved ?? 'N/A' }})</li> {{-- Display both requested and approved quantity --}}
                                                @endforeach
                                            </ul>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    {{-- Optional: Display loan dates and purpose --}}
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 border-b">
                                        {{ $app->loan_start_date?->format('Y-m-d') ?? 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 border-b">
                                        {{ $app->loan_end_date?->format('Y-m-d') ?? 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900 border-b"> {{-- Removed whitespace-nowrap --}}
                                        {{ Str::limit($app->purpose, 50) }}
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
            @endif

            {{-- Optional: Back button to a reports dashboard or home --}}
            <div class="mt-6 text-center">
                {{-- Assuming a route named 'reports.index' or similar --}}
                {{-- <a href="{{ route('reports.index') }}" class="btn btn-secondary">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                 Kembali ke Laporan
             </a> --}}
            </div>

        </div> {{-- End max-w-7xl container --}}
    @endsection

</body>

</html>
