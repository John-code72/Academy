@php
    $notice = App\Models\NoticeBoard::where('id', $id)->first();
@endphp
<form action="{{ route('instructor.course.update_notice', $notice->id) }}" method="post">
    @csrf
    <div class="fpb-7 mb-3">
        <label class="form-label ol-form-label" for="title">{{ get_phrase('Notice title') }}<span
                class="required">*</span></label>
        <input type="text" name="title" value="{{ $notice->title }}" class="form-control ol-form-control"
            id="title" required>
    </div>

    <div class="fpb-7 mb-3">
        <label class="form-label ol-form-label" for="description">{{ get_phrase('Description') }}<span
                class="required">*</span></label>
        <textarea name="description" rows="5" class="form-control ol-form-control text_editor" id="description" required>{{ $notice->description }}</textarea>
    </div>
    <div class="fpb-7 mb-3">
        <button type="submit" class="ol-btn-primary">{{ get_phrase('Update') }}</button>
    </div>
</form>
@include('admin.init')
