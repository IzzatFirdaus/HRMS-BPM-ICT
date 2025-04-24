@extends('layouts.app')

@section('content')
    <div class="container">
        <h2>Notifications</h2>
        <ul class="list-group">
            @foreach ($notifications as $notification)
                <li class="list-group-item">
                    {{ $notification->data['message'] ?? $notification->type }}
                    <span class="text-muted float-end">{{ $notification->created_at->diffForHumans() }}</span>
                </li>
            @endforeach
        </ul>
    </div>
@endsection
