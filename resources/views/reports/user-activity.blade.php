<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Aktiviti Pengguna</title> {{-- Updated title --}}
    {{-- Consider using your main layout's CSS/JS includes instead of standalone Tailwind CDN --}}
    {{-- <script src="https://cdn.tailwindcss.com"></script> --}}
    {{-- If this view extends a layout, the layout should handle CSS/JS --}}
    @extends('layouts.app') {{-- Assuming you extend a layout like layouts.app --}}

    @section('content') {{-- Assuming the layout has a content section --}}

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
            }

            .table tbody tr:nth-child(even) {
                background-color: #f3f4f6;
                /* gray-100 */
            }

            .table tbody tr:hover {
                background-color: #e5e7eb;
                /* gray-200 */
            }
        </style>

        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 py-6">

            <h2 class="text-2xl font-bold mb-6 text-gray-800">Laporan Aktiviti Pengguna (Jumlah Permohonan/Kelulusan
                Pengguna)</h2> {{-- Updated title to reflect data --}}

            {{-- Display success messages --}}
            @if (session()->has('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Table to display user activity data (now displaying users with counts) --}}
            {{-- FIX: Use $users variable instead of $activities --}}
            @if ($users->isEmpty())
                {{-- Message if no user data --}}
                <p class="text-gray-600">Tiada data pengguna ditemui untuk laporan ini.</p>
            @else
                <div class="overflow-x-auto shadow-sm rounded-md border border-gray-200">
                    <table class="min-w-full divide-y divide-gray-200 table">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">
                                    Nama Pengguna
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">
                                    Email
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">
                                    Permohonan Emel
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">
                                    Permohonan Pinjaman
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">
                                    Kelulusan (Officer)
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">
                                    Dibuat Pada
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            {{-- FIX: Loop through the $users data passed from the controller --}}
                            @foreach ($users as $user)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 border-b">
                                        {{ $user->name }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 border-b">
                                        {{ $user->email }}
                                    </td>
                                    {{-- Display counts from withCount --}}
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 border-b">
                                        {{ $user->email_applications_count }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 border-b">
                                        {{ $user->loan_applications_count }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 border-b">
                                        {{ $user->approvals_count }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 border-b">
                                        {{ $user->created_at?->format('Y-m-d H:i:s') ?? 'N/A' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div> {{-- End overflow-x-auto --}}

                {{-- Pagination links --}}
                {{-- FIX: Use $users->links() --}}
                @if ($users->hasPages())
                    <div class="mt-4">
                        {{ $users->links() }}
                    </div>
                @endif
            @endif

            {{-- Optional: Back button --}}
            <div class="mt-6 text-center">
                {{-- If you have a reports index page --}}
                {{-- <a href="{{ route('admin.reports.index') }}" class="btn btn-secondary">
               Kembali ke Laporan
            </a> --}}
                {{-- Or a dashboard link --}}
                {{-- <a href="{{ route('dashboard') }}" class="btn btn-primary">
               Kembali ke Dashboard
            </a> --}}
            </div>

        </div> {{-- End max-w-7xl container --}}
    @endsection {{-- End content section --}}

    {{-- </body>
</html> --}} {{-- These tags should be in your main layout --}}
