{{--
    resources/views/admin/users/index.blade.php

    This view displays a list of users with their basic and new MOTAC-specific attributes.
    It includes columns for Employee ID, MOTAC Email, Department, and Grade.
    Assumes a collection of $users (paginated) is passed to the view.
    Requires eager loading of 'department' and 'grade' relationships in the controller.
--}}

{{-- Extend your main admin layout --}}
{{-- Adjust 'layouts.app' if your admin layout is different --}}
@extends('layouts.app')

@section('content')
    <div class="container mx-auto px-4 py-8">
        <div class="flex items-center justify-between mb-6">
            {{-- Page Title --}}
            <h1 class="text-2xl font-bold text-gray-800">{{ __('User List') }}</h1>

            {{-- Add New User Button --}}
            {{-- Ensure user has permission to create users --}}
            @can('create users')
                {{-- Assuming a 'create users' permission exists --}}
                {{-- Assuming a route named 'resource-management.admin.users.create' --}}
                <a href="{{ route('resource-management.admin.users.create') }}"
                    class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                    {{ __('Add New User') }}
                </a>
            @endcan
        </div>

        {{-- Users Table Card --}}
        <div class="bg-white shadow-md rounded-lg p-6">
            <div class="table-responsive">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            {{-- Existing Columns --}}
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('Name') }}
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('Email') }}
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('Roles') }}
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('Status') }}
                            </th>

                            {{-- 👇 New MOTAC Columns 👇 --}}
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('Employee ID') }}
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('MOTAC Email') }}
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('Department') }}
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('Grade') }}
                            </th>
                            {{-- ☝️ End New MOTAC Columns ☝️ --}}

                            {{-- Actions Column --}}
                            <th scope="col"
                                class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('Actions') }}
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        {{-- Loop through the users --}}
                        @forelse ($users as $user)
                            <tr>
                                {{-- Existing Data Cells --}}
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ $user->name ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $user->email ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $user->getRoleNames()->implode(', ') }} {{-- Display user roles (requires Spatie HasRoles) --}}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{-- You might want a colored badge for status here, similar to the dashboard --}}
                                    {{ $user->status ?? 'N/A' }}
                                </td>

                                {{-- 👇 New MOTAC Data Cells 👇 --}}
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $user->employee_id ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $user->motac_email ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{-- Accessing department name via relationship - Requires eager loading in controller --}}
                                    {{ $user->department->name ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{-- Accessing grade name via relationship - Requires eager loading in controller --}}
                                    {{ $user->grade->name ?? 'N/A' }}
                                </td>
                                {{-- ☝️ End New MOTAC Data Cells ☝️ --}}


                                {{-- Actions Column --}}
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    {{-- View Button --}}
                                    @can('view', $user)
                                        {{-- Assuming a 'view User' permission/policy exists --}}
                                        <a href="{{ route('resource-management.admin.users.show', $user) }}"
                                            class="text-blue-600 hover:text-blue-900 mr-2">
                                            {{ __('View') }}
                                        </a>
                                    @endcan

                                    {{-- Edit Button --}}
                                    @can('update', $user)
                                        {{-- Assuming an 'update User' permission/policy exists --}}
                                        <a href="{{ route('resource-management.admin.users.edit', $user) }}"
                                            class="text-yellow-600 hover:text-yellow-900 mr-2">
                                            {{ __('Edit') }}
                                        </a>
                                    @endcan

                                    {{-- Delete Button --}}
                                    @can('delete', $user)
                                        {{-- Assuming a 'delete User' permission/policy exists --}}
                                        {{-- Implement a confirmation dialog for deletion --}}
                                        <form action="{{ route('resource-management.admin.users.destroy', $user) }}"
                                            method="POST" class="inline"
                                            onsubmit="return confirm('{{ __('Are you sure you want to delete this user?') }}');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900">
                                                {{ __('Delete') }}
                                            </button>
                                        </form>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            {{-- Message when no users are found --}}
                            <tr>
                                <td colspan="9" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                    {{ __('No users found.') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination Links --}}
            @if ($users->hasPages())
                {{-- Check if pagination is needed --}}
                <div class="mt-6">
                    {{ $users->links() }} {{-- Render pagination links --}}
                </div>
            @endif
        </div>
    </div>
@endsection
