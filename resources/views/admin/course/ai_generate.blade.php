<form action="{{ route('admin.course.ai.generate') }}" method="post" class="ajaxForm">
    @csrf
    <input type="hidden" name="course_id" value="{{ $id }}">

    <div class="mb-3">
        <label class="form-label ol-form-label">{{ get_phrase('Number of lessons') }}</label>
        <input type="number" name="lesson_count" class="form-control ol-form-control" value="6" min="3" max="12">
        <small class="text-muted">{{ get_phrase('Mix of video (HeyGen), audio (Gemini), and reading lessons') }}</small>
    </div>

    <div class="mb-3">
        <label class="form-label ol-form-label">{{ get_phrase('Course brief (optional)') }}</label>
        <textarea name="brief" class="form-control ol-form-control" rows="4"
            placeholder="{{ get_phrase('Topics, level, goals… Leave empty to use the course title and description.') }}"></textarea>
    </div>

    <p class="small text-muted mb-3">
        <i class="fi fi-rr-magic-wand"></i>
        {{ get_phrase('Uses the same AI pipeline as personalized courses: HeyGen presenter videos, Gemini narration, and structured text lessons.') }}
    </p>

    <button type="submit" class="btn ol-btn-primary w-100" id="btn-submit-ai-curriculum">
        <i class="fi fi-rr-sparkles"></i> {{ get_phrase('Generate curriculum with AI') }}
    </button>
    <p class="small text-muted mt-2 mb-0">{{ get_phrase('Generation may take 1–3 minutes. Do not close this window.') }}</p>
</form>
