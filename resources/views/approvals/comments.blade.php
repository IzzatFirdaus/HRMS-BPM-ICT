{{-- resources/views/approvals/comments.blade.php --}}
{{--
    This partial is used to display the comments associated with an approval record.
    It expects an $approval variable to be passed to it, which is an instance of the Approval model
    or an object/array with a 'comments' property/key.
--}}

{{-- Ensure the $approval variable is provided and is not null before attempting to access its properties --}}
@if (isset($approval) && $approval !== null)
    <div class="mb-4"> {{-- Add some bottom margin --}}
        {{-- Use localization for the label --}}
        <p class="text-lg font-semibold mb-2 text-gray-700">{{ __('Catatan Pegawai:') }}</p>

        {{-- Check if the 'comments' property is not empty (handles null, '', false, etc.) --}}
        @if (!empty($approval->comments))
            {{-- Display the comments --}}
            <div class="bg-gray-100 p-4 rounded-md text-gray-800"> {{-- Style comments block --}}
                {{ $approval->comments }}
            </div>
        @else
            {{-- Display a message if no comments are available or comments property is empty --}}
            <p class="text-gray-600 italic">{{ __('Tiada catatan disediakan.') }}</p>
        @endif
    </div>
@else
    {{-- Optional: You could render nothing or an error message here if $approval is not provided/null --}}
    {{-- <div class="mb-4 text-danger">{{ __('Error: Approval data missing.') }}</div> --}}
@endif
