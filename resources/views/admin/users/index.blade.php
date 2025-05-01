<!DOCTYPE html>
{{-- HTML document with language attribute set based on application locale --}}
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    {{-- Character set declaration --}}
    <meta charset="utf-8">
    {{-- Viewport settings for responsive design --}}
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    {{-- Page title, translated --}}
    @section('title', __('User List')) {{-- Set the page title using translation --}}
    <title>@yield('title', __('User List'))</title> {{-- Default title if not set in @section('title') --}}

    {{-- Link Tailwind CSS via CDN for styling.
         This replaces the custom CSS classes with standard Tailwind utility classes. --}}
    <script src="https://cdn.tailwindcss.com"></script>

    {{-- Ensure your project is configured to use Tailwind CSS, either via CDN (as shown here)
         or preferably through a build process (like Webpack or Vite) for better performance and customization. --}}

    {{-- Add any necessary custom CSS here if Tailwind alone is insufficient for specific styling requirements --}}
</head>

{{-- Body element with Tailwind background color and padding applied --}}

<body class="bg-gray-100 p-6">

    {{-- Extend your main admin layout. Adjust 'layouts.app' if your admin layout is different. --}}
    @extends('layouts.app')

    {{-- Define the title section (defined in <head> above) --}}


    {{-- Define the content section where the list will be placed within the layout --}}
    @section('content')
        {{-- Main container for the content. Sets max-width, centers horizontally, and adds padding, using Tailwind classes. --}}
        <div class="max-w-full mx-auto sm:px-6 lg:px-8 py-8"> {{-- Use max-w-full for wider container --}}

            {{-- Header section with title and Add New User button --}}
            <div class="flex items-center justify-between mb-6">
                {{-- Page Title --}}
                <h1 class="text-2xl font-bold text-gray-800">{{ __('User List') }}</h1> {{-- Applied Tailwind classes for heading --}}

                {{-- Add New User Button --}}
                {{-- Ensure user has permission to create users --}}
                @can('create users')
                    {{-- Assuming a 'create users' permission exists (from Spatie/laravel-permission) --}}
                    {{-- Assuming a route named 'resource-management.admin.users.create' exists --}}
                    {{-- Replaced the previous green button classes with the standardized blue primary button styling --}}
                    <a href="{{ route('resource-management.admin.users.create') }}"
                        class="inline-flex items-center justify-center px-5 py-2.5 bg-blue-500 text-white font-semibold rounded-md border border-blue-500 hover:bg-blue-600 hover:border-blue-600 focus:outline-none focus:ring focus:ring-blue-500 focus:ring-opacity-50 transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                        </svg>
                        {{ __('Add New User') }} {{-- Translated button text --}}
                    </a>
                @endcan {{-- End can create users check --}}
            </div> {{-- End header section --}}

            {{-- Display success or error messages from session (using flash messages) --}}
            @if (session()->has('success'))
                {{-- Applied Tailwind classes for a success alert box --}}
                <div class="bg-green-100 border border-green-200 text-green-800 p-4 rounded-md mb-4">
                    {{ session('success') }} {{-- Display the success message --}}
                </div>
            @endif {{-- End success message display --}}

            @if (session()->has('error'))
                {{-- Applied Tailwind classes for an error alert box --}}
                <div class="bg-red-100 border border-red-200 text-red-800 p-4 rounded-md mb-4">
                    {{ session('error') }} {{-- Display the error message --}}
                </div>
            @endif {{-- End error message display --}}


            {{-- Users Table Card --}}
            {{-- Container for the table, using Tailwind classes for background, shadow, rounded corners, and padding --}}
            <div class="bg-white shadow-md rounded-lg p-6">
                {{-- Replaced Bootstrap 'table-responsive' with Tailwind 'overflow-x-auto' for horizontal scrolling on small screens --}}
                <div class="overflow-x-auto">
                    {{-- User data table, applied Tailwind table classes --}}
                    <table class="min-w-full divide-y divide-gray-200">
                        {{-- Table header --}}
                        <thead class="bg-gray-50">
                            <tr>
                                {{-- Table header cells, applied Tailwind th classes for padding, alignment, text size, font weight, color, uppercase, and tracking --}}
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('Name') }} {{-- Translated header --}}
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('Email') }} {{-- Translated header --}}
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('Roles') }} {{-- Translated header --}}
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('Status') }} {{-- Translated header --}}
                                </th>

                                {{-- 👇 New MOTAC Columns 👇 --}}
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('Employee ID') }} {{-- Translated header --}}
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('MOTAC Email') }} {{-- Translated header --}}
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('Department') }} {{-- Translated header --}}
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('Grade') }} {{-- Translated header --}}
                                </th>
                                {{-- ☝️ End New MOTAC Columns ☝️ --}}

                                {{-- Actions Column --}}
                                <th scope="col"
                                    class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('Actions') }} {{-- Translated header --}}
                                </th>
                            </tr>
                        </thead>
                        {{-- Table body --}}
                        <tbody class="bg-white divide-y divide-gray-200">
                            {{-- Loop through the users collection. @forelse handles the empty state. --}}
                            @forelse ($users as $user)
                                <tr class="hover:bg-gray-100"> {{-- Added hover effect --}}
                                    {{-- Existing Data Cells, applied Tailwind td classes --}}
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ $user->name ?? 'N/A' }} {{-- Display user name with fallback --}}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $user->email ?? 'N/A' }} {{-- Display user email with fallback --}}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{-- Display user roles using Spatie/laravel-permission's getRoleNames(). Requires HasRoles trait on User model. --}}
                                        {{ $user->getRoleNames()->implode(', ') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{-- Added colored badge for status based on the user's status value --}}
                                        @php
                                            // Determine Tailwind classes based on user status
                                            $statusClass = match ($user->status) {
                                                'Active' => 'bg-green-100 text-green-800',
                                                'Inactive' => 'bg-red-100 text-red-800',
                                                'Pending'
                                                    => 'bg-yellow-100 text-yellow-800', // Added Pending status example
                                                default => 'bg-gray-100 text-gray-800', // Default styling
                                            };
                                        @endphp
                                        <span
                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $statusClass }}">
                                            {{ __($user->status ?? 'N/A') }} {{-- Translated status with fallback --}}
                                        </span>
                                    </td>

                                    {{-- 👇 New MOTAC Data Cells, applied Tailwind td classes 👇 --}}
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $user->employee_id ?? 'N/A' }} {{-- Display employee ID with fallback --}}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $user->motac_email ?? 'N/A' }} {{-- Display MOTAC email with fallback --}}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{-- Accessing department name via relationship. Requires eager loading 'department' in controller. --}}
                                        {{ $user->department->name ?? 'N/A' }} {{-- Display department name with fallback --}}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{-- Accessing grade name via relationship. Requires eager loading 'grade' in controller. --}}
                                        {{ $user->grade->name ?? 'N/A' }} {{-- Display grade name with fallback --}}
                                    </td>
                                    {{-- ☝️ End New MOTAC Data Cells ☝️ --}}


                                    {{-- Actions Column --}}
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        {{-- Wrapped actions in a flex container for layout and spacing --}}
                                        <div class="flex items-center justify-end space-x-2"> {{-- Used space-x-2 for horizontal spacing between links --}}
                                            {{-- View Button --}}
                                            {{-- Ensure user has permission to view the user --}}
                                            @can('view', $user)
                                                {{-- Assuming a 'view User' permission/policy exists --}}
                                                {{-- Assuming a route named 'resource-management.admin.users.show' exists --}}
                                                {{-- Applied Tailwind link styling with hover effects --}}
                                                <a href="{{ route('resource-management.admin.users.show', $user) }}"
                                                    class="text-blue-600 hover:text-blue-800">
                                                    {{ __('View') }} {{-- Translated action --}}
                                                </a>
                                            @endcan {{-- End can view check --}}

                                            {{-- Edit Button --}}
                                            {{-- Ensure user has permission to update the user --}}
                                            @can('update', $user)
                                                {{-- Assuming an 'update User' permission/policy exists --}}
                                                {{-- Assuming a route named 'resource-management.admin.users.edit' exists --}}
                                                {{-- Applied Tailwind link styling with hover effects and color for edit --}}
                                                <a href="{{ route('resource-management.admin.users.edit', $user) }}"
                                                    class="text-indigo-600 hover:text-indigo-800">
                                                    {{ __('Edit') }} {{-- Translated action --}}
                                                </a>
                                            @endcan {{-- End can update check --}}

                                            {{-- Delete Button --}}
                                            {{-- Ensure user has permission to delete the user --}}
                                            @can('delete', $user)
                                                {{-- Assuming a 'delete User' permission/policy exists --}}
                                                {{-- Implement a confirmation dialog for deletion using a form submission --}}
                                                {{-- Applied Tailwind classes for form display to align with other buttons --}}
                                                <form action="{{ route('resource-management.admin.users.destroy', $user) }}"
                                                    method="POST" class="inline-flex items-center" {{-- Used inline-flex and items-center to align button within the flex container --}}
                                                    onsubmit="return confirm('{{ __('Are you sure you want to delete this user?') }}');">
                                                    {{-- JavaScript confirm dialog with translated message --}}
                                                    @csrf {{-- CSRF token for security --}}
                                                    @method('DELETE') {{-- Method spoofing for DELETE request --}}
                                                    {{-- Delete button, applied Tailwind link styling with hover effects and color for delete --}}
                                                    <button type="submit" class="text-red-600 hover:text-red-800">
                                                        {{ __('Delete') }} {{-- Translated action --}}
                                                    </button>
                                                </form>
                                                {{-- Or if using a modal/Livewire for delete confirmation, you would call a JavaScript or Livewire method here --}}
                                                {{-- <button type="button" onclick="confirmDelete({{ $user->id }})" class="text-red-600 hover:text-red-800">
                                                    {{ __('Delete') }}
                                                </button> --}}
                                            @endcan {{-- End can delete check --}}
                                        </div> {{-- End actions flex container --}}
                                    </td> {{-- End actions cell --}}
                                </tr> {{-- End table row --}}
                            @empty
                                {{-- Message when no users are found --}}
                                <tr>
                                    {{-- Increased colspan to match the total number of columns in the header (currently 9) --}}
                                    <td colspan="9"
                                        class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                        {{ __('No users found.') }} {{-- Translated message --}}
                                    </td>
                                </tr>
                            @endforelse {{-- End loop through users --}}
                        </tbody> {{-- End table body --}}
                    </table> {{-- End table --}}
                </div> {{-- End overflow-x-auto --}}

                {{-- Pagination Links --}}
                {{-- Check if the users collection is a paginator and has multiple pages --}}
                @if ($users->hasPages())
                    <div class="mt-6">
                        {{ $users->links() }} {{-- Render Tailwind-styled pagination links --}}
                    </div>
                @endif {{-- End hasPages check --}}
            </div> {{-- End bg-white card --}}
        </div> {{-- End main container --}}
    @endsection {{-- End content section --}}

</body> {{-- End body --}}

</html> {{-- End HTML document --}}
