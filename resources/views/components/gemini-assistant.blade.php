@php
    $geminiAssistant = app(\App\Services\GeminiVisionChatService::class);
    $coachBootstrap = app(\App\Services\CoachCurriculumService::class)->clientBootstrap(auth()->id());
@endphp

@if ($geminiAssistant->isConfigured() && Route::currentRouteName() !== 'ai.assistant.page')
    <link rel="stylesheet" href="{{ asset('assets/global/gemini-assistant/gemini-assistant.css') }}?v=20260616">

    <button type="button" id="gemini-assistant-fab" class="gemini-assistant-fab" title="{{ ai_assistant_name() }}">
        <i class="fi-rr-comment-alt"></i>
    </button>

    <div id="gemini-assistant-panel" class="gemini-assistant-panel">
        <div class="gemini-assistant-header">
            <div>
                <h6>{{ ai_assistant_name() }}</h6>
                <small id="gemini-assistant-status">{{ get_phrase('Ready to guide you') }}</small>
            </div>
            <a href="{{ route('ai.assistant.page') }}" class="gemini-assistant-expand" title="{{ get_phrase('Open full page') }}">
                <i class="fi-rr-expand"></i>
            </a>
            <button type="button" id="gemini-assistant-close" class="gemini-assistant-close" aria-label="{{ get_phrase('Close') }}">
                <i class="fi-rr-cross-small"></i>
            </button>
        </div>

        @include('components.gemini-assistant.chat-body', [
            'openingMessage' => $coachBootstrap['opening_message'] ?? '',
            'coachingTracks' => $coachBootstrap['tracks'] ?? [],
            'defaultTrack' => $coachBootstrap['default_track'] ?? '',
        ])
    </div>

    @include('components.gemini-assistant.voice-overlay')

    @include('components.gemini-assistant.config-script', ['mode' => 'widget', 'coachBootstrap' => $coachBootstrap])
    <script src="{{ asset('assets/global/gemini-assistant/gemini-assistant.js') }}?v=20260616"></script>
    <script src="{{ asset('assets/global/gemini-assistant/gemini-live-voice.js') }}?v=20260613"></script>
@endif
