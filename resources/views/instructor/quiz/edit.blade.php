@php
    $quiz = App\Models\Lesson::join('sections', 'lessons.section_id', 'sections.id')
        ->join('courses', 'sections.course_id', 'courses.id')
        ->select('lessons.*', 'courses.id as course_id')
        ->where('lessons.id', $id)
        ->first();

    $duration = $quiz->duration ? explode(':', $quiz->duration) : [];
@endphp

<form action="{{ route('instructor.course.quiz.update', $id) }}" method="post">@csrf
    <div class="fpb7 mb-3">
        <label class="form-label ol-form-label" for="title">
            {{ get_phrase('Title') }}
            <span class="text-danger ms-1">*</span>
        </label>
        <input class="form-control ol-form-control" type="text" id="title" name="title" value="{{ $quiz->title }}"
            required>
    </div>

    <div class="row mb-3">
        <div class="col-sm-12 fpb-7">
            <label class="form-label ol-form-label">
                {{ get_phrase('Section') }}
                <span class="text-danger ms-1">*</span>
            </label>
            <select class="form-control ol-form-control ol-select2" data-toggle="select2" name="section">
                <option value="">{{ get_phrase('Select an option') }}</option>
                @foreach (App\Models\Section::where('course_id', $quiz->course_id)->get() as $section)
                    <option value="{{ $section->id }}" @if ($section->id == $quiz->section_id) selected @endif>
                        {{ $section->title }}</option>
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
                    placeholder="00 hour" value="{{ $duration[0] }}">
            </div>
            <div class="col-4">
                <input class="form-control ol-form-control" type="number" min="0" max="59" name="minute"
                    placeholder="00 minute" value="{{ $duration[1] }}">
            </div>
            <div class="col-4">
                <input class="form-control ol-form-control" type="number" min="0" max="59" name="second"
                    placeholder="00 second" value="{{ $duration[2] }}">
            </div>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-sm-4">
            <label class="form-label ol-form-label" for="total_mark">
                {{ get_phrase('Total Mark') }}
                <span class="text-danger ms-1">*</span>
            </label>
            <input class="form-control ol-form-control" type="number" min="1" id="total_mark" name="total_mark"
                value="{{ $quiz->total_mark }}" required>
        </div>
        <div class="col-sm-4">
            <label class="form-label ol-form-label" for="pass_mark">
                {{ get_phrase('Pass Mark') }}
                <span class="text-danger ms-1">*</span>
            </label>
            <input class="form-control ol-form-control" type="number" min="1" id="pass_mark" name="pass_mark"
                value="{{ $quiz->pass_mark }}" required>
        </div>
        @if (($quiz->lesson_type ?? '') === 'practice_test')
            <div class="col-sm-4">
                <label class="form-label ol-form-label" for="retake_display">
                    {{ get_phrase('Retake') }}
                </label>
                <input class="form-control ol-form-control" type="text" id="retake_display" readonly
                    value="{{ get_phrase('Unlimited') }}">
                <input type="hidden" name="retake" value="9999">
            </div>
        @else
            <div class="col-sm-4">
                <label class="form-label ol-form-label" for="retake">
                    {{ get_phrase('Retake') }}
                    <span class="text-danger ms-1">*</span>
                </label>
                <input class="form-control ol-form-control" type="number" min="1" id="retake" name="retake"
                    value="{{ $quiz->retake }}" required>
            </div>
        @endif
    </div>

    @if (($quiz->lesson_type ?? '') === 'practice_test')
        <div class="fpb-7 mb-3">
            <label class="form-label ol-form-label" for="visibility_scope">
                {{ get_phrase('Who can see this test') }}
            </label>
            <select class="form-control ol-form-control ol-select2" data-toggle="select2" name="visibility_scope" id="visibility_scope">
                <option value="all_authenticated" @selected(($quiz->visibility_scope ?? 'all_authenticated') === 'all_authenticated')>{{ get_phrase('All authenticated users') }}</option>
                <option value="students_only" @selected(($quiz->visibility_scope ?? 'all_authenticated') === 'students_only')>{{ get_phrase('Students only') }}</option>
                <option value="instructors_only" @selected(($quiz->visibility_scope ?? 'all_authenticated') === 'instructors_only')>{{ get_phrase('Instructors only') }}</option>
            </select>
        </div>
    @endif

    <div class="fpb-7 mb-3">
        <label for="description"
            class="form-label ol-form-label col-form-label">{{ get_phrase('Description') }}</label>
        <textarea name="description" rows="5" class="form-control ol-form-control text_editor">{!! $quiz->description !!}</textarea>
    </div>

    <div class="fpb7">
        <button type="submit" class="btn ol-btn-primary">{{ ($quiz->lesson_type ?? '') === 'practice_test' ? get_phrase('Update Defrilex onboarding Test') : get_phrase('Update Quiz') }}</button>
    </div>
</form>

@include('instructor.init')
