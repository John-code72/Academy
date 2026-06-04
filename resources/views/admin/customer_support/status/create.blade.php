<form action="{{ route('admin.customer.support.ticket.status.store') }}" method="post" enctype="multipart/form-data">
    @csrf
    <div class="fpb7 mb-2">
        <label class="form-label ol-form-label" for="title">{{ get_phrase('Title') }}</label>
        <input class="form-control ol-form-control" type="text" id="title" name="title" required>
    </div>

    <div class="fpb-7 mb-3">
        <label for="status" class="form-label ol-form-label">{{ get_phrase('Status') }}</label>
        <select class="form-control ol-form-control ol-select2" name="status" id="status" required>
            <option value="1">{{ get_phrase('Active') }}</option>
            <option value="0">{{ get_phrase('De Active') }}</option>
        </select>
    </div>

    <div class="fpb-7 mb-3">
        <label for="icon" class="form-label ol-form-label">{{ get_phrase('Icon') }}</label>
        <input type="file" name="icon" class="form-control ol-form-control" id="icon" accept="image/*" />
    </div>

    <div class="form-check">
        <input type="checkbox" id="view-10" name="default_view" class="form-check-input" value="1" checked="">
        <label class="form-check-label text-capitalize sub-title fw-medium w-100" for="view-10">{{ get_phrase('Set as Default View') }}</label>
    </div>

    <div class="fpb7 mb-3">
        <label class="form-label ol-form-label" for="color">{{ get_phrase('Color') }}</label>
        <input type="color" class="form-control form-control-color" name="color" id="color" value="#007bff">
    </div>

    <div class="fpb7 mb-2">
        <button type="submit" class="btn ol-btn-primary">{{ get_phrase('Add status') }}</button>
    </div>
</form>
<script>
    "use strict";
    $(".ol-select2").select2({
        dropdownParent: $('#ajaxModal')
    });
    $(".ol-modal-niceSelect").niceSelect({
        dropdownParent: $('#ajaxModal')
    });
</script>
