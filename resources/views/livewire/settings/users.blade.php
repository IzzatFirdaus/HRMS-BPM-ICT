<div>

    @php
        // Assuming Helper is a facade or globally available
        // use App\Helpers\Helper; // Uncomment if Helper is a facade
        $configData = Helper::appClasses();
    @endphp

    {{-- The title needs to be defined or passed dynamically --}}
    @section('title', __('Default Page Title')) {{-- Added a default localized title --}}

    @section('vendor-style')
        {{-- Add vendor specific CSS files here --}}
    @endsection

    @section('page-style')
        {{-- Add page specific CSS files here --}}
    @endsection

    {{-- Edit HERE --}}
    {{-- This is the main content area of your Livewire component view. --}}
    {{-- What would you like to add here? --}}

    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="py-3 mb-4">
            {{-- Dynamic or static page title --}}
            @yield('page-title', __('Page Content Title')) {{-- Added a placeholder for page content title --}}
        </h4>

        <p>{{ __('This is where your page content goes.') }}</p>

        {{-- Example: Displaying some data --}}
        {{-- @if (isset($items))
        <ul>
            @foreach ($items as $item)
                <li>{{ $item->name }}</li>
            @endforeach
        </ul>
    @else
        <p>{{ __('No items to display.') }}</p>
    @endif --}}

        {{-- Example: A simple form --}}
        {{-- <form wire:submit="save">
        <div class="mb-3">
            <label for="name" class="form-label">{{ __('Name') }}</label>
            <input type="text" class="form-control" id="name" wire:model="name">
        </div>
        <button type="submit" class="btn btn-primary">{{ __('Save') }}</button>
    </form> --}}

    </div>
    {{-- End Edit HERE --}}


    @push('custom-scripts')
        {{-- Add custom scripts here --}}
    @endpush
</div>
