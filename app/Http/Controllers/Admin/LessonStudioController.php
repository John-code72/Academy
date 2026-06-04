<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\FileUploader;
use App\Models\Lesson;
use App\Models\Section;
use App\Models\Setting;
use App\Services\AiCoursePersonalizer;
use App\Services\HeyGenCatalogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Session;
use Throwable;

class LessonStudioController extends Controller
{
    public function show(int $course, HeyGenCatalogService $heygen, AiCoursePersonalizer $personalizer)
    {
        $course = Course::findOrFail($course);

        $sections = Section::where('course_id', $course->id)
            ->orderBy('sort')
            ->with(['lessons' => fn ($q) => $q->orderBy('sort')])
            ->get();

        $lessons = Lesson::where('course_id', $course->id)->orderBy('sort')->get();

        return view('admin.lesson_studio.show', [
            'course' => $course,
            'sections' => $sections,
            'lessons' => $lessons,
            'avatars' => $heygen->avatars(),
            'voices' => $heygen->voices(),
            'heygenAvatarId' => get_settings('heygen_avatar_id') ?: config('heygen.avatar_id'),
            'heygenVoiceId' => get_settings('heygen_voice_id') ?: config('heygen.voice_id'),
            'heygenVoiceIdFr' => get_settings('heygen_voice_id_fr') ?: config('heygen.voice_id_fr'),
            'geminiOk' => (bool) config('gemini.api_key'),
            'heygenOk' => $heygen->isConfigured(),
            'openAiOk' => $personalizer->isAiConfigured(),
            'heygenPending' => $lessons->where('video_provider', 'heygen')->where('video_status', 'pending')->count(),
            'heygenFailed' => $lessons->where('video_provider', 'heygen')->where('video_status', 'failed')->count(),
        ]);
    }

    public function saveSettings(Request $request, int $course, HeyGenCatalogService $heygen)
    {
        $request->validate([
            'heygen_avatar_id' => 'required|string|max:255',
            'heygen_voice_id' => 'required|string|max:255',
            'heygen_voice_id_fr' => 'nullable|string|max:255',
        ]);

        foreach ([
            'heygen_avatar_id' => $request->heygen_avatar_id,
            'heygen_voice_id' => $request->heygen_voice_id,
            'heygen_voice_id_fr' => $request->heygen_voice_id_fr ?? '',
        ] as $type => $value) {
            if (Setting::where('type', $type)->exists()) {
                Setting::where('type', $type)->update(['description' => $value]);
            } else {
                Setting::insert(['type' => $type, 'description' => $value]);
            }
        }

        $heygen->clearCache();

        Session::flash('success', get_phrase('Studio settings saved.'));

        return redirect()->route('admin.lesson.studio', $course);
    }

    public function generateCurriculum(Request $request, int $course, AiCoursePersonalizer $personalizer)
    {
        $course = Course::findOrFail($course);

        $request->validate([
            'lesson_count' => 'nullable|integer|min:1|max:12',
            'brief'        => 'nullable|string|max:2000',
        ]);

        @set_time_limit(0);
        @ini_set('max_execution_time', '0');

        try {
            $result = $personalizer->generateForMainCourse(
                $course,
                (int) ($request->lesson_count ?? 6),
                $request->brief
            );
        } catch (Throwable $e) {
            Session::flash('error', get_phrase('AI generation failed: ') . $e->getMessage());

            return redirect()->route('admin.lesson.studio', $course->id);
        }

        $heygen = $result['heygen'];
        $msg = get_phrase('Lessons created:') . ' ' . $result['created'];
        if (($heygen['ready'] + $heygen['pending'] + $heygen['failed']) > 0) {
            $msg .= ' — ' . get_phrase('HeyGen:') . ' ' . $heygen['ready'] . ' ' . get_phrase('ready')
                . ', ' . $heygen['pending'] . ' ' . get_phrase('pending');
            if ($heygen['failed'] > 0) {
                $msg .= ', ' . $heygen['failed'] . ' ' . get_phrase('failed');
            }
        }

        Session::flash('success', $msg);

        return redirect()->route('admin.lesson.studio', $course->id);
    }

