{{--
    resources/views/admin/users/index.blade.php

    This Blade view file displays the list of users for admin management.
    It has been revised to use the specified structure:
    - Includes full HTML boilerplate.
    - Includes the Tailwind CSS via CDN.
    - Extends 'layouts.app' and places content within @section('content').
    - Uses Tailwind CSS classes for styling, aligning with the grades/index.blade.php design.
    - Displays relevant user data fields including MOTAC fields.
    - Correctly uses route names as defined in web.php ('admin.users.*').
    - Applies permission checks for actions.
    - Removes the '+' icon from the 'Add New User' button, as per the original comment.
    - Uses the full pagination links (with icons and page numbers).
    - Assumes a layout file 'layouts.app' exists and has a @yield('content') section.
    - Assumes the User model has relationships for department, grade, and roles (using Spatie\Permission), and a 'status' attribute.
--}}

<!DOCTYPE html>
{{-- HTML document with language attribute set based on application locale --}}
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    {{-- Character set declaration --}}
    <meta charset="utf-8">
    {{-- Viewport settings for responsive design --}}
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    {{-- Page title, translated --}}
    <title>{{ __('User List') }}</title> {{-- Updated title --}}

    {{-- Link Tailwind CSS via CDN for styling. --}}
    <script src="https://cdn.tailwindcss.com"></script>

    {{-- Add any necessary custom CSS here if Tailwind alone is insufficient for specific styling requirements --}}
</head>

{{-- Body element with Tailwind background color and padding applied --}}

<body class="bg-gray-100 p-6">

    {{-- Extend your main layout file. Assuming 'layouts.app' exists and has a @yield('content') section. --}}
    @extends('layouts.app')

    {{-- Define the content section where the list and messages will be placed within the layout --}}
    @section('content')
        {{-- Main container for the content. Sets max-width, centers horizontally, and adds padding, matching grades/equipment design. --}}
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 py-6">

            {{-- Header section with title and Add New User button --}}
            <div class="flex items-center justify-between mb-6">
                {{-- Page Title - Matching grades styling (h2 equivalent styling) --}}
                <h1 class="text-2xl font-bold text-gray-800">{{ __('User List') }}</h1> {{-- Using h1 tag as before --}}

                {{-- Add New User Button --}}
                @can('create users')
                    {{-- Link to the create user page - Route name is correct --}}
                    {{-- Applied button styling exactly matching grades/equipment --}}
                    <a href="{{ route('admin.users.create') }}"
                        class="inline-flex items-center justify-center px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white font-bold rounded shadow-sm focus:outline-none focus:ring focus:ring-blue-500 transition">
                        {{-- No '+' icon as per original users comment --}}
                        {{ __('Add New User') }} {{-- Translated button text --}}
                    </a>
                @endcan
            </div>

            {{-- Display success or error messages from session (using flash messages) --}}
            {{-- Applied styling exactly matching grades/equipment message boxes --}}
            @if (session()->has('success'))
                <div class="bg-green-100 border border-green-200 text-green-800 p-4 rounded-md mb-4">
                    {{ session('success') }} {{-- Display the success message --}}
                </div>
            @endif
            @if (session()->has('error'))
                <div class="bg-red-100 border border-red-200 text-red-800 p-4 rounded-md mb-4">
                    {{ session('error') }} {{-- Display the error message --}}
                </div>
            @endif


            {{-- Users Table Card --}}
            {{-- Applied table container styling exactly matching grades/equipment --}}
            <div class="overflow-x-auto shadow-sm rounded-lg border border-gray-200">
                {{-- User data table, applied Tailwind table classes --}}
                <table class="min-w-full divide-y divide-gray-200">
                    {{-- Table header --}}
                    <thead class="bg-gray-50">
                        <tr>
                            {{-- Table header cells, applied Tailwind th classes --}}
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('Name') }}
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('Email') }}
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('Roles') }}
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('Status') }}
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('Employee ID') }}
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('MOTAC Email') }}
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('Department') }}
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('Grade') }}
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('Actions') }}
                            </th>
                        </tr>
                    </thead>
                    {{-- Table body - Matching grades/equipment styling --}}
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($users as $user)
                            <tr class="hover:bg-gray-100">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ optional($user)->full_name ?? (optional($user)->name ?? 'N/A') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ optional($user)->email ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ optional($user)->getRoleNames()->implode(', ') ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    @php
                                        $statusValue = optional($user)->status;
                                        $statusClass = match ($statusValue) {
                                            'active' => 'bg-green-100 text-green-800',
                                            'inactive' => 'bg-red-100 text-red-800',
                                            'suspended' => 'bg-yellow-100 text-yellow-800',
                                            default => 'bg-gray-100 text-gray-800',
                                        };
                                    @endphp
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $statusClass }}">
                                        {{ __(ucfirst($statusValue ?? 'N/A')) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ optional($user)->employee_id ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ optional($user)->motac_email ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ optional(optional($user)->department)->name ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ optional(optional($user)->grade)->name ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-left text-sm font-medium">
                                    <div class="flex items-center space-x-2">
                                        @can('view', $user)
                                            <a href="{{ route('admin.users.show', $user) }}"
                                                class="text-blue-600 hover:text-blue-800">
                                                {{ __('View') }}
                                            </a>
                                        @endcan
                                        @can('update', $user)
                                            <a href="{{ route('admin.users.edit', $user) }}"
                                                class="text-indigo-600 hover:text-indigo-800">
                                                {{ __('Edit') }}
                                            </a>
                                        @endcan
                                        @can('delete', $user)
                                            <form action="{{ route('admin.users.destroy', $user) }}" method="POST"
                                                onsubmit="return confirm('{{ __('Are you sure you want to delete this user?') }}');"
                                                class="inline-block">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-800">
                                                    {{ __('Delete') }}
                                                </button>
                                            </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-600">
                                    {{ __('No users found.') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination Links --}}
            @if ($users instanceof \Illuminate\Pagination\LengthAwarePaginator && $users->hasPages())
                <div class="mt-4">
                    {{ $users->links() }}
                </div>
            @endif

        </div>
    @endsection

</body>

</html>
