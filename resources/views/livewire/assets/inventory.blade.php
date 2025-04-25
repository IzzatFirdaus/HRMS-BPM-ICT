<div>

    @php
        $configData = Helper::appClasses();
    @endphp

    @section('title', 'Inventory - Assets')

    @section('vendor-style')

    @endsection

    @section('page-style')
        <style>
            /* Keep existing styles */
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

            /* Add styles for status badges if needed, or rely on existing theme classes */
        </style>
    @endsection

    <div class="card">
        <div class="card-header d-flex justify-content-between">
            <h5 class="card-title m-0 me-2">{{ __('Assets Inventory') }}</h5> {{-- Adjusted title --}}
            <div
                class="dt-action-buttons text-xl-end text-lg-start text-md-end text-start d-flex align-items-center justify-content-end flex-md-row flex-column mb-3 mb-md-0 gap-2">
                <div id="DataTables_Table_0_filter" class="dataTables_filter">
                    <label>
                        {{-- Search input - wire:model.live is correct for Livewire --}}
                        <input autofocus wire:model.live="search_term" type="text" class="form-control"
                            placeholder="{{ __('Search (Tag ID, Serial Number, Description...)') }}">
                        {{-- Adjusted placeholder --}}
                    </label>
                </div>
                <div class="dt-buttons">
                    {{-- Add New Asset button - wire:click and modal are correct for Livewire --}}
                    {{-- Ensure user has permission to create equipment --}}
                    @can('create equipment')
                        {{-- Assuming a permission check --}}
                        <button wire:click.prevent='showNewAssetModal' type="button" class="btn btn-primary"
                            data-bs-toggle="modal" data-bs-target="#assetModal">
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
                        <th class="col-1">{{ __('Tag ID') }}</th> {{-- Changed to Tag ID as per design --}}
                        <th>{{ __('Type') }}</th> {{-- Add equipment type --}}
                        <th>{{ __('Brand') }}</th> {{-- Add brand --}}
                        <th>{{ __('Model') }}</th> {{-- Add model --}}
                        <th>{{ __('Serial Number') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th>{{ __('Actions') }}</th> {{-- Added header for Actions --}}
                    </tr>
                </thead>
                <tbody class="table-border-bottom-0">
                    {{-- Loop through equipment assets (assuming $assets variable is available from Livewire component) --}}
                    @forelse ($assets as $asset)
                        {{-- Assuming $assets are Equipment models --}}
                        <tr>
                            {{-- Display Tag ID --}}
                            <td><i class="ti ti-tag ti-sm text-danger me-1"></i> <strong>{{ $asset->tag_id }}</strong>
                            </td> {{-- Display Tag ID --}}
                            {{-- Display Type, Brand, Model as per Equipment model --}}
                            <td>{{ __($asset->asset_type) }}</td> {{-- Assuming asset_type column exists --}}
                            <td>{{ $asset->brand }}</td> {{-- Assuming brand column exists --}}
                            <td>{{ $asset->model }}</td> {{-- Assuming model column exists --}}
                            {{-- Display Serial Number --}}
                            <td>{{ $asset->serial_number }}</td>

                            {{-- Display Status with dynamic badge --}}
                            <td>
                                {{-- Ensure $colors array in Livewire component has classes for 'available', 'on_loan', 'under_maintenance', 'disposed' --}}
                                {{-- Consider using a reusable status badge component here --}}
                                <span
                                    class="badge bg-label-{{ $colors[$asset->status] ?? 'secondary' }} me-1">{{ __($asset->status) }}</span>
                            </td>

                            {{-- Actions Column --}}
                            <td>
                                {{-- Button/Link to view active loan details if the asset is on loan --}}
                                {{-- Assuming the Equipment model has an 'activeLoanTransaction' relationship --}}
                                @if ($asset->status === 'on_loan' && $asset->activeLoanTransaction)
                                    <a href="{{ route('loan-transactions.show', $asset->activeLoanTransaction) }}"
                                        class="btn btn-sm btn-tr rounded-pill btn-icon btn-outline-info waves-effect"
                                        title="{{ __('View Active Loan') }}"> {{-- Example icon and color --}}
                                        <span class="ti ti-file-text"></span> {{-- Example icon --}}
                                    </a>
                                @endif

                                {{-- Button to edit the asset --}}
                                {{-- Ensure user has permission to update equipment --}}
                                @can('update equipment')
                                    {{-- Assuming a permission check --}}
                                    {{-- wire:click to show modal for editing asset --}}
                                    <button wire:click.prevent='showEditAssetModal({{ $asset->id }})' type="button"
                                        class="btn btn-sm btn-tr rounded-pill btn-icon btn-outline-secondary waves-effect"
                                        data-bs-toggle="modal" data-bs-target="#assetModal" title="{{ __('Edit') }}">
                                        <span class="ti ti-pencil"></span>
                                    </button>
                                @endcan

                                {{-- Button to delete the asset --}}
                                {{-- Ensure user has permission to delete equipment --}}
                                @can('delete equipment')
                                    {{-- Assuming a permission check --}}
                                    {{-- wire:click for confirming delete action --}}
                                    <button wire:click.prevent='confirmDeleteAsset({{ $asset->id }})' type="button"
                                        class="btn btn-sm btn-tr rounded-pill btn-icon btn-outline-danger waves-effect"
                                        title="{{ __('Delete') }}">
                                        <span class="ti ti-trash"></span>
                                    </button>
                                @endcan

                                {{-- Confirmation button for deletion --}}
                                @if ($confirmedId === $asset->id)
                                    {{-- Assuming confirmedId is a Livewire component property --}}
                                    <button wire:click.prevent='deleteAsset({{ $asset->id }})' type="button"
                                        class="btn btn-sm btn-danger waves-effect waves-light">{{ __('Sure?') }}</button>
                                @endif

                                {{-- Removed the commented out button ti ti-arrow-guide --}}
                                {{-- <button type="button" class="btn btn-sm btn-tr rounded-pill btn-icon btn-outline-secondary waves-effect">
                  <span class="ti ti-arrow-guide"></span>
                </button> --}}
                            </td>
                        </tr>
                    @empty
                        {{-- Message when no assets are found --}}
                        <tr>
                            <td colspan="7"> {{-- Adjusted colspan --}}
                                <div class="mt-2 mb-2" style="text-align: center">
                                    <h3 class="mb-1 mx-2">{{ __('Oopsie-doodle!') }}</h3>
                                    <p class="mb-4 mx-2">
                                        {{ __('No equipment assets found in the inventory.') }} {{-- Adjusted message --}}
                                    </p>
                                    {{-- Add New Asset button in empty state --}}
                                    @can('create equipment')
                                        {{-- Assuming a permission check --}}
                                        <button class="btn btn-label-primary mb-4" data-bs-toggle="modal"
                                            data-bs-target="#assetModal">
                                            {{ __('Add New Asset') }}
                                        </button>
                                    @endcan
                                    <div>
                                        {{-- Adjust asset path if needed --}}
                                        <img src="{{ asset('assets/img/illustrations/page-misc-under-maintenance.png') }}"
                                            width="200" class="img-fluid">
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination Links --}}
        <div class="card-body"> {{-- Added card-body wrapper --}}
            <div class="row">
                <div class="col-sm-12 col-md-5">
                    <div class="dataTables_info" role="status" aria-live="polite">
                        Showing {{ $assets->firstItem() }} to {{ $assets->lastItem() }} of {{ $assets->total() }}
                        entries
                    </div>
                </div>
                <div class="col-sm-12 col-md-7">
                    <div class="dataTables_paginate paging_simple_numbers">
                        {{ $assets->links() }} {{-- Livewire pagination links --}}
                    </div>
                </div>
            </div>
        </div>


        {{-- Modal for Add/Edit Asset - ensure this modal is included elsewhere or in this file --}}
        @include('_partials/_modals/modal-inventory') {{-- Adjust path if needed --}}

    </div>
