{{--
    This Blade file is a form for adding a new Position.
    It uses Tailwind CSS for styling and extends a base layout.
--}}

@extends('layouts.app') {{-- Extend your main layout file --}}

@section('title', __('Add New Position')) {{-- Set the page title in the layout --}}

{{-- Removed the <head> and <body> tags and inline <style> block, as these should be handled by the layout --}}
{{-- The Tailwind JIT compiler script <script src="https://cdn.tailwindcss.com"></script> should also be in your main layout <head> --}}

@section('content')
    <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 py-6"> {{-- Container for the content --}}

        <h2 class="text-2xl font-bold mb-6 text-gray-800">{{ __('Add New Position') }}</h2> {{-- Translated Title, applied Tailwind classes --}}

        {{-- Display validation errors if any --}}
        @if ($errors->any())
            {{-- Converted alert alert-danger to Tailwind classes --}}
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <p class="font-bold">{{ __('Validation Error') }}:</p> {{-- Translated string, applied Tailwind font-bold --}}
                <ul class="mt-1 list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Display success or error messages from session --}}
        @if (session()->has('success'))
            {{-- Converted alert alert-success to Tailwind classes --}}
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                {{ session('success') }}
            </div>
        @endif

        @if (session()->has('error'))
            {{-- Converted alert alert-danger to Tailwind classes --}}
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                {{ session('error') }}
            </div>
        @endif


        {{-- Position Creation Form --}}
        {{-- Assuming position creation is handled by a standard controller action --}}
        <form action="{{ route('admin.positions.store') }}" method="POST">
            @csrf {{-- CSRF token for security --}}

            <div class="bg-white shadow-md rounded-lg p-6 mb-6"> {{-- Converted card to Tailwind classes --}}
                <h4 class="text-xl font-semibold mb-4 text-gray-800"> {{-- Converted card-title to Tailwind classes, adjusted size --}}
                    {{ __('Position Details') }} {{-- Translated string --}}
                </h4>

                {{-- Converted form-group to Tailwind margin --}}
                <div class="mb-4">
                    {{-- Converted form-label to Tailwind classes --}}
                    <label for="name"
                        class="block text-gray-700 text-sm font-bold mb-2">{{ __('Position Name*') }}:</label>
                    {{-- Translated string --}}
                    {{-- Converted form-control and error state to Tailwind classes --}}
                    <input type="text" name="name" id="name" required
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('name') border-red-500 @enderror"
                        value="{{ old('name') }}">
                    @error('name')
                        {{-- Converted text-danger to Tailwind classes --}}
                        <span class="text-red-500 text-sm mt-1">{{ $message }}</span> {{-- Added margin-top --}}
                    @enderror
                </div>

                {{-- Converted form-group to Tailwind margin --}}
                <div class="mb-4">
                    {{-- Converted form-label to Tailwind classes --}}
                    <label for="code"
                        class="block text-gray-700 text-sm font-bold mb-2">{{ __('Position Code') }}:</label>
                    {{-- Translated string --}}
                    {{-- Converted form-control and error state to Tailwind classes --}}
                    <input type="text" name="code" id="code"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('code') border-red-500 @enderror"
                        value="{{ old('code') }}">
                    @error('code')
                        {{-- Converted text-danger to Tailwind classes --}}
                        <span class="text-red-500 text-sm mt-1">{{ $message }}</span> {{-- Added margin-top --}}
                    @enderror
                </div>

                {{-- Converted form-group to Tailwind margin --}}
                <div class="mb-4">
                    {{-- Converted form-label to Tailwind classes --}}
                    <label for="description"
                        class="block text-gray-700 text-sm font-bold mb-2">{{ __('Description') }}:</label>
                    {{-- Translated string --}}
                    {{-- Converted form-control and error state to Tailwind classes --}}
                    <textarea name="description" id="description" rows="3"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('description') border-red-500 @enderror">{{ old('description') }}</textarea>
                    @error('description')
                        {{-- Converted text-danger to Tailwind classes --}}
                        <span class="text-red-500 text-sm mt-1">{{ $message }}</span> {{-- Added margin-top --}}
                    @enderror
                </div>

            </div> {{-- End card --}}


            {{-- Form Submission Button --}}
            <div class="flex justify-center mt-6">
                {{-- Converted btn btn-primary to Tailwind classes --}}
                <button type="submit"
                    class="inline-flex items-center px-5 py-2.5 bg-blue-600 text-white font-semibold rounded-md shadow hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                    {{ __('Save Position') }} {{-- Translated string --}}
                </button>
            </div>

        </form>

        {{-- Back Button --}}
        <div class="mt-6 text-center"> {{-- Centered the back button --}}
            {{-- Converted btn btn-secondary to Tailwind classes --}}
            <a href="{{ route('admin.positions.index') }}"
                class="inline-flex items-center px-5 py-2.5 bg-gray-200 text-gray-800 font-semibold rounded-md shadow hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                {{ __('Back to Position List') }} {{-- Translated string --}}
            </a>
        </div>

    </div> {{-- End container --}}
@endsection
