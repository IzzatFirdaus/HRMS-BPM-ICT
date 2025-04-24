@extends('layouts.app')

@section('content')
    <div class="container">
        <h2>Pending Approvals</h2>

        @if (count($approvals))
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Type</th>
                        <th>Applicant</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($approvals as $approval)
                        <tr>
                            <td>{{ class_basename($approval->approvable_type) }}</td>
                            <td>{{ $approval->approvable->user->full_name }}</td>
                            <td><span class="badge bg-warning">{{ ucfirst($approval->status) }}</span></td>
                            <td><a href="{{ route('approvals.show', $approval->id) }}"
                                    class="btn btn-sm btn-outline-primary">Review</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p>No approvals pending at this time.</p>
        @endif
    </div>
@endsection
