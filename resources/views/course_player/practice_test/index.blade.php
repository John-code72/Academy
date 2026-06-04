<style>
    .quiz-starter {
        background: #f8faff;
        border: 1px solid #e6ebff;
        border-radius: 14px;
        padding-top: 18px;
        padding-bottom: 18px;
        margin-top: 12px;
    }

    .quiz-title {
        font-size: 18px;
        font-weight: 500;
        margin-bottom: 12px;
        border-bottom: 1px solid #C3C9DA;
        padding-bottom: 20px;
        font-weight: 600;
    }

    .quiz-starter .starter-label {
        display: inline-block;
        width: 110px;
    }

    .quiz-starter p {
        font-size: 15px;
        font-weight: 500;
        color: #6e798a;
        margin-bottom: 10px;
    }

    .question {
        min-height: 155px;
    }

    input[type="text"] {
        padding: 12px 50px 12px 20px;
        border-radius: 10px;
        border: 1px solid #6b738530;
        box-shadow: none !important;
    }

    .gradient-border {
        background: #fff;
        border: 2px solid #2d4281;
        color: #212529;
        transition: .3s;
        border-radius: 10px;
    }

    .gradient-border:hover {
        color: #fff;
        background: #2d4281;
    }

    .serial {
        width: 30px;
        height: 30px;
        background: #F2F3F5;
        border-radius: 30px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    #quizTimer {
        width: 80px;
        margin-bottom: 0;
    }

    .load-content {
        margin-top: 8px;
        padding-inline: clamp(12px, 3vw, 24px);
    }

    @media (max-width: 768px) {
        .quiz-starter .starter-label {
            width: 96px;
        }
    }
</style>
@php
    $quiz = App\Models\Lesson::where('id', request()->route()->parameter('id'))
        ->firstOrNew();

    $questions = DB::table('questions')
        ->where('quiz_id', $quiz->id)
        ->get();

    $submits = DB::table('quiz_submissions')
        ->where('quiz_id', $quiz->id)
        ->where('user_id', auth()->user()->id)
        ->get();
@endphp

<div class="row px-4">
    <div class="col-12">
        <h4 class="quiz-title text-center mt-4">
            <span>{{ $quiz->title }}</span>
            <span class="badge bg-secondary ms-2">{{ get_phrase('Defrilex onboarding Test') }}</span>
        </h4>

        <div class="timer-container d-none">
            <div class="d-flex align-content-center gap-2 justify-content-end">
                <span>
                    <i class="fi fi-rr-clock-five"></i>
                </span>
                <span class="fw-600 fs-6">{{ get_phrase('Time left : ') }}</span>
                <p class="text-center fw-600 fs-6" id="quizTimer"></p>
            </div>
        </div>
    </div>
</div>


<div class="row px-4 quiz-starter">
    <div class="col-md-6">
        <p>
            @php $duration = explode(':', $quiz->duration); @endphp
            <span class="starter-label">{{ get_phrase('Duration') }}</span>
            <span>: {{ $duration[0] ?? '0' }} {{ get_phrase('Hour') }}</span>
            <span>{{ $duration[1] ?? '0' }} {{ get_phrase('Minute') }}</span>
            <span>{{ $duration[2] ?? '0' }} {{ get_phrase('Second') }}</span>
        </p>
        <p>
            <span class="starter-label">{{ get_phrase('Total Marks') }}</span>
            <span>: {{ $quiz->total_mark < 10 ? '0' : '' }}{{ $quiz->total_mark }}</span>
        </p>
        <p>
            <span class="starter-label">{{ get_phrase('Pass Marks') }}</span>
            <span>: {{ $quiz->pass_mark < 10 ? '0' : '' }}{{ $quiz->pass_mark }}</span>
        </p>
        <p>
            <span class="starter-label">{{ get_phrase('Retake') }}</span>
            <span>: {{ get_phrase('Unlimited') }}</span>
        </p>
    </div>
    <div class="col-md-6">
        <p>
            <span class="starter-label">{{ get_phrase('Question Type') }}</span>
            <span class="text-capitalize">:
                {{ str_replace('_', ' ', implode(', ', $questions->pluck('type')->unique()->toArray())) }}</span>
        </p>
        <p>
            <span class="starter-label">{{ get_phrase('Attempts') }}</span>
            <span>: {{ $submits->count() < 10 ? '0' : '' }}{{ $submits->count() }}</span>
        </p>
        <p>
            <span class="starter-label">{{ get_phrase('Total Question') }}</span>
            <span>: {{ $questions->count() < 10 ? '0' : '' }}{{ $questions->count() }}</span>
        </p>
    </div>

    <div class="col-12 d-flex justify-content-center gap-3">
        @foreach ($submits as $key => $submit)
            <button type="button" class="eBtn gradient-border result-btn" onclick="getResult(this)"
                id="{{ $submit->id }}">{{ get_phrase('View Result') }} {{ ++$key }}</button>
        @endforeach

        <button type="button" class="eBtn gradient border-0"
            id="starterBtn" @if ($questions->count() === 0) disabled @endif>{{ get_phrase('Start Defrilex onboarding Test') }}</button>
    </div>
