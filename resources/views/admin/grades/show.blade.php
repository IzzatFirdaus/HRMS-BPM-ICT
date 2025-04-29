<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"> {{-- Use app locale for language --}}

<head>
    <meta charset="utf-8"> {{-- Correct charset --}}
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> {{-- Correct viewport --}}
    <title>{{ __('Grade Details') }}: {{ $grade->name ?? 'N/A' }}</title> {{-- Translate title with grade name --}}
    {{-- Link Tailwind CSS via CDN --}}
    <script src="https://cdn.tailwindcss.com"></script>
    {{-- Removed the inline <style> block and custom CSS classes --}}
    {{-- Add any necessary custom CSS if Tailwind alone is insufficient --}}
</head>

<body class="bg-gray-100 p-6"> {{-- Applied Tailwind background and padding to body --}}

    {{-- Extend your main layout --}}
    @extends('layouts.app')

    {{-- Define the content section --}}
    @section('content')
        {{-- Main container with max width and centering --}}
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 py-8"> {{-- Increased vertical padding slightly --}}
            {{-- Main card-like container (already using some Tailwind) --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">

                {{-- Page Title --}}
                {{-- Translate title with grade name --}}
                <h2 class="text-2xl font-bold mb-6 text-gray-800">{{ __('Grade Details') }}: {{ $grade->name ?? 'N/A' }}</h2>

                {{-- Display success or error messages from session (using flash messages) --}}
                @if (session()->has('success'))
                    {{-- Replaced custom alert-success with Tailwind classes --}}
                    <div class="bg-green-100 border border-green-200 text-green-800 p-4 rounded-md mb-4">
                        {{ session('success') }}
                    </div>
                @endif

                @if (session()->has('error'))
                    {{-- Replaced custom alert-danger with Tailwind classes --}}
                    <div class="bg-red-100 border border-red-200 text-red-800 p-4 rounded-md mb-4">
                        {{ session('error') }}
                    </div>
                @endif

                {{-- Grade Details Block --}}
                {{-- Streamlined the card structure, using Tailwind classes on the main div --}}
                <div class="border border-gray-200 rounded-md p-4 mb-6 bg-gray-50"> {{-- Added simple border, padding, background, and bottom margin --}}
                    {{-- Replaced custom card-title with Tailwind classes --}}
                    <h3 class="text-xl font-bold mb-4 text-gray-800">{{ __('Grade Details') }}</h3> {{-- Translate title --}}

                    {{-- Grade detail items with consistent styling --}}
                    <p class="mb-2"><span class="font-semibold">{{ __('Grade Name:') }}</span> {{-- Translate label and add font-semibold --}}
                        {{ $grade->name ?? 'N/A' }}</p>
                    <p class="mb-2"><span class="font-semibold">{{ __('Grade Code:') }}</span> {{-- Translate label --}}
                        {{ $grade->code ?? 'N/A' }}</p>
                    <p class="mb-2"><span class="font-semibold">{{ __('Description:') }}</span> {{-- Translate label --}}
                        {{ $grade->description ?? '-' }}</p>
                    <p class="mb-2 flex items-center"> {{-- Use flex to align label and status/icon --}}
                        <span class="font-semibold mr-2">{{ __('Requires Approval:') }}</span> {{-- Translate label and add right margin --}}
                        {{-- Display status with icons and translated text using Tailwind classes --}}
                        @if ($grade->requires_approval)
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-800">
                                <svg class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                    stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                </svg>
                                {{ __('Yes') }} {{-- Translate text --}}
                            </span>
                        @else
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-800">
                                <svg class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                    stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                                {{ __('No') }} {{-- Translate text --}}
                            </span>
                        @endif
                    </p>

                    {{-- TODO: Add more details here if needed (e.g., Created At, Updated At, Linked Positions count) --}}
                    {{-- Assuming audit columns like created_at and updated_at exist and are populated --}}
                    {{-- <p class="mb-2"><span class="font-semibold">{{ __('Created At:') }}</span> {{ $grade->created_at?->format('Y-m-d H:i') ?? '-' }}</p> --}}
                    {{-- <p class="mb-2"><span class="font-semibold">{{ __('Last Updated At:') }}</span> {{ $grade->updated_at?->format('Y-m-d H:i') ?? '-' }}</p> --}}


                </div> {{-- End details block --}}

                {{-- Optional: Link to edit grade --}}
                {{-- @can('update', $grade) --}}
                <div class="mt-6 text-center">
                    {{-- Standardized button styling --}}
                    <a href="{{ route('admin.grades.edit', $grade) }}"
                        class="inline-flex items-center justify-center px-5 py-2.5 bg-blue-500 text-white font-semibold rounded-md border border-blue-500 hover:bg-blue-600 hover:border-blue-600 focus:outline-none focus:ring focus:ring-blue-500 focus:ring-opacity-50 transition">
                        {{-- SVG icon --}}
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                        </svg>
                        {{ __('Edit Grade') }} {{-- Translate button text --}}
                    </a>
                </div>
                {{-- @endcan --}}


                {{-- Back Button (Centered) --}}
                <div class="mt-6 text-center"> {{-- Centered the back button --}}
                    {{-- Standardized button styling --}}
                    <a href="{{ route('admin.grades.index') }}"
                        class="inline-flex items-center justify-center px-5 py-2.5 bg-gray-200 text-gray-800 font-semibold rounded-md border border-gray-200 hover:bg-gray-300 hover:border-gray-300 focus:outline-none focus:ring focus:ring-gray-500 focus:ring-opacity-50 transition">
                        {{-- Adjusted focus ring color --}}
                        {{-- SVG icon --}}
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        {{ __('Back to Grades List') }} {{-- Translate button text --}}
                    </a>
                </div>

            </div> {{-- End outer bg-white card --}}
        </div> {{-- End main container --}}
    @endsection

    {{-- You might have other scripts or footer content in your layout --}}

</body>

</html>
