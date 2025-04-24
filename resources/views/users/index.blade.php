@extends('layouts.app')

@section('content')
    <div class="container">
        <h2>All Users</h2>
        <table class="table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Department</th>
                    <th>Grade</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($users as $user)
                    <tr>
                        <td>{{ $user->full_name }}</td>
                        <td>{{ $user->department->name ?? '-' }}</td>
                        <td>{{ $user->grade->name ?? '-' }}</td>
                        <td><a href="{{ route('users.show', $user->id) }}" class="btn btn-sm btn-info">View</a></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
