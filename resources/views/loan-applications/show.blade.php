{{-- resources/views/loan-applications/show.blade.php --}}

{{-- Extend your main layout file. Remove standalone HTML structure if extending a layout. --}}
@extends('layouts.app')

@section('title', 'Butiran Permohonan Pinjaman Peralatan ICT') {{-- Set a specific title --}}

@section('content') {{-- Start the content section --}}

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
                        'partially_issued', 'issued' => 'badge-teal',
                        'returned' => 'badge-purple',
                        'overdue' => 'badge-red',
                        'rejected', 'cancelled' => 'badge-danger',
                        default => 'badge-secondary',
                    } }} text-lg">
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
                                        <td class="px-6 py-4 text-sm text-gray-900 border-b"> {{-- Removed whitespace-nowrap --}}
                                            {{ $item->notes ?? '-' }}
                                        </td>
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
                {{-- Corrected route name from 'loan-applications.index' to 'my-applications.loan.index' --}}
                <a href="{{ route('my-applications.loan.index') }}" class="btn btn-secondary">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Kembali ke Senarai
                </a>
            </div>

        </div> {{-- End bg-white card --}}
    </div> {{-- End container --}}

@endsection {{-- End of content section --}}

{{-- Remove standalone HTML tags if using layouts.app --}}
{{-- </body> --}}
{{-- </html> --}}
