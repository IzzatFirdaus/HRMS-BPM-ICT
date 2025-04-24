@extends('layouts.app')

@section('content')
    <div class="container">
        <h2>User Dashboard</h2>
        <ul class="list-group">
            <li class="list-group-item"><a href="{{ route('email-applications.index') }}">Email Applications</a></li>
            <li class="list-group-item"><a href="{{ route('loan-applications.index') }}">Loan Applications</a></li>
            <li class="list-group-item"><a href="{{ route('notifications.index') }}">Notifications</a></li>
        </ul>
    </div>
@endsection
