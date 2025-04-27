{{--
    resources/views/admin/profiles/edit.blade.php

    This view provides a form for the authenticated user (likely an admin) to edit their own profile.
    It includes standard user fields and a password change section.
    It *does not* include sensitive or organization-managed fields like NRIC, grade, department, etc.
    Assumes the authenticated user is available via Auth::user() or passed as $user.
--}}

{{-- Extend your main admin layout --}}
{{-- Adjust 'layouts.app' if your admin layout is different --}}
@extends('layouts.app')

@section('content')
    <div class="container mx-auto px-4 py-8">
        <div class="flex items-center justify-between mb-6">
            {{-- Page Title --}}
            <h1 class="text-2xl font-bold text-gray-800">{{ __('Edit My Profile') }}</h1>

            {{-- Optional: Link back to a dashboard or home page --}}
            {{-- Assuming a 'dashboard' route exists --}}
            {{-- <a href="{{ route('dashboard') }}"
                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                {{ __('Back to Dashboard') }}
            </a> --}}
        </div>

        {{-- Edit Profile Form --}}
        {{-- Assuming a route named 'resource-management.admin.profiles.update' for handling updates --}}
        {{-- Pass the user ID or model to the route if not implicitly handled --}}
        <form action="{{ route('resource-management.admin.profiles.update', Auth::user()) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="bg-white shadow-md rounded-lg p-6 mb-8">
                <h2 class="text-xl font-semibold text-gray-700 mb-4">{{ __('Basic Information') }}</h2>

                {{-- Form Fields Grid --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                    {{-- Name Field (Username/Display Name) --}}
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">{{ __('Display Name') }}</label>
                        <input type="text" name="name" id="name"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 @error('name') border-red-500 @enderror"
                            value="{{ old('name', Auth::user()->name) }}" required>
                        @error('name')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Full Name Field --}}
                    <div>
                        <label for="full_name" class="block text-sm font-medium text-gray-700">{{ __('Full Name') }}</label>
                        <input type="text" name="full_name" id="full_name"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 @error('full_name') border-red-500 @enderror"
                            value="{{ old('full_name', Auth::user()->full_name) }}">
                        @error('full_name')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Personal Email Field --}}
                    <div>
                        <label for="personal_email"
                            class="block text-sm font-medium text-gray-700">{{ __('Personal Email') }}</label>
                        <input type="email" name="personal_email" id="personal_email"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 @error('personal_email') border-red-500 @enderror"
                            value="{{ old('personal_email', Auth::user()->personal_email) }}">
                        @error('personal_email')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Phone Number Field --}}
                    <div>
                        <label for="phone_number"
                            class="block text-sm font-medium text-gray-700">{{ __('Phone Number') }}</label>
                        <input type="text" name="phone_number" id="phone_number"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 @error('phone_number') border-red-500 @enderror"
                            value="{{ old('phone_number', Auth::user()->phone_number ?? Auth::user()->mobile) }}">
                        @error('phone_number')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Note: MOTAC Email, Employee ID, NRIC, User ID Assigned, Department, Position, Grade, Service Status, Appointment Type, Status are NOT included here as they are typically admin-managed. --}}

                </div>
            </div>

            {{-- Password Change Section --}}
            <div class="bg-white shadow-md rounded-lg p-6 mb-8">
                <h2 class="text-xl font-semibold text-gray-700 mb-4">{{ __('Change Password') }}</h2>
                <p class="text-sm text-gray-600 mb-4">
                    {{ __('Leave password fields blank if you do not want to change your password.') }}</p>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- Current Password Field (Required for changing password) --}}
                    <div>
                        <label for="current_password"
                            class="block text-sm font-medium text-gray-700">{{ __('Current Password') }}</label>
                        <input type="password" name="current_password" id="current_password"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 @error('current_password') border-red-500 @enderror">
                        @error('current_password')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- New Password Field --}}
                    <div>
                        <label for="password"
                            class="block text-sm font-medium text-gray-700">{{ __('New Password') }}</label>
                        <input type="password" name="password" id="password"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 @error('password') border-red-500 @enderror">
                        @error('password')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Confirm New Password Field --}}
                    <div>
                        <label for="password_confirmation"
                            class="block text-sm font-medium text-gray-700">{{ __('Confirm New Password') }}</label>
                        <input type="password" name="password_confirmation" id="password_confirmation"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                    </div>
                </div>
            </div>


            {{-- Submit Button --}}
            <div class="flex justify-end">
                <button type="submit" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                    {{ __('Update Profile') }}
                </button>
            </div>
        </form>
    </div>
@endsection
