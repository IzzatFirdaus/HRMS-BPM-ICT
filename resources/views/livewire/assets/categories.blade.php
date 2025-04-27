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
                    <div class="col-md-6">
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
                                                class="btn btn-sm btn-tr rounded-pill btn-icon btn-outline-danger waves-effect"
                                                wire:click.stop='confirmDeleteCategory({{ $category->id }})'>
                                                <span class="ti ti-trash"></span>
                                            </button>
                                            @if ($confirmedCategoryId === $category->id)
                                                <button wire:click.stop='deleteCategory({{ $category }})'
                                                    type="button" class="btn btn-sm btn-danger ms-2">
                                                    {{ __('Sure?') }}
                                                </button>
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
                                            <button class="btn btn-primary" data-bs-toggle="modal"
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
            </div>
        </div>

        <!-- Sub-Categories Column -->
        <div class="col-lg-6">
            <div class="d-flex justify-content-start mb-3">
                <button wire:click='showNewSubCategoryModal' type="button" data-bs-toggle="modal"
                    data-bs-target="#subCategoryModal" class="btn btn-primary waves-effect">
                    <span class="ti-xs ti ti-plus me-1"></span>
                    {{ __('Add New Sub-Category') }}
                </button>
            </div>

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title m-0">{{ __('Sub-Categories') }}</h5>
                    <div class="col-md-6">
                        <input wire:model.live.debounce.300ms="search_term_sub_categories" type="text"
                            class="form-control" placeholder="{{ __('Search (ID, Sub-Category...)') }}">
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
                            @forelse ($subCategories as $subCategory)
                                <tr>
                                    <td>
                                        <i class="ti ti-tag ti-sm text-primary me-2"></i>
                                        <strong>{{ $subCategory->id }}</strong>
                                    </td>
                                    <td class="td">{{ $subCategory->name }}</td>
                                    <td class="text-end">
                                        <div class="btn-group" role="group">
                                            <button type="button"
                                                class="btn btn-sm btn-tr rounded-pill btn-icon btn-outline-secondary waves-effect"
                                                wire:click='showEditSubCategoryModal({{ $subCategory }})'
                                                data-bs-toggle="modal" data-bs-target="#subCategoryModal">
                                                <span class="ti ti-pencil"></span>
                                            </button>
                                            <button type="button"
                                                class="btn btn-sm btn-tr rounded-pill btn-icon btn-outline-danger waves-effect"
                                                wire:click='confirmDeleteSubCategory({{ $subCategory->id }})'>
                                                <span class="ti ti-trash"></span>
                                            </button>
                                            @if ($confirmedSubCategoryId === $subCategory->id)
                                                <button wire:click='deleteSubCategory({{ $subCategory }})'
                                                    type="button" class="btn btn-sm btn-danger ms-2">
                                                    {{ __('Sure?') }}
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3">
                                        <div class="text-center py-4">
                                            <h3 class="mb-2">{{ __('No sub-categories found') }}</h3>
                                            <p class="mb-3">
                                                {{ __('Start by adding your first sub-category') }}
                                            </p>
                                            <button class="btn btn-primary" data-bs-toggle="modal"
                                                data-bs-target="#subCategoryModal">
                                                {{ __('Add New Sub-Category') }}
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
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
