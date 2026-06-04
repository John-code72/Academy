<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\PersonalizedCourseLesson;
use App\Services\AiCoursePersonalizer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Throwable;

class AiStudioController extends Controller
{
    public function index(AiCoursePersonalizer $personalizer)
    {
        $courses = Course::orderByDesc('id')->limit(100)->get(['id', 'title', 'slug', 'language', 'status']);

        $courseStats = Lesson::query()
            ->select('course_id')
            ->selectRaw("SUM(CASE WHEN ai_lesson_format = 'video' THEN 1 ELSE 0 END) as video_lessons")
            ->selectRaw("SUM(CASE WHEN ai_lesson_format = 'audio' THEN 1 ELSE 0 END) as audio_lessons")
            ->selectRaw("SUM(CASE WHEN video_provider = 'heygen' AND video_status = 'pending' THEN 1 ELSE 0 END) as heygen_pending")
            ->selectRaw("SUM(CASE WHEN video_provider = 'heygen' AND video_status = 'failed' THEN 1 ELSE 0 END) as heygen_failed")
            ->selectRaw("SUM(CASE WHEN video_provider = 'heygen' AND video_status = 'ready' THEN 1 ELSE 0 END) as heygen_ready")
            ->whereNotNull('course_id')
            ->groupBy('course_id')
            ->get()
            ->keyBy('course_id');

        $personalizedPending = PersonalizedCourseLesson::where('video_provider', 'heygen')
            ->where('video_status', 'pending')
            ->count();

        $categoryCount = Category::count();
        $categoriesWithCourse = Course::whereIn('status', ['active', 'pending', 'upcoming'])
            ->whereNotNull('category_id')
            ->distinct()
            ->count('category_id');

        return view('admin.ai_studio.index', [
            'courses' => $courses,
            'courseStats' => $courseStats,
            'categoryCount' => $categoryCount,
            'categoriesWithCourse' => $categoriesWithCourse,
            'geminiOk' => (bool) config('gemini.api_key'),
            'heygenOk' => function_exists('heygen_is_configured') && heygen_is_configured(),
            'openAiOk' => $personalizer->isAiConfigured(),
            'personalizedPending' => $personalizedPending,
            'mainPending' => Lesson::where('video_provider', 'heygen')->where('video_status', 'pending')->count(),
        ]);
    }

    public function generate(Request $request, AiCoursePersonalizer $personalizer)
    {
        $request->validate([
            'lesson_count'  => 'nullable|integer|min:3|max:12',
            'brief'         => 'nullable|string|max:2000',
            'language'      => 'nullable|string|max:50',
            'skip_existing' => 'nullable|boolean',
        ]);

        @set_time_limit(0);
        @ini_set('max_execution_time', '0');

        try {
            $result = $personalizer->createAllCoursesFromScratch(
                (int) ($request->lesson_count ?? 6),
                $request->brief,
                $request->language ?: 'English',
                $request->boolean('skip_existing', true),
                auth()->id()
            );
        } catch (Throwable $e) {
            Session::flash('error', get_phrase('AI generation failed: ') . $e->getMessage());

            return redirect()->route('admin.ai.studio');
        }

        $msg = get_phrase('Courses created:') . ' ' . $result['created']
            . ', ' . get_phrase('lessons:') . ' ' . $result['lesson_total']
            . ', ' . get_phrase('skipped (existing):') . ' ' . $result['skipped'];

        if (!empty($result['errors'])) {
            $msg .= '. ' . get_phrase('Errors:') . ' ' . implode('; ', array_slice($result['errors'], 0, 3));
            if (count($result['errors']) > 3) {
                $msg .= '…';
            }
        }

        Session::flash('success', $msg);

        return redirect()->route('admin.ai.studio');
    }

    public function refreshHeygen(Request $request, AiCoursePersonalizer $personalizer)
    {
        $request->validate([
            'scope' => 'nullable|in:main,personalized,all',
            'course_id' => 'nullable|exists:courses,id',
        ]);

        $scope = $request->scope ?? 'all';
        $refreshed = 0;
        $retried = 0;

        if (in_array($scope, ['main', 'all'], true)) {
            $query = Lesson::where('video_provider', 'heygen');
            if ($request->course_id) {
                $query->where('course_id', $request->course_id);
            }
            foreach ($query->get() as $lesson) {
                if ($lesson->video_status === 'failed') {
                    $personalizer->regenerateMainLessonVideo($lesson);
                    $retried++;
                } elseif ($lesson->video_status === 'pending') {
                    $personalizer->refreshHeyGenStatusForMainLesson($lesson);
                    $refreshed++;
                }
            }
        }

        if (in_array($scope, ['personalized', 'all'], true)) {
            $pQuery = PersonalizedCourseLesson::where('video_provider', 'heygen');
            if ($request->course_id) {
                $pQuery->whereHas('course', fn ($q) => $q->where('source_course_id', $request->course_id));
            }
            foreach ($pQuery->get() as $lesson) {
                if ($lesson->video_status === 'failed') {
                    $personalizer->regenerateLessonVideo($lesson);
                    $retried++;
                } elseif ($lesson->video_status === 'pending') {
                    $personalizer->refreshHeyGenStatus($lesson);
                    $refreshed++;
                }
            }
        }

        Session::flash('success', get_phrase('HeyGen:') . " {$refreshed} " . get_phrase('refreshed') . ", {$retried} " . get_phrase('retried'));

        return redirect()->route('admin.ai.studio');
    }

    public function fixAudio(Request $request, AiCoursePersonalizer $personalizer)
    {
        $request->validate(['course_id' => 'nullable|exists:courses,id']);

        $query = Lesson::where('ai_lesson_format', 'audio')
            ->where(function ($q) {
                $q->whereNull('audio_url')
                    ->orWhereIn('audio_status', ['browser', 'pending', 'failed']);
            });

        if ($request->course_id) {
            $query->where('course_id', $request->course_id);
        }

        $fixed = 0;
        $language = optional(Course::find($request->course_id))->language ?? 'English';

        foreach ($query->get() as $lesson) {
            $lang = optional($lesson->course)->language ?? $language;
            if ($personalizer->finalizeMainLessonAudio($lesson, $lang)) {
                $fixed++;
            }
        }

        Session::flash('success', get_phrase('Gemini audio updated for') . " {$fixed} " . get_phrase('lessons'));

        return redirect()->route('admin.ai.studio');
    }
}
