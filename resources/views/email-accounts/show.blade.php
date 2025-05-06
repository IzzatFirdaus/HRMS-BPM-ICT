@extends('layouts.app') {{-- Assuming you have a base layout file --}}

@section('content')
    <div class="container mx-auto px-4 py-6">
<<<<<<< HEAD
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
=======
        {{-- Use a max-width container for better readability on large screens --}}
        <div class="max-w-3xl mx-auto bg-white shadow-md rounded-lg p-6">
            {{-- Title with Application ID --}}
            <h1 class="text-2xl font-bold mb-4 text-center">{{ __('Butiran Permohonan Akaun E-mel / ID Pengguna') }}
                #{{ $emailApplication->id }}</h1>

            {{-- Display success and error messages --}}
            @if (session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4" role="alert">
                    {{ session('success') }}
                </div>
            @endif
            @if (session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4" role="alert">
                    {{ session('error') }}
                </div>
            @endif


            {{-- Application Status (Using Accessor) --}}
            {{-- Ensure $emailApplication->status_translated accessor exists on the model --}}
            {{-- Ensure $emailApplication helper methods (isApproved, isRejected, etc.) exist on the model --}}
            <div class="mb-6 text-center"> {{-- Center the status badge --}}
                <span class="text-lg font-semibold text-gray-800">{{ __('Status Permohonan:') }}</span>
                {{-- Use the accessor for translated status (from EmailApplication model) --}}
                {{-- Dynamic classes applied based on status helper methods --}}
                {{-- The linter might warn about cssConflict here, but it's expected as only one set of classes is applied at runtime. --}}
                <span
                    class="ml-2 inline-flex items-center px-3 py-0.5 rounded-full text-sm font-medium
                    @if ($emailApplication->isCompleted() || $emailApplication->isApproved()) bg-green-100 text-green-800
                    @elseif($emailApplication->isRejected()) bg-red-100 text-red-800
                    @elseif($emailApplication->isPendingApproval() || $emailApplication->isProcessing()) bg-yellow-100 text-yellow-800
                    @else {{-- draft or any other status --}} bg-gray-100 text-gray-800 @endif">
>>>>>>> 1cf33d772cd3fdd0b638a8a52f99ce5c006c2227
                    {{ $emailApplication->status_translated }}
                </span>
            </div>

            {{-- SECTION: Applicant Information --}}
            {{-- Based on Section 5.1, Part 1. Accessing the related user model --}}
<<<<<<< HEAD
            <h2 class="text-xl font-semibold mb-4">{{ __('Maklumat Pemohon') }}</h2>
            @if ($emailApplication->user)
                {{-- Ensure the user relationship is loaded --}}
=======
            {{-- Ensure the 'user', 'user.position', 'user.grade', 'user.department' relationships are eager loaded in the controller's show method --}}
            <h2 class="text-xl font-semibold mb-4 text-gray-800">{{ __('Maklumat Pemohon') }}</h2>
            @if ($emailApplication->user)
                {{-- Check if the user relationship is loaded --}}
>>>>>>> 1cf33d772cd3fdd0b638a8a52f99ce5c006c2227
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                    <div>
                        <p class="text-gray-700 text-sm font-bold">{{ __('Nama Penuh') }}:</p>
                        <p class="text-gray-800">{{ $emailApplication->user->full_name ?? __('Tidak Ditetapkan') }}</p>
                    </div>
                    <div>
                        <p class="text-gray-700 text-sm font-bold">{{ __('No. Kad Pengenalan') }}:</p>
<<<<<<< HEAD
=======
                        {{-- Assuming 'nric' attribute on User model as per migration --}}
>>>>>>> 1cf33d772cd3fdd0b638a8a52f99ce5c006c2227
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
<<<<<<< HEAD
=======
                        {{-- Assuming personal_email attribute on User model as per migration --}}
>>>>>>> 1cf33d772cd3fdd0b638a8a52f99ce5c006c2227
                        <p class="text-gray-800">{{ $emailApplication->user->personal_email ?? __('Tidak Ditetapkan') }}
                        </p>
                    </div>
                    <div>
                        <p class="text-gray-700 text-sm font-bold">{{ __('Taraf Perkhidmatan') }}:</p>
                        {{-- Use the accessor for translated service status (from EmailApplication model) --}}
                        <p class="text-gray-800">
                            {{ $emailApplication->service_status_translated ?? __('Tidak Ditetapkan') }}</p>
                    </div>
<<<<<<< HEAD
=======
                    {{-- Display Supporting Officer details if assigned --}}
                    {{-- Ensure the 'supportingOfficer' relationship is eager loaded --}}
                    @if ($emailApplication->supportingOfficer)
                        <div>
                            <p class="text-gray-700 text-sm font-bold">{{ __('Pegawai Penyokong') }}:</p>
                            <p class="text-gray-800">
                                {{ $emailApplication->supportingOfficer->full_name ?? __('Tidak Dikenali') }}</p>
                        </div>
                    @endif
>>>>>>> 1cf33d772cd3fdd0b638a8a52f99ce5c006c2227
                </div>
            @else
                <p class="text-red-600">{{ __('Maklumat pemohon tidak dapat dimuatkan.') }}</p>
            @endif

            {{-- SECTION: Application Details --}}
<<<<<<< HEAD
            <h2 class="text-xl font-semibold mb-4">{{ __('Butiran Permohonan') }}</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <div>
=======
            <h2 class="text-xl font-semibold mb-4 text-gray-800">{{ __('Butiran Permohonan') }}</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <div class="md:col-span-2"> {{-- Make purpose field display span full width --}}
>>>>>>> 1cf33d772cd3fdd0b638a8a52f99ce5c006c2227
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
<<<<<<< HEAD
            <h2 class="text-xl font-semibold mb-4">{{ __('Perakuan Pemohon') }}</h2>
            <div class="mb-6">
=======
            <h2 class="text-xl font-semibold mb-4 text-gray-800">{{ __('Perakuan Pemohon') }}</h2>
            <div class="mb-6">
                {{-- Check the single certification_accepted field on the model --}}
>>>>>>> 1cf33d772cd3fdd0b638a8a52f99ce5c006c2227
                @if ($emailApplication->certification_accepted)
                    <p class="text-green-600">{{ __('Pemohon telah memperakui semua syarat.') }}</p>
                    <p class="text-gray-700 text-sm">{{ __('Masa Perakuan:') }}
                        {{ $emailApplication->certification_timestamp?->format('d/m/Y H:i') ?? __('Tidak Ditetapkan') }}
                    </p>
                @else
<<<<<<< HEAD
                    <p class="text-red-600">{{ __('Pemohon belum membuat perakuan.') }}</p>
=======
                    <p class="text-red-600">{{ __('Pemohon belum membuat perakuan atau permohonan belum dihantar.') }}</p>
>>>>>>> 1cf33d772cd3fdd0b638a8a52f99ce5c006c2227
                @endif
            </div>

            {{-- SECTION: Approval History (Section 9.4) --}}
<<<<<<< HEAD
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
=======
            {{-- Ensure the 'approvals' relationship is eager loaded, and 'approvals.officer' --}}
            <h2 class="text-xl font-semibold mb-4 text-gray-800">{{ __('Sejarah Kelulusan') }}</h2>
            @if ($emailApplication->approvals->isNotEmpty())
                <div class="mb-6">
                    @foreach ($emailApplication->approvals as $approval)
                        {{-- Use a slightly different border color/style for clarity --}}
                        <div class="border-l-4 border-blue-400 pl-4 mb-4">
                            <p class="text-gray-700 font-semibold">{{ __('Tahap Kelulusan:') }}
                                {{ $approval->stage ?? __('Tidak Dinyatakan') }}</p>
                            {{-- Ensure the 'officer' relationship exists and is loaded on the Approval model and has 'full_name' --}}
                            <p class="text-gray-700">{{ __('Pegawai:') }}
                                {{ $approval->officer->full_name ?? __('Tidak Dikenali') }}</p>
                            <p class="text-gray-700">{{ __('Status Keputusan:') }}
                                {{-- Dynamic classes for status badge --}}
                                {{-- The linter might warn about cssConflict here, but it's expected. --}}
                                <span
                                    class="ml-1 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    @if ($approval->status === 'approved') bg-green-100 text-green-800
                                    @elseif($approval->status === 'rejected') bg-red-100 text-red-800
                                    @else {{-- pending or other approval statuses --}} bg-yellow-100 text-yellow-800 @endif">
>>>>>>> 1cf33d772cd3fdd0b638a8a52f99ce5c006c2227
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

<<<<<<< HEAD
            {{-- SECTION: Rejection Reason (Conditional) --}}
            @if ($emailApplication->isRejected())
                {{-- Using model helper method --}}
=======
            {{-- SECTION: IT Admin Notes (Conditional) --}}
            @if ($emailApplication->admin_notes)
                <h2 class="text-xl font-semibold mb-4 text-gray-800">{{ __('Catatan IT Admin') }}</h2>
                <p class="text-gray-800 mb-6">{{ $emailApplication->admin_notes }}</p>
            @endif


            {{-- SECTION: Rejection Reason (Conditional) --}}
            {{-- Ensure isRejected() helper method exists on the model --}}
            @if ($emailApplication->isRejected())
>>>>>>> 1cf33d772cd3fdd0b638a8a52f99ce5c006c2227
                <h2 class="text-xl font-semibold text-red-600 mb-4">{{ __('Sebab Penolakan') }}</h2>
                <p class="text-red-800 mb-6">{{ $emailApplication->rejection_reason ?? __('Tiada sebab dinyatakan.') }}</p>
            @endif

            {{-- SECTION: Final Assigned Details (Conditional) --}}
            {{-- Displayed once provisioning is complete or details are assigned by IT Admin --}}
<<<<<<< HEAD
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
=======
            {{-- Check if the related user model exists AND if its motac_email OR user_id_assigned are set (as per user migration) --}}
            {{-- Ensure the 'user' relationship is eager loaded --}}
            @if ($emailApplication->user && ($emailApplication->user->motac_email || $emailApplication->user->user_id_assigned))
                <h2 class="text-xl font-semibold mb-4 text-gray-800">{{ __('Butiran Akaun yang Ditetapkan') }}</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                    @if ($emailApplication->user->motac_email)
                        <div>
                            <p class="text-gray-700 text-sm font-bold">{{ __('Akaun E-mel MOTAC') }}:</p>
                            <p class="text-gray-800">{{ $emailApplication->user->motac_email }}</p>
                        </div>
                    @endif
                    {{-- Display user_id_assigned from the related User model --}}
                    @if ($emailApplication->user->user_id_assigned)
                        <div>
                            <p class="text-gray-700 text-sm font-bold">{{ __('ID Pengguna Ditetapkan (Sistem Luar)') }}:
                            </p>
                            <p class="text-gray-800">{{ $emailApplication->user->user_id_assigned }}</p>
                            {{-- This displays the string user ID from the users table. --}}
                        </div>
                    @endif
                    {{-- 'provisioned_at' was not in the provided email_applications migration. Do not display unless added. --}}

                    {{-- Link to the provisioned user's profile if needed --}}
                    {{-- Only show if the related user exists and the current user has permission to view them --}}
                    @if ($emailApplication->user)
                        @can('view', $emailApplication->user)
                            {{-- Check if user can view the provisioned user's profile (assuming UserPolicy exists) --}}
                            {{-- Assuming a route like 'users.show' exists --}}
                            <div><a href="{{ route('users.show', $emailApplication->user) }}"
                                    class="text-blue-500 hover:underline">{{ __('Lihat Profil Pengguna Ditetapkan') }}</a>
                            </div>
                        @endcan
                    @endif
>>>>>>> 1cf33d772cd3fdd0b638a8a52f99ce5c006c2227
                </div>
            @endif


            {{-- SECTION: Action Buttons (Conditional based on status and user role/permissions) --}}
            {{-- Implement authorization checks using @can or Policy methods --}}
<<<<<<< HEAD
            <div class="flex justify-end space-x-2 mt-6">
                {{-- Example: Edit button for applicant if status is draft --}}
                {{-- Assumes a 'update' policy method check includes status and user ownership --}}
                @can('update', $emailApplication)
                    @if ($emailApplication->isDraft())
                        {{-- Use model helper method --}}
=======
            {{-- Ensure Policy methods ('update', 'delete', 'approve', 'reject', 'process') exist and are registered in AuthServiceProvider --}}
            {{-- Ensure helper methods (isDraft, isPendingSupport, isPendingAdmin) exist on the model --}}
            <div class="flex justify-end space-x-2 mt-6">
                {{-- Example: Edit button for applicant if status is draft --}}
                @can('update', $emailApplication)
                    @if ($emailApplication->isDraft())
>>>>>>> 1cf33d772cd3fdd0b638a8a52f99ce5c006c2227
                        <a href="{{ route('email-applications.edit', $emailApplication) }}"
                            class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded">
                            {{ __('Edit Permohonan') }}
                        </a>
                    @endif
                @endcan

                {{-- Example: Delete/Withdraw button for applicant if status is draft --}}
<<<<<<< HEAD
                {{-- Assumes a 'delete' policy method check includes status and user ownership --}}
                @can('delete', $emailApplication)
                    @if ($emailApplication->isDraft())
                        {{-- Use model helper method --}}
=======
                @can('delete', $emailApplication)
                    @if ($emailApplication->isDraft())
>>>>>>> 1cf33d772cd3fdd0b638a8a52f99ce5c006c2227
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
<<<<<<< HEAD
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
=======
                @can('approve', $emailApplication)
                    @if ($emailApplication->isPendingSupport())
                        {{-- Placeholder buttons to trigger approval/rejection modals --}}
                        <button type="button" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded"
                            onclick="openApprovalModal('approved')">
                            {{ __('Lulus') }}
                        </button>
                        <button type="button" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded"
                            onclick="openApprovalModal('rejected')">
                            {{ __('Tolak') }}
                        </button>
>>>>>>> 1cf33d772cd3fdd0b638a8a52f99ce5c006c2227
                    @endif
                @endcan

                {{-- Example: Process action for IT Admin if status is pending_admin --}}
<<<<<<< HEAD
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

=======
                @can('process', $emailApplication)
                    @if ($emailApplication->isPendingAdmin())
                        {{-- Placeholder button to trigger IT Admin processing modal --}}
                        <button type="button" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded"
                            onclick="openProcessingModal()">
                            {{ __('Proses Akaun') }}
                        </button>
                    @endif
                @endcan

            </div>

            {{-- TODO: Add Modals/Forms for Approval/Rejection and Processing --}}
            {{-- These modals would contain the forms to submit data. --}}
            {{-- Refer to commented-out placeholder modal structure in previous suggestions. --}}


>>>>>>> 1cf33d772cd3fdd0b638a8a52f99ce5c006c2227
        </div>
    </div>
@endsection
