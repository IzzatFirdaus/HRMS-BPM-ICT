{{--
    Partial for a password input field with toggle.

    Parameters:
    - $name: The input field's name and ID.
    - $label: The label text (should be translated).
    - $required: Boolean indicating if the field is required (default false).
    - $hint: Optional hint text below the input.
--}}
<div class="form-group">
    <label for="{{ $name }}" class="form-label">{{ $label }}</label>
    <div class="relative">
        <input type="password" name="{{ $name }}" id="{{ $name }}"
            class="form-input @error($name) form-input-error @enderror" {{ $required ?? false ? 'required' : '' }}
            autocomplete="new-password">
        <button type="button"
            class="password-toggle absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600"
            {{-- Adjusted position and alignment --}} data-target="{{ $name }}" aria-label="{{ __('Toggle password visibility') }}">
            {{-- Added ARIA label --}}
            {{-- SVG icons needed here (e.g., Tabler Icons 'ti-eye' and 'ti-eye-off') --}}
            {{-- Example: <svg class="ti ti-eye w-5 h-5"></svg> --}}
            <svg xmlns="http://www.w3.org/2000/svg" class="ti ti-eye w-5 h-5" width="24" height="24"
                viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round"
                stroke-linejoin="round">
                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                <path d="M10 12a2 2 0 1 0 4 0a2 2 0 0 0 -4 0" />
                <path d="M21 12c-2.4 4 -5.4 6 -9 6c-3.6 0 -6.6 -2 -9 -6c2.4 -4 5.4 -6 9 -6c3.6 0 6.6 2 9 6" />
            </svg>
            <svg xmlns="http://www.w3.org/2000/svg" class="ti ti-eye-off w-5 h-5 hidden" width="24" height="24"
                viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round"
                stroke-linejoin="round">
                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                <path d="M3 3l18 18" />
                <path d="M10.584 10.587a2 2 0 0 0 2.828 2.83" />
                <path
                    d="M9.363 5.365a10.03 10.03 0 0 1 2.637 -0.365c3.6 0 6.6 2 9 6c-.646 1.39 -1.464 2.612 -2.496 3.633m-2.913 2.915a10.09 10.09 0 0 1 -7.591 2.452c-3.6 0 -6.6 -2 -9 -6c2.4 -4 5.4 -6 9 -6c1.09 0 2.18 .166 3.206 .493" />
            </svg>
        </button>
    </div>
    @if (isset($hint))
        <p class="text-sm text-gray-500 mt-1">{{ $hint }}</p>
    @endif
    @error($name)
        <p class="form-error">{{ $message }}</p>
    @enderror
</div>
