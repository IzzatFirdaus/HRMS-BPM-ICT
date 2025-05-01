<!DOCTYPE html>
{{-- HTML document with language attribute set based on application locale --}}
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    {{-- Character set declaration --}}
    <meta charset="utf-8">
    {{-- Viewport settings for responsive design --}}
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    {{-- Page title, translated, includes the user's name or full name with fallbacks --}}
    {{-- Define title using @section('title', ...) which is common for layouts --}}
    <title>@yield('title', __('Edit User'))</title> {{-- Default title if not set in @section('title') --}}


    {{-- Link Tailwind CSS via CDN for styling.
         This replaces the custom CSS classes with standard Tailwind utility classes. --}}
    <script src="https://cdn.tailwindcss.com"></script>

    {{-- Ensure your project is configured to use Tailwind CSS, either via CDN (as shown here)
         or preferably through a build process (like Webpack or Vite) for better performance and customization. --}}

    {{-- Add any necessary custom CSS here if Tailwind alone is insufficient for specific styling requirements --}}
    {{-- You might also need to include Select2 CSS if the partials use it --}}
    {{-- <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" /> --}}

    {{-- Custom styles for section headings if 'section-heading' is not a standard Tailwind class --}}
    <style>
        .section-heading {
            @apply text-xl font-semibold text-gray-700 mb-4;
            /* Applying Tailwind classes */
        }
    </style>
</head>

{{-- Body element with Tailwind background color and padding applied --}}
<body class="bg-gray-100 p-6">

    {{-- Extend your main layout file. Assuming 'layouts.app' exists and has a @yield('content') section. --}}
    @extends('layouts.app')

    {{-- Define the title section --}}
    @section('title', __('Edit User') . ': ' . ($user->name ?? ($user->full_name ?? 'N/A')))

    {{-- Define the content section where the form and messages will be placed within the layout --}}
    @section('content')
        {{-- Main container for the content. Sets max-width, centers horizontally, and adds padding, using Tailwind classes. --}}
        {{-- Updated container classes --}}
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 py-8"> {{-- Increased max-width slightly and vertical padding --}}

            {{-- Header section with title, last updated time, and back button --}}
            <div class="flex items-center justify-between mb-8">
                <div>
                    {{-- Page Title, translated, includes user's name or full name --}}
                    <h1 class="text-2xl font-bold text-gray-800">
                        {{ __('Edit User') }}: {{ $user->name ?? ($user->full_name ?? 'N/A') }}
                    </h1>
                    {{-- Last updated time --}}
                    <p class="text-sm text-gray-600 mt-2">
                        {{ __('Last updated') }}: {{ $user->updated_at->format('d M Y H:i') }} {{-- Formatted updated_at timestamp --}}
                    </p>
                </div>
                {{-- Back button to the users list --}}
                {{-- Replaced custom 'btn-primary' with Tailwind button classes --}}
                <a href="{{ route('resource-management.admin.users.index') }}"
                    class="inline-flex items-center justify-center px-5 py-2.5 bg-gray-200 text-gray-800 font-semibold rounded-md border border-gray-200 hover:bg-gray-300 hover:border-gray-300 focus:outline-none focus:ring focus:ring-gray-500 focus:ring-opacity-50 transition">
                     {{-- Adjusted focus ring color to gray --}}
                    {{-- SVG icon (example: left arrow) --}}
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    {{ __('Back to Users List') }} {{-- Translated link text --}}
                </a>
            </div> {{-- End header section --}}

            {{-- Display validation errors from the $errors bag --}}
            @if ($errors->any())
                {{-- Applied Tailwind classes for a danger alert box --}}
                <div class="bg-red-100 border border-red-200 text-red-800 p-4 rounded-md mb-4">
                    <p class="font-semibold">{{ __('Validation Error:') }}</p> {{-- Translated header --}}
                    <ul class="mt-1 list-disc list-inside text-sm"> {{-- Styled list for error messages --}}
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif {{-- End validation error display --}}

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


            {{-- User Editing Form --}}
            {{-- This form submits to the 'resource-management.admin.users.update' route, passing the $user model, using POST method with PUT method spoofing --}}
            <form action="{{ route('resource-management.admin.users.update', $user) }}" method="POST">
                @csrf {{-- CSRF token for security --}}
                @method('PUT') {{-- Method spoofing to simulate a PUT request for updates --}}

                {{-- Main form content container, using Tailwind classes for background, shadow, rounded corners, and padding --}}
                <div class="bg-white shadow-lg rounded-xl p-8 mb-8">

                    {{-- Section: Basic Information --}}
                    <section class="mb-10">
                        {{-- Section heading, using the custom 'section-heading' class defined in the style block --}}
                        <h2 class="section-heading">{{ __('Basic Information') }}</h2>
                        {{-- Grid layout for form fields, responsive (1 column on small screens, 2 on medium and larger) --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            {{-- Include partial for a text input field --}}
                            @include('admin.users.partials.input-text', [
                                'name' => 'name', // Input field name
                                'label' => __('Name'), // Label text, translated
                                'value' => old('name', $user->name), // Value, repopulated with old input or existing data
                                'required' => true, // Mark as required
                            ])

                            {{-- Include partial for a text input field (Full Name) --}}
                            @include('admin.users.partials.input-text', [
                                'name' => 'full_name',
                                'label' => __('Full Name'),
                                'value' => old('full_name', $user->full_name),
                            ])

                            {{-- Include partial for an email input field (Official Email) --}}
                            @include('admin.users.partials.input-email', [
                                'name' => 'email',
                                'label' => __('Official Email'),
                                'value' => old('email', $user->email),
                                'required' => true,
                            ])

                            {{-- Include partial for an email input field (Personal Email) --}}
                            @include('admin.users.partials.input-email', [
                                'name' => 'personal_email',
                                'label' => __('Personal Email'),
                                'value' => old('personal_email', $user->personal_email),
                            ])

                            {{-- Include partial for an email input field (MOTAC Email) --}}
                            @include('admin.users.partials.input-email', [
                                'name' => 'motac_email',
                                'label' => __('MOTAC Email'),
                                'value' => old('motac_email', $user->motac_email),
                            ])
                        </div> {{-- End grid --}}
                    </section> {{-- End Basic Information section --}}

                    {{-- Section: Employment Details --}}
                    <section class="mb-10">
                        {{-- Section heading --}}
                        <h2 class="section-heading">{{ __('Employment Details') }}</h2>
                         {{-- Grid layout for form fields --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            {{-- Include partial for a text input field (Employee ID) --}}
                            @include('admin.users.partials.input-text', [
                                'name' => 'employee_id',
                                'label' => __('Employee ID'),
                                'value' => old('employee_id', $user->employee_id),
                            ])

                            {{-- Include partial for a text input field (Assigned User ID) --}}
                            @include('admin.users.partials.input-text', [
                                'name' => 'user_id_assigned',
                                'label' => __('Assigned User ID'),
                                'value' => old('user_id_assigned', $user->user_id_assigned),
                            ])

                            {{-- Include partial for a text input field (Phone Number) --}}
                            {{-- Uses fallback value for phone number --}}
                            @include('admin.users.partials.input-text', [
                                'name' => 'phone_number',
                                'label' => __('Phone Number'),
                                'value' => old('phone_number', $user->phone_number ?? $user->mobile),
                            ])

                            {{-- Include partial for a text input field (NRIC/Passport Number) --}}
                            @include('admin.users.partials.input-text', [
                                'name' => 'identification_number',
                                'label' => __('NRIC/Passport Number'),
                                'value' => old('identification_number', $user->identification_number),
                            ])
                        </div> {{-- End grid --}}
                    </section> {{-- End Employment Details section --}}

                    {{-- Section: Organizational Details --}}
                    <section class="mb-10">
                        {{-- Section heading --}}
                        <h2 class="section-heading">{{ __('Organizational Details') }}</h2>
                         {{-- Grid layout for form fields --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            {{-- Include partial for a select dropdown (Department) --}}
                            {{-- Assumes $departments is a collection/array of department objects passed to the view --}}
                            @include('admin.users.partials.select', [
                                'name' => 'department_id', // Select input name
                                'label' => __('Department'), // Label text, translated
                                'options' => $departments, // Array of options for the select
                                'optionValue' => 'id', // Key to use for option value
                                'optionLabel' => 'name', // Key to use for option label
                                'selectedValue' => old('department_id', $user->department_id), // Value to pre-select
                            ])

                            {{-- Include partial for a select dropdown (Position) --}}
                            {{-- Assumes $positions is a collection/array of position objects passed to the view --}}
                            @include('admin.users.partials.select', [
                                'name' => 'position_id',
                                'label' => __('Position'),
                                'options' => $positions,
                                'optionValue' => 'id',
                                'optionLabel' => 'name',
                                'selectedValue' => old('position_id', $user->position_id),
                            ])

                            {{-- Include partial for a select dropdown (Grade) --}}
                            {{-- Assumes $grades is a collection/array of grade objects passed to the view --}}
                            @include('admin.users.partials.select', [
                                'name' => 'grade_id',
                                'label' => __('Grade'),
                                'options' => $grades,
                                'optionValue' => 'id',
                                'optionLabel' => 'name',
                                'selectedValue' => old('grade_id', $user->grade_id),
                            ])
                        </div> {{-- End grid --}}
                    </section> {{-- End Organizational Details section --}}

                    {{-- Section: Employment Status --}}
                    <section class="mb-10">
                         {{-- Section heading --}}
                        <h2 class="section-heading">{{ __('Employment Status') }}</h2>
                         {{-- Grid layout for form fields --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            {{-- Include partial for a select dropdown (Service Status) --}}
                            {{-- Assumes config('user.service_statuses') provides options, with 'value' and 'label' keys --}}
                            @include('admin.users.partials.select', [
                                'name' => 'service_status',
                                'label' => __('Service Status'),
                                'options' => config('user.service_statuses'),
                                'optionValue' => 'value',
                                'optionLabel' => 'label',
                                'selectedValue' => old('service_status', $user->service_status),
                            ])

                            {{-- Include partial for a select dropdown (Appointment Type) --}}
                            {{-- Assumes config('user.appointment_types') provides options, with 'value' and 'label' keys --}}
                            @include('admin.users.partials.select', [
                                'name' => 'appointment_type',
                                'label' => __('Appointment Type'),
                                'options' => config('user.appointment_types'),
                                'optionValue' => 'value',
                                'optionLabel' => 'label',
                                'selectedValue' => old('appointment_type', $user->appointment_type),
                            ])

                            {{-- Include partial for a select dropdown (Account Status) --}}
                            {{-- Assumes config('user.statuses') provides options, with 'value' and 'label' keys --}}
                            @include('admin.users.partials.select', [
                                'name' => 'status',
                                'label' => __('Account Status'),
                                'options' => config('user.statuses'),
                                'optionValue' => 'value',
                                'optionLabel' => 'label',
                                'selectedValue' => old('status', $user->status),
                            ])
                        </div> {{-- End grid --}}
                    </section> {{-- End Employment Status section --}}

                    {{-- Form Actions: Reset and Save buttons --}}
                    <div class="border-t pt-8 mt-8"> {{-- Top border, padding top, and margin top --}}
                        <div class="flex justify-end gap-4"> {{-- Align buttons to the right with spacing --}}
                            {{-- Reset Changes button, uses inline JavaScript to reload the page --}}
                            {{-- Replaced custom 'btn-secondary' with Tailwind button classes --}}
                            <button type="button" class="inline-flex items-center justify-center px-5 py-2.5 bg-gray-200 text-gray-800 font-semibold rounded-md border border-gray-200 hover:bg-gray-300 hover:border-gray-300 focus:outline-none focus:ring focus:ring-gray-500 focus:ring-opacity-50 transition" onclick="window.location.reload()">
                                {{ __('Reset Changes') }} {{-- Translated button text --}}
                            </button>
                            {{-- Save Changes button (Submit button for the form) --}}
                            {{-- Replaced custom 'btn-primary' with Tailwind button classes --}}
                            <button type="submit" class="inline-flex items-center justify-center px-5 py-2.5 bg-blue-500 text-white font-semibold rounded-md border border-blue-500 hover:bg-blue-600 hover:border-blue-600 focus:outline-none focus:ring focus:ring-blue-500 focus:ring-opacity-50 transition">
                                {{ __('Save Changes') }} {{-- Translated button text --}}
                            </button>
                        </div> {{-- End button container --}}
                    </div> {{-- End form actions --}}
                </div> {{-- End main form content container --}}
            </form> {{-- End form --}}
        </div> {{-- End main container --}}
    @endsection {{-- End content section --}}

    {{-- Push script block to the 'scripts' stack defined in the layout --}}
    @push('scripts')
        {{-- Include jQuery and Select2 JS here if not already in the layout --}}
        {{-- <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script> --}}
        {{-- <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script> --}}
        <script>
            // Dynamic select2 initialization if needed
            // This script relies on jQuery and Select2 JS being loaded beforehand
            document.addEventListener('DOMContentLoaded', function() {
                // Initialize Select2 for elements with [data-select2] attribute
                $('[data-select2]').select2({
                    placeholder: $(this).data('placeholder'), // Use placeholder from data attribute
                    allowClear: true, // Allow clearing the selection
                    width: '100%' // Set width to 100%
                });
            });
        </script>
    @endpush {{-- End push scripts --}}

</body> {{-- End body --}}

</html> {{-- End HTML document --}}
