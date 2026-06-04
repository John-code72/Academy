<form action="{{ route('admin.course.quiz.store') }}" method="post">@csrf

    <input type="hidden" name="course_id" value="{{ $id }}">
    <input type="hidden" name="lesson_type" value="practice_test">
    <input type="hidden" name="retake" value="1">
    <div class="fpb7 mb-3">
        <label class="form-label ol-form-label" for="title">
            {{ get_phrase('Title') }}
            <span class="text-danger ms-1">*</span>
        </label>
        <input class="form-control ol-form-control" type="text" id="title" name="title" required>
    </div>

    <div class="row mb-3">
        <div class="col-sm-12 fpb-7">
            <label class="form-label ol-form-label">
                {{ get_phrase('Section') }}
                <span class="text-danger ms-1">*</span>
            </label>
            <select class="form-control ol-form-control ol-select2" data-toggle="select2" name="section">
                <option value="">{{ get_phrase('Select an option') }}</option>
                @foreach (App\Models\Section::where('course_id', $id)->get() as $section)
                    <option value="{{ $section->id }}">{{ $section->title }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="mb-3">
        <label class="form-label ol-form-label" for="duration">
            {{ get_phrase('Duration') }}
            <span class="text-danger ms-1">*</span>
        </label>
        <div class="row">
            <div class="col-4">
                <input class="form-control ol-form-control" type="number" min="0" max="23" name="hour"
                    placeholder="00 hour">
            </div>
            <div class="col-4">
                <input class="form-control ol-form-control" type="number" min="0" max="59" name="minute"
                    placeholder="00 minute">
            </div>
            <div class="col-4">
                <input class="form-control ol-form-control" type="number" min="0" max="59" name="second"
                    placeholder="00 second">
            </div>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-sm-6">
            <label class="form-label ol-form-label" for="total_mark">
                {{ get_phrase('Total Mark') }}
                <span class="text-danger ms-1">*</span>
            </label>
            <input class="form-control ol-form-control" type="number" min="1" id="total_mark" name="total_mark"
                required>
        </div>
        <div class="col-sm-6">
            <label class="form-label ol-form-label" for="pass_mark">
                {{ get_phrase('Pass Mark') }}
                <span class="text-danger ms-1">*</span>
            </label>
            <input class="form-control ol-form-control" type="number" min="1" id="pass_mark" name="pass_mark"
                required>
        </div>
    </div>

    <p class="text-muted small mb-3">{{ get_phrase('Students can retry this test without a limit on attempts.') }}</p>

    <div class="fpb-7 mb-3">
        <label class="form-label ol-form-label" for="visibility_scope">
            {{ get_phrase('Who can see this test') }}
        </label>
        <select class="form-control ol-form-control ol-select2" data-toggle="select2" name="visibility_scope" id="visibility_scope">
            <option value="all_authenticated">{{ get_phrase('All authenticated users') }}</option>
            <option value="students_only">{{ get_phrase('Students only') }}</option>
            <option value="instructors_only">{{ get_phrase('Instructors only') }}</option>
        </select>
    </div>

    <div class="fpb-7 mb-3">
        <label for="description"
            class="form-label ol-form-label col-form-label">{{ get_phrase('Description') }}</label>
        <textarea name="description" rows="5" class="form-control ol-form-control text_editor"></textarea>
    </div>

    <div class="fpb7">
        <button type="submit" class="btn ol-btn-primary">{{ get_phrase('Add Defrilex onboarding Test') }}</button>
    </div>
</form>

@include('admin.init')
