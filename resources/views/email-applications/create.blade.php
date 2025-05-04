{{--
    Blade view for the Email Application Form Livewire component.
    This content will be rendered within your main application layout (layouts.app).
    Livewire handles the form submission and data binding.
--}}

{{-- Removed: <!DOCTYPE html>, <html>, <head>, <body> --}}
{{-- Removed: @extends('layouts.app') and @section('content') --}}

<div class="max-w-3xl mx-auto sm:px-6 lg:px-8 py-6"> {{-- Converted container to Tailwind --}}

    <h2 class="text-2xl font-bold mb-6 text-gray-800">Permohonan Akaun Emel / ID Pengguna MOTAC</h2>
    {{-- Converted h2 --}}

    {{-- Display validation errors if any (This works with Livewire validation) --}}
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Display success or error messages from session (Can work with Livewire, or use component properties) --}}
    @if (session()->has('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif


    {{-- Email Application Form - Using wire:submit for Livewire handling --}}
    {{-- Removed: action="..." method="..." and @csrf --}}
    <form wire:submit.prevent="submitApplication">

        {{-- Section: MAKLUMAT PEMOHON (Applicant Information) --}}
        <div class="card">
            <h4 class="card-title">MAKLUMAT PEMOHON</h4>
            {{-- Display basic user details, assumed to be available via the component or Auth facade.
               Best practice is to pass data from component backend to view.
               Assuming $user property exists in the Livewire component, populated with Auth::user() --}}
            <div class="form-group">
                <label class="block text-gray-700 text-sm font-bold mb-1">Nama Penuh:</label>
                <p class="text-gray-800">{{ $user->name ?? (Auth::user()->name ?? 'N/A') }}</p> {{-- Use $user or Auth::user() --}}
            </div>
            <div class="form-group">
                <label class="block text-gray-700 text-sm font-bold mb-1">No. Pengenalan (NRIC):</label>
                <p class="text-gray-800">{{ $user->nric ?? (Auth::user()->nric ?? 'N/A') }}</p>
            </div>
            <div class="form-group">
                <label class="block text-gray-700 text-sm font-bold mb-1">Jawatan & Gred:</label>
                {{-- Assuming relationships exist and have 'name' attribute, access via $user property --}}
                <p class="text-gray-800">{{ $user->position->name ?? (Auth::user()->position->name ?? 'N/A') }} &
                    {{ $user->grade->name ?? (Auth::user()->grade->name ?? 'N/A') }}</p>
            </div>
            <div class="form-group">
                <label class="block text-gray-700 text-sm font-bold mb-1">Bahagian/Unit:</label>
                <p class="text-gray-800">{{ $user->department->name ?? (Auth::user()->department->name ?? 'N/A') }}</p>
            </div>
            <div class="form-group">
                <label class="block text-gray-700 text-sm font-bold mb-1">No. Telefon Bimbit:</label>
                <p class="text-gray-800">{{ $user->phone_number ?? (Auth::user()->phone_number ?? 'N/A') }}</p>
                {{-- Using phone_number --}}
            </div>
            <div class="form-group">
                <label class="block text-gray-700 text-sm font-bold mb-1">E-mel Peribadi:</label>
                <p class="text-gray-800">{{ $user->personal_email ?? (Auth::user()->personal_email ?? 'N/A') }}</p>
                {{-- Using personal_email --}}
            </div>

            {{-- This field is crucial as per the form and system design - Using wire:model --}}
            <div class="form-group">
                <label for="service_status" class="block text-gray-700 text-sm font-bold mb-2">Taraf
                    Perkhidmatan*:</label>
                {{-- wire:model="service_status" binds this select's value to the component property --}}
                <select wire:model="service_status" id="service_status" class="form-control" required>
                    <option value="">- Pilih Taraf Perkhidmatan -</option>
                    {{-- Use the exact enum values from the migration/form request --}}
                    {{-- old() is not typically needed with wire:model as the property holds the value --}}
                    <option value="Kakitangan Tetap">Kakitangan Tetap</option>
                    <option value="Lantikan Kontrak">Lantikan Kontrak</option>
                    <option value="Personel MySTEP">Personel MySTEP</option>
                    <option value="Pelajar Latihan Industri">Pelajar Latihan Industri</option>
                    <option value="E-mel Sandaran MOTAC">E-mel Sandaran MOTAC (Kakitangan Agensi Lain)</option>
                </select>
                {{-- @error directive works directly with Livewire validation --}}
                @error('service_status')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>

            {{-- Note: Conditional display based on service_status can be done with wire:model and @if in Blade --}}
            {{-- Example: <div wire:model="service_status" === 'E-mel Sandaran MOTAC'> ... group email fields ... </div> --}}
            {{-- For simplicity here, fields are always shown, but backend validation is key for conditional requirements. --}}


            {{-- Purpose field - Using wire:model --}}
            <div class="form-group">
                <label for="purpose" class="block text-gray-700 text-sm font-bold mb-2">Tujuan Permohonan /
                    Catatan*:</label>
                {{-- wire:model="purpose" binds this textarea's value to the component property --}}
                <textarea wire:model="purpose" id="purpose" class="form-control" rows="3" required></textarea>
                @error('purpose')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>

            {{-- Proposed Email field - Using wire:model --}}
            <div class="form-group">
                <label for="proposed_email" class="block text-gray-700 text-sm font-bold mb-2">Cadangan
                    E-mel:</label> {{-- This field is optional --}}
                {{-- wire:model="proposed_email" binds this input's value to the component property --}}
                <input type="email" wire:model="proposed_email" id="proposed_email" class="form-control">
                @error('proposed_email')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>

            {{-- Section for Group email request - Using wire:model for fields inside --}}
            {{-- Note: Toggling visibility with wire:model="service_status" and @if is possible here. --}}
            <div class="card mt-4 p-4 bg-gray-50 rounded-md border border-gray-200"> {{-- Style with a card/border --}}
                <h5 class="text-lg font-semibold mb-3 text-gray-700">Butiran Group E-mel (Jika Berkenaan)</h5>
                <p class="text-gray-600 text-sm mb-4">Sila isi bahagian ini jika permohonan ini adalah untuk akaun
                    Group E-mel.</p>

                <div class="form-group">
                    <label for="group_email" class="block text-gray-700 text-sm font-bold mb-2">Nama Group
                        Email:</label> {{-- Required if requesting group email (handled by validation) --}}
                    {{-- wire:model="group_email" binds this input's value to the component property --}}
                    <input type="text" wire:model="group_email" id="group_email" class="form-control">
                    @error('group_email')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="group_admin_name" class="block text-gray-700 text-sm font-bold mb-2">Nama
                        Admin/EO/CC Group Email:</label> {{-- Required if requesting group email --}}
                    {{-- wire:model="group_admin_name" binds this input's value to the component property --}}
                    <input type="text" wire:model="group_admin_name" id="group_admin_name" class="form-control">
                    @error('group_admin_name')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="group_admin_email" class="block text-gray-700 text-sm font-bold mb-2">E-mel
                        Admin/EO/CC Group Email:</label> {{-- Required if requesting group email --}}
                    {{-- wire:model="group_admin_email" binds this input's value to the component property --}}
                    <input type="email" wire:model="group_admin_email" id="group_admin_email" class="form-control">
                    @error('group_admin_email')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
                <div class="alert alert-warning mt-2">
                    *Sila pastikan E-mel Admin/EO/CC Group E-mel adalah e-mel rasmi MOTAC.
                </div>
            </div>


        </div> {{-- End MAKLUMAT PEMOHON card --}}


        {{-- Section: PERAKUAN PEMOHON (Applicant Certification) --}}
        {{-- This section requires acceptance of terms before submission --}}
        <div class="card mt-6"> {{-- Added margin-top --}}
            <h4 class="card-title">PERAKUAN PEMOHON</h4>
            <p class="mb-4 text-gray-700">Saya dengan ini mengesahkan bahawa:</p>

            {{-- Using a single checkbox for certification acceptance, bound with wire:model --}}
            <div class="form-group">
                <div class="flex items-center"> {{-- Use flex for checkbox and label alignment --}}
                    {{-- wire:model="certification_accepted" binds this checkbox to the component property --}}
                    <input type="checkbox" wire:model="certification_accepted" id="certification_accepted"
                        value="1"
                        class="form-check-input h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                        required> {{-- Required to be checked for submission --}}
                    <label class="ml-2 block text-sm text-gray-700" for="certification_accepted">
                        {{-- Adjusted label styling --}}
                        Saya telah membaca dan memahami semua maklumat di dalam borang permohonan ini dan
                        mengesahkan semua maklumat yang dinyatakan adalah BENAR. Saya juga BERSETUJU maklumat ini
                        diguna pakai oleh BPM dan BERSETUJU untuk bertanggungjawab ke atas akaun e-mel saya.
                    </label>
                </div>
                @error('certification_accepted')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>

            {{-- Note: "Tarikh" and "Tandatangan & Cop" are typically handled electronically (timestamp and logged-in user). --}}
        </div> {{-- End PERAKUAN PEMOHON card --}}


        {{-- Form Submission Button - Type submit is important for wire:submit --}}
        <div class="flex justify-center mt-6">
            <button type="submit" class="btn btn-success">
                {{-- Optional: Add a loading indicator --}}
                <span wire:loading.remove>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                </span>
                <span wire:loading>
                    <svg class="animate-spin h-5 w-5 text-white mr-2" xmlns="http://www.w3.org/2000/svg"
                        fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                            stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.0.0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                        </path>
                    </svg>
                </span>
                Hantar Permohonan
            </button>
        </div>

    </form>

</div> {{-- End max-w-3xl container --}}

{{-- Removed: @endsection --}}
{{-- Removed: </body>, </html> --}}

{{-- Optional: Add custom styles within <style> tags or link a CSS file if needed --}}
<style>
    /* Your custom styles for form-group, form-control, etc. can remain here or be converted to Tailwind */
    /* Ensure these styles are loaded by your main layout */
    .form-group {
        margin-bottom: 1rem;
    }

    .form-control {
        width: 100%;
        padding: 0.5rem 0.75rem;
        border: 1px solid #d1d5db;
        border-radius: 0.25rem;
        box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.075);
        outline: none;
        transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
    }

    .form-control:focus {
        border-color: #60a5fa;
        box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.075), 0 0 0 0.2rem rgba(96, 165, 250, 0.25);
    }

    .form-check-input {
        /* Basic styling, Tailwind classes on the input are preferred */
        margin-right: 0.5rem;
    }

    .text-danger {
        color: #ef4444;
        /* red-500 */
        font-size: 0.875rem;
        /* text-sm */
    }

    .alert {
        padding: 1rem;
        border-radius: 0.25rem;
        margin-bottom: 1rem;
        border-width: 1px;
    }

    .alert-success {
        background-color: #d1fae5;
        /* green-100 */
        border-color: #a7f3d0;
        /* green-200 */
        color: #065f46;
        /* green-800 */
    }

    .alert-danger {
        background-color: #fee2e2;
        /* red-100 */
        border-color: #fecaca;
        /* red-200 */
        color: #991b1b;
        /* red-800 */
    }

    .alert-info {
        background-color: #e0f2f7;
        /* cyan-100 */
        border-color: #bae6fd;
        /* cyan-200 */
        color: #0e7490;
        /* cyan-800 */
    }

    .card {
        border: 1px solid #d1d5db;
        /* gray-300 */
        border-radius: 0.5rem;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        background-color: #fff;
        /* white */
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
    }

    .card-title {
        font-size: 1.25rem;
        /* text-xl */
        font-weight: bold;
        margin-bottom: 1rem;
        color: #1f2937;
        /* gray-800 */
    }

    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0.5rem 1.25rem;
        border-radius: 0.375rem;
        /* rounded-md */
        font-weight: 600;
        /* font-semibold */
        transition: background-color 0.15s ease-in-out, border-color 0.15s ease-in-out, color 0.15s ease-in-out;
        outline: none;
        cursor: pointer;
    }

    .btn-primary {
        background-color: #3b82f6;
        /* blue-500 */
        color: #fff;
        border: 1px solid #3b82f6;
    }

    .btn-primary:hover {
        background-color: #2563eb;
        /* blue-600 */
        border-color: #2563eb;
    }

    .btn-success {
        background-color: #48bb78;
        /* green-500 */
        color: #fff;
        border: 1px solid #48bb78;
    }

    .btn-success:hover {
        background-color: #38a169;
        /* green-600 */
        border-color: #38a169;
    }
</style>

{{-- You will also need to create the EmailApplicationForm Livewire component
   (php artisan make:livewire EmailApplicationForm) and add the public properties
   and submitApplication method to handle the form logic. --}}
