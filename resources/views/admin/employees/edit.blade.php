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
        <div class="flex flex-wrap items-center justify-between mb-6 gap-4"> {{-- Added flex-wrap and gap for better responsiveness --}}
            {{-- Page Title --}}
            <h1 class="text-2xl font-bold text-gray-800">
                {{ __('Edit Employee') }}: {{ $employee->full_name ?? 'N/A' }} {{-- Using the employee's full name --}}
            </h1>

            {{-- Back Button --}}
            {{-- Assuming a route named 'resource-management.admin.employees.index' for the employee list --}}
            {{-- Converted button styling to more standard Tailwind for buttons --}}
            <a href="{{ route('resource-management.admin.employees.index') }}"
                class="inline-flex items-center justify-center px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold rounded shadow-sm focus:outline-none focus:ring focus:ring-gray-400 transition">
                {{ __('Back to Employees List') }}
            </a>
        </div>

        {{-- Display session-based success or error messages (flash messages) --}}
        @if (session()->has('success'))
            <div class="bg-green-100 border border-green-200 text-green-800 p-4 rounded-md mb-4">
                {{ session('success') }}
            </div>
        @endif

        @if (session()->has('error'))
            <div class="bg-red-100 border border-red-200 text-red-800 p-4 rounded-md mb-4">
                {{ session('error') }}
            </div>
        @endif

        {{-- Edit Employee Form --}}
        {{-- Assuming a route named 'resource-management.admin.employees.update' for handling updates --}}
        <form action="{{ route('resource-management.admin.employees.update', $employee) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="bg-white shadow-md rounded-lg p-6 mb-8">
                <h2 class="text-xl font-semibold text-gray-700 mb-4">{{ __('Employee Information') }}</h2>

                {{-- Form Fields Grid --}}
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6"> {{-- Added lg:grid-cols-3 for wider screens --}}

                    {{-- Contract Select (Requires $contracts collection) --}}
                    <div>
                        <label for="contract_id"
                            class="block text-sm font-medium text-gray-700">{{ __('Contract') }}</label>
                        <select name="contract_id" id="contract_id"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 @error('contract_id') border-red-500 @enderror">
                            {{-- Adjusted focus ring color --}}
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
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 @error('first_name') border-red-500 @enderror"
                            {{-- Adjusted focus ring color --}} value="{{ old('first_name', $employee->first_name) }}"
                            placeholder="{{ __('Enter first name') }}" {{-- Added placeholder --}} required>
                        @error('first_name')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Father's Name Field --}}
                    <div>
                        <label for="father_name"
                            class="block text-sm font-medium text-gray-700">{{ __('Father\'s Name') }}</label>
                        <input type="text" name="father_name" id="father_name"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 @error('father_name') border-red-500 @enderror"
                            {{-- Adjusted focus ring color --}} value="{{ old('father_name', $employee->father_name) }}"
                            placeholder="{{ __('Enter father\'s name') }}"> {{-- Added placeholder --}}
                        @error('father_name')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Last Name Field --}}
                    <div>
                        <label for="last_name"
                            class="block text-sm font-medium text-gray-700">{{ __('Last Name') }}</label>
                        <input type="text" name="last_name" id="last_name"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 @error('last_name') border-red-500 @enderror"
                            {{-- Adjusted focus ring color --}} value="{{ old('last_name', $employee->last_name) }}"
                            placeholder="{{ __('Enter last name') }}" {{-- Added placeholder --}} required>
                        @error('last_name')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Mother's Name Field --}}
                    <div>
                        <label for="mother_name"
                            class="block text-sm font-medium text-gray-700">{{ __('Mother\'s Name') }}</label>
                        <input type="text" name="mother_name" id="mother_name"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 @error('mother_name') border-red-500 @enderror"
                            {{-- Adjusted focus ring color --}} value="{{ old('mother_name', $employee->mother_name) }}"
                            placeholder="{{ __('Enter mother\'s name') }}"> {{-- Added placeholder --}}
                        @error('mother_name')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Birth Date and Place Field (Assuming text input, could be date picker) --}}
                    <div>
                        <label for="birth_and_place"
                            class="block text-sm font-medium text-gray-700">{{ __('Birth Date and Place') }}</label>
                        <input type="text" name="birth_and_place" id="birth_and_place"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 @error('birth_and_place') border-red-500 @enderror"
                            {{-- Adjusted focus ring color --}} value="{{ old('birth_and_place', $employee->birth_and_place) }}"
                            placeholder="{{ __('e.g., 1990-01-01, City, Country') }}"> {{-- Added placeholder --}}
                        {{-- TODO: Consider using a Datepicker here (like Flatpickr) and potentially separate place field --}}
                        @error('birth_and_place')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- National Number (NRIC) Field --}}
                    <div>
                        <label for="national_number"
                            class="block text-sm font-medium text-gray-700">{{ __('Identification Number (NRIC)') }}</label>
                        <input type="text" name="national_number" id="national_number"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 @error('national_number') border-red-500 @enderror"
                            {{-- Adjusted focus ring color --}} value="{{ old('national_number', $employee->national_number) }}"
                            placeholder="{{ __('e.g., 02000000000') }}"> {{-- Added placeholder --}}
                        @error('national_number')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Mobile Number Field --}}
                    <div>
                        <label for="mobile_number"
                            class="block text-sm font-medium text-gray-700">{{ __('Mobile Number') }}</label>
                        <input type="text" name="mobile_number" id="mobile_number"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 @error('mobile_number') border-red-500 @enderror"
                            {{-- Adjusted focus ring color --}} value="{{ old('mobile_number', $employee->mobile_number) }}"
                            placeholder="{{ __('e.g., 900000000') }}"> {{-- Added placeholder --}}
                        @error('mobile_number')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Degree Field --}}
                    <div>
                        <label for="degree" class="block text-sm font-medium text-gray-700">{{ __('Degree') }}</label>
                        <input type="text" name="degree" id="degree"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 @error('degree') border-red-500 @enderror"
                            {{-- Adjusted focus ring color --}} value="{{ old('degree', $employee->degree) }}"
                            placeholder="{{ __('e.g., Bachelor\'s Degree') }}"> {{-- Added placeholder --}}
                        @error('degree')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Gender Select --}}
                    <div>
                        <label for="gender" class="block text-sm font-medium text-gray-700">{{ __('Gender') }}</label>
                        <select name="gender" id="gender"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 @error('gender') border-red-500 @enderror">
                            {{-- Adjusted focus ring color --}}
                            <option value="">{{ __('Select Gender') }}</option>
                            {{-- Assuming 'Male' and 'Female' are the values stored in the database --}}
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
                    <div class="md:col-span-2 lg:col-span-3"> {{-- Make address span all columns on medium and large screens --}}
                        <label for="address" class="block text-sm font-medium text-gray-700">{{ __('Address') }}</label>
                        <textarea name="address" id="address" rows="3"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 @error('address') border-red-500 @enderror"
                            {{-- Adjusted focus ring color --}} placeholder="{{ __('Enter full address') }}">{{ old('address', $employee->address) }}</textarea> {{-- Added placeholder --}}
                        @error('address')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Notes Textarea --}}
                    <div class="md:col-span-2 lg:col-span-3"> {{-- Make notes span all columns on medium and large screens --}}
                        <label for="notes" class="block text-sm font-medium text-gray-700">{{ __('Notes') }}</label>
                        <textarea name="notes" id="notes" rows="3"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 @error('notes') border-red-500 @enderror"
                            {{-- Adjusted focus ring color --}} placeholder="{{ __('Any additional notes about the employee') }}">{{ old('notes', $employee->notes) }}</textarea> {{-- Added placeholder --}}
                        @error('notes')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Leave Balances and Counters --}}
                    {{-- Wrapped in a col-span-full div to keep them grouped --}}
                    <div class="col-span-full">
                        <h3 class="text-lg font-semibold text-gray-700 mb-4 mt-2">{{ __('Leave Balances & Counters') }}
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6"> {{-- Subgrid for these fields --}}
                            <div>
                                <label for="balance_leave_allowed"
                                    class="block text-sm font-medium text-gray-700">{{ __('Balance Leave Allowed') }}</label>
                                <input type="number" name="balance_leave_allowed" id="balance_leave_allowed"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 @error('balance_leave_allowed') border-red-500 @enderror"
                                    {{-- Adjusted focus ring color --}}
                                    value="{{ old('balance_leave_allowed', $employee->balance_leave_allowed) }}"
                                    placeholder="0.00" {{-- Added placeholder --}} step="0.01"> {{-- Use step for decimal if applicable --}}
                                @error('balance_leave_allowed')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="max_leave_allowed"
                                    class="block text-sm font-medium text-gray-700">{{ __('Max Leave Allowed') }}</label>
                                <input type="number" name="max_leave_allowed" id="max_leave_allowed"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 @error('max_leave_allowed') border-red-500 @enderror"
                                    {{-- Adjusted focus ring color --}}
                                    value="{{ old('max_leave_allowed', $employee->max_leave_allowed) }}"
                                    placeholder="0.00" {{-- Added placeholder --}} step="0.01">
                                @error('max_leave_allowed')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Assuming delay_counter and hourly_counter are stored as times or decimals --}}
                            <div>
                                <label for="delay_counter"
                                    class="block text-sm font-medium text-gray-700">{{ __('Delay Counter (HH:MM)') }}</label>
                                {{-- You might need a time input or handle string formatting in controller/mutator --}}
                                <input type="text" name="delay_counter" id="delay_counter"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 @error('delay_counter') border-red-500 @enderror"
                                    {{-- Adjusted focus ring color --}} {{-- Show the formatted value from the accessor for display --}}
                                    value="{{ old('delay_counter', $employee->delay_counter_formatted ?? $employee->delay_counter) }}"
                                    {{-- Added fallback and assumed a formatted accessor exists --}} placeholder="00:00"> {{-- Added placeholder --}}
                                {{-- Check if this maps to delay_counter or hourly_counter --}}
                                {{-- TODO: Consider using a Time input type or a JS library for time input --}}
                                @error('delay_counter')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="hourly_counter"
                                    class="block text-sm font-medium text-gray-700">{{ __('Hourly Counter (HH:MM)') }}</label>
                                {{-- You might need a time input or handle string formatting in controller/mutator --}}
                                <input type="text" name="hourly_counter" id="hourly_counter"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 @error('hourly_counter') border-red-500 @enderror"
                                    {{-- Adjusted focus ring color --}}
                                    value="{{ old('hourly_counter', $employee->hourly_counter_formatted ?? $employee->hourly_counter) }}"
                                    {{-- Added fallback and assumed a formatted accessor exists --}} placeholder="00:00"> {{-- Added placeholder --}}
                                {{-- TODO: Consider using a Time input type or a JS library for time input --}}
                                @error('hourly_counter')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div> {{-- End subgrid --}}
                    </div> {{-- End col-span-full --}}


                    {{-- Is Active Checkbox or Select --}}
                    {{-- Wrapped in a div to keep consistent grid structure --}}
                    <div>
                        <label for="is_active"
                            class="block text-sm font-medium text-gray-700">{{ __('Is Active') }}</label>
                        {{-- Using a select for True/False --}}
                        <select name="is_active" id="is_active"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 @error('is_active') border-red-500 @enderror">
                            {{-- Adjusted focus ring color --}}
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

                    {{-- TODO: Add fields for relationships if needed (e.g., Department, Position, Center, Grade) --}}
                    {{-- These would typically be select dropdowns, possibly requiring a JS library like Select2 --}}
                    {{-- Example Select for Department:
                    <div>
                         <label for="department_id" class="block text-sm font-medium text-gray-700">{{ __('Department') }}</label>
                         <select name="department_id" id="department_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 @error('department_id') border-red-500 @enderror">
                             <option value="">{{ __('Select Department') }}</option>
                             @isset($departments) // Assuming $departments is passed
                                 @foreach ($departments as $department)
                                     <option value="{{ $department->id }}" {{ old('department_id', $employee->department_id) == $department->id ? 'selected' : '' }}>
                                         {{ $department->name }}
                                     </option>
                                 @endforeach
                             @endisset
                         </select>
                         @error('department_id')
                             <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                         @enderror
                     </div>
                    --}}

                </div> {{-- End Form Fields Grid --}}
            </div> {{-- End bg-white card --}}

            {{-- Link to Edit Linked User Account (Optional) --}}
            {{-- Check if a related User exists and the admin has permission to update users --}}
            @if ($employee->user && Auth::user()->can('update', $employee->user))
                {{-- Check permission to update the related user --}}
                <div class="bg-white shadow-md rounded-lg p-6 mb-8">
                    <h2 class="text-xl font-semibold text-gray-700 mb-4">{{ __('Linked User Account') }}</h2>
                    <p class="text-gray-600 mb-4">
                        {{ __('This employee is linked to a user account. You can edit the user account details (like email, grade, department) separately.') }}
                    </p>
                    {{-- Converted button styling to more standard Tailwind for buttons --}}
                    <a href="{{ route('resource-management.admin.users.edit', $employee->user) }}"
                        class="inline-flex items-center justify-center px-4 py-2 bg-indigo-500 hover:bg-indigo-700 text-white font-bold rounded shadow-sm focus:outline-none focus:ring focus:ring-indigo-400 transition">
                        {{ __('Edit Linked User Account') }}
                    </a>
                </div>
            @endif

            {{-- Submit Button (Aligned to the right) --}}
            <div class="flex justify-end">
                {{-- Converted button styling to more standard Tailwind for buttons --}}
                <button type="submit"
                    class="inline-flex items-center justify-center px-6 py-3 bg-green-500 hover:bg-green-700 text-white font-bold rounded shadow focus:outline-none focus:ring focus:ring-green-400 transition">
                    {{-- Adjusted padding --}}
                    {{ __('Update Employee') }}
                </button>
            </div>
        </form>
    </div>
@endsection
