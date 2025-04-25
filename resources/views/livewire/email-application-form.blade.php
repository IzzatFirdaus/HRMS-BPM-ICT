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

    {{-- Display application reference ID and status if editing --}}
    @if ($applicationId)
        <p>Nombor Rujukan Permohonan: #{{ $applicationId }}</p>
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
        <p>Nama Penuh: {{ Auth::user()->name ?? 'N/A' }}</p> {{-- Assuming 'name' for full name --}}
        <p>No. Pengenalan (NRIC): {{ Auth::user()->nric ?? 'N/A' }}</p>
        {{-- Assuming relationships exist and have 'name' attribute --}}
        <p>Jawatan & Gred: {{ Auth::user()->position->name ?? 'N/A' }} & {{ Auth::user()->grade->name ?? 'N/A' }}</p>
        <p>Bahagian/Unit: {{ Auth::user()->department->name ?? 'N/A' }}</p>
        <p>No. Telefon Bimbit: {{ Auth::user()->phone_number ?? 'N/A' }}</p> {{-- Added phone number from PDF --}}
        <p>E-mel Peribadi: {{ Auth::user()->personal_email ?? 'N/A' }}</p> {{-- Added personal email from PDF --}}


        {{-- This field is crucial as per the form and system design --}}
        <div class="form-group">
            <label for="service_status">Taraf Perkhidmatan*:</label>
            {{-- wire:model binds the select value to the public property $service_status --}}
            <select wire:model.live="service_status" id="service_status" class="form-control" required
                {{ $emailApplication && $emailApplication->status !== 'draft' ? 'disabled' : '' }}>
                <option value="">- Pilih Taraf Perkhidmatan -</option>
                {{-- Use the exact enum values from the migration/form request --}}
                <option value="Kakitangan Tetap">Kakitangan Tetap</option>
                <option value="Lantikan Kontrak">Lantikan Kontrak</option>
                <option value="Personel MySTEP">Personel MySTEP</option>
                <option value="Pelajar Latihan Industri">Pelajar Latihan Industri</option>
                <option value="E-mel Sandaran MOTAC">E-mel Sandaran MOTAC (Kakitangan Agensi Lain)</option>
            </select>
            {{-- Display validation errors for this field --}}
            @error('service_status')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>

        {{-- Dynamically show fields based on service_status as per PDF notes --}}
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
                <label for="purpose">Tujuan Permohonan / Catatan*:</label>
                {{-- wire:model binds to $purpose --}}
                <textarea wire:model="purpose" id="purpose" class="form-control" rows="3" required
                    {{ $emailApplication && $emailApplication->status !== 'draft' ? 'disabled' : '' }}></textarea>
                @error('purpose')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="proposed_email">Cadangan E-mel:</label> {{-- This field is optional --}}
                {{-- wire:model binds to $proposed_email --}}
                {{-- This could be pre-filled by the component using a service based on user name --}}
                <input type="email" wire:model="proposed_email" id="proposed_email" class="form-control"
                    {{ $emailApplication && $emailApplication->status !== 'draft' ? 'disabled' : '' }}>
                @error('proposed_email')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>

            {{-- Section for Group email request - shown based on a checkbox --}}
            <div class="form-group mt-4">
                <div class="form-check">
                    {{-- wire:model.live to toggle visibility immediately --}}
                    <input type="checkbox" wire:model.live="is_group_email_request" id="is_group_email_request"
                        value="1" class="form-check-input"
                        {{ $emailApplication && $emailApplication->status !== 'draft' ? 'disabled' : '' }}>
                    <label class="form-check-label" for="is_group_email_request">
                        Ini adalah Permohonan Group E-mel?
                    </label>
                </div>
            </div>

            {{-- Show Group Email fields only if the checkbox is ticked --}}
            @if ($is_group_email_request)
                <div class="card p-3 mb-4"> {{-- Optional: style with a card/border --}}
                    <h5 class="card-title">Butiran Group E-mel</h5>
                    <div class="form-group">
                        <label for="group_email">Nama Group Email*:</label> {{-- Made required if requesting group email --}}
                        <input type="text" wire:model="group_email" id="group_email" class="form-control" required
                            {{ $emailApplication && $emailApplication->status !== 'draft' ? 'disabled' : '' }}>
                        @error('group_email')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="group_admin_name">Nama Admin/EO/CC Group Email*:</label> {{-- Required if requesting group email --}}
                        <input type="text" wire:model="group_admin_name" id="group_admin_name" class="form-control"
                            required
                            {{ $emailApplication && $emailApplication->status !== 'draft' ? 'disabled' : '' }}>
                        @error('group_admin_name')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="group_admin_email">E-mel Admin/EO/CC Group Email*:</label> {{-- Required if requesting group email --}}
                        <input type="email" wire:model="group_admin_email" id="group_admin_email" class="form-control"
                            required
                            {{ $emailApplication && $emailApplication->status !== 'draft' ? 'disabled' : '' }}>
                        @error('group_admin_email')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="alert alert-warning mt-2">
                        *Sila pastikan E-mel Admin/EO/CC Group E-mel adalah e-mel rasmi MOTAC.
                    </div>
                </div>
            @endif

        @endif {{-- End of condition for full email account request fields --}}


        {{-- Section: PERAKUAN PEMOHON (Applicant Certification) --}}
        {{-- This section requires acceptance of terms before submission --}}
        {{-- Only show certification section if the application is a draft or new --}}
        @if (($emailApplication && $emailApplication->status === 'draft') || !$emailApplication)
            <h4 class="mt-4">PERAKUAN PEMOHON</h4>
            <p>Saya dengan ini mengesahkan bahawa:</p>

            {{-- Note: The PDF has three separate checkboxes for certification.
               For simplicity in Livewire, we are using a single boolean property ($certification)
               bound to one checkbox representing acceptance of all terms.
               If three separate confirmations are strictly required, you would need
               three boolean properties (e.g., $cert1, $cert2, $cert3) and update the
               Livewire component and validation rules accordingly.
               The 'certification_accepted' field in the database stores the final boolean state. --}}
            <div class="form-group">
                <div class="form-check">
                    {{-- wire:model binds to the boolean property $certification --}}
                    <input type="checkbox" wire:model.live="certification" id="certification" value="1"
                        class="form-check-input" required> {{-- Required to be checked for submission --}}
                    <label class="form-check-label" for="certification">
                        Saya telah membaca dan memahami semua maklumat di dalam borang permohonan ini dan mengesahkan
                        semua maklumat yang dinyatakan adalah BENAR. Saya juga BERSETUJU maklumat ini diguna pakai oleh
                        BPM dan BERSETUJU untuk bertanggungjawab ke atas akaun e-mel saya.
                    </label>
                </div>
            </div>
            {{-- Display validation error for certification --}}
            @error('certification')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        @endif


        {{-- Form Submission Buttons --}}
        {{-- Only show buttons if the application is a draft or a new application --}}
        @if (($emailApplication && $emailApplication->status === 'draft') || !$emailApplication)
            <button type="submit" class="btn btn-primary mt-4" wire:loading.attr="disabled">
                {{-- wire:loading.attr="disabled" disables the button while the Livewire component is processing --}}
                <span wire:loading.remove>Hantar Permohonan</span>
                <span wire:loading>Menghantar...</span>
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
    {{-- @if ($emailApplication)
    <h4 class="mt-5">Maklumat Proses Permohonan</h4>
    <p>Status Semasa: {{ $emailApplication->status }}</p>
      // Display approval history ($emailApplication->approvals)
      // Display rejection reason if status is rejected
      // Display final assigned email/ID if status is completed/provisioned
  @endif --}}


</div>
