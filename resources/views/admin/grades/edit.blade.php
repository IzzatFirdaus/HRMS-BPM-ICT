<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"> {{-- Use app locale for language --}}

<head>
    <meta charset="utf-8"> {{-- Correct charset --}}
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> {{-- Correct viewport --}}
    <title>{{ __('Edit Grade') }}: {{ $grade->name ?? 'N/A' }}</title> {{-- Translate title with grade name --}}
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
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 py-6">

            {{-- Page Title --}}
            {{-- Translate title with grade name --}}
            <h2 class="text-2xl font-bold mb-6 text-gray-800">{{ __('Edit Grade') }}: {{ $grade->name ?? 'N/A' }}</h2>

            {{-- Display validation errors from the $errors bag --}}
            @if ($errors->any())
                {{-- Replaced custom alert-danger with Tailwind classes --}}
                <div class="bg-red-100 border border-red-200 text-red-800 p-4 rounded-md mb-4">
                    <p class="font-semibold">{{ __('Validation Error:') }}</p> {{-- Translate string --}}
                    <ul class="mt-1 list-disc list-inside text-sm"> {{-- Added text-sm for list items --}}
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Display success or error messages from session (using flash messages) --}}
            @if (session()->has('success'))
                {{-- Replaced custom alert-success with Tailwind classes --}}
                <div class="bg-green-100 border border-green-200 text-green-800 p-4 rounded-md mb-4">
                    {{ session('success') }}
                </div>
            @endif

            @if (session()->has('error'))
                <div class="bg-red-100 border border-red-200 text-red-800 p-4 rounded-md mb-4">
                    {{ session('error') }}
                </div>
            @endif


            {{-- Grade Editing Form --}}
            {{-- Assuming grade editing is handled by a standard controller action --}}
            <form action="{{ route('admin.grades.update', $grade) }}" method="POST">
                @csrf {{-- CSRF token for security --}}
                @method('PUT') {{-- Method spoofing for PUT request --}}

                {{-- Replaced custom card with Tailwind classes --}}
                <div class="border border-gray-300 rounded-lg p-6 mb-6 bg-white shadow-md">
                    {{-- Replaced custom card-title with Tailwind classes --}}
                    <h4 class="text-xl font-bold mb-4 text-gray-800">{{ __('Grade Details') }}</h4> {{-- Translate title --}}

                    {{-- Replaced custom form-group with Tailwind margin utility --}}
                    <div class="mb-4">
                        <label for="name"
                            class="block text-gray-700 text-sm font-bold mb-2">{{ __('Grade Name*:') }}</label>
                        {{-- Translate label --}}
                        {{-- Replaced custom form-control with Tailwind classes and added placeholder --}}
                        {{-- Value populated using old() with fallback to existing grade data --}}
                        <input type="text" name="name" id="name"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50 @error('name') border-red-500 @enderror"
                            {{-- Added Tailwind validation styling --}} required value="{{ old('name', $grade->name) }}"
                            placeholder="{{ __('Enter grade name') }}"> {{-- Added placeholder --}}
                        @error('name')
                            {{-- Replaced custom text-danger span with Tailwind class --}}
                            <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Replaced custom form-group with Tailwind margin utility --}}
                    <div class="mb-4">
                        <label for="code"
                            class="block text-gray-700 text-sm font-bold mb-2">{{ __('Grade Code*:') }}</label>
                        {{-- Translate label --}}
                        {{-- Replaced custom form-control with Tailwind classes and added placeholder --}}
                        {{-- Value populated using old() with fallback to existing grade data --}}
                        <input type="text" name="code" id="code"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50 @error('code') border-red-500 @enderror"
                            {{-- Added Tailwind validation styling --}} required value="{{ old('code', $grade->code) }}"
                            placeholder="{{ __('Enter grade code') }}"> {{-- Added placeholder --}}
                        @error('code')
                            {{-- Replaced custom text-danger span with Tailwind class --}}
                            <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Replaced custom form-group with Tailwind margin utility --}}
                    <div class="mb-4">
                        <label for="description"
                            class="block text-gray-700 text-sm font-bold mb-2">{{ __('Description:') }}</label>
                        {{-- Translate label --}}
                        {{-- Replaced custom form-control with Tailwind classes and added placeholder --}}
                        {{-- Value populated using old() with fallback to existing grade data --}}
                        <textarea name="description" id="description" rows="3"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50 @error('description') border-red-500 @enderror"
                            {{-- Added Tailwind validation styling --}} placeholder="{{ __('Enter grade description') }}">{{ old('description', $grade->description) }}</textarea> {{-- Added placeholder --}}
                        @error('description')
                            {{-- Replaced custom text-danger span with Tailwind class --}}
                            <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Replaced custom form-group with Tailwind margin utility and checkbox styling --}}
                    <div class="mb-4">
                        <div class="flex items-center"> {{-- Use flex for checkbox and label alignment --}}
                            <input type="checkbox" name="requires_approval" id="requires_approval" value="1"
                                class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                                {{-- Used Tailwind classes for checkbox --}}
                                {{ old('requires_approval', $grade->requires_approval) ? 'checked' : '' }}>
                            {{-- Repopulate checkbox state using old() with fallback to existing data --}}
                            <label class="ml-2 block text-sm text-gray-700" for="requires_approval"> {{-- Adjusted label styling --}}
                                {{ __('This grade requires approval for certain applications.') }} {{-- Translate label --}}
                            </label>
                        </div>
                        @error('requires_approval')
                            {{-- Replaced custom text-danger span with Tailwind class --}}
                            <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- TODO: Add more fields here if needed for your Grade model --}}

                </div> {{-- End card (Tailwind equivalent div) --}}


                {{-- Form Submission Button (Centered) --}}
                <div class="flex justify-center mt-6">
                    {{-- Replaced custom btn btn-primary with Tailwind classes --}}
                    <button type="submit"
                        class="inline-flex items-center justify-center px-5 py-2.5 bg-blue-500 text-white font-semibold rounded-md border border-blue-500 hover:bg-blue-600 hover:border-blue-600 focus:outline-none focus:ring focus:ring-blue-500 focus:ring-opacity-50 transition">
                        {{-- SVG icon --}}
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                        {{ __('Update Grade') }} {{-- Translate string --}}
                    </button>
                </div>

            </form>

            {{-- Back Button (Centered) --}}
            <div class="mt-6 text-center"> {{-- Centered the back button --}}
                {{-- Replaced custom btn btn-secondary with Tailwind classes --}}
                <a href="{{ route('admin.grades.index') }}"
                    class="inline-flex items-center justify-center px-5 py-2.5 bg-gray-200 text-gray-800 font-semibold rounded-md border border-gray-200 hover:bg-gray-300 hover:border-gray-300 focus:outline-none focus:ring focus:ring-gray-500 focus:ring-opacity-50 transition">
                    {{-- Adjusted focus ring color --}}
                    {{-- SVG icon --}}
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    {{ __('Back to Grades List') }} {{-- Translate string --}}
                </a>
            </div>

        </div> {{-- End container --}}
    @endsection

</body>

</html>
