{{-- resources/views/livewire/email-application-form.blade.php --}}
<div>
    {{-- Display success or error messages using Livewire's flash messages or session --}}
    {{-- Livewire often uses $this->dispatch('message', ...) and listeners in layout for toast messages --}}
    {{-- Or you can use basic session flashes: --}}
    @if (session()->has('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    {{-- Form Title --}}
    <h3>Permohonan Akaun Emel / ID Pengguna MOTAC</h3>

    {{-- Display application reference ID and status if editing an existing application --}}
    {{-- $applicationId would be a public property in the Livewire component, null for new forms --}}
    @if ($applicationId)
        <p>Nombor Rujukan Permohonan: #{{ $applicationId }}</p>
        {{-- Assuming $emailApplication is a public property loaded when editing --}}
        <p>Status Semasa: {{ $emailApplication->status ?? 'N/A' }}</p>
        {{-- Display message if the application is not in draft and cannot be edited --}}
        @if ($emailApplication && $emailApplication->status !== 'draft')
            <div class="alert alert-info">
                Permohonan ini bukan dalam status draf dan tidak boleh disunting. Anda sedang melihat data tersimpan.
            </div>
        @endif
    @else
        <p>Permohonan Baru</p>
    @endif


    {{-- Application Form --}}
    {{-- wire:submit.prevent will call the submit() method in the Livewire component when the form is submitted.
      .prevent prevents the default browser form submission. --}}
    <form wire:submit.prevent="submit">

        {{-- Section: MAKLUMAT PEMOHON (Applicant Information) --}}
        <h4>MAKLUMAT PEMOHON</h4>
        {{-- Display basic user details, assumed to be pre-filled and not editable on this form.
          Ensure your User model has these attributes/relationships (position, grade, department). --}}
        {{-- Using Auth::user() directly as these are read-only fields populated from authenticated user --}}
        <p>Nama Penuh: {{ Auth::user()->name ?? 'N/A' }}</p> {{-- Assuming 'name' for full name, provide fallback --}}
        <p>No. Pengenalan (NRIC): {{ Auth::user()->nric ?? 'N/A' }}</p> {{-- Display NRIC, provide fallback --}}
        {{-- Assuming relationships exist and have 'name' attribute, provide fallbacks --}}
        <p>Jawatan & Gred: {{ Auth::user()->position->name ?? 'N/A' }} & {{ Auth::user()->grade->name ?? 'N/A' }}</p>
        <p>Bahagian/Unit: {{ Auth::user()->department->name ?? 'N/A' }}</p>
        <p>No. Telefon Bimbit: {{ Auth::user()->phone_number ?? 'N/A' }}</p> {{-- Added phone number from PDF, provide fallback --}}
        <p>E-mel Peribadi: {{ Auth::user()->personal_email ?? 'N/A' }}</p> {{-- Added personal email from PDF, provide fallback --}}


        {{-- This field is crucial as per the form and system design --}}
        <div class="form-group">
            <label for="service_status">Taraf Perkhidmatan*:</label>
            {{-- wire:model.live binds the select value to the public property $service_status. .live updates property immediately on change. --}}
            {{-- Disable the field if the application is not a draft --}}
            <select wire:model.live="service_status" id="service_status" class="form-control" required
                {{ $emailApplication && $emailApplication->status !== 'draft' ? 'disabled' : '' }}>
                <option value="">- Pilih Taraf Perkhidmatan -</option> {{-- Default placeholder option --}}
                {{-- Use the exact enum values from the migration/form request for backend consistency --}}
                <option value="Kakitangan Tetap">Kakitangan Tetap</option>
                <option value="Lantikan Kontrak">Lantikan Kontrak</option>
                <option value="Personel MySTEP">Personel MySTEP</option>
                <option value="Pelajar Latihan Industri">Pelajar Latihan Industri</option>
                <option value="E-mel Sandaran MOTAC">E-mel Sandaran MOTAC (Kakitangan Agensi Lain)</option>
            </select>
            {{-- Display validation errors for this field using standard Blade @if --}}
            {{-- REPLACED: @error('service_status') <span class="text-danger">{{ $message }}</span> @enderror --}}
            {{-- Standard way to display errors for a specific field using $errors bag --}}
            @if ($errors->has('service_status'))
                <span class="text-danger">{{ $errors->first('service_status') }}</span>
            @endif
        </div>

        {{-- Dynamically show fields and alerts based on service_status as per PDF notes --}}
        @if ($service_status === 'E-mel Sandaran MOTAC') {{-- Matches enum value --}}
            <div class="alert alert-info mt-2">
                *Perhatian: Akaun e-mel MOTAC baru tidak akan diwujudkan. Penetapan e-mel sandaran MOTAC akan
                dilaksanakan.
            </div>
            {{-- Note: Personal email is displayed above from Auth::user() data --}}
        @elseif ($service_status === 'Pelajar Latihan Industri')
            {{-- Matches enum value --}}
            <div class="alert alert-info mt-2">
                *Perhatian: Hanya ID Pengguna akan dibekalkan, bukan akaun e-mel penuh.
            </div>
        @elseif (in_array($service_status, ['Kakitangan Tetap', 'Lantikan Kontrak', 'Personel MySTEP']))
            {{-- Matches enum values for full account request --}}
            {{-- Fields for full email account request --}}
            <div class="form-group">
                <label for="purpose">Tujuan Permohonan / Catatan*:</label> {{-- Made required as per form logic --}}
                {{-- wire:model binds textarea value to $purpose property --}}
                {{-- Disable if not draft --}}
                <textarea wire:model="purpose" id="purpose" class="form-control" rows="3" required
                    {{ $emailApplication && $emailApplication->status !== 'draft' ? 'disabled' : '' }}></textarea>
                {{-- Display validation errors for this field using standard Blade @if --}}
                @if ($errors->has('purpose'))
                    <span class="text-danger">{{ $errors->first('purpose') }}</span>
                @endif
            </div>

            <div class="form-group">
                <label for="proposed_email">Cadangan E-mel:</label> {{-- This field is optional --}}
                {{-- wire:model binds input value to $proposed_email property --}}
                {{-- This could potentially be pre-filled by the component using a service based on user name --}}
                {{-- Disable if not draft --}}
                <input type="email" wire:model="proposed_email" id="proposed_email" class="form-control"
                    {{ $emailApplication && $emailApplication->status !== 'draft' ? 'disabled' : '' }}>
                {{-- Display validation errors for this field using standard Blade @if --}}
                @if ($errors->has('proposed_email'))
                    <span class="text-danger">{{ $errors->first('proposed_email') }}</span>
                @endif
            </div>

            {{-- Section for Group email request - shown based on a checkbox --}}
            <div class="form-group mt-4">
                <div class="form-check">
                    {{-- wire:model.live binds checkbox state to $is_group_email_request. .live updates property immediately. --}}
                    {{-- Use value="1" for checked state if the property is a boolean/integer --}}
                    {{-- Disable if not draft --}}
                    <input type="checkbox" wire:model.live="is_group_email_request" id="is_group_email_request"
                        value="1" class="form-check-input"
                        {{ $emailApplication && $emailApplication->status !== 'draft' ? 'disabled' : '' }}>
                    <label class="form-check-label" for="is_group_email_request">
                        Ini adalah Permohonan Group E-mel?
                    </label>
                </div>
            </div>

            {{-- Show Group Email fields only if the checkbox is ticked ($is_group_email_request is true/truthy) --}}
            @if ($is_group_email_request)
                <div class="card p-3 mb-4"> {{-- Optional: style with a card/border --}}
                    <h5 class="card-title">Butiran Group E-mel</h5>
                    <div class="form-group">
                        <label for="group_email">Nama Group Email*:</label> {{-- Made required if requesting group email --}}
                        {{-- wire:model binds input value to $group_email property --}}
                        {{-- Disable if not draft --}}
                        <input type="text" wire:model="group_email" id="group_email" class="form-control" required
                            {{ $emailApplication && $emailApplication->status !== 'draft' ? 'disabled' : '' }}>
                        {{-- Display validation errors for this field using standard Blade @if --}}
                        @if ($errors->has('group_email'))
                            <span class="text-danger">{{ $errors->first('group_email') }}</span>
                        @endif
                    </div>
                    <div class="form-group">
                        <label for="group_admin_name">Nama Admin/EO/CC Group Email*:</label> {{-- Required if requesting group email --}}
                        {{-- wire:model binds input value to $group_admin_name property --}}
                        {{-- Disable if not draft --}}
                        <input type="text" wire:model="group_admin_name" id="group_admin_name" class="form-control"
                            required
                            {{ $emailApplication && $emailApplication->status !== 'draft' ? 'disabled' : '' }}>
                        {{-- Display validation errors for this field using standard Blade @if --}}
                        @if ($errors->has('group_admin_name'))
                            <span class="text-danger">{{ $errors->first('group_admin_name') }}</span>
                        @endif
                    </div>
                    <div class="form-group">
                        <label for="group_admin_email">E-mel Admin/EO/CC Group Email*:</label> {{-- Required if requesting group email --}}
                        {{-- wire:model binds input value to $group_admin_email property --}}
                        {{-- Disable if not draft --}}
                        <input type="email" wire:model="group_admin_email" id="group_admin_email" class="form-control"
                            required
                            {{ $emailApplication && $emailApplication->status !== 'draft' ? 'disabled' : '' }}>
                        {{-- Display validation errors for this field using standard Blade @if --}}
                        @if ($errors->has('group_admin_email'))
                            <span class="text-danger">{{ $errors->first('group_admin_email') }}</span>
                        @endif
                    </div>
                    <div class="alert alert-warning mt-2">
                        *Sila pastikan E-mel Admin/EO/CC Group E-mel adalah e-mel rasmi MOTAC.
                    </div>
                </div>
            @endif

        @endif {{-- End of condition for full email account request fields --}}


        {{-- Section: PERAKUAN PEMOHON (Applicant Certification) --}}
        {{-- This section requires acceptance of terms before submission --}}
        {{-- Only show certification section if the application is a draft or a new application --}}
        @if (($emailApplication && $emailApplication->status === 'draft') || !$emailApplication)
            <h4 class="mt-4">PERAKUAN PEMOHON</h4>
            <p>Saya dengan ini mengesahkan bahawa:</p>

            {{-- Note: The PDF has three separate checkboxes for certification.
               For simplicity in Livewire, this implementation uses a single boolean property ($certification)
               bound to one checkbox representing acceptance of all terms.
               If three separate confirmations are strictly required, you would need
               three boolean properties (e.g., $cert1, $cert2, $cert3) in the Livewire component
               and update the validation rules accordingly.
               The 'certification_accepted' field in the database stores the final boolean state. --}}
            <div class="form-group">
                <div class="form-check">
                    {{-- wire:model.live binds the checkbox state to the boolean property $certification --}}
                    {{-- Use value="1" for checked state --}}
                    {{-- Required to be checked for submission --}}
                    <input type="checkbox" wire:model.live="certification" id="certification" value="1"
                        class="form-check-input" required>
                    <label class="form-check-label" for="certification">
                        Saya telah membaca dan memahami semua maklumat di dalam borang permohonan ini dan mengesahkan
                        semua maklumat yang dinyatakan adalah BENAR. Saya juga BERSETUJU maklumat ini diguna pakai oleh
                        BPM dan BERSETUJU untuk bertanggungjawab ke atas akaun e-mel saya.
                    </label>
                </div>
            </div>
            {{-- Display validation error for certification using standard Blade @if --}}
            {{-- REPLACED: @error('certification') <span class="text-danger">{{ $message }}</span> @enderror --}}
            @if ($errors->has('certification'))
                <span class="text-danger">{{ $errors->first('certification') }}</span>
            @endif
        @endif


        {{-- Form Submission Buttons --}}
        {{-- Only show buttons if the application is a draft or a new application --}}
        @if (($emailApplication && $emailApplication->status === 'draft') || !$emailApplication)
            {{-- Submit button --}}
            <button type="submit" class="btn btn-primary mt-4" wire:loading.attr="disabled">
                {{-- wire:loading.attr="disabled" disables the button while the Livewire component is processing --}}
                <span wire:loading.remove>Hantar Permohonan</span> {{-- Text when not loading --}}
                <span wire:loading>Menghantar...</span> {{-- Text when loading --}}
            </button>
            {{-- Optional: Save as Draft button if workflow allows saving without full validation --}}
            {{-- wire:click="saveAsDraft" would call a method to save the current state without submitting --}}
            {{-- <button type="button" wire:click="saveAsDraft" class="btn btn-secondary mt-4 ml-2" wire:loading.attr="disabled">
              <span wire:loading.remove>Simpan Draf</span>
              <span wire:loading>Menyimpan...</span>
          </button> --}}
        @endif

    </form>

    {{-- You can add sections to display application status history, approvals, etc. below the form --}}
    {{-- @if ($emailApplication) // Show this section only if an application is loaded (editing/viewing)
      <h4 class="mt-5">Maklumat Proses Permohonan</h4>
      <p>Status Semasa: {{ $emailApplication->status }}</p>
      // Display approval history ($emailApplication->approvals relation)
      // Display rejection reason if status is rejected
      // Display final assigned email/ID if status is completed/provisioned
  @endif --}}


</div>
