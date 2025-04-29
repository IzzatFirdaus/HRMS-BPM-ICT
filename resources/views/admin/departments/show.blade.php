{{--
    resources/views/admin/departments/show.blade.php

    This view displays the details of a specific department/unit.
    It extends the main application layout and uses Tailwind CSS classes.
    Assumes the $department model is passed from the controller (DepartmentController@show).
    Assumes the Department model has 'name', 'branch_type', 'code', 'description',
    and potentially 'created_at', 'updated_at' attributes.
    Assumes the layout (layouts.app) handles asset inclusion (like compiled CSS including Tailwind).
    Assumes standard Laravel routing and controller methods for CRUD actions.
--}}

{{-- Extend your main application layout --}}
{{-- Adjust 'layouts.app' if your layout is different --}}
@extends('layouts.app')

{{-- Define the title section to be included in the layout's <head> --}}
{{-- Translate the page title with the department name --}}
@section('title', __('Department/Unit Details: :name', ['name' => $department->name ?? 'N/A']))

{{-- Define any page-specific styles (optional) --}}
@section('page-style')
    {{-- Link any CSS specific to this page if needed, typically compiled assets --}}
    {{-- Example: <link rel="stylesheet" href="{{ asset('css/departments-show.css') }}"> --}}
    {{-- Tailwind CSS is assumed to be included in the main app.css or layout --}}
@endsection

{{-- Define the main content section --}}
@section('content')

    {{-- Main container with max width and centering --}}
    {{-- Using max-w-3xl for a narrower content container --}}
    <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 py-8"> {{-- Increased vertical padding slightly --}}
        {{-- Outer card-like container (already using some Tailwind) --}}
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">

            {{-- Page Title --}}
            {{-- Translate title with department name --}}
            <h2 class="text-2xl font-bold mb-6 text-gray-800">
                {{ __('Department/Unit Details: :name', ['name' => $department->name ?? 'N/A']) }}
            </h2>

            {{-- Display success or error messages from session (using flash messages) --}}
            @if (session()->has('success'))
                {{-- Applied Tailwind classes for success alert --}}
                <div class="bg-green-100 border border-green-200 text-green-800 p-4 rounded-md mb-4">
                    {{ session('success') }}
                </div>
            @endif

            @if (session()->has('error'))
                {{-- Applied Tailwind classes for error alert --}}
                <div class="bg-red-100 border border-red-200 text-red-800 p-4 rounded-md mb-4">
                    {{ session('error') }}
                </div>
            @endif

            {{-- Department Details Block --}}
            {{-- Inner details container with styling --}}
            <div class="border border-gray-200 rounded-md p-4 mb-6 bg-gray-50"> {{-- Added simple border, padding, background, and bottom margin --}}
                {{-- Details section title --}}
                <h3 class="text-xl font-bold mb-4 text-gray-800">{{ __('Department/Unit Details') }}</h3>
                {{-- Translate title --}}

                {{-- Display individual department attributes --}}
                <p class="mb-2"><span class="font-semibold">{{ __('Name Department/Unit:') }}</span>
                    {{-- Translate label --}}
                    {{ $department->name ?? 'N/A' }}
                </p>
                {{-- Added display for Branch Type --}}
                <p class="mb-2"><span class="font-semibold">{{ __('Branch Type:') }}</span> {{-- Translate label --}}
                    {{ __(ucfirst($department->branch_type ?? '-')) }} {{-- Translate and capitalize value, default to '-' --}}
                </p>
                <p class="mb-2"><span class="font-semibold">{{ __('Department/Unit Code:') }}</span>
                    {{-- Translate label --}}
                    {{ $department->code ?? 'N/A' }}
                </p>
                <p class="mb-2"><span class="font-semibold">{{ __('Description:') }}</span> {{-- Translate label --}}
                    {{ $department->description ?? '-' }}
                </p>

                {{-- Optional: Add Created At and Updated At if available --}}
                {{-- Uncomment the lines below if you want to display timestamps --}}
                @if ($department->created_at)
                    <p class="mb-2"><span class="font-semibold">{{ __('Created At:') }}</span>
                        {{ $department->created_at?->format('Y-m-d H:i') ?? '-' }}</p> {{-- Translate label --}}
                @endif
                @if ($department->updated_at)
                    <p class="mb-2"><span class="font-semibold">{{ __('Last Updated At:') }}</span>
                        {{ $department->updated_at?->format('Y-m-d H:i') ?? '-' }}</p> {{-- Translate label --}}
                @endif
                {{-- Add display for 'created_by', 'updated_by', 'deleted_by' relationships if needed,
                      assuming the CreatedUpdatedDeletedBy trait provides methods like createdBy() --}}
                {{-- Example: @if ($department->createdBy) <p class="mb-2"><span class="font-semibold">{{ __('Created By:') }}</span> {{ $department->createdBy->name ?? '-' }}</p> @endif --}}


            </div> {{-- End details block --}}

            {{-- Optional: Link to edit department --}}
            {{-- You might wrap this in an @can('update', $department) --}}
            <div class="mt-6 text-center">
                {{-- Edit button with Tailwind classes and icon --}}
                <a href="{{ route('admin.departments.edit', $department) }}"
                    class="inline-flex items-center justify-center px-5 py-2.5 bg-blue-500 text-white font-semibold rounded-md border border-blue-500 hover:bg-blue-600 hover:border-blue-600 focus:outline-none focus:ring focus:ring-blue-500 focus:ring-opacity-50 transition">
                    {{-- SVG icon --}}
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                    </svg>
                    {{ __('Edit Department/Unit') }} {{-- Translate button text --}}
                </a>
            </div>
            {{-- @endcan --}}


            {{-- Back Button --}}
            <div class="mt-6 text-center">
                {{-- Back button with Tailwind classes and icon --}}
                <a href="{{ route('admin.departments.index') }}"
                    class="inline-flex items-center justify-center px-5 py-2.5 bg-gray-200 text-gray-800 font-semibold rounded-md border border-gray-200 hover:bg-gray-300 hover:border-gray-300 focus:outline-none focus:ring focus:ring-gray-500 focus:ring-opacity-50 transition">
                    {{-- SVG icon --}}
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    {{ __('Back to Department/Unit List') }} {{-- Translate button text --}}
                </a>
            </div>

        </div> {{-- End outer bg-white card --}}
    </div> {{-- End main container --}}

@endsection

{{-- Note: This view is intended to be rendered by a standard controller, not directly by a Livewire component,
     as it extends a layout and uses standard form submissions. If it were a Livewire view, it would typically
     use wire:submit and wire:model.
--}}
