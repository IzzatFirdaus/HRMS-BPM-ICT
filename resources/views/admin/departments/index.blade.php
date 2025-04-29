<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"> {{-- Use app locale for language --}}

<head>
    <meta charset="utf-8"> {{-- Correct charset --}}
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> {{-- Correct viewport --}}
    <title>{{ __('Department/Unit List') }}</title> {{-- Translate title --}}
    {{-- Link Tailwind CSS via CDN --}}
    <script src="https://cdn.tailwindcss.com"></script>
    {{-- Removed the inline <style> block and custom CSS classes --}}
    {{-- Add any necessary custom CSS if Tailwind alone is insufficient (e.g., complex table hover effects beyond simple background) --}}
</head>

<body class="bg-gray-100 p-6"> {{-- Applied Tailwind background and padding to body --}}

    {{-- Extend your main layout --}}
    @extends('layouts.app')

    {{-- Define the content section --}}
    @section('content')
        {{-- Main container with max width and centering --}}
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 py-6">

            {{-- Page Title --}}
            <h2 class="text-2xl font-bold mb-6 text-gray-800">{{ __('Department/Unit List') }}</h2> {{-- Translate title --}}

            {{-- Button to add new department --}}
            {{-- You might wrap this in an @can('create', App\Models\Department::class) --}}
            {{-- Replaced custom btn btn-primary with Tailwind classes --}}
            <a href="{{ route('admin.departments.create') }}"
                class="inline-flex items-center justify-center px-5 py-2.5 bg-blue-500 text-white font-semibold rounded-md border border-blue-500 hover:bg-blue-600 hover:border-blue-600 focus:outline-none focus:ring focus:ring-blue-500 focus:ring-opacity-50 transition mb-4">
                {{-- Assuming admin.departments.create route and added mb-4 --}}
                {{-- SVG icon --}}
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
                {{ __('Add New Department/Unit') }} {{-- Translate button text --}}
            </a>
            {{-- @endcan --}}


            {{-- Display success messages from session --}}
            @if (session()->has('success'))
                {{-- Replaced custom alert-success with Tailwind classes --}}
                <div class="bg-green-100 border border-green-200 text-green-800 p-4 rounded-md mb-4">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Display error messages from session if needed --}}
            @if (session()->has('error'))
                <div class="bg-red-100 border border-red-200 text-red-800 p-4 rounded-md mb-4">
                    {{ session('error') }}
                </div>
            @endif


            {{-- Table to display departments --}}
            @if ($departments->isEmpty())
                {{-- Assuming $departments is passed from the controller and is a collection or paginator --}}
                <p class="text-gray-600">{{ __('No departments/units found.') }}</p> {{-- Translate empty message --}}
            @else
                {{-- Added overflow-x-auto for responsiveness on small screens --}}
                <div class="overflow-x-auto shadow-sm rounded-lg border border-gray-200"> {{-- Converted table container styling --}}
                    {{-- Applied Tailwind table classes --}}
                    <table class="min-w-full divide-y divide-gray-200"> {{-- Replaced custom table class --}}
                        <thead class="bg-gray-50"> {{-- Added header background --}}
                            <tr>
                                {{-- Applied Tailwind th classes --}}
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('Name Department/Unit') }} {{-- Translate header --}}
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('Code') }} {{-- Translate header --}}
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('Description') }} {{-- Translate header --}}
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('Actions') }} {{-- Translate header --}}
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
                                    {{-- Removed whitespace-nowrap for description cell --}}
                                    <td class="px-6 py-4 text-sm text-gray-900">
                                        {{ $department->description ?? '-' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium"> {{-- Added font-medium --}}
                                        {{-- Link to view department details --}}
                                        {{-- Assuming a route named 'admin.departments.show' exists --}}
                                        {{-- Replaced custom text-blue-600 hover:text-blue-900 font-semibold with consistent classes --}}
                                        <a href="{{ route('admin.departments.show', $department) }}"
                                            class="text-blue-600 hover:text-blue-800 inline-flex items-center mr-4">
                                            {{-- Added inline-flex etc for consistent button/link styling if needed --}}
                                            {{ __('View') }} {{-- Translate action --}}
                                        </a>

                                        {{-- Optional: Edit button --}}
                                        {{-- @can('update', $department) --}}
                                        {{-- Assuming a route named 'admin.departments.edit' exists --}}
                                        <a href="{{ route('admin.departments.edit', $department) }}"
                                            class="text-indigo-600 hover:text-indigo-800 inline-flex items-center mr-4">
                                            {{ __('Edit') }} {{-- Translate action --}}
                                        </a>
                                        {{-- @endcan --}}

                                        {{-- Optional: Delete button --}}
                                        {{-- @can('delete', $department) --}}
                                        {{-- This would typically be a form submission or Livewire action --}}
                                        {{-- Example Form for Delete (requires confirm dialog) --}}
                                        {{-- <form action="{{ route('admin.departments.destroy', $department) }}" method="POST" onsubmit="return confirm('{{ __('Are you sure you want to delete this department?') }}')" class="inline-block"> // Translate confirm message
                                             @csrf
                                             @method('DELETE')
                                             <button type="submit" class="text-red-600 hover:text-red-800 inline-flex items-center">
                                                 {{ __('Delete') }} // Translate action
                                             </button>
                                          </form>
                                          --}}
                                        {{-- Or if using a modal/Livewire for delete confirmation, call the JS/Livewire method --}}
                                        {{-- <button type="button" onclick="confirmDelete({{ $department->id }})" class="text-red-600 hover:text-red-800 inline-flex items-center">
                                             {{ __('Delete') }}
                                          </button>
                                          --}}
                                        {{-- @endcan --}}
                                    </td>
                                </tr>
                            @endforeach
                            {{-- Optional: Message if collection is empty (handled by the @if $departments->isEmpty() check outside the table) --}}
                            {{-- @forelse ($departments as $department) ... @empty ... @endforelse is another way to handle empty states --}}
                        </tbody>
                    </table>
                </div> {{-- End overflow-x-auto --}}

                {{-- Pagination links --}}
                @if ($departments->hasPages())
                    <div class="mt-4">
                        {{ $departments->links() }} {{-- Renders Tailwind pagination links if available --}}
                    </div>
                @endif
            @endif

        </div> {{-- End max-w-7xl container --}}
    @endsection

    {{-- You might have other scripts or footer content in your layout --}}

</body>

</html>
