{{-- resources/views/livewire/human-resource/structure/centers.blade.php --}}

{{-- Livewire component view file - contains only the HTML for the component --}}
{{-- Layout extension, sections, and general page structure are handled by layouts/app.blade.php --}}
{{-- Remove the initial @php block as data should come from the component class --}}
{{-- Remove @section('title') as title is set in the page using this component --}}
{{-- Remove @extends('layouts.app') and @section('content') --}}

{{-- Main root div for the Livewire component --}}
<div>
    {{-- Use classes directly in the root div if needed, or rely on the parent layout for container/padding --}}
    {{-- Example: <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 py-6"> --}}
    {{-- Assuming the parent layout provides the main container, just keep the component's structure --}}

    {{-- Header section with Add New Center button --}}
    <div class="flex flex-wrap items-center justify-between mb-6 gap-4">
        {{-- Add New Center Button --}}
        <button wire:click.prevent='showNewCenterModal' type="button"
            class="inline-flex items-center px-5 py-2.5 bg-blue-500 text-white font-semibold rounded-md shadow-sm hover:bg-blue-600 focus:outline-none focus:ring focus:ring-blue-500 focus:ring-opacity-50 transition">
            <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            {{ __('Add New Center') }}
        </button>
    </div>

    {{-- Added spacing below the header block --}}
    <div class="mb-4"></div>

    {{-- Card-like container for the table --}}
    <div class="bg-white shadow-md rounded-lg">
        {{-- Card Header --}}
        <h5 class="px-6 py-4 border-b border-gray-200 text-xl font-semibold text-gray-800">
            <svg class="w-6 h-6 inline-block text-blue-500 mr-3 align-middle" fill="none" stroke="currentColor"
                viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                </path>
            </svg>
            {{ __('Centers') }}
        </h5>
        {{-- Table Container with responsiveness --}}
        <div class="overflow-x-auto whitespace-nowrap">
            {{-- Table --}}
            <table class="min-w-full divide-y divide-gray-200">
                {{-- Table Header --}}
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {{ __('ID') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {{ __('Name') }}</th>
                        {{-- <th>Supervisor</th> --}}
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {{ __('Members Count') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {{ __('Working Hours') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {{ __('Weekends') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {{ __('Actions') }}</th>
                    </tr>
                </thead>
                {{-- Table Body --}}
                <tbody class="bg-white divide-y divide-gray-200">
                    {{-- Loop through centers --}}
                    @forelse($centers as $center)
                        <tr class="hover:bg-gray-100">
                            {{-- Table Cells --}}
                            <td class="px-6 py-4 text-sm text-gray-900">{{ $center->id }}</td>
                            <td class="px-6 py-4 text-sm text-gray-900 font-semibold">{{ $center->name }}</td>
                            {{-- <td>... Supervisor section (commented out) ...</td> --}}
                            <td class="px-6 py-4 text-sm text-gray-900">
                                {{ $center->members_count ?? 0 }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-800">
                                    {{ $center->start_work_hour . ' - ' . $center->end_work_hour }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                {{-- Using the weekends_formatted accessor from the model --}}
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-800">
                                    {{ $center->weekends_formatted }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm font-medium">
                                {{-- Actions Column: Using flex for layout --}}
                                <div class="flex items-center space-x-2">
                                    {{-- Dropdown for Actions --}}
                                    <div x-data="{ open: false }" @click.outside="open = false" class="relative">
                                        {{-- Dropdown Toggle Button --}}
                                        <button @click="open = !open" type="button"
                                            class="p-1 text-gray-500 hover:text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 rounded-md transition">
                                            {{-- Adjusted padding --}}
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z">
                                                </path>
                                            </svg>
                                        </button>
                                        {{-- Dropdown Menu --}}
                                        <div x-show="open" x-cloak
                                            class="absolute right-0 z-10 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 focus:outline-none origin-top-right"
                                            @click.outside="open = false">
                                            <div class="py-1" role="menu" aria-orientation="vertical"
                                                aria-labelledby="options-menu">
                                                {{-- Edit action - PASSING ID --}}
                                                <a wire:click.prevent="showEditCenterModal({{ $center->id }})"
                                                    {{-- <-- FIX APPLIED HERE --}} @click="open = false"
                                                    class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900"
                                                    role="menuitem">
                                                    <svg class="w-4 h-4 inline-block mr-1" fill="none"
                                                        stroke="currentColor" viewBox="0 0 24 24"
                                                        xmlns="http://www.w3.org/2000/svg">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z">
                                                        </path>
                                                    </svg>
                                                    {{ __('Edit') }}
                                                </a>
                                                {{-- Delete action (triggers confirmation) - PASSING ID --}}
                                                <a wire:click.prevent='confirmDeleteCenter({{ $center->id }})'
                                                    {{-- <-- Already fixed --}} @click="open = false"
                                                    class="block px-4 py-2 text-sm text-red-600 hover:bg-gray-100 hover:text-red-900"
                                                    role="menuitem">
                                                    <svg class="w-4 h-4 inline-block mr-1" fill="none"
                                                        stroke="currentColor" viewBox="0 0 24 24"
                                                        xmlns="http://www.w3.org/2000/svg">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                                        </path>
                                                    </svg>
                                                    {{ __('Delete') }}
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                    {{-- Delete Confirmation Button (shows next to dropdown when confirmedId matches) - PASSING ID --}}
                                    @if ($confirmedId === $center->id)
                                        <button wire:click.prevent="deleteCenter({{ $center->id }})"
                                            {{-- <-- Already fixed --}} type="button"
                                            class="inline-flex items-center px-3 py-1.5 bg-red-600 text-white font-semibold text-sm rounded shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 transition">
                                            {{ __('Sure?') }}
                                        </button>
                                    @endif
                                </div> {{-- End Actions flex --}}
                            </td>
                        </tr>
                    @empty
                        {{-- Empty state message --}}
                        <tr>
                            {{-- Span across all columns --}}
                            <td colspan="6" class="px-6 py-4 text-center text-gray-600">
                                <div class="my-4">
                                    <h3 class="mb-1 text-xl font-semibold text-gray-800">{{ __('Oopsie-doodle!') }}
                                    </h3>
                                    <p class="mb-4">
                                        {{ __('No data found, please sprinkle some data in my virtual bowl, and let the fun begin!') }}
                                    </p>
                                    {{-- Add New Center Button in empty state --}}
                                    <button wire:click.prevent='showNewCenterModal'
                                        class="inline-flex items-center px-4 py-2 bg-blue-100 text-blue-800 font-bold rounded shadow-sm hover:bg-blue-200 focus:outline-none focus:ring focus:ring-blue-400 transition mb-4">
                                        {{ __('Add New Center') }}
                                    </button>
                                    <div>
                                        <img src="{{ asset('assets/img/illustrations/page-misc-under-maintenance.png') }}"
                                            width="200" class="max-w-full h-auto mx-auto"
                                            alt="{{ __('No data illustration') }}">
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforelse {{-- Correctly closed the @forelse loop --}}
                </tbody>
            </table>
        </div> {{-- End table container --}}
    </div> {{-- End card --}}

    {{-- Pagination links --}}
    {{-- This assumes $centers is a paginated collection from the Livewire component --}}
    @if ($centers->hasPages())
        <div class="mt-4">
            {{ $centers->links() }}
        </div>
    @endif

    {{-- Include the modal file (_partials/_modals/modal-center.blade.php) --}}
    @include('_partials/_modals/modal-center')

</div> {{-- End Livewire root div --}}
