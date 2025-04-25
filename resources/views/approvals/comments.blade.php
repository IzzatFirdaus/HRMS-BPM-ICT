{{-- resources/views/approvals/comments.blade.php --}}
{{--
    This partial is used to display the comments associated with an approval record.
    It expects an $approval variable to be passed to it, which is an instance of the Approval model.
--}}

<div class="mb-4"> {{-- Add some bottom margin --}}
    <p class="text-lg font-semibold mb-2 text-gray-700">Catatan Pegawai:</p> {{-- Label for comments --}}

    @if ($approval->comments)
        {{-- Display the comments if they exist --}}
        <div class="bg-gray-100 p-4 rounded-md text-gray-800"> {{-- Style comments block --}}
            {{ $approval->comments }}
        </div>
    @else
        {{-- Display a message if no comments are available --}}
        <p class="text-gray-600 italic">Tiada catatan disediakan.</p>
    @endif
</div>
