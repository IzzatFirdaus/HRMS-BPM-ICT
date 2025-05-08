{{-- resources/views/livewire/settings/edit-user.blade.php --}}

{{-- Livewire component root element --}}
{{-- Ensure this view is rendered within your main application layout --}}
<div>
    {{-- Set the page title, assuming the component passes a $user variable --}}
    {{-- Also assuming the component has public properties like $name, $email etc. --}}
    @section('title', __('Edit User') . ': ' . ($user->full_name ?? ($user->name ?? 'N/A')))

    {{-- Main content container --}}
    <div class="container-xxl flex-grow-1 container-p-y">
        {{-- Page Title within content, assuming the component passes a $user variable --}}
        <h4 class="py-3 mb-4">{{ __('Edit User') }}: {{ $user->full_name ?? ($user->name ?? 'N/A') }}</h4>

        {{-- Success/Error Alerts (Optional - could be handled by layout or component) --}}
        @if (session()->has('message'))
            <div class="alert alert-success">
                {{ session('message') }}
            </div>
        @endif

        @if (session()->has('error'))
            <div class="alert alert-danger"> {{-- Assuming you have .alert-danger styles --}}
                {{ session('error') }}
            </div>
        @endif

        {{-- Card for form --}}
        <div class="card">
            <div class="card-body">
                {{-- User editing form --}}
                {{-- Bind form submission to the 'saveUser' method in the component --}}
                <form wire:submit.prevent="saveUser">
                    {{-- Include a CSRF token for security --}}
                    @csrf
                    {{-- Optional: Include a method directive if needed for PUT/PATCH --}}
                    {{-- @method('PUT') --}}

                    {{-- Form fields --}}
                    {{-- Assuming component has public $name, $email, etc. properties --}}

                    <div class="mb-3">
                        <label for="name" class="form-label">{{ __('Full Name') }}</label>
                        {{-- wire:model="name" binds this input to the $name public property in the component --}}
                        <input type="text" id="name" class="form-control" wire:model="name"
                            placeholder="{{ __('Enter full name') }}">
                        {{-- Display validation error for this field --}}
                        @error('name')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">{{ __('Email Address') }}</label>
                        {{-- wire:model="email" binds this input to the $email public property --}}
                        <input type="email" id="email" class="form-control" wire:model="email"
                            placeholder="{{ __('Enter email address') }}">
                        @error('email')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Submit button --}}
                    <button type="submit" class="btn btn-primary me-2">{{ __('Save Changes') }}</button>
                    {{-- Loading indicator for the button --}}
                    <span wire:loading wire:target="saveUser">{{ __('Saving...') }}</span>

                </form>
            </div>
        </div>

        {{-- Optional: Action buttons --}}
        <div class="mt-4">
            {{-- Cancel button, links back to the show user page, assuming component passes $user --}}
            <a href="{{ route('settings-users.show', $user) }}" class="btn btn-secondary">{{ __('Cancel') }}</a>
            {{-- Or link back to the list: <a href="{{ route('settings-users') }}" class="btn btn-secondary">{{ __('Back to Users List') }}</a> --}}
        </div>
    </div>
</div>
