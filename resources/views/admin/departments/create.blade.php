{{--
    resources/views/admin/departments/create.blade.php

    This view provides a form for creating a new department/unit.
    It extends the main application layout and uses Tailwind CSS classes.
    Assumes $branchTypes array is passed from the controller (DepartmentController@create).
    Assumes the Department model requires 'name', 'branch_type', and optionally 'code', 'description'.
    Assumes the layout (layouts.app) handles asset inclusion (like compiled CSS including Tailwind).
--}}

{{-- Extend your main application layout --}}
{{-- Adjust 'layouts.app' if your layout is different --}}
@extends('layouts.app')

{{-- Define the title section to be included in the layout's <head> --}}
@section('title', __('Add New Department/Unit')) {{-- Translate the page title --}}

{{-- Define any page-specific styles (optional) --}}
@section('page-style')
    {{-- Link any CSS specific to this page if needed, typically compiled assets --}}
    {{-- Example: <link rel="stylesheet" href="{{ asset('css/departments-create.css') }}"> --}}
    {{-- Tailwind CSS is assumed to be included in the main app.css or layout --}}
@endsection

{{-- Define the main content section --}}
@section('content')

    {{-- Main container with max width and centering --}}
    {{-- Using max-w-3xl for a narrower form container --}}
    <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 py-6">

        {{-- Page Title --}}
        <h2 class="text-2xl font-bold mb-6 text-gray-800">{{ __('Add New Department/Unit') }}</h2> {{-- Translate title --}}

        {{-- Display validation errors from the $errors bag --}}
        @if ($errors->any())
            {{-- Applied Tailwind classes for validation error alert --}}
            <div class="bg-red-100 border border-red-200 text-red-800 p-4 rounded-md mb-4">
                <p class="font-semibold">{{ __('Validation Error:') }}</p> {{-- Translate string --}}
                <ul class="mt-1 list-disc list-inside text-sm"> {{-- Added text-sm for list items --}}
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

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


        {{-- Department Creation Form --}}
        {{-- Assuming department creation is handled by a standard controller action (DepartmentController@store) --}}
        <form action="{{ route('admin.departments.store') }}" method="POST">
            @csrf {{-- CSRF token for security --}}

            {{-- Form container with styling --}}
            <div class="border border-gray-300 rounded-lg p-6 mb-6 bg-white shadow-md">
                {{-- Form section title --}}
                <h4 class="text-xl font-bold mb-4 text-gray-800">{{ __('Department/Unit Details') }}</h4>
                {{-- Translate title --}}

                {{-- Form fields --}}

                {{-- Name Field --}}
                <div class="mb-4">
                    <label for="name"
                        class="block text-gray-700 text-sm font-bold mb-2">{{ __('Name Department/Unit') }}*:</label>
                    {{-- Translate label --}}
                    {{-- Input field with Tailwind classes and validation styling --}}
                    {{-- Value populated using old() --}}
                    <input type="text" name="name" id="name"
                        class="w-full px-3 py-2 border rounded-md shadow-sm focus:outline-none focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50 @error('name') border-red-500 @else border-gray-300 @enderror"
                        {{-- FIX: Corrected border styling conflict --}} required value="{{ old('name') }}">
                    @error('name')
                        {{-- Validation error message --}}
                        <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Branch Type Select Field --}}
                <div class="mb-4">
                    <label for="branch_type"
                        class="block text-gray-700 text-sm font-bold mb-2">{{ __('Branch Type') }}*:</label>
                    {{-- Translate label --}}
                    {{-- Select field with Tailwind classes and validation styling --}}
                    {{-- Value populated using old() --}}
                    <select name="branch_type" id="branch_type"
                        class="w-full px-3 py-2 border rounded-md shadow-sm focus:outline-none focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50 @error('branch_type') border-red-500 @else border-gray-300 @enderror"
                        {{-- FIX: Corrected border styling conflict --}} required>
                        <option value="">{{ __('Select Branch Type') }}</option> {{-- Placeholder --}}
                        {{-- Loop through the $branchTypes array passed from the controller --}}
                        @isset($branchTypes) {{-- Check if the variable exists --}}
                            @foreach ($branchTypes as $type)
                                <option value="{{ $type }}" {{ old('branch_type') == $type ? 'selected' : '' }}>
                                    {{ __(ucfirst($type)) }} {{-- Translate and capitalize type for display --}}
                                </option>
                            @endforeach
                        @endisset
                    </select>
                    @error('branch_type')
                        {{-- Validation error message --}}
                        <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
                    @enderror
                </div>


                {{-- Code Field --}}
                <div class="mb-4">
                    <label for="code"
                        class="block text-gray-700 text-sm font-bold mb-2">{{ __('Department/Unit Code') }}:</label>
                    {{-- Translate label --}}
                    {{-- Input field with Tailwind classes and validation styling --}}
                    {{-- Value populated using old() --}}
                    <input type="text" name="code" id="code"
                        class="w-full px-3 py-2 border rounded-md shadow-sm focus:outline-none focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50 @error('code') border-red-500 @else border-gray-300 @enderror"
                        {{-- FIX: Corrected border styling conflict --}} value="{{ old('code') }}">
                    @error('code')
                        {{-- Validation error message --}}
                        <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Description Field --}}
                <div class="mb-4">
                    <label for="description"
                        class="block text-gray-700 text-sm font-bold mb-2">{{ __('Description') }}:</label>
                    {{-- Translate label --}}
                    {{-- Textarea field with Tailwind classes and validation styling --}}
                    {{-- Value populated using old() --}}
                    <textarea name="description" id="description" rows="3"
                        class="w-full px-3 py-2 border rounded-md shadow-sm focus:outline-none focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50 @error('description') border-red-500 @else border-gray-300 @enderror">{{ old('description') }}</textarea> {{-- FIX: Corrected border styling conflict --}}
                    @error('description')
                        {{-- Validation error message --}}
                        <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
                    @enderror
                </div>

                {{-- TODO: Add more fields here if needed for your Department model --}}

            </div> {{-- End form container --}}


            {{-- Form Submission Button (Centered) --}}
            <div class="flex justify-center mt-6">
                {{-- Submit button with Tailwind classes and icon --}}
                <button type="submit"
                    class="inline-flex items-center justify-center px-5 py-2.5 bg-blue-500 text-white font-semibold rounded-md border border-blue-500 hover:bg-blue-600 hover:border-blue-600 focus:outline-none focus:ring focus:ring-blue-500 focus:ring-opacity-50 transition">
                    {{-- SVG icon --}}
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                    {{ __('Save Department/Unit') }} {{-- Translate string --}}
                </button>
            </div>

        </form>

        {{-- Back Button (Centered) --}}
        <div class="mt-6 text-center">
            {{-- Back button with Tailwind classes and icon --}}
            <a href="{{ route('admin.departments.index') }}"
                class="inline-flex items-center justify-center px-5 py-2.5 bg-gray-200 text-gray-800 font-semibold rounded-md border border-gray-200 hover:bg-gray-300 hover:border-gray-300 focus:outline-none focus:ring focus:ring-gray-500 focus:ring-opacity-50 transition">
                {{-- Adjusted focus ring color --}}
                {{-- SVG icon --}}
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                {{ __('Back to Department/Unit List') }} {{-- Translate string --}}
            </a>
        </div>

    </div> {{-- End main container --}}

@endsection

{{-- Note: This view is intended to be rendered by a standard controller, not directly by a Livewire component,
     as it extends a layout and uses standard form submissions. If it were a Livewire view, it would typically
     use wire:submit and wire:model.
--}}
