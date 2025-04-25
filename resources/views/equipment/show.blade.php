<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Butiran Peralatan ICT</title>
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

        .alert-danger {
            background-color: #fee2e2;
            /* red-100 */
            border-color: #fecaca;
            /* red-200 */
            color: #991b1b;
            /* red-800 */
        }

        .card {
            border: 1px solid #d1d5db;
            /* gray-300 */
            border-radius: 0.5rem;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            background-color: #fff;
            /* white */
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
        }

        .card-title {
            font-size: 1.25rem;
            /* text-xl */
            font-weight: bold;
            margin-bottom: 1rem;
            color: #1f2937;
            /* gray-800 */
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
    </style>
</head>

<body class="bg-gray-100 p-6">

    {{-- Extend a layout if you have one, otherwise include basic HTML structure --}}
    @extends('layouts.app')

    @section('content')
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 py-8"> {{-- Container for the content --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6"> {{-- Card-like container --}}

                <h2 class="text-2xl font-bold mb-6 text-gray-800">Butiran Peralatan ICT #{{ $equipment->id }} (Tag:
                    {{ $equipment->tag_id ?? 'N/A' }})</h2> {{-- Title with equipment ID/Tag ID --}}

                {{-- Display success or error messages --}}
                @if (session()->has('success'))
                    <div class="alert alert-success mb-4">
                        {{ session('success') }}
                    </div>
                @endif

                @if (session()->has('error'))
                    <div class="alert alert-danger mb-4">
                        {{ session('error') }}
                    </div>
                @endif

                {{-- Equipment Details --}}
                <div class="card">
                    <h3 class="card-title">Butiran Peralatan</h3>

                    <p class="mb-2"><span class="font-semibold">Jenis Aset:</span> {{ $equipment->asset_type ?? 'N/A' }}
                    </p>
                    <p class="mb-2"><span class="font-semibold">Jenama & Model:</span> {{ $equipment->brand ?? 'N/A' }}
                        {{ $equipment->model ?? 'N/A' }}</p>
                    <p class="mb-2"><span class="font-semibold">Tag ID MOTAC:</span> {{ $equipment->tag_id ?? 'N/A' }}</p>
                    <p class="mb-2"><span class="font-semibold">Nombor Siri:</span>
                        {{ $equipment->serial_number ?? 'N/A' }}</p>
                    <p class="mb-2"><span class="font-semibold">Tarikh Pembelian:</span>
                        {{ $equipment->purchase_date?->format('Y-m-d') ?? 'N/A' }}</p>
                    <p class="mb-2"><span class="font-semibold">Tarikh Tamat Waranti:</span>
                        {{ $equipment->warranty_expiry_date?->format('Y-m-d') ?? 'N/A' }}</p>
                    <p class="mb-2">
                        <span class="font-semibold">Status:</span>
                        {{-- Display status with a colored badge --}}
                        <span
                            class="badge {{ match ($equipment->status) {
                                'available' => 'badge-success',
                                'on_loan' => 'badge-warning',
                                'under_maintenance' => 'badge-info',
                                'disposed', 'lost', 'damaged' => 'badge-danger',
                                default => 'badge-secondary',
                            } }}">
                            {{ ucfirst(str_replace('_', ' ', $equipment->status)) }}
                        </span>
                    </p>
                    <p class="mb-2"><span class="font-semibold">Lokasi Semasa:</span>
                        {{ $equipment->current_location ?? 'N/A' }}</p>
                    <p class="mb-2"><span class="font-semibold">Catatan:</span> {{ $equipment->notes ?? '-' }}</p>
                </div> {{-- End card --}}

                {{-- Optional: Display Loan History for this equipment --}}
                {{-- Assuming a 'transactions' relationship exists on the Equipment model --}}
                @if ($equipment->transactions->isNotEmpty())
                    <div class="card">
                        <h3 class="card-title">Sejarah Pinjaman (Transaksi)</h3>
                        <div class="overflow-x-auto shadow-sm rounded-md border border-gray-200">
                            <table class="min-w-full divide-y divide-gray-200 table">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">
                                            Permohonan Pinjaman #
                                        </th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">
                                            Status Transaksi
                                        </th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">
                                            Pegawai Pengeluar
                                        </th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">
                                            Tarikh Pengeluaran
                                        </th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">
                                            Pegawai Terima Pulangan
                                        </th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">
                                            Tarikh Pulangan
                                        </th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">
                                            Catatan Pulangan
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    {{-- Loop through the transactions relationship --}}
                                    @foreach ($equipment->transactions as $transaction)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 border-b">
                                                {{-- Link to the loan application show page --}}
                                                <a href="{{ route('loan-applications.show', $transaction->loanApplication) }}"
                                                    class="text-blue-600 hover:text-blue-900 font-semibold">
                                                    #{{ $transaction->loanApplication->id ?? 'N/A' }}
                                                </a>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 border-b">
                                                <span
                                                    class="badge {{ match ($transaction->status) {
                                                        'issued' => 'badge-teal',
                                                        'returned' => 'badge-purple',
                                                        'overdue' => 'badge-red',
                                                        'lost', 'damaged' => 'badge-danger',
                                                        default => 'badge-secondary',
                                                    } }}">
                                                    {{ ucfirst(str_replace('_', ' ', $transaction->status)) }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 border-b">
                                                {{ $transaction->issuingOfficer->name ?? 'N/A' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 border-b">
                                                {{ $transaction->issue_timestamp?->format('Y-m-d H:i') ?? 'N/A' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 border-b">
                                                {{ $transaction->returnAcceptingOfficer->name ?? 'N/A' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 border-b">
                                                {{ $transaction->return_timestamp?->format('Y-m-d H:i') ?? 'N/A' }}
                                            </td>
                                            <td class="px-6 py-4 text-sm text-gray-900 border-b"> {{-- Removed whitespace-nowrap --}}
                                                {{ $transaction->return_notes ?? '-' }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div> {{-- End overflow-x-auto --}}
                    </div> {{-- End card --}}
                @endif


                {{-- Back Button --}}
                <div class="mt-6 text-center"> {{-- Centered the back button --}}
                    <a href="{{ route('equipment.index') }}" class="btn btn-secondary">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Kembali ke Senarai Peralatan
                    </a>
                </div>

            </div> {{-- End bg-white card --}}
        </div> {{-- End container --}}
    @endsection

</body>

</html>
