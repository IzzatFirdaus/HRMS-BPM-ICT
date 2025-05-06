{{-- resources/views/livewire/human-resource/Messages.blade.php --}}

{{-- Livewire component views should have a single root HTML element. --}}
{{-- Do NOT use @extends, @section, @push directives directly in the Livewire component view. --}}
{{-- Layout elements (title, styles, scripts, breadcrumbs, alerts) should be managed by the main layout file --}}
{{-- (e.g., layouts/app.blade.php) or included partials within the layout. --}}

@php
    // Import Storage Facade for file existence checks and URL generation
    use Illuminate\Support\Facades\Storage;
    // Import Auth Facade if needed for authenticated user checks
    use Illuminate\Support\Facades\Auth;
    // Import Employee Model if needed for type hinting or other purposes not shown
    // use App\Models\Employee; // Uncomment if Employee model is used for type hinting in the view
    // Remove if Helper::appClasses() is not used in the component view itself
    // $configData = Helper::appClasses();
@endphp

{{-- Wrap the entire component content in a single root element --}}
<div>

    {{-- Breadcrumbs (If breadcrumbs are handled by the layout, remove this block) --}}
    {{-- If rendering breadcrumbs within the component, keep this block --}}
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a>
            </li>
            <li class="breadcrumb-item active">{{ __('Human Resource') }}</li>
            <li class="breadcrumb-item active">{{ __('Messages') }}</li>
        </ol>
    </nav>

    {{-- Alerts (If alerts are handled by the layout, remove this block) --}}
    {{-- If rendering alerts within the component, keep this include --}}
    {{-- Assumes _partials/_alerts/alert-general.blade.php exists --}}
    @include('_partials/_alerts/alert-general')

    {{-- Main content row or container --}}
    <div class="row">

        {{-- Discount SMS Generation Card (Example from snippet) --}}
        <div class="col-12 col-lg-4"> {{-- Adjusted column class for responsive layout --}}
            <div class="card bg-primary text-white mb-3">
                {{-- <div class="card-header">Header</div> --}}
                <div class="card-body d-flex justify-content-between align-items-center">
                    <h5 class="card-title text-white">{{ __('Generate Discounts SMS') }}</h5>
                    <div class="card-icon cursor-pointer">
                        {{-- Assuming this is a trigger for some action, e.g., a modal --}}
                        <i class="ti ti-message-dots"></i>
                    </div>
                </div>
                {{-- Card Footer or other content if needed --}}
            </div>
            {{-- Add Select2 for batch selection if applicable --}}
            @if (!empty($batches)) {{-- Assuming $batches is a public property in your Livewire component --}}
                <div class="mb-3">
                    <label for="select2Batches"
                        class="form-label text-muted">{{ __('Select Batch for Discount SMS') }}</label>
                    {{-- Use wire:model for Livewire binding --}}
                    {{-- data-select2 is used by the JS init script --}}
                    <select id="select2Batches" class="select2 form-select" wire:model="selectedBatch"
                        data-placeholder="{{ __('Select Batch') }}">
                        <option value=""></option> {{-- Empty option for placeholder --}}
                        @foreach ($batches as $batchValue)
                            {{-- Assuming $batches is a simple array of batch identifiers --}}
                            <option value="{{ $batchValue }}">{{ $batchValue }}</option>
                        @endforeach
                    </select>
                    @error('selectedBatch')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
                @if ($selectedBatch)
                    {{-- Only show button if a batch is selected --}}
                    <button wire:click="generateDiscountSms"
                        class="btn btn-primary mb-3">{{ __('Generate SMS for Batch') }} {{ $selectedBatch }}</button>
                @endif
            @else
                <div class="mb-3 text-center text-muted">{{ __('No batches available for Discount SMS.') }}</div>
            @endif
        </div>

        {{-- Main Chat Area (right side when sidebar is visible, or full width otherwise) --}}
        {{-- Adjust column classes based on your desired responsive layout for chat area --}}
        <div class="col-12 col-lg-8">
            <div class="app-chat"> {{-- Assumes this class is part of your chat layout --}}

                {{-- Chat Header --}}
                <div class="chat-info-header">
                    {{-- Header content goes here, eg., selected user's name --}}
                    @if ($selectedEmployee)
                        {{-- Assuming $selectedEmployee is a public property populated on employee selection --}}
                        <div class="d-flex align-items-center">
                            {{-- Selected Employee's Avatar --}}
                            <div class="flex-shrink-0 avatar avatar-online me-3">
                                {{-- Display selected employee's avatar --}}
                                {{-- Check if path exists and file exists, otherwise use default --}}
                                {{-- Using employee_photo_path accessor from Employee model if available --}}
                                {{-- Otherwise, use the direct Storage check --}}
                                <img src="{{ $selectedEmployee->employee_photo_path ?? ($selectedEmployee->profile_photo_path && Storage::disk('public')->exists($selectedEmployee->profile_photo_path) ? Storage::disk('public')->url($selectedEmployee->profile_photo_path) : Storage::disk('public')->url('profile-photos/.default-photo.jpg')) }}"
                                    alt="{{ $selectedEmployee->full_name ?? 'Avatar' }}"
                                    class="rounded-circle object-cover w-full h-full"> {{-- Added object-cover --}}
                            </div>
                            {{-- Selected Employee's Info --}}
                            <div class="chat-contact-info flex-grow-1">
                                <h6 class="chat-contact-name text-truncate m-0">
                                    {{ $selectedEmployee->full_name ?? $selectedEmployee->name }}</h6>
                                {{-- Use full_name with fallback --}}
                                {{-- Use current_position accessor from Employee model if available --}}
                                {{-- Otherwise, use the direct relationship access with nullsafe operator --}}
                                <small
                                    class="chat-contact-status text-muted">{{ $selectedEmployee->current_position ?? ($selectedEmployee->currentTimeline?->position?->name ?? 'N/A') }}</small>
                            </div>
                        </div>
                    @else
                        {{-- Message when no employee is selected --}}
                        <div class="text-center text-muted">{{ __('Select an employee to start chatting') }}</div>
                    @endif
                </div> {{-- End chat-info-header --}}

                <hr class="container-m-nx m-0"> {{-- Separator --}}

                {{-- Chat History Body --}}
                <div class="chat-history-body">
                    {{-- This area would typically contain the messages --}}
                    {{-- You'll likely loop through messages here --}}
                    @if ($selectedEmployee)
                        {{-- Example: Loop through messages related to $selectedEmployee --}}
                        {{-- Assuming a $messages property with messages for the selected employee --}}
                        {{-- @forelse ($messages as $message)
                          <div class="chat-message {{ $message->sender_id == Auth::id() ? 'chat-message-right' : 'chat-message-left' }}">
                               <div class="chat-message-wrapper">
                                   <div class="chat-message-text">
                                       <p class="mb-0">{{ $message->content }}</p>
                                   </div>
                                   <div class="chat-message-time">{{ optional($message->created_at)->format('H:i') }}</div>
                               </div>
                           </div>
                      @empty
                           <div class="text-center text-muted mt-4">{{ __('No messages yet. Start the conversation!') }}</div>
                      @endforelse --}}
                        <div class="text-center text-muted mt-4">
                            {{ __('Message display area (implement message loop here)') }}</div>
                    @else
                        <div class="text-center text-muted mt-4">
                            {{ __('Chat history will appear here after selecting an employee.') }}</div>
                    @endif
                </div> {{-- End chat-history-body --}}

                {{-- Chat Message Send Area --}}
                <div class="chat-message-send">
                    {{-- Input field and send button --}}
                    @if ($selectedEmployee)
                        <div class="input-group input-group-merge">
                            {{-- Assuming messageContent is a public property for the message input --}}
                            {{-- wire:model.live updates the property as the user types --}}
                            <textarea class="form-control chat-input" placeholder="{{ __('Type your message here') }}" rows="1"
                                wire:model.live="messageContent"></textarea>
                            {{-- wire:click calls the sendMessage method in the Livewire component --}}
                            <button class="btn btn-primary d-flex align-items-center justify-content-center"
                                wire:click="sendMessage">
                                <i class="ti ti-send me-1"></i> {{-- Send icon --}}
                                <span class="d-none d-lg-inline-block">{{ __('Send') }}</span>
                            </button>
                        </div>
                    @else
                        {{-- Message when no employee is selected --}}
                        <div class="text-center text-muted">{{ __('Select an employee to send a message.') }}</div>
                    @endif
                </div> {{-- End chat-message-send --}}

            </div> {{-- End app-chat --}}
        </div> {{-- End main chat area column --}}


        {{-- Left Sidebar (Contact List) --}}
        {{-- This sidebar is likely positioned absolutely or fixed for chat UI --}}
        {{-- Adjust positioning/display classes based on your CSS framework/setup --}}
        {{-- The visibility of this sidebar on smaller screens might be controlled by JS/CSS --}}
        <div class="app-chat-sidebar-left">
            <div class="chat-sidebar-left-content">

                {{-- Sidebar Header --}}
                <div class="chat-sidebar-header">
                    {{-- Assuming a profile/user info area here for the currently authenticated user --}}
                    @auth
                        <div class="d-flex align-items-center">
                            {{-- Auth User's Avatar --}}
                            <div class="flex-shrink-0 avatar avatar-online me-3">
                                {{-- Display auth user's avatar --}}
                                {{-- Using employee_photo_path accessor from Auth::user()->employee if available --}}
                                <img src="{{ optional(Auth::user()->employee)->employee_photo_path ?? (Auth::user()->profile_photo_path && Storage::disk('public')->exists(Auth::user()->profile_photo_path) ? Storage::disk('public')->url(Auth::user()->profile_photo_path) : Storage::disk('public')->url('profile-photos/.default-photo.jpg')) }}"
                                    alt="{{ Auth::user()->full_name ?? Auth::user()->name }}"
                                    class="rounded-circle object-cover w-full h-full"> {{-- Added object-cover --}}
                            </div>
                            {{-- Auth User's Info --}}
                            <div class="chat-contact-info flex-grow-1">
                                <h6 class="chat-contact-name text-truncate m-0">
                                    {{ Auth::user()->full_name ?? Auth::user()->name }}</h6>
                                <small class="chat-contact-status text-muted">{{ __('Online') }}</small>
                                {{-- Example status --}}
                            </div>
                        </div>
                    @endauth
                </div> {{-- End chat-sidebar-header --}}

                {{-- Sidebar Search --}}
                <div class="chat-sidebar-search">
                    <div class="input-group input-group-merge me-1">
                        <span class="input-group-text" id="basic-addon-search31"><i class="ti ti-search"></i></span>
                        {{-- wire:model.live updates the 'search' public property in the component as user types --}}
                        <input type="text" class="form-control chat-sidebar-input"
                            placeholder="{{ __('Search Employees...') }}" aria-label="Search Messages"
                            aria-describedby="basic-addon-search31" wire:model.live="search">
                    </div>
                </div> {{-- End chat-sidebar-search --}}

                {{-- Close button for smaller screens --}}
                {{-- This button might be controlled by JS/CSS framework, adjust data attributes as needed --}}
                <i class="ti ti-x cursor-pointer mt-2 me-1 d-lg-none d-block position-absolute top-0 end-0" data-overlay
                    data-bs-toggle="sidebar" data-target="#app-chat-contacts"></i>

                <hr class="container-m-nx m-0">

                {{-- Sidebar Body (Employee Contact List) --}}
                <div class="sidebar-body"> {{-- This element likely has a fixed height and overflow-y: auto for scrolling --}}
                    <ul class="list-unstyled chat-contact-list mb-0" id="contact-list">
                        <li class="chat-contact-list-item chat-contact-list-item-title">
                            <h5 class="text-primary mb-0">{{ __('Employees') }}</h5> {{-- Translated title --}}
                        </li>

                        {{-- Loop through employees --}}
                        {{-- Assumes $employees is a public property containing employees to display --}}
                        @forelse ($employees as $employee)
                            {{-- wire:key is important for Livewire list rendering performance --}}
                            {{-- wire:click to select an employee. Pass the employee ID for efficiency. --}}
                            <div wire:key="employee-{{ $employee->id }}"
                                wire:click.prevent='selectEmployee({{ $employee->id }})'>
                                {{-- Apply 'active' class if this employee is currently selected --}}
                                {{-- Use optional($selectedEmployee)->id for safe checking if $selectedEmployee is null --}}
                                <li
                                    class="chat-contact-list-item {{ optional($selectedEmployee)->id == $employee->id ? 'active' : '' }}">
                                    <a class="d-flex align-items-center">
                                        <div class="flex-shrink-0 avatar avatar-online">
                                            {{-- Display employee avatar --}}
                                            {{-- Using employee_photo_path accessor from Employee model if available --}}
                                            {{-- Otherwise, use the corrected direct Storage check --}}
                                            <img src="{{ $employee->employee_photo_path ?? ($employee->profile_photo_path && Storage::disk('public')->exists($employee->profile_photo_path) ? Storage::disk('public')->url($employee->profile_photo_path) : Storage::disk('public')->url('profile-photos/.default-photo.jpg')) }}"
                                                alt="{{ $employee->full_name ?? 'Avatar' }}"
                                                class="rounded-circle object-cover w-full h-full">
                                            {{-- Added object-cover --}}
                                        </div>
                                        <div class="chat-contact-info flex-grow-1 ms-2">
                                            <h6 class="chat-contact-name text-truncate m-0">
                                                {{ $employee->full_name ?? $employee->name }}</h6>
                                            {{-- Use full_name with fallback --}}
                                            {{-- Use current_position accessor from Employee model if available --}}
                                            {{-- Otherwise, use the direct relationship access with nullsafe operator --}}
                                            <p class="chat-contact-status text-muted text-truncate mb-0">
                                                {{ $employee->current_position ?? ($employee->currentTimeline?->position?->name ?? 'N/A') }}
                                            </p>
                                        </div>
                                        {{-- Display Employee ID --}}
                                        <small class="text-muted mb-auto">{{ __('ID:') }}
                                            {{ $employee->id }}</small>
                                    </a>
                                </li>
                            </div>
                        @empty
                            {{-- Displayed when the $employees collection is empty --}}
                            <h6 style="text-align: center" class="text-muted mb-0">{{ "No One's Found!" }}</h6>
                            {{-- Translated empty state --}}
                        @endforelse

                    </ul>
                </div> {{-- End sidebar-body --}}

            </div> {{-- End chat-sidebar-left-content --}}
        </div> {{-- End app-chat-sidebar-left --}}

    </div> {{-- End main row --}}

    {{-- Note: Scripts and styles sections should be managed by the layout, NOT the Livewire component view --}}
    {{-- The following script blocks have been commented out or modified to fit this pattern --}}

    {{-- Original vendor-script and page-script sections removed --}}
    {{-- Inline script for Perfect Scrollbar and Select2 initialization --}}
    {{-- This script block should ideally be in a separate JS file or inline in your main layout file, --}}
    {{-- and included AFTER jQuery, Perfect Scrollbar, and Select2 JS libraries are loaded. --}}
    {{-- For now, keeping it inside the component's root div for debugging, but it's NOT standard practice. --}}
    {{-- Consider moving this to a dedicated JS file loaded by your layout. --}}
    <script>
        'use strict';

        document.addEventListener('livewire:initialized', () => {
            // Perfect Scrollbar initialization for chat contacts and history
            const chatContactsBody = document.querySelector('.app-chat-contacts .sidebar-body'),
                chatHistoryBody = document.querySelector('.chat-history-body');

            // Chat contacts scrollbar
            if (chatContactsBody) {
                new PerfectScrollbar(chatContactsBody, {
                    wheelSpeed: 0.2,
                    wheelPropagation: false,
                    suppressScrollX: true
                });
            }

            // Chat history scrollbar
            if (chatHistoryBody) {
                new PerfectScrollbar(chatHistoryBody, {
                    wheelSpeed: 0.2,
                    wheelPropagation: false,
                    suppressScrollX: true
                });
            }

            // Scroll to bottom function (optional - useful for initial load)
            if (chatHistoryBody) { // Check if element exists before scrolling
                function scrollToBottom() {
                    chatHistoryBody.scrollTo(0, chatHistoryBody.scrollHeight);
                }
                scrollToBottom();
            }
            // Note: For new messages arriving, you'll need to call scrollToBottom()
            // after the messages are rendered, possibly via a Livewire event listener.
        });


        $(function() {
            // Assuming you are using jQuery for Select2 initialization
            const selectPicker = $('.selectpicker'), // Assuming selectpicker might exist
                select2 = $('.select2');

            // Default Select2 initialization
            if (select2.length) {
                select2.each(function() {
                    var $this = $(this);
                    $this.wrap('<div class="position-relative"></div>').select2({
                        dropdownParent: $this
                    .parent(), // Attach dropdown to the wrapped parent for correct positioning
                        placeholder: $this.data('placeholder'), // Use data-placeholder attribute
                        allowClear: true // Allow clearing the selection
                    });
                });
            }

            // Example of handling Select2 change event with Livewire
            // If you use wire:model directly on the select, this might not be needed.
            // But if you have custom Select2 logic or need to manually set Livewire properties:
            $('#select2Batches').on('change', function(e) {
                var data = $('#select2Batches').select2("val");
                // Call Livewire component method to set the selected batch property
                @this.set('selectedBatch', data);
            });
        });
    </script>

</div> {{-- End of the single root element --}}
