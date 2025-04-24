@extends('layouts.app')

@section('content')
    <div class="container">
        <h2 class="mb-4">Email Applications</h2>
        <a href="{{ route('email-applications.create') }}" class="btn btn-primary mb-3">New Application</a>

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Purpose</th>
                    <th>Status</th>
                    <th>Proposed Email</th>
                    <th>Submitted</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($applications as $app)
                    <tr>
                        <td>{{ $app->purpose }}</td>
                        <td><span class="badge bg-info">{{ ucfirst($app->status) }}</span></td>
                        <td>{{ $app->proposed_email ?? '-' }}</td>
                        <td>{{ $app->created_at->format('d M Y') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
