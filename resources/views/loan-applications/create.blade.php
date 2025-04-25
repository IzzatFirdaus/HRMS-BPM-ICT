<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Permohonan Pinjaman Peralatan ICT</title>
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

        .btn-success {
            background-color: #48bb78;
            /* green-500 */
            color: #fff;
            border: 1px solid #48bb78;
        }

        .btn-success:hover {
            background-color: #38a169;
            /* green-600 */
            border-color: #38a169;
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

    {{-- Extend a layout if you have one, otherwise include basic HTML structure --}}
    @extends('layouts.app')

    @section('content')
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 py-6"> {{-- Converted container to Tailwind, increased width for table --}}

            <h2 class="text-2xl font-bold mb-6 text-gray-800">Borang Permohonan Pinjaman Peralatan ICT</h2>
            {{-- Converted h2 --}}

            {{-- Display validation errors if any --}}
            @if ($errors->any())
                <div class="alert alert-danger">
                    <p class="font-semibold">Ralat Pengesahan:</p>
                    <ul class="mt-1 list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Display success or error messages from session --}}
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


            {{-- Loan Application Form --}}
            <form action="{{ route('loan-applications.store') }}" method="POST">
                @csrf {{-- CSRF token for security --}}

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
                        <textarea name="purpose" id="purpose" class="form-control" rows="3" required>{{ old('purpose') }}</textarea>
                        @error('purpose')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="location" class="block text-gray-700 text-sm font-bold mb-2">Lokasi Penggunaan
                            Peralatan*:</label>
                        <input type="text" name="location" id="location" class="form-control" required
                            value="{{ old('location') }}">
                        @error('location')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="loan_start_date" class="block text-gray-700 text-sm font-bold mb-2">Tarikh
                            Pinjaman*:</label>
                        <input type="date" name="loan_start_date" id="loan_start_date" class="form-control" required
                            value="{{ old('loan_start_date') }}">
                        @error('loan_start_date')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="loan_end_date" class="block text-gray-700 text-sm font-bold mb-2">Tarikh Dijangka
                            Pulang*:</label>
                        <input type="date" name="loan_end_date" id="loan_end_date" class="form-control" required
                            value="{{ old('loan_end_date') }}">
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
                            {{-- Note: Toggling the select input based on this checkbox requires JavaScript in a standard HTML form.
                             Backend validation should handle if responsible_officer_id is required when the checkbox is NOT checked. --}}
                            <input type="checkbox" name="is_applicant_responsible" id="is_applicant_responsible"
                                value="1"
                                class="form-check-input h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                                {{ old('is_applicant_responsible', true) ? 'checked' : '' }}> {{-- Default checked as per PDF --}}
                            <label class="ml-2 block text-sm text-gray-700" for="is_applicant_responsible">
                                {{-- Adjusted label styling --}}
                                Sila tandakan jika Pemohon adalah Pegawai Bertanggungjawab.
                            </label>
                        </div>
                        @error('is_applicant_responsible')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Responsible Officer fields - always included in HTML, conditional logic handled by JS/backend --}}
                    <p class="text-gray-600 italic mb-4 text-sm">Bahagian ini hanya perlu diisi jika Pegawai
                        Bertanggungjawab bukan Pemohon.</p>

                    <div class="form-group">
                        <label for="responsible_officer_id" class="block text-gray-700 text-sm font-bold mb-2">Nama Penuh
                            Pegawai Bertanggungjawab*:</label>
                        {{-- This should ideally be a select/search input linked to Users --}}
                        {{-- Assuming you have a collection of potential responsible officers named $responsibleOfficers passed from the controller --}}
                        <select name="responsible_officer_id" id="responsible_officer_id" class="form-control">
                            {{-- Not required in HTML, backend validation handles conditional requirement --}}
                            <option value="">- Pilih Pegawai -</option>
                            {{-- Loop through the collection of responsible officers --}}
                            @foreach ($responsibleOfficers as $officer)
                                <option value="{{ $officer->id }}"
                                    {{ old('responsible_officer_id') == $officer->id ? 'selected' : '' }}>
                                    {{ $officer->name }} ({{ $officer->position->name ?? 'N/A' }} -
                                    {{ $officer->grade->name ?? 'N/A' }})
                                </option>
                            @endforeach
                        </select>
                        @error('responsible_officer_id')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Note: Jawatan & Gred and No. Telefon of the responsible officer would typically be displayed
                     dynamically via JavaScript based on the selected officer. --}}

                </div> {{-- End BAHAGIAN 2 --}}


                {{-- BAHAGIAN 3 | MAKLUMAT PERALATAN --}}
                <div class="card">
                    <h4 class="card-title">BAHAGIAN 3 | MAKLUMAT PERALATAN</h4>
                    <p class="text-gray-600 italic mb-4 text-sm">Sila senaraikan peralatan ICT yang diperlukan.</p>

                    {{-- Table/List of Equipment Items --}}
                    {{-- Note: Dynamic addition/removal of rows requires JavaScript in a standard HTML form.
                      Here, we provide a few default rows and use array naming for inputs.
                      Backend validation should ensure at least one item is submitted. --}}
                    <div id="equipment-items-container">
                        {{-- Example: Render a few initial rows --}}
                        @php
                            $initialItems = old('items', [
                                ['equipment_type' => '', 'quantity_requested' => '', 'notes' => ''],
                            ]);
                            // Ensure at least one empty row if old('items') is empty
                            if (empty($initialItems)) {
                                $initialItems = [['equipment_type' => '', 'quantity_requested' => '', 'notes' => '']];
                            }
                        @endphp

                        @foreach ($initialItems as $index => $item)
                            <div class="flex flex-wrap -mx-3 mb-4 border-b pb-4" id="item-row-{{ $index }}">
                                <div class="w-full md:w-4/12 px-3 mb-3 md:mb-0">
                                    <label for="equipment_type_{{ $index }}"
                                        class="block text-gray-700 text-sm font-bold mb-2">Jenis Peralatan*:</label>
                                    <input type="text" name="items[{{ $index }}][equipment_type]"
                                        id="equipment_type_{{ $index }}" class="form-control" required
                                        value="{{ $item['equipment_type'] ?? '' }}">
                                    @error('items.' . $index . '.equipment_type')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="w-full md:w-2/12 px-3 mb-3 md:mb-0">
                                    <label for="quantity_requested_{{ $index }}"
                                        class="block text-gray-700 text-sm font-bold mb-2">Kuantiti*:</label>
                                    <input type="number" name="items[{{ $index }}][quantity_requested]"
                                        id="quantity_requested_{{ $index }}" class="form-control" min="1"
                                        required value="{{ $item['quantity_requested'] ?? '' }}">
                                    @error('items.' . $index . '.quantity_requested')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="w-full md:w-4/12 px-3 mb-3 md:mb-0">
                                    <label for="item_notes_{{ $index }}"
                                        class="block text-gray-700 text-sm font-bold mb-2">Catatan:</label>
                                    <input type="text" name="items[{{ $index }}][notes]"
                                        id="item_notes_{{ $index }}" class="form-control"
                                        value="{{ $item['notes'] ?? '' }}">
                                    @error('items.' . $index . '.notes')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="w-full md:w-2/12 px-3 flex items-end">
                                    {{-- Button to remove this item row (requires JS) --}}
                                    @if ($loop->index > 0 || count($initialItems) > 1)
                                        {{-- Allow removing if not the first row or if there's more than one --}}
                                        <button type="button" onclick="removeItemRow({{ $index }})"
                                            class="btn bg-red-500 hover:bg-red-600 text-white text-sm font-semibold px-3 py-2 rounded-md focus:outline-none">
                                            Buang
                                        </button>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div> {{-- End equipment-items-container --}}

                    {{-- Button to add a new equipment item row (requires JS) --}}
                    <button type="button" id="add-item-button"
                        class="btn btn-secondary mt-4 text-sm font-semibold px-4 py-2 border rounded-md text-gray-700 hover:bg-gray-100 focus:outline-none">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                        </svg>
                        Tambah Item Peralatan
                    </button>

                    @error('items')
                        <span class="text-danger block mt-2">{{ $message }}</span> {{-- Error for the entire items array (e.g., min:1) --}}
                    @enderror

                    {{-- Basic JavaScript for adding/removing rows --}}
                    <script>
                        let itemIndex = {{ count($initialItems) > 0 ? count($initialItems) : 0 }}; // Start index after initial items

                        document.getElementById('add-item-button').onclick = function() {
                            const container = document.getElementById('equipment-items-container');
                            const newRow = document.createElement('div');
                            newRow.classList.add('flex', 'flex-wrap', '-mx-3', 'mb-4', 'border-b', 'pb-4');
                            newRow.id = 'item-row-' + itemIndex;
                            newRow.innerHTML = `
                             <div class="w-full md:w-4/12 px-3 mb-3 md:mb-0">
                                 <label for="equipment_type_${itemIndex}" class="block text-gray-700 text-sm font-bold mb-2">Jenis Peralatan*:</label>
                                 <input type="text" name="items[${itemIndex}][equipment_type]" id="equipment_type_${itemIndex}" class="form-control" required>
                             </div>
                             <div class="w-full md:w-2/12 px-3 mb-3 md:mb-0">
                                 <label for="quantity_requested_${itemIndex}" class="block text-gray-700 text-sm font-bold mb-2">Kuantiti*:</label>
                                 <input type="number" name="items[${itemIndex}][quantity_requested]" id="quantity_requested_${itemIndex}" class="form-control" min="1" required value="1">
                             </div>
                             <div class="w-full md:w-4/12 px-3 mb-3 md:mb-0">
                                 <label for="item_notes_${itemIndex}" class="block text-gray-700 text-sm font-bold mb-2">Catatan:</label>
                                 <input type="text" name="items[${itemIndex}][notes]" id="item_notes_${itemIndex}" class="form-control">
                             </div>
                             <div class="w-full md:w-2/12 px-3 flex items-end">
                                 <button type="button" onclick="removeItemRow(${itemIndex})" class="btn bg-red-500 hover:bg-red-600 text-white text-sm font-semibold px-3 py-2 rounded-md focus:outline-none">
                                     Buang
                                 </button>
                             </div>
                         `;
                            container.appendChild(newRow);
                            itemIndex++;
                            updateRemoveButtons(); // Update button visibility after adding
                        };

                        function removeItemRow(index) {
                            const row = document.getElementById('item-row-' + index);
                            if (row) {
                                // Count current rows excluding the one to be removed
                                const currentRows = container.querySelectorAll('.flex.flex-wrap.-mx-3.mb-4.border-b.pb-4').length;
                                if (currentRows > 1) {
                                    row.remove();
                                    updateRemoveButtons(); // Update button visibility after removing
                                } else {
                                    // Optional: Show a message or disable the remove button if only one row remains
                                    alert('Sekurang-kurangnya satu item peralatan diperlukan.');
                                }
                            }
                        }

                        // Function to update remove button visibility (hide if only one row)
                        function updateRemoveButtons() {
                            const container = document.getElementById('equipment-items-container');
                            const rows = container.querySelectorAll('.flex.flex-wrap.-mx-3.mb-4.border-b.pb-4');
                            rows.forEach(row => {
                                const removeButton = row.querySelector('button');
                                if (removeButton) {
                                    if (rows.length <= 1) {
                                        removeButton.style.display = 'none'; // Hide the remove button
                                    } else {
                                        removeButton.style.display = 'inline-flex'; // Show the remove button
                                    }
                                }
                            });
                        }

                        // Initial call to set button visibility on page load
                        window.onload = updateRemoveButtons;
                    </script>

                </div> {{-- End BAHAGIAN 3 --}}


                {{-- BAHAGIAN 4 | PENGESAHAN PEMOHON (PEGAWAI BERTANGGUNGJAWAB) --}}
                <div class="card">
                    <h4 class="card-title">BAHAGIAN 4 | PENGESAHAN PEMOHON (PEGAWAI BERTANGGUNGJAWAB)</h4>

                    <p class="mb-4 text-gray-700">Saya dengan ini mengesahkan dan memperakukan bahawa semua peralatan yang
                        dipinjam adalah untuk kegunaan
                        rasmi dan berada di bawah tanggungjawab dan penyeliaan saya sepanjang tempoh tersebut;</p>

                    <div class="form-group">
                        <div class="flex items-center"> {{-- Use flex for checkbox and label alignment --}}
                            {{-- The name attribute should match the backend expectation, likely 'applicant_confirmation_timestamp' or similar --}}
                            <input type="checkbox" name="applicant_confirmation" id="applicant_confirmation"
                                value="1"
                                class="form-check-input h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                                required {{ old('applicant_confirmation') ? 'checked' : '' }}> {{-- Repopulate checkbox state --}}
                            <label class="ml-2 block text-sm text-gray-700" for="applicant_confirmation">
                                {{-- Adjusted label styling --}}
                                Saya faham dan bersetuju dengan syarat-syarat peminjaman peralatan ICT.
                            </label>
                        </div>
                        @error('applicant_confirmation')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Note: The PDF has "Tarikh" and "Tandatangan & Cop" below this.
                     The timestamp is handled by the backend.
                     Tandatangan & Cop would typically be handled electronically (e.g., implied by the logged-in user submitting). --}}
                </div> {{-- End BAHAGIAN 4 --}}


                {{-- Form Submission Button --}}
                <div class="flex justify-center mt-6">
                    <button type="submit" class="btn btn-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                        Hantar Permohonan
                    </button>
                </div>

            </form>

        </div> {{-- End max-w-4xl container --}}
    @endsection

</body>

</html>
