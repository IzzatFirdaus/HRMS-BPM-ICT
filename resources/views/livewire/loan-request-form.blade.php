{{-- resources/views/livewire/loan-request-form.blade.php --}}
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
    <h3>Borang Permohonan Peminjaman Peralatan ICT</h3>

    {{-- Form adapts based on existing application --}}
    @if ($applicationId)
        <p>Editing Application Draft #{{ $applicationId }}</p>
        {{-- Display current status if applicable --}}
        <p>Current Status: {{ $loanApplication->status ?? 'N/A' }}</p>
        @if ($loanApplication && $loanApplication->status !== 'draft')
            <div class="alert alert-info">
                This application is no longer in draft status and cannot be edited. You are viewing saved data.
            </div>
        @endif
    @else
        <p>New Application</p>
    @endif


    {{-- Loan Application Form --}}
    {{-- wire:submit.prevent will call the submit() method in the Livewire component --}}
    <form wire:submit.prevent="submit">

        {{-- BAHAGIAN 1 | MAKLUMAT PEMOHON (Applicant Information) --}}
        {{-- Assuming basic applicant details are pre-filled and displayed --}}
        <h4>BAHAGIAN 1 | MAKLUMAT PEMOHON</h4>
        {{-- Displaying current user info (assuming data is available on Auth::user() and relationships) --}}
        <p>Nama Penuh: {{ Auth::user()->full_name ?? 'N/A' }}</p>
        <p>Jawatan & Gred: {{ Auth::user()->position->name ?? 'N/A' }} & {{ Auth::user()->grade->name ?? 'N/A' }}</p>
        <p>Bahagian/Unit: {{ Auth::user()->department->name ?? 'N/A' }}</p>
        <p>No.Telefon: {{ Auth::user()->mobile_number ?? 'N/A' }}</p> {{-- Assuming mobile_number is on User model --}}


        {{-- Fields for the loan request details --}}
        <div class="form-group">
            <label for="purpose">Tujuan Permohonan*:</label>
            {{-- wire:model binds to $purpose --}}
            <textarea wire:model="purpose" id="purpose" class="form-control" rows="3" required
                {{ $loanApplication && $loanApplication->status !== 'draft' ? 'disabled' : '' }}></textarea>
            @error('purpose')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="location">Lokasi Penggunaan Peralatan*:</label>
            {{-- wire:model binds to $location --}}
            <input type="text" wire:model="location" id="location" class="form-control" required
                {{ $loanApplication && $loanApplication->status !== 'draft' ? 'disabled' : '' }}>
            @error('location')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="loan_start_date">Tarikh Pinjaman*:</label>
            {{-- wire:model binds to $loan_start_date --}}
            <input type="date" wire:model="loan_start_date" id="loan_start_date" class="form-control" required
                {{ $loanApplication && $loanApplication->status !== 'draft' ? 'disabled' : '' }}>
            @error('loan_start_date')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="loan_end_date">Tarikh Dijangka Pulang*:</label>
            {{-- wire:model binds to $loan_end_date --}}
            <input type="date" wire:model="loan_end_date" id="loan_end_date" class="form-control" required
                {{ $loanApplication && $loanApplication->status !== 'draft' ? 'disabled' : '' }}>
            @error('loan_end_date')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>


        {{-- BAHAGIAN 2 | MAKLUMAT PEGAWAI BERTANGGUNGJAWAB (Responsible Officer Information) --}}
        <h4 class="mt-4">BAHAGIAN 2 | MAKLUMAT PEGAWAI BERTANGGUNGJAWAB</h4>

        <div class="form-group">
            <div class="form-check">
                {{-- wire:model binds to the boolean property $is_applicant_responsible --}}
                <input type="checkbox" wire:model.live="is_applicant_responsible" id="is_applicant_responsible"
                    class="form-check-input"
                    {{ $loanApplication && $loanApplication->status !== 'draft' ? 'disabled' : '' }}>
                <label class="form-check-label" for="is_applicant_responsible">
                    Sila tandakan jika Pemohon adalah Pegawai Bertanggungjawab.
                </label>
            </div>
        </div>

        {{-- Show responsible officer fields ONLY if applicant is NOT responsible --}}
        @if (!$is_applicant_responsible)
            <div class="alert alert-info mt-2">
                Bahagian ini hanya perlu diisi jika Pegawai Bertanggungjawab bukan Pemohon.
            </div>
            <div class="form-group">
                <label for="responsible_officer_id">Nama Pegawai Bertanggungjawab*:</label>
                {{-- wire:model binds to $responsible_officer_id --}}
                <select wire:model="responsible_officer_id" id="responsible_officer_id" class="form-control" required
                    {{ $loanApplication && $loanApplication->status !== 'draft' ? 'disabled' : '' }}>
                    <option value="">- Pilih Pegawai -</option>
                    {{-- Iterate over the list of potential responsible officers --}}
                    @foreach ($responsibleOfficers as $officer)
                        <option value="{{ $officer->id }}">{{ $officer->full_name }}
                            ({{ $officer->position->name ?? 'N/A' }} - {{ $officer->grade->name ?? 'N/A' }})</option>
                    @endforeach
                </select>
                @error('responsible_officer_id')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>
            {{-- You might want to display chosen officer's details (Jawatan, Gred, No.Telefon) dynamically here --}}
        @endif

        {{-- BAHAGIAN 3 | MAKLUMAT PERALATAN (Equipment Information) --}}
        <h4 class="mt-4">BAHAGIAN 3 | MAKLUMAT PERALATAN</h4>

        {{-- Loop through the $items array to show rows for requested equipment --}}
        @foreach ($items as $index => $item)
            <div class="form-row align-items-center border-bottom mb-3 pb-3">
                <div class="col">
                    <label for="equipment_type_{{ $index }}">Jenis Peralatan*:</label>
                    {{-- wire:model.live allows real-time updates and validation for each item --}}
                    <input type="text" wire:model.live="items.{{ $index }}.equipment_type"
                        id="equipment_type_{{ $index }}" class="form-control" required
                        {{ $loanApplication && $loanApplication->status !== 'draft' ? 'disabled' : '' }}>
                    {{-- Display validation errors for this specific item's equipment_type --}}
                    @error('items.' . $index . '.equipment_type')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
                <div class="col-auto">
                    <label for="quantity_requested_{{ $index }}">Kuantiti*:</label>
                    {{-- wire:model.live binds to quantity_requested --}}
                    <input type="number" wire:model.live="items.{{ $index }}.quantity_requested"
                        id="quantity_requested_{{ $index }}" class="form-control" min="1" required
                        {{ $loanApplication && $loanApplication->status !== 'draft' ? 'disabled' : '' }}>
                    {{-- Display validation errors for quantity_requested --}}
                    @error('items.' . $index . '.quantity_requested')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
                <div class="col">
                    <label for="item_notes_{{ $index }}">Catatan:</label>
                    {{-- wire:model.live binds to notes --}}
                    <input type="text" wire:model.live="items.{{ $index }}.notes"
                        id="item_notes_{{ $index }}" class="form-control"
                        {{ $loanApplication && $loanApplication->status !== 'draft' ? 'disabled' : '' }}>
                    {{-- Display validation errors for notes --}}
                    @error('items.' . $index . '.notes')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
                <div class="col-auto">
                    {{-- Button to remove this item row --}}
                    @if (($loanApplication && $loanApplication->status === 'draft') || !$loanApplication)
                        @if (count($items) > 1)
                            {{-- Prevent removing the last item --}}
                            <button type="button" wire:click="removeItem({{ $index }})"
                                class="btn btn-danger btn-sm mt-3" wire:loading.attr="disabled">
                                <i class="fa fa-trash"></i>
                            </button>
                        @endif
                    @endif
                </div>
            </div>
        @endforeach

        {{-- Button to add a new equipment item row --}}
        @if (($loanApplication && $loanApplication->status === 'draft') || !$loanApplication)
            <button type="button" wire:click="addItem" class="btn btn-secondary btn-sm mt-2"
                wire:loading.attr="disabled">
                <i class="fa fa-plus"></i> Tambah Peralatan
            </button>
            @error('items')
                <span class="text-danger d-block mt-2">{{ $message }}</span>
            @enderror {{-- Error if no items --}}
        @endif


        {{-- BAHAGIAN 4 | PENGESAHAN PEMOHON (Applicant Confirmation) --}}
        {{-- This section requires a mandatory checkbox --}}
        @if (($loanApplication && $loanApplication->status === 'draft') || !$loanApplication)
            <h4 class="mt-4">BAHAGIAN 4 | PENGESAHAN PEMOHON (PEGAWAI BERTANGGUNGJAWAB)</h4>

            <p>Saya dengan ini mengesahkan dan memperakukan bahawa semua peralatan yang dipinjam adalah untuk kegunaan
                rasmi dan berada di bawah tanggungjawab dan penyeliaan saya sepanjang tempoh tersebut;</p>

            <div class="form-group">
                <div class="form-check">
                    {{-- wire:model binds to the boolean property $applicant_confirmation --}}
                    <input type="checkbox" wire:model.live="applicant_confirmation" id="applicant_confirmation"
                        value="1" class="form-check-input">
                    <label class="form-check-label" for="applicant_confirmation">
                        Saya faham dan bersetuju dengan syarat-syarat peminjaman peralatan ICT.
                    </label>
                </div>
            </div>
            {{-- Display validation error for confirmation --}}
            @error('applicant_confirmation')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        @endif

        {{-- Form Submission Button --}}
        @if (($loanApplication && $loanApplication->status === 'draft') || !$loanApplication)
            <button type="submit" class="btn btn-primary mt-4" wire:loading.attr="disabled">
                {{-- wire:loading.attr="disabled" disables the button while the Livewire component is processing --}}
                <span wire:loading.remove>Hantar Permohonan</span>
                <span wire:loading>Menghantar...</span>
            </button>
            {{-- Optional: Save as Draft button --}}
            {{-- <button type="button" wire:click="saveAsDraft" class="btn btn-secondary mt-4" wire:loading.attr="disabled">
               <span wire:loading.remove>Simpan Draf</span>
               <span wire:loading>Menyimpan...</span>
           </button> --}}
        @endif

    </form>

    {{-- You can add sections to display application status, approvals, transactions, etc. below the form --}}
    {{-- @if ($loanApplication)
      <h4 class="mt-5">Status Permohonan</h4>
      <p>Current Status: {{ $loanApplication->status }}</p>
       // Display approvals ($loanApplication->approvals)
       // Display transactions ($loanApplication->transactions)
  @endif --}}

</div>
