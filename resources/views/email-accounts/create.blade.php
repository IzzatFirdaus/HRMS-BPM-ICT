@extends('layouts.app') {{-- Assuming you have a base layout file --}}

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="bg-white shadow-md rounded-lg p-6">
        <h1 class="text-2xl font-bold mb-6">{{ __('Permohonan Akaun E-mel / ID Pengguna MOTAC') }}</h1> {{-- Application Title --}}

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

        {{-- The Form (Section 5.1 in design document) --}}
        {{-- Submits to the store method of EmailApplicationController --}}
        <form action="{{ route('email-applications.store') }}" method="POST">
            @csrf {{-- CSRF token for security --}}

            {{-- SECTION: Applicant Information (Displaying from authenticated user) --}}
            {{-- Based on Section 5.1, Part 1 & 2. Assumes these are pre-filled/displayed from user profile --}}
            <h2 class="text-xl font-semibold mb-4">{{ __('Maklumat Pemohon') }}</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                {{-- Display User's Full Name --}}
                <div>
                    <label for="full_name" class="block text-gray-700 text-sm font-bold mb-2">{{ __('Nama Penuh') }}:</label>
                    {{-- Assuming User model has full_name attribute --}}
                    <p class="text-gray-800">{{ $user->full_name ?? __('Tidak Ditetapkan') }}</p>
                     {{-- Hidden field if full_name is required in validation but not in form --}}
                     {{-- <input type="hidden" name="full_name" value="{{ $user->full_name }}"> --}}
                </div>

                 {{-- Display User's NRIC --}}
                 <div>
                     <label for="nric" class="block text-gray-700 text-sm font-bold mb-2">{{ __('No. Kad Pengenalan') }}:</label>
                     {{-- Assuming User model has nric attribute --}}
                     <p class="text-gray-800">{{ $user->nric ?? __('Tidak Ditetapkan') }}</p>
                      {{-- Hidden field if nric is required in validation but not in form --}}
                      {{-- <input type="hidden" name="nric" value="{{ $user->nric }}"> --}}
                 </div>

                {{-- Display User's Position & Grade --}}
                <div>
                    <label for="position_grade" class="block text-gray-700 text-sm font-bold mb-2">{{ __('Jawatan & Gred') }}:</label>
                     {{-- Assuming relationships position and grade on User model --}}
                    <p class="text-gray-800">{{ $user->position->name ?? __('Tidak Ditetapkan') }} ({{ $user->grade->name ?? __('N/A') }})</p>
                     {{-- Hidden fields if position_id and grade_id are required in validation but not in form --}}
                     {{-- <input type="hidden" name="position_id" value="{{ $user->position_id }}"> --}}
                     {{-- <input type="hidden" name="grade_id" value="{{ $user->grade_id }}"> --}}
                </div>

                 {{-- Display User's Department/Unit --}}
                <div>
                    <label for="department" class="block text-gray-700 text-sm font-bold mb-2">{{ __('Bahagian/Unit') }}:</label>
                     {{-- Assuming relationship department on User model --}}
                    <p class="text-gray-800">{{ $user->department->name ?? __('Tidak Ditetapkan') }}</p>
                    {{-- Hidden field if department_id is required in validation but not in form --}}
                    {{-- <input type="hidden" name="department_id" value="{{ $user->department_id }}"> --}}
                </div>

                 {{-- Display User's Mobile Number --}}
                <div>
                    <label for="mobile_number" class="block text-gray-700 text-sm font-bold mb-2">{{ __('No. Telefon Bimbit') }}:</label>
                     {{-- Assuming User model has mobile_number attribute --}}
                    <p class="text-gray-800">{{ $user->mobile_number ?? __('Tidak Ditetapkan') }}</p>
                    {{-- Hidden field if mobile_number is required in validation but not in form --}}
                    {{-- <input type="hidden" name="mobile_number" value="{{ $user->mobile_number }}"> --}}
                </div>

                 {{-- Display User's Personal Email --}}
                <div>
                    <label for="personal_email" class="block text-gray-700 text-sm font-bold mb-2">{{ __('E-mel Peribadi') }}:</label>
                     {{-- Assuming User model has personal_email attribute --}}
                    <p class="text-gray-800">{{ $user->personal_email ?? __('Tidak Ditetapkan') }}</p>
                    {{-- Hidden field if personal_email is required in validation but not in form --}}
                    {{-- <input type="hidden" name="personal_email" value="{{ $user->personal_email }}"> --}}
                </div>

                {{-- Service Status Field (Dropdown based on email_applications enum) --}}
                {{-- Based on Section 5.1 step 1 & 2, Section 7.1 Eligibility Rules --}}
                {{-- This should be a selectable field as per the PDF form --}}
                <div>
                    <label for="service_status" class="block text-gray-700 text-sm font-bold mb-2">{{ __('Taraf Perkhidmatan') }}*:</label>
                    {{-- $serviceStatuses should be passed from the controller containing the enum values from the migration --}}
                    <select name="service_status" id="service_status" class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('service_status') border-red-500 @enderror" required>
                        <option value="">{{ __('- Pilih Taraf Perkhidmatan -') }}</option>
                        @foreach ($serviceStatuses as $statusValue)
                            <option value="{{ $statusValue }}" {{ old('service_status') == $statusValue ? 'selected' : '' }}>
                                {{-- Display translated version using model accessor if needed, or map here --}}
                                {{ (new \App\Models\EmailApplication())->service_status = $statusValue (new \App\Models\EmailApplication())->service_status_translated }}
                                {{-- A more robust way would be to pass a key/value array of [enum_value => translated_string] from controller --}}
                            </option>
                        @endforeach
                    </select>
                    @error('service_status')
                        <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- SECTION: Application Details --}}
            {{-- Based on Section 5.1, Part 2 --}}
            <h2 class="text-xl font-semibold mb-4">{{ __('Butiran Permohonan') }}</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                {{-- Purpose Field --}}
                 <div>
                     <label for="purpose" class="block text-gray-700 text-sm font-bold mb-2">{{ __('Tujuan / Catatan') }}:</label>
                     <textarea name="purpose" id="purpose" rows="3" class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('purpose') border-red-500 @enderror">{{ old('purpose') }}</textarea>
                     @error('purpose')
                         <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                     @enderror
                 </div>

                {{-- Proposed Email/ID Field (System Suggestion may appear here via JS/Livewire) --}}
                <div>
                    <label for="proposed_email" class="block text-gray-700 text-sm font-bold mb-2">{{ __('Cadangan E-mel / ID Pengguna') }}:</label>
                    <input type="email" name="proposed_email" id="proposed_email" value="{{ old('proposed_email') }}" class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('proposed_email') border-red-500 @enderror">
                    {{-- You might add a note about naming convention or auto-suggestion here --}}
                    <p class="text-gray-600 text-xs italic mt-1">{{ __('Contoh: nama.anda@motac.gov.my') }}</p>
                    @error('proposed_email')
                        <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Group Email Fields (if applicable) --}}
                {{-- Based on PDF form, these fields exist --}}
                 <div>
                     <label for="group_email" class="block text-gray-700 text-sm font-bold mb-2">{{ __('Nama Group Email') }}:</label>
                     <input type="text" name="group_email" id="group_email" value="{{ old('group_email') }}" class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('group_email') border-red-500 @enderror">
                     <p class="text-gray-600 text-xs italic mt-1">{{ __('Contoh: group all, groupunit') }}</p>
                     @error('group_email')
                         <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                     @enderror
                 </div>

                 <div>
                     <label for="group_admin_name" class="block text-gray-700 text-sm font-bold mb-2">{{ __('Nama Admin/EO/CC Group') }}:</label>
                     <input type="text" name="group_admin_name" id="group_admin_name" value="{{ old('group_admin_name') }}" class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('group_admin_name') border-red-500 @enderror">
                      <p class="text-gray-600 text-xs italic mt-1">{{ __('Contoh: Rashid Bin Sardi') }}</p>
                     @error('group_admin_name')
                         <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                     @enderror
                 </div>

                 <div>
                     <label for="group_admin_email" class="block text-gray-700 text-sm font-bold mb-2">{{ __('E-mel Admin/EO/CC Group') }}:</label>
                     <input type="email" name="group_admin_email" id="group_admin_email" value="{{ old('group_admin_email') }}" class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('group_admin_email') border-red-500 @enderror">
                      <p class="text-gray-600 text-xs italic mt-1">{{ __('Contoh: rashid@motac.gov.my') }}</p>
                     @error('group_admin_email')
                         <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                     @enderror
                 </div>

            </div>

            {{-- SECTION: Certification (Section 5.1, step 2 & 7.2) --}}
            <h2 class="text-xl font-semibold mb-4">{{ __('Perakuan Pemohon') }}</h2>
            <p class="text-gray-700 mb-4">{{ __('Saya dengan ini mengesahkan bahawa:') }}</p>
            <div class="mb-6">
                {{-- Checkbox 1 (Mandatory) --}}
                <div class="flex items-center mb-2">
                    <input type="checkbox" name="certification_accepted_1" id="certification_accepted_1" value="1" class="mr-2 leading-tight @error('certification_accepted_1') border-red-500 @enderror" {{ old('certification_accepted_1') ? 'checked' : '' }}>
                    <label class="text-gray-700 text-sm" for="certification_accepted_1">
                        {{ __('Semua maklumat yang dinyatakan di dalam permohonan ini adalah BENAR.') }}
                    </label>
                </div>
                 @error('certification_accepted_1')
                    <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                @enderror

                {{-- Checkbox 2 (Mandatory) --}}
                 <div class="flex items-center mb-2">
                    <input type="checkbox" name="certification_accepted_2" id="certification_accepted_2" value="1" class="mr-2 leading-tight @error('certification_accepted_2') border-red-500 @endror" {{ old('certification_accepted_2') ? 'checked' : '' }}>
                    <label class="text-gray-700 text-sm" for="certification_accepted_2">
                        {{ __('BERSETUJU maklumat yang dinyatakan di dalam permohonan ini diguna pakai oleh Bahagian Pengurusan Maklumat untuk tujuan memproses permohonan saya.') }}
                    </label>
                </div>
                 @error('certification_accepted_2')
                    <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                @enderror

                {{-- Checkbox 3 (Mandatory) --}}
                 <div class="flex items-center mb-2">
                    <input type="checkbox" name="certification_accepted_3" id="certification_accepted_3" value="1" class="mr-2 leading-tight @error('certification_accepted_3') border-red-500 @enderror" {{ old('certification_accepted_3') ? 'checked' : '' }}>
                    <label class="text-gray-700 text-sm" for="certification_accepted_3">
                        {{ __('BERSETUJU untuk bertanggungjawab ke atas setiap e-mel yang dihantar dan diterima melalui akaun e-mel saya.') }}
                    </label>
                </div>
                 @error('certification_accepted_3')
                    <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                @enderror

                {{-- Note about mandatory checkboxes as per PDF --}}
                <p class="text-gray-600 text-xs italic mt-2">{{ __('Sila tandakan pada ketiga-tiga kotak perakuan untuk meneruskan permohonan.') }}</p>
            </div>


            {{-- Submit Button --}}
            <div class="flex items-center justify-end">
                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                    {{ __('Hantar Permohonan') }}
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
