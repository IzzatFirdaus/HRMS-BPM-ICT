{{-- resources/views/livewire/settings/show-user.blade.php --}}

{{-- Livewire component root element --}}
{{-- Ensure this view is rendered within your main application layout --}}
<div>
    {{-- Set the page title, assuming the component passes a $user variable --}}
    @section('title', __('User Details') . ': ' . ($user->full_name ?? ($user->name ?? 'N/A')))

    {{-- Main content container --}}
    <div class="container-xxl flex-grow-1 container-p-y">
        {{-- Page Title within content, assuming the component passes a $user variable --}}
        <h4 class="py-3 mb-4">{{ __('User Details') }}: {{ $user->full_name ?? ($user->name ?? 'N/A') }}</h4>

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

        {{-- Card for details display --}}
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">{{ __('User Information') }}</h5>
            </div>
            <div class="card-body">
                {{-- Display user details here, assuming the component passes a $user variable --}}
                {{-- This is where the actual user data will be displayed --}}

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <p><strong>{{ __('ID') }}:</strong> {{ $user->id ?? 'N/A' }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <p><strong>{{ __('Full Name') }}:</strong> {{ $user->full_name ?? ($user->name ?? 'N/A') }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <p><strong>{{ __('Email Address') }}:</strong> {{ $user->email ?? 'N/A' }}</p>
                    </div>
                    {{-- Display MOTAC Email if available --}}
                    <div class="col-md-6 mb-3">
                        <p><strong>{{ __('MOTAC Email') }}:</strong> {{ $user->motac_email ?? 'N/A' }}</p>
                    </div>
                    {{-- Display User ID Assigned if available --}}
                    <div class="col-md-6 mb-3">
                        <p><strong>{{ __('User ID Assigned') }}:</strong> {{ $user->user_id_assigned ?? 'N/A' }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <p><strong>{{ __('Mobile Number') }}:</strong> {{ $user->mobile_number ?? 'N/A' }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <p><strong>{{ __('NRIC') }}:</strong> {{ $user->identification_number ?? 'N/A' }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <p><strong>{{ __('Service Status') }}:</strong>
                            {{ $user->service_status_translated ?? ($user->service_status ? ucfirst(str_replace('_', ' ', $user->service_status)) : 'N/A') }}
                        </p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <p><strong>{{ __('Appointment Type') }}:</strong> {{ $user->appointment_type ?? 'N/A' }}</p>
                    </div>


                    {{-- Display Relationships (assuming they are loaded or exist) --}}
                    {{-- Ensure your User model has belongsTo relationships for department, position, grade --}}
                    <div class="col-md-6 mb-3">
                        <p><strong>{{ __('Department') }}:</strong> {{ $user->department->name ?? 'N/A' }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <p><strong>{{ __('Position') }}:</strong> {{ $user->position->name ?? 'N/A' }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <p><strong>{{ __('Grade') }}:</strong> {{ $user->grade->name ?? 'N/A' }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <p><strong>{{ __('Grade Level') }}:</strong> {{ $user->grade->level ?? 'N/A' }}</p>
                    </div>


                    {{-- Display User Roles (assuming Spatie roles relationship) --}}
                    <div class="col-12 mb-3">
                        <p><strong>{{ __('Roles') }}:</strong>
                            @if ($user->roles->count())
                                @foreach ($user->roles as $role)
                                    <span class="badge bg-label-info me-1">{{ $role->name }}</span>
                                @endforeach
                            @else
                                {{ __('No roles assigned') }}
                            @endif
                        </p>
                    </div>

                    {{-- Display Status with a badge --}}
                    <div class="col-md-6 mb-3">
                        <p><strong>{{ __('Status') }}:</strong>
                            <span
                                class="badge me-1 {{ $user->status === 'active' ? 'bg-label-success' : 'bg-label-danger' }}">
                                {{ ucfirst($user->status ?? 'N/A') }}
                            </span>
                        </p>
                    </div>

                    {{-- Display Timestamps --}}
                    <div class="col-md-6 mb-3">
                        <p><strong>{{ __('Created At') }}:</strong>
                            {{ $user->created_at ? $user->created_at->format('d M Y H:i') : 'N/A' }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <p><strong>{{ __('Updated At') }}:</strong>
                            {{ $user->updated_at ? $user->updated_at->format('d M Y H:i') : 'N/A' }}</p>
                    </div>

                </div> {{-- End row --}}


            </div> {{-- End card-body --}}
        </div> {{-- End card --}}

        {{-- Optional: Action buttons --}}
        <div class="mt-4">
            {{-- Back button to the user list page --}}
            <a href="{{ route('settings-users') }}" class="btn btn-secondary">{{ __('Back to Users List') }}</a>
            {{-- Optional: Edit button, assuming 'settings-users.edit' route exists and authorization --}}
            {{-- You might want to add a policy check like @can('update', $user) --}}
            <a href="{{ route('settings-users.edit', $user) }}" class="btn btn-primary ms-2">{{ __('Edit User') }}</a>
            {{-- Optional: Delete button (requires confirmation) --}}
            {{-- You might handle delete with a Livewire action from the list page or here with confirmation --}}
            {{-- @can('delete', $user) --}}
            {{-- <button type="button" class="btn btn-danger ms-2" wire:click="deleteUser({{ $user->id }})" wire:confirm="{{ __('Are you sure you want to delete this user?') }}">{{ __('Delete User') }}</button> --}}
            {{-- @endcan --}}
        </div>
    </div>
</div>
