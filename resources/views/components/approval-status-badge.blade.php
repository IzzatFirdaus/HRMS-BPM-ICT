{{-- resources/views/components/approval-status-badge.blade.php --}}
@props(['status'])

@php
    // Determine the badge color based on the status
    $badgeColor = match ($status) {
        'pending' => 'badge-warning', // Yellow for pending
        'approved' => 'badge-success', // Green for approved
        'rejected' => 'badge-danger', // Red for rejected
        default => 'badge-secondary', // Gray for any other status
    };

    // Format the status text for display
    $formattedStatus = ucfirst(str_replace('_', ' ', $status));
@endphp

{{-- Render the badge --}}
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

    .badge-info {
        /* Blue for approved in some contexts (e.g., email app approved but not completed) */
        background-color: #bfdbfe;
        /* blue-200 */
        color: #1e40af;
        /* blue-800 */
    }

    .badge-success {
        /* Green */
        background-color: #d1fae5;
        /* green-100 */
        color: #065f46;
        /* green-800 */
    }

    .badge-warning {
        /* Yellow */
        background-color: #fef3c7;
        /* yellow-100 */
        color: #b45309;
        /* yellow-800 */
    }

    .badge-danger {
        /* Red */
        background-color: #fee2e2;
        /* red-100 */
        color: #991b1b;
        /* red-800 */
    }

    .badge-secondary {
        /* Gray */
        background-color: #e5e7eb;
        /* gray-200 */
        color: #374151;
        /* gray-700 */
    }

    /* Add other specific badge styles if needed for loan statuses etc. */
    /* Example:
     .badge-teal {
        background-color: #b2f5ea;
        color: #2c7a7b;
     }
     */
</style>
