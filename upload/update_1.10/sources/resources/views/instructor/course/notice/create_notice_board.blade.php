<form action="{{ route('instructor.course.notice_store', ['course_id' => $course_id]) }}" method="post">

    @csrf
    <div class="fpb-7 mb-3">
        <label class="form-label ol-form-label" for="title">{{ get_phrase('Notice title') }}<span
                class="required">*</span></label>
        <input type="text" name="title" class="form-control ol-form-control" id="title" required>
    </div>

    <div class="fpb-7 mb-3">
        <label class="form-label ol-form-label" for="description">{{ get_phrase('Description') }}<span
                class="required">*</span></label>
        <textarea name="description" rows="5" class="form-control ol-form-control text_editor" id="description"></textarea>
    </div>

    <div class="mb-4 form-check">
        <input type="checkbox" name="is_urgent" class="form-check-input" id="urgentCheck">
        <label class="form-check-label text-secondary" for="urgentCheck">{{ get_phrase('Send mail to students if urgent') }}</label>
    </div>
    <div class="fpb-7 mb-3">
        <button type="submit" class="ol-btn-primary">{{ get_phrase('Add new notice') }}</button>
    </div>
</form>
@include('admin.init')
