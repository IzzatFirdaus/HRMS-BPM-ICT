{{-- resources/views/notifications/index.blade.php --}}

{{-- Extend the main application layout --}}
@extends('layouts.app')

{{-- Define the content section --}}
@section('content')

    {{-- Note: The outer HTML boilerplate (<!DOCTYPE html>, <html>, <head>, <body>)
        has been removed as it should be in your layouts/app.blade.php file. --}}

    {{-- You might still need to include your CSS if it's not handled by the layout or asset compilation --}}
    {{-- If you're using Tailwind CDN only for this page, you might need it here,
        though it's better included in the main layout head. --}}
    {{-- <script src="https://cdn.tailwindcss.com"></script> --}}

    {{-- Your custom styles - consider moving these to a CSS file and compiling --}}
    <style>
        .alert {
            padding: 1rem;
            border-radius: 0.25rem;
            margin-bottom: 1rem;
            border-width: 1px;
        }

        .alert-success {
            background-color: #d1fae5;
            /* green-100 */
            border-color: #a7f3d0;
            /* green-200 */
            color: #065f46;
            /* green-800 */
        }

        .list-group {
            border: 1px solid #d1d5db;
            /* gray-300 */
            border-radius: 0.5rem;
            overflow: hidden;
            /* Ensures rounded corners apply to list items */
        }

        .list-group-item {
            padding: 1rem;
            border-bottom: 1px solid #e5e7eb;
            /* gray-200 */
            background-color: #fff;
            /* white */
            display: flex;
            /* Use flex to align content and timestamp */
            justify-content: space-between;
            /* Space out content and timestamp */
            align-items: center;
            /* Vertically center items */
        }

        .list-group-item:last-child {
            border-bottom: none;
            /* Remove border from the last item */
        }

        .text-muted {
            color: #6b7280;
            /* gray-500 */
            font-size: 0.875rem;
            /* text-sm */
        }
    </style>

    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 py-6">

        <h2 class="text-2xl font-bold mb-6 text-gray-800">Pusat Pemberitahuan</h2>

        {{-- Display success or error messages from session --}}
        @if (session()->has('success'))
            <div class="alert alert-success">
                {{ session('success') }}
                </div>
        @endif

        @if (session()->has('error'))
            <div class="alert alert-danger"> {{-- Assuming you have alert-danger styles --}}
                {{ session('error') }}
                </div>
        @endif


        {{-- List of Notifications --}}
        @if ($notifications->isEmpty())
            <p class="text-gray-600">Tiada pemberitahuan ditemui.</p>
        @else
            <ul class="list-group">
                {{-- Loop through the collection of notifications --}}
                @foreach ($notifications as $notification)
                    <li
                        class="list-group-item {{ $notification->read_at ? 'bg-gray-50 text-gray-700' : 'bg-white text-gray-900 font-semibold' }}">

                        {{-- Make the notification content clickable, linking to the URL in the data --}}
                        <a href="{{ $notification->data['url'] ?? '#' }}"
                            class="flex-grow mr-4 no-underline text-current"> {{-- Use text-current to inherit color --}}
                            <div>
                                {{-- Display the notification message from the 'data' payload --}}
                                {{ $notification->data['message'] ?? 'New notification' }}
                                {{-- Fallback text if message is missing --}}
                                </div>
                            </a>

                        {{-- Display the timestamp outside the clickable area --}}
                        <span class="text-muted flex-shrink-0">
                            {{ $notification->created_at->diffForHumans() }}
                            </span>
                        </li>
                @endforeach
                </ul>

            {{-- Pagination links --}}
            @if ($notifications->hasPages())
                <div class="mt-4">
                    {{ $notifications->links() }}
                    </div>
            @endif
        @endif

        {{-- Optional: Button to mark all as read --}}
        {{-- This would typically be a form submission or Livewire action --}}
        {{-- @if ($notifications->whereNull('read_at')->isNotEmpty())
        <div class="mt-6 text-center">
            <form action="{{ route('notifications.markAllAsRead') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-secondary">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Tandakan Semua Sebagai Telah Dibaca
                </button>
            </form>
        </div>
    @endif --}}


    </div> {{-- End max-w-4xl container --}}

@endsection
