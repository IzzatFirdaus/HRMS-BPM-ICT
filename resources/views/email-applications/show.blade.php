@extends('layouts.app')

@section('content')
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 py-8"> {{-- Container for the content --}}
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6"> {{-- Card-like container --}}

            <h2 class="text-2xl font-bold mb-6 text-gray-800">Butiran Permohonan E-mel ICT #{{ $emailApplication->id }}</h2>
            {{-- Title with application ID --}}

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
                    class="badge {{ match ($emailApplication->status) {
                        'draft' => 'badge-secondary',
                        'pending_support', 'pending_admin', 'processing' => 'badge-warning',
                        'approved' => 'badge-info', // Approved but not yet provisioned
                        'completed' => 'badge-success', // Provisioned successfully
                        'rejected', 'provision_failed' => 'badge-danger',
                        default => 'badge-secondary',
                    } }} text-lg">
                    {{-- Increased badge size --}}
                    {{ ucfirst(str_replace('_', ' ', $emailApplication->status)) }}
                </span>

                {{-- Display rejection reason if rejected --}}
                @if ($emailApplication->status === 'rejected' && $emailApplication->rejection_reason)
                    <div class="alert alert-danger mt-3">
                        <p class="font-semibold">Sebab Penolakan:</p>
                        <p>{{ $emailApplication->rejection_reason }}</p>
                    </div>
                @endif

                {{-- Display provisioning details if completed or failed --}}
                @if ($emailApplication->status === 'completed' || $emailApplication->status === 'provision_failed')
                    <div class="mt-3">
                        <p class="text-lg font-semibold text-gray-700">Maklumat Akaun E-mel:</p>
                        <p>E-mel Rasmi MOTAC: <span
                                class="font-mono">{{ $emailApplication->final_assigned_email ?? 'N/A' }}</span></p>
                        <p>ID Pengguna: <span
                                class="font-mono">{{ $emailApplication->final_assigned_user_id ?? 'N/A' }}</span></p>
                        <p>Tarikh Disediakan: {{ $emailApplication->provisioned_at?->format('Y-m-d H:i') ?? 'N/A' }}</p>
                        @if ($emailApplication->status === 'provision_failed')
                            <div class="alert alert-danger mt-2">
                                <p class="font-semibold">Status Penyediaan:</p>
                                <p>Gagal. Sila hubungi BPM untuk maklumat lanjut.</p>
                            </div>
                        @endif
                    </div>
                @endif

            </div>

            {{-- Applicant Information --}}
            <div class="mb-6">
                <h3 class="text-xl font-semibold mb-4 text-gray-700">MAKLUMAT PEMOHON</h3>
                {{-- Assuming relationships and attributes exist on the User model --}}
                <p class="mb-2"><span class="font-semibold">Nama Penuh:</span>
                    {{ $emailApplication->user->name ?? 'N/A' }}</p>
                <p class="mb-2"><span class="font-semibold">No. Pengenalan (NRIC):</span>
                    {{ $emailApplication->user->nric ?? 'N/A' }}</p>
                <p class="mb-2"><span class="font-semibold">Jawatan & Gred:</span>
                    {{ $emailApplication->user->position->name ?? 'N/A' }} &
                    {{ $emailApplication->user->grade->name ?? 'N/A' }}</p>
                <p class="mb-2"><span class="font-semibold">Bahagian/Unit:</span>
                    {{ $emailApplication->user->department->name ?? 'N/A' }}</p>
                <p class="mb-2"><span class="font-semibold">No. Telefon Bimbit:</span>
                    {{ $emailApplication->user->phone_number ?? 'N/A' }}</p>
                <p class="mb-2"><span class="font-semibold">E-mel Peribadi:</span>
                    {{ $emailApplication->user->personal_email ?? 'N/A' }}</p>
                <p class="mb-2"><span class="font-semibold">Taraf Perkhidmatan:</span>
                    {{ $emailApplication->service_status ?? 'N/A' }}</p> {{-- Added service status --}}
            </div>

            {{-- Application Details --}}
            <div class="mb-6">
                <h3 class="text-xl font-semibold mb-4 text-gray-700">BUTIRAN PERMOHONAN</h3>
                <p class="mb-2"><span class="font-semibold">Tujuan Permohonan / Catatan:</span>
                    {{ $emailApplication->purpose ?? 'N/A' }}</p>
                <p class="mb-2"><span class="font-semibold">Cadangan E-mel:</span>
                    {{ $emailApplication->proposed_email ?? 'N/A' }}</p>

                {{-- Display Group Email details if available --}}
                @if ($emailApplication->group_email || $emailApplication->group_admin_name || $emailApplication->group_admin_email)
                    <div class="mt-4 p-4 bg-gray-50 rounded-md border border-gray-200">
                        <h5 class="text-lg font-semibold mb-3 text-gray-700">Butiran Group E-mel (Jika Berkenaan)</h5>
                        <p class="mb-2"><span class="font-semibold">Nama Group Email:</span>
                            {{ $emailApplication->group_email ?? 'N/A' }}</p>
                        <p class="mb-2"><span class="font-semibold">Nama Admin/EO/CC:</span>
                            {{ $emailApplication->group_admin_name ?? 'N/A' }}</p>
                        <p class="mb-2"><span class="font-semibold">E-mel Admin/EO/CC:</span>
                            {{ $emailApplication->group_admin_email ?? 'N/A' }}</p>
                    </div>
                @endif
            </div>

            {{-- Applicant Certification --}}
            <div class="mb-6">
                <h3 class="text-xl font-semibold mb-4 text-gray-700">PERAKUAN PEMOHON</h3>
                <p class="mb-2">
                    <span class="font-semibold">Status Pengesahan:</span>
                    @if ($emailApplication->certification_accepted)
                        <span class="text-green-600 font-semibold">Diterima</span>
                    @else
                        <span class="text-red-600 font-semibold">Belum Diterima</span>
                    @endif
                </p>
                <p class="mb-2"><span class="font-semibold">Tarikh Pengesahan:</span>
                    {{ $emailApplication->certification_timestamp?->format('Y-m-d H:i') ?? 'N/A' }}</p>
                {{-- You might display the full certification text here if needed --}}
            </div>


            {{-- Approval History --}}
            <div class="mb-6">
                <h3 class="text-xl font-semibold mb-4 text-gray-700">Sejarah Kelulusan</h3>
                @if ($emailApplication->approvals->isEmpty())
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
                                @foreach ($emailApplication->approvals as $approval)
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
                <a href="{{ route('email-applications.index') }}" class="btn btn-secondary">
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
