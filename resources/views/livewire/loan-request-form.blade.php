{{-- resources/views/livewire/loan-application-form.blade.php --}}
{{-- This view is a Livewire component view, intended to be included within a main layout file.
     It should NOT contain full HTML document structure (<DOCTYPE>, <html>, <head>, <body>). --}}

{{-- Removed: <!DOCTYPE html> --}}
{{-- Removed: <html lang="en"> --}}
{{-- Removed: <head> --}}
{{-- Removed: <meta charset="UTF-8"> --}}
{{-- Removed: <meta name="viewport" content="width=device-width, initial-scale=1.0"> --}}
{{-- Removed: <title>Borang Permohonan Pinjaman Peralatan ICT</title> --}}
{{-- Removed: <script src="https://cdn.tailwindcss.com"></script> --}}
{{-- The Tailwind CDN script should ideally be in your main layout file. --}}
{{-- Custom styles are included here, which is acceptable for page-specific styling,
     but could be refactored into CSS files managed by your build process. --}}
<style>
    /* Optional: Add custom styles if needed, but prefer Tailwind */
    .form-group {
        margin-bottom: 1rem;
        /* mb-4 in Tailwind */
    }

    .form-control {
        width: 100%;
        /* w-full in Tailwind */
        padding: 0.5rem 0.75rem;
        /* py-2 px-3 in Tailwind */
        border: 1px solid #d1d5db;
        /* border border-gray-300 in Tailwind */
        border-radius: 0.25rem;
        /* rounded in Tailwind */
        box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.075);
        /* shadow-inner in Tailwind */
        /* Add focus styles - these might need to be more specific to avoid conflict with Tailwind */
        outline: none;
        transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
    }

    .form-control:focus {
        border-color: #60a5fa;
        /* border-blue-400 in Tailwind */
        /* Example focus ring using Tailwind classes: focus:ring focus:ring-blue-200 focus:border-blue-500 */
        box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.075), 0 0 0 0.2rem rgba(96, 165, 250, 0.25);
    }

    .form-check-input {
        /* Tailwind classes like h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500 can replace this */
        margin-right: 0.5rem;
        /* mr-2 in Tailwind */
    }

    .text-danger {
        color: #ef4444;
        /* text-red-500 in Tailwind */
        font-size: 0.875rem;
        /* text-sm in Tailwind */
    }

    /* Styles for alert boxes */
    .alert {
        padding: 1rem;
        /* p-4 in Tailwind */
        border-radius: 0.25rem;
        /* rounded in Tailwind */
        margin-bottom: 1rem;
        /* mb-4 in Tailwind */
        border-width: 1px;
        /* border in Tailwind */
    }

    .alert-success {
        background-color: #d1fae5;
        /* bg-green-100 in Tailwind */
        border-color: #a7f3d0;
        /* border-green-200 in Tailwind */
        color: #065f46;
        /* text-green-800 in Tailwind */
    }

    .alert-danger {
        background-color: #fee2e2;
        /* bg-red-100 in Tailwind */
        border-color: #fecaca;
        /* border-red-200 in Tailwind */
        color: #991b1b;
        /* text-red-800 in Tailwind */
    }

    .alert-info {
        background-color: #e0f2f7;
        /* bg-cyan-100 in Tailwind */
        border-color: #bae6fd;
        /* border-cyan-200 in Tailwind */
        color: #0e7490;
        /* text-cyan-800 in Tailwind */
    }

    /* Styles for card containers */
    .card {
        border: 1px solid #d1d5db;
        /* border border-gray-300 in Tailwind */
        border-radius: 0.5rem;
        /* rounded-lg in Tailwind */
        padding: 1.5rem;
        /* p-6 in Tailwind */
        margin-bottom: 1.5rem;
        /* mb-6 in Tailwind */
        background-color: #fff;
        /* bg-white in Tailwind */
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
        /* shadow-md in Tailwind */
    }

    .card-title {
        font-size: 1.25rem;
        /* text-xl in Tailwind */
        font-weight: bold;
        /* font-bold in Tailwind */
        margin-bottom: 1rem;
        /* mb-4 in Tailwind */
        color: #1f2937;
        /* text-gray-800 in Tailwind */
    }

    /* Styles for buttons */
    .btn {
        display: inline-flex;
        /* inline-flex in Tailwind */
        align-items: center;
        /* items-center in Tailwind */
        justify-content: center;
        /* justify-center in Tailwind */
        padding: 0.5rem 1.25rem;
        /* py-2 px-5 in Tailwind */
        border-radius: 0.375rem;
        /* rounded-md in Tailwind */
        font-weight: 600;
        /* font-semibold in Tailwind */
        transition: background-color 0.15s ease-in-out, border-color 0.15s ease-in-out, color 0.15s ease-in-out;
        outline: none;
        cursor: pointer;
    }

    .btn-primary {
        background-color: #3b82f6;
        /* bg-blue-500 in Tailwind */
        color: #fff;
        /* text-white in Tailwind */
        border: 1px solid #3b82f6;
        /* border border-blue-500 in Tailwind */
    }

    .btn-primary:hover {
        background-color: #2563eb;
        /* bg-blue-600 in Tailwind */
        border-color: #2563eb;
        /* border-blue-600 in Tailwind */
    }

    .btn-secondary {
        background-color: #e5e7eb;
        /* bg-gray-200 in Tailwind */
        color: #1f2937;
        /* text-gray-800 in Tailwind */
        border: 1px solid #e5e7eb;
        /* border border-gray-200 in Tailwind */
    }

    .btn-secondary:hover {
        background-color: #d1d5db;
        /* bg-gray-300 in Tailwind */
        border-color: #d1d5db;
        /* border-gray-300 in Tailwind */
    }

    .btn-danger {
        background-color: #ef4444;
        /* bg-red-500 in Tailwind */
        color: #fff;
        /* text-white in Tailwind */
        border: 1px solid #ef4444;
        /* border border-red-500 in Tailwind */
    }

    .btn-danger:hover {
        background-color: #dc2626;
        /* bg-red-600 in Tailwind */
        border-color: #dc2626;
        /* border-red-600 in Tailwind */
    }

    /* Styles for tables */
    .table {
        width: 100%;
        /* w-full in Tailwind */
        border-collapse: collapse;
        /* collapse in Tailwind */
        margin-top: 1rem;
        /* mt-4 in Tailwind */
    }

    .table th,
    .table td {
        padding: 0.75rem;
        /* px-3 py-2 in Tailwind */
        border: 1px solid #e5e7eb;
        /* border border-gray-200 in Tailwind */
        text-align: left;
        /* text-left in Tailwind */
    }

    .table th {
        background-color: #f9fafb;
        /* bg-gray-50 in Tailwind */
        font-weight: bold;
        /* font-bold in Tailwind */
        text-transform: uppercase;
        /* uppercase in Tailwind */
        font-size: 0.75rem;
        /* text-xs in Tailwind */
        color: #4b5563;
        /* text-gray-600 in Tailwind */
    }

    .table tbody tr:nth-child(odd) {
        /* bg-gray-50 in Tailwind can achieve similar alternate row color */
        background-color: #f9fafb;
    }

    .table tbody tr:hover {
        background-color: #f3f4f6;
        /* bg-gray-100 in Tailwind */
    }
