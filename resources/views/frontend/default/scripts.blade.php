<script>
    "use strict";

    function wishlistToggle(course_id, elem) {
        $.ajax({
            type: "get",
            url: "{{ route('toggleWishItem') }}" + '/' + course_id,
            success: function(response) {
                if (response) {
                    if (response.toggleStatus == 'added') {
                        $(elem).addClass('inList');

                        $(elem).attr('data-bs-title', '{{ get_phrase('Remove from wishlist') }}')
                            .tooltip('dispose')
                            .tooltip('show');

                        success('{{ get_phrase('This course added to your wishlist') }}');

                    } else if (response.toggleStatus == 'removed') {
                        $(elem).removeClass('inList');

                        $(elem).attr('data-bs-title', '{{ get_phrase('Add to wishlist') }}')
                            .tooltip('dispose')
                            .tooltip('show');

                        success('{{ get_phrase('This course removed from your wishlist') }}');
                    }
                }
            }
        });
    }

    $(document).ready(function() {
        //When need to add a wishlist button inside a anchor tag
        $('.checkPropagation').on('click', function(event) {
            var action = $(this).attr('action');
            var onclickFunction = $(this).attr('onclick');
            var onChange = $(this).attr('onchange');
            var tag = $(this).prop("tagName").toLowerCase();
            console.log(tag);
            if (tag != 'a' && action) {
                $(location).attr('href', $(this).attr('action'));
                return false;
            } else if (onclickFunction) {
                if (onclickFunction) {
                    onclickFunction;
                }
                return false;
            } else if (tag == 'a') {
                return true;
            }
        });


        // change course layout grid and list in course page
        $('.layout').on('click', function(e) {
            e.preventDefault();
            let layout = $(this).attr('id');

            $.ajax({
                type: "get",
                url: "{{ route('change.layout') }}",
                data: {
                    view: layout
                },
                success: function(response) {
                    if (response.reload) {
                        window.location.reload(1);
                    }
                }
            });
        });

        // toggleWishItems
        $('.toggleWishItem').on('click', function(e) {
            e.stopPropagation();
            e.preventDefault();

            let get_item_id = $(this).attr('id');
            let item_id = get_item_id.split('-');
            item_id = item_id[1];

            const $this = $(this);

            $.ajax({
                type: "get",
                url: "{{ route('toggleWishItem') }}" + '/' + item_id,
                success: function(response) {
                    if (response) {
                        if (response.toggleStatus == 'added') {
                            $this.addClass('inList');
                        } else if (response.toggleStatus == 'removed') {
                            $this.removeClass('inList');
                        }
                        window.location.reload(1);
                    }
                }
            });
        });
    });

    $(function() {
        if ($('.tagify:not(.inited)').length) {
            var tagify = new Tagify(document.querySelector('.tagify:not(.inited)'), {
                placeholder: '{{ get_phrase('Enter your keywords') }}',
                delimiters: "~",
            })
            $('.tagify:not(.inited)').addClass('inited');
        }


        $('[data-bs-toggle="tooltip"]').tooltip();


        //Overlap content start
        document.querySelectorAll('.overlay-content').forEach(function(elem) {

            if (elem.classList.contains('overlay-content-max-h-100') && elem.offsetHeight > 100) {
                overlayCollapse(elem);
            } else if (elem.classList.contains('overlay-content-max-h-150') && elem.offsetHeight > 150) {
                overlayCollapse(elem);
            } else if (elem.classList.contains('overlay-content-max-h-200') && elem.offsetHeight > 200) {
                overlayCollapse(elem);
            } else if (elem.classList.contains('overlay-content-max-h-300') && elem.offsetHeight > 300) {
                overlayCollapse(elem);
            } else if (elem.classList.contains('overlay-content-max-h-400') && elem.offsetHeight > 400) {
                overlayCollapse(elem);
            } else if (elem.classList.contains('overlay-content-max-h-500') && elem.offsetHeight > 500) {
                overlayCollapse(elem);
            } else if (elem.classList.contains('overlay-content-max-h-600') && elem.offsetHeight > 600) {
                overlayCollapse(elem);
            } else if (elem.classList.contains('overlay-content-max-h-700') && elem.offsetHeight > 700) {
                overlayCollapse(elem);
            } else if (elem.classList.contains('overlay-content-max-h-800') && elem.offsetHeight > 800) {
                overlayCollapse(elem);
            }
        });

        function overlayCollapse(elem) {
            if (elem.classList.contains('show-more')) {
                elem.classList.add('show-less');
                elem.classList.remove('show-more');
                elem.querySelector('p a.overlay-action').textContent = "{{ get_phrase('Show less') }} - ";
            } else if (elem.classList.contains('show-less')) {
                elem.classList.add('show-more');
                elem.classList.remove('show-less');
                elem.querySelector('p a.overlay-action').textContent = "{{ get_phrase('Show more') }} + ";
            } else {
                elem.classList.add('show-more');
                elem.insertAdjacentHTML('beforeend', '<p><a href="javascript:;" class="overlay-action title text-14px">{{ get_phrase('Show more') }} + </a></p>');

                // Select the newly added element and attach the event listener
                elem.querySelector('p a.overlay-action').addEventListener('click', function(event) {
                    event.preventDefault();
                    overlayCollapse(elem);
                });
            }
        }
        //Overlap content ended
    });
</script>

<script>
    "use strict";

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    $(document).ready(function() {
        $('.gSearch-icon').on('click', function() {
            $('.gSearch-show').toggleClass('active');
        });
    });
</script>
