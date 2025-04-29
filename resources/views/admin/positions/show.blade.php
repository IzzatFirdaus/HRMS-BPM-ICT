{{--
    This Blade file is a detail view for a specific Position.
    It uses Tailwind CSS for styling and extends a base layout.
    It expects a $position variable to be passed to the view.
--}}

@extends('layouts.app') {{-- Extend your main layout file --}}

@section('title', __('Position Details') . ': ' . ($position->name ?? 'N/A')) {{-- Set the page title in the layout --}}

{{-- Removed the <head> and <body> tags and inline <style> block, as these should be handled by the layout --}}
{{-- The Tailwind JIT compiler script <script src="https://cdn.tailwindcss.com"></script> should also be in your main layout <head> --}}

@section('content')
    <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 py-8"> {{-- Container for the content --}}
        {{-- Card-like container for the details --}}
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6"> {{-- Existing Tailwind classes are good --}}

            <h2 class="text-2xl font-bold mb-6 text-gray-800">
                {{ __('Position Details') }}: {{ $position->name ?? 'N/A' }} {{-- Translated Title, applied Tailwind classes, showing position name --}}
            </h2>

            {{-- Display success or error messages --}}
            @if (session()->has('success'))
                {{-- Converted alert alert-success to Tailwind classes --}}
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4"
                    role="alert">
                    {{ session('success') }}
                </div>
            @endif

            @if (session()->has('error'))
                {{-- Converted alert alert-danger to Tailwind classes --}}
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                    {{ session('error') }}
                </div>
            @endif

            {{-- Position Details --}}
            {{-- Converted card to Tailwind classes --}}
            <div class="bg-white shadow-md rounded-lg p-6 mb-6"> {{-- Re-applied card styling as it was in the original, the outer div had different purpose --}}
                <h3 class="text-xl font-semibold mb-4 text-gray-800"> {{-- Converted card-title to Tailwind classes --}}
                    {{ __('Position Details') }} {{-- Translated string --}}
                </h3>

                {{-- Detail Paragraphs with Tailwind classes for spacing and font weight --}}
                <p class="mb-3"><span class="font-semibold">{{ __('Position Name') }}:</span>
                    {{ $position->name ?? 'N/A' }}</p> {{-- Translated label --}}
                <p class="mb-3"><span class="font-semibold">{{ __('Position Code') }}:</span>
                    {{ $position->code ?? 'N/A' }}</p> {{-- Translated label --}}
                <p class="mb-3"><span class="font-semibold">{{ __('Description') }}:</span>
                    {{ $position->description ?? '-' }}</p> {{-- Translated label --}}
            </div> {{-- End card --}}

            {{-- Optional: Link to edit position --}}
            {{-- @can('update', $position) --}}
            <div class="mt-6 text-center">
                {{-- Converted btn btn-primary to Tailwind classes --}}
                <a href="{{ route('admin.positions.edit', $position) }}"
                    class="inline-flex items-center px-5 py-2.5 bg-blue-600 text-white font-semibold rounded-md shadow hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                    </svg>
                    {{ __('Edit Position') }} {{-- Translated string --}}
                </a>
            </div>
            {{-- @endcan --}}


            {{-- Back Button --}}
            <div class="mt-6 text-center"> {{-- Centered the back button --}}
                {{-- Converted btn btn-secondary to Tailwind classes --}}
                <a href="{{ route('admin.positions.index') }}"
                    class="inline-flex items-center px-5 py-2.5 bg-gray-200 text-gray-800 font-semibold rounded-md shadow hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    {{ __('Back to Position List') }} {{-- Translated string --}}
                </a>
            </div>

        </div> {{-- End bg-white card --}}
    </div> {{-- End container --}}
@endsection
