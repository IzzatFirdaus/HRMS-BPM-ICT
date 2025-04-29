@php
    // This helper function might be specific to your template,
    // keep it if you still use it for global config data.
    // Using service container resolution to potentially appease static analysis tools
    // that might misinterpret direct static calls within Blade context.
    // Assuming your Helper class is located at App\Helpers\Helper and registered in the container.
    $configData = app('App\Helpers\Helper')->appClasses();

    // The logic that previously used $this->... is now assumed to be in the Livewire component's render method.

@endphp {{-- This @endphp tag closes the @php tag on line 1 --}}

{{-- Set the page title in the layout --}}
@section('title', 'Centers - Structure')

{{-- Main container div, often provided by the layout --}}
{{-- If this is a Livewire component root div, it might not need extra classes --}}
<div>

    {{-- Header section with Add New Center button --}}
    {{-- Replaced demo-inline-spacing with Tailwind flex and gap utilities --}}
    <div class="flex flex-wrap items-center justify-between mb-6 gap-4">
        {{-- Add New Center Button --}}
        {{-- Converted btn btn-primary to Tailwind classes, removed Bootstrap data attributes for modal toggle --}}
        <button wire:click.prevent='showNewCenterModal' type="button"
            class="inline-flex items-center px-5 py-2.5 bg-blue-500 text-white font-semibold rounded-md shadow-sm hover:bg-blue-600 focus:outline-none focus:ring focus:ring-blue-500 focus:ring-opacity-50 transition">
            {{-- Replaced Bootstrap/ThemeIsle icon class with a simple plus SVG --}}
            <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            {{ __('Add New Center') }} {{-- Translated string --}}
        </button>
    </div>

    {{-- Added spacing below the header block --}}
    <div class="mb-4"></div>

    {{-- Card-like container for the table --}}
    {{-- Converted card class to Tailwind --}}
    <div class="bg-white shadow-md rounded-lg">
        {{-- Card Header --}}
        {{-- Converted card-header and icon classes to Tailwind --}}
        <h5 class="px-6 py-4 border-b border-gray-200 text-xl font-semibold text-gray-800">
            {{-- Replaced Bootstrap/ThemeIsle icon class with a building SVG and Tailwind text-blue-500 --}}
            <svg class="w-6 h-6 inline-block text-blue-500 mr-3 align-middle" fill="none" stroke="currentColor"
                viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                </path>
            </svg>
            {{ __('Centers') }} {{-- Translated string --}}
        </h5>
        {{-- Table Container with responsiveness --}}
        {{-- Converted table-responsive text-nowrap to Tailwind overflow-x-auto and whitespace-nowrap --}}
        <div class="overflow-x-auto whitespace-nowrap">
            {{-- Table --}}
            {{-- Converted table class to Tailwind --}}
            <table class="min-w-full divide-y divide-gray-200">
                {{-- Table Header --}}
                {{-- Converted thead class to Tailwind --}}
                <thead class="bg-gray-50">
                    <tr>
                        {{-- Converted th classes to Tailwind --}}
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {{ __('ID') }}</th> {{-- Translated string --}}
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {{ __('Name') }}</th> {{-- Translated string --}}
                        {{-- <th>Supervisor</th> --}} {{-- Commented out as in original --}}
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {{ __('Members Count') }}</th> {{-- Translated string --}}
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {{ __('Working Hours') }}</th> {{-- Translated string --}}
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {{ __('Weekends') }}</th> {{-- Translated string --}}
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {{ __('Actions') }}</th> {{-- Translated string --}}
                    </tr>
                </thead>
                {{-- Table Body --}}
                {{-- Converted tbody and table-border-bottom-0 classes to Tailwind divide-y --}}
                <tbody class="bg-white divide-y divide-gray-200">
                    {{-- Loop through centers --}}
                    @forelse($centers as $center)
                        {{-- Added hover effect --}}
                        <tr class="hover:bg-gray-100">
                            {{-- Table Cells --}}
                            {{-- Converted td classes to Tailwind --}}
                            <td class="px-6 py-4 text-sm text-gray-900">{{ $center->id }}</td>
                            <td class="px-6 py-4 text-sm text-gray-900 font-semibold">{{ $center->name }}</td>
                            {{-- Kept font-semibold --}}
                            {{-- <td>... Supervisor section (commented out) ...</td> --}}
                            {{-- Displaying the pre-calculated members count from the center object --}}
                            <td class="px-6 py-4 text-sm text-gray-900">
                                {{ $center->members_count ?? 0 }} {{-- Assuming you add 'members_count' property in component --}}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                {{-- Converted badge bg-label-success to Tailwind classes --}}
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-800">
                                    {{ $center->start_work_hour . ' - ' . $center->end_work_hour }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                {{-- Displaying the pre-calculated days name from the center object --}}
                                @php
                                    $daysName = $center->days_name ?? ''; // Assuming you add 'days_name' property in component
                                @endphp
                                {{-- Converted badge bg-label-secondary to Tailwind classes --}}
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-800">
                                    {{ $daysName }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm font-medium">
                                {{-- Actions Column: Using flex for layout --}}
                                <div class="flex items-center space-x-2"> {{-- Added space-x for spacing --}}
                                    {{-- Dropdown for Actions (converted from Bootstrap dropdown to Tailwind + Alpine.js) --}}
                                    <div x-data="{ open: false }" @click.outside="open = false" class="relative">
                                        {{-- Dropdown Toggle Button --}}
                                        {{-- Converted btn p-0 dropdown-toggle hide-arrow to Tailwind --}}
                                        <button @click="open = !open" type="button"
                                            class="p-1 text-gray-500 hover:text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 rounded-md transition">
                                            {{-- Adjusted padding --}}
                                            {{-- Replaced Bootstrap/ThemeIsle icon class with a simple dots SVG --}}
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z">
                                                </path>
                                            </svg>
                                        </button>
                                        {{-- Dropdown Menu --}}
                                        {{-- Converted dropdown-menu to Tailwind classes, added x-show and x-cloak for Alpine --}}
                                        <div x-show="open" x-cloak
                                            class="absolute right-0 z-10 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 focus:outline-none origin-top-right"
                                            @click.outside="open = false"> {{-- Close dropdown on click outside --}}
                                            <div class="py-1" role="menu" aria-orientation="vertical"
                                                aria-labelledby="options-menu">
                                                {{-- Edit action --}}
                                                {{-- Converted dropdown-item and icon class to Tailwind, removed Bootstrap modal toggle --}}
                                                <a wire:click.prevent='showEditCenterModal({{ $center }})'
                                                    @click="open = false" {{-- Close dropdown on click --}}
                                                    class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900"
                                                    role="menuitem">
                                                    {{-- Replaced Bootstrap/ThemeIsle icon class with a simple pencil SVG --}}
                                                    <svg class="w-4 h-4 inline-block mr-1" fill="none"
                                                        stroke="currentColor" viewBox="0 0 24 24"
                                                        xmlns="http://www.w3.org/2000/svg">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z">
                                                        </path>
                                                    </svg>
                                                    {{ __('Edit') }} {{-- Translated string --}}
                                                </a>
                                                {{-- Delete action (triggers confirmation) --}}
                                                {{-- Converted dropdown-item and icon class to Tailwind --}}
                                                <a wire:click.prevent='confirmDeleteCenter({{ $center->id }})'
                                                    @click="open = false" {{-- Close dropdown on click --}}
                                                    class="block px-4 py-2 text-sm text-red-600 hover:bg-gray-100 hover:text-red-900"
                                                    role="menuitem">
                                                    {{-- Replaced Bootstrap/ThemeIsle icon class with a simple trash SVG --}}
                                                    <svg class="w-4 h-4 inline-block mr-1" fill="none"
                                                        stroke="currentColor" viewBox="0 0 24 24"
                                                        xmlns="http://www.w3.org/2000/svg">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                                        </path>
                                                    </svg>
                                                    {{ __('Delete') }} {{-- Translated string --}}
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                    {{-- Delete Confirmation Button --}}
                                    @if ($confirmedId === $center->id)
                                        {{-- Converted btn btn-sm btn-danger waves-effect waves-light to Tailwind --}}
                                        <button wire:click.prevent='deleteCenter({{ $center }})' type="button"
                                            class="inline-flex items-center px-3 py-1.5 bg-red-600 text-white font-semibold text-sm rounded shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 transition">
                                            {{ __('Sure?') }} {{-- Translated string --}}
                                        </button>
                                    @endif
                                </div> {{-- End Actions flex --}}
                            </td>
                        </tr>
                    @empty
                        {{-- Empty state message --}}
                        <tr>
                            {{-- Span across all columns --}}
                            <td colspan="6" class="px-6 py-4 text-center text-gray-600"> {{-- Added padding, text-center, and text color --}}
                                <div class="my-4"> {{-- Added vertical margin --}}
                                    <h3 class="mb-1 text-xl font-semibold text-gray-800">{{ __('Oopsie-doodle!') }}
                                    </h3> {{-- Translated string, added spacing and text styles --}}
                                    <p class="mb-4">
                                        {{ __('No data found, please sprinkle some data in my virtual bowl, and let the fun begin!') }}
                                        {{-- Translated string --}}
                                    </p>
                                    {{-- Add New Center Button in empty state --}}
                                    {{-- Converted btn btn-label-primary to Tailwind classes, removed Bootstrap modal toggle --}}
                                    <button wire:click.prevent='showNewCenterModal'
                                        class="inline-flex items-center px-4 py-2 bg-blue-100 text-blue-800 font-bold rounded shadow-sm hover:bg-blue-200 focus:outline-none focus:ring focus:ring-blue-400 transition mb-4">
                                        {{ __('Add New Center') }} {{-- Translated string --}}
                                    </button>
                                    <div>
                                        {{-- Converted img-fluid to Tailwind max-w-full and h-auto --}}
                                        <img src="{{ asset('assets/img/illustrations/page-misc-under-maintenance.png') }}"
                                            width="200" class="max-w-full h-auto mx-auto"
                                            alt="{{ __('No data illustration') }}"> {{-- Added alt text --}}
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div> {{-- End table container --}}
    </div> {{-- End card --}}

    {{-- Include the modal file (assuming it's updated separately with Tailwind or Livewire state handling) --}}
    @include('_partials/_modals/modal-center')

</div> {{-- End Livewire root div --}}
