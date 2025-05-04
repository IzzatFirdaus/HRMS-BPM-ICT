@extends('layouts.app') {{-- Extend your main layout file --}}

@section('title', 'Edit Equipment') {{-- Set the page title --}}

@section('content') {{-- Start the content section --}}

    <div class="container"> {{-- Basic container for layout --}}
        <h1>Edit Equipment</h1>

        {{-- Display validation errors --}}
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Form for updating equipment, posts to the update route --}}
        <form action="{{ route('admin.equipment.update', $equipment) }}" method="POST">
            @csrf {{-- CSRF token --}}
            @method('PUT') {{-- Method override for PUT request --}}

            {{-- Asset Tag ID Field --}}
            <div class="form-group">
                <label for="tag_id">Asset Tag ID:</label>
                {{-- Use old() to repopulate after validation errors, fallback to model data --}}
                <input type="text" name="tag_id" id="tag_id" class="form-control"
                    value="{{ old('tag_id', $equipment->tag_id) }}" required>
                @error('tag_id')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>

            {{-- Asset Type Dropdown --}}
            <div class="form-group">
                <label for="asset_type">Asset Type:</label>
                <select name="asset_type" id="asset_type" class="form-control" required>
                    <option value="">-- Select Type --</option>
                    @foreach ($equipmentTypes as $type)
                        {{-- Loop through types from controller --}}
                        {{-- Check if the current value or old value matches the option --}}
                        <option value="{{ $type }}"
                            {{ old('asset_type', $equipment->asset_type) == $type ? 'selected' : '' }}>
                            {{ ucfirst($type) }} {{-- Capitalize for display --}}
                        </option>
                    @endforeach
                </select>
                @error('asset_type')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>

            {{-- Brand Field --}}
            <div class="form-group">
                <label for="brand">Brand:</label>
                <input type="text" name="brand" id="brand" class="form-control"
                    value="{{ old('brand', $equipment->brand) }}">
                @error('brand')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>

            {{-- Model Field --}}
            <div class="form-group">
                <label for="model">Model:</label>
                <input type="text" name="model" id="model" class="form-control"
                    value="{{ old('model', $equipment->model) }}">
                @error('model')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>

            {{-- Serial Number Field --}}
            <div class="form-group">
                <label for="serial_number">Serial Number:</label>
                <input type="text" name="serial_number" id="serial_number" class="form-control"
                    value="{{ old('serial_number', $equipment->serial_number) }}">
                @error('serial_number')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>

            {{-- Description Textarea --}}
            <div class="form-group">
                <label for="description">Description:</label>
                <textarea name="description" id="description" class="form-control">{{ old('description', $equipment->description) }}</textarea>
                @error('description')
                    <span class="text-danger">{{ $message }}</span>
                @enderror {{-- Corrected @endror to @enderror --}}
            </div> {{-- Corrected closing div placement --}}

            {{-- Purchase Date Field --}}
            <div class="form-group">
                <label for="purchase_date">Purchase Date:</label>
                {{-- Format date for input type="date" --}}
                <input type="date" name="purchase_date" id="purchase_date" class="form-control"
                    value="{{ old('purchase_date', $equipment->purchase_date?->format('Y-m-d')) }}">
                @error('purchase_date')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>

            {{-- Warranty Expiry Date Field --}}
            <div class="form-group">
                <label for="warranty_expiry_date">Warranty Expiry Date:</label>
                {{-- Format date for input type="date" --}}
                <input type="date" name="warranty_expiry_date" id="warranty_expiry_date" class="form-control"
                    value="{{ old('warranty_expiry_date', $equipment->warranty_expiry_date?->format('Y-m-d')) }}">
                @error('warranty_expiry_date')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>

            {{-- Availability Status Dropdown --}}
            <div class="form-group">
                <label for="availability_status">Availability Status:</label>
                <select name="availability_status" id="availability_status" class="form-control" required>
                    <option value="">-- Select Status --</option>
                    @foreach ($availabilityStatuses as $status)
                        {{-- Loop through statuses from controller --}}
                        {{-- Check if the current value or old value matches the option --}}
                        <option value="{{ $status }}"
                            {{ old('availability_status', $equipment->availability_status) == $status ? 'selected' : '' }}>
                            {{-- Assuming you might have a way to translate statuses for display --}}
                            {{ $status }} {{-- Display raw status, consider using a translation helper like __() --}}
                        </option>
                    @endforeach
                </select>
                @error('availability_status')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>

            {{-- Condition Status Dropdown --}}
            <div class="form-group">
                <label for="condition_status">Condition Status:</label>
                <select name="condition_status" id="condition_status" class="form-control" required>
                    <option value="">-- Select Condition --</option>
                    @foreach ($conditionStatuses as $status)
                        {{-- Loop through statuses from controller --}}
                        {{-- Check if the current value or old value matches the option --}}
                        <option value="{{ $status }}"
                            {{ old('condition_status', $equipment->condition_status) == $status ? 'selected' : '' }}>
                            {{-- Assuming you might have a way to translate statuses for display --}}
                            {{ $status }} {{-- Display raw status, consider using a translation helper like __() --}}
                        </option>
                    @endforeach
                </select>
                @error('condition_status')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>

            {{-- Notes Textarea (This section was causing issues) --}}
            <div class="form-group">
                <label for="notes">Notes:</label>
                <textarea name="notes" id="notes" class="form-control">{{ old('notes', $equipment->notes) }}</textarea>
                @error('notes')
                    <span class="text-danger">{{ $message }}</span>
                @enderror {{-- Corrected typo --}}
            </div> {{-- Corrected closing div placement --}}

            {{-- Location Details Field --}}
            <div class="form-group">
                <label for="current_location">Current Location Details:</label>
                <input type="text" name="current_location" id="current_location" class="form-control"
                    value="{{ old('current_location', $equipment->current_location) }}">
                @error('current_location')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>


            {{-- Department Dropdown (if linking equipment directly) --}}
            <div class="form-group">
                <label for="department_id">Department:</label>
                <select name="department_id" id="department_id" class="form-control">
                    <option value="">-- Select Department --</option>
                    @foreach ($departments as $department)
                        <option value="{{ $department->id }}"
                            {{ old('department_id', $equipment->department_id) == $department->id ? 'selected' : '' }}>
                            {{ $department->name }}
                        </option>
                    @endforeach
                </select>
                @error('department_id')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>

            {{-- Center Dropdown (if linking equipment directly) --}}
            <div class="form-group">
                <label for="center_id">Center:</label>
                <select name="center_id" id="center_id" class="form-control">
                    <option value="">-- Select Center --</option>
                    @foreach ($centers as $center)
                        <option value="{{ $center->id }}"
                            {{ old('center_id', $equipment->center_id) == $center->id ? 'selected' : '' }}>
                            {{ $center->name }}
                        </option>
                    @endforeach
                </select>
                @error('center_id')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div> {{-- Corrected closing div placement --}}


            {{-- Add fields for other columns like is_active, in_service, is_gpr, value, real_price, expected_price, old_id, acquisition_date, acquisition_type, funded_by if needed on the form --}}
            {{-- Example for a boolean field: --}}
            {{-- <div class="form-group form-check">
             <input type="checkbox" name="is_active" id="is_active" class="form-check-input" value="1" {{ old('is_active', $equipment->is_active) ? 'checked' : '' }}>
             <label class="form-check-label" for="is_active">Is Active</label>
              @error('is_active') <span class="text-danger">{{ $message }}</span> @enderror
         </div> --}}


            {{-- Submit Button --}}
            <button type="submit" class="btn btn-success">Update Equipment</button>

            {{-- Link back to the show page --}}
            <a href="{{ route('admin.equipment.show', $equipment) }}" class="btn btn-secondary">Cancel</a>

        </form>

    </div>

@endsection {{-- End the content section --}}
