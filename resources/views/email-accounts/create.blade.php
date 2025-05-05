@extends('layouts.app') {{-- Assuming you have a base layout file --}}

@section('content')
    <div class="container mx-auto px-4 py-6">
        {{-- Use a max-width container for better readability on large screens --}}
        <div class="max-w-3xl mx-auto bg-white shadow-md rounded-lg p-6">
            <h1 class="text-2xl font-bold mb-6 text-center">{{ __('Permohonan Akaun E-mel / ID Pengguna MOTAC') }}</h1>
            {{-- Application Title --}}

            {{-- Display validation errors --}}
            @if ($errors->any())
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4" role="alert">
                    <strong class="font-bold">{{ __('Ralat Validasi!') }}</strong>
                    <span class="block sm:inline">{{ __('Sila semak ralat berikut pada borang:') }}</span>
                    <ul class="mt-2 list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Display success messages --}}
            @if (session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4" role="alert">
                    {{ session('success') }}
                </div>
            @endif

            {{-- The Form (Section 5.1 in design document) --}}
            {{-- Submits to the store method of EmailApplicationController --}}
            <form action="{{ route('email-applications.store') }}" method="POST">
                @csrf {{-- CSRF token for security --}}

                {{-- SECTION: Applicant Information (Displaying from authenticated user) --}}
                {{-- Based on Section 5.1, Part 1 & 2. Assumes these are pre-filled/displayed from user profile --}}
                {{-- Ensure the authenticated user object is available (standard in Laravel with 'auth' middleware) --}}
                @auth {{-- Ensure the user is authenticated to display their info --}}
                    <h2 class="text-xl font-semibold mb-4 text-gray-800">{{ __('Maklumat Pemohon') }}</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                        {{-- Display User's Full Name --}}
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2">{{ __('Nama Penuh') }}:</label>
                            {{-- Assuming User model has full_name attribute as per migration --}}
                            <p class="text-gray-800">{{ Auth::user()->full_name ?? __('Tidak Ditetapkan') }}</p>
                            {{-- Hidden field to pass user_id --}}
                            <input type="hidden" name="user_id" value="{{ Auth::id() }}">
                        </div>

                        {{-- Display User's NRIC --}}
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2">{{ __('No. Kad Pengenalan') }}:</label>
                            {{-- Assuming User model has nric attribute as per migration --}}
                            <p class="text-gray-800">{{ Auth::user()->nric ?? __('Tidak Ditetapkan') }}</p>
                        </div>

                        {{-- Display User's Position & Grade --}}
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2">{{ __('Jawatan & Gred') }}:</label>
                            {{-- Assuming relationships position and grade on User model are eager loaded in the controller --}}
                            <p class="text-gray-800">{{ Auth::user()->position->name ?? __('Tidak Ditetapkan') }}
                                ({{ Auth::user()->grade->name ?? __('N/A') }})</p>
                        </div>

                        {{-- Display User's Department/Unit --}}
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2">{{ __('Bahagian/Unit') }}:</label>
                            {{-- Assuming relationship department on User model is eager loaded in the controller --}}
                            <p class="text-gray-800">{{ Auth::user()->department->name ?? __('Tidak Ditetapkan') }}</p>
                        </div>

                        {{-- Display User's Mobile Number --}}
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2">{{ __('No. Telefon Bimbit') }}:</label>
                            {{-- Assuming User model has mobile_number attribute as per migration --}}
                            <p class="text-gray-800">{{ Auth::user()->mobile_number ?? __('Tidak Ditetapkan') }}</p>
                        </div>

                        {{-- Display User's Personal Email --}}
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2">{{ __('E-mel Peribadi') }}:</label>
                            {{-- Assuming User model has personal_email attribute as per migration --}}
                            <p class="text-gray-800">{{ Auth::user()->personal_email ?? __('Tidak Ditetapkan') }}</p>
                        </div>

                        {{-- Service Status Field (Dropdown based on email_applications enum) --}}
                        {{-- This should be a selectable field as per the PDF form --}}
                        <div>
                            <label for="service_status"
                                class="block text-gray-700 text-sm font-bold mb-2">{{ __('Taraf Perkhidmatan') }}<span
                                    class="text-red-500">*</span>:</label>
                            {{-- $serviceStatuses should be passed from the controller as an associative array [enum_value => translated_label] --}}
                            {{-- The enum values MUST match those in the email_applications migration's 'service_status' column (e.g. 'Kakitangan Tetap', not 'permanent') --}}
                            <select name="service_status" id="service_status"
                                class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('service_status') border-red-500 @enderror"
                                required>
                                <option value="">{{ __('- Pilih Taraf Perkhidmatan -') }}</option>
                                {{-- Ensure the values passed in $serviceStatuses match the EmailApplication::SERVICE_STATUS_* constants / DB enum --}}
                                @foreach ($serviceStatuses as $statusValue => $statusLabel)
                                    <option value="{{ $statusValue }}"
                                        {{ old('service_status') == $statusValue ? 'selected' : '' }}>
                                        {{ $statusLabel }}
                                    </option>
                                @endforeach
                            </select>
                            @error('service_status')
                                {{-- The $message variable is automatically available within @error directive --}}
                                {{-- The linter might incorrectly flag $message as undefined (PHP1412), but Laravel provides it at runtime. This is a linter issue. --}}
                                <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        {{-- TODO: Implement dynamic fields based on service_status using JavaScript or Livewire --}}
                    </div>
                @else
                    {{-- Handle case where user is not authenticated --}}
                    <p class="text-red-600">{{ __('Sila log masuk untuk memohon akaun e-mel/ID pengguna.') }}</p>
                @endauth


                {{-- SECTION: Application Details --}}
                <h2 class="text-xl font-semibold mb-4 text-gray-800">{{ __('Butiran Permohonan') }}</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                    {{-- Purpose Field --}}
                    <div class="md:col-span-2"> {{-- Make purpose field span full width on medium screens and up --}}
                        <label for="purpose"
                            class="block text-gray-700 text-sm font-bold mb-2">{{ __('Tujuan / Catatan') }}:</label>
                        <textarea name="purpose" id="purpose" rows="3"
                            class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('purpose') border-red-500 @enderror">{{ old('purpose') }}</textarea>
                        @error('purpose')
                            {{-- The $message variable is automatically available within @error directive --}}
                            <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Proposed Email/ID Field --}}
                    {{-- This field's required status and display might depend on service_status (Dynamic Field - TODO) --}}
                    <div>
                        <label for="proposed_email"
                            class="block text-gray-700 text-sm font-bold mb-2">{{ __('Cadangan E-mel / ID Pengguna') }}:</label>
                        {{-- Type should be 'text' if it can be an ID string, not just email --}}
                        <input type="text" name="proposed_email" id="proposed_email" value="{{ old('proposed_email') }}"
                            class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('proposed_email') border-red-500 @enderror">
                        <p class="text-gray-600 text-xs italic mt-1">
                            {{ __('Contoh: nama.anda@motac.gov.my atau ID Pengguna (untuk Pelajar Latihan Industri)') }}
                        </p>
                        @error('proposed_email')
                            {{-- The $message variable is automatically available within @error directive --}}
                            <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Group Email Fields (Conditional - TODO: Implement dynamic display) --}}
                    <div>
                        <label for="group_email"
                            class="block text-gray-700 text-sm font-bold mb-2">{{ __('Nama Group Email') }}:</label>
                        <input type="text" name="group_email" id="group_email" value="{{ old('group_email') }}"
                            class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('group_email') border-red-500 @enderror">
                        <p class="text-gray-600 text-xs italic mt-1">{{ __('Contoh: group all, groupunit') }}</p>
                        @error('group_email')
                            {{-- The $message variable is automatically available within @error directive --}}
                            <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="group_admin_name"
                            class="block text-gray-700 text-sm font-bold mb-2">{{ __('Nama Admin/EO/CC Group') }}:</label>
                        <input type="text" name="group_admin_name" id="group_admin_name"
                            value="{{ old('group_admin_name') }}"
                            class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('group_admin_name') border-red-500 @enderror">
                        <p class="text-gray-600 text-xs italic mt-1">{{ __('Contoh: Rashid Bin Sardi') }}</p>
                        @error('group_admin_name')
                            {{-- The $message variable is automatically available within @error directive --}}
                            <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="group_admin_email"
                            class="block text-gray-700 text-sm font-bold mb-2">{{ __('E-mel Admin/EO/CC Group') }}:</label>
                        <input type="email" name="group_admin_email" id="group_admin_email"
                            value="{{ old('group_admin_email') }}"
                            class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('group_admin_email') border-red-500 @enderror">
                        <p class="text-gray-600 text-xs italic mt-1">{{ __('Contoh: rashid@motac.gov.my') }}</p>
                        @error('group_admin_email')
                            {{-- The $message variable is automatically available within @error directive --}}
                            <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                </div>

                {{-- SECTION: Certification --}}
                {{-- These checkboxes are MANDATORY. Validation must enforce all three are checked in the backend. --}}
                {{-- We will submit three distinct values (certification_1, _2, _3) and validate in the backend Form Request/Controller. --}}
                <h2 class="text-xl font-semibold mb-4 text-gray-800">{{ __('Perakuan Pemohon') }}</h2>
                <p class="text-gray-700 mb-4">{{ __('Saya dengan ini mengesahkan bahawa:') }}</p>
                <div class="mb-6">
                    {{-- Checkbox 1 (Mandatory) --}}
                    <div class="flex items-start mb-2"> {{-- Use items-start for better alignment with longer text --}}
                        <input type="checkbox" name="certification_1" id="certification_1" value="1"
                            class="mr-2 leading-tight mt-1 @error('certification_1') border-red-500 @enderror"
                            {{ old('certification_1') ? 'checked' : '' }} required> {{-- Add required attribute for basic browser validation --}}
                        <label class="text-gray-700 text-sm" for="certification_1">
                            {{ __('Semua maklumat yang dinyatakan di dalam permohonan ini adalah BENAR.') }}
                        </label>
                    </div>
                    {{-- Display error specifically for this checkbox key --}}
                    @error('certification_1')
                        {{-- The $message variable is automatically available within @error directive --}}
                        <p class="text-red-500 text-xs italic mt-1 ml-6">{{ $message }}</p> {{-- Adjust margin to align with checkbox --}}
                    @enderror

                    {{-- Checkbox 2 (Mandatory) --}}
                    <div class="flex items-start mb-2"> {{-- Use items-start --}}
                        <input type="checkbox" name="certification_2" id="certification_2" value="1"
                            class="mr-2 leading-tight mt-1 @error('certification_2') border-red-500 @enderror"
                            {{ old('certification_2') ? 'checked' : '' }} required> {{-- Add required --}}
                        <label class="text-gray-700 text-sm" for="certification_2">
                            {{ __('BERSETUJU maklumat yang dinyatakan di dalam permohonan ini diguna pakai oleh Bahagian Pengurusan Maklumat untuk tujuan memproses permohonan saya.') }}
                        </label>
                    </div>
                    {{-- Display error specifically for this checkbox key --}}
                    @error('certification_2')
                        {{-- The $message variable is automatically available within @error directive --}}
                        <p class="text-red-500 text-xs italic mt-1 ml-6">{{ $message }}</p>
                    @enderror

                    {{-- Checkbox 3 (Mandatory) --}}
                    <div class="flex items-start mb-2"> {{-- Use items-start --}}
                        <input type="checkbox" name="certification_3" id="certification_3" value="1"
                            class="mr-2 leading-tight mt-1 @error('certification_3') border-red-500 @enderror"
                            {{ old('certification_3') ? 'checked' : '' }} required> {{-- Add required --}}
                        <label class="text-gray-700 text-sm" for="certification_3">
                            {{ __('BERSETUJU untuk bertanggungjawab ke atas setiap e-mel yang dihantar dan diterima melalui akaun e-mel saya.') }}
                        </label>
                    </div>
                    {{-- Display error specifically for this checkbox key --}}
                    @error('certification_3')
                        {{-- The $message variable is automatically available within @error directive --}}
                        <p class="text-red-500 text-xs italic mt-1 ml-6">{{ $message }}</p>
                    @enderror

                    {{-- Note about mandatory checkboxes as per PDF --}}
                    <p class="text-gray-600 text-xs italic mt-2">
                        {{ __('Sila tandakan pada ketiga-tiga kotak perakuan untuk meneruskan permohonan.') }}</p>

                    {{-- The controller/Form Request will validate certification_1, certification_2, and certification_3
                      and set the single boolean 'certification_accepted' field on the EmailApplication model based on the result.
                 --}}
                </div>


                {{-- Submit Button --}}
                <div class="flex items-center justify-end">
                    <button type="submit"
                        class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                        {{ __('Hantar Permohonan') }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- TODO: Add JavaScript or use Livewire to handle dynamic fields based on Service Status selection --}}
    {{-- Refer to commented-out JS example in previous turn if needed. --}}

@endsection
