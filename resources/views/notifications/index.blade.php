<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pusat Pemberitahuan</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Optional: Add custom styles if needed, but prefer Tailwind */
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
</head>

<body class="bg-gray-100 p-6">

    {{-- Extend a layout if you have one, otherwise include basic HTML structure --}}
    @extends('layouts.app')

    @section('content')
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 py-6"> {{-- Converted container to Tailwind --}}

            <h2 class="text-2xl font-bold mb-6 text-gray-800">Pusat Pemberitahuan</h2> {{-- Converted h2 --}}

            {{-- Display success or error messages from session --}}
            @if (session()->has('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            @if (session()->has('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
            @endif


            {{-- List of Notifications --}}
            @if ($notifications->isEmpty())
                {{-- Use isEmpty() for Laravel collections --}}
                <p class="text-gray-600">Tiada pemberitahuan ditemui.</p> {{-- Message if no notifications --}}
            @else
                <ul class="list-group"> {{-- Converted list-group to Tailwind styles --}}
                    {{-- Loop through the collection of notifications --}}
                    @foreach ($notifications as $notification)
                        {{-- Use wire:key if this were a Livewire component, but for standard Blade, not necessary --}}
                        {{-- Add a class to indicate if the notification is unread --}}
                        <li
                            class="list-group-item {{ $notification->read_at ? 'bg-gray-50 text-gray-700' : 'bg-white text-gray-900 font-semibold' }}">
                            {{-- Converted list-group-item and added read/unread styling --}}
                            {{-- Display the notification message --}}
                            <div class="flex-grow mr-4"> {{-- Allow message to take up space --}}
                                {{ $notification->data['message'] ?? $notification->type }}
                            </div>
                            {{-- Display the timestamp --}}
                            <span class="text-muted flex-shrink-0"> {{-- Prevent timestamp from shrinking --}}
                                {{ $notification->created_at->diffForHumans() }}
                            </span>
                        </li>
                    @endforeach
                </ul>

                {{-- Pagination links --}}
                @if ($notifications->hasPages())
                    {{-- Check if the collection is paginated --}}
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

</body>

</html>
