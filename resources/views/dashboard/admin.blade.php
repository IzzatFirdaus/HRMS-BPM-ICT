@extends('layouts.app')

@section('content')
    <div class="container">
        <h2>Admin Dashboard</h2>
        <div class="row">
            <div class="col-md-4">Users: {{ $users_count ?? '...' }}</div>
            <div class="col-md-4">Pending Approvals: {{ $pending_count ?? '...' }}</div>
            <div class="col-md-4">Assets Available: {{ $assets_count ?? '...' }}</div>
        </div>
    </div>
@endsection
