<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Butiran Kelulusan</title>
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

        .badge {
            display: inline-flex;
            align-items: center;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            /* rounded-full */
            font-size: 0.75rem;
            /* text-xs */
            font-weight: 600;
            /* font-semibold */
            line-height: 1;
        }

        .badge-info {
            background-color: #bfdbfe;
            /* blue-200 */
            color: #1e40af;
            /* blue-800 */
        }

        .badge-success {
            background-color: #d1fae5;
            /* green-100 */
            color: #065f46;
            /* green-800 */
        }

        .badge-warning {
            background-color: #fef3c7;
            /* yellow-100 */
            color: #b45309;
            /* yellow-800 */
        }

        .badge-danger {
            background-color: #fee2e2;
            /* red-100 */
            border-color: #fecaca;
            /* red-200 */
            color: #991b1b;
            /* red-800 */
        }

        .badge-secondary {
            background-color: #e5e7eb;
            /* gray-200 */
            color: #374151;
            /* gray-700 */
        }
    </style>
</head>

<body class="bg-gray-100 p-6">

    {{-- Extend a layout if you have one, otherwise include basic HTML structure --}}
    @extends('layouts.app')

    @section('content')
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 py-8"> {{-- Container for the content --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6"> {{-- Card-like container --}}

                <h2 class="text-2xl font-bold mb-6 text-gray-800">Butiran Kelulusan #{{ $approval->id }}</h2>
                {{-- Title with approval ID --}}

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

                {{-- Approval Details --}}
                <div class="card mb-6">
                    <h3 class="card-title">Butiran Kelulusan</h3>

                    <p class="mb-2">
                        <span class="font-semibold">Status Kelulusan:</span>
                        {{-- Display status with a colored badge --}}
                        <span
                            class="badge {{ match ($approval->status) {
                                'pending' => 'badge-warning',
                                'approved' => 'badge-success',
                                'rejected' => 'badge-danger',
                                default => 'badge-secondary',
                            } }} text-lg">
                            {{-- Increased badge size --}}
                            {{ ucfirst(str_replace('_', ' ', $approval->status)) }}
                        </span>
                    </p>
                    <p class="mb-2"><span class="font-semibold">Peringkat:</span> {{ $approval->stage ?? 'N/A' }}</p>
                    <p class="mb-2"><span class="font-semibold">Pegawai:</span> {{ $approval->officer->name ?? 'N/A' }}
                    </p> {{-- Assuming officer relationship with 'name' --}}
                    <p class="mb-2"><span class="font-semibold">Tarikh & Masa Keputusan:</span>
                        {{ $approval->approval_timestamp?->format('Y-m-d H:i') ?? 'N/A' }}</p>
                    <p class="mb-2"><span class="font-semibold">Catatan Pegawai:</span> {{ $approval->comments ?? '-' }}
                    </p>
                </div> {{-- End card --}}

                {{-- Associated Application Details --}}
                <div class="card mb-6">
                    <h3 class="card-title">Butiran Permohonan Berkaitan</h3>

                    {{-- Check the type of the approvable relationship --}}
                    @if ($approval->approvable instanceof \App\Models\EmailApplication)
                        <p class="mb-2"><span class="font-semibold">Jenis Permohonan:</span> Permohonan E-mel ICT</p>
                        <p class="mb-2"><span class="font-semibold">Nombor Rujukan:</span>
                            #{{ $approval->approvable->id }}</p>
                        <p class="mb-2"><span class="font-semibold">Pemohon:</span>
                            {{ $approval->approvable->user->name ?? 'N/A' }}</p> {{-- Assuming user relationship on EmailApplication --}}
                        <p class="mb-2"><span class="font-semibold">Tujuan Permohonan:</span>
                            {{ $approval->approvable->purpose ?? 'N/A' }}</p>
                        {{-- Link to the specific email application show page --}}
                        <div class="mt-4">
                            <a href="{{ route('email-applications.show', $approval->approvable) }}"
                                class="text-blue-600 hover:text-blue-900 font-semibold">Lihat Butiran Permohonan E-mel
                                ICT</a>
                        </div>
                    @elseif ($approval->approvable instanceof \App\Models\LoanApplication)
                        <p class="mb-2"><span class="font-semibold">Jenis Permohonan:</span> Permohonan Pinjaman Peralatan
                            ICT</p>
                        <p class="mb-2"><span class="font-semibold">Nombor Rujukan:</span>
                            #{{ $approval->approvable->id }}</p>
                        <p class="mb-2"><span class="font-semibold">Pemohon:</span>
                            {{ $approval->approvable->user->name ?? 'N/A' }}</p> {{-- Assuming user relationship on LoanApplication --}}
                        <p class="mb-2"><span class="font-semibold">Tujuan Permohonan:</span>
                            {{ $approval->approvable->purpose ?? 'N/A' }}</p>
                        <p class="mb-2"><span class="font-semibold">Tarikh Pinjaman:</span>
                            {{ $approval->approvable->loan_start_date?->format('Y-m-d') ?? 'N/A' }}</p>
                        <p class="mb-2"><span class="font-semibold">Tarikh Dijangka Pulang:</span>
                            {{ $approval->approvable->loan_end_date?->format('Y-m-d') ?? 'N/A' }}</p>
                        {{-- Link to the specific loan application show page --}}
                        <div class="mt-4">
                            <a href="{{ route('loan-applications.show', $approval->approvable) }}"
                                class="text-blue-600 hover:text-blue-900 font-semibold">Lihat Butiran Permohonan Pinjaman
                                Peralatan ICT</a>
                        </div>
                    @else
                        <p class="text-gray-600">Jenis permohonan berkaitan tidak diketahui.</p>
                    @endif
                </div> {{-- End card --}}


                {{-- Back Button --}}
                <div class="mt-6 text-center"> {{-- Centered the back button --}}
                    {{-- Assuming a route named 'approval-dashboard.index' for the main dashboard --}}
                    <a href="{{ route('approval-dashboard.index') }}" class="btn btn-secondary">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Kembali ke Dashboard Kelulusan
                    </a>
                </div>

            </div> {{-- End bg-white card --}}
        </div> {{-- End container --}}
    @endsection

</body>

</html>
