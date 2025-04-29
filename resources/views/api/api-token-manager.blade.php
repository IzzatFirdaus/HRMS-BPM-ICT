{{-- This file is likely resources/views/api/api-token-manager.blade.php or similar --}}
{{-- It uses Livewire properties and Jetstream components --}}

{{-- Assuming this is included within a layout file --}}
{{-- For a standalone view, you might need @extends and @section('content') --}}

@section('title', __('API Tokens')) {{-- Added page title --}}

<div>
    {{-- Generate API Token --}}
    <x-form-section submit="createApiToken">
        <x-slot name="title">
            {{ __('Create API Token') }}
        </x-slot>

        <x-slot name="description">
            {{ __('API tokens allow third-party services to authenticate with our application on your behalf.') }}
        </x-slot>

        <x-slot name="form">
            {{-- Use Livewire action message --}}
            <x-action-message on="created" class="mb-3"> {{-- Added mb-3 for spacing --}}
                {{ __('Created.') }}
            </x-action-message>

            {{-- Token Name --}}
            <div class="mb-3"> {{-- Bootstrap/custom class --}}
                {{-- Jetstream label component --}}
                <x-label for="name" class="form-label" value="{{ __('Token Name') }}" /> {{-- Bootstrap/custom class --}}
                {{-- Jetstream input component --}}
                {{-- Conditional class for validation error. Assumes 'is-invalid' is a custom/Bootstrap class. --}}
                <x-input id="name" type="text"
                    class="{{ $errors->has('name') ? 'is-invalid' : '' }} form-control" {{-- Added form-control, common with is-invalid --}}
                    wire:model.defer="createApiTokenForm.name" autofocus /> {{-- Changed wire:model to wire:model.defer for better performance --}}
                {{-- Jetstream input error component --}}
                <x-input-error for="name" />
            </div>

            {{-- Token Permissions --}}
            {{-- Check if Jetstream is configured with permissions --}}
            @if (Laravel\Jetstream\Jetstream::hasPermissions())
                <div>
                    <x-label class="form-label" for="permissions" value="{{ __('Permissions') }}" />
                    {{-- Bootstrap/custom class --}}

                    <div class="mt-2 row"> {{-- Bootstrap grid classes --}}
                        @foreach (Laravel\Jetstream\Jetstream::$permissions as $permission)
                            <div class="col-md-6"> {{-- Using md breakpoint for grid columns --}}
                                <div class="mb-3 form-check"> {{-- Bootstrap/custom class --}}
                                    {{-- Jetstream checkbox component --}}
                                    <x-checkbox wire:model.defer="createApiTokenForm.permissions" {{-- Changed wire:model to wire:model.defer --}}
                                        id="{{ 'create-' . $permission }}" :value="$permission" />
                                    {{-- Bootstrap/custom class --}}
                                    <label class="form-check-label" for="{{ 'create-' . $permission }}">
                                        {{ $permission }} {{-- Permission names might need translation depending on setup --}}
                                    </label>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </x-slot>

        <x-slot name="actions">
            {{-- Use Livewire action message for saving state --}}
            <x-action-message on="creating" class="me-3"> {{-- Added me-3 for spacing, Bootstrap spacing --}}
                {{ __('Creating...') }} {{-- Added translation/text for pending state --}}
            </x-action-message>

            {{-- Jetstream button component --}}
            {{-- Consider adding Bootstrap button classes like 'btn btn-primary' if not handled by x-button --}}
            <x-button>
                {{ __('Create') }}
            </x-button>
        </x-slot>
    </x-form-section>

    {{-- Check if user has any API tokens --}}
    {{-- Use direct property access without $this-> --}}
    @if ($user->tokens->isNotEmpty())

        <x-section-border /> {{-- Jetstream component for a divider --}}

        {{-- Manage API Tokens --}}
        <div class="mt-4">
            <x-action-section>
                <x-slot name="title">
                    {{ __('Manage API Tokens') }}
                </x-slot>

                <x-slot name="description">
                    {{ __('You may delete any of your existing tokens if they are no longer needed.') }}
                </x-slot>

                {{-- API Token List --}}
                <x-slot name="content">
                    <div>
                        {{-- Use direct property access without $this-> --}}
                        @foreach ($user->tokens->sortBy('name') as $token)
                            {{-- Using Bootstrap d-flex and justify-content-between --}}
                            <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                {{-- Added alignment and border --}}
                                <div class="fw-medium text-gray-900"> {{-- Bootstrap/custom class, added text-gray-900 --}}
                                    {{ $token->name }}
                                </div>

                                <div class="d-flex align-items-center"> {{-- Added alignment --}}
                                    @if ($token->last_used_at)
                                        <div class="text-sm text-gray-500 me-2"> {{-- Bootstrap/custom class, added text-sm and me-2 --}}
                                            {{ __('Last used') }} {{ $token->last_used_at->diffForHumans() }}
                                        </div>
                                    @endif

                                    {{-- Check if Jetstream is configured with permissions --}}
                                    @if (Laravel\Jetstream\Jetstream::hasPermissions())
                                        {{-- Bootstrap button link classes --}}
                                        <button class="btn btn-link text-secondary text-decoration-none me-2"
                                            {{-- Added text-decoration-none and me-2 --}}
                                            wire:click="manageApiTokenPermissions({{ $token->id }})">
                                            {{ __('Permissions') }}
                                        </button>
                                    @endif

                                    {{-- Bootstrap button link classes --}}
                                    <button class="btn btn-link text-danger text-decoration-none"
                                        wire:click="confirmApiTokenDeletion({{ $token->id }})">
                                        {{ __('Delete') }}
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </x-slot>
            </x-action-section>
        </div>
    @endif

    {{-- Token Value Modal --}}
    {{-- Jetstream dialog modal component --}}
    {{-- wire:model.live requires Livewire v3+ --}}
    <x-dialog-modal wire:model.live="displayingToken">
        <x-slot name="title">
            {{ __('API Token') }}
        </x-slot>

        <x-slot name="content">
            <div class="mb-3"> {{-- Added mb-3 for spacing --}}
                {{ __('Please copy your new API token. For your security, it won\'t be shown again.') }}
            </div>

            {{-- Jetstream input component --}}
            {{-- Use direct property access without $this-> --}}
            <x-input x-ref="plaintextToken" type="text" readonly :value="$plainTextToken" autofocus autocomplete="off"
                autocorrect="off" autocapitalize="off" spellcheck="false" class="form-control" {{-- Added form-control --}}
                @showing-token-modal.window="setTimeout(() => $refs.plaintextToken.select(), 250)" />
        </x-slot>

        <x-slot name="footer">
            {{-- Jetstream secondary button component --}}
            {{-- Consider adding Bootstrap button classes like 'btn btn-secondary' if not handled by x-secondary-button --}}
            <x-secondary-button wire:click="$set('displayingToken', false)" wire:loading.attr="disabled">
                {{ __('Close') }}
            </x-secondary-button>
        </x-slot>
    </x-dialog-modal>

    {{-- API Token Permissions Modal --}}
    {{-- Jetstream dialog modal component --}}
    <x-dialog-modal wire:model.live="managingApiTokenPermissions">
        <x-slot name="title">
            {{ __('API Token Permissions') }}
        </x-slot>

        <x-slot name="content">
            <div class="mt-2 row"> {{-- Bootstrap grid classes --}}
                {{-- Check Jetstream permissions --}}
                @foreach (Laravel\Jetstream\Jetstream::$permissions as $permission)
                    <div class="col-md-6"> {{-- Using md breakpoint for grid columns --}}
                        <div class="mb-3 form-check"> {{-- Bootstrap/custom class --}}
                            {{-- Jetstream checkbox component --}}
                            <x-checkbox wire:model.defer="updateApiTokenForm.permissions"
                                id="{{ 'update-' . $permission }}" {{-- Changed wire:model to wire:model.defer --}} :value="$permission" />
                            {{-- Bootstrap/custom class --}}
                            <label class="form-check-label" for="{{ 'update-' . $permission }}">
                                {{ $permission }} {{-- Permission names might need translation depending on setup --}}
                            </label>
                        </div>
                    </div>
                @endforeach
            </div>
        </x-slot>

        <x-slot name="footer">
            {{-- Jetstream secondary button component --}}
            {{-- Consider adding Bootstrap button classes like 'btn btn-secondary' if not handled by x-secondary-button --}}
            <x-secondary-button wire:click="$set('managingApiTokenPermissions', false)" wire:loading.attr="disabled">
                {{ __('Cancel') }}
            </x-secondary-button>

            {{-- Jetstream button component --}}
            {{-- Consider adding Bootstrap button classes like 'btn btn-primary' if not handled by x-button --}}
            <x-button wire:click="updateApiToken" wire:loading.attr="disabled">
                {{ __('Save') }}
            </x-button>
        </x-slot>
    </x-dialog-modal>

    {{-- Delete Token Confirmation Modal --}}
    {{-- Jetstream confirmation modal component --}}
    <x-confirmation-modal wire:model.live="confirmingApiTokenDeletion">
        <x-slot name="title">
            {{ __('Delete API Token') }}
        </x-slot>

        <x-slot name="content">
            {{ __('Are you sure you would like to delete this API token?') }}
        </x-slot>

        <x-slot name="footer">
            {{-- Jetstream secondary button component --}}
            {{-- Consider adding Bootstrap button classes like 'btn btn-secondary' if not handled by x-secondary-button --}}
            <x-secondary-button wire:click="$toggle('confirmingApiTokenDeletion')" wire:loading.attr="disabled">
                {{ __('Cancel') }}
            </x-secondary-button>

            {{-- Jetstream danger button component --}}
            {{-- Consider adding Bootstrap button classes like 'btn btn-danger' if not handled by x-danger-button --}}
            <x-danger-button wire:loading.attr="disabled" wire:click="deleteApiToken">
                {{ __('Delete') }}
            </x-danger-button>
        </x-slot>
    </x-confirmation-modal>
</div>
