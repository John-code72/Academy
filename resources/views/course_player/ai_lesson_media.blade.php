@php
    $aiFormat = $lesson_details->ai_lesson_format ?? null;
    $hasAiMedia = $aiFormat || $lesson_details->video_provider === 'heygen' || $lesson_details->audio_url || $lesson_details->audio_status === 'browser';
@endphp

@if ($hasAiMedia)
    @if ($lesson_details->video_provider === 'heygen' && $lesson_details->video_status === 'pending')
        <div class="alert alert-info mb-3" id="main-heygen-pending"
             data-lesson-id="{{ $lesson_details->id }}"
             data-course-id="{{ $lesson_details->course_id }}">
            <i class="fi fi-rr-spinner"></i>
            {{ get_phrase('AI presenter video is being generated. This page will refresh when ready.') }}
        </div>
    @elseif ($lesson_details->video_provider === 'heygen' && $lesson_details->video_status === 'failed')
        <div class="alert alert-danger mb-3">
            <strong>{{ get_phrase('Video generation failed') }}</strong>
            @if ($lesson_details->video_error)
                <div class="small mt-1">{{ $lesson_details->video_error }}</div>
            @endif
        </div>
    @endif

    @if (($aiFormat === 'audio' || $lesson_details->audio_status) && ($lesson_details->audio_url || $lesson_details->video_script))
        <div class="mb-4 p-3 rounded-3" style="background: linear-gradient(135deg, #ecfeff, #eef2ff); border: 1px solid #a5f3fc;">
            @if ($lesson_details->audio_url)
                @php
                    $mainAudioSrc = $lesson_details->audio_url;
                    if (preg_match('#/storage/.+#i', $mainAudioSrc, $m)) {
                        $mainAudioSrc = url(ltrim($m[0], '/'));
                    } elseif (!preg_match('#^https?://#i', $mainAudioSrc)) {
                        $mainAudioSrc = url(ltrim($mainAudioSrc, '/'));
                    }
                    $mainAudioMime = str_contains(strtolower($lesson_details->audio_url), '.wav') ? 'audio/wav' : 'audio/mpeg';
                @endphp
                <audio controls preload="metadata" class="w-100 mb-2">
                    <source src="{{ $mainAudioSrc }}" type="{{ $mainAudioMime }}">
                </audio>
                <span class="small text-muted"><i class="fi fi-rr-volume"></i> {{ get_phrase('AI narration (Gemini)') }}</span>
            @elseif ($lesson_details->video_script)
                <p class="small text-muted mb-2">{{ get_phrase('Listen with browser voice:') }}</p>
                <button type="button" class="btn btn-primary btn-sm" id="main-browser-listen">
                    <i class="fi fi-rr-play"></i> {{ get_phrase('Listen') }}
                </button>
                <script type="application/json" id="main-audio-script-json">@json($lesson_details->video_script)</script>
            @endif
        </div>
    @endif
@endif

@push('js')
<script>
(function() {
    const pending = document.getElementById('main-heygen-pending');
    if (!pending) return;
    const lessonId = pending.dataset.lessonId;
    const url = @json(route('player.lesson.heygen.status', ['lesson' => $lesson_details->id ?? 0]));
    let tries = 0;
    const maxTries = 40;
    const timer = setInterval(function() {
        tries++;
        fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } })
            .then(r => r.json())
            .then(data => {
                if (data.reload) {
                    clearInterval(timer);
                    location.reload();
                } else if (data.status === 'failed') {
                    clearInterval(timer);
                    pending.className = 'alert alert-danger mb-3';
                    pending.innerHTML = '<strong>{{ get_phrase('Video generation failed') }}</strong>'
                        + (data.error ? '<div class="small mt-1">' + data.error + '</div>' : '');
                }
            })
            .catch(() => {});
        if (tries >= maxTries) clearInterval(timer);
    }, 15000);
})();
</script>
@endpush
