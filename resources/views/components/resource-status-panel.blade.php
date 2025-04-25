{{-- resources/views/components/resource-status-panel.blade.php --}}
@props(['resource', 'statusAttribute' => 'status'])

@php
    // Get the status value from the resource object using the specified attribute
    $status = $resource->{$statusAttribute} ?? 'unknown';

    // Determine the badge color based on the status value
    $badgeColor = match ($status) {
        'available', 'completed', 'approved', 'returned', 'success' => 'badge-success', // Green statuses
        'on_loan',
        'pending',
        'pending_support',
        'pending_admin',
        'processing',
        'warning'
            => 'badge-warning', // Yellow/Warning statuses
        'under_maintenance', 'info' => 'badge-info', // Blue/Info statuses
        'disposed',
        'lost',
        'damaged',
        'rejected',
        'cancelled',
        'provision_failed',
        'danger'
            => 'badge-danger', // Red/Danger statuses
        'draft', 'secondary' => 'badge-secondary', // Gray/Secondary statuses
        'issued' => 'badge-teal', // Custom teal for issued loan transactions
        'overdue' => 'badge-red', // Custom red for overdue loan transactions
        'partially_issued' => 'badge-orange', // Custom orange for partially issued
        'partially_returned' => 'badge-purple', // Custom purple for partially returned
        default => 'badge-secondary', // Default to gray
    };

    // Format the status text for display (replace underscores with spaces and capitalize)
    $formattedStatus = ucfirst(str_replace('_', ' ', $status));
@endphp

{{-- Render the status badge --}}
<span class="badge {{ $badgeColor }}">
    {{ $formattedStatus }}
</span>

{{-- Add basic Tailwind badge styling here or ensure it's in your main CSS --}}
{{-- If using inline styles or a separate CSS file, make sure these classes are defined --}}
<style>
    .badge {
        display: inline-flex;
        align-items: center;
        padding: 0.25rem 0.75rem;
        /* py-1 px-3 */
        border-radius: 9999px;
        /* rounded-full */
        font-size: 0.75rem;
        /* text-xs */
        font-weight: 600;
        /* font-semibold */
        line-height: 1;
        text-transform: capitalize;
        /* Capitalize the first letter */
    }

    /* Define badge colors using Tailwind color classes */
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

    .badge-info {
        background-color: #bfdbfe;
        /* blue-200 */
        color: #1e40af;
        /* blue-800 */
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

    /* Custom badge colors based on previous views/design */
    .badge-teal {
        /* For 'issued' */
        background-color: #b2f5ea;
        /* teal-200 */
        color: #2c7a7b;
        /* teal-800 */
    }

    .badge-purple {
        /* For 'returned' or 'partially_returned' */
        background-color: #e9d8fd;
        /* purple-200 */
        color: #6b46c1;
        /* purple-800 */
    }

    .badge-red {
        /* For 'overdue' */
        background-color: #feb2b2;
        /* red-200 */
        color: #c53030;
        /* red-800 */
    }

    .badge-orange {
        /* For 'partially_issued' */
        background-color: #fed7aa;
        /* orange-200 */
        color: #c05621;
        /* orange-800 */
    }
</style>
