{{-- resources/views/livewire/settings/permissions.blade.php --}}
{{-- This view is the template for the App\Livewire\Settings\Permissions Livewire component. --}}
{{-- It should contain the HTML structure for the permissions management page, rendered within the main layout. --}}

{{-- Livewire component root element --}}
<div>

    {{-- Set the page title --}}
    @section('title', __('Permission Management'))

    {{-- Main content container --}}
    <div class="container-xxl flex-grow-1 container-p-y">

        {{-- Page Title within content --}}
        <h4 class="py-3 mb-4">{{ __('Permission Management') }}</h4>

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

        {{-- Add buttons/links for adding new permissions here --}}
        <div class="mb-4">
            {{-- Example: Button to trigger a modal for adding a new permission --}}
            {{-- <button wire:click="openCreateModal" class="btn btn-primary">{{ __('Add New Permission') }}</button> --}}
            <p class="text-gray-600">Add a button or link here to open a form/modal for adding a new permission.</p>
        </div>

        {{-- Display the list of permissions --}}
        {{-- Assuming $permissions are passed from the component (using Permission::all() or pagination) --}}
        @if ($permissions->count() > 0)
            <div class="card"> {{-- Use card for styling consistent with users list --}}
                <h5 class="card-header">{{ __('Permissions List') }}</h5>
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
                            @foreach ($permissions as $permission)
                                <tr>
                                    <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                        {{ $permission->id }}</td>
                                    <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                        {{ $permission->name }}</td>
                                    <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                        {{ $permission->created_at ? $permission->created_at->format('Y-m-d H:i') : 'N/A' }}
                                        {{-- Format the date --}}
                                    </td>
                                    <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                        {{-- Add action buttons for editing/deleting permissions here --}}
                                        {{-- Example: Using Livewire actions --}}
                                        {{-- <button wire:click="openEditModal({{ $permission->id }})" class="btn btn-sm btn-primary me-1">{{ __('Edit') }}</button> --}}
                                        {{-- <button wire:click="deletePermission({{ $permission->id }})" wire:confirm="{{ __('Are you sure you want to delete this permission?') }}" class="btn btn-sm btn-danger">{{ __('Delete') }}</button> --}}
                                        <p class="text-gray-600">{{ __('Add Edit/Delete buttons here.') }}</p>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- If using pagination with Spatie Permission model: {{ $permissions->links() }} --}}
            @if (method_exists($permissions, 'hasPages') && $permissions->hasPages())
                <div class="mt-4">
                    {{ $permissions->links() }}
                </div>
            @endif
        @else
            <div class="card">
                <div class="card-body">
                    <p class="text-center text-gray-600">{{ __('No permissions found in the system.') }}</p>
                </div>
            </div>
        @endif

        {{-- Include modals for adding/editing permissions here if you use modal forms --}}
        {{-- @include('livewire.settings.permissions.create-modal') --}}
        {{-- @include('livewire.settings.permissions.edit-modal') --}}
        {{-- <p class="mt-4 text-gray-600">{{ __('Include any modals for adding/editing permissions here.') }}</p> --}}

    </div> {{-- End container --}}

    {{-- Optional: Push custom scripts --}}
    @push('custom-scripts')
        {{-- Add page-specific JavaScript here --}}
    @endpush

</div>
