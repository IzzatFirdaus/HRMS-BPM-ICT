@extends('layouts.app')

@section('content')
    <div class="container">
        <h2>Email Provisioning Reports</h2>
        <table class="table">
            <thead>
                <tr>
                    <th>Applicant</th>
                    <th>Status</th>
                    <th>Assigned Email</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($applications as $app)
                    <tr>
                        <td>{{ $app->user->full_name }}</td>
                        <td>{{ $app->status }}</td>
                        <td>{{ $app->final_assigned_email ?? '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
