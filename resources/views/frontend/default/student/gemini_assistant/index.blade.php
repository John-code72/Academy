@extends('layouts.default')
@push('title', ai_assistant_name())
@push('meta')@endpush
@push('css')
    <link rel="stylesheet" href="{{ asset('assets/global/gemini-assistant/gemini-assistant.css') }}?v=20260619">
@endpush

@section('content')
    <section class="gemini-chatgpt-page">
        <div class="container-fluid gemini-chatgpt-container">
            <header class="gemini-chatgpt-topbar">
                <a href="{{ route('dashboard') }}" class="gemini-chatgpt-back" title="{{ get_phrase('Back') }}">
                    <i class="fi-rr-arrow-left"></i>
                    <span class="gemini-chatgpt-back-label">{{ get_phrase('Back') }}</span>
                </a>
                <div class="gemini-chatgpt-topbar-center">
                    <div class="gemini-chatgpt-avatar" aria-hidden="true">
                        <i class="fi-rr-sparkles"></i>
                    </div>
                    <div class="gemini-chatgpt-topbar-meta">
                        <h1 class="gemini-chatgpt-topbar-title">{{ ai_assistant_name() }}</h1>
                        <p id="gemini-assistant-status" class="gemini-chatgpt-topbar-status">{{ get_phrase('Ready to guide you') }}</p>
                    </div>
                </div>
                <span class="gemini-chatgpt-topbar-spacer" aria-hidden="true"></span>
            </header>

            @include('components.gemini-assistant.chat-body-page', [
                'coachingTracks' => $coachingTracks ?? [],
                'defaultTrack' => $defaultTrack ?? '',
                'openingMessage' => $openingMessage ?? '',
            ])
            @include('components.gemini-assistant.voice-overlay')
        </div>
    </section>
@endsection

@push('js')
    <script>document.body.classList.add('gemini-assistant-chat-page');</script>
    @include('components.gemini-assistant.config-script', [
        'mode' => 'page',
        'coachBootstrap' => $coachBootstrap ?? [],
    ])
    <script src="{{ asset('assets/global/gemini-assistant/gemini-assistant.js') }}?v=20260619"></script>
    <script src="{{ asset('assets/global/gemini-assistant/gemini-live-voice.js') }}?v=20260613"></script>
@endpush
