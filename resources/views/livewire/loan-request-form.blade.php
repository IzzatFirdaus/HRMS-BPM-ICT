<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Borang Permohonan Pinjaman Peralatan ICT</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Optional: Add custom styles if needed, but prefer Tailwind */
        .form-group {
            margin-bottom: 1rem;
        }

        .form-control {
            width: 100%;
            padding: 0.5rem 0.75rem;
            border: 1px solid #d1d5db;
            /* gray-300 */
            border-radius: 0.25rem;
            box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.075);
            /* Add focus styles */
            outline: none;
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        }

        .form-control:focus {
            border-color: #60a5fa;
            /* blue-400 */
            box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.075), 0 0 0 0.2rem rgba(96, 165, 250, 0.25);
            /* blue-400 with alpha */
        }

        .form-check-input {
            margin-right: 0.5rem;
        }

        .text-danger {
            color: #ef4444;
            /* red-500 */
            font-size: 0.875rem;
            /* text-sm */
        }

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

        .btn-danger {
            background-color: #ef4444;
            /* red-500 */
            color: #fff;
            border: 1px solid #ef4444;
        }

        .btn-danger:hover {
            background-color: #dc2626;
            /* red-600 */
            border-color: #dc2626;
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

    <div class="max-w-4xl mx-auto bg-white p-8 rounded-lg shadow-md"> {{-- Increased max-width slightly for table --}}

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
        <h3 class="text-2xl font-bold mb-6 text-center text-gray-800">BORANG PERMOHONAN PEMINJAMAN PERALATAN ICT</h3>

        {{-- Display application reference ID and status if editing --}}
        @if ($applicationId)
            <p class="mb-2 text-gray-700">Nombor Rujukan Permohonan: #{{ $applicationId }}</p>
            <p class="mb-4 text-gray-700">Status Semasa: {{ $loanApplication->status ?? 'N/A' }}</p>
            {{-- Display message if the application is not in draft and cannot be edited --}}
            @if ($loanApplication && $loanApplication->status !== 'draft')
                <div class="alert alert-info">
                    Permohonan ini bukan dalam status draf dan tidak boleh disunting. Anda sedang melihat data
                    tersimpan.
                </div>
            @endif
        @else
            <p class="mb-4 text-gray-700">Permohonan Baru</p>
        @endif


        {{-- Application Form --}}
        {{-- wire:submit.prevent will call the submit() method in the Livewire component when the form is submitted.
             .prevent prevents the default browser form submission. --}}
        <form wire:submit.prevent="submit">

            {{-- BAHAGIAN 1 | MAKLUMAT PEMOHON --}}
            <div class="card">
                <h4 class="card-title">BAHAGIAN 1 | MAKLUMAT PEMOHON</h4>
                {{-- Display basic user details, assumed to be pre-filled and not editable on this form.
                     Ensure your User model has these attributes/relationships (position, grade, department). --}}
                <div class="form-group">
                    <label class="block text-gray-700 text-sm font-bold mb-1">Nama Penuh:</label>
                    <p class="text-gray-800">{{ Auth::user()->name ?? 'N/A' }}</p> {{-- Assuming 'name' for full name --}}
                </div>
                <div class="form-group">
                    <label class="block text-gray-700 text-sm font-bold mb-1">Jawatan & Gred:</label>
                    {{-- Assuming relationships exist and have 'name' attribute --}}
                    <p class="text-gray-800">{{ Auth::user()->position->name ?? 'N/A' }} &
                        {{ Auth::user()->grade->name ?? 'N/A' }}</p>
                </div>
                <div class="form-group">
                    <label class="block text-gray-700 text-sm font-bold mb-1">Bahagian/Unit:</label>
                    <p class="text-gray-800">{{ Auth::user()->department->name ?? 'N/A' }}</p>
                </div>
                <div class="form-group">
                    <label class="block text-gray-700 text-sm font-bold mb-1">No. Telefon:</label>
                    <p class="text-gray-800">{{ Auth::user()->phone_number ?? 'N/A' }}</p> {{-- Using phone_number from User model --}}
                </div>

                {{-- Form fields for Loan Details --}}
                <div class="form-group">
                    <label for="purpose" class="block text-gray-700 text-sm font-bold mb-2">Tujuan Permohonan*:</label>
                    <textarea wire:model="purpose" id="purpose" class="form-control" rows="3" required
                        {{ $loanApplication && $loanApplication->status !== 'draft' ? 'disabled' : '' }}></textarea>
                    @error('purpose')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="location" class="block text-gray-700 text-sm font-bold mb-2">Lokasi Penggunaan
                        Peralatan*:</label>
                    <input type="text" wire:model="location" id="location" class="form-control" required
                        {{ $loanApplication && $loanApplication->status !== 'draft' ? 'disabled' : '' }}>
                    @error('location')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="loan_start_date" class="block text-gray-700 text-sm font-bold mb-2">Tarikh
                        Pinjaman*:</label>
                    <input type="date" wire:model="loan_start_date" id="loan_start_date" class="form-control"
                        required {{ $loanApplication && $loanApplication->status !== 'draft' ? 'disabled' : '' }}>
                    @error('loan_start_date')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="loan_end_date" class="block text-gray-700 text-sm font-bold mb-2">Tarikh Dijangka
                        Pulang*:</label>
                    <input type="date" wire:model="loan_end_date" id="loan_end_date" class="form-control" required
                        {{ $loanApplication && $loanApplication->status !== 'draft' ? 'disabled' : '' }}>
                    @error('loan_end_date')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
            </div> {{-- End BAHAGIAN 1 --}}


            {{-- BAHAGIAN 2 | MAKLUMAT PEGAWAI BERTANGGUNGJAWAB --}}
            <div class="card">
                <h4 class="card-title">BAHAGIAN 2 | MAKLUMAT PEGAWAI BERTANGGUNGJAWAB</h4>
                <div class="form-group">
                    <div class="flex items-center"> {{-- Use flex for checkbox and label alignment --}}
                        {{-- wire:model.live binds to the boolean property $is_applicant_responsible --}}
                        <input type="checkbox" wire:model.live="is_applicant_responsible" id="is_applicant_responsible"
                            value="1"
                            class="form-check-input h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                            {{-- Tailwind checkbox styles --}}
                            {{ $loanApplication && $loanApplication->status !== 'draft' ? 'disabled' : '' }}>
                        <label class="ml-2 block text-sm text-gray-700" for="is_applicant_responsible">
                            {{-- Adjusted label styling --}}
                            Sila tandakan jika Pemohon adalah Pegawai Bertanggungjawab.
                        </label>
                    </div>
                </div>

                {{-- Show Responsible Officer fields only if the applicant is NOT the responsible officer --}}
                @if (!$is_applicant_responsible)
                    <p class="text-gray-600 italic mb-4 text-sm">Bahagian ini hanya perlu diisi jika Pegawai
                        Bertanggungjawab bukan Pemohon.</p>

                    <div class="form-group">
                        <label for="responsible_officer_id" class="block text-gray-700 text-sm font-bold mb-2">Nama
                            Penuh Pegawai Bertanggungjawab*:</label>
                        {{-- wire:model binds to responsible_officer_id. This should be a select/search input linked to Users --}}
                        {{-- Assuming you have a public property $responsibleOfficers containing available users --}}
                        <select wire:model="responsible_officer_id" id="responsible_officer_id" class="form-control"
                            required {{ $loanApplication && $loanApplication->status !== 'draft' ? 'disabled' : '' }}>
                            <option value="">- Pilih Pegawai -</option>
                            {{-- Loop through the collection of responsible officers --}}
                            @foreach ($responsibleOfficers as $officer)
                                <option value="{{ $officer->id }}">{{ $officer->name }}
                                    ({{ $officer->position->name ?? 'N/A' }} - {{ $officer->grade->name ?? 'N/A' }})
                                </option>
                            @endforeach
                        </select>
                        @error('responsible_officer_id')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Note: Jawatan & Gred and No. Telefon of the responsible officer are typically displayed
                         after selecting the officer via the ID, by loading their details.
                         They are not separate input fields on the form itself in this design. --}}

                @endif
            </div> {{-- End BAHAGIAN 2 --}}


            {{-- BAHAGIAN 3 | MAKLUMAT PERALATAN --}}
            <div class="card">
                <h4 class="card-title">BAHAGIAN 3 | MAKLUMAT PERALATAN</h4>

                {{-- Table/List of Equipment Items --}}
                <table class="table">
                    <thead>
                        <tr>
                            <th class="w-1/12">Bil.</th>
                            <th class="w-4/12">Jenis Peralatan*</th>
                            <th class="w-2/12">Kuantiti*</th>
                            <th class="w-4/12">Catatan</th>
                            <th class="w-1/12"></th> {{-- For Remove button --}}
                        </tr>
                    </thead>
                    <tbody>
                        {{-- Loop through the items array in the Livewire component --}}
                        @forelse ($items as $index => $item)
                            <tr wire:key="item-{{ $index }}"> {{-- Add wire:key for better performance --}}
                                <td class="text-gray-800">{{ $loop->iteration }}</td>
                                <td>
                                    {{-- wire:model binds to a specific item in the array --}}
                                    <input type="text" wire:model.live="items.{{ $index }}.equipment_type"
                                        id="item-type-{{ $index }}" class="form-control" required
                                        {{ $loanApplication && $loanApplication->status !== 'draft' ? 'disabled' : '' }}>
                                    @error('items.' . $index . '.equipment_type')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </td>
                                <td>
                                    {{-- wire:model binds to quantity --}}
                                    <input type="number"
                                        wire:model.live="items.{{ $index }}.quantity_requested"
                                        id="item-quantity-{{ $index }}" class="form-control" min="1"
                                        required
                                        {{ $loanApplication && $loanApplication->status !== 'draft' ? 'disabled' : '' }}>
                                    @error('items.' . $index . '.quantity_requested')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </td>
                                <td>
                                    {{-- wire:model binds to notes --}}
                                    <input type="text" wire:model.live="items.{{ $index }}.notes"
                                        id="item-notes-{{ $index }}" class="form-control"
                                        {{ $loanApplication && $loanApplication->status !== 'draft' ? 'disabled' : '' }}>
                                    @error('items.' . $index . '.notes')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </td>
                                <td class="text-center">
                                    {{-- Button to remove this item --}}
                                    @if (($loanApplication && $loanApplication->status === 'draft') || !$loanApplication)
                                        @if (count($items) > 1)
                                            {{-- Prevent removing the last item --}}
                                            <button type="button" wire:click="removeItem({{ $index }})"
                                                class="text-red-600 hover:text-red-800 text-sm font-semibold focus:outline-none">
                                                Buang
                                            </button>
                                        @endif
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-4 px-4 text-center text-gray-500">Tiada item peralatan
                                    ditambah lagi.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                {{-- Button to add a new item row --}}
                @if (($loanApplication && $loanApplication->status === 'draft') || !$loanApplication)
                    <button type="button" wire:click="addItem"
                        class="btn btn-secondary mt-4 text-sm font-semibold px-4 py-2 border rounded-md text-gray-700 hover:bg-gray-100 focus:outline-none">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                        </svg>
                        Tambah Item Peralatan
                    </button>
                @endif

                @error('items')
                    <span class="text-danger block mt-2">{{ $message }}</span> {{-- Error for the entire items array (e.g., min:1) --}}
                @enderror

            </div> {{-- End BAHAGIAN 3 --}}


            {{-- BAHAGIAN 4 | PENGESAHAN PEMOHON (PEGAWAI BERTANGGUNGJAWAB) --}}
            {{-- Only show certification section if the application is a draft or new --}}
            @if (($loanApplication && $loanApplication->status === 'draft') || !$loanApplication)
                <div class="card">
                    <h4 class="card-title">BAHAGIAN 4 | PENGESAHAN PEMOHON (PEGAWAI BERTANGGUNGJAWAB)</h4>

                    <p class="mb-4 text-gray-700">Saya dengan ini mengesahkan dan memperakukan bahawa semua peralatan
                        yang dipinjam adalah untuk kegunaan
                        rasmi dan berada di bawah tanggungjawab dan penyeliaan saya sepanjang tempoh tersebut;</p>

                    <div class="form-group">
                        <div class="flex items-center"> {{-- Use flex for checkbox and label alignment --}}
                            {{-- wire:model binds to the boolean property $applicant_confirmation --}}
                            <input type="checkbox" wire:model.live="applicant_confirmation"
                                id="applicant_confirmation"
                                class="form-check-input h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                                {{-- Tailwind checkbox styles --}} required> {{-- Required to be checked for submission --}}
                            <label class="ml-2 block text-sm text-gray-700" for="applicant_confirmation">
                                {{-- Adjusted label styling --}}
                                Saya faham dan bersetuju dengan syarat-syarat peminjaman peralatan ICT.
                            </label>
                        </div>
                    </div>
                    {{-- Display validation error for confirmation --}}
                    @error('applicant_confirmation')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror

                    {{-- Note: The PDF has a "Tarikh" and "Tandatangan & Cop" below this.
                         The timestamp is handled by the backend ($applicant_confirmation_timestamp).
                         Tandatangan & Cop would typically be handled electronically (e.g., digital signature, or simply implied by the logged-in user submitting). --}}
                </div> {{-- End BAHAGIAN 4 --}}
            @endif


            {{-- Form Submission Button --}}
            {{-- Only show buttons if the application is a draft or a new application --}}
            @if (($loanApplication && $loanApplication->status === 'draft') || !$loanApplication)
                <div class="flex justify-center mt-6">
                    <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                        {{-- wire:loading.attr="disabled" disables the button while the Livewire component is processing --}}
                        <span wire:loading.remove>Hantar Permohonan</span>
                        <span wire:loading class="flex items-center">
                            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg"
                                fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10"
                                    stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l2-2.647z">
                                </path>
                            </svg>
                            Menghantar...
                        </span>
                    </button>
                    {{-- Optional: Save as Draft button if workflow allows --}}
                    {{-- wire:click="saveAsDraft" would call a method to save the current state without submitting --}}
                    {{-- <button type="button" wire:click="saveAsDraft" class="btn btn-secondary ml-4" wire:loading.attr="disabled">
                        <span wire:loading.remove>Simpan Draf</span>
                        <span wire:loading class="flex items-center">
                             <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-gray-800" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l2-2.647z"></path>
                            </svg>
                            Menyimpan...
                        </span>
                    </button> --}}
                </div>
            @endif

        </form>

        {{-- You can add sections to display application status history, approvals, transactions, etc. below the form --}}
        {{-- @if ($loanApplication)
          <h4 class="mt-8 text-xl font-bold text-gray-800">Maklumat Proses Permohonan</h4>
          <p class="mb-4 text-gray-700">Status Semasa: {{ $loanApplication->status }}</p>
            // Display approvals ($loanApplication->approvals)
            // Display transactions ($loanApplication->transactions)
            // Display rejection reason if status is rejected
        @endif --}}


    </div> {{-- End max-w-4xl container --}}

</body>

</html>
