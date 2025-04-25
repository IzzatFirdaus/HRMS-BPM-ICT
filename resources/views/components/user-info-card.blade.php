{{-- resources/views/components/user-info-card.blade.php --}}
@props(['user'])

{{--
    This component is used to display a user's information in a card format.
    It expects a $user variable to be passed to it, which is an instance of the User model.
    Assumes the User model has attributes like name, nric, phone_number, personal_email
    and relationships like position, grade, department, each with a 'name' attribute.
--}}

<div class="card"> {{-- Apply card styling --}}
    <h4 class="card-title">MAKLUMAT PENGGUNA</h4> {{-- Title for the user info --}}

    {{-- Display user details --}}
    <div class="form-group"> {{-- Use form-group for consistent spacing --}}
        <label class="block text-gray-700 text-sm font-bold mb-1">Nama Penuh:</label>
        <p class="text-gray-800">{{ $user->name ?? 'N/A' }}</p> {{-- Assuming 'name' for full name --}}
    </div>
    <div class="form-group">
        <label class="block text-gray-700 text-sm font-bold mb-1">No. Pengenalan (NRIC):</label>
        <p class="text-gray-800">{{ $user->nric ?? 'N/A' }}</p>
    </div>
    <div class="form-group">
        <label class="block text-gray-700 text-sm font-bold mb-1">Jawatan & Gred:</label>
        {{-- Assuming relationships exist and have 'name' attribute --}}
        <p class="text-gray-800">{{ $user->position->name ?? 'N/A' }} & {{ $user->grade->name ?? 'N/A' }}</p>
    </div>
    <div class="form-group">
        <label class="block text-gray-700 text-sm font-bold mb-1">Bahagian/Unit:</label>
        <p class="text-gray-800">{{ $user->department->name ?? 'N/A' }}</p>
    </div>
    <div class="form-group">
        <label class="block text-gray-700 text-sm font-bold mb-1">No. Telefon:</label>
        <p class="text-gray-800">{{ $user->phone_number ?? 'N/A' }}</p> {{-- Using phone_number from User model --}}
    </div>
    <div class="form-group">
        <label class="block text-gray-700 text-sm font-bold mb-1">E-mel Peribadi:</label>
        <p class="text-gray-800">{{ $user->personal_email ?? 'N/A' }}</p> {{-- Using personal_email from User model --}}
    </div>

    {{-- Add basic Tailwind card and form styling here or ensure it's in your main CSS --}}
    {{-- If using inline styles or a separate CSS file, make sure these classes are defined --}}
    <style>
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

        .form-group {
            margin-bottom: 1rem;
        }

        .text-gray-700 {
            color: #374151;
            /* gray-700 */
        }

        .text-gray-800 {
            color: #1f2937;
            /* gray-800 */
        }

        .text-sm {
            font-size: 0.875rem;
            /* 14px */
        }

        .font-bold {
            font-weight: 700;
        }

        .block {
            display: block;
        }

        .mb-1 {
            margin-bottom: 0.25rem;
            /* 4px */
        }
    </style>
</div>
