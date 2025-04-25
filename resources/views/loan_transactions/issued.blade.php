<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Peralatan Sedang Dipinjam</title>
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

        .btn-success {
            background-color: #48bb78;
            /* green-500 */
            color: #fff;
            border: 1px solid #48bb78;
        }

        .btn-success:hover {
            background-color: #38a169;
            /* green-600 */
            border-color: #38a169;
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

        /* Add badge colors based on the Resource Status Panel component */
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

        .badge-info {
            background-color: #bfdbfe;
            /* blue-200 */
            color: #1e40af;
            /* blue-800 */
        }

        .badge-danger {
            background-color: #fee2e2;
            /* red-100 */
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
            /* For 'issued' */
            background-color: #b2f5ea;
            /* teal-200 */
            color: #2c7a7b;
            /* teal-800 */
        }

        .badge-purple {
            /* For 'returned' or 'partially_returned' */
            background-color: #e9d8fd;
            /* purple-200 */
            color: #6b46c1;
            /* purple-800 */
        }

        .badge-red {
            /* For 'overdue' */
            background-color: #feb2b2;
            /* red-200 */
            color: #c53030;
            /* red-800 */
        }

        .badge-orange {
            /* For 'partially_issued' */
            background-color: #fed7aa;
            /* orange-200 */
            color: #c05621;
            /* orange-800 */
        }
    </style>
</head>

<body class="bg-gray-100 p-6">

    {{-- Extend a layout if you have one, otherwise include basic HTML structure --}}
    @extends('layouts.app')

    @section('content')
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 py-6"> {{-- Converted container to Tailwind --}}
            <h2 class="text-2xl font-bold mb-6 text-gray-800">Senarai Peralatan Sedang Dipinjam</h2> {{-- Title --}}

            {{-- Display success messages --}}
            @if (session()->has('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Table to display issued loan transactions --}}
            {{-- Assuming $issuedTransactions is passed from the controller, filtered for 'issued' or 'partially_returned' status --}}
            @if ($issuedTransactions->isEmpty())
                <p class="text-gray-600">Tiada peralatan sedang dipinjam pada masa ini.</p> {{-- Message if no issued equipment --}}
            @else
                <div class="overflow-x-auto shadow-sm rounded-md border border-gray-200"> {{-- Added overflow and shadow for table container --}}
                    <table class="min-w-full divide-y divide-gray-200 table"> {{-- Converted table classes --}}
                        <thead class="bg-gray-50"> {{-- Added header background --}}
                            <tr>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">
                                    {{-- Converted th classes --}}
                                    Peralatan (Tag ID)
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">
                                    Dipinjam Oleh
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">
                                    Tarikh Dikeluarkan
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">
                                    Tarikh Dijangka Pulang
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">
                                    Status Transaksi
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">
                                    Tindakan
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200"> {{-- Added body background and divider --}}
                            {{-- Loop through the collection of issued loan transactions --}}
                            @foreach ($issuedTransactions as $transaction)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 border-b">
                                        {{-- Converted td classes --}}
                                        {{ $transaction->equipment->brand ?? 'N/A' }}
                                        {{ $transaction->equipment->model ?? 'N/A' }}
                                        (Tag: {{ $transaction->equipment->tag_id ?? 'N/A' }})
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 border-b">
                                        {{ $transaction->loanApplication->user->name ?? 'N/A' }} {{-- Assuming loanApplication and user relationships --}}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 border-b">
                                        {{ $transaction->issue_timestamp?->format('Y-m-d H:i') ?? 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 border-b">
                                        {{ $transaction->loanApplication->loan_end_date?->format('Y-m-d') ?? 'N/A' }}
                                        {{-- Assuming loanApplication relationship --}}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 border-b">
                                        {{-- Display status with a colored badge - can use component or inline logic --}}
                                        <span
                                            class="badge {{ match ($transaction->status) {
                                                'issued' => 'badge-teal',
                                                'partially_returned' => 'badge-purple', // Or badge-orange, depending on color scheme
                                                'overdue' => 'badge-red',
                                                'damaged', 'lost' => 'badge-danger',
                                                default => 'badge-secondary',
                                            } }}">
                                            {{ ucfirst(str_replace('_', ' ', $transaction->status)) }}
                                        </span>
                                        {{-- Or use the component: <x-resource-status-panel :resource="$transaction" /> --}}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 border-b">
                                        {{-- Link to the return interface for this transaction --}}
                                        {{-- Assuming a route named 'loan-transactions.return' exists --}}
                                        <a href="{{ route('loan-transactions.return', $transaction) }}"
                                            class="btn btn-success btn-sm">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                            </svg>
                                            Rekod Pulangan
                                        </a>
                                        {{-- Optional: Link to transaction show page --}}
                                        {{-- <a href="{{ route('loan-transactions.show', $transaction) }}" class="text-blue-600 hover:text-blue-900 font-semibold ml-4">Lihat Butiran</a> --}}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div> {{-- End overflow-x-auto --}}

                {{-- Pagination links --}}
                @if ($issuedTransactions->hasPages())
                    <div class="mt-4">
                        {{ $issuedTransactions->links() }}
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
