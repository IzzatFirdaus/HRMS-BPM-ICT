<!DOCTYPE html>
{{-- HTML document with language attribute set based on application locale --}}
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    {{-- Character set declaration --}}
    <meta charset="utf-8">
    {{-- Viewport settings for responsive design --}}
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    {{-- Page title, translated, includes the user's name or full name with fallbacks --}}
    {{-- Define title using @section('title', ...) which is common for layouts --}}
    <title>@yield('title', __('User Details'))</title> {{-- Default title if not set in @section('title') --}}

    {{-- Link Tailwind CSS via CDN for styling.
         This replaces the custom CSS classes with standard Tailwind utility classes. --}}
    <script src="https://cdn.tailwindcss.com"></script>

    {{-- Ensure your project is configured to use Tailwind CSS, either via CDN (as shown here)
         or preferably through a build process (like Webpack or Vite) for better performance and customization. --}}

    {{-- Add any necessary custom CSS here if Tailwind alone is insufficient for specific styling requirements --}}
    {{-- For example, the 'badge' classes might need custom definition or Tailwind config --}}
    <style>
        .badge {
            display: inline-flex;
            align-items: center;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            /* rounded-full */
            font-size: 0.75rem;
            /* text-xs */
            font-weight: 600;
            /* font-semibold */
            line-height: 1;
        }

        .badge-info {
            background-color: #bfdbfe;
            /* blue-200 */
            color: #1e40af;
            /* blue-800 */
        }

        .badge-success {
            background-color: #d1fae5;
            /* green-100 */
            color: #065f46;
            /* green-800 */
        }

        .badge-warning {
            background-color: #fef3c7;
            /* yellow-100 */
            color: #b45309;
            /* yellow-800 */
        }

        .badge-danger {
            background-color: #fee2e2;
            /* red-100 */
            color: #991b1b;
            /* red-800 */
        }

        .badge-secondary {
            background-color: #e5e7eb;
            /* gray-200 */
            color: #374151;
            /* gray-700 */
        }

        /* Add other custom styles as needed */
    </style>
</head>

<body class="bg-gray-100"> {{-- Apply background color to the body --}}

    {{-- Extend your main application layout. This layout should provide the basic HTML structure,
         asset includes (like main CSS and JS), and yielding sections like 'content'. --}}
    @extends('layouts.app')

    {{-- Define the content section where the details will be placed within the layout --}}
    @section('content')

        {{-- Main container for the content. Sets max-width, centers horizontally, and adds padding, using Tailwind classes. --}}
        <div class="max-w-full mx-auto sm:px-6 lg:px-8 py-8"> {{-- Use max-w-full for wider container --}}

            {{-- Card-like container for the details --}}
            <div class="bg-white overflow-hidden shadow-lg sm:rounded-lg p-8">

                {{-- Header section with title and Back to Users List button --}}
                <div class="flex items-center justify-between mb-6">
                    {{-- Page Title --}}
                    <h1 class="text-2xl font-bold text-gray-800">
                        {{ __('User Details') }}: {{ $user->name ?? ($user->full_name ?? 'N/A') }} {{-- Translated title with user name/full name fallback --}}
                    </h1>

                    {{-- Back Button to the users list --}}
                    {{-- CORRECTED ROUTE NAME: Use the correct registered name 'admin.users.index' --}}
                    <a href="{{ route('admin.users.index') }}"
                        class="inline-flex items-center justify-center px-5 py-2.5 bg-gray-200 text-gray-800 font-semibold rounded-md border border-gray-200 hover:bg-gray-300 hover:border-gray-300 focus:outline-none focus:ring focus:ring-gray-500 focus:ring-opacity-50"> {{-- Applied standard Tailwind button styles --}}
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
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4"
                        role="alert">
                        {{ session('success') }}
                    </div>
                @endif

                @if (session()->has('error'))
                    {{-- Applied Tailwind classes for a danger alert box --}}
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4"
                        role="alert">
                        {{ session('error') }}
                    </div>
                @endif


                {{-- User Details Section --}}
                {{-- Using grid layout for key-value pairs --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4 text-gray-700">

                    {{-- Full Name --}}
                    <div>
                        <p class="text-sm font-medium text-gray-500">{{ __('Full Name') }}</p>
                        <p class="mt-1 text-sm text-gray-900">{{ $user->full_name ?? 'N/A' }}</p>
                    </div>

                    {{-- User ID Assigned (MOTAC) --}}
                    <div>
                        <p class="text-sm font-medium text-gray-500">{{ __('User ID Assigned (MOTAC)') }}</p>
                        <p class="mt-1 text-sm text-gray-900">{{ $user->user_id_assigned ?? 'N/A' }}</p>
                    </div>

                    {{-- NRIC --}}
                    <div>
                        <p class="text-sm font-medium text-gray-500">{{ __('NRIC') }}</p>
                        <p class="mt-1 text-sm text-gray-900">{{ $user->nric ?? 'N/A' }}</p>
                    </div>

                    {{-- Mobile Number --}}
                    <div>
                        <p class="text-sm font-medium text-gray-500">{{ __('Mobile Number') }}</p>
                        <p class="mt-1 text-sm text-gray-900">{{ $user->mobile_number ?? 'N/A' }}</p>
                    </div>

                    {{-- Primary Email (Login) --}}
                    <div>
                        <p class="text-sm font-medium text-gray-500">{{ __('Primary Email (Login)') }}</p>
                        <p class="mt-1 text-sm text-gray-900">{{ $user->email ?? 'N/A' }}</p>
                    </div>

                     {{-- Personal Email --}}
                    <div>
                        <p class="text-sm font-medium text-gray-500">{{ __('Personal Email') }}</p>
                        <p class="mt-1 text-sm text-gray-900">{{ $user->personal_email ?? 'N/A' }}</p>
                    </div>

                    {{-- MOTAC Official Email --}}
                    <div>
                        <p class="text-sm font-medium text-gray-500">{{ __('MOTAC Official Email') }}</p>
                        <p class="mt-1 text-sm text-gray-900">{{ $user->motac_email ?? 'N/A' }}</p>
                    </div>

                    {{-- Department/Unit --}}
                    <div>
                        <p class="text-sm font-medium text-gray-500">{{ __('Department/Unit') }}</p>
                         {{-- Accessing relationship property with optional() for safety --}}
                        <p class="mt-1 text-sm text-gray-900">{{ optional($user->department)->name ?? 'N/A' }}</p>
                    </div>

                    {{-- Position --}}
                    <div>
                        <p class="text-sm font-medium text-gray-500">{{ __('Position') }}</p>
                         {{-- Accessing relationship property with optional() for safety --}}
                        <p class="mt-1 text-sm text-gray-900">{{ optional($user->position)->name ?? 'N/A' }}</p>
                    </div>

                    {{-- Grade --}}
                    <div>
                        <p class="text-sm font-medium text-gray-500">{{ __('Grade') }}</p>
                         {{-- Accessing relationship property with optional() for safety --}}
                        <p class="mt-1 text-sm text-gray-900">{{ optional($user->grade)->name ?? 'N/A' }}</p>
                    </div>

                    {{-- Service Status --}}
                    <div>
                        <p class="text-sm font-medium text-gray-500">{{ __('Service Status') }}</p>
                        <p class="mt-1 text-sm text-gray-900">{{ ucfirst(str_replace('_', ' ', $user->service_status ?? 'N/A')) }}</p> {{-- Format for display --}}
                    </div>

                    {{-- User Status --}}
                    <div>
                        <p class="text-sm font-medium text-gray-500">{{ __('User Status') }}</p>
                        {{-- Display status with a colored badge --}}
                        <p class="mt-1 text-sm text-gray-900">
                             <span class="badge {{ match ($user->status ?? '') {
                                 'active' => 'badge-success',
                                 'inactive' => 'badge-secondary',
                                 'suspended' => 'badge-danger',
                                 default => 'badge-secondary',
                             } }}">
                                 {{ ucfirst(str_replace('_', ' ', $user->status ?? 'N/A')) }}
                             </span>
                        </p>
                    </div>

                     {{-- Admin Role --}}
                    <div class="md:col-span-2"> {{-- Span across two columns --}}
                        <p class="text-sm font-medium text-gray-500">{{ __('Is Admin?') }}</p>
                        <p class="mt-1 text-sm text-gray-900">{{ ($user->is_admin ?? false) ? __('Yes') : __('No') }}</p>
                    </div>

                     {{-- BPM Staff Role --}}
                    <div class="md:col-span-2"> {{-- Span across two columns --}}
                         <p class="text-sm font-medium text-gray-500">{{ __('Is BPM Staff?') }}</p>
                        <p class="mt-1 text-sm text-gray-900">{{ ($user->is_bpm_staff ?? false) ? __('Yes') : __('No') }}</p>
                    </div>

                    {{-- Created At --}}
                    <div>
                        <p class="text-sm font-medium text-gray-500">{{ __('Created At') }}</p>
                         {{-- Use optional() for Carbon instance formatting --}}
                        <p class="mt-1 text-sm text-gray-900">{{ optional($user->created_at)->format('d M Y H:i') ?? 'N/A' }}</p>
                    </div>

                    {{-- Updated At --}}
                    <div>
                         <p class="text-sm font-medium text-gray-500">{{ __('Updated At') }}</p>
                         {{-- Use optional() for Carbon instance formatting --}}
                         <p class="mt-1 text-sm text-gray-900">{{ optional($user->updated_at)->format('d M Y H:i') ?? 'N/A' }}</p>
                    </div>

                    {{-- Created By (if CreatedUpdatedDeletedBy trait is used and relationship exists) --}}
                    @if (optional($user->createdBy)->name) {{-- Check if createdBy relationship exists and has a name --}}
                         <div>
                             <p class="text-sm font-medium text-gray-500">{{ __('Created By') }}</p>
                              {{-- Accessing relationship property with optional() for safety --}}
                             <p class="mt-1 text-sm text-gray-900">{{ optional($user->createdBy)->name }}</p>
                         </div>
                    @endif

                     {{-- Updated By (if CreatedUpdatedDeletedBy trait is used and relationship exists) --}}
                     @if (optional($user->updatedBy)->name) {{-- Check if updatedBy relationship exists and has a name --}}
                         <div>
                             <p class="text-sm font-medium text-gray-500">{{ __('Updated By') }}</p>
                              {{-- Accessing relationship property with optional() for safety --}}
                             <p class="mt-1 text-sm text-gray-900">{{ optional($user->updatedBy)->name }}</p>
                         </div>
                     @endif


                    {{-- Add more fields as needed --}}

                </div> {{-- End grid --}}

                 {{-- Optional: Add buttons for actions if applicable and authorized (e.g., Edit, Delete) --}}
                 <div class="mt-8 border-t border-gray-200 pt-6 flex justify-start space-x-4">
                    {{-- Edit Button --}}
                    @can('update', $user) {{-- Check if the user is authorized to update this specific user --}}
                         <a href="{{ route('admin.users.edit', $user->id) }}"
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                 <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a12.006 12.006 0 011.617 1.618l-.383.383L14.768 7.82l-.383-.383a12.006 12.006 0 011.617 1.618zM16 10l-4 4-4 0 0 4 4 0 4-4 0-4z" />
                             </svg>
                             {{ __('Edit') }}
                         </a>
                    @endcan

                    {{-- Delete Button (using a form for DELETE request) --}}
                     @can('delete', $user) {{-- Check if the user is authorized to delete this specific user --}}
                         <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" onsubmit="return confirm('{{ __('Are you sure you want to delete this user?') }}');">
                             @csrf
                             @method('DELETE')
                             <button type="submit"
                                     class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                 <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                     <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                 </svg>
                                 {{ __('Delete') }}
                             </button>
                         </form>
                     @endcan
                 </div>


            </div> {{-- End main container padding --}}
        </div> {{-- End main container --}}

    @endsection {{-- End content section --}}

    {{-- Note: The closing </body> and </html> tags might be in your layouts.app blade file --}}
    {{-- If they are not, uncomment the original closing tags below --}}
    {{-- </body> --}}
    {{-- </html> --}}
