{{-- resources/views/livewire/settings/create-user.blade.php --}}

{{-- Livewire component root element --}}
{{-- Ensure this view is rendered within your main application layout --}}
<div>
    {{-- Set the page title --}}
    @section('title', __('Create User'))

    {{-- Main content container --}}
    <div class="container-xxl flex-grow-1 container-p-y">
        {{-- Page Title within content --}}
        <h4 class="py-3 mb-4">{{ __('Create New User') }}</h4>

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
                {{-- User creation form --}}
                {{-- Bind form submission to the 'saveUser' method in the component --}}
                <form wire:submit.prevent="saveUser">
                    {{-- Include a CSRF token for security --}}
                    @csrf

                    {{-- Form fields --}}
                    {{-- Assuming component has public $name, $email, $password, etc. properties --}}

                    <div class="mb-3">
                        <label for="name" class="form-label">{{ __('Full Name') }}</label>
                        {{-- wire:model="name" binds this input to the $name public property in the component --}}
                        <input type="text" id="name" class="form-control" wire:model="name"
                            placeholder="{{ __('Enter full name') }}" required>
                        {{-- Display validation error for this field --}}
                        @error('name')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">{{ __('Email Address') }}</label>
                        {{-- wire:model="email" binds this input to the $email public property --}}
                        <input type="email" id="email" class="form-control" wire:model="email"
                            placeholder="{{ __('Enter email address') }}" required>
                        @error('email')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">{{ __('Password') }}</label>
                        {{-- wire:model="password" binds this input to the $password public property --}}
                        <input type="password" id="password" class="form-control" wire:model="password"
                            placeholder="{{ __('Enter password') }}" required>
                        @error('password')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Submit button --}}
                    <button type="submit" class="btn btn-primary me-2">{{ __('Create User') }}</button>
                    {{-- Loading indicator for the button --}}
                    <span wire:loading wire:target="saveUser">{{ __('Creating...') }}</span>

                </form>
            </div>
        </div>

        {{-- Optional: Back button --}}
        <div class="mt-4">
            {{-- Link back to the user list page --}}
            <a href="{{ route('settings-users') }}" class="btn btn-secondary">{{ __('Back to Users List') }}</a>
        </div>
    </div>
</div>
