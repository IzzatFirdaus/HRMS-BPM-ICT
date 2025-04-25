{{-- This view serves as the Administrator Dashboard for the MOTAC system. --}}
{{-- It extends the main application layout and displays key statistics and components. --}}

{{-- Ensure you are extending the correct layout file for your project. --}}
{{-- Based on the provided snippet, it's layouts.app, but verify this in your HRMS repo. --}}
@extends('layouts.app')

@section('title', 'Admin Dashboard') {{-- Set the page title --}}

@section('content')
    <div class="container-fluid"> {{-- Use container-fluid for wider content if needed --}}
        <h2>Administrator Dashboard</h2>

        {{-- Section to display key statistics --}}
        <div class="row">
            <div class="col-md-4 mb-4"> {{-- Added margin-bottom --}}
                <div class="card"> {{-- Use card for better presentation --}}
                    <div class="card-body">
                        <h5 class="card-title">Total Users</h5>
                        {{-- Display the actual count passed from the controller --}}
                        <p class="card-text display-4">{{ $users_count ?? '...' }}</p>
                        {{-- Optional: Add a link to the user management page --}}
                        {{-- <a href="{{ route('admin.users.index') }}" class="card-link">View All Users</a> --}}
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Pending Approvals</h5>
                        {{-- Display the count of all pending approvals (email and loan) --}}
                        <p class="card-text display-4">{{ $pending_approvals_count ?? '...' }}</p>
                        {{-- Optional: Add a link to the approvals dashboard --}}
                        {{-- <a href="{{ route('approvals') }}" class="card-link">View Pending Approvals</a> --}}
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Equipment Available</h5>
                        {{-- Display the count of available equipment assets --}}
                        <p class="card-text display-4">{{ $equipment_available_count ?? '...' }}</p>
                        {{-- Optional: Add a link to the equipment inventory --}}
                        {{-- <a href="{{ route('admin.equipment.index') }}" class="card-link">View Equipment Inventory</a> --}}
                    </div>
                </div>
            </div>
        </div>

        {{-- Optional: Add more rows for other statistics or charts --}}
        {{-- <div class="row">
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title">Email Application Status Summary</h5>
                        {{-- Include a chart or summary table here --}}
        {{-- </div>
                </div>
            </div>
             <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title">Equipment Loan Trends</h5>
                        {{-- Include a chart or summary table here --}}
        {{-- </div>
                </div>
            </div>
        </div> --}}

        {{-- You can include the ApprovalDashboard Livewire component here if desired, --}}
        {{-- although the system design also lists a separate route for it. --}}
        {{-- If you want to show pending approvals directly on the admin dashboard: --}}
        {{-- <div class="row">
            <div class="col-12">
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title">Recent Pending Approvals</h5>
                        @livewire('approval-dashboard') // Include the Livewire component
                    </div>
                </div>
            </div>
        </div> --}}

        {{-- Add other sections or components as needed for the admin overview --}}

    </div>
@endsection

{{-- Optional: Add any specific scripts for this view --}}
@push('scripts')
    <script>
        // Custom scripts for the admin dashboard
    </script>
@endpush
