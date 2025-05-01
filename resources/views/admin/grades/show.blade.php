<!DOCTYPE html>
{{-- HTML document with language attribute set based on application locale --}}
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    {{-- Character set declaration --}}
    <meta charset="utf-8">
    {{-- Viewport settings for responsive design --}}
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    {{-- Page title, translated, includes the current grade name with a fallback --}}
    <title>{{ __('Grade Details') }}: {{ $grade->name ?? 'N/A' }}</title>

    {{-- Link Tailwind CSS via CDN for styling.
         This replaces the custom CSS classes with standard Tailwind utility classes. --}}
    <script src="https://cdn.tailwindcss.com"></script>

    {{-- Removed the inline <style> block and custom CSS classes that were present in the original file's context.
         Ensure your project is configured to use Tailwind CSS, either via CDN (as shown here)
         or preferably through a build process (like Webpack or Vite) for better performance and customization. --}}

    {{-- Add any necessary custom CSS here if Tailwind alone is insufficient for specific styling requirements --}}
</head>

{{-- Body element with Tailwind background color and padding applied --}}

<body class="bg-gray-100 p-6">

    {{-- Extend your main layout file. Assuming 'layouts.app' exists and has a @yield('content') section. --}}
    @extends('layouts.app')

    {{-- Define the content section where the details will be placed within the layout --}}
    @section('content')
        {{-- Main container for the content. Sets max-width, centers horizontally, and adds increased vertical padding. --}}
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 py-8">
            {{-- Main card-like container for the content block, using Tailwind classes for background, overflow, shadow, rounded corners, and padding. --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">

                {{-- Page Title --}}
                {{-- Translated title includes the current grade name, with a fallback to 'N/A' if grade name is not available --}}
                <h2 class="text-2xl font-bold mb-6 text-gray-800">{{ __('Grade Details') }}: {{ $grade->name ?? 'N/A' }}</h2>

                {{-- Display success or error messages from session (using flash messages) --}}
                @if (session()->has('success'))
                    {{-- Replaced custom alert-success styling with Tailwind classes for a success alert box --}}
                    <div class="bg-green-100 border border-green-200 text-green-800 p-4 rounded-md mb-4">
                        {{ session('success') }} {{-- Display the success message --}}
                    </div>
                @endif {{-- End success message display --}}

                @if (session()->has('error'))
                    {{-- Replaced custom alert-danger styling with Tailwind classes for an error alert box --}}
                    <div class="bg-red-100 border border-red-200 text-red-800 p-4 rounded-md mb-4">
                        {{ session('error') }} {{-- Display the error message --}}
                    </div>
                @endif {{-- End error message display --}}

                {{-- Grade Details Block --}}
                {{-- Streamlined the card structure, using Tailwind classes on this div for border, padding, background, and bottom margin --}}
                <div class="border border-gray-200 rounded-md p-4 mb-6 bg-gray-50">
                    {{-- Section title for grade details. Replaced custom 'card-title' with Tailwind classes. --}}
                    <h3 class="text-xl font-bold mb-4 text-gray-800">{{ __('Grade Details') }}</h3> {{-- Translated title --}}

                    {{-- Grade detail items with consistent styling using Tailwind classes for margin and font weight --}}
                    <p class="mb-2"><span class="font-semibold">{{ __('Grade Name:') }}</span> {{-- Translated label and added font-semibold --}}
                        {{ $grade->name ?? 'N/A' }} {{-- Display grade name with fallback --}}
                    </p>
                    <p class="mb-2"><span class="font-semibold">{{ __('Grade Code:') }}</span> {{-- Translated label --}}
                        {{ $grade->code ?? 'N/A' }} {{-- Display grade code with fallback --}}
                    </p>
                    <p class="mb-2"><span class="font-semibold">{{ __('Description:') }}</span> {{-- Translated label --}}
                        {{ $grade->description ?? '-' }} {{-- Display description with fallback --}}
                    </p>
                    <p class="mb-2 flex items-center"> {{-- Use flex to align label and status/icon horizontally --}}
                        <span class="font-semibold mr-2">{{ __('Requires Approval:') }}</span> {{-- Translated label and added right margin --}}
                        {{-- Display status with icons and translated text using Tailwind classes for badge-like appearance --}}
                        @if ($grade->requires_approval)
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-800">
                                {{-- Green checkmark icon --}}
                                <svg class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                    stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                </svg>
                                {{ __('Yes') }} {{-- Translated text --}}
                            </span>
                        @else
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-800">
                                {{-- Red cross icon --}}
                                <svg class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                    stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                                {{ __('No') }} {{-- Translated text --}}
                            </span>
                        @endif
                    </p>

                    {{-- TODO: Add more details here if needed (e.g., Created At, Updated At, Linked Positions count) --}}
                    {{-- Assuming audit columns like created_at and updated_at exist and are populated --}}
                    {{-- <p class="mb-2"><span class="font-semibold">{{ __('Created At:') }}</span> {{ $grade->created_at?->format('Y-m-d H:i') ?? '-' }}</p> --}}
                    {{-- <p class="mb-2"><span class="font-semibold">{{ __('Last Updated At:') }}</span> {{ $grade->updated_at?->format('Y-m-d H:i') ?? '-' }}</p> --}}


                </div> {{-- End details block --}}

                {{-- Optional: Link to edit grade --}}
                {{-- You might wrap this in an @can('update', $grade) to control access --}}
                <div class="mt-6 text-center"> {{-- Center the button horizontally --}}
                    {{-- Link to the edit grade page. Standardized button styling using Tailwind classes. --}}
                    <a href="{{ route('admin.grades.edit', $grade) }}"
                        class="inline-flex items-center justify-center px-5 py-2.5 bg-blue-500 text-white font-semibold rounded-md border border-blue-500 hover:bg-blue-600 hover:border-blue-600 focus:outline-none focus:ring focus:ring-blue-500 focus:ring-opacity-50 transition">
                        {{-- SVG icon (example: edit) --}}
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                        </svg>
                        {{ __('Edit Grade') }} {{-- Translated button text --}}
                    </a>
                </div>
                {{-- @endcan --}} {{-- End can check --}}


                {{-- Back Button (Centered) --}}
                <div class="mt-6 text-center"> {{-- Added text-center to center the link --}}
                    {{-- Back link to the grades list. Standardized button styling using Tailwind classes. --}}
                    <a href="{{ route('admin.grades.index') }}"
                        class="inline-flex items-center justify-center px-5 py-2.5 bg-gray-200 text-gray-800 font-semibold rounded-md border border-gray-200 hover:bg-gray-300 hover:border-gray-300 focus:outline-none focus:ring focus:ring-gray-500 focus:ring-opacity-50 transition">
                        {{-- Adjusted focus ring color to gray --}}
                        {{-- SVG icon (example: left arrow) --}}
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        {{ __('Back to Grades List') }} {{-- Translated link text --}}
                    </a>
                </div> {{-- End back button container --}}

            </div> {{-- End outer bg-white card-like container --}}
        </div> {{-- End main container --}}
    @endsection {{-- End content section --}}

    {{-- You might have other scripts or footer content in your layout --}}

</body> {{-- End body --}}

</html> {{-- End HTML document --}}
