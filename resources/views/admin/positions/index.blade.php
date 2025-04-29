{{--
    This Blade file is a list view for Positions.
    It uses Tailwind CSS for styling and extends a base layout.
--}}

@extends('layouts.app') {{-- Extend your main layout file --}}

@section('title', __('Position List')) {{-- Set the page title in the layout --}}

{{-- Removed the <head> and <body> tags and inline <style> block, as these should be handled by the layout --}}
{{-- The Tailwind JIT compiler script <script src="https://cdn.tailwindcss.com"></script> should also be in your main layout <head> --}}

@section('content')
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 py-6"> {{-- Converted container to Tailwind --}}
        <h2 class="text-2xl font-bold mb-6 text-gray-800">{{ __('Position List') }}</h2> {{-- Converted h2, translated --}}

        {{-- Button to add new position --}}
        {{-- You might wrap this in an @can('create', App\Models\Position::class) --}}
        {{-- Converted btn btn-primary to Tailwind classes --}}
        <a href="{{ route('admin.positions.create') }}"
            class="inline-flex items-center px-5 py-2.5 bg-blue-600 text-white font-semibold rounded-md shadow hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition mb-4">
            {{-- Assuming admin.positions.create route --}}
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
            </svg>
            {{ __('Add New Position') }} {{-- Translated string --}}
        </a>
        {{-- @endcan --}}


        {{-- Display success messages --}}
        @if (session()->has('success'))
            {{-- Converted alert alert-success to Tailwind classes --}}
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                {{ session('success') }}
            </div>
        @endif

        {{-- Table to display positions --}}
        @if ($positions->isEmpty())
            {{-- Assuming $positions is passed from the controller --}}
            <p class="text-gray-600">{{ __('No positions found.') }}</p> {{-- Converted text-gray-600, translated --}}
        @else
            {{-- Added overflow and shadow for table container --}}
            <div class="overflow-x-auto shadow-sm rounded-md border border-gray-200">
                {{-- Converted table classes --}}
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50"> {{-- Added header background --}}
                        <tr>
                            {{-- Converted th classes --}}
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('Position Name') }} {{-- Translated string --}}
                            </th>
                            {{-- Converted th classes --}}
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('Code') }} {{-- Translated string --}}
                            </th>
                            {{-- Converted th classes --}}
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('Description') }} {{-- Translated string --}}
                            </th>
                            {{-- Converted th classes --}}
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('Actions') }} {{-- Translated string --}}
                            </th>
                        </tr>
                    </thead>
                    {{-- Added body background and divider, converted tbody class --}}
                    <tbody class="bg-white divide-y divide-gray-200">
                        {{-- Loop through the collection of positions --}}
                        @foreach ($positions as $position)
                            {{-- Added hover effect --}}
                            <tr class="hover:bg-gray-100">
                                {{-- Converted td classes --}}
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $position->name ?? 'N/A' }}
                                </td>
                                {{-- Converted td classes --}}
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $position->code ?? 'N/A' }}
                                </td>
                                {{-- Converted td classes, removed whitespace-nowrap for potentially longer descriptions --}}
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    {{ $position->description ?? '-' }}
                                </td>
                                {{-- Converted td classes, used flex for actions --}}
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{-- Added flex and space-x for layout --}}
                                    <div class="flex items-center space-x-4">
                                        {{-- Link to view position details, converted btn-info text --}}
                                        {{-- Assuming a route named 'admin.positions.show' exists --}}
                                        <a href="{{ route('admin.positions.show', $position) }}"
                                            class="text-blue-600 hover:text-blue-900 font-semibold">
                                            {{ __('View') }} {{-- Translated string --}}
                                        </a>
                                        {{-- Optional: Edit button, converted btn-info text --}}
                                        {{-- @can('update', $position) --}}
                                        {{-- Assuming a route named 'admin.positions.edit' exists --}}
                                        <a href="{{ route('admin.positions.edit', $position) }}"
                                            class="text-indigo-600 hover:text-indigo-900 font-semibold">
                                            {{-- Adjusted margin-left --}}
                                            {{ __('Edit') }} {{-- Translated string --}}
                                        </a>
                                        {{-- @endcan --}}
                                        {{-- Optional: Delete button, converted btn-danger text --}}
                                        {{-- @can('delete', $position) --}}
                                        {{-- Assuming a route named 'admin.positions.destroy' exists --}}
                                        {{-- This would typically be a form submission or Livewire action --}}
                                        {{-- Converted form class to Tailwind inline --}}
                                        {{-- <form action="{{ route('admin.positions.destroy', $position) }}" method="POST" onsubmit="return confirm('{{ __('Are you sure?') }}')" class="inline"> --}} {{-- Added translation for confirm --}}
                                        {{-- @csrf --}}
                                        {{-- @method('DELETE') --}}
                                        {{-- <button type="submit" class="text-red-600 hover:text-red-900 font-semibold">{{ __('Delete') }}</button> --}} {{-- Translated string --}}
                                        {{-- </form> --}}
                                        {{-- @endcan --}}
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div> {{-- End overflow-x-auto --}}

            {{-- Pagination links --}}
            @if ($positions->hasPages())
                <div class="mt-4"> {{-- Added top margin --}}
                    {{ $positions->links() }}
                </div>
            @endif
        @endif

    </div> {{-- End max-w-7xl container --}}
@endsection
