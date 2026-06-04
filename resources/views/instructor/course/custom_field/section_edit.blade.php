<form action="{{ route('instructor.custom.section.update', $customField->id) }}" method="post">
    @csrf

    <div class="mb-3">
        <label class="form-label ol-form-label">{{ get_phrase('Section Title') }}</label>
        <input type="text" name="custom_title" value="{{ $customField->custom_title }}" class="form-control">
    </div>

    <div class="col-sm-12 text-end mt-3" id="submit_button_wrapper">
        <button type="submit" class="btn ol-btn-primary ">{{ get_phrase('Update') }}</button>
    </div>
</form>
