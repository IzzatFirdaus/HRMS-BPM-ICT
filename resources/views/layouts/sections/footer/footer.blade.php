<footer class="content-footer footer bg-footer-theme">
    {{-- Container for the footer content, uses the passed $containerNav variable with a fallback --}}
    <div class="{{ !empty($containerNav) ? $containerNav : 'container-fluid' }}">
        {{-- Flex container for footer content, aligns items and justifies content --}}
        <div class="footer-container d-flex align-items-center justify-content-between py-2 flex-md-row flex-column">
            {{-- Left-aligned content (copyright, made with, IT Department link) --}}
            <div>
                &copy; {{-- Copyright symbol --}}
                <script>
                    // JavaScript to display the current year.
                    document.write(new Date().getFullYear())
                </script>
                {{-- Updated text for 'made with ❤️ by' --}}
                , dibuat dengan ❤️ oleh
                {{-- Updated link and text for the IT Department --}}
                {{-- Ensure 'resource-management.it-department.contact' route exists if using a named route, otherwise use a direct URL --}}
                {{-- Assuming a direct URL for now based on the previous code's 'http://namaa.sy/' --}}
                <a href="http://namaa.sy/" target="_blank" class="fw-semibold">Bahagian Pengurusan Maklumat (BPM)</a>
            </div>
            {{-- Right-aligned content (Data system and Namaa links) --}}
            <div>
                {{-- Link to the Data system website --}}
                <a href="https://data.namaa.sy/" target="_blank" class="footer-link me-4">Sistem Data</a>
                {{-- Link to the Namaa website, hidden on small screens --}}
                <a href="http://namaa.sy/" target="_blank" class="footer-link d-none d-sm-inline-block">Namaa</a>
            </div>
        </div> {{-- End footer-container --}}
    </div> {{-- End container div --}}
</footer> {{-- End footer --}}
