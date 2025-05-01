<!DOCTYPE html>
{{-- HTML document with language attribute set based on application locale --}}
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    {{-- Character set declaration --}}
    <meta charset="utf-8">
    {{-- Viewport settings for responsive design --}}
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    {{-- Page title, translated --}}
    <title>{{ __('All Users') }}</title>

    {{-- Link Tailwind CSS via CDN for styling.
         This replaces the custom CSS classes like table, alert, etc.,
         used in some other files with standard Tailwind utility classes. --}}
    <script src="https://cdn.tailwindcss.com"></script>

    {{-- Ensure your project is configured to use Tailwind CSS, either via CDN (as shown here)
         or preferably through a build process (like Webpack or Vite) for better performance and customization. --}}

    {{-- Add any necessary custom CSS here if Tailwind alone is insufficient for specific styling requirements --}}
</head>

{{-- Body element with Tailwind background color and padding applied --}}

<body class="bg-gray-100 p-6">

    {{-- Extend your main layout file. Assuming 'layouts.app' exists and has a @yield('content') section. --}}
    @extends('layouts.app')

    {{-- Define the content section where the list will be placed within the layout --}}
    @section('content')
        {{-- Main container for the content. Sets max-width, centers horizontally, and adds padding, using Tailwind classes. --}}
        {{-- Replaced Bootstrap 'container' class with Tailwind classes for a wider container --}}
        <div class="max-w-full mx-auto sm:px-6 lg:px-8 py-6">

            {{-- Page Title --}}
            {{-- Applied Tailwind classes for heading size, font weight, margin, and text color --}}
            <h2>{{ __('All Users') }}</h2> {{-- Translated title --}}

            {{-- Table to display users --}}
            {{-- Added overflow-x-auto for responsiveness on small screens so the table can be scrolled horizontally --}}
            {{-- Applied Tailwind table classes --}}
            <div class="overflow-x-auto shadow-sm rounded-lg border border-gray-200"> {{-- Added container styling --}}
                <table class="min-w-full divide-y divide-gray-200"> {{-- Replaced Bootstrap 'table' with Tailwind classes --}}
                    {{-- Table header --}}
                    <thead class="bg-gray-50"> {{-- Added header background color --}}
                        <tr>
                            {{-- Table header cells, applied Tailwind th classes for padding, alignment, text size, font weight, color, uppercase, and tracking --}}
                            <th>{{ __('Name') }}</th> {{-- Translated header --}}
                            <th>{{ __('Department') }}</th> {{-- Translated header --}}
                            <th>{{ __('Grade') }}</th> {{-- Translated header --}}
                            <th>{{ __('Action') }}</th> {{-- Translated header --}}
                        </tr>
                    </thead>
                    {{-- Table body --}}
                    {{-- Added body background color and row dividers --}}
                    <tbody class="bg-white divide-y divide-gray-200">
                        {{-- Loop through the collection of users passed to the view --}}
                        @foreach ($users as $user)
                            {{-- Table row, added hover effect --}}
                            <tr class="hover:bg-gray-100">
                                {{-- Table data cells, applied Tailwind td classes for padding, whitespace handling, text size, and color --}}
                                <td>{{ $user->full_name }}</td> {{-- Display user's full name --}}
                                <td>{{ $user->department->name ?? '-' }}</td> {{-- Display department name with fallback --}}
                                <td>{{ $user->grade->name ?? '-' }}</td> {{-- Display grade name with fallback --}}
                                {{-- Action column with 'View' link --}}
                                <td>
                                    {{-- 'View' link, uses the named route 'users.show' with the user's ID --}}
                                    {{-- Replaced Bootstrap 'btn btn-sm btn-info' with Tailwind link styling --}}
                                    <a href="{{ route('users.show', $user->id) }}"
                                        class="text-blue-600 hover:text-blue-800">
                                        {{ __('View') }} {{-- Translated action text --}}
                                    </a>
                                </td>
                            </tr> {{-- End table row --}}
                        @endforeach {{-- End loop through users --}}
                    </tbody> {{-- End table body --}}
                </table> {{-- End table --}}
            </div> {{-- End overflow-x-auto container --}}

            {{-- Pagination links would typically go here if using pagination --}}
            {{-- @if ($users->hasPages()) --}}
            {{--     <div class="mt-4"> --}}
            {{--         {{ $users->links() }} --}}
            {{--     </div> --}}
            {{-- @endif --}}

        </div> {{-- End main container --}}
    @endsection {{-- End content section --}}

</body> {{-- End body --}}

</html> {{-- End HTML document --}}
