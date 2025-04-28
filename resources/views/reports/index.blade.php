{{-- resources/views/reports/index.blade.php --}}

{{-- Extend your main layout if you have one --}}
{{-- @extends('layouts.app') --}}

{{-- @section('content') --}}

<div class="container mx-auto px-4 py-6"> {{-- Basic container --}}
    <h2 class="text-2xl font-bold mb-6 text-gray-800">Available Reports</h2> {{-- Page Title --}}

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6"> {{-- Grid for report cards/links --}}

        {{-- Equipment Report Link --}}
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
            <h3 class="text-xl font-semibold mb-3 text-gray-800">Equipment Report</h3>
            <p class="text-gray-600 mb-4">View reports on equipment loan applications.</p>
            {{-- Assuming a route named 'reports.equipment' --}}
            <a href="{{ route('reports.equipment') }}" class="btn btn-primary">
                View Report
            </a>
        </div>

        {{-- Email Accounts Report Link --}}
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
            <h3 class="text-xl font-semibold mb-3 text-gray-800">Email Accounts Report</h3>
            <p class="text-gray-600 mb-4">View reports on email account applications.</p>
            {{-- Assuming a route named 'reports.emailAccounts' --}}
            <a href="{{ route('reports.emailAccounts') }}" class="btn btn-primary">
                View Report
            </a>
        </div>

        {{-- User Activity Report Link --}}
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
            <h3 class="text-xl font-semibold mb-3 text-gray-800">User Activity Report</h3>
            <p class="text-gray-600 mb-4">View reports on user application activity.</p>
            {{-- Assuming a route named 'reports.userActivity' --}}
            <a href="{{ route('reports.userActivity') }}" class="btn btn-primary">
                View Report
            </a>
        </div>

        {{-- Add more report links here as you create them --}}

    </div> {{-- End grid --}}
</div> {{-- End container --}}

{{-- @endsection --}}

{{-- You might need to include Tailwind CSS or define your own styles for the classes used above (container, text-*, etc.). --}}
{{-- If you are using a layout like 'layouts.app', these styles might already be included. --}}
