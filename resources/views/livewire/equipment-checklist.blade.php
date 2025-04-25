<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checklist Peralatan ICT</title>
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
    </style>
</head>

<body class="bg-gray-100 p-6">

    <div class="max-w-3xl mx-auto bg-white p-8 rounded-lg shadow-md">

        {{-- resources/views/livewire/equipment-checklist.blade.php --}}

        {{-- Display success or error messages --}}
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

        {{-- Title based on transaction type --}}
        @if ($transactionType === 'issue')
            <h3 class="text-2xl font-bold mb-4 text-gray-800">Pengeluaran Peralatan untuk Permohonan Pinjaman
                #{{ $loanApplicationId }}</h3>
        @elseif ($transactionType === 'return')
            <h3 class="text-2xl font-bold mb-4 text-gray-800">Proses Pulangan Peralatan untuk Permohonan Pinjaman
                #{{ $loanTransaction->loanApplication->id ?? $loanApplicationId }}</h3>
            {{-- Display details of the specific transaction being returned --}}
            @if ($loanTransaction)
                <div class="mb-4 text-gray-700">
                    <p>ID Transaksi Pengeluaran: #{{ $loanTransaction->id }}</p>
                    <p>Dikeluarkan Pada: {{ $loanTransaction->issue_timestamp?->format('Y-m-d H:i') ?? 'N/A' }}</p>
                    <p>Dikeluarkan Oleh: {{ $loanTransaction->issuingOfficer->name ?? 'N/A' }}</p> {{-- Assuming 'name' for officer --}}
                    <p>Peralatan Dikeluarkan:
                        {{ $loanTransaction->equipment->brand ?? 'N/A' }}
                        {{ $loanTransaction->equipment->model ?? 'N/A' }}
                        (Tag: {{ $loanTransaction->equipment->tag_id ?? 'N/A' }})
                    </p>
                    <p>Aksesori Dikeluarkan:
                        {{ implode(', ', json_decode($loanTransaction->accessories_checklist_on_issue, true) ?? []) }}
                    </p>
                </div>
            @endif
        @endif

        <form wire:submit.prevent="saveTransaction">

            {{-- Select Equipment Asset(s) --}}
            <div class="form-group">
                <label for="selectedEquipment" class="block text-gray-700 text-sm font-bold mb-2">Pilih
                    Peralatan:</label>
                @if ($transactionType === 'issue')
                    {{-- For issuance, select available equipment --}}
                    <select wire:model="selectedEquipmentIds" id="selectedEquipment" class="form-control" multiple>
                        {{-- Use plural for multiple selection --}}
                        <option value="">- Pilih Peralatan -</option> {{-- Optional: default option --}}
                        {{-- Display available equipment --}}
                        @foreach ($availableEquipment as $equipment)
                            <option value="{{ $equipment->id }}">
                                {{ $equipment->brand }} {{ $equipment->model }} (Tag:
                                {{ $equipment->tag_id ?? 'N/A' }})
                            </option>
                        @endforeach
                    </select>
                    @error('selectedEquipmentIds')
                        {{-- Validate the array of IDs --}}
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                @elseif ($transactionType === 'return')
                    {{-- For return, select equipment currently on loan for this application --}}
                    {{-- Note: This assumes you are returning ONE specific transaction's equipment.
                         If returning multiple items from different transactions at once, the logic would be more complex.
                         The current view structure seems geared towards processing a single issued item's return transaction at a time.
                         If you need to select multiple items for return, the $onLoanEquipment loop is correct,
                         but the component logic needs to handle multiple selectedEquipmentIds for return. --}}
                    <select wire:model="selectedEquipmentIds" id="selectedEquipment" class="form-control" multiple>
                        {{-- Use plural for multiple selection --}}
                        <option value="">- Pilih Peralatan Untuk Dipulangkan -</option> {{-- Optional: default option --}}
                        {{-- Display equipment currently on loan for this application/user --}}
                        {{-- This would typically show items from LoanTransactions where status is 'issued' for this loan app --}}
                        @foreach ($onLoanEquipment as $equipment)
                            <option value="{{ $equipment->id }}">
                                {{ $equipment->brand }} {{ $equipment->model }} (Tag:
                                {{ $equipment->tag_id ?? 'N/A' }})
                            </option>
                        @endforeach
                    </select>
                    @error('selectedEquipmentIds')
                        {{-- Validate the array of IDs --}}
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                @endif
            </div>

            {{-- Accessories Checklist --}}
            <div class="form-group">
                <label class="block text-gray-700 text-sm font-bold mb-2">Senarai Semak Aksesori:</label>
                <p class="text-gray-600 text-sm mb-2">Sila tandakan aksesori yang disertakan bersama peralatan.</p>
                <div class="grid grid-cols-2 gap-4"> {{-- Use grid for layout --}}
                    @foreach ($allAccessoriesList as $accessory)
                        <div class="flex items-center"> {{-- Use flex for checkbox and label alignment --}}
                            {{-- wire:model binds to an array property $accessories --}}
                            <input type="checkbox" wire:model="accessories" value="{{ $accessory }}"
                                id="accessory-{{ Str::slug($accessory) }}"
                                class="form-check-input h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                            {{-- Tailwind checkbox styles --}}
                            <label class="ml-2 block text-sm text-gray-700"
                                for="accessory-{{ Str::slug($accessory) }}">{{ $accessory }}</label>
                        </div>
                    @endforeach
                </div>
                @error('accessories')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>

            {{-- Notes --}}
            <div class="form-group">
                <label for="notes" class="block text-gray-700 text-sm font-bold mb-2">
                    {{ $transactionType === 'return' ? 'Catatan Pulangan (cth: kerosakan, item hilang)' : 'Catatan Pengeluaran' }}:
                </label>
                <textarea wire:model="notes" id="notes" class="form-control" rows="3"></textarea>
                @error('notes')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>

            {{-- Officer (BPM Staff) - Display pre-filled with current user --}}
            <div class="form-group">
                <label class="block text-gray-700 text-sm font-bold mb-1">Diproses Oleh:</label>
                <p class="text-gray-800">{{ Auth::user()->name ?? 'N/A' }}</p> {{-- Assuming 'name' for user --}}
            </div>


            {{-- Submit Button --}}
            <div class="flex justify-center mt-6">
                <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                    {{-- wire:loading.attr="disabled" disables the button while the Livewire component is processing --}}
                    <span wire:loading.remove>
                        {{ $transactionType === 'issue' ? 'Rekod Pengeluaran Peralatan' : 'Rekod Pulangan Peralatan' }}
                    </span>
                    <span wire:loading class="flex items-center">
                        <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg"
                            fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l2-2.647z">
                            </path>
                        </svg>
                        Menyimpan...
                    </span>
                </button>
            </div>
        </form>

        {{-- You might add sections to display transaction history for this loan application --}}
        {{-- @if ($loanApplication)
            <h4 class="mt-8 text-xl font-bold text-gray-800">Sejarah Transaksi Pinjaman</h4>
            // Display issued/returned transactions related to $loanApplication
         @endif --}}

    </div> {{-- End max-w-3xl container --}}

</body>

</html>
