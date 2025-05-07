{{--
    resources/views/livewire/human-resource/structure/positions.blade.php

    This view displays a table of positions and includes Livewire actions for management.
    It is rendered by the App\Livewire\HumanResource\Structure\Positions Livewire component.
    It MUST have a single root HTML element.
    Accessing computed properties via $this->propertyName as suggested.
--}}

{{-- FIX: Wrap ALL content in a single root element for Livewire --}}
<div class="positions-container"> {{-- Use a single wrapping div that spans the entire content --}}

    {{-- Content starts here --}}
    <div class="demo-inline-spacing">
        {{-- Button to open the modal for adding a new position --}}
        <button wire:click.prevent='showNewPositionModal' type="button" class="btn btn-primary" data-bs-toggle="modal"
            data-bs-target="#positionModal">
            <span class="ti-xs ti ti-plus me-1"></span>{{ __('Add New Position') }}
        </button>
    </div>

    <br> {{-- Keep the br tag, but it's now inside the root div --}}

    <div class="card"> {{-- Bootstrap card for the table container --}}
        <h5 class="card-header">
            <i class="ti ti-map-pin ti-lg text-info me-3"></i>{{ __('Positions') }}
        </h5>

        <div class="table-responsive text-nowrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>{{ __('ID') }}</th>
                        <th>{{ __('Name') }}</th>
                        {{-- <th>Coordinator</th> --}}
                        {{-- Displaying users_count based on the component's computed property --}}
                        <th>{{ __('Users Count') }}</th>
                        <th>{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="table-border-bottom-0">
                    {{-- Loop through the positions - FIX: Use $this->positions --}}
                    @forelse($this->positions as $position)
                        <tr>
                            <td>{{ $position->id }}</td>
                            <td><strong>{{ $position->name }}</strong></td>
                            {{-- Displaying the users_count from the relationship count --}}
                            <td>{{ $position->users_count }}</td>

                            <td>
                                <div style="display: flex">
                                    <div class="dropdown">
                                        <button type="button" class="btn p-0 dropdown-toggle hide-arrow"
                                            data-bs-toggle="dropdown"><i class="ti ti-dots-vertical"></i></button>
                                        <div class="dropdown-menu">
                                            {{-- Edit button - FIX: Change href="" to href="#" --}}
                                            <a class="dropdown-item" href="#"
                                                wire:click.prevent="showEditPositionModal({{ $position }})"
                                                data-bs-toggle="modal" data-bs-target="#positionModal"><i
                                                    class="ti ti-pencil me-1"></i>
                                                {{ __('Edit') }}</a>
                                            {{-- Delete button - Triggers confirmation state - FIX: Change href="" to href="#" --}}
                                            <a class="dropdown-item" href="#"
                                                wire:click.prevent="confirmDeletePosition({{ $position->id }})">
                                                <i class="ti ti-trash me-1"></i> {{ __('Delete') }}
                                            </a>
                                        </div>
                                    </div>
                                    {{-- Show confirmation button only if this position's ID is confirmed for deletion --}}
                                    @if ($confirmedId === $position->id)
                                        {{-- Delete button (after confirmation) - Passes the position model --}}
                                        <button wire:click.prevent='deletePosition({{ $position }})' type="button"
                                            class="btn btn-sm btn-danger waves-effect waves-light ms-2">{{ __('Sure?') }}</button>
                                        {{-- Added ms-2 for spacing --}}
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        {{-- Message when no positions are found --}}
                        <tr>
                            <td colspan="4"> {{-- Updated colspan to match number of columns --}}
                                <div class="mt-2 mb-2 text-center"> {{-- Use Bootstrap text-center class --}}
                                    <h3 class="mb-1 mx-2">{{ __('Oopsie-doodle!') }}</h3>
                                    <p class="mb-4 mx-2">
                                        {{ __('No data found, please sprinkle some data in my virtual bowl, and let the fun begin!') }}
                                    </p>
                                    {{-- Button to open the modal for adding a new position --}}
                                    <button class="btn btn-label-primary mb-4" wire:click.prevent='showNewPositionModal'
                                        data-bs-toggle="modal" data-bs-target="#positionModal">
                                        {{ __('Add New Position') }}
                                    </button>
                                    <div>
                                        {{-- Illustration image --}}
                                        <img src="{{ asset('assets/img/illustrations/page-misc-under-maintenance.png') }}"
                                            width="200" class="img-fluid"> {{-- Bootstrap class --}}
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination links --}}
        {{-- Assumes $positions is a paginated collection - FIX: Use $this->positions --}}
        @if ($this->positions instanceof \Illuminate\Pagination\LengthAwarePaginator)
            {{-- Check if it's a paginator --}}
            <div class="mt-3 px-4"> {{-- Add padding/margin for pagination --}}
                {{ $this->positions->links() }} {{-- Renders pagination links (styled by paginationTheme in component) --}}
            </div>
        @endif

    </div> {{-- End card --}}

    {{-- Include the modal partial --}}
    {{-- Ensure this modal partial itself has a single root element inside it as well if it's also a Livewire component,
     or if it's a standard Blade include, its structure is fine as long as it's included within the parent's single root. --}}
    @include('_partials/_modals/modal-position')

    {{-- Content ends here --}}

</div> {{-- End of the single wrapping div --}}
