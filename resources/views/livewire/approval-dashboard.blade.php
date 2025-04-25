<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Kelulusan</title>
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

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.5rem 1rem;
            /* py-2 px-4 */
            border-radius: 0.375rem;
            /* rounded-md */
            font-weight: 600;
            /* font-semibold */
            transition: background-color 0.15s ease-in-out, border-color 0.15s ease-in-out, color 0.15s ease-in-out;
            outline: none;
            cursor: pointer;
            font-size: 0.875rem;
            /* text-sm */
        }

        .btn-info {
            background-color: #38b2ac;
            /* teal-500 */
            color: #fff;
            border: 1px solid #38b2ac;
        }

        .btn-info:hover {
            background-color: #319795;
            /* teal-600 */
            border-color: #319795;
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

        .btn-danger {
            background-color: #f56565;
            /* red-500 */
            color: #fff;
            border: 1px solid #f56565;
        }

        .btn-danger:hover {
            background-color: #e53e3e;
            /* red-600 */
            border-color: #e53e3e;
        }
    </style>
</head>

<body class="bg-gray-100 p-6">

    <div class="max-w-7xl mx-auto bg-white p-8 rounded-lg shadow-md"> {{-- Increased max-width for dashboard --}}

        {{-- resources/views/livewire/approval-dashboard.blade.php --}}
        <h2 class="text-2xl font-bold mb-6 text-gray-800">Dashboard Kelulusan</h2>

        {{-- Display success or error messages --}}
        @if (session()->has('message'))
            <div class="alert alert-success">
                {{ session('message') }}
            </div>
        @endif

        @if (session()->has('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif


        {{-- Filter/Search options could go here --}}
        {{-- Example: Search input --}}
        {{-- <div class="mb-4">
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari permohonan..." class="form-control w-auto">
        </div> --}}

        <h3 class="text-xl font-semibold mb-4 text-gray-700">Permohonan Tertunda (Pending Approvals)</h3>

        @if ($pendingApprovals->isEmpty())
            <p class="text-gray-600">Tiada permohonan tertunda pada masa ini.</p>
        @else
            <div class="overflow-x-auto"> {{-- Add horizontal scroll on small screens if table is wide --}}
                <table class="table">
                    <thead>
                        <tr>
                            <th class="w-1/6">Jenis Permohonan</th>
                            <th class="w-1/6">Pemohon</th>
                            <th class="w-1/6">Tarikh Hantar</th>
                            <th class="w-1/6">Status Semasa</th>
                            <th class="w-1/6">Tindakan</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- Loop through pending applications that the current user is authorized to approve --}}
                        @foreach ($pendingApprovals as $application)
                            {{-- Use wire:key for better performance --}}
                            <tr wire:key="approval-{{ $application->id }}-{{ get_class($application) }}">
                                <td class="text-gray-800">
                                    {{-- Display type based on the model --}}
                                    @if ($application instanceof \App\Models\EmailApplication)
                                        Permohonan E-mel ICT (#{{ $application->id }})
                                    @elseif ($application instanceof \App\Models\LoanApplication)
                                        Permohonan Pinjaman Peralatan ICT (#{{ $application->id }})
                                    @else
                                        Jenis Tidak Diketahui
                                    @endif
                                </td>
                                <td class="text-gray-800">{{ $application->user->name ?? 'N/A' }}</td>
                                {{-- Assuming a user relationship with 'name' --}}
                                <td class="text-gray-800">{{ $application->created_at->format('Y-m-d H:i') }}</td>
                                <td class="text-gray-800">{{ ucfirst(str_replace('_', ' ', $application->status)) }}
                                </td>
                                <td class="text-gray-800">
                                    {{-- Link to view details and perform approval action --}}
                                    {{-- Assuming routes are named consistently, e.g., 'email-applications.show' and 'loan-applications.show' --}}
                                    <a href="{{ $application instanceof \App\Models\EmailApplication ? route('email-applications.show', $application) : route('loan-applications.show', $application) }}"
                                        class="btn btn-info mr-2">Lihat Butiran</a>

                                    {{-- Approval/Rejection buttons (conditional based on status and policy) --}}
                                    {{-- Ensure your policies (EmailApplicationPolicy, LoanApplicationPolicy) have 'approve' and 'reject' methods --}}
                                    {{-- The @can directive checks if the currently authenticated user is authorized to perform the action on the given model instance --}}
                                    @can('approve', $application)
                                        {{-- wire:click calls the Livewire component method, passing application ID and class name --}}
                                        <button
                                            wire:click="approve({{ $application->id }}, '{{ get_class($application) }}')"
                                            class="btn btn-success mr-2" wire:loading.attr="disabled">Lulus</button>
                                    @endcan

                                    @can('reject', $application)
                                        {{-- wire:click calls the Livewire component method --}}
                                        <button
                                            wire:click="reject({{ $application->id }}, '{{ get_class($application) }}')"
                                            class="btn btn-danger" wire:loading.attr="disabled">Tolak</button>
                                    @endcan
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div> {{-- End overflow-x-auto --}}

            {{-- Pagination links --}}
            <div class="mt-4">
                {{ $pendingApprovals->links() }}
            </div>


        @endif

        {{-- You might add sections for "Approved History", "Rejected History" etc. here --}}
        {{-- Example: Displaying a history of approvals/rejections by the current user --}}
        {{-- <h3 class="text-xl font-semibold mt-8 mb-4 text-gray-700">Sejarah Kelulusan Saya</h3>
        @if ($approvalHistory->isEmpty())
            <p class="text-gray-600">Tiada sejarah kelulusan.</p>
        @else
             // Display history table similar to pending approvals
        @endif --}}


    </div> {{-- End max-w-7xl container --}}

</body>

</html>
