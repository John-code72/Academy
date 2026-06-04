@extends('layouts.admin')
@push('title', get_phrase('Lesson Studio') . ' — ' . $course->title)
@section('content')
    <div class="ol-card radius-8px mb-3">
        <div class="ol-card-body py-4 px-20px">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div>
                    <h4 class="title fs-16px mb-1">
                        <i class="fi-rr-magic-wand me-2"></i>{{ get_phrase('Lesson Studio') }}
                    </h4>
                    <p class="text-muted small mb-0">{{ $course->title }} · {{ $course->language }}</p>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('admin.course.edit', ['id' => $course->id, 'tab' => 'curriculum']) }}" class="btn ol-btn-light ol-btn-sm">
                        <i class="fi-rr-arrow-left"></i> {{ get_phrase('Curriculum') }}
                    </a>
                    <a href="{{ route('course.player', ['slug' => $course->slug, 'id' => '']) }}" target="_blank" class="btn ol-btn-light ol-btn-sm">
                        {{ get_phrase('Preview') }} <i class="fi-rr-arrow-up-right-from-square"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-3">
            <div class="ol-card h-100 {{ $openAiOk ? 'border-success' : 'border-warning' }}">
                <div class="ol-card-body px-20px py-3">
                    <p class="sub-title fs-12px mb-1">OpenAI / DeepSeek</p>
                    <p class="mb-0">{{ $openAiOk ? get_phrase('OK') : get_phrase('Missing') }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="ol-card h-100 {{ $heygenOk ? 'border-success' : 'border-warning' }}">
                <div class="ol-card-body px-20px py-3">
                    <p class="sub-title fs-12px mb-1">HeyGen</p>
                    <p class="mb-0">{{ $heygenOk ? get_phrase('OK') : get_phrase('Missing key') }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="ol-card h-100 {{ $geminiOk ? 'border-success' : 'border-warning' }}">
                <div class="ol-card-body px-20px py-3">
                    <p class="sub-title fs-12px mb-1">Gemini (audio)</p>
                    <p class="mb-0">{{ $geminiOk ? get_phrase('OK') : get_phrase('Missing') }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="ol-card h-100">
                <div class="ol-card-body px-20px py-3">
                    <p class="sub-title fs-12px mb-1">HeyGen jobs</p>
                    <p class="mb-0">{{ $heygenPending }} {{ get_phrase('pending') }} · {{ $heygenFailed }} {{ get_phrase('failed') }}</p>
                </div>
            </div>
        </div>
    </div>

    <ul class="nav nav-tabs ol-tab-nav mb-3" role="tablist">
        <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-avatar" type="button">{{ get_phrase('Avatar & voice') }}</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-generate" type="button">{{ get_phrase('AI generation') }}</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-add" type="button">{{ get_phrase('Add content') }}</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-lessons" type="button">{{ get_phrase('Lessons') }} ({{ $lessons->count() }})</button></li>
    </ul>

    <div class="tab-content">
        {{-- Avatar & voice --}}
        <div class="tab-pane fade show active" id="tab-avatar">
            <div class="ol-card p-4">
                <form action="{{ route('admin.lesson.studio.settings', $course->id) }}" method="post">
                    @csrf
                    <p class="text-muted small">{{ get_phrase('These settings apply to all HeyGen videos generated from this academy.') }}</p>
                    <div class="row g-3">
                        <div class="col-lg-6">
                            <label class="form-label ol-form-label">{{ get_phrase('Presenter avatar') }}</label>
                            <select name="heygen_avatar_id" class="form-control ol-form-control ol-select2" required>
                                @foreach ($avatars as $avatar)
                                    <option value="{{ $avatar['avatar_id'] }}" @selected($heygenAvatarId === $avatar['avatar_id'])>
                                        {{ $avatar['avatar_name'] }}
                                        @if (!empty($avatar['gender'])) ({{ $avatar['gender'] }}) @endif
                                    </option>
                                @endforeach
                            </select>
                            @if (count($avatars) > 0 && !empty($avatars[0]['preview_image_url']))
                                <img src="{{ $avatars[0]['preview_image_url'] }}" alt="" class="mt-2 rounded" height="80" id="avatar-preview" style="display:none">
                            @endif
                        </div>
                        <div class="col-lg-6">
                            <label class="form-label ol-form-label">{{ get_phrase('Voice (English / default)') }}</label>
                            <select name="heygen_voice_id" class="form-control ol-form-control ol-select2" required>
                                @foreach ($voices as $voice)
                                    <option value="{{ $voice['voice_id'] }}" @selected($heygenVoiceId === $voice['voice_id'])>
                                        {{ $voice['name'] }}
                                        @if (!empty($voice['language'])) — {{ $voice['language'] }} @endif
                                    </option>
                                @endforeach
                            </select>
                            <label class="form-label ol-form-label mt-3">{{ get_phrase('Voice (French)') }}</label>
                            <select name="heygen_voice_id_fr" class="form-control ol-form-control ol-select2">
                                <option value="">{{ get_phrase('Same as default') }}</option>
                                @foreach ($voices as $voice)
                                    @if (stripos($voice['language'] ?? '', 'french') !== false || stripos($voice['language'] ?? '', 'fr') !== false || stripos($voice['name'] ?? '', 'fr') !== false)
                                        <option value="{{ $voice['voice_id'] }}" @selected($heygenVoiceIdFr === $voice['voice_id'])>
                                            {{ $voice['name'] }} — {{ $voice['language'] ?? '' }}
                                        </option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="mt-3 d-flex gap-2">
                        <button type="submit" class="btn ol-btn-primary" @disabled(!$heygenOk)>{{ get_phrase('Save avatar & voice') }}</button>
                        <a href="{{ route('admin.open.ai.settings') }}" class="btn ol-btn-light">{{ get_phrase('API keys') }}</a>
                    </div>
                </form>
            </div>
        </div>

        {{-- AI generation --}}
        <div class="tab-pane fade" id="tab-generate">
            <div class="ol-card p-4">
                <form action="{{ route('admin.lesson.studio.generate', $course->id) }}" method="post"
                    onsubmit="return confirm({{ json_encode(get_phrase('Generate AI lessons for this course? Existing sections will get new lessons.')) }});">
                    @csrf
                    <p class="text-muted small">{{ get_phrase('Creates video (HeyGen), audio (Gemini), and text lessons using the avatar and voice above.') }}</p>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label ol-form-label">{{ get_phrase('Number of lessons') }}</label>
                            <input type="number" name="lesson_count" class="form-control ol-form-control" value="6" min="1" max="12">
                        </div>
                        <div class="col-md-8">
                            <label class="form-label ol-form-label">{{ get_phrase('Brief (optional)') }}</label>
                            <textarea name="brief" class="form-control ol-form-control" rows="3"
                                placeholder="{{ get_phrase('Topics, level, objectives…') }}">{{ $course->short_description }}</textarea>
                        </div>
                    </div>
                    <button type="submit" class="btn ol-btn-primary mt-3" @disabled(!$openAiOk)>
                        <i class="fi fi-rr-sparkles"></i> {{ get_phrase('Generate curriculum with AI') }}
                    </button>
                </form>
                <hr>
                <form action="{{ route('admin.lesson.studio.refresh.heygen', $course->id) }}" method="post" class="d-inline">
                    @csrf
                    <button type="submit" class="btn ol-btn-light" @disabled(!$heygenOk)>
                        <i class="fi-rr-refresh"></i> {{ get_phrase('Refresh all HeyGen videos') }}
                    </button>
                </form>
            </div>
        </div>

        {{-- Add content --}}
        <div class="tab-pane fade" id="tab-add">
            <div class="row g-3">
                <div class="col-lg-6">
                    <div class="ol-card p-4 h-100">
                        <h5 class="fs-14px mb-3"><i class="fi fi-rr-document"></i> {{ get_phrase('Add PDF lesson') }}</h5>
                        <form action="{{ route('admin.lesson.studio.pdf', $course->id) }}" method="post" enctype="multipart/form-data">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label ol-form-label">{{ get_phrase('Title') }}</label>
                                <input type="text" name="title" class="form-control ol-form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label ol-form-label">{{ get_phrase('PDF file') }}</label>
                                <input type="file" name="pdf" class="form-control ol-form-control" accept=".pdf" required>
                            </div>
                            @if ($sections->isNotEmpty())
                                <div class="mb-3">
                                    <label class="form-label ol-form-label">{{ get_phrase('Section') }}</label>
                                    <select name="section_id" class="form-control ol-form-control">
                                        @foreach ($sections as $section)
                                            <option value="{{ $section->id }}">{{ $section->title }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif
                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" name="is_free" value="1" id="pdf_free">
                                <label class="form-check-label" for="pdf_free">{{ get_phrase('Free preview lesson') }}</label>
                            </div>
                            <button type="submit" class="btn ol-btn-primary">{{ get_phrase('Upload PDF') }}</button>
                        </form>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="ol-card p-4 h-100">
                        <h5 class="fs-14px mb-3"><i class="fi fi-rr-align-left"></i> {{ get_phrase('Add text lesson') }}</h5>
                        <form action="{{ route('admin.lesson.studio.text', $course->id) }}" method="post">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label ol-form-label">{{ get_phrase('Title') }}</label>
                                <input type="text" name="title" class="form-control ol-form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label ol-form-label">{{ get_phrase('Content (HTML allowed)') }}</label>
                                <textarea name="content" class="form-control ol-form-control" rows="6" required></textarea>
                            </div>
                            @if ($sections->isNotEmpty())
                                <div class="mb-3">
                                    <label class="form-label ol-form-label">{{ get_phrase('Section') }}</label>
                                    <select name="section_id" class="form-control ol-form-control">
                                        @foreach ($sections as $section)
                                            <option value="{{ $section->id }}">{{ $section->title }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif
                            <button type="submit" class="btn ol-btn-primary">{{ get_phrase('Add text lesson') }}</button>
                        </form>
                    </div>
                </div>
                <div class="col-12">
                    <div class="ol-card p-4">
                        <h5 class="fs-14px mb-2">{{ get_phrase('More lesson types') }}</h5>
                        <p class="text-muted small mb-2">{{ get_phrase('Use the classic curriculum editor for quizzes, SCORM, YouTube, Vimeo, live class, etc.') }}</p>
                        <a href="{{ route('admin.course.edit', ['id' => $course->id, 'tab' => 'curriculum']) }}" class="btn ol-btn-light ol-btn-sm">
                            {{ get_phrase('Open curriculum editor') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Lessons list --}}
        <div class="tab-pane fade" id="tab-lessons">
            <div class="ol-card p-4">
                <div class="table-responsive">
                    <table class="table eTable eTable-2">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>{{ get_phrase('Title') }}</th>
                                <th>{{ get_phrase('Type') }}</th>
                                <th>{{ get_phrase('AI') }}</th>
                                <th>HeyGen</th>
                                <th>{{ get_phrase('Audio') }}</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($lessons as $lesson)
                                <tr>
                                    <td>{{ $lesson->sort }}</td>
                                    <td>{{ Str::limit($lesson->title, 50) }}</td>
                                    <td><span class="badge bg-secondary">{{ $lesson->lesson_type }}</span></td>
                                    <td>{{ $lesson->ai_lesson_format ?? '—' }}</td>
                                    <td>
                                        @if ($lesson->video_provider === 'heygen')
                                            <span class="badge bg-{{ $lesson->video_status === 'ready' ? 'success' : ($lesson->video_status === 'failed' ? 'danger' : 'warning') }}">
                                                {{ $lesson->video_status }}
                                            </span>
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td>{{ $lesson->audio_status ?? '—' }}</td>
                                    <td class="text-end text-nowrap">
                                        @if ($lesson->video_provider === 'heygen')
                                            <form action="{{ route('admin.lesson.studio.regenerate.video', [$course->id, $lesson->id]) }}" method="post" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn ol-btn-outline-secondary ol-btn-sm py-0" title="{{ get_phrase('Regenerate video') }}"><i class="fi-rr-refresh"></i></button>
                                            </form>
                                        @endif
                                        @if ($lesson->ai_lesson_format === 'audio')
                                            <form action="{{ route('admin.lesson.studio.fix.audio', [$course->id, $lesson->id]) }}" method="post" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn ol-btn-outline-secondary ol-btn-sm py-0" title="{{ get_phrase('Fix audio') }}"><i class="fi-rr-volume"></i></button>
                                            </form>
                                        @endif
                                        <a href="{{ route('admin.course.edit', ['id' => $course->id, 'tab' => 'curriculum']) }}" class="btn ol-btn-outline-secondary ol-btn-sm py-0"><i class="fi-rr-pen-clip"></i></a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-muted text-center py-4">{{ get_phrase('No lessons yet. Use AI generation or add content.') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
@php
    $avatarPreviews = collect($avatars)->mapWithKeys(fn ($a) => [$a['avatar_id'] => $a['preview_image_url'] ?? null])->all();
@endphp
<script>
    (function() {
        const previews = @json($avatarPreviews);
        const sel = document.querySelector('select[name="heygen_avatar_id"]');
        const img = document.getElementById('avatar-preview');
        function updatePreview() {
            if (img && sel && previews[sel.value]) {
                img.src = previews[sel.value];
                img.style.display = 'block';
            }
        }
        sel?.addEventListener('change', updatePreview);
        updatePreview();
    })();
</script>
@endpush
