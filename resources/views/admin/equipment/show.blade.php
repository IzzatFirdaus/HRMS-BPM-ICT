@extends('layouts.app') {{-- Extend your main layout file --}}

@section('title', 'Equipment Details') {{-- Set the page title --}}

@section('content') {{-- Start the content section --}}

    <div class="container"> {{-- Basic container for layout --}}
        <h1>Equipment Details</h1>

        {{-- Display success or error messages --}}
        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif

        {{-- Basic details of the equipment --}}
        <div>
            <p><strong>Asset Tag ID:</strong> {{ $equipment->tag_id ?? 'N/A' }}</p>
            <p><strong>Asset Type:</strong> {{ $equipment->asset_type ?? 'N/A' }}</p>
            <p><strong>Brand:</strong> {{ $equipment->brand ?? 'N/A' }}</p>
            <p><strong>Model:</strong> {{ $equipment->model ?? 'N/A' }}</p>
            <p><strong>Serial Number:</strong> {{ $equipment->serial_number ?? 'N/A' }}</p>
            <p><strong>Description:</strong> {{ $equipment->description ?? 'N/A' }}</p>
            <p><strong>Availability Status:</strong>
                {{ $equipment->availability_status_translated ?? ($equipment->availability_status ?? 'N/A') }}</p>
            <p><strong>Condition Status:</strong>
                {{ $equipment->condition_status_translated ?? ($equipment->condition_status ?? 'N/A') }}</p>
            <p><strong>Current Location Details:</strong> {{ $equipment->current_location ?? 'N/A' }}</p>
            <p><strong>Department:</strong> {{ $equipment->department->name ?? 'N/A' }}</p>
            <p><strong>Center:</strong> {{ $equipment->center->name ?? 'N/A' }}</p>
            <p><strong>Purchase Date:</strong>
                {{ $equipment->purchase_date ? $equipment->purchase_date->format('Y-m-d') : 'N/A' }}</p>
            <p><strong>Warranty Expiry Date:</strong>
                {{ $equipment->warranty_expiry_date ? $equipment->warranty_expiry_date->format('Y-m-d') : 'N/A' }}</p>
            <p><strong>Notes:</strong> {{ $equipment->notes ?? 'N/A' }}</p>

            {{-- Display other fields if needed: is_active, in_service, is_gpr, value, real_price, expected_price, old_id, acquisition_date, acquisition_type, funded_by --}}
            {{-- <p><strong>Is Active:</strong> {{ $equipment->is_active ? 'Yes' : 'No' }}</p> --}}

        </div>

        <hr> {{-- Separator --}}

        {{-- Active Loan Information --}}
        <h3>Active Loan</h3>
        @if ($equipment->activeLoanTransaction) {{-- Check if there's an active loan --}}
            <p><strong>Currently Loaned To (Applicant):</strong>
                @if ($equipment->activeLoanTransaction->loanApplication && $equipment->activeLoanTransaction->loanApplication->user)
                    {{ $equipment->activeLoanTransaction->loanApplication->user->full_name }}
                    ({{ $equipment->activeLoanTransaction->loanApplication->user->employee_id ?? 'N/A' }})
                    {{-- Assuming employee_id exists on User --}}
                    @if ($equipment->activeLoanTransaction->loanApplication->user->position)
                        <br> Position:
                        {{ $equipment->activeLoanTransaction->loanApplication->user->position->name ?? 'N/A' }}
                    @endif
                @else
                    N/A {{-- Should not happen if relationships are correct and eager loaded --}}
                @endif
            </p>
            <p><strong>Issued By:</strong> {{ $equipment->activeLoanTransaction->issuingOfficer->full_name ?? 'N/A' }}</p>
            {{-- Assuming full_name on User model --}}
            <p><strong>Issue Timestamp:</strong> {{ $equipment->activeLoanTransaction->issue_timestamp ?? 'N/A' }}</p>
            {{-- Display other active loan transaction details as needed (e.g., accessories, loan start/end dates from loanApplication) --}}
        @else
            {{-- Message if no active loan --}}
            <p>This equipment is not currently on loan.</p>
        @endif

        <hr> {{-- Separator --}}

        {{-- Loan History --}}
        <h3>Loan History</h3>
        @if ($equipment->loanTransactions->count() > 0) {{-- Check if there is loan history --}}
            <table class="table table-bordered"> {{-- Table for loan transactions --}}
                <thead>
                    <tr>
                        <th>Issue Timestamp</th>
                        <th>Return Timestamp</th>
                        <th>Applicant</th>
                        <th>Issuing Officer</th>
                        <th>Return Accepting Officer</th>
                        <th>Status</th>
                        {{-- Add other columns like Accessories, Notes if you want to display them --}}
                    </tr>
                </thead>
                <tbody>
                    @foreach ($equipment->loanTransactions as $transaction)
                        {{-- Loop through loan transactions --}}
                        <tr>
                            <td>{{ $transaction->issue_timestamp ?? 'N/A' }}</td>
                            <td>{{ $transaction->return_timestamp ?? 'N/A' }}</td>
                            {{-- Display applicant via loanApplication relationship --}}
                            <td>{{ $transaction->loanApplication->user->full_name ?? 'N/A' }}</td>
                            {{-- Display officers via their specific relationships --}}
                            <td>{{ $transaction->issuingOfficer->full_name ?? 'N/A' }}</td>
                            <td>{{ $transaction->returnAcceptingOfficer->full_name ?? 'N/A' }}</td>
                            {{-- Use translated status accessor from LoanTransaction model --}}
                            <td>{{ $transaction->status_translated ?? ($transaction->status ?? 'N/A') }}</td>
                            {{-- Add other columns --}}
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            {{-- Message if no loan history --}}
            <p>No loan history found for this equipment.</p>
        @endif

        <hr> {{-- Separator --}}

        {{-- Actions: Edit and Delete --}}
        <p>
            <a href="{{ route('admin.equipment.edit', $equipment) }}" class="btn btn-warning">Edit Equipment</a>
        <form action="{{ route('admin.equipment.destroy', $equipment) }}" method="POST" style="display:inline-block;">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger"
                onclick="return confirm('Are you sure you want to delete this equipment? This action cannot be undone.')">Delete
                Equipment</button>
        </form>
        </p>

        {{-- Link back to the index page --}}
        <p><a href="{{ route('admin.equipment.index') }}" class="btn btn-secondary">Back to List</a></p>

    </div>

@endsection {{-- End the content section --}}
