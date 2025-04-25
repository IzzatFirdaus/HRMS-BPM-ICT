<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Gred</title>
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
    </style>
</head>

<body class="bg-gray-100 p-6">

    {{-- Extend a layout if you have one, otherwise include basic HTML structure --}}
    @extends('layouts.app')

    @section('content')
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 py-6"> {{-- Container for the content --}}

            <h2 class="text-2xl font-bold mb-6 text-gray-800">Edit Gred: {{ $grade->name ?? 'N/A' }}</h2>
            {{-- Title with grade name --}}

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


            {{-- Grade Editing Form --}}
            {{-- Assuming grade editing is handled by a standard controller action --}}
            <form action="{{ route('admin.grades.update', $grade) }}" method="POST">
                @csrf {{-- CSRF token for security --}}
                @method('PUT') {{-- Method spoofing for PUT request --}}

                <div class="card">
                    <h4 class="card-title">Butiran Gred</h4>

                    <div class="form-group">
                        <label for="name" class="block text-gray-700 text-sm font-bold mb-2">Nama Gred*:</label>
                        <input type="text" name="name" id="name" class="form-control" required
                            value="{{ old('name', $grade->name) }}"> {{-- Populate with existing data --}}
                        @error('name')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="code" class="block text-gray-700 text-sm font-bold mb-2">Kod Gred*:</label>
                        <input type="text" name="code" id="code" class="form-control" required
                            value="{{ old('code', $grade->code) }}"> {{-- Populate with existing data --}}
                        @error('code')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="description" class="block text-gray-700 text-sm font-bold mb-2">Penerangan:</label>
                        <textarea name="description" id="description" class="form-control" rows="3">{{ old('description', $grade->description) }}</textarea> {{-- Populate with existing data --}}
                        @error('description')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <div class="flex items-center"> {{-- Use flex for checkbox and label alignment --}}
                            <input type="checkbox" name="requires_approval" id="requires_approval" value="1"
                                class="form-check-input h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                                {{ old('requires_approval', $grade->requires_approval) ? 'checked' : '' }}>
                            {{-- Repopulate checkbox state --}}
                            <label class="ml-2 block text-sm text-gray-700" for="requires_approval"> {{-- Adjusted label styling --}}
                                Gred ini memerlukan kelulusan untuk permohonan tertentu.
                            </label>
                        </div>
                        @error('requires_approval')
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
                        Kemaskini Gred
                    </button>
                </div>

            </form>

            {{-- Back Button --}}
            <div class="mt-6 text-center"> {{-- Centered the back button --}}
                <a href="{{ route('admin.grades.index') }}" class="btn btn-secondary">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Kembali ke Senarai Gred
                </a>
            </div>

        </div> {{-- End container --}}
    @endsection

</body>

</html>
