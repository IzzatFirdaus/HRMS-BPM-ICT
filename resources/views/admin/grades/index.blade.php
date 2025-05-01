<!DOCTYPE html>
{{-- HTML document with language attribute set based on application locale --}}
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    {{-- Character set declaration --}}
    <meta charset="utf-8">
    {{-- Viewport settings for responsive design --}}
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    {{-- Page title, translated --}}
    <title>{{ __('Grades List') }}</title>

    {{-- Link Tailwind CSS via CDN for styling.
         This replaces the custom CSS classes like table, alert, etc.,
         used in some other files with standard Tailwind utility classes. --}}
    <script src="https://cdn.tailwindcss.com"></script>

    {{-- Removed the inline <style> block and custom CSS classes that were present in the original file's context.
         Ensure your project is configured to use Tailwind CSS, either via CDN (as shown here)
         or preferably through a build process (like Webpack or Vite) for better performance and customization. --}}

    {{-- Add any necessary custom CSS here if Tailwind alone is insufficient for specific styling requirements --}}
</head>

{{-- Body element with Tailwind background color and padding applied --}}

<body class="bg-gray-100 p-6">

    {{-- Extend your main layout file. Assuming 'layouts.app' exists and has a @yield('content') section. --}}
    @extends('layouts.app')

    {{-- Define the content section where the list and messages will be placed within the layout --}}
    @section('content')
        {{-- Main container for the content. Sets max-width (larger for list view), centers horizontally, and adds padding. --}}
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 py-6">

            {{-- Page Title --}}
            <h2 class="text-2xl font-bold mb-6 text-gray-800">{{ __('Grades List') }}</h2> {{-- Translated title --}}

            {{-- Button to add new grade --}}
            {{-- You might wrap this in an @can('create', App\Models\Grade::class) to control access --}}
            {{-- Standardized button styling using Tailwind classes --}}
            <a href="{{ route('admin.grades.create') }}"
                class="inline-flex items-center justify-center px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white font-bold rounded shadow-sm focus:outline-none focus:ring focus:ring-blue-500 transition mb-4">
                {{-- Link uses the named route 'admin.grades.create'. Added mb-4 for bottom margin. --}}
                {{-- SVG icon for 'add' --}}
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
                {{ __('Add New Grade') }} {{-- Translated button text --}}
            </a>
            {{-- @endcan --}} {{-- End can check --}}


            {{-- Display success messages from session (using flash messages) --}}
            @if (session()->has('success'))
                {{-- Replaced custom alert-success styling with Tailwind classes for a success alert box --}}
                <div class="bg-green-100 border border-green-200 text-green-800 p-4 rounded-md mb-4">
                    {{ session('success') }} {{-- Display the success message --}}
                </div>
            @endif {{-- End success message display --}}

            {{-- Display error messages from session if needed --}}
            @if (session()->has('error'))
                {{-- Replaced custom alert-danger styling with Tailwind classes for an error alert box --}}
                <div class="bg-red-100 border border-red-200 text-red-800 p-4 rounded-md mb-4">
                    {{ session('error') }} {{-- Display the error message --}}
                </div>
            @endif {{-- End error message display --}}


            {{-- Table to display grades --}}
            {{-- Check if the $grades collection is empty --}}
            @if ($grades->isEmpty())
                {{-- Assuming $grades is passed from the controller and is a collection or paginator --}}
                <p class="text-gray-600">{{ __('No grades found.') }}</p> {{-- Translated empty state message --}}
            @else
                {{-- Added overflow-x-auto for responsiveness on small screens so the table can be scrolled horizontally --}}
                {{-- Converted table container styling using Tailwind classes for shadow, rounded corners, and border --}}
                <div class="overflow-x-auto shadow-sm rounded-lg border border-gray-200">
                    {{-- Applied Tailwind table classes --}}
                    <table class="min-w-full divide-y divide-gray-200"> {{-- Replaced custom table class --}}
                        {{-- Table header --}}
                        <thead class="bg-gray-50"> {{-- Added header background color --}}
                            <tr>
                                {{-- Table header cells, applied Tailwind th classes for padding, alignment, text size, font weight, color, uppercase, and tracking --}}
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('Grade Name') }} {{-- Translated header --}}
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('Grade Code') }} {{-- Translated header --}}
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('Description') }} {{-- Translated header --}}
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{-- Centered header content --}}
                                    {{ __('Requires Approval') }} {{-- Translated header --}}
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('Actions') }} {{-- Translated header --}}
                                </th>
                            </tr>
                        </thead>
                        {{-- Table body --}}
                        {{-- Added body background color and row dividers --}}
                        <tbody class="bg-white divide-y divide-gray-200">
                            {{-- Loop through the collection of grades passed to the view --}}
                            @foreach ($grades as $grade)
                                {{-- Table row, added hover effect --}}
                                <tr class="hover:bg-gray-100">
                                    {{-- Table data cells, applied Tailwind td classes for padding, whitespace handling, text size, and color --}}
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $grade->name ?? 'N/A' }} {{-- Display grade name with fallback --}}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $grade->code ?? 'N/A' }} {{-- Display grade code with fallback --}}
                                    </td>
                                    {{-- Removed whitespace-nowrap for description cell to allow text wrapping --}}
                                    <td class="px-6 py-4 text-sm text-gray-900">
                                        {{ $grade->description ?? '-' }} {{-- Display description with fallback --}}
                                    </td>
                                    {{-- Requires Approval column with check/cross icons based on boolean value --}}
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                                        {{-- Centered content --}}
                                        {{-- Replicated badge styling using Tailwind classes based on requires_approval boolean --}}
                                        @if ($grade->requires_approval)
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-800">
                                                {{-- Green checkmark icon --}}
                                                <svg class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24"
                                                    stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M5 13l4 4L19 7" />
                                                </svg>
                                                {{ __('Yes') }} {{-- Translated text (or could just be the icon) --}}
                                            </span>
                                        @else
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-800">
                                                {{-- Red cross icon --}}
                                                <svg class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24"
                                                    stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M6 18L18 6M6 6l12 12" />
                                                </svg>
                                                {{ __('No') }} {{-- Translated text (or could just be the icon) --}}
                                            </span>
                                        @endif
                                    </td>
                                    {{-- Actions column with links/buttons for view, edit, and delete --}}
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium"> {{-- Added font-medium --}}
                                        {{-- Link to view grade details. Assuming a route named 'admin.grades.show' exists. --}}
                                        {{-- Standardized link styling using Tailwind classes --}}
                                        <a href="{{ route('admin.grades.show', $grade) }}"
                                            class="text-blue-600 hover:text-blue-800 inline-flex items-center mr-4">
                                            {{ __('View') }} {{-- Translated action --}}
                                        </a>
                                        {{-- Optional: Edit button, wrapped in @can('update', $grade) --}}
                                        {{-- Assuming a route named 'admin.grades.edit' exists --}}
                                        {{-- @can('update', $grade) --}}
                                        <a href="{{ route('admin.grades.edit', $grade) }}"
                                            class="text-indigo-600 hover:text-indigo-800 inline-flex items-center mr-4">
                                            {{ __('Edit') }} {{-- Translated action --}}
                                        </a>
                                        {{-- @endcan --}} {{-- End can check --}}
                                        {{-- Optional: Delete button, wrapped in @can('delete', $grade) --}}
                                        {{-- This would typically be a form submission or Livewire action --}}
                                        {{-- Example Form for Delete (requires client-side confirm dialog) --}}
                                        {{-- Assuming a route named 'admin.grades.destroy' exists --}}
                                        {{-- @can('delete', $grade) --}}
                                        <form action="{{ route('admin.grades.destroy', $grade) }}" method="POST"
                                            onsubmit="return confirm('{{ __('Are you sure you want to delete this grade?') }}');"
                                            {{-- JavaScript confirm dialog with translated message --}} class="inline-block"> {{-- Use inline-block to keep the form element in line with other action links --}}
                                            @csrf {{-- CSRF token for security --}}
                                            @method('DELETE') {{-- Method spoofing for DELETE request --}}
                                            {{-- Delete button --}}
                                            <button type="submit"
                                                class="text-red-600 hover:text-red-800 inline-flex items-center">
                                                {{ __('Delete') }} {{-- Translated action --}}
                                            </button>
                                        </form>
                                        {{-- Or if using a modal/Livewire for delete confirmation, you would call a JavaScript or Livewire method here --}}
                                        {{-- <button type="button" onclick="confirmDelete({{ $grade->id }})" class="text-red-600 hover:text-red-800 inline-flex items-center">
                                            {{ __('Delete') }}
                                        </button> --}}
                                        {{-- @endcan --}} {{-- End can check --}}
                                    </td> {{-- End actions cell --}}
                                </tr> {{-- End table row --}}
                            @endforeach {{-- End loop through grades --}}
                            {{-- Optional: Message if collection is empty (handled by the @if $grades->isEmpty() check outside the table) --}}
                        </tbody> {{-- End table body --}}
                    </table> {{-- End table --}}
                </div> {{-- End overflow-x-auto container --}}

                {{-- Pagination links --}}
                {{-- Check if the grades collection has multiple pages --}}
                @if ($grades->hasPages())
                    <div class="mt-4">
                        {{ $grades->links() }} {{-- Renders Tailwind-styled pagination links if available --}}
                    </div>
                @endif {{-- End hasPages check --}}
            @endif {{-- End $grades->isEmpty() check --}}

        </div> {{-- End main container --}}
    @endsection {{-- End content section --}}

</body> {{-- End body --}}

</html> {{-- End HTML document --}}
