<form class="row">
    <div class="col-12">
        <div id="drag-container" class="list-group mb-3" style="gap: 0.5rem; cursor: pointer;">
            @foreach ($sectionSorting as $sort)
                <div class="list-group-item rounded-3 py-2 px-3 border d-flex align-items-center justify-content-between draggable-item hover-parent" id="section-{{ $sort->id }}">
                    <span>{{ $sort->custom_title }}</span>
                    <i class="fi-rr-apps-sort text-muted ps-2 border-start ms-3 hover-show cursor-move"></i>
                </div>
            @endforeach
        </div>

        <div class="text-end">
            <button type="button" class="btn btn-primary" onclick="saveSorting()">
                {{ get_phrase('Save Changes') }}
            </button>
        </div>
    </div>
</form>

<script>
    "use strict";

    $("#drag-container").sortable({
        placeholder: "ui-sortable-placeholder",
        helper: "clone",
        axis: "y"
    });
    $("#drag-container").disableSelection();

    function saveSorting() {
        let order = [];
        $('#drag-container .list-group-item').each(function() {
            let id = $(this).attr('id').replace('section-', '');
            order.push(id);
        });

        $.ajax({
            url: "{{ route('admin.section.sort.update') }}",
            type: "POST",
            data: {
                order: order,
                _token: "{{ csrf_token() }}"
            },
            success: function(response) {
                success('Sorting updated successfully!');
                location.reload();
            },
            error: function() {
                warning('Something went wrong!');
            }
        });
    }
</script>
