{{-- resources/views/livewire/settings/roles.blade.php --}}
{{-- This view is the template for the App\Livewire\Settings\Roles Livewire component. --}}
{{-- It should contain the HTML structure for the roles management page, rendered within the main layout. --}}

{{-- Livewire component root element --}}
<div>

    {{-- Set the page title --}}
    @section('title', __('Role Management'))

    {{-- Main content container --}}
    <div class="container-xxl flex-grow-1 container-p-y">

        {{-- Page Title within content --}}
        <h4 class="py-3 mb-4">{{ __('Role Management') }}</h4>

        {{-- Success/Error Alerts --}}
        @if (session()->has('message'))
            <div class="alert alert-success">
                {{ session('message') }}
            </div>
        @endif

        @if (session()->has('error'))
            <div class="alert alert-danger"> {{-- Assuming you have .alert-danger styles --}}
                {{ session('error') }}
            </div>
        @endif

        {{-- Add buttons/links for adding new roles here --}}
        <div class="mb-4">
            {{-- Example: Button to trigger a modal for adding a new role --}}
            {{-- <button wire:click="openCreateModal" class="btn btn-primary">{{ __('Add New Role') }}</button> --}}
            <p class="text-gray-600">Add a button or link here to open a form/modal for adding a new role.</p>
        </div>


        {{-- Display the list of roles --}}
        {{-- Assuming $roles are passed from the component (using Role::all() or pagination) --}}
        @if ($roles->count() > 0)
            <div class="card"> {{-- Use card for styling consistent with users list --}}
                <h5 class="card-header">{{ __('Roles List') }}</h5>
                <div class="table-responsive text-nowrap"> {{-- Responsive table container --}}
                    <table class="table"> {{-- Basic table structure --}}
                        <thead>
                            <tr>
                                <th
                                    class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    {{ __('ID') }}</th>
                                <th
                                    class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    {{ __('Name') }}</th>
                                <th
                                    class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    {{ __('Created At') }}</th>
                                <th
                                    class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    {{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="table-border-bottom-0">
                            @foreach ($roles as $role)
                                <tr>
                                    <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">{{ $role->id }}
                                    </td>
                                    <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">{{ $role->name }}
                                    </td>
                                    <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                        {{ $role->created_at ? $role->created_at->format('Y-m-d H:i') : 'N/A' }}
                                        {{-- Format the date --}}
                                    </td>
                                    <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                        {{-- Add action buttons for editing/deleting roles here --}}
                                        {{-- Example: Using Livewire actions --}}
                                        {{-- <button wire:click="openEditModal({{ $role->id }})" class="btn btn-sm btn-primary me-1">{{ __('Edit') }}</button> --}}
                                        {{-- <button wire:click="deleteRole({{ $role->id }})" wire:confirm="{{ __('Are you sure you want to delete this role?') }}" class="btn btn-sm btn-danger">{{ __('Delete') }}</button> --}}
                                        <p class="text-gray-600">{{ __('Add Edit/Delete buttons here.') }}</p>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- If using pagination with Spatie Role model: {{ $roles->links() }} --}}
            @if (method_exists($roles, 'hasPages') && $roles->hasPages())
                <div class="mt-4">
                    {{ $roles->links() }}
                </div>
            @endif
        @else
            <div class="card">
                <div class="card-body">
                    <p class="text-center text-gray-600">{{ __('No roles found in the system.') }}</p>
                </div>
            </div>
        @endif

        {{-- Include modals for adding/editing roles here if you use modal forms --}}
        {{-- @include('livewire.settings.roles.create-modal') --}}
        {{-- @include('livewire.settings.roles.edit-modal') --}}
        {{-- <p class="mt-4 text-gray-600">{{ __('Include any modals for adding/editing roles here.') }}</p> --}}


    </div> {{-- End container --}}\n
    {{-- Optional: Push custom scripts --}}
    @push('custom-scripts')
        {{-- Add page-specific JavaScript here --}}
    @endpush

</div>
