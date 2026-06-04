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
        justify-content: flex-end;
        gap: 12px;
        flex-wrap: wrap;
        margin-bottom: 14px;
    }

    .question-stage {
        color: #475569;
        font-weight: 600;
        font-size: clamp(13px, 2.5vw, 15px);
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
        color: #4b5675;
    }

    .test-nav {
        background: rgba(255, 255, 255, 0.9);
        -webkit-backdrop-filter: blur(10px);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(226, 232, 240, 0.95);
        border-radius: var(--test-radius);
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

@php
    $lesson_history = App\Models\Watch_history::where('course_id', $course_details->id)
        ->where('student_id', auth()->user()->id)
        ->firstOrNew();
    $completed_lesson_arr = json_decode($lesson_history->completed_lesson, true);
    $completed_lesson_arr = is_array($completed_lesson_arr) ? $completed_lesson_arr : array();
    $isPracticeTest = ($quiz->lesson_type ?? '') === 'practice_test';
@endphp

<div class="test-pass-session">
<form action="{{ route('quiz.submit', $quiz->id) }}" method="post" class="quiz-submit-form">@csrf
    <input type="hidden" name="quiz_id" value="{{ $quiz->id }}">
    <div class="test-session-shell">
        <div class="test-session-topbar">
            <div class="test-session-meta">
                <span class="question-stage" id="questionStageLabel">
                    {{ get_phrase('Question') }} 1 / {{ $questions->count() }}
                </span>
            </div>
            <div class="test-progress-wrap">
                <div class="test-progress-bar" id="testProgressBar"></div>
            </div>
        </div>
    @foreach ($questions as $key => $question)
        <div class="question mb-4 @if ($key > 0) d-none @endif">
            <div class="question-inner">
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
                @endif
            </div>
            </div>
        </div>
    @endforeach
    </div>
</form>


@if ($questions->count() > 0)
    <div class="row test-nav g-0">
        <div class="col-12 test-nav-inner">
            <button type="button" class="eBtn gradient border-0" id="prevBtn" onclick="prevQuestion()"><i
                    class="fi fi-rr-angle-small-left"></i>{{ get_phrase('Prev') }}</button>
            <button type="button" class="eBtn gradient border-0" id="nextBtn"
                onclick="nextQuestion()">{{ get_phrase('Next') }}<i class="fi fi-rr-angle-small-right"></i></button>
            @if ($isPracticeTest || $submits->count() < $quiz->retake)
                <button type="button" class="eBtn gradient border-0 d-none" id="submitBtn"
                    onclick="submitQuiz()">{{ get_phrase('Submit') }}<i class="fi fi-rr-badge-check ms-2"></i></button>
            @endif
        </div>
    </div>
@endif

</div>

@include('course_player.init')

<script>
    let nextBtn = document.querySelector('#nextBtn');
    let prevBtn = document.querySelector('#prevBtn');
    let submitBtn = document.querySelector('#submitBtn');
    let submitForm = document.querySelector('.quiz-submit-form');
    let questions = document.querySelectorAll('.question');
    let testProgressBar = document.querySelector('#testProgressBar');
    let questionStageLabel = document.querySelector('#questionStageLabel');

    function getVisibleQuestionIndex() {
        const visible = document.querySelector('.question:not(.d-none)');
        return Array.from(questions).indexOf(visible);
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
    }

    // Initialize buttons visibility
    if (questions.length === 1 && nextBtn) {
        nextBtn.classList.add('d-none'); // Hide Next button
        if (submitBtn) {
            submitBtn.classList.remove('d-none'); // Show Submit button
        }
    }
    refreshProgress();

    // Next question
    function nextQuestion() {
        if (!nextBtn) {
            return;
        }
        let selectQuestion = document.querySelector('.question:not(.d-none)');
        let nextQuestion = selectQuestion.nextElementSibling;
        if (nextQuestion && nextQuestion.classList.contains('question')) {
            selectQuestion.classList.add('d-none');
            nextQuestion.classList.remove('d-none');
        }
        let nextNextQuestion = nextQuestion ? nextQuestion.nextElementSibling : null;
        if (!(nextNextQuestion && nextNextQuestion.classList.contains('question'))) {
            if (submitBtn) {
                submitBtn.classList.remove('d-none');
            }
            nextBtn.classList.add('d-none');
        }
        refreshProgress();
    }

    // Previous question
    function prevQuestion() {
        if (!nextBtn) {
            return;
        }
        let selectQuestion = document.querySelector('.question:not(.d-none)');
        let prevQuestion = selectQuestion.previousElementSibling;
        if (prevQuestion && prevQuestion.classList.contains('question')) {
            selectQuestion.classList.add('d-none');
            prevQuestion.classList.remove('d-none');
        }
        if (nextBtn.classList.contains('d-none')) {
            nextBtn.classList.remove('d-none');
            if (submitBtn) {
                submitBtn.classList.add('d-none');
            }
        }
        refreshProgress();
    }

    // Submit quiz
    function submitQuiz() {

        var quizId = "{{ $quiz->id }}";
        var completed_lesson_arr = @json($completed_lesson_arr);  // Convert the PHP array to a JavaScript array
        
        // Check if quizId is in the completed_lesson_arr array using JavaScript's `includes()` method
        if (!completed_lesson_arr.includes(quizId)) {
            $.ajax({
                url: "{{ route('set.watch.history') }}", // Your route
                type: "post",
                data: {
                    lesson_id: "{{ $quiz->id }}",
                    course_id: "{{ $course_details->id }}"
                },
                success: function(response) {
                    submitForm.submit();
                },
                error: function(xhr, status, error) {
                    console.error("Error updating watch history:", xhr.responseText);
                }
            });
        } else {
            submitForm.submit();
        }
    }
</script>

