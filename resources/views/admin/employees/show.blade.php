{{--
    resources/views/admin/employees/show.blade.php

    This view displays the details of a specific employee.
    It includes standard employee information and relevant attributes from the related User model,
    such as MOTAC-specific attributes (NRIC, emails, grade, etc.) and linked applications.
    Assumes an $employee object is passed to the view.

    ****************************************************************************
    IMPORTANT: This view critically relies on eager loading of the 'user' relationship
               and its nested relationships (user.department, user.position, user.grade),
               and other related relationships (e.g., employee.leaves, employee.fingerprints,
               user.emailApplications, user.loanApplications, etc.) in the controller's
               show method to avoid N+1 query issues.
               Example in controller: Employee::with([
                   'user.department', 'user.position', 'user.grade',
                   'leaves.leaveType', 'fingerprints.device', // Eager load relationships for related data sections
                   'user.emailApplications', 'user.loanApplications', // Eager load relationships for user applications
                   // Add other relationships used in TODO sections here (e.g., 'timelines.center', 'timelines.department', 'timelines.position')
               ])->findOrFail($id);
    ****************************************************************************
--}}

{{-- Extend your main admin layout --}}
{{-- Adjust 'layouts.app' if your admin layout is different --}}
@extends('layouts.app')

{{-- Define the title section to be included in the layout's <head> --}}
{{-- Translate the page title with the employee's full name --}}
@section('title', __('Employee Details: :name', ['name' => $employee->full_name ?? 'N/A']))

{{-- Define any page-specific styles (optional) --}}
@section('page-style')
    {{-- Link any CSS specific to this page if needed, typically compiled assets --}}
    {{-- Example: <link rel="stylesheet" href="{{ asset('css/employees-show.css') }}"> --}}
    {{-- Tailwind CSS is assumed to be included in the main app.css or layout --}}
@endsection

{{-- Define the main content section --}}
@section('content')
    <div class="container mx-auto px-4 py-8">
        {{-- Header: Title and Back Button --}}
        {{-- Use flexbox to align title and button on the same line, allowing wrap on small screens --}}
        <div class="flex flex-wrap items-center justify-between mb-6 gap-4"> {{-- Added flex-wrap and gap for better responsiveness --}}
            {{-- Page Title --}}
            <h1 class="text-2xl font-bold text-gray-800">
                {{ __('Employee Details') }}: {{ $employee->full_name ?? 'N/A' }} {{-- Using the employee's full name accessor, provide fallback --}}
            </h1>

            {{-- Back Button --}}
            {{-- Assuming a route named 'resource-management.admin.employees.index' for the employee list --}}
            {{-- Standardized button styling using Tailwind classes --}}
            <a href="{{ route('resource-management.admin.employees.index') }}"
                class="inline-flex items-center justify-center px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold rounded shadow-sm focus:outline-none focus:ring focus:ring-gray-400 transition">
                {{-- Check for gray-400 focus ring --}}
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
        {{-- Outer container with card-like appearance --}}
        <div class="bg-white shadow-md rounded-lg p-6 mb-8">
            {{-- Use flexbox to arrange profile photo and information block side-by-side on medium screens and up --}}
            <div class="flex flex-col md:flex-row items-center md:items-start">
                {{-- Profile Photo Block --}}
                {{-- md:mr-8 adds margin-right on medium screens; mb-6 adds margin-bottom on all screens, removed on medium --}}
                <div class="md:mr-8 mb-6 md:mb-0 flex-shrink-0"> {{-- Added flex-shrink-0 to prevent photo from shrinking --}}
                    {{-- Using the getEmployeePhoto helper method from the Employee model (assumes it returns a relative path) --}}
                    {{-- asset() is used to generate a public URL for the photo --}}
                    <img src="{{ asset($employee->getEmployeePhoto()) }}"
                        alt="{{ $employee->full_name ?? 'Employee' }} Profile Photo" {{-- Added localized alt text, provide fallback --}}
                        class="w-32 h-32 rounded-full object-cover border-4 border-blue-200"> {{-- Applied standard Tailwind classes for size, shape, fit, border --}}
                </div>

                {{-- Employee and User Information Block --}}
                <div class="flex-grow"> {{-- Allows this block to take up remaining space --}}
                    <h2 class="text-xl font-semibold text-gray-700 mb-4">{{ __('Basic Information') }}</h2>
                    {{-- Use a grid for a two-column layout on medium screens and above --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-gray-800"> {{-- Added text-gray-800 for general text color --}}
                        {{-- Fields from the Employee Model --}}
                        <div>
                            <p class="text-sm font-medium text-gray-500">{{ __('Employee ID') }}:</p> {{-- Label styling --}}
                            <p class="mt-1">{{ $employee->id ?? 'N/A' }}</p> {{-- Value styling, provide fallback --}}
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">{{ __('HRMS Employee ID') }}:</p>
                            <p class="mt-1">{{ $employee->employee_id ?? 'N/A' }}</p> {{-- Provide fallback --}}
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">{{ __('Full Name') }}:</p>
                            <p class="mt-1">{{ $employee->full_name ?? 'N/A' }}</p> {{-- Provide fallback --}}
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">{{ __('Identification Number (NRIC)') }}:</p>
                            <p class="mt-1">{{ $employee->national_number ?? 'N/A' }}</p> {{-- Provide fallback --}}
                            {{-- Mapping national_number to NRIC as per comment --}}
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">{{ __('Mobile Number') }}:</p>
                            <p class="mt-1">{{ $employee->mobile_number ?? 'N/A' }}</p> {{-- Provide fallback --}}
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">{{ __('Gender') }}:</p>
                            {{-- Localize gender if needed, provide fallback --}}
                            <p class="mt-1">{{ $employee->gender ? __($employee->gender) : 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">{{ __('Degree') }}:</p>
                            <p class="mt-1">{{ $employee->degree ?? 'N/A' }}</p> {{-- Provide fallback --}}
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">{{ __('Address') }}:</p>
                            <p class="mt-1">{{ $employee->address ?? 'N/A' }}</p> {{-- Provide fallback --}}
                        </div>
                        {{-- Added Birth Date and Place as it was in the edit form, provide fallback --}}
                        <div>
                            <p class="text-sm font-medium text-gray-500">{{ __('Birth Date and Place') }}:</p>
                            <p class="mt-1">{{ $employee->birth_and_place ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">{{ __('Join Date') }}:</p>
                            {{-- Assuming join_at is a Carbon instance or accessor providing formatted date string, provide fallback --}}
                            <p class="mt-1">{{ $employee->join_at?->format('Y-m-d') ?? 'N/A' }}</p>
                            {{-- Added formatting for clarity --}}
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">{{ __('Worked Years') }}:</p>
                            {{-- Assuming worked_years is an accessor providing formatted string, provide fallback --}}
                            <p class="mt-1">{{ $employee->worked_years ?? 'N/A' }}</p>
                        </div>
                        {{-- Display Current Position, Department, and Center from potential accessors or relationships --}}
                        {{-- Assumes accessors exist or this data is derived --}}
                        <div>
                            <p class="text-sm font-medium text-gray-500">{{ __('Current Position') }}:</p>
                            <p class="mt-1">{{ $employee->current_position ?? 'N/A' }}</p> {{-- Provide fallback --}}
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">{{ __('Current Department') }}:</p>
                            <p class="mt-1">{{ $employee->current_department ?? 'N/A' }}</p> {{-- Provide fallback --}}
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">{{ __('Current Center') }}:</p>
                            <p class="mt-1">{{ $employee->current_center ?? 'N/A' }}</p> {{-- Provide fallback --}}
                        </div>


                        {{-- Fields from the Related User Model (New RM Attributes) --}}
                        {{-- IMPORTANT: Ensure the 'user' relationship and its nested relationships (department, position, grade) are eager loaded --}}
                        @if ($employee->user)
                            {{-- Check if a related User exists before attempting to display user details --}}
                            {{-- Divider and title for User Account Details, spanning columns on medium screens --}}
                            <div class="md:col-span-2 pt-4 mt-4 border-t border-gray-200"> {{-- Added border and padding/margin --}}
                                <h3 class="text-lg font-semibold text-gray-700 mb-3">{{ __('User Account Details') }}</h3>
                            </div>
                            {{-- Display user details in the grid --}}
                            <div>
                                <p class="text-sm font-medium text-gray-500">{{ __('Linked Email') }}:</p>
                                <p class="mt-1">{{ $employee->user->email ?? 'N/A' }}</p> {{-- Provide fallback --}}
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">{{ __('Personal Email') }}:</p>
                                <p class="mt-1">{{ $employee->user->personal_email ?? 'N/A' }}</p> {{-- Provide fallback --}}
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">{{ __('MOTAC Email') }}:</p>
                                <p class="mt-1">{{ $employee->user->motac_email ?? 'N/A' }}</p> {{-- Provide fallback --}}
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">{{ __('User ID Assigned') }}:</p>
                                <p class="mt-1">{{ $employee->user->user_id_assigned ?? 'N/A' }}</p>
                                {{-- Provide fallback --}}
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">{{ __('User Account Status') }}:</p>
                                <p class="mt-1">{{ $employee->user->status ?? 'N/A' }}</p> {{-- Provide fallback --}}
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">{{ __('Grade') }}:</p>
                                {{-- Accessing grade name via nested relationship (requires user.grade eager loading), provide fallback --}}
                                <p class="mt-1">{{ $employee->user->grade->name ?? 'N/A' }}</p>
                            </div>
                            {{-- Added Department and Position from User relationship (requires user.department, user.position eager loading), provide fallback --}}
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
                                <p class="mt-1">{{ $employee->user->service_status ?? 'N/A' }}</p> {{-- Provide fallback --}}
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">{{ __('Appointment Type') }}:</p>
                                <p class="mt-1">{{ $employee->user->appointment_type ?? 'N/A' }}</p>
                                {{-- Provide fallback --}}
                            </div>

                            {{-- Link to View Full User Profile --}}
                            {{-- Check permission to view the related user model --}}
                            {{-- Spans across columns on medium/large screens --}}
                            <div class="md:col-span-2 lg:col-span-3 mt-4">
                                @can('view', $employee->user)
                                    {{-- Assuming a route named 'resource-management.admin.users.show' exists and takes the user model --}}
                                    {{-- Standardized link styling --}}
                                    <a href="{{ route('resource-management.admin.users.show', $employee->user) }}"
                                        class="text-blue-600 hover:text-blue-800 underline inline-flex items-center">
                                        {{ __('View Full User Profile') }} {{-- Translate link text --}}
                                    </a>
                                @endcan
                            </div>
                        @else
                            {{-- Message displayed if no user is linked, spanning columns --}}
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
        {{-- Check if the user relationship exists and the current user has permissions to view application lists --}}
        {{-- Ensure 'user.emailApplications' and 'user.loanApplications' relationships are eager loaded --}}
        @if (
            $employee->user && // Check if a user is linked
                (Auth::user()?->can('viewAny', \App\Models\EmailApplication::class) || // Safely check Auth user and permission
                    Auth::user()?->can('viewAny', \App\Models\LoanApplication::class)))
            {{-- Outer container for related applications section --}}
            <div class="bg-white shadow-md rounded-lg p-6 mb-8">
                <h2 class="text-xl font-semibold text-gray-700 mb-4">{{ __('Related Applications (by Linked User)') }}</h2>

                {{-- Email Applications List --}}
                {{-- Check permission to view email applications list --}}
                @can('viewAny', \App\Models\EmailApplication::class)
                    <div class="mb-6"> {{-- Added margin-bottom --}}
                        <h3 class="text-lg font-medium text-gray-700 mb-3">{{ __('Email Applications') }}</h3>
                        {{-- Adjusted heading level and color --}}
                        {{-- Check if the user has any email applications --}}
                        @if ($employee->user->emailApplications && $employee->user->emailApplications->count() > 0)
                            {{-- Safely check if relationship exists and is not empty --}}
                            {{-- TODO: Consider displaying this in a table for better structure with multiple applications --}}
                            <ul class="list-disc pl-5 space-y-1"> {{-- Added space between list items --}}
                                @foreach ($employee->user->emailApplications as $application)
                                    {{-- Assuming 'my-applications.email.show' route exists and takes the application model --}}
                                    <li>
                                        {{-- Standardized link styling --}}
                                        <a href="{{ route('my-applications.email.show', $application) }}"
                                            class="text-blue-600 hover:text-blue-800 underline">
                                            {{ __('Application ID') }}: {{ $application->id }} -
                                            {{ $application->created_at?->format('Y-m-d') ?? 'N/A' }} ({{ __('Status') }}:
                                            {{ $application->status ?? 'N/A' }}) {{-- Format date, translate Status, provide fallbacks --}}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            {{-- Message if no email applications found --}}
                            <p class="text-gray-600">{{ __('No email applications submitted by this user.') }}</p>
                            {{-- Translate message --}}
                        @endif
                    </div>
                @endcan

                {{-- Loan Applications List --}}
                {{-- Check permission to view loan applications list --}}
                @can('viewAny', \App\Models\LoanApplication::class)
                    <div class="mb-6"> {{-- Added margin-bottom --}}
                        <h3 class="text-lg font-medium text-gray-700 mb-3">{{ __('Loan Applications') }}</h3>
                        {{-- Adjusted heading level and color --}}
                        {{-- Check if the user has any loan applications --}}
                        @if ($employee->user->loanApplications && $employee->user->loanApplications->count() > 0)
                            {{-- Safely check if relationship exists and is not empty --}}
                            {{-- TODO: Consider displaying this in a table for better structure with multiple applications --}}
                            <ul class="list-disc pl-5 space-y-1"> {{-- Added space between list items --}}
                                @foreach ($employee->user->loanApplications as $application)
                                    {{-- Assuming 'my-applications.loan.show' route exists and takes the application model --}}
                                    <li>
                                        {{-- Standardized link styling --}}
                                        <a href="{{ route('my-applications.loan.show', $application) }}"
                                            class="text-blue-600 hover:text-blue-800 underline">
                                            {{ __('Application ID') }}: {{ $application->id }} -
                                            {{ $application->created_at?->format('Y-m-d') ?? 'N/A' }} ({{ __('Status') }}:
                                            {{ $application->status ?? 'N/A' }}) {{-- Format date, translate Status, provide fallbacks --}}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            {{-- Message if no loan applications found --}}
                            <p class="text-gray-600">{{ __('No loan applications submitted by this user.') }}</p>
                            {{-- Translate message --}}
                        @endif
                    </div>
                @endcan

                {{-- Add other related Resource Management data sections here as needed (e.g., Issued Loans, Approvals Made by this user) --}}
                {{-- TODO: Add sections for other User-related data here --}}

            </div> {{-- End Related Applications Card --}}
        @endif


        {{-- Related Data Sections (Leaves, Fingerprints, Timelines etc.) --}}

        {{-- Leaves Section --}}
        {{-- Ensure 'leaves' relationship is eager loaded, and nested 'leaveType' if used --}}
        {{-- Check if the employee has any leaves --}}
        @if ($employee->leaves && $employee->leaves->count() > 0) {{-- Safely check if relationship exists and is not empty --}}
            <div class="bg-white shadow-md rounded-lg p-6 mb-8">
                <h2 class="text-xl font-semibold text-gray-700 mb-4">{{ __('Related Leaves') }}</h2>
                {{-- TODO: Consider displaying this in a table for better structure or using a Livewire component --}}
                {{-- You would typically list leaves in a table here --}}
                <ul class="list-disc pl-5 space-y-1"> {{-- Added space between list items --}}
                    @foreach ($employee->leaves as $leave)
                        <li>
                            {{-- Display leave type name (requires leaveType eager loading), dates, and status --}}
                            {{ __('Leave Type') }}: {{ $leave->leaveType->name ?? 'N/A' }} - {{-- Provide fallback --}}
                            {{ __('Dates') }}: {{ $leave->from_date?->format('Y-m-d') ?? 'N/A' }} to
                            {{-- Use null-safe operator and formatting, provide fallback --}}
                            {{ $leave->to_date?->format('Y-m-d') ?? 'N/A' }}
                            ({{ __('Status') }}: {{ $leave->status ?? 'N/A' }})
                            {{-- Added status and translation, provide fallback --}}
                            {{-- TODO: Add a link to view full leave details if applicable --}}
                            {{-- Example: Assuming a route 'resource-management.admin.leaves.show' or similar that takes the leave model --}}
                            {{-- <a href="{{ route('resource-management.admin.leaves.show', $leave) }}" class="text-blue-600 hover:underline ml-2">{{ __('View Details') }}</a> --}}
                        </li>
                    @endforeach
                </ul>
                {{-- Link to show all leaves for this employee if applicable --}}
                {{-- Example: Assuming a route resource-management.admin.leaves.employee_index or similar that takes the employee model --}}
                {{-- @if (Route::has('resource-management.admin.leaves.employee_index'))
                    <div class="mt-4">
                        <a href="{{ route('resource-management.admin.leaves.employee_index', $employee) }}" class="text-blue-600 hover:text-blue-800 underline">{{ __('View All Leaves for this Employee') }}</a> // Standardized link styling
                    </div>
                @endif --}}
            </div> {{-- End Leaves Section --}}
        @endif

        {{-- Fingerprints Section --}}
        {{-- Ensure 'fingerprints' relationship is eager loaded, and nested 'device' if used --}}
        {{-- Check if the employee has any fingerprints --}}
        @if ($employee->fingerprints && $employee->fingerprints->count() > 0) {{-- Safely check if relationship exists and is not empty --}}
            <div class="bg-white shadow-md rounded-lg p-6 mb-8">
                <h2 class="text-xl font-semibold text-gray-700 mb-4">{{ __('Fingerprints') }}</h2>
                {{-- TODO: Consider displaying this in a table for better structure or using a Livewire component --}}
                <ul class="list-disc pl-5 space-y-1"> {{-- Added space between list items --}}
                    @foreach ($employee->fingerprints as $fingerprint)
                        <li>
                            {{ __('Fingerprint ID') }}: {{ $fingerprint->fingerprint_id ?? 'N/A' }} -
                            {{-- Provide fallback --}}
                            {{ __('Device') }}: {{ $fingerprint->device->name ?? 'N/A' }} {{-- Access device name (requires device eager loading), provide fallback --}}
                            {{-- TODO: Add more fingerprint details or a link to view full details --}}
                        </li>
                    @endforeach
                </ul>
                {{-- TODO: Add a link to show all fingerprints for this employee if applicable --}}
                {{-- @if (Route::has('resource-management.admin.fingerprints.employee_index'))
                    <div class="mt-4">
                         <a href="{{ route('resource-management.admin.fingerprints.employee_index', $employee) }}" class="text-blue-600 hover:text-blue-800 underline">{{ __('View All Fingerprints for this Employee') }}</a> // Standardized link styling
                    </div>
                @endif --}}
            </div> {{-- End Fingerprints Section --}}
        @endif

        {{-- TODO: Add other related data sections as needed (Timelines, Assets, Trainings, etc.) --}}
        {{-- Example: Timelines Section --}}
        {{-- Assumes 'timelines' relationship exists and is eager loaded with nested relationships (center, department, position) --}}
        {{-- @if ($employee->timelines && $employee->timelines->count() > 0)
             <div class="bg-white shadow-md rounded-lg p-6 mb-8">
                  <h2 class="text-xl font-semibold text-gray-700 mb-4">{{ __('Employment History / Timelines') }}</h2>
                  // TODO: Display timeline entries (e.g., in a table or chronologically) or use a Livewire component
                  <ul class="list-disc pl-5 space-y-1">
                       @foreach ($employee->timelines->sortByDesc('start_date') as $timeline) // Sort by start date for chronological order
                            <li>
                                 {{ __('Period') }}: {{ $timeline->start_date?->format('Y-m-d') ?? 'N/A' }} - {{ $timeline->end_date?->format('Y-m-d') ?? 'Present') }} // Format dates, provide fallbacks ('Present' for null end_date)
                                 <br>
                                 ({{ __('Center') }}: {{ $timeline->center->name ?? 'N/A' }}, {{ __('Department') }}: {{ $timeline->department->name ?? 'N/A' }}, {{ __('Position') }}: {{ $timeline->position->name ?? 'N/A' }}) // Access nested relationship names, provide fallbacks
                            </li>
                       @endforeach
                  </ul>
                  // TODO: Add a link to show all timelines for this employee if applicable
                   // @if (Route::has('resource-management.admin.timelines.employee_index'))
                   //      <div class="mt-4">
                   //           <a href="{{ route('resource-management.admin.timelines.employee_index', $employee) }}" class="text-blue-600 hover:text-blue-800 underline">{{ __('View Full Employment History') }}</a> // Standardized link styling
                   //      </div>
                   // @endif
             </div>
           @endif
        --}}


        {{-- Actions Section --}}
        {{-- Check if the current user has permission to update or delete this employee or create users --}}
        @if (Auth::user()?->can('update employees', $employee) || // Safely check Auth user and permission
                Auth::user()?->can('delete employees', $employee) || // Safely check Auth user and permission
                Auth::user()?->can('create users')) {{-- Safely check Auth user and permission --}}
            {{-- Outer container for actions section --}}
            <div class="bg-white shadow-md rounded-lg p-6 mb-8"> {{-- Added mb-8 for spacing --}}
                <h2 class="text-xl font-semibold text-gray-700 mb-4">{{ __('Actions') }}</h2>
                {{-- Use flexbox with wrapping and gap for button layout --}}
                <div class="flex flex-wrap gap-4"> {{-- Added flex-wrap and gap for layout --}}
                    {{-- Edit Button --}}
                    {{-- Check permission to update this specific employee --}}
                    @can('update employees', $employee)
                        {{-- Assuming 'resource-management.admin.employees.edit' route exists and takes the employee model --}}
                        {{-- Standardized button styling --}}
                        <a href="{{ route('resource-management.admin.employees.edit', $employee) }}"
                            class="inline-flex items-center justify-center px-4 py-2 bg-yellow-500 hover:bg-yellow-600 text-white font-bold rounded shadow-sm focus:outline-none focus:ring focus:ring-yellow-400 transition">
                            {{ __('Edit Employee') }} {{-- Translate button text --}}
                        </a>
                    @endcan

                    {{-- Delete Button --}}
                    {{-- Check permission to delete this specific employee --}}
                    @can('delete employees', $employee)
                        {{-- Implement a confirmation dialog for deletion (using browser confirm for simplicity here). --}}
                        {{-- TODO: Consider using a modal or Alpine.js for delete confirmation for better UX, consistent with the index view. --}}
                        {{-- Assuming 'resource-management.admin.employees.destroy' route exists and takes the employee model --}}
                        <form action="{{ route('resource-management.admin.employees.destroy', $employee) }}" method="POST"
                            onsubmit="return confirm('{{ __('Are you sure you want to delete this employee?') }}');"
                            {{-- Simple JS confirmation, translate message --}} class="inline-block"> {{-- Use inline-block to keep it in line with other buttons if needed --}}
                            @csrf {{-- CSRF token --}}
                            @method('DELETE') {{-- Method spoofing for DELETE request --}}
                            {{-- Standardized button styling --}}
                            <button type="submit"
                                class="inline-flex items-center justify-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-bold rounded shadow-sm focus:outline-none focus:ring focus:ring-red-400 transition">
                                {{ __('Delete Employee') }} {{-- Translate button text --}}
                            </button>
                        </form>
                    @endcan

                    {{-- Add other actions here (e.g., link/button to create a linked User account if one doesn't exist) --}}
                    {{-- Check if no user is linked AND the current user has permission to create users --}}
                    @if (!$employee->user && Auth::user()?->can('create users'))
                        {{-- Safely check Auth user and permission --}}
                        {{-- Assuming a route named 'resource-management.admin.users.create' exists and can accept employee_id --}}
                        {{-- Standardized button styling --}}
                        <a href="{{ route('resource-management.admin.users.create', ['employee_id' => $employee->id]) }}"
                            class="inline-flex items-center justify-center px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white font-bold rounded shadow-sm focus:outline-none focus:ring focus:ring-purple-400 transition">
                            {{ __('Link New User Account') }} {{-- Translate button text --}}
                        </a>
                    @endif
                    {{-- TODO: Add a link/button to unlink a user account if one is linked, potentially with a confirmation. --}}
                    {{-- Example: @if ($employee->user && Auth::user()?->can('delete users')) ... (using a form/modal) ... @endif --}}
                </div> {{-- End Flex Container for Actions --}}
            </div> {{-- End Actions Section --}}
        @endif


    </div> {{-- End main container --}}
@endsection

{{-- You might have other scripts or footer content in your layout --}}
