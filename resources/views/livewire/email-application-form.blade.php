{{-- resources/views/livewire/email-application-form.blade.php --}}
<div>
    {{-- Display success or error messages --}}
    @if (session()->has('message'))
        <div class="alert alert-success">
            {{ session('message') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    {{-- Form Title --}}
    <h3>Permohonan Akaun Emel / ID Pengguna MOTAC</h3>

    {{-- Form adapts based on existing application --}}
    @if ($applicationId)
        <p>Editing Application Draft #{{ $applicationId }}</p>
        {{-- You might want to display the current status here --}}
        <p>Current Status: {{ $emailApplication->status ?? 'N/A' }}</p>
        @if ($emailApplication && $emailApplication->status !== 'draft')
            <div class="alert alert-info">
                This application is no longer in draft status and cannot be edited. You are viewing saved data.
            </div>
        @endif
    @else
        <p>New Application</p>
    @endif


    {{-- Application Form --}}
    {{-- wire:submit.prevent will call the submit() method in the Livewire component when the form is submitted.
       .prevent prevents the default browser form submission. --}}
    <form wire:submit.prevent="submit">

        {{-- Section: MAKLUMAT PEMOHON (Applicant Information) --}}
        {{-- Assuming basic user details are pre-filled and maybe not editable here,
           or displayed for confirmation. Add fields if the user can update them here. --}}
        <h4>MAKLUMAT PEMOHON</h4>
        {{-- Example: Displaying current user info (assuming data is available on Auth::user() and relationships) --}}
        <p>Nama Penuh: {{ Auth::user()->full_name ?? 'N/A' }}</p>
        <p>No. Pengenalan (NRIC): {{ Auth::user()->nric ?? 'N/A' }}</p>
        <p>Jawatan & Gred: {{ Auth::user()->position->name ?? 'N/A' }} & {{ Auth::user()->grade->name ?? 'N/A' }}</p>
        <p>Bahagian/Unit: {{ Auth::user()->department->name ?? 'N/A' }}</p>
        {{-- Add other pre-filled user details as needed --}}

        {{-- This field is crucial as per the form and system design --}}
        <div class="form-group">
            <label for="service_status">Taraf Perkhidmatan*:</label>
            {{-- wire:model binds the select value to the public property $service_status --}}
            <select wire:model="service_status" id="service_status" class="form-control" required
                {{ $emailApplication && $emailApplication->status !== 'draft' ? 'disabled' : '' }}>
                <option value="">- Pilih Taraf Perkhidmatan -</option>
                <option value="permanent">Kakitangan Tetap</option>
                <option value="contract">Lantikan Kontrak</option>
                <option value="mystep">Personel MySTEP</option>
                <option value="intern">Pelajar Latihan Industri</option>
                <option value="other_agency">Kakitangan Agensi Lain</option>
            </select>
            {{-- Display validation errors for this field --}}
            @error('service_status')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>

        {{-- Dynamically show fields based on service_status if needed --}}
        {{-- Example: Show specific fields only for "other_agency" --}}
        @if ($service_status === 'other_agency')
            <div class="form-group">
                <label for="personal_email">E-mel Peribadi (Agensi Utama)*:</label>
                {{-- wire:model binds to a potentially new property or assumes user can update it --}}
                {{-- If this field should only be filled for 'other_agency', it might not be directly on the User model initially --}}
                <input type="email" wire:model="personal_email" id="personal_email" class="form-control" required
                    {{ $emailApplication && $emailApplication->status !== 'draft' ? 'disabled' : '' }}>
                @error('personal_email')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>
            {{-- Add other fields specific to 'other_agency' if any --}}
            <div class="alert alert-info mt-2">
                *Perhatian: Akaun e-mel MOTAC tidak akan diwujudkan. Penetapan e-mel sandaran MOTAC akan dilaksanakan.
            </div>
        @elseif ($service_status === 'intern')
            <div class="alert alert-info mt-2">
                *Perhatian: Hanya ID Pengguna akan dibekalkan.
            </div>
        @else
            {{-- permanent, contract, mystep --}}
            {{-- Fields for full email account request --}}
            <div class="form-group">
                <label for="purpose">Tujuan Permohonan / Catatan*:</label>
                {{-- wire:model binds to $purpose --}}
                <textarea wire:model="purpose" id="purpose" class="form-control" rows="3" required
                    {{ $emailApplication && $emailApplication->status !== 'draft' ? 'disabled' : '' }}></textarea>
                @error('purpose')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="proposed_email">Cadangan E-mel:</label>
                {{-- wire:model binds to $proposed_email --}}
                {{-- This could be pre-filled by the component using a service --}}
                <input type="email" wire:model="proposed_email" id="proposed_email" class="form-control"
                    {{ $emailApplication && $emailApplication->status !== 'draft' ? 'disabled' : '' }}>
                @error('proposed_email')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>

            {{-- Optional: Group email section if applicable and shown based on role/status --}}
            {{-- You might need to add a checkbox/toggle to show these fields --}}
            @if (false)
                {{-- Replace 'false' with condition to show group email fields --}}
                <div class="form-group">
                    <label for="group_email">Nama Group Email (jika berkenaan):</label>
                    <input type="text" wire:model="group_email" id="group_email" class="form-control"
                        {{ $emailApplication && $emailApplication->status !== 'draft' ? 'disabled' : '' }}>
                    @error('group_email')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="group_admin_name">Nama Admin/EO/CC Group Email:</label>
                    <input type="text" wire:model="group_admin_name" id="group_admin_name" class="form-control"
                        {{ $emailApplication && $emailApplication->status !== 'draft' ? 'disabled' : '' }}>
                    @error('group_admin_name')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="group_admin_email">E-mel Admin/EO/CC Group Email:</label>
                    <input type="email" wire:model="group_admin_email" id="group_admin_email" class="form-control"
                        {{ $emailApplication && $emailApplication->status !== 'draft' ? 'disabled' : '' }}>
                    @error('group_admin_email')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
            @endif

        @endif


        {{-- Section: PERAKUAN PEMOHON (Applicant Certification) --}}
        {{-- This section requires three mandatory checkboxes --}}
        @if (($emailApplication && $emailApplication->status === 'draft') || !$emailApplication)
            <h4 class="mt-4">PERAKUAN PEMOHON</h4>
            <p>Saya dengan ini mengesahkan bahawa:</p>

            <div class="form-group">
                <div class="form-check">
                    {{-- wire:model binds to the boolean property $certification --}}
                    <input type="checkbox" wire:model.live="certification" id="cert_benar" value="1"
                        class="form-check-input">
                    <label class="form-check-label" for="cert_benar">
                        Semua maklumat yang dinyatakan di dalam permohonan ini adalah BENAR.
                    </label>
                </div>
                {{-- You'd typically have multiple checkboxes here, but Livewire's wire:model="certification" as a single boolean works if only one overall confirmation is needed.
                     If three *separate* confirmations are needed, you'd need three boolean properties and update the validation rule.
                     Let's assume one overall confirmation checkbox bound to $certification for simplicity, representing acceptance of all three statements.
                --}}
            </div>
            {{-- Display validation error for certification --}}
            @error('certification')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        @endif


        {{-- Form Submission Button --}}
        @if (($emailApplication && $emailApplication->status === 'draft') || !$emailApplication)
            <button type="submit" class="btn btn-primary mt-4" wire:loading.attr="disabled">
                {{-- wire:loading.attr="disabled" disables the button while the Livewire component is processing --}}
                <span wire:loading.remove>Hantar Permohonan</span>
                <span wire:loading>Menghantar...</span>
            </button>
            {{-- Optional: Save as Draft button if workflow allows --}}
            {{-- <button type="button" wire:click="saveAsDraft" class="btn btn-secondary mt-4" wire:loading.attr="disabled">
                <span wire:loading.remove>Simpan Draf</span>
                <span wire:loading>Menyimpan...</span>
            </button> --}}
        @endif

    </form>

    {{-- You can add sections to display application status history, approvals, etc. below the form --}}
    {{-- @if ($emailApplication)
      <h4 class="mt-5">Status Permohonan</h4>
      <p>Current Status: {{ $emailApplication->status }}</p>
       // Display approval history ($emailApplication->approvals)
  @endif --}}


</div>
