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
            border-bottom: 2px solid #e5e7eb;
            /* gray-200 */
            padding-bottom: 0.75rem;
            margin-bottom: 1.5rem;
        }

        /* Add other custom styles as needed, potentially moving them to a compiled asset file */
    </style>
</head>

<body class="bg-gray-100"> {{-- Apply background color to the body --}}

    {{-- Extend your main application layout. This layout should provide the basic HTML structure,
         asset includes (like main CSS and JS), and yielding sections like 'content'. --}}
    @extends('layouts.app')

    {{-- Start the content section --}}
    @section('content')
        {{-- Main container for the page content, centered and with padding --}}
        <div class="max-w-6xl mx-auto py-6 sm:px-6 lg:px-8"> {{-- Increased max-width for more content space --}}
            {{-- Card-like container for the form --}}
            <div class="bg-white overflow-hidden shadow-lg sm:rounded-lg p-8"> {{-- Increased padding --}}

                {{-- Form element with submission method and action --}}
                {{-- Assuming the form updates the user data via a PUT or PATCH request to the user update route --}}
                {{-- The form action uses the 'admin.users.update' route, passing the user model/ID --}}
                {{-- Ensure your controller handles PUT/PATCH requests for updating --}}
                <form action="{{ route('admin.users.update', $user->id) }}" method="POST">
                    @csrf {{-- CSRF token for form submission security --}}
                    @method('PUT') {{-- Method spoofing for PUT/PATCH request --}}

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
                        {{-- CORRECTED ROUTE NAME: Use the correct registered name 'admin.users.index' --}}
                        <a href="{{ route('admin.users.index') }}"
                            class="inline-flex items-center justify-center px-5 py-2.5 bg-gray-200 text-gray-800 font-semibold rounded-md border border-gray-200 hover:bg-gray-300 hover:border-gray-300 focus:outline-none focus:ring focus:ring-gray-500 focus:ring-opacity-50"> {{-- Applied standard Tailwind button styles --}}
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
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6"
                            role="alert">
                            <strong class="font-bold">{{ __('Whoops! Something went wrong.') }}</strong>
                            <span class="block sm:inline">{{ __('Please correct the following errors:') }}</span>
                            <ul class="mt-3 list-disc list-inside">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    {{-- Main form content container with grid layout for inputs --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6"> {{-- Using responsive grid --}}

                        {{-- Full Name Input --}}
                        <div>
                            <label for="full_name" class="block text-sm font-medium text-gray-700">{{ __('Full Name') }}</label>
                            {{-- Applied Tailwind form input classes --}}
                            <input type="text" name="full_name" id="full_name" value="{{ old('full_name', $user->full_name) }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            @error('full_name')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- User ID Assigned Input --}}
                        <div>
                            <label for="user_id_assigned" class="block text-sm font-medium text-gray-700">{{ __('User ID Assigned (MOTAC)') }}</label>
                            <input type="text" name="user_id_assigned" id="user_id_assigned" value="{{ old('user_id_assigned', $user->user_id_assigned) }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            @error('user_id_assigned')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                         {{-- NRIC Input --}}
                        <div>
                            <label for="nric" class="block text-sm font-medium text-gray-700">{{ __('NRIC') }}</label>
                            <input type="text" name="nric" id="nric" value="{{ old('nric', $user->nric) }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            @error('nric')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                         {{-- Mobile Number Input --}}
                        <div>
                            <label for="mobile_number" class="block text-sm font-medium text-gray-700">{{ __('Mobile Number') }}</label>
                            <input type="text" name="mobile_number" id="mobile_number" value="{{ old('mobile_number', $user->mobile_number) }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            @error('mobile_number')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Email (Primary/Login) Input - Often not editable or carefully handled --}}
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700">{{ __('Primary Email (Login)') }}</label>
                             {{-- Primary email might not be editable via this form, or requires special handling --}}
                            <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}"
                                readonly {{-- Added readonly for primary email --}}
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm bg-gray-100 cursor-not-allowed sm:text-sm">
                            @error('email')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                             <p class="mt-2 text-sm text-gray-500">{{ __('Primary email is usually not editable here.') }}</p>
                        </div>

                         {{-- Personal Email Input --}}
                        <div>
                            <label for="personal_email" class="block text-sm font-medium text-gray-700">{{ __('Personal Email') }}</label>
                            <input type="email" name="personal_email" id="personal_email" value="{{ old('personal_email', $user->personal_email) }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            @error('personal_email')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                         {{-- MOTAC Email Input --}}
                        <div>
                            <label for="motac_email" class="block text-sm font-medium text-gray-700">{{ __('MOTAC Official Email') }}</label>
                            <input type="email" name="motac_email" id="motac_email" value="{{ old('motac_email', $user->motac_email) }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            @error('motac_email')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>


                        {{-- Department Dropdown --}}
                        <div>
                            <label for="department_id" class="block text-sm font-medium text-gray-700">{{ __('Department/Unit') }}</label>
                            {{-- Assuming $departments is passed to the view and has 'id' and 'name' properties --}}
                            <select name="department_id" id="department_id"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    data-select2 data-placeholder="{{ __('Select Department/Unit') }}"> {{-- Added data attributes for Select2 --}}
                                <option value=""></option> {{-- Empty option for placeholder/allowClear --}}
                                @foreach ($departments as $department)
                                    <option value="{{ $department->id }}"
                                        {{ old('department_id', $user->department_id) == $department->id ? 'selected' : '' }}>
                                        {{ $department->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('department_id')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Position Dropdown --}}
                        <div>
                            <label for="position_id" class="block text-sm font-medium text-gray-700">{{ __('Position') }}</label>
                            {{-- Assuming $positions is passed to the view and has 'id' and 'name' properties --}}
                             <select name="position_id" id="position_id"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                     data-select2 data-placeholder="{{ __('Select Position') }}"> {{-- Added data attributes for Select2 --}}
                                <option value=""></option> {{-- Empty option for placeholder/allowClear --}}
                                @foreach ($positions as $position)
                                    <option value="{{ $position->id }}"
                                        {{ old('position_id', $user->position_id) == $position->id ? 'selected' : '' }}>
                                        {{ $position->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('position_id')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                         {{-- Grade Dropdown --}}
                        <div>
                            <label for="grade_id" class="block text-sm font-medium text-gray-700">{{ __('Grade') }}</label>
                             {{-- Assuming $grades is passed to the view and has 'id' and 'name' properties --}}
                             <select name="grade_id" id="grade_id"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                     data-select2 data-placeholder="{{ __('Select Grade') }}"> {{-- Added data attributes for Select2 --}}
                                <option value=""></option> {{-- Empty option for placeholder/allowClear --}}
                                @foreach ($grades as $grade)
                                    <option value="{{ $grade->id }}"
                                        {{ old('grade_id', $user->grade_id) == $grade->id ? 'selected' : '' }}>
                                        {{ $grade->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('grade_id')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Service Status Dropdown --}}
                        <div>
                            <label for="service_status" class="block text-sm font-medium text-gray-700">{{ __('Service Status') }}</label>
                            {{-- Assuming $serviceStatuses is an array of valid status values --}}
                             <select name="service_status" id="service_status"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                     data-select2 data-placeholder="{{ __('Select Service Status') }}"> {{-- Added data attributes for Select2 --}}
                                <option value=""></option> {{-- Empty option for placeholder/allowClear --}}
                                @foreach ($serviceStatuses as $statusValue)
                                    <option value="{{ $statusValue }}"
                                        {{ old('service_status', $user->service_status) == $statusValue ? 'selected' : '' }}>
                                        {{ ucfirst(str_replace('_', ' ', $statusValue)) }} {{-- Format for display --}}
                                    </option>
                                @endforeach
                            </select>
                             @error('service_status')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                         {{-- User Status Dropdown --}}
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700">{{ __('User Status') }}</label>
                            {{-- Assuming $userStatuses is an array of valid status values --}}
                             <select name="status" id="status"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                     data-select2 data-placeholder="{{ __('Select User Status') }}"> {{-- Added data attributes for Select2 --}}
                                <option value=""></option> {{-- Empty option for placeholder/allowClear --}}
                                @foreach ($userStatuses as $statusValue)
                                    <option value="{{ $statusValue }}"
                                        {{ old('status', $user->status) == $statusValue ? 'selected' : '' }}>
                                        {{ ucfirst(str_replace('_', ' ', $statusValue)) }} {{-- Format for display --}}
                                    </option>
                                @endforeach
                            </select>
                             @error('status')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Admin Role Checkbox --}}
                        <div class="md:col-span-2"> {{-- Span across two columns --}}
                            <div class="flex items-start">
                                <div class="flex items-center h-5">
                                    <input id="is_admin" name="is_admin" type="checkbox" value="1" {{ old('is_admin', $user->is_admin) ? 'checked' : '' }}
                                           class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded">
                                </div>
                                <div class="ml-3 text-sm">
                                    <label for="is_admin" class="font-medium text-gray-700">{{ __('Is Admin?') }}</label>
                                    <p class="text-gray-500">{{ __('Check if this user should have administrator privileges.') }}</p>
                                </div>
                            </div>
                             @error('is_admin')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- BPM Staff Role Checkbox --}}
                        <div class="md:col-span-2"> {{-- Span across two columns --}}
                            <div class="flex items-start">
                                <div class="flex items-center h-5">
                                    <input id="is_bpm_staff" name="is_bpm_staff" type="checkbox" value="1" {{ old('is_bpm_staff', $user->is_bpm_staff) ? 'checked' : '' }}
                                           class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded">
                                </div>
                                <div class="ml-3 text-sm">
                                    <label for="is_bpm_staff" class="font-medium text-gray-700">{{ __('Is BPM Staff?') }}</label>
                                    <p class="text-gray-500">{{ __('Check if this user belongs to the BPM staff role.') }}</p>
                                </div>
                            </div>
                             @error('is_bpm_staff')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                         {{-- Password Section (Optional: Add fields for changing password if allowed) --}}
                         {{-- This often requires separate validation and logic for security --}}
                         {{-- <div class="md:col-span-2 border-t border-gray-200 pt-6 mt-6">
                             <h3 class="text-xl font-semibold text-gray-800 mb-4">{{ __('Change Password') }}</h3>
                              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                   <div>
                                     <label for="password" class="block text-sm font-medium text-gray-700">{{ __('New Password') }}</label>
                                     <input type="password" name="password" id="password" autocomplete="new-password"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                      @error('password') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                                   </div>
                                    <div>
                                     <label for="password_confirmation" class="block text-sm font-medium text-gray-700">{{ __('Confirm New Password') }}</label>
                                     <input type="password" name="password_confirmation" id="password_confirmation" autocomplete="new-password"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                      @error('password_confirmation') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                                   </div>
                              </div>
                         </div> --}}

                    </div> {{-- End grid --}}

                    {{-- Form actions (Submit button) --}}
                    <div class="mt-8 border-t border-gray-200 pt-6">
                        <div class="flex justify-end">
                            {{-- Applied Tailwind button styles --}}
                            <button type="submit"
                                class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                {{ __('Update User') }} {{-- Translated button text --}}
                            </button>
                        </div> {{-- End button container --}}
                    </div> {{-- End form actions --}}
                </form> {{-- End form --}}
            </div> {{-- End main container --}}
        </div> {{-- End main container padding --}}
    @endsection {{-- End content section --}}

    {{-- Push script block to the 'scripts' stack defined in the layout --}}
    {{-- This script block should be placed after the layout yields the scripts stack --}}
    @push('scripts')
        {{-- Include jQuery and Select2 JS here if not already in the layout --}}
        {{-- You might need to include Select2 JS and its dependencies if you are using it for the dropdowns above --}}
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

{{-- Note: The closing </body> and </html> tags might be in your layouts.app blade file --}}
{{-- If they are not, uncomment the original closing tags below --}}
{{-- </body> --}}
{{-- </html> --}}
