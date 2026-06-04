<div class="gemini-assistant-chat-body">
    <div id="gemini-assistant-preview" class="gemini-assistant-preview">
        <video id="gemini-assistant-preview-video" autoplay playsinline muted></video>
        <canvas id="gemini-assistant-capture-canvas" class="gemini-assistant-capture-canvas"></canvas>
        <div id="gemini-assistant-live-badge" class="gemini-assistant-live-badge">
            <span class="gemini-assistant-live-dot"></span>
            <span id="gemini-assistant-live-badge-text">{{ get_phrase('SCREEN LIVE') }}</span>
        </div>
        <div class="gemini-assistant-preview-actions">
            <button type="button" id="gemini-assistant-analyze" class="gemini-assistant-analyze-btn">
                {{ get_phrase('Analyze screen') }}
            </button>
            <button type="button" id="gemini-assistant-stop" class="gemini-assistant-stop-btn">
                {{ get_phrase('Stop') }}
            </button>
        </div>
    </div>

    <div id="gemini-assistant-messages" class="gemini-assistant-messages"></div>

    <div class="gemini-assistant-toolbar">
        <button type="button" id="gemini-assistant-voice-live" class="gemini-assistant-voice-live-btn">
            <i class="fi-rr-microphone"></i> {{ get_phrase('Voice') }}
        </button>
        <button type="button" id="gemini-assistant-screen" class="gemini-assistant-screen-btn">
            <i class="fi-rr-desktop"></i> {{ get_phrase('Screen') }}
        </button>
        <button type="button" id="gemini-assistant-camera">
            <i class="fi-rr-camera"></i> {{ get_phrase('Camera') }}
        </button>
        <button type="button" id="gemini-assistant-speak" class="gemini-assistant-speak-btn">
            <i class="fi-rr-volume"></i> {{ get_phrase('Listen') }}
        </button>
    </div>

    <div id="gemini-assistant-attached" class="gemini-assistant-attached">
        <img id="gemini-assistant-attached-img" src="" alt="">
        <span id="gemini-assistant-attached-label">{{ get_phrase('Current screen frame will be sent with your message') }}</span>
        <button type="button" id="gemini-assistant-attached-remove" aria-label="{{ get_phrase('Remove') }}">&times;</button>
    </div>

    <div class="gemini-assistant-input-row">
        <textarea id="gemini-assistant-input" rows="1" placeholder="{{ get_phrase('Ask while sharing your screen...') }}"></textarea>
        <button type="button" id="gemini-assistant-send" class="gemini-assistant-send" aria-label="{{ get_phrase('Send') }}">
            <i class="fi-rr-paper-plane"></i>
        </button>
    </div>
</div>
