{{-- resources/views/partials/report-filters.blade.php --}}
{{--
    This partial provides filtering options for reports.
    It expects to be included in a view that has loaded Tailwind CSS.
    It uses a form that should submit GET requests to the report's index route
    to apply filters.
    Props can be used to customize the available filters or their labels if needed.
    For simplicity, this version includes common filters.
--}}

@props(['reportRoute', 'statuses' => [], 'users' => [], 'departments' => []])

<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6"> {{-- Card-like container for filters --}}
    <h4 class="text-xl font-semibold mb-4 text-gray-800">Tapis Laporan</h4> {{-- Filter section title --}}

    {{-- Assuming the form submits to the current report's index route via GET --}}
    <form method="GET" action="{{ route($reportRoute) }}" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        {{-- Responsive grid layout for form elements --}}

        {{-- Date Range Filter --}}
        <div class="form-group col-span-1">
            <label for="start_date" class="block text-gray-700 text-sm font-bold mb-2">Tarikh Mula:</label>
            <input type="date" name="start_date" id="start_date" class="form-control"
                value="{{ request('start_date') }}"> {{-- Populate with current filter value --}}
        </div>

        <div class="form-group col-span-1">
            <label for="end_date" class="block text-gray-700 text-sm font-bold mb-2">Tarikh Tamat:</label>
            <input type="date" name="end_date" id="end_date" class="form-control" value="{{ request('end_date') }}">
            {{-- Populate with current filter value --}}
        </div>

        {{-- Status Filter (Optional - pass statuses as a prop) --}}
        @if (!empty($statuses))
            <div class="form-group col-span-1">
                <label for="status" class="block text-gray-700 text-sm font-bold mb-2">Status:</label>
                <select name="status" id="status" class="form-control">
                    <option value="">- Semua Status -</option>
                    @foreach ($statuses as $value => $label)
                        <option value="{{ $value }}" {{ request('status') === $value ? 'selected' : '' }}>
                            {{ $label }}</option> {{-- Populate with current filter value --}}
                    @endforeach
                </select>
            </div>
        @endif

        {{-- User Filter (Optional - pass users as a prop) --}}
        @if (!empty($users))
            <div class="form-group col-span-1">
                <label for="user_id" class="block text-gray-700 text-sm font-bold mb-2">Pengguna:</label>
                <select name="user_id" id="user_id" class="form-control">
                    <option value="">- Semua Pengguna -</option>
                    @foreach ($users as $user)
                        {{-- Assuming $users is a collection of User models --}}
                        <option value="{{ $user->id }}"
                            {{ (int) request('user_id') === $user->id ? 'selected' : '' }}>
                            {{ $user->name ?? ($user->full_name ?? 'N/A') }}</option> {{-- Populate with current filter value --}}
                    @endforeach
                </select>
            </div>
        @endif

        {{-- Department Filter (Optional - pass departments as a prop) --}}
        @if (!empty($departments))
            <div class="form-group col-span-1">
                <label for="department_id" class="block text-gray-700 text-sm font-bold mb-2">Bahagian/Unit:</label>
                <select name="department_id" id="department_id" class="form-control">
                    <option value="">- Semua Bahagian/Unit -</option>
                    @foreach ($departments as $department)
                        {{-- Assuming $departments is a collection of Department models --}}
                        <option value="{{ $department->id }}"
                            {{ (int) request('department_id') === $department->id ? 'selected' : '' }}>
                            {{ $department->name ?? 'N/A' }}</option> {{-- Populate with current filter value --}}
                    @endforeach
                </select>
            </div>
        @endif

        {{-- Add more filter types here as needed --}}

        {{-- Filter and Reset Buttons --}}
        <div class="form-group col-span-1 md:col-span-2 lg:col-span-3 flex items-end space-x-4 mt-2">
            {{-- Buttons span across columns and align at the bottom --}}
            <button type="submit" class="btn btn-primary">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.707A1 1 0 013 7.001V4z" />
                </svg>
                Tapis
            </button>
            {{-- Link to reset filters by going back to the base report route --}}
            <a href="{{ route($reportRoute) }}" class="btn btn-secondary">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M4 4v5h.582m15.418 0h.582m-15.418 0a8.001 8.001 0 0015.418 0m-15.418 0H4m7 12v-3m0 0l3 3m-3-3l-3 3M4 9v-4a2 2 0 012-2h4l2 2h4a2 2 0 012 2v1" />
                </svg>
                Set Semula
            </a>
        </div>
    </form>
</div>

{{-- Add basic Tailwind form and button styling here or ensure it's in your main CSS --}}
{{-- If using inline styles or a separate CSS file, make sure these classes are defined --}}
<style>
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

    .btn-secondary {
        background-color: #e5e7eb;
        /* gray-200 */
        color: #1f2937;
        /* gray-800 */
        border: 1px solid #e5e7eb;
    }

    .btn-secondary:hover {
        background-color: #d1d5db;
        /* gray-300 */
        border-color: #d1d5db;
    }

    .text-gray-700 {
        color: #374151;
        /* gray-700 */
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

    .mb-2 {
        margin-bottom: 0.5rem;
        /* 8px */
    }
</style>
