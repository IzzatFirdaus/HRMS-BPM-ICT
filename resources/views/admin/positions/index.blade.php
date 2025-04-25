<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Senarai Jawatan</title>
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

        .btn-danger {
            background-color: #ef4444;
            /* red-500 */
            color: #fff;
            border: 1px solid #ef4444;
        }

        .btn-danger:hover {
            background-color: #dc2626;
            /* red-600 */
            border-color: #dc2626;
        }
    </style>
</head>

<body class="bg-gray-100 p-6">

    {{-- Extend a layout if you have one, otherwise include basic HTML structure --}}
    @extends('layouts.app')

    @section('content')
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 py-6"> {{-- Converted container to Tailwind --}}
            <h2 class="text-2xl font-bold mb-6 text-gray-800">Senarai Jawatan</h2> {{-- Converted h2 --}}

            {{-- Button to add new position --}}
            {{-- You might wrap this in an @can('create', App\Models\Position::class) --}}
            <a href="{{ route('admin.positions.create') }}" class="btn btn-primary mb-4"> {{-- Assuming admin.positions.create route --}}
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
                Tambah Jawatan Baru
            </a>
            {{-- @endcan --}}


            {{-- Display success messages --}}
            @if (session()->has('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Table to display positions --}}
            @if ($positions->isEmpty())
                {{-- Assuming $positions is passed from the controller --}}
                <p class="text-gray-600">Tiada jawatan ditemui.</p> {{-- Message if no positions --}}
            @else
                <div class="overflow-x-auto shadow-sm rounded-md border border-gray-200"> {{-- Added overflow and shadow for table container --}}
                    <table class="min-w-full divide-y divide-gray-200 table"> {{-- Converted table classes --}}
                        <thead class="bg-gray-50"> {{-- Added header background --}}
                            <tr>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">
                                    {{-- Converted th classes --}}
                                    Nama Jawatan
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">
                                    Kod
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">
                                    Penerangan
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">
                                    Tindakan
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200"> {{-- Added body background and divider --}}
                            {{-- Loop through the collection of positions --}}
                            @foreach ($positions as $position)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 border-b">
                                        {{-- Converted td classes --}}
                                        {{ $position->name ?? 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 border-b">
                                        {{ $position->code ?? 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900 border-b"> {{-- Removed whitespace-nowrap --}}
                                        {{ $position->description ?? '-' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 border-b">
                                        {{-- Link to view position details --}}
                                        {{-- Assuming a route named 'admin.positions.show' exists --}}
                                        <a href="{{ route('admin.positions.show', $position) }}"
                                            class="text-blue-600 hover:text-blue-900 font-semibold">Lihat</a>
                                        {{-- Optional: Edit button --}}
                                        {{-- @can('update', $position) --}}
                                        {{-- Assuming a route named 'admin.positions.edit' exists --}}
                                        <a href="{{ route('admin.positions.edit', $position) }}"
                                            class="text-indigo-600 hover:text-indigo-900 font-semibold ml-4">Edit</a>
                                        {{-- @endcan --}}
                                        {{-- Optional: Delete button --}}
                                        {{-- @can('delete', $position) --}}
                                        {{-- Assuming a route named 'admin.positions.destroy' exists --}}
                                        {{-- This would typically be a form submission or Livewire action --}}
                                        {{-- <form action="{{ route('admin.positions.destroy', $position) }}" method="POST" onsubmit="return confirm('Are you sure?')" class="inline ml-4">
                                             @csrf
                                             @method('DELETE')
                                             <button type="submit" class="text-red-600 hover:text-red-900 font-semibold">Buang</button>
                                         </form> --}}
                                        {{-- @endcan --}}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div> {{-- End overflow-x-auto --}}

                {{-- Pagination links --}}
                @if ($positions->hasPages())
                    <div class="mt-4">
                        {{ $positions->links() }}
                    </div>
                @endif
            @endif

        </div> {{-- End max-w-7xl container --}}
    @endsection

</body>

</html>
