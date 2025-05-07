<div>
    @php
        $configData = Helper::appClasses();
    @endphp

    @section('title', 'Categories')

    @section('vendor-style')
    @endsection

    @section('page-style')
        <style>
            .btn-tr {
                opacity: 0;
                transition: opacity 0.2s ease;
            }

            tr:hover .btn-tr {
                opacity: 1;
            }

            tr:hover .td {
                color: #7367f0 !important;
                transition: color 0.2s ease;
            }

            .clickable-row {
                cursor: pointer;
            }

            .clickable-row:hover {
                background-color: #f8f9fa;
            }
        </style>
    @endsection

    <div class="row justify-content-between g-4">
        <!-- Categories Column -->
        <div class="col-lg-6">
            <div class="d-flex justify-content-start mb-3">
                <button wire:click.prevent='showNewCategoryModal' type="button" data-bs-toggle="modal"
                    data-bs-target="#categoryModal" class="btn btn-primary waves-effect">
                    <span class="ti-xs ti ti-plus me-1"></span>
                    {{ __('Add New Category') }}
                </button>
            </div>

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title m-0">{{ __('Categories') }}</h5>
                    <div class="col-md-4 col-12">
                        <input wire:model.live.debounce.300ms="search_term_categories" type="text"
                            class="form-control" placeholder="{{ __('Search (ID, Category...)') }}">
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th width="15%">{{ __('ID') }}</th>
                                <th>{{ __('Name') }}</th>
                                <th width="20%">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            {{-- Loop through the categories provided by the Livewire component --}}
                            {{-- The $categories variable is now explicitly passed from the render() method --}}
                            @forelse ($categories as $category)
                                <tr class="clickable-row" wire:click='showCategoryInfo({{ $category->id }})'
                                    data-bs-toggle="modal" data-bs-target="#categoryInfoModal">
                                    <td>
                                        <i class="ti ti-tag ti-sm text-primary me-2"></i>
                                        <strong>{{ $category->id }}</strong>
                                    </td>
                                    <td class="td">{{ $category->name }}</td>
                                    <td class="text-end">
                                        <div class="btn-group" role="group">
                                            <button type="button"
                                                class="btn btn-sm btn-tr rounded-pill btn-icon btn-outline-secondary waves-effect"
                                                wire:click.stop='showEditCategoryModal({{ $category }})'
                                                data-bs-toggle="modal" data-bs-target="#categoryModal">
                                                <span class="ti ti-pencil"></span>
                                            </button>
                                            <button type="button"
                                                class="btn btn-sm btn-tr rounded-pill btn-icon btn-outline-danger waves-effect">
                                                <span wire:click.prevent='confirmDeleteCategory({{ $category->id }})'
                                                    class="ti ti-trash"></span>
                                            </button>
                                            {{-- Show confirmation button only if this category's ID is confirmed --}}
                                            @if ($confirmedId === $category->id)
                                                <button wire:click.prevent='deleteCategory' type="button"
                                                    class="btn btn-sm btn-danger waves-effect waves-light">{{ __('Sure?') }}</button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3">
                                        <div class="text-center py-4">
                                            <h3 class="mb-2">{{ __('No categories found') }}</h3>
                                            <p class="mb-3">
                                                {{ __('Start by adding your first category') }}
                                            </p>
                                            <button wire:click.prevent='showNewCategoryModal' type="button"
                                                class="btn btn-primary" data-bs-toggle="modal"
                                                data-bs-target="#categoryModal">
                                                {{ __('Add New Category') }}
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                {{-- Pagination --}}
                @if ($categories instanceof \Illuminate\Pagination\LengthAwarePaginator)
                    <div class="mt-3 px-3">
                        {{ $categories->links() }}
                    </div>
                @endif
            </div>
        </div>

        <!-- Sub-Categories Column (Assuming this is part of the same component or structure) -->
        <div class="col-lg-6">
            {{-- TODO: Implement Sub-category display and management --}}
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title m-0">{{ __('Sub-Categories') }}</h5>
                </div>
                <div class="card-body">
                    <p>{{ __('Sub-category management section under development.') }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Modals -->
    @include('_partials/_modals/modal-category')
    @include('_partials/_modals/modal-categoryInfo')
    @include('_partials/_modals/modal-sub-category')

    @push('custom-scripts')
    @endpush
</div>
