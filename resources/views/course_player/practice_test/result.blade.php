<style>
    .question {
        min-height: auto !important;
    }

    .result-question {
        background: #ffffff;
        border: 1px solid #e9edf5;
        border-radius: 12px;
        padding: 12px;
    }

    .result .row.mb-3 {
        background: #f8faff;
        border: 1px solid #e6ebff;
        border-radius: 12px;
        padding: 12px;
        margin-left: 0;
        margin-right: 0;
    }
</style>

<div class="result">
    @php
        $submits = $result->submits ? json_decode($result->submits, true) : [];
        $correct_answers = $result->correct_answer ? json_decode($result->correct_answer, true) : [];
        $wrong_answers = $result->wrong_answer ? json_decode($result->wrong_answer, true) : [];
        $mark_per_question = $questions->count() > 0 ? ($quiz->total_mark / $questions->count()) : 0;
        @endphp

    <div class="row mb-3">
        <div class="col-md-6">
            <p>{{ get_phrase('Duration : ') }}
                @php $duration = explode(':', $quiz->duration); @endphp
                {{ $duration[0] }} {{ get_phrase('Hour') }}
                {{ $duration[1] }} {{ get_phrase('Minute') }}
                {{ $duration[1] }} {{ get_phrase('Second') }}
            </p>
            <p>{{ get_phrase('Total Mark : ') }}{{ $quiz->total_mark }}</p>
            <p>{{ get_phrase('Pass Mark : ') }}{{ $quiz->pass_mark }}</p>
        </div>
        <div class="col-md-6">
            <p>{{ get_phrase('Correct Answer : ') }}{{ count($correct_answers) }}</p>
            <p>{{ get_phrase('Wrong Answer : ') }}{{ count($wrong_answers) }}</p>
            <p>{{ get_phrase('Obtained marks') }} : {{ count($correct_answers) * $mark_per_question }}</p>
            <p>{{ get_phrase('Result : ') }}
                @if (count($correct_answers)*$mark_per_question >= $quiz->pass_mark)
                    <span class="text-success">{{ get_phrase('Passed') }}</span>
                @else
                    <span class="text-danger">{{ get_phrase('Failed') }}</span>
                @endif
            </p>
        </div>
    </div>

    @foreach ($questions as $key => $question)
        @php
            $given_answer = '-';
            if ($question->type == 'true_false') {
                $given_answer = $question->answer;
            } elseif ($question->type == 'mcq' || $question->type == 'fill_blanks') {
                $given_answer = implode(', ', json_decode($question->answer, true) ?? []);
            }
            $user_answers = array_key_exists($question->id, $submits) ? $submits[$question->id] : [];
        @endphp

        <div class="result-question mb-4 @if ($key > 0)  @endif">
            <div class="mb-1 d-flex align-items-center gap-3">
                <span class="serial">{{ ++$key }}</span>
                @if ($question->type == 'speaking_prompt')
                    <div class="text-muted">{{ get_phrase('Speaking response') }}</div>
                @else
                    <div>{!! $question->title !!}</div>
                @endif

                @if (in_array($question->id, $correct_answers))
                    <i class="fi fi-br-check text-success"></i>
                @elseif(in_array($question->id, $wrong_answers))
                    <i class="fi fi-br-cross-small text-danger"></i>
                @endif
            </div>

            <div class="row gap-0">
                @if ($question->type == 'mcq')
                    @php $options = json_decode($question->options, true) ?? []; @endphp
                    @foreach ($options as $index => $option)
                        @php $val = $user_answers ? array_search($option, $user_answers) : ''; @endphp
                        <div class="col-sm-6">
                            <input class="form-check-input" type="checkbox" value="{{ $option }}" @if (is_numeric($val)) checked @endif disabled>
                            <label class="form-check-label text-capitalize">{{ $option }}</label>
                        </div>
                    @endforeach
                @elseif($question->type == 'fill_blanks')
                    <input type="text" class="form-control tagify" data-role="tagsinput" value="{{ json_encode($user_answers) }}" disabled>
                @elseif($question->type == 'true_false')
                    <div class="col-sm-2">
                        <input class="form-check-input" type="radio" disabled @if ($user_answers == 'true') checked @endif>
                        <label class="form-check-label">{{ get_phrase('True') }}</label>
                    </div>
                    <div class="col-sm-2">
                        <input class="form-check-input" type="radio" disabled @if ($user_answers == 'false') checked @endif>
                        <label class="form-check-label">{{ get_phrase('False') }}</label>
                    </div>
                @elseif($question->type == 'long_answer')
                    <textarea class="form-control" rows="7" disabled>{{ is_array($user_answers) ? implode(', ', $user_answers) : $user_answers }}</textarea>
                    <p class="text-warning fw-600 mt-2 mb-0">
                        {{ get_phrase('Manual review required for this writing response.') }}
                    </p>
                @elseif($question->type == 'speaking_prompt')
                    @php
                        $mediaPath = is_array($user_answers)
                            ? ($user_answers['video'] ?? $user_answers['audio'] ?? null)
                            : $user_answers;
                        $analysis = is_array($user_answers) ? ($user_answers['analysis'] ?? null) : null;
                    @endphp
                    @if ($mediaPath)
                        @php
                            $ext = strtolower(pathinfo($mediaPath, PATHINFO_EXTENSION));
                        @endphp
                        @if (in_array($ext, ['webm', 'mp4', 'mov', 'm4v'], true))
                            <video class="w-100 rounded-3" controls playsinline>
                                <source src="{{ asset('storage/' . $mediaPath) }}">
                            </video>
                        @else
                            <audio class="w-100" controls>
                                <source src="{{ asset('storage/' . $mediaPath) }}">
                            </audio>
                        @endif
                    @else
                        <p class="text-danger mb-1">{{ get_phrase('No recording was submitted.') }}</p>
                    @endif
                    @if (is_array($analysis))
                        <div class="mt-2 p-2 border rounded">
                            <strong>{{ get_phrase('AI analysis status') }}:</strong> {{ $analysis['status'] ?? 'n/a' }}<br>
                            @if (!empty($analysis['transcript']))
                                <strong>{{ get_phrase('Transcript') }}:</strong> {{ $analysis['transcript'] }}<br>
                            @endif
                            @if (!empty($analysis['scores']))
                                <strong>{{ get_phrase('Scores') }}:</strong>
                                <pre class="mb-0">{{ json_encode($analysis['scores'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                            @elseif(!empty($analysis['message']))
                                <strong>{{ get_phrase('Message') }}:</strong> {{ $analysis['message'] }}
                            @endif
                        </div>
                    @endif
                @endif
                @if (!in_array($question->type, ['long_answer', 'speaking_prompt']))
                    <p class="text-capitalize text-success fw-600">
                        {{ get_phrase('Answer : ') }}{{ $given_answer }}
                    </p>
                @endif
            </div>
        </div>
    @endforeach

    @if (count($wrong_answers) > 0)
        <div class="mb-4 p-3 border rounded bg-white">
            <h5 class="mb-2">{{ get_phrase('Want to improve?') }}</h5>
            <p class="mb-3 text-muted">
                {{ get_phrase('Generate a short personalized course built from the topics you missed in this test.') }}
            </p>
            <form action="{{ route('personalized.course.generate') }}" method="POST">
                @csrf
                <input type="hidden" name="submission_id" value="{{ $result->id }}">
                <button type="submit" class="eBtn gradient border-0 d-inline-flex align-items-center gap-2">
                    <i class="fi fi-rr-bulb"></i>
                    {{ get_phrase('Generate my personalized course') }}
                </button>
            </form>
        </div>
    @endif

    <div class="row">
        <div class="col-12 d-flex gap-3 justify-content-center">
            <button type="button" class="eBtn gradient border-0 mb-4 d-flex align-items-center gap-2" id="backBtn" onclick="back()"><i class="fi fi-rr-angle-small-left fs-5"></i>{{ get_phrase('Back') }}</button>
        </div>
    </div>
</div>

<script>
    function back() {
        if (window.description) {
            window.description.classList.remove('d-none');
        }
        if (window.starterContainer) {
            window.starterContainer.classList.remove('d-none');
        }
        $('.load-content').html('');
    }

    $('.result .tagify:not(.inited)').each(function(index, element) {
        var tagify = new Tagify(element, {
            placeholder: '{{ get_phrase('Enter your keywords') }}'
        });
        $(element).addClass('inited');
    });
</script>
