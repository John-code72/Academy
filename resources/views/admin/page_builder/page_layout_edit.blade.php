<!DOCTYPE html>
<html lang="en">

<head>
    {{ config(['app.name' => get_settings('system_title')]) }}
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ get_phrase('Edit Layout') }} | {{ config('app.name') }}</title>
    <meta content="{{ csrf_token() }}" name="csrf_token" />

    <link rel="shortcut icon" href="{{ asset(get_frontend_settings('favicon')) }}" />

    <!-- owl carousel -->
    <link rel="stylesheet" href="{{ asset('assets/frontend/default/css/owl.carousel.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/frontend/default/css/owl.theme.default.min.css') }}">

    <!-- Jquery Ui Css -->
    <link rel="stylesheet" href="{{ asset('assets/frontend/default/css/jquery-ui.css') }}">

    <!-- Nice Select Css -->
    <link rel="stylesheet" href="{{ asset('assets/frontend/default/css/nice-select.css') }}">

    <!-- Fontawasome Css -->
    <link rel="stylesheet" href="{{ asset('assets/frontend/default/css/all.min.css') }}">

    <!-- FlatIcons Css -->
    <link rel="stylesheet" href="{{ asset('assets/global/icons/uicons-bold-rounded/css/uicons-bold-rounded.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/global/icons/uicons-bold-straight/css/uicons-bold-straight.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/global/icons/uicons-regular-rounded/css/uicons-regular-rounded.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/global/icons/uicons-solid-rounded/css/uicons-solid-rounded.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/global/icons/uicons-solid-rounded/css/uicons-solid-rounded.css') }}" />

    <!-- Custom Fonts -->
    <link rel="stylesheet" href="{{ asset('assets/frontend/default/css/custome-front/custom-fronts.css') }}">

    <!-- Player Css -->
    <link rel="stylesheet" href="{{ asset('assets/frontend/default/css/plyr.css') }}">

    <!-- Player Css -->
    <link rel="stylesheet" href="{{ asset('assets/frontend/default/css/sweet_alert.css') }}">

    <!-- Bootstrap Css -->
    <link rel="stylesheet" href="{{ asset('assets/frontend/default/css/bootstrap.min.css') }}">

    <!-- Main Css -->
    <link rel="stylesheet" href="{{ asset('assets/frontend/default/css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/frontend/default/css/responsive.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/frontend/default/css/custom_style.css') }}">



    <!-- Jquery Js -->
    <script src="{{ asset('assets/frontend/default/js/jquery-3.7.1.min.js') }}"></script>

    <!-- Bootstrap Js -->
    <script src="{{ asset('assets/frontend/default/js/bootstrap.bundle.min.js') }}"></script>

    <!-- nice select js -->
    <script src="{{ asset('assets/frontend/default/js/jquery.nice-select.min.js') }}"></script>

    <!-- owl carousel js -->
    <script src="{{ asset('assets/frontend/default/js/owl.carousel.min.js') }}"></script>

    <!-- Player Js -->
    <script src="{{ asset('assets/frontend/default/js/plyr.js') }}"></script>

    <!-- Jquery Ui Js -->
    <script src="{{ asset('assets/frontend/default/js/jquery-ui.min.js') }}"></script>

    {{-- Jquery form --}}
    <script type="text/javascript" src="{{ asset('assets/global/jquery-form/jquery.form.min.js') }}"></script>

    <!-- price range Js -->
    <script src="{{ asset('assets/frontend/default/js/price_range_script.js') }}"></script>

    <!-- Main Js -->
    <script src="{{ asset('assets/frontend/default/js/script.js') }}"></script>
    <!-- toster file -->
    @include('frontend.default.toaster')

    <style>
        .text-30px {
            font-size: 30px;
        }
    </style>
</head>

