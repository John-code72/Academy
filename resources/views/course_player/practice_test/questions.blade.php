<style>
    .test-pass-session {
        --test-pad: clamp(14px, 3.5vw, 28px);
        --test-radius: 20px;
        --test-accent: #2d4281;
        --test-accent-soft: rgba(45, 66, 129, 0.08);
        max-width: 920px;
        margin-left: auto;
        margin-right: auto;
        padding-inline: var(--test-pad);
        padding-block: clamp(8px, 2vw, 16px) clamp(24px, 4vw, 40px);
    }

    .test-session-shell {
        background:
            radial-gradient(120% 140% at 100% -20%, rgba(99, 102, 241, 0.12), transparent 50%),
            linear-gradient(180deg, #f5f8ff 0%, #eef2fb 100%);
        border: 1px solid rgba(45, 66, 129, 0.1);
        border-radius: var(--test-radius);
        padding: var(--test-pad);
        margin-bottom: var(--test-pad);
        box-shadow: 0 1px 0 rgba(255, 255, 255, 0.85) inset, 0 16px 40px rgba(37, 55, 120, 0.08);
    }

    .test-session-topbar {
        background: rgba(255, 255, 255, 0.72);
        -webkit-backdrop-filter: blur(12px);
        backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.9);
        border-radius: calc(var(--test-radius) - 4px);
        padding: clamp(12px, 3vw, 18px) clamp(14px, 3vw, 20px);
        margin-bottom: clamp(14px, 3vw, 22px);
        box-shadow: 0 4px 24px rgba(30, 41, 90, 0.06);
    }

    .test-session-meta {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        flex-wrap: wrap;
        margin-bottom: 14px;
    }

    .test-progress-wrap {
        background: rgba(45, 66, 129, 0.1);
        border-radius: 999px;
        height: 8px;
        overflow: hidden;
    }

    .test-progress-bar {
        background: linear-gradient(90deg, #2d4281, #5b73c9, #818cf8);
        height: 100%;
        width: 0;
        transition: width 0.32s cubic-bezier(0.33, 1, 0.68, 1);
        border-radius: 999px;
        box-shadow: 0 0 12px rgba(45, 66, 129, 0.35);
    }

    .section-chip {
        background: linear-gradient(135deg, var(--test-accent-soft) 0%, rgba(129, 140, 248, 0.15) 100%);
        color: var(--test-accent);
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        border-radius: 999px;
        padding: 6px 12px;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        border: 1px solid rgba(45, 66, 129, 0.12);
    }

    .question-stage {
        color: #475569;
        font-weight: 600;
        font-size: clamp(13px, 2.5vw, 15px);
    }

    .part-heading-modern {
        font-size: clamp(1rem, 2.8vw, 1.125rem);
        font-weight: 700;
        color: #0f172a;
        letter-spacing: -0.02em;
        margin-bottom: 0.875rem !important;
        padding-left: 14px;
        border-left: 4px solid var(--test-accent);
    }

    .question {
        background: #ffffff;
        border: 1px solid rgba(226, 232, 240, 0.95);
        border-radius: calc(var(--test-radius) - 4px);
        margin-bottom: var(--test-pad) !important;
        box-shadow: 0 2px 0 rgba(15, 23, 42, 0.02), 0 12px 40px rgba(30, 41, 59, 0.06);
    }

    .question-inner {
        padding: clamp(18px, 4vw, 28px);
    }

    .serial {
        flex-shrink: 0;
        width: 36px;
        height: 36px;
        background: linear-gradient(145deg, #f1f5f9 0%, #fff 100%);
        color: var(--test-accent);
        font-weight: 700;
        font-size: 14px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 1px solid rgba(45, 66, 129, 0.12);
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.06);
    }

    .question-title-block {
        font-size: clamp(14px, 2.8vw, 16px);
        line-height: 1.6;
        color: #1e293b;
        flex: 1;
        min-width: 0;
    }
    .fill-text-note {
        color: #4b5675
    }

    .question .form-check-label {
        margin-left: 4px;
        line-height: 1.45;
    }

    .question .col-sm-6,
    .question .col-sm-2 {
        margin-bottom: 8px;
    }

    .question .col-sm-6,
    .question .col-sm-2 {
        display: flex;
        align-items: flex-start;
        gap: 8px;
    }

    .question .form-check-input {
        margin-top: 3px;
        flex-shrink: 0;
    }

    .part-break {
        background: linear-gradient(180deg, #fff 0%, #f8fafc 100%);
        border: 1px dashed rgba(45, 66, 129, 0.25);
        border-radius: var(--test-radius);
        padding: clamp(24px, 5vw, 36px);
        text-align: center;
        margin-bottom: var(--test-pad, 16px);
        box-shadow: 0 8px 30px rgba(30, 41, 90, 0.06);
    }

    .part-break h5 {
        font-weight: 700;
        color: #0f172a;
        letter-spacing: -0.02em;
        margin-bottom: 0.5rem !important;
    }

    .part-b-passage {
        background: linear-gradient(180deg, #fffbeb 0%, #fefce8 100%);
        border: 1px solid rgba(234, 179, 8, 0.28);
        border-radius: 14px;
        padding: clamp(16px, 3vw, 22px);
        margin-bottom: 16px;
        line-height: 1.8;
        color: #334155;
        box-shadow: 0 2px 8px rgba(234, 179, 8, 0.08);
    }

    .part-b-passage .err-token {
        background: #fff1f2;
        border-bottom: 2px solid #ef4444;
        padding: 0 2px;
        border-radius: 4px;
        font-weight: 600;
    }

    .part-b-passage .err-num {
        display: inline-flex;
        justify-content: center;
        align-items: center;
        min-width: 18px;
        height: 18px;
        font-size: 11px;
        border-radius: 50%;
        background: #ef4444;
        color: #fff;
        margin-left: 3px;
        vertical-align: text-top;
    }

    .part-b-help {
        font-size: 13px;
        color: #64748b;
        margin-bottom: 14px;
    }

    .part-c-card {
        background: linear-gradient(160deg, #f0f9ff 0%, #eef5ff 100%);
        border: 1px solid rgba(56, 189, 248, 0.35);
        border-radius: 14px;
        padding: clamp(14px, 3vw, 20px);
        margin-bottom: 16px;
        color: #0f172a;
    }

    .part-c-card h6 {
        margin-bottom: 8px;
        font-weight: 700;
        font-size: 13px;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: var(--test-accent);
    }

    .long-answer-input {
        min-height: 220px;
        border-radius: 14px;
        border: 1px solid rgba(148, 163, 184, 0.5);
        padding: 14px 16px;
        transition: border-color 0.2s, box-shadow 0.2s;
    }

    .long-answer-input:focus {
        border-color: var(--test-accent);
        box-shadow: 0 0 0 3px rgba(45, 66, 129, 0.12);
    }

    .speaking-card {
        border: 1px solid rgba(59, 130, 246, 0.2);
        background: linear-gradient(180deg, #f8fbff 0%, #ffffff 50%);
        border-radius: 14px;
        padding: clamp(14px, 3vw, 20px);
        width: 100%;
        box-shadow: 0 2px 16px rgba(30, 64, 175, 0.05);
    }

    .speaking-video-placeholder {
        border: 1px dashed #93c5fd;
        border-radius: 10px;
        min-height: 180px;
        background: linear-gradient(180deg, #eff6ff 0%, #f8fafc 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: #1e3a8a;
        font-weight: 600;
        text-align: center;
        padding: 14px;
        margin-bottom: 12px;
    }

    .recording-state {
        font-size: 13px;
        color: #475569;
        margin-top: 8px;
    }

    .test-nav {
        background: rgba(255, 255, 255, 0.9);
        -webkit-backdrop-filter: blur(10px);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(226, 232, 240, 0.95);
        border-radius: calc(var(--test-radius));
        padding: clamp(12px, 3vw, 16px);
        margin-bottom: clamp(8px, 2vw, 16px);
        box-shadow: 0 -4px 32px rgba(15, 23, 42, 0.06);
    }

    .test-nav-inner {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: center;
        gap: 10px 14px;
    }

    .test-nav button.eBtn {
        min-width: clamp(116px, 28vw, 140px);
        border-radius: 12px !important;
        padding: 10px 18px !important;
        font-weight: 600;
        transition: transform 0.18s ease, box-shadow 0.18s ease;
    }

    .test-nav button.eBtn:active {
        transform: scale(0.98);
    }

    @media (max-width: 768px) {
        .question .col-sm-6 {
            flex: 1 1 100%;
            max-width: 100%;
        }
    }
</style>

<div class="test-pass-session">
<form action="{{ route('quiz.submit', $quiz->id) }}" method="post" class="quiz-submit-form">@csrf
    <input type="hidden" name="quiz_id" value="{{ $quiz->id }}">
    @php
        $partABreakAfter = 15;
        $partBBreakAfter = 25;
        $totalQuestions = $questions->count();
    @endphp
    <div class="test-session-shell">
        <div class="test-session-topbar">
            <div class="test-session-meta">
                <span class="section-chip" id="currentSectionLabel">{{ get_phrase('PART A') }}</span>
                <span class="question-stage" id="questionStageLabel">
                    {{ get_phrase('Question') }} 1 / {{ $totalQuestions }}
                </span>
            </div>
            <div class="test-progress-wrap">
                <div class="test-progress-bar" id="testProgressBar"></div>
            </div>
        </div>
    @foreach ($questions as $key => $question)
        <div class="question mb-4 @if ($key > 0) d-none @endif">
            <div class="question-inner">
            @if ($key === 0)
                <h5 class="part-heading-modern mb-3">{{ get_phrase('PART A: Grammar Knowledge') }}</h5>
            @elseif($key === $partABreakAfter)
                <h5 class="part-heading-modern mb-3">{{ get_phrase('PART B: Error Identification and Correction') }}</h5>
                <p class="part-b-help">
                    {{ get_phrase('Read the full passage below. The 10 marked items correspond to the 10 correction questions.') }}
                </p>
                <div class="part-b-passage">
                    Dear Valued Client, Thank you for <span class="err-token">you're</span><span class="err-num">1</span>
                    continued partnership with Defrilex. We are <span class="err-token">writting</span><span class="err-num">2</span>
                    to inform you that our interpreter scheduling system will undergo
                    <span class="err-token">maintainance</span><span class="err-num">3</span>
                    this Saturday from 2:00 AM to 6:00 AM EST. During this window, the system
                    <span class="err-token">maybe</span><span class="err-num">4</span> temporarily
                    <span class="err-token">unavailble</span><span class="err-num">5</span>. However, emergency interpretation services will remain
                    <span class="err-token">availible</span><span class="err-num">6</span>
                    through our dedicated hotline. Each of our interpreters
                    <span class="err-token">are</span><span class="err-num">7</span> prepared to handle urgent requests during the
                    <span class="err-token">maintanence</span><span class="err-num">8</span> period.
                    We apologize for any <span class="err-token">inconveniance</span><span class="err-num">9</span>
                    and appreciate you patience. If you have questions, please do not hesitate to reach out to your dedicated account manager,
                    <span class="err-token">whom</span><span class="err-num">10</span> is available Monday through Friday.
                    <br><br>
                    Sincerely,<br>
                    The Defrilex Operations Team
                </div>
            @elseif($key === $partBBreakAfter)
                <h5 class="part-heading-modern mb-3">{{ get_phrase('PART C: Professional Writing Prompt (40 points)') }}</h5>
                <div class="part-c-card">
                    <h6>{{ get_phrase('Instructions') }}</h6>
                    <p class="mb-2">{{ get_phrase('Write a professional email (150–250 words) based on the scenario below.') }}</p>
                    <h6>{{ get_phrase('Scenario') }}</h6>
                    <p class="mb-0">
                        {{ get_phrase('You work at Defrilex. A healthcare client has reported that an interpreter arrived 10 minutes late to a scheduled session yesterday, causing the patient appointment to run over. The client is frustrated. Write an email to the client acknowledging the issue, explaining what happened (the interpreter experienced a technical connectivity issue), what steps are being taken to prevent it from happening again (backup interpreter protocol and pre-session connectivity checks), and expressing Defrilex commitment to service quality.') }}
                    </p>
                </div>
            @endif
            <div class="mb-3 d-flex gap-3 align-items-start">
                <span class="serial">{{ ++$key }} </span>
                @if($question->type == 'fill_blanks')
                    <div class="question-title-block">
                        @php
                        $correct_answers = json_decode($question['answer'], true);
                        $question_title = remove_js(htmlspecialchars_decode_($question['title']));
                        foreach($correct_answers as $correct_answer):
                            $question_title = str_replace($correct_answer, ' _____ ', $question_title);
                        endforeach;
                        @endphp
                        {{ $question_title; }}
                    </div>
                @elseif($question->type == 'speaking_prompt')
                    {{-- Prompt text is only in video; no duplicate title here --}}
                @else
                    <div class="question-title-block">{!! $question->title !!}</div>
                @endif
            </div>

            <div class="row gap-0">
                @if ($question->type == 'mcq')
                    @php $options = json_decode($question->options, true) ?? []; @endphp
                    @foreach ($options as $index => $option)
                        <div class="col-sm-6">
                            <input class="form-check-input" type="checkbox" name="{{ $question->id }}[]"
                                value="{{ $option }}" id="{{ $option }}-{{ $question->id }}">
                            <label class="form-check-label text-capitalize"
                                for="{{ $option }}-{{ $question->id }}">{{ $option }}</label>
                        </div>
                    @endforeach
                @elseif($question->type == 'fill_blanks')
                    <input type="text" class="form-control tagify" name="{{ $question->id }}" data-role="tagsinput">
                    <small class="fill-text-note">{{ get_phrase('You can keep multiple answers. Just put your answer and hit enter.') }}</small>
                @elseif($question->type == 'true_false')
                    <div class="col-sm-2">
                        <input class="form-check-input" type="radio" name="{{ $question->id }}" value="true"
                            id="question-{{ $question->id }}-true">
                        <label class="form-check-label"
                            for="question-{{ $question->id }}-true">{{ get_phrase('True') }}</label>
                    </div>
                    <div class="col-sm-2">
                        <input class="form-check-input" type="radio" name="{{ $question->id }}" value="false"
                            id="question-{{ $question->id }}-false">
                        <label class="form-check-label"
                            for="question-{{ $question->id }}-false">{{ get_phrase('False') }}</label>
                    </div>
                @elseif($question->type == 'long_answer')
                    <textarea class="form-control long-answer-input" name="{{ $question->id }}"
                        placeholder="{{ get_phrase('Write your professional email here...') }}"></textarea>
                @elseif($question->type == 'speaking_prompt')
                    @php
                        $promptMeta = json_decode($question->options, true) ?? [];
                        $videoUrl = $promptMeta['video_url'] ?? null;
                    @endphp
                    <div class="speaking-card">
                        @if ($videoUrl)
                            <video class="w-100 rounded-3 mb-3" controls>
                                <source src="{{ $videoUrl }}">
                            </video>
                        @else
                            <div class="speaking-video-placeholder">
                                {{ get_phrase('Video prompt area') }}
                            </div>
                        @endif
                        <div class="d-flex gap-2 flex-wrap">
                            <button type="button" class="eBtn gradient border-0 btn-start-record" data-question-id="{{ $question->id }}">
                                {{ get_phrase('Start recording') }}
                            </button>
                            <button type="button" class="eBtn gradient-border btn-stop-record d-none" data-question-id="{{ $question->id }}">
                                {{ get_phrase('Stop recording') }}
                            </button>
                            <button type="button" class="eBtn gradient-border btn-rerecord d-none" data-question-id="{{ $question->id }}">
                                {{ get_phrase('Re-record') }}
                            </button>
                        </div>
                        <p class="recording-state" id="recording-state-{{ $question->id }}">
                            {{ get_phrase('No video recorded yet.') }}
                        </p>
                        <video class="w-100 rounded-3 d-none" controls playsinline id="video-preview-{{ $question->id }}"></video>
                        <input type="hidden" name="{{ $question->id }}" id="video-input-{{ $question->id }}">
                    </div>
                @endif
            </div>
            </div>
        </div>
    @endforeach
    </div>
</form>

<div id="partBreakCard" class="part-break d-none">
    <h5 class="mb-2">{{ get_phrase('PART A completed') }}</h5>
    <p class="text-muted mb-3">{{ get_phrase('Click below to start PART B.') }}</p>
    <button type="button" class="eBtn gradient border-0" onclick="startPartB()">
        {{ get_phrase('Start PART B') }}
    </button>
</div>
<div id="partBreakCardB" class="part-break d-none">
    <h5 class="mb-2">{{ get_phrase('PART B completed') }}</h5>
    <p class="text-muted mb-3">{{ get_phrase('Click below to start PART C.') }}</p>
    <button type="button" class="eBtn gradient border-0" onclick="startPartC()">
        {{ get_phrase('Start PART C') }}
    </button>
</div>


@if ($questions->count() > 0)
    <div class="row test-nav g-0">
        <div class="col-12 test-nav-inner">
            <button type="button" class="eBtn gradient border-0" id="prevBtn" onclick="prevQuestion()"><i
                    class="fi fi-rr-angle-small-left"></i>{{ get_phrase('Prev') }}</button>
            <button type="button" class="eBtn gradient border-0" id="nextBtn"
                onclick="nextQuestion()">{{ get_phrase('Next') }}<i class="fi fi-rr-angle-small-right"></i></button>
            <button type="button" class="eBtn gradient border-0 d-none" id="submitBtn"
                onclick="submitQuiz()">{{ get_phrase('Submit') }}<i class="fi fi-rr-badge-check ms-2"></i></button>
        </div>
    </div>
@endif
</div>

<script>
    let nextBtn = document.querySelector('#nextBtn');
    let prevBtn = document.querySelector('#prevBtn');
    let submitBtn = document.querySelector('#submitBtn');
    let submitForm = document.querySelector('.quiz-submit-form');
    let questions = document.querySelectorAll('.question');
    let partBreakCard = document.querySelector('#partBreakCard');
    let partBreakCardB = document.querySelector('#partBreakCardB');
    let currentIndex = 0;
    let partABreakAfter = 15;
    let partBBreakAfter = 25;
    let testProgressBar = document.querySelector('#testProgressBar');
    let questionStageLabel = document.querySelector('#questionStageLabel');
    let currentSectionLabel = document.querySelector('#currentSectionLabel');

    function getVisibleQuestionIndex() {
        const visible = document.querySelector('.question:not(.d-none)');
        return Array.from(questions).indexOf(visible);
    }

    function detectSection(index) {
        if (index >= partBBreakAfter) {
            return "{{ get_phrase('PART C') }}";
        }
        if (index >= partABreakAfter) {
            return "{{ get_phrase('PART B') }}";
        }
        return "{{ get_phrase('PART A') }}";
    }

    function refreshProgress() {
        if (!questions.length) {
            return;
        }
        const index = getVisibleQuestionIndex();
        if (index < 0) {
            return;
        }
        const total = questions.length;
        const ratio = ((index + 1) / total) * 100;
        if (testProgressBar) {
            testProgressBar.style.width = `${ratio}%`;
        }
        if (questionStageLabel) {
            questionStageLabel.textContent = `{{ get_phrase('Question') }} ${index + 1} / ${total}`;
        }
        if (currentSectionLabel) {
            currentSectionLabel.textContent = detectSection(index);
        }
    }

    if (questions.length === 1 && nextBtn && submitBtn) {
        nextBtn.classList.add('d-none');
        submitBtn.classList.remove('d-none');
    }
    refreshProgress();

    function nextQuestion() {
        if (!nextBtn || !submitBtn) {
            return;
        }
        let selectQuestion = document.querySelector('.question:not(.d-none)');
        if (!selectQuestion) {
            return;
        }
        let nextQuestion = selectQuestion.nextElementSibling;
        currentIndex = Array.from(questions).indexOf(selectQuestion);

        if (currentIndex === partABreakAfter - 1 && partBreakCard && questions.length > partABreakAfter) {
            selectQuestion.classList.add('d-none');
            partBreakCard.classList.remove('d-none');
            nextBtn.classList.add('d-none');
            submitBtn.classList.add('d-none');
            return;
        }

        if (currentIndex === partBBreakAfter - 1 && questions.length > partBBreakAfter) {
            selectQuestion.classList.add('d-none');
            questions[partBBreakAfter].classList.remove('d-none');
            nextBtn.classList.add('d-none');
            submitBtn.classList.remove('d-none');
            return;
        }

        if (nextQuestion && nextQuestion.classList.contains('question')) {
            selectQuestion.classList.add('d-none');
            nextQuestion.classList.remove('d-none');
        }
        let nextNextQuestion = nextQuestion ? nextQuestion.nextElementSibling : null;
        if (!(nextNextQuestion && nextNextQuestion.classList.contains('question'))) {
            submitBtn.classList.remove('d-none');
            nextBtn.classList.add('d-none');
        }
        refreshProgress();
    }

    function prevQuestion() {
        if (!nextBtn || !submitBtn) {
            return;
        }
        let selectQuestion = document.querySelector('.question:not(.d-none)');
        if (!selectQuestion) {
            return;
        }
        let prevQuestion = selectQuestion.previousElementSibling;
        currentIndex = Array.from(questions).indexOf(selectQuestion);
        if (prevQuestion && prevQuestion.classList.contains('question')) {
            selectQuestion.classList.add('d-none');
            prevQuestion.classList.remove('d-none');
        }
        if (nextBtn.classList.contains('d-none')) {
            nextBtn.classList.remove('d-none');
            submitBtn.classList.add('d-none');
        }
        refreshProgress();
    }

    function submitQuiz() {
        if (submitForm) {
            submitForm.submit();
        }
    }

    function startPartB() {
        if (!partBreakCard || questions.length <= partABreakAfter) {
            return;
        }
        partBreakCard.classList.add('d-none');
        questions[partABreakAfter].classList.remove('d-none');
        if (nextBtn) {
            nextBtn.classList.remove('d-none');
        }
        refreshProgress();
    }

    function startPartC() {
        if (!partBreakCardB || questions.length <= partBBreakAfter) {
            return;
        }
        partBreakCardB.classList.add('d-none');
        questions[partBBreakAfter].classList.remove('d-none');
        if (nextBtn) {
            nextBtn.classList.add('d-none');
        }
        if (submitBtn) {
            submitBtn.classList.remove('d-none');
        }
        refreshProgress();
    }

    window.nextQuestion = nextQuestion;
    window.prevQuestion = prevQuestion;
    window.submitQuiz = submitQuiz;
    window.startPartB = startPartB;
    window.startPartC = startPartC;

    $('.tagify:not(.inited)').each(function(index, element) {
        var tagify = new Tagify(element, {
            placeholder: '{{ get_phrase('Enter your keywords') }}',
            delimiters: "~",
        });
        $(element).addClass('inited');
    });

    let mediaRecorder = null;
    let mediaStream = null;
    let mediaChunks = [];
    let activeQuestionId = null;
    let recordedMimeType = 'video/webm';

    function pickVideoMimeType() {
        const candidates = [
            'video/webm;codecs=vp9,opus',
            'video/webm;codecs=vp8,opus',
            'video/webm',
        ];
        for (let i = 0; i < candidates.length; i++) {
            if (MediaRecorder.isTypeSupported(candidates[i])) {
                return candidates[i];
            }
        }
        return '';
    }

    async function startRecording(questionId) {
        try {
            activeQuestionId = questionId;
            mediaChunks = [];
            recordedMimeType = pickVideoMimeType();
            mediaStream = await navigator.mediaDevices.getUserMedia({
                video: { facingMode: 'user', width: { ideal: 1280 }, height: { ideal: 720 } },
                audio: true,
            });
            const options = recordedMimeType ? { mimeType: recordedMimeType } : {};
            mediaRecorder = new MediaRecorder(mediaStream, options);
            if (mediaRecorder.mimeType) {
                recordedMimeType = mediaRecorder.mimeType;
            }

            mediaRecorder.ondataavailable = function(event) {
                if (event.data.size > 0) {
                    mediaChunks.push(event.data);
                }
            };

            mediaRecorder.onstop = async function() {
                const blob = new Blob(mediaChunks, { type: recordedMimeType || 'video/webm' });
                const preview = document.querySelector(`#video-preview-${activeQuestionId}`);
                const state = document.querySelector(`#recording-state-${activeQuestionId}`);
                const input = document.querySelector(`#video-input-${activeQuestionId}`);
                const startBtn = document.querySelector(`.btn-start-record[data-question-id='${activeQuestionId}']`);
                const stopBtn = document.querySelector(`.btn-stop-record[data-question-id='${activeQuestionId}']`);
                const reBtn = document.querySelector(`.btn-rerecord[data-question-id='${activeQuestionId}']`);

                if (preview) {
                    preview.src = URL.createObjectURL(blob);
                    preview.classList.remove('d-none');
                }
                if (state) state.textContent = "{{ get_phrase('Uploading video...') }}";

                const ext = (recordedMimeType && recordedMimeType.indexOf('mp4') !== -1) ? 'mp4' : 'webm';
                const formData = new FormData();
                formData.append('video', new File([blob], `answer-${activeQuestionId}.${ext}`, { type: blob.type || 'video/webm' }));
                formData.append('quiz_id', "{{ $quiz->id }}");
                formData.append('question_id', activeQuestionId);
                formData.append('_token', "{{ csrf_token() }}");

                $.ajax({
                    url: "{{ route('practice.test.upload.recording') }}",
                    type: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (input) input.value = response.path || '';
                        if (state) state.textContent = "{{ get_phrase('Video uploaded successfully.') }}";
                        if (startBtn) startBtn.classList.add('d-none');
                        if (stopBtn) stopBtn.classList.add('d-none');
                        if (reBtn) reBtn.classList.remove('d-none');
                    },
                    error: function() {
                        if (state) state.textContent = "{{ get_phrase('Upload failed. Please re-record.') }}";
                    }
                });
            };

            mediaRecorder.start();

            const state = document.querySelector(`#recording-state-${questionId}`);
            const startBtn = document.querySelector(`.btn-start-record[data-question-id='${questionId}']`);
            const stopBtn = document.querySelector(`.btn-stop-record[data-question-id='${questionId}']`);
            if (state) state.textContent = "{{ get_phrase('Recording in progress...') }}";
            if (startBtn) startBtn.classList.add('d-none');
            if (stopBtn) stopBtn.classList.remove('d-none');
        } catch (e) {
            const state = document.querySelector(`#recording-state-${questionId}`);
            if (state) state.textContent = "{{ get_phrase('Camera or microphone access denied or unavailable.') }}";
        }
    }

    function stopRecording(questionId) {
        if (mediaRecorder && mediaRecorder.state !== 'inactive') {
            mediaRecorder.stop();
        }
        if (mediaStream) {
            mediaStream.getTracks().forEach(track => track.stop());
        }
        const stopBtn = document.querySelector(`.btn-stop-record[data-question-id='${questionId}']`);
        if (stopBtn) stopBtn.classList.add('d-none');
    }

    $(document).on('click', '.btn-start-record', function() {
        startRecording($(this).data('question-id'));
    });
    $(document).on('click', '.btn-stop-record', function() {
        stopRecording($(this).data('question-id'));
    });
    $(document).on('click', '.btn-rerecord', function() {
        const qid = $(this).data('question-id');
        const input = document.querySelector(`#video-input-${qid}`);
        const preview = document.querySelector(`#video-preview-${qid}`);
        const state = document.querySelector(`#recording-state-${qid}`);
        if (input) input.value = '';
        if (preview) {
            preview.classList.add('d-none');
            preview.removeAttribute('src');
        }
        if (state) state.textContent = "{{ get_phrase('No video recorded yet.') }}";
        $(this).addClass('d-none');
        $(`.btn-start-record[data-question-id='${qid}']`).removeClass('d-none');
    });
</script>
