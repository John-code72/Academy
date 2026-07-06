(function () {
    'use strict';

    var cfg = window.GeminiAssistantConfig || {};
    var overlay = document.getElementById('gemini-live-voice-overlay');
    var orb = document.getElementById('gemini-live-voice-orb');
    var statusEl = document.getElementById('gemini-live-voice-status');
    var transcriptEl = document.getElementById('gemini-live-voice-transcript');
    var stopBtn = document.getElementById('gemini-live-voice-stop');
    var voiceLiveBtn = document.getElementById('gemini-assistant-voice-live');
    var voiceBadge = document.getElementById('gemini-live-voice-badge');
    var voicePreviewWrap = document.getElementById('gemini-live-voice-preview-wrap');
    var voicePreviewVideo = document.getElementById('gemini-live-voice-preview-video');
    var voiceCaptureCanvas = document.getElementById('gemini-live-voice-capture-canvas');
    var voicePreviewBadge = document.getElementById('gemini-live-voice-preview-badge');
    var voiceScreenBtn = document.getElementById('gemini-live-voice-screen');
    var voiceCameraBtn = document.getElementById('gemini-live-voice-camera');
    var voiceStopShareBtn = document.getElementById('gemini-live-voice-stop-share');

    var ws = null;
    var isActive = false;
    var isConnecting = false;
    var videoFrameTimer = null;
    var voiceOwnedStream = false;
    var voiceCanvasCtx = voiceCaptureCanvas ? voiceCaptureCanvas.getContext('2d') : null;
    var micStream = null;
    var inputAudioContext = null;
    var outputAudioContext = null;
    var processorNode = null;
    var micSourceNode = null;
    var playbackTime = 0;
    var activeSources = [];
    var liveModel = '';

    var WS_BASE = 'wss://generativelanguage.googleapis.com/ws/google.ai.generativelanguage.v1alpha.GenerativeService.BidiGenerateContentConstrained';

    function csrfToken() {
        var meta = document.querySelector('meta[name="csrf-token"], meta[name="csrf_token"]');
        return meta ? meta.getAttribute('content') : '';
    }

    function setStatus(text) {
        if (statusEl) {
            statusEl.textContent = text;
        }
    }

    function setTranscript(text) {
        if (transcriptEl) {
            transcriptEl.textContent = sanitizeCoachTranscript(text || '');
        }
    }

    function sanitizeCoachTranscript(text) {
        var raw = String(text || '');
        var lower = raw.toLowerCase();
        var forbidden = [
            'comment puis-je vous aider',
            'comment puis je vous aider',
            'how can i help',
            'how may i help',
            'de quoi avez-vous besoin',
        ];

        for (var i = 0; i < forbidden.length; i++) {
            if (lower.indexOf(forbidden[i]) !== -1) {
                return String(cfg.openingMessage || cfg.launchBriefing || raw);
            }
        }

        return raw;
    }

    function sendCoachVoiceOpener() {
        if (!ws || ws.readyState !== WebSocket.OPEN) {
            return;
        }

        var opener = String(cfg.openingMessage || cfg.launchBriefing || '').trim();
        if (!opener) {
            return;
        }

        setTranscript(opener);

        try {
            ws.send(JSON.stringify({
                clientContent: {
                    turns: [{
                        role: 'user',
                        parts: [{
                            text: 'You are the coach. Announce the path modules then teach the current step. '
                                + 'FORBIDDEN to ask how you can help. Follow this message: ' + opener,
                        }],
                    }],
                    turnComplete: true,
                },
            }));
        } catch (e) {
            // ignore
        }
    }

    function setOverlayVisible(visible) {
        if (!overlay) {
            return;
        }
        overlay.classList.toggle('is-open', visible);
        overlay.setAttribute('aria-hidden', visible ? 'false' : 'true');
        document.body.classList.toggle('gemini-live-voice-open', visible);
    }

    function setOrbState(state) {
        if (!orb) {
            return;
        }
        orb.classList.remove('is-connecting', 'is-listening', 'is-speaking', 'is-idle');
        if (state) {
            orb.classList.add(state);
        }

        if (voiceBadge) {
            voiceBadge.classList.remove('is-connecting', 'is-listening', 'is-speaking');
            if (state === 'is-connecting') {
                voiceBadge.textContent = cfg.labels.liveVoiceConnectingShort || 'Connecting';
                voiceBadge.classList.add('is-connecting');
            } else if (state === 'is-listening') {
                voiceBadge.textContent = cfg.labels.liveVoiceListeningShort || 'Listening';
                voiceBadge.classList.add('is-listening');
            } else if (state === 'is-speaking') {
                voiceBadge.textContent = cfg.labels.liveVoiceSpeakingShort || 'Speaking';
                voiceBadge.classList.add('is-speaking');
            } else {
                voiceBadge.textContent = cfg.labels.liveVoiceLabel || 'Voice';
            }
        }
    }

    function downsampleBuffer(buffer, inputSampleRate, outputSampleRate) {
        if (inputSampleRate === outputSampleRate) {
            return buffer;
        }
        var ratio = inputSampleRate / outputSampleRate;
        var newLength = Math.round(buffer.length / ratio);
        var result = new Float32Array(newLength);
        var offsetResult = 0;
        var offsetBuffer = 0;
        while (offsetResult < newLength) {
            var nextOffsetBuffer = Math.round((offsetResult + 1) * ratio);
            var accum = 0;
            var count = 0;
            for (var i = offsetBuffer; i < nextOffsetBuffer && i < buffer.length; i++) {
                accum += buffer[i];
                count++;
            }
            result[offsetResult] = count ? accum / count : 0;
            offsetResult++;
            offsetBuffer = nextOffsetBuffer;
        }
        return result;
    }

    function floatTo16BitPCM(float32Array) {
        var buffer = new ArrayBuffer(float32Array.length * 2);
        var view = new DataView(buffer);
        for (var i = 0; i < float32Array.length; i++) {
            var sample = Math.max(-1, Math.min(1, float32Array[i]));
            view.setInt16(i * 2, sample < 0 ? sample * 0x8000 : sample * 0x7fff, true);
        }
        return buffer;
    }

    function arrayBufferToBase64(buffer) {
        var bytes = new Uint8Array(buffer);
        var binary = '';
        var chunkSize = 0x8000;
        for (var i = 0; i < bytes.length; i += chunkSize) {
            binary += String.fromCharCode.apply(null, bytes.subarray(i, i + chunkSize));
        }
        return btoa(binary);
    }

    function stopPlayback() {
        activeSources.forEach(function (source) {
            try {
                source.stop();
            } catch (e) {
                // ignore
            }
        });
        activeSources = [];
        if (outputAudioContext) {
            playbackTime = outputAudioContext.currentTime;
        }
    }

    function playPcmChunk(base64Data, sampleRate) {
        if (!outputAudioContext || !base64Data) {
            return;
        }

        var binary = atob(base64Data);
        var bytes = new Uint8Array(binary.length);
        for (var i = 0; i < binary.length; i++) {
            bytes[i] = binary.charCodeAt(i);
        }

        var int16 = new Int16Array(bytes.buffer);
        var float32 = new Float32Array(int16.length);
        for (var j = 0; j < int16.length; j++) {
            float32[j] = int16[j] / 32768;
        }

        var audioBuffer = outputAudioContext.createBuffer(1, float32.length, sampleRate);
        audioBuffer.copyToChannel(float32, 0);

        var source = outputAudioContext.createBufferSource();
        source.buffer = audioBuffer;
        source.connect(outputAudioContext.destination);

        var startAt = Math.max(outputAudioContext.currentTime, playbackTime);
        source.start(startAt);
        playbackTime = startAt + audioBuffer.duration;
        activeSources.push(source);

        source.onended = function () {
            activeSources = activeSources.filter(function (item) {
                return item !== source;
            });
            if (!activeSources.length && isActive) {
                setOrbState('is-listening');
                setStatus(cfg.labels.liveVoiceListening || 'Speak now...');
            }
        };

        setOrbState('is-speaking');
        setStatus(cfg.labels.liveVoiceSpeaking || 'Assistant is speaking...');
    }

    function parseSampleRate(mimeType) {
        var match = /rate=(\d+)/i.exec(mimeType || '');
        return match ? parseInt(match[1], 10) : 24000;
    }

    function readMessagePayload(data) {
        if (typeof data === 'string') {
            return Promise.resolve(data);
        }
        if (data instanceof Blob) {
            return data.text();
        }
        if (data instanceof ArrayBuffer) {
            return Promise.resolve(new TextDecoder().decode(data));
        }
        return Promise.resolve(String(data));
    }

    function handleLiveMessage(raw) {
        var message;
        try {
            message = JSON.parse(raw);
        } catch (e) {
            return;
        }

        if (message.setupComplete) {
            setOrbState('is-listening');
            setStatus(cfg.labels.liveVoiceListening || 'Speak now...');
            return;
        }

        var serverContent = message.serverContent;
        if (!serverContent) {
            return;
        }

        if (serverContent.interrupted) {
            stopPlayback();
            setOrbState('is-listening');
            setStatus(cfg.labels.liveVoiceListening || 'Speak now...');
        }

        if (serverContent.inputTranscription && serverContent.inputTranscription.text) {
            setTranscript(serverContent.inputTranscription.text);
        }

        if (serverContent.outputTranscription && serverContent.outputTranscription.text) {
            setTranscript(serverContent.outputTranscription.text);
        }

        var parts = serverContent.modelTurn ? serverContent.modelTurn.parts : null;
        if (!parts || !parts.length) {
            return;
        }

        parts.forEach(function (part) {
            if (part.inlineData && part.inlineData.data) {
                playPcmChunk(part.inlineData.data, parseSampleRate(part.inlineData.mimeType));
            }
            if (part.text) {
                setTranscript(part.text);
            }
        });
    }

    function sendAudioChunk(base64) {
        if (!ws || ws.readyState !== WebSocket.OPEN) {
            return;
        }

        var payload = {
            realtimeInput: {
                audio: {
                    mimeType: 'audio/pcm;rate=16000',
                    data: base64,
                },
            },
        };

        try {
            ws.send(JSON.stringify(payload));
        } catch (e) {
            // ignore
        }
    }

    function sendVideoFrame(frame) {
        if (!ws || ws.readyState !== WebSocket.OPEN || !frame || !frame.data) {
            return;
        }

        var payload = {
            realtimeInput: {
                video: {
                    mimeType: frame.mimeType || 'image/jpeg',
                    data: frame.data,
                },
            },
        };

        try {
            ws.send(JSON.stringify(payload));
        } catch (e) {
            // ignore
        }
    }

    function isVideoStreaming() {
        if (window.GeminiAssistantMedia && window.GeminiAssistantMedia.isStreaming()) {
            return true;
        }
        return !!(voicePreviewVideo && voicePreviewVideo.srcObject && voicePreviewVideo.srcObject.getVideoTracks().some(function (t) {
            return t.readyState === 'live';
        }));
    }

    function getMainPreviewVideo() {
        return document.getElementById('gemini-assistant-preview-video');
    }

    function captureFrameFromVideo(video, source) {
        if (!video || !voiceCanvasCtx || !video.videoWidth || !video.videoHeight) {
            return null;
        }

        var maxSize = 768;
        var scale = Math.min(1, maxSize / video.videoWidth, maxSize / video.videoHeight);
        var width = Math.round(video.videoWidth * scale);
        var height = Math.round(video.videoHeight * scale);

        voiceCaptureCanvas.width = width;
        voiceCaptureCanvas.height = height;

        try {
            voiceCanvasCtx.drawImage(video, 0, 0, width, height);
        } catch (e) {
            return null;
        }

        var dataUrl = voiceCaptureCanvas.toDataURL('image/jpeg', 0.72);
        var base64 = dataUrl.split(',')[1] || '';
        if (!base64) {
            return null;
        }

        return {
            mimeType: 'image/jpeg',
            data: base64,
        };
    }

    function grabVideoFrame() {
        if (window.GeminiAssistantMedia && window.GeminiAssistantMedia.isStreaming()) {
            var shared = window.GeminiAssistantMedia.grabFrame();
            if (shared) {
                return shared;
            }
        }

        var mainVideo = getMainPreviewVideo();
        if (mainVideo && mainVideo.srcObject) {
            return captureFrameFromVideo(mainVideo, null);
        }

        if (voicePreviewVideo && voicePreviewVideo.srcObject) {
            return captureFrameFromVideo(voicePreviewVideo, null);
        }

        return null;
    }

    function syncVoicePreviewUi() {
        var streaming = isVideoStreaming();
        var source = window.GeminiAssistantMedia && window.GeminiAssistantMedia.getSource
            ? window.GeminiAssistantMedia.getSource()
            : null;

        if (voicePreviewWrap) {
            voicePreviewWrap.hidden = !streaming;
        }

        if (streaming) {
            var mainVideo = getMainPreviewVideo();
            if (voicePreviewVideo && mainVideo && mainVideo.srcObject) {
                if (voicePreviewVideo.srcObject !== mainVideo.srcObject) {
                    voicePreviewVideo.srcObject = mainVideo.srcObject;
                    voicePreviewVideo.play().catch(function () {});
                }
            }
            if (voicePreviewBadge) {
                voicePreviewBadge.textContent = source === 'screen'
                    ? (cfg.labels.liveScreen || 'SCREEN LIVE')
                    : (cfg.labels.liveCamera || 'CAMERA LIVE');
            }
            if (voiceScreenBtn) {
                voiceScreenBtn.classList.toggle('is-active', source === 'screen');
            }
            if (voiceCameraBtn) {
                voiceCameraBtn.classList.toggle('is-active', source === 'camera');
            }
            if (voiceStopShareBtn) {
                voiceStopShareBtn.hidden = false;
            }
        } else {
            if (voicePreviewVideo) {
                voicePreviewVideo.srcObject = null;
            }
            if (voiceScreenBtn) {
                voiceScreenBtn.classList.remove('is-active');
            }
            if (voiceCameraBtn) {
                voiceCameraBtn.classList.remove('is-active');
            }
            if (voiceStopShareBtn) {
                voiceStopShareBtn.hidden = true;
            }
        }
    }

    function updateVoiceMediaStatus() {
        syncVoicePreviewUi();
        if (!isActive && !isConnecting) {
            return;
        }
        if (isVideoStreaming()) {
            var source = window.GeminiAssistantMedia && window.GeminiAssistantMedia.getSource
                ? window.GeminiAssistantMedia.getSource()
                : null;
            if (source === 'screen') {
                setStatus(cfg.labels.liveVoiceScreenActive || 'Screen shared — assistant can see your display');
            } else {
                setStatus(cfg.labels.liveVoiceCameraActive || 'Camera on — assistant can see you');
            }
            return;
        }
        if (!activeSources.length && isActive) {
            setOrbState('is-listening');
            setStatus(cfg.labels.liveVoiceListening || 'Speak now...');
        }
    }

    function startVideoFrameLoop() {
        stopVideoFrameLoop();
        videoFrameTimer = setInterval(function () {
            if (!isActive || !ws || ws.readyState !== WebSocket.OPEN) {
                return;
            }
            syncVoicePreviewUi();
            var frame = grabVideoFrame();
            if (frame) {
                sendVideoFrame(frame);
            }
        }, 1000);
    }

    function stopVideoFrameLoop() {
        if (videoFrameTimer) {
            clearInterval(videoFrameTimer);
            videoFrameTimer = null;
        }
    }

    function stopVoiceShare() {
        if (window.GeminiAssistantMedia && window.GeminiAssistantMedia.isStreaming()) {
            window.GeminiAssistantMedia.stopStream();
        }
        if (voicePreviewVideo) {
            voicePreviewVideo.srcObject = null;
        }
        voiceOwnedStream = false;
        syncVoicePreviewUi();
        updateVoiceMediaStatus();
    }

    async function startVoiceScreen() {
        voiceOwnedStream = true;
        if (window.GeminiAssistantMedia && typeof window.GeminiAssistantMedia.startScreen === 'function') {
            await window.GeminiAssistantMedia.startScreen();
            syncVoicePreviewUi();
            updateVoiceMediaStatus();
            return;
        }
        setStatus(cfg.errors.screenUnsupported || 'Screen sharing is not supported.');
    }

    async function startVoiceCamera() {
        voiceOwnedStream = true;
        if (window.GeminiAssistantMedia && typeof window.GeminiAssistantMedia.startCamera === 'function') {
            await window.GeminiAssistantMedia.startCamera();
            syncVoicePreviewUi();
            updateVoiceMediaStatus();
            return;
        }
        setStatus(cfg.errors.media || 'Could not access camera.');
    }

    async function startMicStreaming() {
        micStream = await navigator.mediaDevices.getUserMedia({
            audio: {
                channelCount: 1,
                echoCancellation: true,
                noiseSuppression: true,
                autoGainControl: true,
            },
            video: false,
        });

        inputAudioContext = new (window.AudioContext || window.webkitAudioContext)();
        outputAudioContext = new (window.AudioContext || window.webkitAudioContext)({ sampleRate: 24000 });
        playbackTime = outputAudioContext.currentTime;

        await Promise.all([
            inputAudioContext.resume(),
            outputAudioContext.resume(),
        ]);

        micSourceNode = inputAudioContext.createMediaStreamSource(micStream);
        processorNode = inputAudioContext.createScriptProcessor(4096, 1, 1);

        processorNode.onaudioprocess = function (event) {
            if (!isActive || !ws || ws.readyState !== WebSocket.OPEN) {
                return;
            }
            var input = event.inputBuffer.getChannelData(0);
            var downsampled = downsampleBuffer(input, inputAudioContext.sampleRate, 16000);
            sendAudioChunk(arrayBufferToBase64(floatTo16BitPCM(downsampled)));
        };

        micSourceNode.connect(processorNode);
        processorNode.connect(inputAudioContext.destination);

        setOrbState('is-listening');
        setStatus(cfg.labels.liveVoiceListening || 'Speak now...');
    }

    function stopMicStreaming() {
        if (processorNode) {
            processorNode.disconnect();
            processorNode.onaudioprocess = null;
            processorNode = null;
        }
        if (micSourceNode) {
            micSourceNode.disconnect();
            micSourceNode = null;
        }
        if (inputAudioContext) {
            inputAudioContext.close().catch(function () {});
            inputAudioContext = null;
        }
        if (outputAudioContext) {
            outputAudioContext.close().catch(function () {});
            outputAudioContext = null;
        }
        if (micStream) {
            micStream.getTracks().forEach(function (track) {
                track.stop();
            });
            micStream = null;
        }
        stopPlayback();
    }

    async function fetchLiveToken() {
        var topicEl = document.getElementById('gemini-assistant-input');
        var topic = topicEl ? String(topicEl.value || '').trim() : '';
        var trackEl = document.getElementById('gemini-coach-track');
        var track = trackEl ? String(trackEl.value || '').trim() : '';
        if (!track && cfg.defaultTrack) {
            track = String(cfg.defaultTrack || '').trim();
        }

        var response = await fetch(cfg.liveTokenUrl, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken(),
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({
                topic: topic ? topic.slice(0, 500) : null,
                track: track || null,
            }),
        });

        var data = await response.json().catch(function () {
            return {};
        });
        if (!response.ok) {
            throw new Error(data.error || cfg.errors.liveVoiceFailed || 'Could not start live voice session.');
        }
        if (!data.token || !data.model) {
            throw new Error(cfg.errors.liveVoiceFailed || 'Invalid live voice session response.');
        }

        if (data.openingMessage) {
            cfg.openingMessage = data.openingMessage;
        }

        return data;
    }

    function updateVoiceButtonState() {
        if (!voiceLiveBtn) {
            return;
        }
        voiceLiveBtn.classList.toggle('is-active', isActive || isConnecting);
        voiceLiveBtn.setAttribute('aria-pressed', (isActive || isConnecting) ? 'true' : 'false');
    }

    function parseLiveError(raw) {
        try {
            var message = JSON.parse(raw);
            if (message.error && message.error.message) {
                return message.error.message;
            }
            if (message.error && typeof message.error === 'string') {
                return message.error;
            }
        } catch (e) {
            // ignore
        }
        return null;
    }

    function buildSetupMessage(tokenData) {
        return {
            setup: {
                model: tokenData.model,
                generationConfig: {
                    responseModalities: ['AUDIO'],
                    speechConfig: {
                        voiceConfig: {
                            prebuiltVoiceConfig: {
                                voiceName: tokenData.voice || 'Aoede',
                            },
                        },
                    },
                },
                systemInstruction: {
                    parts: [{
                        text: tokenData.systemInstruction || ('I\'m ' + (cfg.coachName || cfg.assistantName || 'your coach') + ', Defrilex Academy COACH. We will work through the path step by step. FORBIDDEN: asking how I can help.'),
                    }],
                },
                inputAudioTranscription: {},
                outputAudioTranscription: {},
            },
        };
    }

    function connectWebSocket(tokenData) {
        var setupMessage = buildSetupMessage(tokenData);
        liveModel = tokenData.model || '';

        var url = WS_BASE + '?access_token=' + encodeURIComponent(tokenData.token);

        return new Promise(function (resolve, reject) {
            var socket = new WebSocket(url);
            var settled = false;
            var setupDone = false;
            var lastError = null;

            function finish(err) {
                if (settled) {
                    return;
                }
                settled = true;
                if (err) {
                    try {
                        socket.close();
                    } catch (closeErr) {
                        // ignore
                    }
                    reject(err);
                    return;
                }
                ws = socket;
                resolve();
            }

            socket.onopen = function () {
                try {
                    socket.send(JSON.stringify(setupMessage));
                } catch (sendErr) {
                    finish(sendErr);
                }
            };

            socket.onmessage = function (event) {
                readMessagePayload(event.data).then(function (raw) {
                    var serverError = parseLiveError(raw);
                    if (serverError) {
                        lastError = new Error(serverError);
                    }

                    try {
                        var parsed = JSON.parse(raw);
                        if (parsed.setupComplete) {
                            setupDone = true;
                        }
                    } catch (parseErr) {
                        // ignore
                    }

                    handleLiveMessage(raw);

                    if (setupDone && !settled) {
                        finish(null);
                    }
                }).catch(function () {
                    lastError = new Error(cfg.errors.liveVoiceFailed || 'Live voice connection failed.');
                });
            };

            socket.onerror = function () {
                if (!lastError) {
                    lastError = new Error(cfg.errors.liveVoiceFailed || 'Live voice connection failed.');
                }
            };

            socket.onclose = function (event) {
                if (setupDone && settled) {
                    if (isActive) {
                        stop(true);
                    }
                    return;
                }
                if (!settled) {
                    var closeDetail = event && event.reason ? event.reason : '';
                    var message = (lastError && lastError.message)
                        || closeDetail
                        || (cfg.errors.liveVoiceFailed || 'Live voice connection closed.');
                    if (event && event.code && !closeDetail) {
                        message += ' (' + event.code + ')';
                    }
                    finish(new Error(message));
                } else if (isActive) {
                    stop(true);
                }
            };

            setTimeout(function () {
                if (!settled && setupDone) {
                    finish(null);
                } else if (!settled) {
                    finish(lastError || new Error(cfg.errors.liveVoiceFailed || 'Live voice setup timed out.'));
                }
            }, 12000);
        });
    }

    async function start() {
        if (isActive || isConnecting) {
            return;
        }

        isConnecting = true;
        updateVoiceButtonState();
        setOverlayVisible(true);
        setOrbState('is-connecting');
        setStatus(cfg.labels.liveVoiceConnecting || 'Connecting to voice assistant...');
        setTranscript('');

        try {
            var tokenData = await fetchLiveToken();
            await connectWebSocket(tokenData);
            isConnecting = false;
            isActive = true;
            updateVoiceButtonState();
            sendCoachVoiceOpener();
            await startMicStreaming();
            startVideoFrameLoop();
            syncVoicePreviewUi();
            if (!isVideoStreaming()) {
                setTranscript(cfg.labels.liveVoiceShareHint || 'Share screen or camera so the voice assistant can see what you see');
            }
        } catch (err) {
            isConnecting = false;
            isActive = false;
            updateVoiceButtonState();
            setOrbState('is-idle');
            setStatus(err.message || cfg.errors.liveVoiceFailed || 'Could not start live voice session.');
        }
    }

    function stop(silent) {
        isConnecting = false;
        isActive = false;
        stopVideoFrameLoop();
        stopMicStreaming();

        if (voiceOwnedStream) {
            stopVoiceShare();
        }

        if (ws) {
            try {
                ws.close();
            } catch (e) {
                // ignore
            }
            ws = null;
        }

        updateVoiceButtonState();
        setOverlayVisible(false);
        setOrbState('is-idle');
        setTranscript('');

        if (!silent) {
            setStatus(cfg.labels.liveVoiceEnded || 'Conversation ended.');
        }
    }

    function toggle() {
        if (isActive || isConnecting) {
            stop(false);
            return;
        }
        start();
    }

    if (stopBtn) {
        stopBtn.addEventListener('click', function () {
            stop(false);
        });
    }

    if (voiceLiveBtn) {
        voiceLiveBtn.addEventListener('click', function (event) {
            event.preventDefault();
            toggle();
        });
    }

    if (voiceScreenBtn) {
        voiceScreenBtn.addEventListener('click', function (event) {
            event.preventDefault();
            if (!isActive && !isConnecting) {
                return;
            }
            startVoiceScreen();
        });
    }

    if (voiceCameraBtn) {
        voiceCameraBtn.addEventListener('click', function (event) {
            event.preventDefault();
            if (!isActive && !isConnecting) {
                return;
            }
            startVoiceCamera();
        });
    }

    if (voiceStopShareBtn) {
        voiceStopShareBtn.addEventListener('click', function (event) {
            event.preventDefault();
            stopVoiceShare();
        });
    }

    window.GeminiLiveVoice = {
        start: start,
        stop: stop,
        toggle: toggle,
        isActive: function () {
            return isActive || isConnecting;
        },
        pushVideoFrame: function (frame) {
            sendVideoFrame(frame);
            syncVoicePreviewUi();
        },
    };
})();