    public function storePdf(Request $request, int $course)
    {
        $course = Course::findOrFail($course);

        $request->validate([
            'title' => 'required|string|max:255',
            'pdf'   => 'required|file|mimes:pdf|max:51200',
            'section_id' => 'nullable|exists:sections,id',
            'is_free' => 'nullable|in:0,1',
        ]);

        $sectionId = $request->section_id ?: $this->ensureStudioSection($course)->id;

        $fileName = strtotime('now') . random(4) . '.pdf';
        $path = public_path('uploads/lesson_file/attachment');
        if (!File::isDirectory($path)) {
            File::makeDirectory($path, 0777, true, true);
        }
        FileUploader::upload($request->file('pdf'), 'uploads/lesson_file/attachment/' . $fileName);

        $sort = (int) (Lesson::where('course_id', $course->id)->max('sort') ?? 0) + 1;

        Lesson::create([
            'title' => $request->title,
            'user_id' => auth()->id(),
            'course_id' => $course->id,
            'section_id' => $sectionId,
            'sort' => $sort,
            'is_free' => (int) ($request->is_free ?? 0),
            'lesson_type' => 'document_type',
            'attachment' => $fileName,
            'attachment_type' => 'pdf',
            'duration' => '00:10:00',
            'status' => 1,
        ]);

        Session::flash('success', get_phrase('PDF lesson added.'));

        return redirect()->route('admin.lesson.studio', $course->id);
    }

    public function storeText(Request $request, int $course)
    {
        $course = Course::findOrFail($course);

        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'section_id' => 'nullable|exists:sections,id',
            'is_free' => 'nullable|in:0,1',
        ]);

        $sectionId = $request->section_id ?: $this->ensureStudioSection($course)->id;
        $sort = (int) (Lesson::where('course_id', $course->id)->max('sort') ?? 0) + 1;

        Lesson::create([
            'title' => $request->title,
            'user_id' => auth()->id(),
            'course_id' => $course->id,
            'section_id' => $sectionId,
            'sort' => $sort,
            'is_free' => (int) ($request->is_free ?? 0),
            'lesson_type' => 'text',
            'attachment' => $request->content,
            'attachment_type' => 'text',
            'ai_lesson_format' => 'text',
            'duration' => '00:05:00',
            'status' => 1,
        ]);

        Session::flash('success', get_phrase('Text lesson added.'));

        return redirect()->route('admin.lesson.studio', $course->id);
    }

    public function refreshHeygen(Request $request, int $course, AiCoursePersonalizer $personalizer)
    {
        Course::findOrFail($course);
        $stats = $personalizer->processHeyGenPendingForCourse($course, 3);

        Session::flash('success', get_phrase('HeyGen:') . ' ' . $stats['ready'] . ' ' . get_phrase('ready')
            . ', ' . $stats['pending'] . ' ' . get_phrase('pending')
            . ', ' . $stats['failed'] . ' ' . get_phrase('failed'));

        return redirect()->route('admin.lesson.studio', $course);
    }

    public function regenerateLessonVideo(int $course, int $lesson, AiCoursePersonalizer $personalizer)
    {
        $lesson = Lesson::where('course_id', $course)->findOrFail($lesson);
        $personalizer->regenerateMainLessonVideo($lesson);

        Session::flash('success', get_phrase('Video regeneration started.'));

        return redirect()->route('admin.lesson.studio', $course);
    }

    public function fixLessonAudio(int $course, int $lesson, AiCoursePersonalizer $personalizer)
    {
        $lesson = Lesson::where('course_id', $course)->findOrFail($lesson);
        $lang = optional($lesson->course)->language ?? 'English';
        $personalizer->finalizeMainLessonAudio($lesson, $lang);

        Session::flash('success', get_phrase('Audio updated.'));

        return redirect()->route('admin.lesson.studio', $course);
    }

    private function ensureStudioSection(Course $course): Section
    {
        $section = Section::where('course_id', $course->id)->orderBy('sort')->first();

        if ($section) {
            return $section;
        }

        return Section::create([
            'title' => get_phrase('Main section'),
            'user_id' => auth()->id() ?? $course->user_id,
            'course_id' => $course->id,
            'sort' => 1,
        ]);
    }
}
