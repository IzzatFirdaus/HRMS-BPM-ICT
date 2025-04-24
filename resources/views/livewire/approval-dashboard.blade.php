<div>
    {{-- resources/views/livewire/approval-dashboard.blade.php --}}
    <h2>Approval Dashboard</h2>

    @if (session()->has('message'))
        <div class="alert alert-success">
            {{ session('message') }}
        </div>
    @endif

    {{-- Filter/Search options could go here --}}

    <h3>Pending Approvals</h3>

    @if ($pendingApprovals->isEmpty())
        <p>No pending approvals at this time.</p>
    @else
        <table class="table">
            <thead>
                <tr>
                    <th>Application Type</th>
                    <th>Applicant</th>
                    <th>Submitted At</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                {{-- Loop through pending applications that the current user is authorized to approve --}}
                @foreach ($pendingApprovals as $application)
                    <tr>
                        <td>
                            {{-- Display type based on the model --}}
                            @if ($application instanceof \App\Models\EmailApplication)
                                Email Application
                            @elseif ($application instanceof \App\Models\LoanApplication)
                                Loan Application
                            @else
                                Unknown Type
                            @endif
                        </td>
                        <td>{{ $application->user->full_name ?? 'N/A' }}</td> {{-- Assuming a user relationship --}}
                        <td>{{ $application->created_at->format('Y-m-d H:i') }}</td>
                        <td>{{ ucfirst(str_replace('_', ' ', $application->status)) }}</td>
                        <td>
                            {{-- Link to view details and perform approval action --}}
                            <a href="{{ $application instanceof \App\Models\EmailApplication ? route('email-applications.show', $application) : route('loan-applications.show', $application) }}"
                                class="btn btn-sm btn-info">View Details</a>

                            {{-- Approval/Rejection buttons (conditional based on status and policy) --}}
                            @can('approve', $application)
                                <button wire:click="approve({{ $application->id }}, '{{ get_class($application) }}')"
                                    class="btn btn-sm btn-success">Approve</button>
                            @endcan

                            @can('reject', $application)
                                <button wire:click="reject({{ $application->id }}, '{{ get_class($application) }}')"
                                    class="btn btn-sm btn-danger">Reject</button>
                            @endcan
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{-- Pagination if needed --}}
        {{ $pendingApprovals->links() }}

    @endif

    {{-- You might add sections for "Approved History", "Rejected History" etc. --}}

</div>
