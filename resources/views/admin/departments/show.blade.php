<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Butiran Bahagian/Unit</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Optional: Add custom styles if needed, but prefer Tailwind */
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
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 py-8"> {{-- Container for the content --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6"> {{-- Card-like container --}}

                <h2 class="text-2xl font-bold mb-6 text-gray-800">Butiran Bahagian/Unit: {{ $department->name ?? 'N/A' }}
                </h2> {{-- Title with department name --}}

                {{-- Display success or error messages --}}
                @if (session()->has('success'))
                    <div class="alert alert-success mb-4">
                        {{ session('success') }}
                    </div>
                @endif

                @if (session()->has('error'))
                    <div class="alert alert-danger mb-4">
                        {{ session('error') }}
                    </div>
                @endif

                {{-- Department Details --}}
                <div class="card">
                    <h3 class="card-title">Butiran Bahagian/Unit</h3>

                    <p class="mb-2"><span class="font-semibold">Nama Bahagian/Unit:</span>
                        {{ $department->name ?? 'N/A' }}</p>
                    <p class="mb-2"><span class="font-semibold">Kod Bahagian/Unit:</span> {{ $department->code ?? 'N/A' }}
                    </p>
                    <p class="mb-2"><span class="font-semibold">Penerangan:</span> {{ $department->description ?? '-' }}
                    </p>
                </div> {{-- End card --}}

                {{-- Optional: Link to edit department --}}
                {{-- @can('update', $department) --}}
                <div class="mt-6 text-center">
                    <a href="{{ route('admin.departments.edit', $department) }}" class="btn btn-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                        </svg>
                        Edit Bahagian/Unit
                    </a>
                </div>
                {{-- @endcan --}}


                {{-- Back Button --}}
                <div class="mt-6 text-center"> {{-- Centered the back button --}}
                    <a href="{{ route('admin.departments.index') }}" class="btn btn-secondary">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Kembali ke Senarai Bahagian/Unit
                    </a>
                </div>

            </div> {{-- End bg-white card --}}
        </div> {{-- End container --}}
    @endsection

</body>

</html>