<body>

    <!-- Builder bar -->
    <div id="editor_top_bar" class="editor_top_bar">
        <div class="container">
            <div class="row">
                <div class="col-4 py-3">
                    <div class="editor_title">{{ get_phrase('Page Builder') }}</div>
                </div>
                <div class="col-4 py-2 text-center">
                    <a class="btn btn-dark btn-lg mx-1 save-button" href="{{ route('admin.page.preview', $id) }}" target="_blank">
                        <i class="fi-rr-eye"> </i>{{ get_phrase('Preview') }}
                    </a>

                    <button class="btn btn-dark btn-lg mx-1 save-button" onclick="getDeveloperFileContent()">
                        <i class="fi-rr-arrow-alt-square-down"> </i>{{ get_phrase('Save') }}
                    </button>

                    <button class="btn btn-dark btn-lg mx-1 save-button" onclick="show_offcanvas('{{ route('view', ['path' => 'admin.page_builder.page_elements']) }}', '{{ get_phrase('Elements') }}')">
                        <i class="fi-rr-plus"> </i>{{ get_phrase('Add New Element') }}
                    </button>

                </div>
                <div class="col-4 py-2">
                    <a class="btn btn-dark btn-lg float-end mx-1" href="{{ route('admin.pages') }}">
                        <i class="fi-rr-angle-left"> </i>{{ get_phrase('Back') }}</a>
                </div>
            </div>
        </div>

    </div>

    <div id="main">
        @php $builder_file_names = App\Models\Builder_page::where('id', $id)->firstOrNew()->html; @endphp
        @foreach (json_decode($builder_file_names, true) ?? [] as $key => $builder_file_name)
            <div builder-block-identifier="{{ time() . rand(1000, 9999) . $key }}" builder-block-file-name="{{ $builder_file_name }}">
                @include('components.home_made_by_builder.' . $builder_file_name)
            </div>
        @endforeach
    </div>




    @include ('admin.page_builder.page_layout_edit_offcanvas')
    @include ('admin.toaster')
    <script>
        "use strict";

        // Save the edited layout into database
        // function save_layout(developer_elements, builder_elements) {
        function save_layout(builder_elements) {
            // Remove builder tool START
            // removes the options elements: buttons & borders
            $(".content_editor_buttons").remove();
            $('.builder-editable.initialized').removeClass('initialized');
            $('#main .builder-editable-wraper > .builder-editable').each(function(index, elem) {
                //To remove parent div (.builder-editable-wraper)
                $(this).unwrap();
            });
            // Remove builder tool END

            // Sending the ajax call
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                type: "POST",
                data: {
                    // developer_elements: developer_elements,
                    builder_elements: builder_elements,
                    id: {{ $id }}
                },
                url: "{{ route('admin.page.layout.update', ['id' => $id]) }}",
                success: function(msg) {
                    success('Layout updated');
                    // Re-initiate the builder
                    builder_initiated()
                },
                error: function(xhr, status, error) {
                    if (xhr.responseText && JSON.parse(xhr.responseText).errors && JSON.parse(xhr.responseText).errors[0] && JSON.parse(xhr.responseText).errors[0].message) {
                        error(JSON.parse(xhr.responseText).errors[0].message);
                    } else {
                        error('An error occurred while updating the layout');
                    }
                }
            });


        }


        function separateElementsByDom(html) {
            // Create a new DOMParser instance
            let parser = new DOMParser();
            let doc = parser.parseFromString(html, 'text/html');
            let builderElements = {};
            // console.log(html)
            // Find all elements with the builder-block-file-name attribute
            let nodes = doc.querySelectorAll('[builder-block-file-name]');
            nodes.forEach(node => {
                // console.log(node.innerHTML);
                // Find elements within each node with builder-identity attribute
                let nodeIdentities = node.querySelectorAll('[builder-identity]');
                let fileName = node.getAttribute('builder-block-file-name');

                // Initialize the fileName key in the builderElements object
                if (!builderElements[fileName]) {
                    builderElements[fileName] = {};
                }
                // console.log(nodeIdentities.length)
                if (nodeIdentities.length > 0) {
                    nodeIdentities.forEach(identityNode => {
                        // console.log(getElementPath(identityNode));

                        let identity = identityNode.getAttribute('builder-identity');

                        // Check for duplicate identity
                        if (builderElements[fileName][identity]) {
                            var errorMessage = `Duplicate builder-identity "${identity}" found in "${fileName}". Execution stopped. You can solve this issue by removing the "${fileName}" block and adding it again from the right sidebar.`;
                            error(errorMessage);
                            // Show an error message and stop execution
                            throw new Error(errorMessage);
                        }

                        builderElements[fileName][identity] = {
                            element: btoa(unescape(encodeURIComponent(identityNode.outerHTML))),
                            tag: btoa(unescape(encodeURIComponent(identityNode.tagName.toLowerCase()))),
                            identity: identity,
                            content: btoa(unescape(encodeURIComponent(identityNode.textContent))),
                            src: btoa(unescape(encodeURIComponent(identityNode.getAttribute('src')))),

                            // Those for drop area
                            dropAreaIndex: getElementPosition(identityNode)[0],
                            droppedIndex: getElementPosition(identityNode)[1],
                        };
                        // console.log(builderElements[fileName][identity])
                    });
                } else {
                    builderElements[fileName][1] = {
                        element: 'null',
                        tag: 'null',
                        identity: 'null',
                        content: 'null',
                        src: 'null',

                        // Those for drop area
                        dropAreaIndex: 'null',
                        droppedIndex: 'null',
                    };
                }
                // console.log(builderElements)
            });

            return builderElements;
        }

        function getDeveloperFileContent() {
            $(".remove-dropped-item").remove();

            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                type: "POST",
                url: "{{ route('admin.page.all.builder.developer.file') }}",
                success: function(developerhtml) {
                    var builder_html = document.querySelector('#main').innerHTML;
                    // var developer_elements = separateElementsByDom(developerhtml);
                    var builder_elements = separateElementsByDom(builder_html);
                    save_layout(builder_elements);
                    // save_layout(developer_elements, builder_elements);
                }
            });
        }

        function getElementPosition(el) {
            var dropAreaIndex = null;
            var droppedIndex = null;


            var dropArea = $(el).closest('.drop-area');
            var selectedFileArea = dropArea.closest('[builder-block-file-name]');

            if (dropArea) {
                dropAreaIndex = selectedFileArea.find('.drop-area').index(dropArea);
                droppedIndex = dropArea.find('*').index(el);
            }

            // console.log('Drop Area Index: ' + dropAreaIndex + ', Dropped Index: ' + droppedIndex);

            return [dropAreaIndex, droppedIndex];
        }



        function initialize_draggable(helper = 'original') {
            // Palette / modal items (source)
            $(".draggable:not(.dragable-initiated)").draggable({
                helper: helper,
                appendTo: "body",
                zIndex: 10000,
                revert: "invalid",
                cancel: false,
                connectToSortable: ".drop-area"
            });
            $(".draggable:not(.dragable-initiated)").addClass('dragable-initiated'); // mark as initiated

            // Drop areas: sortable setup
            $(".drop-area:not(.drop-area-initiated)").sortable({
                connectWith: ".drop-area",
                placeholder: "drop-placeholder",
                tolerance: "pointer",

                start: function(event, ui) {
                    // Disable sorting for non-draggable items
                    if (!ui.item.hasClass("draggable")) {
                        $(this).sortable("cancel"); // stop sorting non-draggable
                        return false;
                    }
                },
                receive: function(event, ui) {
                    // enhance_dragable_elements(ui.item);
                },
                update: function(event, ui) {
                    enhance_dragable_elements(ui.item);
                },

                // 💡 Highlight handlers
                activate: function(e) {
                    $(this).addClass("ui-droppable-active"); // start highlighting when dragging starts
                },
                deactivate: function() {
                    $(this).removeClass("ui-droppable-active"); // remove highlight when drag stops
                },
                over: function() {
                    if ($(this).find('.ui-draggable-dragging, .ui-sortable-helper').length == 0) {
                        return; // Skip if no draggable item is present
                    }

                    $(this).addClass("ui-droppable-hover"); // stronger highlight when hovering

                    var sortableItemWidth = $(this).find('.ui-draggable-dragging, .ui-sortable-helper').outerWidth();
                    var sortableItemHeight = $(this).find('.ui-draggable-dragging, .ui-sortable-helper').outerHeight();
                    console.log('Width: ' + sortableItemWidth + ', Height: ' + sortableItemHeight);
                    if (sortableItemWidth == undefined || sortableItemWidth < 10) {
                        sortableItemWidth = 120;
                    }
                    if (sortableItemHeight == undefined || sortableItemHeight < 5) {
                        sortableItemHeight = 40;
                    }
                    $('.ui-sortable-placeholder, .drop-placeholder').width(sortableItemWidth);
                    $('.ui-sortable-placeholder, .drop-placeholder').height(sortableItemHeight);
                },
                out: function() {
                    $(this).removeClass("ui-droppable-hover"); // remove hover highlight
                }

            }).disableSelection();
            $(".drop-area:not(.drop-area-initiated)").addClass('drop-area-initiated'); // mark as initiated
        }

        function enhance_dragable_elements(item = null) {
            if (item != null && item.hasClass('draggable')) {
                item.removeClass("cursor-move ui-draggable ui-draggable-handle ui-draggable-dragging")
                    .addClass("dropped-item builder-editable")
                    .attr("builder-identity", function() {
                        return Math.floor(Math.random() * 1000000); // Generate a random unique ID
                    })
                    .removeAttr("style");

                if (item.find(".remove-dropped-item").length == 0) {
                    item.append('<span class="remove-dropped-item"><i class="fi-rr-cross"></i></span>');
                }

                item.find(".remove-dropped-item").off('click').on('click', function(e) {
                    $(this).closest('.dropped-item').remove();
                    e.preventDefault();
                });
            } else {
                $('.dropped-item.draggable').each(function() {
                    enhance_dragable_elements($(this));
                });
            }

            setTimeout(() => {
                $('a[href="#"]').on('click', function(event) {
                    event.preventDefault();
                });
                text_and_image_make_editable();
            }, 800);
        }
    </script>


    <style>
        .parent {
            position: relative;
            /* Add any other styles you want for the parent div */
        }

        .parent:hover {
            outline: 2px dashed black;
            display: block;
        }

        .child {
            position: absolute;
            display: none;
            float: right;
            text-align: center;
            top: 0;
            left: 0;
            width: 100%;
            cursor: move;
            margin-top: 5px;
        }

        .parent:hover>.child {
            display: block;
        }

        .block_delete {
            border-radius: 5px 0px 0px 5px !important;
            margin-right: -5px !important;
        }

        .block_add {
            border-radius: 0px 5px 5px 0px !important;
        }


        .editor_top_bar {
            background-color: #121729;
        }

        .editor_title {
            color: #faf4ff;
            font-size: 14px;
        }

        .builder_image img {
            display: block;
        }

        .placeholder_block {
            text-align: center;
            outline: 2px dashed #c1b4d8;
            padding: 50px;
            margin-top: 50px;
            border-radius: 10px;
        }

        .placeholder_block>div {
            margin-top: 10px;
            font-size: 16px;
            margin-bottom: 20px;
            color: #12172a;
        }

        .toast {
            font-size: 13px;
        }

        /* Flaticon spacing isse fixed START*/
        i:not(.fas, .fa, .fab) {
            line-height: 1.5 !important;
            vertical-align: -0.12em !important;
            display: inline-flex !important;
            margin: 3px;
        }

        /* Flaticon spacing isse fixed END*/


        /* Drag and Drop element css started */
        .ui-sortable-placeholder,
        .drop-placeholder {
            display: inline-block;
            border: 3px dashed #c1b4d8;
            visibility: visible !important;
            background: transparent;
            height: 40px;
            width: 120px;
            margin-bottom: 10px;
            border-radius: 8px !important;
        }

        .ui-droppable-active,
        .ui-droppable-hover {
            position: relative;
        }

        .ui-droppable-active::after {
            content: "";
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0;
            right: 0;
            left: 0;
            bottom: 0;
            border-radius: inherit !important;
            border: 3px dashed #FF9800;
        }

        .ui-droppable-hover::after {
            content: "";
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0;
            right: 0;
            left: 0;
            bottom: 0;
            border-radius: inherit !important;
            border: 3px dashed #00ff88;
        }

        .cursor-move {
            cursor: move !important;
        }

        .dropped-item:hover .remove-dropped-item {
            display: block;
        }

        .remove-dropped-item {
            display: none;
            position: absolute;
            top: -15px;
            right: -4px;
            background: #ff1010;
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            text-align: center;
            line-height: 20px;
            font-weight: bold;
            cursor: pointer;
            z-index: 1000;
            font-size: 10px;
        }

        .remove-dropped-item:hover {
            background: #c10909;
            color: white;
        }

        button,
        a {
            transition: none !important;
        }

        /* .drop-area :not(.dropped-item) {
            position: relative !important;
        } */
        /* Drag and Drop element css started */
    </style>
</body>

</html>
