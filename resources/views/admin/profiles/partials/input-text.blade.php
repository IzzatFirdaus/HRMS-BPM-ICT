{{--
    Partial for a text input field.

    Parameters:
    - $name: The input field's name and ID.
    - $label: The label text (should be translated).
    - $type: The input type (default 'text').
    - $required: Boolean indicating if the field is required (default false).
    - $value: The input's initial value (uses old() or Auth::user()->{$name}).
--}}
<div class="form-group">
    <label for="{{ $name }}" class="form-label">{{ $label }}</label>
    <input type="{{ $type ?? 'text' }}" name="{{ $name }}" id="{{ $name }}"
        class="form-input @error($name) form-input-error @enderror" value="{{ old($name, $value ?? '') }}"
        {{-- Use passed $value --}} {{ $required ?? false ? 'required' : '' }}>
    @error($name)
        <p class="form-error">{{ $message }}</p>
    @enderror
</div>
