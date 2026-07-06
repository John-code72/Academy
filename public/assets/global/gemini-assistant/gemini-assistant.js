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

    function getMessagesContainer() {
        if (!messagesEl) {
            return null;
        }
        var inner = messagesEl.querySelector('.gemini-chat-messages-inner');
        return inner || messagesEl;
    }
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

    var coachTrackEl = document.getElementById('gemini-coach-track');
    var coachProgressWrap = document.getElementById('gemini-coach-progress-wrap');
    var coachStepLabel = document.getElementById('gemini-coach-step-label');
    var coachPercentEl = document.getElementById('gemini-coach-percent');
    var coachProgressFill = document.getElementById('gemini-coach-progress-fill');
    var coachCurrentStep = document.getElementById('gemini-coach-current-step');
    var coachStartBtn = document.getElementById('gemini-coach-start');
    var coachContinueBtn = document.getElementById('gemini-coach-continue');
    var coachNextBtn = document.getElementById('gemini-coach-next');
    var coachRestartBtn = document.getElementById('gemini-coach-restart');
    var coachModuleLabel = document.getElementById('gemini-coach-module-label');
    var coachCurriculumEl = document.getElementById('gemini-coach-curriculum');
    var coachCurriculumList = document.getElementById('gemini-coach-curriculum-list');
    var coachCurriculumDesc = document.getElementById('gemini-coach-curriculum-desc');
    var coachCurriculumToggle = document.getElementById('gemini-coach-curriculum-toggle');
    var coachPanelToggle = document.getElementById('gemini-coach-panel-toggle');
    var coachPanelBody = document.getElementById('gemini-coach-panel-body');
    var coachProgressBar = document.querySelector('.gemini-coach-progress-bar');
    var COACH_TRACK_KEY = 'ai_assistant_coach_track';
    var coachSessionStarted = false;
    var coachPanelInitialized = false;
    var quickRepliesEl = document.getElementById('gemini-assistant-quick-replies');
    var lastStepCard = cfg.stepCard || null;
    var lastCoachQuestion = (cfg.coachQuestion || (cfg.stepCard && cfg.stepCard.question)) ? (cfg.coachQuestion || cfg.stepCard.question) : '';

    function selectedCoachTrack() {
        if (coachTrackEl) {
            var fromSelect = String(coachTrackEl.value || '').trim();
            if (fromSelect) {
                return fromSelect;
            }
        }

        return String(cfg.defaultTrack || '').trim();
    }

    function renderCurriculumOutline(curriculum) {
        if (!coachCurriculumList || !curriculum || !curriculum.modules) {
            return;
        }

        if (coachCurriculumDesc) {
            coachCurriculumDesc.textContent = curriculum.description || '';
        }

        coachCurriculumList.innerHTML = '';

        curriculum.modules.forEach(function (module) {
            var moduleLi = document.createElement('li');
            moduleLi.className = 'gemini-coach-curriculum-module' + (module.is_current ? ' is-current-module' : '');

            var moduleHead = document.createElement('div');
            moduleHead.className = 'gemini-coach-curriculum-module-head';
            moduleHead.innerHTML = '<span class="gemini-coach-curriculum-module-title">' + escapeHtml(module.title || '') + '</span>'
                + '<span class="gemini-coach-curriculum-module-count">' + (module.completed_count || 0) + '/' + (module.total_count || 0) + '</span>';
            moduleLi.appendChild(moduleHead);

            var stepsUl = document.createElement('ul');
            stepsUl.className = 'gemini-coach-curriculum-steps';

            (module.steps || []).forEach(function (step) {
                var stepLi = document.createElement('li');
                stepLi.className = 'gemini-coach-curriculum-step is-' + (step.status || 'pending');
                var statusLabel = step.status === 'completed'
                    ? (cfg.labels.completed || 'Done')
                    : (step.status === 'current' ? (cfg.labels.current || 'Current') : (cfg.labels.pending || 'Up next'));
                stepLi.innerHTML = '<span class="gemini-coach-curriculum-step-icon" aria-hidden="true"></span>'
                    + '<span class="gemini-coach-curriculum-step-pos">' + (step.position || '') + '</span>'
                    + '<span class="gemini-coach-curriculum-step-title">' + escapeHtml(step.title || '') + '</span>'
                    + '<span class="gemini-coach-curriculum-step-status">' + statusLabel + '</span>';
                stepsUl.appendChild(stepLi);
            });

            moduleLi.appendChild(stepsUl);
            coachCurriculumList.appendChild(moduleLi);
        });
    }

    function buildStepCardEl(stepCard) {
        if (!stepCard) {
            return null;
        }

        var card = document.createElement('div');
        card.className = 'gemini-coach-step-card';

        var head = document.createElement('div');
        head.className = 'gemini-coach-step-card-head';
        head.innerHTML = '<span class="gemini-coach-step-card-badge">'
            + escapeHtml((cfg.labels.step || 'Step') + ' ' + (stepCard.position || 0) + '/' + (stepCard.total || 0))
            + '</span>'
            + '<strong class="gemini-coach-step-card-title">' + escapeHtml(stepCard.title || '') + '</strong>';
        card.appendChild(head);

        if (stepCard.module_title) {
            var module = document.createElement('div');
            module.className = 'gemini-coach-step-card-module';
            module.textContent = (cfg.labels.module || 'Module') + ': ' + stepCard.module_title;
            card.appendChild(module);
        }

        if (stepCard.question || stepCard.practice) {
            var practice = document.createElement('div');
            practice.className = 'gemini-coach-step-card-practice';
            practice.innerHTML = '<span class="gemini-coach-step-card-practice-label">' + escapeHtml(cfg.labels.coachQuestion || 'Coach asks') + '</span>'
                + '<p class="gemini-coach-step-card-question">' + escapeHtml(stepCard.question || stepCard.practice) + '</p>';
            card.appendChild(practice);
        }

        return card;
    }

    function appendCoachMessage(text, stepCard) {
        var div = document.createElement('div');
        div.className = 'gemini-assistant-msg model';
        if (isPageMode) {
            div.classList.add('gemini-chatgpt-msg');
        }

        if (cfg.assistantName) {
            var author = document.createElement('span');
            author.className = 'gemini-assistant-msg-author';
            author.textContent = cfg.assistantName;
            div.appendChild(author);
        }

        var card = buildStepCardEl(stepCard || lastStepCard);
        if (card) {
            div.appendChild(card);
            lastStepCard = stepCard || lastStepCard;
        }

        var body = document.createElement('div');
        body.className = 'gemini-assistant-msg-body';
        body.textContent = text || '';
        div.appendChild(body);

        getMessagesContainer().appendChild(div);
        if (text) {
            setLastModelReply(text);
        }
        scrollChatToBottom();
        return div;
    }

    function enhanceOpeningMessageEl() {
        var openingEl = document.getElementById('gemini-coach-opening-msg');
        if (!openingEl) {
            return;
        }

        var text = String(openingEl.textContent || '').trim();
        openingEl.textContent = '';
        openingEl.classList.add('gemini-coach-opening');
        if (isPageMode) {
            openingEl.classList.add('gemini-chatgpt-msg');
        }

        if (cfg.assistantName) {
            var author = document.createElement('span');
            author.className = 'gemini-assistant-msg-author';
            author.textContent = cfg.assistantName;
            openingEl.appendChild(author);
        }

        var card = buildStepCardEl(cfg.stepCard);
        if (card) {
            openingEl.appendChild(card);
            lastStepCard = cfg.stepCard;
        }

        var body = document.createElement('div');
        body.className = 'gemini-assistant-msg-body';
        body.textContent = text;
        openingEl.appendChild(body);
    }

    function escapeHtml(text) {
        var div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function appendTypingIndicator() {
        var div = document.createElement('div');
        div.className = 'gemini-assistant-msg model gemini-typing loading';
        if (isPageMode) {
            div.classList.add('gemini-chatgpt-msg');
        }

        if (cfg.assistantName) {
            var author = document.createElement('span');
            author.className = 'gemini-assistant-msg-author';
            author.textContent = cfg.assistantName;
            div.appendChild(author);
        }

        var dots = document.createElement('div');
        dots.className = 'gemini-typing-dots';
        dots.setAttribute('aria-label', cfg.labels.thinking || 'Thinking…');
        dots.innerHTML = '<span></span><span></span><span></span>';
        div.appendChild(dots);
        getMessagesContainer().appendChild(div);
        scrollChatToBottom();
        return div;
    }

    function renderQuickReplies(replies) {
        if (!quickRepliesEl) {
            return;
        }

        quickRepliesEl.innerHTML = '';

        if (!replies || !replies.length) {
            quickRepliesEl.hidden = true;
            return;
        }

        quickRepliesEl.hidden = false;

        replies.forEach(function (item) {
            var btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'gemini-quick-reply-btn';
            btn.textContent = item.label || '';
            btn.addEventListener('click', function () {
                if (isSending) {
                    return;
                }

                if (item.action === 'focus_input') {
                    applyCoachQuestionPlaceholder();
                    focusInput();
                    return;
                }

                if (item.action === 'answer_starter') {
                    applyCoachQuestionPlaceholder();
                    focusInput();
                    if (inputEl) {
                        inputEl.value = 'My answer: ';
                        var len = inputEl.value.length;
                        inputEl.setSelectionRange(len, len);
                    }
                    return;
                }

                if (item.action === 'restart') {
                    startCoachTrack(true);
                    return;
                }

                if (item.message) {
                    sendMessage(item.message);
                } else {
                    focusInput();
                }
            });
            quickRepliesEl.appendChild(btn);
        });
    }

    function showAdvanceFeedback(progress) {
        if (coachProgressFill) {
            coachProgressFill.classList.add('is-advancing');
            setTimeout(function () {
                coachProgressFill.classList.remove('is-advancing');
            }, 900);
        }

        if (coachProgressWrap) {
            coachProgressWrap.classList.add('is-advancing');
            setTimeout(function () {
                coachProgressWrap.classList.remove('is-advancing');
            }, 900);
        }

        if (progress && progress.status === 'completed') {
            renderQuickReplies([
                { label: cfg.labels.restartPath || 'Restart path', message: '', action: 'restart' },
            ]);
            return;
        }

        if (statusEl && progress) {
            statusEl.textContent = (cfg.labels.stepComplete || 'Step complete!') + ' — ' + (progress.step_title || '');
            setTimeout(function () {
                if (statusEl && progress.status === 'active') {
                    statusEl.textContent = (progress.track_name || '') + ' — ' + (progress.step_title || '');
                }
            }, 2500);
        }
    }

    function applyCoachQuestionPlaceholder() {
        if (!inputEl) {
            return;
        }
        var q = lastCoachQuestion || cfg.labels.typeYourAnswer || 'Type your answer below…';
        inputEl.placeholder = q.length > 80 ? (q.substring(0, 77) + '…') : q;
    }

    function handleCoachResponse(data) {
        if (data.step_card) {
            lastStepCard = data.step_card;
            lastCoachQuestion = data.step_card.question || data.coach_question || lastCoachQuestion;
        } else if (data.coach_question) {
            lastCoachQuestion = data.coach_question;
        }

        appendCoachMessage(data.reply || '', data.step_card || null);

        if (data.reply) {
            history.push({ role: 'model', text: data.reply });
        }

        if (data.progress) {
            updateCoachUi(data.progress, data.progress.curriculum || null);
        }

        renderQuickReplies(data.quick_replies || []);
        applyCoachQuestionPlaceholder();

        if (data.advanced) {
            showAdvanceFeedback(data.progress);
        }

        if (voiceModeEnabled && data.reply) {
            speakText(data.reply);
        }
    }

    function updateCoachUi(progress, curriculum) {
        var track = selectedCoachTrack();
        if (!coachProgressWrap || !track) {
            if (coachProgressWrap) {
                coachProgressWrap.hidden = true;
            }
            return;
        }

        coachProgressWrap.hidden = false;

        var outline = curriculum || (progress && progress.curriculum) || cfg.curriculum || null;
        if (outline) {
            renderCurriculumOutline(outline);
        }

        if (!progress) {
            if (coachStepLabel) {
                coachStepLabel.textContent = cfg.labels.coachSelectTrack || 'Select a path';
            }
            if (coachPercentEl) {
                coachPercentEl.textContent = '0%';
            }
            if (coachProgressFill) {
                coachProgressFill.style.width = '0%';
            }
            if (coachProgressBar) {
                coachProgressBar.setAttribute('aria-valuenow', '0');
            }
            if (coachCurrentStep) {
                coachCurrentStep.textContent = '';
            }
            if (coachModuleLabel) {
                coachModuleLabel.textContent = '';
            }
            if (coachStartBtn) {
                coachStartBtn.hidden = false;
            }
            if (coachContinueBtn) {
                coachContinueBtn.hidden = true;
            }
            if (coachNextBtn) {
                coachNextBtn.hidden = true;
            }
            if (coachRestartBtn) {
                coachRestartBtn.hidden = true;
            }
            return;
        }

        if (coachStepLabel) {
            coachStepLabel.textContent = (cfg.labels.step || 'Step') + ' ' + (progress.step_position || 0) + '/' + (progress.total_steps || 0);
        }
        if (coachPercentEl) {
            coachPercentEl.textContent = (progress.percent || 0) + '%';
        }
        if (coachProgressFill) {
            coachProgressFill.style.width = (progress.percent || 0) + '%';
        }
        if (coachProgressBar) {
            coachProgressBar.setAttribute('aria-valuenow', String(progress.percent || 0));
        }
        if (coachCurrentStep) {
            coachCurrentStep.textContent = progress.step_title || '';
        }
        if (coachModuleLabel) {
            coachModuleLabel.textContent = progress.module_title
                ? ((cfg.labels.module || 'Module') + ': ' + progress.module_title)
                : '';
        }
        if (coachStartBtn) {
            coachStartBtn.hidden = true;
        }
        if (coachContinueBtn) {
            coachContinueBtn.hidden = progress.status === 'completed';
        }
        if (coachNextBtn) {
            coachNextBtn.hidden = progress.status === 'completed';
        }
        if (coachRestartBtn) {
            coachRestartBtn.hidden = false;
        }
        if (statusEl && progress.status === 'active') {
            statusEl.textContent = (progress.track_name || '') + ' — ' + (progress.step_title || '');
        }
    }

    async function fetchCoachProgress(track) {
        if (!cfg.coachProgressUrl || !track) {
            return { progress: null, curriculum: cfg.curriculum || null };
        }
        var response = await fetch(cfg.coachProgressUrl + '?track=' + encodeURIComponent(track), {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        });
        var data = await response.json().catch(function () { return {}; });
        return {
            progress: data.progress || null,
            curriculum: data.curriculum || null,
        };
    }

    async function startCoachTrack(restart, silentWelcome, skipKickoff) {
        var track = selectedCoachTrack();
        if (!track || !cfg.coachStartUrl) {
            appendMessage('model', cfg.labels.coachSelectTrack || 'Select a learning path to begin.');
            return;
        }

        try {
            var response = await fetch(cfg.coachStartUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken(),
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({ track: track, restart: !!restart }),
            });
            var data = await response.json();
            if (!response.ok) {
                appendMessage('model', data.error || cfg.errors.generic || 'Error');
                return;
            }
            updateCoachUi(data.progress);
            if (data.welcome && !silentWelcome && !data.kickoff) {
                appendCoachMessage(data.welcome);
                history.push({ role: 'model', text: data.welcome });
            }
            try {
                localStorage.setItem(COACH_TRACK_KEY, track);
            } catch (e) {
                // ignore
            }
            if (data.kickoff && !skipKickoff) {
                await sendCoachKickoff(data.kickoff_mode || 'start');
            }
        } catch (err) {
            appendMessage('model', cfg.errors.network || 'Network error.');
        }
    }

    async function sendCoachKickoff(mode) {
        if (!cfg.chatUrl || isSending) {
            return;
        }

        var activeTrack = selectedCoachTrack();
        if (!activeTrack) {
            return;
        }

        var loadingEl = appendTypingIndicator();
        setSending(true);

        var payload = {
            message: '.',
            kickoff: true,
            kickoff_mode: mode || 'start',
            history: history.slice(-16),
            live: false,
            source: null,
            track: activeTrack,
        };

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
                appendMessage('model', data.error || cfg.errors.generic || 'Something went wrong.');
                return;
            }

            handleCoachResponse(data);
        } catch (err) {
            loadingEl.remove();
            appendMessage('model', cfg.errors.network || 'Network error.');
        } finally {
            setSending(false);
            scrollChatToBottom();
            focusInput();
        }
    }

    function seedOpeningMessage() {
        var openingEl = document.getElementById('gemini-coach-opening-msg');
        if (openingEl) {
            enhanceOpeningMessageEl();
            var bodyEl = openingEl.querySelector('.gemini-assistant-msg-body');
            var openingText = String((bodyEl && bodyEl.textContent) || openingEl.textContent || '').trim();
            if (openingText) {
                history.push({ role: 'model', text: openingText });
                coachSessionStarted = true;
            }
            renderQuickReplies(cfg.initialQuickReplies || []);
            applyCoachQuestionPlaceholder();
            return;
        }

        var text = String(cfg.openingMessage || cfg.launchBriefing || '').trim();
        if (!text || !messagesEl || messagesEl.children.length > 0) {
            return;
        }

        appendCoachMessage(text, cfg.stepCard || null);
        history.push({ role: 'model', text: text });
        renderQuickReplies(cfg.initialQuickReplies || []);
        applyCoachQuestionPlaceholder();
        coachSessionStarted = true;
    }

    async function autoStartCoachSession() {
        if (coachSessionStarted) {
            return true;
        }

        var track = selectedCoachTrack();
        if (!track || !cfg.autoStartCoach) {
            return false;
        }

        coachSessionStarted = true;

        if (coachProgressWrap) {
            coachProgressWrap.hidden = false;
        }

        seedOpeningMessage();

        var coachState = await fetchCoachProgress(track);
        updateCoachUi(coachState.progress, coachState.curriculum);
        var hasOpening = !!document.getElementById('gemini-coach-opening-msg') || history.length > 0;

        if (!coachState.progress) {
            await startCoachTrack(false, true, hasOpening);
            return true;
        }

        if (coachState.progress.status === 'active' && !hasOpening) {
            await sendCoachKickoff('resume');
        }

        return true;
    }

    async function initCoachPanel(autoStart) {
        if (!cfg.defaultTrack && !coachTrackEl) {
            return false;
        }

        if (!coachPanelInitialized) {
            coachPanelInitialized = true;
            bindCoachPanelUi();
        }

        if (cfg.curriculum) {
            renderCurriculumOutline(cfg.curriculum);
        }
        if (cfg.initialProgress) {
            updateCoachUi(cfg.initialProgress, cfg.curriculum);
        }

        if (autoStart) {
            return await autoStartCoachSession();
        }

        return false;
    }

    function bindCoachPanelUi() {
        if (coachTrackEl) {
            var saved = '';
            try {
                saved = localStorage.getItem(COACH_TRACK_KEY) || '';
            } catch (e) {
                saved = '';
            }

            if (saved && coachTrackEl.querySelector('option[value="' + saved + '"]')) {
                coachTrackEl.value = saved;
            } else if (cfg.defaultTrack && coachTrackEl.querySelector('option[value="' + cfg.defaultTrack + '"]')) {
                coachTrackEl.value = cfg.defaultTrack;
            }

            coachTrackEl.addEventListener('change', async function () {
                var track = selectedCoachTrack();
                try {
                    if (track) {
                        localStorage.setItem(COACH_TRACK_KEY, track);
                    }
                } catch (e) {
                    // ignore
                }

                if (!track) {
                    updateCoachUi(null);
                    return;
                }

                var trackState = await fetchCoachProgress(track);
                updateCoachUi(trackState.progress, trackState.curriculum);

                if (!trackState.progress) {
                    await startCoachTrack(false);
                } else if (trackState.progress.status === 'active') {
                    await sendCoachKickoff('resume');
                }
            });

            if (coachStartBtn) {
                coachStartBtn.addEventListener('click', function () { startCoachTrack(false); });
            }
            if (coachRestartBtn) {
                coachRestartBtn.addEventListener('click', function () { startCoachTrack(true); });
            }
            if (coachContinueBtn) {
                coachContinueBtn.addEventListener('click', function () {
                    focusInput();
                    if (inputEl) {
                        inputEl.value = cfg.labels.coachContinuePrompt || 'Here\'s my answer: ';
                        inputEl.placeholder = cfg.labels.typeYourAnswer || 'Type your answer below…';
                        inputEl.focus();
                    }
                });
            }
            if (coachNextBtn) {
                coachNextBtn.addEventListener('click', function () {
                    sendMessage(cfg.labels.coachNextPrompt || 'I\'m ready for the next step.');
                });
            }
        }

        if (coachPanelToggle && coachPanelBody) {
            coachPanelToggle.addEventListener('click', function () {
                var isOpen = coachPanelBody.classList.toggle('is-open');
                coachPanelToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
            });
        }

        if (coachCurriculumToggle && coachCurriculumEl) {
            coachCurriculumToggle.addEventListener('click', function () {
                var isOpen = coachCurriculumEl.classList.toggle('is-open');
                coachCurriculumToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
            });
        }
    }

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
        utterance.lang = document.documentElement.lang || 'en-US';
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
                statusEl.textContent = cfg.labels.ready || cfg.labels.liveOff || 'Ready to guide you';
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

        getMessagesContainer().appendChild(div);
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
            if (previewWrap) {
                previewWrap.classList.toggle('is-active', streaming);
            }
            if (screenPanel) {
                screenPanel.classList.toggle('is-streaming', streaming);
                screenPanel.setAttribute('aria-hidden', streaming ? 'false' : 'true');
            }
            if (panel) {
                panel.classList.toggle('is-screen-streaming', streaming);
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
                statusEl.textContent = cfg.labels.ready || cfg.labels.liveOff || 'Ready to guide you';
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

        var loadingEl = appendTypingIndicator();
        setSending(true);

        var payload = {
            message: text,
            history: history.slice(-16),
            live: isLiveSend,
            source: streamSource || null,
        };

        var activeTrack = selectedCoachTrack();
        if (activeTrack) {
            payload.track = activeTrack;
        }

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

            history.push({ role: 'user', text: text });
            handleCoachResponse(data);
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
            if (cfg.autoStartCoach) {
                initCoachPanel(true);
            }
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

    updateSpeakUi();
    updateMicUi();
    updateLiveUi();
    seedOpeningMessage();

    initCoachPanel(isPageMode || (panel && panel.classList.contains('is-open')));

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
