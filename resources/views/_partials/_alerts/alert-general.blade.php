{{--
    This Blade partial displays flash messages stored in the session.
    It checks for 'success', 'info', 'warning', and 'error' session keys.
    It assumes you are using Bootstrap 5 alert classes and Tabler Icons (ti ti-*)
    for icons.
--}}

@php
    // Define the types of alerts to check for and their corresponding Bootstrap classes and icons.
    $alertTypes = ['success', 'info', 'warning', 'error'];

    $alertClasses = [
        'success' => 'alert-success text-success ti ti-check',
        'info' => 'alert-info text-info ti ti-info-circle',
        'warning' => 'alert-warning text-warning ti ti-alert-triangle',
        'error' => 'alert-danger text-danger ti ti-ban', // Map 'error' session key to Bootstrap 'danger' class
    ];
@endphp

{{-- Loop through each defined alert type --}}
@foreach ($alertTypes as $type)
    {{-- Check if a flash message with this type exists in the session --}}
    @if (session()->has($type))
        {{-- Get the corresponding Bootstrap class and icon from the mapping --}}
        @php
            $classes = explode(' ', $alertClasses[$type]);
            $alertClass = $classes[0]; // e.g., alert-success
            $textColor = $classes[1]; // e.g., text-success
            $iconClass = $classes[2] . ' ' . $classes[3]; // e.g., ti ti-check
        @endphp

        {{-- Display the alert div --}}
        <div class="alert {{ $alertClass }} alert-dismissible d-flex align-items-baseline" role="alert">
            {{-- Alert icon --}}
            <span class="alert-icon alert-icon-lg {{ $textColor }} me-2">
                <i class="{{ $iconClass }} ti-sm"></i>
            </span>
            {{-- Alert message content --}}
            <div class="d-flex flex-column ps-1">
                {{-- Optional: Add a heading based on the alert type --}}
                {{-- <h5 class="alert-heading mb-2">{{ __(ucfirst($type)) }}!</h5> --}}
                {{-- Display the actual message from the session --}}
                <p class="mb-0">{{ session($type) }}</p>
                {{-- Dismiss button --}}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
    @endif
@endforeach

{{--
    Note: This code handles session flash messages set like:
    return redirect()->back()->with('success', 'Operation successful!');
    return redirect()->route('dashboard')->with('error', 'Something went wrong.');
    return redirect()->back()->with('warning', 'Action required.');
    return redirect()->back()->with('info', 'Please note this information.');
--}}
