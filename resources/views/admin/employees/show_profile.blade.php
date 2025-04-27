{{--
    resources/views/admin/employees/show_profile.blade.php

    This view displays the detailed profile of a specific employee for administrators.
    It includes standard employee information and relevant attributes from the related User model,
    such as MOTAC-specific attributes (NRIC, emails, grade, etc.) and linked applications.
    Assumes an $employee object is passed to the view.
    IMPORTANT: Requires eager loading of the 'user' relationship on the Employee model,
               and nested relationships on the user (e.g., user.department, user.position, user.grade).
--}}

{{-- Extend your main admin layout --}}
{{-- Adjust 'layouts.app' if your admin layout is different --}}
@extends('layouts.app')

@section('content')
    <div class="container mx-auto px-4 py-8">
        <div class="flex items-center justify-between mb-6">
            {{-- Page Title --}}
            <h1 class="text-2xl font-bold text-gray-800">
                {{ __('Employee Profile') }}: {{ $employee->full_name ?? 'N/A' }} {{-- Using the employee's full name accessor --}}
            </h1>

            {{-- Back Button --}}
            {{-- Assuming a route named 'resource-management.admin.employees.index' for the employee list --}}
            <a href="{{ route('resource-management.admin.employees.index') }}"
                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                {{ __('Back to Employees List') }}
            </a>
        </div>

        {{-- Employee Profile Card --}}
        <div class="bg-white shadow-md rounded-lg p-6 mb-8">
            <div class="flex flex-col md:flex-row items-center md:items-start">
                {{-- Profile Photo --}}
                <div class="md:mr-8 mb-6 md:mb-0">
                    {{-- Using the getEmployeePhoto helper method from the Employee model --}}
                    <img src="{{ asset($employee->getEmployeePhoto()) }}"
                        alt="{{ $employee->full_name ?? 'Employee' }} Profile Photo"
                        class="w-32 h-32 rounded-full object-cover border-4 border-blue-200">
                </div>

                {{-- Employee and User Information --}}
                <div class="flex-grow">
                    <h2 class="text-xl font-semibold text-gray-700 mb-4">{{ __('Basic Information') }}</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        {{-- Fields from the Employee Model --}}
                        <div>
                            <p class="text-sm font-medium text-gray-500">{{ __('Employee ID') }}:</p>
                            <p class="mt-1 text-gray-900">{{ $employee->id ?? 'N/A' }}</p> {{-- Using the Employee model ID --}}
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">{{ __('HRMS Employee ID') }}:</p>
                            <p class="mt-1 text-gray-900">{{ $employee->employee_id ?? 'N/A' }}</p> {{-- The old HRMS employee ID field --}}
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">{{ __('Full Name') }}:</p>
                            <p class="mt-1 text-gray-900">{{ $employee->full_name ?? 'N/A' }}</p> {{-- Using the full_name accessor --}}
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">{{ __('Identification Number (NRIC)') }}:</p>
                            <p class="mt-1 text-gray-900">{{ $employee->national_number ?? 'N/A' }}</p>
                            {{-- Mapping national_number to NRIC --}}
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">{{ __('Mobile Number') }}:</p>
                            <p class="mt-1 text-gray-900">{{ $employee->mobile_number ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">{{ __('Gender') }}:</p>
                            <p class="mt-1 text-gray-900">{{ $employee->gender ? __($employee->gender) : 'N/A' }}</p>
                            {{-- Localize gender if needed --}}
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">{{ __('Degree') }}:</p>
                            <p class="mt-1 text-gray-900">{{ $employee->degree ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">{{ __('Address') }}:</p>
                            <p class="mt-1 text-gray-900">{{ $employee->address ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">{{ __('Join Date') }}:</p>
                            <p class="mt-1 text-gray-900">{{ $employee->join_at ?? 'N/A' }}</p> {{-- Using the join_at accessor --}}
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">{{ __('Worked Years') }}:</p>
                            <p class="mt-1 text-gray-900">{{ $employee->worked_years ?? 'N/A' }}</p> {{-- Using the worked_years accessor --}}
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">{{ __('Current Position') }}:</p>
                            <p class="mt-1 text-gray-900">{{ $employee->current_position ?? 'N/A' }}</p>
                            {{-- Using the current_position accessor --}}
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">{{ __('Current Department') }}:</p>
                            <p class="mt-1 text-gray-900">{{ $employee->current_department ?? 'N/A' }}</p>
                            {{-- Using the current_department accessor --}}
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">{{ __('Current Center') }}:</p>
                            <p class="mt-1 text-gray-900">{{ $employee->current_center ?? 'N/A' }}</p>
                            {{-- Using the current_center accessor --}}
                        </div>


                        {{-- Fields from the Related User Model (New RM Attributes) --}}
                        {{-- IMPORTANT: Ensure the 'user' relationship and its nested relationships (department, position, grade) are eager loaded --}}
                        @if ($employee->user)
                            {{-- Check if a related User exists --}}
                            <div class="md:col-span-2 border-t pt-4 mt-4">
                                <h3 class="text-lg font-semibold text-gray-700 mb-3">{{ __('User Account Details') }}</h3>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">{{ __('Linked Email') }}:</p>
                                <p class="mt-1 text-gray-900">{{ $employee->user->email ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">{{ __('Personal Email') }}:</p>
                                <p class="mt-1 text-gray-900">{{ $employee->user->personal_email ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">{{ __('MOTAC Email') }}:</p>
                                <p class="mt-1 text-gray-900">{{ $employee->user->motac_email ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">{{ __('User ID Assigned') }}:</p>
                                <p class="mt-1 text-gray-900">{{ $employee->user->user_id_assigned ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">{{ __('User Account Status') }}:</p>
                                <p class="mt-1 text-gray-900">{{ $employee->user->status ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">{{ __('Grade') }}:</p>
                                <p class="mt-1 text-gray-900">{{ $employee->user->grade->name ?? 'N/A' }}</p>
                                {{-- Accessing grade name via nested relationship --}}
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">{{ __('Service Status') }}:</p>
                                <p class="mt-1 text-gray-900">{{ $employee->user->service_status ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">{{ __('Appointment Type') }}:</p>
                                <p class="mt-1 text-gray-900">{{ $employee->user->appointment_type ?? 'N/A' }}</p>
                            </div>

                            {{-- Link to User Profile --}}
                            <div class="md:col-span-2">
                                @can('view', $employee->user)
                                    {{-- Check permission to view the related user --}}
                                    <a href="{{ route('resource-management.admin.users.show', $employee->user) }}"
                                        class="text-blue-600 hover:underline">
                                        {{ __('View Full User Profile') }}
                                    </a>
                                @endcan
                            </div>
                        @else
                            <div class="md:col-span-2 border-t pt-4 mt-4">
                                <p class="text-gray-600">{{ __('No user account linked to this employee.') }}</p>
                            </div>
                        @endif

                    </div>
                </div>
            </div>
        </div>

        {{-- Related Applications Section (Optional) --}}
        {{-- Check if the user relationship exists and has permissions --}}
        {{-- Ensure 'user.emailApplications' and 'user.loanApplications' relationships are eager loaded --}}
        @if (
            $employee->user &&
                (Auth::user()->can('viewAny', \App\Models\EmailApplication::class) ||
                    Auth::user()->can('viewAny', \App\Models\LoanApplication::class)))
            <div class="bg-white shadow-md rounded-lg p-6 mb-8">
                <h2 class="text-xl font-semibold text-gray-700 mb-4">{{ __('Related Applications (by Linked User)') }}</h2>

                {{-- Email Applications --}}
                @can('viewAny', \App\Models\EmailApplication::class)
                    <h3 class="text-lg font-medium text-gray-600 mb-3">{{ __('Email Applications') }}</h3>
                    @if ($employee->user->emailApplications->count() > 0)
                        <ul class="list-disc pl-5 mb-4">
                            @foreach ($employee->user->emailApplications as $application)
                                {{-- Assuming 'my-applications.email.show' route exists --}}
                                <li>
                                    <a href="{{ route('my-applications.email.show', $application) }}"
                                        class="text-blue-600 hover:underline">
                                        {{ __('Application ID') }}: {{ $application->id }} -
                                        {{ $application->created_at->format('Y-m-d') }} (Status: {{ $application->status }})
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-gray-600 mb-4">{{ __('No email applications submitted by this user.') }}</p>
                    @endif
                @endcan

                {{-- Loan Applications --}}
                @can('viewAny', \App\Models\LoanApplication::class)
                    <h3 class="text-lg font-medium text-gray-600 mb-3">{{ __('Loan Applications') }}</h3>
                    @if ($employee->user->loanApplications->count() > 0)
                        <ul class="list-disc pl-5 mb-4">
                            @foreach ($employee->user->loanApplications as $application)
                                {{-- Assuming 'my-applications.loan.show' route exists --}}
                                <li>
                                    <a href="{{ route('my-applications.loan.show', $application) }}"
                                        class="text-blue-600 hover:underline">
                                        {{ __('Application ID') }}: {{ $application->id }} -
                                        {{ $application->created_at->format('Y-m-d') }} (Status: {{ $application->status }})
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-gray-600 mb-4">{{ __('No loan applications submitted by this user.') }}</p>
                    @endif
                @endcan

                {{-- Add other related resource management data here (e.g., Issued Loans, Approvals Made by this user) --}}

            </div>
        @endif


        {{-- Related Data Sections (Leaves, Fingerprints, etc.) --}}
        {{-- Example: Leaves --}}
        @if ($employee->leaves->count() > 0)
            <div class="bg-white shadow-md rounded-lg p-6 mb-8">
                <h2 class="text-xl font-semibold text-gray-700 mb-4">{{ __('Related Leaves') }}</h2>
                {{-- You would typically list leaves in a table here --}}
                {{-- Example list item --}}
                <ul class="list-disc pl-5">
                    @foreach ($employee->leaves as $leave)
                        <li>{{ __('Leave Type') }}: {{ $leave->leaveType->name ?? 'N/A' }} - {{ __('Dates') }}:
                            {{ $leave->from_date }} to {{ $leave->to_date }}</li>
                    @endforeach
                </ul>
                {{-- Link to show all leaves for this employee if applicable --}}
                {{-- Example: Assuming a route resource-management.admin.leaves.employee_index or similar --}}
                {{-- <div class="mt-4">
                        <a href="{{ route('resource-management.admin.leaves.employee_index', $employee) }}" class="text-blue-600 hover:underline">{{ __('View All Leaves for this Employee') }}</a>
                   </div> --}}
            </div>
        @endif

        {{-- Add other related data sections as needed (Fingerprints, Timelines, etc.) --}}
        {{-- Example: Fingerprints --}}
        @if ($employee->fingerprints->count() > 0)
            <div class="bg-white shadow-md rounded-lg p-6 mb-8">
                <h2 class="text-xl font-semibold text-gray-700 mb-4">{{ __('Fingerprints') }}</h2>
                <ul class="list-disc pl-5">
                    @foreach ($employee->fingerprints as $fingerprint)
                        <li>{{ __('Fingerprint ID') }}: {{ $fingerprint->fingerprint_id }} - {{ __('Device') }}:
                            {{ $fingerprint->device->name ?? 'N/A' }}</li>
                    @endforeach
                </ul>
            </div>
        @endif


        {{-- Actions Section --}}
        {{-- Check if the user has permission to update or delete this employee --}}
        @if (Auth::user()->can('update employees', $employee) || Auth::user()->can('delete employees', $employee))
            {{-- Assuming permissions like 'update employees', 'delete employees' --}}
            <div class="bg-white shadow-md rounded-lg p-6">
                <h2 class="text-xl font-semibold text-gray-700 mb-4">{{ __('Actions') }}</h2>
                <div class="flex space-x-4">
                    {{-- Edit Button --}}
                    @can('update employees', $employee)
                        {{-- Assuming 'resource-management.admin.employees.edit' route exists --}}
                        <a href="{{ route('resource-management.admin.employees.edit', $employee) }}"
                            class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded">
                            {{ __('Edit Employee') }}
                        </a>
                    @endcan

                    {{-- Delete Button --}}
                    @can('delete employees', $employee)
                        {{-- Implement a confirmation dialog for deletion --}}
                        <form action="{{ route('resource-management.admin.employees.destroy', $employee) }}" method="POST"
                            onsubmit="return confirm('{{ __('Are you sure you want to delete this employee?') }}');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
                                {{ __('Delete Employee') }}
                            </button>
                        </form>
                    @endcan

                    {{-- Add other actions here (e.g., link/button to create a linked User account if one doesn't exist) --}}
                    @if (!$employee->user && Auth::user()->can('create users'))
                        {{-- Check if no user linked and admin can create users --}}
                        <a href="{{ route('resource-management.admin.users.create', ['employee_id' => $employee->id]) }}"
                            {{-- Example route to create user linked to employee --}}
                            class="bg-purple-500 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded">
                            {{ __('Link New User Account') }}
                        </a>
                    @endif
                </div>
            </div>
        @endif

    </div>
@endsection
