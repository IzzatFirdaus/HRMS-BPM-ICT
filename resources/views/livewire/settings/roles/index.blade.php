{{-- resources/views/livewire/settings/roles/index.blade.php --}}
{{-- This view is the template for the App\Livewire\Settings\Roles Livewire component. --}}
{{-- It should contain the HTML structure for the roles management page. --}}

{{-- Extend your main application layout --}}
@extends('layouts.app') {{-- <--- **IMPORTANT:** Adjust 'layouts.app' if your main layout file is named differently --}}

@section('content')
    <div class="container mx-auto py-6">
        <h1 class="text-2xl font-bold mb-6 text-gray-800">Manage User Roles</h1>

        {{-- Add buttons/links for adding new roles here --}}
        <div class="mb-4">
            {{-- Example: <button wire:click="openCreateModal" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Add New Role</button> --}}
            <p class="text-gray-600">Add a button or link here to open a form/modal for adding a new role.</p>
        </div>

        {{-- Display the list of roles --}}
        @if ($roles->count() > 0)
            <div class="bg-white shadow-md rounded overflow-hidden">
                <table class="min-w-full leading-normal">
                    <thead>
                        <tr>
                            <th
                                class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                ID
                            </th>
                            <th
                                class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Role Name
                            </th>
                            <th
                                class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Guard Name
                            </th>
                            <th
                                class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Created At
                            </th>
                            <th
                                class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($roles as $role)
                            <tr wire:key="role-{{ $role->id }}"> {{-- Add wire:key for Livewire list rendering --}}
                                <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                    {{ $role->id }}
                                </td>
                                <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                    {{ $role->name }}
                                </td>
                                <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                    {{ $role->guard_name }}
                                </td>
                                <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                    {{ $role->created_at->format('Y-m-d H:i') }} {{-- Format the date --}}
                                </td>
                                <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                    {{-- Add action buttons for editing/deleting roles here --}}
                                    {{-- Example: <button wire:click="openEditModal({{ $role->id }})" class="text-blue-600 hover:text-blue-900 mr-3">Edit</button> --}}
                                    {{-- Example: <button wire:click="deleteRole({{ $role->id }})" onclick="confirm('Are you sure you want to delete this role?') || event.stopImmediatePropagation()" class="text-red-600 hover:text-red-900">Delete</button> --}}
                                    <p class="text-gray-600">Add Edit/Delete buttons here.</p>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            {{-- If using pagination with Spatie Role model: {{ $roles->links() }} --}}
        @else
            <p class="text-gray-600">No roles found in the system.</p>
        @endif

        {{-- Include modals for adding/editing roles here if you use modal forms --}}
        {{-- @include('livewire.settings.roles.create-modal') --}}
        {{-- @include('livewire.settings.roles.edit-modal') --}}
        <p class="mt-4 text-gray-600">Include any modals for adding/editing roles here.</p>


    </div>
@endsection
