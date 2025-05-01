<!DOCTYPE html>
{{-- HTML document with language attribute set based on application locale --}}
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    {{-- Character set declaration --}}
    <meta charset="utf-8">
    {{-- Viewport settings for responsive design --}}
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    {{-- Page title, translated, includes the user's name or full name with fallbacks --}}
    @section('title', __('User Details') . ': ' . ($user->name ?? ($user->full_name ?? 'N/A'))) {{-- Set the page title using translation --}}
    <title>@yield('title', __('User Details'))</title> {{-- Default title if not set in @section('title') --}}

    {{-- Link Tailwind CSS via CDN for styling.
         This replaces the custom CSS classes with standard Tailwind utility classes. --}}
    <script src="https://cdn.tailwindcss.com"></script>

    {{-- Ensure your project is configured to use Tailwind CSS, either via CDN (as shown here)
         or preferably through a build process (like Webpack or Vite) for better performance and customization. --}}

    {{-- Add any necessary custom CSS here if Tailwind alone is insufficient for specific styling requirements --}}
</head>

{{-- Body element with Tailwind background color and padding applied --}}

<body class="bg-gray-100 p-6">

    {{-- Extend your main admin layout. Adjust 'layouts.app' if your admin layout is different. --}}
    @extends('layouts.app')

    {{-- Define the title section (defined in <head> above) --}}


    {{-- Define the content section where the details will be placed within the layout --}}
    @section('content')
        {{-- Main container for the content. Sets max-width, centers horizontally, and adds padding, using Tailwind classes. --}}
        <div class="max-w-full mx-auto sm:px-6 lg:px-8 py-8"> {{-- Use max-w-full for wider container --}}

            {{-- Header section with title and Back to Users List button --}}
            <div class="flex items-center justify-between mb-6">
                {{-- Page Title --}}
                <h1 class="text-2xl font-bold text-gray-800">
                    {{ __('User Details') }}: {{ $user->name ?? ($user->full_name ?? 'N/A') }} {{-- Translated title with user name/full name fallback --}}
                </h1>

                {{-- Back Button to the users list --}}
                {{-- Assuming a route named 'resource-management.admin.users.index' exists for the user list --}}
                {{-- Replaced previous button classes with the standardized gray secondary button styling --}}
                <a href="{{ route('resource-management.admin.users.index') }}"
                    class="inline-flex items-center justify-center px-5 py-2.5 bg-gray-200 text-gray-800 font-semibold rounded-md border border-gray-200 hover:bg-gray-300 hover:border-gray-300 focus:outline-none focus:ring focus:ring-gray-500 focus:ring-opacity-50 transition">
                    {{-- Adjusted focus ring color to gray --}}
                    {{-- SVG icon (example: left arrow) --}}
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    {{ __('Back to Users List') }} {{-- Translated link text --}}
                </a>
            </div> {{-- End header section --}}

            {{-- Display success or error messages from session (using flash messages) --}}
            @if (session()->has('success'))
                {{-- Applied Tailwind classes for a success alert box --}}
                <div class="bg-green-100 border border-green-200 text-green-800 p-4 rounded-md mb-4">
                    {{ session('success') }} {{-- Display the success message --}}
                </div>
            @endif {{-- End success message display --}}

            @if (session()->has('error'))
                {{-- Applied Tailwind classes for an error alert box --}}
                <div class="bg-red-100 border border-red-200 text-red-800 p-4 rounded-md mb-4">
                    {{ session('error') }} {{-- Display the error message --}}
                </div>
            @endif {{-- End error message display --}}


            {{-- User Details Card --}}
            {{-- Container for the user details block, using Tailwind classes for background, shadow, rounded corners, padding, and bottom margin --}}
            <div class="bg-white shadow-md rounded-lg p-6 mb-8">
                {{-- Flex container for profile photo and user information, responsive layout (column on small screens, row on medium and larger) --}}
                <div class="flex flex-col md:flex-row items-center md:items-start">
                    {{-- Profile Photo container --}}
                    <div class="md:mr-8 mb-6 md:mb-0"> {{-- Added right margin on medium+ screens and bottom margin on small screens --}}
                        {{-- Profile photo image, uses profile_photo_url from user or a default image asset --}}
                        {{-- Applied Tailwind classes for size, rounded shape, object fit, and border --}}
                        <img src="{{ $user->profile_photo_url ?? asset('path/to/default/profile.png') }}"
                            alt="{{ $user->name ?? 'User' }} Profile Photo"
                            class="w-32 h-32 rounded-full object-cover border-4 border-blue-200">
                    </div> {{-- End profile photo container --}}

                    {{-- User Information block --}}
                    <div class="flex-grow"> {{-- Allows this block to grow and take available space --}}
                        {{-- Section heading for basic information --}}
                        <h2 class="text-xl font-semibold text-gray-700 mb-4">{{ __('Basic Information') }}</h2>
                        {{-- Applied Tailwind heading classes --}}
                        {{-- Grid layout for displaying individual user details --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            {{-- Standard User Fields --}}
                            <div>
                                {{-- Label for detail item --}}
                                <p class="text-sm font-medium text-gray-500">{{ __('Name') }}:</p>
                                {{-- Value of detail item --}}
                                <p class="mt-1 text-gray-900">{{ $user->name ?? 'N/A' }}</p> {{-- Display user name with fallback --}}
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">{{ __('Full Name') }}:</p>
                                <p class="mt-1 text-gray-900">{{ $user->full_name ?? 'N/A' }}</p> {{-- Display user full name with fallback --}}
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">{{ __('Email') }}:</p>
                                <p class="mt-1 text-gray-900">{{ $user->email ?? 'N/A' }}</p> {{-- Display user email with fallback --}}
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">{{ __('Personal Email') }}:</p>
                                <p class="mt-1 text-gray-900">{{ $user->personal_email ?? 'N/A' }}</p>
                                {{-- Display user personal email with fallback --}}
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">{{ __('MOTAC Email') }}:</p>
                                <p class="mt-1 text-gray-900">{{ $user->motac_email ?? 'N/A' }}</p> {{-- Display user MOTAC email with fallback --}}
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">{{ __('Employee ID') }}:</p>
                                <p class="mt-1 text-gray-900">{{ $user->employee_id ?? 'N/A' }}</p> {{-- Display employee ID with fallback --}}
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">{{ __('User ID Assigned') }}:</p>
                                <p class="mt-1 text-gray-900">{{ $user->user_id_assigned ?? 'N/A' }}</p>
                                {{-- Display assigned user ID with fallback --}}
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">{{ __('Phone Number') }}:</p>
                                <p class="mt-1 text-gray-900">{{ $user->phone_number ?? ($user->mobile ?? 'N/A') }}</p>
                                {{-- Display phone number with fallback to mobile --}}
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">{{ __('Status') }}:</p>
                                <p class="mt-1 text-gray-900">{{ __($user->status ?? 'N/A') }}</p> {{-- Display status with translation and fallback --}}
                            </div>

                            {{-- New MOTAC Specific Fields (Protected by policy) --}}
                            {{-- Use the 'viewSensitiveData' policy check to protect the Identification Number (NRIC) --}}
                            @can('viewSensitiveData', $user)
                                {{-- Assuming a 'viewSensitiveData' policy exists on the User model --}}
                                <div>
                                    <p class="text-sm font-medium text-gray-500">{{ __('Identification Number (NRIC)') }}:</p>
                                    <p class="mt-1 text-gray-900">{{ $user->identification_number ?? 'N/A' }}</p>
                                    {{-- Display NRIC with fallback --}}
                                </div>
                            @endcan {{-- End can viewSensitiveData check --}}

                            {{-- Relationships --}}
                            <div>
                                <p class="text-sm font-medium text-gray-500">{{ __('Department') }}:</p>
                                {{-- Accessing department name via relationship. Requires eager loading 'department' in controller. --}}
                                <p class="mt-1 text-gray-900">{{ $user->department->name ?? 'N/A' }}</p>
                                {{-- Display department name with fallback --}}
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">{{ __('Position') }}:</p>
                                {{-- Assuming 'position' relationship exists and links to the Designation model. Requires eager loading 'position'. --}}
                                <p class="mt-1 text-gray-900">{{ $user->position->name ?? 'N/A' }}</p>
                                {{-- Display position name with fallback --}}
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">{{ __('Grade') }}:</p>
                                {{-- Assuming 'grade' relationship exists. Requires eager loading 'grade'. --}}
                                <p class="mt-1 text-gray-900">{{ $user->grade->name ?? 'N/A' }}</p> {{-- Display grade name with fallback --}}
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">{{ __('Service Status') }}:</p>
                                <p class="mt-1 text-gray-900">{{ __($user->service_status ?? 'N/A') }}</p>
                                {{-- Display service status with translation and fallback --}}
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">{{ __('Appointment Type') }}:</p>
                                <p class="mt-1 text-gray-900">{{ __($user->appointment_type ?? 'N/A') }}</p>
                                {{-- Display appointment type with translation and fallback --}}
                            </div>

                            {{-- Audit Fields (Optionally Protected by policy) --}}
                            {{-- Assuming 'viewAuditData' policy or similar exists --}}
                            {{-- @can('viewAuditData', $user) --}}
                            <div>
                                <p class="text-sm font-medium text-gray-500">{{ __('Created By') }}:</p>
                                {{-- Assuming 'createdBy' relationship exists on User model. Requires eager loading 'createdBy'. --}}
                                <p class="mt-1 text-gray-900">{{ $user->createdBy->name ?? __('System') }}</p>
                                {{-- Display creator name with fallback to 'System' --}}
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">{{ __('Updated By') }}:</p>
                                {{-- Assuming 'updatedBy' relationship exists on User model. Requires eager loading 'updatedBy'. --}}
                                <p class="mt-1 text-gray-900">{{ $user->updatedBy->name ?? 'N/A' }}</p>
                                {{-- Display updater name with fallback --}}
                            </div>
                            {{-- Display Deleted By only if the user is soft-deleted --}}
                            @if ($user->deleted_by)
                                <div>
                                    <p class="text-sm font-medium text-gray-500">{{ __('Deleted By') }}:</p>
                                    {{-- Assuming 'deletedBy' relationship exists on User model. Requires eager loading 'deletedBy'. --}}
                                    <p class="mt-1 text-gray-900">{{ $user->deletedBy->name ?? 'N/A' }}</p>
                                    {{-- Display deleter name with fallback --}}
                                </div>
                            @endif
                            <div>
                                <p class="text-sm font-medium text-gray-500">{{ __('Created At') }}:</p>
                                <p class="mt-1 text-gray-900">
                                    {{ $user->created_at ? $user->created_at->format('Y-m-d H:i:s') : 'N/A' }}
                                    {{-- Display creation timestamp with formatting and fallback --}}
                                </p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">{{ __('Updated At') }}:</p>
                                <p class="mt-1 text-gray-900">
                                    {{ $user->updated_at ? $user->updated_at->format('Y-m-d H:i:s') : 'N/A' }}
                                    {{-- Display update timestamp with formatting and fallback --}}
                                </p>
                            </div>
                            {{-- Display Deleted At only if the user is soft-deleted --}}
                            @if ($user->deleted_at)
                                <div>
                                    <p class="text-sm font-medium text-gray-500">{{ __('Deleted At') }}:</p>
                                    <p class="mt-1 text-gray-900">
                                        {{ $user->deleted_at ? $user->deleted_at->format('Y-m-d H:i:s') : 'N/A' }}
                                        {{-- Display deletion timestamp with formatting and fallback --}}
                                    </p>
                                </div>
                            @endif
                            {{-- @endcan --}} {{-- End can viewAuditData check --}}
                        </div> {{-- End grid --}}
                    </div> {{-- End user information block --}}
                </div> {{-- End flex container for photo and info --}}
            </div> {{-- End user details card --}}

            {{-- Related Applications Section (Optional) --}}
            {{-- Check if the authenticated user has permission to view any type of application listed --}}
            @if (Auth::user() &&
                    (Auth::user()->can('viewAny', \App\Models\EmailApplication::class) ||
                        Auth::user()->can('viewAny', \App\Models\LoanApplication::class))) {{-- Added check for authenticated user --}}
                {{-- Container for related applications block --}}
                <div class="bg-white shadow-md rounded-lg p-6 mb-8">
                    {{-- Section heading --}}
                    <h2 class="text-xl font-semibold text-gray-700 mb-4">{{ __('Related Applications') }}</h2>
                    {{-- Applied Tailwind heading classes --}}

                    {{-- Email Applications Sub-section --}}
                    {{-- Check if user has permission to view Email Applications --}}
                    @can('viewAny', \App\Models\EmailApplication::class)
                        <h3 class="text-lg font-medium text-gray-600 mb-3">{{ __('Email Applications') }}</h3>
                        {{-- Applied Tailwind heading classes --}}
                        {{-- Check if the user has any email applications --}}
                        @if ($user->emailApplications->count() > 0)
                            {{-- List of email applications --}}
                            <ul class="list-disc pl-5 mb-4"> {{-- Styled list with bullets and padding --}}
                                @foreach ($user->emailApplications as $application)
                                    {{-- Assuming 'my-applications.email.show' route exists for viewing individual email applications --}}
                                    <li>
                                        {{-- Link to individual email application details --}}
                                        <a href="{{ route('my-applications.email.show', $application) }}"
                                            class="text-blue-600 hover:underline"> {{-- Styled link --}}
                                            {{ __('Application ID') }}: {{ $application->id }} - {{-- Display application ID, translated label --}}
                                            {{ $application->created_at->format('Y-m-d') }} ({{ __('Status') }}:
                                            {{ __($application->status) }}) {{-- Display creation date, translated Status label, and translated status value --}}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            {{-- Message if no email applications found --}}
                            <p class="text-gray-600 mb-4">{{ __('No email applications submitted by this user.') }}</p>
                            {{-- Styled paragraph --}}
                        @endif {{-- End check for email applications count --}}
                    @endcan {{-- End can viewAny EmailApplication check --}}

                    {{-- Loan Applications Sub-section --}}
                    {{-- Check if user has permission to view Loan Applications --}}
                    @can('viewAny', \App\Models\LoanApplication::class)
                        <h3 class="text-lg font-medium text-gray-600 mb-3">{{ __('Loan Applications') }}</h3>
                        {{-- Applied Tailwind heading classes --}}
                        {{-- Check if the user has any loan applications --}}
                        @if ($user->loanApplications->count() > 0)
                            {{-- List of loan applications --}}
                            <ul class="list-disc pl-5 mb-4"> {{-- Styled list --}}
                                @foreach ($user->loanApplications as $application)
                                    {{-- Assuming 'my-applications.loan.show' route exists for viewing individual loan applications --}}
                                    <li>
                                        {{-- Link to individual loan application details --}}
                                        <a href="{{ route('my-applications.loan.show', $application) }}"
                                            class="text-blue-600 hover:underline"> {{-- Styled link --}}
                                            {{ __('Application ID') }}: {{ $application->id }} - {{-- Display application ID, translated label --}}
                                            {{ $application->created_at->format('Y-m-d') }} ({{ __('Status') }}:
                                            {{ __($application->status) }}) {{-- Display creation date, translated Status label, and translated status value --}}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            {{-- Message if no loan applications found --}}
                            <p class="text-gray-600 mb-4">{{ __('No loan applications submitted by this user.') }}</p>
                            {{-- Styled paragraph --}}
                        @endif {{-- End check for loan applications count --}}
                    @endcan {{-- End can viewAny LoanApplication check --}}

                    {{-- Add other related resource management data here (e.g., Issued Loans, Approvals Made) --}}

                </div> {{-- End related applications block --}}
            @endif {{-- End check for viewing related applications --}}

            {{-- Actions Section (Optional) --}}
            {{-- Check if the authenticated user has permission to update or delete this user --}}
            @if (Auth::user() && (Auth::user()->can('update', $user) || Auth::user()->can('delete', $user) || $user->trashed())) {{-- Added check for authenticated user and if the user is trashed for restore/force delete --}}
                {{-- Container for actions block --}}
                <div class="bg-white shadow-md rounded-lg p-6">
                    {{-- Section heading --}}
                    <h2 class="text-xl font-semibold text-gray-700 mb-4">{{ __('Actions') }}</h2> {{-- Applied Tailwind heading classes --}}
                    {{-- Flex container for action buttons with spacing --}}
                    <div class="flex space-x-4">
                        {{-- Edit Button --}}
                        {{-- Ensure user has permission to update the user and the user is not soft-deleted --}}
                        @can('update', $user)
                            @if (!$user->trashed())
                                {{-- Only show edit if not soft-deleted --}}
                                {{-- Assuming 'resource-management.admin.users.edit' route exists --}}
                                {{-- Replaced previous yellow button classes with the standardized blue primary button styling --}}
                                <a href="{{ route('resource-management.admin.users.edit', $user) }}"
                                    class="inline-flex items-center justify-center px-5 py-2.5 bg-blue-500 text-white font-semibold rounded-md border border-blue-500 hover:bg-blue-600 hover:border-blue-600 focus:outline-none focus:ring focus:ring-blue-500 focus:ring-opacity-50 transition">
                                    {{ __('Edit User') }} {{-- Translated button text --}}
                                </a>
                            @endif
                        @endcan {{-- End can update check --}}

                        {{-- Delete Button --}}
                        {{-- Ensure user has permission to delete the user and the user is not soft-deleted --}}
                        @can('delete', $user)
                            @if (!$user->trashed())
                                {{-- Only show delete if not soft-deleted --}}
                                {{-- Implement a confirmation dialog for deletion using a form submission --}}
                                <form action="{{ route('resource-management.admin.users.destroy', $user) }}" method="POST"
                                    onsubmit="return confirm('{{ __('Are you sure you want to delete this user?') }}');">
                                    {{-- JavaScript confirm dialog with translated message --}}
                                    @csrf {{-- CSRF token for security --}}
                                    @method('DELETE') {{-- Method spoofing for DELETE request --}}
                                    {{-- Delete button, applied Tailwind button classes --}}
                                    <button type="submit"
                                        class="inline-flex items-center justify-center px-5 py-2.5 bg-red-500 text-white font-semibold rounded-md border border-red-500 hover:bg-red-600 hover:border-red-600 focus:outline-none focus:ring focus:ring-red-500 focus:ring-opacity-50 transition">
                                        {{ __('Delete User') }} {{-- Translated button text --}}
                                    </button>
                                </form>
                            @endif
                        @endcan {{-- End can delete check --}}

                        {{-- Restore and Force Delete Buttons (Only if user is soft-deleted) --}}
                        @if ($user->trashed())
                            {{-- Check if the user is soft-deleted --}}
                            {{-- Restore Button --}}
                            @can('restore', $user)
                                {{-- Assuming a 'restore User' permission/policy exists --}}
                                <form action="{{ route('resource-management.admin.users.restore', $user) }}" method="POST"
                                    onsubmit="return confirm('{{ __('Are you sure you want to restore this user?') }}');">
                                    @csrf {{-- CSRF token for security --}}
                                    @method('PUT') {{-- Or @method('POST') if your route is POST for restore --}}
                                    {{-- Restore button, applied Tailwind button classes --}}
                                    <button type="submit"
                                        class="inline-flex items-center justify-center px-5 py-2.5 bg-green-500 text-white font-semibold rounded-md border border-green-500 hover:bg-green-600 hover:border-green-600 focus:outline-none focus:ring focus:ring-green-500 focus:ring-opacity-50 transition">
                                        {{ __('Restore User') }} {{-- Translated button text --}}
                                    </button>
                                </form>
                            @endcan {{-- End can restore check --}}
                            {{-- Force Delete Button --}}
                            @can('forceDelete', $user)
                                {{-- Assuming a 'forceDelete User' permission/policy exists --}}
                                <form action="{{ route('resource-management.admin.users.force-delete', $user) }}"
                                    method="POST"
                                    onsubmit="return confirm('{{ __('Are you sure you want to permanently delete this user? This action cannot be undone.') }}');">
                                    @csrf {{-- CSRF token for security --}}
                                    @method('DELETE') {{-- Or @method('POST') --}}
                                    {{-- Force Delete button, applied Tailwind button classes (darker red) --}}
                                    <button type="submit"
                                        class="inline-flex items-center justify-center px-5 py-2.5 bg-red-700 text-white font-semibold rounded-md border border-red-700 hover:bg-red-800 hover:border-red-800 focus:outline-none focus:ring focus:ring-red-700 focus:ring-opacity-50 transition">
                                        {{ __('Force Delete User') }} {{-- Translated button text --}}
                                    </button>
                                </form>
                            @endcan {{-- End can forceDelete check --}}
                        @endif {{-- End check if user is trashed --}}

                        {{-- Add other actions here (e.g., Link to Profile, Reset Password, etc.) --}}
                        {{-- Example Link to User's Profile --}}
                        @if (Route::has('profile.show'))
                            {{-- Check if Jetstream profile route exists --}}
                            <a href="{{ route('profile.show', $user) }}"
                                class="inline-flex items-center justify-center px-5 py-2.5 bg-indigo-500 text-white font-semibold rounded-md border border-indigo-500 hover:bg-indigo-600 hover:border-indigo-600 focus:outline-none focus:ring focus:ring-indigo-500 focus:ring-opacity-50 transition">
                                {{ __('View Profile') }}
                            </a>
                        @endif

                    </div> {{-- End flex container for actions --}}
                </div> {{-- End actions block --}}
            @endif {{-- End check for viewing/deleting/restoring actions --}}

        </div> {{-- End main container --}}
    @endsection {{-- End content section --}}

    {{-- You might have other scripts or footer content in your layout --}}

</body> {{-- End body --}}

</html> {{-- End HTML document --}}
