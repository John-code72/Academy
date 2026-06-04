<?php

namespace App\Http\Controllers\student;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\Question;
use App\Models\QuizSubmission;
use App\Support\DefrilexTestDeployment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class QuizController extends Controller
{
    private function canUserViewPracticeTest(Lesson $lesson): bool
    {
        if (($lesson->lesson_type ?? '') !== 'practice_test') {
            return true;
        }

        $scope = $lesson->visibility_scope ?: 'all_authenticated';
        $role = auth()->user()->role ?? null;

        if ($scope === 'students_only') {
            return $role === 'student';
        }

        if ($scope === 'instructors_only') {
            return $role === 'instructor';
        }

        return true;
    }

    public function upload_practice_recording(Request $request)
    {
        $request->validate([
            'video'       => 'required|file|mimes:webm,mp4,mov,quicktime,m4v|max:102400',
            'quiz_id'     => 'required|numeric',
            'question_id' => 'required|numeric',
        ]);

        $fileName = Str::uuid() . '.' . $request->file('video')->getClientOriginalExtension();
        $path = $request->file('video')->storeAs('practice_video/' . auth()->id(), $fileName, 'public');

        return response()->json([
            'status' => 'success',
            'path'   => $path,
        ]);
    }

    public function index_practice_tests()
    {
        $tests = Lesson::where('lesson_type', 'practice_test')
            ->get()
            ->filter(fn (Lesson $lesson) => $this->canUserViewPracticeTest($lesson))
            ->values();

        foreach ($tests as $test) {
            $test->questions_count = Question::where('quiz_id', $test->id)->count();
            $test->deployment_meta = DefrilexTestDeployment::resolve($test->title);
        }

        $tests = $tests->sort(function ($a, $b) {
            $pa = DefrilexTestDeployment::sortPriority($a->title);
            $pb = DefrilexTestDeployment::sortPriority($b->title);
            if ($pa !== $pb) {
                return $pa <=> $pb;
            }

            return $b->id <=> $a->id;
        })->reverse()->values();

        return view('frontend.default.practice_test.list', [
            'tests' => $tests,
        ]);
    }

    public function show_practice_test($id)
    {
        $quiz = Lesson::where('id', $id)
            ->where('lesson_type', 'practice_test')
            ->firstOrFail();

        if (!$this->canUserViewPracticeTest($quiz)) {
            abort(403);
        }

        return view('frontend.default.practice_test.show', ['quiz' => $quiz]);
    }

    public function quiz_submit(Request $request)
    {
        $lesson = Lesson::where('id', $request->quiz_id)->firstOrFail();
        if (!$this->canUserViewPracticeTest($lesson)) {
            abort(403);
        }
        $retake = $lesson->retake;
        $submit = QuizSubmission::where('quiz_id', $request->quiz_id)->where('user_id', auth()->user()->id)->count();
        $isPracticeTest = $lesson->lesson_type === 'practice_test';
        if (!$isPracticeTest && $submit > $retake) {
            Session::flash('warning', get_phrase('Attempt has been over.'));
            $redirectUrl = $isPracticeTest
                ? route('practice.test.show', ['id' => $lesson->id])
                : route('course.player', ['slug' => $lesson->course->slug, 'id' => $lesson->id ?? '']);
            echo '<script>
            window.location.href = "'.$redirectUrl.'"
            </script>';
            die();
        }

        $inputs  = collect($request->all());
        $quiz_id = $inputs->pull('quiz_id');
        $inputs->forget(['_token', 'quiz_id']);

        $submits = $inputs->whereNotNull();
        foreach ($submits as $key => $submit) {
            if (is_string($submit) && ($submit != 'true' && $submit != 'false')) {
                $decoded = json_decode($submit, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded) && isset($decoded[0]['value'])) {
                    $submits[$key] = array_column($decoded, 'value');
                }
            }
        }

        $question_ids = $submits->keys();
        $questions    = Question::whereIn('id', $question_ids)->get();

        $right_answers = $wrong_answers = [];
        foreach ($questions as $key => $question) {

            $correct_answer = json_decode($question->answer, true);
            $submitted      = $submits[$question->id] ?? null;

            if ($submitted === null) {
                continue;
            }

            if ($question->type == 'mcq') {
                $isCorrect = empty(array_diff($correct_answer, $submitted)) && empty(array_diff($submitted, $correct_answer));
            } elseif ($question->type == 'fill_blanks') {
                $isCorrect = count($correct_answer) === count($submitted);

                if ($isCorrect) {
                    for ($i = 0; $i < count($correct_answer); $i++) {
                        if (strtolower($correct_answer[$i]) != strtolower($submitted[$i])) {
                            $isCorrect = false;
                            break;
                        }
                    }
                } else {
                    $isCorrect = false;
                }
            } elseif ($question->type == 'true_false') {
                $isCorrect = strtolower(json_encode($correct_answer)) == strtolower($submitted);
            } elseif ($question->type == 'long_answer') {
                // Long answers require manual review, so exclude from auto right/wrong buckets.
                continue;
            } elseif ($question->type == 'speaking_prompt') {
                $mediaPath = is_string($submitted) ? $submitted : null;
                $analysis = $this->analyzeSpeakingResponse($question->title, $mediaPath);
                $submits[$question->id] = [
                    'video'    => $mediaPath,
                    'analysis' => $analysis,
                ];
                continue;
            }

            $isCorrect ? $right_answers[] = $question->id : $wrong_answers[] = $question->id;
        }

        $data['quiz_id']        = $quiz_id;
        $data['user_id']        = auth()->user()->id;
        $data['correct_answer'] = $right_answers ? json_encode($right_answers) : null;
        $data['wrong_answer']   = $wrong_answers ? json_encode($wrong_answers) : null;
        $data['submits']        = $submits->count() > 0 ? json_encode($submits->toArray()) : null;

        QuizSubmission::insert($data);
        Session::flash('success', get_phrase('Your answers have been submitted.'));
        $redirectUrl = $isPracticeTest
            ? route('practice.test.show', ['id' => $lesson->id])
            : route('course.player', ['slug' => $lesson->course->slug, 'id' => $lesson->id ?? '']);
        echo '<script>
        window.location.href = "'.$redirectUrl.'"
        </script>';
        die();
    }

    public function load_result(Request $request)
    {
        $page_data['quiz']      = Lesson::where('id', $request->quiz_id)->first();
        if (!$page_data['quiz']) {
            abort(404);
        }
        if (!$this->canUserViewPracticeTest($page_data['quiz'])) {
            abort(403);
        }
        $page_data['questions'] = Question::where('quiz_id', $request->quiz_id)->orderBy('sort')->get();
        $page_data['result']    = QuizSubmission::where('id', $request->submit_id)
            ->where('quiz_id', $request->quiz_id)
            ->where('user_id', auth()->user()->id)
            ->first();
        $resultView = ($page_data['quiz']->lesson_type ?? '') === 'practice_test'
            ? 'course_player.practice_test.result'
            : 'course_player.quiz.result';

        return view($resultView, $page_data);
    }

    public function load_questions(Request $request)
    {
        $page_data['quiz']      = Lesson::where('id', $request->quiz_id)->first();
        if (!$page_data['quiz']) {
            abort(404);
        }
        if (!$this->canUserViewPracticeTest($page_data['quiz'])) {
            abort(403);
        }
        $page_data['questions'] = Question::where('quiz_id', $request->quiz_id)->orderBy('sort')->get();
        $page_data['submits']   = QuizSubmission::where('quiz_id', $request->quiz_id)
            ->where('user_id', auth()->user()->id)
            ->get();
        $questionsView = ($page_data['quiz']->lesson_type ?? '') === 'practice_test'
            ? 'course_player.practice_test.questions'
            : 'course_player.quiz.questions';

        if (($page_data['quiz']->lesson_type ?? '') !== 'practice_test') {
            $page_data['course_details'] = Course::where('id', $page_data['quiz']->course_id)->first();
        }

        return view($questionsView, $page_data);
    }

    private function analyzeSpeakingResponse(string $prompt, ?string $mediaPath): array
    {
        if (!$mediaPath) {
            return [
                'status'  => 'failed',
                'message' => 'Recording file missing.',
            ];
        }

        if (!env('OPENAI_API_KEY')) {
            return [
                'status'  => 'pending_manual_review',
                'message' => 'AI key is not configured.',
            ];
        }

        if (!Storage::disk('public')->exists($mediaPath)) {
            return [
                'status'  => 'failed',
                'message' => 'Recording file not found on server.',
            ];
        }

        try {
            $absolutePath = storage_path('app/public/' . $mediaPath);
            $transcriptionResponse = Http::withToken(env('OPENAI_API_KEY'))
                ->attach('file', fopen($absolutePath, 'r'), basename($absolutePath))
                ->post('https://api.openai.com/v1/audio/transcriptions', [
                    'model' => 'gpt-4o-mini-transcribe',
                ]);

            if (!$transcriptionResponse->successful()) {
                return [
                    'status'  => 'failed',
                    'message' => 'Transcription failed.',
                ];
            }

            $transcript = (string) ($transcriptionResponse->json('text') ?? '');

            $analysisPrompt = "You are evaluating a speaking assessment response.\n"
                . "Prompt: {$prompt}\n"
                . "Transcript: {$transcript}\n\n"
                . "Return ONLY valid JSON with keys: clarity, tone, organization, conciseness, vocabulary_grammar, poise, total, recommendation, summary.\n"
                . "Each score is 0-10, total is 0-60, recommendation is one of pass/borderline/fail.";

            $analysisResponse = Http::withToken(env('OPENAI_API_KEY'))
                ->post('https://api.openai.com/v1/chat/completions', [
                    'model' => 'gpt-4o-mini',
                    'messages' => [
                        ['role' => 'system', 'content' => 'Return strict JSON only.'],
                        ['role' => 'user', 'content' => $analysisPrompt],
                    ],
                    'temperature' => 0.2,
                ]);

            if (!$analysisResponse->successful()) {
                return [
                    'status'     => 'analyzed_transcript_only',
                    'transcript' => $transcript,
                    'message'    => 'AI scoring failed.',
                ];
            }

            $rawContent = (string) ($analysisResponse->json('choices.0.message.content') ?? '');
            $parsed = json_decode($rawContent, true);

            return [
                'status'     => 'analyzed',
                'transcript' => $transcript,
                'scores'     => is_array($parsed) ? $parsed : ['raw' => $rawContent],
            ];
        } catch (Throwable $e) {
            return [
                'status'  => 'failed',
                'message' => 'AI analysis exception: ' . $e->getMessage(),
            ];
        }
    }
}
