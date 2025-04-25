<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Senarai Peralatan ICT</title>
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

        .badge-teal {
            /* Custom badge for 'issued' */
            background-color: #b2f5ea;
            /* teal-200 */
            color: #2c7a7b;
            /* teal-800 */
        }

        .badge-purple {
            /* Custom badge for 'returned' */
            background-color: #e9d8fd;
            /* purple-200 */
            color: #6b46c1;
            /* purple-800 */
        }

        .badge-red {
            /* Custom badge for 'overdue' */
            background-color: #feb2b2;
            /* red-200 */
            color: #c53030;
            /* red-800 */
        }
    </style>
</head>

<body class="bg-gray-100 p-6">

    {{-- Extend a layout if you have one, otherwise include basic HTML structure --}}
    @extends('layouts.app')

    @section('content')
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 py-6"> {{-- Converted container to Tailwind --}}
            <h2 class="text-2xl font-bold mb-6 text-gray-800">Senarai Peralatan ICT</h2> {{-- Converted h2 --}}

            {{-- Button to add new equipment (assuming this is an admin/BPM function) --}}
            {{-- You might wrap this in an @can('create', App\Models\Equipment::class) --}}
            <a href="{{ route('equipment.create') }}" class="btn btn-primary mb-4"> {{-- Assuming equipment.create route --}}
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
                Tambah Peralatan Baru
            </a>
            {{-- @endcan --}}


            {{-- Display success messages --}}
            @if (session()->has('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Table to display equipment assets --}}
            @if ($equipment->isEmpty())
                <p class="text-gray-600">Tiada peralatan ICT ditemui dalam inventori.</p> {{-- Message if no equipment --}}
            @else
                <div class="overflow-x-auto shadow-sm rounded-md border border-gray-200"> {{-- Added overflow and shadow for table container --}}
                    <table class="min-w-full divide-y divide-gray-200 table"> {{-- Converted table classes --}}
                        <thead class="bg-gray-50"> {{-- Added header background --}}
                            <tr>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">
                                    {{-- Converted th classes --}}
                                    Jenis Aset
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">
                                    Jenama & Model
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">
                                    Tag ID MOTAC
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">
                                    Nombor Siri
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">
                                    Status
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">
                                    Lokasi Semasa
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">
                                    Tindakan
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200"> {{-- Added body background and divider --}}
                            {{-- Loop through the collection of equipment assets --}}
                            @foreach ($equipment as $item)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 border-b">
                                        {{-- Converted td classes --}}
                                        {{ $item->asset_type ?? 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 border-b">
                                        {{ $item->brand ?? 'N/A' }} {{ $item->model ?? 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 border-b">
                                        {{ $item->tag_id ?? 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 border-b">
                                        {{ $item->serial_number ?? 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 border-b">
                                        {{-- Display status with a colored badge --}}
                                        <span
                                            class="badge {{ match ($item->status) {
                                                'available' => 'badge-success',
                                                'on_loan' => 'badge-warning',
                                                'under_maintenance' => 'badge-info',
                                                'disposed', 'lost', 'damaged' => 'badge-danger',
                                                default => 'badge-secondary',
                                            } }}">
                                            {{ ucfirst(str_replace('_', ' ', $item->status)) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 border-b">
                                        {{ $item->current_location ?? 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 border-b">
                                        {{-- Link to view equipment details --}}
                                        {{-- Assuming a route named 'equipment.show' exists --}}
                                        <a href="{{ route('equipment.show', $item) }}"
                                            class="text-blue-600 hover:text-blue-900 font-semibold">Lihat</a>
                                        {{-- Optional: Edit button if user is authorized (e.g., BPM Staff/Admin) --}}
                                        {{-- @can('update', $item) --}}
                                        {{-- Assuming a route named 'equipment.edit' exists --}}
                                        <a href="{{ route('equipment.edit', $item) }}"
                                            class="text-indigo-600 hover:text-indigo-900 font-semibold ml-4">Edit</a>
                                        {{-- @endcan --}}
                                        {{-- Optional: Delete button if user is authorized --}}
                                        {{-- @can('delete', $item) --}}
                                        {{-- Assuming a route named 'equipment.destroy' exists --}}
                                        {{-- This would typically be a form submission or Livewire action --}}
                                        {{-- <form action="{{ route('equipment.destroy', $item) }}" method="POST" onsubmit="return confirm('Are you sure?')" class="inline ml-4">
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
                @if ($equipment->hasPages())
                    <div class="mt-4">
                        {{ $equipment->links() }}
                    </div>
                @endif
            @endif

        </div> {{-- End max-w-7xl container --}}
    @endsection

</body>

</html>
