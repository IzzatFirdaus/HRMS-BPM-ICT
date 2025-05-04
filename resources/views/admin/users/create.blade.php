{{--
    resources/views/admin/users/create.blade.php

    This Blade view file provides the form for creating a new User in the Admin panel.
    It includes fields for both standard user details and MOTAC-specific fields.
    It is accessed via the route 'admin.users.create' and uses the
    App\Http\Controllers\Admin\UserController@create controller method.
    This file uses Tailwind CSS classes for styling, aligning with the HRMS UI style.
    Assumes a layout file that provides the basic HTML structure (@extends('layouts.app')).
    Assumes Tailwind CSS is configured (via CDN or build process).
    Assumes the controller passes $departments, $positions, $grades, $serviceStatuses, $userStatuses, and $roles.
--}}

{{-- Extend your main admin layout --}}
@extends('layouts.app')

{{-- Set the page title --}}
@section('title', __('Create New User'))

{{-- Define the content section --}}
@section('content')

    {{-- Main container for the content --}}
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 py-8"> {{-- Adjust max-w-* for desired form width --}}

        {{-- Header section with title --}}
        <div class="flex items-center justify-between mb-6">
            {{-- Page Title --}}
            <h1 class="text-2xl font-bold text-gray-800">{{ __('Create New User') }}</h1> {{-- Translated heading --}}
        </div> {{-- End header section --}}

        {{-- Display validation errors --}}
        @if ($errors->any())
            {{-- Applied Tailwind classes for an error alert box --}}
            <div class="bg-red-100 border border-red-200 text-red-800 p-4 rounded-md mb-4">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- User Creation Form Card --}}
        {{-- Container for the form, using Tailwind classes for background, shadow, rounded corners, and padding --}}
        <div class="bg-white shadow-md rounded-lg p-6">

            {{-- The form for creating a user --}}
            {{-- Submits to the 'admin.users.store' route --}}
            {{-- Assumes the AdminUserController has a store method --}}
            <form action="{{ route('admin.users.store') }}" method="POST">
                @csrf {{-- CSRF token for security --}}

                {{-- --- Standard User Details --- --}}
                <h2 class="text-xl font-semibold text-gray-700 mb-4">{{ __('Account Details') }}</h2>

                {{-- Name Field --}}
                <div class="mb-4">
                    <label for="name" class="block text-gray-700 text-sm font-bold mb-2">{{ __('Name') }}:</label>
                    <input type="text" name="name" id="name"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('name') border-red-500 @enderror"
                        value="{{ old('name') }}" required>
                    @error('name')
                        <p class="text-red-500 text-xs italic">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Email Field --}}
                <div class="mb-4">
                    <label for="email" class="block text-gray-700 text-sm font-bold mb-2">{{ __('Email') }}:</label>
                    <input type="email" name="email" id="email"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('email') border-red-500 @enderror"
                        value="{{ old('email') }}" required>
                    @error('email')
                        <p class="text-red-500 text-xs italic">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Password Field --}}
                <div class="mb-4">
                    <label for="password" class="block text-gray-700 text-sm font-bold mb-2">{{ __('Password') }}:</label>
                    <input type="password" name="password" id="password"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('password') border-red-500 @enderror"
                        required>
                    @error('password')
                        <p class="text-red-500 text-xs italic">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Confirm Password Field --}}
                <div class="mb-6">
                    <label for="password_confirmation"
                        class="block text-gray-700 text-sm font-bold mb-2">{{ __('Confirm Password') }}:</label>
                    <input type="password" name="password_confirmation" id="password_confirmation"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                        required>
                </div>


                {{-- --- MOTAC Specific User Details (Based on Migration) --- --}}
                <h2 class="text-xl font-semibold text-gray-700 mb-4 mt-6">{{ __('MOTAC Details') }}</h2>

                {{-- Full Name Field --}}
                <div class="mb-4">
                    <label for="full_name"
                        class="block text-gray-700 text-sm font-bold mb-2">{{ __('Full Name') }}:</label>
                    <input type="text" name="full_name" id="full_name"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('full_name') border-red-500 @enderror"
                        value="{{ old('full_name') }}"> {{-- Made nullable based on migration --}}
                    @error('full_name')
                        <p class="text-red-500 text-xs italic">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Employee ID Field (assuming external ID) --}}
                <div class="mb-4">
                    <label for="employee_id"
                        class="block text-gray-700 text-sm font-bold mb-2">{{ __('Employee ID') }}:</label>
                    <input type="text" name="employee_id" id="employee_id"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('employee_id') border-red-500 @enderror"
                        value="{{ old('employee_id') }}"> {{-- Made nullable based on migration --}}
                    @error('employee_id')
                        <p class="text-red-500 text-xs italic">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Personal Email Field --}}
                <div class="mb-4">
                    <label for="personal_email"
                        class="block text-gray-700 text-sm font-bold mb-2">{{ __('Personal Email') }}:</label>
                    <input type="email" name="personal_email" id="personal_email"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('personal_email') border-red-500 @enderror"
                        value="{{ old('personal_email') }}"> {{-- Made nullable based on migration --}}
                    @error('personal_email')
                        <p class="text-red-500 text-xs italic">{{ $message }}</p>
                    @enderror
                </div>

                {{-- MOTAC Email Field --}}
                <div class="mb-4">
                    <label for="motac_email"
                        class="block text-gray-700 text-sm font-bold mb-2">{{ __('MOTAC Email') }}:</label>
                    <input type="email" name="motac_email" id="motac_email"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('motac_email') border-red-500 @enderror"
                        value="{{ old('motac_email') }}"> {{-- Made nullable based on migration --}}
                    @error('motac_email')
                        <p class="text-red-500 text-xs italic">{{ $message }}</p>
                    @enderror
                </div>

                {{-- NRIC Field --}}
                <div class="mb-4">
                    <label for="nric" class="block text-gray-700 text-sm font-bold mb-2">{{ __('NRIC') }}:</label>
                    <input type="text" name="nric" id="nric"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('nric') border-red-500 @enderror"
                        value="{{ old('nric') }}"> {{-- Made unique and nullable based on migration --}}
                    @error('nric')
                        <p class="text-red-500 text-xs italic">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Mobile Number Field --}}
                <div class="mb-4">
                    <label for="mobile_number"
                        class="block text-gray-700 text-sm font-bold mb-2">{{ __('Mobile Number') }}:</label>
                    <input type="text" name="mobile_number" id="mobile_number"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('mobile_number') border-red-500 @enderror"
                        value="{{ old('mobile_number') }}"> {{-- Made unique and nullable based on migration --}}
                    @error('mobile_number')
                        <p class="text-red-500 text-xs italic">{{ $message }}</p>
                    @enderror
                </div>


                {{-- Department Select --}}
                <div class="mb-4">
                    <label for="department_id"
                        class="block text-gray-700 text-sm font-bold mb-2">{{ __('Department') }}:</label>
                    {{-- Assumes $departments collection is passed from controller --}}
                    <select name="department_id" id="department_id"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('department_id') border-red-500 @enderror">
                        <option value="">{{ __('Select Department') }}</option>
                        @foreach ($departments as $department)
                            <option value="{{ $department->id }}"
                                {{ old('department_id') == $department->id ? 'selected' : '' }}>
                                {{ $department->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('department_id')
                        <p class="text-red-500 text-xs italic">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Position Select --}}
                <div class="mb-4">
                    <label for="position_id"
                        class="block text-gray-700 text-sm font-bold mb-2">{{ __('Position') }}:</label>
                    {{-- Assumes $positions collection is passed from controller --}}
                    <select name="position_id" id="position_id"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('position_id') border-red-500 @enderror">
                        <option value="">{{ __('Select Position') }}</option>
                        @foreach ($positions as $position)
                            <option value="{{ $position->id }}"
                                {{ old('position_id') == $position->id ? 'selected' : '' }}>
                                {{ $position->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('position_id')
                        <p class="text-red-500 text-xs italic">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Grade Select --}}
                <div class="mb-4">
                    <label for="grade_id" class="block text-gray-700 text-sm font-bold mb-2">{{ __('Grade') }}:</label>
                    {{-- Assumes $grades collection is passed from controller --}}
                    <select name="grade_id" id="grade_id"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('grade_id') border-red-500 @enderror">
                        <option value="">{{ __('Select Grade') }}</option>
                        @foreach ($grades as $grade)
                            <option value="{{ $grade->id }}" {{ old('grade_id') == $grade->id ? 'selected' : '' }}>
                                {{ $grade->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('grade_id')
                        <p class="text-red-500 text-xs italic">{{ $message }}</p>
                    @enderror
                </div>

                {{-- User ID Assigned Field (nullable unique string) --}}
                <div class="mb-4">
                    <label for="user_id_assigned"
                        class="block text-gray-700 text-sm font-bold mb-2">{{ __('User ID Assigned') }}:</label>
                    <input type="text" name="user_id_assigned" id="user_id_assigned"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('user_id_assigned') border-red-500 @enderror"
                        value="{{ old('user_id_assigned') }}"> {{-- Made nullable unique based on migration --}}
                    @error('user_id_assigned')
                        <p class="text-red-500 text-xs italic">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Service Status Select (enum) --}}
                <div class="mb-4">
                    <label for="service_status"
                        class="block text-gray-700 text-sm font-bold mb-2">{{ __('Service Status') }}:</label>
                    {{-- Assumes $serviceStatuses array is passed from controller, matching enum values --}}
                    <select name="service_status" id="service_status"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('service_status') border-red-500 @enderror">
                        <option value="">{{ __('Select Service Status') }}</option>
                        {{-- Loop through the enum values passed from the controller --}}
                        @foreach ($serviceStatuses as $status)
                            <option value="{{ $status }}" {{ old('service_status') == $status ? 'selected' : '' }}>
                                {{ __(ucfirst($status)) }} {{-- Translate and capitalize the status value --}}
                            </option>
                        @endforeach
                    </select>
                    @error('service_status')
                        <p class="text-red-500 text-xs italic">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Appointment Type Field (nullable string) --}}
                <div class="mb-4">
                    <label for="appointment_type"
                        class="block text-gray-700 text-sm font-bold mb-2">{{ __('Appointment Type') }}:</label>
                    <input type="text" name="appointment_type" id="appointment_type"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('appointment_type') border-red-500 @enderror"
                        value="{{ old('appointment_type') }}"> {{-- Made nullable based on migration --}}
                    @error('appointment_type')
                        <p class="text-red-500 text-xs italic">{{ $message }}</p>
                    @enderror
                </div>

                {{-- User Status Select (enum) --}}
                <div class="mb-6">
                    <label for="status"
                        class="block text-gray-700 text-sm font-bold mb-2">{{ __('User Status') }}:</label>
                    {{-- Assumes $userStatuses array is passed from controller, matching enum values --}}
                    <select name="status" id="status"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('status') border-red-500 @enderror">
                        <option value="">{{ __('Select User Status') }}</option>
                        {{-- Loop through the enum values passed from the controller --}}
                        @foreach ($userStatuses as $status)
                            <option value="{{ $status }}" {{ old('status') == $status ? 'selected' : '' }}>
                                {{ __(ucfirst($status)) }} {{-- Translate and capitalize the status value --}}
                            </option>
                        @endforeach
                    </select>
                    @error('status')
                        <p class="text-red-500 text-xs italic">{{ $message }}</p>
                    @enderror
                </div>

                {{-- --- Role Assignment (Assuming Spatie Roles) --- --}}
                <h2 class="text-xl font-semibold text-gray-700 mb-4 mt-6">{{ __('Role Assignment') }}</h2>

                {{-- Roles Checkboxes/Select --}}
                <div class="mb-6">
                    <label class="block text-gray-700 text-sm font-bold mb-2">{{ __('Assign Roles') }}:</label>
                    {{-- Assumes $roles collection is passed from controller, containing all available roles --}}
                    {{-- Use checkboxes for multi-select roles --}}
                    @forelse ($roles ?? [] as $role)
                        <div class="flex items-center mb-2">
                            <input type="checkbox" name="roles[]" id="role_{{ $role->id }}"
                                value="{{ $role->name }}"
                                class="form-checkbox h-5 w-5 text-blue-600 rounded focus:ring-blue-500 @error('roles') border-red-500 @enderror"
                                {{-- Check old roles if form submission failed --}} {{ in_array($role->name, old('roles', [])) ? 'checked' : '' }}>
                            {{-- Note: old('roles', []) provides an empty array default if no old roles exist --}}
                            <label for="role_{{ $role->id }}" class="ml-2 text-gray-700">{{ $role->name }}</label>
                        </div>
                    @empty
                        <p class="text-gray-500 text-sm">{{ __('No roles available to assign.') }}</p>
                    @endforelse
                    @error('roles')
                        <p class="text-red-500 text-xs italic">{{ $message }}</p>
                    @enderror
                </div>


                {{-- --- Submit Button --- --}}
                <div class="flex items-center justify-between">
                    <button type="submit"
                        class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                        {{ __('Create User') }} {{-- Translated button text --}}
                    </button>
                    {{-- Optional: Cancel button --}}
                    <a href="{{ route('admin.users.index') }}"
                        class="inline-block align-baseline font-bold text-sm text-blue-500 hover:text-blue-800">
                        {{ __('Cancel') }} {{-- Translated link text --}}
                    </a>
                </div>
            </form>

        </div> {{-- End bg-white card --}}
    </div> {{-- End main container --}}

@endsection {{-- End content section --}}
