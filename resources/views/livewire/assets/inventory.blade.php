<div>
    @php
        $configData = Helper::appClasses();
    @endphp

    @section('title', __('Assets Inventory'))

    @section('vendor-style')
    @endsection

    @section('page-style')
        <style>
            .btn-tr {
                opacity: 0;
            }

            tr:hover .btn-tr {
                display: inline-block;
                opacity: 1;
            }

            tr:hover .td {
                color: #7367f0 !important;
            }
        </style>
    @endsection

    <div class="card">
        <div class="card-header d-flex justify-content-between">
            <h5 class="card-title m-0 me-2">{{ __('Assets Inventory') }}</h5>

            <div class="dt-action-buttons d-flex align-items-center gap-2">
                <div id="DataTables_Table_0_filter" class="dataTables_filter">
                    <label>
                        <input autofocus wire:model.live="search_term" type="text" class="form-control"
                            placeholder="{{ __('Search (Tag ID, Brand, Model, Serial Number...)') }}">
                    </label>
                </div>
                <div class="dt-buttons">
                    @can('create equipment')
                        <button wire:click.prevent="create" type="button" class="btn btn-primary" data-bs-toggle="modal"
                            data-bs-target="#assetModal">
                            <span class="ti-xs ti ti-plus me-1"></span>{{ __('Add New Asset') }}
                        </button>
                    @endcan
                </div>
            </div>
        </div>

        <div class="table-responsive text-nowrap">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>{{ __('Tag ID') }}</th>
                        <th>{{ __('Type') }}</th>
                        <th>{{ __('Brand') }}</th>
                        <th>{{ __('Model') }}</th>
                        <th>{{ __('Serial Number') }}</th>
                        <th>{{ __('Availability') }}</th>
                        <th>{{ __('Actions') }}</th>
                    </tr>
                </thead>

                <tbody class="table-border-bottom-0">
                    @forelse ($assets as $asset)
                        <tr>
                            <td><i class="ti ti-tag ti-sm text-danger me-1"></i> <strong>{{ $asset->tag_id }}</strong>
                            </td>
                            <td>{{ __($asset->asset_type) }}</td>
                            <td>{{ $asset->brand }}</td>
                            <td>{{ $asset->model }}</td>
                            <td>{{ $asset->serial_number }}</td>
                            <td>
                                <span
                                    class="badge bg-label-{{ $colors[$asset->availability_status] ?? 'secondary' }} me-1">
                                    {{ __(ucwords(str_replace('_', ' ', $asset->availability_status))) }}
                                </span>
                            </td>

                            <td>
                                {{-- View active loan if applicable --}}
                                @if ($asset->availability_status === 'on_loan' && $asset->current_transaction)
                                    <a href="{{ route('resource-management.admin.bpm.loan-transactions.show', $asset->current_transaction->id) }}"
                                        class="btn btn-sm btn-tr rounded-pill btn-icon btn-outline-info waves-effect"
                                        title="{{ __('View Active Loan') }}">
                                        <span class="ti ti-file-text"></span>
                                    </a>
                                @endif

                                {{-- Edit --}}
                                @can('update equipment')
                                    <button wire:click.prevent="edit({{ $asset->id }})" type="button"
                                        class="btn btn-sm btn-tr rounded-pill btn-icon btn-outline-secondary waves-effect"
                                        data-bs-toggle="modal" data-bs-target="#assetModal" title="{{ __('Edit') }}">
                                        <span class="ti ti-pencil"></span>
                                    </button>
                                @endcan

                                {{-- Delete --}}
                                @can('delete equipment')
                                    <button wire:click.prevent="confirmDelete({{ $asset->id }})" type="button"
                                        class="btn btn-sm btn-tr rounded-pill btn-icon btn-outline-danger waves-effect"
                                        title="{{ __('Delete') }}">
                                        <span class="ti ti-trash"></span>
                                    </button>
                                @endcan

                                {{-- Confirm Deletion --}}
                                @if ($confirmedId === $asset->id)
                                    <button wire:click.prevent="delete" type="button"
                                        class="btn btn-sm btn-danger waves-effect waves-light">
                                        {{ __('Sure?') }}
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <div class="mt-2 mb-2 text-center">
                                    <h3 class="mb-1">{{ __('Oops!') }}</h3>
                                    <p class="mb-4">{{ __('No equipment assets found in the inventory.') }}</p>

                                    @can('create equipment')
                                        <button wire:click.prevent="create" class="btn btn-label-primary mb-4"
                                            data-bs-toggle="modal" data-bs-target="#assetModal">
                                            {{ __('Add New Asset') }}
                                        </button>
                                    @endcan

                                    <div>
                                        <img src="{{ asset('assets/img/illustrations/page-misc-under-maintenance.png') }}"
                                            width="200" class="img-fluid" alt="{{ __('No data') }}">
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="card-body">
            <div class="row">
                <div class="col-md-5">
                    <div class="dataTables_info" role="status" aria-live="polite">
                        {{ __('Showing') }} {{ $assets->firstItem() }} {{ __('to') }} {{ $assets->lastItem() }}
                        {{ __('of') }} {{ $assets->total() }} {{ __('entries') }}
                    </div>
                </div>
                <div class="col-md-7">
                    <div class="dataTables_paginate paging_simple_numbers">
                        {{ $assets->links() }}
                    </div>
                </div>
            </div>
        </div>

        {{-- Modal --}}
        @include('_partials._modals.modal-inventory')

    </div>
</div>
