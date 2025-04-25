<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengeluaran Peralatan Pinjaman ICT</title>
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

        select[multiple].form-control {
            height: auto;
            /* Allow multiple select to grow */
            min-height: 100px;
            /* Minimum height for usability */
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
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 py-8"> {{-- Container for the content --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6"> {{-- Card-like container --}}

                <h2 class="text-2xl font-bold mb-6 text-gray-800">Rekod Pengeluaran Peralatan untuk Permohonan Pinjaman
                    #{{ $loanApplication->id }}</h2> {{-- Title with application ID --}}

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

                {{-- Loan Application Details for Context --}}
                <div class="card mb-6">
                    <h3 class="card-title">Butiran Permohonan Pinjaman</h3>
                    <p class="mb-2"><span class="font-semibold">Pemohon:</span>
                        {{ $loanApplication->user->name ?? 'N/A' }}</p> {{-- Assuming user relationship --}}
                    <p class="mb-2"><span class="font-semibold">Tujuan Permohonan:</span>
                        {{ $loanApplication->purpose ?? 'N/A' }}</p>
                    <p class="mb-2"><span class="font-semibold">Lokasi Penggunaan:</span>
                        {{ $loanApplication->location ?? 'N/A' }}</p>
                    <p class="mb-2"><span class="font-semibold">Tarikh Pinjaman:</span>
                        {{ $loanApplication->loan_start_date?->format('Y-m-d') ?? 'N/A' }}</p>
                    <p class="mb-2"><span class="font-semibold">Tarikh Dijangka Pulang:</span>
                        {{ $loanApplication->loan_end_date?->format('Y-m-d') ?? 'N/A' }}</p>

                    {{-- Display Requested Items --}}
                    @if ($loanApplication->items->isNotEmpty())
                        <h4 class="text-lg font-semibold mt-4 mb-2 text-gray-700">Item Peralatan Dimohon:</h4>
                        <div class="overflow-x-auto shadow-sm rounded-md border border-gray-200">
                            <table class="min-w-full divide-y divide-gray-200 table">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">
                                            Bil.</th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">
                                            Jenis Peralatan</th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">
                                            Kuantiti Dimohon</th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">
                                            Kuantiti Diluluskan</th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">
                                            Catatan</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    {{-- Loop through the loan application items relationship --}}
                                    @foreach ($loanApplication->items as $item)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 border-b">
                                                {{ $loop->iteration }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 border-b">
                                                {{ $item->equipment_type ?? 'N/A' }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 border-b">
                                                {{ $item->quantity_requested ?? 'N/A' }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 border-b">
                                                {{ $item->quantity_approved ?? 'N/A' }}</td>
                                            <td class="px-6 py-4 text-sm text-gray-900 border-b">{{ $item->notes ?? '-' }}
                                            </td> {{-- Removed whitespace-nowrap --}}
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div> {{-- End overflow-x-auto --}}
                    @else
                        <p class="text-gray-600 italic">Tiada item peralatan dimohon untuk permohonan ini.</p>
                    @endif
                </div> {{-- End Loan Details Card --}}


                {{-- Issuance Checklist Form --}}
                {{-- Assuming the form submits to a 'storeIssue' route for LoanTransaction --}}
                <form action="{{ route('loan-transactions.storeIssue', $loanApplication) }}" method="POST">
                    @csrf {{-- CSRF token for security --}}

                    <div class="card">
                        <h3 class="card-title">Rekod Pengeluaran Peralatan</h3>

                        {{-- Select Equipment Asset(s) to Issue --}}
                        <div class="form-group">
                            <label for="equipment_ids" class="block text-gray-700 text-sm font-bold mb-2">Pilih Peralatan
                                untuk Dikeluarkan*:</label>
                            {{-- Multiple select to choose one or more available equipment assets --}}
                            {{-- Assuming $availableEquipment is passed from the controller --}}
                            <select name="equipment_ids[]" id="equipment_ids" class="form-control" multiple required>
                                <option value="">- Pilih Peralatan -</option>
                                {{-- Loop through available equipment assets --}}
                                @foreach ($availableEquipment as $equipment)
                                    <option value="{{ $equipment->id }}"
                                        {{ in_array($equipment->id, old('equipment_ids', [])) ? 'selected' : '' }}>
                                        {{ $equipment->brand }} {{ $equipment->model }} (Tag:
                                        {{ $equipment->tag_id ?? 'N/A' }})
                                    </option>
                                @endforeach
                            </select>
                            @error('equipment_ids')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                            @error('equipment_ids.*')
                                {{-- For individual items in the array --}}
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- Accessories Checklist --}}
                        <div class="form-group">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Senarai Semak Aksesori
                                Dikeluarkan:</label>
                            <p class="text-gray-600 text-sm mb-2">Sila tandakan aksesori yang disertakan bersama peralatan
                                yang dipilih.</p>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4"> {{-- Use grid for layout --}}
                                {{-- Assuming $allAccessoriesList is passed from the controller --}}
                                @foreach ($allAccessoriesList as $accessory)
                                    <div class="flex items-center"> {{-- Use flex for checkbox and label alignment --}}
                                        <input type="checkbox" name="accessories[]" value="{{ $accessory }}"
                                            id="accessory-{{ Str::slug($accessory) }}"
                                            class="form-check-input h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                                            {{ in_array($accessory, old('accessories', [])) ? 'checked' : '' }}>
                                        {{-- Repopulate --}}
                                        <label class="ml-2 block text-sm text-gray-700"
                                            for="accessory-{{ Str::slug($accessory) }}">{{ $accessory }}</label>
                                    </div>
                                @endforeach
                            </div>
                            @error('accessories')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                            @error('accessories.*')
                                {{-- For individual items in the array --}}
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- Notes --}}
                        <div class="form-group">
                            <label for="issue_notes" class="block text-gray-700 text-sm font-bold mb-2">Catatan
                                Pengeluaran:</label>
                            <textarea name="issue_notes" id="issue_notes" class="form-control" rows="3">{{ old('issue_notes') }}</textarea>
                            @error('issue_notes')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- Officer (BPM Staff) - Display pre-filled with current user --}}
                        <div class="form-group">
                            <label class="block text-gray-700 text-sm font-bold mb-1">Diproses Oleh:</label>
                            <p class="text-gray-800">{{ Auth::user()->name ?? 'N/A' }}</p> {{-- Assuming 'name' for user --}}
                            {{-- Hidden input to pass the officer ID --}}
                            <input type="hidden" name="issuing_officer_id" value="{{ Auth::id() }}">
                            @error('issuing_officer_id')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                    </div> {{-- End card --}}


                    {{-- Submit Button --}}
                    <div class="flex justify-center mt-6">
                        <button type="submit" class="btn btn-primary">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                            Rekod Pengeluaran Peralatan
                        </button>
                    </div>

                </form>

                {{-- Back Button --}}
                <div class="mt-6 text-center"> {{-- Centered the back button --}}
                    {{-- Link back to the loan application show page --}}
                    <a href="{{ route('loan-applications.show', $loanApplication) }}" class="btn btn-secondary">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Kembali ke Butiran Permohonan
                    </a>
                </div>

            </div> {{-- End bg-white card --}}
        </div> {{-- End container --}}
    @endsection

</body>

</html>