</div>

<div class="load-content"></div>
@if ($questions->count() === 0)
    <p class="text-center text-muted mt-3">{{ get_phrase('No questions available for this Defrilex onboarding Test yet.') }}</p>
@endif

<script src="{{ asset('assets/frontend/default/js/jquery-3.7.1.min.js') }}"></script>
<script>
    let starterContainer = document.querySelector('.quiz-starter');
    let starterBtn = document.querySelector('#starterBtn');
    let quizTimer = document.querySelector('#quizTimer');
    let timerInterval = null;
    window.description = null;
    window.starterContainer = starterContainer;

    // start quiz
    if (starterBtn) {
        starterBtn.addEventListener('click', function() {
            if (starterBtn.disabled) {
                return;
            }

            starterBtn.disabled = true;
            starterContainer.classList.add('d-none');
            $.ajax({
                type: "get",
                url: "{{ route('load.quiz.questions') }}",
                data: {
                    quiz_id: "{{ $quiz->id }}"
                },
                success: function(response) {
                    $('.load-content').html(response);
                    startTimer();
                },
                error: function() {
                    starterBtn.disabled = false;
                    starterContainer.classList.remove('d-none');
                    alert("{{ get_phrase('Unable to load questions. Please try again.') }}");
                }
            });
        });
    }

    function startTimer() {
        let timerContainer = document.querySelector('.timer-container');
        if (!timerContainer || !quizTimer) {
            return;
        }

        timerContainer.classList.remove('d-none');

        if (timerInterval) {
            clearInterval(timerInterval);
        }

        let duration = "{{ $quiz->duration }}";
        let durationArr = duration.split(":");

        let hour = parseInt(durationArr[0] || 0, 10);
        let minute = parseInt(durationArr[1] || 0, 10);
        let second = parseInt(durationArr[2] || 0, 10);

        quizTimer.innerHTML = (hour < 10 ? '0' + hour : hour) + ':' +
            (minute < 10 ? '0' + minute : minute) + ':' +
            (second < 10 ? '0' + second : second);

        timerInterval = setInterval(() => {
            if (hour === 0 && minute === 0 && second === 0) {
                clearInterval(timerInterval);
                timerInterval = null;
                endQuiz();
                return;
            }

            if (second === 0) {
                if (minute === 0) {
                    hour--;
                    minute = 59;
                } else {
                    minute--;
                }
                second = 59;
            } else {
                second--;
            }

            quizTimer.innerHTML = (hour < 10 ? '0' + hour : hour) + ':' +
                (minute < 10 ? '0' + minute : minute) + ':' +
                (second < 10 ? '0' + second : second);
        }, 1000);
    }

    function getResult(elem) {
        let id = $(elem).attr('id');
        starterContainer.classList.add('d-none');

        $.ajax({
            type: "get",
            url: "{{ route('load.quiz.result') }}",
            data: {
                submit_id: id,
                quiz_id: "{{ $quiz->id }}"
            },
            success: function(response) {
                $('.load-content').html(response);
            },
            error: function() {
                alert("{{ get_phrase('Unable to load result. Please try again.') }}");
            }
        });
    }
    window.getResult = getResult;

    function endQuiz() {
        submitQuiz();
    }
</script>
