{{--
    resources/views/admin/users/show.blade.php

    This view displays the details of a specific user for administrators.
    It includes both standard user information and new MOTAC-specific attributes.
    Authorization checks are used to protect sensitive data.
--}}

{{-- Extend your main admin layout --}}
{{-- Adjust 'layouts.app' if your admin layout is different --}}
@extends('layouts.app')

@section('title', __('User Details') . ': ' . ($user->name ?? ($user->full_name ?? 'N/A'))) {{-- Set the page title using translation --}}

@section('content')
    <div class="container mx-auto px-4 py-8">
        <div class="flex items-center justify-between mb-6">
            {{-- Page Title --}}
            <h1 class="text-2xl font-bold text-gray-800">
                {{ __('User Details') }}: {{ $user->name ?? ($user->full_name ?? 'N/A') }}
            </h1>

            {{-- Back Button --}}
            {{-- Assuming a route named 'admin.users.index' for the user list --}}
            <a href="{{ route('resource-management.admin.users.index') }}"
                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition">
                {{-- Added focus/transition --}}
                {{ __('Back to Users List') }}
            </a>
        </div>

        {{-- User Details Card --}}
        <div class="bg-white shadow-md rounded-lg p-6 mb-8">
            <div class="flex flex-col md:flex-row items-center md:items-start">
                {{-- Profile Photo --}}
                <div class="md:mr-8 mb-6 md:mb-0">
                    <img src="{{ $user->profile_photo_url ?? asset('path/to/default/profile.png') }}"
                        alt="{{ $user->name ?? 'User' }} Profile Photo"
                        class="w-32 h-32 rounded-full object-cover border-4 border-blue-200">
                </div>

                {{-- User Information --}}
                <div class="flex-grow">
                    <h2 class="text-xl font-semibold text-gray-700 mb-4">{{ __('Basic Information') }}</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        {{-- Standard User Fields --}}
                        <div>
                            <p class="text-sm font-medium text-gray-500">{{ __('Name') }}:</p>
                            <p class="mt-1 text-gray-900">{{ $user->name ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">{{ __('Full Name') }}:</p>
                            <p class="mt-1 text-gray-900">{{ $user->full_name ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">{{ __('Email') }}:</p>
                            <p class="mt-1 text-gray-900">{{ $user->email ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">{{ __('Personal Email') }}:</p>
                            <p class="mt-1 text-gray-900">{{ $user->personal_email ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">{{ __('MOTAC Email') }}:</p>
                            <p class="mt-1 text-gray-900">{{ $user->motac_email ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">{{ __('Employee ID') }}:</p>
                            <p class="mt-1 text-gray-900">{{ $user->employee_id ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">{{ __('User ID Assigned') }}:</p>
                            <p class="mt-1 text-gray-900">{{ $user->user_id_assigned ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">{{ __('Phone Number') }}:</p>
                            <p class="mt-1 text-gray-900">{{ $user->phone_number ?? ($user->mobile ?? 'N/A') }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">{{ __('Status') }}:</p>
                            <p class="mt-1 text-gray-900">{{ __($user->status ?? 'N/A') }}</p> {{-- Translated status --}}
                        </div>

                        {{-- New MOTAC Specific Fields --}}
                        {{-- Use the 'viewSensitiveData' policy to protect NRIC --}}
                        @can('viewSensitiveData', $user)
                            <div>
                                <p class="text-sm font-medium text-gray-500">{{ __('Identification Number (NRIC)') }}:</p>
                                <p class="mt-1 text-gray-900">{{ $user->identification_number ?? 'N/A' }}</p>
                            </div>
                        @endcan

                        {{-- Relationships --}}
                        <div>
                            <p class="text-sm font-medium text-gray-500">{{ __('Department') }}:</p>
                            <p class="mt-1 text-gray-900">{{ $user->department->name ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">{{ __('Position') }}:</p>
                            {{-- Assuming 'position' relationship exists and links to the Designation model --}}
                            <p class="mt-1 text-gray-900">{{ $user->position->name ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">{{ __('Grade') }}:</p>
                            {{-- Assuming 'grade' relationship exists --}}
                            <p class="mt-1 text-gray-900">{{ $user->grade->name ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">{{ __('Service Status') }}:</p>
                            <p class="mt-1 text-gray-900">{{ __($user->service_status ?? 'N/A') }}</p>
                            {{-- Translated status --}}
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">{{ __('Appointment Type') }}:</p>
                            <p class="mt-1 text-gray-900">{{ __($user->appointment_type ?? 'N/A') }}</p>
                            {{-- Translated type --}}
                        </div>

                        {{-- Audit Fields (Protected by policy if needed) --}}
                        {{-- Assuming 'viewAuditData' policy or similar --}}
                        {{-- @can('viewAuditData', $user) --}}
                        <div>
                            <p class="text-sm font-medium text-gray-500">{{ __('Created By') }}:</p>
                            {{-- Assuming 'createdBy' relationship exists on User model --}}
                            <p class="mt-1 text-gray-900">{{ $user->createdBy->name ?? __('System') }}</p>
                            {{-- Translated System --}}
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">{{ __('Updated By') }}:</p>
                            {{-- Assuming 'updatedBy' relationship exists on User model --}}
                            <p class="mt-1 text-gray-900">{{ $user->updatedBy->name ?? 'N/A' }}</p>
                        </div>
                        @if ($user->deleted_by)
                            <div>
                                <p class="text-sm font-medium text-gray-500">{{ __('Deleted By') }}:</p>
                                {{-- Assuming 'deletedBy' relationship exists on User model --}}
                                <p class="mt-1 text-gray-900">{{ $user->deletedBy->name ?? 'N/A' }}</p>
                            </div>
                        @endif
                        <div>
                            <p class="text-sm font-medium text-gray-500">{{ __('Created At') }}:</p>
                            <p class="mt-1 text-gray-900">
                                {{ $user->created_at ? $user->created_at->format('Y-m-d H:i:s') : 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">{{ __('Updated At') }}:</p>
                            <p class="mt-1 text-gray-900">
                                {{ $user->updated_at ? $user->updated_at->format('Y-m-d H:i:s') : 'N/A' }}</p>
                        </div>
                        @if ($user->deleted_at)
                            <div>
                                <p class="text-sm font-medium text-gray-500">{{ __('Deleted At') }}:</p>
                                <p class="mt-1 text-gray-900">
                                    {{ $user->deleted_at ? $user->deleted_at->format('Y-m-d H:i:s') : 'N/A' }}</p>
                            </div>
                        @endif
                        {{-- @endcan --}}
                    </div>
                </div>
            </div>
        </div>

        {{-- Related Applications Section (Optional) --}}
        {{-- Check if the user has permission to view applications --}}
        @if (Auth::user()->can('viewAny', \App\Models\EmailApplication::class) ||
                Auth::user()->can('viewAny', \App\Models\LoanApplication::class))
            <div class="bg-white shadow-md rounded-lg p-6 mb-8">
                <h2 class="text-xl font-semibold text-gray-700 mb-4">{{ __('Related Applications') }}</h2>

                {{-- Email Applications --}}
                @can('viewAny', \App\Models\EmailApplication::class)
                    <h3 class="text-lg font-medium text-gray-600 mb-3">{{ __('Email Applications') }}</h3>
                    @if ($user->emailApplications->count() > 0)
                        <ul class="list-disc pl-5 mb-4">
                            @foreach ($user->emailApplications as $application)
                                {{-- Assuming 'my-applications.email.show' route exists --}}
                                <li>
                                    <a href="{{ route('my-applications.email.show', $application) }}"
                                        class="text-blue-600 hover:underline">
                                        {{ __('Application ID') }}: {{ $application->id }} -
                                        {{ $application->created_at->format('Y-m-d') }} ({{ __('Status') }}:
                                        {{ __($application->status) }}) {{-- Translated Status --}}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-gray-600 mb-4">{{ __('No email applications submitted by this user.') }}</p>
                    @endif
                @endcan

                {{-- Loan Applications --}}
                @can('viewAny', \App\Models\LoanApplication::class)
                    <h3 class="text-lg font-medium text-gray-600 mb-3">{{ __('Loan Applications') }}</h3>
                    @if ($user->loanApplications->count() > 0)
                        <ul class="list-disc pl-5 mb-4">
                            @foreach ($user->loanApplications as $application)
                                {{-- Assuming 'my-applications.loan.show' route exists --}}
                                <li>
                                    <a href="{{ route('my-applications.loan.show', $application) }}"
                                        class="text-blue-600 hover:underline">
                                        {{ __('Application ID') }}: {{ $application->id }} -
                                        {{ $application->created_at->format('Y-m-d') }} ({{ __('Status') }}:
                                        {{ __($application->status) }}) {{-- Translated Status --}}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-gray-600 mb-4">{{ __('No loan applications submitted by this user.') }}</p>
                    @endif
                @endcan

                {{-- Add other related resource management data here (e.g., Issued Loans, Approvals Made) --}}

            </div>
        @endif

        {{-- Actions Section (Optional) --}}
        {{-- Check if the user has permission to update or delete this user --}}
        @if (Auth::user()->can('update', $user) || Auth::user()->can('delete', $user))
            <div class="bg-white shadow-md rounded-lg p-6">
                <h2 class="text-xl font-semibold text-gray-700 mb-4">{{ __('Actions') }}</h2>
                <div class="flex space-x-4">
                    {{-- Edit Button --}}
                    @can('update', $user)
                        {{-- Assuming 'admin.users.edit' route exists --}}
                        <a href="{{ route('resource-management.admin.users.edit', $user) }}"
                            class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 transition">
                            {{-- Added focus/transition --}}
                            {{ __('Edit User') }}
                        </a>
                    @endcan

                    {{-- Delete Button --}}
                    @can('delete', $user)
                        {{-- Implement a confirmation dialog for deletion --}}
                        <form action="{{ route('resource-management.admin.users.destroy', $user) }}" method="POST"
                            onsubmit="return confirm('{{ __('Are you sure you want to delete this user?') }}');">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition">
                                {{-- Added focus/transition --}}
                                {{ __('Delete User') }}
                            </button>
                        </form>
                    @endcan

                    {{-- Add other actions here (e.g., Force Delete, Restore if soft-deleted) --}}
                    @if ($user->trashed())
                        {{-- Check if the user is soft-deleted --}}
                        @can('restore', $user)
                            <form action="{{ route('resource-management.admin.users.restore', $user) }}" method="POST"
                                onsubmit="return confirm('{{ __('Are you sure you want to restore this user?') }}');">
                                @csrf
                                {{-- Assuming a PUT or POST route for restore --}}
                                @method('PUT') {{-- Or @method('POST') if your route is POST --}}
                                <button type="submit"
                                    class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition">
                                    {{-- Added focus/transition --}}
                                    {{ __('Restore User') }}
                                </button>
                            </form>
                        @endcan
                        @can('forceDelete', $user)
                            <form action="{{ route('resource-management.admin.users.force-delete', $user) }}" method="POST"
                                onsubmit="return confirm('{{ __('Are you sure you want to permanently delete this user? This action cannot be undone.') }}');">
                                @csrf
                                @method('DELETE') {{-- Or @method('POST') --}}
                                <button type="submit"
                                    class="bg-red-700 hover:bg-red-900 text-white font-bold py-2 px-4 rounded focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-700 transition">
                                    {{-- Added focus/transition --}}
                                    {{ __('Force Delete User') }}
                                </button>
                            </form>
                        @endcan
                    @endif
                </div>
            </div>
        @endif

    </div>
@endsection
