<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Permohonan Akaun Emel / ID Pengguna MOTAC</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Optional: Add custom styles if needed, but prefer Tailwind */
        .form-group {
            margin-bottom: 1rem;
        }

        .form-control {
            width: 100%;
            padding: 0.5rem 0.75rem;
            border: 1px solid #d1d5db;
            /* gray-300 */
            border-radius: 0.25rem;
            box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.075);
            outline: none;
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        }

        .form-control:focus {
            border-color: #60a5fa;
            /* blue-400 */
            box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.075), 0 0 0 0.2rem rgba(96, 165, 250, 0.25);
            /* blue-400 with alpha */
        }

        .form-check-input {
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
</head>

<body class="bg-gray-100 p-6">

    {{-- Extend a layout if you have one, otherwise include basic HTML structure --}}
    @extends('layouts.app')

    @section('content')
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 py-6"> {{-- Converted container to Tailwind --}}

            <h2 class="text-2xl font-bold mb-6 text-gray-800">Permohonan Akaun Emel / ID Pengguna MOTAC</h2>
            {{-- Converted h2 --}}

            {{-- Display validation errors if any --}}
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Display success or error messages from session --}}
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


            {{-- Email Application Form --}}
            <form action="{{ route('email-applications.store') }}" method="POST">
                @csrf {{-- CSRF token for security --}}

                {{-- Section: MAKLUMAT PEMOHON (Applicant Information) --}}
                <div class="card">
                    <h4 class="card-title">MAKLUMAT PEMOHON</h4>
                    {{-- Display basic user details, assumed to be pre-filled and not editable on this form.
                      Ensure your User model has these attributes/relationships (position, grade, department). --}}
                    <div class="form-group">
                        <label class="block text-gray-700 text-sm font-bold mb-1">Nama Penuh:</label>
                        <p class="text-gray-800">{{ Auth::user()->name ?? 'N/A' }}</p> {{-- Assuming 'name' for full name --}}
                    </div>
                    <div class="form-group">
                        <label class="block text-gray-700 text-sm font-bold mb-1">No. Pengenalan (NRIC):</label>
                        <p class="text-gray-800">{{ Auth::user()->nric ?? 'N/A' }}</p>
                    </div>
                    <div class="form-group">
                        <label class="block text-gray-700 text-sm font-bold mb-1">Jawatan & Gred:</label>
                        {{-- Assuming relationships exist and have 'name' attribute --}}
                        <p class="text-gray-800">{{ Auth::user()->position->name ?? 'N/A' }} &
                            {{ Auth::user()->grade->name ?? 'N/A' }}</p>
                    </div>
                    <div class="form-group">
                        <label class="block text-gray-700 text-sm font-bold mb-1">Bahagian/Unit:</label>
                        <p class="text-gray-800">{{ Auth::user()->department->name ?? 'N/A' }}</p>
                    </div>
                    <div class="form-group">
                        <label class="block text-gray-700 text-sm font-bold mb-1">No. Telefon Bimbit:</label>
                        <p class="text-gray-800">{{ Auth::user()->phone_number ?? 'N/A' }}</p> {{-- Using phone_number from User model --}}
                    </div>
                    <div class="form-group">
                        <label class="block text-gray-700 text-sm font-bold mb-1">E-mel Peribadi:</label>
                        <p class="text-gray-800">{{ Auth::user()->personal_email ?? 'N/A' }}</p> {{-- Using personal_email from User model --}}
                    </div>

                    {{-- This field is crucial as per the form and system design --}}
                    <div class="form-group">
                        <label for="service_status" class="block text-gray-700 text-sm font-bold mb-2">Taraf
                            Perkhidmatan*:</label>
                        <select name="service_status" id="service_status" class="form-control" required>
                            <option value="">- Pilih Taraf Perkhidmatan -</option>
                            {{-- Use the exact enum values from the migration/form request --}}
                            <option value="Kakitangan Tetap"
                                {{ old('service_status') == 'Kakitangan Tetap' ? 'selected' : '' }}>Kakitangan Tetap
                            </option>
                            <option value="Lantikan Kontrak"
                                {{ old('service_status') == 'Lantikan Kontrak' ? 'selected' : '' }}>Lantikan Kontrak
                            </option>
                            <option value="Personel MySTEP"
                                {{ old('service_status') == 'Personel MySTEP' ? 'selected' : '' }}>Personel MySTEP</option>
                            <option value="Pelajar Latihan Industri"
                                {{ old('service_status') == 'Pelajar Latihan Industri' ? 'selected' : '' }}>Pelajar Latihan
                                Industri</option>
                            <option value="E-mel Sandaran MOTAC"
                                {{ old('service_status') == 'E-mel Sandaran MOTAC' ? 'selected' : '' }}>E-mel Sandaran
                                MOTAC (Kakitangan Agensi Lain)</option>
                        </select>
                        @error('service_status')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Note: Conditional display based on service_status would require JavaScript for a standard HTML form.
                      For simplicity here, all fields are shown, and backend validation (e.g., in StoreEmailApplicationRequest)
                      should handle conditional requirements (e.g., purpose/proposed_email required unless service_status is 'E-mel Sandaran MOTAC'). --}}

                    <div class="form-group">
                        <label for="purpose" class="block text-gray-700 text-sm font-bold mb-2">Tujuan Permohonan /
                            Catatan*:</label>
                        <textarea name="purpose" id="purpose" class="form-control" rows="3" required>{{ old('purpose') }}</textarea>
                        @error('purpose')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="proposed_email" class="block text-gray-700 text-sm font-bold mb-2">Cadangan
                            E-mel:</label> {{-- This field is optional --}}
                        <input type="email" name="proposed_email" id="proposed_email" class="form-control"
                            value="{{ old('proposed_email') }}">
                        @error('proposed_email')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Section for Group email request --}}
                    {{-- Note: Toggling visibility with a checkbox requires JavaScript in a standard HTML form.
                      Here, we'll just include the fields. Backend validation should handle if they are required. --}}
                    <div class="card mt-4 p-4 bg-gray-50 rounded-md border border-gray-200"> {{-- Style with a card/border --}}
                        <h5 class="text-lg font-semibold mb-3 text-gray-700">Butiran Group E-mel (Jika Berkenaan)</h5>
                        <p class="text-gray-600 text-sm mb-4">Sila isi bahagian ini jika permohonan ini adalah untuk akaun
                            Group E-mel.</p>

                        <div class="form-group">
                            <label for="group_email" class="block text-gray-700 text-sm font-bold mb-2">Nama Group
                                Email:</label> {{-- Required if requesting group email (handled by validation) --}}
                            <input type="text" name="group_email" id="group_email" class="form-control"
                                value="{{ old('group_email') }}">
                            @error('group_email')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="group_admin_name" class="block text-gray-700 text-sm font-bold mb-2">Nama
                                Admin/EO/CC Group Email:</label> {{-- Required if requesting group email --}}
                            <input type="text" name="group_admin_name" id="group_admin_name" class="form-control"
                                value="{{ old('group_admin_name') }}">
                            @error('group_admin_name')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="group_admin_email" class="block text-gray-700 text-sm font-bold mb-2">E-mel
                                Admin/EO/CC Group Email:</label> {{-- Required if requesting group email --}}
                            <input type="email" name="group_admin_email" id="group_admin_email" class="form-control"
                                value="{{ old('group_admin_email') }}">
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

                    {{-- Note: The PDF has three separate checkboxes for certification.
                     For simplicity and alignment with the database field 'certification_accepted',
                     we use a single checkbox representing acceptance of all terms.
                     The 'name' attribute is crucial for form submission. --}}
                    <div class="form-group">
                        <div class="flex items-center"> {{-- Use flex for checkbox and label alignment --}}
                            <input type="checkbox" name="certification_accepted" id="certification_accepted"
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

                    {{-- Note: The PDF has "Tarikh" and "Tandatangan & Cop" below this.
                     The timestamp is handled by the backend.
                     Tandatangan & Cop would typically be handled electronically (e.g., implied by the logged-in user submitting). --}}
                </div> {{-- End PERAKUAN PEMOHON card --}}


                {{-- Form Submission Button --}}
                <div class="flex justify-center mt-6">
                    <button type="submit" class="btn btn-success">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                        Hantar Permohonan
                    </button>
                </div>

            </form>

        </div> {{-- End bg-white card --}}
        </div> {{-- End container --}}
    @endsection

</body>

</html>
