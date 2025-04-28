{{-- resources\views\_partials\_modals\modal-categoryInfo.blade.php --}}

{{-- Push custom CSS specific to this modal or jstree --}}
@push('custom-css')
    {{-- Link to jstree default CSS (adjust path if necessary) --}}
    {{-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jstree/3.3.12/themes/default/style.min.css" /> --}}
    {{-- Or use your theme's jstree CSS like 'default-dark' --}}
    {{-- Example using a local asset: --}}
    {{-- <link rel="stylesheet" href="{{ asset('assets/vendor/libs/jstree/jstree.css') }}" /> --}}

    <style>
        /* Optional: Adjust jstree appearance */
        .jstree-default-dark .jstree-anchor {
            color: #6f6b7d;
        }

        /* Default text color */
        .jstree-default-dark .jstree-hovered {
            background-color: #e7e7e7 !important;
        }

        /* Hover background */
        .jstree-default-dark .jstree-clicked {
            background-color: #dcdcdc !important;
            color: #000 !important;
        }

        /* Clicked background/text */
        /* Adjust margins/padding if needed to fit your modal */
        .modal-body .jstree {
            padding-top: 15px;
            padding-bottom: 15px;
        }
    </style>
@endpush

{{--
    Bootstrap Modal Structure for Category Information (Treeview)
    wire:ignore.self: Essential for Livewire to leave Bootstrap's modal handling to Bootstrap JS.
    id: The ID used to target the modal with Bootstrap JS.
    tabindex="-1": Makes the modal accessible.
    aria-hidden="true": Hides the modal from assistive technologies when closed.
--}}
<div wire:ignore.self class="modal fade" id="categoryInfoModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-simple"> {{-- modal-simple or other Bootstrap sizing like modal-lg --}}
        <div class="modal-content">
            {{-- Modal Body contains the main content --}}
            <div class="modal-body">
                {{-- Close button inside the body for simpler modals --}}
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>

                {{-- Optional: Add a title or description here --}}
                <div class="text-center mb-4">
                    {{-- Display the name of the category being viewed --}}
                    <h3 class="mb-2">{{ $categoryInfo->name ?? __('Category Info') }}</h3>
                    <p class="text-muted">{{ __('Subcategories List') }}</p> {{-- Changed text as it's a list of subcategories --}}
                </div>


                {{--
                    Container for the jstree.
                    The jstree library will initialize and render the tree inside this div.
                    Ensure this div has an ID that you can target with JavaScript.
                    The HTML structure inside will be the root node and its immediate children (subcategories).
                --}}
                <div id="jstree-categories" class="jstree jstree-default-dark" role="tree">
                    <ul class="jstree-container-ul jstree-children" role="group">
                        {{--
                            Render the root Category node.
                            Check if categoryInfo is loaded and has subCategories loaded.
                        --}}
                        @if ($categoryInfo)
                            {{-- Main Category Node (Level 1) --}}
                            <li role="none" data-jstree='{"icon" : "ti ti-folder"}' {{-- Icon for the main category --}}
                                id="category-{{ $categoryInfo->id }}"
                                class="jstree-node @if ($categoryInfo->subCategories && $categoryInfo->subCategories->count() > 0) jstree-closed @else jstree-leaf @endif">
                                <i class="jstree-icon jstree-ocl" role="presentation"></i>
                                <a class="jstree-anchor" href="#" tabindex="-1" role="treeitem"
                                    aria-selected="false" aria-level="1"
                                    @if ($categoryInfo->subCategories && $categoryInfo->subCategories->count() > 0) aria-expanded="false" @endif
                                    id="category-{{ $categoryInfo->id }}-anchor">
                                    <i class="jstree-icon jstree-themeicon ti ti-folder jstree-themeicon-custom"
                                        role="presentation"></i>
                                    {{ $categoryInfo->name }} @if ($categoryInfo->subCategories && $categoryInfo->subCategories->count() > 0)
                                        ({{ $categoryInfo->subCategories->count() }})
                                    @endif
                                </a>

                                {{-- Nested UL for SubCategory children (Level 2) --}}
                                @if ($categoryInfo->subCategories && $categoryInfo->subCategories->count() > 0)
                                    <ul role="group" class="jstree-children">
                                        {{-- Loop through each SubCategory related to this Category --}}
                                        @foreach ($categoryInfo->subCategories as $subCategory)
                                            {{-- SubCategory Node (Level 2) --}}
                                            {{-- Subcategories are leaf nodes in this structure --}}
                                            <li role="none" data-jstree='{"icon" : "ti ti-file-text"}'
                                                {{-- Use a different icon for leaf nodes if desired --}} id="subcategory-{{ $subCategory->id }}"
                                                {{-- Unique ID for subcategory node --}} class="jstree-node jstree-leaf"
                                                {{-- Subcategories are leaves --}}>
                                                <i class="jstree-icon jstree-ocl" role="presentation"></i>
                                                <a class="jstree-anchor" href="#" tabindex="-1" role="treeitem"
                                                    aria-selected="false" aria-level="2"
                                                    id="subcategory-{{ $subCategory->id }}-anchor">
                                                    <i class="jstree-icon jstree-themeicon ti ti-file-text jstree-themeicon-custom"
                                                        role="presentation"></i>
                                                    {{ $subCategory->name }}
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif

                            </li> {{-- End of Main Category Node --}}
                        @else
                            {{-- Optional: Display a message if category data is not loaded --}}
                            <li>{{ __('Loading category information...') }}</li>
                        @endif
                    </ul>
                </div>

                {{-- Optional: Modal Footer --}}
            </div>
        </div>
    </div>
</div>

{{-- Push custom JavaScript specific to this modal or jstree --}}
@push('custom-scripts')
    {{--
        Link to jstree library JavaScript file (adjust path if necessary).
        Ensure this is loaded AFTER jQuery.
    --}}
    {{-- <script src="https://cdnjs.cloudflare.com/ajax/libs/jstree/3.3.12/jstree.min.js"></script> --}}
    {{-- Example using a local asset: --}}
    {{-- <script src="{{ asset('assets/vendor/libs/jstree/jstree.js') }}"></script> --}}


    <script>
        // Function to initialize jstree
        function initializeJstree(modalElement) {
            console.log('Attempting to initialize jstree...');
            // Ensure the jstree container exists within the modal
            const jstreeContainer = modalElement.querySelector('#jstree-categories');
            if (!jstreeContainer) {
                console.error('Jstree container #jstree-categories not found in modal.');
                return;
            }

            // Destroy any existing jstree instance before initializing a new one
            // This is important if the modal content is loaded once but shown/hidden multiple times
            if ($(jstreeContainer).hasClass('jstree')) {
                console.log('Destroying existing jstree instance...');
                $(jstreeContainer).jstree('destroy');
                console.log('Existing jstree instance destroyed.');
            } else {
                console.log('No existing jstree instance to destroy.');
            }

            // Initialize jstree on the container div #jstree-categories
            $(jstreeContainer).jstree({
                "core": {
                    "themes": {
                        "name": "default-dark", // Use your theme name
                        "responsive": true,
                        "variant": "large" // Optional: Use large variant for more spacing
                    },
                    // Data is loaded from the HTML UL/LI structure already present in the div.
                    // If you were loading from JSON, you would specify a 'data' array/URL here.
                    // "data": [...] // Example if loading from JSON
                },
                "types": {
                    // Define default type for nodes. This applies to all nodes unless overridden.
                    "default": {
                        "icon": "ti ti-folder" // Default icon using Tabler Icons
                    },
                    // You could define other types here if you had different node kinds (e.g., "file", "category", "subcategory")
                    "leaf": { // Example type for leaf nodes
                        "icon": "ti ti-file-text"
                    }
                },
                "plugins": ["types"] // Enable the 'types' plugin to use the "types" configuration above
                // Add other plugins like "wholerow", "state", "dnd", etc., if needed
            });

            console.log('Jstree initialization complete.');

            // Optional: Listen to jstree events if needed (e.g., when a node is selected)
            // $(jstreeContainer).on('select_node.jstree', function (e, data) {
            //    console.log('Selected node ID: ' + data.selected[0]);
            //    console.log('Selected node text: ' + data.instance.get_node(data.selected[0]).text);
            //    // Example: Emit a Livewire event passing the node ID
            //    // @this.dispatch('jstreeNodeSelected', { nodeId: data.selected[0] }); // Dispatch to any component listening
            //    // Or if targeting a specific component instance:
            //    // Livewire.find('YOUR_COMPONENT_ID').call('nodeSelected', data.selected[0]);
            // });

            // Optional: Auto-open the first level node(s) after load
            $(jstreeContainer).on('ready.jstree', function() {
                $(this).jstree('open_node', $('#jstree-categories > ul > li'));
            });

        }

        // Get the modal element by its ID
        const categoryInfoModalElement = document.getElementById('categoryInfoModal');

        // Add event listener to initialize jstree when the modal is fully shown (after transitions)
        if (categoryInfoModalElement) {
            categoryInfoModalElement.addEventListener('shown.bs.modal', function(event) {
                console.log('Category Info Modal shown event triggered.');
                // Ensure the Livewire component has loaded the data before initializing jstree.
                // If categoryInfo might be null initially, you might need to wait for a Livewire event
                // signaling that data is ready, or check the presence of the UL inside the jstree div.
                // For this structure, the UL/LI is always present if $categoryInfo is not null.
                initializeJstree(categoryInfoModalElement);
            });

            // Optional: Add event listener to destroy jstree when the modal is hidden (before transitions)
            // This helps clean up resources and prepare for potential future re-initialization.
            categoryInfoModalElement.addEventListener('hidden.bs.modal', function(event) {
                console.log('Category Info Modal hidden event triggered.');
                // Destroy the jstree instance
                const jstreeContainer = categoryInfoModalElement.querySelector('#jstree-categories');
                if (jstreeContainer && $(jstreeContainer).hasClass('jstree')) {
                    console.log('Destroying jstree instance...');
                    $(jstreeContainer).jstree('destroy');
                    console.log('Jstree instance destroyed.');
                }
            });

        } else {
            console.error('Category Info Modal element with ID #categoryInfoModal not found.');
        }
    </script>
@endpush
