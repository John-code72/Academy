<form action="{{ route('admin.customer.support.ticket.category.update', ['id' => $category->id]) }}" method="post" enctype="multipart/form-data">
    @csrf
    <div class="fpb7 mb-2">
        <label class="form-label ol-form-label" for="title">{{ get_phrase('Title') }}</label>
        <input class="form-control ol-form-control" type="text" id="title" name="title" value="{{ $category->title }}" required>
    </div>

    <div class="fpb-7 mb-3">
        <label for="status" class="form-label ol-form-label">{{ get_phrase('Status') }}</label>
        <select class="form-control ol-form-control ol-select2" name="status" id="status" required>
            <option value="1" {{ $category->status == 1 ? 'selected' : '' }}>{{ get_phrase('Active') }}</option>
            <option value="0" {{ $category->status == 0 ? 'selected' : '' }}>{{ get_phrase('De Active') }}</option>
        </select>
    </div>
    <div class="fpb7 mb-2">
        <button type="submit" class="btn ol-btn-primary">{{ get_phrase('Update') }}</button>
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
