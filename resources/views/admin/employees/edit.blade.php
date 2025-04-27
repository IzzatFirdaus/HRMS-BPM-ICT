{{--
    resources/views/admin/employees/edit.blade.php

    This view provides a form for administrators to edit the details of a specific employee.
    It includes fields corresponding to the fillable attributes of the App\Models\Employee model.
    It also includes a link to edit the related User account if one exists.
    Assumes an $employee object is passed to the view.
    Requires passing a collection of $contracts to the view for the contract dropdown.
--}}

{{-- Extend your main admin layout --}}
{{-- Adjust 'layouts.app' if your admin layout is different --}}
@extends('layouts.app')

@section('content')
    <div class="container mx-auto px-4 py-8">
        <div class="flex items-center justify-between mb-6">
            {{-- Page Title --}}
            <h1 class="text-2xl font-bold text-gray-800">
                {{ __('Edit Employee') }}: {{ $employee->full_name ?? 'N/A' }} {{-- Using the employee's full name --}}
            </h1>

            {{-- Back Button --}}
            {{-- Assuming a route named 'resource-management.admin.employees.index' for the employee list --}}
            <a href="{{ route('resource-management.admin.employees.index') }}"
                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                {{ __('Back to Employees List') }}
            </a>
        </div>

        {{-- Edit Employee Form --}}
        {{-- Assuming a route named 'resource-management.admin.employees.update' for handling updates --}}
        <form action="{{ route('resource-management.admin.employees.update', $employee) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="bg-white shadow-md rounded-lg p-6 mb-8">
                <h2 class="text-xl font-semibold text-gray-700 mb-4">{{ __('Employee Information') }}</h2>

                {{-- Form Fields Grid --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                    {{-- Contract Select (Requires $contracts collection) --}}
                    <div>
                        <label for="contract_id"
                            class="block text-sm font-medium text-gray-700">{{ __('Contract') }}</label>
                        <select name="contract_id" id="contract_id"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 @error('contract_id') border-red-500 @enderror">
                            <option value="">{{ __('Select Contract') }}</option>
                            {{-- IMPORTANT: Ensure $contracts is passed to the view and is a collection/array of Contract models --}}
                            @isset($contracts)
                                @foreach ($contracts as $contract)
                                    <option value="{{ $contract->id }}"
                                        {{ old('contract_id', $employee->contract_id) == $contract->id ? 'selected' : '' }}>
                                        {{ $contract->name ?? 'Contract ' . $contract->id }} {{-- Adjust to show contract name or relevant identifier --}}
                                    </option>
                                @endforeach
                            @else
                                <option value="" disabled>{{ __('Contracts not available') }}</option>
                            @endisset
                        </select>
                        @error('contract_id')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- First Name Field --}}
                    <div>
                        <label for="first_name"
                            class="block text-sm font-medium text-gray-700">{{ __('First Name') }}</label>
                        <input type="text" name="first_name" id="first_name"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 @error('first_name') border-red-500 @enderror"
                            value="{{ old('first_name', $employee->first_name) }}" required>
                        @error('first_name')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Father's Name Field --}}
                    <div>
                        <label for="father_name"
                            class="block text-sm font-medium text-gray-700">{{ __('Father\'s Name') }}</label>
                        <input type="text" name="father_name" id="father_name"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 @error('father_name') border-red-500 @enderror"
                            value="{{ old('father_name', $employee->father_name) }}">
                        @error('father_name')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Last Name Field --}}
                    <div>
                        <label for="last_name"
                            class="block text-sm font-medium text-gray-700">{{ __('Last Name') }}</label>
                        <input type="text" name="last_name" id="last_name"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 @error('last_name') border-red-500 @enderror"
                            value="{{ old('last_name', $employee->last_name) }}" required>
                        @error('last_name')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Mother's Name Field --}}
                    <div>
                        <label for="mother_name"
                            class="block text-sm font-medium text-gray-700">{{ __('Mother\'s Name') }}</label>
                        <input type="text" name="mother_name" id="mother_name"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 @error('mother_name') border-red-500 @enderror"
                            value="{{ old('mother_name', $employee->mother_name) }}">
                        @error('mother_name')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Birth Date and Place Field (Assuming text input for simplicity, could be date picker) --}}
                    <div>
                        <label for="birth_and_place"
                            class="block text-sm font-medium text-gray-700">{{ __('Birth Date and Place') }}</label>
                        <input type="text" name="birth_and_place" id="birth_and_place"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 @error('birth_and_place') border-red-500 @enderror"
                            value="{{ old('birth_and_place', $employee->birth_and_place) }}">
                        @error('birth_and_place')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- National Number (NRIC) Field --}}
                    <div>
                        <label for="national_number"
                            class="block text-sm font-medium text-gray-700">{{ __('Identification Number (NRIC)') }}</label>
                        <input type="text" name="national_number" id="national_number"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 @error('national_number') border-red-500 @enderror"
                            value="{{ old('national_number', $employee->national_number) }}">
                        @error('national_number')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Mobile Number Field --}}
                    <div>
                        <label for="mobile_number"
                            class="block text-sm font-medium text-gray-700">{{ __('Mobile Number') }}</label>
                        <input type="text" name="mobile_number" id="mobile_number"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 @error('mobile_number') border-red-500 @enderror"
                            value="{{ old('mobile_number', $employee->mobile_number) }}">
                        @error('mobile_number')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Degree Field --}}
                    <div>
                        <label for="degree" class="block text-sm font-medium text-gray-700">{{ __('Degree') }}</label>
                        <input type="text" name="degree" id="degree"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 @error('degree') border-red-500 @enderror"
                            value="{{ old('degree', $employee->degree) }}">
                        @error('degree')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Gender Select --}}
                    <div>
                        <label for="gender" class="block text-sm font-medium text-gray-700">{{ __('Gender') }}</label>
                        <select name="gender" id="gender"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 @error('gender') border-red-500 @enderror">
                            <option value="">{{ __('Select Gender') }}</option>
                            <option value="Male" {{ old('gender', $employee->gender) == 'Male' ? 'selected' : '' }}>
                                {{ __('Male') }}</option>
                            <option value="Female" {{ old('gender', $employee->gender) == 'Female' ? 'selected' : '' }}>
                                {{ __('Female') }}</option>
                            {{-- Add other options if needed --}}
                        </select>
                        @error('gender')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Address Textarea --}}
                    <div class="md:col-span-2"> {{-- Make address span two columns --}}
                        <label for="address" class="block text-sm font-medium text-gray-700">{{ __('Address') }}</label>
                        <textarea name="address" id="address" rows="3"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 @error('address') border-red-500 @enderror">{{ old('address', $employee->address) }}</textarea>
                        @error('address')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Notes Textarea --}}
                    <div class="md:col-span-2"> {{-- Make notes span two columns --}}
                        <label for="notes" class="block text-sm font-medium text-gray-700">{{ __('Notes') }}</label>
                        <textarea name="notes" id="notes" rows="3"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 @error('notes') border-red-500 @enderror">{{ old('notes', $employee->notes) }}</textarea>
                        @error('notes')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Leave Balances and Counters --}}
                    <div>
                        <label for="balance_leave_allowed"
                            class="block text-sm font-medium text-gray-700">{{ __('Balance Leave Allowed') }}</label>
                        <input type="number" name="balance_leave_allowed" id="balance_leave_allowed"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 @error('balance_leave_allowed') border-red-500 @enderror"
                            value="{{ old('balance_leave_allowed', $employee->balance_leave_allowed) }}" step="0.01">
                        {{-- Use step for decimal if applicable --}}
                        @error('balance_leave_allowed')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="max_leave_allowed"
                            class="block text-sm font-medium text-gray-700">{{ __('Max Leave Allowed') }}</label>
                        <input type="number" name="max_leave_allowed" id="max_leave_allowed"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 @error('max_leave_allowed') border-red-500 @enderror"
                            value="{{ old('max_leave_allowed', $employee->max_leave_allowed) }}" step="0.01">
                        @error('max_leave_allowed')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Assuming delay_counter and hourly_counter are stored as times or decimals --}}
                    <div>
                        <label for="delay_counter"
                            class="block text-sm font-medium text-gray-700">{{ __('Delay Counter (HH:MM)') }}</label>
                        {{-- You might need a time input or handle string formatting in controller --}}
                        <input type="text" name="delay_counter" id="delay_counter"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 @error('delay_counter') border-red-500 @enderror"
                            {{-- Show the formatted value from the accessor for display --}} value="{{ old('delay_counter', $employee->hourly_counter) }}">
                        {{-- Check if this maps to delay_counter or hourly_counter --}}
                        @error('delay_counter')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="hourly_counter"
                            class="block text-sm font-medium text-gray-700">{{ __('Hourly Counter (HH:MM)') }}</label>
                        {{-- You might need a time input or handle string formatting in controller --}}
                        <input type="text" name="hourly_counter" id="hourly_counter"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 @error('hourly_counter') border-red-500 @enderror"
                            value="{{ old('hourly_counter', $employee->hourly_counter) }}">
                        @error('hourly_counter')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Is Active Checkbox or Select --}}
                    <div>
                        <label for="is_active"
                            class="block text-sm font-medium text-gray-700">{{ __('Is Active') }}</label>
                        {{-- Using a select for True/False --}}
                        <select name="is_active" id="is_active"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 @error('is_active') border-red-500 @enderror">
                            {{-- Assuming 1 for Active, 0 for Inactive --}}
                            <option value="1" {{ old('is_active', $employee->is_active) == 1 ? 'selected' : '' }}>
                                {{ __('Yes') }}</option>
                            <option value="0" {{ old('is_active', $employee->is_active) == 0 ? 'selected' : '' }}>
                                {{ __('No') }}</option>
                        </select>
                        @error('is_active')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                </div>
            </div>

            {{-- Link to Edit Linked User Account (Optional) --}}
            {{-- Check if a related User exists and the admin has permission to update users --}}
            @if ($employee->user && Auth::user()->can('update', $employee->user))
                {{-- Check permission to update the related user --}}
                <div class="bg-white shadow-md rounded-lg p-6 mb-8">
                    <h2 class="text-xl font-semibold text-gray-700 mb-4">{{ __('Linked User Account') }}</h2>
                    <p class="text-gray-600 mb-4">
                        {{ __('This employee is linked to a user account. You can edit the user account details (like email, grade, department) separately.') }}
                    </p>
                    <a href="{{ route('resource-management.admin.users.edit', $employee->user) }}"
                        class="bg-indigo-500 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded">
                        {{ __('Edit Linked User Account') }}
                    </a>
                </div>
            @endif

            {{-- Submit Button --}}
            <div class="flex justify-end">
                <button type="submit" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                    {{ __('Update Employee') }}
                </button>
            </div>
        </form>
    </div>
@endsection
