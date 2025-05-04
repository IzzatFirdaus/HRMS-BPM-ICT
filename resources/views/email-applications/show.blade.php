{{--
    resources/views/email-applications/show.blade.php

    This Blade view displays the details of a specific email application.
    It assumes the $emailApplication model instance is passed to the view.
    Includes basic Tailwind CSS structure (assuming extended layout provides it)
    and uses correct route names based on web.php.
--}}
@extends('layouts.app') {{-- Assumes a layout file named 'app.blade.php' exists --}}

@section('content')
    {{-- Using Tailwind CSS classes for layout and styling --}}
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 py-8"> {{-- Container for the content --}}
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6"> {{-- Card-like container --}}

            {{-- Application Title --}}
            <h2 class="text-2xl font-bold mb-6 text-gray-800">Butiran Permohonan E-mel ICT
                #{{ $emailApplication->id ?? 'N/A' }}</h2>
            {{-- Added null coalescing for ID --}}

            {{-- Display success or error messages --}}
            @if (session()->has('success'))
                <div class="alert alert-success mb-4"> {{-- Assuming .alert-success and .alert CSS/Tailwind classes exist --}}
                    {{ session('success') }}
                </div>
            @endif

            @if (session()->has('error'))
                <div class="alert alert-danger mb-4"> {{-- Assuming .alert-danger and .alert CSS/Tailwind classes exist --}}
                    {{ session('error') }}
                </div>
            @endif

            {{-- Application Status --}}
            <div class="mb-6">
                <p class="text-lg font-semibold text-gray-700">Status Semasa:</p>
                {{-- Display status with a colored badge. Assumes badge classes are defined (e.g., in custom CSS or via Tailwind config). --}}
                {{-- FIX: Removed incorrect parenthesis around the match expression --}}
                <span
                    class="badge {{ match ($emailApplication->status ?? '') {
                        'draft' => 'badge-secondary',
                        'pending_support', 'pending_admin', 'processing' => 'badge-warning',
                        'approved' => 'badge-info', // Approved but not yet provisioned
                        'completed' => 'badge-success', // Provisioned successfully
                        'rejected', 'provision_failed' => 'badge-danger',
                        default => 'badge-secondary',
                    } }} text-lg">
                    {{-- Increased badge size --}}
                    {{ ucfirst(str_replace('_', ' ', $emailApplication->status ?? 'N/A')) }} {{-- Added null coalescing for status --}}
                </span>

                {{-- Display rejection reason if rejected --}}
                @if (($emailApplication->status ?? '') === 'rejected' && ($emailApplication->rejection_reason ?? null))
                    {{-- Added null checks --}}
                    <div class="alert alert-danger mt-3">
                        <p class="font-semibold">Sebab Penolakan:</p>
                        <p>{{ $emailApplication->rejection_reason }}</p>
                    </div>
                @endif

                {{-- Display provisioning details if completed or failed --}}
                @if (($emailApplication->status ?? '') === 'completed' || ($emailApplication->status ?? '') === 'provision_failed')
                    {{-- Added null checks --}}
                    <div class="mt-3">
                        <p class="text-lg font-semibold text-gray-700">Maklumat Akaun E-mel:</p>
                        <p>E-mel Rasmi MOTAC: <span
                                class="font-mono">{{ $emailApplication->final_assigned_email ?? 'N/A' }}</span></p>
                        {{-- Added null coalescing --}}
                        <p>ID Pengguna: <span
                                class="font-mono">{{ $emailApplication->final_assigned_user_id ?? 'N/A' }}</span></p>
                        {{-- Added null coalescing --}}
                        <p>Tarikh Disediakan:
                            {{ optional($emailApplication->provisioned_at)->format('Y-m-d H:i') ?? 'N/A' }}</p>
                        {{-- Use optional and null coalescing --}}
                        @if (($emailApplication->status ?? '') === 'provision_failed')
                            {{-- Added null check --}}
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
                {{-- Assuming relationships (user, position, grade, department) exist on EmailApplication and attributes exist on related models --}}
                {{-- Using optional() and null coalescing for safe access --}}
                <p class="mb-2"><span class="font-semibold">Nama Penuh:</span>
                    {{ optional($emailApplication->user)->name ?? 'N/A' }}</p>
                <p class="mb-2"><span class="font-semibold">No. Pengenalan (NRIC):</span>
                    {{ optional($emailApplication->user)->nric ?? 'N/A' }}</p>
                <p class="mb-2"><span class="font-semibold">Jawatan & Gred:</span>
                    {{ optional(optional($emailApplication->user)->position)->name ?? 'N/A' }} &
                    {{ optional(optional($emailApplication->user)->grade)->name ?? 'N/A' }}</p> {{-- Accessing nested relationships safely --}}
                <p class="mb-2"><span class="font-semibold">Bahagian/Unit:</span>
                    {{ optional(optional($emailApplication->user)->department)->name ?? 'N/A' }}</p> {{-- Accessing nested relationships safely --}}
                <p class="mb-2"><span class="font-semibold">No. Telefon Bimbit:</span>
                    {{ optional($emailApplication->user)->phone_number ?? 'N/A' }}</p>
                <p class="mb-2"><span class="font-semibold">E-mel Peribadi:</span>
                    {{ optional($emailApplication->user)->personal_email ?? 'N/A' }}</p>
                <p class="mb-2"><span class="font-semibold">Taraf Perkhidmatan:</span>
                    {{ $emailApplication->service_status ?? 'N/A' }}</p> {{-- Added null coalescing --}}
            </div>

            {{-- Application Details --}}
            <div class="mb-6">
                <h3 class="text-xl font-semibold mb-4 text-gray-700">BUTIRAN PERMOHONAN</h3>
                <p class="mb-2"><span class="font-semibold">Tujuan Permohonan / Catatan:</span>
                    {{ $emailApplication->purpose ?? 'N/A' }}</p> {{-- Added null coalescing --}}
                <p class="mb-2"><span class="font-semibold">Cadangan E-mel:</span>
                    {{ $emailApplication->proposed_email ?? 'N/A' }}</p> {{-- Added null coalescing --}}

                {{-- Display Group Email details if available --}}
                @if (
                    ($emailApplication->group_email ?? null) ||
                        ($emailApplication->group_admin_name ?? null) ||
                        ($emailApplication->group_admin_email ?? null))
                    {{-- Added null checks for conditional display --}}
                    <div class="mt-4 p-4 bg-gray-50 rounded-md border border-gray-200">
                        <h5 class="text-lg font-semibold mb-3 text-gray-700">Butiran Group E-mel (Jika Berkenaan)</h5>
                        <p class="mb-2"><span class="font-semibold">Nama Group Email:</span>
                            {{ $emailApplication->group_email ?? 'N/A' }}</p> {{-- Added null coalescing --}}
                        <p class="mb-2"><span class="font-semibold">Nama Admin/EO/CC:</span>
                            {{ $emailApplication->group_admin_name ?? 'N/A' }}</p> {{-- Added null coalescing --}}
                        <p class="mb-2"><span class="font-semibold">E-mel Admin/EO/CC:</span>
                            {{ $emailApplication->group_admin_email ?? 'N/A' }}</p> {{-- Added null coalescing --}}
                    </div>
                @endif
            </div>

            {{-- Applicant Certification --}}
            <div class="mb-6">
                <h3 class="text-xl font-semibold mb-4 text-gray-700">PERAKUAN PEMOHON</h3>
                <p class="mb-2">
                    <span class="font-semibold">Status Pengesahan:</span>
                    @if ($emailApplication->certification_accepted ?? false)
                        {{-- Added null coalescing --}}
                        <span class="text-green-600 font-semibold">Diterima</span>
                    @else
                        <span class="text-red-600 font-semibold">Belum Diterima</span>
                    @endif
                </p>
                <p class="mb-2"><span class="font-semibold">Tarikh Pengesahan:</span>
                    {{ optional($emailApplication->certification_timestamp)->format('Y-m-d H:i') ?? 'N/A' }}</p>
                {{-- Use optional and null coalescing --}}
                {{-- You might display the full certification text here if needed --}}
            </div>

            {{-- Approval History --}}
            <div class="mb-6">
                <h3 class="text-xl font-semibold mb-4 text-gray-700">Sejarah Kelulusan</h3>
                {{-- Using ?? [] for safe iteration in case approvals relationship is null or empty --}}
                @if (($emailApplication->approvals ?? [])->isEmpty())
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
                                    {{-- Assuming $emailApplication->approvals is a collection --}}
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 border-b">
                                            {{ optional($approval->officer)->name ?? 'N/A' }} {{-- Use optional() for officer relationship --}}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 border-b">
                                            <span
                                                class="badge {{ match ($approval->status ?? '') {
                                                    'pending' => 'badge-warning',
                                                    'approved' => 'badge-success',
                                                    'rejected' => 'badge-danger',
                                                    default => 'badge-secondary',
                                                } }}">
                                                {{ ucfirst(str_replace('_', ' ', $approval->status ?? 'N/A')) }}
                                                {{-- Added null coalescing --}}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 border-b">
                                            {{ $approval->stage ?? 'N/A' }} {{-- Display the approval stage, added null coalescing --}}
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-900 border-b">
                                            {{ $approval->comments ?? '-' }} {{-- Added null coalescing --}}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 border-b">
                                            {{ optional($approval->approval_timestamp)->format('Y-m-d H:i') ?? 'N/A' }}
                                            {{-- Use optional() and null coalescing --}}
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
                {{-- CORRECTED ROUTE NAME: Use the full registered name 'my-applications.email.index' --}}
                <a href="{{ route('my-applications.email.index') }}" class="btn btn-secondary"> {{-- Assuming .btn and .btn-secondary CSS/Tailwind classes exist --}}
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Kembali ke Senarai
                </a>
            </div>

            {{-- Optional: Add buttons for actions if applicable and authorized (e.g., Edit Draft, Cancel, Approve/Reject for approvers) --}}
            {{-- Example for Edit Draft (assuming policy checks): --}}
            @can('update', $emailApplication)
                {{-- Check if the user is authorized to update this application (e.g., it's their draft) --}}
                @if (($emailApplication->status ?? '') === 'draft')
                    {{-- Only show edit for draft applications --}}
                    <div class="mt-4">
                        {{-- Link to the create/edit form route, passing the application ID --}}
                        <a href="{{ route('resource-management.email-applications.create', $emailApplication->id ?? null) }}"
                            class="btn btn-primary">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M15.232 5.232l3.536 3.536m-2.036-5.036a12.006 12.006 0 011.617 1.618l-.383.383L14.768 7.82l-.383-.383a12.006 12.006 0 011.617 1.618zM16 10l-4 4-4 0 0 4 4 0 4-4 0-4z" />
                            </svg>
                            Edit Draf Permohonan
                        </a>
                    </div>
                @endif
            @endcan

            {{-- Example for Approve/Reject buttons (assuming policy checks and controller methods): --}}
            @can('approve', $emailApplication)
                {{-- Check if user is authorized to approve this application (e.g., it's pending their approval) --}}
                {{-- You would typically have a form here for comments and submit buttons --}}
                <div class="mt-4">
                    {{-- Note: wire:click actions assume you are using a Livewire component to handle show page logic --}}
                    <button wire:click="approveApplication({{ $emailApplication->id ?? null }})"
                        class="btn btn-success">Lulus</button>
                    <button wire:click="rejectApplication({{ $emailApplication->id ?? null }})"
                        class="btn btn-danger ml-4">Tolak</button>
                </div>
            @endcan


        </div> {{-- End bg-white card --}}
    </div> {{-- End container --}}
@endsection

{{-- Optional: Add custom styles if not provided by layout or Tailwind config --}}
{{-- Example alert-danger style --}}
@push('styles')
    <style>
        .alert-danger {
            background-color: #fee2e2;
            /* red-100 */
            border-color: #fca5a5;
            /* red-300 */
            color: #991b1b;
            /* red-900 */
        }

        /* Add styles for .btn, .btn-secondary, .badge, etc. if needed */
    </style>
@endpush
