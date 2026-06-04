<div id="gemini-live-voice-overlay" class="gemini-live-voice-overlay" aria-hidden="true">
    <div class="gemini-live-voice-panel">
        <div class="gemini-live-voice-header">
            <span class="gemini-live-voice-badge" id="gemini-live-voice-badge">{{ get_phrase('Voice') }}</span>
            <h3>{{ ai_assistant_name() }}</h3>
            <p class="gemini-live-voice-subtitle">{{ get_phrase('Live voice') }}</p>
        </div>

        <div class="gemini-live-voice-orb-wrap">
            <div class="gemini-live-voice-orb" id="gemini-live-voice-orb"></div>
            <div class="gemini-live-voice-ring gemini-live-voice-ring-1"></div>
            <div class="gemini-live-voice-ring gemini-live-voice-ring-2"></div>
        </div>

        <p id="gemini-live-voice-status" class="gemini-live-voice-status">{{ get_phrase('Connecting...') }}</p>

        <div class="gemini-live-voice-media">
            <div id="gemini-live-voice-preview-wrap" class="gemini-live-voice-preview-wrap" hidden>
                <video id="gemini-live-voice-preview-video" autoplay playsinline muted></video>
                <canvas id="gemini-live-voice-capture-canvas" class="gemini-live-voice-capture-canvas" aria-hidden="true"></canvas>
                <span id="gemini-live-voice-preview-badge" class="gemini-live-voice-preview-badge">{{ get_phrase('LIVE') }}</span>
            </div>
            <div class="gemini-live-voice-media-toolbar">
                <button type="button" id="gemini-live-voice-screen" class="gemini-live-voice-media-btn">
                    <i class="fi-rr-desktop"></i> {{ get_phrase('Screen') }}
                </button>
                <button type="button" id="gemini-live-voice-camera" class="gemini-live-voice-media-btn">
                    <i class="fi-rr-camera"></i> {{ get_phrase('Camera') }}
                </button>
                <button type="button" id="gemini-live-voice-stop-share" class="gemini-live-voice-media-btn gemini-live-voice-stop-share" hidden>
                    <i class="fi-rr-cross-small"></i> {{ get_phrase('Stop sharing') }}
                </button>
            </div>
        </div>

        <div class="gemini-live-voice-transcript-wrap">
            <p id="gemini-live-voice-transcript" class="gemini-live-voice-transcript"></p>
        </div>

        <div class="gemini-live-voice-actions">
            <button type="button" id="gemini-live-voice-stop" class="gemini-live-voice-stop">
                <i class="fi-rr-cross-small"></i> {{ get_phrase('End conversation') }}
            </button>
        </div>
    </div>
</div>
