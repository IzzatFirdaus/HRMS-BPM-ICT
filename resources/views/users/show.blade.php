@extends('layouts.app')

@section('content')
    <div class="container">
        <h2>User Details: {{ $user->full_name }}</h2>
        <ul>
            <li>NRIC: {{ $user->identification_number }}</li>
            <li>Email: {{ $user->motac_email }}</li>
            <li>Grade: {{ $user->grade->name }}</li>
            <li>Status: {{ $user->service_status }}</li>
        </ul>
    @endsection
