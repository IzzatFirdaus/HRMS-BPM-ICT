{{-- resources/views/livewire/settings/users.blade.php --}}

{{-- Livewire component root element --}}
{{-- A Livewire component must have a single root HTML element --}}
<div>

    {{-- Set the page title by yielding to the layout's 'title' section --}}
    {{-- This helps set the browser tab title dynamically --}}
    @section('title', __('User Management'))

    {{-- Optional: Push page-specific CSS files or inline styles to a stack defined in the layout --}}
    {{-- Example: Assuming your layout has a @stack('styles') --}}
    {{-- @push('styles') --}}
    {{-- <link rel="stylesheet" href="{{ asset('assets/vendor/css/pages/users.css') }}" /> --}}
    {{-- Add other specific styles needed only for this page --}}
    {{-- @endpush --}}

    {{-- Optional: Include the @php block if you need config data from a Helper --}}
    {{-- Ensure the 'Helper' facade or class is correctly set up and accessible --}}
    {{-- @php
      // Assuming Helper is a facade or globally available
      // use App\Helpers\Helper; // Uncomment if Helper is a facade and you need to import it here
      $configData = Helper::appClasses();
  @endphp --}}

    {{-- This is the main content area that will be inserted into the layout's @yield('content') --}}
    {{-- The container div structure is common and correct for this purpose --}}
    <div class="container-xxl flex-grow-1 container-p-y">

        {{-- Page Title --}}
        {{-- Display the title for the content area. It can be static or passed from the component. --}}
        {{-- If using @section('title') above, the layout handles the main page title in the browser tab. --}}
        {{-- This h4 is the visible title within the page content. --}}
        <h4 class="py-3 mb-4">
            {{-- Example: If passing a variable $pageTitle from the Livewire component --}}
            {{-- {{ $pageTitle ?? __('User Management') }} --}}
            {{ __('User Management') }} {{-- Static title for the content area --}}
        </h4>

        {{-- Success/Error Alerts (Optional - could be handled by layout) --}}
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

        {{-- Button to add a new user --}}
        {{-- Assumes a route named 'settings-users.create' exists for creating users --}}
        {{-- If user creation is handled via modal within this component, you won't need this link --}}
        {{-- @can('create', App\Models\User::class) --}} {{-- Optional: Gate/Policy check --}}
        <a href="{{ route('settings-users.create') }}" class="btn btn-primary mb-4">
            {{-- Assuming you have a button style defined --}}
            <i class="ti ti-plus me-1"></i> {{-- Example icon class --}}
            {{ __('Add New User') }}
        </a>
        {{-- @endcan --}}

        {{-- User Listing Table --}}
        {{-- This assumes your Livewire component passes a paginated collection of users named $users --}}
        {{-- If the variable name is different in your component, update $users here --}}
        @if ($users->count())
            <div class="card"> {{-- Card container for the table --}}
                <h5 class="card-header">{{ __('Users List') }}</h5> {{-- Table Header --}}
                <div class="table-responsive text-nowrap"> {{-- Responsive table container --}}
                    <table class="table"> {{-- Basic table structure --}}
                        <thead>
                            <tr>
                                <th>{{ __('ID') }}</th>
                                <th>{{ __('Name') }}</th>
                                <th>{{ __('Email') }}</th>
                                <th>{{ __('Roles') }}</th> {{-- Assuming users have roles --}}
                                <th>{{ __('Status') }}</th> {{-- Assuming users have a status attribute --}}
                                <th>{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="table-border-bottom-0">
                            @foreach ($users as $user)
                                <tr>
                                    <td>{{ $user->id }}</td>
                                    <td>{{ $user->full_name ?? $user->name }}</td> {{-- Display full_name or name --}}
                                    <td>{{ $user->email }}</td>
                                    <td>
                                        {{-- Display user roles (assuming Spatie roles relationship) --}}
                                        @foreach ($user->roles as $role)
                                            <span class="badge bg-label-info me-1">{{ $role->name }}</span>
                                            {{-- Example badge --}}
                                        @endforeach
                                    </td>
                                    <td>
                                        {{-- Display user status with a badge --}}
                                        <span
                                            class="badge me-1 {{ $user->status === 'active' ? 'bg-label-success' : 'bg-label-danger' }}">
                                            {{ ucfirst($user->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        {{-- Action links/buttons --}}
                                        <div class="dropdown">
                                            <button type="button" class="btn p-0 dropdown-toggle hide-arrow"
                                                data-bs-toggle="dropdown"><i class="ti ti-dots-vertical"></i></button>
                                            <div class="dropdown-menu">
                                                {{-- Link to view/edit user details --}}
                                                {{-- Assumes a route named 'settings-users.show' or 'settings-users.edit' exists --}}
                                                {{-- @can('view', $user) --}}
                                                <a class="dropdown-item"
                                                    href="{{ route('settings-users.show', $user->id) }}">
                                                    <i class="ti ti-eye me-1"></i> {{ __('View') }}
                                                </a>
                                                {{-- @endcan --}}
                                                {{-- @can('update', $user) --}}
                                                <a class="dropdown-item"
                                                    href="{{ route('settings-users.edit', $user->id) }}">
                                                    <i class="ti ti-pencil me-1"></i> {{ __('Edit') }}
                                                </a>
                                                {{-- @endcan --}}
                                                {{-- Delete button (requires confirmation) --}}
                                                {{-- @can('delete', $user) --}}
                                                <button type="button" class="dropdown-item"
                                                    wire:click="deleteUser({{ $user->id }})"
                                                    wire:confirm="{{ __('Are you sure you want to delete this user?') }}">
                                                    <i class="ti ti-trash me-1"></i> {{ __('Delete') }}
                                                </button>
                                                {{-- @endcan --}}
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Pagination links --}}
            @if ($users->hasPages())
                <div class="mt-4">
                    {{ $users->links() }}
                </div>
            @endif
        @else
            {{-- Message if no users are found --}}
            <div class="card">
                <div class="card-body">
                    <p class="text-center text-gray-600">{{ __('No users found.') }}</p>
                </div>
            </div>
        @endif


    </div> {{-- End container --}}


    {{-- Push custom scripts to a stack defined in the layout (e.g., 'scripts' or 'custom-scripts') --}}
    {{-- These scripts will be included at the end of the body section in your layout --}}
    @push('custom-scripts')
        {{-- Add page-specific JavaScript here --}}
        {{-- Example:
      <script>
          // Custom script for the users page
          console.log('Users page loaded!');
      </script>
      --}}
    @endpush

</div>
