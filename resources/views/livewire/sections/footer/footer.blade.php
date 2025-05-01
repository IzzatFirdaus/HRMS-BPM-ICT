<div>
    {{-- Footer section with theme-specific classes --}}
    <footer class="content-footer footer bg-footer-theme">
        {{-- Container for footer content, uses the passed $containerNav variable with a fallback --}}
        <div class="{{ !empty($containerNav) ? $containerNav : 'container-fluid' }}">
            {{-- Flex container for footer content, aligns items and justifies content --}}
            <div class="footer-container d-flex align-items-center justify-content-between py-2 flex-md-row flex-column">
                {{-- Left-aligned content (copyright, made with, IT Department link, descriptive phrase) --}}
                <div style="text-align: center">
                    &copy; {{-- Copyright symbol --}}
                    <script>
                        // JavaScript to display the current year.
                        // Alternative: Use PHP's date('Y') for a server-side approach.
                        document.write(new Date().getFullYear())
                    </script>
                    , {{ __('made with') }} ❤️ {{-- Translated "made with" text and heart icon --}}
                    {{-- Link to the IT Department (assuming 'contact-us' route exists), translated text --}}
                    <a href="{{ route('contact-us') }}" target="_blank" class="fw-semibold">{{ __('IT Department') }}</a>
                    {{-- Translated descriptive phrase --}}
                    {{ __('for a better work environment.') }}
                </div>
                {{-- Right-aligned content (Data system link) --}}
                <div>
                    {{-- Link to an external "Data system" website --}}
                    <a href="https://data.namaa.sy/" target="_blank"
                        class="footer-link d-none d-sm-inline-block">{{ __('Data system') }}</a> {{-- Translated link text, hidden on small screens --}}
                </div>
            </div> {{-- End footer-container --}}
        </div> {{-- End container div --}}
    </footer> {{-- End footer --}}
</div> {{-- End Livewire component root element --}}
