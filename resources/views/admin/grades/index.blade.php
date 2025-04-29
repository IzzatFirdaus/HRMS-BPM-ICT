<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"> {{-- Use app locale for language --}}

<head>
    <meta charset="utf-8"> {{-- Correct charset --}}
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> {{-- Correct viewport --}}
    <title>{{ __('Grades List') }}</title> {{-- Translate title --}}
    {{-- Link Tailwind CSS via CDN --}}
    <script src="https://cdn.tailwindcss.com"></script>
    {{-- Removed the inline <style> block and custom CSS classes --}}
    {{-- Add any necessary custom CSS if Tailwind alone is insufficient --}}
</head>

<body class="bg-gray-100 p-6"> {{-- Applied Tailwind background and padding to body --}}

    {{-- Extend your main layout --}}
    @extends('layouts.app')

    {{-- Define the content section --}}
    @section('content')
        {{-- Main container with max width and centering --}}
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 py-6">

            {{-- Page Title --}}
            <h2 class="text-2xl font-bold mb-6 text-gray-800">{{ __('Grades List') }}</h2> {{-- Translate title --}}

            {{-- Button to add new grade --}}
            {{-- You might wrap this in an @can('create', App\Models\Grade::class) --}}
            {{-- Standardized button styling --}}
            <a href="{{ route('admin.grades.create') }}"
                class="inline-flex items-center justify-center px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white font-bold rounded shadow-sm focus:outline-none focus:ring focus:ring-blue-500 transition mb-4">
                {{-- Assuming admin.grades.create route and added mb-4 --}}
                {{-- SVG icon --}}
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
                {{ __('Add New Grade') }} {{-- Translate button text --}}
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


            {{-- Table to display grades --}}
            @if ($grades->isEmpty())
                {{-- Assuming $grades is passed from the controller and is a collection or paginator --}}
                <p class="text-gray-600">{{ __('No grades found.') }}</p> {{-- Translate empty message --}}
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
                                    {{ __('Grade Name') }} {{-- Translate header --}}
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('Grade Code') }} {{-- Translate header --}}
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('Description') }} {{-- Translate header --}}
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{-- Centered header --}}
                                    {{ __('Requires Approval') }} {{-- Translate header --}}
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('Actions') }} {{-- Translate header --}}
                                </th>
                            </tr>
                        </thead>
                        {{-- Added body background and divider --}}
                        <tbody class="bg-white divide-y divide-gray-200">
                            {{-- Loop through the collection of grades --}}
                            @foreach ($grades as $grade)
                                {{-- Added hover effect --}}
                                <tr class="hover:bg-gray-100">
                                    {{-- Applied Tailwind td classes --}}
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $grade->name ?? 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $grade->code ?? 'N/A' }}
                                    </td>
                                    {{-- Removed whitespace-nowrap for description cell --}}
                                    <td class="px-6 py-4 text-sm text-gray-900">
                                        {{ $grade->description ?? '-' }}
                                    </td>
                                    {{-- Requires Approval column with check/cross icons --}}
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                                        {{-- Centered content --}}
                                        {{-- Replicated badge styling using Tailwind classes --}}
                                        @if ($grade->requires_approval)
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-800">
                                                <svg class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24"
                                                    stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M5 13l4 4L19 7" />
                                                </svg>
                                                {{ __('Yes') }} {{-- Or just show icon, but text is clearer --}}
                                            </span>
                                        @else
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-800">
                                                <svg class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24"
                                                    stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M6 18L18 6M6 6l12 12" />
                                                </svg>
                                                {{ __('No') }} {{-- Or just show icon --}}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium"> {{-- Added font-medium --}}
                                        {{-- Link to view grade details --}}
                                        {{-- Assuming a route named 'admin.grades.show' exists --}}
                                        {{-- Standardized link styling --}}
                                        <a href="{{ route('admin.grades.show', $grade) }}"
                                            class="text-blue-600 hover:text-blue-800 inline-flex items-center mr-4">
                                            {{ __('View') }} {{-- Translate action --}}
                                        </a>
                                        {{-- Optional: Edit button --}}
                                        {{-- @can('update', $grade) --}}
                                        {{-- Assuming a route named 'admin.grades.edit' exists --}}
                                        <a href="{{ route('admin.grades.edit', $grade) }}"
                                            class="text-indigo-600 hover:text-indigo-800 inline-flex items-center mr-4">
                                            {{ __('Edit') }} {{-- Translate action --}}
                                        </a>
                                        {{-- @endcan --}}
                                        {{-- Optional: Delete button --}}
                                        {{-- @can('delete', $grade) --}}
                                        {{-- This would typically be a form submission or Livewire action --}}
                                        {{-- Example Form for Delete (requires confirm dialog) --}}
                                        <form action="{{ route('admin.grades.destroy', $grade) }}" method="POST"
                                            onsubmit="return confirm('{{ __('Are you sure you want to delete this grade?') }}');"
                                            {{-- Translate confirm message --}} class="inline-block"> {{-- Use inline-block to keep it in line with other buttons if needed --}}
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="text-red-600 hover:text-red-800 inline-flex items-center">
                                                {{ __('Delete') }} {{-- Translate action --}}
                                            </button>
                                        </form>
                                        {{-- Or if using a modal/Livewire for delete confirmation, call the JS/Livewire method --}}
                                        {{-- <button type="button" onclick="confirmDelete({{ $grade->id }})" class="text-red-600 hover:text-red-800 inline-flex items-center">
                                             {{ __('Delete') }}
                                          </button>
                                          --}}
                                        {{-- @endcan --}}
                                    </td>
                                </tr>
                            @endforeach
                            {{-- Optional: Message if collection is empty (handled by the @if $grades->isEmpty() check outside the table) --}}
                        </tbody>
                    </table>
                </div> {{-- End overflow-x-auto --}}

                {{-- Pagination links --}}
                @if ($grades->hasPages())
                    <div class="mt-4">
                        {{ $grades->links() }} {{-- Renders Tailwind pagination links if available --}}
                    </div>
                @endif
            @endif

        </div> {{-- End max-w-7xl container --}}
    @endsection

</body>

</html>
