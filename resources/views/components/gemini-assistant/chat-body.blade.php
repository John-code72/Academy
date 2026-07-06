<div class="gemini-assistant-chat-body">
    @include('components.gemini-assistant.coach-panel', [
        'coachingTracks' => $coachingTracks ?? [],
        'defaultTrack' => $defaultTrack ?? '',
    ])

    <div id="gemini-assistant-preview" class="gemini-assistant-preview gemini-widget-preview-dock">
        <video id="gemini-assistant-preview-video" autoplay playsinline muted></video>
        <canvas id="gemini-assistant-capture-canvas" class="gemini-assistant-capture-canvas"></canvas>
        <div id="gemini-assistant-live-badge" class="gemini-assistant-live-badge">
            <span class="gemini-assistant-live-dot"></span>
            <span id="gemini-assistant-live-badge-text">{{ get_phrase('LIVE') }}</span>
        </div>
        <button type="button" id="gemini-assistant-stop" class="gemini-assistant-stop-btn gemini-widget-preview-stop" title="{{ get_phrase('Stop') }}">
            <i class="fi-rr-cross-small"></i>
        </button>
    </div>

    <div id="gemini-assistant-messages" class="gemini-assistant-messages">
        @if (!empty($openingMessage))
            <div class="gemini-assistant-msg model" id="gemini-coach-opening-msg">{{ $openingMessage }}</div>
        @endif
    </div>

    <div class="gemini-chat-composer">
        <div class="gemini-assistant-toolbar">
            <button type="button" id="gemini-assistant-voice-live" class="gemini-assistant-voice-live-btn" title="{{ get_phrase('Voice') }}">
                <i class="fi-rr-microphone"></i>
            </button>
            <button type="button" id="gemini-assistant-screen" class="gemini-assistant-screen-btn" title="{{ get_phrase('Screen') }}">
                <i class="fi-rr-desktop"></i>
            </button>
            <button type="button" id="gemini-assistant-camera" title="{{ get_phrase('Camera') }}">
                <i class="fi-rr-camera"></i>
            </button>
            <button type="button" id="gemini-assistant-speak" class="gemini-assistant-speak-btn" title="{{ get_phrase('Listen') }}">
                <i class="fi-rr-volume"></i>
            </button>
        </div>

        <div id="gemini-assistant-attached" class="gemini-assistant-attached">
            <img id="gemini-assistant-attached-img" src="" alt="">
            <span id="gemini-assistant-attached-label">{{ get_phrase('Current screen frame will be sent with your message') }}</span>
            <button type="button" id="gemini-assistant-attached-remove" aria-label="{{ get_phrase('Remove') }}">&times;</button>
        </div>

        <div id="gemini-assistant-quick-replies" class="gemini-quick-replies" hidden></div>

        <div class="gemini-assistant-input-row">
            <textarea id="gemini-assistant-input" rows="1" placeholder="{{ get_phrase('Your answer or reflection…') }}"></textarea>
            <button type="button" id="gemini-assistant-send" class="gemini-assistant-send" aria-label="{{ get_phrase('Send') }}">
                <i class="fi-rr-paper-plane"></i>
            </button>
        </div>
    </div>
</div>
