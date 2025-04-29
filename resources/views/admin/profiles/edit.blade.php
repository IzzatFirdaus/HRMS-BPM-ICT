@extends('layouts.app')

@section('title', __('Edit Profile'))

@section('content')
    <div class="container mx-auto px-4 py-8">
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">{{ __('My Profile') }}</h1>
                <p class="text-sm text-gray-600 mt-2">
                    {{ __('Last updated') }}: {{ Auth::user()->updated_at->format('d M Y H:i') }}
                </p>
            </div>
            <a href="{{ route('dashboard') }}" class="btn-secondary">
                {{ __('Back to Dashboard') }}
            </a>
        </div>

        <form action="{{ route('resource-management.admin.profiles.update', Auth::user()) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="bg-white shadow-lg rounded-xl p-8 mb-8">
                <section class="mb-10">
                    <h2 class="section-heading">{{ __('Personal Information') }}</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        @include('admin.profiles.partials.input-text', [
                            'name' => 'name',
                            'label' => __('Display Name'),
                            'required' => true,
                            'value' => Auth::user()->name, // Pass value explicitly for consistency
                        ])

                        @include('admin.profiles.partials.input-text', [
                            'name' => 'full_name',
                            'label' => __('Full Name'),
                            'value' => Auth::user()->full_name, // Pass value explicitly for consistency
                        ])

                        @include('admin.profiles.partials.input-email', [
                            'name' => 'personal_email',
                            'label' => __('Personal Email'),
                            'value' => Auth::user()->personal_email, // Pass value explicitly for consistency
                        ])

                        @include('admin.profiles.partials.input-text', [
                            'name' => 'phone_number',
                            'label' => __('Phone Number'),
                            'type' => 'tel',
                            'value' => Auth::user()->phone_number ?? Auth::user()->mobile, // Pass value explicitly for consistency
                        ])
                    </div>
                </section>

                <section class="mb-10">
                    <h2 class="section-heading">{{ __('Security Settings') }}</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        @include('admin.profiles.partials.input-password', [
                            'name' => 'current_password',
                            'label' => __('Current Password'),
                            'required' => false,
                            'hint' => __('Required when changing password'),
                        ])

                        @include('admin.profiles.partials.input-password', [
                            'name' => 'password',
                            'label' => __('New Password'),
                        ])

                        @include('admin.profiles.partials.input-password', [
                            'name' => 'password_confirmation',
                            'label' => __('Confirm New Password'),
                        ])
                    </div>
                    <p class="text-sm text-gray-500 mt-4">
                        {{ __('Minimum 8 characters, include at least one number and one special character') }}
                    </p>
                </section>

                <div class="border-t pt-8 mt-8">
                    <div class="flex justify-end gap-4">
                        <button type="button" class="btn-secondary" onclick="window.location.reload()">
                            {{ __('Discard Changes') }}
                        </button>
                        <button type="submit" class="btn-primary">
                            {{ __('Save Profile') }}
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Password visibility toggles
            document.querySelectorAll('.password-toggle').forEach(function(toggle) {
                toggle.addEventListener('click', function() {
                    const input = document.getElementById(this.dataset.target);
                    const type = input.type === 'password' ? 'text' : 'password';
                    input.type = type;
                    // Toggle the icon class
                    const icon = this.querySelector('svg');
                    if (icon) {
                        // Assuming 'ti-eye' is the initial state and 'ti-eye-off' is the toggled state
                        icon.classList.toggle('ti-eye');
                        icon.classList.toggle('ti-eye-off');
                    }
                });
            });
        });
    </script>
@endpush
