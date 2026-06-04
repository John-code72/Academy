<div id="gemini-assistant-panel" class="gemini-assistant-panel gemini-assistant-page gemini-chatgpt-layout is-open">
    {{-- Colonne gauche : chat --}}
    <div class="gemini-chatgpt-chat">
        <div class="gemini-chatgpt-chat-header">
            <div class="gemini-chatgpt-chat-header-main">
                <div class="gemini-chatgpt-avatar">
                    <i class="fi-rr-sparkles"></i>
                </div>
                <div>
                    <h5>{{ ai_assistant_name() }}</h5>
                    <small id="gemini-assistant-status">{{ get_phrase('Ready to help') }}</small>
                </div>
            </div>
        </div>

        <div id="gemini-assistant-messages" class="gemini-assistant-messages gemini-chatgpt-messages"></div>

        <div class="gemini-chatgpt-composer">
            <div id="gemini-assistant-attached" class="gemini-assistant-attached">
                <img id="gemini-assistant-attached-img" src="" alt="">
                <span id="gemini-assistant-attached-label">{{ get_phrase('Live frame attached') }}</span>
            </div>

            <div class="gemini-assistant-toolbar gemini-chatgpt-toolbar">
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
                <button type="button" id="gemini-assistant-analyze" class="gemini-assistant-analyze-btn">
                    <i class="fi-rr-search"></i> {{ get_phrase('Analyze') }}
                </button>
            </div>

            <div class="gemini-assistant-input-row gemini-chatgpt-input-row">
                <textarea id="gemini-assistant-input" rows="1" placeholder="{{ get_phrase('Type your message...') }}"></textarea>
                <button type="button" id="gemini-assistant-send" class="gemini-assistant-send" aria-label="{{ get_phrase('Send') }}">
                    <i class="fi-rr-paper-plane"></i>
                </button>
            </div>
            <p class="gemini-chatgpt-hint">{{ get_phrase('Share your screen on the right panel — the assistant sees it live with each message.') }}</p>
        </div>
    </div>

    {{-- Colonne droite : écran / caméra --}}
    <div class="gemini-chatgpt-screen" id="gemini-assistant-screen-panel">
        <div class="gemini-chatgpt-screen-header">
            <h6>{{ get_phrase('Live preview') }}</h6>
            <button type="button" id="gemini-assistant-stop" class="gemini-assistant-stop-btn gemini-chatgpt-stop">
                {{ get_phrase('Stop') }}
            </button>
        </div>

        <div id="gemini-assistant-screen-empty" class="gemini-chatgpt-screen-empty">
            <div class="gemini-chatgpt-screen-empty-icon">
                <i class="fi-rr-desktop"></i>
            </div>
            <h6>{{ get_phrase('Share your screen') }}</h6>
            <p>{{ get_phrase('Your screen or camera will appear here in real time. The assistant uses this view to help you.') }}</p>
            <button type="button" class="gemini-chatgpt-screen-start" data-action="share-screen">
                <i class="fi-rr-desktop"></i> {{ get_phrase('Share screen') }}
            </button>
            <button type="button" class="gemini-chatgpt-camera-start" data-action="share-camera">
                <i class="fi-rr-camera"></i> {{ get_phrase('Use camera') }}
            </button>
        </div>

        <div id="gemini-assistant-preview" class="gemini-assistant-preview gemini-chatgpt-preview">
            <video id="gemini-assistant-preview-video" autoplay playsinline muted></video>
            <canvas id="gemini-assistant-capture-canvas" class="gemini-assistant-capture-canvas"></canvas>
            <div id="gemini-assistant-live-badge" class="gemini-assistant-live-badge">
                <span class="gemini-assistant-live-dot"></span>
                <span id="gemini-assistant-live-badge-text">{{ get_phrase('SCREEN LIVE') }}</span>
            </div>
        </div>
    </div>
</div>
