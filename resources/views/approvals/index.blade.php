<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Senarai Kelulusan Tertunda</title>
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
            background-color: #d1fae5; /* green-100 */
            border-color: #a7f3d0; /* green-200 */
            color: #065f46; /* green-800 */
        }
         .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }
        .table th, .table td {
             padding: 0.75rem;
             border: 1px solid #e5e7eb; /* gray-200 */
             text-align: left;
        }
        .table th {
            background-color: #f9fafb; /* gray-50 */
            font-weight: bold;
            text-transform: uppercase;
            font-size: 0.75rem; /* text-xs */
            color: #4b5563; /* gray-600 */
        }
         .table tbody tr:nth-child(odd) {
             background-color: #f9fafb; /* gray-50 */
         }
         .table tbody tr:hover {
             background-color: #f3f4f6; /* gray-100 */
         }
         .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.5rem 1rem; /* py-2 px-4 */
            border-radius: 0.375rem; /* rounded-md */
            font-weight: 600; /* font-semibold */
            transition: background-color 0.15s ease-in-out, border-color 0.15s ease-in-out, color 0.15s ease-in-out;
            outline: none;
            cursor: pointer;
            font-size: 0.875rem; /* text-sm */
         }
         .btn-info {
            background-color: #38b2ac; /* teal-500 */
            color: #fff;
            border: 1px solid #38b2ac;
         }
         .btn-info:hover {
            background-color: #319795; /* teal-600 */
            border-color: #319795;
         }
         .badge {
            display: inline-flex;
            align-items: center;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px; /* rounded-full */
            font-size: 0.75rem; /* text-xs */
            font-weight: 600; /* font-semibold */
            line-height: 1;
         }
         .badge-warning {
             background-color: #fef3c7; /* yellow-100 */
             color: #b45309; /* yellow-800 */
         }
          .badge-secondary {
             background-color: #e5e7eb; /* gray-200 */
             color: #374151; /* gray-700 */
          }
    </style>
</head>
<body class="bg-gray-100 p-6">

    {{-- Extend a layout if you have one, otherwise include basic HTML structure --}}
    @extends('layouts.app')

    @section('content')
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 py-6"> {{-- Converted container to Tailwind --}}
        <h2 class="text-2xl font-bold mb-6 text-gray-800">Senarai Kelulusan Tertunda</h2> {{-- Converted h2 --}}

        {{-- Display success messages --}}
        @if (session()->has('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        {{-- Table to display pending approvals --}}
        @if ($approvals->isEmpty()) {{-- Use isEmpty() for Laravel collections --}}
            <p class="text-gray-600">Tiada kelulusan tertunda pada masa ini.</p> {{-- Message if no pending approvals --}}
        @else
            <div class="overflow-x-auto shadow-sm rounded-md border border-gray-200"> {{-- Added overflow and shadow for table container --}}
                <table class="min-w-full divide-y divide-gray-200 table"> {{-- Converted table classes --}}
                    <thead class="bg-gray-50"> {{-- Added header background --}}
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b"> {{-- Converted th classes --}}
                                Jenis Permohonan
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">
                                Pemohon
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">
                                Status Kelulusan
                            </th>
                             <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">
                                Peringkat
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">
                                Tarikh Dihantar
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">
                                Tindakan
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200"> {{-- Added body background and divider --}}
                        {{-- Loop through the collection of pending approval records --}}
                        @foreach ($approvals as $approval)
                             {{-- Use wire:key if this were a Livewire component, but for standard Blade, not necessary --}}
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 border-b"> {{-- Converted td classes --}}
                                    {{-- Display application type based on the polymorphic relationship --}}
                                    @if ($approval->approvable instanceof \App\Models\EmailApplication)
                                        Permohonan E-mel ICT (#{{ $approval->approvable->id }})
                                    @elseif ($approval->approvable instanceof \App\Models\LoanApplication)
                                        Permohonan Pinjaman Peralatan ICT (#{{ $approval->approvable->id }})
                                    @else
                                        Jenis Tidak Diketahui
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 border-b">
                                    {{-- Display applicant name from the related application's user relationship --}}
                                    {{ $approval->approvable->user->name ?? 'N/A' }} {{-- Assuming user relationship with 'name' --}}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 border-b">
                                    {{-- Display approval status with a colored badge --}}
                                    <span class="badge {{
                                        match($approval->status) {
                                            'pending' => 'badge-warning',
                                            'approved' => 'badge-success', {{-- Should not appear in 'pending' list, but included for completeness --}}
                                            'rejected' => 'badge-danger', {{-- Should not appear in 'pending' list, but included for completeness --}}
                                            default => 'badge-secondary',
                                        }
                                    }}">
                                        {{ ucfirst(str_replace('_', ' ', $approval->status)) }}
                                    </span>
                                </td>
                                 <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 border-b">
                                    {{ $approval->stage ?? 'N/A' }} {{-- Display the approval stage --}}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 border-b">
                                    {{ $approval->created_at->format('Y-m-d H:i') }} {{-- Assuming created_at is submission time --}}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 border-b">
                                    {{-- Link to view details of the specific approval record --}}
                                    {{-- Assuming a route named 'approvals.show' exists --}}
                                    <a href="{{ route('approvals.show', $approval) }}" class="btn btn-info">Lihat Butiran</a> {{-- Converted button --}}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div> {{-- End overflow-x-auto --}}

            {{-- Pagination links --}}
            @if ($approvals->hasPages()) {{-- Check if the collection is paginated --}}
                <div class="mt-4">
                    {{ $approvals->links() }}
                </div>
            @endif

        @endif

        {{-- Optional: Link to Approval History page --}}
         <div class="mt-6 text-center">
             {{-- Assuming a route named 'approvals.history' exists --}}
             <a href="{{ route('approvals.history') }}" class="btn btn-secondary">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                 Lihat Sejarah Kelulusan
             </a>
         </div>


    </div> {{-- End max-w-7xl container --}}
    @endsection

</body>
</html>
