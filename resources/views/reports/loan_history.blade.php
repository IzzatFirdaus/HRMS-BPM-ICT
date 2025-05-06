{{-- resources/views/reports/loan_history.blade.php --}}

@extends('layouts.app') {{-- Assumes you have a main layout file named 'layouts/app.blade.php' --}}

{{-- Set the page title --}}
@section('title', __('Loan History Report')) {{-- Translated Page Title --}}

{{-- Define the content section --}}
@section('content')

    <div class="container mx-auto px-4 py-6"> {{-- Basic container, adjust classes as needed --}}

        {{-- Page Title --}}
        <h2 class="text-2xl font-bold mb-6 text-gray-800">{{ __('Loan History Report') }}</h2> {{-- Translated Page Title --}}

        @if ($transactions->count())
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
                                    {{ __('Borrower') }}</th> {{-- Display Borrower's Full Name --}}
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('Issue Timestamp') }}</th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('Return Timestamp') }}</th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('Status') }}</th>
                                {{-- Add headers for other details you want to display, e.g., Issuing Officer, Return Accepting Officer, Condition on Return, etc. --}}
                                {{-- <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Issuing Officer') }}</th> --}}
                                {{-- <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Condition on Return') }}</th> --}}
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            {{-- Loop through the paginated transactions --}}
                            @foreach ($transactions as $transaction)
                                <tr>
                                    {{-- Table Data --}}
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $transaction->id }}
                                    </td>
                                    {{-- Access equipment name using the 'equipment' relationship --}}
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $transaction->equipment->name ?? 'N/A' }}</td> {{-- Assuming Equipment model has a 'name' attribute --}}
                                    {{-- Access borrower's full name via 'loanApplication' and 'user' relationships --}}
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $transaction->loanApplication->user->full_name ?? 'N/A' }}</td>
                                    {{-- Access borrower via loan application and its user --}}
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{-- Format the issue timestamp --}}
                                        {{ $transaction->issue_timestamp ? $transaction->issue_timestamp->format('Y-m-d H:i:s') : 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{-- Format the return timestamp --}}
                                        {{ $transaction->return_timestamp ? $transaction->return_timestamp->format('Y-m-d H:i:s') : 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $transaction->status }}</td>
                                    {{-- Display other transaction details similarly --}}
                                    {{-- <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $transaction->issuingOfficer->full_name ?? 'N/A' }}</td> --}}
                                    {{-- <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $transaction->equipment_condition_on_return ?? 'N/A' }}</td> --}}
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div> {{-- End overflow-x-auto --}}

                {{-- Pagination Links --}}
                <div class="mt-4">
                    {{ $transactions->links() }} {{-- Renders pagination links --}}
                </div>
            </div>
        @else
            {{-- Message if no transactions are found --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 text-center text-gray-500">
                {{ __('No loan transactions found.') }}
            </div>
        @endif

    </div> {{-- End container --}}

@endsection {{-- End content section --}}

{{-- Optional: Add any necessary styles or scripts here --}}
{{-- This might include Tailwind CSS directives or links to CSS files in your layout --}}
