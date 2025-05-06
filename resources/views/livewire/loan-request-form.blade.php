<style>
    /* Optional: Add custom styles if needed, but prefer Tailwind */
    .form-group {
        margin-bottom: 1rem;
    }

    .form-control {
        width: 100%;
        padding: 0.5rem 0.75rem;
        border: 1px solid #d1d5db;
        border-radius: 0.25rem;
        box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.075);
        outline: none;
        transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
    }

    .form-control:focus {
        border-color: #60a5fa;
        box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.075), 0 0 0 0.2rem rgba(96, 165, 250, 0.25);
    }

    .form-check-input {
        margin-right: 0.5rem;
    }

    /* Add styles for validation errors if not using Tailwind's default error classes */
    .text-danger {
        color: #e3342f;
        font-size: 0.875em;
    }
</style>

<div>
    <h2 class="text-2xl font-bold mb-6 text-gray-800">Borang Permohonan Pinjaman Peralatan ICT</h2>

    @if (session()->has('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif
    @if (session()->has('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ session('error') }}</span>
        </div>
    @endif

    <form wire:submit.prevent="submitApplication" class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
        @csrf

        <h3 class="text-xl font-semibold mb-4 text-gray-700">BAHAGIAN 1 | MAKLUMAT PEMOHON</h3>

        <div class="form-group">
            <label for="full_name" class="block text-gray-700 text-sm font-bold mb-2">Nama Penuh*</label>
            <input type="text" id="full_name" class="form-control" wire:model="fullName" required readonly>
            @error('fullName')
                <span class="text-danger text-xs italic">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="position_grade" class="block text-gray-700 text-sm font-bold mb-2">Jawatan & Gred*</label>
            <input type="text" id="position_grade" class="form-control" wire:model="positionGrade" required readonly>
            @error('positionGrade')
                <span class="text-danger text-xs italic">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="department_unit" class="block text-gray-700 text-sm font-bold mb-2">Bahagian/Unit*</label>
            <input type="text" id="department_unit" class="form-control" wire:model="departmentUnit" required
                readonly>
            @error('departmentUnit')
                <span class="text-danger text-xs italic">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="phone_number" class="block text-gray-700 text-sm font-bold mb-2">No. Telefon*</label>
            <input type="text" id="phone_number" class="form-control" wire:model="phoneNumber" required readonly>
            @error('phoneNumber')
                <span class="text-danger text-xs italic">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="purpose" class="block text-gray-700 text-sm font-bold mb-2">Tujuan Permohonan*</label>
            <textarea id="purpose" class="form-control" wire:model="purpose" required></textarea>
            @error('purpose')
                <span class="text-danger text-xs italic">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="location" class="block text-gray-700 text-sm font-bold mb-2">Lokasi*</label>
            <input type="text" id="location" class="form-control" wire:model="location" required>
            @error('location')
                <span class="text-danger text-xs italic">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="loan_start_date" class="block text-gray-700 text-sm font-bold mb-2">Tarikh Pinjaman*</label>
            <input type="date" id="loan_start_date" class="form-control" wire:model="loanStartDate" required>
            @error('loanStartDate')
                <span class="text-danger text-xs italic">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="loan_end_date" class="block text-gray-700 text-sm font-bold mb-2">Tarikh Dijangka
                Pulang*</label>
            <input type="date" id="loan_end_date" class="form-control" wire:model="loanEndDate" required>
            @error('loanEndDate')
                <span class="text-danger text-xs italic">{{ $message }}</span>
            @enderror
        </div>

        <h3 class="text-xl font-semibold mt-6 mb-4 text-gray-700">BAHAGIAN 2 | MAKLUMAT PEGAWAI BERTANGGUNGJAWAB</h3>

        <div class="form-group">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="is_applicant_responsible"
                    wire:model.live="isApplicantResponsible">
                <label class="form-check-label text-gray-700 text-sm" for="is_applicant_responsible">
                    Sila tandakan jika Pemohon adalah Pegawai Bertanggungjawab. Bahagian ini hanya perlu diisi jika
                    Pegawai Bertanggungjawab bukan Pemohon.
                </label>
            </div>
            @error('isApplicantResponsible')
                <span class="text-danger text-xs italic">{{ $message }}</span>
            @enderror
        </div>

        @if (!$isApplicantResponsible)
            <div class="form-group">
                <label for="responsible_officer_name" class="block text-gray-700 text-sm font-bold mb-2">Nama Penuh
                    Pegawai Bertanggungjawab*</label>
                <input type="text" id="responsible_officer_name" class="form-control"
                    wire:model="responsibleOfficerName" required>
                @error('responsibleOfficerName')
                    <span class="text-danger text-xs italic">{{ $message }}</span>
                @enderror
            </div>
            <div class="form-group">
                <label for="responsible_officer_position_grade"
                    class="block text-gray-700 text-sm font-bold mb-2">Jawatan & Gred Pegawai Bertanggungjawab*</label>
                <input type="text" id="responsible_officer_position_grade" class="form-control"
                    wire:model="responsibleOfficerPositionGrade" required>
                @error('responsibleOfficerPositionGrade')
                    <span class="text-danger text-xs italic">{{ $message }}</span>
                @enderror
            </div>
            <div class="form-group">
                <label for="responsible_officer_phone" class="block text-gray-700 text-sm font-bold mb-2">No. Telefon
                    Pegawai Bertanggungjawab*</label>
                <input type="text" id="responsible_officer_phone" class="form-control"
                    wire:model="responsibleOfficerPhoneNumber" required>
                @error('responsibleOfficerPhoneNumber')
                    <span class="text-danger text-xs italic">{{ $message }}</span>
                @enderror {{-- CORRECTED from @endror --}}
            </div>
        @endif

        <h3 class="text-xl font-semibold mt-6 mb-4 text-gray-700">BAHAGIAN 3 | MAKLUMAT PERALATAN</h3>

        @foreach ($loanItems as $index => $item)
            <div class="border rounded-md p-4 mb-4" wire:key="loan-item-{{ $index }}">
                <h4 class="text-lg font-medium mb-3">Peralatan #{{ $index + 1 }}</h4>

                <div class="form-group">
                    <label for="jenis_peralatan_{{ $index }}"
                        class="block text-gray-700 text-sm font-bold mb-2">Jenis Peralatan*</label>
                    <input type="text" id="jenis_peralatan_{{ $index }}" class="form-control"
                        wire:model="loanItems.{{ $index }}.equipmentType" required>
                    {{-- Replaced @error with @if ($errors->has(...)) to fix linter issue with dynamic keys --}}
                    @if ($errors->has("loanItems.{$index}.equipmentType"))
                        <span class="text-danger text-xs italic">
                            {{ $errors->first("loanItems.{$index}.equipmentType") }}
                        </span>
                    @endif
                </div>

                <div class="form-group">
                    <label for="kuantiti_{{ $index }}"
                        class="block text-gray-700 text-sm font-bold mb-2">Kuantiti*</label>
                    <input type="number" id="kuantiti_{{ $index }}" class="form-control"
                        wire:model="loanItems.{{ $index }}.quantityRequested" min="1" required>
                    {{-- Replaced @error with @if ($errors->has(...)) to fix linter issue with dynamic keys --}}
                    @if ($errors->has("loanItems.{$index}.quantityRequested"))
                        <span class="text-danger text-xs italic">
                            {{ $errors->first("loanItems.{$index}.quantityRequested") }}
                        </span>
                    @endif
                </div>

                <div class="form-group">
                    <label for="catatan_{{ $index }}"
                        class="block text-gray-700 text-sm font-bold mb-2">Catatan</label>
                    <textarea id="catatan_{{ $index }}" class="form-control" wire:model="loanItems.{{ $index }}.notes"></textarea>
                    {{-- Replaced @error with @if ($errors->has(...)) to fix linter issue with dynamic keys --}}
                    @if ($errors->has("loanItems.{$index}.notes"))
                        <span class="text-danger text-xs italic">
                            {{ $errors->first("loanItems.{$index}.notes") }}
                        </span>
                    @endif
                </div>

                @if (count($loanItems) > 1)
                    <button type="button" wire:click="removeLoanItem({{ $index }})"
                        class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded text-xs mt-2">
                        Remove Item
                    </button>
                @endif
            </div>
        @endforeach

        <button type="button" wire:click="addLoanItem"
            class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded text-sm mt-3">
            Add Another Item
        </button>

        <h3 class="text-xl font-semibold mt-6 mb-4 text-gray-700">BAHAGIAN 4 | PENGESAHAN PEMOHON (PEGAWAI
            BERTANGGUNGJAWAB)</h3>

        <div class="form-group">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="applicant_confirmation"
                    wire:model="applicantConfirmation" required>
                <label class="form-check-label text-gray-700 text-sm" for="applicant_confirmation">
                    Saya faham dan bersetuju dengan syarat-syarat peminjaman peralatan ICT.
                </label>
            </div>
            @error('applicantConfirmation')
                <span class="text-danger text-xs italic">{{ $message }}</span>
            @enderror
        </div>

        <div class="flex justify-center mt-6">
            <button type="submit"
                class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline"
                wire:loading.attr="disabled">
                <span wire:loading.remove>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 inline-block" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                    Hantar Permohonan
                </span>
                <span wire:loading>
                    <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg"
                        fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                            stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l2-2.647z">
                        </path>
                    </svg>
                    Menghantar...
                </span>
            </button>
        </div>

        @if ($loanApplication && !$loanApplication->isDraft())
            <div class="flex justify-center mt-4">
                <p class="text-sm text-gray-600">Permohonan ini telah dihantar dan tidak boleh diubah lagi.</p>
            </div>
        @endif

    </form>
</div>