</style>
{{-- Removed: </head> --}}
{{-- Removed: <body class="bg-gray-100 p-6"> --}}
{{-- Added a root div with background and padding classes, assuming this content will be placed within a layout --}}
<div class="bg-gray-100 p-6">

    {{-- Main content container with max-width, centering, background, padding, rounded corners, and shadow --}}
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

        {{-- Display application reference ID and status if editing an existing application --}}
        {{-- $applicationId would be a public property in the Livewire component, null for new forms --}}
        @if ($applicationId)
            <p class="mb-2 text-gray-700">Nombor Rujukan Permohonan: #{{ $applicationId }}</p>
            {{-- Assuming $loanApplication is a public property loaded when editing --}}
            <p class="mb-4 text-gray-700">Status Semasa: {{ $loanApplication->status ?? 'N/A' }}</p>
            {{-- Display status, provide fallback --}}
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
            <div class="card"> {{-- Styling section with a card --}}
                <h4 class="card-title">BAHAGIAN 1 | MAKLUMAT PEMOHON</h4>
                {{-- Display basic user details, assumed to be pre-filled and not editable on this form.
                       Ensure your User model has these attributes/relationships (position, grade, department). --}}
                {{-- Using Auth::user() directly as these are read-only fields populated from authenticated user --}}
                <div class="form-group">
                    <label class="block text-gray-700 text-sm font-bold mb-1">Nama Penuh:</label> {{-- Label styling --}}
                    <p class="text-gray-800">{{ Auth::user()->name ?? 'N/A' }}</p> {{-- Display user name, provide fallback --}}
                </div>
                <div class="form-group">
                    <label class="block text-gray-700 text-sm font-bold mb-1">Jawatan & Gred:</label>
                    {{-- Label styling --}}
                    {{-- Assuming relationships exist and have 'name' attribute, provide fallbacks --}}
                    <p class="text-gray-800">{{ Auth::user()->position->name ?? 'N/A' }} &
                        {{ Auth::user()->grade->name ?? 'N/A' }}</p> {{-- Display position and grade --}}
                </div>
                <div class="form-group">
                    <label class="block text-gray-700 text-sm font-bold mb-1">Bahagian/Unit:</label>
                    {{-- Label styling --}}
                    <p class="text-gray-800">{{ Auth::user()->department->name ?? 'N/A' }}</p> {{-- Display department, provide fallback --}}
                </div>
                <div class="form-group">
                    <label class="block text-gray-700 text-sm font-bold mb-1">No. Telefon:</label>
                    {{-- Label styling --}}
                    <p class="text-gray-800">{{ Auth::user()->phone_number ?? 'N/A' }}</p> {{-- Using phone_number from User model, provide fallback --}}
                </div>

                {{-- Form fields for Loan Details --}}
                <div class="form-group">
                    <label for="purpose" class="block text-gray-700 text-sm font-bold mb-2">Tujuan Permohonan*:</label>
                    {{-- Label styling, required --}}
                    {{-- wire:model binds textarea value to $purpose property --}}
                    {{-- Disable the field if the application is not a draft --}}
                    <textarea wire:model="purpose" id="purpose" class="form-control" rows="3" required
                        {{ $loanApplication && $loanApplication->status !== 'draft' ? 'disabled' : '' }}></textarea>
                    {{-- Display validation errors using standard Blade @if --}}
                    @if ($errors->has('purpose'))
                        <span class="text-danger">{{ $errors->first('purpose') }}</span>
                    @endif
                </div>

                <div class="form-group">
                    <label for="location" class="block text-gray-700 text-sm font-bold mb-2">Lokasi Penggunaan
                        Peralatan*:</label> {{-- Label styling, required --}}
                    {{-- wire:model binds input value to $location property --}}
                    {{-- Disable if not draft --}}
                    <input type="text" wire:model="location" id="location" class="form-control" required
                        {{ $loanApplication && $loanApplication->status !== 'draft' ? 'disabled' : '' }}>
                    {{-- Display validation errors using standard Blade @if --}}
                    @if ($errors->has('location'))
                        <span class="text-danger">{{ $errors->first('location') }}</span>
                    @endif
                </div>

                <div class="form-group">
                    <label for="loan_start_date" class="block text-gray-700 text-sm font-bold mb-2">Tarikh
                        Pinjaman*:</label> {{-- Label styling, required --}}
                    {{-- wire:model binds input value to $loan_start_date property --}}
                    {{-- Disable if not draft --}}
                    <input type="date" wire:model="loan_start_date" id="loan_start_date" class="form-control"
                        required {{ $loanApplication && $loanApplication->status !== 'draft' ? 'disabled' : '' }}>
                    {{-- Display validation errors using standard Blade @if --}}
                    @if ($errors->has('loan_start_date'))
                        <span class="text-danger">{{ $errors->first('loan_start_date') }}</span>
                    @endif
                </div>

                <div class="form-group">
                    <label for="loan_end_date" class="block text-gray-700 text-sm font-bold mb-2">Tarikh Dijangka
                        Pulang*:</label> {{-- Label styling, required --}}
                    {{-- wire:model binds input value to $loan_end_date property --}}
                    {{-- Disable if not draft --}}
                    <input type="date" wire:model="loan_end_date" id="loan_end_date" class="form-control" required
                        {{ $loanApplication && $loanApplication->status !== 'draft' ? 'disabled' : '' }}>
                    {{-- Display validation errors using standard Blade @if --}}
                    @if ($errors->has('loan_end_date'))
                        <span class="text-danger">{{ $errors->first('loan_end_date') }}</span>
                    @endif
                </div>
            </div> {{-- End BAHAGIAN 1 card --}}


            {{-- BAHAGIAN 2 | MAKLUMAT PEGAWAI BERTANGGUNGJAWAB --}}
            <div class="card"> {{-- Styling section with a card --}}
                <h4 class="card-title">BAHAGIAN 2 | MAKLUMAT PEGAWAI BERTANGGUNGJAWAB</h4>
                <div class="form-group">
                    <div class="flex items-center"> {{-- Use flex for checkbox and label alignment --}}
                        {{-- wire:model.live binds the checkbox state to the boolean property $is_applicant_responsible. .live updates property immediately. --}}
                        {{-- Use value="1" for checked state if the property is a boolean/integer --}}
                        {{-- Apply Tailwind checkbox styles and disable if not draft --}}
                        <input type="checkbox" wire:model.live="is_applicant_responsible" id="is_applicant_responsible"
                            value="1"
                            class="form-check-input h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
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
                        Bertanggungjawab bukan Pemohon.</p> {{-- Informational text --}}

                    <div class="form-group">
                        <label for="responsible_officer_id" class="block text-gray-700 text-sm font-bold mb-2">Nama
                            Penuh Pegawai Bertanggungjawab*:</label> {{-- Label styling, required --}}
                        {{-- wire:model binds the select value to the public property $responsible_officer_id --}}
                        {{-- This should ideally be a select/search input linked to Users data --}}
                        {{-- Assuming you have a public property $responsibleOfficers containing available users --}}
                        {{-- Disable if not draft --}}
                        <select wire:model="responsible_officer_id" id="responsible_officer_id" class="form-control"
                            required {{ $loanApplication && $loanApplication->status !== 'draft' ? 'disabled' : '' }}>
                            <option value="">- Pilih Pegawai -</option> {{-- Default placeholder option --}}
                            {{-- Loop through the collection of responsible officers (assumes $responsibleOfficers is available) --}}
                            @foreach ($responsibleOfficers ?? [] as $officer)
                                {{-- Provide fallback for loop --}}
                                <option value="{{ $officer->id }}">{{ $officer->name ?? 'N/A' }}
                                    ({{ $officer->position->name ?? 'N/A' }} - {{ $officer->grade->name ?? 'N/A' }})
                                </option> {{-- Display officer name, position, and grade, provide fallbacks --}}
                            @endforeach
                        </select>
                        {{-- Display validation errors for this field using standard Blade @if --}}
                        @if ($errors->has('responsible_officer_id'))
                            <span class="text-danger">{{ $errors->first('responsible_officer_id') }}</span>
                        @endif
                    </div>

                    {{-- Note: Jawatan & Gred and No. Telefon of the responsible officer are typically displayed
                           after selecting the officer via the ID, by loading their details in the component and showing them in the view.
                           They are not separate input fields on the form itself in this design. --}}

                @endif {{-- End conditional display for responsible officer fields --}}
            </div> {{-- End BAHAGIAN 2 card --}}


            {{-- BAHAGIAN 3 | MAKLUMAT PERALATAN --}}
            <div class="card"> {{-- Styling section with a card --}}
                <h4 class="card-title">BAHAGIAN 3 | MAKLUMAT PERALATAN</h4>

                {{-- Table/List of Equipment Items --}}
                <table class="table"> {{-- Styling with custom table classes --}}
                    <thead>
                        <tr>
                            <th class="w-1/12">Bil.</th> {{-- Column for item number --}}
                            <th class="w-4/12">Jenis Peralatan*</th> {{-- Column for equipment type, required --}}
                            <th class="w-2/12">Kuantiti*</th> {{-- Column for quantity, required --}}
                            <th class="w-4/12">Catatan</th> {{-- Column for notes --}}
                            <th class="w-1/12"></th> {{-- Column for Remove button --}}
                        </tr>
                    </thead>
                    <tbody>
                        {{-- Loop through the items array in the Livewire component --}}
                        {{-- Assuming $items is a public property in the Livewire component, an array of item data --}}
                        @forelse ($items ?? [] as $index => $item)
                            {{-- Provide fallback for loop --}}
                            <tr wire:key="item-{{ $index }}"> {{-- Add wire:key for better performance when adding/removing items --}}
                                <td class="text-gray-800">{{ $loop->iteration }}</td> {{-- Display item number --}}
                                <td>
                                    {{-- wire:model binds input value to a specific item in the array --}}
                                    {{-- Use wire:model.live for immediate updates on change --}}
                                    {{-- Disable if not draft --}}
                                    <input type="text" wire:model.live="items.{{ $index }}.equipment_type"
                                        id="item-type-{{ $index }}" class="form-control" required
                                        {{ $loanApplication && $loanApplication->status !== 'draft' ? 'disabled' : '' }}>
                                    {{-- Display validation errors using standard Blade @if --}}
                                    @if ($errors->has('items.' . $index . '.equipment_type'))
                                        <span
                                            class="text-danger">{{ $errors->first('items.' . $index . '.equipment_type') }}</span>
                                    @endif
                                </td>
                                <td>
                                    {{-- wire:model binds input value to quantity --}}
                                    {{-- Use wire:model.live for immediate updates on change --}}
                                    {{-- Disable if not draft --}}
                                    <input type="number"
                                        wire:model.live="items.{{ $index }}.quantity_requested"
                                        id="item-quantity-{{ $index }}" class="form-control" min="1"
                                        required
                                        {{ $loanApplication && $loanApplication->status !== 'draft' ? 'disabled' : '' }}>
                                    {{-- Display validation errors using standard Blade @if --}}
                                    @if ($errors->has('items.' . $index . '.quantity_requested'))
                                        <span
                                            class="text-danger">{{ $errors->first('items.' . $index . '.quantity_requested') }}</span>
                                    @endif
                                </td>
                                <td>
                                    {{-- wire:model binds input value to notes --}}
                                    {{-- Use wire:model.live for immediate updates on change --}}
                                    {{-- Disable if not draft --}}
                                    <input type="text" wire:model.live="items.{{ $index }}.notes"
                                        id="item-notes-{{ $index }}" class="form-control"
                                        {{ $loanApplication && $loanApplication->status !== 'draft' ? 'disabled' : '' }}>
                                    {{-- Display validation errors using standard Blade @if --}}
                                    @if ($errors->has('items.' . $index . '.notes'))
                                        <span
                                            class="text-danger">{{ $errors->first('items.' . $index . '.notes') }}</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    {{-- Button to remove this item --}}
                                    {{-- Only show button if application is new or draft --}}
                                    @if (($loanApplication && $loanApplication->status === 'draft') || !$loanApplication)
                                        {{-- Prevent removing the last item --}}
                                        @if (count($items ?? []) > 1)
                                            {{-- Provide fallback for count --}}
                                            <button type="button" wire:click="removeItem({{ $index }})"
                                                class="text-red-600 hover:text-red-800 text-sm font-semibold focus:outline-none">
                                                Buang
                                            </button> {{-- 'Remove' button --}}
                                        @endif
                                    @endif
                                </td>
                            </tr>
                        @empty
                            {{-- Message displayed when no items are added --}}
                            <tr>
                                <td colspan="5" class="py-4 px-4 text-center text-gray-500">Tiada item peralatan
                                    ditambah lagi.</td> {{-- Empty state message --}}
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                {{-- Button to add a new item row --}}
                {{-- Only show button if application is new or draft --}}
                @if (($loanApplication && $loanApplication->status === 'draft') || !$loanApplication)
                    <button type="button" wire:click="addItem"
                        class="btn btn-secondary mt-4 text-sm font-semibold px-4 py-2 border rounded-md text-gray-700 hover:bg-gray-100 focus:outline-none">
                        {{-- Add icon --}}
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                        </svg>
                        Tambah Item Peralatan {{-- 'Add Equipment Item' button text --}}
                    </button>
                @endif

                {{-- Display validation errors for the entire items array using standard Blade @if --}}
                {{-- This is useful for errors related to the minimum number of items, etc. --}}
                @if ($errors->has('items'))
                    <span class="text-danger block mt-2">{{ $errors->first('items') }}</span> {{-- Error for the entire items array (e.g., min:1) --}}
                @endif

            </div> {{-- End BAHAGIAN 3 card --}}


            {{-- BAHAGIAN 4 | PENGESAHAN PEMOHON (PEGAWAI BERTANGGUNGJAWAB) --}}
            {{-- Only show certification section if the application is a draft or new --}}
            @if (($loanApplication && $loanApplication->status === 'draft') || !$loanApplication)
                <div class="card"> {{-- Styling section with a card --}}
                    <h4 class="card-title">BAHAGIAN 4 | PENGESAHAN PEMOHON (PEGAWAI BERTANGGUNGJAWAB)</h4>

                    <p class="mb-4 text-gray-700">Saya dengan ini mengesahkan dan memperakukan bahawa semua peralatan
                        yang dipinjam adalah untuk kegunaan
                        rasmi dan berada di bawah tanggungjawab dan penyeliaan saya sepanjang tempoh tersebut;</p>
                    {{-- Certification text --}}

                    <div class="form-group">
                        <div class="flex items-center"> {{-- Use flex for checkbox and label alignment --}}
                            {{-- wire:model.live binds the checkbox state to the boolean property $applicant_confirmation. .live updates property immediately. --}}
                            {{-- Use value="1" for checked state --}}
                            {{-- Apply Tailwind checkbox styles and make required --}}
                            <input type="checkbox" wire:model.live="applicant_confirmation"
                                id="applicant_confirmation"
                                class="form-check-input h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                                required>
                            <label class="ml-2 block text-sm text-gray-700" for="applicant_confirmation">
                                {{-- Adjusted label styling --}}
                                Saya faham dan bersetuju dengan syarat-syarat peminjaman peralatan ICT.
                                {{-- Certification statement --}}
                            </label>
                        </div>
                    </div>
                    {{-- Display validation error for confirmation using standard Blade @if --}}
                    @if ($errors->has('applicant_confirmation'))
                        <span class="text-danger">{{ $errors->first('applicant_confirmation') }}</span>
                    @endif
                </div> {{-- End BAHAGIAN 4 card --}}
            @endif


            {{-- Form Submission Button --}}
            {{-- Only show buttons if the application is a draft or a new application --}}
            @if (($loanApplication && $loanApplication->status === 'draft') || !$loanApplication)
                <div class="flex justify-center mt-6"> {{-- Center the button --}}
                    {{-- Submit button --}}
                    <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                        {{-- wire:loading.attr="disabled" disables the button while the Livewire component is processing --}}
                        <span wire:loading.remove>Hantar Permohonan</span> {{-- Text when not loading --}}
                        {{-- Display loading text and spinner while processing --}}
                        <span wire:loading class="flex items-center">
                            {{-- SVG spinner icon with animation --}}
                            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg"
                                fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10"
                                    stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l2-2.647z">
                                </path>
                            </svg>
                            Menghantar... {{-- Loading text --}}
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
                </div> {{-- End button container --}}
            @endif {{-- End conditional button display --}}

        </form> {{-- End form --}}

        {{-- You can add sections to display application status history, approvals, transactions, etc. below the form --}}
        {{-- @if ($loanApplication) // Show this section only if an application is loaded (editing/viewing)
              <h4 class="mt-8 text-xl font-bold text-gray-800">Maklumat Proses Permohonan</h4> // Section title
              <p class="mb-4 text-gray-700">Status Semasa: {{ $loanApplication->status }}</p> // Display current status
              // Display approvals ($loanApplication->approvals relation)
              // Display transactions ($loanApplication->transactions relation)
              // Display rejection reason if status is rejected
           @endif --}}


    </div> {{-- End max-w-4xl container --}}

    {{-- Removed: </body> --}}
    {{-- Removed: </html> --}}
</div> {{-- Livewire component root element --}}
