(function () {
    'use strict';

    if (!window.GeminiAssistantConfig) {
        return;
    }

    var cfg = window.GeminiAssistantConfig;
    var isPageMode = cfg.mode === 'page';
    var PANEL_OPEN_KEY = 'ai_assistant_panel_open';
    var panel = document.getElementById('gemini-assistant-panel');
    var fab = document.getElementById('gemini-assistant-fab');
    var closeBtn = document.getElementById('gemini-assistant-close');
    var statusEl = document.getElementById('gemini-assistant-status');
    var messagesEl = document.getElementById('gemini-assistant-messages');
    var inputEl = document.getElementById('gemini-assistant-input');
    var sendBtn = document.getElementById('gemini-assistant-send');
    var previewWrap = document.getElementById('gemini-assistant-preview');
    var previewVideo = document.getElementById('gemini-assistant-preview-video');
    var captureCanvas = document.getElementById('gemini-assistant-capture-canvas');
    var liveBadge = document.getElementById('gemini-assistant-live-badge');
    var liveBadgeText = document.getElementById('gemini-assistant-live-badge-text');
    var stopBtn = document.getElementById('gemini-assistant-stop');
    var cameraBtn = document.getElementById('gemini-assistant-camera');
    var screenBtn = document.getElementById('gemini-assistant-screen');
    var speakBtn = document.getElementById('gemini-assistant-speak');
    var micBtn = document.getElementById('gemini-assistant-mic');
    var analyzeBtn = document.getElementById('gemini-assistant-analyze');
    var attachedWrap = document.getElementById('gemini-assistant-attached');
    var attachedImg = document.getElementById('gemini-assistant-attached-img');
    var attachedLabel = document.getElementById('gemini-assistant-attached-label');
    var attachedRemove = document.getElementById('gemini-assistant-attached-remove');
    var screenPanel = document.getElementById('gemini-assistant-screen-panel');
    var screenEmpty = document.getElementById('gemini-assistant-screen-empty');

    var history = [];
    var pendingImage = null;
    var mediaStream = null;
    var isSending = false;
    var livePreviewTimer = null;
    var streamSource = null;
    var canvasCtx = captureCanvas ? captureCanvas.getContext('2d') : null;
    var speechApi = window.speechSynthesis || null;
    var currentSpeech = null;
    var lastModelReply = '';
    var speechRecognition = null;
    var isListening = false;
    var micBaseText = '';
    var micFinalText = '';
    var micAutoSend = false;
    var voiceModeEnabled = false;
    var micRestartTimer = null;

    function csrfToken() {
        var meta = document.querySelector('meta[name="csrf-token"], meta[name="csrf_token"]');
        return meta ? meta.getAttribute('content') : '';
    }

    function isStreaming() {
        return !!mediaStream && mediaStream.getVideoTracks().some(function (track) {
            return track.readyState === 'live';
        });
    }

    function scrollChatToBottom() {
        if (!messagesEl) {
            return;
        }
        requestAnimationFrame(function () {
            messagesEl.scrollTop = messagesEl.scrollHeight;
        });
    }

    function focusInput() {
        if (!inputEl) {
            return;
        }
        try {
            inputEl.focus({ preventScroll: true });
        } catch (e) {
            inputEl.focus();
        }
    }

    function canUseSpeech() {
        return !!speechApi && typeof window.SpeechSynthesisUtterance !== 'undefined';
    }

    function setLastModelReply(text) {
        lastModelReply = (text || '').trim();
        updateSpeakUi();
    }

    function stopSpeaking() {
        if (!speechApi) {
            currentSpeech = null;
            updateSpeakUi();
            return;
        }
        speechApi.cancel();
        currentSpeech = null;
        updateSpeakUi();
    }

    function updateSpeakUi() {
        if (!speakBtn) {
            return;
        }
        speakBtn.classList.toggle('is-active', !!currentSpeech);
        speakBtn.disabled = !canUseSpeech() || (!lastModelReply && !currentSpeech);
    }

    function speakLatestReply() {
        if (!speakBtn) {
            return;
        }
        if (!canUseSpeech()) {
            appendMessage('model', cfg.errors.voiceUnsupported || 'Voice playback is not supported in this browser.');
            return;
        }
        if (currentSpeech) {
            stopSpeaking();
            return;
        }
        if (!lastModelReply) {
            appendMessage('model', cfg.labels.noReplyToRead || 'No reply to read yet.');
            return;
        }

        var utterance = new window.SpeechSynthesisUtterance(lastModelReply);
        utterance.lang = document.documentElement.lang || 'fr-FR';
        utterance.rate = 1;
        utterance.pitch = 1;
        utterance.onend = function () {
            currentSpeech = null;
            updateSpeakUi();
            if (voiceModeEnabled) {
                queueMicRestart(300);
            }
        };
        utterance.onerror = function () {
            currentSpeech = null;
            updateSpeakUi();
            if (voiceModeEnabled) {
                queueMicRestart(300);
            }
        };

        currentSpeech = utterance;
        updateSpeakUi();
        speechApi.cancel();
        speechApi.speak(utterance);
    }

    function speakText(text) {
        if (!text || !canUseSpeech()) {
            if (voiceModeEnabled) {
                queueMicRestart(200);
            }
            return;
        }
        if (speechApi) {
            speechApi.cancel();
        }
        currentSpeech = null;
        setLastModelReply(text);
        speakLatestReply();
    }

    function canUseMic() {
        return !!(window.SpeechRecognition || window.webkitSpeechRecognition);
    }

    function getSpeechLang() {
        var lang = (document.documentElement.lang || '').trim();
        if (lang) {
            return lang;
        }
        return 'fr-FR';
    }

    function getRecognition() {
        if (speechRecognition) {
            return speechRecognition;
        }
        var SpeechRecognitionCtor = window.SpeechRecognition || window.webkitSpeechRecognition;
        if (!SpeechRecognitionCtor) {
            return null;
        }

        speechRecognition = new SpeechRecognitionCtor();
        speechRecognition.continuous = false;
        speechRecognition.interimResults = true;
        speechRecognition.maxAlternatives = 1;
        speechRecognition.lang = getSpeechLang();

        speechRecognition.onresult = function (event) {
            var interim = '';
            var finalChunk = '';

            for (var i = event.resultIndex; i < event.results.length; i++) {
                var piece = event.results[i][0].transcript;
                if (event.results[i].isFinal) {
                    finalChunk += piece;
                } else {
                    interim += piece;
                }
            }

            if (finalChunk) {
                micFinalText += finalChunk;
            }

            if (inputEl) {
                var combined = (micBaseText + micFinalText + interim).trim();
                inputEl.value = combined;
            }
        };

        speechRecognition.onstart = function () {
            isListening = true;
            micAutoSend = true;
            updateMicUi();
            if (statusEl) {
                statusEl.textContent = cfg.labels.listening || 'Listening...';
            }
        };

        speechRecognition.onend = function () {
            var shouldSend = micAutoSend;
            micAutoSend = false;
            isListening = false;
            updateMicUi();
            updateLiveUi();

            var text = inputEl ? (inputEl.value || '').trim() : '';
            if (shouldSend && text && !isSending) {
                sendMessage(text);
            } else {
                focusInput();
                if (voiceModeEnabled) {
                    queueMicRestart(350);
                }
            }
        };

        speechRecognition.onerror = function (event) {
            micAutoSend = false;
            isListening = false;
            updateMicUi();
            updateLiveUi();

            if (!event || !event.error || event.error === 'aborted' || event.error === 'no-speech') {
                if (voiceModeEnabled) {
                    queueMicRestart(350);
                }
                return;
            }

            if (event.error === 'not-allowed' || event.error === 'service-not-allowed') {
                appendMessage('model', cfg.errors.micDenied || 'Microphone access denied.');
                return;
            }

            appendMessage('model', cfg.errors.micFailed || 'Could not start the microphone.');
        };

        return speechRecognition;
    }

    function updateMicUi() {
        if (!micBtn) {
            return;
        }
        micBtn.classList.toggle('is-listening', isListening);
        micBtn.classList.toggle('is-voice-mode', voiceModeEnabled);
        micBtn.disabled = !canUseMic() || isSending;
        micBtn.setAttribute('aria-pressed', voiceModeEnabled ? 'true' : 'false');
    }

    function stopListening() {
        micAutoSend = false;
        if (micRestartTimer) {
            clearTimeout(micRestartTimer);
            micRestartTimer = null;
        }
        if (speechRecognition && isListening) {
            try {
                speechRecognition.stop();
            } catch (e) {
                isListening = false;
                updateMicUi();
            }
            return;
        }
        isListening = false;
        updateMicUi();
    }

    function queueMicRestart(delayMs) {
        if (!voiceModeEnabled || isListening || isSending || currentSpeech) {
            return;
        }
        if (micRestartTimer) {
            clearTimeout(micRestartTimer);
        }
        micRestartTimer = setTimeout(function () {
            micRestartTimer = null;
            if (voiceModeEnabled && !isListening && !isSending && !currentSpeech) {
                startListening();
            }
        }, delayMs || 250);
    }

    function setVoiceMode(enabled, silent) {
        voiceModeEnabled = !!enabled;
        if (!voiceModeEnabled) {
            stopListening();
            stopSpeaking();
            if (statusEl) {
                statusEl.textContent = cfg.labels.ready || cfg.labels.liveOff || 'Ready to help';
            }
            if (!silent) {
                appendMessage('model', cfg.labels.voiceModeOff || 'Voice mode disabled.');
            }
            updateMicUi();
            return;
        }

        if (!canUseMic()) {
            voiceModeEnabled = false;
            updateMicUi();
            appendMessage('model', cfg.errors.micUnsupported || 'Speech recognition is not supported in this browser.');
            return;
        }

        if (!silent) {
            appendMessage('model', cfg.labels.voiceModeOn || 'Voice mode enabled. Speak naturally, I will answer by voice.');
        }
        queueMicRestart(150);
        updateMicUi();
    }

    function startListening() {
        if (!micBtn || isSending) {
            return;
        }
        if (!canUseMic()) {
            appendMessage('model', cfg.errors.micUnsupported || 'Speech recognition is not supported in this browser.');
            return;
        }

        stopSpeaking();

        var recognition = getRecognition();
        if (!recognition) {
            appendMessage('model', cfg.errors.micUnsupported || 'Speech recognition is not supported in this browser.');
            return;
        }

        micBaseText = inputEl ? (inputEl.value || '') : '';
        if (micBaseText && !/\s$/.test(micBaseText)) {
            micBaseText += ' ';
        }
        micFinalText = '';
        recognition.lang = getSpeechLang();

        try {
            recognition.start();
        } catch (err) {
            if (err && err.name === 'InvalidStateError') {
                stopListening();
                setTimeout(startListening, 200);
                return;
            }
            appendMessage('model', cfg.errors.micFailed || 'Could not start the microphone.');
        }
    }

    function toggleListening() {
        setVoiceMode(!voiceModeEnabled);
    }

    function ensureComposerVisible() {
        var composer = document.querySelector('.gemini-chatgpt-composer');
        if (composer) {
            composer.style.display = 'block';
            composer.style.visibility = 'visible';
        }
        if (inputEl) {
            inputEl.style.display = 'block';
            inputEl.style.visibility = 'visible';
        }
    }

    function fitChatPageLayout() {
        if (!isPageMode) {
            return;
        }

        var page = document.querySelector('.gemini-chatgpt-page');
        if (!page) {
            return;
        }

        var top = 0;
        var chromeSelectors = ['.top-bar', '.header-area', 'header', '.navbar', '.menu-area', '.main-header'];
        chromeSelectors.forEach(function (selector) {
            document.querySelectorAll(selector).forEach(function (el) {
                var rect = el.getBoundingClientRect();
                if (rect.height > 0 && rect.top >= 0 && rect.bottom <= window.innerHeight) {
                    top = Math.max(top, Math.ceil(rect.bottom));
                }
            });
        });

        page.style.top = top + 'px';
        page.style.height = Math.max(320, (window.innerHeight || document.documentElement.clientHeight) - top) + 'px';
        page.style.maxHeight = '100dvh';
    }

    function appendMessage(role, text, imageDataUrl) {
        var div = document.createElement('div');
        div.className = 'gemini-assistant-msg ' + role;
        if (isPageMode) {
            div.classList.add('gemini-chatgpt-msg');
        }

        if (imageDataUrl && !isPageMode) {
            var img = document.createElement('img');
            img.src = imageDataUrl;
            img.alt = 'live frame';
            div.appendChild(img);
        }

        if (role === 'model' && cfg.assistantName) {
            var author = document.createElement('span');
            author.className = 'gemini-assistant-msg-author';
            author.textContent = cfg.assistantName;
            div.appendChild(author);
        }

        var span = document.createElement('span');
        span.textContent = text;
        div.appendChild(span);

        messagesEl.appendChild(div);
        var thinkingText = cfg.labels.thinking || 'Analyzing live view...';
        if (role === 'model' && text && text !== thinkingText) {
            setLastModelReply(text);
        }
        scrollChatToBottom();
        return div;
    }

    function setSending(state) {
        isSending = state;
        if (sendBtn) {
            sendBtn.disabled = state;
        }
        if (inputEl) {
            inputEl.readOnly = state;
            inputEl.classList.toggle('is-sending', state);
        }
        if (analyzeBtn) {
            analyzeBtn.disabled = state;
        }
        updateSpeakUi();
        updateMicUi();
    }

    function waitForVideoReady(video, timeoutMs) {
        return new Promise(function (resolve, reject) {
            if (video.videoWidth > 0 && video.videoHeight > 0) {
                resolve();
                return;
            }

            var done = false;
            var timer = setTimeout(function () {
                if (!done) {
                    done = true;
                    reject(new Error('timeout'));
                }
            }, timeoutMs || 10000);

            function finish() {
                if (done) {
                    return;
                }
                if (video.videoWidth > 0 && video.videoHeight > 0) {
                    done = true;
                    clearTimeout(timer);
                    resolve();
                }
            }

            video.addEventListener('loadedmetadata', finish);
            video.addEventListener('loadeddata', finish);
            video.addEventListener('playing', finish);
        });
    }

    function updateLiveUi() {
        var streaming = isStreaming();

        if (isPageMode) {
            previewWrap.classList.toggle('is-active', streaming);
            if (screenEmpty) {
                screenEmpty.classList.toggle('is-hidden', streaming);
            }
            if (screenPanel) {
                screenPanel.classList.toggle('is-streaming', streaming);
            }
            if (stopBtn) {
                stopBtn.style.display = streaming ? 'inline-flex' : 'none';
            }
            var chatCol = document.querySelector('.gemini-chatgpt-chat');
            if (chatCol) {
                chatCol.classList.toggle('is-streaming', streaming);
            }
        } else {
            previewWrap.classList.toggle('is-active', streaming);
            previewWrap.classList.toggle('is-screen', streaming && streamSource === 'screen');
        }

        liveBadge.classList.toggle('is-visible', streaming);

        if (liveBadgeText) {
            if (streamSource === 'screen') {
                liveBadgeText.textContent = cfg.labels.liveScreen || 'SCREEN LIVE';
            } else if (streamSource === 'camera') {
                liveBadgeText.textContent = cfg.labels.liveCamera || 'CAMERA LIVE';
            } else {
                liveBadgeText.textContent = cfg.labels.live || 'LIVE';
            }
        }

        if (analyzeBtn && !isPageMode) {
            analyzeBtn.style.display = streaming && streamSource === 'screen' ? 'inline-flex' : 'none';
        }

        if (analyzeBtn && isPageMode) {
            analyzeBtn.disabled = !(streaming && streamSource === 'screen');
        }

        if (streaming) {
            if (statusEl) {
                statusEl.textContent = streamSource === 'screen'
                    ? (cfg.labels.screenActive || 'Screen sharing live')
                    : (cfg.labels.liveActive || 'Live sharing active');
            }
            if (attachedWrap && !isPageMode) {
                attachedWrap.classList.add('is-visible');
            } else if (attachedWrap) {
                attachedWrap.classList.remove('is-visible');
            }
            if (attachedLabel && !isPageMode) {
                attachedLabel.textContent = streamSource === 'screen'
                    ? (cfg.labels.screenFrame || 'Current screen frame will be sent with your message')
                    : (cfg.labels.liveFrame || 'Live frame will be sent with your message');
            }
        } else {
            if (attachedWrap) {
                attachedWrap.classList.remove('is-visible');
            }
            pendingImage = null;
            if (attachedImg) {
                attachedImg.src = '';
            }
            if (statusEl) {
                statusEl.textContent = cfg.labels.ready || cfg.labels.liveOff || 'Ready to help';
            }
        }

        ensureComposerVisible();
    }

    function stopLivePreviewTimer() {
        if (livePreviewTimer) {
            clearInterval(livePreviewTimer);
            livePreviewTimer = null;
        }
    }

    function startLivePreviewTimer() {
        stopLivePreviewTimer();

        livePreviewTimer = setInterval(function () {
            if (!isStreaming()) {
                stopLivePreviewTimer();
                return;
            }

            var frame = grabCurrentFrame(false);
            if (frame) {
                if (attachedImg && !isPageMode) {
                    attachedImg.src = frame.dataUrl;
                }
                pendingImage = {
                    mimeType: frame.mimeType,
                    data: frame.data,
                    dataUrl: frame.dataUrl,
                    isLive: true,
                };
                if (window.GeminiLiveVoice && typeof window.GeminiLiveVoice.pushVideoFrame === 'function') {
                    window.GeminiLiveVoice.pushVideoFrame(frame);
                }
            }
        }, 1000);
    }

    function stopStream() {
        stopLivePreviewTimer();

        if (mediaStream) {
            mediaStream.getTracks().forEach(function (track) {
                track.stop();
            });
            mediaStream = null;
        }

        streamSource = null;
        previewVideo.srcObject = null;
        cameraBtn.classList.remove('is-active');
        screenBtn.classList.remove('is-active');
        updateLiveUi();
    }

    function setAttachedImage(dataUrl, mimeType, base64Data) {
        pendingImage = {
            mimeType: mimeType,
            data: base64Data,
            dataUrl: dataUrl,
            isLive: true,
        };
        if (attachedImg && !isPageMode) {
            attachedImg.src = dataUrl;
        }
        updateLiveUi();
        if (isPageMode) {
            fitChatPageLayout();
            focusInput();
        }
    }

    function grabCurrentFrame(updatePreview) {
        if (!canvasCtx || !previewVideo.videoWidth || !previewVideo.videoHeight) {
            return null;
        }

        var maxWidth = streamSource === 'screen' ? 1600 : 1280;
        var scale = Math.min(1, maxWidth / previewVideo.videoWidth);
        var width = Math.round(previewVideo.videoWidth * scale);
        var height = Math.round(previewVideo.videoHeight * scale);

        captureCanvas.width = width;
        captureCanvas.height = height;

        try {
            canvasCtx.drawImage(previewVideo, 0, 0, width, height);
        } catch (e) {
            return null;
        }

        var dataUrl = captureCanvas.toDataURL('image/jpeg', 0.78);
        var base64 = dataUrl.split(',')[1] || '';

        if (!base64) {
            return null;
        }

        if (updatePreview !== false && attachedImg && !isPageMode) {
            attachedImg.src = dataUrl;
        }

        return {
            mimeType: 'image/jpeg',
            data: base64,
            dataUrl: dataUrl,
        };
    }

    async function grabLiveFrameWithRetry() {
        for (var attempt = 0; attempt < 5; attempt++) {
            var frame = grabCurrentFrame(false);
            if (frame) {
                return frame;
            }
            await new Promise(function (r) { setTimeout(r, 300); });
        }
        return null;
    }

    async function bindStream(stream, source, activeBtn) {
        stopStream();

        mediaStream = stream;
        streamSource = source;
        previewVideo.srcObject = mediaStream;
        activeBtn.classList.add('is-active');

        try {
            await previewVideo.play();
            await waitForVideoReady(previewVideo, 10000);
            await new Promise(function (r) { setTimeout(r, 200); });

            var frame = await grabLiveFrameWithRetry();
            if (frame) {
                setAttachedImage(frame.dataUrl, frame.mimeType, frame.data);
            }

            startLivePreviewTimer();
            updateLiveUi();
            if (isPageMode) {
                fitChatPageLayout();
            }
            requestAnimationFrame(function () {
                ensureComposerVisible();
                focusInput();
            });

            if (source === 'screen') {
                appendMessage('model', cfg.labels.screenStarted || 'Screen sharing is live. Ask your question or click Analyze screen.');
            } else {
                appendMessage('model', cfg.labels.cameraStarted || 'Camera is live. Ask your question.');
            }

            if (window.GeminiLiveVoice && window.GeminiLiveVoice.isActive()) {
                var liveFrame = grabCurrentFrame(false);
                if (liveFrame && typeof window.GeminiLiveVoice.pushVideoFrame === 'function') {
                    window.GeminiLiveVoice.pushVideoFrame(liveFrame);
                }
            }
        } catch (err) {
            stopStream();
            appendMessage('model', cfg.errors.screenCapture || 'Could not capture the stream. Try again.');
        }
    }

    async function startCamera() {
        try {
            var stream = await navigator.mediaDevices.getUserMedia({
                video: { facingMode: 'user' },
                audio: false,
            });
            await bindStream(stream, 'camera', cameraBtn);
        } catch (err) {
            appendMessage('model', cfg.errors.media || 'Camera access denied.');
        }
    }

    async function startScreen() {
        if (!navigator.mediaDevices.getDisplayMedia) {
            appendMessage('model', cfg.errors.screenUnsupported || 'Screen sharing is not supported in this browser.');
            return;
        }

        try {
            var constraints = {
                video: {
                    width: { ideal: 1920, max: 1920 },
                    height: { ideal: 1080, max: 1080 },
                    frameRate: { ideal: 15, max: 30 },
                },
                audio: false,
            };

            var stream;
            try {
                stream = await navigator.mediaDevices.getDisplayMedia(constraints);
            } catch (innerErr) {
                stream = await navigator.mediaDevices.getDisplayMedia({ video: true, audio: false });
            }

            stream.getVideoTracks()[0].addEventListener('ended', function () {
                stopStream();
                appendMessage('model', cfg.labels.screenStopped || 'Screen sharing stopped.');
            });

            await bindStream(stream, 'screen', screenBtn);
        } catch (err) {
            if (err && err.name !== 'NotAllowedError') {
                appendMessage('model', cfg.errors.media || 'Screen sharing cancelled.');
            }
        }
    }

    async function sendMessage(forcedText) {
        var text = (typeof forcedText === 'string' ? forcedText : (inputEl.value || '')).trim();
        if (!text || isSending) {
            return;
        }

        var imageForSend = isStreaming()
            ? await grabLiveFrameWithRetry()
            : null;

        if (isStreaming() && !imageForSend) {
            appendMessage('model', cfg.errors.screenCapture || 'Could not capture the stream. Try sharing again.');
            return;
        }

        var previewUrl = imageForSend ? imageForSend.dataUrl : null;
        var isLiveSend = !!isStreaming();

        appendMessage('user', text, previewUrl);
        if (inputEl) {
            inputEl.value = '';
        }

        var loadingEl = appendMessage('model', cfg.labels.thinking || 'Analyzing live view...');
        loadingEl.classList.add('loading');
        setSending(true);

        var payload = {
            message: text,
            history: history.slice(-16),
            live: isLiveSend,
            source: streamSource || null,
        };

        if (imageForSend) {
            payload.image = {
                mimeType: imageForSend.mimeType,
                data: imageForSend.data,
            };
        }

        try {
            var response = await fetch(cfg.chatUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken(),
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify(payload),
            });

            var data = await response.json();
            loadingEl.remove();

            if (!response.ok) {
                appendMessage('model', data.error || cfg.errors.generic || 'An error occurred.');
                return;
            }

            appendMessage('model', data.reply);
            history.push({ role: 'user', text: text });
            history.push({ role: 'model', text: data.reply });
            if (voiceModeEnabled) {
                speakText(data.reply);
            }
        } catch (err) {
            loadingEl.remove();
            appendMessage('model', cfg.errors.network || 'Network error. Please try again.');
            if (voiceModeEnabled) {
                queueMicRestart(400);
            }
        } finally {
            setSending(false);
            scrollChatToBottom();
            focusInput();
        }
    }

    function analyzeScreenNow() {
        if (!isStreaming() || streamSource !== 'screen') {
            appendMessage('model', cfg.labels.screenHint || 'Click Share screen to start live screen sharing first.');
            return;
        }

        sendMessage(cfg.labels.analyzePrompt || 'Analyze my current screen and tell me what you see. Help me with anything visible.');
    }

    function readPanelOpenPreference() {
        try {
            var stored = sessionStorage.getItem(PANEL_OPEN_KEY);
            if (stored === '0') {
                return false;
            }
            if (stored === '1') {
                return true;
            }
        } catch (e) {
            // ignore
        }
        return cfg.defaultOpen !== false;
    }

    function savePanelOpenPreference(open) {
        try {
            sessionStorage.setItem(PANEL_OPEN_KEY, open ? '1' : '0');
        } catch (e) {
            // ignore
        }
    }

    function setPanelOpen(open) {
        if (!panel || isPageMode) {
            return;
        }

        panel.classList.toggle('is-open', open);
        savePanelOpenPreference(open);

        if (fab) {
            fab.classList.toggle('is-panel-open', open);
            fab.setAttribute('aria-expanded', open ? 'true' : 'false');
        }

        if (open) {
            ensureComposerVisible();
            focusInput();
            return;
        }

        stopStream();
        if (window.GeminiLiveVoice) {
            window.GeminiLiveVoice.stop(true);
        }
    }

    if (fab) {
        fab.addEventListener('click', function () {
            if (!panel.classList.contains('is-open')) {
                setPanelOpen(true);
                return;
            }
            focusInput();
        });
    }

    if (closeBtn) {
        closeBtn.addEventListener('click', function () {
            setPanelOpen(false);
        });
    }

    if (!isPageMode && panel) {
        setPanelOpen(readPanelOpenPreference());
    }

    if (isPageMode && panel) {
        panel.classList.add('is-open');
        fitChatPageLayout();
        window.addEventListener('resize', fitChatPageLayout);
        setTimeout(fitChatPageLayout, 150);
        setTimeout(fitChatPageLayout, 600);
        focusInput();
    }

    if (sendBtn) {
        sendBtn.addEventListener('click', function () { sendMessage(); });
    }

    if (inputEl) {
        inputEl.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                sendMessage();
            }
        });
    }

    if (cameraBtn) {
        cameraBtn.addEventListener('click', startCamera);
    }
    if (screenBtn) {
        screenBtn.addEventListener('click', startScreen);
    }
    if (stopBtn) {
        stopBtn.addEventListener('click', stopStream);
    }
    if (analyzeBtn) {
        analyzeBtn.addEventListener('click', analyzeScreenNow);
    }
    if (speakBtn) {
        speakBtn.addEventListener('click', speakLatestReply);
    }
    if (attachedRemove) {
        attachedRemove.style.display = 'none';
    }

    if (screenEmpty) {
        screenEmpty.addEventListener('click', function (e) {
            var action = e.target.closest('[data-action]');
            if (!action) {
                return;
            }
            if (action.getAttribute('data-action') === 'share-screen') {
                startScreen();
            }
            if (action.getAttribute('data-action') === 'share-camera') {
                startCamera();
            }
        });
    }

    appendMessage('model', cfg.labels.welcome || 'Click Share screen or Camera — sharing is always live. Then ask your question.');
    updateSpeakUi();
    updateMicUi();
    updateLiveUi();

    window.GeminiAssistantMedia = {
        isStreaming: isStreaming,
        getSource: function () {
            return streamSource;
        },
        grabFrame: function () {
            return grabCurrentFrame(false);
        },
        startCamera: startCamera,
        startScreen: startScreen,
        stopStream: stopStream,
    };
})();
