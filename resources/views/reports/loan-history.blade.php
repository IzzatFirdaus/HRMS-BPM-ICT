{{-- resources/views/reports/loan_history.blade.php --}}

@extends('layouts.app') {{-- Assumes you have a main layout file named 'layouts/app.blade.php' --}}

{{-- Set the page title --}}
@section('title', __('Loan History Report')) {{-- Translated Page Title --}}

{{-- Define the content section --}}
@section('content')

    <div class="container mx-auto px-4 py-6"> {{-- Basic container, adjust classes as needed --}}

        {{-- Page Title --}}
        <h2 class="text-2xl font-bold mb-6 text-gray-800">{{ __('Loan History Report') }}</h2> {{-- Translated Page Title --}}

        {{-- Check if there are any loan transactions to display --}}
        {{-- CORRECTED: Changed $transactions to $loanTransactions --}}
        @if ($loanTransactions->count())

            {{-- Display transactions in a table --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="overflow-x-auto"> {{-- Add responsive scrolling for the table --}}
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                {{-- Table Headers --}}
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('ID') }}</th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('Equipment') }}</th> {{-- Display Equipment Name --}}
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('User (Applicant)') }}</th> {{-- Display User Name --}}
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('Transaction Type') }}</th> {{-- e.g., Issue, Return --}}
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('Timestamp') }}</th> {{-- Issue or Return Timestamp --}}
                                {{-- Add other relevant columns --}}
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            {{-- Loop through the loan transactions --}}
                            {{-- CORRECTED: Changed $transactions to $loanTransactions --}}
                            @foreach ($loanTransactions as $transaction)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ $transaction->id }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{-- Access the equipment relationship --}}
                                        {{ $transaction->equipment->name ?? __('N/A') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{-- Access the user relationship (applicant) --}}
                                        {{ $transaction->user->name ?? __('N/A') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $transaction->transaction_type }} {{-- Assuming a 'transaction_type' column --}}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $transaction->issue_timestamp ?? ($transaction->return_timestamp ?? __('N/A')) }}
                                        {{-- Display relevant timestamp --}}
                                    </td>
                                    {{-- Add other table data cells --}}
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div> {{-- End overflow-x-auto --}}

                {{-- Pagination links --}}
                {{-- Check if the paginator has more than one page --}}
                {{-- CORRECTED: Changed $transactions to $loanTransactions --}}
                @if ($loanTransactions->hasPages())
                    <div class="mt-4">
                        {{-- Render the pagination links --}}
                        {{-- CORRECTED: Changed $transactions to $loanTransactions --}}
                        {{ $loanTransactions->links() }}
                    </div>
                @endif
            @else
                {{-- Message to display if no transactions are found --}}
                <div class="text-center py-4">
                    <p class="text-lg text-gray-600">{{ __('No loan history found.') }}</p>
                </div>
        @endif

        {{-- Optional: Back button to a reports dashboard or home --}}
        <div class="mt-6 text-center">
            {{-- Assuming a route named 'reports.index' or similar --}}
            {{-- <a href="{{ route('reports.index') }}" class="btn btn-secondary">
                      {{ __('Back to Reports') }}
                  </a> --}}
            {{-- Or a link back to the dashboard --}}
            <a href="{{ route('dashboard') }}" class="btn btn-secondary">
                {{ __('Back to Dashboard') }}
            </a>
        </div>


    </div> {{-- End bg-white card --}}
    </div> {{-- End container --}}

@endsection

{{-- Note: This code assumes 'layouts.app' provides the main HTML structure --}}
{{-- and includes necessary CSS frameworks like Tailwind CSS and potentially Bootstrap for .container, .btn, .alert, .badge, etc. --}}
{{-- If you are using a different layout or CSS setup, you may need to adjust the HTML structure and classes. --}}

{{-- If this view is NOT extended by a layout, and is intended as a standalone Livewire component view,
     remove the @extends, @section directives, and the HTML structure outside the component's root element (e.g., <div>).
     However, the error suggests this is a standard Blade view rendered by a Controller.
--}}
