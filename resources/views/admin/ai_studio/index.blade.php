@extends('layouts.admin')
@push('title', get_phrase('AI Studio'))
@push('meta')@endpush
@push('css')@endpush
@section('content')
    <div class="ol-card radius-8px">
        <div class="ol-card-body my-3 py-4 px-20px">
            <div class="d-flex align-items-center justify-content-between gap-3 flex-wrap flex-md-nowrap">
                <h4 class="title fs-16px mb-0">
                    <i class="fi-rr-magic-wand me-2"></i>
                    {{ get_phrase('AI Studio') }}
                </h4>
                <a href="{{ route('admin.open.ai.settings') }}" class="btn ol-btn-light ol-btn-sm">
                    <i class="fi-rr-settings"></i> {{ get_phrase('API keys & providers') }}
                </a>
            </div>
            <p class="text-muted small mb-0 mt-2">
                {{ get_phrase('Global tools: create all courses by category, or open Lesson Studio per course (avatar, voice, PDF, AI).') }}
            </p>
        </div>
    </div>

    <div class="ol-card p-4 mb-3">
        <h5 class="fs-14px mb-3">{{ get_phrase('Lesson Studio (per course)') }}</h5>
        <form method="get" action="" class="row g-2 align-items-end" id="goto-lesson-studio">
            <div class="col-md-8">
                <label class="form-label ol-form-label">{{ get_phrase('Select course') }}</label>
                <select class="form-control ol-form-control ol-select2" id="lesson-studio-course-pick">
                    <option value="">{{ get_phrase('Choose a course…') }}</option>
                    @foreach ($courses as $c)
                        <option value="{{ route('admin.lesson.studio', $c->id) }}">#{{ $c->id }} — {{ $c->title }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <button type="button" class="btn ol-btn-primary w-100" onclick="const u=document.getElementById('lesson-studio-course-pick').value;if(u)location.href=u;">
                    {{ get_phrase('Open Lesson Studio') }}
                </button>
            </div>
        </form>
    </div>

    <div class="row g-3 my-3">
        <div class="col-md-4">
            <div class="ol-card h-100 {{ $openAiOk ? 'border-success' : 'border-warning' }}">
                <div class="ol-card-body px-20px py-3">
                    <p class="sub-title fs-12px text-uppercase mb-1">OpenAI / DeepSeek</p>
                    <p class="title fs-16px mb-0">
                        @if ($openAiOk)
                            <span class="text-success"><i class="fi-rr-check"></i> {{ get_phrase('Configured') }}</span>
                        @else
                            <span class="text-warning"><i class="fi-rr-exclamation"></i> {{ get_phrase('Missing key') }}</span>
                        @endif
                    </p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="ol-card h-100 {{ $geminiOk ? 'border-success' : 'border-warning' }}">
                <div class="ol-card-body px-20px py-3">
                    <p class="sub-title fs-12px text-uppercase mb-1">Gemini (audio)</p>
                    <p class="title fs-16px mb-0">
                        @if ($geminiOk)
                            <span class="text-success"><i class="fi-rr-check"></i> {{ get_phrase('Configured') }}</span>
                        @else
                            <span class="text-warning"><i class="fi-rr-exclamation"></i> GEMINI_API_KEY</span>
                        @endif
                    </p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="ol-card h-100 {{ $heygenOk ? 'border-success' : 'border-warning' }}">
                <div class="ol-card-body px-20px py-3">
                    <p class="sub-title fs-12px text-uppercase mb-1">HeyGen (video)</p>
                    <p class="title fs-16px mb-0">
                        @if ($heygenOk)
                            <span class="text-success"><i class="fi-rr-check"></i> {{ get_phrase('Configured') }}</span>
                        @else
                            <span class="text-warning"><i class="fi-rr-exclamation"></i> {{ get_phrase('Missing key') }}</span>
                        @endif
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-6">
            <div class="ol-card">
                <div class="ol-card-body px-20px py-3">
                    <p class="sub-title fs-14px">{{ get_phrase('Main courses — HeyGen pending') }}</p>
                    <p class="title fs-24px mb-0">{{ $mainPending }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="ol-card">
                <div class="ol-card-body px-20px py-3">
                    <p class="sub-title fs-14px">{{ get_phrase('Personalized courses — HeyGen pending') }}</p>
                    <p class="title fs-24px mb-0">{{ $personalizedPending }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-5">
            <div class="ol-card p-4 h-100">
                <h5 class="title fs-14px mb-3">{{ get_phrase('Create all courses from scratch') }}</h5>
                <p class="small text-muted">
                    {{ get_phrase('Categories') }}: <strong>{{ $categoryCount }}</strong>
                    — {{ get_phrase('already with a course') }}: <strong>{{ $categoriesWithCourse }}</strong>
                </p>
                @if (!$openAiOk)
                    <div class="alert alert-warning small py-2">
                        {{ get_phrase('Configure OpenAI or DeepSeek API key in') }}
                        <a href="{{ route('admin.open.ai.settings') }}">{{ get_phrase('Settings') }}</a>.
                    </div>
                @endif
                @if ($categoryCount < 1)
                    <div class="alert alert-warning small py-2">
                        {{ get_phrase('Add at least one category before generating courses.') }}
                    </div>
                @endif
                <form action="{{ route('admin.ai.studio.generate') }}" method="post"
                    onsubmit="return confirm({{ json_encode(get_phrase('This will create one full course per category. It may take several minutes. Continue?')) }});">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label ol-form-label">{{ get_phrase('Language') }}</label>
                        <input type="text" name="language" class="form-control ol-form-control" value="French" placeholder="French, English…">
                    </div>
                    <div class="mb-3">
                        <label class="form-label ol-form-label">{{ get_phrase('Lessons per course') }}</label>
                        <input type="number" name="lesson_count" class="form-control ol-form-control" value="6" min="3" max="12">
                        <small class="text-muted">{{ get_phrase('Video HeyGen + audio Gemini + text per course') }}</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label ol-form-label">{{ get_phrase('Academy theme (optional)') }}</label>
                        <textarea name="brief" class="form-control ol-form-control" rows="3"
                            placeholder="{{ get_phrase('e.g. Professional training for adults, practical and progressive…') }}"></textarea>
                    </div>
                    <div class="mb-3 form-check">
                        <input type="hidden" name="skip_existing" value="0">
                        <input type="checkbox" class="form-check-input" name="skip_existing" id="skip_existing" value="1" checked>
                        <label class="form-check-label" for="skip_existing">
                            {{ get_phrase('Skip categories that already have a course') }}
                        </label>
                    </div>
                    <button type="submit" class="btn ol-btn-primary w-100">
                        <i class="fi fi-rr-sparkles"></i> {{ get_phrase('Create all courses with AI') }}
                    </button>
                </form>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="ol-card p-4 mb-3">
                <h5 class="title fs-14px mb-3">{{ get_phrase('Maintenance actions') }}</h5>
                <div class="d-flex flex-wrap gap-2">
                    <form action="{{ route('admin.ai.studio.refresh.heygen') }}" method="post" class="d-inline">
                        @csrf
                        <input type="hidden" name="scope" value="all">
                        <button type="submit" class="btn ol-btn-light ol-btn-sm" @disabled(!$heygenOk)>
                            <i class="fi-rr-refresh"></i> {{ get_phrase('Refresh all HeyGen') }}
                        </button>
                    </form>
                    <form action="{{ route('admin.ai.studio.fix.audio') }}" method="post" class="d-inline">
                        @csrf
                        <button type="submit" class="btn ol-btn-light ol-btn-sm" @disabled(!$geminiOk)>
                            <i class="fi-rr-volume"></i> {{ get_phrase('Fix all Gemini audio') }}
                        </button>
                    </form>
                </div>
                <p class="small text-muted mt-2 mb-0">
                    {{ get_phrase('Refreshes pending HeyGen jobs and retries failed ones. Regenerates missing or broken lesson audio via Gemini.') }}
                </p>
            </div>

            <div class="ol-card p-4">
                <h5 class="title fs-14px mb-3">{{ get_phrase('Courses with AI lessons') }}</h5>
                <div class="table-responsive">
                    <table class="table eTable eTable-2">
                        <thead>
                            <tr>
                                <th>{{ get_phrase('Course') }}</th>
                                <th>{{ get_phrase('Video') }}</th>
                                <th>{{ get_phrase('Audio') }}</th>
                                <th>HeyGen</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($courses as $course)
                                @php $s = $courseStats[$course->id] ?? null; @endphp
                                @if ($s && ((int) $s->video_lessons + (int) $s->audio_lessons) > 0)
                                    <tr>
                                        <td>
                                            <a href="{{ route('admin.course.edit', ['id' => $course->id, 'tab' => 'curriculum']) }}">
                                                {{ Str::limit($course->title, 40) }}
                                            </a>
                                        </td>
                                        <td>{{ (int) $s->video_lessons }}</td>
                                        <td>{{ (int) $s->audio_lessons }}</td>
                                        <td>
                                            @if ((int) $s->heygen_pending > 0)
                                                <span class="badge bg-warning">{{ (int) $s->heygen_pending }} {{ get_phrase('pending') }}</span>
                                            @endif
                                            @if ((int) $s->heygen_failed > 0)
                                                <span class="badge bg-danger">{{ (int) $s->heygen_failed }} {{ get_phrase('failed') }}</span>
                                            @endif
                                            @if ((int) $s->heygen_ready > 0)
                                                <span class="badge bg-success">{{ (int) $s->heygen_ready }} OK</span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            <form action="{{ route('admin.ai.studio.refresh.heygen') }}" method="post" class="d-inline">
                                                @csrf
                                                <input type="hidden" name="scope" value="main">
                                                <input type="hidden" name="course_id" value="{{ $course->id }}">
                                                <button type="submit" class="btn ol-btn-outline-secondary ol-btn-sm py-0 px-2" title="{{ get_phrase('Refresh HeyGen') }}">
                                                    <i class="fi-rr-refresh"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endif
                            @empty
                                <tr>
                                    <td colspan="5" class="text-muted">{{ get_phrase('No courses') }}</td>
                                </tr>
                            @endforelse
                            @if ($courses->isNotEmpty() && $courseStats->isEmpty())
                                <tr>
                                    <td colspan="5" class="text-muted">{{ get_phrase('No AI lessons yet. Use Create all courses above.') }}</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
