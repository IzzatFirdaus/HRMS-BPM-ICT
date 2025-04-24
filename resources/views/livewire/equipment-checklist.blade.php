<div>
    {{-- resources/views/livewire/equipment-checklist.blade.php --}}
    @if ($transactionType === 'issue')
        <h3>Issue Equipment for Loan Application #{{ $loanApplicationId }}</h3>
    @elseif ($transactionType === 'return')
        <h3>Process Equipment Return for Loan Application
            #{{ $loanTransaction->loanApplication->id ?? $loanApplicationId }}</h3>
        @if ($loanTransaction)
            <p>Transaction ID: #{{ $loanTransaction->id }}</p>
            <p>Issued On: {{ $loanTransaction->issue_timestamp->format('Y-m-d H:i') ?? 'N/A' }}</p>
            <p>Issued By: {{ $loanTransaction->issuingOfficer->full_name ?? 'N/A' }}</p>
            <p>Equipment Issued: {{ $loanTransaction->equipment->brand ?? 'N/A' }}
                {{ $loanTransaction->equipment->model ?? 'N/A' }} (Tag:
                {{ $loanTransaction->equipment->tag_id ?? 'N/A' }})</p>
            <p>Accessories Issued:
                {{ implode(', ', json_decode($loanTransaction->accessories_checklist_on_issue, true) ?? []) }}</p>
        @endif
    @endif

    @if (session()->has('message'))
        <div class="alert alert-success">
            {{ session('message') }}
        </div>
    @endif

    <form wire:submit.prevent="saveTransaction">
        {{-- Select Equipment --}}
        <div class="form-group">
            <label for="selectedEquipment">Select Equipment Asset:</label>
            @if ($transactionType === 'issue')
                <select wire:model="selectedEquipment" id="selectedEquipment" class="form-control" multiple>
                    {{-- Display available equipment --}}
                    @foreach ($availableEquipment as $equipment)
                        <option value="{{ $equipment->id }}">{{ $equipment->brand }} {{ $equipment->model }} (Tag:
                            {{ $equipment->tag_id }})</option>
                    @endforeach
                </select>
            @elseif ($transactionType === 'return')
                <select wire:model="selectedEquipment" id="selectedEquipment" class="form-control" multiple>
                    {{-- Display equipment currently on loan for this application/user --}}
                    @foreach ($onLoanEquipment as $equipment)
                        <option value="{{ $equipment->id }}">{{ $equipment->brand }} {{ $equipment->model }} (Tag:
                            {{ $equipment->tag_id }})</option>
                    @endforeach
                </select>
            @endif
            @error('selectedEquipment')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>

        {{-- Accessories Checklist --}}
        <div class="form-group">
            <label>Accessories Checklist:</label>
            @foreach ($allAccessoriesList as $accessory)
                <div class="form-check">
                    <input type="checkbox" wire:model="accessories" value="{{ $accessory }}"
                        id="accessory-{{ Str::slug($accessory) }}" class="form-check-input">
                    <label class="form-check-label"
                        for="accessory-{{ Str::slug($accessory) }}">{{ $accessory }}</label>
                </div>
            @endforeach
            @error('accessories')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>

        {{-- Notes --}}
        <div class="form-group">
            <label
                for="notes">{{ $transactionType === 'return' ? 'Return Notes (e.g., damage, missing items)' : 'Issue Notes' }}:</label>
            <textarea wire:model="notes" id="notes" class="form-control" rows="3"></textarea>
            @error('notes')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>

        {{-- Officer (BPM Staff) - Could be pre-filled with current user --}}
        <p>Processed by: {{ Auth::user()->full_name ?? 'N/A' }}</p>


        <button type="submit" class="btn btn-primary">
            {{ $transactionType === 'issue' ? 'Issue Equipment' : 'Process Return' }}
        </button>
    </form>
</div>
