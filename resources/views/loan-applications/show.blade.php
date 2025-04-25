<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Butiran Permohonan Pinjaman Peralatan ICT</title>
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
    </style>
</head>

<body class="bg-gray-100 p-6">

    {{-- Extend a layout if you have one, otherwise include basic HTML structure --}}
    @extends('layouts.app')

    @section('content')
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 py-8"> {{-- Container for the content --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6"> {{-- Card-like container --}}

                <h2 class="text-2xl font-bold mb-6 text-gray-800">Butiran Permohonan Pinjaman Peralatan ICT
                    #{{ $loanApplication->id }}</h2> {{-- Title with application ID --}}

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

                {{-- Application Status --}}
                <div class="mb-6">
                    <p class="text-lg font-semibold text-gray-700">Status Semasa:</p>
                    <span
                        class="badge {{ match ($loanApplication->status) {
                            'draft' => 'badge-secondary',
                            'pending_support' => 'badge-warning',
                            'approved' => 'badge-info',
                            'partially_issued', 'issued' => 'badge-teal', // Use custom badge for issued/partially issued
                            'returned' => 'badge-purple', // Use custom badge for returned
                            'overdue' => 'badge-red', // Use custom badge for overdue
                            'rejected', 'cancelled' => 'badge-danger',
                            default => 'badge-secondary',
                        } }} text-lg">
                        {{-- Increased badge size --}}
                        {{ ucfirst(str_replace('_', ' ', $loanApplication->status)) }}
                    </span>

                    {{-- Display rejection reason if rejected --}}
                    @if ($loanApplication->status === 'rejected' && $loanApplication->rejection_reason)
                        <div class="alert alert-danger mt-3">
                            <p class="font-semibold">Sebab Penolakan:</p>
                            <p>{{ $loanApplication->rejection_reason }}</p>
                        </div>
                    @endif
                </div>

                {{-- BAHAGIAN 1 | MAKLUMAT PEMOHON --}}
                <div class="card">
                    <h3 class="card-title">BAHAGIAN 1 | MAKLUMAT PEMOHON</h3>
                    {{-- Assuming relationships and attributes exist on the User model --}}
                    <p class="mb-2"><span class="font-semibold">Nama Penuh:</span>
                        {{ $loanApplication->user->name ?? 'N/A' }}</p>
                    <p class="mb-2"><span class="font-semibold">Jawatan & Gred:</span>
                        {{ $loanApplication->user->position->name ?? 'N/A' }} &
                        {{ $loanApplication->user->grade->name ?? 'N/A' }}</p>
                    <p class="mb-2"><span class="font-semibold">Bahagian/Unit:</span>
                        {{ $loanApplication->user->department->name ?? 'N/A' }}</p>
                    <p class="mb-2"><span class="font-semibold">No. Telefon:</span>
                        {{ $loanApplication->user->phone_number ?? 'N/A' }}</p>
                </div>

                {{-- Loan Details --}}
                <div class="card">
                    <h3 class="card-title">BUTIRAN PINJAMAN</h3>
                    <p class="mb-2"><span class="font-semibold">Tujuan Permohonan:</span>
                        {{ $loanApplication->purpose ?? 'N/A' }}</p>
                    <p class="mb-2"><span class="font-semibold">Lokasi Penggunaan Peralatan:</span>
                        {{ $loanApplication->location ?? 'N/A' }}</p>
                    <p class="mb-2"><span class="font-semibold">Tarikh Pinjaman:</span>
                        {{ $loanApplication->loan_start_date?->format('Y-m-d') ?? 'N/A' }}</p>
                    <p class="mb-2"><span class="font-semibold">Tarikh Dijangka Pulang:</span>
                        {{ $loanApplication->loan_end_date?->format('Y-m-d') ?? 'N/A' }}</p>
                </div>

                {{-- BAHAGIAN 2 | MAKLUMAT PEGAWAI BERTANGGUNGJAWAB --}}
                <div class="card">
                    <h3 class="card-title">BAHAGIAN 2 | MAKLUMAT PEGAWAI BERTANGGUNGJAWAB</h3>
                    @if ($loanApplication->responsibleOfficer)
                        <p class="mb-2"><span class="font-semibold">Nama Penuh:</span>
                            {{ $loanApplication->responsibleOfficer->name ?? 'N/A' }}</p>
                        <p class="mb-2"><span class="font-semibold">Jawatan & Gred:</span>
                            {{ $loanApplication->responsibleOfficer->position->name ?? 'N/A' }} &
                            {{ $loanApplication->responsibleOfficer->grade->name ?? 'N/A' }}</p>
                        <p class="mb-2"><span class="font-semibold">No. Telefon:</span>
                            {{ $loanApplication->responsibleOfficer->phone_number ?? 'N/A' }}</p>
                    @else
                        <p class="text-gray-600">Pemohon adalah Pegawai Bertanggungjawab.</p>
                    @endif
                </div>


                {{-- BAHAGIAN 3 | MAKLUMAT PERALATAN DIMOHON --}}
                <div class="card">
                    <h3 class="card-title">BAHAGIAN 3 | MAKLUMAT PERALATAN DIMOHON</h3>
                    @if ($loanApplication->items->isEmpty())
                        <p class="text-gray-600">Tiada item peralatan dimohon untuk permohonan ini.</p>
                    @else
                        <div class="overflow-x-auto shadow-sm rounded-md border border-gray-200">
                            <table class="min-w-full divide-y divide-gray-200 table">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">
                                            Bil.</th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">
                                            Jenis Peralatan</th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">
                                            Kuantiti Dimohon</th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">
                                            Kuantiti Diluluskan</th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">
                                            Catatan</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    {{-- Loop through the loan application items relationship --}}
                                    @foreach ($loanApplication->items as $item)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 border-b">
                                                {{ $loop->iteration }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 border-b">
                                                {{ $item->equipment_type ?? 'N/A' }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 border-b">
                                                {{ $item->quantity_requested ?? 'N/A' }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 border-b">
                                                {{ $item->quantity_approved ?? 'N/A' }}</td>
                                            <td class="px-6 py-4 text-sm text-gray-900 border-b">{{ $item->notes ?? '-' }}
                                            </td> {{-- Removed whitespace-nowrap --}}
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div> {{-- End overflow-x-auto --}}
                    @endif
                </div>

                {{-- BAHAGIAN 4 | PENGESAHAN PEMOHON --}}
                <div class="card">
                    <h3 class="card-title">BAHAGIAN 4 | PENGESAHAN PEMOHON (PEGAWAI BERTANGGUNGJAWAB)</h3>
                    <p class="mb-2">
                        <span class="font-semibold">Status Pengesahan:</span>
                        @if ($loanApplication->applicant_confirmation_timestamp)
                            <span class="text-green-600 font-semibold">Diterima</span>
                        @else
                            <span class="text-red-600 font-semibold">Belum Diterima</span>
                        @endif
                    </p>
                    <p class="mb-2"><span class="font-semibold">Tarikh Pengesahan:</span>
                        {{ $loanApplication->applicant_confirmation_timestamp?->format('Y-m-d H:i') ?? 'N/A' }}</p>
                    {{-- You might display the full certification text here if needed --}}
                </div>


                {{-- Transaction History (Issued and Returned Equipment) --}}
                <div class="card">
                    <h3 class="card-title">Sejarah Transaksi Pinjaman (Pengeluaran & Pulangan)</h3>
                    @if ($loanApplication->transactions->isEmpty())
                        <p class="text-gray-600">Tiada sejarah transaksi untuk permohonan ini.</p>
                    @else
                        <div class="overflow-x-auto shadow-sm rounded-md border border-gray-200">
                            <table class="min-w-full divide-y divide-gray-200 table">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">
                                            Peralatan (Tag ID)
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
                                    {{-- Loop through the loan transactions relationship --}}
                                    @foreach ($loanApplication->transactions as $transaction)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 border-b">
                                                {{ $transaction->equipment->asset_type ?? 'N/A' }}
                                                ({{ $transaction->equipment->tag_id ?? 'N/A' }})
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
                    @endif
                </div>


                {{-- Approval History --}}
                <div class="card">
                    <h3 class="card-title">Sejarah Kelulusan</h3>
                    @if ($loanApplication->approvals->isEmpty())
                        <p class="text-gray-600">Tiada sejarah kelulusan untuk permohonan ini.</p>
                    @else
                        <div class="overflow-x-auto shadow-sm rounded-md border border-gray-200">
                            <table class="min-w-full divide-y divide-gray-200 table">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">
                                            Pegawai
                                        </th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">
                                            Status Kelulusan
                                        </th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">
                                            Peringkat
                                        </th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">
                                            Catatan
                                        </th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">
                                            Tarikh & Masa
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    {{-- Loop through the approvals relationship --}}
                                    @foreach ($loanApplication->approvals as $approval)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 border-b">
                                                {{ $approval->officer->name ?? 'N/A' }} {{-- Assuming officer relationship with 'name' --}}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 border-b">
                                                <span
                                                    class="badge {{ match ($approval->status) {
                                                        'pending' => 'badge-warning',
                                                        'approved' => 'badge-success',
                                                        'rejected' => 'badge-danger',
                                                        default => 'badge-secondary',
                                                    } }}">
                                                    {{ ucfirst(str_replace('_', ' ', $approval->status)) }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 border-b">
                                                {{ $approval->stage ?? 'N/A' }} {{-- Display the approval stage --}}
                                            </td>
                                            <td class="px-6 py-4 text-sm text-gray-900 border-b"> {{-- Removed whitespace-nowrap for comments --}}
                                                {{ $approval->comments ?? '-' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 border-b">
                                                {{ $approval->approval_timestamp?->format('Y-m-d H:i') ?? 'N/A' }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div> {{-- End overflow-x-auto --}}
                    @endif
                </div>


                {{-- Back Button --}}
                <div class="mt-6">
                    <a href="{{ route('loan-applications.index') }}" class="btn btn-secondary">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Kembali ke Senarai
                    </a>
                </div>

            </div> {{-- End bg-white card --}}
        </div> {{-- End container --}}
    @endsection

</body>

</html>
