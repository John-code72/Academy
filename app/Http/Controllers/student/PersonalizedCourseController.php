<?php

namespace App\Http\Controllers\student;

use App\Http\Controllers\Controller;
use App\Models\PersonalizedCourse;
use App\Models\PersonalizedCourseLesson;
use App\Models\QuizSubmission;
use App\Services\AiCoursePersonalizer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Throwable;

class PersonalizedCourseController extends Controller
{
    public function index()
    {
        $courses = PersonalizedCourse::where('user_id', auth()->id())
            ->withCount('lessons')
            ->orderByDesc('created_at')
            ->get();

        return view('frontend.default.personalized_course.index', [
            'courses' => $courses,
        ]);
    }

    public function generate(Request $request, AiCoursePersonalizer $personalizer)
    {
        $request->validate([
            'submission_id' => 'required|numeric',
        ]);

        $submission = QuizSubmission::where('id', $request->submission_id)
            ->where('user_id', auth()->id())
            ->first();

        if (!$submission) {
            Session::flash('error', get_phrase('Submission not found.'));
            return back();
        }

        try {
            $course = $personalizer->generateForSubmission($submission);
        } catch (Throwable $e) {
            Session::flash('error', get_phrase('Course generation failed: ') . $e->getMessage());
            return back();
        }

        Session::flash('success', get_phrase('Your personalized course is ready.'));
        return redirect()->route('personalized.course.show', ['slug' => $course->slug]);
    }

    public function show(string $slug)
    {
        $course = PersonalizedCourse::where('slug', $slug)
            ->where('user_id', auth()->id())
            ->with('lessons')
            ->firstOrFail();

        return view('frontend.default.personalized_course.show', [
            'course' => $course,
        ]);
    }

    public function lesson(string $slug, int $lessonId, AiCoursePersonalizer $personalizer)
    {
        $course = PersonalizedCourse::where('slug', $slug)
            ->where('user_id', auth()->id())
            ->with('lessons')
            ->firstOrFail();

        $lesson = $course->lessons->firstWhere('id', $lessonId);
        if (!$lesson) {
            abort(404);
        }

        if ($lesson->video_provider === 'heygen' && in_array($lesson->video_status, ['pending', 'processing'], true)) {
            $personalizer->refreshHeyGenStatus($lesson);
            $lesson->refresh();
        }

        $isAudioLesson = ($lesson->lesson_format === 'audio')
            || ($lesson->audio_status === 'browser')
            || ($lesson->audio_url || $lesson->video_script);

        if ($isAudioLesson && !$lesson->audio_url && $lesson->video_script) {
            @set_time_limit(300);
            $personalizer->ensureLessonAudio($lesson, $course->language ?? 'English');
            $lesson->refresh();
        }

        $previous = $course->lessons->where('sort', '<', $lesson->sort)->last();
        $next = $course->lessons->where('sort', '>', $lesson->sort)->first();

        return view('frontend.default.personalized_course.lesson', [
            'course' => $course,
            'lesson' => $lesson,
            'previous' => $previous,
            'next' => $next,
        ]);
    }

    public function checkVideo(string $slug, int $lessonId, AiCoursePersonalizer $personalizer)
    {
        $course = PersonalizedCourse::where('slug', $slug)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $lesson = PersonalizedCourseLesson::where('personalized_course_id', $course->id)
            ->where('id', $lessonId)
            ->firstOrFail();

        $personalizer->refreshHeyGenStatus($lesson);
        $lesson->refresh();

        return response()->json([
            'status' => $lesson->video_status,
            'provider' => $lesson->video_provider,
            'video_url' => $lesson->video_url,
            'thumbnail' => $lesson->video_thumbnail,
            'error' => $lesson->video_error,
        ]);
    }

    public function regenerateVideo(string $slug, int $lessonId, AiCoursePersonalizer $personalizer)
    {
        $course = PersonalizedCourse::where('slug', $slug)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $lesson = PersonalizedCourseLesson::where('personalized_course_id', $course->id)
            ->where('id', $lessonId)
            ->firstOrFail();

        $isAudio = ($lesson->lesson_format === 'audio');
        $ok = $personalizer->regenerateLessonVideo($lesson);
        if ($ok) {
            Session::flash('success', $isAudio
                ? get_phrase('Audio narration regenerated.')
                : get_phrase('Video regeneration started. It will be ready in a few minutes.'));
        } else {
            Session::flash('error', $isAudio
                ? get_phrase('Could not regenerate audio narration.')
                : get_phrase('Could not start video regeneration.'));
        }

        return redirect()->route('personalized.course.lesson', [
            'slug' => $course->slug,
            'lesson_id' => $lesson->id,
        ]);
    }
}
