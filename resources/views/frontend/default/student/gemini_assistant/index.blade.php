@extends('layouts.default')
@push('title', ai_assistant_name())
@push('meta')@endpush
@push('css')
    <link rel="stylesheet" href="{{ asset('assets/global/gemini-assistant/gemini-assistant.css') }}">
@endpush

@section('content')
    <section class="gemini-chatgpt-page">
        <div class="container-fluid gemini-chatgpt-container">
            <div class="gemini-chatgpt-topbar">
                <a href="{{ route('dashboard') }}" class="gemini-chatgpt-back">
                    <i class="fi-rr-arrow-left"></i> {{ get_phrase('Back') }}
                </a>
                <span class="gemini-chatgpt-topbar-title">{{ ai_assistant_name() }}</span>
            </div>

            @include('components.gemini-assistant.chat-body-page')
            @include('components.gemini-assistant.voice-overlay')
        </div>
    </section>
@endsection

@push('js')
    <script>document.body.classList.add('gemini-assistant-chat-page');</script>
    @include('components.gemini-assistant.config-script', ['mode' => 'page'])
    <script src="{{ asset('assets/global/gemini-assistant/gemini-assistant.js') }}"></script>
    <script src="{{ asset('assets/global/gemini-assistant/gemini-live-voice.js') }}"></script>
@endpush
