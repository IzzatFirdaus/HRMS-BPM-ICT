<!DOCTYPE html>
{{-- HTML document with language attribute set based on application locale --}}
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    {{-- Character set declaration --}}
    <meta charset="utf-8">
    {{-- Viewport settings for responsive design --}}
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    {{-- Page title, translated, includes the user's full name with a fallback --}}
    <title>{{ __('User Details') }}: {{ $user->full_name ?? 'N/A' }}</title>

    {{-- Link Tailwind CSS via CDN for styling.
         This replaces the custom CSS classes with standard Tailwind utility classes. --}}
    <script src="https://cdn.tailwindcss.com"></script>

    {{-- Ensure your project is configured to use Tailwind CSS, either via CDN (as shown here)
         or preferably through a build process (like Webpack or Vite) for better performance and customization. --}}

    {{-- Add any necessary custom CSS here if Tailwind alone is insufficient for specific styling requirements --}}
</head>

{{-- Body element with Tailwind background color and padding applied --}}

<body class="bg-gray-100 p-6">

    {{-- Extend your main layout file. Assuming 'layouts.app' exists and has a @yield('content') section. --}}
    @extends('layouts.app')

    {{-- Define the content section where the details will be placed within the layout --}}
    @section('content')
        {{-- Main container for the content. Sets max-width, centers horizontally, and adds vertical padding, using Tailwind classes. --}}
        {{-- Replaced Bootstrap 'container' class with Tailwind classes --}}
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 py-6">
            {{-- Main card-like container for the content block, using Tailwind classes for background, overflow, shadow, rounded corners, and padding. --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">

                {{-- Page Title --}}
                {{-- Applied Tailwind classes for heading size, font weight, margin, and text color --}}
                {{-- Translated title includes the user's full name --}}
                <h2 class="text-2xl font-bold mb-6 text-gray-800">{{ __('User Details') }}: {{ $user->full_name ?? 'N/A' }}
                </h2>

                {{-- Display success or error messages from session (using flash messages) if needed --}}
                @if (session()->has('success'))
                    <div class="bg-green-100 border border-green-200 text-green-800 p-4 rounded-md mb-4">
                        {{ session('success') }}
                    </div>
                @endif

                @if (session()->has('error'))
                    <div class="bg-red-100 border border-red-200 text-red-800 p-4 rounded-md mb-4">
                        {{ session('error') }}
                    </div>
                @endif

                {{-- User Details Block --}}
                {{-- Structured details display using Tailwind classes, similar to grade show view --}}
                <div class="border border-gray-200 rounded-md p-4 mb-6 bg-gray-50">
                    {{-- Section title for user details --}}
                    <h3 class="text-xl font-bold mb-4 text-gray-800">{{ __('User Information') }}</h3>
                    {{-- Translated title --}}

                    {{-- Detail items with consistent styling using Tailwind classes for margin and font weight --}}
                    <p class="mb-2"><span class="font-semibold">{{ __('Name:') }}</span> {{ $user->full_name ?? 'N/A' }}
                    </p> {{-- Display user name --}}
                    <p class="mb-2"><span class="font-semibold">{{ __('NRIC:') }}</span>
                        {{ $user->identification_number ?? 'N/A' }}</p> {{-- Display NRIC --}}
                    <p class="mb-2"><span class="font-semibold">{{ __('MOTAC Email:') }}</span>
                        {{ $user->motac_email ?? 'N/A' }}</p> {{-- Display MOTAC Email --}}
                    {{-- Display Department name, handling potential null relationship --}}
                    <p class="mb-2"><span class="font-semibold">{{ __('Department:') }}</span>
                        {{ $user->department->name ?? '-' }}</p>
                    {{-- Display Grade name, handling potential null relationship --}}
                    <p class="mb-2"><span class="font-semibold">{{ __('Grade:') }}</span>
                        {{ $user->grade->name ?? '-' }}</p>
                    {{-- Display Service Status --}}
                    <p class="mb-2"><span class="font-semibold">{{ __('Status:') }}</span>
                        {{ $user->service_status ?? '-' }}</p>

                    {{-- TODO: Add more user details here if needed (e.g., Phone, Position, Roles, Created At, Updated At) --}}
                    {{-- Example: Display roles using Spatie/laravel-permission package --}}
                    {{-- @if ($user->roles->isNotEmpty()) --}}
                    {{--    <p class="mb-2"><span class="font-semibold">{{ __('Roles:') }}</span> {{ $user->roles->pluck('name')->join(', ') }}</p> --}}
                    {{-- @endif --}}

                </div> {{-- End details block --}}

                {{-- Back Button (Centered) --}}
                <div class="mt-6 text-center"> {{-- Center the button horizontally --}}
                    {{-- Back link to the users list. Standardized button styling using Tailwind classes. --}}
                    <a href="{{ route('users.index') }}"
                        class="inline-flex items-center justify-center px-5 py-2.5 bg-gray-200 text-gray-800 font-semibold rounded-md border border-gray-200 hover:bg-gray-300 hover:border-gray-300 focus:outline-none focus:ring focus:ring-gray-500 focus:ring-opacity-50 transition">
                        {{-- Adjusted focus ring color to gray --}}
                        {{-- SVG icon (example: left arrow) --}}
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        {{ __('Back to Users List') }} {{-- Translated link text --}}
                    </a>
                </div> {{-- End back button container --}}

            </div> {{-- End outer bg-white card-like container --}}
        </div> {{-- End main container --}}
    @endsection {{-- End content section --}}

    {{-- You might have other scripts or footer content in your layout --}}

</body> {{-- End body --}}

</html> {{-- End HTML document --}}
