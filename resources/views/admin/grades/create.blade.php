<!DOCTYPE html>
{{-- HTML document with language attribute set based on application locale --}}
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    {{-- Character set declaration --}}
    <meta charset="utf-8">
    {{-- Viewport settings for responsive design --}}
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    {{-- Page title, translated --}}
    <title>{{ __('Add New Grade') }}</title>

    {{-- Link Tailwind CSS via CDN for styling.
         This replaces the custom CSS classes like form-group, form-control, etc.,
         used in some other files with standard Tailwind utility classes. --}}
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

    {{-- Define the content section where the form and messages will be placed within the layout --}}
    @section('content')
        {{-- Main container for the form content. Sets max-width, centers horizontally, and adds padding. --}}
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 py-6">

            {{-- Page Title for the form --}}
            <h2 class="text-2xl font-bold mb-6 text-gray-800">{{ __('Add New Grade') }}</h2> {{-- Translated title --}}

            {{-- Display validation errors from the $errors bag --}}
            @if ($errors->any())
                {{-- Replaced custom alert-danger styling with Tailwind classes for a danger alert box --}}
                <div class="bg-red-100 border border-red-200 text-red-800 p-4 rounded-md mb-4">
                    {{-- Translated header for validation errors --}}
                    <p class="font-semibold">{{ __('Validation Error:') }}</p>
                    {{-- List of individual validation error messages --}}
                    <ul class="mt-1 list-disc list-inside text-sm"> {{-- Added text-sm for smaller list item text --}}
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif {{-- End validation error display --}}

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


            {{-- Grade Creation Form --}}
            {{-- This form submits to the 'admin.grades.store' route using POST method --}}
            {{-- Assuming grade creation is handled by a standard controller action, not Livewire --}}
            <form action="{{ route('admin.grades.store') }}" method="POST">
                @csrf {{-- CSRF token for form security --}}

                {{-- Main content container for the form fields. Replaced custom 'card' styling with Tailwind classes. --}}
                <div class="border border-gray-300 rounded-lg p-6 mb-6 bg-white shadow-md">
                    {{-- Section title for grade details. Replaced custom 'card-title' with Tailwind classes. --}}
                    <h4 class="text-xl font-bold mb-4 text-gray-800">{{ __('Grade Details') }}</h4> {{-- Translated title --}}

                    {{-- Form group for Grade Name --}}
                    {{-- Replaced custom 'form-group' styling with Tailwind margin utility 'mb-4' --}}
                    <div class="mb-4">
                        {{-- Label for the name field, styled with Tailwind --}}
                        <label for="name"
                            class="block text-gray-700 text-sm font-bold mb-2">{{ __('Grade Name*:') }}</label>
                        {{-- Input field for grade name --}}
                        {{-- Replaced custom 'form-control' styling with Tailwind classes for input appearance, focus states, and validation errors --}}
                        <input type="text" name="name" id="name"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50 @error('name') border-red-500 @enderror"
                            required value="{{ old('name') }}" {{-- Required field, repopulates value on error --}}
                            placeholder="{{ __('Enter grade name') }}"> {{-- Added placeholder, translated --}}
                        @error('name')
                            {{-- Display validation error message for 'name'. Replaced custom 'text-danger' with Tailwind class. --}}
                            <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
                        @enderror
                    </div> {{-- End form group --}}

                    {{-- Form group for Grade Code --}}
                    {{-- Replaced custom 'form-group' styling with Tailwind margin utility 'mb-4' --}}
                    <div class="mb-4">
                        {{-- Label for the code field, styled with Tailwind --}}
                        <label for="code"
                            class="block text-gray-700 text-sm font-bold mb-2">{{ __('Grade Code*:') }}</label>
                        {{-- Input field for grade code --}}
                        {{-- Replaced custom 'form-control' styling with Tailwind classes --}}
                        <input type="text" name="code" id="code"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50 @error('code') border-red-500 @enderror"
                            required value="{{ old('code') }}" {{-- Required field, repopulates value on error --}}
                            placeholder="{{ __('Enter grade code') }}"> {{-- Added placeholder, translated --}}
                        @error('code')
                            {{-- Display validation error message for 'code'. Replaced custom 'text-danger' with Tailwind class. --}}
                            <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
                        @enderror
                    </div> {{-- End form group --}}

                    {{-- Form group for Description --}}
                    {{-- Replaced custom 'form-group' styling with Tailwind margin utility 'mb-4' --}}
                    <div class="mb-4">
                        {{-- Label for the description field, styled with Tailwind --}}
                        <label for="description"
                            class="block text-gray-700 text-sm font-bold mb-2">{{ __('Description:') }}</label>
                        {{-- Textarea for grade description --}}
                        {{-- Replaced custom 'form-control' styling with Tailwind classes --}}
                        <textarea name="description" id="description" rows="3"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50 @error('description') border-red-500 @enderror"
                            placeholder="{{ __('Enter grade description') }}">{{ old('description') }}</textarea> {{-- Added placeholder, translated, repopulates value on error --}}
                        @error('description')
                            {{-- Display validation error message for 'description'. Replaced custom 'text-danger' with Tailwind class. --}}
                            <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
                        @enderror
                    </div> {{-- End form group --}}

                    {{-- Form group for 'requires_approval' checkbox --}}
                    {{-- Replaced custom 'form-group' styling with Tailwind margin utility 'mb-4' and flex for alignment --}}
                    <div class="mb-4">
                        <div class="flex items-center"> {{-- Use flex for checkbox and label alignment --}}
                            {{-- Checkbox input --}}
                            {{-- Used Tailwind classes for checkbox appearance and focus states --}}
                            <input type="checkbox" name="requires_approval" id="requires_approval" value="1"
                                class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                                {{ old('requires_approval') ? 'checked' : '' }}> {{-- Repopulate checkbox state based on old input --}}
                            {{-- Label for the checkbox, adjusted styling --}}
                            <label class="ml-2 block text-sm text-gray-700" for="requires_approval">
                                {{ __('This grade requires approval for certain applications.') }} {{-- Translated label --}}
                            </label>
                        </div> {{-- End flex container for checkbox and label --}}
                        @error('requires_approval')
                            {{-- Display validation error message for 'requires_approval'. Replaced custom 'text-danger' with Tailwind class. --}}
                            <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
                        @enderror
                    </div> {{-- End form group --}}

                    {{-- TODO: Add more fields here if needed for your Grade model --}}

                </div> {{-- End main form fields container (Tailwind equivalent of a card div) --}}


                {{-- Form Submission Button (Centered) --}}
                <div class="flex justify-center mt-6"> {{-- Center the button horizontally --}}
                    {{-- Submit button. Replaced custom 'btn btn-primary' styling with Tailwind classes. --}}
                    <button type="submit"
                        class="inline-flex items-center justify-center px-5 py-2.5 bg-blue-500 text-white font-semibold rounded-md border border-blue-500 hover:bg-blue-600 hover:border-blue-600 focus:outline-none focus:ring focus:ring-blue-500 focus:ring-opacity-50 transition">
                        {{-- SVG icon (example: checkmark) --}}
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                        {{ __('Save Grade') }} {{-- Translated button text --}}
                    </button>
                </div> {{-- End button container --}}

            </form> {{-- End form --}}

            {{-- Back Button (Centered) --}}
            <div class="mt-6 text-center"> {{-- Added text-center to center the link --}}
                {{-- Back link. Replaced custom 'btn btn-secondary' styling with Tailwind classes. --}}
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

        </div> {{-- End main container --}}
    @endsection {{-- End content section --}}

</body> {{-- End body --}}

</html> {{-- End HTML document --}}
