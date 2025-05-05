@extends('layouts.app') {{-- Assuming you have a base layout file --}}

@section('content')
    <div class="container mx-auto px-4 py-6">
        <div class="bg-white shadow-md rounded-lg p-6">
            {{-- Title with Application ID --}}
            <h1 class="text-2xl font-bold mb-4">{{ __('Butiran Permohonan Akaun E-mel / ID Pengguna') }}
                #{{ $emailApplication->id }}</h1>

            {{-- Application Status (Using Accessor) --}}
            <div class="mb-6">
                <span class="text-lg font-semibold">{{ __('Status Permohonan:') }}</span>
                {{-- Use the accessor for translated status (from EmailApplication model) --}}
                {{-- Dynamic classes moved inside the Blade directives to avoid linter conflict --}}
                <span
                    class="ml-2 inline-flex items-center px-3 py-0.5 rounded-full text-sm font-medium
                @if ($emailApplication->isApproved() || $emailApplication->isCompleted()) bg-green-100 text-green-800
                @elseif($emailApplication->isRejected())
                    bg-red-100 text-red-800
                @elseif($emailApplication->isPendingApproval() || $emailApplication->isProcessing()) {{-- Include processing in pending/yellow status or create a new one --}}
                    bg-yellow-100 text-yellow-800
                @else
                    bg-gray-100 text-gray-800 @endif">
                    {{ $emailApplication->status_translated }}
                </span>
            </div>

            {{-- SECTION: Applicant Information --}}
            {{-- Based on Section 5.1, Part 1. Accessing the related user model --}}
            <h2 class="text-xl font-semibold mb-4">{{ __('Maklumat Pemohon') }}</h2>
            @if ($emailApplication->user)
                {{-- Ensure the user relationship is loaded --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                    <div>
                        <p class="text-gray-700 text-sm font-bold">{{ __('Nama Penuh') }}:</p>
                        <p class="text-gray-800">{{ $emailApplication->user->full_name ?? __('Tidak Ditetapkan') }}</p>
                    </div>
                    <div>
                        <p class="text-gray-700 text-sm font-bold">{{ __('No. Kad Pengenalan') }}:</p>
                        <p class="text-gray-800">{{ $emailApplication->user->nric ?? __('Tidak Ditetapkan') }}</p>
                    </div>
                    <div>
                        <p class="text-gray-700 text-sm font-bold">{{ __('Jawatan & Gred') }}:</p>
                        {{-- Assuming relationships position and grade on User model --}}
                        <p class="text-gray-800">{{ $emailApplication->user->position->name ?? __('Tidak Ditetapkan') }}
                            ({{ $emailApplication->user->grade->name ?? __('N/A') }})</p>
                    </div>
                    <div>
                        <p class="text-gray-700 text-sm font-bold">{{ __('Bahagian/Unit') }}:</p>
                        {{-- Assuming relationship department on User model --}}
                        <p class="text-gray-800">{{ $emailApplication->user->department->name ?? __('Tidak Ditetapkan') }}
                        </p>
                    </div>
                    <div>
                        <p class="text-gray-700 text-sm font-bold">{{ __('No. Telefon Bimbit') }}:</p>
                        <p class="text-gray-800">{{ $emailApplication->user->mobile_number ?? __('Tidak Ditetapkan') }}</p>
                    </div>
                    <div>
                        <p class="text-gray-700 text-sm font-bold">{{ __('E-mel Peribadi') }}:</p>
                        <p class="text-gray-800">{{ $emailApplication->user->personal_email ?? __('Tidak Ditetapkan') }}
                        </p>
                    </div>
                    <div>
                        <p class="text-gray-700 text-sm font-bold">{{ __('Taraf Perkhidmatan') }}:</p>
                        {{-- Use the accessor for translated service status (from EmailApplication model) --}}
                        <p class="text-gray-800">
                            {{ $emailApplication->service_status_translated ?? __('Tidak Ditetapkan') }}</p>
                    </div>
                </div>
            @else
                <p class="text-red-600">{{ __('Maklumat pemohon tidak dapat dimuatkan.') }}</p>
            @endif

            {{-- SECTION: Application Details --}}
            <h2 class="text-xl font-semibold mb-4">{{ __('Butiran Permohonan') }}</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <div>
                    <p class="text-gray-700 text-sm font-bold">{{ __('Tujuan / Catatan') }}:</p>
                    <p class="text-gray-800">{{ $emailApplication->purpose ?? __('Tiada') }}</p>
                </div>
                <div>
                    <p class="text-gray-700 text-sm font-bold">{{ __('Cadangan E-mel / ID Pengguna') }}:</p>
                    <p class="text-gray-800">{{ $emailApplication->proposed_email ?? __('Tiada Cadangan') }}</p>
                </div>
                <div>
                    <p class="text-gray-700 text-sm font-bold">{{ __('Nama Group Email') }}:</p>
                    <p class="text-gray-800">{{ $emailApplication->group_email ?? __('Tiada') }}</p>
                </div>
                <div>
                    <p class="text-gray-700 text-sm font-bold">{{ __('Nama Admin/EO/CC Group') }}:</p>
                    <p class="text-gray-800">{{ $emailApplication->group_admin_name ?? __('Tiada') }}</p>
                </div>
                <div>
                    <p class="text-gray-700 text-sm font-bold">{{ __('E-mel Admin/EO/CC Group') }}:</p>
                    <p class="text-gray-800">{{ $emailApplication->group_admin_email ?? __('Tiada') }}</p>
                </div>
            </div>

            {{-- SECTION: Certification --}}
            <h2 class="text-xl font-semibold mb-4">{{ __('Perakuan Pemohon') }}</h2>
            <div class="mb-6">
                @if ($emailApplication->certification_accepted)
                    <p class="text-green-600">{{ __('Pemohon telah memperakui semua syarat.') }}</p>
                    <p class="text-gray-700 text-sm">{{ __('Masa Perakuan:') }}
                        {{ $emailApplication->certification_timestamp?->format('d/m/Y H:i') ?? __('Tidak Ditetapkan') }}
                    </p>
                @else
                    <p class="text-red-600">{{ __('Pemohon belum membuat perakuan.') }}</p>
                @endif
            </div>

            {{-- SECTION: Approval History (Section 9.4) --}}
            <h2 class="text-xl font-semibold mb-4">{{ __('Sejarah Kelulusan') }}</h2>
            @if ($emailApplication->approvals->isNotEmpty())
                {{-- Ensure the approvals relationship is loaded --}}
                <div class="mb-6">
                    @foreach ($emailApplication->approvals as $approval)
                        <div class="border-l-4 border-blue-500 pl-4 mb-4">
                            <p class="text-gray-700 font-semibold">{{ __('Tahap Kelulusan:') }}
                                {{ $approval->stage ?? __('Tidak Dinyatakan') }}</p>
                            <p class="text-gray-700">{{ __('Pegawai:') }}
                                {{ $approval->officer->full_name ?? __('Tidak Dikenali') }}</p>
                            <p class="text-gray-700">{{ __('Status Keputusan:') }}
                                {{-- Dynamic classes moved inside the Blade directives --}}
                                <span
                                    class="ml-1 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                @if ($approval->status === 'approved') bg-green-100 text-green-800
                                @elseif($approval->status === 'rejected')
                                    bg-red-100 text-red-800
                                @else {{-- Using yellow for pending/other approval statuses --}}
                                    bg-yellow-100 text-yellow-800 @endif">
                                    {{ ucfirst($approval->status) }} {{-- Or use translation if Approval model has status accessor --}}
                                </span>
                            </p>
                            @if ($approval->comments)
                                <p class="text-gray-700">{{ __('Komen:') }} {{ $approval->comments }}</p>
                            @endif
                            <p class="text-gray-700 text-sm">{{ __('Pada:') }}
                                {{ $approval->approval_timestamp?->format('d/m/Y H:i') ?? __('Tidak Ditetapkan') }}</p>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-gray-600 mb-6">{{ __('Tiada sejarah kelulusan direkodkan lagi.') }}</p>
            @endif

            {{-- SECTION: Rejection Reason (Conditional) --}}
            @if ($emailApplication->isRejected())
                {{-- Using model helper method --}}
                <h2 class="text-xl font-semibold text-red-600 mb-4">{{ __('Sebab Penolakan') }}</h2>
                <p class="text-red-800 mb-6">{{ $emailApplication->rejection_reason ?? __('Tiada sebab dinyatakan.') }}</p>
            @endif

            {{-- SECTION: Final Assigned Details (Conditional) --}}
            {{-- Displayed once provisioning is complete or details are assigned by IT Admin --}}
            {{-- Check if final_assigned_email OR provisioned_at exists, as final_assigned_user_id might not apply to email-only accounts --}}
            @if ($emailApplication->final_assigned_email || $emailApplication->provisioned_at)
                <h2 class="text-xl font-semibold mb-4">{{ __('Butiran Akaun yang Ditetapkan') }}</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                    @if ($emailApplication->final_assigned_email)
                        <div>
                            <p class="text-gray-700 text-sm font-bold">{{ __('Akaun E-mel MOTAC') }}:</p>
                            <p class="text-gray-800">{{ $emailApplication->final_assigned_email }}</p>
                        </div>
                    @endif
                    {{-- Display final_assigned_user_id only if it exists and is relevant (e.g., for ID Pengguna) --}}
                    @if ($emailApplication->final_assigned_user_id)
                        <div>
                            <p class="text-gray-700 text-sm font-bold">{{ __('ID Pengguna Ditetapkan (Sistem Luar)') }}:
                            </p>
                            <p class="text-gray-800">{{ $emailApplication->final_assigned_user_id }}</p>
                            {{-- Note: final_assigned_user_id in model/migration is integer FK, but users.user_id_assigned is string username. Clarify purpose/usage! --}}
                        </div>
                    @endif
                    {{-- Display provisioned_at if it exists in the DB and model --}}
                    @if ($emailApplication->provisioned_at)
                        <div>
                            <p class="text-gray-700 text-sm font-bold">{{ __('Diproses Pada') }}:</p>
                            <p class="text-gray-800">{{ $emailApplication->provisioned_at->format('d/m/Y H:i') }}</p>
                        </div>
                    @endif
                    {{-- Add link to the provisioned user's profile if needed --}}
                    {{-- @if ($emailApplication->finalAssignedUser) --}}
                    {{-- <div><a href="{{ route('users.show', $emailApplication->finalAssignedUser) }}">Lihat Profil Pengguna</a></div> --}}
                    {{-- @endif --}}
                </div>
            @endif


            {{-- SECTION: Action Buttons (Conditional based on status and user role/permissions) --}}
            {{-- Implement authorization checks using @can or Policy methods --}}
            <div class="flex justify-end space-x-2 mt-6">
                {{-- Example: Edit button for applicant if status is draft --}}
                {{-- Assumes a 'update' policy method check includes status and user ownership --}}
                @can('update', $emailApplication)
                    @if ($emailApplication->isDraft())
                        {{-- Use model helper method --}}
                        <a href="{{ route('email-applications.edit', $emailApplication) }}"
                            class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded">
                            {{ __('Edit Permohonan') }}
                        </a>
                    @endif
                @endcan

                {{-- Example: Delete/Withdraw button for applicant if status is draft --}}
                {{-- Assumes a 'delete' policy method check includes status and user ownership --}}
                @can('delete', $emailApplication)
                    @if ($emailApplication->isDraft())
                        {{-- Use model helper method --}}
                        <form action="{{ route('email-applications.destroy', $emailApplication) }}" method="POST"
                            onsubmit="return confirm('{{ __('Adakah anda pasti ingin membuang permohonan ini? Tindakan ini tidak boleh asal.') }}');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
                                {{ __('Buang Permohonan') }}
                            </button>
                        </form>
                    @endif
                @endcan

                {{-- Example: Approval/Rejection actions for Supporting Officer if status is pending_support --}}
                {{-- This would likely be a form submitting to the ApprovalController or a specific action method --}}
                {{-- Assumes a 'approve' or 'reject' policy method check for the user and the application status/assignment --}}
                @can('approve', $emailApplication)
                    {{-- Assuming a policy method 'approve' exists --}}
                    @if ($emailApplication->isPendingSupport())
                        {{-- Use model helper method --}}
                        {{-- Placeholder for actual approval/rejection UI --}}
                        {{-- This UI would typically be a form allowing the officer to select Approve/Reject and add comments --}}
                        <a href="#"
                            class="bg-green-500 text-white font-bold py-2 px-4 rounded opacity-50 cursor-not-allowed">{{ __('Lulus (UI belum siap)') }}</a>
                        <a href="#"
                            class="bg-red-500 text-white font-bold py-2 px-4 rounded opacity-50 cursor-not-allowed">{{ __('Tolak (UI belum siap)') }}</a>
                    @endif
                @endcan

                {{-- Example: Process action for IT Admin if status is pending_admin --}}
                {{-- This would likely be a form submitting to EmailAccountController@process --}}
                {{-- Assumes a 'process' policy method check for IT Admin role and application status --}}
                @can('process', $emailApplication)
                    {{-- Assuming a policy method 'process' exists --}}
                    @if ($emailApplication->isPendingAdmin())
                        {{-- Use model helper method --}}
                        {{-- Placeholder for IT Admin processing UI --}}
                        {{-- This UI would likely be a form allowing IT Admin to enter final email/ID, set status (processing/completed), add notes, and submit to the EmailAccountController@process method --}}
                        <a href="#"
                            class="bg-blue-500 text-white font-bold py-2 px-4 rounded opacity-50 cursor-not-allowed">{{ __('Proses Akaun (UI belum siap)') }}</a>
                    @endif
                @endcan

                {{-- Example: View link to provisioned account details (if a separate view exists for this) --}}
                {{-- Only show if provisioning is completed or details are assigned --}}
                {{-- @if ($emailApplication->isCompleted() || $emailApplication->status === 'provisioned') --}}
                {{-- @can('viewProvisionedAccountDetails', $emailApplication) --}}
                {{-- <a href="{{ route('email-accounts.provisioned.show', $emailApplication->user) }}">Lihat Butiran Akaun</a> --}}
                {{-- @endcan --}}
                {{-- @endif --}}

            </div>

        </div>
    </div>
@endsection
