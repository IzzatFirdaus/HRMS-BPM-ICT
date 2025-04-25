<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Peralatan ICT</title>
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
    </style>
</head>

<body class="bg-gray-100 p-6">

    {{-- Extend a layout if you have one, otherwise include basic HTML structure --}}
    @extends('layouts.app')

    @section('content')
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 py-6"> {{-- Container for the content --}}

            <h2 class="text-2xl font-bold mb-6 text-gray-800">Edit Peralatan ICT #{{ $equipment->id }} (Tag:
                {{ $equipment->tag_id ?? 'N/A' }})</h2> {{-- Title with equipment ID/Tag ID --}}

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


            {{-- Equipment Editing Form --}}
            {{-- Assuming equipment editing is handled by a standard controller action --}}
            <form action="{{ route('equipment.update', $equipment) }}" method="POST">
                @csrf {{-- CSRF token for security --}}
                @method('PUT') {{-- Method spoofing for PUT request --}}

                <div class="card">
                    <h4 class="card-title">Butiran Peralatan</h4>

                    <div class="form-group">
                        <label for="asset_type" class="block text-gray-700 text-sm font-bold mb-2">Jenis Aset*:</label>
                        {{-- You might use a select dropdown with predefined types if available --}}
                        <input type="text" name="asset_type" id="asset_type" class="form-control" required
                            value="{{ old('asset_type', $equipment->asset_type) }}"> {{-- Populate with existing data --}}
                        @error('asset_type')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="brand" class="block text-gray-700 text-sm font-bold mb-2">Jenama*:</label>
                        <input type="text" name="brand" id="brand" class="form-control" required
                            value="{{ old('brand', $equipment->brand) }}"> {{-- Populate with existing data --}}
                        @error('brand')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="model" class="block text-gray-700 text-sm font-bold mb-2">Model*:</label>
                        <input type="text" name="model" id="model" class="form-control" required
                            value="{{ old('model', $equipment->model) }}"> {{-- Populate with existing data --}}
                        @error('model')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="serial_number" class="block text-gray-700 text-sm font-bold mb-2">Nombor Siri*:</label>
                        <input type="text" name="serial_number" id="serial_number" class="form-control" required
                            value="{{ old('serial_number', $equipment->serial_number) }}"> {{-- Populate with existing data --}}
                        @error('serial_number')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="tag_id" class="block text-gray-700 text-sm font-bold mb-2">Tag ID MOTAC*:</label>
                        <input type="text" name="tag_id" id="tag_id" class="form-control" required
                            value="{{ old('tag_id', $equipment->tag_id) }}"> {{-- Populate with existing data --}}
                        @error('tag_id')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="purchase_date" class="block text-gray-700 text-sm font-bold mb-2">Tarikh
                            Pembelian:</label>
                        <input type="date" name="purchase_date" id="purchase_date" class="form-control"
                            value="{{ old('purchase_date', $equipment->purchase_date?->format('Y-m-d')) }}">
                        {{-- Populate with existing data, format date --}}
                        @error('purchase_date')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="warranty_expiry_date" class="block text-gray-700 text-sm font-bold mb-2">Tarikh Tamat
                            Waranti:</label>
                        <input type="date" name="warranty_expiry_date" id="warranty_expiry_date" class="form-control"
                            value="{{ old('warranty_expiry_date', $equipment->warranty_expiry_date?->format('Y-m-d')) }}">
                        {{-- Populate with existing data, format date --}}
                        @error('warranty_expiry_date')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="status" class="block text-gray-700 text-sm font-bold mb-2">Status*:</label>
                        {{-- Select dropdown for status --}}
                        <select name="status" id="status" class="form-control" required>
                            <option value="available"
                                {{ old('status', $equipment->status) == 'available' ? 'selected' : '' }}>Tersedia
                                (Available)</option>
                            <option value="on_loan" {{ old('status', $equipment->status) == 'on_loan' ? 'selected' : '' }}>
                                Sedang Dipinjam (On Loan)</option>
                            <option value="under_maintenance"
                                {{ old('status', $equipment->status) == 'under_maintenance' ? 'selected' : '' }}>Dalam
                                Penyelenggaraan (Under Maintenance)</option>
                            <option value="disposed"
                                {{ old('status', $equipment->status) == 'disposed' ? 'selected' : '' }}>Dilupuskan
                                (Disposed)</option>
                            <option value="lost" {{ old('status', $equipment->status) == 'lost' ? 'selected' : '' }}>
                                Hilang (Lost)</option>
                            <option value="damaged" {{ old('status', $equipment->status) == 'damaged' ? 'selected' : '' }}>
                                Rosak (Damaged)</option>
                        </select>
                        @error('status')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="current_location" class="block text-gray-700 text-sm font-bold mb-2">Lokasi
                            Semasa:</label>
                        <input type="text" name="current_location" id="current_location" class="form-control"
                            value="{{ old('current_location', $equipment->current_location) }}"> {{-- Populate with existing data --}}
                        @error('current_location')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="notes" class="block text-gray-700 text-sm font-bold mb-2">Catatan:</label>
                        <textarea name="notes" id="notes" class="form-control" rows="3">{{ old('notes', $equipment->notes) }}</textarea> {{-- Populate with existing data --}}
                        @error('notes')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                </div> {{-- End card --}}


                {{-- Form Submission Button --}}
                <div class="flex justify-center mt-6">
                    <button type="submit" class="btn btn-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                        Kemaskini Peralatan
                    </button>
                </div>

            </form>

            {{-- Back Button --}}
            <div class="mt-6 text-center"> {{-- Centered the back button --}}
                <a href="{{ route('equipment.index') }}" class="btn btn-secondary">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Kembali ke Senarai Peralatan
                </a>
            </div>

        </div> {{-- End container --}}
    @endsection

</body>

</html>
