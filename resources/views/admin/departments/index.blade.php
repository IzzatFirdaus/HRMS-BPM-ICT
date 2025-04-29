{{--
    resources/views/admin/departments/index.blade.php

    This view displays a list of departments/units in a table.
    It extends the main application layout and uses Tailwind CSS classes.
    Assumes a collection/paginator of $departments is passed from the controller
    (DepartmentController@index), loaded using ->withCount('users') to display member counts.
    Assumes the Department model has 'name', 'code', and 'description' attributes and a 'users' relationship.
    Assumes the layout (layouts.app) handles asset inclusion (like compiled CSS including Tailwind).
    Assumes standard Laravel routing and controller methods for CRUD actions.
--}}

{{-- Extend your main application layout --}}
{{-- Adjust 'layouts.app' if your layout is different --}}
@extends('layouts.app')

{{-- Define the title section to be included in the layout's <head> --}}
@section('title', __('Department/Unit List')) {{-- Translate the page title --}}

{{-- Define any page-specific styles (optional) --}}
@section('page-style')
    {{-- Link any CSS specific to this page if needed, typically compiled assets --}}
    {{-- Example: <link rel="stylesheet" href="{{ asset('css/departments.css') }}"> --}}
    {{-- Tailwind CSS is assumed to be included in the main app.css or layout --}}
@endsection

{{-- Define the main content section --}}
@section('content')

    {{-- Main container with max width and centering, adding padding/margin --}}
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 py-6">

        {{-- Page Title --}}
        <h2 class="text-2xl font-bold mb-6 text-gray-800">{{ __('Department/Unit List') }}</h2> {{-- Translate title --}}

        {{-- Button to add new department --}}
        {{-- You might wrap this in an @can('create', App\Models\Department::class) --}}
        <a href="{{ route('admin.departments.create') }}"
            class="inline-flex items-center justify-center px-5 py-2.5 bg-blue-500 text-white font-semibold rounded-md border border-blue-500 hover:bg-blue-600 hover:border-blue-600 focus:outline-none focus:ring focus:ring-blue-500 focus:ring-opacity-50 transition mb-4">
            {{-- Assuming admin.departments.create route and added mb-4 --}}
            {{-- SVG icon (ensure this is included or handled by an icon set, or use Heroicons via Blade Components) --}}
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
            </svg>
            {{ __('Add New Department/Unit') }} {{-- Translate button text --}}
        </a>
        {{-- @endcan --}}


        {{-- Display success messages from session --}}
        @if (session()->has('success'))
            {{-- Applied Tailwind classes for success alert --}}
            <div class="bg-green-100 border border-green-200 text-green-800 p-4 rounded-md mb-4">
                {{ session('success') }}
            </div>
        @endif

        {{-- Display error messages from session if needed --}}
        @if (session()->has('error'))
            {{-- Applied Tailwind classes for error alert --}}
            <div class="bg-red-100 border border-red-200 text-red-800 p-4 rounded-md mb-4">
                {{ session('error') }}
            </div>
        @endif


        {{-- Table to display departments --}}
        {{-- Check if $departments collection is empty --}}
        @if ($departments->isEmpty())
            <p class="text-gray-600">{{ __('No departments/units found.') }}</p> {{-- Translate empty message --}}
        @else
            {{-- Added overflow-x-auto for responsiveness on small screens --}}
            <div class="overflow-x-auto shadow-sm rounded-lg border border-gray-200"> {{-- Converted table container styling --}}
                {{-- Applied Tailwind table classes --}}
                <table class="min-w-full divide-y divide-gray-200"> {{-- Replaced custom table class --}}
                    <thead class="bg-gray-50"> {{-- Added header background --}}
                        <tr>
                            {{-- Applied Tailwind th classes and translation --}}
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('Name Department/Unit') }}
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('Code') }}
                            </th>
                            {{-- Added Branch Type header based on previous discussion and Department model --}}
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('Branch Type') }}
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('Description') }}
                            </th>
                            {{-- Added Members Count header based on previous discussion and Department model --}}
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('Members Count') }}
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{-- Right-aligned actions header --}}
                                {{ __('Actions') }}
                            </th>
                        </tr>
                    </thead>
                    {{-- Added body background and divider --}}
                    <tbody class="bg-white divide-y divide-gray-200">
                        {{-- Loop through the collection of departments --}}
                        @foreach ($departments as $department)
                            {{-- Added hover effect --}}
                            <tr class="hover:bg-gray-100">
                                {{-- Applied Tailwind td classes --}}
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $department->name ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $department->code ?? 'N/A' }}
                                </td>
                                {{-- Display Branch Type --}}
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ __(ucfirst($department->branch_type ?? '-')) }} {{-- Translate and capitalize value, default to '-' --}}
                                </td>
                                {{-- Removed whitespace-nowrap for description cell to allow wrapping --}}
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    {{ $department->description ?? '-' }}
                                </td>
                                {{-- Display Members Count from the users_count attribute added by withCount('users') --}}
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $department->users_count ?? 0 }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    {{-- Right-aligned actions --}}
                                    {{-- Action links wrapped in a flex container for spacing --}}
                                    <div class="flex items-center justify-end space-x-4"> {{-- Added flex container for actions and justify-end for right alignment --}}
                                        {{-- Link to view department details --}}
                                        {{-- Assuming a route named 'admin.departments.show' exists --}}
                                        {{-- @can('view', $department) --}}
                                        <a href="{{ route('admin.departments.show', $department) }}"
                                            class="text-blue-600 hover:text-blue-800"> {{-- Removed inline-flex etc and mr-4, using space-x on parent --}}
                                            {{ __('View') }} {{-- Translate action --}}
                                        </a>
                                        {{-- @endcan --}}

                                        {{-- Optional: Edit button --}}
                                        {{-- @can('update', $department) --}}
                                        {{-- Assuming a route named 'admin.departments.edit' exists --}}
                                        <a href="{{ route('admin.departments.edit', $department) }}"
                                            class="text-indigo-600 hover:text-indigo-800"> {{-- Removed inline-flex etc and mr-4, using space-x on parent --}}
                                            {{ __('Edit') }} {{-- Translate action --}}
                                        </a>
                                        {{-- @endcan --}}

                                        {{-- Optional: Delete button --}}
                                        {{-- @can('delete', $department) --}}
                                        {{-- This would typically be a form submission or Livewire action --}}
                                        {{-- Example Form for Delete (requires confirm dialog) --}}
                                        {{-- Using form with POST/DELETE methods and confirmation --}}
                                        {{-- Removed inline-block as flex parent usually handles this --}}
                                        <form action="{{ route('admin.departments.destroy', $department) }}" method="POST"
                                            onsubmit="return confirm('{{ __('Are you sure you want to delete this department?') }}')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-800">
                                                {{-- Removed inline-flex etc, using space-x on parent --}}
                                                {{ __('Delete') }} {{-- Translate action --}}
                                            </button>
                                        </form>
                                        {{-- Or if using a modal/Livewire for delete confirmation, call the JS/Livewire method --}}
                                        {{-- <button type="button" onclick="confirmDelete({{ $department->id }})" class="text-red-600 hover:text-red-800">
                                             {{ __('Delete') }}
                                           </button> --}}
                                        {{-- @endcan --}}
                                    </div> {{-- End flex container for actions --}}
                                </td>
                            </tr>
                        @endforeach
                        {{-- @forelse ($departments as $department) ... @empty ... @endforelse is another way to handle empty states,
                             but the @if ($departments->isEmpty()) check outside the table already covers this for a simple empty message. --}}
                    </tbody>
                </table>
            </div> {{-- End overflow-x-auto --}}

            {{-- Pagination links --}}
            {{-- Check if the departments collection is a paginator instance and has pages --}}
            {{-- The ->links() method exists on a LengthAwarePaginator instance and is correct Blade syntax. --}}
            {{-- If your IDE shows an error here, it's likely a false positive related to static analysis of Blade templates. --}}
            @if ($departments instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator && $departments->hasPages())
                <div class="mt-4">
                    {{ $departments->links() }} {{-- Renders pagination links using the default or configured paginator style --}}
                </div>
            @endif
        @endif {{-- End @if ($departments->isEmpty()) --}}

    </div> {{-- End max-w-7xl container --}}

@endsection

{{-- Note: This view is intended to be rendered by a standard controller, not directly by a Livewire component,
     as it extends a layout and uses standard form submissions. If it were a Livewire view, it would typically
     use wire:submit and wire:model.
--}}
