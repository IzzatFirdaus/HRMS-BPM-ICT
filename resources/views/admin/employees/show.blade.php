{{--
    resources/views/admin/employees/show.blade.php

    This view displays the details of a specific employee.
    It includes standard employee information and relevant attributes from the related User model,
    such as MOTAC-specific attributes (NRIC, emails, grade, etc.) and linked applications.
    Assumes an $employee object is passed to the view.
    IMPORTANT: Requires eager loading of the 'user' relationship on the Employee model,
               and nested relationships on the user (e.g., user.department, user.position, user.grade),
               and other related relationships (e.g., employee.leaves, employee.fingerprints, user.emailApplications, user.loanApplications).
--}}

{{-- Extend your main admin layout --}}
{{-- Adjust 'layouts.app' if your admin layout is different --}}
@extends('layouts.app')

@section('content')
    <div class="container mx-auto px-4 py-8">
        {{-- Header: Title and Back Button --}}
        <div class="flex flex-wrap items-center justify-between mb-6 gap-4"> {{-- Added flex-wrap and gap for better responsiveness --}}
            {{-- Page Title --}}
            <h1 class="text-2xl font-bold text-gray-800">
                {{ __('Employee Details') }}: {{ $employee->full_name ?? 'N/A' }} {{-- Using the employee's full name accessor --}}
            </h1>

            {{-- Back Button --}}
            {{-- Assuming a route named 'resource-management.admin.employees.index' for the employee list --}}
            {{-- Standardized button styling --}}
            <a href="{{ route('resource-management.admin.employees.index') }}"
                class="inline-flex items-center justify-center px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold rounded shadow-sm focus:outline-none focus:ring focus:ring-gray-400 transition">
                {{ __('Back to Employees List') }} {{-- Translate button text --}}
            </a>
        </div>

        {{-- Display session-based success or error messages (flash messages) --}}
        @if (session()->has('success'))
            <div class="bg-green-100 border border-green-200 text-green-800 p-4 rounded-md mb-4">
                {{ session('success') }}
            </div>
        @endif

        @if (session()->has('error'))
            <div class="bg-red-100 border border-red-200 text-red-800 p-4 rounded-md mb-4">
                {{ session('error') }}
            </div>
        @endif

        {{-- Employee Details Card --}}
        <div class="bg-white shadow-md rounded-lg p-6 mb-8">
            <div class="flex flex-col md:flex-row items-center md:items-start">
                {{-- Profile Photo --}}
                <div class="md:mr-8 mb-6 md:mb-0 flex-shrink-0"> {{-- Added flex-shrink-0 to prevent photo from shrinking --}}
                    {{-- Using the getEmployeePhoto helper method from the Employee model --}}
                    <img src="{{ asset($employee->getEmployeePhoto()) }}"
                        alt="{{ $employee->full_name ?? 'Employee' }} Profile Photo"
                        class="w-32 h-32 rounded-full object-cover border-4 border-blue-200">
                </div>

                {{-- Employee and User Information --}}
                <div class="flex-grow">
                    <h2 class="text-xl font-semibold text-gray-700 mb-4">{{ __('Basic Information') }}</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-gray-800"> {{-- Added text-gray-800 for general text color --}}
                        {{-- Fields from the Employee Model --}}
                        <div>
                            <p class="text-sm font-medium text-gray-500">{{ __('Employee ID') }}:</p> {{-- Label styling --}}
                            <p class="mt-1">{{ $employee->id ?? 'N/A' }}</p> {{-- Value styling --}}
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">{{ __('HRMS Employee ID') }}:</p>
                            <p class="mt-1">{{ $employee->employee_id ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">{{ __('Full Name') }}:</p>
                            <p class="mt-1">{{ $employee->full_name ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">{{ __('Identification Number (NRIC)') }}:</p>
                            <p class="mt-1">{{ $employee->national_number ?? 'N/A' }}</p>
                            {{-- Mapping national_number to NRIC --}}
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">{{ __('Mobile Number') }}:</p>
                            <p class="mt-1">{{ $employee->mobile_number ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">{{ __('Gender') }}:</p>
                            <p class="mt-1">{{ $employee->gender ? __($employee->gender) : 'N/A' }}</p>
                            {{-- Localize gender if needed --}}
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">{{ __('Degree') }}:</p>
                            <p class="mt-1">{{ $employee->degree ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">{{ __('Address') }}:</p>
                            <p class="mt-1">{{ $employee->address ?? 'N/A' }}</p>
                        </div>
                        {{-- Added Birth Date and Place as it was in the edit form --}}
                        <div>
                            <p class="text-sm font-medium text-gray-500">{{ __('Birth Date and Place') }}:</p>
                            <p class="mt-1">{{ $employee->birth_and_place ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">{{ __('Join Date') }}:</p>
                            {{-- Assuming join_at accessor provides formatted date string --}}
                            <p class="mt-1">{{ $employee->join_at ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">{{ __('Worked Years') }}:</p>
                            {{-- Assuming worked_years accessor provides formatted string --}}
                            <p class="mt-1">{{ $employee->worked_years ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">{{ __('Current Position') }}:</p>
                            {{-- Assuming current_position accessor gets name from relationship --}}
                            <p class="mt-1">{{ $employee->current_position ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">{{ __('Current Department') }}:</p>
                            {{-- Assuming current_department accessor gets name from relationship --}}
                            <p class="mt-1">{{ $employee->current_department ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">{{ __('Current Center') }}:</p>
                            {{-- Assuming current_center accessor gets name from relationship --}}
                            <p class="mt-1">{{ $employee->current_center ?? 'N/A' }}</p>
                        </div>


                        {{-- Fields from the Related User Model (New RM Attributes) --}}
                        {{-- IMPORTANT: Ensure the 'user' relationship and its nested relationships (department, position, grade) are eager loaded --}}
                        @if ($employee->user)
                            {{-- Check if a related User exists --}}
                            {{-- Divider and title for User Account Details --}}
                            <div class="md:col-span-2 pt-4 mt-4 border-t border-gray-200"> {{-- Added border and padding/margin --}}
                                <h3 class="text-lg font-semibold text-gray-700 mb-3">{{ __('User Account Details') }}</h3>
                            </div>
                            {{-- Display user details in the grid --}}
                            <div>
                                <p class="text-sm font-medium text-gray-500">{{ __('Linked Email') }}:</p>
                                <p class="mt-1">{{ $employee->user->email ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">{{ __('Personal Email') }}:</p>
                                <p class="mt-1">{{ $employee->user->personal_email ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">{{ __('MOTAC Email') }}:</p>
                                <p class="mt-1">{{ $employee->user->motac_email ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">{{ __('User ID Assigned') }}:</p>
                                <p class="mt-1">{{ $employee->user->user_id_assigned ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">{{ __('User Account Status') }}:</p>
                                <p class="mt-1">{{ $employee->user->status ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">{{ __('Grade') }}:</p>
                                {{-- Accessing grade name via nested relationship --}}
                                <p class="mt-1">{{ $employee->user->grade->name ?? 'N/A' }}</p>
                            </div>
                            {{-- Added Department and Position from User relationship --}}
                            <div>
                                <p class="text-sm font-medium text-gray-500">{{ __('User Department') }}:</p>
                                <p class="mt-1">{{ $employee->user->department->name ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">{{ __('User Position') }}:</p>
                                <p class="mt-1">{{ $employee->user->position->name ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">{{ __('Service Status') }}:</p>
                                <p class="mt-1">{{ $employee->user->service_status ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">{{ __('Appointment Type') }}:</p>
                                <p class="mt-1">{{ $employee->user->appointment_type ?? 'N/A' }}</p>
                            </div>

                            {{-- Link to User Profile --}}
                            <div class="md:col-span-2 lg:col-span-3 mt-4"> {{-- Span across columns --}}
                                @can('view', $employee->user)
                                    {{-- Check permission to view the related user --}}
                                    <a href="{{ route('resource-management.admin.users.show', $employee->user) }}"
                                        class="text-blue-600 hover:text-blue-800 underline inline-flex items-center">
                                        {{-- Standardized link styling --}}
                                        {{ __('View Full User Profile') }} {{-- Translate link text --}}
                                    </a>
                                @endcan
                            </div>
                        @else
                            {{-- Message if no user is linked --}}
                            <div class="md:col-span-2 lg:col-span-3 pt-4 mt-4 border-t border-gray-200">
                                <p class="text-gray-600">{{ __('No user account linked to this employee.') }}</p>
                                {{-- Translate message --}}
                            </div>
                        @endif

                    </div> {{-- End Employee and User Info Grid --}}
                </div> {{-- End Employee and User Info Block --}}
            </div> {{-- End Flex Container for Photo and Info --}}
        </div> {{-- End Employee Details Card --}}

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
                    <div class="mb-6"> {{-- Added margin-bottom --}}
                        <h3 class="text-lg font-medium text-gray-700 mb-3">{{ __('Email Applications') }}</h3>
                        {{-- Adjusted heading level and color --}}
                        @if ($employee->user->emailApplications->count() > 0)
                            {{-- TODO: Consider displaying this in a table for better structure --}}
                            <ul class="list-disc pl-5 space-y-1"> {{-- Added space between list items --}}
                                @foreach ($employee->user->emailApplications as $application)
                                    {{-- Assuming 'my-applications.email.show' route exists --}}
                                    <li>
                                        <a href="{{ route('my-applications.email.show', $application) }}"
                                            class="text-blue-600 hover:text-blue-800 underline"> {{-- Standardized link styling --}}
                                            {{ __('Application ID') }}: {{ $application->id }} -
                                            {{ $application->created_at->format('Y-m-d') }} ({{ __('Status') }}:
                                            {{ $application->status }}) {{-- Translate Status --}}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <p class="text-gray-600">{{ __('No email applications submitted by this user.') }}</p>
                            {{-- Translate message --}}
                        @endif
                    </div>
                @endcan

                {{-- Loan Applications --}}
                @can('viewAny', \App\Models\LoanApplication::class)
                    <div class="mb-6"> {{-- Added margin-bottom --}}
                        <h3 class="text-lg font-medium text-gray-700 mb-3">{{ __('Loan Applications') }}</h3>
                        {{-- Adjusted heading level and color --}}
                        @if ($employee->user->loanApplications->count() > 0)
                            {{-- TODO: Consider displaying this in a table for better structure --}}
                            <ul class="list-disc pl-5 space-y-1"> {{-- Added space between list items --}}
                                @foreach ($employee->user->loanApplications as $application)
                                    {{-- Assuming 'my-applications.loan.show' route exists --}}
                                    <li>
                                        <a href="{{ route('my-applications.loan.show', $application) }}"
                                            class="text-blue-600 hover:text-blue-800 underline"> {{-- Standardized link styling --}}
                                            {{ __('Application ID') }}: {{ $application->id }} -
                                            {{ $application->created_at->format('Y-m-d') }} ({{ __('Status') }}:
                                            {{ $application->status }}) {{-- Translate Status --}}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <p class="text-gray-600">{{ __('No loan applications submitted by this user.') }}</p>
                            {{-- Translate message --}}
                        @endif
                    </div>
                @endcan

                {{-- Add other related resource management data here (e.g., Issued Loans, Approvals Made by this user) --}}
                {{-- TODO: Add sections for other User-related data here --}}

            </div> {{-- End Related Applications Card --}}
        @endif


        {{-- Related Data Sections (Leaves, Fingerprints, Timelines etc.) --}}

        {{-- Leaves Section --}}
        {{-- Ensure 'leaves' relationship is eager loaded, and nested 'leaveType' if used --}}
        @if ($employee->leaves && $employee->leaves->count() > 0)
            {{-- Added null check for $employee->leaves --}}
            <div class="bg-white shadow-md rounded-lg p-6 mb-8">
                <h2 class="text-xl font-semibold text-gray-700 mb-4">{{ __('Related Leaves') }}</h2>
                {{-- TODO: Consider displaying this in a table for better structure or using a Livewire component --}}
                {{-- You would typically list leaves in a table here --}}
                <ul class="list-disc pl-5 space-y-1"> {{-- Added space between list items --}}
                    @foreach ($employee->leaves as $leave)
                        <li>
                            {{ __('Leave Type') }}: {{ $leave->leaveType->name ?? 'N/A' }} -
                            {{ __('Dates') }}: {{ $leave->from_date?->format('Y-m-d') ?? 'N/A' }} to
                            {{ $leave->to_date?->format('Y-m-d') ?? 'N/A' }} {{-- Added null-safe operator and formatting --}}
                            ({{ __('Status') }}: {{ $leave->status ?? 'N/A' }})
                            {{-- Added status and translation --}}
                            {{-- TODO: Add a link to view full leave details if applicable --}}
                            {{-- Example: Assuming a route 'resource-management.admin.leaves.show' or similar --}}
                            {{-- <a href="{{ route('resource-management.admin.leaves.show', $leave) }}" class="text-blue-600 hover:underline ml-2">{{ __('View Details') }}</a> --}}
                        </li>
                    @endforeach
                </ul>
                {{-- Link to show all leaves for this employee if applicable --}}
                {{-- Example: Assuming a route resource-management.admin.leaves.employee_index or similar --}}
                {{-- <div class="mt-4">
                        <a href="{{ route('resource-management.admin.leaves.employee_index', $employee) }}" class="text-blue-600 hover:text-blue-800 underline">{{ __('View All Leaves for this Employee') }}</a> // Standardized link styling
                   </div> --}}
            </div> {{-- End Leaves Section --}}
        @endif

        {{-- Fingerprints Section --}}
        {{-- Ensure 'fingerprints' relationship is eager loaded, and nested 'device' if used --}}
        @if ($employee->fingerprints && $employee->fingerprints->count() > 0)
            {{-- Added null check for $employee->fingerprints --}}
            <div class="bg-white shadow-md rounded-lg p-6 mb-8">
                <h2 class="text-xl font-semibold text-gray-700 mb-4">{{ __('Fingerprints') }}</h2>
                {{-- TODO: Consider displaying this in a table for better structure or using a Livewire component --}}
                <ul class="list-disc pl-5 space-y-1"> {{-- Added space between list items --}}
                    @foreach ($employee->fingerprints as $fingerprint)
                        <li>
                            {{ __('Fingerprint ID') }}: {{ $fingerprint->fingerprint_id ?? 'N/A' }} -
                            {{ __('Device') }}: {{ $fingerprint->device->name ?? 'N/A' }}
                            {{-- TODO: Add more fingerprint details or a link to view full details --}}
                        </li>
                    @endforeach
                </ul>
                {{-- TODO: Add a link to show all fingerprints for this employee if applicable --}}
            </div> {{-- End Fingerprints Section --}}
        @endif

        {{-- TODO: Add other related data sections as needed (Timelines, Assets, Trainings, etc.) --}}
        {{-- Example: Timelines --}}
        {{-- @if ($employee->timelines && $employee->timelines->count() > 0) // Assuming 'timelines' relationship exists and is eager loaded
             <div class="bg-white shadow-md rounded-lg p-6 mb-8">
                 <h2 class="text-xl font-semibold text-gray-700 mb-4">{{ __('Timelines') }}</h2>
                 // TODO: Display timeline entries (e.g., in a table or chronologically) or use a Livewire component
                  <ul class="list-disc pl-5 space-y-1">
                      @foreach ($employee->timelines as $timeline)
                          <li>
                              {{ __('Period') }}: {{ $timeline->start_date?->format('Y-m-d') ?? 'N/A' }} - {{ $timeline->end_date?->format('Y-m-d') ?? 'N/A' }}
                              ({{ __('Center') }}: {{ $timeline->center->name ?? 'N/A' }}, {{ __('Department') }}: {{ $timeline->department->name ?? 'N/A' }}, {{ __('Position') }}: {{ $timeline->position->name ?? 'N/A' }})
                          </li>
                      @endforeach
                  </ul>
                 // TODO: Add a link to show all timelines for this employee if applicable
             </div>
         @endif
        --}}


        {{-- Actions Section --}}
        {{-- Check if the user has permission to update or delete this employee or create users --}}
        @if (Auth::user()->can('update employees', $employee) ||
                Auth::user()->can('delete employees', $employee) ||
                Auth::user()->can('create users'))
            {{-- Assuming permissions like 'update employees', 'delete employees', 'create users' --}}
            <div class="bg-white shadow-md rounded-lg p-6 mb-8"> {{-- Added mb-8 for spacing --}}
                <h2 class="text-xl font-semibold text-gray-700 mb-4">{{ __('Actions') }}</h2>
                <div class="flex flex-wrap gap-4"> {{-- Added flex-wrap and gap for layout --}}
                    {{-- Edit Button --}}
                    @can('update employees', $employee)
                        {{-- Assuming 'resource-management.admin.employees.edit' route exists --}}
                        {{-- Standardized button styling --}}
                        <a href="{{ route('resource-management.admin.employees.edit', $employee) }}"
                            class="inline-flex items-center justify-center px-4 py-2 bg-yellow-500 hover:bg-yellow-600 text-white font-bold rounded shadow-sm focus:outline-none focus:ring focus:ring-yellow-400 transition">
                            {{ __('Edit Employee') }} {{-- Translate button text --}}
                        </a>
                    @endcan

                    {{-- Delete Button --}}
                    @can('delete employees', $employee)
                        {{-- Implement a confirmation dialog for deletion (using browser confirm for simplicity here) --}}
                        <form action="{{ route('resource-management.admin.employees.destroy', $employee) }}" method="POST"
                            onsubmit="return confirm('{{ __('Are you sure you want to delete this employee?') }}');"
                            {{-- Translate confirm message --}} class="inline-block"> {{-- Use inline-block to keep it in line with other buttons if needed --}}
                            @csrf
                            @method('DELETE')
                            {{-- Standardized button styling --}}
                            <button type="submit"
                                class="inline-flex items-center justify-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-bold rounded shadow-sm focus:outline-none focus:ring focus:ring-red-400 transition">
                                {{ __('Delete Employee') }} {{-- Translate button text --}}
                            </button>
                        </form>
                        {{-- TODO: Consider using a modal for delete confirmation --}}
                    @endcan

                    {{-- Add other actions here (e.g., link/button to create a linked User account if one doesn't exist) --}}
                    @if (!$employee->user && Auth::user()->can('create users'))
                        {{-- Check if no user linked and admin can create users --}}
                        {{-- Standardized button styling --}}
                        <a href="{{ route('resource-management.admin.users.create', ['employee_id' => $employee->id]) }}"
                            {{-- Example route to create user linked to employee, passing employee_id --}}
                            class="inline-flex items-center justify-center px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white font-bold rounded shadow-sm focus:outline-none focus:ring focus:ring-purple-400 transition">
                            {{ __('Link New User Account') }} {{-- Translate button text --}}
                        </a>
                    @endif
                    {{-- TODO: Add a link/button to unlink a user account if one is linked --}}
                </div> {{-- End Flex Container for Actions --}}
            </div> {{-- End Actions Section --}}
        @endif


    </div> {{-- End main container --}}
@endsection

{{-- You might have other scripts or footer content in your layout --}}
