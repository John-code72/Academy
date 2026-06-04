@php
    $geminiAssistant = app(\App\Services\GeminiVisionChatService::class);
@endphp

@if ($geminiAssistant->isConfigured() && Route::currentRouteName() !== 'ai.assistant.page')
    <link rel="stylesheet" href="{{ asset('assets/global/gemini-assistant/gemini-assistant.css') }}">

    <button type="button" id="gemini-assistant-fab" class="gemini-assistant-fab" title="{{ ai_assistant_name() }}">
        <i class="fi-rr-comment-alt"></i>
    </button>

    <div id="gemini-assistant-panel" class="gemini-assistant-panel">
        <div class="gemini-assistant-header">
            <div>
                <h6>{{ ai_assistant_name() }}</h6>
                <small id="gemini-assistant-status">{{ get_phrase('Live screen sharing') }}</small>
            </div>
            <a href="{{ route('ai.assistant.page') }}" class="gemini-assistant-expand" title="{{ get_phrase('Open full page') }}">
                <i class="fi-rr-expand"></i>
            </a>
            <button type="button" id="gemini-assistant-close" class="gemini-assistant-close" aria-label="{{ get_phrase('Close') }}">
                <i class="fi-rr-cross-small"></i>
            </button>
        </div>

        @include('components.gemini-assistant.chat-body')
    </div>

    @include('components.gemini-assistant.voice-overlay')

    @include('components.gemini-assistant.config-script', ['mode' => 'widget'])
    <script src="{{ asset('assets/global/gemini-assistant/gemini-assistant.js') }}"></script>
    <script src="{{ asset('assets/global/gemini-assistant/gemini-live-voice.js') }}"></script>
@endif
