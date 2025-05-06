{{-- resources/views/livewire/settings/permissions/index.blade.php --}}
{{-- This view is the template for the App\Livewire\Settings\Permissions Livewire component. --}}
{{-- It should contain the HTML structure for the permissions management page. --}}

{{-- Extend your main application layout --}}
@extends('layouts.app') {{-- <--- **IMPORTANT:** Adjust 'layouts.app' if your main layout file is named differently --}}

@section('content')
    <div class="container mx-auto py-6">
        <h1 class="text-2xl font-bold mb-6 text-gray-800">Manage Permissions</h1>

        {{-- Add buttons/links for adding new permissions here --}}
        <div class="mb-4">
            {{-- Example: <button wire:click="openCreateModal" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Add New Permission</button> --}}
            <p class="text-gray-600">Add a button or link here to open a form/modal for adding a new permission.</p>
        </div>

        {{-- Display the list of permissions --}}
        @if ($permissions->count() > 0)
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
                                Permission Name
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
                        @foreach ($permissions as $permission)
                            <tr wire:key="permission-{{ $permission->id }}"> {{-- Add wire:key for Livewire list rendering --}}
                                <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                    {{ $permission->id }}
                                </td>
                                <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                    {{ $permission->name }}
                                </td>
                                <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                    {{ $permission->guard_name }}
                                </td>
                                <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                    {{ $permission->created_at->format('Y-m-d H:i') }} {{-- Format the date --}}
                                </td>
                                <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                    {{-- Add action buttons for editing/deleting permissions here --}}
                                    {{-- Example: <button wire:click="openEditModal({{ $permission->id }})" class="text-blue-600 hover:text-blue-900 mr-3">Edit</button> --}}
                                    {{-- Example: <button wire:click="deletePermission({{ $permission->id }})" onclick="confirm('Are you sure you want to delete this permission?') || event.stopImmediatePropagation()" class="text-red-600 hover:text-red-900">Delete</button> --}}
                                    <p class="text-gray-600">Add Edit/Delete buttons here.</p>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            {{-- If using pagination with Spatie Permission model: {{ $permissions->links() }} --}}
        @else
            <p class="text-gray-600">No permissions found in the system.</p>
        @endif

        {{-- Include modals for adding/editing permissions here if you use modal forms --}}
        {{-- @include('livewire.settings.permissions.create-modal') --}}
        {{-- @include('livewire.settings.permissions.edit-modal') --}}
        <p class="mt-4 text-gray-600">Include any modals for adding/editing permissions here.</p>

    </div>
@endsection
