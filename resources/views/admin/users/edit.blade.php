{{--
    resources/views/admin/users/edit.blade.php

    This view provides a form for administrators to edit the details of a specific user.
    It includes fields for both standard user information and new MOTAC-specific attributes.
    Necessary data for dropdowns (departments, grades, etc.) should be passed from the controller.
--}}

{{-- Extend your main admin layout --}}
{{-- Adjust 'layouts.app' if your admin layout is different --}}
@extends('layouts.app')

@section('content')
    <div class="container mx-auto px-4 py-8">
        <div class="flex items-center justify-between mb-6">
            {{-- Page Title --}}
            <h1 class="text-2xl font-bold text-gray-800">
                {{ __('Edit User') }}: {{ $user->name ?? ($user->full_name ?? 'N/A') }}
            </h1>

            {{-- Back Button --}}
            {{-- Assuming a route named 'resource-management.admin.users.index' for the user list --}}
            <a href="{{ route('resource-management.admin.users.index') }}"
                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                {{ __('Back to Users List') }}
            </a>
        </div>

        {{-- Edit User Form --}}
        {{-- Assuming a route named 'resource-management.admin.users.update' for handling updates --}}
        <form action="{{ route('resource-management.admin.users.update', $user) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="bg-white shadow-md rounded-lg p-6 mb-8">
                <h2 class="text-xl font-semibold text-gray-700 mb-4">{{ __('User Information') }}</h2>

                {{-- Form Fields Grid --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                    {{-- Name Field --}}
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">{{ __('Name') }}</label>
                        <input type="text" name="name" id="name"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 @error('name') border-red-500 @enderror"
                            value="{{ old('name', $user->name) }}" required>
                        @error('name')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Full Name Field --}}
                    <div>
                        <label for="full_name" class="block text-sm font-medium text-gray-700">{{ __('Full Name') }}</label>
                        <input type="text" name="full_name" id="full_name"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 @error('full_name') border-red-500 @enderror"
                            value="{{ old('full_name', $user->full_name) }}">
                        @error('full_name')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Email Field --}}
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">{{ __('Email') }}</label>
                        <input type="email" name="email" id="email"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 @error('email') border-red-500 @enderror"
                            value="{{ old('email', $user->email) }}" required>
                        @error('email')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Personal Email Field --}}
                    <div>
                        <label for="personal_email"
                            class="block text-sm font-medium text-gray-700">{{ __('Personal Email') }}</label>
                        <input type="email" name="personal_email" id="personal_email"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 @error('personal_email') border-red-500 @enderror"
                            value="{{ old('personal_email', $user->personal_email) }}">
                        @error('personal_email')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- MOTAC Email Field (New RM Attribute) --}}
                    <div>
                        <label for="motac_email"
                            class="block text-sm font-medium text-gray-700">{{ __('MOTAC Email') }}</label>
                        <input type="email" name="motac_email" id="motac_email"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 @error('motac_email') border-red-500 @enderror"
                            value="{{ old('motac_email', $user->motac_email) }}">
                        @error('motac_email')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Employee ID Field (New RM Attribute) --}}
                    <div>
                        <label for="employee_id"
                            class="block text-sm font-medium text-gray-700">{{ __('Employee ID') }}</label>
                        <input type="text" name="employee_id" id="employee_id"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 @error('employee_id') border-red-500 @enderror"
                            value="{{ old('employee_id', $user->employee_id) }}">
                        @error('employee_id')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- User ID Assigned Field (New RM Attribute) --}}
                    <div>
                        <label for="user_id_assigned"
                            class="block text-sm font-medium text-gray-700">{{ __('User ID Assigned') }}</label>
                        <input type="text" name="user_id_assigned" id="user_id_assigned"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 @error('user_id_assigned') border-red-500 @enderror"
                            value="{{ old('user_id_assigned', $user->user_id_assigned) }}">
                        @error('user_id_assigned')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Phone Number Field --}}
                    <div>
                        <label for="phone_number"
                            class="block text-sm font-medium text-gray-700">{{ __('Phone Number') }}</label>
                        <input type="text" name="phone_number" id="phone_number"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 @error('phone_number') border-red-500 @enderror"
                            value="{{ old('phone_number', $user->phone_number ?? $user->mobile) }}">
                        @error('phone_number')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Identification Number (NRIC) Field (New RM Attribute) --}}
                    {{-- Consider if this field should be editable by admin and/or protected --}}
                    <div>
                        <label for="identification_number"
                            class="block text-sm font-medium text-gray-700">{{ __('Identification Number (NRIC)') }}</label>
                        <input type="text" name="identification_number" id="identification_number"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 @error('identification_number') border-red-500 @enderror"
                            value="{{ old('identification_number', $user->identification_number) }}">
                        @error('identification_number')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Department Select (New RM Attribute - Relationship) --}}
                    {{-- IMPORTANT: Ensure $departments is passed to the view and is a collection/array of Department models/objects --}}
                    <div>
                        <label for="department_id"
                            class="block text-sm font-medium text-gray-700">{{ __('Department') }}</label>
                        <select name="department_id" id="department_id"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 @error('department_id') border-red-500 @enderror">
                            <option value="">{{ __('Select Department') }}</option>
                            @isset($departments)
                                {{-- Check if $departments variable exists and is not null --}}
                                @foreach ($departments as $department)
                                    <option value="{{ $department->id }}"
                                        {{ old('department_id', $user->department_id) == $department->id ? 'selected' : '' }}>
                                        {{ $department->name }}
                                    </option>
                                @endforeach
                            @else
                                {{-- Fallback or error message if $departments is not passed --}}
                                <option value="" disabled>{{ __('Departments not available') }}</option>
                            @endisset
                        </select>
                        @error('department_id')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Position Select (New RM Attribute - Relationship) --}}
                    {{-- IMPORTANT: Ensure $positions is passed to the view and is a collection/array of Position/Designation models/objects --}}
                    <div>
                        <label for="position_id"
                            class="block text-sm font-medium text-gray-700">{{ __('Position') }}</label>
                        <select name="position_id" id="position_id"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 @error('position_id') border-red-500 @enderror">
                            <option value="">{{ __('Select Position') }}</option>
                            @isset($positions)
                                {{-- Check if $positions variable exists and is not null --}}
                                @foreach ($positions as $position)
                                    {{-- Assuming Position/Designation model --}}
                                    <option value="{{ $position->id }}"
                                        {{ old('position_id', $user->position_id) == $position->id ? 'selected' : '' }}>
                                        {{ $position->name }}
                                    </option>
                                @endforeach
                            @else
                                {{-- Fallback or error message if $positions is not passed --}}
                                <option value="" disabled>{{ __('Positions not available') }}</option>
                            @endisset
                        </select>
                        @error('position_id')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Grade Select (New RM Attribute - Relationship) --}}
                    {{-- IMPORTANT: Ensure $grades is passed to the view and is a collection/array of Grade models/objects --}}
                    <div>
                        <label for="grade_id" class="block text-sm font-medium text-gray-700">{{ __('Grade') }}</label>
                        <select name="grade_id" id="grade_id"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 @error('grade_id') border-red-500 @enderror">
                            <option value="">{{ __('Select Grade') }}</option>
                            @isset($grades)
                                {{-- Check if $grades variable exists and is not null --}}
                                @foreach ($grades as $grade)
                                    {{-- Assuming Grade model --}}
                                    <option value="{{ $grade->id }}"
                                        {{ old('grade_id', $user->grade_id) == $grade->id ? 'selected' : '' }}>
                                        {{ $grade->name }}
                                    </option>
                                @endforeach
                            @else
                                {{-- Fallback or error message if $grades is not passed --}}
                                <option value="" disabled>{{ __('Grades not available') }}</option>
                            @endisset
                        </select>
                        @error('grade_id')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Service Status Select (New RM Attribute) --}}
                    {{-- IMPORTANT: Ensure $serviceStatuses is passed to the view or define options here --}}
                    <div>
                        <label for="service_status"
                            class="block text-sm font-medium text-gray-700">{{ __('Service Status') }}</label>
                        <select name="service_status" id="service_status"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 @error('service_status') border-red-500 @enderror">
                            <option value="">{{ __('Select Service Status') }}</option>
                            {{-- Example hardcoded options, replace with loop if passed from controller --}}
                            <option value="Active"
                                {{ old('service_status', $user->service_status) == 'Active' ? 'selected' : '' }}>
                                {{ __('Active') }}</option>
                            <option value="Inactive"
                                {{ old('service_status', $user->service_status) == 'Inactive' ? 'selected' : '' }}>
                                {{ __('Inactive') }}</option>
                            {{-- Add other options as needed --}}
                            {{--
                             @isset($serviceStatuses)
                                 @foreach ($serviceStatuses as $statusValue => $statusLabel)
                                     <option value="{{ $statusValue }}" {{ old('service_status', $user->service_status) == $statusValue ? 'selected' : '' }}>
                                         {{ __($statusLabel) }}
                                     </option>
                                 @endforeach
                             @endisset
                             --}}
                        </select>
                        @error('service_status')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Appointment Type Select (New RM Attribute) --}}
                    {{-- IMPORTANT: Ensure $appointmentTypes is passed to the view or define options here --}}
                    <div>
                        <label for="appointment_type"
                            class="block text-sm font-medium text-gray-700">{{ __('Appointment Type') }}</label>
                        <select name="appointment_type" id="appointment_type"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 @error('appointment_type') border-red-500 @enderror">
                            <option value="">{{ __('Select Appointment Type') }}</option>
                            {{-- Example hardcoded options, replace with loop if passed from controller --}}
                            <option value="Permanent"
                                {{ old('appointment_type', $user->appointment_type) == 'Permanent' ? 'selected' : '' }}>
                                {{ __('Permanent') }}</option>
                            <option value="Contract"
                                {{ old('appointment_type', $user->appointment_type) == 'Contract' ? 'selected' : '' }}>
                                {{ __('Contract') }}</option>
                            {{-- Add other options as needed --}}
                            {{--
                             @isset($appointmentTypes)
                                 @foreach ($appointmentTypes as $typeValue => $typeLabel)
                                     <option value="{{ $typeValue }}" {{ old('appointment_type', $user->appointment_type) == $typeValue ? 'selected' : '' }}>
                                         {{ __($typeLabel) }}
                                     </option>
                                 @endforeach
                             @endisset
                              --}}
                        </select>
                        @error('appointment_type')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @endror
                        </div>

                        {{-- Account Status Select --}}
                        <div>
                            <label for="status"
                                class="block text-sm font-medium text-gray-700">{{ __('Account Status') }}</label>
                            <select name="status" id="status"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 @error('status') border-red-500 @enderror">
                                <option value="Active" {{ old('status', $user->status) == 'Active' ? 'selected' : '' }}>
                                    {{ __('Active') }}</option>
                                <option value="Inactive" {{ old('status', $user->status) == 'Inactive' ? 'selected' : '' }}>
                                    {{ __('Inactive') }}</option>
                                {{-- Add other status options if applicable --}}
                            </select>
                            @error('status')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Password Fields (Optional - uncomment if admin can change password here) --}}
                        {{-- You might want separate functionality for password reset/change --}}
                        {{--
                    <div class="col-span-1 md:col-span-2 border-t pt-6 mt-6">
                        <h3 class="text-lg font-semibold text-gray-700 mb-4">{{ __('Change Password (Optional)') }}</h3>
                         <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                             <div>
                                 <label for="password" class="block text-sm font-medium text-gray-700">{{ __('New Password') }}</label>
                                 <input type="password" name="password" id="password"
                                     class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 @error('password') border-red-500 @enderror">
                                 @error('password')
                                     <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                 @enderror
                             </div>
                             <div>
                                 <label for="password_confirmation" class="block text-sm font-medium text-gray-700">{{ __('Confirm New Password') }}</label>
                                 <input type="password" name="password_confirmation" id="password_confirmation"
                                     class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                             </div>
                         </div>
                    </div>
                    --}}

                    </div>
                </div>

                {{-- Submit Button --}}
                <div class="flex justify-end">
                    <button type="submit" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                        {{ __('Update User') }}
                    </button>
                </div>
            </form>
        </div>
    @endsection
