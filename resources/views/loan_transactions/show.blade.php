<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Butiran Transaksi Pinjaman Peralatan ICT</title>
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

        .alert-info {
            background-color: #e0f2f7;
            /* cyan-100 */
            border-color: #bae6fd;
            /* cyan-200 */
            color: #0e7490;
            /* cyan-800 */
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
    </style>
</head>

<body class="bg-gray-100 p-6">

    {{-- Extend a layout if you have one, otherwise include basic HTML structure --}}
    @extends('layouts.app')

    @section('content')
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 py-8"> {{-- Container for the content --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6"> {{-- Card-like container --}}

                <h2 class="text-2xl font-bold mb-6 text-gray-800">Butiran Transaksi Pinjaman Peralatan ICT
                    #{{ $loanTransaction->id }}</h2> {{-- Title with transaction ID --}}

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

                {{-- Transaction Details --}}
                <div class="card mb-6">
                    <h3 class="card-title">Butiran Transaksi</h3>

                    <p class="mb-2">
                        <span class="font-semibold">Permohonan Pinjaman:</span>
                        {{-- Link to the related loan application --}}
                        <a href="{{ route('loan-applications.show', $loanTransaction->loanApplication) }}"
                            class="text-blue-600 hover:text-blue-900 font-semibold">
                            #{{ $loanTransaction->loanApplication->id ?? 'N/A' }}
                        </a>
                    </p>
                    <p class="mb-2">
                        <span class="font-semibold">Peralatan:</span>
                        {{-- Link to the related equipment asset --}}
                        <a href="{{ route('equipment.show', $loanTransaction->equipment) }}"
                            class="text-blue-600 hover:text-blue-900 font-semibold">
                            {{ $loanTransaction->equipment->brand ?? 'N/A' }}
                            {{ $loanTransaction->equipment->model ?? 'N/A' }}
                            (Tag: {{ $loanTransaction->equipment->tag_id ?? 'N/A' }})
                        </a>
                    </p>

                    <p class="mb-2">
                        <span class="font-semibold">Status Transaksi:</span>
                        {{-- Display status with a colored badge --}}
                        <span
                            class="badge {{ match ($loanTransaction->status) {
                                'issued' => 'badge-teal',
                                'returned' => 'badge-purple',
                                'overdue' => 'badge-red',
                                'lost', 'damaged' => 'badge-danger',
                                default => 'badge-secondary',
                            } }}">
                            {{ ucfirst(str_replace('_', ' ', $loanTransaction->status)) }}
                        </span>
                    </p>

                    {{-- Issue Details --}}
                    <h4 class="text-lg font-semibold mt-4 mb-2 text-gray-700">Butiran Pengeluaran</h4>
                    <p class="mb-2"><span class="font-semibold">Pegawai Pengeluar:</span>
                        {{ $loanTransaction->issuingOfficer->name ?? 'N/A' }}</p> {{-- Assuming issuingOfficer relationship --}}
                    <p class="mb-2"><span class="font-semibold">Tarikh & Masa Pengeluaran:</span>
                        {{ $loanTransaction->issue_timestamp?->format('Y-m-d H:i') ?? 'N/A' }}</p>
                    <p class="mb-2"><span class="font-semibold">Aksesori Dikeluarkan:</span>
                        {{ implode(', ', json_decode($loanTransaction->accessories_checklist_on_issue, true) ?? []) }}</p>
                    <p class="mb-2"><span class="font-semibold">Catatan Pengeluaran:</span>
                        {{ $loanTransaction->issue_notes ?? '-' }}</p>

                    {{-- Return Details (only show if returned or has return-related status) --}}
                    @if (in_array($loanTransaction->status, ['returned', 'damaged', 'lost', 'overdue']))
                        {{-- Show return details if status indicates return process started/completed --}}
                        <h4 class="text-lg font-semibold mt-4 mb-2 text-gray-700">Butiran Pulangan</h4>
                        <p class="mb-2"><span class="font-semibold">Pegawai Terima Pulangan:</span>
                            {{ $loanTransaction->returnAcceptingOfficer->name ?? 'N/A' }}</p> {{-- Assuming returnAcceptingOfficer relationship --}}
                        <p class="mb-2"><span class="font-semibold">Tarikh & Masa Pulangan:</span>
                            {{ $loanTransaction->return_timestamp?->format('Y-m-d H:i') ?? 'N/A' }}</p>
                        <p class="mb-2"><span class="font-semibold">Aksesori Dipulangkan:</span>
                            {{ implode(', ', json_decode($loanTransaction->accessories_checklist_on_return, true) ?? []) }}
                        </p>
                        <p class="mb-2"><span class="font-semibold">Catatan Pulangan:</span>
                            {{ $loanTransaction->return_notes ?? '-' }}</p>

                        {{-- Display Damaged/Lost Status if applicable --}}
                        @if ($loanTransaction->status === 'damaged')
                            <div class="alert alert-danger mt-3">
                                <p class="font-semibold">Status Penemuan Semasa Pulangan:</p>
                                <p>Peralatan Ditemui Rosak.</p>
                            </div>
                        @elseif ($loanTransaction->status === 'lost')
                            <div class="alert alert-danger mt-3">
                                <p class="font-semibold">Status Penemuan Semasa Pulangan:</p>
                                <p>Peralatan Dilaporkan Hilang.</p>
                            </div>
                        @endif
                    @endif

                </div> {{-- End card --}}


                {{-- Back Button --}}
                <div class="mt-6 text-center"> {{-- Centered the back button --}}
                    {{-- Link back to the loan application show page --}}
                    <a href="{{ route('loan-applications.show', $loanTransaction->loanApplication) }}"
                        class="btn btn-secondary">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Kembali ke Butiran Permohonan
                    </a>
                </div>

            </div> {{-- End bg-white card --}}
        </div> {{-- End container --}}
    @endsection

</body>

</html>
