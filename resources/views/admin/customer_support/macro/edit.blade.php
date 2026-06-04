<form action="{{ route('admin.customer.support.ticket.macro.update', ['id' => $macro->id]) }}" method="post" enctype="multipart/form-data">
    @csrf
    <div class="fpb7 mb-2">
        <label class="form-label ol-form-label" for="title">{{ get_phrase('Title') }}</label>
        <input class="form-control ol-form-control" type="text" id="title" name="title" value="{{ $macro->title }}" required>
    </div>
    <div class="fpb7 mb-2">
        <label class="form-label ol-form-label" for="description">{{ get_phrase('Description') }}</label>
        <textarea class="form-control ol-form-control" id="description" name="description" rows="4" required>{{ $macro->description }}</textarea>
    </div>

    <div class="fpb7 mb-2">
        <button type="submit" class="btn ol-btn-primary">{{ get_phrase('Update') }}</button>
    </div>
</form>
